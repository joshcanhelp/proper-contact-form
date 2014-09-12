<?php

// Theme settings/options page	

/*
0 = name
1 = id
2 = desc
3 = type
4 = default
5 = options
*/

function proper_contact_plugin_options() {
	return array(
		'head1'                          => array(
			'Fields to show',
			'',
			'title',
			'',
		),
		'propercfp_name_field'           => array(
			'Name',
			'Should a name field be displayed?',
			'select',
			'yes',
			array(
				''    => 'None',
				'yes' => 'Yes but not required',
				'req' => 'Required'
			),
		),
		'propercfp_email_field'          => array(
			'Email address',
			'Should an email address field be displayed?',
			'select',
			'yes',
			array(
				''    => 'None',
				'yes' => 'Yes but not required',
				'req' => 'Required'
			),
		),
		'propercfp_phone_field'          => array(
			'Phone number',
			'Should a phone number field be displayed?',
			'select',
			'yes',
			array(
				''    => 'None',
				'yes' => 'Yes but not required',
				'req' => 'Required'
			),
		),
		'propercfp_reason'               => array(
			'"Reason for contacting" options',
			'You can have people choose the reason for their contact from a drop-down list. If you would like this option to appear, enter the different reasons into the text box below, each one on its own line.',
			'textarea',
			'',
		),
		'propercfp_captcha_field'        => array(
			'Add a math CAPTCHA',
			'Checking this box will add a math CAPTCHA to the form to discourage spam',
			'checkbox',
			'',
		),
		'head2'                          => array(
			'Form processing options',
			'',
			'title',
			'',
		),
		'propercfp_email'                => array(
			'Contact notification sender email',
			'Email to use for the sender of the contact form emails both to the recipients below and the contact form submitter (if this is activated below). The domain for this email address should match your site\'s domain.',
			'email',
			get_bloginfo( 'admin_email' )
		),
		'propercfp_reply_to_admin'       => array(
			'Use the email address above as notification sender',
			'When this is on, the notification emails sent from your site will come from the email address above. When this is off, the emails will come from the form submitter, making it easy to reply. If you are not receiving notifications from the site, then turn this option off as your email server might be marking them as spam.',
			'checkbox',
			'',
		),
		'propercfp_email_recipients' => array(
			'Contact submission recipients',
			'Email address(es) to receive contact submission notifications. You can separate multiple emails with a comma.',
			'text',
			proper_contact_get_key( 'propercfp_email' ) ?
				proper_contact_get_key( 'propercfp_email' ) :
				get_bloginfo( 'admin_email' )
		),
		'propercfp_result_url'           => array(
			'"Thank You" URL',
			'Select the post-submit page for all forms submitted',
			'select',
			'',
			proper_get_content_array()
		),
		'propercfp_css'                  => array(
			'Add styles to the site',
			'Checking this box will add styles to the form. By default, this is off so you can add your own styles.',
			'checkbox',
			'',
		),
		'propercfp_store'                => array(
			'Store submissions in the database',
			'Should the submissions be stored in the admin area? If chosen, contact form submissions will be saved in Contacts on the left (appears after this option is activated).',
			'checkbox',
			'',
		),
		'propercfp_blacklist'            => array(
			'Use the comments blacklist to restrict submissions',
			'Should form submission IP and email addresses be compared against the Comment Blacklist, found in <strong>wp-admin > Settings > Discussion > Comment Blacklist?</strong>',
			'checkbox',
			'yes',
		),
		'propercfp_confirm_email'        => array(
			'Send email confirmation to form submitter',
			'Adding text here will send an email to the form submitter. The email uses the "Text to show when form is submitted..." field below as the subject line. Plain text only here, no HTML.',
			'textarea',
			'',
		),
		'head3'                          => array(
			'Label Fields',
			'',
			'title',
			'',
		),
		'propercfp_label_name'           => array(
			'Name field label',
			'',
			'text',
			'Your full name'
		),
		'propercfp_label_email'          => array(
			'Email field label',
			'',
			'text',
			'Your email address'
		),
		'propercfp_label_phone'          => array(
			'Phone field label',
			'',
			'text',
			'Your phone number'
		),
		'propercfp_label_reason'         => array(
			'Reason for contacting label',
			'',
			'text',
			'Reason for contacting'
		),
		'propercfp_label_comment'        => array(
			'Comment field label',
			'',
			'text',
			'Question or comment'
		),
		'propercfp_label_math'           => array(
			'Math CAPTCHA label',
			'',
			'text',
			'Solve this equation: '
		),
		'propercfp_label_submit_btn'     => array(
			'Submit button text',
			'',
			'text',
			'Submit'
		),
		'propercfp_label_submit'         => array(
			'Successful form submission text',
			'This text is used on the page if no "Thank You" URL is set above. This is also used as the confirmation email title, if one is set to send out.',
			'text',
			'Thank you for your contact!'
		),
		'head4'                          => array(
			'HTML5 validation',
			'',
			'',
			'title',
			'',
		),
		'propercfp_html5_no_validate'    => array(
			'Use HTML5 validation',
			'',
			'checkbox',
			'yes'
		),
		'head5'                          => array(
			'Error Messages',
			'',
			'title',
			'',
		),
		'propercfp_label_err_name'       => array(
			'Name required and missing',
			'',
			'text',
			'Enter your name'
		),
		'propercfp_label_err_email'      => array(
			'E-mail required and missing',
			'',
			'text',
			'Enter a valid email'
		),
		'propercfp_label_err_phone'      => array(
			'EPhone required and missing',
			'',
			'text',
			'Please enter a phone number'
		),
		'propercfp_label_err_no_content' => array(
			'Question/comment is missing',
			'',
			'text',
			'Enter your question or comment'
		),
		'propercfp_label_err_captcha'    => array(
			'Incorrect math CAPTCHA',
			'',
			'text',
			'Check your math ...'
		),
	);
}

function cfp_add_admin() {

	global $current_user;
	get_currentuserinfo();

	$propercfp_options = get_option( 'propercfp_settings_array' );
	$plugin_options    = proper_contact_plugin_options();

	if (
		// On the right page
		array_key_exists( 'page', $_GET ) &&
		$_GET['page'] === 'pcfp-admin' &&
		// We're saving options
		array_key_exists( 'action', $_REQUEST ) &&
		$_REQUEST['action'] == 'save' &&
		// This action is authorized
		current_user_can( 'manage_options' ) &&
		wp_verify_nonce( $_POST['proper_nonce'], $current_user->user_email )
	) {

		foreach ( $plugin_options as $key => $opt ) :
			if ( isset( $_REQUEST[$key] ) ) {
				$opt_data                = filter_var( $_REQUEST[$key], FILTER_SANITIZE_STRING );
				$propercfp_options[$key] = $opt_data;
			}
			else {
				$propercfp_options[$key] = '';
			}
		endforeach;

		update_option( 'propercfp_settings_array', $propercfp_options );
		header( "Location: admin.php?page=pcfp-admin&saved=true" );
		die;
	}

	add_submenu_page(
		'options-general.php',
		__( 'PROPER Contact settings', 'proper-contact' ),
		__( 'PROPER Contact', 'proper-contact' ),
		'manage_options',
		'pcfp-admin',
		'proper_contact_admin' );

}

add_action( 'admin_menu', 'cfp_add_admin' );


function proper_contact_admin() {

	global $current_user;
	get_currentuserinfo();

	$propercfp_options = get_option( 'propercfp_settings_array' );
	$plugin_options    = proper_contact_plugin_options();
	?>

	<div class="wrap" id="proper-contact-options">

	<h2><?php
		_e( 'PROPER Contact Form', 'proper-contact' );
		echo ' ';
		_e( 'Settings', 'proper-contact' );
		?></h2>

	<div class="postbox" style="margin-top: 20px; padding: 0 20px">

		<p>Simply configure the form below, save your changes, then add
			<code>[proper_contact_form]</code> to any page or post. You can also add a
			<a href="<?php echo admin_url( 'widgets.php' ); ?>">widget</a>.<br>
			If you're adding this to a theme file, add
			<code>&lt;?php echo do_shortcode( '[proper_contact_form]' ) ?&gt;</code>
		</p>

	</div>

	<?php if ( ! empty( $_REQUEST['saved'] ) ) : ?>
		<div id="setting-error-settings_updated" class="updated settings-error">
			<p><strong>
					<?php _e( 'PROPER Contact Form', 'proper-contact' ) ?>
					<?php _e( 'settings saved.', 'proper-contact' ) ?></strong></p>
		</div>
	<?php endif ?>

	<div class="proper_contact_promo_sidebar">
		<p>I hope you are using and loving the PROPER Contact Form! This plugin was brought to you by:</p>
		<p>
			<a href="http://theproperweb.com/?ref=pcf-settings" target="_blank">
				<img src="<?php echo PROPER_CONTACT_URL . 'images/proper-logo.png' ?>" class="aligncenter">
			</a>
		</p>
		<p>If you use and enjoy the plugin, you can show support by
			<a href="http://theproperweb.com/product/proper-contact-form/" target="_blank">donating</a> or
			<a href="https://wordpress.org/support/view/plugin-reviews/proper-contact-form?filter=5#postform" target="_blank">giving it a positive review</a> on WordPress.org. If you're having any trouble,
			<a href="https://wordpress.org/support/plugin/proper-contact-form" target="_blank">post a support request here</a>.
		</p>
		<hr>
		<p>Like what you see and want more?</p>

		<p><strong>Premium Themes and Plugins:</strong></p>
		<ul>
			<li><a href="http://wpdrudge.com/?ref=pcf-settings" target="_blank">WP-Drudge curation theme</a></li>
			<li><a href="http://www.wpwritersblock.com/?ref=pcf-settings" target="_blank">WP Writer's Block writer's theme</a></li>
			<li><a href="http://theproperweb.com/product/google-news-wordpress/?ref=pcf-settings" target="_blank">Google News for WordPress</a></li>
		</ul>
		<p><strong>Free Plugins:</strong></p>
		<ul>
			<li><a href="https://wordpress.org/plugins/proper-widgets/" target="_blank">PROPER Widgets</a></li>
			<li><a href="https://wordpress.org/plugins/proper-shortcodes/" target="_blank">PROPER Shortcodes</a></li>
		</ul>
		<hr>
		<p>
			By the way, I'm Josh, the developer of this plugin. Nice to meet you!</p>
		<p>
			<a href="https://twitter.com/joshcanhelp" class="twitter-follow-button" data-show-count="false" data-dnt="true">Follow @joshcanhelp</a>
			<script>!function (d, s, id) {
					var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location) ? 'http' : 'https';
					if (!d.getElementById(id)) {
						js = d.createElement(s);
						js.id = id;
						js.src = p + '://platform.twitter.com/widgets.js';
						fjs.parentNode.insertBefore(js, fjs);
					}
				}(document, 'script', 'twitter-wjs');</script></p>

	</div>

	<form method="post" class="proper_contact_settings_form">
	<table class="form-table">
	<tr>
		<td>
			<p><input name="save" type="submit" value="Save changes" class="button-primary"></p>
		</td>
	</tr>

	<?php
	foreach ( $plugin_options as $key => $value ) :

		// More clear option names

		// Human-readable name
		$opt_name = $value[0];

		// Machine name as ID
		$opt_id = $key;

		// Description for this field, aka help text
		$opt_desc = $value[1];

		// Input type, set to callback to use a function to build the input
		$opt_type = $value[2];

		// Default value
		$opt_default = $value[3];

		// Value currently saved
		$opt_val = isset( $propercfp_options[$opt_id] ) ? $propercfp_options[$opt_id] : $opt_default;

		// Options if checkbox, select, or radio
		$opt_options = empty( $value[4] ) ? array() : $value[4];

		// Allow for blocks of HTML to be displayed within the settings form
		if ( $opt_type == 'html' ) :
			?>
			<tr>
				<td colspan="2">
					<h4><?php _e( $opt_name, 'proper-contact' ) ?></h4>

					<p class="option_desc"><?php _e( $opt_desc, 'proper-contact' ) ?></p>
				</td>
			</tr>
		<?php

		// Allow titles to be added to deliniate sections
		elseif ( $opt_type == 'title' ) :
			?>

			<tr>
				<th colspan="2" scope="row">
					<hr>
					<h3 class="title"><?php _e( $opt_name, 'proper-contact' ) ?></h3>
				</th>
			</tr>

		<?php

		// Displays correct inputs for "text" type
		elseif ( $opt_type == 'text' || $opt_type == 'number' || $opt_type == 'email' || $opt_type == 'url' ) :
			?>

			<tr>
				<th scope="row">
					<label for="<?php echo $opt_id ?>"><?php _e( $opt_name, 'proper-contact' ) ?>:</label>
				</th>
				<td>
					<input name="<?php echo $opt_id ?>" id="<?php echo $opt_id ?>" type="<?php echo $opt_type ?>" value="<?php echo stripslashes( $opt_val ) ?>" class="widefat">

					<p class="description"><?php _e( $opt_desc, 'proper-contact' ) ?></p>

				</td>
			</tr>

		<?php

		// Displays correct inputs for "select" type
		elseif ( $opt_type == 'select' ) :
			?>

			<tr>
				<th scope="row">
					<label for="<?php echo $opt_id ?>"><?php _e( $opt_name, 'proper-contact' ) ?>:</label>
				</th>
				<td>
					<select name="<?php echo $opt_id ?>" id="<?php echo $opt_id ?>">
						<?php
						foreach ( $opt_options as $key => $val ) :

							$selected = '';
							if ( $opt_val == $key )
								$selected = 'selected';
							?>
							<option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $val ?></option>
						<?php endforeach; ?>
					</select>

					<p class="description"><?php _e( $opt_desc, 'proper-contact' ) ?></p>
				</td>
			</tr>

		<?php

		// Displays correct inputs for "radio" type
		elseif ( $opt_type == 'radio' ) :
			?>

			<tr>
				<th scope="row">
					<span><?php _e( $opt_name, 'proper-contact' ) ?>:</span>
				</th>
				<td>

					<?php
					foreach ( $opt_options as $val ) :

						$checked = '';
						if ( $propercfp_options[$opt_id] == $val )
							$checked = 'checked';
						?>

						<input type="radio" value="<?php echo $val ?>" name="<?php echo $opt_id ?>" id="<?php echo $opt_id . '_' . $val; ?>" <?php echo $checked ?>>
						<label for="<?php echo $opt_id . $val; ?>"><?php echo $val ?></label><br>

						<p class="description"><?php _e( $opt_desc, 'proper-contact' ) ?></p>

					<?php endforeach; ?>
				</td>
			</tr>

		<?php

		// Checkbox input, allows for multiple or single
		elseif ( $opt_type == 'checkbox' ) :
			?>

			<tr>
				<th scope="row">
					<span><?php _e( $opt_name, 'proper-contact' ) ?>:</span>
				</th>
				<td>
					<?php
					// If we have multiple checkboxes to show
					if ( ! empty( $opt_options ) ) :
						for ( $i = 0; $i < count( $opt_options ); $i ++ ) :

							// Need to mark current options as checked
							$checked = '';
							if ( in_array( $opt_options[$i], $propercfp_options[$opt_id] ) )
								$checked = 'checked';
							?>
							<p>
								<input type="checkbox" value="<?php echo $opt_options[$i] ?>" name="<?php echo $opt_id ?>[]" id="<?php echo $opt_id . '_' . $i ?>" <?php echo $checked ?>>
								<label for="<?php echo $opt_id . '_' . $i ?>"><?php echo $opt_options[$i] ?></label>
							</p>
						<?php
						endfor;

					// Single "on-off" checkbox
					else :
						$checked = '';
						if ( $opt_val == 'yes' )
							$checked = 'checked';
						?>

						<input type="checkbox" value="yes" name="<?php echo $opt_id ?>" id="<?php echo $opt_id ?>" <?php echo $checked ?>>
						<label for="<?php echo $opt_id ?>">Yes</label>

					<?php endif; ?>
					<p class="description"><?php _e( $opt_desc, 'proper-contact' ) ?></p>
				</td>
			</tr>

		<?php

		// Displays input for "textarea" type
		elseif ( $opt_type == 'textarea' ) :
			?>
			<tr>
				<th scope="row">
					<label for="<?php echo $opt_id ?>"><?php _e( $opt_name, 'proper-contact' ) ?>:</label>
				</th>
				<td>
					<textarea rows="6" cols="60" name="<?php echo $opt_id ?>" id="<?php echo $opt_id ?>" class="large-text"><?php echo stripslashes( $opt_val ) ?></textarea>

					<p class="description"><?php _e( $opt_desc, 'proper-contact' ) ?></p>
				</td>
			</tr>

		<?php
		endif;

	endforeach;
	?>
	<tr>
		<td colspan="2">
			<p>
				<input name="save" type="submit" value="<?php _e( 'Save changes', 'proper-contact' ) ?>" class="button-primary">
				<input type="hidden" name="action" value="save">
				<input type="hidden" name="proper_nonce" value="<?php echo wp_create_nonce( $current_user->user_email ) ?>">
			</p>

		</td>
	</tr>
	</table>
	</form>

	</div>

<?php
}

/**
 * Save default options if none exist
 */
function proper_contact_form_settings_init() {

	if ( ! get_option( 'propercfp_settings_array' ) ) {

		$new_opt = array();

		foreach ( proper_contact_plugin_options() as $key => $opt ) {
			$new_opt[$key] = $opt[3];
		}

		update_option( 'propercfp_settings_array', $new_opt );

	}

}

add_action( 'admin_head', 'proper_contact_form_settings_init' );

/**
 * Add a settings link to the plugin listing
 */
function proper_contact_form_plugin_links( $links ) {

	$settings_link = '<a href="' . admin_url( 'options-general.php?page=pcfp-admin' ) . '">' .
		__( 'Settings', 'proper-contact' ) . '</a>';
	array_unshift( $links, $settings_link );

	return $links;
}

add_filter( 'plugin_action_links_proper-contact-form/proper-contact-form.php',
	'proper_contact_form_plugin_links', 10, 2 );

/**
 * Enqueue CSS and JS needed in the admin
 */
function proper_contact_admin_css_js() {
	global $pagenow;

	if (
		( $pagenow == 'options-general.php' || $pagenow == 'admin.php' )
		&& isset( $_GET['page'] ) && $_GET['page'] == 'pcfp-admin'
	) {
		wp_enqueue_style( 'proper-contact', PROPER_CONTACT_URL . 'css/wp-admin.css' );
	}
}

add_action( 'admin_enqueue_scripts', 'proper_contact_admin_css_js' );