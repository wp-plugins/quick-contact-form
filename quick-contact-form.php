<?php
/*
Plugin Name: Quick Contact Form
Plugin URI: http://quick-plugins.com/quick-contact-form/
Description: A really, really simple contact form. There is nothing to configure, just add your email address and it's ready to go.
Version: 4.4
Author: fisicx
Author URI: http://quick-plugins.com/
*/

add_shortcode('qcf', 'qcf_start');
add_action('init', 'qcf_init');
add_action('admin_menu', 'qcf_page_init');
add_action( 'admin_notices', 'qcf_admin_notice' );
add_action( 'wp_dashboard_setup', 'qcf_add_dashboard_widgets' );
add_action('wp_head', 'qcf_use_custom_css');
add_filter( 'plugin_action_links', 'qcf_plugin_action_links', 10, 2 );

/* register_deactivation_hook( __FILE__, 'qcf_delete_options' ); */
register_uninstall_hook(__FILE__, 'qcf_delete_options');

$myScriptUrl = plugins_url('quick-contact-form-javascript.js', __FILE__);
wp_register_script('qcf_script', $myScriptUrl);
wp_enqueue_script( 'qcf_script');

$myStyleUrl = plugins_url('quick-contact-form-style.css', __FILE__);
wp_register_style('qcf_style', $myStyleUrl);
wp_enqueue_style( 'qcf_style');

function qcf_start() {
	return qcf_loop();
	}

function qcf_page_init() {
	add_options_page('Quick Contact', 'Quick Contact', 'manage_options', __FILE__, 'qcf_tabbed_page');
	}

function qcf_plugin_action_links($links, $file ) {
	if ( $file == plugin_basename( __FILE__ ) )
		{
		$qcf_links = '<a href="'.get_admin_url().'options-general.php?page=quick-contact-form/quick-contact-form.php">'.__('Settings').'</a>';
		array_unshift( $links, $qcf_links );
		}
	return $links;
	}

function qcf_delete_options() {
	delete_option('qcf_settings');
	delete_option('qcf_options');
	delete_option('qcf_reply');
	delete_option('qcf_error');
	delete_option('qcf_email');
	delete_option('qcf_style');
	delete_option('qcf_attach');
	}

function qcf_admin_tabs( $current = 'settings' ) { 
	$tabs = array( 'setup' => 'Setup' , 'settings' => 'Form Settings', 'attach' => 'Attachments' , 'styles' => 'Styling' , 'reply' => 'Send Options' , 'error' => 'Error Messages' , 'help' => 'Help' , 'reset' => 'Reset' , ); 
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

function qcf_tabbed_page() {
	echo '<div class="wrap">';
	echo '<h1>Quick Contact Form</h1>';
	if ( isset ( $_GET['tab'] ) ) qcf_admin_tabs($_GET['tab']); else qcf_admin_tabs('setup');
	if ( isset ( $_GET['tab'] ) ) $tab = $_GET['tab']; else $tab = 'setup'; 
	switch ( $tab )
		{
		case 'setup' : qcf_setup(); break;
		case 'settings' : qcf_form_options(); break;
		case 'styles' : qcf_styles(); break;
		case 'reply' : qcf_reply_page(); break;
		case 'error' : qcf_error_page (); break;
		case 'attach' : qcf_attach (); break;
		case 'help' : qcf_help (); break;
		case 'reset' : qcf_reset_page(); break;
		}
	echo '</div>';
	}

function qcf_admin_notice( $message) {
	if (!empty( $message)) echo '<div class="updated"><p>'.$message.'</p></div>';
	}

function qcf_setup () {
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
		<h2>If you have upgraded from Version 2</h2>
		<p>The way the plugin writes to the database has totally changed. Please check your settings to make sure nothing has got mussed up.</p>
		<p>Your dashboard messages will have gone.  Sorry but it was just too complicated and buggy to get them transferred.</p>
		</form>
		</div>
		</div>
		<div id="qcf-options"> 
		<div id="qcf-style"> 
		<h2>Options and Settings</h2>
		<p>To change the layout of the form, add or remove fields, change the order they appear and edit the labels and captions use the <a href="?page=quick-contact-form/quick-contact-form.php&tab=settings">Form Settings</a> tab.</p>
		<p>Use the <a href="?page=quick-contact-form/quick-contact-form.php&tab=reply">Send Options</a> tab to change the thank you message and how the form is sent.</p>
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
	$qcf = qcf_get_stored_options();
	$active_buttons = array( 'field1' , 'field2' , 'field3' , 'field4' , 'field5' , 'field6' ,  'field7' , 'field8' ,  'field9' , 'field10');	
	if( isset( $_POST['Submit']))
		{
		foreach ( $active_buttons as $item)
			{
			$qcf['active_buttons'][$item] = (isset( $_POST['qcf_settings_active_'.$item]) and $_POST['qcf_settings_active_'.$item] == 'on' ) ? true : false;
			$qcf['required'][$item] = (isset( $_POST['required_'.$item]) );
			if (!empty ( $_POST['label_'.$item])) $qcf['label'][$item] = stripslashes($_POST['label_'.$item]);
			}
		$qcf['sort'] = $_POST['qcf_settings_sort'];
		$qcf['dropdownlist'] = str_replace(', ' , ',' , $_POST['dropdown_string']);
		$qcf['checklist'] = str_replace(', ' , ',' , $_POST['checklist_string']);
		$qcf['radiolist'] = str_replace(', ' , ',' , $_POST['radio_string']);
		$qcf['lines'] = $_POST['message_lines'];
		$qcf['title'] = $_POST['qcfname_title'];
		$qcf['blurb'] = $_POST['qcfname_blurb'];
		$qcf['border'] = $_POST['border'];
		$qcf['captcha'] = $_POST['captcha'];
		$qcf['mathscaption'] = $_POST['mathscaption'];
		$qcf['send'] = $_POST['sendcaption'];
		update_option( 'qcf_settings', $qcf);
		qcf_admin_notice("The form settings have been updated.");
		}
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
		<p>
		<span style="margin-left:7px;width:180px;"><a class="tooltip" href="#">Field Selection & Label<span>Check the box to use this field in your contact form</span></a></span>
		<span style="width:80px;"><a class="tooltip" href="#">Field Type<span>This defines the type of field</span></a></span>
		<span style="width:90px;"><a class="tooltip" href="#">Validation<span>Select for required feilds. Customise the response using the Error Messages tab</span></a></span>
		<span style="float:right; width:50px;"><a class="tooltip" href="#">Position<span>Click and hold to change the position of the field</span></a></span></p>
		<div style="clear:left"></div>
		<ul id="qcf_sort">';
		$first = 'first';
		$sort = explode(",", $qcf['sort']); 
		$last = array_pop($sort);
		foreach (explode( ',',$qcf['sort']) as $name)
			{
			if ($name == $last && $qcf['active_buttons'][$name] == 'checked') $first = 'last';
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
					$type = 'Checkbox';
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
	$content .= '<li class="ui-state-default '.$li_class.' '.$first.'" id="' . $name . '">
		<div style="float:left; width:180px;overflow:hidden;">
		<input type="checkbox" class="button_activate" style="border: none; padding: 0; margin:0;" name="qcf_settings_active_' . $name . '" ' . $checked . ' />
		<input type="text" style="border: border:1px solid #415063; width:150px; padding: 1px; margin:0;" name="label_' . $name . '" value="' . $qcf['label'][$name] . '"/>
		</div>
		<div style="float:left; width:70px;">' . $type . '</div>
		<div style="float:left;">';
	if ($name <> 'field7') $content .='<input type="checkbox" class="button_activate" style="border: none; padding: 0; margin:0;" name="required_'.$name.'" '.$required.' /> ';
	$content .= $options . '</div>
		</li>';
	$first = '';
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
		<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="captcha"' . $qcf['captcha'] . ' value="checked" /> Add a maths checker to the form to (hopefully) block most of the spambots.</p>
		<p>Caption (leave blank if you just want the sum):</p>
		<p><input type="text" style="width:100%;" name="mathscaption" value="' . $qcf['mathscaption'] . '" /></p>
		<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /></p>
		</form>
		</div>
		</div>
		<div id="qcf-options"> 
		<h2>Form Preview</h2>
		<p>Note: The preview form uses the wordpress admin styles. Your form will use the theme styles so won&#146;t look exactly like the one below.</p>';
	$content .=	qcf_loop();
	$content .= '<p>Have you set up your <a href="?page=quick-contact-form/quick-contact-form.php&tab=reply">reply options</a>?</p>
		<p>If you are not using English then you can customise your <a href="?page=quick-contact-form/quick-contact-form.php&tab=error">error messages</a>.</p>
		</div>';
	echo $content;
	}

function qcf_attach () {
	if( isset( $_POST['Submit'])) {
		$attach['qcf_attach'] = $_POST['qcf_attach'];
		$attach['qcf_attach_label'] = stripslashes( $_POST['qcf_attach_label']);
		$attach['qcf_attach_size'] = stripslashes( $_POST['qcf_attach_size']);
		$attach['qcf_attach_type'] = stripslashes( $_POST['qcf_attach_type']);
		$attach['qcf_attach_width'] = stripslashes( $_POST['qcf_attach_width']);
		$attach['qcf_attach_error_size'] = stripslashes( $_POST['qcf_attach_error_size']);
		$attach['qcf_attach_error_type'] = stripslashes( $_POST['qcf_attach_error_type']);
		update_option( 'qcf_attach', $attach);
		qcf_admin_notice("The attachment settings have been updated.");
		}
	$attach = qcf_get_stored_attach();
	$content = '
		<div id="qcf-options">
		<div id ="qcf-style">
		<p>If you want your visitors to attach files then use these settings. I am not responsible if you let them attach system files, executables, trojans, worms and a other nasties!</p>
		<form id="qcf_settings_form" method="post" action="">
		<h2>Attachment Settings</h2>
		<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_attach"' . $attach['qcf_attach'] . ' value="checked" /> User can attach files</p>
		<h3>Field Label</h3>
		<p><input type="text" style="width:100%;" name="qcf_attach_label" value="' . $attach['qcf_attach_label'] . '" /></p>
		<h3>Maximum File size</h3>
		<p><input type="text" style="width:100%;" name="qcf_attach_size" value="' . $attach['qcf_attach_size'] . '" /></p>
		<h3>Allowable file types</h3>
		<p><input type="text" style="width:100%;" name="qcf_attach_type" value="' . $attach['qcf_attach_type'] . '" /></p>
		<h3>Field size</h3>
		<p>This is a trial and error number. You can&#146;t use a &#146;width&#146;, the size is a number of characters. Test using the live form not the preview.</p>
		<p><em>Example: A form width of 280 with a plain border has field width of about 15. With no border it&#146;s about 18.</em></p>
		<p><input type="text" style="width:5em;" name="qcf_attach_width" value="' . $attach['qcf_attach_width'] . '" /></p>
		<h2>Error messages</h2>
		<p>If the file is too big:</p>
		<p><input type="text" style="width:100%;" name="qcf_attach_error_size" value="' . $attach['qcf_attach_error_size'] . '" /></p>
		<p>If the filetype is incorrect:</p>
		<p><input type="text" style="width:100%;" name="qcf_attach_error_type" value="' . $attach['qcf_attach_error_type'] . '" /></p>
		<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /></p>
		</form>
		</div>
		</div>
		<div id="qcf-options"> 
		<h2>Form Preview</h2>
		<p>Note: The preview form uses the wordpress admin styles. Your form will use the theme styles so won&#146;t look exactly like the one below.</p>';
	$content .=	qcf_loop();
	$content .= '</div>';
	echo $content;
	}

function qcf_styles() {
	if( isset( $_POST['Submit'])) {
		$style['width'] = $_POST['width'];
		$style['widthtype'] = $_POST['widthtype'];
		$style['border'] = $_POST['border'];
		$style['background'] = $_POST['background'];
		$style['backgroundhex'] = stripslashes( $_POST['backgroundhex']);
		$style['use_custom'] = $_POST['use_custom'];
		$style['styles'] = stripslashes( $_POST['styles']);
		update_option( 'qcf_style', $style);
		qcf_admin_notice("The form styles have been updated.");
		}
	$qcf = qcf_get_stored_options();
	$style = qcf_get_stored_style();
	$$style['border'] = 'checked'; 
	$$style['background'] = 'checked'; 
	$$style['widthtype'] = 'checked';
	$content ='
		<div id="qcf-options"> 
		<div id="qcf-style"> 
		<form method="post" action="">
		<h2>Form Width</h2>
	<p>
	<input style="margin: 0; padding: 0; border: none;" type="radio" name="widthtype" value="percent" ' . $percent . ' /> 100% (fill the available space)<br />
	<input style="margin: 0; padding: 0; border: none;" type="radio" name="widthtype" value="pixel" ' . $pixel . ' /> Pixel (fixed)</p>
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
		<h2>Custom CSS</h2>
		<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="use_custom"' . $style['use_custom'] . ' value="checked" /> Use Custom CSS</p>
		<p><textarea style="width:100%; height: 200px" name="styles">' . $style['styles'] . '</textarea></p>
		<p>To see all the styling use the <a href="'.get_admin_url().'plugin-editor.php?file=quick-contact-form/quick-contact-form-style.css">CSS editor</a>.</p>
		<p>The main style wrapper is the <code>#qcf-style</code> id.</p>
		<p>The form borders are: #none, #plain, #rounded, #shadow, #roundshadow.</p>
		<p>Errors and required fields have the classes .error and .required</p>
		<p>You can&#146;t set widths using custom styles, these are set in the <a href="'.get_admin_url().'plugin-editor.php?file=quick-contact-form/quick-contact-form.php">qcf_display_form</a> function.</p>
		<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /></p>
		</form>
		</div>
		</div>
		<div id="qcf-options"> 
		<h2>Test Form</h2>
		<p>If you are using your theme colours as the background they will only display when you use the form on your the site (because that&#146;s how WordPress works).</p>';
	$content .= qcf_loop();
	$content .= '</div>';
	echo $content;
	}

function qcf_reply_page() {
	$qcf = qcf_get_stored_options();
	$reply = qcf_get_stored_reply();
	if( isset( $_POST['Submit'])) {
		$reply['replytitle'] = stripslashes( $_POST['replytitle']);
		$reply['replyblurb'] = stripslashes( $_POST['replyblurb']);
		$reply['messages'] = $_POST['qcf_showmessage'];
		$reply['dashboard'] = $_POST['qcf_dashboard'];
		$reply['tracker'] = $_POST['qcf_tracker'];
		$reply['subject'] = stripslashes( $_POST['subject']);
		$reply['subjectoption'] = stripslashes( $_POST['subjectoption']);
		$reply['qcf_redirect'] = $_POST['qcf_redirect'];
		$reply['qcf_redirect_url'] = $_POST['qcf_redirect_url'];
		update_option( 'qcf_reply', $reply);
		qcf_admin_notice("The reply settings have been updated.");
		}
	$$reply['subjectoption'] = "checked"; 
	$content ='
		<div id="qcf-options"> 
		<div id="qcf-style"> 
		<form method="post" action="">
		<h2>Send Options</h2>
		<h3>Email subject</h3>
		<p>The message subject has two parts: the bit in the text box plus the option below.</p>
		<p><input type="text"  style="width:100%" name="subject" value="' . $reply['subject'] . '"/></p>
		<p><input type="radio" style="margin:0; padding: 0; border: none" name="subjectoption" value="sendername" ' . $sendername . '> sender&#146;s name (the contents of the first field)</p>
		<p><input type="radio" style="margin:0; padding: 0; border: none" name="subjectoption" value="senderpage" ' . $senderpage . '> page title (only works if sent from a post or a page)</p>
		<p><input type="radio" style="margin:0; padding: 0; border: none" name="subjectoption" value="sendernone" ' . $sendernone . '> blank</p>
		<h3>Tracking</h3>
		<p>Adds the IP address and the current page title to the message you receive.</p>
		<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="qcf_tracker" ' . $reply['tracker'] . ' value="checked"> Show tracking info</p>
		<h3>Redirection</h3>
		<p>Send your visitor to new page instead of displaying the thank-you message.</p>
		<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="qcf_redirect" ' . $reply['qcf_redirect'] . ' value="checked"> Redirect to new page</p>
		<p>URL: <input type="text"  style="width:100%" name="qcf_redirect_url" value="' . $reply['qcf_redirect_url'] . '"/></p>
		<h2>Thank you message</h2>
		<p>Thank you header (leave blank if you don&#146;t want a heading):</p>
		<p><input type="text"  style="width:100%" name="replytitle" value="' . $reply['replytitle'] . '"/></p>
		<p>This is the blurb that will appear below the thank you heading and above the actual message (leave blank if you don&#146;t want any blurb):</p>
		<p><input type="text" style="width:100%" name="replyblurb" value="' . $reply['replyblurb'] . '" /></p>
		<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="qcf_showmessage" ' . $reply['messages'] . ' value="checked"> Show the sender the content of their message.</p>
		<h2>Dashboard Widget</h2>
		<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="qcf_dashboard" ' . $reply['dashboard'] . ' value="checked"> Display the most recent messages on your dashboard</p>
		<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /></p>
		</form>
		</div>
		</div>
		<div id="qcf-options"> 
		<h2>Test Form</h2>
		<p>Use the form below to test your thank-you message settings. You will see what your visitors will see when the complete and send the form.</p>';
	$content .= qcf_loop();
	$content .= '</div>';
	echo $content;
	}

function qcf_error_page() {
	$error = qcf_get_stored_error();
	$qcf = qcf_get_stored_options();
	if( isset( $_POST['Submit'])) {
		for ($i=1; $i<=9; $i++) { $error['field'.$i] = stripslashes($_POST['error'.$i]); }
		$error['errortitle'] = stripslashes( $_POST['errortitle']);
		$error['errorblurb'] = stripslashes( $_POST['errorblurb']);
		$error['email'] = stripslashes( $_POST['email']);
		$error['telephone'] = stripslashes( $_POST['telephone']);
		$error['mathsmissing'] = stripslashes( $_POST['errorsum1']);
		$error['mathsanswer'] = stripslashes( $_POST['errorsum2']);
		$error['emailcheck'] = $_POST['emailcheck'];
		$error['phonecheck'] = $_POST['phonecheck'];
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
		<p>Invalid email address:</p>
		<p><input type="text" style="width:100%" name="email" value="' .  $error['email'] . '" /></p>
		<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="emailcheck"' . $error['emailcheck'] . ' value="checked" /> Check for invalid email even if field is not required</p>
		<p>If <em>' .  $qcf['label']['field3'] . '</em> is missing:</p>
		<p>	<input type="text" style="width:100%" name="error3" value="' .  $error['field3'] . '" /></p>
		<p>Invalid telephone number:</p>
		<p>	<input type="text" style="width:100%" name="telephone" value="' .  $error['telephone'] . '" /></p>
		<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="phonecheck"' . $error['phonecheck'] . ' value="checked" /> Check for invalid phone number even if field is not required</p>
		<p>If <em>' .  $qcf['label']['field4'] . '</em> is missing:</p>
		<p>	<input type="text" style="width:100%;" name="error4" value="' .  $error['field4'] . '" /></p>
		<p>Drop dopwn list:</p>
		<p>	<input type="text" style="width:100%;" name="error5" value="' .  $error['field5'] . '" /></p>
		<p>Checkboxes:</p>
		<p><input type="text" style="width:100%;" name="error6" value="' .  $error['field6'] . '" /></p>
		<p>If <em>' .  $qcf['label']['field8'] . '</em> is missing:</p>
		<p><input type="text" style="width:100%;" name="error8" value="' .  $error['field8'] . '" /></p>
		<p>If <em>' .  $qcf['label']['field9'] . '</em> is missing:</p>
		<p><input type="text" style="width:100%;" name="error9" value="' .  $error['field9'] . '" /></p>
		<p>Maths Captcha missing answer:</p>
		<p><input type="text" style="width:100%" name="errorsum1" value="' .  $error['mathsmissing'] . '" /></p>
		<p>Maths Captcha wrong answer:</p>
		<p><input type="text" style="width:100%" name="errorsum2" value="' .  $error['mathsanswer'] . '" /></p>
		<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /></p>
		</form>
		</div>
		</div>
		<div id="qcf-options"> 
		<h2>Error Checker</h2>
		<p>Try sending end a blank form to test your error messages.</p>';
	$content .= qcf_loop();
	$content .= '</div>';
	echo $content;
	}

function qcf_help() {
	$content = '';
	$content .='
		<div id="qcf-options"> 
		<div id="qcf-style"> 
		<h2>From settings and options</h2>
		<p>The <a href= "?page=quick-contact-form/quick-contact-form.php&tab=settings">Form Settings</a> page allows you to select and order which fields are displayed, change the labels and have them validated. You can also add an optional spambot cruncher. When you save the changes the updated form will preview on the right.</p>
		<p>To change the width of the form, border style and background colour use the <a href= "?page=quick-contact-form/quick-contact-form.php&tab=settings">styling</a> page. You also have the option to add some custom CSS.</p>
		<p>You can create your own <a href= "?page=quick-contact-form/quick-contact-form.php&tab=error">error messages</a> and configure <a href= "?page=quick-contact-form/quick-contact-form.php&tab=reply">how the message is sent</a> as well.</p>
		<p>If you want to allow attachments then use the <a href= "?page=quick-contact-form/quick-contact-form.php&tab=error">attachments page</a>. Make sure to restrict the file types people can send. You will also have to adjust the field width. This is because the input field ignores just about all styling. <a href="http://www.quirksmode.org/dom/inputfile.html" target="_blank">Quirksmode</a> has some suggestions on how to manage this but it&#146;s not easy. Even then, every browser is different so the attachment field won&#146;t look the same every time.</p>
		<p>If it all goes a bit pear shaped you can <a href= "?page=quick-contact-form/quick-contact-form.php&tab=reset">reset everything</a> to the defaults.</p>
		<h2>Problems</h2>
		<p>Some users report that they can&#146;t send emails to gmail, hotmail and other webmail type accounts. This isn&#146;t a problem with the plugin, it&#146;s usually a block with the hosting package. Make sure your host has no restrictions on the php mail function. Some people have found it works by adding asterisks to their code like this:<br>
		<code>$headers = 	"From: {$values[\'qcfname1\']}<*{$values[\'qcfname2\']}*>\r\n"</code><br>
		If it does work for you them please let me know (it solved the problem on the <a href="http://wordpress.org/support/topic/contact-form-7-not-working-3/page/5" target="_blank">CF7 and CCF</a> plugins).</p>
		<p>There is some installation info and FAQs on the <a href="http://wordpress.org/extend/plugins/quick-contact-form/installation/" target="_blank">wordpress plugin page</a>. Some development info is on <a href="http://quick-plugins.com/quick-contact-form/" target="_blank">my plugin page</a> along with a feedback form. Or you can email me at <a href="mailto:mail@quick-plugins.com">mail@quick-plugins.com</a>.</p>
		</div>
		</div>
		<div id="qcf-options"> 
		<div id="qcf-style"> 
		<h2>Validation</h2>
		<p>Check the validation box if you want a field checked.</p>
		<p>Validation removes all the unwanted characters (URLs, HTML, javascript and so on) leaving just the alphabet, numbers and a few punctuation marks).</p>
		<p>It then checks that the field isn&#146;t empty or that the user has actually typed something in the box. The error message suggests that they need to enter &lt;something&gt; where something is the info you need (name, email, phone number, colour etc).</p>
		<p>It also checks for a valid email address and phone number. This only takes place in the telephone and email fields. If you want the email address and telephone number format validated even if they aren&#146;t reuquired fields, then check the boxes on the <a href= "?page=quick-contact-form/quick-contact-form.php&tab=error">error messages</a> page.</p>
		</div>
		</div>';
	echo $content;
	}

function qcf_reset_page() {
	if (isset($_POST['qcf_reset'])) {
		if (isset($_POST['qcf_reset_email'])) {
			$qcf_email = "";
			update_option('qcf_email', $qcf_email);
			qcf_admin_notice("<b>Your email adress has been reset.</b> Use the <a href= '?page=quick-contact-form/quick-contact-form.php&tab=setup'>Setup</a> tab to add a new email address");
			}
		if (isset($_POST['qcf_reset_options'])) {
			$qcf = qcf_get_default_options();
			update_option('qcf_settings', $qcf);
			qcf_admin_notice("<b>Form settings have been reset.</b> Use the <a href= '?page=quick-contact-form/quick-contact-form.php&tab=settings'>Form Settings</a> tab to change the settings");
			}
		if (isset($_POST['qcf_reset_attach'])) {
			$attach = qcf_get_default_attach();
			update_option('qcf_attach', $attach);
			qcf_admin_notice("<b>The attachment options have been reset.</b>. Use the <a href= '?page=quick-contact-form/quick-contact-form.php&tab=attach'>Attachments</a> tab to change the settings");
			}
		if (isset($_POST['qcf_reset_reply'])) {
			$reply = qcf_get_default_reply();
			update_option('qcf_reply', $reply);
			qcf_admin_notice("<b>The send options have been reset.</b>. Use the <a href= '?page=quick-contact-form/quick-contact-form.php&tab=reply'>Reply Options</a> tab to change the settings");
			}
		if (isset($_POST['qcf_reset_styles'])) {
			$style = qcf_get_default_style();
			update_option('qcf_style', $style);
			qcf_admin_notice("<b>The styles have been reset.</b>. Use the <a href= '?page=quick-contact-form/quick-contact-form.php&tab=styles'>Styling</a> tab to change the settings");
			}
		if (isset($_POST['qcf_reset_message'])) {
			$qcf_message = array();
			update_option('qcf_message', $qcf_message);
			qcf_admin_notice("<b>The message list has been deleted.</b> Only those messages received from today will be displayed.");
			}
		if (isset($_POST['qcf_reset_errors'])) {
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
		<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_reset_email"> Email address</p>
		<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_reset_options"> Form settings</p>
		<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_reset_attach"> Attachments</p>
		<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_reset_styles"> Styling (also delete any custom CSS)</p>
		<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_reset_reply"> Send and thank-you options</p>
		<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_reset_errors"> Error messages</p>
		<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_reset_message"> Dashboard messages - this won&#146;t delete any emails you have recieved.</p>
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

function qcf_verify_form(&$values, &$errors) {
	$qcf = qcf_get_stored_options();
	$error = qcf_get_stored_error();
	$attach = qcf_get_stored_attach();
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
		$tmp_name = $_FILES['filename']['tmp_name'];
		$name = $_FILES['filename']['name'];
		$size = $_FILES['filename']['size'];
		if (file_exists($tmp_name)) {
			if ($size > $attach['qcf_attach_size']) $errors['attach'] = '<p class="error">' . $attach['qcf_attach_error_size'] . '</p>'; 
			$ext = substr(strrchr($name,'.'),1);
			$pos = strpos($qcf['qcf_attach_type'],$ext);
			if (strpos($attach['qcf_attach_type'],$ext) === false) $errors['attach'] = '<p class="error">' . $attach['qcf_attach_error_type'] . '</p>'; 
			}
	return (count($errors) == 0);	
	}

function qcf_display_form( $values, $errors) {
	$qcf = qcf_get_stored_options();
	$error = qcf_get_stored_error();
	$attach = qcf_get_stored_attach();
	$style = qcf_get_stored_style();
	if ($style['background'] == 'white') $background = 'style="background:#FFF"';
	if ($style['background'] == 'color') $background = 'style="background: ' . $style['backgroundhex'] . '"';
	if ($style['border'] == "none") $padding = 0;
	else $padding = 6;
	if ($style['widthtype'] == 'pixel') {
		$width = ' style="width: ' . preg_replace("/[^0-9]/", "", $style['width']) . 'px"';
		}
	else {
		$width = ' style="width: 100%;"';
		$submit = ' style="width: calc(100% - 14px);"';
		}
	if (!empty($qcf['title'])) $qcf['title'] = '<h2>' . $qcf['title'] . '</h2>';
	if (!empty($qcf['blurb'])) $qcf['blurb'] = '<p>' . $qcf['blurb'] . '</p>';
	if (!empty($qcf['mathscaption'])) $qcf['mathscaption'] = '<p class="input">' . $qcf['mathscaption'] . '</p>';
	$content = "<div id='qcf-style' " . $width . ">\r\t<div id='" . $style['border'] . "' " . $background . ">\r\t";
	if (count($errors) > 0)
		$content .= "<h2>" . $error['errortitle'] . "</h2>\r\t<p class='error'>" . $error['errorblurb'] . "</p>\r\t";
	else
		$content .= $qcf['title'] . "\r\t" . $qcf['blurb'] . "\r\t";
	
	$content .= "<form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\r\t";
		foreach (explode( ',',$qcf['sort']) as $name)
		{
		$required = ( $qcf['required'][$name]) ? 'class="required"' : '';
		if ($qcf['active_buttons'][$name] == "on") {
			switch ( $name )
				{
				case 'field1':
					$content .= $errors['qcfname1'];
					$content .= '<input type="text" ' . $required . ' label="Name" name="qcfname1" value="' . $values['qcfname1'] . '" onfocus="clickclear(this, \'' . $values['qcfname1'] . '\')" onblur="clickrecall(this, \'' . $values['qcfname1'] . '\'">'."\r\t";
					break;
				case 'field2':
					$content .= $errors['qcfname2'];
					$content .= '<input type="text" ' . $required . ' label="Name" name="qcfname2"  value="' . $values['qcfname2'] . '" onfocus="clickclear(this, \'' . $values['qcfname2'] . '\')" onblur="clickrecall(this, \'' . $values['qcfname2'] . '\'">'."\r\t";
					break;
				case 'field3':
					$content .= $errors['qcfname3'];
					$content .= '<input type="text" ' . $required . ' label="Name" name="qcfname3"  value="' . $values['qcfname3'] . '" onfocus="clickclear(this, \'' . $values['qcfname3'] . '\')" onblur="clickrecall(this, \'' . $values['qcfname3'] . '\'">'."\r\t";
					break;
				case 'field4':
					$content .= $errors['qcfname4'];
					$content .= '<textarea ' . $required . '  rows="' . $qcf['lines'] . '" label="Name" name="qcfname4" onFocus="this.value=\'\'; this.onfocus=null;">' . strip_tags(stripslashes($values['qcfname4'])) . '</textarea>'."\r\t";
					break;
				case 'field5':
					$content .= $errors['qcfname5'];
					$content .= '<select name="qcfname5" ' . $required . ' ><option value="' . $qcf['label'][$name] . '">' . $qcf['label'][$name] . '</option>'."\r\t";
						$arr = explode(",",$qcf['dropdownlist']);
						foreach ($arr as $item) 
							{
							$selected = '';
							if ($values['qcfname5'] == $item) $selected = 'selected';
							$content .= '<option value="' .  $item . '" ' . $selected .'>' .  $item . '</option>'."\r\t";
							}
					$content .= '</select>'."\r\t";
					break;
				case 'field6':
					if ($errors['qcfname6']) $content .= $errors['qcfname6'];
					else $content .= '<p class="input">' . $qcf['label'][$name] . '</p>';
					$arr = explode(",",$qcf['checklist']);
					foreach ($arr as $item)
						{
						$checked = '';
						if ($values['qcfname6_'. str_replace(' ','',$item)] == $item) $checked = 'checked';
						$content .= '<input type="checkbox" style="margin:0; padding: 0; border: none" name="qcfname6_' . str_replace(' ','',$item) . '" value="' .  $item . '" ' . $checked . '> ' .  $item . '<br>';
						}
					break;
					case 'field7':
					$content .= '<p class="input">' . $qcf['label'][$name] . '</p>';
					$arr = explode(",",$qcf['radiolist']);
					foreach ($arr as $item)
						{
						$checked = '';
						if ($values['qcfname7'] == $item) $checked = 'checked';
						if ($item === reset($arr)) $content .= '<input type="radio" style="margin:0; padding: 0; border: none" name="qcfname7" value="' .  $item . '" checked> ' .  $item . ' ';
						else $content .=  '<input type="radio" style="margin:0; padding: 0; border: none" name="qcfname7" value="' .  $item . '" ' . $checked . '> ' .  $item . ' ';
						}
					break;
				case 'field8':
					$content .= $errors['qcfname8'];
					$content .= '<input type="text" ' . $required . ' label="Name" name="qcfname8" value="' . $values['qcfname8'] . '" onfocus="clickclear(this, \'' . $values['qcfname8'] . '\')" onblur="clickrecall(this, \'' . $values['qcfname8'] . '\'">'."\r\t";
					break;
				case 'field9':
					$content .= $errors['qcfname9'];
					$content .= '<input type="text" ' . $required . ' label="Name" name="qcfname9"  value="' . $values['qcfname9'] . '" onfocus="clickclear(this, \'' . $values['qcfname9'] . '\')" onblur="clickrecall(this, \'' . $values['qcfname9'] . '\'">'."\r\t";
					break;
				}
			}
		}
	if ($attach['qcf_attach'] == "checked") {
		if ($errors['attach']) $content .= $errors['attach'];
		else $content .= '<p class="input">' . $attach['qcf_attach_label'] . '</p>'."\r\t";
		$size = $attach['qcf_attach_width'];
		$content .= '<input type="file" size="' . $size . '" name="filename">'."\r\t";
		}
	if ($qcf['captcha'] == "checked") {
		if ($errors['captcha']) $content .= $errors['captcha'];
		else $content .= $qcf['mathscaption']; 
		$content .= '<p id="sums">' . $values['thesum'] . ' = <input type="text" class="required" style="width:3em; font-size:100%" label="Sum" name="maths"  value="' . $values['maths'] . '"></p> 
		<input type="hidden" name="answer" value="' . $values['answer'] . '" />
		<input type="hidden" name="thesum" value="' . $values['thesum'] . '" />';
		}
	$content .= '<input type="submit" id="submit" ' .  ' name="submit" value="' . $qcf['send'] . '">'."\r\t".
		'</form>'."\r\t".
		'</div>'."\r\t".
		'</div>'."\r\t";
	echo $content;
	}
	
function qcf_process_form($values) {
	$qcf = qcf_get_stored_options();
	$reply = qcf_get_stored_reply();
	$style = get_option('qcf_style');
	$qcf_email = get_option('qcf_email');
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
	$content = '';
	foreach (explode( ',',$qcf['sort']) as $item)
		if ($qcf['active_buttons'][$item]) {
			switch ( $item ) {
				case 'field1':
					if ($values['qcfname1'] == $qcf['label'][$item]) $values['qcfname1'] ='';
					$content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags($values['qcfname1']) . '</p>';
					break;
				case 'field2':
					if ($values['qcfname2'] == $qcf['label'][$item]) $values['qcfname2'] ='';
					$content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags($values['qcfname2']) . '</p>';
					break;
				case 'field3':
					if ($values['qcfname3'] == $qcf['label'][$item]) $values['qcfname3'] ='';
					$content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags($values['qcfname3']) . '</p>';
					break;
				case 'field4':
					if ($values['qcfname4'] == $qcf['label'][$item]) $values['qcfname4'] ='';
					$content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags(stripslashes($values['qcfname4'])) . '</p>';
					break;
				case 'field5':
					if ($values['qcfname5'] == $qcf['label'][$item]) $values['qcfname5'] ='';
					$content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . $values['qcfname5'] . '</p>';
					break;
				case 'field6':
					$arr = explode(",",$qcf['checklist']);
					$content .= '<p><b>' . $qcf['label'][$item] . ': </b>';
					foreach ($arr as $key) if ($values['qcfname6_' . str_replace(' ','',$key)]) $checks .= $key . ', ';
					$content .= rtrim( $checks , ', ' ) . '</p>';
					break;
				case 'field7':
					if ($values['qcfname7'] == $qcf['label'][$item]) $values['qcfname7'] ='';
					$content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . $values['qcfname7'] . '</p>';
					break;
				case 'field8':
					if ($values['qcfname8'] == $qcf['label'][$item]) $values['qcfname8'] ='';
					$content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags($values['qcfname8']) . '</p>';
					break;
				case 'field9':
					if ($values['qcfname9'] == $qcf['label'][$item]) $values['qcfname9'] ='';
					$content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags($values['qcfname9']) . '</p>';
				}
			}
	$sendcontent = "<html><h2>The message is:</h2>".$content;
	if ($reply['tracker']) $sendcontent .= "<p>Message was sent from: <b>".$page."</b></p><p>Senders IP address: <b>".$ip."</b></p>";
	$sendcontent .="</html>";
	
	$subject = "{$reply['subject']} {$addon}";
			
	$tmp_name = $_FILES['filename']['tmp_name'];
	$type = $_FILES['filename']['type'];
	$name = $_FILES['filename']['name'];
	$size = $_FILES['filename']['size'];
	if (file_exists($tmp_name))
		{
 		if(is_uploaded_file($tmp_name))
		 	{
			$file = fopen($tmp_name,'rb');				//open the file
     		$data = fread($file,filesize($tmp_name));	//read the file
     		fclose($file);								// close the file
     		$data = chunk_split(base64_encode($data));	// encode and split
    		}
			$bound_text = "x".md5(mt_rand())."x";
			$bound = "--".$bound_text."\r\n";
			$bound_last = "--".$bound_text."--\r\n";
 			$headers = "From: {$values['qcfname1']}<{$values['qcfname2']}>\r\n"
			."MIME-Version: 1.0\r\n"
  			."Content-Type: multipart/mixed; boundary=\"$bound_text\"";
			$message .= "If you can see this MIME than your client doesn't accept MIME types!\r\n"
  			.$bound;
  	 
			$message .= "Content-Type: text/html; charset=\"iso-8859-1\"\r\n"
  			."Content-Transfer-Encoding: 7bit\r\n\r\n"
  			.$sendcontent."\r\n"
  			.$bound;
  	  
			$message .= "Content-Type: ".$type."; name=\"".$name."\"\r\n" 
  			."Content-Transfer-Encoding: base64\r\n"
  			."Content-disposition: attachment; file=\"".$name."\"\r\n" 
  			."\r\n"
  			.$data
  			.$bound_last; 
		}
	else {
		$headers = "From: {$values['qcfname1']}<{$values['qcfname2']}>\r\n"
			. "MIME-Version: 1.0\r\n"
			. "Content-Type: text/html; charset=\"utf-8\"\r\n"; 
		$message = $sendcontent;
		}

	mail($qcf_email, $subject, $message, $headers);

	if ( $reply['qcf_redirect'] == 'checked') {
		$location = $reply['qcf_redirect_url'];	
		echo "<meta http-equiv='refresh' content='0;url=$location' />";
		}
	else {
		echo '<div id="qcf-style">
		<div id="'.$style['border'].'" style="width:'.$style['width'].'px;">'
		.$reply['replytitle'].$reply['replyblurb'];
		if ($reply['messages']) echo $content;
		echo '</div></div>'; 
		}	
	$qcf_message = get_option('qcf_message');
	if(!is_array($qcf_message)) $qcf_message = array();
	if ($values['qcfname1'] == $qcf['label']['field1']) $values['qcfname1'] ='';
	$sentdate = date('d M Y');
	$qcf_message[] = array('field1' => $values['qcfname1'] , 'field2' => $values['qcfname2'] , 'field4' => $values['qcfname4'] , date => $sentdate,);
	update_option('qcf_message',$qcf_message);
	}
	
function qcf_loop() {
	ob_start();
	if (isset($_POST['submit'])) {
		$formvalues = $_POST;
		$formerrors = array();
    	if (!qcf_verify_form($formvalues, $formerrors)) qcf_display_form($formvalues, $formerrors);
    	else qcf_process_form($formvalues);
		}
	else {
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
		qcf_display_form( $values , null );
		}
	$output_string=ob_get_contents();
	ob_end_clean();
	return $output_string;
	}

class qcf_widget extends WP_Widget {
	function qcf_widget() {
		$widget_ops = array('classname' => 'qcf_widget', 'description' => 'Add the Quick Contact Form to your sidebar');
		$this->WP_Widget('qcf_widget', 'Quick Contact Form', $widget_ops);
		}
	function form($instance) {
		echo '<p>All options for the quick contact form are changed on the plugin <a href="'.get_admin_url().'options-general.php?page=quick-contact-form/quick-contact-form.php">Settings</a> page.</p>';
		}
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['email'] = $new_instance['email'];
		return $instance;
		}
	function widget($args, $instance) {
 	   	extract($args, EXTR_SKIP);
		echo qcf_loop();
		}
	}

add_action( 'widgets_init', create_function('', 'return register_widget("qcf_widget");') );

function qcf_add_dashboard_widgets() {
	$reply = qcf_get_stored_reply();
	if ( $reply['dashboard'] == 'checked' ) {
		wp_add_dashboard_widget( 'qcf_dashboard_widget', 'Latest Messages', 'qcf_dashboard_widget' );	
		}
	}

function qcf_dashboard_widget() {
	$message = get_option( 'qcf_message' );
	if(!is_array($message)) $message = array();
	$qcf = qcf_get_stored_options ();
	$dashboard = '<div id="qcf-widget">'
		. '<table cellspacing="0">'
		. '<tr>'
		. '<th>From</th><th>'.$qcf['label']['field2'].'</th><th>'.$qcf['label']['field4'].'</th><th>Date</th>'
		. '</tr>';
	foreach(array_reverse( $message ) as $value) {
		$dashboard .= '<tr>';
		foreach($value as $item) {
			if (strlen($item) > 25) $ellipses = ' ...';
			else $ellipses = '';
			$trim = substr($item, 0 , 25).$ellipses;
			$dashboard .= '<td>'.$trim.'</td>';
			}
		$dashboard .= '</tr>';
 		}
		$dashboard .= '</table>'
			. '</div>';
		echo $dashboard;
	}
	
function qcf_use_custom_css () {
	$style = qcf_get_stored_style();
	if ($style['use_custom'] == 'checked') {
		$code = "<style type=\"text/css\" media=\"screen\">\r\n" . $style['styles'] . "\r\n</style>\r\n";
		echo $code;
		}
	}
	
function qcf_get_stored_options () {
	$qcf = get_option('qcf_settings');
	if(!is_array($qcf)) $qcf = array();
	$option_default = qcf_get_default_options();
	$qcf = array_merge($option_default, $qcf);
	return $qcf;
	}

function qcf_get_default_options () {
	$qcf = array();
	$qcf['active_buttons'] = array( 'field1'=>'on' , 'field2'=>'on' , 'field3'=>'' , 'field4'=>'on' , 'field5'=>'' , 'field6'=>'' ,  'field7'=>'' , 'field8'=>'' ,  'field9'=>'');	
	$qcf['required'] = array('field1'=>'checked' , 'field2'=>'checked' , 'field3'=>'' , 'field4'=>'' , 'field5'=>'' , 'field6'=>'' , 'field7'=>'' , 'field8'=>'' , 'field9'=>'');
	$qcf['label'] = array( 'field1'=>'Your Name' , 'field2'=>'Email' , 'field3'=>'Telephone' , 'field4'=>'Message' , 'field5'=>'Select an option' , 'field6'=>'Check at least one box' ,  'field7'=>'Radio' , 'field8'=>'Website' ,  'field9'=>'Subject');
	$qcf['sort'] = implode(',',array('field1', 'field2' , 'field3' , 'field4' , 'field5' , 'field6' , 'field7' , 'field8' , 'field9'));
	$qcf['type'] = array( 'field1' => 'text' , 'field2' => 'email' , 'field3' => 'phone' , );
	$qcf['lines'] = 6;
	$qcf['dropdownlist'] = 'Pound,Dollar,Euro,Yen,Triganic Pu';
	$qcf['checklist'] = 'Donald Duck,Mickey Mouse,Goofy';
	$qcf['radiolist'] = 'Large,Medium,Small';
	$qcf['title'] = 'Enquiry Form';
	$qcf['blurb'] = 'Fill in the form below and we will be in touch soon';
	$qcf['send'] = 'Send it!';
	$qcf['captcha'] = '';
	$qcf['mathscaption'] = 'Spambot blocker question';
	return $qcf;
	}

function qcf_get_stored_attach () {
	$attach = get_option('qcf_attach');
	if(!is_array($attach)) $attach = array();
	$option_default = qcf_get_default_attach();
	$attach = array_merge($option_default, $attach);
	return $attach;
	}

function qcf_get_default_attach () {
	$attach = array();
	$attach['qcf_attach'] = '';
	$attach['qcf_attach_label'] = 'Attach an image (Max 100kB)';
	$attach['qcf_attach_size'] = '100000';
	$attach['qcf_attach_type'] = 'jpg,gif,png,pdf';
	$attach['qcf_attach_width'] = '15';
	$attach['qcf_attach_error_size'] = 'File is too big';
	$attach['qcf_attach_error_type'] = 'Filetype not permitted';
	return $attach;
	}

function qcf_get_stored_style() {
	$style = get_option('qcf_style');
	if(!is_array($style)) $style = array();
	$option_default = qcf_get_default_style();
	$style = array_merge($option_default, $style);
	return $style;
	}

function qcf_get_default_style() {
	$style['width'] = 280;
	$style['widthtype'] = 'pixel';
	$style['border'] = 'rounded';
	$style['background'] = 'white';
	$style['backgroundhex'] = '#FFF';
	$style['use_custom'] = '';
	$style['styles'] = "#qcf-style {\r\n\r\n}";
	return $style;
	}

function qcf_get_stored_reply () {
	$reply = get_option('qcf_reply');
	if(!is_array($reply)) $reply = array();
	$option_default = qcf_get_default_reply();
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
	$reply['qcf_redirect'] = '';
	$reply['qcf_redirect_url'] = '';
	return $reply;
	}

function qcf_get_stored_error () {
	$error = get_option('qcf_error');
	if(!is_array($error)) $error = array();
	$option_default = qcf_get_default_error(); 	
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
	return $error;
	}