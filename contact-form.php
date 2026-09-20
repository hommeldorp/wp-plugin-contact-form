<?php
/**
 * Plugin Name:       Contact Form
 * Description:       A block that opens a contact form panel when clicked.
 * Version:           0.1.0
 * Requires at least: 6.8
 * Requires PHP:      7.4
 * Author:            Greg Rozmarynowycz
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       contact-form
 * Domain Path:       /languages
 *
 * @package Hommeldorp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
 * based on the registered block metadata. Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
 */
function hommeldorp_contact_form_block_init() {
	wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
}
add_action( 'init', 'hommeldorp_contact_form_block_init' );

function contact_form_load_textdomain() {
	load_plugin_textdomain(
		'contact-form',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'init', 'contact_form_load_textdomain' );

// these functions ensure translation keys from the view are recognized when generating files and that they are loaded
// into the UI
add_action( 'init', 'contact_form_register_i18n_scripts' );

function contact_form_register_i18n_scripts() {
	wp_register_script(
		'edit-script',
		plugins_url( 'src/contact-form/edit.js', __FILE__ ),
		array( 'wp-i18n' ),
		'0.0.1'
	);

	wp_register_script(
		'view-script',
		plugins_url( 'build/contact-form/view/index.js', __FILE__ ),
		array( 'wp-i18n' ),
		'0.0.1'
	);
}

add_action( 'wp_enqueue_scripts', 'contact_form_load_view_textdomain', 100 );

function contact_form_load_view_textdomain() {
	wp_set_script_translations(
		'view-script',
		'contact-form',
		plugin_dir_path( __FILE__ ) . 'languages'
	);
}

// routes wp_mail() through SMTP instead of the host's sendmail.
// With no SMTP_HOST set (e.g. local dev), wp_mail() keeps its default transport.

add_action( 'phpmailer_init', 'contact_form_configure_smtp' );

function contact_form_configure_smtp( PHPMailer\PHPMailer\PHPMailer $mailer ) {
	$host = getenv( 'SMTP_HOST' );

	if ( ! $host ) {
		error_log('SMTP_HOST not set');
		return;
	}

	$mailer->isSMTP();
	$mailer->Host = $host;
	$mailer->Port = (int) ( getenv( 'SMTP_PORT' ) ?: 587 );

	// "tls" for STARTTLS, "ssl" for implicit TLS. Local mail catchers speak plain SMTP, so an empty
	// SMTP_SECURE also switches off the automatic STARTTLS upgrade
	$secure = getenv( 'SMTP_SECURE' );

	if ( $secure ) {
		$mailer->SMTPSecure = $secure;
	} else {
		$mailer->SMTPSecure  = '';
		$mailer->SMTPAutoTLS = false;
	}

	$user = getenv( 'SMTP_USER' );
	$pass = getenv( 'SMTP_PASS' );

	// mail catchers like Mailpit accept unauthenticated connections
	if ( $user && $pass ) {
		$mailer->SMTPAuth = true;
		$mailer->Username = $user;
		$mailer->Password = $pass;
	}
}

// Contact Form REST API

add_action( 'rest_api_init', 'hommeldorp_contact_form_register_routes' );

function hommeldorp_contact_form_register_routes() {
	register_rest_route( 'contact-slide-in/v1', '/message', [
		'methods' => 'POST',
		'callback' => 'hommeldorp_contact_form_post_message',
		'permission_callback' => '__return_true',
		'args' => [
			'name' => [
				'required' => true,
				'validate_callback' => function ($param) {
					return strlen($param) >= 2;
				},
				'sanitize_callback' => 'sanitize_text_field',
				'type' => 'string',
				'description' => 'The name of the sender'
			],
			'email' => [
				'sanitize_callback' => 'sanitize_email',
				'type' => 'string',
				'description' => 'The email of the sender, to reply back to'
			],
			'cap-token' => [
				'required' => true,
				'sanitize_callback' => 'sanitize_text_field',
				'type' => 'string',
				'description' => 'Captcha token from Cap widget'
			],
			'message' => [
				'required' => true,
				'validate_callback' => function ($param) {
					return strlen($param) >= 20;
				},
				'sanitize_callback' => 'sanitize_textarea_field',
				'type' => 'string',
			]
		]
	]);
}

function hommeldorp_contact_form_post_message(WP_REST_Request $request) {
	if (!getenv("CAP_SECRET_KEY")) {
		error_log("Cap secret key not set");
		return new WP_Error('email_not_sent', 'Email not sent');
	}

	$data = json_decode(file_get_contents("https://cap.hommeldorp.nl/84e2a6d091/siteverify",
		false, stream_context_create([
			"http" => [
				"method" => "POST",
				"header" => "Content-Type: application/json",
				"content" => json_encode(["secret"=>getenv("CAP_SECRET_KEY"),"response"=>$request->get_param('cap-token')])
			]
		])
	), true);

	if (!$data || !$data['success']) {
		return new WP_Error( 'invalid_captcha', 'Invalid captcha.', array( 'status' => 400 ));
	}

	$recipient = get_option( 'admin_email' );

	if ( ! $recipient ) {
		error_log("Get admin email: the admin_email option is empty.");
		return new WP_Error( 'email_not_sent', 'Email not sent.', array( 'status' => 500 ));
	}

	// at least for transip, it seems this *must* be a hommeldorp.nl address
	$from = "From: " . $request->get_param('name') . " via contact form<" . $recipient . ">";

	// if the sender didn't provide an email, use the admin email
	$replyTo = $request->get_param('email') != ''
		? "Reply-To: " . $request->get_param("name") . " <" . $request->get_param('email') . ">"
		: "";

	$headers = array( 'Content-Type: text/plain; charset=UTF-8', $from, $replyTo);

	error_log("Attempt send email " . $request->get_param('message'));
	// errors form this function trigger the wp_mail_failed action
	$result = wp_mail( $recipient, 'Contact Form Submission', $request->get_param('message'), $headers );

	if (! $result ) {
		return new WP_Error( 'email_not_sent', 'Email not sent.', array( 'status' => 500 ));
	}

	return new WP_REST_Response( 'success' );
}

add_action( 'wp_mail_failed', 'on_mail_error');
function on_mail_error( $wp_error ) {
	error_log("Failed to send e-mail: ".$wp_error->get_error_message());
}
