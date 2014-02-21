<?php

add_action('init', 'qcf_init');
add_action('admin_menu', 'qcf_page_init');
add_action( 'admin_notices', 'qcf_admin_notice' );
add_action( 'wp_dashboard_setup', 'qcf_add_dashboard_widgets' );
add_filter( 'plugin_action_links', 'qcf_plugin_action_links', 10, 2 );

$settingsurl = plugins_url('settings.css', __FILE__);
wp_register_style('qcf_settings', $settingsurl);
wp_enqueue_style( 'qcf_settings');

/* register_deactivation_hook( __FILE__, 'qcf_delete_options' ); */
register_uninstall_hook(__FILE__, 'qcf_delete_options');

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
		echo "<a class='nav-tab$class' href='?page=quick-contact-form/settings.php&tab=$tab'>$name</a>";
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
		<form method="post" action="">
		<h2>Setting up the Quick Contact Form</h2>
		<p><span style="color:red; font-weight: bold;">Important!</span> Enter YOUR email address below and save the changes. This won&#146;t display, it&#146;s just so the plugin knows where to send the message.</p>
		<p>To send to multiple addresses, put a comma betweeen each address.</p>
		<p><input type="text" style="width:100%" label="Email" name="qcf_email" value="' . $qcf_email . '" /></p>
		<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /></p>
		<h2>What&#146;s New</h2>
		<p>It&#146;s all about <a href="?page=quick-contact-form/settings.php&tab=styles">style</a>. You now have various font options and I&#146;ve put the stylesheets the document head where they belong.</p>
		<p>If you look at the <a href="?page=quick-contact-form/settings.php&tab=reply">send options</a> there are now better ways to track your visitors.</p>
		</form>
		</div>
		<div id="qcf-options"> 
		<h2>Options and Settings</h2>
		<p>To change the layout of the form, add or remove fields, change the order they appear and edit the labels and captions use the <a href="?page=quick-contact-form/settings.php&tab=settings">Form Settings</a> tab.</p>
		<p>Use the <a href="?page=quick-contact-form/settings.php&tab=reply">Send Options</a> tab to change the thank you message and how the form is sent.</p>
		<p>To change the way the form looks use the <a href="?page=quick-contact-form/settings.php&tab=styles">styling</a> tab.</p>
		<p>You can also customise the <a href="?page=quick-contact-form/settings.php&tab=error">error messages</a>.</p>
		<p>If it all goes wrong you can <a href="?page=quick-contact-form/settings.php&tab=reset">reset</a> everything.</p>
		<h2>Adding the contact form to your site</h2>
		<p>To add the contact form your posts or pages use the short code: <code>[qcf]</code>.<br />
		<p>To add it to your theme files use <code>&lt;?php echo do_shortcode("[qcf]"); ?&gt;</code></p>
		<p>There is also a widget called "Quick Contact Form" you can drag and drop into your sidebar.</p>
		<p>That&#146;s it. The form is ready to use.</p>
		</div>';
	echo $content;
	}

function qcf_form_options () {
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
		<form id="qcf_settings_form" method="post" action="">
		<h2>Form Title and Introductory Blurb</h2>
		<p>Form title (leave blank if you don&#146;t want a heading):</p>
		<p><input type="text" style="width:100%" name="qcfname_title" value="' . $qcf['title'] . '" /></p>
		<p>This is the blurb that will appear below the heading and above the form (leave blank if you don&#146;t want any blurb):</p>
		<p><input type="text" style="width:100%" name="qcfname_blurb" value="' . $qcf['blurb'] . '" /></p>
		<h2>Form Fields</h2>
		<p>
		<span style="margin-left:7px;width:180px;">Field Selection & Label</span>
		<span style="width:70px;">Field Type</span>
		<span style="width:90px;">Validation</span>
		<span style="float:right; width:50px;">Position</span></p>
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
		<div id="qcf-options"> 
		<h2>Form Preview</h2>
		<p>Note: The preview form uses the wordpress admin styles. Your form will use the theme styles so won&#146;t look exactly like the one below.</p>';
	$content .=	qcf_loop();
	$content .= '<p>Have you set up your <a href="?page=quick-contact-form/settings.php&tab=reply">reply options</a>?</p>
		<p>If you are not using English then you can customise your <a href="?page=quick-contact-form/settings.php&tab=error">error messages</a>.</p>
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
		<div id="qcf-options">
		<h2>Form Preview</h2>
		<p>Note: The preview form uses the wordpress admin styles. Your form will use the theme styles so won&#146;t look exactly like the one below.</p>';
	$content .=	qcf_loop();
	$content .= '</div>';
	echo $content;
	}

function qcf_styles() {
	if( isset( $_POST['Submit'])) {
	$style['font'] = $_POST['font'];
	$style['font-family'] = $_POST['font-family'];
	$style['font-size'] = $_POST['font-size'];
	$style['width'] = $_POST['width'];
	$style['widthtype'] = $_POST['widthtype'];
	$style['border'] = $_POST['border'];
	$style['background'] = $_POST['background'];
	$style['corners'] = $_POST['corners'];
	$style['backgroundhex'] = stripslashes( $_POST['backgroundhex']);
	$style['use_custom'] = $_POST['use_custom'];
	$style['styles'] = stripslashes( $_POST['styles']);
	update_option( 'qcf_style', $style);
	qcf_admin_notice("The form styles have been updated.");
	}
	$qcf = qcf_get_stored_options();
	$style = qcf_get_stored_style();
	$$style['font'] = 'checked'; 
	$$style['border'] = 'checked'; 
	$$style['background'] = 'checked'; 
	$$style['widthtype'] = 'checked';
	$$style['corners'] = 'checked';
	$content ='
	<div id="qcf-options"> 
	<form method="post" action="">
	<h2>Form Width</h2>
	<p>
	<input style="margin: 0; padding: 0; border: none;" type="radio" name="widthtype" value="percent" ' . $percent . ' /> 100% (fill the available space)<br />
	<input style="margin: 0; padding: 0; border: none;" type="radio" name="widthtype" value="pixel" ' . $pixel . ' /> Pixel (fixed)</p>
	<p>Enter the width of the form in pixels. Just enter the value, no need to add &#146;px&#146;. The current width is as you see it here.</p>
	<p><input type="text" style="width:4em" label="width" name="width" value="' . $style['width'] . '" /> px</p>
	<h2>Font Options</h2>
	<p>
		<input type="radio" style="margin:0; padding: 0; border: none" name="font" value="theme" ' . $theme . ' /> Use your theme font styles<br />
		<input type="radio" style="margin:0; padding: 0; border: none" name="font" value="plugin" ' . $plugin . ' /> Use Plugin font styles (enter font family and size below)
	</p>
	<p>Font Family: <input type="text" style="width:15em" label="font-family" name="font-family" value="' . $style['font-family'] . '" /></p>
	<p>Font Size: <input type="text" style="width:6em" label="font-size" name="font-size" value="' . $style['font-size'] . '" /></p>
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
	<h2>Input field corners</h2>
	<p>
		<input type="radio" style="margin:0; padding: 0; border: none" name="corners" value="corner" ' . $corner . ' /> Use theme settings<br />
		<input type="radio" style="margin:0; padding: 0; border: none" name="corners" value="square" ' . $square . ' /> Square corners<br />
		<input type="radio" style="margin:0; padding: 0; border: none" name="corners" value="round" ' . $round . ' /> 5px rounded corners
	</p>
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
		$reply['tracker'] = $_POST['tracker'];
		$reply['url'] = $_POST['url'];
		$reply['page'] = $_POST['page'];
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
		<form method="post" action="">
		<h2>Send Options</h2>
		<h3>Email subject</h3>
		<p>The message subject has two parts: the bit in the text box plus the option below.</p>
		<p><input type="text"  style="width:100%" name="subject" value="' . $reply['subject'] . '"/></p>
		<p><input type="radio" style="margin:0; padding: 0; border: none" name="subjectoption" value="sendername" ' . $sendername . '> sender&#146;s name (the contents of the first field)</p>
		<p><input type="radio" style="margin:0; padding: 0; border: none" name="subjectoption" value="senderpage" ' . $senderpage . '> page title (only works if sent from a post or a page)</p>
		<p><input type="radio" style="margin:0; padding: 0; border: none" name="subjectoption" value="sendernone" ' . $sendernone . '> blank</p>
		<h3>Tracking</h3>
		<p>Adds the tracking information to the message you receive.</p>
		<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="page" ' . $reply['page'] . ' value="checked"> Show page title</p>
		<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="tracker" ' . $reply['tracker'] . ' value="checked"> Show IP address</p>
		<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="url" ' . $reply['url'] . ' value="checked"> Show URL</p>
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
		<h2>From settings and options</h2>
		<p>The <a href= "?page=quick-contact-form/settings.php&tab=settings">Form Settings</a> page allows you to select and order which fields are displayed, change the labels and have them validated. You can also add an optional spambot cruncher. When you save the changes the updated form will preview on the right.</p>
		<p>To change the width of the form, border style and background colour use the <a href= "?page=quick-contact-form/settings.php&tab=settings">styling</a> page. You also have the option to add some custom CSS.</p>
		<p>You can create your own <a href= "?page=quick-contact-form/settings.php&tab=error">error messages</a> and configure <a href= "?page=quick-contact-form/settings.php&tab=reply">how the message is sent</a> as well.</p>
		<p>If you want to allow attachments then use the <a href= "?page=quick-contact-form/settings.php&tab=error">attachments page</a>. Make sure to restrict the file types people can send. You will also have to adjust the field width. This is because the input field ignores just about all styling. <a href="http://www.quirksmode.org/dom/inputfile.html" target="_blank">Quirksmode</a> has some suggestions on how to manage this but it&#146;s not easy. Even then, every browser is different so the attachment field won&#146;t look the same every time.</p>
		<p>If it all goes a bit pear shaped you can <a href= "?page=quick-contact-form/settings.php&tab=reset">reset everything</a> to the defaults.</p>
		<h2>Problems</h2>
		<p>Some users report that they can&#146;t send emails to gmail, hotmail and other webmail type accounts. This isn&#146;t a problem with the plugin, it&#146;s usually a block with the hosting package. Make sure your host has no restrictions on the php mail function. Some people have found it works by adding asterisks to their code like this:<br>
		<code>$headers = 	"From: {$values[\'qcfname1\']}<*{$values[\'qcfname2\']}*>\r\n"</code><br>
		If it does work for you them please let me know (it solved the problem on the <a href="http://wordpress.org/support/topic/contact-form-7-not-working-3/page/5" target="_blank">CF7 and CCF</a> plugins).</p>
		<p>There is some installation info and FAQs on the <a href="http://wordpress.org/extend/plugins/quick-contact-form/installation/" target="_blank">wordpress plugin page</a>. Some development info is on <a href="http://quick-plugins.com/quick-contact-form/" target="_blank">my plugin page</a> along with a feedback form. Or you can email me at <a href="mailto:mail@quick-plugins.com">mail@quick-plugins.com</a>.</p>
		</div>
		<div id="qcf-options"> 
		<h2>Validation</h2>
		<p>Check the validation box if you want a field checked.</p>
		<p>Validation removes all the unwanted characters (URLs, HTML, javascript and so on) leaving just the alphabet, numbers and a few punctuation marks).</p>
		<p>It then checks that the field isn&#146;t empty or that the user has actually typed something in the box. The error message suggests that they need to enter &lt;something&gt; where something is the info you need (name, email, phone number, colour etc).</p>
		<p>It also checks for a valid email address and phone number. This only takes place in the telephone and email fields. If you want the email address and telephone number format validated even if they aren&#146;t reuquired fields, then check the boxes on the <a href= "?page=quick-contact-form/settings.php&tab=error">error messages</a> page.</p>
		</div>';
	echo $content;
	}

function qcf_reset_page() {
	if (isset($_POST['qcf_reset'])) {
		if (isset($_POST['qcf_reset_email'])) {
			$qcf_email = "";
			update_option('qcf_email', $qcf_email);
			qcf_admin_notice("<b>Your email adress has been reset.</b> Use the <a href= '?page=quick-contact-form/settings.php&tab=setup'>Setup</a> tab to add a new email address");
			}
		if (isset($_POST['qcf_reset_options'])) {
			$qcf = qcf_get_default_options();
			update_option('qcf_settings', $qcf);
			qcf_admin_notice("<b>Form settings have been reset.</b> Use the <a href= '?page=quick-contact-form/settings.php&tab=settings'>Form Settings</a> tab to change the settings");
			}
		if (isset($_POST['qcf_reset_attach'])) {
			$attach = qcf_get_default_attach();
			update_option('qcf_attach', $attach);
			qcf_admin_notice("<b>The attachment options have been reset.</b>. Use the <a href= '?page=quick-contact-form/settings.php&tab=attach'>Attachments</a> tab to change the settings");
			}
		if (isset($_POST['qcf_reset_reply'])) {
			$reply = qcf_get_default_reply();
			update_option('qcf_reply', $reply);
			qcf_admin_notice("<b>The send options have been reset.</b>. Use the <a href= '?page=quick-contact-form/settings.php&tab=reply'>Reply Options</a> tab to change the settings");
			}
		if (isset($_POST['qcf_reset_styles'])) {
			$style = qcf_get_default_style();
			update_option('qcf_style', $style);
			qcf_admin_notice("<b>The styles have been reset.</b>. Use the <a href= '?page=quick-contact-form/settings.php&tab=styles'>Styling</a> tab to change the settings");
			}
		if (isset($_POST['qcf_reset_message'])) {
			$qcf_message = array();
			update_option('qcf_message', $qcf_message);
			qcf_admin_notice("<b>The message list has been deleted.</b> Only those messages received from today will be displayed.");
			}
		if (isset($_POST['qcf_reset_errors'])) {
			$error = qcf_get_default_error();
			update_option('qcf_error', $error);
			qcf_admin_notice("<b>The error messages have been reset.</b> Use the <a href= '?page=quick-contact-form/settings.php&tab=error'>Error Messages</a> tab to change the settings.");
			}
		}
	$content ='
		<div id="qcf-options"> 
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
		</div>';
	echo $content;
	}

function qcf_init() {
	if (is_admin()) {
		wp_enqueue_script('jquery-ui-sortable');
		return;
		}
	}

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
?>