<?php

/*
Plugin Name: Quick Contact Form
Plugin URI: http://www.aerin.co.uk/quick-contact-form-plugin
Description: A really, really simple contact form. There is nothing to configure, just add your email address and it's ready to go.
Version: 1.0
Author: Graham Smith
Author URI: http://www.aerin.co.uk
*/

/*
Copyright (C) 2012 Graham Smith, aerin.co.uk (graham AT aerin DOT co DOT uk)

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

add_shortcode('qcf', 'qcf_form');
add_action('admin_init', 'qcf_init');
add_action('admin_init', 'qcf_add_defaults');
add_action('admin_menu', 'qcf_add_options_page');
add_filter( 'plugin_action_links', 'qcf_plugin_action_links', 10, 2 );

register_activation_hook(__FILE__, 'qcf_add_defaults');
/*
register_deactivation_hook( __FILE__, 'qcf_delete_options' );
*/
register_uninstall_hook(__FILE__, 'qcf_delete_options');


$myStyleUrl = plugins_url('quick-contact-form-style.css', __FILE__);
wp_register_style('qcf_style', $myStyleUrl);
wp_enqueue_style( 'qcf_style');

function qcf_add_defaults()
	{
	$qcf_options = array("Enquiry Form", "Complete the form below and we will be in contact very soon","Your Name", "Email Address","Message", "Send It!","","250","plain");
	add_option('qcf_options', $qcf_options);
	}

function qcf_admin_notice()
{
if ($qcf_options[6] == "") echo '<div class="updated">
       <p>For the <b>Easy Contact Form</b> to work you need to add your email address in the Settings page.</p>
    </div>';
}

function qcf_delete_options()
	{
	delete_option('qcf_options');
	}

function qcf_init()
	{
	register_setting('my_qcf_options', 'qcf_options');
	}

function qcf_add_options_page()
	{
	add_options_page('Quick Contact Form Options Page', 'Quick Contact', 'manage_options', __FILE__, 'qcf_options_page');
	}

function qcf_options_page()
	{
	?>
	<div style="margin-left: 14px">
	<h1>The Quick Contact Form</h1>
	<h2>Description</h2>
		<p>This contact form plugin can almost be used straight out the box. All you need to do is save your email address and insert the shortcode into your posts and pages.
	If you want to change the labels and captions just edit the existing entries and save. You can also alter the width of the form and change the border style.
	When you save the changes the updated form will preview on the right.</p>
		<h2>How to use the plugin</h2>
		<p>To add the contact form your posts or pages use the short code: <code>[qcf]</code>.<br />
		To use the form in a text widget add the line <code>add_filter('widget_text', 'do_shortcode');</code> to your functions.php file. You can now use the shortcode <code>[qcf]</code>.<br />
	To add it to your theme files use <code>&lt;?php echo do_shortcode('[qcf]'); ?&gt;</code><br />
	To change the styles use the plugin editor to fiddle with qcf-contact-form-styles.css</p>
		<p>Not much else to add really. Enjoy using the plugin</p>
		</div>
		<div id="qcf-options">
	<div id="qcf">
		<form method="post" action="options.php">
		<?php settings_fields('my_qcf_options');
		$qcf_options = get_option('qcf_options');
		$width = preg_replace("/[^0-9]/", "", $qcf_options[7]);
		$width = $width.'px';
		$input = $width;
		$submit = $width;
		$textarea = $width;
		if ($qcf_options[8] == "shadow") $shadow = "checked"; else $shadow = "";
		if ($qcf_options[8] == "roundshadow") $roundshadow = "checked"; else $roundshadow = "";
		if ($qcf_options[8] == "plain") $plain = "checked"; else $plain = "";
		if ($qcf_options[8] == "rounded") $rounded = "checked"; else $rounded = "";
		if ($qcf_options[8] == "none") $none = "checked"; else $none = "";
		?>
		<h2>Your email address</h2>	
		<p><span style="color:red; font-weight: bold;">Important!</span> Enter YOUR email address below.  This won't display, it's just so the plugin knows where to send the message.</p>
		<p><input type="text"  style="width:<?php echo $input; ?>;" label="Email" name="qcf_options[6]" value="<?php echo $qcf_options[6]; ?>" /></p>
		<p><input type="submit" class="button-primary" style="color: #FFF" value="<?php _e('Save Changes') ?>" /></p>
		<h2>Form Title and Introductory Blurb</h2>
		<p>Form title (leave blank if you don't want a heading):</p>
		<p><input type="text"  style="width:<?php echo $input; ?>;" name="qcf_options[0]" value="<?php echo $qcf_options[0]; ?>" /></p>
		<p>This is the blurb that will appear below the heading and above the form (leave blank if you don't want any blurb):</p>
		<p><input type="text"   style="width:100%" name="qcf_options[1]" value="<?php echo $qcf_options[1]; ?>" /></p>
		<h2>Form fields and captions</h2>
		<p>These can be any questions you like: name, email, telephone, shoe size, favourite colour and so on. The first two fields are required so don't leave them blank. The third field is a text area, you can leave this one blank if you like.</p><p>You can change the caption on the submit button as well.</p>
		<p><input type="text"   style="width:<?php echo $input; ?>;" name="qcf_options[2]" value="<?php echo $qcf_options[2]; ?>" /></p>
		<p><input type="text"   style="width:<?php echo $input; ?>;" name="qcf_options[3]" value="<?php echo $qcf_options[3]; ?>" /></p>
		<p><input type="text"  style="width:<?php echo $input; ?>;" name="qcf_options[4]" value="<?php echo $qcf_options[4]; ?>" /></p>
		<p><input type="text" id="submit" style="width:<?php echo $input; ?>; font-size: 130%;cursor:auto; color: #FFF" name="qcf_options[5]" value="<?php echo $qcf_options[5]; ?>" /></p>
		<h2>Form Width</h2>
		<p>Enter the width of the form in pixels. Just enter the value, no need to add 'px'. The current width is as you see it here.</p>
		<p><input type="text"  style="width:<?php echo $input; ?>;" label="width" name="qcf_options[7]" value="<?php echo $qcf_options[7]; ?>" /></p>
		<h2>Form Border</h2>
		
      <p>Note: The rounded corners and shadows only work on CSS3 supported browsers 
        and even then not in IE8. Don't blame me, blame Microsoft.</p>
		<p>
		<input style="margin: 0;" type="radio" name="qcf_options[8]"  value="none" <?php echo $none; ?> > No border<br />
		<input style="margin: 0;" type="radio" name="qcf_options[8]"  value="plain" <?php echo $plain; ?>  > Plain Border<br /> 
		<input style="margin: 0;" type="radio" name="qcf_options[8]"  value="rounded" <?php echo $rounded; ?>  > Round Corners (Not IE8)<br /> 		
		<input style="margin: 0;" type="radio" name="qcf_options[8]"  value="shadow" <?php echo $shadow; ?> > Shadowed Border(Not IE8)<br />
		<input style="margin: 0;" type="radio" name="qcf_options[8]"  value="roundshadow" <?php echo $roundshadow; ?> > Rounded Shadowed Border (Not IE8)</p>		
		<p><input type="submit" class="button-primary" style="color: #FFF" value="<?php _e('Save Changes') ?>" /></p>
		</form>
		</div>
		</div>
	<div id="qcf-options">
		
		<h2>Form Preview</h2>
		<p>Note: When you add the form to your site it uses the theme styles so it won't look exactly like the one below.</p>
		<?php
		qcf_theform("options_page");
		if (isset($_POST['submit']))
		qcf_verify("options_page");
		?>
		</div>
		
<?php
}

function qcf_plugin_action_links( $links, $file )
	{
	if ( $file == plugin_basename( __FILE__ ) )
		{
		$qcf_links = '<a href="'.get_admin_url().'options-general.php?page=quick-contact-form/quick-contact-form.php">'.__('Settings').'</a>';
		array_unshift( $links, $qcf_links );
		}
	return $links;
	}

function qcf_theform($options)
	{
	$qcf_options = get_option('qcf_options');
	$width = preg_replace("/[^0-9]/", "", $qcf_options[7]);
	if ($qcf_options[8] == "none") $padding = 0; else $padding = 12;
	if ($options == "options_page")
	{
	$input = $width;
	$submit = $width;
	$textarea = $width;
	}
	else
	{
	$input = $width - $padding;
	$textarea = $width - $padding;
	$submit = $width + 14 - $padding;
	}
	$width = $width.'px';
	$input =  $input.'px';
	$textarea = $textarea.'px';
	$submit = $submit.'px';
	$border = $qcf_options[8];
	?>
	<div id="qcf">
	<div id="<?php echo $border; ?>" style="width: <?php echo $width; ?>">
	<h2><?php echo $qcf_options[0]; ?></h2>
	<p><?php echo $qcf_options[1]; ?></p>
	<form action="" method="POST">
	<div>
	<input type="text"  style="width:<?php echo $input; ?>" label="Name" name="qcfname"  value="<?php echo $qcf_options[2]; ?>" onfocus="clickclear(this, '<?php echo $qcf_options[2]; ?>')" onblur="clickrecall(this,'<?php echo $qcf_options[2]; ?>')"></div>
	<div>
	<input type="text"  style="width:<?php echo $input; ?>" label="Callback" name="qcfcontact"  value="<?php echo $qcf_options[3]; ?>" onfocus="clickclear(this, '<?php echo $qcf_options[3]; ?>')" onblur="clickrecall(this,'<?php echo $qcf_options[3]; ?>')"></div>
	<textarea name="qcfmessage" label="Message" rows="6" style="width:<?php echo $textarea ?>" onFocus="this.value=''; this.onfocus=null;"><?php echo $qcf_options[4] ?></textarea>
	<div><input type="submit" id="submit" style="width:<?php echo $submit; ?>" name="submit" value="<?php echo $qcf_options[5]; ?>">	
	</div>
	</form>
	</div>
	</div>
	<?php
	}

function qcf_verify($options)
	{
	$qcf_options = get_option('qcf_options');
	$form_valid = true;
	$qcf_name = preg_replace("/[^a-zA-Z0-9@.\+\-_\s]/", "", $_REQUEST['qcfname']);
	$qcf_contact = preg_replace("/[^a-zA-Z0-9@.\+\-_\s]/", "", $_REQUEST['qcfcontact']);
	$qcf_message = preg_replace("/[^a-zA-Z0-9@.\+\-_\s]/", "", $_REQUEST['qcfmessage']);
	$qcf_sendmail = $qcf_options[6];
	$ip=$_SERVER['REMOTE_ADDR'];
	$url = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
   	if (empty($qcf_contact) || $qcf_contact== $qcf_options[3]) { $error = "Please enter ".strtolower($qcf_options[3]); $form_valid = false; }
	if (empty($qcf_name) || $qcf_name == $qcf_options[2]) { $error = "Please enter ".strtolower($qcf_options[2]); $form_valid = false; }
	if (empty($qcf_sendmail) && $options == 'options_page') { $error = 'You need to set up an email address first';}
	if ($form_valid)
		{
		$subject = "Enquiry from $qcf_name";
		$headers = "From: $qcf_name <$qcf_contact>\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
   		$headers .= "Content-Type: text/html; charset=\"utf-8\"\r\n";
		$message = "<html><p>$qcf_options[2]: <b>$qcf_name</b></p>";
		$message .= "<p>$qcf_options[3]: <b>$qcf_contact</b></p>";
		$message .= "<p>$qcf_options[4]: <b>$qcf_message</b></p>";
		$message .= "<p>The message was sent from this page: <b>$url</b></p>";
		$message .= "<p>This is the senders IP address: <b>$ip</b></p></html>";
		mail($qcf_sendmail, $subject, $message, $headers);
		echo '<div id="qcf-message"><p>Thank you for your enquiry '.$qcf_name.'</p><p><a href="javascript:history.go(-1)">Click to close</a></p></div>';	
		}
	else
		{
		echo '<div id="qcf-message"><p>'.$error.'</p><p><a href="javascript:history.go(-1)">Return to the  form</a></p></div>';	
		}
	}

function qcf_form()
	{
	
	ob_start();
	qcf_theform("");
	if (isset($_POST['submit']))
	qcf_verify();
	$output_string=ob_get_contents();;
	ob_end_clean();
	return $output_string;
	}
