Proper Contact Platform
================= 

A well-coded, secure, and (soon to be very) flexible WordPress plugin that makes creating contact (and other) forms very simple. This is meant to be a simple tool for both savvy WordPress users and seasoned WordPress developers. 

At the moment, this simply creates a contact form with the shortcode [proper_contact_form]. There is a settings page to tinker with a few of the options, and allows you to validate and submit to a new page to help with goal tracking in analytics.

This will eventually be submitted to the WordPress repo, most likely after the first feature listed below. There are a bazillion contact form plugins out there but nothing I've ever liked using or extending. I usually just code my own but I want a bit of flexibility for clients and projects, hance this. 

Planned features, in order:

1) Custom forms using shortcodes - this appears to be the easiest way to create forms both in the content, using the shortcode tag, and in the template, using `do_shortcut()`. We're working on the best way to balance flexibility, functionality, and ease-of-use. 

2) Storing form submissions - since we're pushing changes to the database from a public form, we have to be very careful here. I've tested a proof of concept in a few places but want to make sure it's solid before I release it. 

3) TinyLetter and Mailchimp integration - at some point, since I use these so often in my other projects.
