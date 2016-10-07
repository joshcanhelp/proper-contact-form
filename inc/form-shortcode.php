<?php
/**
 * Declares the shortcode that displays the PROPER contact form
 *
 * @package WordPress
 * @subpackage PROPER Contact
 */

/**
 * Do not allow this file to be loaded directly
 */

if ( ! function_exists( 'add_action' ) ) {
	die( 'Nothing to do...' );
}


/**
 * Form shortcode for outputting the contact form
 *
 * @see https://developer.wordpress.org/reference/functions/add_shortcode/
 *
 * @param array  $atts
 * @param string $content
 *
 * @return mixed|void
 */

function proper_contact_form( $atts = array(), $content = '' ) {

	// Looking for a submitted form if not redirect
	if ( isset( $_GET['pcf'] ) && $_GET['pcf'] == 1 ) {

		return apply_filters( 'pcf_form_submitted_text', '<div class="proper_contact_thankyou_wrap">
			<h2>' . sanitize_text_field( proper_contact_get_key( 'propercfp_label_submit' ) ) . '</h2>
		</div>' );
	}

	/**
	 * PHP Form Builder from:
	 *
	 * https://github.com/joshcanhelp/php-form-builder
	 */

	if ( ! class_exists( 'PhpFormBuilder' ) ) {
		require_once( PROPER_CONTACT_INC_PATH . 'inc/PhpFormBuilder.php' );
	}

	$form = new PhpFormBuilder();

	/**
	 * Keep track of the number of forms on a page to give it some kind of ID
	 * Of course, this isn't a great way to go about giving an ID to an element since it can change
	 */

	$cache_key = 'form_id';
	$form_id = (int) wp_cache_get( $cache_key, PROPER_CONTACT_CACHE_GROUP );

	if ( empty( $form_id ) ) {
		$form_id = 1;

	} else {
		$form_id++;
	}

	wp_cache_set( $cache_key, $form_id, PROPER_CONTACT_CACHE_GROUP );

	/**
	 * Basic form attributes
	 */

	$form->set_att( 'id', 'proper_contact_form_' . $form_id );
	$form->set_att( 'class', array( 'proper_contact_form' ) );

	if ( 'yes' === proper_contact_get_key( 'propercfp_nonce' ) ) {
		$form->set_att( 'add_nonce', 'proper_cfp_nonce' );
	}

	if ( ! proper_contact_get_key( 'propercfp_html5_no_validate' ) ) {
		$form->set_att( 'novalidate', TRUE );
	}

	/**
	 * Used to adjust form attributes before adding fields
	 * Can also be used to add fields at the beginning of the form
	 *
	 * @param PhpFormBuilder $form
	 */

	do_action( 'pcf_field_form_start', $form );

	/**
	 * Name field, if turned on in settings
	 */

	if ( $propercfp_name_field = proper_contact_get_key( 'propercfp_name_field' ) ) {

		$wrap_classes = array( 'form_field_wrap', 'contact_name_wrap' );

		// If this field was submitted with invalid data

		if ( isset( $_SESSION['cfp_contact_errors']['contact-name'] ) ) {
			$wrap_classes[] = 'error';
		}

		$form->add_input(
			proper_prepare_label( 'propercfp_label_name' ),
			array(
				'required' => $propercfp_name_field === 'req' ? TRUE : FALSE,
				'wrap_class' => $wrap_classes
			),
			'contact-name'
		);
	}

	/**
	 * Add fields after the name field
	 *
	 * @since 1.1.0
	 *
	 * @param PhpFormBuilder $form
	 */

	do_action( 'pcf_field_form_after_name', $form );

	/**
	 * Email field, if turned on in settings
	 */

	if ( $propercfp_email_field = proper_contact_get_key( 'propercfp_email_field' ) ) {

		$wrap_classes = array( 'form_field_wrap', 'contact_email_wrap' );

		// If this field was submitted with invalid data

		if ( isset( $_SESSION['cfp_contact_errors']['contact-email'] ) ) {
			$wrap_classes[] = 'error';
		}

		$form->add_input(
			proper_prepare_label( 'propercfp_label_email' ),
			array(
				'required' => $propercfp_email_field === 'req' ? TRUE : FALSE,
				'type' => 'email',
				'wrap_class' => $wrap_classes
			),
			'contact-email'
		);
	}

	/**
	 * Add fields after the email field
	 *
	 * @since 1.1.0
	 *
	 * @param PhpFormBuilder $form
	 */

	do_action( 'pcf_field_form_after_email', $form );

	/**
	 * Phone field, if turned on in settings
	 */

	if ( $propercfp_phone_field = proper_contact_get_key( 'propercfp_phone_field' ) ) {

		$wrap_classes = array( 'form_field_wrap', 'contact_phone_wrap' );

		// If this field was submitted with invalid data

		if ( isset( $_SESSION['cfp_contact_errors']['contact-phone'] ) ) {
			$wrap_classes[] = 'error';
		}

		$form->add_input(
			proper_prepare_label( 'propercfp_label_phone' ),
			array(
				'required' => $propercfp_phone_field === 'req' ? TRUE : FALSE,
				'wrap_class' => $wrap_classes
			),
			'contact-phone'
		);
	}

	/**
	 * Add fields after the phone field
	 *
	 * @since 1.1.0
	 *
	 * @param PhpFormBuilder $form
	 */

	do_action( 'pcf_field_form_after_phone', $form );

	/**
	 * Reasons for contacting drop-down, if any are added on the settings page
	 */

	if ( $contact_reasons = proper_get_textarea_opts( trim( proper_contact_get_key( 'propercfp_reason' ) ) ) ) {

		$contact_reasons = array_map( 'trim', $contact_reasons );
		$contact_reasons = array_map( 'sanitize_text_field', $contact_reasons );
		array_unshift( $contact_reasons, __( 'Select one...', 'proper-contact' ) );

		$form->add_input(
			proper_prepare_label( 'propercfp_label_reason' ),
			array(
				'type' => 'select',
				'wrap_class' => array(
					'form_field_wrap',
					'contact_reasons_wrap'
				),
				'options' => $contact_reasons
			),
			'contact-reasons'
		);
	}

	/**
	 * Add fields before the comment field
	 *
	 * @param PhpFormBuilder $form
	 */

	do_action( 'pcf_field_above_comment', $form );

	/**
	 * Comment form
	 */

	if ( $propercfp_comment_field = proper_contact_get_key( 'propercfp_comment_field' ) ) {

		$wrap_classes = array( 'form_field_wrap', 'question_or_comment_wrap' );

		// If this field was submitted with invalid data

		if ( isset( $_SESSION['cfp_contact_errors']['question-or-comment'] ) ) {
			$wrap_classes[] = 'error';
		}

		$form->add_input(
			proper_prepare_label( 'propercfp_label_comment' ),
			array(
				'required' => $propercfp_comment_field === 'req' ? TRUE : FALSE,
				'type' => 'textarea',
				'wrap_class' => $wrap_classes
			),
			'question-or-comment'
		);
	}

	/**
	 * Add fields after the comment field
	 *
	 * @param PhpFormBuilder $form
	 */

	do_action( 'pcf_field_below_comment', $form );

	/**
	 * Math CAPTCHA, if turned on in settings
	 */

	if ( proper_contact_get_key( 'propercfp_captcha_field' ) ) {

		$wrap_classes = array( 'form_field_wrap', 'math_captcha_wrap' );

		// If this field was submitted with invalid data

		if ( isset( $_SESSION['cfp_contact_errors']['math-captcha'] ) ) {
			$wrap_classes[] = 'error';
		}

		$num_1 = mt_rand( 1, 10 );
		$num_2 = mt_rand( 1, 10 );
		$sum   = $num_1 + $num_2;

		// Build the input with the correct label, options, and name

		$form->add_input(
			proper_prepare_label( 'propercfp_label_math' ) . " $num_1 + $num_2",
			array(
				'type' => 'number',
				'required' => TRUE,
				'wrap_class' => $wrap_classes,
				'request_populate' => FALSE
			),
			'math-captcha'
		);

		$form->add_input(
			'Math CAPTCHA sum',
			array(
				'type' => 'hidden',
				'value' => $sum,
				'request_populate' => FALSE
			),
			'math-captcha-sum'
		);
	}

	/**
	 * Add fields before the submit button
	 *
	 * @param PhpFormBuilder $form
	 */

	do_action( 'pcf_field_above_submit', $form );

	/**
	 * Submit button
	 */

	$submit_btn_text = proper_prepare_label( 'propercfp_label_submit_btn' );

	$form->add_input(
		$submit_btn_text,
		array(
			'type' => 'submit',
			'wrap_class' => array(
				'form_field_wrap',
				'submit_wrap'
			),
			'value' => esc_attr( $submit_btn_text )
		),
		'submit'
	);

	// Referring site or page, if any
	if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
		$form->add_input(
			'Contact Referrer',
			array(
				'type' => 'hidden',
				'value' => $_SERVER['HTTP_REFERER']
			)
		);
	}

	// Referring page, if sent via URL query
	if ( ! empty( $_REQUEST['src'] ) || ! empty( $_REQUEST['ref'] ) ) {
		$form->add_input(
			'Referring page', array(
				'type' => 'hidden',
				'value' => ! empty( $_REQUEST['src'] ) ? $_REQUEST['src'] : $_REQUEST['ref']
			)
		);
	}

	// Are there any submission errors?
	$errors = '';
	if ( ! empty( $_SESSION['cfp_contact_errors'] ) ) {
		$errors = proper_display_errors( $_SESSION['cfp_contact_errors'] );
		unset( $_SESSION['cfp_contact_errors'] );
	}

	// Display that beautiful form!
	return apply_filters( 'pcf_form_final_html', '<div class="proper_contact_form_wrap">
		' . $errors . '
		' . $form->build_form( FALSE ) . '
		</div>' );

}

add_shortcode( 'proper_contact_form', 'proper_contact_form' );