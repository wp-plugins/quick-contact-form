<?php
/*
Plugin Name: Quick Contact Form
Plugin URI: http://www.aerin.co.uk/quick-contact-form-plugin
Description: A really, really simple contact form. There is nothing to configure, just add your email address and it's ready to go.
Version: 2.0
Author: fisicx
Author URI: http://www.aerin.co.uk
*/

/*
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

add_shortcode('qcf', 'qcf_loop');
add_action('admin_init', 'qcf_init');
add_action('admin_init', 'qcf_add_defaults');
add_action('admin_menu', 'qcf_options_page_init');
add_filter( 'plugin_action_links', 'qcf_plugin_action_links', 10, 2 );
add_action('wp_dashboard_setup', 'qcf_add_dashboard_widgets' );

register_activation_hook(__FILE__, 'qcf_add_defaults');
register_deactivation_hook( __FILE__, 'qcf_delete_options' );
register_uninstall_hook(__FILE__, 'qcf_delete_options');

$myStyleUrl = plugins_url('quick-contact-form-style.css', __FILE__);
wp_register_style('qcf_style', $myStyleUrl);
wp_enqueue_style( 'qcf_style');

$myScriptUrl = plugins_url('quick-contact-form-javascript.js', __FILE__);
wp_register_script('qcf_script', $myScriptUrl);
wp_enqueue_script( 'qcf_script');

function qcf_options_page_init()
	{
	add_options_page('Quick Contact', 'Quick Contact', 'manage_options', __FILE__, 'qcf_tabbed_page');
	}

function qcf_add_defaults()
	{
	$qcf_options = array("Enquiry Form", "Complete the form below and we will be in contact very soon","Your Name", "Email Address","Message", "Send It!","","250","plain","","","","required","required","","","","","","");
	add_option('qcf_options', $qcf_options);
	$qcf_messages = array(
	array('name' => 'Test', 'contact' => 'test', 'message' => 'This is a test message', 'date' => '01 Jan 1970',),
	);
	add_option('qcf_messages', $qcf_messages);
	}

function qcf_delete_options()
	{
	delete_option('qcf_options');
	delete_option('qcf_messages');
	}

function qcf_init()
	{
	register_setting('my_qcf_options', 'qcf_options');
	}

function qcf_setup_page()
	{
	?>
	<div id="qcf-options">
	<div id="qcf-style">
	<form method="post" action="options.php">
	<?php
	settings_fields('my_qcf_options');
	$qcf_options = get_option('qcf_options');
	$width = preg_replace("/[^0-9]/", "", $qcf_options[7]);
	$input = $width.'px';
	?>
	<h2>Setting up the plugin</h2>	
	<p><span style="color:red; font-weight: bold;">Important!</span> Enter YOUR email address below and save the changes.  This won't display, it's just so the plugin knows where to send the message.</p>
	<p><input type="text" style="width:100%" label="Email" name="qcf_options[6]" value="<?php echo $qcf_options[6]; ?>" /></p>
	<p><input type="submit" class="button-primary" style="color: #FFF" value="<?php _e('Save Changes') ?>" /></p>
	<h2>Adding the contact form to your site</h2>
	<p>To add the contact form your posts or pages use the short code: <code>[qcf]</code>.<br />
	<p>To add it to your theme files use <code>&lt;?php echo do_shortcode('[qcf]'); ?&gt;</code></p>
	<p>There is also a widget called 'Quick Contact Form' you can drag and drop into your sidebar.</p>	
	<p>That's it.  The plugin is ready to use.</p>	
	</div>
	</div>
	<?php
	}

function qcf_options_page()
	{
	?>
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
	if ($qcf_options[10] == "yes") $checked10 = "checked";
	?>
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
	<h2>Dashboard Widget</h2>
	<p>Displays most recent messages on your dashboard</p>
	<p><input type="checkbox" style="margin: 0;" name="qcf_options[10]" <?php echo $checked10; ?> value="yes"> Add latest messages to dashboard</p>
<p><input type="submit" class="button-primary" style="color: #FFF" value="<?php _e('Save Changes') ?>" /></p>
	</form>
	</div>
	</div>
	<div id="qcf-options">
	<h2>Form Preview</h2>
	<p>Note: The preview form uses the wordpress admin styles. Your form will use the theme styles so won't look exactly like the one below.</p>
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
		for ($i=0; $i<=20; $i++) { $qcf_options['qcfname'.$i] = $qcf_options[$i]; }
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
	for ($i=0; $i<=15; $i++) {$values['qcfname'.$i] = strip_tags($values['qcfname'.$i]);}

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

	if($qcf_options[9] == 'required')
		{
		if($values['qcfname11']<>7)
       		$errors['qcfname11'] = 'That&#146;s not the right answer, try again';
		if(empty($values['qcfname11']))
    		$errors['qcfname11'] = 'Can you give me an answer to the sum please';
		}
	return (count($errors) == 0);	
	}

function qcf_display_form($values, $errors, $whichpage)
	{
	$qcf_options = get_option('qcf_options');
	$qcf_messages = get_option('qcf_messages');
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
	<p class="error"><?= $errors['qcfname11'] ?></p>
	<p id="sums">What is 3 + 4? <input type="text" class="required" style="width:30px" label="Sum" name="qcfname11"  value="<?php echo $values['qcfname11']; ?>"></p>  
	<?php }; ?>
	<p><input type="submit" id="submit" style="width:<?php echo $submit; ?>" name="submit" value="<?php echo $qcf_options[5]; ?>">	
	</form>
	</div>
	</div>
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

	echo '<div id="qcf-style">
	<div id="'.$qcf_options[8].'" style="width:'.$qcf_options[7].'px;">
	<h2>Message Sent!</h2>
	<p>Thank you for your enquiry. I&#146;ll be in contact soon</p>
	<p>The details you sent me were:</p>
	<p><b>'.$qcf_options[2].': </b><br />'.$values['qcfname2'].'</p>
	<p><b>'.$qcf_options[3].': </b><br />'.$values['qcfname3'].'</p>
	<p><b>'.$qcf_options[4].': </b><br />'.$values['qcfname4'].'</p>
	</div>
	</div>';  	
	
	$qcf_messages = get_option('qcf_messages');
	$sentdate = date('d M Y');
	$qcf_messages[] = array(name => $values['qcfname2'], contact => $values['qcfname3'], message => $values['qcfname4'],date => $sentdate,);
	update_option('qcf_messages',$qcf_messages);
}

function qcf_show_messages()
	{
	$messages = get_option('qcf_messages');
	usort($messages, function($a1, $a2) {
		$v1 = strtotime($a1['date']);
		$v2 = strtotime($a2['date']);
   		return $v2 - $v1; // $v2 - $v1 to reverse direction
	});
	echo '<div id="qcf-options" style="width:90%;">
	<div id="qcf-style">
	<h2>Message List</h2>
	<p>Lists the messages received since the plugin was activated.</p>
	<table cellspacing="0">
	<tr><td><b>From</b</td><td><b>&nbsp;</b</td><td><b>Message</b</td><td><b>Date</b</td></tr>';
	foreach($messages as $value)
		{
		echo '<tr>';
		foreach($value as $item)
			{
			echo '<td>'.$item.'</td>';
			}
		echo '</tr>';
 		}
		echo '</table></div></div>';
	}

function qcf_help()
	{
	?>
	<div id="qcf-options" style="width:90%;">
	<div id="qcf-style">
	<h2>Introduction</h2>
	<p>This contact form plugin can almost be used straight out the box. All you need to do is add your email address and insert the shortcode into your posts and pages. There are options to edit the labels and captions, select which fields are required, alter the width of the form and change the border style. I've also added an optionals spambot cruncher When you save the changes the updated form will preview on the right.</p>
	<h2>Installing the plugin, FAQs and other info</h2>
	<p>Everything you need to know is on the <a href="http://wordpress.org/extend/plugins/quick-contact-form/installation/" target="_blank">wordpress plugin page</a>.  If you have a question or want to offer a suggestion then use the form on <a href="http://aerin.co.uk/quick-contact-form/">my plugin page</a>.</p>
	<h2>Validation</h2>
	<p>I've spent quite some time trying to make the validation as usable as possible but keep the configuration as simple as possible. I think I've captured most conditions but there might be the odd omission if you set up a weird label.</p>
	<p>Anyway, here's how it works.  If you tick the 'required' checkbox the field will get validated.</p>
	<p>The first validation removes all the unwanted characters. Essentially this clears out the sort of thing a spammer would use: URLs, HTML and so on leaving just the alphabet, numbers and a few punctuation marks.</p>
	<p>The second validation checks that the field isn't empty or that the user has actually typed something in the box. The error message suggests that they need to enter &lt;something&gt; where something is the info you need (name, email, phone number, colour etc).</p>
	<p>The third validation checks that it is a valid phone number (no letters) or valid email but only if the word 'phone' or 'mail' are used in the field label. This is the flaky bit. If you don't have these characters in the field label then the check doesn't get carried out.  Let me know if you need  different words or an alternate validation check. The error messages you may see are:</p>
	<p>Giving me &lt;field 1 label&gt; would really help<br /> The &lt;field 2 label&gt; is needed<br /> What is the &lt;field 3 label&gt;<br /> Please enter a valid phone number<br /> Something's not right with your email address</p>
	<h2>Styling</h2>
	<p>Everything is wrapped in the <code>qcf-style</code> id. Most of the styles are standard HTML tags. The additional styles are the 5 border options, the required fields and error messages.</p>
	<p>The colours I've used are the result of a whole bunch of tests. Many of them over at <a href="http://usabilityhub.com" target="blank">usabilityhub.com</a>. There are 6 main colours: form border - #888, field colour - #465069, normal field border - #415063, required field border - #00C618, errors - #D31900. The submit button is #343838 with white text.</p>
	<h2>Options</h2>
	<p>The options used are held in the <code>qcf-options</code> array. The current keys are:</p> 
	<pre>&nbsp;0	Title
&nbsp;1	Introduction
&nbsp;2	Field 1 label
&nbsp;3	Field 2 label
&nbsp;4	Field 3 label
&nbsp;5	Submit button caption
&nbsp;6	Email Address
&nbsp;7	Width
&nbsp;8	Border
&nbsp;9	Maths checker required
10	Dashboard widget
11	Spare
12	Field 1 required
13	Field 2 required
14	Field 3 required</pre>
	</div>
	</div>
	<?php
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
		for ($i=0; $i<=20; $i++) { $qcf_options['qcfname'.$i] = $qcf_options[$i]; }
		qcf_display_form($qcf_options, null,14);
		}
	$output_string=ob_get_contents();;
	ob_end_clean();
	return $output_string;
	}

function qcf_admin_tabs( $current = 'settings' )
	{ 
	$tabs = array( 'setup' => 'Setup', 'options' => 'Options', 'messages' => 'Messages', 'support' => 'Support' ); 
	$links = array();
	echo '<div id="icon-themes" class="icon32"><br></div>';
	echo '<h2 class="nav-tab-wrapper">';
	foreach( $tabs as $tab => $name )
		{
		$class = ( $tab == $current ) ? ' nav-tab-active' : '';
		echo "<a class='nav-tab$class' href='?page=quick-contact-form/quick-contact-form.php&tab=$tab'>$name</a>";
		}
	echo '</h2>';
	}

function qcf_tabbed_page()
	{
	?>
	<div class="wrap">
	<h1>Quick Contact Form</h1>
	<?php
	if ( isset ( $_GET['tab'] ) ) qcf_admin_tabs($_GET['tab']); else qcf_admin_tabs('setup');
	if ( isset ( $_GET['tab'] ) ) $tab = $_GET['tab']; 
	else $tab = 'setup'; 
	switch ( $tab )
		{
		case 'messages' : qcf_show_messages();
		break; 
		case 'support' : qcf_help();
		break;
		case 'options' : qcf_options_page();
		break;
		case 'setup' : qcf_setup_page();
		break;
		}
	?>			
	</div>
	<?php
	}


class qcf_widget extends WP_Widget
	{
	function qcf_widget()
		{
		$widget_ops = array('classname' => 'qcf_widget', 'description' => 'Add the Quick Contact Form to your sidebar');
		$this->WP_Widget('qcf_widget', 'Quick Contact Form', $widget_ops);
		}

	function form($instance)
		{
		echo '<p>All options for the quick contact form are changed on the plugin <a href="'.get_admin_url().'options-general.php?page=quick-contact-form/quick-contact-form.php">Settings</a> page.</p>';
		}

	function update($new_instance, $old_instance)
		{
		$instance = $old_instance;
		$instance['email'] = $new_instance['email'];
		return $instance;
		}
 
	function widget($args, $instance)
		{
 	   	extract($args, EXTR_SKIP);
		echo qcf_loop();
		}
	}

add_action( 'widgets_init', create_function('', 'return register_widget("qcf_widget");') );

function qcf_dashboard_widget() 
	{
	$messages = get_option('qcf_messages');
	usort($messages, function($a1, $a2) {
		$v1 = strtotime($a1['date']);
		$v2 = strtotime($a2['date']);
   		return $v2 - $v1; // $v2 - $v1 to reverse direction
	});
	echo '<div id="qcf-widget"><table cellspacing="0">
	<tr><td><b>From</b</td><td><b>&nbsp;</b</td><td><b>Message</b</td><td><b>Date</b</td></tr>';
	foreach($messages as $value)
		{
		echo '<tr>';
		foreach($value as $item)
			{
			echo '<td>'.$item.'</td>';
			}
		echo '</tr>';
 		}
		echo '</table></div>';
	}
	
function qcf_add_dashboard_widgets() {
	$qcf_options = get_option('qcf_options');
	if ($qcf_options[10] == 'yes')
		{
		wp_add_dashboard_widget('qcf_dashboard_widget', 'Quick Contact Form Messages', 'qcf_dashboard_widget');	
		}
	}