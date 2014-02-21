<?php
/*
Plugin Name: Quick Contact Form
Plugin URI: http://quick-plugins.com/quick-contact-form/
Description: A really, really simple contact form. There is nothing to configure, just add your email address and it's ready to go.
Version: 4.5
Author: fisicx
Author URI: http://quick-plugins.com/
*/

add_shortcode('qcf', 'qcf_start');
add_action('wp_head', 'qcf_use_custom_css');

if (is_admin()) require_once( plugin_dir_path( __FILE__ ) . '/settings.php' );

$myScriptUrl = plugins_url('quick-contact-form-javascript.js', __FILE__);
wp_register_script('qcf_script', $myScriptUrl);
wp_enqueue_script( 'qcf_script');

$myStyleUrl = plugins_url('quick-contact-form-style.css', __FILE__);
wp_register_style('qcf_style', $myStyleUrl);
wp_enqueue_style( 'qcf_style');

function qcf_start() {
	return qcf_loop();
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
	if (!empty($qcf['title'])) $qcf['title'] = '<h2>' . $qcf['title'] . '</h2>';
	if (!empty($qcf['blurb'])) $qcf['blurb'] = '<p>' . $qcf['blurb'] . '</p>';
	if (!empty($qcf['mathscaption'])) $qcf['mathscaption'] = '<p class="input">' . $qcf['mathscaption'] . '</p>';
	$content = "<div id='qcf-style'>\r\t
	<div id='" . $style['border'] . "'>\r\t";
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
					$content .= '<input type="text" ' . $required . ' label="Name" name="qcfname1" value="' . $values['qcfname1'] . '" onfocus="qcfclear(this, \'' . $values['qcfname1'] . '\')" onblur="qcfrecall(this, \'' . $values['qcfname1'] . '\')">'."\r\t";
					break;
				case 'field2':
					$content .= $errors['qcfname2'];
					$content .= '<input type="text" ' . $required . ' label="Name" name="qcfname2"  value="' . $values['qcfname2'] . '" onfocus="qcfclear(this, \'' . $values['qcfname2'] . '\')" onblur="qcfrecall(this, \'' . $values['qcfname2'] . '\')">'."\r\t";
					break;
				case 'field3':
					$content .= $errors['qcfname3'];
					$content .= '<input type="text" ' . $required . ' label="Name" name="qcfname3"  value="' . $values['qcfname3'] . '" onfocus="qcfclear(this, \'' . $values['qcfname3'] . '\')" onblur="qcfrecall(this, \'' . $values['qcfname3'] . '\')">'."\r\t";
					break;
				case 'field4':
					$content .= $errors['qcfname4'];
					$content .= '<textarea ' . $required . '  rows="' . $qcf['lines'] . '" label="Name" name="qcfname4" onfocus="qcfclear(this, \'' . $values['qcfname4'] . '\')" onblur="qcfrecall(this, \'' . $values['qcfname4'] . '\')">' . strip_tags(stripslashes($values['qcfname4'])) . '</textarea>'."\r\t";
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
					$content .= '<input type="text" ' . $required . ' label="Name" name="qcfname8" value="' . $values['qcfname8'] . '" onfocus="qcfclear(this, \'' . $values['qcfname8'] . '\')" onblur="qcfrecall(this, \'' . $values['qcfname8'] . '\')">'."\r\t";
					break;
				case 'field9':
					$content .= $errors['qcfname9'];
					$content .= '<input type="text" ' . $required . ' label="Name" name="qcfname9"  value="' . $values['qcfname9'] . '" onfocus="qcfclear(this, \'' . $values['qcfname9'] . '\')" onblur="qcfrecall(this, \'' . $values['qcfname9'] . '\')">'."\r\t";
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
		if ( $reply['subjectoption'] == "sendername") $addon = $values['qcfname1'];
	if ( $reply['subjectoption'] == "senderpage") $addon = $pagetitle;
	if ( $reply['subjectoption'] == "sendernone") $addon = ''; 
	$ip=$_SERVER['REMOTE_ADDR'];
	$url = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
	$page = get_the_title();
	if (empty($page)) $page = 'quick contact form';
	$replycontent = "<div id='qcf-style'>\r\t
	<div id='" . $style['border'] . "'>\r\t";
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
	if ($reply['page']) $sendcontent .= "<p>Message was sent from: <b>".$page."</b></p>";
	if ($reply['tracker']) $sendcontent .= "<p>Senders IP address: <b>".$ip."</b></p>";
	if ($reply['url']) $sendcontent .= "<p>URL: <b>".$url."</b></p>";
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
		$replycontent .= $reply['replytitle'].$reply['replyblurb'];
		if ($reply['messages']) $replycontent .= $content.'</div></div>';
		echo $replycontent; 
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
		echo '<p>All options for the quick contact form are changed on the plugin <a href="'.get_admin_url().'options-general.php?page=quick-contact-form/settings.php">Settings</a> page.</p>';
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

function qcf_use_custom_css () {
	$style = qcf_get_stored_style();
	if ($style['font'] == 'plugin') {
		$font = "font-family: ".$style['font-family']."; font-size: ".$style['font-size']."; ";
		$input = "#qcf-style input[type=text], #qcf-style textarea, #qcf-style select, #qcf-style #submit {".$font."}\r\n";
		}
	if ($style['background'] == 'white') $background = "#qcf-style div {background:#FFF;}\r\n";
	if ($style['background'] == 'color') $background = "#qcf-style div {background:".$style['backgroundhex'].";}\r\n";
	if ($style['widthtype'] == 'pixel') $width = preg_replace("/[^0-9]/", "", $style['width']) . 'px';
	else $width = '100%';
	if ($style['corners'] == 'round') $corner = '5px';
	if ($style['corners'] == 'square') $corner = '0';
	$corners = "#qcf-style input[type=text], #qcf-style textarea, #qcf-style select, #qcf-style #submit {border-radius:".$corner.";}\r\n";
	$code = "<style type=\"text/css\" media=\"screen\">\r\n";
	$code .= "#qcf-style {width:".$width.";}\r\n"; 
	$code .= $corners; 
	$code .= $input; 
	$code .= $background; 
	if ($style['use_custom'] == 'checked') $code .= $style['styles'] . "\r\n";
	$code .= "</style>\r\n";
	echo $code;
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
	$style['font'] = 'theme';
	$style['font-family'] = 'arial, sans-serif';
	$style['font-size'] = '1.2em';
	$style['width'] = 280;
	$style['widthtype'] = 'pixel';
	$style['border'] = 'rounded';
	$style['background'] = 'white';
	$style['backgroundhex'] = '#FFF';
	$style['corners'] = 'corner';
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
	$reply['page'] = 'checked';
	$reply['url'] = '';
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
