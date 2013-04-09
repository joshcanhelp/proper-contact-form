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

global $plugin_options;
$plugin_options = array(
	'head1' => array(
		'Fields to show',
		'',
		'',
		'title',
		'',
	),
	'propercfp_name_field' => array(
		'Name',
		'propercfp_name_field',
		'Should a name field be displayed?',
		'select',
		'yes', 
		array(
			'' => 'None',
			'yes' => 'Yes but not required',
			'req' => 'Required'
		),
	),
	'propercfp_email_field' => array(
		'Email address',
		'propercfp_email_field',
		'Should an email address field be displayed?',
		'select',
		'yes', 
		array(
			'' => 'None',
			'yes' => 'Yes but not required',
			'req' => 'Required'
		),
	),
	'propercfp_phone_field' => array(
		'Phone number',
		'propercfp_phone_field',
		'Should a phone number field be displayed?',
		'select',
		'yes',
		array(
			'' => 'None',
			'yes' => 'Yes but not required',
			'req' => 'Required'
		),
	),
	'propercfp_reason' => array(
		'"Reason for contacting" options',
		'propercfp_reason',
		'You can have people choose the reason for their contact from a drop-down list. If you would like this option to appear, enter the different reasons into the text box below, each one on its own line.',
		'textarea',
		'',
	),
	'head2' => array(
		'Form processing options',
		'',
		'',
		'title',
		'',
	),
	'propercfp_email' => array(
		'Default contact submission email',
		'propercfp_email',
		'Email to use for the sender and receiver of the contact form',
		'text',
		get_bloginfo('admin_email')
	),
	'propercfp_result_url' => array(
		'"Thank You" URL',
		'propercfp_result_url',
		'Select the post-submit page for all forms submitted',
		'select',
		'',
		proper_get_content_array()
	),
	'propercfp_css' => array(
		'Add styles to the site',
		'propercfp_css',
		'Checking this box will add styles to the form. By default, this is off so you can add your own styles.',
		'checkbox',
		'',
	),
	'propercfp_store' => array(
		'Store submissions in the database',
		'propercfp_store',
		'Should the submissions be stored in the admin area? If chosen, contact form submissions will be saved in Contacts on the left (appears after this option is activated).',
		'checkbox',
		'',
	),
	'propercfp_confirm_email' => array(
		'Send email confirmation to form submitter',
		'propercfp_confirm_email',
		'Adding text here will send an email to the form submitter. The email uses the "Text to show when form is submitted..." field below as the subject line. Plain text only here, no HTML.',
		'textarea',
		'',
	),
	'head3' => array(
		'Text overrides',
		'',
		'',
		'title',
		'',
	),
	'propercfp_label_name' => array(
		'Name field label',
		'propercfp_label_name',
		'',
		'text',
		'Your full name'
	),
	'propercfp_label_email' => array(
		'Email field label',
		'propercfp_label_email',
		'',
		'text',
		'Your email address'
	),
	'propercfp_label_phone' => array(
		'Phone field label<br />(if activated above)',
		'propercfp_label_phone',
		'',
		'text',
		'Your phone number'
	),
	'propercfp_label_reason' => array(
		'Reason for contacting label<br />(if activated above)',
		'propercfp_label_reason',
		'',
		'text',
		'Reason for contacting'
	),
	'propercfp_label_comment' => array(
		'Comment field label',
		'propercfp_label_comment',
		'',
		'text',
		'Question or comment'
	),
	'propercfp_label_submit_btn' => array(
		'Submit button text',
		'propercfp_label_submit_btn',
		'',
		'text',
		'Submit'
	),
	'propercfp_label_submit' => array(
		'Successful form submission text',
		'propercfp_label_submit',
		'This text is used on the page if no "Thank You" URL is set above. This is also used as the confirmation email title, if one is set to send out.',
		'text',
		'Thank you for your contact!'
	),
	'head4' => array(
		'HTML5 validation',
		'',
		'',
		'title',
		'',
	),
		'propercfp_html5_no_validate' => array(
		'Use HTML5 validation',
		'propercfp_html5_no_validate',
		'',
		'checkbox',
		'yes'
	),
	'head5' => array (
		'Error Messages (if not using HTML5 validation)',
		'',
		'',
		'title',
		'',
	),
	'propercfp_label_err_name' => array(
		'Error message if name required and missing',
		'propercfp_label_err_name',
		'',
		'text',
		'Enter your name'
	),
	'propercfp_label_err_email' => array(
		'Error message if E-mail required and missing',
		'propercfp_label_err_email',
		'',
		'text',
		'Enter a valid email'
	),
	'propercfp_label_err_phone' => array(
		'Error message if phone required and missing',
		'propercfp_label_err_phone',
		'',
		'text',
		'Please enter a phone number'
	),
	'propercfp_label_err_no_content' => array(
		'Error message if post content is missing',
		'propercfp_label_err_no_content',
		'',
		'text',
		'Enter your question or comment'
	)
);

function cfp_add_admin() {
	
	global $plugin_options, $propercfp_options, $current_user;
	get_currentuserinfo();

	if (
		// On the right page
		array_key_exists('page', $_GET) &&
		$_GET['page'] === 'pcfp-admin' &&
		// We're saving options
		array_key_exists( 'action', $_REQUEST ) &&
		$_REQUEST['action'] == 'save' &&
		// This action is authorized
		current_user_can( 'manage_options' ) &&
		wp_verify_nonce( $_POST['proper_nonce'], $current_user->user_email )
	) {

		foreach ($plugin_options as $opt) :
			if (isset($_REQUEST[$opt[1]])) {
				$opt_data = filter_var($_REQUEST[$opt[1]], FILTER_SANITIZE_STRING);
				$propercfp_options[$opt[1]] = $opt_data;
			} else {
				$propercfp_options[$opt[1]] = '';
			}
		endforeach;

		update_option('propercfp_settings_array', $propercfp_options);
		header("Location: admin.php?page=pcfp-admin&saved=true");
		die;
	}

	add_submenu_page('options-general.php', "PROPER Contact settings", "PROPER Contact", 'edit_themes', 'pcfp-admin', 'proper_contact_admin');

}

add_action('admin_menu' , 'cfp_add_admin');



function proper_contact_admin() {

    global $plugin_options, $propercfp_options, $current_user;
	get_currentuserinfo();
		?>
	
		<div class="wrap" id="proper-contact-options">

			<h2>PROPER Contact Form Settings</h2>
			
			<?php 
			$doc_file = WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)) . '/inc/docs.html';
			if (is_readable($doc_file)) echo file_get_contents($doc_file) 
			?>

			<?php if ( !empty( $_REQUEST['saved'] ) ) : ?>
				<div id="setting-error-settings_updated" class="updated settings-error">
					<p><strong>PROPER Contact Form <?php echo  __( 'settings saved.', 'properwp' ) ?></strong></p>
				</div>
			<?php endif ?>

			<form method="post">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td>
							<p><input name="save" type="submit" value="Save changes" class="button-primary"></p>
						</td>
					</tr>

			<?php 
			foreach ($plugin_options as $value) :
				
				// More clear option names
				
				// Human-readable name
				$opt_name = $value[0];
				
				// Machine name as ID
				$opt_id = $value[1];
				
				// Description for this field, aka help text
				$opt_desc = $value[2];
				
				// Input type, set to callback to use a function to build the input
				$opt_type = $value[3];
				
				// Default value
				$opt_default = $value[4];
			
				// Value currently saved
				$opt_val = isset($propercfp_options[$opt_id]) ? $propercfp_options[$opt_id] : $opt_default;
	
				// Options if checkbox, select, or radio
				$opt_options = empty($value[5]) ? array() : $value[5];
		
				// Allow for blocks of HTML to be displayed within the settings form
				if ($opt_type == 'html') :
				?>
					<tr>
						<td colspan="2">
							<h4><?php echo $opt_name ?></h4>
							<p class="option_desc"><?php echo $opt_desc ?></p>
						</td>
					</tr>
				<?php
				
				// Allow titles to be added to deliniate sections
				elseif ($opt_type == 'title') :
				?>
				
					<tr>
						<td colspan="2" class="header">
							<h3 style="font-size: 1.6em"><?php  echo $opt_name ?></h3>
						</td>
					</tr>
					
				<?php  
				
				// Horizontal breaks
				elseif ($opt_type == "break") : 
				?>
					
					<tr><td colspan="2"><hr></td></tr>
					
				<?php
			
				// Displays correct inputs for "text" type			
				elseif ($opt_type == 'text' || $opt_type == 'number' || $opt_type == 'email' || $opt_type == 'url') :
				?>
				
					<tr>
						<th>
							<label for="<?php echo $opt_id ?>"><?php echo $opt_name ?>:</label>
						</th>
						<td>
							<p class="option_desc"><?php echo $opt_desc ?></p>
							<p><input size="60" name="<?php echo $opt_id ?>" id="<?php echo $opt_id ?>" type="<?php echo $opt_type ?>" value="<?php echo stripslashes($opt_val) ?>"></p>
						
						</td>
					</tr>
				
        <?php 
				
				// Displays correct inputs for "select" type
				elseif ($opt_type == 'select') :
				?>
                
					<tr>
						<th>
							<label for="<?php echo $opt_id ?>"><?php echo $opt_name ?>:</label>
						</th>
						<td>
							<p class="option_desc"><?php echo $opt_desc; ?></p>
							<p>
								<select name="<?php echo $opt_id ?>" id="<?php echo $opt_id ?>">
									<?php 
									
									foreach ($opt_options as $key => $val) : 
									
										$selected = '';	
										if ( $opt_val == $key )
											$selected = 'selected';	
											?>
										<option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $val ?></option> 
									<?php endforeach; ?>
								</select>
							</p>
						</td>
					</tr>
                
       	<?php 
				 
				// Displays correct inputs for "radio" type
				elseif ($opt_type == 'radio') :
				?>
                
					<tr>
						<th>
							<span><?php echo $opt_name ?>:</span>
						</th>
						<td>
							<p class="option_desc"><?php echo $opt_desc; ?></p>
							
							<?php 
							foreach ($opt_options as $val) : 
								
								$checked = '';
								if ( $propercfp_options[$opt_id] == $val ) 
									$checked = 'checked';
									?>		
												
								<p><input type="radio" value="<?php echo $val ?>" name="<?php echo $opt_id ?>" id="<?php echo $opt_id . '_' . $val; ?>" <?php echo $checked ?>>
								<label for="<?php echo $opt_id . $val; ?>"><?php echo $val ?></label><br></p>
								
							<?php endforeach; ?>
						</td>
					</tr>
                
        <?php 
				
				// Checkbox input, allows for multiple or single
				elseif ($opt_type == 'checkbox') :
				?>
                
					<tr>
						<th>
							<span><?php echo $opt_name ?>:</span>
						</th>
					<td>	
						<p class="option_desc"><?php echo $opt_desc ?></p>
						<?php
						// If we have multiple checkboxes to show
						if (!empty($opt_options)) : 
							for ( $i = 0; $i < count($opt_options); $i++ ) :
								
								// Need to mark current options as checked
								$checked = '';
								if ( in_array($opt_options[$i], $propercfp_options[$opt_id]) ) 
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
						<p>
							<input type="checkbox" value="yes" name="<?php echo $opt_id ?>" id="<?php echo $opt_id ?>" <?php echo $checked ?>>
							<label for="<?php echo $opt_id ?>">Yes</label>
						</p>
						<?php endif; ?>
					
					</td>
					</tr>
                
				<?php 
				
				// Displays input for "textarea" type
				elseif ($opt_type == 'textarea') : 
				?>
				<tr>
					<th>
						<?php echo $opt_name ?>:
					</th>
					<td>
						<p class="option_desc"><?php echo $opt_desc ?></p>
						<textarea rows="6" cols="60" name="<?php echo $opt_id ?>" id="<?php echo $opt_id ?>"><?php echo stripslashes($opt_val)?></textarea>
					</td>
				</tr>
				
				<?php 
				endif;
	
			endforeach; 
			?>
				<tr>
					<td colspan="2">
						<p>
							<input name="save" type="submit" value="Save changes" class="button-primary">
							<input type="hidden" name="action" value="save" >
							<input type="hidden" name="proper_nonce" value="<?php
								echo wp_create_nonce( $current_user->user_email )
							?>">
						</p>
						
					</td>
				</tr>
			</table>
		</form>
	
	</div>
	
	<?php 
} 


function proper_contact_form_settings_init() {
	
	global $plugin_options;
	
	if (!get_option('propercfp_settings_array')) {
		
		$new_opt = array();
		
		foreach ($plugin_options as $opt)				
			$new_opt[$opt[1]] = $opt[4];
			
		update_option( 'propercfp_settings_array', $new_opt);
	
	}
	
}

add_action('admin_head', 'proper_contact_form_settings_init');

// Admin screens
function proper_contact_form_css () {

	wp_register_style( 'proper_contact_form_css', plugin_dir_url( __FILE__ ) . 'css/admin.css');
	wp_enqueue_style( 'proper_contact_form_css' );
	
}
add_action('admin_enqueue_scripts', 'proper_contact_form_css');