<?php
/*
Plugin Name: Quick Contact Form
Plugin URI: http://www.aerin.co.uk/quick-contact-form-plugin
Description: A really, really simple contact form. There is nothing to configure, just add your email address and it's ready to go.
Version: 3.0
Author: fisicx
Author URI: http://www.aerin.co.uk
*/

add_action( 'admin_notices', 'qcf_admin_notice' );
add_action( 'wp_dashboard_setup', 'qcf_add_dashboard_widgets' );
add_shortcode('qcf', 'qcf_loop');
add_action('init', 'qcf_init');
add_action('admin_menu', 'qcf_page_init');
add_filter( 'plugin_action_links', 'qcf_plugin_action_links', 10, 2 );
add_action('wp_dashboard_setup', 'qcf_add_dashboard_widgets' );

/* register_deactivation_hook( __FILE__, 'qcf_delete_options' ); */
register_uninstall_hook(__FILE__, 'qcf_delete_options');

$myScriptUrl = plugins_url('quick-contact-form-javascript.js', __FILE__);
wp_register_script('qcf_script', $myScriptUrl);
wp_enqueue_script( 'qcf_script');

$myStyleUrl = plugins_url('quick-contact-form-style.css', __FILE__);
wp_register_style('qcf_style', $myStyleUrl);
wp_enqueue_style( 'qcf_style');

function qcf_page_init()
	{
	add_options_page('Quick Contact', 'Quick Contact', 'manage_options', __FILE__, 'qcf_tabbed_page');
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

function qcf_delete_options()
	{
	delete_option('qcf_settings');
	delete_option('qcf_options');
	delete_option('qcf_reply');
	delete_option('qcf_error');
	delete_option('qcf_email');
	delete_option('qcf_messages');
	}

function qcf_admin_tabs( $current = 'settings' )
	{ 
	$tabs = array( 'setup' => 'Setup' , 'settings' => 'Form Settings', 'error' => 'Error Messages' , 'reply' => 'Reply Options' , 'help' => 'Help' , 'reset' => 'Reset' , ); 
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
		case 'setup' : qcf_setup();
		break;
		case 'settings' : qcf_form_options();
		break;
		case 'reply' : qcf_reply_page();
		break;
		case 'error' : qcf_error_page ();
		break;
		case 'help' : qcf_help ();
		break;
		case 'reset' : qcf_reset_page();
		break;
		}
	?>
	</div>
	<?php
	}

function qcf_admin_notice( $message)
	{
	if (!empty( $message)) echo '<div class="updated"><p>'.$message.'</p></div>';
	}

function qcf_setup ()
	{
	if( isset( $_POST['Submit'])) {
		$qcf_email = esc_html( $_POST['qcf_email']);
		update_option( 'qcf_email', $qcf_email);
		qcf_admin_notice("The email address has been updated.");
		}
	$qcf_email = get_option('qcf_email');
	?>
	<div id="qcf-options"> 
		<div id="qcf-style"> 
    	<form method="post" action="">
		<h2>Setting up the Quick Contact Form</h2>
		<p><span style="color:red; font-weight: bold;">Important!</span> Enter YOUR email address below and save the changes. This won't display, it's just so the plugin knows where to send the message.</p>
		<p>To send to muliple addresses, put a comma betweeen each address.</p>
		<p><input type="text" style="width:100%" label="Email" name="qcf_email" value="<?php echo $qcf_email; ?>" /></p>
		<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /></p>
	<h2>If you have upgraded</h2>
	<p>The way the plugin writes to the database has totally changed. Please check your settings to make sure nothing has got mussed up.</p>
	<p>Your dashboard messages will have gone.  Sorry but it was just too complicated and buggy to get them transferred.</p>
	</form>
	</div>
	</div>
	<div id="qcf-options"> 
	<div id="qcf-style"> 
	<h2>Form Settings</h2>
	<p>To change the layout of the form, add or remove fields, change the order they appear and edit the labels and captions use the <a href='?page=quick-contact-form/quick-contact-form.php&tab=settings'>Form Setting</a> tab.</p>
	<p>Use the <a href='?page=quick-contact-form/quick-contact-form.php&tab=reply'>Reply Options</a> tab to change the thank you message and send options.</p>
	<p>You can also customise the <a href='?page=quick-contact-form/quick-contact-form.php&tab=error'>error messages</a>.</p>
	<p>If it all goes wrong you can <a href='?page=quick-contact-form/quick-contact-form.php&tab=reset'>reset</a> everything.
	<h2>Adding the contact form to your site</h2>
	<p>To add the contact form your posts or pages use the short code: <code>[qcf]</code>.<br />
	<p>To add it to your theme files use <code>&lt;?php echo do_shortcode('[qcf]'); ?&gt;</code></p>
	<p>There is also a widget called 'Quick Contact Form' you can drag and drop into your sidebar.</p>
	<p>That's it. The form is ready to use.</p>
	</div>
	</div>
	<?php
	}

function qcf_form_options () {
	$option_name = 'qcf_settings';
	$active_buttons = array( 'field1' , 'field2' , 'field3' , 'field4' , 'field5' , 'field6' ,  'field7' , 'field8' ,  'field9' ,);	
	if( isset( $_POST['Submit']))
		{
		foreach ( $active_buttons as $item)
			{
			$qcf['active_buttons'][$item] = (isset( $_POST['qcf_settings_active_'.$item]) and $_POST['qcf_settings_active_'.$item] == 'on' ) ? true : false;
			$qcf['required'][$item] = (isset( $_POST['required_'.$item]) );
			if (!empty ( $_POST['label_'.$item])) $qcf['label'][$item] = $_POST['label_'.$item];
			}
		$qcf['sort'] = esc_html( $_POST['qcf_settings_sort']);
		$qcf['dropdownlist'] = esc_html( $_POST['dropdown_string']);
		$qcf['checklist'] = esc_html( $_POST['checklist_string']);
		$qcf['radiolist'] = esc_html( $_POST['radio_string']);
		$qcf['lines'] = esc_html( $_POST['message_lines']);
		$qcf['title'] = esc_html( $_POST['qcfname_title']);
		$qcf['blurb'] = esc_html( $_POST['qcfname_blurb']);
		$qcf['border'] = esc_html( $_POST['border']);
		$qcf['width'] = esc_html( $_POST['width']);
		$qcf['captcha'] = esc_html( $_POST['captcha']);
		$qcf['send'] = esc_html( $_POST['sendcaption']);
		$qcf['update'] = 'updated';
		update_option( 'qcf_settings', $qcf);
		qcf_admin_notice("The form settings have been updated.");
		}
		$qcf = qcf_get_stored_options();
		if ( $qcf['border'] == "shadow") $shadow = "checked"; 
		if ( $qcf['border'] == "roundshadow") $roundshadow = "checked"; 
		if ( $qcf['border'] == "plain") $plain = "checked"; 
		if ( $qcf['border'] == "rounded") $rounded = "checked"; 
		if ( $qcf['border'] == "none") $none = "checked";
		$qcf_options = get_option('qcf_options');
		$out = '
		<script>
		jQuery(function() {
			var qcf_sort = jQuery( "#qcf_sort" ).sortable({ axis: "y" ,
			update:function(e,ui) {
				var order = qcf_sort.sortable("toArray").join();
				jQuery("#qcf_settings_sort").val(order);
				}
			});
		});
		</script>
		<div id="qcf-options">
		<div id ="qcf-style">
		<form id="qcf_settings_form" method="post" action="">
		<h2>Form Title and Introductory Blurb</h2>
		<p>Form title (leave blank if you don&#146;t want a heading):</p>
		<p><input type="text" style="width:100%" name="qcfname_title" value="' . $qcf['title'] . '" /></p>
		<p>This is the blurb that will appear below the heading and above the form (leave blank if you don&#146;t want any blurb):</p>
		<p><input type="text" style="width:100%" name="qcfname_blurb" value="' . $qcf['blurb'] . '" /></p>
		<h2>Form Fields</h2>
		<p>Check to activate the field. Drag&Drop to change the order they appear on your form.</p>
		<ul id="qcf_sort">';
			foreach (explode( ',',$qcf['sort']) as $name) {
				$checked = ( $qcf['active_buttons'][$name]) ? 'checked' : '';
				$required = ( $qcf['required'][$name]) ? 'checked' : '';
				$lines = $qcf['lines'];
				$options = '';
				switch ( $name ) {
					case 'field1':
						$type = 'Text';
						break;
					case 'field2':
						$type = 'Email';
						$options = 'and validates email';
						break;
					case 'field3':
						$type = 'Telephone';
						$options = 'and checks format';
						break;	
					case 'field4':
						$type = 'Textarea';
						$options = 'and <input type="text" style="border:1px solid #415063; width:1.5em; padding: 1px; margin:0;" name="message_lines" . value ="' . $lines . '" /> rows';
						break;	
					case 'field5':
						$type = 'Dropdown';
						$options = 'and see below';
						break;
					case 'field6':
						$type = 'Checkboxes';
						$options = 'and see below';
						break;
					case 'field7':
						$type = 'Radio buttons';
						$options = 'and see below';
						break;
					case 'field8':
						$type = 'Text';
						break;
					case 'field9':
						$type = 'Text';
						break;
						}
			$li_class = ( $checked) ? 'button_active' : 'button_inactive';
			$out .= '<li class="ui-state-default '.$li_class.'" id="'.$name.'">
				<div style="float:left; width:110px;">
				<input type="checkbox" class="button_activate" style="border: none; padding: 0; margin:0;" name="qcf_settings_active_'.$name.'" '.$checked.' />' . $type . '</div>
				<div style="float:left; width:160px;">
				<input type="text" style="border: border:1px solid #415063; width:150px; padding: 1px; margin:0;" name="label_'.$name.'" value="' . $qcf['label'][$name] . '"/>
				</div>
				<div style="float:left;">
				<input type="checkbox" class="button_activate" style="border: none; padding: 0; margin:0;" name="required_'.$name.'" '.$required.' /> Required ' . $options . '</div>
				</li>';
			}
			$out .= '
				</ul>
				<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /></p>
				<input type="hidden" id="qcf_settings_sort" name="qcf_settings_sort" value="'.stripslashes( $qcf['sort']).'" />
				<div style="clear:left"></div>
				<h2>Dropdown options</h2>
				<p>Separate each option with a comma. Don&#146;t use a space after the comma!</p>
				<p><textarea  name="dropdown_string" label="Dropdown" rows="2" style="width:100%">' . $qcf['dropdownlist'] . '</textarea></p>
				<h2>Checklist button options</h2>
				<p>Separate each checkbox label with a comma. Don&#146;t use a space after the comma!</p>
				<p><textarea  name="checklist_string" label="Checklist" rows="2" style="width:100%">' . $qcf['checklist'] . '</textarea></p>
				<h2>Radio button options</h2>
				<p>Separate each radio button option with a comma. Don&#146;t use a space after the comma!</p>
				<p><textarea  name="radio_string" label="Radio" rows="2" style="width:100%">' . $qcf['radiolist'] . '</textarea></p>
				<h2>Submit Button</h2>
				<p><input type="text" id="submit" style="width:100%; font-size: 130%;cursor:auto; color: #FFF" name="sendcaption" value="' . $qcf['send'] . '" /></p>
				<h2>Form Width</h2>
				<p>Enter the width of the form in pixels. Just enter the value, no need to add &#146;px&#146;. The current width is as you see it here.</p>
				<p><input type="text" style="width:4em" label="width" name="width" value="' . $qcf['width'] . '" /></p>
				<h2>Spambot Checker</h2>
				<p>Add a maths checker to the form to (hopefully) block most of the spambots.</p>
				<p><input type="checkbox" style="border: none; padding: 0; margin:0;" name="captcha"' . $qcf['captcha'] . ' value="checked" /> Add Spambot blocker</p>
				<h2>Form Border</h2>
				<p>Choose your border style.</p>
				<p>Note: The rounded corners and shadows only work on CSS3 supported browsers and even then not in IE8. Don&#146;t blame me, blame Microsoft.</p>
				<p>
				<input style="margin: 0; padding: 0; border: none;" type="radio" name="border" value="none"' . $none . ' /> No border<br />
				<input style="margin: 0; padding: 0; border: none;" type="radio" name="border" value="plain"' . $plain . ' /> Plain Border<br />
				<input style="margin: 0; padding: 0; border: none;" type="radio" name="border" value="rounded"' . $rounded . ' /> Round Corners (Not IE8)<br />
				<input style="margin: 0; padding: 0; border: none;" type="radio" name="border" value="shadow"' . $shadow . ' /> Shadowed Border(Not IE8)<br />
				<input style="margin: 0; padding: 0; border: none;" type="radio" name="border" value="roundshadow"' . $roundshadow . ' /> Rounded Shadowed Border (Not IE8)</p>
				<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /></p>
				</form>
			</div>
		</div>';
		echo $out;
		?>
		<div id="qcf-options"> 
		<h2>Form Preview</h2>
		<p>Note: The preview form uses the wordpress admin styles. Your form will use the theme styles so won't look exactly like the one below.</p>
		<?php
		form_preview ();
		?>
		</div>
		<?php
		}

function qcf_reply_page()
	{
	$qcf = qcf_get_stored_options();
	$reply = qcf_get_stored_reply();
	if( isset( $_POST['Submit']))
		{
		$reply['replytitle'] = esc_html( $_POST['replytitle']);
		$reply['replyblurb'] = esc_html( $_POST['replyblurb']);
		$reply['messages'] = esc_html( $_POST['qcf_showmessage']);
		$reply['dashboard'] = esc_html( $_POST['qcf_dashboard']);
		$reply['tracker'] = esc_html( $_POST['qcf_tracker']);
		$reply['update'] = 'updated';
		update_option( 'qcf_reply', $reply);
		qcf_admin_notice("The reply settings have been updated.");
		}
	$width = preg_replace("/[^0-9]/", "", $qcf['width']);
	$width = $width.'px';
	$input = $width;
	$submit = $width;
	$textarea = $width;
	?>
	<div id="qcf-options"> 
	<div id="qcf-style"> 
	<form method="post" action="">
	<h2>Thank you message</h2>
	<p>Thank you header (leave blank if you don't want a heading):</p>
	<p><input type="text"  style="width:100%" name="replytitle" value="<?php echo $reply['replytitle']; ?>" /></p>
	<p>This is the blurb that will appear below the thank you heading and above the actual message (leave blank if you don't want any blurb):</p>
	<p><input type="text" style="width:100%" name="replyblurb" value="<?php echo $reply['replyblurb']; ?>" /></p>
	<h2>Show message content</h2>
	<p>Show the sender the content of their message.</p>
	<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="qcf_showmessage" <?php echo $reply['messages']; ?> value="checked"> Show message content</p>
	<h2>Dashboard Widget</h2>
	<p>Displays most recent messages on your dashboard</p>
	<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="qcf_dashboard" <?php echo $reply['dashboard']; ?> value="checked"> Add latest messages to dashboard</p>
	<h2>Tracking</h2>
	<p>Add the IP address and the current page title to the message you receive. The sender doesn't see this information.</p>
	<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="qcf_tracker" <?php echo $reply['tracker']; ?> value="checked"> Show tracking info</p>
	<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /></p>
	</form>
	</div>
	</div>
	<div id="qcf-options"> 
	<h2>Test Form</h2>
	<p>Use the form below to test your thank-you message settings.</p>
	<?php
	form_preview ();
	?>
	</div>
	<?php
	}

function qcf_error_page()
	{
	$error = qcf_get_stored_error();
	$qcf = qcf_get_stored_options();
	if( isset( $_POST['Submit']))
		{
		$error['errortitle'] = esc_html( $_POST['errortitle']);
		$error['errorblurb'] = esc_html( $_POST['errorblurb']);
		$error['field1'] = esc_html( $_POST['error1']);
		$error['field2'] = esc_html( $_POST['error2']);
		$error['field3'] = esc_html( $_POST['error3']);
		$error['field4'] = esc_html( $_POST['error4']);
		$error['field5'] = esc_html( $_POST['error5']);
		$error['field6'] = esc_html( $_POST['error6']);
		$error['field7'] = esc_html( $_POST['error7']);
		$error['field8'] = esc_html( $_POST['error8']);
		$error['field9'] = esc_html( $_POST['error9']);
		$error['email'] = esc_html( $_POST['email']);
		$error['telephone'] = esc_html( $_POST['telephone']);
		$error['mathsmissing'] = esc_html( $_POST['errorsum1']);
		$error['mathsanswer'] = esc_html( $_POST['errorsum2']);
		$error['update'] = 'updated';
		update_option( 'qcf_error', $error);
		qcf_admin_notice("The reply settings have been updated.");
		}
	?>
<div id="qcf-options"> 
<div id="qcf-style"> 
<form method="post" action="">
	<?php
	if (empty($error['field1'])) $error['field1'] = 'Giving me '.strtolower($qcf['label']['field1']).' would really help';
	if (empty($error['field2'])) $error['field2'] = 'The '.strtolower($qcf['label']['field2']).' is needed';
	if (empty($error['field3'])) $error['field3'] = 'What is the '.strtolower($qcf['label']['field3']).'?';
	if (empty($error['field4'])) $error['field4'] = 'The '.strtolower($qcf['label']['field4']).' is needed';
	$width = preg_replace("/[^0-9]/", "", $qcf['width']);
	$width = $width.'px';
	$input = $width;
	$submit = $width;
	$textarea = $width;
	?>
	<h2>Error Reporting</h2>
	<p>Error header (leave blank if you don't want a heading):</p>
	<p><input type="text"  style="width:100%" name="errortitle" value="<?php echo $error['errortitle']; ?>" />
	</p>
	<p>This is the blurb that will appear below the error heading and above the actual error messages (leave blank if you don't want any blurb):</p>
	<p><input type="text" style="width:100%" name="errorblurb" value="<?php echo $error['errorblurb']; ?>" />
	</p>
	<h2>Error Messages</h2>
	<p>Error message for <em>
	<?php echo $qcf['label']['field1']; ?>
	</em>:</p>
	<p><input type="text" style="width:100%" name="error1" value="<?php echo $error['field1']; ?>" />
	</p>
	<p>Error message for <em>
	<?php echo $qcf['label']['field2']; ?>
	</em>:</p>
	<p><input type="text" style="width:100%" name="error2" value="<?php echo $error['field2']; ?>" />
	</p>
	<p>Error message for an invalid email address:</p>
	<p><input type="text" style="width:100%" name="email" value="<?php echo $error['email']; ?>" />
	</p>
	<p>Error message for <em><?php echo $qcf['label']['field3']; ?></em>:</p>
	<p>	<input type="text" style="width:100%" name="error3" value="<?php echo $error['field3']; ?>" />
	</p>
	<p>Error message for an invalid telephone number:</p>
	<p>	<input type="text" style="width:100%" name="telephone" value="<?php echo $error['telephone']; ?>" />
	</p>
	<p>Error message for <em><?php echo $qcf['label']['field4']; ?></em>:</p>
	<p>	<input type="text" style="width:100%;" name="error4" value="<?php echo $error['field4']; ?>" />
</p>
	<p>Error message for the drop dopwn list:</p>
	<p>	<input type="text" style="width:100%;" name="error5" value="<?php echo $error['field5']; ?>" />
	</p>
	<p>Error message for the checkboxes:</p>
	<p><input type="text" style="width:100%;" name="error6" value="<?php echo $error['field6']; ?>" />
	</p>
	<p>Error message for <em><?php echo $qcf['label']['field8']; ?></em>:</p>
	<p><input type="text" style="width:100%;" name="error8" value="<?php echo $error['field8']; ?>" />
	</p>
	<p>Error message for <em><?php echo $qcf['label']['field9']; ?></em>:</p>
	<p><input type="text" style="width:100%;" name="error9" value="<?php echo $error['field9']; ?>" />
	</p>
	<p>Maths Captcha missing answer error message:</p>
	<p><input type="text" style="width:100%" name="errorsum1" value="<?php echo $error['mathsmissing']; ?>" />
	</p>
	<p>Maths Captcha wrong answer error message:</p>
	<p><input type="text" style="width:100%" name="errorsum2" value="<?php echo $error['mathsanswer']; ?>" />
	</p>
	<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" />
	</p>
	</form>
	</div>
	</div>
	<div id="qcf-options"> 
	<h2>Error Checker</h2>
	<p>Send a blank mesage to test your error messages.</p>
	<?php
	form_preview ();
	?>
	</div>
	<?php
	}

function qcf_help()
	{
	?>
	<div id="qcf-options"> 
	<div id="qcf-style"> 
	<h2>Introduction</h2>
	<p>This contact form plugin can almost be used straight out the box. All you need to do is add your email address and insert the shortcode into your posts and pages.</p>
	<p>The <a href= '?page=quick-contact-form/quick-contact-form.php&tab=settings'>Form 
      Settings</a> page allows you to select and order which fields are displayed, 
      change the labels and have them validated. You can also alter the width 
      of the form, change the border style and add an optionals spambot cruncher. 
      When you save the changes the updated form will preview on the right.</p>
	<p>You can create your own <a href= '?page=quick-contact-form/quick-contact-form.php&tab=error'>error 
      messages</a> and configure <a href= '?page=quick-contact-form/quick-contact-form.php&tab=reply'>how 
      the message is sent</a> as well.</p>
	<p>If it all goes a bit pear shaped you can <a href= '?page=quick-contact-form/quick-contact-form.php&tab=reset'>reset 
      everything</a> to the defaults.</p>
	<h2>Installing the plugin, FAQs and other info</h2>
	<p>There is some installation info and FAQs on the <a href="http://wordpress.org/extend/plugins/quick-contact-form/installation/" target="_blank">wordpress 
      plugin page</a>. Some developement info on <a href="http://aerin.co.uk/quick-contact-form/">my 
      plugin page</a> along with a feedback form. Or you can email me at <a href="mailto:graham@aerin.co.uk">graham@aerin.co.uk</a>.</p>
	<h2>New Stuff</h2>
	<p>The big change with version 3 was the introduction of multiple fields. 
      Older versions only had 4 fields but now you can add a drop down, checkboxes 
      and radio buttons. There are also two extra text fields if you need them.</p>
	<p>To make it work the way the information is written to the database had 
      to be changed so any messages you had displayed on your dashboard have been 
      replaced by the new system.</p>
	<h2>Next Up</h2>
	<p>customised subject fields. Woooo...</p>
	</div>
	</div>
	<div id="qcf-options"> 
	<div id="qcf-style"> 
	<h2>Validation</h2>
	<p>I've spent quite some time trying to make the validation as usable as possible 
      but keep the configuration as simple as possible. I think I've captured 
      most conditions but there might be the odd omission if you set up a weird 
      label.</p>
	<p>Anyway, here's how it works.</p>
	<p>If you tick the 'required' checkbox the field will get validated.</p>
	<p>The first validation removes all the unwanted characters. Essentially this 
      clears out the sort of thing a spammer would use: URLs, HTML and so on leaving 
      just the alphabet, numbers and a few punctuation marks.</p>
	<p>The second validation checks that the field isn't empty or that the user 
      has actually typed something in the box. The error message suggests that 
      they need to enter &lt;something&gt; where something is the info you need 
      (name, email, phone number, colour etc).</p>
	<p>The third validation checks that it is a valid phone number (no letters) 
      or valid email. This only takes place in the telephone and email fields 
      (see the Form Settings page).</p>
	<h2>Styling</h2>
	<p>Everything is wrapped in the <code>qcf-style</code> id. Most of the styles 
      are standard HTML tags. The additional styles are the 5 border options, 
      the required fields and error messages.</p>
	<p>The colours I've used are the result of a whole bunch of tests. Many of 
      them over at <a href="http://usabilityhub.com" target="blank">usabilityhub.com</a>. 
      There are 6 main colours: form border - #888, field colour - #465069, normal 
      field border - #415063, required field border - #00C618, errors - #D31900. 
      The submit button is #343838 with white text.</p>
	</div>
	</div>
	<?php
	}

function qcf_reset_page()
	{
	if (isset($_POST['qcf_reset']))
		{
		if (isset($_POST['qcf_reset_email']))
			{
			$qcf_email = "";
			update_option('qcf_email', $qcf_email);
			qcf_admin_notice("<b>Your email adress has been reset.</b> Use the <a href= '?page=quick-contact-form/quick-contact-form.php&tab=setup'>Setup</a> tab to add a new email address");
			}
		if (isset($_POST['qcf_reset_options']))
			{
			$qcf = qcf_get_default_options();
			update_option('qcf_settings', $qcf);
			qcf_admin_notice("<b>Form settings have been reset.</b> Use the <a href= '?page=quick-contact-form/quick-contact-form.php&tab=settings'>Form Settings</a> tab to change the settings");
			}
		if (isset($_POST['qcf_reset_reply']))
			{
			$reply = qcf_get_default_reply();
			update_option('qcf_reply', $reply);
			qcf_admin_notice("<b>The reply options have been reset.</b>. Use the <a href= '?page=quick-contact-form/quick-contact-form.php&tab=reply'>Reply Options</a> tab to change the settings");
			}
		if (isset($_POST['qcf_reset_message']))
			{
			$qcf_message = array();
			update_option('qcf_message', $qcf_message);
			qcf_admin_notice("<b>The message list has been deleted.</b> Only those messages received from today will be displayed.");
			}
		if (isset($_POST['qcf_reset_errors']))
			{
			$error = qcf_get_default_error();
			update_option('qcf_error', $error);
			qcf_admin_notice("<b>The error messages have been reset.</b> Use the <a href= '?page=quick-contact-form/quick-contact-form.php&tab=error'>Error Messages</a> tab to change the settings.");
			}
		}
		?>
	<div id="qcf-options"> 
	<div id="qcf-style"> 
	<h2>Reset Everything</h2>
	<p><span style="color:red; font-weight: bold;">Use with caution!</span></p>
	<p>Select the options you wish to reset and click on the blue button. This will reset the selected settings to the defaults.</p>
	<form action="" method="POST">
	<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_reset_email">Reset email</p>
	<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_reset_options">Reset form options</p>
	<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_reset_reply">Reset reply options</p>
	<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_reset_errors">Reset error messages</p>
	<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_reset_message">Delete message list - this won't delete any email you have recieved.</p>
	<input type="submit" class="button-primary" name="qcf_reset" style="color: #FFF" value="Reset Options" onclick="return window.confirm( 'Are you sure you want to reset these settings?' );"/>
	</form>
	</div>
	</div>
	<?php 
	}

function form_preview () {
	if (isset($_POST['submit']))
		{
		$formvalues = $_POST;
		$formerrors = array();
    		if (!qcf_verify_form($formvalues, $formerrors)) qcf_display_form($formvalues, $formerrors, 0);
    		else qcf_process_form($formvalues);
		}
	else
		{
		$qcf = qcf_get_stored_options();
		for ($i=1; $i<=9; $i++) { $values['qcfname'.$i] = $qcf['label']['field'.$i]; }
		qcf_display_form( $values , null , 0 );
		}
	}

function qcf_init() {
	if (is_admin()) {
		wp_enqueue_script('jquery-ui-sortable');
		return;
		}
	global $qcf_settings_option;
	}

function qcf_get_stored_options () {
	$qcf = get_option('qcf_settings');
	if(!is_array($qcf)) $qcf = array();
	if ($qcf['update'] == '') $option_default = qcf_get_update_options();
	else $option_default = qcf_get_default_options();
	$qcf = array_merge($option_default, $qcf);
	return $qcf;
	}

function qcf_get_default_options () {
	$qcf = array();
	$qcf['active_buttons'] = array( 'field1'=>'on' , 'field2'=>'on' , 'field3'=>'on' , 'field4'=>'on' , 'field5'=>'' , 'field6'=>'' ,  'field7'=>'' , 'field8'=>'' ,  'field9'=>'' ,);	
	$qcf['required'] = array('field1'=>'checked' , 'field2'=>'checked' , 'field3'=>'' , 'field4'=>'' , 'field5'=>'' , 'field6'=>'' , 'field7'=>'' , 'field8'=>'' , 'field9'=>'' , );
	$qcf['label'] = array( 'field1'=>'Your Name' , 'field2'=>'Email' , 'field3'=>'Telephone' , 'field4'=>'Message' , 'field5'=>'Select an option' , 'field6'=>'Check at least one box' ,  'field7'=>'Radio' , 'field8'=>'Website' ,  'field9'=>'Subject' ,);
	$qcf['sort'] = implode(',',array('field1', 'field2', 'field3', 'field8', 'field9' , 'field4', 'field5', 'field6', 'field7' ));
	$qcf['type'] = array( 'field1' => 'text' , 'field2' => 'email' , 'field3' => 'phone' , );
	$qcf['lines'] = 6;
	$qcf['dropdownlist'] = 'Pound,Dollar,Euro,Yen,Triganic Pu';
	$qcf['checklist'] = 'Donald Duck,Mickey Mouse,Goofy';
	$qcf['radiolist'] = 'Large,Medium,Small';
	$qcf['width'] = 280;
	$qcf['border'] = 'rounded';
	$qcf['title'] = 'Enquiry Form';
	$qcf['blurb'] = 'Fill in the form below and we will be in touch soon';	
	$qcf['send'] = 'Send it!';
	$qcf['captcha'] = '';
	$qcf['update'] = 'updated';
	return $qcf;
}

function qcf_get_stored_reply () {
	$reply = get_option('qcf_reply');
	if(!is_array($reply)) $reply = array();
	if ($reply['update'] == '') $option_default = qcf_get_update_reply();
	else $option_default = qcf_get_default_options();
	$reply = array_merge($option_default, $reply);
	return $reply;
	}

function qcf_get_default_reply () {
	$reply = array();
	$reply['replytitle'] = 'Message sent!';
	$reply['replyblurb'] = 'Thank you for your enquiry. I&#146;ll be in contact soon';
	$reply['messages'] = 'checked';
	$reply['dashboard'] = '';
	$reply['tracker'] = 'checked';
	$reply['update'] = 'updated';
	return $reply;
}

function qcf_get_stored_error () {
	$error = get_option('qcf_error');
	if(!is_array($error)) $error = array();
	if ($error['update'] == '') $option_default = qcf_get_update_error();	
	else $option_default = qcf_get_default_options(); 	
	$error = array_merge($option_default, $error);
	return $error;
	}

function qcf_get_default_error () {
	$qcf = get_option('qcf_settings');
	$error = array();
	$error['field1'] = 'Giving me '. strtolower($qcf['label']['field1']) . ' would really help';
	$error['field2'] = 'Please enter your ' . strtolower($qcf['label']['field2']);
	$error['field3'] = 'A '. strtolower($qcf['label']['field3']) .' is needed';
	$error['field4'] = 'What is the '. strtolower($qcf['label']['field4']);
	$error['field5'] = 'Select a option from the list';
	$error['field6'] = 'Check at least one box';
	$error['field7'] = 'There is an error';
	$error['field8'] = 'The ' . strtolower($qcf['label']['field8']) . ' is missing';
	$error['field9'] = 'What is your '. strtolower($qcf['label']['field9']) . '?';
	$error['email'] = 'There&#146;s a problem with your email address';
	$error['telephone'] = 'Please check your phone number';
	$error['mathsmissing'] = 'Answer the sum please';
	$error['mathsanswer'] = 'That&#146;s not the right answer, try again';
	$error['errortitle'] = 'Oops, got a few problems here';
	$error['errorblurb'] = 'Can you sort out the details highlighted below.';
	$error['update'] = 'updated';
	return $error;
	}

function qcf_get_update_options() {
	$qcf_options = get_option('qcf_options');
	$option_default = qcf_get_default_options();
	$qcf = array();
	if(is_array($qcf_options)) {
		$qcf['label'] = array( 'field1'=>'Your Name' , 'field2'=>'Email' , 'field3'=>'Telephone' , 'field4'=>'Message' , 'field5'=>'Select an option' , 'field6'=>'Check at least one box' ,  'field7'=>'Radio' , 'field8'=>'Spare' ,  'field9'=>'Spare' ,);
		$qcf['sort'] = implode(',',array('field1', 'field2', 'field3', 'field8', 'field9' , 'field4', 'field5', 'field6', 'field7' ));
		if ($qcf_options[17] == 'yes') $qcf['active_buttons']['field1'] = 'on';
		if ($qcf_options[18] == 'yes') $qcf['active_buttons']['field2'] = 'on';
		if ($qcf_options[19] == 'yes') $qcf['active_buttons']['field3'] = 'on';
		if ($qcf_options[20] == 'yes') $qcf['active_buttons']['field4'] = 'on';
		if ($qcf_options[12] == 'required') $qcf['required']['field1'] = 'checked';
		if ($qcf_options[13] == 'required') $qcf['required']['field2'] = 'checked';
		if ($qcf_options[16] == 'required') $qcf['required']['field3'] = 'checked';
		if ($qcf_options[14] == 'required') $qcf['required']['field4'] = 'checked';
		$qcf['label']['field1'] = $qcf_options[2];
		$qcf['label']['field2'] = $qcf_options[3];
		$qcf['label']['field3'] = $qcf_options[15];
		$qcf['label']['field4'] = $qcf_options[4];
		$qcf['width'] = $qcf_options[7];
		$qcf['border'] = $qcf_options[8];
		$qcf['title'] = $qcf_options[0];
		$qcf['blurb'] = $qcf_options[1];	
		$qcf['send'] = $qcf_options[5];
		if ($qcf_options[9] == 'required') $qcf['captcha'] = 'checked';
		}
	$qcf['update'] = 'updated';
	$qcf = array_merge($option_default, $qcf);
	update_option( 'qcf_settings', $qcf);
	return $qcf;
	}
	
function qcf_get_update_reply() {
	$qcf_reply = get_option('qcf_reply');
	$option_default = qcf_get_default_reply();
	$reply = array();
	if(is_array($qcf_reply)) {
		$reply['replytitle'] = $qcf_reply[0];
		$reply['replyblurb'] = $qcf_reply[1];
		$reply['messages'] = $qcf_reply[2];
		$reply['dashboard'] = $qcf_reply[3];
		$reply['tracker'] = $qcf_reply[4];
		}
	$reply['update'] = 'updated';
	$reply = array_merge($option_default, $reply);
	update_option( 'qcf_reply', $reply);
	return $reply;
	}
	
function qcf_get_update_error() {
	$qcf_error  = get_option('qcf_error');
	$option_default = qcf_get_default_error();	
	$error = array();
	if(is_array($qcf_error)) {
		$error['field1'] = $qcf_error[2];
		$error['field2'] = $qcf_error[3];
		$error['field3'] = $qcf_error[4];
		$error['field4'] = $qcf_error[5];
		$error['field5'] = 'Select a option from the list';
		$error['field6'] = 'Check at least one box';
		$error['field7'] = 'There is an error';
		$error['field8'] = 'There is an error';
		$error['field9'] = 'There is an error';
		$error['mathsmissing'] = $qcf_error[6];
		$error['mathsanswer'] = $qcf_error[7];
		$error['errortitle'] = $qcf_error[0];
		$error['errorblurb'] = $qcf_error[1];
		}
	$error['update'] = 'updated';
	$error = array_merge($option_default, $error);
	update_option( 'qcf_error', $error);
	return $error;	
	}

function qcf_verify_form(&$values, &$errors)
	{
	$qcf = qcf_get_stored_options();
	$error = qcf_get_stored_error();
	foreach (explode( ',',$qcf['sort']) as $name)
		if ($qcf['active_buttons'][$name] && $qcf['required'][$name]) {
			switch ( $name ) {
				case 'field1':
					if (empty($values['qcfname1']) || $values['qcfname1'] == $qcf['label'][$name])
						$errors['qcfname1'] = '<p class="error">' . $error['field1'] . '</p>';
					break;
				case 'field2':
					if (!filter_var($values['qcfname2'], FILTER_VALIDATE_EMAIL))
						$errors['qcfname2'] = '<p class="error">Please enter a valid email address</p>';
					if (empty($values['qcfname2']) || $values['qcfname2'] == $qcf['label'][$name])
						$errors['qcfname2'] = '<p class="error">' . $error['field2'] . '</p>';
					break;
				case 'field3':
					if (preg_match("/[^0-9()\+\.-\s]$/",$values['qcfname3']))
						$errors['qcfname3'] = '<p class="error">Please enter a valid phone number</p>';
					if (empty($values['qcfname3']) || $values['qcfname3'] == $qcf['label'][$name])
						$errors['qcfname3'] = '<p class="error">' . $error['field3'] . '</p>';
					break;
				case 'field4':
					if (empty($values['qcfname4']) || $values['qcfname4'] == $qcf['label'][$name])
						$errors['qcfname4'] = '<p class="error">' . $error['field4'] . '</p>';
					break;
				case 'field5':
					if ($values['qcfname5'] == $qcf['label'][$name])
						$errors['qcfname5'] = '<p class="error">' . $error['field5'] . '</p>';
					break;
				case 'field6':
					$check = '';
					$arr = explode(",",$qcf['checklist']);
					foreach ($arr as $item) $check = $check . $values['qcfname6_'.$item];
					if (empty($check)) $errors['qcfname6'] = '<p class="error">' . $error['field6'] . '</p>';
					break;
				case 'field8':
					if (empty($values['qcfname8']) || $values['qcfname8'] == $qcf['label'][$name])
						$errors['qcfname8'] = '<p class="error">' . $error['field8'] . '</p>';
					break;
				case 'field9':
					if (empty($values['qcfname9']) || $values['qcfname9'] == $qcf['label'][$name])
						$errors['qcfname9'] = '<p class="error">' . $error['field9'] . '</p>';
					break;
				}
			}
		if($qcf['captcha'] == 'checked')
			{
			if($values['maths']<>7) $errors['captcha'] = '<p class="error">' . $error['mathsanswer'] . '</p>';
			if(empty($values['maths'])) $errors['captcha'] = '<p class="error">' . $error['mathsmissing'] . '</p>';
			}		
	return (count($errors) == 0);	
	}

function qcf_display_form( $values, $errors, $whichpage )
	{
	$qcf = qcf_get_stored_options();
	$error = qcf_get_stored_error();
	$width = preg_replace("/[^0-9]/", "", $qcf['width']);
	if ($qcf['border'] == "none") $padding = 0;
	if ($qcf['border'] == "plain") $padding = 22;
	if ($qcf['border'] == "rounded") $padding = 22;
	if ($qcf['border'] == "shadow") $padding = 32;
	if ($qcf['border'] == "roundshadow") $padding = 32;
	$input = $width - $padding - $whichpage;
	$textarea = $width - $padding;
	$submit = $width - $padding;
	$width = $width.'px';
	$input =  $input.'px';
	$textarea = $textarea.'px';
	$submit = $submit.'px';
	$border = $qcf['border'];
	if (!empty($qcf['title'])) $qcf['title'] = '<h2>' . $qcf['title'] . '</h2>';
	if (!empty($qcf['blurb'])) $qcf['blurb'] = '<p>' . $qcf['blurb'] . '</p>';
	?>
	<div id="qcf-style" style="width: <?php echo $width; ?>">
	<div id="<?php echo $border; ?>"> 
	<?php
	if (count($errors) > 0)
		echo '<h2>' . $error['errortitle'] . '</h2><p class="error">' . $error['errorblurb'] . '</p>';
	else
		echo $qcf['title'] . $qcf['blurb'];

	?>
	<form action="" method="POST">
	<?php
	foreach (explode( ',',$qcf['sort']) as $name)
		{
		$required = ( $qcf['required'][$name]) ? 'class="required"' : '';
		$lines = $qcf['lines'];
		if ($qcf['active_buttons'][$name] == "on")
			{
			switch ( $name )
				{
				case 'field1':
					echo $errors['qcfname1'];
					?>
					<p><input type="text" <?php echo $required; ?> style="width:<?php echo $input; ?>" label="Name" name="qcfname1" value="<?php echo $values['qcfname1']; ?>" onfocus="clickclear(this, '<?php echo $values['qcfname1']; ?>')" onblur="clickrecall(this,'<?php echo $values['qcfname1']; ?>')"></p>
					<?php ;
					break;
				case 'field2':
					echo $errors['qcfname2'];
					?>
					<p><input type="text" <?php echo $required; ?> style="width:<?php echo $input; ?>" label="Name" name="qcfname2"  value="<?php echo $values['qcfname2']; ?>" onfocus="clickclear(this, '<?php echo $values['qcfname2']; ?>')" onblur="clickrecall(this,'<?php echo $values['qcfname2']; ?>')"></p>
					<?php ;
					break;
				case 'field3':
					echo $errors['qcfname3'];
					?>
					<p><input type="text" <?php echo $required; ?> style="width:<?php echo $input; ?>" label="Name" name="qcfname3"  value="<?php echo $values['qcfname3']; ?>" onfocus="clickclear(this, '<?php echo $values['qcfname3']; ?>')" onblur="clickrecall(this,'<?php echo $values['qcfname3']; ?>')">
					</p>
					<?php ;
					break;
				case 'field4':
					echo $errors['qcfname4'];
					?>
					<p><textarea <?php echo $required; ?> style="width:<?php echo $input; ?>" rows="<?php echo $lines; ?>" label="Name" name="qcfname4"  onFocus="this.value=''; this.onfocus=null;"><?php echo strip_tags(stripslashes($values['qcfname4'])); ?></textarea>
					</p>
					<?php ;
					break;
				case 'field5':
					echo $errors['qcfname5'];
					?>
					<p><select name="qcfname5" style="width:<?php echo $submit; ?>">
					<option value="<?php echo $qcf['label'][$name]; ?>">
					<?php echo $qcf['label'][$name]; ?>
					</option>
					<?php ;
					$str=$qcf['dropdownlist'];
					$arr = explode(",",$str);
					foreach ($arr as $item) 
					{
					$selected = '';
					if ($values['qcfname5'] == $item) $selected = 'selected';
					echo '<option value="' .  $item . '" ' . $selected .'>' .  $item . '</option>';
					}
					echo '</select></p>';
					break;
				case 'field6':
					if ($errors['qcfname6']) echo $errors['qcfname6'];
					else echo '<p class="input">' . $qcf['label'][$name] . '</p>';
					echo '<p>';
					$arr = explode(",",$qcf['checklist']);
					foreach ($arr as $item) {
					$checked = '';
					if ($values['qcfname6_'.$item] == $item) $checked = 'checked';
					echo '<input type="checkbox" style="margin:0; padding: 0; border: none" name="qcfname6_'.$item.'" value="' .  $item . '" ' . $checked . '> ' .  $item . '<br>';
					}
					echo '</p>';
					break;
				case 'field7':
					echo '<p class="input">' . $qcf['label'][$name] . '</p>';
					echo '<p>';
					$str=$qcf['radiolist'];
					$arr = explode(",",$str);
					foreach ($arr as $item) {
					$checked = '';
					if ($values['qcfname7'] == $item) $checked = 'checked';
					if ($item === reset($arr)) echo '<input type="radio" style="margin:0; padding: 0; border: none" name="qcfname7" value="' .  $item . '" checked> ' .  $item . ' ';
					else echo '<input type="radio" style="margin:0; padding: 0; border: none" name="qcfname7" value="' .  $item . '" ' . $checked . '> ' .  $item . ' ';
					}
					echo '</p>';
					break;
				case 'field8':
					echo $errors['qcfname8'];
					?><p><input type="text" <?php echo $required; ?> style="width:<?php echo $input; ?>" label="Field8" name="qcfname8" value="<?php echo $values['qcfname8']; ?>" onfocus="clickclear(this, '<?php echo $values['qcfname8']; ?>')" onblur="clickrecall(this,'<?php echo $values['qcfname8']; ?>')"><p></p> 
					<?php ;
					break;
				case 'field9':
					echo $errors['qcfname9'];
					?><p><input type="text" <?php echo $required; ?> style="width:<?php echo $input; ?>" label="Field9" name="qcfname9" value="<?php echo $values['qcfname9']; ?>" onfocus="clickclear(this, '<?php echo $values['qcfname9']; ?>')" onblur="clickrecall(this,'<?php echo $values['qcfname9']; ?>')"><p></p> 
					<?php ;
						break;
					}
			}
		}
	if ($qcf['captcha'] == "checked")
		{ 
		echo $errors['captcha']
		?>
		<p id="sums">What is 3 + 4? <input type="text" class="required" style="width:30px" label="Sum" name="maths"  value="<?php echo $values['maths']; ?>"><p></p> 
		<?php ;} ?>
		<p><input type="submit" id="submit" style="width:<?php echo $submit; ?>" name="submit" value="<?php echo $qcf['send']; ?>">	</form> </div> </div> 
	<?php
	}

function qcf_process_form($values)
	{
	$qcf = qcf_get_stored_options();
	$reply = qcf_get_stored_reply();
	$qcf_email = get_option('qcf_email');
	if (empty($qcf_email)) $qcf_email = $qcf[6];
	if (!empty($reply['replytitle'])) $reply['replytitle'] = '<h2>' . $reply['replytitle'] . '</h2>';
	if (!empty($reply['replyblurb'])) $reply['replyblurb'] = '<p>' . $reply['replyblurb'] . '</p>';
	$ip=$_SERVER['REMOTE_ADDR'];
	$url = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
	$page = get_the_title();
	$subject = "Enquiry from {$values['qcfname1']}";
	$headers = "From: {$values['qcfname1']}<{$values['qcfname2']}>\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=\"utf-8\"\r\n"; 
	$content = '';
	foreach (explode( ',',$qcf['sort']) as $name)
		if ($qcf['active_buttons'][$name])
		{
			switch ( $name )
				{
				case 'field1':
					if ($values['qcfname1'] == $qcf['label'][$name]) $values['qcfname1'] ='';
					$content .= '<p><b>' . $qcf['label'][$name] . ': </b>' . strip_tags($values['qcfname1']) . '</p>';
					break;
				case 'field2':
					if ($values['qcfname2'] == $qcf['label'][$name]) $values['qcfname2'] ='';
					$content .= '<p><b>' . $qcf['label'][$name] . ': </b>' . strip_tags($values['qcfname2']) . '</p>';
					break;
				case 'field3':
					if ($values['qcfname3'] == $qcf['label'][$name]) $values['qcfname3'] ='';
					$content .= '<p><b>' . $qcf['label'][$name] . ': </b>' . strip_tags($values['qcfname3']) . '</p>';
					break;
				case 'field4':
					if ($values['qcfname4'] == $qcf['label'][$name]) $values['qcfname4'] ='';
					$content .= '<p><b>' . $qcf['label'][$name] . ': </b>' . strip_tags(stripslashes($values['qcfname4'])) . '</p>';
					break;
				case 'field5':
					if ($values['qcfname5'] == $qcf['label'][$name]) $values['qcfname5'] ='';
					$content .= '<p><b>' . $qcf['label'][$name] . ': </b>' . $values['qcfname5'] . '</p>';
					break;
				case 'field6':
					$arr = explode(",",$qcf['checklist']);
					$content .= '<p><b>' . $qcf['label'][$name] . ': </b>';
					foreach ($arr as $item) if ($values['qcfname6_'.$item]) $content .= $item . ', ';
					$contet .='/p>';
					break;
				case 'field7':
					if ($values['qcfname7'] == $qcf['label'][$name]) $values['qcfname7'] ='';
					$content .= '<p><b>' . $qcf['label'][$name] . ': </b>' . $values['qcfname7'] . '</p>';
					break;
				case 'field8':
					if ($values['qcfname8'] == $qcf['label'][$name]) $values['qcfname8'] ='';
					$content .= '<p><b>' . $qcf['label'][$name] . ': </b>' . strip_tags($values['qcfname8']) . '</p>';
					break;
				case 'field9':
					if ($values['qcfname9'] == $qcf['label'][$name]) $values['qcfname9'] ='';
					$content .= '<p><b>' . $qcf['label'][$name] . ': </b>' . strip_tags($values['qcfname9']) . '</p>';

				}
			}
	$message = "<html><h2>The message is:</h2>".$content;
	if ($reply['tracker']) $message .= "<p>Message was sent from: <b>".$page."</b></p><p>Senders IP address: <b>".$ip."</b></p>";
	$message .="</html>";
	
	mail($qcf_email, $subject, $message, $headers);

	echo '<div id="qcf-style">
	<div id="'.$qcf['border'].'" style="width:'.$qcf['width'].'px;">'
	.$reply['replytitle'].$reply['replyblurb'];
	echo '<p>The details you sent me were:</p>'.$content.'</div></div>';  	
	
	$qcf_message = get_option('qcf_message');
	if(!is_array($qcf_message)) $qcf_message = array();
	if ($values['qcfname1'] == $qcf['label']['field1']) $values['qcfname1'] ='';
	$sentdate = date('d M Y');
	$qcf_message[] = array('field1' => $values['qcfname1'] , 'field2' => $values['qcfname2'] , 'field4' => $values['qcfname4'] , date => $sentdate,);
	update_option('qcf_message',$qcf_message);
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
		$qcf = qcf_get_stored_options();
		for ($i=1; $i<=9; $i++) { $values['qcfname'.$i] = $qcf['label']['field'.$i]; }
		$values['captcha'] = $qcf['captcha'];
		qcf_display_form( $values , null , 14 );
		}
	$output_string=ob_get_contents();;
	ob_end_clean();
	return $output_string;
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

function qcf_add_dashboard_widgets()
	{
	$reply = qcf_get_stored_reply();
	if ( $reply['messages'] == 'checked' )
		{
		wp_add_dashboard_widget( 'qcf_dashboard_widget', 'Latest Messages', 'qcf_dashboard_widget' );	
		}
	}

function qcf_dashboard_widget() 
	{
	$message = get_option( 'qcf_message' );
	if(!is_array($message)) $message = array();
	$qcf = qcf_get_stored_options ();
	echo '<div id="qcf-widget">';
	echo '<table cellspacing="0">';
	echo '<tr>';
	echo '<th>From</th><th>'.$qcf['label']['field2'].'</th><th>'.$qcf['label']['field4'].'</th><th>Date</th>';
	echo '</tr>';
	foreach(array_reverse( $message ) as $value)
		{
		echo '<tr>';
		foreach($value as $item)
			{
			if (strlen($item) > 25) $ellipses = ' ...';
			else $ellipses = '';
			$trim = substr($item, 0 , 25).$ellipses;
			echo '<td>'.$trim.'</td>';
			}
		echo '</tr>';
 		}
		echo '</table>';
		echo '</div>';
	}
?>