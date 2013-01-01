<?php 

/*
Plugin Name: PROPER Contact Form
Plugin URI: http://theproperweb.com/shipped/wp/proper-contact-form
Description: A better contact form processor
Version: 0.9.2
Author: PROPER Development
Author URI: http://theproperweb.com
License: GPL2
*/

// Help functions
require_once(WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)) . '/inc/helpers.php');

function proper_contact_form($atts, $content = null) {
	
	if (isset($_SESSION['propercfp_sent']) && $_SESSION['propercfp_sent'] === 'yes') :
		unset($_SESSION['propercfp_sent']);
		return '
		<div class="proper_contact_form_wrap">
			<h2>'.proper_get_key('propercfp_label_submit').'</h2>
		</div>';
	endif;
	
	// FormBuilder
	require_once(plugin_dir_path( __FILE__ ) . '/inc/FormBuilder.php');
	
	$form = new ThatFormBuilder;
	
	$form->set_att('id', 'proper_contact_form_' . get_the_id());
	$form->set_att('class', array('proper_contact_form'));
	$form->set_att('add_nonce', get_bloginfo('admin_email'));

	// Add name field if selected on the settings page
	if( proper_get_key('propercfp_name_field') ) :
		$required = proper_get_key('propercfp_name_field') === 'req' ? true : false;
		$form->add_input(stripslashes(proper_get_key('propercfp_label_name')), array(
			'required' => $required,
			'wrap_class' => isset($_SESSION['cfp_contact_errors']['contact-name']) ? array('form_field_wrap', 'error') : array('form_field_wrap')
		), 'contact-name');
	endif;
	
	// Add email field if selected on the settings page
	if( proper_get_key('propercfp_email_field') ) :
		$required = proper_get_key('propercfp_email_field') === 'req' ? true : false;
		$form->add_input(stripslashes(proper_get_key('propercfp_label_email')), array(
			'required' => $required,
			'type' => 'email',
			'wrap_class' => isset($_SESSION['cfp_contact_errors']['contact-email']) ? array('form_field_wrap', 'error') : array('form_field_wrap')
		), 'contact-email');
	endif;
	
	// Add phone field if selected on the settings page
	if( proper_get_key('propercfp_phone_field') ) :
		$required = proper_get_key('propercfp_phone_field') === 'req' ? true : false;
		$form->add_input(stripslashes(proper_get_key('propercfp_label_phone')), array(
			'required' => $required
		), 'contact-phone');
	endif;
	
	// Add reasons drop-down
	$reasons = trim(proper_get_key('propercfp_reason'));
	if(!empty($reasons)) :
		$options = proper_get_textarea_opts($reasons);
		if (!empty($options))
			array_unshift($options, 'Select one...');
			$form->add_input(stripslashes(proper_get_key('propercfp_label_reason')), array(
				'type' => 'select',
				'options' => $options
			), 'contact-reasons');
	endif;
	
	// Comment field
	$form->add_input(stripslashes(proper_get_key('propercfp_label_comment')), array(
		'required' => true,
		'type' => 'textarea',
		'wrap_class' => isset($_SESSION['cfp_contact_errors']['question-or-comment']) ? array('form_field_wrap', 'error') : array('form_field_wrap')
	), 'question-or-comment');
	
	// IP Address
	$form->add_input('Contact IP', array(
		'type' => 'hidden',
		'value' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''
	));
	
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
	' . $errors . $form->build_form(false) . '
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
	if (! wp_verify_nonce($_POST['wordpress-nonce'], get_bloginfo('admin_email')))
		$_SESSION['cfp_contact_errors']['nonce'] = 'Invalid form submission!';
		
	if (! empty($_POST['honeypot'])) 
		$_SESSION['cfp_contact_errors']['honeypot'] = 'No spam please!';
	
	$body = "
*** Contact form submission on " . get_bloginfo('name') . " (" . site_url() . ")\n\n";
	
	// Sanitize and validate name
	$contact_name = isset($_POST['contact-name']) ? sanitize_text_field(trim($_POST['contact-name'])) : '';
	if (proper_get_key('propercfp_email_field') === 'req' && empty($contact_name)) 
		$_SESSION['cfp_contact_errors']['contact-name'] = 'Enter your name';
	else 
		$body .= "
Name: $contact_name\n";
	
	// Sanitize and validate email
	$contact_email = isset($_POST['contact-email']) ? sanitize_email($_POST['contact-email']) : '';
	if (proper_get_key('propercfp_email_field') === 'req' && ! filter_var($contact_email, FILTER_VALIDATE_EMAIL) ) 
		$_SESSION['cfp_contact_errors']['contact-email'] = 'Enter a valid email';
	elseif (!empty($contact_email)) 
		$body .= "
Email: $contact_email\r
Email search: https://www.google.com/#q=$contact_email\n";
	
	// Sanitize phone number
	$contact_phone = isset($_POST['contact-phone']) ? sanitize_text_field($_POST['contact-phone']) : '';
	if (proper_get_key('propercfp_phone_field') === 'req' && empty($contact_phone) ) 
		$_SESSION['cfp_contact_errors']['contact-phone'] = 'Please enter a phone number';
	elseif (!empty($contact_phone)) 
		$body .= "
Phone: $contact_phone\n";
		
	// Sanitize contact reason
	$contact_reason = isset($_POST['contact-reasons']) ? strip_tags($_POST['contact-reasons']) : '';
	if (!empty($contact_reason)) 
		$body .= "
Reason for contacting: $contact_reason\n";
	
	// Sanitize and validate comments
	$contact_comment = sanitize_text_field(trim($_POST['question-or-comment']));
	if (empty($contact_comment)) 
		$_SESSION['cfp_contact_errors']['question-or-comment'] = 'Enter your question or comment';
	else 
		$body .= "
Comment/question: " . stripslashes($contact_comment) . "\n";
	
	// Sanitize and validate IP
	$contact_ip = filter_var($_POST['contact-ip'], FILTER_VALIDATE_IP);
	if (!empty($contact_ip)) 
		$body .= "
IP address: $contact_ip \r
IP search: http://whatismyipaddress.com/ip/$contact_ip\n";
	
	// Sanitize and prepare referrer
	$contact_referrer = sanitize_text_field($_POST['contact-referrer']);
	if (!empty($contact_referrer)) 
		$body .= "
Came from: $contact_referrer\n";
	
	$body .= '
Sent from page: ' . get_permalink(get_the_id());
	
	if (empty($_SESSION['cfp_contact_errors'])) :

		$site_email = proper_get_key('propercfp_email');
		$site_name = get_bloginfo('name');
		
		$headers[] = "From: $contact_name <$contact_email>";
		$headers[] = "Reply-To: $contact_email";
		$headers[] = 'X-Mailer: PHP/' . phpversion();
		
		wp_mail($site_email, 'Contact on ' . $site_name, $body, $headers);
		
		// Should a confirm email be sent? 
		$confirm_body = stripslashes(trim(proper_get_key('propercfp_confirm_email')));
		if (!empty($confirm_body)) :
			$headers[] = "From: $site_name <$site_email>";
			$headers[] = "Reply-To: $site_email";
			$headers[] = 'X-Mailer: PHP/' . phpversion();
			wp_mail($contact_email, 'Your contact on ' . get_bloginfo('name'), $confirm_body, $headers);
		endif;
		
		// Should the entry be stored in the DB?
		if (proper_get_key('propercfp_store') === 'yes') :
			$new_post_id = wp_insert_post(array(
				'post_type' => 'proper_contact',
				'post_title' => date('l, M j, Y', time()) . ' by "' . $contact_name . '"',
				'post_content' => $body,
				'post_author' => 1,
				'post_status' => 'private'
			));
			if (isset($contact_email) && !empty($contact_email)) add_post_meta($new_post_id, 'Contact email', $contact_email);
		endif;
		
		// Should the user get redirected?
		if( proper_get_key('propercfp_result_url')) : 
			$redirect_id = proper_get_key('propercfp_result_url');
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

if (! function_exists('proper_get_key')) :
function proper_get_key($id) {
	global $propercfp_options;
	if (isset($propercfp_options[$id])) return $propercfp_options[$id];
	else return '';
}
endif;


/*
Add CSS
*/
function proper_contact_styles() {
	wp_register_style( 'proper_contact_styles', plugins_url('css/front.css', __FILE__));
	wp_enqueue_style( 'proper_contact_styles' );
} 
	
if (proper_get_key('propercfp_css') === 'yes') 
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
    'public' => false,
    'publicly_queryable' => false,
    'show_ui' => true, 
    'show_in_menu' => true,
    'has_archive' => 'string',
    'hierarchical' => false,
		'menu_position' => 27,
		'menu_icon' => plugin_dir_url(__FILE__) . '/images/person.png',
    'supports' => array( 'title', 'editor', 'custom-fields')
  ); 
  register_post_type('proper_contact',$args);
}

if (proper_get_key('propercfp_store') === 'yes') 
	add_action( 'init', 'proper_contact_content_type' );
	
	