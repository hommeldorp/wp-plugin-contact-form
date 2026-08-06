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

wp_register_script(
	'edit-script',
	plugins_url( 'src/contact-form/edit.js', __FILE__ ),
	array( 'wp-i18n' ),
	'0.0.1'
);

// these functions ensure translation keys from the view are recognized when generating files and that they are loaded
// into the UI
wp_register_script(
	'view-script',
	plugins_url( 'build/contact-form/view/index.js', __FILE__ ),
	array( 'wp-i18n' ),
	'0.0.1'
);

add_action( 'wp_enqueue_scripts', 'contact_form_load_view_textdomain', 100 );

function contact_form_load_view_textdomain() {
	wp_set_script_translations(
		'view-script',
		'contact-form',
		plugin_dir_path( __FILE__ ) . 'languages'
	);
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

function get_admin_email_from_db () {
	global $wpdb;

	$admin_email = $wpdb->get_var( "SELECT option_value FROM wp_options WHERE option_name = 'admin_email'" );

	if ($wpdb->last_error) {
		throw new Exception( $wpdb->last_error );
	}

	return $admin_email;
}

function hommeldorp_contact_form_post_message(WP_REST_Request $request) {

	$data = json_decode(file_get_contents("https://cap.hommeldorp.nl/siteverify",
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

	try {
		$recipient = get_admin_email_from_db();
	} catch (Exception $e) {
		error_log("Get admin email: ".$e->getMessage());
		return new WP_Error( 'email_not_sent', 'Email not sent.', array( 'status' => 500 ));
	}

	// at least for transip, it seems this *must* be a hommeldorp.nl address
	$from = "From: " . $request->get_param('name') . "<" . $recipient . ">";

	// if the sender didn't provide an email, use the admin email
	$replyTo = $request->get_param('email') != ''
		? "Reply-To: " . $request->get_param("name") . " <" . $request->get_param('email') . ">"
		: "";

	error_log(print_r($from, TRUE));
	error_log(print_r($recipient, TRUE));

	$headers = array( 'Content-Type: text/plain; charset=UTF-8', $from, $replyTo);

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
