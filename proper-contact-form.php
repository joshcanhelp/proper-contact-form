<?php

/*
 * Plugin Name: PROPER Contact Form
 * Plugin URI: http://theproperweb.com/product/proper-contact-form/
 * Description: A better contact form processor
 * Version: 1.1.0
 * Author: PROPER Web Development
 * Author URI: http://theproperweb.com
 * Text Domain: proper-contact
 * Domain Path: /languages/
 * License: GPL v3
 */

// Make sure we don't expose any info if called directly
if ( ! function_exists( 'add_action' ) ) {
	die( 'Nothing to do...' );
}

// Important constants
define( 'PROPER_CONTACT_VERSION', '1.1.0' );
define( 'PROPER_CONTACT_URL', plugin_dir_url( __FILE__ ) );
define( 'PROPER_CONTACT_INC_PATH', plugin_dir_path( __FILE__ ) );
define( 'PROPER_CONTACT_CACHE_GROUP', 'proper_contact' );

// Required helper functions
include_once( dirname( __FILE__ ) . '/inc/helpers.php' );
include_once( dirname( __FILE__ ) . '/inc/settings.php' );
include_once( dirname( __FILE__ ) . '/inc/form-shortcode.php' );
include_once( dirname( __FILE__ ) . '/inc/widget.php' );

/**
 * WP init hook actions
 */
function proper_contact_action_init () {

	// Translation ready
	load_plugin_textdomain( 'proper-contact', FALSE, get_template_directory() . '/languages' );;
}

add_action( 'init', 'proper_contact_action_init' );





/**
 * Process the incoming contact form data, if any
 */

function cfp_process_contact () {

	// If nonce and honeypot are not set, beat it
	if ( ! isset( $_POST['honeypot'] ) ) {
		return;
	}

	// Session variable for form errors
	$_SESSION['cfp_contact_errors'] = array();

	// If nonce is not valid, beat it

	if ( 'yes' === proper_contact_get_key( 'propercfp_nonce' ) ) {
		if ( ! wp_verify_nonce( $_POST['wordpress-nonce'], 'proper_cfp_nonce' ) ) {
			$_SESSION['cfp_contact_errors']['nonce'] = __( 'Nonce failed!', 'proper-contact' );

			return;

		}
	}

	// If the honeypot caught a bear, beat it
	if ( ! empty( $_POST['honeypot'] ) ) {
		$_SESSION['cfp_contact_errors']['honeypot'] = __( 'Form submission failed!', 'proper-contact' );

		return;
	}

	// Start the body of the contact email
	$body = "
*** " . __( 'Contact form submission on', 'proper-contact' ) . " " .
	        get_bloginfo( 'name' ) . " (" . site_url() . ") *** \n\n";

	// Sanitize and validate name
	$contact_name = isset( $_POST['contact-name'] ) ?
		sanitize_text_field( trim( $_POST['contact-name'] ) ) :
		'';

	// Do we require a name?
	if ( proper_contact_get_key( 'propercfp_name_field' ) === 'req' && empty( $contact_name ) ) {

		// Name is required but missing
		$_SESSION['cfp_contact_errors']['contact-name'] = proper_contact_get_key( 'propercfp_label_err_name' );
	}
	elseif ( ! empty( $contact_name ) ) {

		// If not required and empty, leave it out
		$body .= stripslashes( proper_contact_get_key( 'propercfp_label_name' ) ) . ": $contact_name \r";
	}

	// Sanitize and validate email
	$contact_email = isset( $_POST['contact-email'] ) ?
		sanitize_email( $_POST['contact-email'] ) : '';

	// If required, is it valid?
	if (
		proper_contact_get_key( 'propercfp_email_field' ) === 'req' &&
		! filter_var( $contact_email, FILTER_VALIDATE_EMAIL )
	) {
		$_SESSION['cfp_contact_errors']['contact-email'] = proper_contact_get_key( 'propercfp_label_err_email' );
	} elseif ( ! empty( $contact_email ) ) {

		// If not required and empty, leave it out
		$body .= stripslashes( proper_contact_get_key( 'propercfp_label_email' ) )
		         . ": $contact_email \r"
		         . __( 'Google it', 'proper-contact' )
		         . ": https://www.google.com/#q=$contact_email \r";
	}

	// Sanitize phone number
	$contact_phone = isset( $_POST['contact-phone'] ) ?
		sanitize_text_field( $_POST['contact-phone'] ) :
		'';

	// Do we require a phone number?
	if ( proper_contact_get_key( 'propercfp_phone_field' ) === 'req' && empty( $contact_phone ) ) {
		$_SESSION['cfp_contact_errors']['contact-phone'] = proper_contact_get_key( 'propercfp_label_err_phone' );
		// If not required and empty, leave it out
	} elseif ( ! empty( $contact_phone ) ) {
		$body .= stripslashes( proper_contact_get_key( 'propercfp_label_phone' ) )
		         . ": $contact_phone \r"
		         . __( 'Google it', 'proper-contact' )
		         . ": https://www.google.com/#q=$contact_phone\r";
	}

	// Sanitize contact reason
	$contact_reason = isset( $_POST['contact-reasons'] ) ?
		sanitize_text_field( $_POST['contact-reasons'] ) :
		'';

	// If empty, leave it out
	if ( ! empty( $contact_reason ) ) {
		$contact_reason = stripslashes( $contact_reason );
		$body .= stripslashes( proper_contact_get_key( 'propercfp_label_reason' ) ) . ": $contact_reason \r";
	}

	$body = apply_filters( 'pcf_process_above_comment', $body );

	// Sanitize and validate comments
	$contact_comment = sanitize_text_field( trim( $_POST['question-or-comment'] ) );
	if ( empty( $contact_comment ) && proper_contact_get_key( 'propercfp_comment_field' ) === 'req' ) {
		$_SESSION['cfp_contact_errors']['question-or-comment'] =
			sanitize_text_field( proper_contact_get_key( 'propercfp_label_err_no_content' ) );
	} else {
		$body .= "\n\n" . stripslashes( proper_contact_get_key( 'propercfp_label_comment' ) )
		         . ": " . stripslashes( $contact_comment ) . " \n\n";
	}

	$body = apply_filters( 'pcf_process_below_comment', $body );

	// Check the math CAPTCHA, if present
	if ( proper_contact_get_key( 'propercfp_captcha_field' ) ) {
		$captcha_sum = isset( $_POST['math-captcha'] ) ? intval( $_POST['math-captcha'] ) : 0;
		if ( $captcha_sum != (int) $_POST['math-captcha-sum'] ) {
			$_SESSION['cfp_contact_errors']['math-captcha'] =
				sanitize_text_field( proper_contact_get_key( 'propercfp_label_err_captcha' ) );
		}
	}

	// Sanitize and validate IP
	$contact_ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP );

	// If valid and present, create a link to an IP search
	if ( ! empty( $contact_ip ) ) {
		$body .= "IP address: $contact_ip \r
IP search: http://whatismyipaddress.com/ip/$contact_ip \n\n";
	}

	// Sanitize and prepare referrer;
	if ( ! empty( $_POST['contact-referrer'] ) ) {
		$body .= "Came from: " . sanitize_text_field( $_POST['contact-referrer'] ) . " \r";
	}

	// Show the page this contact form was submitted on
	$body .= 'Sent from page: ' . get_permalink( get_the_id() );

	// Check the blacklist

	$blacklist_check = wp_blacklist_check(
		$contact_name,
		$contact_email,
		'',
		$contact_comment,
		$contact_ip,
		! empty( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : ''
	);

	if ( $blacklist_check ) {
		$_SESSION['cfp_contact_errors']['blacklist-blocked'] = 'Form submission blocked!';

		return;
	}

	// No errors? Go ahead and process the contact
	if ( empty( $_SESSION['cfp_contact_errors'] ) ) {

		// Should the entry be stored in the DB?
		if ( proper_contact_get_key( 'propercfp_store' ) === 'yes' ) {

			$insert_post_args = array(
				'post_type'    => 'proper_contact',
				'post_title'   => date( 'l, M j, Y', time() ) . ' by "' . $contact_name . '"',
				'post_content' => $body,
				'post_author'  => 1,
				'post_status'  => 'private'
			);
			$insert_post_args = apply_filters( 'pcf_process_insert_post_args', $insert_post_args );
			$new_post_id      = wp_insert_post( $insert_post_args );

			if ( isset( $contact_email ) && ! empty( $contact_email ) ) {
				add_post_meta( $new_post_id, 'Contact email', $contact_email );
			}

			do_action( 'pcf_process_after_insert_post', $new_post_id );
		}

		$site_email = sanitize_email( proper_contact_get_key( 'propercfp_email' ) );
		$site_name  = htmlspecialchars_decode( get_bloginfo( 'name' ) );

		// Notification recipients
		$site_recipients = sanitize_text_field( proper_contact_get_key( 'propercfp_email_recipients' ) );
		$site_recipients = explode( ',', $site_recipients );
		$site_recipients = array_map( 'trim', $site_recipients );
		$site_recipients = array_map( 'sanitize_email', $site_recipients );
		$site_recipients = implode( ',', $site_recipients );

		// No name? Use the submitter email address, if one is present
		if ( empty( $contact_name ) ) {
			$contact_name = ! empty( $contact_email ) ?
				$contact_email :
				__( '[None given]', 'proper-contact' );
		}

		// Need an email address for the email notification
		if ( proper_contact_get_key( 'propercfp_reply_to_admin' ) == 'yes' ) {
			$send_from      = $site_email;
			$send_from_name = $site_name;
		} else {
			$send_from      = ! empty( $contact_email ) ? $contact_email : $site_email;
			$send_from_name = $contact_name;
		}

		// Sent an email notification to the correct address
		$headers = "From: $send_from_name <$send_from>\r\nReply-To: $send_from_name <$send_from>";

		wp_mail( $site_recipients, 'Contact on ' . $site_name, $body, $headers );

		// Should a confirm email be sent?
		$confirm_body = stripslashes( trim( proper_contact_get_key( 'propercfp_confirm_email' ) ) );
		if ( ! empty( $confirm_body ) && ! empty( $contact_email ) ) {

			// Removing entities
			$confirm_body = htmlspecialchars_decode( $confirm_body );
			$confirm_body = html_entity_decode( $confirm_body );
			$confirm_body = str_replace( '&#39;', "'", $confirm_body );

			$headers = "From: $site_name <$site_email>\r\nReply-To: $site_name <$site_email>";

			wp_mail(
				$contact_email,
				proper_contact_get_key( 'propercfp_label_submit' ) . ' - ' . $site_name,
				$confirm_body,
				$headers
			);
		}

		// Should the user get redirected?
		if ( proper_contact_get_key( 'propercfp_result_url' ) ) {
			$redirect_id = intval( proper_contact_get_key( 'propercfp_result_url' ) );
			$redirect    = get_permalink( $redirect_id );
		} else {
			$redirect = add_query_arg( 'pcf', 1, $_SERVER["HTTP_REFERER"] );
		}

		$redirect = apply_filters( 'pcf_process_redirect', $redirect );
		wp_safe_redirect( $redirect );
	}
}

add_action( 'template_redirect', 'cfp_process_contact' );

/**
 * Get a settings value with a key
 *
 * @param $id
 *
 * @return string
 */
function proper_contact_get_key ( $id ) {
	$propercfp_options = get_option( 'propercfp_settings_array' );

	return isset( $propercfp_options[ $id ] ) ? $propercfp_options[ $id ] : '';
}

/**
 * If styles should be added, do that
 */


function proper_contact_styles () {

	if ( proper_contact_get_key( 'propercfp_css' ) === 'yes' ) {
		wp_register_style( 'proper_contact_styles', plugins_url( 'css/front.css', __FILE__ ) );
		wp_enqueue_style( 'proper_contact_styles' );
	}
}

add_action( 'wp_enqueue_scripts', 'proper_contact_styles' );


/**
 * Create the CPT if settings allow it
 */
function proper_contact_content_type () {

	if ( proper_contact_get_key( 'propercfp_store' ) !== 'yes' ) {
		return;
	}

	// Adding a lighter icon for 3.8 and higher
	if ( version_compare( $GLOBALS['wp_version'], '3.8', '>=' ) ) {
		$icon_file = 'person-3point8.png';
	} else {
		$icon_file = 'person.png';
	}

	$labels = array(
		'name'               => __( 'Contacts', 'proper-contact' ),
		'post type general name',
		'singular_name'      => __( 'Contact', 'proper-contact' ),
		'post type singular name',
		'add_new'            => __( 'Add Contact', 'proper-contact' ),
		'proper_contact',
		'add_new_item'       => __( 'Add New Contact', 'proper-contact' ),
		'edit_item'          => __( 'Edit Contact', 'proper-contact' ),
		'new_item'           => __( 'New Contact', 'proper-contact' ),
		'all_items'          => __( 'All Contacts', 'proper-contact' ),
		'view_item'          => __( 'View Contact', 'proper-contact' ),
		'not_found'          => __( 'No Contacts found', 'proper-contact' ),
		'not_found_in_trash' => __( 'No Contacts found in Trash', 'proper-contact' ),
		'menu_name'          => __( 'Contacts', 'proper-contact' )
	);

	$args = array(
		'labels'        => $labels,
		'public'        => FALSE,
		'show_ui'       => TRUE,
		'show_in_menu'  => TRUE,
		'hierarchical'  => FALSE,
		'menu_position' => 27,
		'menu_icon'     => PROPER_CONTACT_URL . '/images/' . $icon_file,
		'supports'      => array( 'title', 'editor', 'custom-fields' )
	);

	register_post_type( 'proper_contact', $args );
}

add_action( 'init', 'proper_contact_content_type' );

