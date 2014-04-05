<?php
/*
Plugin Name: Quick Contact Form
Plugin URI: http://quick-plugins.com/quick-contact-form/
Description: A really, really simple contact form. There is nothing to configure, just add your email address and it's ready to go.
Version: 6.8.1
Author: fisicx
Author URI: http://quick-plugins.com/
*/

add_shortcode('qcf', 'qcf_start');
add_filter('plugin_action_links', 'qcf_plugin_action_links', 10, 2 );
add_action('wp_enqueue_scripts', 'qcf_admin_scripts');
add_action('init', 'qcf_init');

function qcf_admin_scripts() {
	wp_enqueue_style( 'qcf_style',plugins_url('quick-contact-form.css', __FILE__));
	wp_enqueue_style( 'qcf_custom',plugins_url('quick-contact-form-custom.css', __FILE__));
	wp_enqueue_script( 'qcf_script',plugins_url('quick-contact-form.js', __FILE__));
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
	}
function qcf_init() {
	qcf_create_css_file ('');
	}
function qcf_create_css_file ($update) {
	if (function_exists(file_put_contents)) {
		$css_dir = plugin_dir_path( __FILE__ ) . '/quick-contact-form-custom.css' ;
		$filename = plugin_dir_path( __FILE__ );
		if (is_writable($filename) && (!file_exists($css_dir) || !empty($update))) {
			$data = qcf_generate_css();
			file_put_contents($css_dir, $data, LOCK_EX);
			}
		}
	else add_action('wp_head', 'qcf_head_css');
	}

if (is_admin()) require_once( plugin_dir_path( __FILE__ ) . '/settings.php' );

function qcf_start($atts) {
	extract(shortcode_atts(array( 'id' => '' ), $atts));
	return qcf_loop($id);
	}
function qcf_plugin_action_links($links, $file ) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$qcf_links = '<a href="'.get_admin_url().'options-general.php?page=quick-contact-form/settings.php">'.__('Settings').'</a>';
		array_unshift( $links, $qcf_links );
		}
	return $links;
	}
function qcf_display_form( $values, $errors, $id ) {
	$qcf_form = qcf_get_stored_setup();
	$qcf = qcf_get_stored_options($id);
	$error = qcf_get_stored_error($id);
	$reply = qcf_get_stored_reply($id);
	$attach = qcf_get_stored_attach($id);
	$style = qcf_get_stored_style($id);
	if ($id) $formstyle=$id; else $formstyle='default';
	if (!empty($qcf['title'])) $qcf['title'] = '<h2>' . $qcf['title'] . '</h2>';
	if (!empty($qcf['blurb'])) $qcf['blurb'] = '<p>' . $qcf['blurb'] . '</p>';
	if (!empty($qcf['mathscaption'])) $qcf['mathscaption'] = '<p class="input">' . $qcf['mathscaption'] . '</p>';
	$content = "<div class='qcf-style ".$formstyle."'>\r\t";
	$content .= "<div id='" . $style['border'] . "'>\r\t";
	if (count($errors) > 0)
		$content .= "<h2>" . $error['errortitle'] . "</h2>\r\t<p>" . $error['errorblurb'] . "</p>\r\t";
	else
		$content .= $qcf['title'] . "\r\t" . $qcf['blurb'] . "\r\t";	
	$content .= "<form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\r\t";
		foreach (explode( ',',$qcf['sort']) as $name) {
		$required = ( $qcf['required'][$name]) ? 'required' : '';
		if ($qcf['active_buttons'][$name] == "on") {
			switch ( $name ) {
				case 'field1':
					$content .= $errors['qcfname1'];
					$content .= '<input type="text" class="' . $required . '" label="Name" name="qcfname1" value="' . $values['qcfname1'] . '" onfocus="qcfclear(this, \'' . $values['qcfname1'] . '\')" onblur="qcfrecall(this, \'' . $values['qcfname1'] . '\')">'."\r\t";
					break;
				case 'field2':
					$content .= $errors['qcfname2'];
					$content .= '<input type="text" class="' . $required . '" label="Email" name="qcfname2"  value="' . $values['qcfname2'] . '" onfocus="qcfclear(this, \'' . $values['qcfname2'] . '\')" onblur="qcfrecall(this, \'' . $values['qcfname2'] . '\')">'."\r\t";
					break;
				case 'field3':
					$content .= $errors['qcfname3'];
					$content .= '<input type="text" class="' . $required . '" label="Telephone" name="qcfname3"  value="' . $values['qcfname3'] . '" onfocus="qcfclear(this, \'' . $values['qcfname3'] . '\')" onblur="qcfrecall(this, \'' . $values['qcfname3'] . '\')">'."\r\t";
					break;
				case 'field4':
					$content .= $errors['qcfname4'];
					$content .= '<textarea class="' . $required . '"  rows="' . $qcf['lines'] . '" label="Name" name="qcfname4" onfocus="qcfclear(this, \'' . $values['qcfname4'] . '\')" onblur="qcfrecall(this, \'' . $values['qcfname4'] . '\')">' . stripslashes($values['qcfname4']) . '</textarea></p>'."\r\t";
					break;
				case 'field5':
					if ($qcf['selectora'] == 'dropdowna') $content .= qcf_dropdown('qcfname5','dropdownlist',$values,$errors,$required,$qcf,$name);
                    if ($qcf['selectora'] == 'checkboxa') $content .= qcf_checklist('qcfname5','dropdownlist',$values,$errors,$required,$qcf,$name);
					if ($qcf['selectora'] == 'radioa') $content .= qcf_radio('qcfname5','dropdownlist',$values,$errors,$required,$qcf,$name);
                    break;
				case 'field6':
					if ($qcf['selectorb'] == 'dropdownb') $content .= qcf_dropdown('qcfname6','checklist',$values,$errors,$required,$qcf,$name);
                    if ($qcf['selectorb'] == 'checkboxb') $content .= qcf_checklist('qcfname6','checklist',$values,$errors,$required,$qcf,$name);
					if ($qcf['selectorb'] == 'radiob') $content .= qcf_radio('qcfname6','checklist',$values,$errors,$required,$qcf,$name);
                    break;
				case 'field7':
					if ($qcf['selectorc'] == 'dropdownc') $content .= qcf_dropdown('qcfname7','radiolist',$values,$errors,$required,$qcf,$name);
                    if ($qcf['selectorc'] == 'checkboxc') $content .= qcf_checklist('qcfname7','radiolist',$values,$errors,$required,$qcf,$name);
					if ($qcf['selectorc'] == 'radioc') $content .= qcf_radio('qcfname7','radiolist',$values,$errors,$required,$qcf,$name);
                    break;
				case 'field8':
					$content .= $errors['qcfname8'];
					$content .= '<input type="text" class="' . $required . '" label="Text 1" name="qcfname8"  value="' . $values['qcfname8'] . '" onfocus="qcfclear(this, \'' . $values['qcfname8'] . '\')" onblur="qcfrecall(this, \'' . $values['qcfname8'] . '\')">'."\r\t";
					break;
				case 'field9':
					$content .= $errors['qcfname9'];
					$content .= '<input type="text" class="' . $required . '" label="Text 2" name="qcfname9"  value="' . $values['qcfname9'] . '" onfocus="qcfclear(this, \'' . $values['qcfname9'] . '\')" onblur="qcfrecall(this, \'' . $values['qcfname9'] . '\')">'."\r\t";
					break;
				case 'field10':
					$content .= $errors['qcfname10'];
					$content .= '<input type="text" class="qcfdate ' . $required . '" label="Date" name="qcfname10"  value="' . $values['qcfname10'] . '" onfocus="qcfclear(this, \'' . $values['qcfname10'] . '\')" onblur="qcfrecall(this, \'' . $values['qcfname10'] . '\')">
					<script type="text/javascript">jQuery(document).ready(function() {jQuery(\'\.qcfdate\').datepicker({dateFormat : \'dd M yy\'});});</script>'."\r\t";
					break;
				case 'field11':
					$content .= $errors['qcfname11'];
					if ($qcf['fieldtype'] == 'tdate') $content .= '<input type="text" class="qcfdate ' . $required . '" label="Date 2" name="qcfname11"  value="' . $values['qcfname11'] . '" onfocus="qcfclear(this, \'' . $values['qcfname11'] . '\')" onblur="qcfrecall(this, \'' . $values['qcfname11'] . '\')">
					<script type="text/javascript">jQuery(document).ready(function() {jQuery(\'\.qcfdate\').datepicker({dateFormat : \'dd M yy\'});});</script>'."\r\t";
					else $content .= '<input type="text" class="' . $required . '" label="Multibox 1" name="qcfname11" value="' . $values['qcfname11'] . '" onfocus="qcfclear(this, \'' . $values['qcfname11'] . '\')" onblur="qcfrecall(this, \'' . $values['qcfname11'] . '\')">'."\r\t";
					break;
                case 'field13':
					$content .= $errors['qcfname13'];
					if ($qcf['fieldtypeb'] == 'bdate') $content .= '<input type="text" class="qcfdate ' . $required . '" label="Date 2" name="qcfname13"  value="' . $values['qcfname13'] . '" onfocus="qcfclear(this, \'' . $values['qcfname13'] . '\')" onblur="qcfrecall(this, \'' . $values['qcfname13'] . '\')">
					<script type="text/javascript">jQuery(document).ready(function() {jQuery(\'\.qcfdate\').datepicker({dateFormat : \'dd M yy\'});});</script>'."\r\t";
					else $content .= '<input type="text" class="' . $required . '" label="Multibox 2" name="qcfname13" value="' . $values['qcfname13'] . '" onfocus="qcfclear(this, \'' . $values['qcfname13'] . '\')" onblur="qcfrecall(this, \'' . $values['qcfname13'] . '\')">'."\r\t";
					break;
				case 'field12':
					if ($errors['qcfname12']) $content .= $errors['qcfname12'];
					else $content .= '<p>'.$qcf['label']['field12'].'</p>'; 
					$content .= '<p>' . strip_tags($values['thesum']) . ' = <input type="text" class="required" style="width:3em;font-size:inherit;" label="Sum" name="qcfname12"  value="' . strip_tags($values['qcfname12']) . '"></p> 
					<input type="hidden" name="answer" value="' . strip_tags($values['answer']) . '" />
					<input type="hidden" name="thesum" value="' . strip_tags($values['thesum']) . '" />';
					break;
				}
			}
		}
	if ($attach['qcf_attach'] == "checked") {
		if ($errors['attach']) $content .= $errors['attach'];
		else $content .= '<p class="input">' . $attach['qcf_attach_label'] . '</p>'."\r\t";
		$size = $attach['qcf_attach_width'];
		$content .= '<p><input type="file" size="' . $size . '" name="filename"></p>'."\r\t";
		}
	$caption = $qcf['send'];
	if ($style['submit-button']) $content .= '<p><input type="image" value="' . $caption . '" style="border:none;width:100%;height:auto;overflow:hidden;" src="'.$style['submit-button'].'" id="submit" name="qcfsubmit'.$id.'" /></p>';
	else $content .= '<p><input type="submit" value="' . $caption . '" id="submit" name="qcfsubmit'.$id.'" /></p>';
	$content .= '</form>'."\r\t".
		'<div style="clear:both;"></div></div>'."\r\t".
		'</div>'."\r\t";
	echo $content;
	}
function qcf_dropdown($var,$list,$values,$errors,$required,$qcf,$name) {
    $content .= $errors[$var];
    $content .= '<select name="'.$var.'" class="' . $required . '" ><option value="' . $qcf['label'][$name] . '">' . $qcf['label'][$name] . '</option>'."\r\t";
    $arr = explode(",",$qcf[$list]);
    foreach ($arr as $item) {
        $selected = '';
        if ($values[$var] == $item) $selected = 'selected';
        $content .= '<option value="' .  $item . '" ' . $selected .'>' .  $item . '</option>'."\r\t";
    }
    $content .= '</select>'."\r\t";
    return $content;
    }
function qcf_checklist($var,$list,$values,$errors,$required,$qcf,$name) {
    if ($errors[$var]) $content .= $errors[$var].'<br>';
    else $content .= '<p class="input">' . $qcf['label'][$name] . '</p>';
    $content .= '<p class="input">';
    $arr = explode(",",$qcf[$list]);
    foreach ($arr as $item) {
        $checked = '';
        if ($values[$var.'_'. str_replace(' ','',$item)] == $item) $checked = 'checked';
        $content .= '<label><input type="checkbox" style="margin:0; padding: 0; border: none" name="'.$var.'_' . str_replace(' ','',$item) . '" value="' .  $item . '" ' . $checked . '> ' .  $item . '</label><br>';
        }
    $content .= '</p>';
    return $content;
    }
function qcf_radio($var,$list,$values,$errors,$required,$qcf,$name) {
    $content .= '<p class="input">' . $qcf['label'][$name] . '</p>';
    $arr = explode(",",$qcf[$list]);
    foreach ($arr as $item) {
        $checked = '';
        if ($values[$var] == $item) $checked = 'checked';
        if ($item === reset($arr)) $content .= '<p class="input"><input type="radio" style="margin:0; padding: 0; border: none" name="'.$var.'" value="' .  $item . '" id="' .  $item . '" checked><label for="' .  $item . '"> ' .  $item . '</label><br>';
        else $content .=  '<p class="input"><input type="radio" style="margin:0; padding: 0; border: none" name="'.$var.'" value="' .  $item . '" id="' .  $item . '" ' . $checked . '><label for="' .  $item . '"> ' .  $item . '</label><br>';
        }
    $content .= '</p>';
    return $content;
    }
function qcf_verify_form(&$values, &$errors,$id) {
	$qcf = qcf_get_stored_options($id);
	$error = qcf_get_stored_error($id);
	$attach = qcf_get_stored_attach($id);
	$emailcheck = $error['emailcheck'];
	if ($qcf['required']['field2'] == 'checked') $emailcheck = 'checked';
	$qcf['required']['field12'] = 'checked';
	$phonecheck = $error['phonecheck'];
	if ($qcf['required']['field3'] == 'checked') $phonecheck = 'checked';
	if ($qcf['active_buttons']['field2'] && $emailcheck == 'checked' && $values['qcfname2'] !== $qcf['label']['field2']) {
		if (!filter_var($values['qcfname2'], FILTER_VALIDATE_EMAIL))
			$errors['qcfname2'] = '<p><span>' . $error['email'] . '</span></p>';
		}
	if ($qcf['active_buttons']['field3'] && $phonecheck == 'checked' && $values['qcfname3'] !== $qcf['label']['field3']) {
		if (preg_match("/[^0-9()\+\.-\s]$/",$values['qcfname3']))
			$errors['qcfname3'] = '<p><span>' . $error['telephone'] . '</span></p>';
		}
    if ($qcf['fieldtype'] == 'tmail' && $qcf['active_buttons']['field11'] && $values['qcfname11'] !== $qcf['label']['field11']) {
        if (!filter_var($values['qcfname11'], FILTER_VALIDATE_EMAIL))
			$errors['qcfname11'] = '<p><span>' . $error['email'] . '</span></p>';
		}
	if ($qcf['fieldtype'] == 'ttele' && $qcf['active_buttons']['field11'] && $phonecheck == 'checked' && $values['qcfname11'] !== $qcf['label']['field11']) {
		if (preg_match("/[^0-9()\+\.-\s]$/",$values['qcfname11']))
			$errors['qcfname11'] = '<p><span>' . $error['telephone'] . '</span></p>';
		}
	if ($qcf['fieldtypeb'] == 'bmail' && $qcf['active_buttons']['field13'] && $values['qcfname13'] !== $qcf['label']['field13']) {
        if (!filter_var($values['qcfname13'], FILTER_VALIDATE_EMAIL))
			$errors['qcfname13'] = '<p><span>' . $error['email'] . '</span></p>';
		}
	if ($qcf['fieldtypeb'] == 'btele' && $qcf['active_buttons']['field13'] && $phonecheck == 'checked' && $values['qcfname13'] !== $qcf['label']['field13']) {
		if (preg_match("/[^0-9()\+\.-\s]$/",$values['qcfname11']))
			$errors['qcfname13'] = '<p><span>' . $error['telephone'] . '</span></p>';
		}
    foreach (explode( ',',$qcf['sort']) as $name)
        if ($qcf['active_buttons'][$name] && $qcf['required'][$name]) {
			switch ( $name ) {
				case 'field1':
					$values['qcfname1'] = filter_var($values['qcfname1'], FILTER_SANITIZE_STRING);
					if (empty($values['qcfname1']) || $values['qcfname1'] == $qcf['label'][$name])
						$errors['qcfname1'] = '<p><span>' . $error['field1'] . '</span></p>';
					break;
				case 'field2':
					$values['qcfname2'] = filter_var($values['qcfname2'], FILTER_SANITIZE_STRING);
					if (empty($values['qcfname2']) || $values['qcfname2'] == $qcf['label'][$name])
						$errors['qcfname2'] = '<p><span>' . $error['field2'] . '</span></p>';
					break;
				case 'field3':
					$values['qcfname3'] = filter_var($values['qcfname3'], FILTER_SANITIZE_STRING);
					if (empty($values['qcfname3']) || $values['qcfname3'] == $qcf['label'][$name])
						$errors['qcfname3'] = '<p><span>' . $error['field3'] . '</span></p>';
					break;
				case 'field4':
					$values['qcfname4'] = strip_tags(stripslashes($values['qcfname4']),$qcf['htmltags']);
					if (empty($values['qcfname4']) || $values['qcfname4'] == $qcf['label'][$name])
						$errors['qcfname4'] = '<p><span>' . $error['field4'] . '</span></p>';
					break;
				case 'field5':
                    if ($qcf['selectora'] == 'checkboxa') {
					   $check = '';
					   $arr = explode(",",$qcf['dropdownlist']);
					   foreach ($arr as $item) $check = $check . $values['qcfname5_'.str_replace(' ','',$item)];
					   if (empty($check)) $errors['qcfname5'] = '<p><span>' . $error['field5'] . '</span></p>';
                    } else {
                        $values['qcfname5'] = filter_var($values['qcfname5'], FILTER_SANITIZE_STRING);
                        if (empty($values['qcfname5']) || $values['qcfname5'] == $qcf['label'][$name])
                            $errors['qcfname5'] = '<p><span>' . $error['field5'] . '</span></p>';
                    }
					break;
				case 'field6':
                    if ($qcf['selectorb'] == 'checkboxb') {
					   $check = '';
					   $arr = explode(",",$qcf['checklist']);
					   foreach ($arr as $item) $check = $check . $values['qcfname6_'.str_replace(' ','',$item)];
					   if (empty($check)) $errors['qcfname6'] = '<p><span>' . $error['field6'] . '</span></p>';
                    } else {
                        $values['qcfname6'] = filter_var($values['qcfname6'], FILTER_SANITIZE_STRING);
                        if (empty($values['qcfname6']) || $values['qcfname6'] == $qcf['label'][$name])
                            $errors['qcfname6'] = '<p><span>' . $error['field6'] . '</span></p>';
                    }
                case 'field7':
                    if ($qcf['selectorc'] == 'checkboxc') {
					   $check = '';
					   $arr = explode(",",$qcf['radiolist']);
					   foreach ($arr as $item) $check = $check . $values['qcfname7_'.str_replace(' ','',$item)];
					   if (empty($check)) $errors['qcfname7'] = '<p><span>' . $error['field7'] . '</span></p>';
                    } else {
                        $values['qcfname7'] = filter_var($values['qcfname7'], FILTER_SANITIZE_STRING);
                        if (empty($values['qcfname7']) || $values['qcfname7'] == $qcf['label'][$name])
                            $errors['qcfname67'] = '<p><span>' . $error['field7'] . '</span></p>';
                    }
					break;
				case 'field8':
					$values['qcfname8'] = filter_var($values['qcfname8'], FILTER_SANITIZE_STRING);
					if (empty($values['qcfname8']) || $values['qcfname8'] == $qcf['label'][$name])
						$errors['qcfname8'] = '<p><span>' . $error['field8'] . '</span></p>';
					break;
				case 'field9':
					$values['qcfname9'] = filter_var($values['qcfname9'], FILTER_SANITIZE_STRING);
					if (empty($values['qcfname9']) || $values['qcfname9'] == $qcf['label'][$name])
						$errors['qcfname9'] = '<p><span>' . $error['field9'] . '</span></p>';
					break;
				case 'field10':
					$values['qcfname10'] = filter_var($values['qcfname10'], FILTER_SANITIZE_STRING);
					if (empty($values['qcfname10']) || $values['qcfname10'] == $qcf['label'][$name])
						$errors['qcfname10'] = '<p><span>' . $error['field10'] . '</span></p>';
					break;
				case 'field11':
					$values['qcfname11'] = filter_var($values['qcfname11'], FILTER_SANITIZE_STRING);
					if (empty($values['qcfname11']) || $values['qcfname11'] == $qcf['label'][$name])
						$errors['qcfname11'] = '<p><span>' . $error['field11'] . '</span></p>';
					break;
				case 'field12':
					$values['qcfname12'] = filter_var($values['qcfname12'], FILTER_SANITIZE_STRING);
					if($values['qcfname12']<>$values['answer']) $errors['qcfname12'] = '<p><span>' . $error['mathsanswer'] . '</span></p>';
                    			if(empty($values['qcfname12'])) $errors['qcfname12'] = '<p><span>' .$error['mathsmissing'] . '</span></p>'; 
					break;
                case 'field13':
					$values['qcfname13'] = filter_var($values['qcfname13'], FILTER_SANITIZE_STRING);
					if (empty($values['qcfname13']) || $values['qcfname13'] == $qcf['label'][$name])
						$errors['qcfname13'] = '<p><span>' . $error['field13'] . '</span></p>';
					break;
				}
			}			
	$tmp_name = $_FILES['filename']['tmp_name'];
	$name = $_FILES['filename']['name'];
	$size = $_FILES['filename']['size'];
	if (file_exists($tmp_name)) {
		if ($size > $attach['qcf_attach_size']) $errors['attach'] = '<p><span>' . $attach['qcf_attach_error_size'] . '</span></p>'; 
		$ext = substr(strrchr($name,'.'),1);
		$pos = strpos($qcf['qcf_attach_type'],$ext);
		if (strpos($attach['qcf_attach_type'],$ext) === false) $errors['attach'] = '<p><span>' . $attach['qcf_attach_error_type'] . '</span></p>'; 
		}
	return (count($errors) == 0);	
	}

function qcf_process_form($values,$id) {
	$qcf = qcf_get_stored_options($id);
	$reply = qcf_get_stored_reply($id);
	$style = qcf_get_stored_style($id);
	$qcfemail = qcf_get_stored_email();
	$qcf_email = $qcfemail[$id];
	if (empty($qcf_email)) {global $current_user;
	get_currentuserinfo();
	$qcf_email = $current_user->user_email;}
	if (!empty($reply['replytitle'])) $reply['replytitle'] = '<h2>' . $reply['replytitle'] . '</h2>';
	if (!empty($reply['replyblurb'])) $reply['replyblurb'] = '<p>' . $reply['replyblurb'] . '</p>';
	if ( $reply['subjectoption'] == "sendername") $addon = $values['qcfname1'];
	if ( $reply['subjectoption'] == "sendersubj") $addon = $values['qcfname9'];
	if ( $reply['subjectoption'] == "senderpage") $addon = $pagetitle;
	if ( $reply['subjectoption'] == "sendernone") $addon = ''; 
	$ip=$_SERVER['REMOTE_ADDR'];
	$url = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
	$page = get_the_title();
	if (empty($page)) $page = 'quick contact form';
	foreach (explode( ',',$qcf['sort']) as $item)
		if ($qcf['active_buttons'][$item]) {
			switch ( $item ) {
				case 'field1':
					if ($values['qcfname1'] == $qcf['label'][$item]) $values['qcfname1'] ='';
					$content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags(stripslashes($values['qcfname1'])) . '</p>';
					break;
				case 'field2':
					if ($values['qcfname2'] == $qcf['label'][$item]) $values['qcfname2'] ='';
					$content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags(stripslashes($values['qcfname2'])) . '</p>';
					break;
				case 'field3':
					if ($values['qcfname3'] == $qcf['label'][$item]) $values['qcfname3'] ='';
					$content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags(stripslashes($values['qcfname3'])) . '</p>';
					break;
				case 'field4':
					if ($values['qcfname4'] == $qcf['label'][$item]) $values['qcfname4'] ='';
					$content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags(stripslashes($values['qcfname4']),$qcf['htmltags']) . '</p>';
					break;
				case 'field5':
                    if ($qcf['selectora'] == 'checkboxa') {
                        $checks ='';
                        $arr = explode(",",$qcf['dropdownlist']);
                        $content .= '<p><b>' . $qcf['label'][$item] . ': </b>';
                        foreach ($arr as $key) if ($values['qcfname5_' . str_replace(' ','',$key)]) $checks .= $key . ', ';
                            $content .= rtrim( $checks , ', ' ) . '</p>';
                    } else {
                        if ($values['qcfname5'] == $qcf['label'][$item]) $values['qcfname5'] ='';
                        $content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . $values['qcfname5'] . '</p>';
                    }
					break;
				case 'field6':
                    if ($qcf['selectorb'] == 'checkboxb') {
                        $checks ='';
                        $arr = explode(",",$qcf['checklist']);
                        $content .= '<p><b>' . $qcf['label'][$item] . ': </b>';
                        foreach ($arr as $key) if ($values['qcfname6_' . str_replace(' ','',$key)]) $checks .= $key . ', ';
                        $content .= rtrim( $checks , ', ' ) . '</p>';
                    } else {
                        if ($values['qcfname6'] == $qcf['label'][$item]) $values['qcfname6'] ='';
                        $content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . $values['qcfname6'] . '</p>';
                    }
					break;
				case 'field7':
                    if ($qcf['selectorc'] == 'checkboxc') {
                        $checks ='';
                        $arr = explode(",",$qcf['radiolist']);
                        $content .= '<p><b>' . $qcf['label'][$item] . ': </b>';
                        foreach ($arr as $key) if ($values['qcfname7_' . str_replace(' ','',$key)]) $checks .= $key . ', ';
                        $content .= rtrim( $checks , ', ' ) . '</p>';
                    } else {
					   if ($values['qcfname7'] == $qcf['label'][$item]) $values['qcfname7'] ='';
					   $content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . $values['qcfname7'] . '</p>';
                    }
					break;
				case 'field8':
					if ($values['qcfname8'] == $qcf['label'][$item]) $values['qcfname8'] ='';
					$content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags(stripslashes($values['qcfname8'])) . '</p>';
					break;
				case 'field9':
					if ($values['qcfname9'] == $qcf['label'][$item]) $values['qcfname9'] ='';
					$content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags(stripslashes($values['qcfname9'])) . '</p>';
					break;
				case 'field10':
					if ($values['qcfname10'] == $qcf['label'][$item]) $values['qcfname10'] ='';
					if (!empty($values['qcfname10'])) $content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags($values['qcfname10']) . '</p>';
					break;
				case 'field11':
					if ($values['qcfname11'] == $qcf['label'][$item]) $values['qcfname11'] ='';
					$content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags(stripslashes($values['qcfname11'])) . '</p>';
					break;
                case 'field13':
					if ($values['qcfname13'] == $qcf['label'][$item]) $values['qcfname13'] ='';
					$content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags(stripslashes($values['qcfname13'])) . '</p>';
					break;
				}
			}
	$sendcontent = "<html><h2>".$reply['bodyhead']."</h2>".$content;
	$copycontent = "<html>";
	if ($reply['replymessage']) $copycontent .=$reply['replymessage'];
	if ($reply['replycopy']) $copycontent .= $content;
	if ($reply['page']) $sendcontent .= "<p>Message was sent from: <b>".$page."</b></p>";
	if ($reply['tracker']) $sendcontent .= "<p>Senders IP address: <b>".$ip."</b></p>";
	if ($reply['url']) $sendcontent .= "<p>URL: <b>".$url."</b></p>";
	$sendcontent .="</html>";
	$copycontent .="</html>";
	$subject = "{$reply['subject']} {$addon}";
	$tmp_name = $_FILES['filename']['tmp_name'];
	$type = $_FILES['filename']['type'];
	$name = $_FILES['filename']['name'];
	$size = $_FILES['filename']['size'];
	if (file_exists($tmp_name)) {
 		if(is_uploaded_file($tmp_name)) {
			$file = fopen($tmp_name,'rb');
     			$data = fread($file,filesize($tmp_name));
     			fclose($file);
     			$data = chunk_split(base64_encode($data));
    			}
			$bound_text = "x".md5(mt_rand())."x";
			$bound = "--".$bound_text."\r\n";
			$bound_last = "--".$bound_text."--\r\n";
 			$headers = "From: {$values['qcfname1']} <{$values['qcfname2']}>\r\n"
			."MIME-Version: 1.0\r\n"
  			."Content-Type: multipart/mixed; boundary=\"$bound_text\"";
			$message .= "If you can see this MIME than your client doesn't accept MIME types!\r\n"
  			.$bound; 
			$message .= "Content-Type: text/html; charset=\"utf-8\"\r\n"
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
			$headers = "From: {$values['qcfname1']} <{$values['qcfname2']}>\r\n";
                if ($reply['qcf_bcc']) { $headers .= "BCC: ".$qcf_email."\r\n";$qcf_email = 'null';}
                
			$headers .= "MIME-Version: 1.0\r\n"
			. "Content-Type: text/html; charset=\"utf-8\"\r\n"; 
			$message = $sendcontent;
			}
	if (function_exists('qcf_select_email')) {
		$email = qcf_select_email($id,$values['qcfname5']);
		if ($email) $qcf_email = $email;
		}
	if ($reply['qcfmail'] == 'phpmail') mail($qcf_email, $subject, $message, $headers);
	if ($reply['qcfmail'] == 'wpemail') wp_mail($qcf_email, $subject, $message, $headers);
	if ($reply['qcfmail'] == 'smtp') {
		$qcfsmtp = qcf_get_stored_smtp ();
		require_once ABSPATH . WPINC . '/class-phpmailer.php';
		require_once ABSPATH . WPINC . '/class-smtp.php';
		$phpmailer = new PHPMailer( true );
		$phpmailer->Mailer = 'smtp';
		$phpmailer->AddAddress($qcf_email);
		$phpmailer->SetFrom($values['qcfname2'], $values['qcfname1']);
		$phpmailer->Subject = $subject;
		$phpmailer->ContentType = "text/html";
		$phpmailer->MsgHTML($message);
		$phpmailer->IsSMTP();
		$phpmailer->SMTPSecure = $qcfsmtp['smtp_ssl'] == 'none' ? '' : $qcfsmtp['smtp_ssl'];
		$phpmailer->Host = $qcfsmtp['smtp_host'];
		$phpmailer->Port = $qcfsmtp['smtp_port'];
		if ($qcfsmtp['smtp_auth'] == "authtrue") {
			$phpmailer->SMTPAuth = TRUE;
			$phpmailer->Username = $qcfsmtp['smtp_user'];
			$phpmailer->Password = $qcfsmtp['smtp_pass'];}
		$phpmailer->Send();
		unset($phpmailer);
		}
	if ($reply['sendcopy']) mail($values['qcfname2'], $reply['replysubject'], $copycontent, $headers);
	
	$qcf_messages = get_option('qcf_messages'.$id);
	if(!is_array($qcf_messages)) $qcf_messages = array();
	if ($values['qcfname1'] == $qcf['label']['field1']) $values['qcfname1'] ='';
	$sentdate = date_i18n('d M Y');
	$qcf_messages[] = array('field0'=>$sentdate,'field1' => $values['qcfname1'] , 'field2' => $values['qcfname2'] , 'field3' => $values['qcfname3'], 'field4' => $values['qcfname4'], 'field5' => $values['qcfname5'], 'field6' => $values['qcfname6'], 'field7' => $values['qcfname7'], 'field8' => $values['qcfname8'], 'field9' => $values['qcfname9'], 'field10' => $values['qcfname10'], 'field11' => $values['qcfname11'], 'field13' => $values['qcfname13'],date => $sentdate,);
	update_option('qcf_messages'.$id,$qcf_messages);
	if ( $reply['qcf_redirect'] == 'checked') {
		$location = $reply['qcf_redirect_url'];	
		echo "<meta http-equiv='refresh' content='0;url=$location' />";
		}
	else {
		$replycontent = "<div class='qcf-style ".$id."'>\r\t
		<div id='" . $style['border'] . "'>\r\t";
		$replycontent .= $reply['replytitle'].$reply['replyblurb'];
		if ($reply['messages']) $replycontent .= $content;
		$replycontent.='</div></div>';
		echo $replycontent; 
		if ( $reply['qcf_reload'] == 'checked') {
			$_POST = array();
			echo '<meta http-equiv="refresh" content="'.$reply['qcf_reload_time'].'">';
			}
		}
	}
function qcf_loop($id) {
	ob_start();
	if (isset($_POST['qcfsubmit'.$id]) || isset($_POST['qcfsubmit'.$id.'_x'])) {
		$formvalues = $_POST;
		$formerrors = array();
		if (!qcf_verify_form($formvalues, $formerrors,$id)) qcf_display_form($formvalues, $formerrors,$id);
    	else qcf_process_form($formvalues,$id);
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
		$qcf = qcf_get_stored_options($id);
		for ($i=1; $i<=13; $i++) { $values['qcfname'.$i] = $qcf['label']['field'.$i]; }
		$values['qcfname12'] = '';
        qcf_display_form( $values , null,$id );
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
		$instance = wp_parse_args( (array) $instance, array( 'formname' => '' ) );
		$formname = $instance['formname'];
		$qcf_setup = qcf_get_stored_setup();
		echo 'Select Form:</ br>';
		?><select class="widefat" name="<?php echo $this->get_field_name('formname'); ?>"><?php
		$arr = explode(",",$qcf_setup['alternative']);
		foreach ($arr as $item) {
			if ($item == '') {$showname = 'default'; $item='';} else $showname = $item;
			if ($showname == $formname || $formname == '') $selected = 'selected'; else $selected = '';
			?><option value="<?php echo $item; ?>" id="<?php echo $this->get_field_id('formname'); ?>" <?php echo $selected; ?>><?php echo $showname; ?></option>
			<?php  
			}
		?>
		</select>
		<p>All options for the quick contact form are changed on the plugin <a href="options-general.php?page=quick-contact-form/settings.php">Settings</a> page.</p>
		<?php
		}
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['formname'] = $new_instance['formname'];
		return $instance;
		}
	function widget($args, $instance) {
 	   	extract($args, EXTR_SKIP);
		$id=$instance['formname'];
		echo qcf_loop($id);
		}
	}
add_action( 'widgets_init', create_function('', 'return register_widget("qcf_widget");') );

function qcf_generate_css() {
	$qcf_form = qcf_get_stored_setup();
	$arr = explode(",",$qcf_form['alternative']);
	foreach ($arr as $item) {
		$corners='';$input='';$background='';$submitwidth='';$paragraph ='';$submitbutton='';
		$style = qcf_get_stored_style($item);
		if ($item !='') $id = '.'.$item; else $id = '.default';
		if ($style['font'] == 'plugin') {
			$font = "font-family: ".$style['text-font-family']."; font-size: ".$style['text-font-size'].";color: ".$style['text-font-colour'].";height:auto;";
			$inputfont = "font-family: ".$style['font-family']."; font-size: ".$style['font-size']."; color: ".$style['font-colour'].";";
			$submitfont = "font-family: ".$style['font-family'];
			if ($style['header']) $header = ".qcf-style".$id." h2 {font-size: ".$style['header-size']."; color: ".$style['header-colour'].";height:auto;}";
			}
		$input = ".qcf-style".$id." input[type=text], .qcf-style".$id." textarea, .qcf-style".$id." select {border: ".$style['input-border'].";background:".$style['inputbackground'].";".$inputfont.";height:auto;}\r\n";
		$focus = ".qcf-style".$id." input:focus, .qcf-style".$id." textarea:focus {background:".$style['inputfocus'].";}\r\n";
		$paragraph = ".qcf-style".$id." p, .qcf-style".$id." select{".$font.";}\r\n";
		$required = ".qcf-style".$id." input[type=text].required, .qcf-style".$id." select.required, .qcf-style".$id." textarea.required {border: ".$style['input-required'].";}\r\n";
		if ($style['submitwidth'] == 'submitpercent') $submitwidth = 'width:100%;';
		if ($style['submitwidth'] == 'submitrandom') $submitwidth = 'width:auto;';
		if ($style['submitwidth'] == 'submitpixel') $submitwidth = 'width:'.$style['submitwidthset'].';';
		if ($style['submitposition'] == 'submitleft') $submitposition = 'float:left;'; else $submitposition = 'float:right;';
		$submitbutton = ".qcf-style".$id." #submit, .qcf-style".$id." #submit:hover{".$submitposition.$submitwidth."color:".$style['submit-colour'].";background:".$style['submit-background'].";border:".$style['submit-border'].";".$submitfont.";font-size: inherit;}\r\n";
		if ($style['border']<>'none') $border =".qcf-style".$id." #".$style['border']." {border:".$style['form-border'].";}\r\n";
		if ($style['background'] == 'white') $background = ".qcf-style".$id." div {background:#FFF;}\r\n";
		if ($style['background'] == 'color') $background = ".qcf-style".$id." div {background:".$style['backgroundhex'].";}\r\n";
		if ($style['backgroundimage']) $background = ".qcf-style".$id." div {background: url('".$style['backgroundimage']."');}\r\n";
$formwidth = preg_split('#(?<=\d)(?=[a-z%])#i', $style['width']);
if (!$formwidth[1]) $formwidth[1] = 'px';
		if ($style['widthtype'] == 'pixel') $width = $formwidth[0].$formwidth[1];
		else $width = '100%';
		if ($style['corners'] == 'round') $corner = '5px'; else $corner = '0';
		$corners = ".qcf-style".$id." input[type=text], .qcf-style".$id." textarea, .qcf-style".$id." select, .qcf-style".$id." #submit {border-radius:".$corner.";}\r\n";
		if ($style['corners'] == 'theme') $corners = '';
		$code .= ".qcf-style".$id." {width:".$width.";}\r\n".$border.$corners.$header.$paragraph.$input.$focus.$required.$background.$submitbutton;
		if ($style['use_custom'] == 'checked') $code .= $style['styles'] . "\r\n";
		}
	return $code;	
	}
function qcf_head_css () {
	$data = '<style type="text/css" media="screen">'.qcf_generate_css().'</style>';
	echo $data;
	}
function qcf_get_stored_options ($id) {
	$qcf = get_option('qcf_settings'.$id);
	if(!is_array($qcf)) $qcf = array();
	$default = qcf_get_default_options();
	$qcf = array_merge($default, $qcf);
	if (!strpos($qcf['sort'],'10')) {$qcf['sort'] = $qcf['sort'].',field10';$qcf['label']['field12'] = 'Select Date';}
	if (!strpos($qcf['sort'],'11')) {$qcf['sort'] = $qcf['sort'].',field11';$qcf['label']['field11'] = 'Add text';}
	if (!strpos($qcf['sort'],'12')) {$qcf['sort'] = $qcf['sort'].',field12';}
    if (!strpos($qcf['sort'],'13')) {$qcf['sort'] = $qcf['sort'].',field13';$qcf['label']['field13'] = 'Add text';}
	if ($qcf['captcha'] && !strpos('field12', $qcf['sort'])) {$qcf['label']['field12'] = $qcf['mathscaption'];$qcf['active_buttons']['field12'] = 'on';$qcf['required']['field12'] = 'checked';}
	if (!$qcf['captcha'] && !$qcf['label']['field12']){$qcf['label']['field12'] = 'Spambot blocker question';$qcf['required']['field12'] = 'checked';}
	$version = get_option('qcf_version');
    if ($version <> '6.8') {update_option('qcf_settings',$qcf);update_option('qcf_version','6.8');}         
    return $qcf;
	}
function qcf_get_default_options () {
	$qcf = array();
	$qcf['active_buttons'] = array( 'field1'=>'on' , 'field2'=>'on','field4'=>'on');	
	$qcf['required'] = array('field1'=>'checked' , 'field2'=>'checked','field12'=>'checked');
	$qcf['label'] = array( 'field1'=>'Your Name' , 'field2'=>'Email' , 'field3'=>'Telephone' , 'field4'=>'Message' , 'field5'=>'Select a value' , 'field6'=>'Select a value' ,  'field7'=>'Select a value' , 'field8'=>'Website' , 'field9'=>'Subject', 'field10'=>'Select date', 'field11'=>'Add text','field12'=>'Spambot blocker question', 'field13'=>'Add text');
	$qcf['sort'] = 'field1,field2,field3,field4,field5,field6,field7,field10,field8,field9,field11,field13,field12';
	$qcf['lines'] = 6;
	$qcf['htmltags'] = '<a><b><i>';
	$qcf['datepicker'] = 'checked';
	$qcf['dropdownlist'] = 'Pound,Dollar,Euro,Yen,Triganic Pu';
	$qcf['checklist'] = 'Donald Duck,Mickey Mouse,Goofy';
	$qcf['radiolist'] = 'Large,Medium,Small';
	$qcf['title'] = 'Enquiry Form';
	$qcf['blurb'] = 'Fill in the form below and we will be in touch soon';
	$qcf['send'] = 'Send it!';
	$qcf['fieldtype'] = 'ttext';
    $qcf['fieldtypeb'] = 'btext';
    $qcf['selectora'] = 'dropdowna';
    $qcf['selectorb'] = 'checkboxb';
    $qcf['selectorc'] = 'radioc';
	return $qcf;
	}
function qcf_get_stored_attach ($id) {
	$attach = get_option('qcf_attach'.$id);
	if(!is_array($attach)) $attach = array();
	$default = qcf_get_default_attach();
	$attach = array_merge($default, $attach);
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
function qcf_get_stored_style($id) {
	$style = get_option('qcf_style'.$id);
	if(!is_array($style)) $style = array();
	$default = qcf_get_default_style();
	$style = array_merge($default, $style);
	return $style;
	}
function qcf_get_default_style() {
	$style['font'] = 'plugin';
	$style['font-family'] = 'arial, sans-serif';
	$style['font-size'] = '1.2em';
	$style['font-colour'] = '#465069';
	$style['header'] = '';
	$style['header-size'] = '1.6em';
	$style['header-colour'] = '#465069';
	$style['text-font-family'] = 'arial, sans-serif';
	$style['text-font-size'] = '1.2em';
	$style['text-font-colour'] = '#465069';
	$style['width'] = 280;
	$style['widthtype'] = 'pixel';
	$style['submitwidth'] = 'submitpercent';
	$style['submitposition'] = 'submitleft';
	$style['border'] = 'plain';
	$style['form-border'] = '1px solid #415063';
	$style['input-border'] = '1px solid #415063';
	$style['input-required'] = '1px solid #00C618';
	$style['bordercolour'] = '#415063';
	$style['inputborderdefault'] = '1px solid #415063';
	$style['inputborderrequired'] = '1px solid #00C618';
	$style['inputbackground'] = '#FFFFFF';
	$style['inputfocus'] = '#FFFFCC';
	$style['background'] = 'white';
	$style['backgroundhex'] = '#FFF';
	$style['submit-colour'] = '#FFF';
	$style['submit-background'] = '#343838';
	$style['submit-button'] = '';
	$style['submit-border'] = '1px solid #415063';
	$style['submitwidth'] = 'submitpercent';
	$style['submitposition'] = 'submitleft';
	$style['corners'] = 'corner';
	$style['use_custom'] = '';
	$style['styles'] = ".qcf-style {\r\n\r\n}";
	return $style;
	}
function qcf_get_stored_reply ($id) {
	$reply = get_option('qcf_reply'.$id);
	if(!is_array($reply)) $reply = array();
	$default = qcf_get_default_reply();
	$reply = array_merge($default, $reply);
	return $reply;
	}
function qcf_get_default_reply () {
	$reply = array();
	$reply['replytitle'] = 'Message sent!';
	$reply['replyblurb'] = 'Thank you for your enquiry, I&#146;ll be in contact soon';
	$reply['sendcopy'] = '!';
	$reply['replycopy'] = '!';
	$reply['replysubject'] = 'Thank you for your enquiry';
	$reply['replymessage'] = 'I&#146;ll be in contact soon. If you have any questions please reply to this email.';
	$reply['messages'] = 'checked';
	$reply['tracker'] = 'checked';
	$reply['page'] = 'checked';
	$reply['url'] = '';
	$reply['subject'] = 'Enquiry from';
	$reply['subjectoption'] = 'sendername';
	$reply['qcf_redirect'] = '';
	$reply['qcf_redirect_url'] = '';
	$reply['copy_message'] = 'Thank you for your enquiry. This is a copy of your message';
	$reply['qcf_reload'] = '';
	$reply['qcf_reload_time'] = '5';
	$reply['qcfmail'] = 'wpemail';
	$reply['bodyhead'] = 'The message is:';
	return $reply;
	}
function qcf_get_stored_error ($id) {
	$error = get_option('qcf_error'.$id);
	if(!is_array($error)) $error = array();
	$default = qcf_get_default_error($id);
	$error = array_merge($default, $error);
	return $error;
	}
function qcf_get_default_error ($id) {
	$qcf = qcf_get_stored_options($id);
	$error = array();
	$error['field1'] = 'Giving me '. strtolower($qcf['label']['field1']) . ' would really help.';
	$error['field2'] = 'Please enter your '. strtolower($qcf['label']['field2']) . ' address';
	$error['field3'] = 'A telephone number is needed';
	$error['field4'] = 'What is the '. strtolower($qcf['label']['field4']);
	$error['field5'] = 'Select a option from the list';
	$error['field6'] = 'Check at least one box';
	$error['field7'] = 'There is an error';
	$error['field8'] = 'The ' . strtolower($qcf['label']['field8']) . ' is missing';
	$error['field9'] = 'What is your '. strtolower($qcf['label']['field9']) . '?';
	$error['field10'] = 'Please select a date';
	$error['field11'] = 'Enter a value';
    $error['field13'] = 'Enter a value';
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
function qcf_get_stored_setup () {
	$qcf_setup = get_option('qcf_setup');
	if(!is_array($qcf_setup)) $qcf_setup = array();
	$default = qcf_get_default_setup();
	$qcf_setup = array_merge($default, $qcf_setup);
	return $qcf_setup;
	}
function qcf_get_default_setup () {
	$qcf_setup = array();
	$qcf_setup['current'] = '';
	$qcf_setup['alternative'] = '';
	$qcf_setup['dashboard'] = '';
	return $qcf_setup;
	}
function qcf_get_stored_email () {
	$qcf_email = get_option('qcf_email');
	if(!is_array($qcf_email)) { $old_email = $qcf_email; $qcf_email = array(); $qcf_email[''] = $old_email;}
	$default = qcf_get_default_email();
	$qcf_email = array_merge($default, $qcf_email);
	return $qcf_email;
	}
function qcf_get_default_email () {	
	$qcf_email = array();
	$qcf_email[''] = '';
	return $qcf_email;
	}
function qcf_get_stored_msg () {
	$messageoptions = get_option('qcf_messageoptions');
	if(!is_array($messageoptions)) $messageoptions = array();
	$default = qcf_get_default_msg();
	$messageoptions = array_merge($default, $messageoptions);
	return $messageoptions;
	}
function qcf_get_default_msg () {
	$messageoptions = array();
	$messageoptions['messageqty'] = 'fifty';
	$messageoptions['messageorder'] = 'newest';
	return $messageoptions;
	}
function qcf_get_stored_smtp () {
	$smtp = get_option('qcf_smtp');
	if(!is_array($smtp)) $smtp = array();
	$default = qcf_get_default_smtp();
	$smtp = array_merge($default, $smtp);
	return $smtp;
	}
function qcf_get_default_smtp () {
	$smtp = array();
	$smtp['smtp_host'] = 'localhost';
	$smtp['smtp_port'] = '25';
	$smtp['smtp_ssl'] = 'none';
	$smtp['smtp_auth'] = 'authfalse';
	$smtp['smtp_user'] = '';
	$smtp['smtp_pass'] = '';
	return $smtp;
	}