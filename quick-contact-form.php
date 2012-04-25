<?php
/*
Plugin Name: Quick Contact Form
Plugin URI: http://www.aerin.co.uk/quick-contact-form-plugin
Description: A really, really simple contact form. There is nothing to configure, just add your email address and it's ready to go.
Version: 1.4
Author: fisicx
Author URI: http://www.aerin.co.uk
*/

/*
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

add_shortcode('qcf', 'qcf_loop');
add_action('admin_init', 'qcf_init');
add_action('admin_init', 'qcf_add_defaults');
add_action('admin_menu', 'qcf_add_options_page');
add_filter( 'plugin_action_links', 'qcf_plugin_action_links', 10, 2 );

register_sidebar_widget("Quick Contact Form", "qcf_widget");

register_activation_hook(__FILE__, 'qcf_add_defaults');
/* register_deactivation_hook( __FILE__, 'qcf_delete_options' ); */
register_uninstall_hook(__FILE__, 'qcf_delete_options');

$myStyleUrl = plugins_url('quick-contact-form-style.css', __FILE__);
wp_register_style('qcf_style', $myStyleUrl);
wp_enqueue_style( 'qcf_style');

$myScriptUrl = plugins_url('quick-contact-form-javascript.js', __FILE__);
wp_register_script('qcf_script', $myScriptUrl);
wp_enqueue_script( 'qcf_script');

function qcf_add_defaults()
	{
	$qcf_options = array("Enquiry Form", "Complete the form below and we will be in contact very soon","Your Name", "Email Address","Message", "Send It!","","250","plain","","","","required","required","","","","","","");
	add_option('qcf_options', $qcf_options);
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
	add_options_page('Quick Contact Options Page', 'Quick Contact', 'manage_options', __FILE__, 'qcf_options_page');
	}

function qcf_widget()
	{
	echo qcf_loop();
	}


function qcf_options_page()
	{
	?>
	<div style="margin-left: 14px">
	<h1>The Quick Contact Form</h1>
	<p>This contact form plugin can almost be used straight out the box. All you need to do is add your email address and insert the shortcode into your posts and pages. There are options to edit the labels and captions, select which fields are required, alter the width of the form and change the border style. I've also added an optionals spambot cruncher When you save the changes the updated form will preview on the right.</p>
	<h2>How to use the plugin</h2>
	<p>To add the contact form your posts or pages use the short code: <code>[qcf]</code>.<br />
	To use the form in a text widget add the line <code>add_filter('widget_text', 'do_shortcode');</code> to your functions.php file. You can now use the shortcode <code>[qcf]</code>.<br />
	To add it to your theme files use <code>&lt;?php echo do_shortcode('[qcf]'); ?&gt;</code><br />
	To change the styles use the plugin editor to fiddle with quick-contact-form-styles.css</p>
	<p>Not much else to add really. Enjoy using the plugin</p>
	</div>
	<div id="qcf-options">
	<div id="qcf-style">
	<form method="post" action="options.php">
	<?php
	settings_fields('my_qcf_options');
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
	if ($qcf_options[12] == "required") $checked2 = "checked"; else $checked2 = "";
	if ($qcf_options[13] == "required") $checked3 = "checked"; else $checked3 = "";
	if ($qcf_options[14] == "required") $checked4 = "checked"; else $checked4 = "";
	if ($qcf_options[9] == "required") $checked9 = "checked";
	?>
	<h2>Your email address</h2>	
	<p><span style="color:red; font-weight: bold;">Important!</span> Enter YOUR email address below.  This won't display, it's just so the plugin knows where to send the message.</p>
	<p><input type="text"  style="width:<?php echo $input; ?>;" label="Email" name="qcf_options[6]" value="<?php echo $qcf_options[6]; ?>" /></p>
	<p><input type="submit" class="button-primary" style="color: #FFF" value="<?php _e('Save Changes') ?>" /></p>
	<h2>Form Title and Introductory Blurb</h2>
	<p>Form title (leave blank if you don't want a heading):</p>
	<p><input type="text"  style="width:<?php echo $input; ?>;" name="qcf_options[0]" value="<?php echo $qcf_options[0]; ?>" /></p>
	<p>This is the blurb that will appear below the heading and above the form (leave blank if you don't want any blurb):</p>
	<p><input type="text" style="width:100%" name="qcf_options[1]" value="<?php echo $qcf_options[1]; ?>" /></p>
	<h2>Form fields and captions</h2>
	<p>These can be any questions you like: name, email, telephone, shoe size, favourite colour and so on. Tick the checkbox if the field is required.</p><p>You can change the caption on the submit button as well.</p>
	<p><input type="text" class="<?php echo $qcf_options[12]; ?>" style="width:<?php echo $input; ?>;" name="qcf_options[2]" value="<?php echo $qcf_options[2]; ?>" /></p>
	<p><input type="checkbox" style="margin: 0;" name="qcf_options[12]" <?php echo $checked2; ?> value="required">Required</p>
	<p><input type="text" class="<?php echo $qcf_options[13]; ?>" style="width:<?php echo $input; ?>;" name="qcf_options[3]" value="<?php echo $qcf_options[3]; ?>" /></p>
	<p><input type="checkbox" style="margin: 0;" name="qcf_options[13]" <?php echo $checked3; ?> value="required">Required</p>
	<p><input type="text" class="<?php echo $qcf_options[14]; ?>" style="width:<?php echo $input; ?>;" name="qcf_options[4]" value="<?php echo $qcf_options[4]; ?>" /></p>
	<p><input type="checkbox" style="margin: 0;" name="qcf_options[14]" <?php echo $checked4; ?> value="required">Required</p>
	<p><input type="text" id="submit" style="width:<?php echo $input; ?>; font-size: 130%;cursor:auto; color: #FFF" name="qcf_options[5]" value="<?php echo $qcf_options[5]; ?>" /></p>
	<h2>Spambot Checker</h2>
	<p>Add a maths checker to the form to (hopefully) block most of the spambots</p>
	<p><input type="checkbox" style="margin: 0;" name="qcf_options[9]" <?php echo $checked9; ?> value="required"> Add Spambot blocker</p>
	<h2>Form Width</h2>
	<p>Enter the width of the form in pixels. Just enter the value, no need to add 'px'. The current width is as you see it here.</p>
	<p><input type="text"  style="width:<?php echo $input; ?>;" label="width" name="qcf_options[7]" value="<?php echo $qcf_options[7]; ?>" /></p>
	<h2>Form Border</h2>
	<p>Choose your border style.</p>
	<p>Note: The rounded corners and shadows only work on CSS3 supported browsers and even then not in IE8. Don't blame me, blame Microsoft.</p>
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
	<p>Note: The form uses the theme styles so yours won't look exactly like the one below.</p>
	<?php
	if (isset($_POST['submit']))
	{
	$formvalues = $_POST;
	$formerrors = array();
    if (!qcf_verify_form($formvalues, $formerrors)) qcf_display_form($formvalues, $formerrors, 0);
    else qcf_process_form($formvalues);
	}
	else
	{
	$qcf_options['qcfname2'] = $qcf_options[2];
	$qcf_options['qcfname3'] = $qcf_options[3];
	$qcf_options['qcfname4'] = $qcf_options[4];
	qcf_display_form($qcf_options, null,0);
	}
	?>
	</div>
	<?php
	}

function qcf_plugin_action_links($links, $file )
	{
	if ( $file == plugin_basename( __FILE__ ) )
		{
		$qcf_links = '<a href="'.get_admin_url().'options-general.php?page=quick-contact-form/quick-contact-form.php">'.__('Settings').'</a>';
		array_unshift( $links, $qcf_links );
		}
	return $links;
	}

function qcf_verify_form(&$values, &$errors)
	{
	$qcf_options = get_option('qcf_options');
	if (preg_match('/script/', $values['qcfname2'])) $values['qcfname2'] = $qcf_options[2];
	if (preg_match('/script/', $values['qcfname3'])) $values['qcfname3'] = $qcf_options[3];
	if (preg_match('/script/', $values['qcfname4'])) $values['qcfname4'] = $qcf_options[4];
	if (preg_match('/http/', $values['qcfname2'])) $values['qcfname2'] = $qcf_options[2];
	if (preg_match('/http/', $values['qcfname3'])) $values['qcfname3'] = $qcf_options[3];
	if (preg_match('/http/', $values['qcfname4'])) $values['qcfname4'] = $qcf_options[4];
	if (preg_match('/000/', $values['qcfname3'])) $values['qcfname3'] = $qcf_options[3];

	$values['qcfname2'] = preg_replace("/[^a-zA-Z0-9.\s]/", "", $values['qcfname2']);
	$values['qcfname3'] = preg_replace("/[^a-zA-Z0-9@_()\.\+-\s]/", "", $values['qcfname3']);
	$values['qcfname4'] = preg_replace("/[^a-zA-Z0-9.,\?\s]/", "", $values['qcfname4']);
	
	if (($qcf_options[12] == "required") && ((empty($values['qcfname2']) || $values['qcfname2'] == $qcf_options[2])))
		$errors['qcfname2'] = 'Giving me '.strtolower($qcf_options[2]).' would really help';

	if ($qcf_options[13] == "required")
		{
		if (strpos(strtolower($qcf_options[3]),'phone') && preg_match("/[^0-9()\+-\s]$/",$values['qcfname3']))
			$errors['qcfname3'] = 'Please enter a valid phone number';
		if (strpos($qcf_options[3],'mail') && !filter_var($values['qcfname3'], FILTER_VALIDATE_EMAIL))
			$errors['qcfname3'] = 'Please enter a valid email address';
		if (empty($values['qcfname3']) || $values['qcfname3'] == $qcf_options[3])
			$errors['qcfname3'] = 'The '.strtolower($qcf_options[3]).' is needed';
		}

	if (($qcf_options[14] == "required") && ((empty($values['qcfname4']) || $values['qcfname4'] == $qcf_options[4]))) 
		$errors['qcfname4'] = 'What is the '.strtolower($qcf_options[4]).'?';

	if($qcf_options[9] == 'required') {
		if($values['qcfname9']<>7)
       		$errors['qcfname9'] = 'That&#146;s not the right answer, try again';
		if(empty($values['qcfname9']))
			$errors['qcfname9'] = 'Can you give me an answer to the sum please';
		}

	return (count($errors) == 0);	
	}

function qcf_display_form($values, $errors, $whichpage)
	{
	$qcf_options = get_option('qcf_options');
	$width = preg_replace("/[^0-9]/", "", $qcf_options[7]);
	if ($qcf_options[8] == "none") $padding = 0; else $padding = 12;
	$input = $width - $padding;
	$textarea = $width - $padding;
	$submit = $width + $whichpage - $padding;
	$width = $width.'px';
	$input =  $input.'px';
	$textarea = $textarea.'px';
	$submit = $submit.'px';
	$border = $qcf_options[8];
	?>
	<div id="qcf-style">
	<div id="<?php echo $border; ?>" style="width: <?php echo $width; ?>">
	<?php
	if (count($errors) > 0)
		echo "<h2>Oops, got a few problems here</h2><p class='error'>Can you sort out the details highlighted below.</p>";
	else
		echo '<h2>'.$qcf_options[0].'</h2><p>'. $qcf_options[1].'</p>';
	?>
	<form action="" method="POST">
	<p class="error"><?= $errors['qcfname2'] ?></p>
	<p><input type="text" class="<?php echo $qcf_options[12]; ?>" style="width:<?php echo $input; ?>" label="Name" name="qcfname2"  value="<?php echo $values['qcfname2']; ?>" onfocus="clickclear(this, '<?php echo $values['qcfname2']; ?>')" onblur="clickrecall(this,'<?php echo $values['qcfname2']; ?>')"></p>
	<p class="error"><?= $errors['qcfname3'] ?></p>
	<p><input type="text" class="<?php echo $qcf_options[13]; ?>" style="width:<?php echo $input; ?>" label="Contact" name="qcfname3"  value="<?php echo $values['qcfname3']; ?>" onfocus="clickclear(this, '<?php echo $values['qcfname3']; ?>')" onblur="clickrecall(this,'<?php echo $values['qcfname3']; ?>')"></p>
	<p class="error"><?= $errors['qcfname4'] ?></p>
	<p><textarea  class="<?php echo $qcf_options[14]; ?>" name="qcfname4" label="Message" rows="6" style="width:<?php echo $textarea ?>" onFocus="this.value=''; this.onfocus=null;"><?php echo $values['qcfname4'] ?></textarea></p>
	<?php
	if ($qcf_options[9] == 'required'){
	?>
	<p class="error"><?= $errors['qcfname9'] ?></p>
	<p id="sums">What is 3 + 4? <input type="text" class="required" style="width:30px" label="Sum" name="qcfname9"  value="<?php echo $values['qcfname9']; ?>"></p>  
	<?php }; ?>
	<p><input type="submit" id="submit" style="width:<?php echo $submit; ?>" name="submit" value="<?php echo $qcf_options[5]; ?>">	
	</form>
	</div></div>
	<?php
	}

function qcf_process_form($values)
	{
	$qcf_options = get_option('qcf_options');
	$qcf_sendmail = $qcf_options[6];
	$ip=$_SERVER['REMOTE_ADDR'];
	$url = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
	$subject = "Enquiry from {$values['qcfname2']}";
	$headers = "From: {$values['qcfname2']}<{$values['qcfname3']}>\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
   	$headers .= "Content-Type: text/html; charset=\"utf-8\"\r\n"; 
	$message = "<html><h2>The message was:</h2>";
	$message .= "<p><b>{$qcf_options[2]}: </b>{$values['qcfname2']}</p>";
	$message .= "<p><b>{$qcf_options[3]}: </b>{$values['qcfname3']}</p>";
	$message .= "<p><b>{$qcf_options[4]}: </b>{$values['qcfname4']}</p>";
	$message .= "<p>The message was sent from this page: <b>$url</b></p>";
	$message .= "<p>This is the senders IP address: <b>$ip</b></p></html>";
	$message .= "</html>"; 

	mail($qcf_sendmail, $subject, $message, $headers);

	echo '<div id="qcf-style"><div id="'.$qcf_options[8].'" style="width:'.$qcf_options[7].'px;">
	<h2>Message Sent!</h2>
	<p>Thank you for your enquiry. I&#146;ll be in contact soon</p>
	<p>The details you sent me were:</p>
	<p><b>'.$qcf_options[2].': </b><br />'.$values['qcfname2'].'</p>
	<p><b>'.$qcf_options[3].': </b><br />'.$values['qcfname3'].'</p>
	<p><b>'.$qcf_options[4].': </b><br />'.$values['qcfname4'].'</p></div></div>';  	
	}

function qcf_loop()
	{
	ob_start();	
	if (isset($_POST['submit']))
		{
		$formvalues = $_POST;
		$formerrors = array();
		if (!qcf_verify_form($formvalues, $formerrors)) qcf_display_form($formvalues, $formerrors, 14);
		else qcf_process_form($formvalues);
		}
	else
		{
		$qcf_options = get_option('qcf_options');
		$qcf_options['qcfname2'] = $qcf_options[2];
		$qcf_options['qcfname3'] = $qcf_options[3];
		$qcf_options['qcfname4'] = $qcf_options[4];
		qcf_display_form($qcf_options, null,14);
		}
	$output_string=ob_get_contents();;
	ob_end_clean();
	return $output_string;
	}