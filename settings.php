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
	array(
		'Fields to show',
		'',
		'',
		'title',
		'',
	),
	array(
		'Email address',
		'propercfp_email_field',
		'Should an email address field be displayed?',
		'select',
		'yes', 
		array(
			'yes' => 'Yes but not required',
			'req' => 'Required'
		),
	),
	array(
		'Phone number',
		'propercfp_phone_field',
		'Should a phone number field be displayed?',
		'select',
		'yes',
		array(
			'yes' => 'Yes but not required',
			'req' => 'Required'
		),
	),
	array(
		'"Reason for contacting" options',
		'propercfp_reason',
		'Enter the options for the "Reason for contacting" dropdown, each on their own line. Leave this blank to omit this option',
		'textarea',
		'',
	),
	array(
		'Form processing options',
		'',
		'',
		'title',
		'',
	),
	array(
		'Default contact submission email',
		'propercfp_email',
		'Email to use for the sender and receiver of the contact form',
		'text',
		get_bloginfo('admin_email')
	),
	array(
		'"Thank You" URL',
		'propercfp_result_url',
		'Select the post-submit page for all forms submitted',
		'select',
		'',
		proper_get_content_array()
	),
	array(
		'Add styles to the site',
		'propercfp_css',
		'Checking this box will add styles to the form. By deafult, this is off so you can add your own styles.',
		'checkbox',
		'',
	),
	array(
		'Store submissions in the database',
		'propercfp_store',
		'Should the submissions be stored in the admin area?',
		'checkbox',
		'',
	),
	array(
		'Send email confirmation<br />to form submitter',
		'propercfp_confirm_email',
		'Adding text here will send an email to the form submitter.',
		'textarea',
		'',
	),
	array(
		'Text overrides',
		'',
		'',
		'title',
		'',
	),
	array(
		'Name field label',
		'propercfp_label_name',
		'',
		'text',
		'Your full name'
	),
	array(
		'Email field label',
		'propercfp_label_email',
		'',
		'text',
		'Your email address'
	),
	array(
		'Phone field label<br />(if activated above)',
		'propercfp_label_phone',
		'',
		'text',
		'Your phone number'
	),
	array(
		'Reason for contacting label<br />(if activated above)',
		'propercfp_label_reason',
		'',
		'text',
		'Reason for contacting'
	),
	array(
		'Comment field label',
		'propercfp_label_comment',
		'',
		'text',
		'Question or comment'
	),
	array(
		'Submit complete text<br />(if "Thank You" URL above is not set)',
		'propercfp_label_submit',
		'',
		'text',
		'Thank you for your contact!'
	),
	
);

function cfp_add_admin() {
	
	global $plugin_options, $propercfp_options ;
	
	if ( array_key_exists('page', $_GET) && $_GET['page'] === 'pcfp-admin' ) {
		
		if (array_key_exists('action', $_REQUEST)) {
		
			if ('save' == $_REQUEST['action'] ) {
		
				foreach ($plugin_options as $opt) {
		
					if (isset($_REQUEST[$opt[1]])) $propercfp_options[$opt[1]] = $_REQUEST[$opt[1]];
					else $propercfp_options[$opt[1]] = '';
		
				}
				
				update_option('propercfp_settings_array', $propercfp_options);
		
				header("Location: admin.php?page=pcfp-admin&saved=true");
				
				die;
		
			} 
		}
	}

	add_submenu_page('options-general.php', "Proper Contact Form Options", "Proper Contact", 'edit_themes', 'pcfp-admin', 'proper_contact_admin');

}

add_action('admin_menu' , 'cfp_add_admin');

function proper_contact_admin() {

    global $plugin_options, $propercfp_options;

    if ( isset($_REQUEST['saved']) && $_REQUEST['saved'] ) 
			echo '<div id="message" class="updated fade"><p><strong>'.__('Settings saved.','thematic').'</strong></p></div>';
			
		elseif ( isset($_REQUEST['reset']) && $_REQUEST['reset'] ) 
			echo '<div id="message" class="updated fade"><p><strong>'.__('Settings reset.','thematic').'</strong></p></div>';
	
	?>
	<div class="wrap" id="proper-options-page">
		
		<h2>Proper Contact Settings</h2>
		
		<?php 
		$doc_file = WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)) . '/inc/docs.html';
		if (is_readable($doc_file)) echo file_get_contents($doc_file) 
		?>
            
		<form method="post">
	
			<table class="jch-form-table" cellpadding="0" cellspacing="0">	    
			<?php 
			foreach ($plugin_options as $value) :
				
				/*
				Setting better variable names for clarity
				*/
				
				$opt_name = $value[0];
				
				$opt_id = $value[1];
				
				$opt_desc = $value[2];
				
				$opt_type = $value[3];
				
				if (isset($value[4][0]) && $value[4][0] == '#')
					$opt_default = substr($value[4], 1, 6);
				else 
					$opt_default = $value[4];
				
				if(isset($value[5])) $opt_options = $value[5];
				
				/*
				Descriptive text in the theme settings
				*/
				if ($opt_type == 'description') { 
				?>
				
				<tr>
					<td style="padding: 20px 10px; border-bottom: 1px solid #f1f1f1" colspan="2">
						<h4><?php echo $opt_name; ?>:</h4>
						<p><?php echo $opt_desc ?></p>
					</td>
				</tr>
				
				<?php
				/*
				Text input
				*/		
				} elseif ($opt_type == 'text' || $opt_type == 'url' || $opt_type == 'email') { 
				?>
				<tr>
					<th style="border-bottom: 1px solid #f1f1f1; text-align: left; padding: 10px" scope="row">
						<label for="<?php echo $opt_id; ?>"><?php echo $opt_name; ?>:</label>
					</th>
					<td style="padding: 20px 10px; border-bottom: 1px solid #f1f1f1">
						<?php echo $opt_desc ?><br >
						<input size="60" onfocus="this.select();" name="<?php echo $opt_id; ?>" id="<?php echo $opt_id; ?>" type="<?php echo $opt_type ?>" value="<?php 
							if ( isset($propercfp_options[$opt_id]) && !empty($propercfp_options[$opt_id])) { 
								echo stripslashes($propercfp_options[$opt_id]); 
							} else {
								echo $opt_default;
							}
							?>" >
					</td>
				</tr>
                <?php 
				// Displays correct inputs for "select" type
				} elseif ($opt_type == 'select') {
				?>
                
                <tr>
					<th style="border-bottom: 1px solid #f1f1f1; text-align: left; padding: 10px" scope="row">
						<label for="<?php echo $opt_id; ?>"><?php echo $opt_name; ?>:</label>
					</th>
					<td style="padding: 20px 10px; border-bottom: 1px solid #f1f1f1">
						<?php echo $opt_desc; ?><br >
						<select name="<?php echo $opt_id; ?>" id="<?php echo $opt_id; ?>">	
								<option value="">None</option>
                        <?php foreach ($opt_options as $key => $val) {?>
                        	<option value="<?php echo $key ?>" <?php 
						if ( isset($propercfp_options[$opt_id]) && $propercfp_options[$opt_id] == $key) { 
							echo 'selected';
						} ?>><?php echo $val ?></option> 
                        <?php } ?>
                        </select>
					</td>
				</tr>
                
                <?php 
				} 
				// Displays correct inputs for "radio" type
				elseif ($opt_type == 'radio') {
				?>
                
                <tr>
					<th style="border-bottom: 1px solid #f1f1f1; text-align: left; padding: 10px" scope="row">
						<?php echo $opt_name; ?>:
					</th>
					<td style="padding: 20px 10px; border-bottom: 1px solid #f1f1f1">
						<?php echo $opt_desc; ?><br >
                        <?php foreach ($opt_options as $val) {?>
                        
                        <input type="radio" value="<?php echo $val ?>" <?php if ( $propercfp_options[$opt_id] == $val || ($propercfp_options[$opt_id] == '' && $opt_default == $val )) { echo 'checked';} ?> name="<?php echo $opt_id; ?>" id="<?php echo $opt_id . $val; ?>">
                        <label for="<?php echo $opt_id . $val; ?>"><?php echo $val ?></label><br >
                        <?php } ?>
					</td>
				</tr>
                
                <?php } elseif ($opt_type == 'checkbox') {?>
                
                <tr>
					<th style="border-bottom: 1px solid #f1f1f1; text-align: left; padding: 10px" scope="row">
						<?php echo $opt_name; ?>:
					</th>
					<td style="padding: 20px 10px; border-bottom: 1px solid #f1f1f1">	
						<?php echo $opt_desc ?><br >
                        <input type="checkbox" value="yes" <?php if ( isset($propercfp_options[$opt_id]) && $propercfp_options[$opt_id] == 'yes' ) { echo 'checked';} ?> name="<?php echo $opt_id; ?>" id="<?php echo $opt_id; ?>">
                        <label for="<?php echo $opt_id; ?>">Yes</label><br >

					</td>
				</tr>
                
				<?php } 
				// Displays correct inputs for "textarea" type
				elseif ($opt_type == 'textarea') { ?>
				<tr>
					<th style="border-bottom: 1px solid #f1f1f1; text-align: left; padding: 10px" scope="row">
						<?php echo $opt_name; ?>:
					</th>
					<td style="padding: 20px 10px; border-bottom: 1px solid #f1f1f1">
					<?php echo $opt_desc ?><br>
						<textarea onfocus="this.select();" rows="6" cols="60" name="<?php echo $opt_id; ?>" id="<?php echo $opt_id; ?>" style="<?php echo $value['style']; ?>" type="<?php echo $opt_type; ?>" ><?php if ( isset($propercfp_options[$opt_id])) { echo stripslashes($propercfp_options[$opt_id]); }?></textarea>
					</td>
				</tr>
				
				<?php } elseif ($opt_type == 'title') {?>
				<tr>
                      <td style="padding: 20px 10px;" colspan="2" class="header">
                         <h3 style="font-size: 1.6em"><?php  echo $opt_name ?></h3>
                       </td>
				 </tr>
				<?php  }?>
            
			<?php 
	
			endforeach; 
			?>
			</div>
			 </table>
			 <p class="submit">
			 <input name="save" type="submit" value="Save changes" class="button-primary">
			 <input type="hidden" name="action" value="save" >
			</p>
		  </form>
	
		  
		</div>
		
<?php 

}//end function mytheme_admin() 


function jch_settings_init() {
	
	global $plugin_options, $propercfp_options;
	
	if (!get_option('propercfp_settings_array')) :
		
		foreach ($plugin_options as $opt) {
				
			$propercfp_options[$opt[1]] = $opt[3];
			
		}
			
		update_option( 'propercfp_settings_array', $propercfp_options);
	
	endif; 
		

}

add_action('admin_head', 'jch_settings_init');