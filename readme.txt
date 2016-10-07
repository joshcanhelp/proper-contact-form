=== PROPER Contact Form ===
Contributors: properwp, joshcanhelp
Donate link: http://theproperweb.com/product/proper-contact-form/
Tags: contact, contact form, contact form widget, email form, lead generation
Requires at least: 4.0
Tested up to: 4.6.1
Stable tag: 1.1.0

Creates a flexible, secure contact form on your WP site that saves contact emails

== Description ==

A well-coded, secure, and flexible WordPress plugin that makes creating a contact form very simple. This is meant to be a simple tool for both both savvy and novice WordPress users alike.

At the moment, this plugins creates a contact form with the shortcode `[proper_contact_form]` that works on any page. Users have the option to:

- Choose the fields that appear
- Create an auto-respond email
- Redirect contact submissions to a new page (helpful for goal tracking)
- Store contacts in the database
- Over-ride label names and error messages

Features in the works:

- Additional style options
- Ability to add custom fields to the form

Get the absolute latest at the [Github repo](https://github.com/joshcanhelp/proper-contact-form).

== Screenshots ==

1. The contact form, with styles, on a page
2. First section of the settings page
3. Second section of the settings page
4: Fourth section of the settings page

== Installation ==

Activating the Proper Contact Form plugin is just like any other plugin. If you've uploaded the plugin package to your server already, skip to step 5 below:

1. In your WordPress admin, go to **Plugins > Add New**
2. In the Search field type "proper contact form"
3. Under "PROPER Contact Form," click the **Install Now** link
4. Once the process is complete, click the **Activate Plugin** link
5. Now, you're able to add contact forms but, first, we could configure a few settings. These can be found at **Settings > Proper Contact**
6. Make the changes desired, then click the **Save changes** button at the bottom
7. To add this form to any page or post, just copy/paste or type "[proper_contact_form]" into the page or post content and save. The form should appear on that page

== Changelog ==

= 1.1.0 =
* Fixed deprecated get_currentuserinfo()
* Moved shortcode declaration to separate file; minor refactor and additional comments
* Added pcf_field_form_after_name action
* Added pcf_field_form_after_email action
* Added pcf_field_form_after_phone action
* Made comment textarea field optional, like name and email and phone

= 1.0.0 =
* Adjusted styling on form when styles are active
* Correct spam blacklist checking
* Added a setting to remove the nonce on the contact form
* Fixed the PHP notice caused by incorrect widget loading

= 0.9.8.6 =
* Fixed the math captcha bug that required page reload
* Updated to latest PhpFormBuilder include (0.8.6)

= 0.9.8.5 =
* Added a settings field for submission notification emails

= 0.9.8.4 =
* Changed capability required for the settings page to `manage_options` from `edit_themes`

= 0.9.8.3 =
* Upgraded PhpFormBuilder class to latest version; added check to make sure the class doesn't exist
* Added an option to send notification emails from a different address
* Removed id array element, using array key instead

= 0.9.8.2 =
* Fixed no-settings error on install

= 0.9.8.1 =
* Fixed post-submit redirect bug on sub-directory WP installs

= 0.9.8 =
* Added a redirect back to the same page to avoid duplicate form submissions
* Fixed issue with HTML special characters in email subject line
* Fixed the wp-admin icon for new 3.8 dashboard

= 0.9.7 =
* Fixed issue with widget not displaying text above the form

= 0.9.6 =
* Contact form widget
* Math CAPTCHA
* Added ability to block the form using the comment blacklist
* Improved sanitization and escaping of data
* Better and more complete internationalization
* More standard styles for the settings page
 
= 0.9.5.1 =
* Improved field handling
* Better security for the settings page

= 0.9.5 =
* Added text fields for error messages and submit button
* Added a setting to use HTML5 validation
* Changed to use custom label fields for email notification
* Changed to use the "Text to show when form is submitted..." text for the confirmation email subject
* Changed the email notification format slightly
* Setting better default text and information throughout
* Fixed the missing error formatting for the phone number field

= 0.9.3 =
* Fixed name requirement issue

= 0.9.2 =
* Fixed default handling
* Fixed custom label handling
* Changed email sent to admin slightly
* Added option for name

= 0.9.1 =
* Fixed wp_kses error
* Corrected outgoing email

= 0.9 =
* First release
e
