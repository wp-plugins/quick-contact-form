<?php
$qcf_setup = qcf_get_stored_setup();
$tabs = explode(",",$qcf_setup['alternative']);
$firsttab = reset($tabs);
echo '<div class="wrap">';
echo '<h1>Quick Contact Form Messages</h1>';
if ( isset ($_GET['tab'])) {qcf_messages_admin_tabs($_GET['tab']); $tab = $_GET['tab'];} else {qcf_messages_admin_tabs($firsttab); $tab = $firsttab;}
if ($tab =='archive') qcf_show_archive(); else qcf_show_messages($tab);
echo '</div>';

function qcf_messages_admin_tabs($current = 'default') { 
	$qcf_setup = qcf_get_stored_setup();
	$tabs = explode(",",$qcf_setup['alternative']);
	array_push($tabs, 'default');
	$message = get_option( 'qcf_message' );
	if(is_array($message)) array_push($tabs, 'archive');
	echo '';
	echo '<h2 class="nav-tab-wrapper">';
	foreach( $tabs as $tab ) {
		$class = ( $tab == $current ) ? ' nav-tab-active' : '';
		if ($tab) echo "<a class='nav-tab$class' href='?page=quick-contact-form/quick-contact-messages.php&tab=".$tab."'>$tab</a>";
		}
	echo '</h2>';

	}
function qcf_show_messages($id) {
	if ($id == 'default') $id='';
	$qcf_setup = qcf_get_stored_setup();
	$qcf = qcf_get_stored_options($id);
	qcf_generate_csv();
	if (isset($_POST['qcf_reset_message'.$id])) delete_option('qcf_messages'.$id);
	if( isset( $_POST['Submit'])) {
		$options = array( 'messageqty','messageorder');
		foreach ( $options as $item) $messageoptions[$item] = stripslashes($_POST[$item]);
		update_option( 'qcf_messageoptions', $messageoptions );
		qcf_admin_notice("The message options have been updated.");
		}
	$messageoptions = qcf_get_stored_msg();
	$showthismany = '9999';
	if ($messageoptions['messageqty'] == 'fifty') $showthismany = '50';
	if ($messageoptions['messageqty'] == 'hundred') $showthismany = '100';
	$$messageoptions['messageqty'] = "checked";
	$$messageoptions['messageorder'] = "checked";
	$dashboard = '<form method="post" action="">
	<p><b>Show</b> <input style="margin:0; padding:0; border:none;" type="radio" name="messageqty" value="fifty" ' . $fifty . ' /> 50 
	<input style="margin:0; padding:0; border:none;" type="radio" name="messageqty" value="hundred" ' . $hundred . ' /> 100 
	<input style="margin:0; padding:0; border:none;" type="radio" name="messageqty" value="all" ' . $all . ' /> all messages.&nbsp;&nbsp;
	<b>List</b> <input style="margin:0; padding:0; border:none;" type="radio" name="messageorder" value="oldest" ' . $oldest . ' /> oldest first 
	<input style="margin:0; padding:0; border:none;" type="radio" name="messageorder" value="newest" ' . $newest . ' /> newest first
	&nbsp;&nbsp;<input type="submit" name="Submit" class="button-secondary" value="Update options" />
	</form></p>';
	
	$message = get_option('qcf_messages'.$id);
	if(!is_array($message)) $message = array();
	$title = $id; if ($id == '') $title = 'Default';
	$dashboard .= '<div class="wrap"><div id="qcf-widget">';
	$dashboard .= '<table cellspacing="0"><tr>';
	foreach (explode( ',',$qcf['sort']) as $name) {if ($qcf['active_buttons'][$name] == "on") $dashboard .= '<th>'.$qcf['label'][$name].'</th>';}
	$dashboard .= '<th>Date Sent</th></tr>';
	if ($messageoptions['messageorder'] == 'newest') {
	foreach(array_reverse( $message ) as $value) {
		if ($count < $showthismany ) {
			$content .= '<tr>';
			foreach (explode( ',',$qcf['sort']) as $name) {
				if ($qcf['active_buttons'][$name] == "on") {
					if ($value[$name]) $report = 'messages';
					$content .= '<td>'.strip_tags($value[$name],$qcf['htmltags']).'</td>';
					}
				}
			$content .= '<td>'.$value['field0'].'</td></tr>';
			$count = $count+1;	}
			}
		}
	else {
	foreach($message as $value) {
		if ($count < $showthismany ) {
			$content .= '<tr>';
			foreach (explode( ',',$qcf['sort']) as $name) {
				if ($qcf['active_buttons'][$name] == "on") {
					if ($value[$name]) $report = 'messages';
					$content .= '<td>'.strip_tags($value[$name],$qcf['htmltags']).'</td>';
					}
				}
			$content .= '<td>'.$value['field0'].'</td></tr>';
			$count = $count+1;	}		
			}
		}	
	if ($report) $dashboard .= $content.'</table>';
	else $dashboard .= '</table><p>No messages found</p>';
	$dashboard .='<form method="post" id="download_form" action=""><input type="hidden" name="formname" value = "'.$id.'" /><input type="submit" name="download_csv" class="submit" value="Export to CSV" /> <input type="submit" name="qcf_reset_message'.$id.'" class="submit" style="color: #FFF;" value="Delete Messages" onclick="return window.confirm( \'Are you sure you want to delete the messages for '.$title.'?\' );"/></form></div></div>';		
	echo $dashboard;
	}

function qcf_show_archive() {
	$qcf_setup = qcf_get_stored_setup();
	$qcf = qcf_get_stored_options($id);
	if (isset($_POST['qcf_reset_message'.$id])) delete_option('qcf_message');
	qcf_dashboard_widget();
	$dashboard ='<div id="qcf-widget"><form method="post" id="download_csv" action=""><input type="submit" name="qcf_reset_message'.$id.'" style="color: #FFF;" class="submit" value="Delete Messages" onclick="return window.confirm( \'Are you sure you want to delete the archive messages?\' );"/></form></div>';		
	echo $dashboard;
	}