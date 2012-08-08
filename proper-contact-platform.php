<?php 

/*
Plugin Name: Contact Form platform
Description: A better contact form processor
Version: 0.9
Author: This.Next
License: GPL2
*/

function proper_contact_form($atts, $content = null) {
	
	if (isset($_SESSION['propercfp_sent']) && $_SESSION['propercfp_sent'] === 'yes') :
		echo '<h2>'.proper_get_key('propercfp_label_submit').'</h2>';
		return;
	endif;
	
	// FormBuilder
	load_template(__DIR__ . '/inc/FormBuilder.php');
	
	$form = new ThatFormBuilder;
	
	$form->set_att('id', 'proper_contact_form_' . get_the_id());
	$form->set_att('add_nonce', get_bloginfo('admin_email'));

	// Required name field
	$form->add_input(proper_get_key('propercfp_label_name'), array(
	'required' => true
	), 'contact-name');
	
	// Required email field
	$form->add_input(proper_get_key('propercfp_label_email'), array(
		'required' => true,
		'type' => 'email'
	), 'contact-email');
	
	// Add phone field if selected on the settings page
	if(proper_get_key('propercfp_phone') === 'yes') :
			$form->add_input(proper_get_key('propercfp_label_phone'), array(), 'contact-phone');
	endif;
	
	// Add reasons drop-down
	$reasons = trim(proper_get_key('propercfp_reason'));
	if(!empty($reasons)) :
		$options = proper_get_textarea_opts($reasons);
		if (!empty($options))
			array_unshift($options, 'Select one...');
			$form->add_input(proper_get_key('propercfp_label_reason'), array(
				'type' => 'select',
				'options' => $options
			), 'contact-reasons');
	endif;
	
	// Comment field
	$form->add_input(proper_get_key('propercfp_label_comment'), array(
		'required' => true,
		'type' => 'textarea',
	));
	
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
	
	if (isset($_SESSION['cfp_contact_errors']) && !empty($_SESSION['cfp_contact_errors'])) 
		proper_display_errors($_SESSION['cfp_contact_errors']);
	
	$form->build_form();
	
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
		$_SESSION['cfp_contact_errors'][] = 'Invalid form submission!';
		
	if (! empty($_POST['honeypot'])) 
		$_SESSION['cfp_contact_errors'][] = 'No spam please!';
	
	$body = "
	*** Contact form submission on " . get_bloginfo('name') . " (" . site_url() . ")\n\n";
	
	// Sanitize and validate name
	$contact_name = sanitize_text_field(trim($_POST['contact-name']));
	if (empty($contact_name)) 
		$_SESSION['cfp_contact_errors'][] = 'Enter your name';
	else 
		$body .= "
	Name: $contact_name\n";
	
	// Sanitize and validate email
	$contact_email = sanitize_email($_POST['contact-email']);
	if (! filter_var($contact_email, FILTER_VALIDATE_EMAIL) ) 
		$_SESSION['cfp_contact_errors'][] = 'Enter a valid email';
	else 
		$body .= "
	Email: $contact_email\n";
	
	// Sanitize phone number
	$contact_phone = isset($_POST['contact-phone']) ? sanitize_text_field($_POST['contact-phone']) : '';
	if (!empty($contact_phone)) 
		$body .= "
	Phone: $contact_phone\n";
		
	// Sanitize contact reason
	$contact_reason = isset($_POST['contact-reason']) ? sanitize_text_field($_POST['contact-reason']) : '';
	if (!empty($contact_reason)) 
		$body .= "
	Reason for contact: $contact_reason\n";
	
	// Sanitize and validate comments
	$contact_comment = sanitize_text_field(trim($_POST['question-or-comment']));
	if (empty($contact_comment)) 
		$_SESSION['cfp_contact_errors'][] = 'Enter your question or comment';
	else 
		$body .= "
	Comment/question: $contact_comment\n";
	
	// Sanitize and validate IP
	$contact_ip = filter_var($_POST['contact-ip'], FILTER_VALIDATE_IP);
	if (!empty($contact_ip)) 
		$body .= "
	IP address: $contact_ip (http://whois.domaintools.com/$contact_ip, http://whatismyipaddress.com/ip/$contact_ip)\n";
	
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
		
		wp_mail($site_email, 'Contact on ' . $site_name, $body);
		
		if(proper_get_key('propercfp_result_url')) : 
			$redirect_id = proper_get_key('propercfp_result_url');
			$redirect = get_permalink($redirect_id);
			wp_redirect($redirect);
		else :
			$_SESSION['propercfp_sent'] = 'yes';
		endif;
		
		$body = trim(proper_get_key('propercfp_confirm_email'));
		if (!empty($body)) :
			$headers = "From: $site_name <$site_email>\r\n";
			wp_mail($contact_email, 'Your contact on ' . get_bloginfo('name'), $body, $headers);
		endif;
		
	endif;
	
}
add_action('template_redirect', 'cfp_process_contact');


// Help functions
include('inc/helpers.php');


// Custom plugin settings
global $propercfp_options;
$propercfp_options = get_option('propercfp_settings_array');

include('settings.php');

if (! function_exists('proper_get_key')) :
function proper_get_key($id) {
	global $propercfp_options;
	if (isset($propercfp_options[$id])) return $propercfp_options[$id];
	else return false;
}
endif;

/*
Add JS validation
:FIXIT:
*/
if (proper_get_key('propercfp_js') === 'yes') : 

	function proper_contact_scripts() {
	
		wp_register_script( 'common_scripts', get_bloginfo('template_url') . '/js/scripts.js', array('jquery'), false, true);
		wp_enqueue_script( 'common_scripts' );
		
	}    
	 
	add_action('wp_enqueue_scripts', 'proper_contact_scripts');
	
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