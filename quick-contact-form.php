<?php
/*
Plugin Name: Quick Contact Form
Plugin URI: http://www.aerin.co.uk/quick-contact-form
Description: A really, really simple contact form. There is nothing to configure, just add your email address and it's ready to go.
Version: 3.2.1
Author: fisicx
Author URI: http://www.aerin.co.uk
*/

add_action( 'admin_notices', 'qcf_admin_notice' );
add_action( 'wp_dashboard_setup', 'qcf_add_dashboard_widgets' );
add_shortcode('qcf', 'qcf_start');
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

function qcf_start()
	{
	return qcf_loop(14);
	}

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
	delete_option('qcf_style');
	delete_option('qcf_messages');
	}

function qcf_admin_tabs( $current = 'settings' )
	{ 
	$tabs = array( 'setup' => 'Setup' , 'settings' => 'Form Settings', 'styles' => 'Styling' , 'error' => 'Error Messages' , 'reply' => 'Send Options' , 'help' => 'Help' , 'reset' => 'Reset' , ); 
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
	echo '<div class="wrap">';
	echo '<h1>Quick Contact Form</h1>';
	if ( isset ( $_GET['tab'] ) ) qcf_admin_tabs($_GET['tab']); else qcf_admin_tabs('setup');
	if ( isset ( $_GET['tab'] ) ) $tab = $_GET['tab']; else $tab = 'setup'; 
	switch ( $tab )
		{
		case 'setup' : qcf_setup();
		break;
		case 'settings' : qcf_form_options();
		break;
		case 'styles' : qcf_styles();
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
	echo '</div>';
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
		qcf_admin_notice("Your email address has been updated.");
		}
	$qcf_email = get_option('qcf_email');
	$content ='
		<div id="qcf-options"> 
		<div id="qcf-style"> 
    	<form method="post" action="">
		<h2>Setting up the Quick Contact Form</h2>
		<p><span style="color:red; font-weight: bold;">Important!</span> Enter YOUR email address below and save the changes. This won&#146;t display, it&#146;s just so the plugin knows where to send the message.</p>
		<p>To send to multiple addresses, put a comma betweeen each address.</p>
		<p><input type="text" style="width:100%" label="Email" name="qcf_email" value="' . $qcf_email . '" /></p>
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
		<p>To change the layout of the form, add or remove fields, change the order they appear and edit the labels and captions use the <a href="?page=quick-contact-form/quick-contact-form.php&tab=settings">Form Setting</a> tab.</p>
		<p>Use the <a href="?page=quick-contact-form/quick-contact-form.php&tab=reply">Reply Options</a> tab to change the thank you message and send options.</p>
		<p>To change the way the form looks use the <a href="?page=quick-contact-form/quick-contact-form.php&tab=styles">styling</a> tab.</p>
		<p>You can also customise the <a href="?page=quick-contact-form/quick-contact-form.php&tab=error">error messages</a>.</p>
		<p>If it all goes wrong you can <a href="?page=quick-contact-form/quick-contact-form.php&tab=reset">reset</a> everything.</p>
		<h2>Adding the contact form to your site</h2>
		<p>To add the contact form your posts or pages use the short code: <code>[qcf]</code>.<br />
		<p>To add it to your theme files use <code>&lt;?php echo do_shortcode("[qcf]"); ?&gt;</code></p>
		<p>There is also a widget called "Quick Contact Form" you can drag and drop into your sidebar.</p>
		<p>That&#146;s it. The form is ready to use.</p>
		</div>
		</div>';
	echo $content;
	}

function qcf_form_options () {
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
		$qcf['dropdownlist'] = str_replace(', ' , ',' , esc_html( $_POST['dropdown_string']));
		$qcf['checklist'] = str_replace(', ' , ',' , esc_html( $_POST['checklist_string']));
		$qcf['radiolist'] = str_replace(', ' , ',' , esc_html( $_POST['radio_string']));
		$qcf['lines'] = esc_html( $_POST['message_lines']);
		$qcf['title'] = esc_html( $_POST['qcfname_title']);
		$qcf['blurb'] = esc_html( $_POST['qcfname_blurb']);
		$qcf['border'] = esc_html( $_POST['border']);
		$qcf['width'] = esc_html( $_POST['width']);
		$qcf['captcha'] = esc_html( $_POST['captcha']);
		$qcf['mathscaption'] = esc_html( $_POST['mathscaption']);
		$qcf['send'] = esc_html( $_POST['sendcaption']);
		$qcf['update'] = 'updated';
		update_option( 'qcf_settings', $qcf);
		qcf_admin_notice("The form settings have been updated.");
		}
	$qcf = qcf_get_stored_options();
	$content = '
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
		<p>Setting up the form fields:</p>
		<ul class="instructions">
		<li>Select the fields you want to use on your form</li>
		<li>Edit the field labels if required.</li>
		<li>Drag and drop to change the order of the fields.</li>
		<li>Select which fields you want validating.</li>
		<li>Set the number of rows in the text (message) area.</li>
		<li>Update the dropdown, checkbox and radio options (down the page).</li>
		<li>Don&#146;t forget to save!</li>
		</ul>
		<p><b><div style="float:left; margin-left:7px;width:170px;">Field Selection & Label</div><div style="float:left; width:85px;">Field Type</div>Validation</b></p>
		<div style="clear:left"></div>
		<ul id="qcf_sort">';
		foreach (explode( ',',$qcf['sort']) as $name)
			{
			$checked = ( $qcf['active_buttons'][$name]) ? 'checked' : '';
			$required = ( $qcf['required'][$name]) ? 'checked' : '';
			$lines = $qcf['lines'];
			$options = '';
			switch ( $name )
				{
				case 'field1':
					$type = 'Textbox';
					$options = 'Required field';
					break;
				case 'field2':
					$type = 'Email';
					$options = 'Validate email address';
					break;
				case 'field3':
					$type = 'Telephone';
					$options = 'Check number format';
					break;	
				case 'field4':
					$type = 'Textarea';
					$options = 'Required. <input type="text" style="border:1px solid #415063; width:1.5em; padding: 1px; margin:0;" name="message_lines" . value ="' . $lines . '" /> rows';
					break;	
				case 'field5':
					$type = 'Dropdown';
					$options = 'Ensure a selection is made';
					break;
				case 'field6':
					$type = 'Checkboxes';
					$options = 'Ensure a box is checked';
					break;
				case 'field7':
					$type = 'Radio';
					$options = '';
					break;
				case 'field8':
					$type = 'Textbox';
					$options = 'Required field';
					break;
				case 'field9':
					$type = 'Textbox';
					$options = 'Required field';
					break;
				}
			$li_class = ( $checked) ? 'button_active' : 'button_inactive';
	$content .= '<li class="ui-state-default '.$li_class.'" id="' . $name . '">
		<div style="float:left; width:170px;overflow:hidden;">
		<input type="checkbox" class="button_activate" style="border: none; padding: 0; margin:0;" name="qcf_settings_active_' . $name . '" ' . $checked . ' />
		<input type="text" style="border: border:1px solid #415063; width:150px; padding: 1px; margin:0;" name="label_' . $name . '" value="' . $qcf['label'][$name] . '"/>
		</div>
		<div style="float:left; width:85px;">' . $type . '</div>
		<div style="float:left;">';
	if ($name <> 'field7') $content .='<input type="checkbox" class="button_activate" style="border: none; padding: 0; margin:0;" name="required_'.$name.'" '.$required.' /> ';
	$content .= $options . '</div>
		</li>';
	}
	$content .= '
		</ul>
		<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /></p>
		<input type="hidden" id="qcf_settings_sort" name="qcf_settings_sort" value="'.stripslashes( $qcf['sort']).'" />
		<h2>Submit button caption</h2>
		<p><input type="text" style="width:100%; text-align:center" name="sendcaption" value="' . $qcf['send'] . '" /></p>
		<h2>Selection field options</h2>
		<p>Separate each option with a comma. Don&#146;t use a space between each option!</p>
		<h3>Dropdown list:</h3>
		<p><textarea  name="dropdown_string" label="Dropdown" rows="2" style="width:100%">' . $qcf['dropdownlist'] . '</textarea></p>
		<h3>Checkboxes:</h3>
		<p><textarea  name="checklist_string" label="Checklist" rows="2" style="width:100%">' . $qcf['checklist'] . '</textarea></p>
		<h3>Radio buttons:</h3>
		<p><textarea  name="radio_string" label="Radio" rows="2" style="width:100%">' . $qcf['radiolist'] . '</textarea></p>
		<h2>Spambot Checker</h2>
		<p>Add a maths checker to the form to (hopefully) block most of the spambots.</p>
		<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="captcha"' . $qcf['captcha'] . ' value="checked" /> Add Spambot blocker</p>
		<p>Caption (leave blank if you just want the sum):</p>
		<p><input type="text" style="width:100%;" name="mathscaption" value="' . $qcf['mathscaption'] . '" /></p>
		<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /></p>
		</form>
		</div>
		</div>
		<div id="qcf-options"> 
		<h2>Form Preview</h2>
		<p>Note: The preview form uses the wordpress admin styles. Your form will use the theme styles so won&#146;t look exactly like the one below.</p>';
	$content .=	qcf_loop (null);
	$content .= '<p>Have you set up your <a href="?page=quick-contact-form/quick-contact-form.php&tab=reply">reply options</a>?</p>
		<p>If you are not using english then you can customise your <a href="?page=quick-contact-form/quick-contact-form.php&tab=error">error messages</a>.</p>
		</div>';
	echo $content;
	}

function qcf_styles()
	{
	if( isset( $_POST['Submit']))
		{
		$style['width'] = esc_html( $_POST['width']);
		$style['border'] = esc_html( $_POST['border']);
		$style['background'] = esc_html( $_POST['background']);
		$style['backgroundhex'] = esc_html( $_POST['backgroundhex']);
		$style['update'] = 'updated';
		update_option( 'qcf_style', $style);
		qcf_admin_notice("The form styles have been updated.");
		}
	$qcf = qcf_get_stored_options();
	$style = qcf_get_stored_style();
	if ( $style['border'] == "shadow") $shadow = "checked"; 
	if ( $style['border'] == "roundshadow") $roundshadow = "checked"; 
	if ( $style['border'] == "plain") $plain = "checked"; 
	if ( $style['border'] == "rounded") $rounded = "checked"; 
	if ( $style['border'] == "none") $none = "checked";
	if ( $style['background'] == "white") $white = "checked"; 
	if ( $style['background'] == "theme") $theme = "checked"; 
	if ( $style['background'] == "color") $color = "checked"; 
	$content ='
		<div id="qcf-options"> 
		<div id="qcf-style"> 
		<form method="post" action="">
		<h2>Form Width</h2>
		<p>Enter the width of the form in pixels. Just enter the value, no need to add &#146;px&#146;. The current width is as you see it here.</p>
		<p><input type="text" style="width:4em" label="width" name="width" value="' . $style['width'] . '" /> px</p>
		<h2>Form Border</h2>
		<p>Note: The rounded corners and shadows only work on CSS3 supported browsers and even then not in IE8. Don&#146;t blame me, blame Microsoft.</p>
		<p>
			<input style="margin: 0; padding: 0; border: none;" type="radio" name="border" value="none" ' . $none . ' /> No border<br />
			<input style="margin: 0; padding: 0; border: none;" type="radio" name="border" value="plain" ' . $plain . ' /> Plain Border<br />
			<input style="margin: 0; padding: 0; border: none;" type="radio" name="border" value="rounded" ' . $rounded . ' /> Round Corners (Not IE8)<br />
			<input style="margin: 0; padding: 0; border: none;" type="radio" name="border" value="shadow" ' . $shadow . ' /> Shadowed Border(Not IE8)<br />
			<input style="margin: 0; padding: 0; border: none;" type="radio" name="border" value="roundshadow" ' . $roundshadow . ' /> Rounded Shadowed Border (Not IE8)</p>
		<h2>Background colour</h2>
		<p>
			<input style="margin: 0; padding: 0; border: none;" type="radio" name="background" value="white" ' . $white . ' /> White<br />
			<input style="margin: 0; padding: 0; border: none;" type="radio" name="background" value="theme" ' . $theme . ' /> Use theme colours<br />
			<input style="margin: 0; padding: 0; border: none;" type="radio" name="background" value="color" ' . $color . ' /> Set your own (enter HEX code or color name below)</p>
			<p><input type="text" style="width:7em" label="background" name="backgroundhex" value="' . $style['backgroundhex'] . '" /></p>
			<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /></p>
		</form>
		<p>The styling features are a bit naff at the moment. It&#146;s actually easier for you to use the <a href="'.get_admin_url().'plugin-editor.php?file=quick-contact-form/quick-contact-form-style.css">CSS editor</a> than mess about here. However, if there is something you really want to see then email me at <a href="mailto:graham@aerin.co.uk">graham@aerin.co.uk</a>.
		
		?file=quick-contact-form%2Fquick-contact-form-style.css
		
		</div>
		</div>
		<div id="qcf-options"> 
		<h2>Test Form</h2>
		<p>If you are using your theme colours as the background they will only display when you use the form on your the site (because that&#146;s how WordPress works).</p>';
	$content .= qcf_loop (null);
	$content .= '</div>';
	echo $content;
	}

function qcf_reply_page()
	{
	if( isset( $_POST['Submit']))
		{
		$reply['replytitle'] = esc_html( $_POST['replytitle']);
		$reply['replyblurb'] = esc_html( $_POST['replyblurb']);
		$reply['messages'] = esc_html( $_POST['qcf_showmessage']);
		$reply['dashboard'] = esc_html( $_POST['qcf_dashboard']);
		$reply['tracker'] = esc_html( $_POST['qcf_tracker']);
		$reply['subject'] = esc_html( $_POST['subject']);
		$reply['subjectoption'] = esc_html( $_POST['subjectoption']);
		$reply['update'] = 'updated';
		update_option( 'qcf_reply', $reply);
		qcf_admin_notice("The reply settings have been updated.");
		}
	$qcf = qcf_get_stored_options();
	$reply = qcf_get_stored_reply();
	if ( $reply['subjectoption'] == "sendername") $sname = "checked"; 
	if ( $reply['subjectoption'] == "senderpage") $spage = "checked"; 
	if ( $reply['subjectoption'] == "sendernone") $snone = "checked"; 
	$content ='
		<div id="qcf-options"> 
		<div id="qcf-style"> 
		<form method="post" action="">
		<h2>Send Options</h2>
		<h3>Email subject</h3>
		<p>The message subject has two parts: the bit in the text box plus the option below.</p>
		<p><input type="text"  style="width:100%" name="subject" value="' . $reply['subject'] . '"/></p>
		<p><input type="radio" style="margin:0; padding: 0; border: none" name="subjectoption" value="sendername" ' . $sname . '> sender&#146;s name (the contents of the first field)</p>
		<p><input type="radio" style="margin:0; padding: 0; border: none" name="subjectoption" value="senderpage" ' . $spage . '> page title (only works if sent from a post or a page)</p>
		<p><input type="radio" style="margin:0; padding: 0; border: none" name="subjectoption" value="sendernone" ' . $snone . '> blank</p>
		<h3>Tracking</h3>
		<p>Adds the IP address and the current page title to the message you receive.</p>
		<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="qcf_tracker" ' . $reply['tracker'] . ' value="checked"> Show tracking info</p>
		<h2>Thank you message</h2>
		<p>Thank you header (leave blank if you don&#146;t want a heading):</p>
		<p><input type="text"  style="width:100%" name="replytitle" value="' . $reply['replytitle'] . '"/></p>
		<p>This is the blurb that will appear below the thank you heading and above the actual message (leave blank if you don&#146;t want any blurb):</p>
		<p><input type="text" style="width:100%" name="replyblurb" value="' . $reply['replyblurb'] . '" /></p>
		<p>Show the sender the content of their message.</p>
		<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="qcf_showmessage" ' . $reply['messages'] . ' value="checked"> Show message content</p>
		<h2>Dashboard Widget</h2>
		<p>Displays most recent messages on your dashboard</p>
		<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="qcf_dashboard" ' . $reply['dashboard'] . ' value="checked"> Add latest messages to dashboard</p>
			<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /></p>
		</form>
		</div>
		</div>
		<div id="qcf-options"> 
		<h2>Test Form</h2>
		<p>Use the form below to test your thank-you message settings. You will see what your visitors will see when the complete and send the form.</p>';
	$content .= qcf_loop (null);
	$content .= '</div>';
	echo $content;
	}

function qcf_error_page()
	{
	$error = qcf_get_stored_error();
	$qcf = qcf_get_stored_options();
	if( isset( $_POST['Submit']))
		{
		for ($i=1; $i<=9; $i++) { $error['field'.$i] = esc_html( $_POST['error'.$i]); }
		$error['errortitle'] = esc_html( $_POST['errortitle']);
		$error['errorblurb'] = esc_html( $_POST['errorblurb']);
		$error['email'] = esc_html( $_POST['email']);
		$error['telephone'] = esc_html( $_POST['telephone']);
		$error['mathsmissing'] = esc_html( $_POST['errorsum1']);
		$error['mathsanswer'] = esc_html( $_POST['errorsum2']);
		$error['emailcheck'] = esc_html( $_POST['emailcheck']);
		$error['phonecheck'] = esc_html( $_POST['phonecheck']);
		$error['update'] = 'updated';
		update_option( 'qcf_error', $error );
		qcf_admin_notice("The reply settings have been updated.");
		}
	$content ='
		<div id="qcf-options"> 
		<div id="qcf-style"> 
		<form method="post" action="">
		<h2>Error Reporting</h2>
		<p>Error header (leave blank if you don&#146;t want a heading):</p>
		<p><input type="text"  style="width:100%" name="errortitle" value="' . $error['errortitle'] . '" /></p>
		<p>This is the blurb that will appear below the error heading and above the actual error messages (leave blank if you don&#146;t want any blurb):</p>
		<p><input type="text" style="width:100%" name="errorblurb" value="' . $error['errorblurb'] . '" /></p>
		<h2>Error Messages</h2>
		<p>If <em>' .  $qcf['label']['field1'] . '</em> is missing:</p>
		<p><input type="text" style="width:100%" name="error1" value="' .  $error['field1'] . '" /></p>
		<p>If <em>' .  $qcf['label']['field2'] . '</em> is missing:</p>
		<p><input type="text" style="width:100%" name="error2" value="' .  $error['field2'] . '" /></p>
		<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="emailcheck"' . $error['emailcheck'] . ' value="checked" /> Check for invalid email even if field is not required</p>
		<p>Error message for an invalid email address:</p>
		<p><input type="text" style="width:100%" name="email" value="' .  $error['email'] . '" /></p>
		<p>If <em>' .  $qcf['label']['field3'] . '</em> is missing:</p>
		<p>	<input type="text" style="width:100%" name="error3" value="' .  $error['field3'] . '" /></p>
		<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="phonecheck"' . $error['phonecheck'] . ' value="checked" /> Check for invalid phone number even if field is not required</p>
		<p>Error message for an invalid telephone number:</p>
		<p>	<input type="text" style="width:100%" name="telephone" value="' .  $error['telephone'] . '" /></p>
		<p>If <em>' .  $qcf['label']['field4'] . '</em> is missing:</p>
		<p>	<input type="text" style="width:100%;" name="error4" value="' .  $error['field4'] . '" /></p>
		<p>Error message for the drop dopwn list:</p>
		<p>	<input type="text" style="width:100%;" name="error5" value="' .  $error['field5'] . '" /></p>
		<p>Error message for the checkboxes:</p>
		<p><input type="text" style="width:100%;" name="error6" value="' .  $error['field6'] . '" /></p>
		<p>If <em>' .  $qcf['label']['field8'] . '</em> is missing:</p>
		<p><input type="text" style="width:100%;" name="error8" value="' .  $error['field8'] . '" /></p>
		<p>If <em>' .  $qcf['label']['field9'] . '</em> is missing:</p>
		<p><input type="text" style="width:100%;" name="error9" value="' .  $error['field9'] . '" /></p>
		<p>Maths Captcha missing answer error message:</p>
		<p><input type="text" style="width:100%" name="errorsum1" value="' .  $error['mathsmissing'] . '" /></p>
		<p>Maths Captcha wrong answer error message:</p>
		<p><input type="text" style="width:100%" name="errorsum2" value="' .  $error['mathsanswer'] . '" /></p>
		<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /></p>
		</form>
		</div>
		</div>
		<div id="qcf-options"> 
		<h2>Error Checker</h2>
		<p>Send a blank form to test your error messages.</p>';
	$content .= qcf_loop (null);
	$content .= '</div>';
	echo $content;
	}

function qcf_help()
	{
	$content = '';
	$content .='
		<div id="qcf-options"> 
		<div id="qcf-style"> 
		<h2>Introduction</h2>
		<p>This contact form plugin can almost be used straight out the box. All you need to do is add your email address and insert the shortcode into your posts and pages.</p>
		<p>The <a href= "?page=quick-contact-form/quick-contact-form.php&tab=settings">Form Settings</a> page allows you to select and order which fields are displayed, change the labels and have them validated. You can also add an optionals spambot cruncher. When you save the changes the updated form will preview on the right.</p>
		<p>To change the width of the form, border style and background colour use the <a href= "?page=quick-contact-form/quick-contact-form.php&tab=settings">styling</a> page.</p>
		<p>You can create your own <a href= "?page=quick-contact-form/quick-contact-form.php&tab=error">error messages</a> and configure <a href= "?page=quick-contact-form/quick-contact-form.php&tab=reply">how the message is sent</a> as well.</p>
		<p>If it all goes a bit pear shaped you can <a href= "?page=quick-contact-form/quick-contact-form.php&tab=reset">reset everything</a> to the defaults.</p>
		<h2>Installing the plugin, FAQs and other info</h2>
		<p>There is some installation info and FAQs on the <a href="http://wordpress.org/extend/plugins/quick-contact-form/installation/" target="_blank">wordpress plugin page</a>. Some developement info on <a href="http://aerin.co.uk/quick-contact-form/">my plugin page</a> along with a feedback form. Or you can email me at <a href="mailto:graham@aerin.co.uk">graham@aerin.co.uk</a>.</p>
		<h2>New Stuff</h2>
		<p>The big change with version 3 was the introduction of multiple fields. Older versions only had 4 fields but now you can add a drop down, checkboxes and radio buttons. There are also two extra text fields if you need them.</p>
		<p>To make it work the way the information is written to the database had to be changed so any messages you had displayed on your dashboard have been replaced by the new system.</p>
		<h2>Next Up</h2>
		<p>Adding a field to attach attachments.</p>
		</div>
		</div>
		<div id="qcf-options"> 
		<div id="qcf-style"> 
		<h2>Validation</h2>
		<p>I&#146;ve spent quite some time trying to make the validation as usable as possible but keep the configuration as simple as possible. I think I&#146;ve captured most conditions but there might be the odd omission if you set up a weird label.</p>
		<p>Anyway, here&#146;s how it works.</p>
		<p>If you tick the "required" checkbox the field will get validated.</p>
		<p>The first validation removes all the unwanted characters. Essentially this clears out the sort of thing a spammer would use: URLs, HTML and so on leaving just the alphabet, numbers and a few punctuation marks.</p>
		<p>The second validation checks that the field isn&#146;t empty or that the user has actually typed something in the box. The error message suggests that they need to enter &lt;something&gt; where something is the info you need (name, email, phone number, colour etc).</p>
		<p>The third validation checks for a valid email address and phone number. This only takes place in the telephone and email fields (see the Form Settings page).</p>
		<h2>Styling</h2>
		<p>Everything is wrapped in the <code>qcf-style</code> id. Most of the styles are standard HTML tags. The additional styles are the 5 border options, the required fields and error messages.</p>
		<p>The colours I&#146;ve used are the result of a whole bunch of tests. Many of them over at <a href="http://usabilityhub.com" target="blank">usabilityhub.com</a>. There are 6 main colours: form border - #888, field colour - #465069, normal field border - #415063, required field border - #00C618, errors - #D31900. The submit button is #343838 with white text.</p>
		</div>
		</div>';
	echo $content;
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
			qcf_admin_notice("<b>The send options have been reset.</b>. Use the <a href= '?page=quick-contact-form/quick-contact-form.php&tab=reply'>Reply Options</a> tab to change the settings");
			}
		if (isset($_POST['qcf_reset_styles']))
			{
			$style = qcf_get_default_style();
			update_option('qcf_style', $style);
			qcf_admin_notice("<b>The styles have been reset.</b>. Use the <a href= '?page=quick-contact-form/quick-contact-form.php&tab=styles'>Styling</a> tab to change the settings");
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
	$content ='
		<div id="qcf-options"> 
		<div id="qcf-style"> 
		<h2>Reset Everything</h2>
		<p><span style="color:red; font-weight: bold;">Use with caution!</span></p>
		<p>Select the options you wish to reset and click on the blue button. This will reset the selected settings to the defaults.</p>
		<form action="" method="POST">
		<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_reset_email">Reset email</p>
		<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_reset_options">Reset form options</p>
		<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_reset_styles">Reset form styles</p>
		<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_reset_reply">Reset send and thank-you message options</p>
		<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_reset_errors">Reset the error messages</p>
		<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_reset_message">Delete message list - this won&#146;t delete any email you have recieved.</p>
		<input type="submit" class="button-primary" name="qcf_reset" style="color: #FFF" value="Reset Options" onclick="return window.confirm( \'Are you sure you want to reset these settings?\' );"/>
		</form>
		</div>
		</div>';
	echo $content;
	}

function qcf_init() {
	if (is_admin()) {
		wp_enqueue_script('jquery-ui-sortable');
		return;
		}
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
	$qcf['active_buttons'] = array( 'field1'=>'on' , 'field2'=>'on' , 'field3'=>'' , 'field4'=>'on' , 'field5'=>'' , 'field6'=>'' ,  'field7'=>'' , 'field8'=>'' ,  'field9'=>'' ,);	
	$qcf['required'] = array('field1'=>'checked' , 'field2'=>'checked' , 'field3'=>'' , 'field4'=>'' , 'field5'=>'' , 'field6'=>'' , 'field7'=>'' , 'field8'=>'' , 'field9'=>'' , );
	$qcf['label'] = array( 'field1'=>'Your Name' , 'field2'=>'Email' , 'field3'=>'Telephone' , 'field4'=>'Message' , 'field5'=>'Select an option' , 'field6'=>'Check at least one box' ,  'field7'=>'Radio' , 'field8'=>'Website' ,  'field9'=>'Subject' ,);
	$qcf['sort'] = implode(',',array('field1', 'field2' , 'field3' , 'field4' , 'field5' , 'field6' , 'field7' , 'field8' , 'field9'));
	$qcf['type'] = array( 'field1' => 'text' , 'field2' => 'email' , 'field3' => 'phone' , );
	$qcf['lines'] = 6;
	$qcf['dropdownlist'] = 'Pound,Dollar,Euro,Yen,Triganic Pu';
	$qcf['checklist'] = 'Donald Duck,Mickey Mouse,Goofy';
	$qcf['radiolist'] = 'Large,Medium,Small';
	$qcf['width'] = 280;
	$qcf['border'] = 'rounded';
	$qcf['background'] = '#666';
	$qcf['title'] = 'Enquiry Form';
	$qcf['blurb'] = 'Fill in the form below and we will be in touch soon';	
	$qcf['send'] = 'Send it!';
	$qcf['captcha'] = '';
	$qcf['mathscaption'] = 'Spambot blocker question';
	$qcf['update'] = 'updated';
	return $qcf;
	}

function qcf_get_stored_style() {
	$style = get_option('qcf_style');
	if(!is_array($style)) $style = array();
	if ($style['update'] == '') $option_default = qcf_get_update_style();
	else $option_default = qcf_get_default_options();
	$option_default = qcf_get_default_style();
	$style = array_merge($option_default, $style);
	return $style;
	}

function qcf_get_default_style() {
	$style['width'] = 280;
	$style['border'] = 'rounded';
	$style['background'] = 'white';
	$style['backgroundhex'] = '#FFF';
	$style['update'] = 'updated';
	return $style;
	}

function qcf_get_update_style() {
	$qcf = get_option('qcf_settings');
	$style = array();
	$style['width'] = $qcf['width'];
	$style['border'] = $qcf['border'];
	$style['background'] = 'white';
	$style['backgroundhex'] = '#FFF';
	$style['update'] = 'updated';
	update_option( 'qcf_style', $style);
	return $style;
	}

function qcf_get_stored_reply () {
	$reply = get_option('qcf_reply');
	if(!is_array($reply)) $reply = array();
	if ($reply['update'] == '') $option_default = qcf_get_update_reply();
	else $option_default = qcf_get_default_reply();
	$reply = array_merge($option_default, $reply);
	return $reply;
	}

function qcf_get_default_reply () {
	$reply = array();
	$reply['replytitle'] = 'Message sent!';
	$reply['replyblurb'] = 'Thank you for your enquiry, I&#146;ll be in contact soon';
	$reply['messages'] = 'checked';
	$reply['dashboard'] = '';
	$reply['tracker'] = 'checked';
	$reply['subject'] = 'Enquiry from';
	$reply['subjectoption'] = 'sendername';
	$reply['update'] = 'updated';
	return $reply;
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
	$reply['subject'] = 'Enquiry from';
	$reply['subjectoption'] = 'sendername';
	$reply['update'] = 'updated';
	$reply = array_merge($option_default, $reply);
	update_option( 'qcf_reply', $reply);
	return $reply;
	}

function qcf_get_stored_error () {
	$error = get_option('qcf_error');
	if(!is_array($error)) $error = array();
	if ($error['update'] == '') $option_default = qcf_get_update_error();	
	else $option_default = qcf_get_default_error(); 	
	$error = array_merge($option_default, $error);
	return $error;
	}

function qcf_get_default_error () {
	$qcf = get_option('qcf_settings');
	$error = array();
	$error['field1'] = 'Giving me '. strtolower($qcf['label']['field1']) . ' would really help';
	$error['field2'] = 'Please enter your email address';
	$error['field3'] = 'A telephone number is needed';
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
	$error['emailcheck'] = '';
	$error['phonecheck'] = '';
	$error['update'] = 'updated';
	return $error;
	}

function qcf_get_update_options() {
	$qcf_options = get_option('qcf_options');
	$option_default = qcf_get_default_options();
	$qcf = array();
	if(is_array($qcf_options)) {
		$qcf['label'] = array( 'field1'=>'Your Name' , 'field2'=>'Email' , 'field3'=>'Telephone' , 'field4'=>'Message' , 'field5'=>'Select an option' , 'field6'=>'Check at least one box' ,  'field7'=>'Radio' , 'field8'=>'Website' ,  'field9'=>'Subject' ,);
		$qcf['sort'] = implode(',',array('field1', 'field2' , 'field3' , 'field4' , 'field5' , 'field6' , 'field7' , 'field8' , 'field9'));
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
	$emailcheck = $error['emailcheck'];
	if ($qcf['required']['field2'] == 'checked') $emailcheck = 'checked';
	$phonecheck = $error['phonecheck'];
	if ($qcf['required']['field3'] == 'checked') $phonecheck = 'checked';
	if ($qcf['active_buttons']['field2'] && $emailcheck == 'checked' && $values['qcfname2'] !== $qcf['label']['field2']) {
			if (!filter_var($values['qcfname2'], FILTER_VALIDATE_EMAIL))
				$errors['qcfname2'] = '<p class="error">' . $error['email'] . '</p>';
			}
		if ($qcf['active_buttons']['field3'] && $phonecheck == 'checked' && $values['qcfname3'] !== $qcf['label']['field3']) {
			if (preg_match("/[^0-9()\+\.-\s]$/",$values['qcfname3']))
				$errors['qcfname3'] = '<p class="error">' . $error['telephone'] . '</p>';
			}
	foreach (explode( ',',$qcf['sort']) as $name)
		if ($qcf['active_buttons'][$name] && $qcf['required'][$name]) {
			switch ( $name ) {
				case 'field1':
					if (empty($values['qcfname1']) || $values['qcfname1'] == $qcf['label'][$name])
						$errors['qcfname1'] = '<p class="error">' . $error['field1'] . '</p>';
					break;
				case 'field2':
					if (empty($values['qcfname2']) || $values['qcfname2'] == $qcf['label'][$name])
						$errors['qcfname2'] = '<p class="error">' . $error['field2'] . '</p>';
					break;
				case 'field3':
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
					foreach ($arr as $item) $check = $check . $values['qcfname6_'.str_replace(' ','',$item)];
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
		if($qcf['captcha'] == 'checked') {
			if($values['maths']<>$values['answer']) $errors['captcha'] = '<p class="error">' . $error['mathsanswer'] . '</p>';
			if(empty($values['maths'])) $errors['captcha'] = '<p class="error">' . $error['mathsmissing'] . '</p>';
			}		
	return (count($errors) == 0);	
	}

function qcf_display_form( $values, $errors, $whichpage )
	{
	$qcf = qcf_get_stored_options();
	$error = qcf_get_stored_error();
	$style = qcf_get_stored_style();
	$width = preg_replace("/[^0-9]/", "", $style['width']);
	if ($style['background'] == 'white') $background = 'style="background:#FFF"';
	if ($style['background'] == 'color') $background = 'style="background: ' . $style['backgroundhex'] . '"';
	if ($style['border'] == "none") $padding = 0;
	if ($style['border'] == "plain") $padding = 22;
	if ($style['border'] == "rounded") $padding = 22;
	if ($style['border'] == "shadow") $padding = 32;
	if ($style['border'] == "roundshadow") $padding = 32;
	$input = ($width - $padding - $whichpage) . 'px';
	$submit = ($width - $padding) . 'px';
	$width = $width.'px';
	if (!empty($qcf['title'])) $qcf['title'] = '<h2>' . $qcf['title'] . '</h2>';
	if (!empty($qcf['blurb'])) $qcf['blurb'] = '<p>' . $qcf['blurb'] . '</p>';
	if (!empty($qcf['mathscaption'])) $qcf['mathscaption'] = '<p class="input">' . $qcf['mathscaption'] . '</p>';
	$content = '<div id="qcf-style" style="width:' . $width . '"><div id="' . $style['border'] . '" ' . $background . '>';
	if (count($errors) > 0)
		$content .= '<h2>' . $error['errortitle'] . '</h2><p class="error">' . $error['errorblurb'] . '</p>';
	else
		$content .= $qcf['title'] . $qcf['blurb'];
	
	$content .= '<form action="" method="POST">';
		foreach (explode( ',',$qcf['sort']) as $name)
		{
		$required = ( $qcf['required'][$name]) ? 'class="required"' : '';
		if ($qcf['active_buttons'][$name] == "on")
			{
			switch ( $name )
				{
				case 'field1':
					$content .= $errors['qcfname1'];
					$content .= '<p><input type="text"' . $required . ' style="width:' . $input . '" label="Name" name="qcfname1" value="' . $values['qcfname1'] . '" onfocus="clickclear(this, \'' . $values['qcfname1'] . '\')" onblur="clickrecall(this, \'' . $values['qcfname1'] . '\'"></p>';
					break;
				case 'field2':
					$content .= $errors['qcfname2'];
					$content .= '<p><input type="text" ' . $required . '  style="width:' . $input . '" label="Name" name="qcfname2"  value="' . $values['qcfname2'] . '" onfocus="clickclear(this, \'' . $values['qcfname2'] . '\')" onblur="clickrecall(this, \'' . $values['qcfname2'] . '\'"></p>';
					break;
				case 'field3':
					$content .= $errors['qcfname3'];
					$content .= '<p><input type="text" ' . $required . '  style="width:' . $input . '" label="Name" name="qcfname3"  value="' . $values['qcfname3'] . '" onfocus="clickclear(this, \'' . $values['qcfname3'] . '\')" onblur="clickrecall(this, \'' . $values['qcfname3'] . '\'"></p>';
					break;
				case 'field4':
					$content .= $errors['qcfname4'];
					$content .= '<p><textarea ' . $required . '  style="width:' . $input . '" rows="' . $qcf['lines'] . '" label="Name" name="qcfname4" onFocus="this.value=\'\'; this.onfocus=null;">' . strip_tags(stripslashes($values['qcfname4'])) . '</textarea></p>';
					break;
					case 'field5':
					$content .= $errors['qcfname5'];
					$content .= '
						<p><select name="qcfname5"' . $required . ' style="width:' . $submit . '">
						<option value="' . $qcf['label'][$name] . '">' . $qcf['label'][$name] . '</option>';
						$str=$qcf['dropdownlist'];
						$arr = explode(",",$str);
						foreach ($arr as $item) 
							{
							$selected = '';
							if ($values['qcfname5'] == $item) $selected = 'selected';
							$content .= '<option value="' .  $item . '" ' . $selected .'>' .  $item . '</option>';
							}
					$content .= '</select></p>';
					break;
				case 'field6':
					if ($errors['qcfname6']) $content .= $errors['qcfname6'];
					else $content .= '<p class="input">' . $qcf['label'][$name] . '</p>';
					$content .= '<p>';
					$arr = explode(",",$qcf['checklist']);
					foreach ($arr as $item)
						{
						$checked = '';
						if ($values['qcfname6_'. str_replace(' ','',$item)] == $item) $checked = 'checked';
						$content .= '<input type="checkbox" style="margin:0; padding: 0; border: none" name="qcfname6_' . str_replace(' ','',$item) . '" value="' .  $item . '" ' . $checked . '> ' .  $item . '<br>';
						}
					$content .= '</p>';
					break;
					case 'field7':
					$content .= '<p class="input">' . $qcf['label'][$name] . '</p>';
					$content .= '<p>';
					$str=$qcf['radiolist'];
					$arr = explode(",",$str);
					foreach ($arr as $item)
						{
						$checked = '';
						if ($values['qcfname7'] == $item) $checked = 'checked';
						if ($item === reset($arr)) $content .= '<input type="radio" style="margin:0; padding: 0; border: none" name="qcfname7" value="' .  $item . '" checked> ' .  $item . ' ';
						else $content .=  '<input type="radio" style="margin:0; padding: 0; border: none" name="qcfname7" value="' .  $item . '" ' . $checked . '> ' .  $item . ' ';
						}
					$content .= '</p>';
					break;
				case 'field8':
					$content .= $errors['qcfname8'];
					$content .= '<p><input type="text"' . $required . ' style="width:' . $input . '" label="Name" name="qcfname8" value="' . $values['qcfname8'] . '" onfocus="clickclear(this, \'' . $values['qcfname8'] . '\')" onblur="clickrecall(this, \'' . $values['qcfname8'] . '\'"></p>';
					break;
				case 'field9':
					$content .= $errors['qcfname9'];
					$content .= '<p><input type="text" ' . $required . '  style="width:' . $input . '" label="Name" name="qcfname9"  value="' . $values['qcfname9'] . '" onfocus="clickclear(this, \'' . $values['qcfname9'] . '\')" onblur="clickrecall(this, \'' . $values['qcfname9'] . '\'"></p>';
					break;
				}
			}
		}
	if ($qcf['captcha'] == "checked")
		{
		if ($errors['captcha']) $content .= $errors['captcha'];
		else $content .= $qcf['mathscaption']; 
		$content .= '<p id="sums">' . $values['thesum'] . ' = <input type="text" class="required" style="width:3em; font-size:100%" label="Sum" name="maths"  value="' . $values['maths'] . '"></p> 
		<input type="hidden" name="answer" value="' . $values['answer'] . '" />
		<input type="hidden" name="thesum" value="' . $values['thesum'] . '" />';
		}
	$content .= '<p><input type="submit" id="submit" style="width:' .  $submit . '" name="submit" value="' . $qcf['send'] . '"></p>
		</form>
		</div>
		</div>';
	echo $content;
	}
	
function qcf_process_form($values)
	{
	$qcf = qcf_get_stored_options();
	$reply = qcf_get_stored_reply();
	$qcf_email = get_option('qcf_email');
	if (empty($qcf_email)) $qcf_email = $qcf[6];
	if (!empty($reply['replytitle'])) $reply['replytitle'] = '<h2>' . $reply['replytitle'] . '</h2>';
	if (!empty($reply['replyblurb'])) $reply['replyblurb'] = '<p>' . $reply['replyblurb'] . '</p>';
	$pagetitle = get_the_title();
	if (empty($pagetitle)) $pagetitle = 'quick contact form';
	if ( $reply['subjectoption'] == "sendername") $addon = $values['qcfname1'];
	if ( $reply['subjectoption'] == "senderpage") $addon = $pagetitle;
	if ( $reply['subjectoption'] == "sendernone") $addon = ''; 
	$ip=$_SERVER['REMOTE_ADDR'];
	$url = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
	$page = get_the_title();
	$subject = "{$reply['subject']} {$addon}";
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
					foreach ($arr as $item) if ($values['qcfname6_' . str_replace(' ','',$item)]) $checks .= $item . ', ';
					$content .= rtrim( $checks , ', ' );
					$content .='</p>';
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
	
function qcf_loop($preview)
	{
	ob_start();
	if (isset($_POST['submit']))
		{
		$formvalues = $_POST;
		$formerrors = array();
    	if (!qcf_verify_form($formvalues, $formerrors, $preview)) qcf_display_form($formvalues, $formerrors, $preview);
    	else qcf_process_form($formvalues);
		}
	else
		{
		$digit1 = mt_rand(1,10);
		$digit2 = mt_rand(1,10);
		if( $digit2 >= $digit1 ) {
		$values['thesum'] = "$digit1 + $digit2";
		$values['answer'] = $digit1 + $digit2;
		} else {
		$values['thesum'] = "$digit1 - $digit2";
		$values['answer'] = $digit1 - $digit2;
		}
		$qcf = qcf_get_stored_options();
		for ($i=1; $i<=9; $i++) { $values['qcfname'.$i] = $qcf['label']['field'.$i]; }
		qcf_display_form( $values , null , $preview );
		}
	$output_string=ob_get_contents();
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
		echo qcf_loop(14);
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
	