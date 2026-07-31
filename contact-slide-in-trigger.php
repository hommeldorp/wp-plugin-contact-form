<?php
/**
 * Plugin Name:       Contact Slide In Trigger
 * Description:       A block that opens a contact form panel when clicked.
 * Version:           0.1.0
 * Requires at least: 6.8
 * Requires PHP:      7.4
 * Author:            Greg Rozmarynowycz
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       contact-slide-in-trigger
 * Domain Path:       /languages
 *
 * @package CreateBlock
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
function create_block_contact_slide_in_trigger_block_init() {
	wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
}
add_action( 'init', 'create_block_contact_slide_in_trigger_block_init' );

function contact_slide_in_trigger_load_textdomain() {
	load_plugin_textdomain(
		'contact-slide-in-trigger',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'init', 'contact_slide_in_trigger_load_textdomain' );

wp_register_script(
	'edit-script',
	plugins_url( 'src/contact-slide-in-trigger/edit.js', __FILE__ ),
	array( 'wp-i18n' ),
	'0.0.1'
);

// these functions ensure translation keys from the view are recognized when generating files and that they are loaded
// into the UI
wp_register_script(
	'view-script',
	plugins_url( 'build/contact-slide-in-trigger/view/index.js', __FILE__ ),
	array( 'wp-i18n' ),
	'0.0.1'
);

add_action( 'wp_enqueue_scripts', 'contact_slide_in_trigger_load_view_textdomain', 100 );

function contact_slide_in_trigger_load_view_textdomain() {
	wp_set_script_translations(
		'view-script',
		'contact-slide-in-trigger',
		plugin_dir_path( __FILE__ ) . 'languages'
	);
}

// Contact Form REST API

add_action( 'rest_api_init', 'create_block_contact_slide_in_trigger_register_routes' );

function create_block_contact_slide_in_trigger_register_routes() {
	register_rest_route( 'contact-slide-in/v1', '/message', [
		'methods' => 'POST',
		'callback' => 'create_block_contact_slide_in_trigger_post_message',
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

function create_block_contact_slide_in_trigger_post_message(WP_REST_Request $request) {
	$recipient = ""; // TODO get this from the database
	$from = "From: " . $request->get_param('name') . " <" . $request->get_param('email') . ">";
	$headers = array( 'Content-Type: text/plain; charset=UTF-8', $from );

	$result = wp_mail( $recipient, 'Contact Form Submission', $request->get_param('message'), $headers );

	if (! $result ) {
		return new WP_Error( 'email_not_sent', 'Email not sent.', array( 'status' => 500 ));
	}

	return new WP_REST_Response( 'success' );
}
