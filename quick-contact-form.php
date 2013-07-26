<?php
/*
Plugin Name: Quick Contact Form
Plugin URI: http://quick-plugins.com/quick-contact-form/
Description: A really, really simple contact form. There is nothing to configure, just add your email address and it's ready to go.
Version: 5.7
Author: fisicx
Author URI: http://quick-plugins.com/
*/

add_shortcode('qcf', 'qcf_start');
add_action('wp_head', 'qcf_use_custom_css');
add_filter('plugin_action_links', 'qcf_plugin_action_links', 10, 2 );

if (is_admin()) require_once( plugin_dir_path( __FILE__ ) . '/settings.php' );

wp_enqueue_script( 'qcf_script',plugins_url('quick-contact-form-javascript.js', __FILE__));

wp_enqueue_style( 'qcf_style',plugins_url('quick-contact-form-style.css', __FILE__));
wp_enqueue_script('jquery-ui-datepicker');
wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');

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
function qcf_verify_form(&$values, &$errors,$id) {
	$qcf = qcf_get_stored_options($id);
	$error = qcf_get_stored_error($id);
	$attach = qcf_get_stored_attach($id);
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
				case 'field10':
					if (empty($values['qcfname10']) || $values['qcfname10'] == $qcf['label'][$name])
						$errors['qcfname10'] = '<p class="error">' . $error['field10'] . '</p>';
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
function qcf_display_form( $values, $errors, $id ) {
	$qcf_form = qcf_get_stored_setup();
	$qcf = qcf_get_stored_options($id);
	$error = qcf_get_stored_error($id);
	$reply = qcf_get_stored_reply($id);
	$attach = qcf_get_stored_attach($id);
	$style = qcf_get_stored_style($id);
	if (!empty($qcf['title'])) $qcf['title'] = '<h2>' . $qcf['title'] . '</h2>';
	if (!empty($qcf['blurb'])) $qcf['blurb'] = '<p>' . $qcf['blurb'] . '</p>';
	if (!empty($qcf['mathscaption'])) $qcf['mathscaption'] = '<p class="input">' . $qcf['mathscaption'] . '</p>';
	$content = "<div class='qcf-style ".$id."'>\r\t";
	$content .= "<div id='" . $style['border'] . "'>\r\t";
	if (count($errors) > 0)
		$content .= "<h2>" . $error['errortitle'] . "</h2>\r\t<p class='error'>" . $error['errorblurb'] . "</p>\r\t";
	else
		$content .= $qcf['title'] . "\r\t" . $qcf['blurb'] . "\r\t";	
	$content .= "<form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\r\t";
		foreach (explode( ',',$qcf['sort']) as $name) {
		$required = ( $qcf['required'][$name]) ? 'class="required"' : '';
		if ($qcf['active_buttons'][$name] == "on") {
			switch ( $name ) {
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
						foreach ($arr as $item) {
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
					foreach ($arr as $item) {
						$checked = '';
						if ($values['qcfname6_'. str_replace(' ','',$item)] == $item) $checked = 'checked';
						$content .= '<input type="checkbox" style="margin:0; padding: 0; border: none" name="qcfname6_' . str_replace(' ','',$item) . '" value="' .  $item . '" ' . $checked . '> ' .  $item . '<br>';
						}
					break;
					case 'field7':
					$content .= '<p class="input">' . $qcf['label'][$name] . '</p>';
					$arr = explode(",",$qcf['radiolist']);
					foreach ($arr as $item) {
						$checked = '';
						if ($values['qcfname7'] == $item) $checked = 'checked';
						if ($item === reset($arr)) $content .= '<input type="radio" style="margin:0; padding: 0; border: none" name="qcfname7" value="' .  $item . '" checked> ' .  $item . '<br>';
						else $content .=  '<input type="radio" style="margin:0; padding: 0; border: none" name="qcfname7" value="' .  $item . '" ' . $checked . '> ' .  $item . '<br>';
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
				case 'field10':
					$content .= $errors['qcfname10'];
					$content .= '<input type="text" id="qcfdate" ' . $required . ' label="Date" name="qcfname10"  value="' . $values['qcfname10'] . '" onfocus="qcfclear(this, \'' . $values['qcfname10'] . '\')" onblur="qcfrecall(this, \'' . $values['qcfname10'] . '\')">
					<script type="text/javascript">jQuery(document).ready(function() {jQuery(\'#qcfdate\').datepicker({dateFormat : \'dd M yy\'});});</script>'."\r\t";
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
	$content .= '<input type="submit" id="submit" ' .  ' name="submit'.$id.'" value="' . $qcf['send'] . '">'."\r\t".
		'</form>'."\r\t".
		'</div>'."\r\t".
		'</div>'."\r\t";
	echo $content;
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
				case 'field10':
					if ($values['qcfname10'] == $qcf['label'][$item]) $values['qcfname10'] ='';
					$content .= '<p><b>' . $qcf['label'][$item] . ': </b>' . strip_tags($values['qcfname10']) . '</p>';
				}
			}
	$sendcontent = "<html><h2>".$reply['bodyhead']."</h2>".$content;
	$copycontent = "<html><h2>".$reply['copy_message']."</h2>".$content;
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
			$headers = "From: {$values['qcfname1']} <{$values['qcfname2']}>\r\n"
			. "MIME-Version: 1.0\r\n"
			. "Content-Type: text/html; charset=\"utf-8\"\r\n"; 
			$message = $sendcontent;
			}

	if ($reply['mail'] == 'wp-mail') wp_mail($qcf_email, $subject, $message, $headers);
	else mail($qcf_email, $subject, $message, $headers);
	
	if ($reply['sendcopy']) mail($values['qcfname2'], 'Message Copy', $copycontent, $headers);
	
	$qcf_message = get_option('qcf_message');
	if(!is_array($qcf_message)) $qcf_message = array();
	if ($values['qcfname1'] == $qcf['label']['field1']) $values['qcfname1'] ='';
	$sentdate = date('d M Y');
	$qcf_message[] = array('field1' => $values['qcfname1'] , 'field2' => $values['qcfname2'] , 'field4' => $values['qcfname4'] , date => $sentdate,);
	update_option('qcf_message',$qcf_message);
	
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
	if (isset($_POST['submit'.$id])) {
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
		for ($i=1; $i<=10; $i++) { $values['qcfname'.$i] = $qcf['label']['field'.$i]; }
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

function qcf_use_custom_css () {
	$qcf_form = qcf_get_stored_setup();
	$arr = explode(",",$qcf_form['alternative']);
	foreach ($arr as $item) {
		$code ='';$corners='';$input='';$background='';
		$style = qcf_get_stored_style($item);
		if ($item !='') $id = '.'.$item; else $id = '';
		if ($style['font'] == 'plugin') {
			$font = "font-family: ".$style['font-family']."; font-size: ".$style['font-size'].";color: ".$style['font-colour'].";";			
			}
		$input = ".qcf-style".$id." input[type=text], .qcf-style".$id." textarea, .qcf-style".$id." select {border: ".$style['input-border'].";".$font."}\r\n";
		$required = ".qcf-style".$id." input[type=text].required, .qcf-style textarea.required {border: ".$style['input-required'].";}\r\n";
		if ($style['background'] == 'white') $background = ".qcf-style".$id." div {background:#FFF;}\r\n";
		if ($style['background'] == 'color') $background = ".qcf-style".$id." div {background:".$style['backgroundhex'].";}\r\n";
		if ($style['widthtype'] == 'pixel') $width = preg_replace("/[^0-9]/", "", $style['width']) . 'px';
		else $width = '100%';
		if ($style['corners'] == 'round') $corner = '5px'; else $corner = '0';
		$corners = ".qcf-style".$id." input[type=text], .qcf-style".$id." textarea, .qcf-style".$id." select, .qcf-style".$id." #submit {border-radius:".$corner.";}\r\n";
		if ($style['corners'] == 'theme') $corners = '';
		$code .= "<style type=\"text/css\" media=\"screen\">\r\n.qcf-style".$id." {width:".$width.";}\r\n".$corners.$input.$required.$background;
		if ($style['use_custom'] == 'checked') $code .= $style['styles'] . "\r\n";
		$code .= "</style>\r\n";
		echo $code;
		}
	}

function qcf_get_stored_options ($id) {
	$qcf = get_option('qcf_settings'.$id);
	if(!is_array($qcf)) $qcf = array();
	$default = qcf_get_default_options();
	$qcf = array_merge($default, $qcf);
	if (empty($qcf['label']['field10'])) {
	$qcf['label']['field10'] = 'Select date';
	$qcf['sort'] = $qcf['sort'].',field10';
	}
	return $qcf;
	}
function qcf_get_default_options () {
	$qcf = array();
	$qcf['active_buttons'] = array( 'field1'=>'on' , 'field2'=>'on' , 'field3'=>'' , 'field4'=>'on' , 'field5'=>'' , 'field6'=>'' ,  'field7'=>'' , 'field8'=>'' , 'field9'=>'' , 'field10'=>'');	
	$qcf['required'] = array('field1'=>'checked' , 'field2'=>'checked' , 'field3'=>'' , 'field4'=>'' , 'field5'=>'' , 'field6'=>'' , 'field7'=>'' , 'field8'=>'' , 'field9'=>'' , 'field10'=>'');
	$qcf['label'] = array( 'field1'=>'Your Name' , 'field2'=>'Email' , 'field3'=>'Telephone' , 'field4'=>'Message' , 'field5'=>'Select an option' , 'field6'=>'Check at least one box' ,  'field7'=>'Radio' , 'field8'=>'Website' , 'field9'=>'Subject', 'field10'=>'Select date');
	$qcf['sort'] = implode(',',array('field1', 'field2' , 'field3' , 'field4' , 'field5' , 'field6' , 'field7' , 'field10' , 'field8' , 'field9'));
	$qcf['type'] = array( 'field1' => 'text' , 'field2' => 'email' , 'field3' => 'phone' , );
	$qcf['lines'] = 6;
	$qcf['datepicker'] = 'checked';
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
	$style['width'] = 280;
	$style['widthtype'] = 'pixel';
	$style['border'] = 'plain';
	$style['input-border'] = '1px solid #415063';
	$style['input-required'] = '1px solid #00C618';
	$style['bordercolour'] = '#415063';
	$style['inputborderdefault'] = '1px solid #415063';
	$style['inputborderrequired'] = '1px solid #00C618';
	$style['background'] = 'white';
	$style['backgroundhex'] = '#FFF';
	$style['corners'] = 'corner';
	$style['use_custom'] = '';
	$style['styles'] = "#qcf-style {\r\n\r\n}";
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