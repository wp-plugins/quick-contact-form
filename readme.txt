=== Quick Contact Form ===

Contributors: 
Tags: contact form
Requires at least: 2.7
Tested up to: 3.3.1
Stable tag: trunk

Simple plug and play contact form. 

== Description ==

A really, really simple contact form. There is nothing to configure, all you have to do is set up your email address and add the shortcode to your pages.

= Features =

*	Ready made contact form with three fields.
*	Compact and easy to use.
*	No configuration required.
*	Editable title, blurb, labels and captions (if you want to).
*	Pre-set range of border styles.
*	Optional form width adjustment.
*	Safe for use with animaals and children.

= Developers plugin page =

[callback form plugin](http://aerin.co.uk/quick-contact-form/).

== Installation ==

1.	Download the plugin.
2.	Login to your wordpress dashboard.
3.	Go to 'Plugins', 'Add New' then 'Upload'.
4.	Browse to the downloaded plugin then then 'Install Now'.
5.	Activate the plugin.
6.	Go to the plugin 'Settings' page to set the recipient's email address.
7.	Edit the field labels and submit button caption is required.
8.	To use the form in your posts and pages add the shortcode [qcf].
9.	To use the form in a text widget add the line `add_filter('widget_text', 'do_shortcode');` to your functions.php file. You can now use the shortcode [qcf].
10.	To use the form in your theme files use the code `<?php echo do_shortcode('[qcf]'); ?>`.

== Frequently Asked Questions ==

= How do I change the labels and captions? =
Go to your plugin list and scroll down until you see the 'Quick Contact Form' and click on 'Settings'. Change the labels and settings and click on 'Save Settings'.

= What's the shortcode? =
[qcf]

= How do I change the colours? =
Go to your plugin list and scroll down until you see the 'Quick Contact Form' and click on 'Edit'. Click on the link 'quick-contact-form-styles.css' over on the right. Make the changes and click on 'Update Changes' down the bottom.

= Can I add more fields? =
No.

= Why not? =
Well OK yes you can add more fields if you want but you are going to have to fiddle about with the php file which needs a bit of care an attention. Everything you need to know is in the [wordpress codex](http://codex.wordpress.org/Writing_a_Plugin).

= It's all gone wrong! =
If it all goes wrong, just reinstall the plugin and start again. If you need help then [contact me](http://aerin.co.uk/contact-me/).

= Does anybody read these things? =
I did, and so have you.  Maybe there will be more, who knows.....

== Changelog ==

= 1.1 =
*	Fixed 'onclick' bug

= 1.0 =
*	Initial Issue