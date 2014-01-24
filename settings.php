<?php
add_action('init', 'qcf_settings_init');
add_action('admin_menu', 'qcf_page_init');
add_action('admin_notices', 'qcf_admin_notice' );
add_action( 'admin_menu', 'add_admin_pages' );

function add_admin_pages() {
	add_menu_page('Messages', 'Messages', 'manage_options','quick-contact-form/quick-contact-messages.php');
	}

/* register_deactivation_hook( __FILE__, 'delete_everything' ); */
register_uninstall_hook(__FILE__, 'delete_everything');

function qcf_page_init() {
	add_options_page('Quick Contact', 'Quick Contact', 'manage_options', __FILE__, 'qcf_tabbed_page');
	}
function qcf_settings_init() {
	qcf_generate_csv();
	return;
	}
function qcf_settings_scripts() {
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_style('qcf_settings',plugins_url('settings.css', __FILE__));
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script('colorpicker-script', plugins_url('color.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
	wp_enqueue_media();
	wp_enqueue_script('qcf-media',plugins_url('media.js', __FILE__ ), array( 'jquery' ), false, true );
	}
add_action('admin_enqueue_scripts', 'qcf_settings_scripts');

function qcf_tabbed_page() {
	$qcf_setup = qcf_get_stored_setup();
	$id=$qcf_setup['current'];
	echo '<div class="wrap">';
	echo '<h1>Quick Contact Form</h1>';
	if ( isset ($_GET['tab'])) {qcf_admin_tabs($_GET['tab']); $tab = $_GET['tab'];} else {qcf_admin_tabs('setup'); $tab = 'setup';}
	switch ($tab) {
		case 'setup' : qcf_setup($id); break;
		case 'settings' : qcf_form_options($id); break;
		case 'styles' : qcf_styles($id); break;
		case 'reply' : qcf_reply_page($id); break;
		case 'error' : qcf_error_page ($id); break;
		case 'attach' : qcf_attach ($id); break;
		case 'help' : qcf_help ($id); break;
		case 'smtp' : qcf_smtp_page(); break;
		case 'reset' : qcf_reset_page($id); break;
		}
	echo '</div>';
	}
function qcf_admin_tabs($current = 'settings') { 
	$tabs = array( 'setup' => 'Setup' , 'settings' => 'Form Settings', 'attach' => 'Attachments' , 'styles' => 'Styling' , 'reply' => 'Send Options' , 'error' => 'Error Messages' , 'help' => 'Help' , 'reset' => 'Reset' ,); 
	$links = array();  
	echo '<h2 class="nav-tab-wrapper">';
	foreach( $tabs as $tab => $name ) {
		$class = ( $tab == $current ) ? ' nav-tab-active' : '';
		echo "<a class='nav-tab$class' href='?page=quick-contact-form/settings.php&tab=$tab'>$name</a>";
		}
	echo '</h2>';
	}
function qcf_setup ($id) {
	$qcf_setup = qcf_get_stored_setup();
	$qcf_email = qcf_get_stored_email();
	if( isset( $_POST['Submit'])) {
		$qcf_setup['alternative'] = $_POST['alternative'];
		if (!empty($_POST['new_form'])) {
			$qcf_setup['current'] = stripslashes($_POST['new_form']);
			$qcf_setup['current'] = preg_replace("/[^A-Za-z]/",'',$qcf_setup['current']);
			$qcf_setup['alternative'] = $qcf_setup['current'].','.$qcf_setup['alternative'];
			}
		else $qcf_setup['current'] = $_POST['current'];
		if (empty($qcf_setup['current'])) $qcf_setup['current'] = '';
		$arr = explode(",",$qcf_setup['alternative']);
		foreach ($arr as $item) $qcf_email[$item] = stripslashes($_POST['qcf_email'.$item]);
		if (!empty($_POST['new_form'])) {
			$email = $qcf_setup['current'];
			$qcf_email[$email] = stripslashes($_POST['new_email']);}
		$qcf_setup['dashboard'] = $_POST['dashboard'];
		update_option( 'qcf_email', $qcf_email);
		update_option( 'qcf_setup', $qcf_setup);
		qcf_admin_notice("The forms have been updated.");	
		}
	$arr = explode(",",$qcf_setup['alternative']);
	foreach ($arr as $item) if ($_POST['deleteform'.$item] == $item && $_POST['delete'.$item] && $item != '') {
		$forms = $qcf_setup['alternative'];
		$qcf_setup['alternative'] = str_replace($id.',','',$forms); 
		$qcf_setup['current'] = '';
		$qcf_setup['email'] = $_POST['email'];
		update_option('qcf_setup', $qcf_setup);
		qcf_delete_things($id);
		qcf_admin_notice("The form named ".$id." has been deleted.");
		$id = '';
		}

		global $current_user;
	get_currentuserinfo();
	$new_email = $current_user->user_email;
	if ($qcf_setup['alternative'] == '' && $qcf_email[''] == '') $qcf_email[''] = $new_email;
	$content ='<div class="qcf-settings"><div class="qcf-options">
		<form method="post" action="">
		<h2 style="color:#B52C00">Existing Forms</h2>
		<table>
		<tr><td><b>Form name&nbsp;&nbsp;</b></td><td><b>Send to this email&nbsp;&nbsp;</b></td><td><b>Shortcode</b></td><td></td></tr>';
		$arr = explode(",",$qcf_setup['alternative']);
		foreach ($arr as $item) {
			if ($qcf_setup['current'] == $item) $checked = 'checked'; else $checked = '';
			if ($item == '') $formname = 'default'; else $formname = $item;
			$content .='<tr><td><input style="margin:0; padding:0; border:none" type="radio" name="current" value="' .$item . '" ' .$checked . ' /> '.$formname.'</td>';
			$content .='<td><input type="text" style="padding:1px;" label="qcf_email" name="qcf_email'.$item.'" value="' . $qcf_email[$item].'" /></td>';
			if ($item) $shortcode = ' id="'.$item.'"'; else $shortcode='';
			$content .= '<td><code>[qpp'.$shortcode.']</code></td><td>';
			if ($item) $content .= '<input type="hidden" name="deleteform'.$item.'" value="'.$item.'"><input type="submit" name="delete'.$item.'" class="button-secondary" value="delete" onclick="return window.confirm( \'Are you sure you want to delete '.$item.'?\' );" />';
			$content .= '</td></tr>';
			}
	$content .= '</table><p>To delete or reset a form use the <a href="?page=quick-contact-form/settings.php&tab=reset">reset</a> tab.</p>
		<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Settings" /></p>
		<h2>Create New Form</h2>
		<p>Enter form name (letters only -  no numbers, spaces or punctuation marks)</p>
		<p><input type="text" label="new_Form" name="new_form" value="" /></p>
		<p>Enter your email address. To send to multiple addresses, put a comma betweeen each address.</p>
		<p><input type="text" label="new_email" name="new_email" value="'.$new_email.'" /></p>
		<input type="hidden" name="alternative" value="' . $qcf_setup['alternative'] . '" />
		<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Create New Form" /></p>
		</form>
		</div>
		<div class="qcf-options" style="float:right"> 
		<h2>Adding the contact form to your site</h2>
		<p>To add the basic contact form to your posts or pages use the shortcode: <code>[qcf]</code>.<br />
		<p>If you have a named form the shortcode is <code>[qcf id="form name"]</code>.<br />
		<p>To add the form to your theme files use <code>&lt;?php echo do_shortcode("[qcf]"); ?&gt;</code></p>
		<p>There is also a widget called "Quick Contact Form" you can drag and drop into a sidebar.</p>
		<p>That\'s it. The form is ready to use.</p>
		<h2>Options and Settings</h2>
		<p>To change the layout of the form, add or remove fields and the order they appear and edit the labels and captions use the <a href="?page=quick-contact-form/settings.php&tab=settings">Form Settings</a> tab.</p>
		<p>Use the <a href="?page=quick-contact-form/settings.php&tab=send">Send Options</a> tab to change the thank you message and how the form is sent.</p>
		<p>To change the way the form looks use the <a href="?page=quick-contact-form/settings.php&tab=styles">styling</a> tab.</p>
		<p>You can also customise the <a href="?page=quick-contact-form/settings.php&tab=error">error messages</a>.</p>
		<p>To see all your messages click on the <b>Messages</b> tab in the dashboard menu or <a href="?page=quick-contact-form/quick-contact-messages.php">click here</a>.</p>
		<p>Please send bug reports to <a href="mailto:mail@quick-plugins.com">mail@quick-plugins.com</a>.</p>';	
	$content .= qcfdonate_loop();
	$content .= '</div></div>';
	echo $content;
	}
function qcf_form_options($id) {
	$active_buttons = array( 'field1' , 'field2' , 'field3' , 'field4' , 'field5' , 'field6' ,  'field7' , 'field8' ,  'field9' , 'field10');
	qcf_change_form_update();
	if( isset( $_POST['Submit'])) {
		foreach ( $active_buttons as $item) {
			$qcf['active_buttons'][$item] = (isset( $_POST['qcf_settings_active_'.$item]) and $_POST['qcf_settings_active_'.$item] == 'on' ) ? true : false;
			$qcf['required'][$item] = (isset( $_POST['required_'.$item]) );
			if (!empty ( $_POST['label_'.$item])) $qcf['label'][$item] = stripslashes($_POST['label_'.$item]);
			}
		$qcf['dropdownlist'] = str_replace(', ' , ',' , $_POST['dropdown_string']);
		$qcf['checklist'] = str_replace(', ' , ',' , $_POST['checklist_string']);
		$qcf['radiolist'] = str_replace(', ' , ',' , $_POST['radio_string']);
		$options = array( 'sort','lines','htmltags','title','blurb','border','captcha','mathscaption','send','datepicker');
		foreach ( $options as $item) $qcf[$item] = stripslashes($_POST[$item]);
		update_option( 'qcf_settings'.$id, $qcf);
		if ($id) qcf_admin_notice("The form settings for ". $id . " have been updated.");
		else qcf_admin_notice("The default form settings have been updated.");
		}
	if( isset( $_POST['Reset'])) {
		delete_option('qcf_settings'.$id);
		if ($id) qcf_admin_notice("The form settings for ".$id. " have been reset.");
		else qcf_admin_notice("The default form settings have been reset.");
		}
	$qcf_setup = qcf_get_stored_setup();
	$id = $qcf_setup['current'];
	$qcf = qcf_get_stored_options($id);
	$content = '<script>
		jQuery(function() {
			var qcf_sort = jQuery( "#qcf_sort" ).sortable({ axis: "y" ,
			update:function(e,ui) {
				var order = qcf_sort.sortable("toArray").join();
				jQuery("#qcf_settings_sort").val(order);
				}
			});
		});
		</script>';
	$content .='<div class="qcf-settings"><div class="qcf-options">';
	if ($id) $content .='<h2 style="color:#B52C00">Form settings for ' . $id . '</h2>';
	else $content .='<h2 style="color:#B52C00">Default form settings</h2>';
	$content .= qcf_change_form($qcf_setup);
	$content .='<form id="qcf_settings_form" method="post" action="">
		<h2>Form Title and Introductory Blurb</h2>
		<p>Form title (leave blank if you don\'t want a heading):</p>
		<p><input type="text" name="title" value="' . $qcf['title'] . '" /></p>
		<p>This is the blurb that will appear below the heading and above the form (leave blank if you don\'t want any blurb):</p>
		<p><input type="text" name="blurb" value="' . $qcf['blurb'] . '" /></p>
		<h2>Form Fields</h2>
		<p>Drag and drop to change order of the fields</p>
		<p style="margin-left:7px;">
		<span style=";width:20%;">Field Selection</span>
		<span style="width:30%;">Label</span>
		<span>Required field</span></p>
		<div style="clear:left"></div>
		<ul id="qcf_sort">';
		foreach (explode( ',',$qcf['sort']) as $name) {
			$checked = ( $qcf['active_buttons'][$name]) ? 'checked' : '';
			$required = ( $qcf['required'][$name]) ? 'checked' : '';
			$datepicker = ($qcf['datepicker']) ? 'checked' : '';
			$lines = $qcf['lines'];
			$options = '';
			switch ( $name ) {
				case 'field1': $type = 'Textbox'; $options = ''; break;
				case 'field2': $type = 'Email'; $options = ' also validates format'; break;
				case 'field3': $type = 'Telephone'; $options = 'also checks number format'; 	break;	
				case 'field4': $type = 'Textarea'; $options = 'Number of rows: <input type="text" style="border:1px solid #415063; width:3em;" name="lines" . value ="' . $qcf['lines'] . '" /><br>
		Allowed Tags:<br> <input type="text" style="border:1px solid #415063; name="htmltags" . value ="' . $qcf['htmltags'] . '" />
		'; break;
				case 'field5': $type = 'Dropdown'; $options = '<span class="description">Options (separate with a comma):</span><br><textarea name="dropdown_string" label="Dropdown" rows="2">' . $qcf['dropdownlist'] . '</textarea>'; break;
				case 'field6': $type = 'Checkbox'; $options = '<span class="description">Options (separate with a comma):</span><br><textarea  name="checklist_string" label="Checklist" rows="2">' . $qcf['checklist'] . '</textarea>'; break;
				case 'field7': $type = 'Radio'; $options = '<span class="description">Options (separate with a comma):</span><br><textarea  name="radio_string" label="Radio" rows="2">' . $qcf['radiolist'] . '</textarea>'; break;
				case 'field8': $type = 'Textbox'; $options = ''; break;
				case 'field9': $type = 'Textbox'; $options = ''; break;
				case 'field10': $type = 'Date'; $options = ''; break;
				}
		$li_class = ( $checked) ? 'button_active' : 'button_inactive';
	$content .= '<li class="'.$li_class.'" id="' . $name . '">
		<div style="float:left; width:20%;"><input type="checkbox" class="button_activate" style="border: none;" name="qcf_settings_active_' . $name . '" ' . $checked . ' />' . $type . '</div>
		<div style="float:left; width:30%;"><input type="text" style="border: border:1px solid #415063; padding: 1px; margin:0;" name="label_' . $name . '" value="' . $qcf['label'][$name] . '"/></div>
		<div style="float:left;width:5%">';
		if ($name <> 'field7') $content .='<input type="checkbox" class="button_activate" style="border: none; padding: 0; margin:0 0 0 5px;" name="required_'.$name.'" '.$required.' /> ';
		else $content .='&nbsp;';
	$content .= '</div><div style="float:left;width:45%">'.$options . '</div><div style="clear:left"></div></li>';
	}
	$content .= '</ul>
		<h2>Submit button caption</h2>
		<p><input type="text" text-align:center" name="send" value="' . $qcf['send'] . '" /></p>
		<h2>Spambot Checker</h2>
		<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="captcha"' . $qcf['captcha'] . ' value="checked" /> Add a maths checker to the form to (hopefully) block most of the spambots.</p>
		<p>Caption (leave blank if you just want the sum):</p>
		<p><input type="text" name="mathscaption" value="' . $qcf['mathscaption'] . '" /></p>
		<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" />  <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset these settings?\' );"/></p>
		</form></div>
		<div class="qcf-options" style="float:right">  
		<h2 style="color:#B52C00">Form Preview</h2>
		<p>Note: The preview form uses the wordpress admin styles. Your form will use the theme styles so won\'t look exactly like the one below.</p>';
	$content .= qcf_loop($id);
	$content .= '<p>Have you set up the <a href="?page=quick-contact-form/settings.php&tab=reply">reply options</a>?</p>
		<p>You can also customise the <a href="?page=quick-contact-form/settings.php&tab=error">error messages</a>.</p>
		</div></div>';
	echo $content;
	}
function qcf_attach ($id) {
	qcf_change_form_update();
	if( isset( $_POST['Submit'])) {
		$options = array( 'qcf_attach','qcf_attach_label','qcf_attach_size','qcf_attach_type','qcf_attach_width','qcf_attach_error_size','qcf_attach_error_type');
		foreach ( $options as $item) $attach[$item] = stripslashes($_POST[$item]);
		update_option( 'qcf_attach'.$id, $attach);
		if ($id) qcf_admin_notice("The attachment settings for ".$id. " have been updated.");
		else qcf_admin_notice("The default form settings have been reset.");
		}
	if( isset( $_POST['Reset'])) {
		delete_option('qcf_attach'.$id);
		if ($id) qcf_admin_notice("The attachment settings for ".$id. " have been reset.");
		else qcf_admin_notice("The default form settings have been reset.");
		}
	$qcf_setup = qcf_get_stored_setup();
	$id=$qcf_setup['current'];
	$attach = qcf_get_stored_attach($id);
	$content ='<div class="qcf-settings"><div class="qcf-options">';
	if ($id) $content .='<h2 style="color:#B52C00">Attachment options for ' . $id . '</h2>';
	else $content .='<h2 style="color:#B52C00">Default attachment options</h2>';
	$content .= qcf_change_form($qcf_setup);
	$content .='<p>If you want your visitors to attach files then use these settings. Take care not to let them attach system files, executables, trojans, worms and a other nasties!</p>
		<form id="qcf_settings_form" method="post" action="">
<table>
<tr><td colspan="2"><h2>Attachment Settings</h2></td></tr>
		<tr><td></td><td><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_attach"' . $attach['qcf_attach'] . ' value="checked" /> User can attach files</td></tr>		<tr><td>Field Label</td><td><input type="text" name="qcf_attach_label" value="' . $attach['qcf_attach_label'] . '" /></td></tr>
		<tr><td>Maximum File size</td><td><input type="text" name="qcf_attach_size" value="' . $attach['qcf_attach_size'] . '" /></td></tr>
		<tr><td>Allowable file types</td><td><input type="text" name="qcf_attach_type" value="' . $attach['qcf_attach_type'] . '" /></td></tr>
		<tr><td>Field size</td><td><p>This is a trial and error number. You can\'t use a \'width\' style as the size is a number of characters. Test using the live form not the preview.</p>
		<p><em>Example: A form width of 280px with a plain border has field width of about 15. With no border it\'s about 18.</em></p>
		<p><input type="text" style="width:5em;" name="qcf_attach_width" value="' . $attach['qcf_attach_width'] . '" /></td></tr>
		<tr><td colspan="2"><h2>Error messages</h2></td></tr>
<tr><td>If the file is too big:</td><td><input type="text" name="qcf_attach_error_size" value="' . $attach['qcf_attach_error_size'] . '" /></td></tr>
	<tr><td>If the filetype is incorrect:</td><td><input type="text" name="qcf_attach_error_type" value="' . $attach['qcf_attach_error_type'] . '" /></td></tr>
</table>
		<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the attachment settings for '.$id.'?\' );"/></p>
		</form>
		</div>
		<div class="qcf-options" style="float:right"> 
		<h2 style="color:#B52C00">Form Preview</h2>
		<p>Note: The preview form uses the wordpress admin styles. Your form will use the theme styles so won\'t look exactly like the one below.</p>';
	$content .= qcf_loop($id);
	$content .= '</div></div>';
	echo $content;
	}
function qcf_styles($id) {
	qcf_change_form_update();
	if( isset( $_POST['Submit'])) {
		$options = array( 'font','font-family','font-size','font-colour','text-font-family','text-font-size','text-font-colour','input-border','input-required' ,'border','width','widthtype','submitwidth','submitwidthset','submitposition','background','backgroundhex','backgroundimage','corners','use_custom','styles','usetheme','submit-colour','submit-background','submit-border','submit-button','form-border','header','header-size','header-colour');
		foreach ( $options as $item) $style[$item] = stripslashes($_POST[$item]);
		update_option( 'qcf_style'.$id, $style);
		qcf_create_css_file ('update');
		qcf_admin_notice("The form styles have been updated.");
		}
	if( isset( $_POST['Reset'])) {
		delete_option('qcf_style'.$id);
		qcf_create_css_file ('update');
		if ($id) qcf_admin_notice("The style settings for ".$id. " have been reset.");
		else qcf_admin_notice("The default form settings have been updated.");
		}
	$qcf_setup = qcf_get_stored_setup();
	$id=$qcf_setup['current'];
	$style = qcf_get_stored_style($id);
	$$style['font'] = 'checked';
	$$style['widthtype'] = 'checked';
	$$style['submitwidth'] = 'checked';
	$$style['submitposition'] = 'checked';
	$$style['border'] = 'checked';
	$$style['background'] = 'checked';
	$$style['corners'] = 'checked';
	$$style['header'] = 'checked';

	$content ='<div class="qcf-settings"><div class="qcf-options">';
	if ($id) $content .='<h2 style="color:#B52C00">Styles for ' . $id . '</h2>';
	else $content .='<h2 style="color:#B52C00">Default form styles</h2>';
	$content .= qcf_change_form($qcf_setup);
	$content .='<form method="post" action=""> 
		<span class="description"><b>NOTE:</b>Leave fields blank if you don\'t want to use them</span>
		<table>
		<tr><td colspan="2"><h2>Form Width</h2></td></tr>
		<tr><td></td><td><input style="margin:0; padding:0; border:none;" type="radio" name="widthtype" value="percent" ' . $percent . ' /> 100% (fill the available space)<br />
		<input style="margin:0; padding:0; border:none;" type="radio" name="widthtype" value="pixel" ' . $pixel . ' /> Pixel (fixed): <input type="text" style="width:4em" label="width" name="width" value="' . $style['width'] . '" /> px</td></tr>
		<tr><td colspan="2"><h2>Form Border</h2>
		<p>Note: The rounded corners and shadows only work on CSS3 supported browsers and even then not in IE8. Don\'t blame me, blame Microsoft.</p></td></tr>
		<tr><td>Type:</td><td><input style="margin:0; padding:0; border:none;" type="radio" name="border" value="none" ' . $none . ' /> No border<br />
		<input style="margin:0; padding:0; border:none;" type="radio" name="border" value="plain" ' . $plain . ' /> Plain Border<br />
		<input style="margin:0; padding:0; border:none;" type="radio" name="border" value="rounded" ' . $rounded . ' /> Round Corners (Not IE8)<br />
		<input style="margin:0; padding:0; border:none;" type="radio" name="border" value="shadow" ' . $shadow . ' /> Shadowed Border(Not IE8)<br />
		<input style="margin:0; padding:0; border:none;" type="radio" name="border" value="roundshadow" ' . $roundshadow . ' /> Rounded Shadowed Border (Not IE8)</td></tr>
		<tr><td>Style:</td><td><input type="text" label="form-border" name="form-border" value="' . $style['form-border'] . '" /></td></tr>
		<tr><td colspan="2"><h2>Background</h2></td</tr>
		<tr><td>Colour:</td><td><input style="margin:0; padding:0; border:none;" type="radio" name="background" value="white" ' . $white . ' /> White<br />
		<input style="margin:0; padding:0; border:none;" type="radio" name="background" value="theme" ' . $theme . ' /> Use theme colours<br />
		<input style="margin:0; padding:0; border:none;" type="radio" name="background" value="color" ' . $color . ' />	Set your own: 
		<input type="text" class="qcf-color" label="background" name="backgroundhex" value="' . $style['backgroundhex'] . '" /></td></tr>
		<tr><td>Background<br>Image:</td><td><input id="qcf_background" type="text" name="backgroundimage" value="' . $style['backgroundimage'] . '" />
   		<input id="qcf_upload_background" class="button" type="button" value="Upload Image" /></td></tr>
		<tr><td colspan="2"><h2>Font Styles</h2></td</tr>
		<tr><td></td><td><input style="margin:0; padding:0; border:none" type="radio" name="font" value="theme" ' . $theme . ' /> Use theme font styles<br />
		<input style="margin:0; padding:0; border:none" type="radio" name="font" value="plugin" ' . $plugin . ' /> Use Plugin font styles (enter font family and size below)
		</td></tr>
		<tr><td colspan="2"><h2>Form Header</h2></td></tr>
		<tr><td></td><td><input type="checkbox" style="margin:0; padding: 0; border: none" name="header"' . $style['header'] . ' value="checked" />Use header styles</td></tr>
		<tr><td>Header Size: </td><td><input type="text" style="width:6em" label="header-size" name="header-size" value="' . $style['header-size'] . '" /></td></tr>
		<tr><td>Header Colour: </td><td><input type="text" class="qcf-color" label="header-colour" name="header-colour" value="' . $style['header-colour'] . '" /></td></tr>
		<tr><td colspan="2"><h2>Input Fields</h2></td></tr>
		<tr><td>Font Family: </td><td><input type="text" label="font-family" name="font-family" value="' . $style['font-family'] . '" /></td></tr>
		<tr><td>Font Size: </td><td><input type="text" style="width:6em" label="font-size" name="font-size" value="' . $style['font-size'] . '" /></td></tr>
		<tr><td>Font Colour: </td><td><input type="text" class="qcf-color" label="font-colour" name="font-colour" value="' . $style['font-colour'] . '" /></td></tr>
		<tr><td>Normal Border: </td><td><input type="text" label="input-border" name="input-border" value="' . $style['input-border'] . '" /></td></tr>
		<tr><td>Required Fields: </td><td><input type="text" label="input-required" name="input-required" value="' . $style['input-required'] . '" /></td></tr>
		<tr><td>Corners: </td><td><input style="margin:0; padding:0; border:none;" type="radio" name="corners" value="corner" ' . $corner . ' /> Use theme settings <input style="margin:0; padding:0; border:none;" type="radio" name="corners" value="square" ' . $square . ' /> Square corners 	<input style="margin:0; padding:0; border:none;" type="radio" name="corners" value="round" ' . $round . ' /> 5px rounded corners</td></tr>
		<tr><td colspan="2"><h2>Other text content</h2></td></tr>
		<tr><td>Font Family: </td><td><input type="text" label="text-font-family" name="text-font-family" value="' . $style['text-font-family'] . '" /></td></tr>
		<tr><td>Font Size: </td><td><input type="text" style="width:6em" label="text-font-size" name="text-font-size" value="' . $style['text-font-size'] . '" /></td></tr>
		<tr><td>Font Colour: </td><td><input type="text" class="qcf-color" label="text-font-colour" name="text-font-colour" value="' . $style['text-font-colour'] . '" /></td></tr>
		<tr><td td colspan="2"><h2>Submit Button</h2></td></tr>
		<tr><td>Font Colour: </td><td><input type="text" class="qcf-color" label="submit-colour" name="submit-colour" value="' . $style['submit-colour'] . '" /></td></tr>
		<tr><td>Background: </td><td><input type="text" class="qcf-color" label="submit-background" name="submit-background" value="' . $style['submit-background'] . '" /></td></tr>
		<tr><td>Border: </td><td><input type="text" label="submit-border" name="submit-border" value="' . $style['submit-border'] . '" /></td></tr>
		<tr><td>Sized: </td><td><input style="margin:0; padding:0; border:none;" type="radio" name="submitwidth" value="submitpercent" ' . $submitpercent . ' /> Same width as the form<br />
		<input style="margin:0; padding:0; border:none;" type="radio" name="submitwidth" value="submitrandom" ' . $submitrandom . ' /> Same width as the button text<br />
		<input style="margin:0; padding:0; border:none;" type="radio" name="submitwidth" value="submitpixel" ' . $submitpixel . ' /> Set your own width: <input type="text" style="width:5em" label="submitwidthset" name="submitwidthset" value="' . $style['submitwidthset'] . '" /> (px, % or em)</td></tr>
		<tr><td>Position: </td><td><input style="margin:0; padding:0; border:none;" type="radio" name="submitposition" value="submitleft" ' . $submitleft . ' /> Left <input style="margin:0; padding:0; border:none;" type="radio" name="submitposition" value="submitright" ' . $submitright . ' /> Right</td></tr>
		<tr><td>Button Image: </td><td>
		<input id="qcf_submit_button" type="text" name="submit-button" value="' . $style['submit-button'] . '" /><input id="qcf_upload_submit_button" class="button-secondary" type="button" value="Upload Image" /></td></tr>
		</table>
		<h2>Custom CSS</h2>
		<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="use_custom"' . $style['use_custom'] . ' value="checked" /> Use Custom CSS</p>
		<p><textarea style="height: 100px" name="styles">' . $style['styles'] . '</textarea></p>
		<p>To see all the styling use the <a href="'.get_admin_url().'plugin-editor.php?file=quick-contact-form/quick-contact-form-style.css">CSS editor</a>.</p>
		<p>The main style wrapper is the <code>.qcf-style</code> id.</p>
		<p>The form borders are: #none, #plain, #rounded, #shadow, #roundshadow.</p>
		<p>Errors and required fields have the classes .error and .required</p>
		<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the style settings for '.$id.'?\' );"/></p>
		</form>
		</div>
		<div class="qcf-options" style="float:right"> 
		<h2 style="color:#B52C00">Test Form</h2>
		<p>Not all of your style selections will display here (because of how WordPress works). So check the form on your site.</p>';
	$content .= qcf_loop($id);
	$content .= '</div></div>';
	echo $content;
	}
function qcf_reply_page($id) {
	qcf_change_form_update();
	if( isset( $_POST['Submit'])) {
		$options = array( 'replytitle','replyblurb','replymessage','replycopy','replysubject','messages' , 'tracker' , 'url' ,  'page' , 'subject' ,  'subjectoption' , 'qcf_redirect','qcf_reload','qcf_reload_time','qcf_redirect_url','qcfmail','sendcopy','copy_message','bodyhead');
		foreach ( $options as $item) $reply[$item] = stripslashes($_POST[$item]);
		update_option('qcf_reply'.$id, $reply);
		if ($id) qcf_admin_notice("The send settings for " . $id . " have been updated.");
		else qcf_admin_notice("The default form send settings have been updated.");
		}
	if( isset( $_POST['Reset'])) {
		delete_option('qcf_reply'.$id);
		qcf_admin_notice("The reply settings for the form called ".$id. " have been reset.");
		}
	$qcf_setup = qcf_get_stored_setup();
	$id=$qcf_setup['current'];
	$reply = qcf_get_stored_reply($id);
	$$reply['subjectoption'] = "checked";
	$$reply['qcfmail'] = "checked";
	
	$content ='<div class="qcf-settings"><div class="qcf-options">';
	if ($id) $content .='<h2 style="color:#B52C00">Send options for ' . $id . '</h2>';
	else $content .='<h2 style="color:#B52C00">Default form send options</h2>';
	$content .= qcf_change_form($qcf_setup);
	$content .='<form method="post" action="">
	<span class="description"><b>NOTE:</b>Leave fields blank if you don\'t want to use them</span>
		<table>
		<tr><td colspan="2"><h2>Send Options</h2></td></tr>
		<tr><td>Send Function</td><td>
		<input style="margin:0; padding:0; border:none" type="radio" name="qcfmail" value="wpemail" ' . $wpemail . '> WP-mail (should work for most email addresses)<br />
		<input style="margin:0; padding:0; border:none" type="radio" name="qcfmail" value="phpmail" ' . $phpmail . '> PHP mail (the default mail function)<br />
		<input style="margin:0; padding:0; border:none" type="radio" name="qcfmail" value="smtp" ' . $smtp . '> SMTP (Only use if you have <a href="?page=quick-contact-form/settings.php&tab=smtp">set up SMTP</a>)</td></tr>
		<tr><td>Email subject</td><td>The message subject has two parts: the bit in the text box plus the option below.<br>
		<input style="width:100%" type="text" name="subject" value="' . $reply['subject'] . '"/><br>
		<input style="margin:0; padding:0; border:none" type="radio" name="subjectoption" value="sendername" ' . $sendername . '> sender\'s name (the contents of the first field)<br />
		<input style="margin:0; padding:0; border:none" type="radio" name="subjectoption" value="sendersubj" ' . $sendersubj . '> Contents of the subject field (if used)<br />
		<input style="margin:0; padding:0; border:none" type="radio" name="subjectoption" value="senderpage" ' . $senderpage . '> page title (only works if sent from a post or a page)<br />
		<input style="margin:0; padding:0; border:none" type="radio" name="subjectoption" value="sendernone" ' . $sendernone . '> blank
		</td></tr>
		<tr><td>Email body header</td><td>
		This is the introduction to the email message you receive.<br>
		<input type="text" name="bodyhead" value="' . $reply['bodyhead'] . '"/></td></tr>
		<tr><td>Tracking</td><td>
		Adds the tracking information to the message you receive.<br>
		<input style="margin:0; padding:0; border: none"type="checkbox" name="page" ' . $reply['page'] . ' value="checked"> Show page title<br />
		<input style="margin:0; padding:0; border:none" type="checkbox" name="tracker" ' . $reply['tracker'] . ' value="checked"> Show IP address<br />
		<input style="margin:0; padding:0; border:none" type="checkbox" name="url" ' . $reply['url'] . ' value="checked"> Show URL
		</td></tr>
		<tr><td colspan="2"><h2>Redirection</h2></td></tr>
		<tr><td></td><td><input style="margin:0; padding:0; border:none" type="checkbox" name="qcf_redirect" ' . $reply['qcf_redirect'] . ' value="checked"> Send your visitor to new page instead of displaying the thank-you message.</td></tr>
		<tr><td>URL:</td><td><input type="text" name="qcf_redirect_url" value="' . $reply['qcf_redirect_url'] . '"/></td></tr>
		<tr><td colspan="2"><h2>On Screen Thank you message</h2></td></tr>
		<tr><td>Thank you header</td><td>
		<input type="text" name="replytitle" value="' . $reply['replytitle'] . '"/></td></tr>
		<tr><td>Thank you message</td><td>
		<textarea height: 100px" name="replyblurb">' . $reply['replyblurb'] . '</textarea></td></tr>
		<tr><td></td><td><input style="margin:0; padding:0; border:none" type="checkbox" name="messages" ' . $reply['messages'] . ' value="checked"> Show the sender the content of their message.</td></tr>
		<tr><td colspan="2"><h2>Email Message</h2></td><td>
		<tr><td></td><td><input style="margin:0; padding:0; border:none" type="checkbox" name="sendcopy" ' . $reply['sendcopy'] . ' value="checked"> Send an email message to the sender.</td></tr>
		<tr><td>Email Subject:</td><td>
		<input type="text" name="replysubject" value="' . $reply['replysubject'] . '"/></td></tr>		
		<tr><td>Email Message</td><td>
		<textarea height: 100px" name="replymessage">' . $reply['replymessage'] . '</textarea>
		<input style="margin:0; padding:0; border:none" type="checkbox" name="replycopy" ' . $reply['replycopy'] . ' value="checked"> Add the content of the email they sent to you.</td></tr>
		<tr><td colspan="2"><h2>Reload Page</h2></td></tr>
		<tr><td></td><td><input style="margin:0; padding:0; border:none" type="checkbox" name="qcf_reload" ' . $reply['qcf_reload'] . ' value="checked"> Refresh the page <input style="width:2em" type="text" name="qcf_reload_time" value="' . $reply['qcf_reload_time'] . '" /> seconds after the thank-you message.</td></tr>
</table>
		<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the reply settings for '.$id.'?\' );"/></p>
		</form>
		</div>
		<div class="qcf-options" style="float:right"> 
		<h2 style="color:#B52C00">Test Form</h2>
		<p>Use the form below to test your thank-you message settings. You will see what your visitors see when they complete and send the form.</p>';
	$content .= qcf_loop($id);
	$content .= '</div></div>';
	echo $content;
	}
function qcf_error_page($id) {
	qcf_change_form_update();
	if( isset( $_POST['Submit'])) {
		for ($i=1; $i<=10; $i++) $error['field'.$i] = stripslashes($_POST['error'.$i]);
		$options = array( 'errortitle','errorblurb','email','telephone','mathsmissing','mathsanswer','emailcheck','phonecheck');
		foreach ( $options as $item) $error[$item] = stripslashes($_POST[$item]);
		update_option( 'qcf_error'.$id, $error );
		if ($id) qcf_admin_notice("The reply settings for " . $id . " have been updated.");
		else qcf_admin_notice("The default form error settings have been updated.");

		}
	if( isset( $_POST['Reset'])) {
		delete_option('qcf_error'.$id);
		qcf_admin_notice("The error settings for the form called ".$id. " have been reset.");
		}
	$qcf_setup = qcf_get_stored_setup();
	$id=$qcf_setup['current'];
	$qcf = qcf_get_stored_options($id);
	$error = qcf_get_stored_error($id);
	$content ='<div class="qcf-settings"><div class="qcf-options">';
	if ($id) $content .='<h2 style="color:#B52C00">Error messages for ' . $id . '</h2>';
	else $content .='<h2 style="color:#B52C00">Default form error messages</h2>';
	$content .= qcf_change_form($qcf_setup);
	$content .='<form method="post" action="">
	<span class="description"><b>NOTE:</b> Leave fields blank if you don\'t want to use them</span>
	<table>
	<tr><td colspan="2"><h2>Error Reporting</h2></td></tr>
		<tr><td>Error header</td><td><input type="text" name="errortitle" value="' . $error['errortitle'] . '" /></td><td>
		<tr><td>Error Blurb</td><td><input type="text" name="errorblurb" value="' . $error['errorblurb'] . '" /></td></tr>
		<tr><td colspan="2"><h2>Error Messages</h2></td></tr>
		<tr><td>If <em>' . $qcf['label']['field1'] . '</em> is missing:</td><td>
		<p><input type="text" name="error1" value="' .  $error['field1'] . '" /></td></tr>
		<tr><td>If <em>' . $qcf['label']['field2'] . '</em> is missing:</td><td>
		<p><input type="text" name="error2" value="' .  $error['field2'] . '" /></td></tr>
		<tr><td>Invalid email address:</td><td>
		<p><input type="text" name="email" value="' .  $error['email'] . '" /></td></tr>
		<tr><td></td><td><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="emailcheck"' . $error['emailcheck'] . ' value="checked" /> Check for invalid email even if field is not required</td></tr>
		<tr><td>If <em>' . $qcf['label']['field3'] . '</em> is missing:</td><td>
		<p><input type="text" name="error3" value="' .  $error['field3'] . '" /></td></tr>
		<tr><td>Invalid telephone number:</td><td>
		<p><input type="text" name="telephone" value="' .  $error['telephone'] . '" /></td></tr>
		<tr><td></td><td><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="phonecheck"' . $error['phonecheck'] . ' value="checked" /> Check for invalid phone number even if field is not required</td></tr>
		<tr><td>If <em>' . $qcf['label']['field4'] . '</em> is missing:</td><td>
		<p><input type="text" name="error4" value="' .  $error['field4'] . '" /></td></tr>
		<tr><td>Drop dopwn list:</td><td>
		<p><input type="text" name="error5" value="' .  $error['field5'] . '" /></td></tr>
		<tr><td>Checkboxes:</td><td>
		<p><input type="text" name="error6" value="' .  $error['field6'] . '" /></td></tr>
		<tr><td>If <em>' .  $qcf['label']['field8'] . '</em> is missing:</td><td>
		<p><input type="text" name="error8" value="' .  $error['field8'] . '" /></td></tr>
		<tr><td>If <em>' .  $qcf['label']['field9'] . '</em> is missing:</td><td>
		<p><input type="text" name="error9" value="' .  $error['field9'] . '" /></td></tr>
		<tr><td>If <em>' .  $qcf['label']['field10'] . '</em> is required:</td><td>
		<p><input type="text" name="error10" value="' .  $error['field10'] . '" /></td></tr>
		<tr><td>Maths Captcha missing answer:</td><td>
		<p><input type="text" name="mathsmissing" value="' .  $error['mathsmissing'] . '" /></td></tr>
		<tr><td>Maths Captcha wrong answer:</td><td><input type="text" name="mathsanswer" value="' .  $error['mathsanswer'] . '" /></td></tr>
</table>
		<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the error settings for '.$id.'?\' );"/></p>
		</form>
		</div>
		<div class="qcf-options" style="float:right"> 
		<h2 style="color:#B52C00">Error Checker</h2>
		<p>Send a blank form to test your error messages.</p>';
	$content .= qcf_loop($id);
	$content .= '</div></div>';
	echo $content;
	}
function qcf_help($id) {
	$content ='<div class="qcf-settings"><div class="qcf-options"> 
		<h2 style="color:#B52C00">Getting Started</h2>
		<p>A default form is already installed and ready to use. To add to a page or a post just add the shortcode <code>[qcf]</code>. If you want to add the form to a sidebar use the Quick Contact Form widget.</p>
		<p>You can now use the tabbed options on this page to change any of settings. If you haven\'t already, check and save your email address for the default form.</p>
		<h2>Form settings and options</h2>
		<p>You can create as many different forms as you like each with their own settings. Just name the form and add an email address on the setup page. To use a named form change the shortcode to <code>[qcf id="name-of-form"]</code>. If you are using a sidebar widget, select the form from the dropdown options.</p>
		<p>The <a href= "?page=quick-contact-form/settings.php&tab=settings">Form Settings</a> page allows you to select and order which fields are displayed, change the labels and have them validated. You can also add an optional spambot cruncher. When you save the changes the updated form will preview on the right.</p>
		<p>To change the width of the form, fonts, colours, border and other styles use the <a href= "?page=quick-contact-form/settings.php&tab=styles">Styling</a> page. You also have the option to add some custom CSS.</p>
		<p>You can create your own <a href= "?page=quick-contact-form/settings.php&tab=error">Error Messages</a> and configure the <a href= "?page=quick-contact-form/settings.php&tab=reply">Send Options</a> as well.</p>
		<p>If you want to allow attachments then use the <a href= "?page=quick-contact-form/settings.php&tab=attach">Attachments</a> page. Make sure to restrict the file types people can send. You will also have to adjust the field width. This is because the input field ignores just about all styling. <a href="http://www.quirksmode.org/dom/inputfile.html" target="_blank">Quirksmode</a> has some suggestions on how to manage this but it\'s not easy. Even then, every browser is different so the attachment field won\'t look the same every time.</p>
		<p>If it all goes a bit pear shaped you can <a href= "?page=quick-contact-form/settings.php&tab=reset">reset everything</a> to the defaults.</p>
		<p>There is some development info on <a href="http://quick-plugins.com/quick-contact-form/" target="_blank">my plugin page</a> along with a feedback form. Or you can email me at <a href="mailto:mail@quick-plugins.com">mail@quick-plugins.com</a>.</p>
		</div>
		<div class="qcf-options" style="float:right"> 
		<h2>Validation</h2>
		<p>On the <a href= "?page=quick-contact-form/settings.php&tab=settings">Form Settings</a> page check the validation box if you want a field checked.</p>
		<p>Validation removes all the unwanted characters (URLs, HTML, javascript and so on) leaving just the alphabet, numbers and a few punctuation marks).</p>
		<p>It then checks that the field isn\'t empty or that the user has actually typed something in the box. The error message suggests that they need to enter &lt;something&gt; where something is the info you need (name, email, phone number, colour etc).</p>
		<p>It also checks for a valid email address and phone number. This only takes place in the telephone and email fields. If you want the email address and telephone number format validated even if they aren\'t reuquired fields, then check the boxes on the <a href= "?page=quick-contact-form/settings.php&tab=error">error messages</a> page.</p>
		<h2>Messages</h2>
		<p>Older versions of the plugin displayed sent messages on the dashboard. The problem was if you had multiple forms you couldn\'t tell which message came from what form.</p>
		<p>All messages are now displayed in full with a download option using the <b>Messages</b> link in the dashboard menu.</p>
		</div></div>';
	echo $content;
	}
function qcf_reset_page($id) {
	qcf_change_form_update();
	if (isset($_POST['qcf_reset'])) {
		if (isset($_POST['qcf_delete_form'])) {
			$qcf_setup = qcf_get_stored_setup();
			if ($id != '') {
				$forms = $qcf_setup['alternative'];
				$qcf_setup['alternative'] = str_replace($id.',','',$forms); 
				$qcf_setup['current'] = '';
				update_option('qcf_setup', $qcf_setup);
				qcf_delete_things($id);
				qcf_admin_notice("<b>The form named ".$id." has been deleted.</b>");
				$id = '';
				}
			}
		if (isset($_POST['qcf_reset_form'])) {
			delete_things($id);
			if ($id) qcf_admin_notice("<b>The form called ".$id. " has been reset.</b> Use the <a href= '?page=quick-contact-form/settings.php&tab=setup'>Setup</a> tab to add a new named form");
			else qcf_admin_notice("<b>The default form has been reset.</b>");
			}
		if (isset($_POST['qcf_reset_options'])) {
			delete_option('qcf_settings'.$id);
			if ($id) qcf_admin_notice("<b>Form settings for ".$id." have been reset.</b> Use the <a href= '?page=quick-contact-form/settings.php&tab=settings'>Form Settings</a> tab to change the settings");
			else qcf_admin_notice("<b>The default form settings have been reset.</b> Use the <a href= '?page=quick-contact-form/settings.php&tab=settings'>Form Settings</a> tab to change the settings");
			}
		if (isset($_POST['qcf_reset_attach'])) {
			delete_option('qcf_attach'.$id);
			if ($id) qcf_admin_notice("<b>The attachment options for ".$id." have been reset.</b>. Use the <a href= '?page=quick-contact-form/settings.php&tab=attach'>Attachments</a> tab to change the settings");
			else qcf_admin_notice("<b>The default attachment options have been reset.</b>. Use the <a href= '?page=quick-contact-form/settings.php&tab=attach'>Attachments</a> tab to change the settings");
			}
		if (isset($_POST['qcf_reset_reply'])) {
			delete_option('qcf_reply'.$id);
			if ($id) qcf_admin_notice("<b>The send options for ".$id." have been reset.</b>. Use the <a href= '?page=quick-contact-form/settings.php&tab=reply'>Reply Options</a> tab to change the settings");
			else qcf_admin_notice("<b>The default send options have been reset.</b>. Use the <a href= '?page=quick-contact-form/settings.php&tab=reply'>Reply Options</a> tab to change the settings");
			}
		if (isset($_POST['qcf_reset_styles'])) {
			delete_option('qcf_style'.$id);
			if ($id) qcf_admin_notice("<b>The styles for ".$id." have been reset.</b>. Use the <a href= '?page=quick-contact-form/settings.php&tab=styles'>Styling</a> tab to change the settings");
			else qcf_admin_notice("<b>The default styles have been reset.</b>. Use the <a href= '?page=quick-contact-form/settings.php&tab=styles'>Styling</a> tab to change the settings");
			}
		if (isset($_POST['qcf_reset_errors'])) {
			delete_option('qcf_error'.$id);
			if ($id) qcf_admin_notice("<b>The error messages for ".$id." have been reset.</b> Use the <a href= '?page=quick-contact-form/settings.php&tab=error'>Error Messages</a> tab to change the settings.");
			else qcf_admin_notice("<b>The default error messages have been reset.</b> Use the <a href= '?page=quick-contact-form/settings.php&tab=error'>Error Messages</a> tab to change the settings.");
			}
		if (isset($_POST['qcf_reset_everything'])) {
			$qcf_setup = qcf_get_stored_setup();
			$id = explode(",",$qcf_setup['alternative']);
			foreach ($id as $item) delete_things($id);
			delete_option('qcf_email');
			delete_option('qcf_setup');
			delete_option('qcf_message');
			qcf_admin_notice("<b>Everything has been reset.</b> This is an ex-parrot. It has gone to meet it's maker.");
			$id = '';
			}
		}
	$qcf_setup = qcf_get_stored_setup();
	$id=$qcf_setup['current'];
	$content ='<div class="qcf-settings"><div class="qcf-options">';
	if ($id) $content .='<h2 style="color:#B52C00">Reset the options for ' . $id . '</h2>';
	else $content .='<h2 style="color:#B52C00">Reset the default form</h2>';
	$content .= qcf_change_form($qcf_setup);
	$content .= '<p>Select the options you wish to reset and click on the blue button. This will reset the selected settings to the defaults.</p>
		<form action="" method="POST">
		<p>
		<input style="margin:0; padding:0; border:none;" type="checkbox" name="qcf_reset_options"> Form settings<br />
		<input style="margin:0; padding:0; border:none;" type="checkbox" name="qcf_reset_attach"> Attachments<br />
		<input style="margin:0; padding:0; border:none;" type="checkbox" name="qcf_reset_styles"> Styling (also delete any custom CSS)<br />
		<input style="margin:0; padding:0; border:none;" type="checkbox" name="qcf_reset_reply"> Send and thank-you options<br />
		<input style="margin:0; padding:0; border:none;" type="checkbox" name="qcf_reset_errors"> Error messages</p>
		<hr>
		<p>
		<input style="margin:0; padding:0; border:none;" type="checkbox" name="qcf_reset_form"> Reset this form to default settings';
		if ($id) $content .= '<br /><input style="margin:0; padding:0; border:none;" type="checkbox" name="qcf_delete_form"> Delete '.$id.'</p>';
		$content .= '<hr>
		<p>
		<input type="submit" class="button-primary" name="qcf_reset" style="color: #FFF" value="Reset Options" onclick="return window.confirm( \'Are you sure you want to reset these settings?\' );"/>
		</form>
	</div></div>';
	echo $content;
	}
function qcf_smtp_page() {
	if( isset( $_POST['Submit'])) {
		$options = array('mailer','smtp_host','smtp_port','smtp_ssl','smtp_auth','smtp_user','smtp_pass');
		foreach ( $options as $item) $qcfsmtp[$item] = stripslashes($_POST[$item]);
		update_option( 'qcf_smtp', $qcfsmtp );
		qcf_admin_notice("The SMTP settings have been updated.");
		}
	if( isset( $_POST['Reset'])) {
		delete_option('qcf_smtp');
		qcf_admin_notice("The SMTP settings have been reset.");
		}
		$qcfsmtp = qcf_get_stored_smtp ();
		$$qcfsmtp['mailer'] = 'checked';
		$$qcfsmtp['smtp_ssl'] = 'checked';
		$$qcfsmtp['smtp_auth'] = 'checked';
		$content = '<div class="qcf-settings"><div class="qcf-options">';
		$content .= wp_nonce_field('email-options');
		$content .= '<h2>SMTP Settings</h2>
			<p>These settings only apply if you have chosen to <a href="?page=quick-contact-form/settings.php&tab=reply">send mail by SMTP</a></p>
			<form method="post" action=""><table style="width:100%>
			<tr valign="top">
			<td>SMTP Host</td>
			<td><input name="smtp_host" type="text" id="smtp_host" value="'.$qcfsmtp['smtp_host'].'" /></td>
			</tr><tr valign="top">
			<td>SMTP Port</td><td><input name="smtp_port" type="text" id="smtp_port" value="'.$qcfsmtp['smtp_port'].'" style="width:6em;" /></td>
			</tr><tr valign="top">
			<td>Encryption </td>
			<td><input style="margin:0; padding:0; border:none" type="radio" name="smtp_ssl" value="none" '.$none.' /> No encryption.<br />
			<input style="margin:0; padding:0; border:none" type="radio" name="smtp_ssl" value="ssl" '.$ssl.' /> Use SSL encryption.<br />
			<input style="margin:0; padding:0; border:none" type="radio" name="smtp_ssl" value="tls" '.$tls.' /> Use TLS encryption.<br />
			<span class="description">This is not the same as STARTTLS. For most servers SSL is the recommended option.</span></td>
			</tr><tr valign="top">
			<td>Authentication</td>
			<td>
			<input style="margin:0; padding:0; border:none" type="radio" name="smtp_auth" value="authfalse" '.$authfalse.' /> No: Do not use SMTP authentication.<br />
			<input style="margin:0; padding:0; border:none" type="radio" name="smtp_auth" value="authtrue" '.$authtrue.' /> Yes: Use SMTP authentication.<br />
			<span class="description">If this is set to no, the values below are ignored.</span>
			</td>
			</tr><tr valign="top">
			<td>Username</td><td><input name="smtp_user" type="text" value=" '.$qcfsmtp['smtp_user'].'" /></td>
			</tr><tr valign="top">
			<td>Password</td><td><input name="smtp_pass" type="text" value=" '.$qcfsmtp['smtp_pass'].'" /></td>
			</tr>
			<tr>
			<td colspan="2"><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" />  <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset these settings?\' );"/></p>
		<input type="hidden" name="action" value="update" /><input type="hidden" name="option_page" value="email">
			</td>
			</tr>
			</table>
			</form>
			</div>
			<div class="qcf-options" style="float:right"> 
		<h2 style="color:#B52C00">SMTP Test</h2>
		<p><span style="color:red;font-weight:bold;">Important!</span>&nbsp; Make sure you test your SMTP settings before. If you don\'t your visitors may get a whole bunch of error messages.</p>';
	$content .= qcf_loop('default');
	$content .= '</div></div>';
	echo $content;
	}
function delete_everything() {
	$qcf_setup = qcf_get_stored_setup();
	$arr = explode(",",$qcf_setup['alternative']);
	foreach ($arr as $item) delete_things($item);
	delete_option('qcf_setup');
	delete_option('qcf_email');
	delete_option('qcf_message');
	}
function qcf_delete_things($id) {
	delete_option('qcf_settings'.$id);
	delete_option('qcf_reply'.$id);
	delete_option('qcf_error'.$id);
	delete_option('qcf_style'.$id);
	delete_option('qcf_attach'.$id);
	}
function qcf_admin_notice($message) {
	if (!empty( $message)) echo '<div class="updated"><p>'.$message.'</p></div>';
	}
function qcf_change_form($qcf_setup) {
	if ($qcf_setup['alternative']) {
		$content .= '<form style="margin-top: 8px" method="post" action="" >';
		$arr = explode(",",$qcf_setup['alternative']);
		foreach ($arr as $item) {
			if ($qcf_setup['current'] == $item) $checked = 'checked'; else $checked = '';
			if ($item == '') {$formname = 'default'; $item='';} else $formname = $item;
			$content .='<input style="margin:0; padding:0; border:none" type="radio" name="current" value="' .$item . '" ' .$checked . ' /> '.$formname . ' ';
			}
		$content .='<input type="hidden" name="alternative" value = "' . $qcf_setup['alternative'] . '" />
		<input type="hidden" name="dashboard" value = "' . $qcf_setup['dashboard'] . '" />&nbsp;&nbsp;<input type="submit" name="Select" class="button-secondary" value="Change Form" /></form>';
		}
	return $content;
	}
function qcf_change_form_update() {
	if( isset( $_POST['Select'])) {
		$qcf_setup['current'] = $_POST['current'];
		$qcf_setup['alternative'] = $_POST['alternative'];
		$qcf_setup['dashboard'] = $_POST['dashboard'];
		update_option( 'qcf_setup', $qcf_setup);
		}
	}
function qcf_generate_csv() {
	if(isset($_POST['download_csv'])) {
	$id = $_POST['formname'];
		$filename = urlencode($id.'.csv');
		if ($id == '') $filename = urlencode('default.csv');
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename="'.$filename.'"');
		header( 'Content-Type: text/csv');$outstream = fopen("php://output",'w');
		$message = get_option( 'qcf_messages'.$id );
		if(!is_array($message))$message = array();
		$qcf = qcf_get_stored_options ($id);
		$headerrow = array();
		foreach (explode( ',',$qcf['sort']) as $name) {if ($qcf['active_buttons'][$name] == "on") array_push($headerrow, $qcf['label'][$name]);}
		array_push($headerrow,'Date Sent');
		fputcsv($outstream,$headerrow, ',', '"');
		foreach(array_reverse( $message ) as $value) {
			$cells = array();
			foreach (explode( ',',$qcf['sort']) as $name) {if ($qcf['active_buttons'][$name] == "on") array_push($cells,$value[$name]);}
			array_push($cells,$value['field0']);
			fputcsv($outstream,$cells, ',', '"');
			}
		fclose($outstream); 
		exit;
		}
	}
function qcfdonate_verify($formvalues) {
	$errors = '';
	if ($formvalues['amount'] == 'Amount' || empty($formvalues['amount'])) $errors = 'first';
	if ($formvalues['yourname'] == 'Your name' || empty($formvalues['yourname'])) $errors = 'second';
	return $errors;
	}
function qcfdonate_display( $values, $errors ) {
	$content = "<script>\r\t
	function donateclear(thisfield, defaulttext) {if (thisfield.value == defaulttext) {thisfield.value = '';}}\r\t
	function donaterecall(thisfield, defaulttext) {if (thisfield.value == '') {thisfield.value = defaulttext;}}\r\t
	</script>\r\t
	<div class='qcf-style'>\r\t<div id='round'>\r\t";
	if ($errors) $content .= "<h2 class='error'>Feed me...</h2>\r\t<p class='error'>...your donation details</p>\r\t";
	else $content .= "<h2>Make a donation</h2>\r\t<p>Whilst I enjoy creating these plugins they don't pay the bills. So a paypal donation will always be gratefully received</p>\r\t";
	$content .= '
	<form method="post" action="" >
	<p><input type="text" label="Your name" name="yourname" value="Your name" onfocus="donateclear(this, \'Your name\')" onblur="donaterecall(this, \'Your name\')"/></p>
	<p><input type="text" label="Amount" name="amount" value="Amount" onfocus="donateclear(this, \'Amount\')" onblur="donaterecall(this, \'Amount\')"/></p>
	<p><input type="submit" value="Donate" id="submit" name="donate" /></p>
	</form></div>';
	echo $content;
	}
function qcfdonate_process($values) {
	$page_url = qcfdonate_page_url();
	$content = '<h2>Waiting for paypal...</h2><form action="https://www.paypal.com/cgi-bin/webscr" method="post" name="frmCart" id="frmCart">
	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="business" value="graham@aerin.co.uk">
	<input type="hidden" name="return" value="' .  $page_url . '">
	<input type="hidden" name="cancel_return" value="' .  $page_url . '">
	<input type="hidden" name="no_shipping" value="1">
	<input type="hidden" name="currency_code" value="">
	<input type="hidden" name="item_number" value="">
	<input type="hidden" name="item_name" value="'.$values['yourname'].'">
	<input type="hidden" name="amount" value="'.preg_replace ( '/[^.,0-9]/', '', $values['amount']).'">
	</form>
	<script language="JavaScript">
	document.getElementById("frmCart").submit();
	</script>';
	echo $content;
	}
function qcfdonate_page_url() {
	$pageURL = 'http';
	if( isset($_SERVER["HTTPS"]) ) { if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";} }
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	else $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	return $pageURL;
	}
function qcfdonate_loop() {
	ob_start();
	if (isset($_POST['donate'])) {
		$formvalues['yourname'] = $_POST['yourname'];
		$formvalues['amount'] = $_POST['amount'];
		if (qcfdonate_verify($formvalues)) qcfdonate_display($formvalues,'donateerror');
   		else qcfdonate_process($formvalues,$form);
		}
	else qcfdonate_display($formvalues,'');
	$output_string=ob_get_contents();
	ob_end_clean();
	return $output_string;
	}