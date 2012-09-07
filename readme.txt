=== Quick Contact Form ===

Contributors: 
Tags: contact form
Requires at least: 2.7
Tested up to: 3.4.1
Stable tag: trunk

Simple plug and play contact form. 

== Description ==

A really, really simple drag and drop contact form. There is nothing to configure, all you have to do is set up your email address and add the shortcode to your pages.

= Features =

*	Drag and drop fields with easy to edit labels and captions.
*	Range of border styles.
*	Display latest messages on dashboard
*	Custom error and thank-you messages

= Developers plugin page =

[quick contact form plugin](http://aerin.co.uk/quick-contact-form/).

= Bugs and Errors =

Some people report errors on activation.  I can't replicate the problem so if it doesn't work for you please let me know the problem: graham@aerin.co.uk.

= Thanks to the debuggers and testers = 
[Jon](http://www.cloud9its.co.uk/)
[Gareth](http://www.garethgillman.co.uk)
[Karl](http://employmentlawclinic.com/)

== Screenshots ==
1. This is an example of a simple form. Everything on the form is editable.  To see more go to the [quick contact form examples](http://aerin.co.uk/contact-form-examples/) page.

== Installation ==

1.	Download the plugin.
2.	Login to your wordpress dashboard.
3.	Go to 'Plugins', 'Add New' then 'Upload'.
4.	Browse to the downloaded plugin then then 'Install Now'.
5.	Activate the plugin.
6.	Go to the plugin 'Settings' page to add the recipient's email address.
7.	Edit any of the form settings if you wish.
8.	Drag and drop the 'Quick Contact Form' widget to your sidebar.
9.	To use the form in your posts and pages add the shortcode `[qcf]`.
10.	To use the form in your theme files use the code `<?php echo do_shortcode('[qcf]'); ?>`.

== Frequently Asked Questions ==

= How do I change the labels and captions? =
Go to your plugin list and scroll down until you see 'Quick Contact Form' and click on 'Settings'. Select the 'Form Settings', 'Error Messages' or 'Reply options' tabs. Change the settings and Save (quite important this last bit).

= What's the shortcode? =
[qcf]

= How do I change the colours? =
Change the background in the plugin settings 'Styling' page. Or edit the 'quick-contact-form-styles.css'.

= Can I add more fields? =
No.

= Why not? =
Well OK yes you can add more fields if you want but you are going to have to fiddle about with the php file which needs a bit of care an attention. Everything you need to know is in the [wordpress codex](http://codex.wordpress.org/Writing_a_Plugin).

= It's all gone wrong! =
If it all goes wrong, just reinstall the plugin and start again. If you need help then [contact me](http://aerin.co.uk/contact-me/).

= Does anybody read these things? =
I did, and so have you.  Maybe there will be more, who knows.

= Have you got any pictures of cute kittens? =
No but I've inherited 2 ancient, deaf and grumpy Persians.

== Changelog ==

= 3.2 =
*	Validation can check the email and telephone number format even if they are not required fields.

= 3.1 =
*	Random maths captcha added
*	Editable email subject line
*	Background colour options
*	Changed the layout of the form editor to make it simpler.
*	Improved the the instructions for use
*	Fixed a small bug in the checkbox validator

= 3.0 =
*	Now lots of fields to select and arrange
*	Changed the way files are written to the database

= 2.5 =
*	Added tab to edit the erorr messages

= 2.4 =
*	Added options to edit the thank you message
*	Added options to display and track messages

= 2.3 =
*	Added options to select which fields you want on your form
*	Added a forth field so you can have email and telephone number (woo!)
*	Tidied up the way messages are displayed
*	Fixed sorting bug

= 2.2 =
*	Added reset options

= 2.1 =
*	Tweak to the stylesheet to cope with the hopeless Internet Explorer CSS support

= 2.0 =
*	Major upgrade to the settings interface
*	Option to display latest messages on your dashboard

= 1.4 =
*	Added widget
*	Changed text colour of required fields (looks much nicer)

= 1.3 =
*	Added an optional maths checker to catch the spambots
*	Added email and telephone number validation

= 1.2 =
*	Changed the way error and the thank you messages are displayed (no more popups).
*	Added option to select which fields are required.

= 1.1 =
*	Fixed 'onclick' bug

= 1.0 =
*	Initial Issue