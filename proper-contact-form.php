<?php

/*
Plugin Name: PROPER Contact Form
Plugin URI: http://theproperweb.com/shipped/wp/proper-contact-form
Description: A better contact form processor
Version: 0.9.5.1
Author: PROPER Development
Author URI: http://theproperweb.com
License: GPL2
*/

// Help functions
require_once(WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)) . '/inc/helpers.php');

function proper_contact_form($atts, $content = NULL) {

	if (
		isset($_SESSION['propercfp_sent']) &&
		$_SESSION['propercfp_sent'] === 'yes'
	) :
		unset($_SESSION['propercfp_sent']);
		return '
		<div class="proper_contact_form_wrap">
			<h2>' . proper_contact_get_key('propercfp_label_submit') . '</h2>
		</div>';
	endif;

	// FormBuilder
	require_once(plugin_dir_path( __FILE__ ) . '/inc/FormBuilder.php');

	$form = new ThatFormBuilder;

	$form->set_att('id', 'proper_contact_form_' . get_the_id());
	$form->set_att('class', array('proper_contact_form'));
	$form->set_att('add_nonce', get_bloginfo('admin_email'));
	if (proper_contact_get_key('propercfp_html5_no_validate') === '') {
		$form->set_att('novalidate', TRUE);
	}


	// Add name field if selected on the settings page
	if( proper_contact_get_key('propercfp_name_field') ) :
		$required = proper_contact_get_key('propercfp_name_field') === 'req' ? TRUE : FALSE;
		$form->add_input(stripslashes(proper_contact_get_key('propercfp_label_name')), array(
			'required' => $required,
			'wrap_class' => isset($_SESSION['cfp_contact_errors']['contact-name'])
				? array('form_field_wrap', 'error')
				: array('form_field_wrap')
		), 'contact-name');
	endif;

	// Add email field if selected on the settings page
	if( proper_contact_get_key('propercfp_email_field') ) :
		$required = proper_contact_get_key('propercfp_email_field') === 'req' ? TRUE : FALSE;
		$form->add_input(stripslashes(proper_contact_get_key('propercfp_label_email')), array(
			'required' => $required,
			'type' => 'email',
			'wrap_class' => isset($_SESSION['cfp_contact_errors']['contact-email'])
				? array('form_field_wrap', 'error')
				: array('form_field_wrap')
		), 'contact-email');
	endif;

	// Add phone field if selected on the settings page
	if( proper_contact_get_key('propercfp_phone_field') ) :
		$required = proper_contact_get_key('propercfp_phone_field') === 'req' ? TRUE : FALSE;
		$form->add_input(stripslashes(proper_contact_get_key('propercfp_label_phone')), array(
			'required' => $required,
			'wrap_class' => isset( $_SESSION['cfp_contact_errors']['contact-phone'] )
				? array ( 'form_field_wrap', 'error' )
				: array ( 'form_field_wrap' )
		), 'contact-phone');
	endif;

	// Add reasons drop-down
	$reasons = trim(proper_contact_get_key('propercfp_reason'));
	$options = proper_get_textarea_opts( $reasons );
	if (!empty($options)) {
		array_unshift($options, 'Select one...');
		$form->add_input(stripslashes(proper_contact_get_key('propercfp_label_reason')), array(
			'type' => 'select',
			'options' => $options
		), 'contact-reasons');
	}


	// Comment field
	$form->add_input(stripslashes(proper_contact_get_key('propercfp_label_comment')), array(
		'required' => TRUE,
		'type' => 'textarea',
		'wrap_class' => isset($_SESSION['cfp_contact_errors']['question-or-comment']) ?
			array('form_field_wrap', 'error') :
			array('form_field_wrap')
	), 'question-or-comment');

	// Submit button
	$form->add_input( proper_contact_get_key( 'propercfp_label_submit_btn' ), array(
		'type' => 'submit'
	), 'submit');

	// Referring site
	$form->add_input('Contact Referrer', array(
		'type' => 'hidden',
		'value' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''
	));

	// Referring page
	if (isset($_REQUEST['src']) || isset($_REQUEST['ref'])) :
		$form->add_input('Referring page', array(
			'type' => 'hidden',
			'value' => isset($_REQUEST['src']) ? $_REQUEST['src'] : $_REQUEST['ref']
		));
	endif;

	$errors = '';
	if (isset($_SESSION['cfp_contact_errors']) && !empty($_SESSION['cfp_contact_errors'])) :
		$errors = proper_display_errors($_SESSION['cfp_contact_errors']);
		unset($_SESSION['cfp_contact_errors']);
	endif;

	return '
	<div class="proper_contact_form_wrap">
	' . $errors . $form->build_form(FALSE) . '
	</div>';

}
add_shortcode( 'proper_contact_form', 'proper_contact_form' );

function cfp_process_contact() {

	// If POST, nonce and honeypot are not set, escape
	if (empty($_POST)) return;
	if (! isset($_POST['wordpress-nonce'])) return;
	if (! isset($_POST['honeypot'])) return;

	// Session variable for form errors
	$_SESSION['cfp_contact_errors'] = array();

	// If nonce is not passed or honeypot is not empty, escape
	if (! wp_verify_nonce($_POST['wordpress-nonce'], get_bloginfo('admin_email'))) {
		$_SESSION['cfp_contact_errors']['nonce'] = 'Nonce failed!';
	}

	if (! empty($_POST['honeypot'])) {
		$_SESSION['cfp_contact_errors']['honeypot'] = 'Form submission failed!';
	}

	$body = "
*** Contact form submission on " . get_bloginfo('name') . " (" . site_url() . ") *** \n\n";

	// Sanitize and validate name
	$contact_name = isset($_POST['contact-name']) ? sanitize_text_field(trim($_POST['contact-name'])) : '';
	if (proper_contact_get_key('propercfp_name_field') === 'req' && empty($contact_name)) {
		$_SESSION['cfp_contact_errors']['contact-name'] = proper_contact_get_key('propercfp_label_err_name');
	} elseif ( !empty( $contact_name ) ) {
		$body .= stripslashes( proper_contact_get_key( 'propercfp_label_name' ) ) . ": $contact_name \r";
	}

	// Sanitize and validate email
	$contact_email = isset($_POST['contact-email']) ? sanitize_email($_POST['contact-email']) : '';
	if (proper_contact_get_key('propercfp_email_field') === 'req' && ! filter_var($contact_email, FILTER_VALIDATE_EMAIL) ) {
		$_SESSION['cfp_contact_errors']['contact-email'] = proper_contact_get_key('propercfp_label_err_email');
	} elseif (!empty($contact_email)) {
		$body .= stripslashes( proper_contact_get_key( 'propercfp_label_email' ) ) . ": $contact_email \r
Google: https://www.google.com/#q=$contact_email \r";
	}

	// Sanitize phone number
	$contact_phone = isset($_POST['contact-phone']) ? sanitize_text_field($_POST['contact-phone']) : '';
	if (proper_contact_get_key('propercfp_phone_field') === 'req' && empty($contact_phone) ) {
		$_SESSION['cfp_contact_errors']['contact-phone'] = proper_contact_get_key('propercfp_label_err_phone');
	} elseif (!empty($contact_phone)) {
		$body .= stripslashes( proper_contact_get_key( 'propercfp_label_phone' ) ) . ": $contact_phone \r";
	}

	// Sanitize contact reason
	$contact_reason = isset($_POST['contact-reasons']) ? sanitize_text_field($_POST['contact-reasons']) : '';
	if (!empty($contact_reason)) {
		$body .= stripslashes( proper_contact_get_key( 'propercfp_label_reason' ) ) . ": $contact_reason \r";
	}

	// Sanitize and validate comments
	$contact_comment = filter_var((trim($_POST['question-or-comment'])), FILTER_SANITIZE_STRING);
	if (empty($contact_comment)) {
		$_SESSION['cfp_contact_errors']['question-or-comment'] = proper_contact_get_key('propercfp_label_err_no_content');
	} else {
		$body .= "\n\n" . stripslashes( proper_contact_get_key( 'propercfp_label_comment' ) ) . ": " . stripslashes($contact_comment) . " \n\n";
	}

	// Sanitize and validate IP
	$contact_ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
	if (!empty($contact_ip)) {
		$body .= "IP address: $contact_ip \r
IP search: http://whatismyipaddress.com/ip/$contact_ip \n\n";
	}

	// Sanitize and prepare referrer
	$contact_referrer = sanitize_text_field($_POST['contact-referrer']);
	if (!empty($contact_referrer)) {
		$body .= "Came from: $contact_referrer \r";
	}

	$body .= 'Sent from page: ' . get_permalink(get_the_id());

	if (empty($_SESSION['cfp_contact_errors'])) :

		$site_email = proper_contact_get_key('propercfp_email');
		$site_name = get_bloginfo('name');

		if ( empty( $contact_name ) ) {
			$contact_name = !empty( $contact_email ) ? $contact_email : '[None given]';
		}

		if ( empty( $contact_email ) ) {
			$contact_email = $site_email;
		}

		$headers = array();
		$headers[] = "From: $contact_name <$contact_email>";
		$headers[] = "Reply-To: $contact_email";

		wp_mail($site_email, 'Contact on ' . $site_name, $body, $headers);

		// Should a confirm email be sent?
		$confirm_body = stripslashes(trim(proper_contact_get_key('propercfp_confirm_email')));
		if (!empty($confirm_body) && !empty( $contact_email ) ) :
			$headers = array ();
			$headers[] = "From: $site_name <$site_email>";
			$headers[] = "Reply-To: $site_email";
			wp_mail($contact_email, proper_contact_get_key( 'propercfp_label_submit' ) . ' - ' . get_bloginfo('name'), $confirm_body, $headers);
		endif;

		// Should the entry be stored in the DB?
		if (proper_contact_get_key('propercfp_store') === 'yes') :
			$new_post_id = wp_insert_post(array(
				'post_type' => 'proper_contact',
				'post_title' => date('l, M j, Y', time()) . ( empty( $contact_name ) ? '' : ' by "' . $contact_name . '"'),
				'post_content' => $body,
				'post_author' => 1,
				'post_status' => 'private'
			));
			if (isset($contact_email) && !empty($contact_email)) {
				add_post_meta($new_post_id, 'Contact email', $contact_email);
			}
		endif;

		// Should the user get redirected?
		if( proper_contact_get_key('propercfp_result_url')) :
			$redirect_id = proper_contact_get_key('propercfp_result_url');
			$redirect = get_permalink($redirect_id);
			wp_redirect($redirect);
		else :
			$_SESSION['propercfp_sent'] = 'yes';
		endif;

	endif;

}
add_action('template_redirect', 'cfp_process_contact');


// Custom plugin settings
global $propercfp_options;
$propercfp_options = get_option('propercfp_settings_array');

include('settings.php');

function proper_contact_get_key($id) {
	global $propercfp_options, $plugin_options;
	return isset( $propercfp_options[$id] ) ? $propercfp_options[$id] : $plugin_options[$id][4];
}


/*
Add CSS
*/
function proper_contact_styles() {
	wp_register_style( 'proper_contact_styles', plugins_url('css/front.css', __FILE__));
	wp_enqueue_style( 'proper_contact_styles' );
}

if (proper_contact_get_key('propercfp_css') === 'yes')
	add_action('wp_enqueue_scripts', 'proper_contact_styles');


/*
Store submissions in the DB
*/

function proper_contact_content_type() {
  $labels = array(
    'name' => _x('Contacts', 'post type general name'),
    'singular_name' => _x('Contact', 'post type singular name'),
    'add_new' => _x('Add Contact', 'proper_contact'),
    'add_new_item' => __('Add New Contact'),
    'edit_item' => __('Edit Contact'),
    'new_item' => __('New Contact'),
    'all_items' => __('All Contacts'),
    'view_item' => __('View Contact'),
    'not_found' =>  __('No Contacts found'),
    'not_found_in_trash' => __('No Contacts found in Trash'),
    'parent_item_colon' => '',
    'menu_name' => 'Contacts'

  );
  $args = array(
    'labels' => $labels,
    'public' => FALSE,
    'publicly_queryable' => FALSE,
    'show_ui' => TRUE,
    'show_in_menu' => TRUE,
    'has_archive' => 'string',
    'hierarchical' => FALSE,
		'menu_position' => 27,
		'menu_icon' => plugin_dir_url(__FILE__) . '/images/person.png',
    'supports' => array( 'title', 'editor', 'custom-fields')
  );
  register_post_type('proper_contact',$args);
}

if (proper_contact_get_key('propercfp_store') === 'yes')
	add_action( 'init', 'proper_contact_content_type' );

