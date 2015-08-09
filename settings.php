<?php

function qcf_setup ($id) {
    $qcf_setup = qcf_get_stored_setup();
    $qcf_email = qcf_get_stored_email();
    $qcf_apikey = get_option('qcf_akismet');
    
    if( isset( $_POST['Submit']) && check_admin_referer("save_qcf")) {
        $options = array('alternative','current','nostyling','noui');
        foreach ( $options as $item) {
            $qcf_setup[$item] = stripslashes($_POST[$item]);
            $qcf_setup[$item] =filter_var($qcf_setup[$item],FILTER_SANITIZE_STRING);
        }
        if (empty($qcf_setup['current'])) $qcf_setup['current'] = '';
        $arr = explode(",",$qcf_setup['alternative']);
        foreach ($arr as $item) {
            $qcf_email[$item] = stripslashes($_POST['qcf_email'.$item]);
            $qcf_email[$item] = filter_var($qcf_email[$item],FILTER_SANITIZE_STRING);
        }
        update_option( 'qcf_email', $qcf_email);
        update_option( 'qcf_setup', $qcf_setup);
        qcf_admin_notice("The forms have been updated.");
    }
    if( isset( $_POST['newform']) && check_admin_referer("save_qcf")) {
        $qcf_setup['alternative'] = $_POST['alternative'];
        if (!empty($_POST['new_form'])) {
            $qcf_setup['current'] = stripslashes($_POST['new_form']);
            $qcf_setup['current'] = preg_replace("/[^A-Za-z]/",'',$qcf_setup['current']);
            $qcf_setup['current'] = filter_var($qcf_setup['current'],FILTER_SANITIZE_STRING);
            $qcf_setup['alternative'] = $qcf_setup['current'].','.$qcf_setup['alternative'];
            $qcf_email[$qcf_setup['current']] = stripslashes($_POST['new_email']);
        }
        $qcf_email[] = $qcf_email[$qcf_setup['current']];
        update_option( 'qcf_email', $qcf_email);
        update_option( 'qcf_setup', $qcf_setup);
        qcf_admin_notice("The new form has been added.");
        if ($_POST['qcf_clone'] && !empty($_POST['new_form'])) qcf_clone($qcf_setup['current'],$_POST['qcf_clone']);
    }
    
    $arr = explode(",",$qcf_setup['alternative']);
    foreach ($arr as $item) if (isset($_POST['deleteform'.$item]) && $_POST['deleteform'.$item] == $item && $_POST['delete'.$item] && $item != '') {
        $forms = $qcf_setup['alternative'];
        $qcf_setup['alternative'] = str_replace($item.',','',$forms);
        $qcf_setup['current'] = '';
        $qcf_setup['email'] = $_POST['email'];
        update_option('qcf_setup', $qcf_setup);
        qcf_delete_things($item);
        qcf_admin_notice("The form named ".$item." has been deleted.");
        $id = '';
        break;
    }
    
    if( isset( $_POST['Reset']) && check_admin_referer("save_qcf")) {
        qcf_delete_everything();
        qcf_create_css_file ('');
        qcf_admin_notice("Everything has been reset.");
        $qcf_setup = qcf_get_stored_setup();
    }
    
    if( isset( $_POST['Validate']) && check_admin_referer("save_qcf")) {
        $apikey = $_POST['qcf_apikey'];
        $blogurl = get_site_url();
        $akismet = new qcf_akismet($blogurl, $apikey);
		if($akismet->isKeyValid()) {qcf_admin_notice("Valid Akismet API Key. All messages will now be checked against the Akismet database.");update_option('qcf_akismet', $apikey);}
        else qcf_admin_notice("Your Akismet API Key is not Valid");
		}

    if( isset( $_POST['Delete']) && check_admin_referer("save_qcf")) {
        delete_option('qcf_akismet');
        qcf_admin_notice("Akismet validation is no longer active on the Quick Contact Form");
		}
    
    global $current_user;
    get_currentuserinfo();
    $new_email = $current_user->user_email;
    if ($qcf_setup['alternative'] == '' && $qcf_email[''] == '') $qcf_email[''] = $new_email;
    $content ='<div class="qcf-settings"><div class="qcf-options">
    <form method="post" action="">
    <h2 style="color:#B52C00">Existing Forms</h2>
    <table>
    <tr>
    <td><b>Form name&nbsp;&nbsp;</b></td><td><b>Send to this email&nbsp;&nbsp;</b></td><td><b>Shortcode</b></td><td></td>
    </tr>';
    $arr = explode(",",$qcf_setup['alternative']);
sort($arr);
    foreach ($arr as $item) {
        if ($qcf_setup['current'] == $item) $checked = 'checked'; else $checked = '';
        if ($item == '') $formname = 'default'; else $formname = $item;
        $content .='<tr><td><input style="margin:0; padding:0; border:none" type="radio" name="current" value="' .$item . '" ' .$checked . ' /> '.$formname.'</td>';
        $content .='<td><input type="text" style="padding:1px;" label="qcf_email" name="qcf_email'.$item.'" value="' . $qcf_email[$item].'" /></td>';
        if ($item) $shortcode = ' id="'.$item.'"'; else $shortcode='';
        $content .= '<td><code>[qcf'.$shortcode.']</code></td><td>';
        if ($item) $content .= '<input type="hidden" name="deleteform'.$item.'" value="'.$item.'"><input type="submit" name="delete'.$item.'" class="button-secondary" value="delete" onclick="return window.confirm( \'Are you sure you want to delete '.$item.'?\' );" />';
        $content .= '</td></tr>';
    }
    $content .= '</table>
    <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Settings" />&nbsp;
    <input type="submit" name="Reset" class="button-secondary" value="Reset Everything" onclick="return window.confirm( \'This will delete all your forms and settings.\nAre you sure you want to reset everything?\' );"/></p>
    <h2>Create New Form</h2>
    <p>Enter form name (letters only -  no numbers, spaces or punctuation marks)</p>
    <p><input type="text" label="new_Form" name="new_form" value="" /></p>
    <p>Enter your email address. To send to multiple addresses, put a comma betweeen each address.</p>
    <p><input type="text" label="new_email" name="new_email" value="'.$new_email.'" /></p>
    <input type="hidden" name="alternative" value="' . $qcf_setup['alternative'] . '" />
    <p>Copy settings from an exisiting form.</p>
    <select name="qcf_clone"><option>Do not copy settings</option>';
    foreach ($arr as $item) {
        if ($item == '') $item = 'default';
        $content .= '<option value="'.$item.'">'.$item.'</option>';
    }
    $content .= '</select>
    <p><input type="submit" name="newform" class="button-primary" style="color: #FFF;" value="Create New Form" /></p>
    <h2>Use Akismet Validation</h2>
    <p>Enter your API Key to check all messages against the Akismet database. <a href="?page=quick-contact-form/settings.php&tab=error">Change the error message</a>.</p> 
    <p><input type="text" label="akismet" name="qcf_apikey" value="'.$qcf_apikey.'" /></p>
    <p><input type="submit" name="Validate" class="button-primary" style="color: #FFF;" value="Activate Akismet Validation" /> <input type="submit" name="Delete" class="button-secondary" value="Deactivate Aksimet Validation" onclick="return window.confirm( \'This will delete the Akismet Key.\nAre you sure you want to do this?\' );"/></p>
    <p><input type="checkbox" style="margin:0; padding: 0; border: none" name="nostyling"' . $qcf_setup['nostyling'] . ' value="checked" /> Remove all form styles</p>
    <p><input type="checkbox" style="margin:0; padding: 0; border: none" name="noui"' . $qcf_setup['noui'] . ' value="checked" /> Remove all jQuery  styles</p>
<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Settings" /></p>';
    $content .= wp_nonce_field("save_qcf");
    $content .= '</form>
    </div>
    <div class="qcf-options" style="float:right">
    <h2>Adding the contact form to your site</h2>
    <p>To add the basic contact form to your posts or pages use the shortcode: <code>[qcf]</code>.<br />
    <p>If you have a named form the shortcode is <code>[qcf id="form name"]</code>.<br />
    <p>To add the form to your theme files use <code>&lt;?php echo do_shortcode("[qcf]"); ?&gt;</code></p>
    <p>There is also a widget called "Quick Contact Form" you can drag and drop into a sidebar.</p>
    <p>That\'s it. The form is ready to use.</p>
    
    <h2>Options and Settings</h2>
    <p><span style="font-weight:bold"><a href="?page=quick-contact-form/settings.php&tab=settings">Form Settings.</a></span> Change the layout of the form, add or remove fields and the order they appear and edit the labels and captions.</p>
    <p><span style="font-weight:bold"><a href="?page=quick-contact-form/settings.php&tab=attach">Attachments.</a></span> Set how the form handles attachments.</p>
    <p><span style="font-weight:bold"><a href="?page=quick-contact-form/settings.php&tab=styles">Styling.</a></span> Change fonts, colours, borders, images and submit button.</p>
    <p><span style="font-weight:bold"><a href="?page=quick-contact-form/settings.php&tab=send">Send Options.</a></span> Change the thank you message and how the form is sent.</p>
    <p><span style="font-weight:bold"><a href="?page=quick-contact-form/settings.php&tab=autoresponce">Auto Responder.</a></span> Send rich content messages to your visitors.</p>
    <p><span style="font-weight:bold"><a href="?page=quick-contact-form/settings.php&tab=error">Error Messages.</a></span> Change the error message.</p>
    <p><span style="font-weight:bold"><a href="?page=quick-contact-form/quick-contact-messages.php">Messages.</a></span> See all the messages. Or click on the <b>Messages</b> link in the dashboard menu.</p>
    <h2>Support</h2>
    <p>If you have any questions visit the <a href="http://quick-plugins.com/quick-contact-form/">plugin support page</a> or email me at <a href="mailto:mail@quick-plugins.com">mail@quick-plugins.com</a>.</p>';
    $content .= qcfdonate_loop();
    $content .= '</div></div>';
    echo $content;
}

function qcf_form_settings($id) {
    $active_buttons =
        array('field1','field2','field3','field4','field5','field6','field7','field8','field9','field10','field11','field12','field13','field14');
    qcf_change_form_update();
    
    if( isset( $_POST['Submit']) && check_admin_referer("save_qcf")) {
        foreach ( $active_buttons as $item) {
            $qcf['active_buttons'][$item] = (isset( $_POST['qcf_settings_active_'.$item]) and $_POST['qcf_settings_active_'.$item] == 'on' ) ? true : false;
            $qcf['required'][$item] = (isset( $_POST['required_'.$item]) );
            if (!empty ( $_POST['label_'.$item])) {
                $qcf['label'][$item] = stripslashes($_POST['label_'.$item]);$qcf['label'][$item] = str_replace("'","&#8217;",$qcf['label'][$item]);
            }
        }
        $qcf['dropdownlist'] = str_replace(', ' , ',' , $_POST['dropdown_string']);
        $qcf['checklist'] = str_replace(', ' , ',' , $_POST['checklist_string']);
        $qcf['radiolist'] = str_replace(', ' , ',' , $_POST['radio_string']);
        $qcf['required']['field12'] = 'checked';
        $options = array(
            'sort',
            'lines',
            'title',
            'blurb',
            'border',
            'send',
            'datepicker',
            'fieldtype',
            'fieldtypeb',
            'selectora',
            'selectorb',
            'selectorc',
            'min',
            'max',
            'initial',
            'step'
        );
        foreach ( $options as $item) {
            $qcf[$item] = stripslashes($_POST[$item]);
            $qcf[$item] =filter_var($qcf[$item],FILTER_SANITIZE_STRING);
        }
        $qcf['htmltags'] = stripslashes($_POST['htmltags']);
        update_option( 'qcf_settings'.$id, $qcf);
        if ($id) qcf_admin_notice("The form settings for ". $id . " have been updated.");
        else qcf_admin_notice("The default form settings have been updated.");
    }
    
    if( isset( $_POST['Reset']) && check_admin_referer("save_qcf")) {
        delete_option('qcf_settings'.$id);
        if ($id) qcf_admin_notice("The form settings for ".$id. " have been reset.");
        else qcf_admin_notice("The default form settings have been reset.");
    }
    
    $qcf_setup = qcf_get_stored_setup();
    $id = $qcf_setup['current'];
    $qcf = qcf_get_stored_options($id);
    $$qcf['fieldtype'] = 'checked';
    $$qcf['fieldtypeb'] = 'checked';
    $$qcf['selectora'] = 'checked';
    $$qcf['selectorb'] = 'checked';
    $$qcf['selectorc'] = 'checked';
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
    <div style="margin-left:7px;font-weight:bold;">
    <div style="float:left; width:20%;">Field Selection</div>
    <div style="float:left; width:30%;">Label</div>
    <div style="float:left;">Required field</div>
    </div>
    <div style="clear:left"></div>
    <ul id="qcf_sort">';
    foreach (explode( ',',$qcf['sort']) as $name) {
        $checked = ( $qcf['active_buttons'][$name]) ? 'checked' : '';
        $required = ( $qcf['required'][$name]) ? 'checked' : '';
        $datepicker = ($qcf['datepicker']) ? 'checked' : '';
        $lines = $qcf['lines'];
        $options = '';
        switch ( $name ) {
            case 'field1': 
            $type = 'Textbox'; 
            $options = ''; 
            break;
            case 'field2': 
            $type = 'Email'; 
            $options = ' also validates format'; 
            break;
            case 'field3': 
            $type = 'Telephone'; 
            $options = 'also checks number format';
            break;	
            case 'field4':
            $type = 'Textarea';
            $options = 'Number of rows: <input type="text" style="border:1px solid #415063; width:3em;" name="lines" . value ="' . $qcf['lines'] . '" /><br>
            Allowed Tags:<br> <input type="text" style="border:1px solid #415063; name="htmltags" . value ="' . $qcf['htmltags'] . '" />';
            break;
            case 'field5':
            $type = 'Selector';
            $options = '<input style="margin:0; padding:0; border:none" type="radio" name="selectora" value="dropdowna" ' .$dropdowna . ' />&nbsp;Dropdown
            <input style="margin:0; padding:0; border:none" type="radio" name="selectora" value="checkboxa" ' .$checkboxa . ' />&nbsp;Checkbox
            <input style="margin:0; padding:0; border:none" type="radio" name="selectora" value="radioa" ' .$radioa . ' />&nbsp;Radio<br>
            <span class="description">Options (separate with a comma):</span><br><textarea name="dropdown_string" label="Dropdown" rows="2">' . $qcf['dropdownlist'] . '</textarea>'; 
            break;
            case 'field6': 
            $type = 'Selector'; 
            $options = '<input style="margin:0; padding:0; border:none" type="radio" name="selectorb" value="dropdownb" ' .$dropdownb . ' />&nbsp;Dropdown
            <input style="margin:0; padding:0; border:none" type="radio" name="selectorb" value="checkboxb" ' .$checkboxb . ' />&nbsp;Checkbox
            <input style="margin:0; padding:0; border:none" type="radio" name="selectorb" value="radiob" ' .$radiob . ' />&nbsp;Radio<br>
            <span class="description">Options (separate with a comma):</span><br><textarea  name="checklist_string" label="Checklist" rows="2">' . $qcf['checklist'] . '</textarea>'; 
            break;
            case 'field7':
            $type = 'Selector'; 
            $options = '<input style="margin:0; padding:0; border:none" type="radio" name="selectorc" value="dropdownc" ' .$dropdownc . ' />&nbsp;Dropdown
            <input style="margin:0; padding:0; border:none" type="radio" name="selectorc" value="checkboxc" ' .$checkboxc . ' />&nbsp;Checkbox
            <input style="margin:0; padding:0; border:none" type="radio" name="selectorc" value="radioc" ' .$radioc . ' />&nbsp;Radio<br>
            <span class="description">Options (separate with a comma):</span><br><textarea  name="radio_string" label="Radio" rows="2">' . $qcf['radiolist'] . '</textarea>'; 
            break;
            case 'field8': $type = 'Textbox'; $options = ''; break;
            case 'field9': $type = 'Textbox'; $options = ''; break;
            case 'field10': $type = 'Date'; $options = ''; break;
            case 'field11': 
            $type = 'Multibox'; 
            $options = '<input style="margin:0; padding:0; border:none" type="radio" name="fieldtype" value="ttext" ' .$ttext . ' />&nbsp;Text
<input style="margin:0; padding:0; border:none" type="radio" name="fieldtype" value="tmail" ' .$tmail . ' />&nbsp;Email
<input style="margin:0; padding:0; border:none" type="radio" name="fieldtype" value="ttele" ' .$ttele . ' />&nbsp;Telephone
<input style="margin:0; padding:0; border:none" type="radio" name="fieldtype" value="tdate" ' .$tdate . ' />&nbsp;Date';
            break;
            case 'field12': 
            $type = 'Maths Captcha'; 
            $options = '<span class="description">Add a maths checker to the form to (hopefully) block most of the spambots.</span>'; 
            break;
            case 'field13': 
            $type = 'Multibox'; 
            $options = '<input style="margin:0; padding:0; border:none" type="radio" name="fieldtypeb" value="btext" ' .$btext . ' />&nbsp;Text
<input style="margin:0; padding:0; border:none" type="radio" name="fieldtypeb" value="bmail" ' .$bmail . ' />&nbsp;Email
<input style="margin:0; padding:0; border:none" type="radio" name="fieldtypeb" value="btele" ' .$btele . ' />&nbsp;Telephone
<input style="margin:0; padding:0; border:none" type="radio" name="fieldtypeb" value="bdate" ' .$bdate . ' />&nbsp;Date';
            break;
            case 'field14';
            $type = 'Range slider';
            $options = '<input type="text" style="border:1px solid #415063; width:3em;" name="min" . value ="' . $qcf['min'] . '" />&nbsp;Minimum value<br>
            <input type="text" style="border:1px solid #415063; width:3em;" name="max" . value ="' . $qcf['max'] . '" />&nbsp;Maximum value<br>
            <input type="text" style="border:1px solid #415063; width:3em;" name="initial" . value ="' . $qcf['initial'] . '" />&nbsp;Initial value<br>
            <input type="text" style="border:1px solid #415063; width:3em;" name="step" . value ="' . $qcf['step'] . '" />&nbspStep';
            break;
        }
        $li_class = ( $checked) ? 'button_active' : 'button_inactive';
        $content .= '<li class="'.$li_class.'" id="' . $name . '">
        <div style="float:left; width:20%;">
        <input type="checkbox" class="button_activate" style="border: none;" name="qcf_settings_active_' . $name . '" ' . $checked . ' />' . $type . '</div>
        <div style="float:left; width:30%;">
        <input type="text" style="border: border:1px solid #415063; padding: 1px; margin:0;" name="label_' . $name . '" value="' . $qcf['label'][$name] . '"/></div>
        <div style="float:left;width:5%">';
        $exclude = array("field12");
        if(!in_array($name, $exclude)) $content .='<input type="checkbox" class="button_activate" style="border: none; padding: 0; margin:0 0 0 5px;" name="required_'.$name.'" '.$required.' /> ';
        else $content .='&nbsp;';
        $content .= '</div><div style="float:left;width:45%">'.$options . '</div><div style="clear:left"></div></li>';
    }
    $content .= '</ul>
    <input type="hidden" id="qcf_settings_sort" name="sort" value="'.$qcf['sort'].'" />
    <h2>Submit button caption</h2>
    <p><input type="text" text-align:center" name="send" value="' . $qcf['send'] . '" /></p>
    <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" />  <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset these settings?\' );"/></p>';
    $content .= wp_nonce_field("save_qcf");
    $content .= '</form>
    </div>
    <div class="qcf-options" style="float:right">
    <h2 style="color:#B52C00">Form Preview</h2>
    <p>Note: The preview form uses the wordpress admin styles. Your form will use the theme styles so won\'t look exactly like the one below.</p>';
    $content .= qcf_loop($id);
    $content .= '<p>Have you set up the <a href="?page=quick-contact-form/settings.php&tab=reply">reply options</a>?</p>
    <p>You can also customise the <a href="?page=quick-contact-form/settings.php&tab=error">error messages</a>.</p>
    </div>
    </div>';
    echo $content;
}

function qcf_attach ($id) {
    qcf_change_form_update();
    if( isset( $_POST['Submit']) && check_admin_referer("save_qcf")) {
        $options = array(
            'qcf_attach',
            'qcf_number',
            'qcf_attach_label',
            'qcf_attach_size',
            'qcf_attach_type',
            'qcf_attach_width',
            'qcf_attach_error',
            'qcf_attach_error_size',
            'qcf_attach_error_type',
            'qcf_attach_link'
        );
        foreach ( $options as $item) {
            $attach[$item] = stripslashes($_POST[$item]);
            $attach[$item] =filter_var($attach[$item],FILTER_SANITIZE_STRING);
        }
        update_option( 'qcf_attach'.$id, $attach);
        if ($id) qcf_admin_notice("The attachment settings for ".$id. " have been updated.");
        else qcf_admin_notice("The default form settings have been reset.");
    }
    
    if( isset( $_POST['Reset']) && check_admin_referer("save_qcf")) {
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
    <tr>
    <td colspan="2"><h2>Attachment Settings</h2></td>
    </tr>
    <tr>
    <td></td>
    <td><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_attach"' . $attach['qcf_attach'] . ' value="checked" /> User can attach files</td>
    </tr>
    <tr>
    <td>Max Number of attachments</td>
    <td><input type="text" style="width:3em" name="qcf_number" value="' . $attach['qcf_number'] . '" /></td>
    </tr>
    <tr>
    <td>Field Label</td>
    <td><input type="text" name="qcf_attach_label" value="' . $attach['qcf_attach_label'] . '" /></td>
    </tr>
    <tr>
    <td>Maximum File size</td>
    <td><input type="text" name="qcf_attach_size" value="' . $attach['qcf_attach_size'] . '" /></td>
    </tr>
    <tr>
    <td>Allowable file types</td>
    <td><input type="text" name="qcf_attach_type" value="' . $attach['qcf_attach_type'] . '" /></td>
    </tr>
    <tr>
    <td>Field size</td>
    <td><p>This is a trial and error number. You can\'t use a \'width\' style as the size is a number of characters. Test using the live form not the preview. Note also that many browsers will ignore your settings.</p>
    <p><em>Example: A form width of 280px with a plain border has field width of about 15. With no border it\'s about 18.</em></p>
    <p><input type="text" style="width:5em;" name="qcf_attach_width" value="' . $attach['qcf_attach_width'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>Error messages</h2></td>
    </tr>
    <tr>
    <td>General Errors:</td>
    <td><input type="text" name="qcf_attach_error" value="' . $attach['qcf_attach_error'] . '" /></td>
    </tr>
    <tr>
    <td>If the file is too big:</td>
    <td><input type="text" name="qcf_attach_error_size" value="' . $attach['qcf_attach_error_size'] . '" /></td>
    </tr>
    <tr>
    <td>If the filetype is incorrect:</td>
    <td><input type="text" name="qcf_attach_error_type" value="' . $attach['qcf_attach_error_type'] . '" /></td>
    </tr>
    </table>
    <p>All attachments are uploaded to a folder called \'qcf\' in your media library. The checkbox below add links to the documents in the email instead of attaching them. Useful if you are allowing multiple or very large files.</p>
    <p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="qcf_attach_link"' . $attach['qcf_attach_link'] . ' value="checked" /> Add attachment links to the email.</p>
    <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the attachment settings for '.$id.'?\' );"/></p>';
    $content .= wp_nonce_field("save_qcf");
    $content .= '</form>
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
    if( isset( $_POST['Submit']) && check_admin_referer("save_qcf")) {
        $options = array(
            'font',
            'font-family',
            'font-size',
            'font-colour',
            'text-font-family',
            'text-font-size',
            'text-font-colour',
            'input-border',
            'input-required',
            'inputbackground',
            'inputfocus',
            'border',
            'width',
            'widthtype',
            'submitwidth',
            'submitwidthset',
            'submitposition',
            'background',
            'backgroundhex',
            'backgroundimage',
            'corners',
            'use_custom',
            'styles',
            'usetheme',
            'submit-colour',
            'submit-background',
            'submit-border',
            'submit-button',
            'form-border',
            'header',
'header-type',
            'header-size',
            'header-colour',
            'error-font-colour',
            'error-border',
            'slider-background',
            'slider-revealed',
            'handle-background',
            'handle-border',
            'output-size',
            'output-colour',
            'nostyling'
        );
        foreach ( $options as $item) {
            $style[$item] = stripslashes($_POST[$item]);
            $style[$item] =filter_var($style[$item],FILTER_SANITIZE_STRING);
        }
        if ($style['widthtype'] == 'pixel') {
            $formwidth = preg_split('#(?<=\d)(?=[a-z%])#i', $style['width']);
            if (!$formwidth[1]) $formwidth[1] = 'px';
            $style['width'] = $formwidth[0].$formwidth[1];
        }
        update_option( 'qcf_style'.$id, $style);
        qcf_create_css_file ('update');
        qcf_admin_notice("The form styles have been updated.");
    }
    if( isset( $_POST['Reset']) && check_admin_referer("save_qcf")) {
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
$$style['header-type'] = 'checked';
    $content ='<div class="qcf-settings"><div class="qcf-options">';
    if ($id) $content .='<h2 style="color:#B52C00">Styles for ' . $id . '</h2>';
    else $content .='<h2 style="color:#B52C00">Default form styles</h2>';
    $content .= qcf_change_form($qcf_setup);
    $content .='<form method="post" action="">
    <span class="description"><b>NOTE:</b>Leave fields blank if you don\'t want to use them</span>
    <table>
    <tr><td colspan="2"><h2>Form Width</h2></td></tr>
    <tr>
    <td></td>
    <td><input style="margin:0; padding:0; border:none;" type="radio" name="widthtype" value="percent" ' . $percent . ' /> 100% (fill the available space)<br />
    <input style="margin:0; padding:0; border:none;" type="radio" name="widthtype" value="pixel" ' . $pixel . ' /> Fixed : <input type="text" style="width:4em" label="width" name="width" value="' . $style['width'] . '" /> use px, em or %. Default is px.</td>
    </tr>
    <tr>
    <td colspan="2"><h2>Form Border</h2>
    <p>Note: The rounded corners and shadows only work on CSS3 supported browsers and even then not in IE8. Don\'t blame me, blame Microsoft.</p></td>
    </tr>
    <tr>
    <td>Type:</td>
    <td><input style="margin:0; padding:0; border:none;" type="radio" name="border" value="none" ' . $none . ' /> No border<br />
    <input style="margin:0; padding:0; border:none;" type="radio" name="border" value="plain" ' . $plain . ' /> Plain Border<br />
    <input style="margin:0; padding:0; border:none;" type="radio" name="border" value="rounded" ' . $rounded . ' /> Round Corners (Not IE8)<br />
    <input style="margin:0; padding:0; border:none;" type="radio" name="border" value="shadow" ' . $shadow . ' /> Shadowed Border(Not IE8)<br />
    <input style="margin:0; padding:0; border:none;" type="radio" name="border" value="roundshadow" ' . $roundshadow . ' /> Rounded Shadowed Border (Not IE8)</td>
    </tr>
    <tr>
    <td>Style:</td>
    <td><input type="text" label="form-border" name="form-border" value="' . $style['form-border'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>Background</h2></td>
    </tr>
    <tr>
    <td>Colour:</td>
    <td><input style="margin:0; padding:0; border:none;" type="radio" name="background" value="white" ' . $white . ' /> White<br />
    <input style="margin:0; padding:0; border:none;" type="radio" name="background" value="theme" ' . $theme . ' /> Use theme colours<br />
    <input style="margin:0; padding:0; border:none;" type="radio" name="background" value="color" ' . $color . ' />	Set your own: 
    <input type="text" class="qcf-color" label="background" name="backgroundhex" value="' . $style['backgroundhex'] . '" /></td>
    </tr>
    <tr>
    <td>Background<br>Image:</td>
    <td><input id="qcf_background" type="text" name="backgroundimage" value="' . $style['backgroundimage'] . '" />
    <input id="qcf_upload_background" class="button" name="bg" type="button" value="Upload Image" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>Font Styles</h2></td>
    </tr>
    <tr>
    <td></td>
    <td><input style="margin:0; padding:0; border:none" type="radio" name="font" value="theme" ' . $theme . ' /> Use theme font styles<br />
    <input style="margin:0; padding:0; border:none" type="radio" name="font" value="plugin" ' . $plugin . ' /> Use Plugin font styles (enter font family and size below)
    </td>
    </tr>
    <tr>
    <td colspan="2"><h2>Form Header</h2></td>
    </tr>
<tr>
    <td style="vertical-align:top;">'.__('Header Type', 'quick-event-manager').'</td>
    <td><input style="margin:0; padding:0; border:none;" type="radio" name="header-type" value="h2" ' . $h2 . ' /> H2 <input style="margin:0; padding:0; border:none;" type="radio" name="header-type" value="h3" ' . $h3 . ' /> H3 <input style="margin:0; padding:0; border:none;" type="radio" name="header-type" value="h4" ' . $h4 . ' /> H4</td>
    </tr>    
<tr>
<td>Header Size: </td>
<td><input type="text" style="width:6em" label="header-size" name="header-size" value="' . $style['header-size'] . '" /></td>
    </tr>
    <tr>
    <td>Header Colour: </td>
<td><input type="text" class="qcf-color" label="header-colour" name="header-colour" value="' . $style['header-colour'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>Input Fields</h2></td>
    </tr>
    <tr>
    <td>Font Family: </td>
<td><input type="text" label="font-family" name="font-family" value="' . $style['font-family'] . '" /></td></tr>
    <tr>
    <td>Font Size: </td><td><input type="text" style="width:6em" label="font-size" name="font-size" value="' . $style['font-size'] . '" /></td>
    </tr>
    <tr>
    <td>Font Colour: </td>
    <td><input type="text" class="qcf-color" label="font-colour" name="font-colour" value="' . $style['font-colour'] . '" /></td>
    </tr>
    <tr>
    <td>Normal Border: </td>
    <td><input type="text" label="input-border" name="input-border" value="' . $style['input-border'] . '" /></td>
    </tr>
    <tr>
    <td>Required Fields: </td>
    <td><input type="text" label="input-required" name="input-required" value="' . $style['input-required'] . '" /></td>
    </tr>
    <tr>
    <td>Background: </td>
    <td><input type="text" class="qcf-color" label="inputbackground" name="inputbackground" value="' . $style['inputbackground'] . '" /></td>
    </tr>
    <tr>
    <td>Focus: </td>
<td><input type="text" class="qcf-color" label="inputfocus" name="inputfocus" value="' . $style['inputfocus'] . '" /></td>
    </tr>
    <tr><td>Corners: </td>
<td><input style="margin:0; padding:0; border:none;" type="radio" name="corners" value="corner" ' . $corner . ' /> Use theme settings <input style="margin:0; padding:0; border:none;" type="radio" name="corners" value="square" ' . $square . ' /> Square corners 	<input style="margin:0; padding:0; border:none;" type="radio" name="corners" value="round" ' . $round . ' /> 5px rounded corners</td>
    </tr>
    <tr>
    <td colspan="2"><h2>Other text content</h2></td>
    </tr>
    <tr>
    <td>Font Family: </td>
<td><input type="text" label="text-font-family" name="text-font-family" value="' . $style['text-font-family'] . '" /></td>
    </tr>
    <tr>
    <td>Font Size: </td>
<td><input type="text" style="width:6em" label="text-font-size" name="text-font-size" value="' . $style['text-font-size'] . '" /></td>
    </tr>
    <tr>
    <td>Font Colour: </td>
<td><input type="text" class="qcf-color" label="text-font-colour" name="text-font-colour" value="' . $style['text-font-colour'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>Error Messages</h2></td>
    </tr>
    <tr><td>Font Colour: </td>
<td><input type="text" class="qcf-color" label="error-font-colour" name="error-font-colour" value="' . $style['error-font-colour'] . '" /></td></tr>
    <tr>
    <td>Error Border: </td><td><input type="text" label="error-border" name="error-border" value="' . $style['error-border'] . '" /></td></tr>
    <tr>
    <td td colspan="2"><h2>Submit Button</h2></td></tr>
    <tr>
    <td>Font Colour: </td><td><input type="text" class="qcf-color" label="submit-colour" name="submit-colour" value="' . $style['submit-colour'] . '" /></td></tr>
    <tr>
    <td>Background: </td><td><input type="text" class="qcf-color" label="submit-background" name="submit-background" value="' . $style['submit-background'] . '" /></td></tr>
    <tr>
    <td>Border: </td><td><input type="text" label="submit-border" name="submit-border" value="' . $style['submit-border'] . '" /></td></tr>
    <tr>
    <td>Sized: </td><td><input style="margin:0; padding:0; border:none;" type="radio" name="submitwidth" value="submitpercent" ' . $submitpercent . ' /> Same width as the form<br />
    <input style="margin:0; padding:0; border:none;" type="radio" name="submitwidth" value="submitrandom" ' . $submitrandom . ' /> Same width as the button text<br />
    <input style="margin:0; padding:0; border:none;" type="radio" name="submitwidth" value="submitpixel" ' . $submitpixel . ' /> Set your own width: <input type="text" style="width:5em" label="submitwidthset" name="submitwidthset" value="' . $style['submitwidthset'] . '" /> (px, % or em)</td>
    </tr>
    <tr>
    <td>Position: </td><td><input style="margin:0; padding:0; border:none;" type="radio" name="submitposition" value="submitleft" ' . $submitleft . ' /> Left <input style="margin:0; padding:0; border:none;" type="radio" name="submitposition" value="submitright" ' . $submitright . ' /> Right</td></tr>
    <tr>
    <td>Button Image: </td><td>
    <input id="qcf_submit_button" type="text" name="submit-button" value="' . $style['submit-button'] . '" />
    <input id="qcf_upload_submit_button" class="button-secondary" name="sb" type="button" value="Upload Image" /></td></tr>
    <tr>
    <td colspan="2"><h2>Slider</h2></td>
    </tr>
    <tr>
    <td>Normal Background</td>
    <td><input type="text" class="qcf-color" label="input-border" name="slider-background" value="' . $style['slider-background'] . '" /></td>
    </tr>
    <tr>
    <td>Revealed Background</td>
    <td><input type="text" class="qcf-color" label="input-border" name="slider-revealed" value="' . $style['slider-revealed'] . '" /></td>
    </tr>
    <tr>
    <td>Handle Background</td>
    <td><input type="text" class="qcf-color" label="input-border" name="handle-background" value="' . $style['handle-background'] . '" /></td>
    </tr>
    <tr>
    <td>Handle Border</td>
    <td><input type="text" class="qcf-color" label="input-border" name="handle-border" value="' . $style['handle-border'] . '" /></td>
    </tr>
    <tr>
    <td>Output Size</td>
    <td><input type="text" style="width:3em" label="input-border" name="output-size" value="' . $style['output-size'] . '" /></td>
    </tr>
    <tr>
    <td>Output Colour</td>
    <td><input type="text" class="qcf-color" label="input-border" name="output-colour" value="' . $style['output-colour'] . '" /></td>
    </tr>
    </table>
    <h2>Custom CSS</h2>
    <p><input type="checkbox" style="margin:0; padding: 0; border: none" name="use_custom"' . $style['use_custom'] . ' value="checked" /> Use Custom CSS</p>
    <p><textarea style="height: 100px" name="styles">' . $style['styles'] . '</textarea></p>
    <p>To see all the styling use the <a href="'.get_admin_url().'plugin-editor.php?file=quick-contact-form/quick-contact-form.css">CSS editor</a>.</p>
    <p>The main style wrapper is the <code>.qcf-style</code> id.</p>
    <p>The form borders are: #none, #plain, #rounded, #shadow, #roundshadow.</p>
    <p>Errors and required fields have the classes .error and .required</p>
    <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the style settings for '.$id.'?\' );"/></p>';
    $content .= wp_nonce_field("save_qcf");
    $content .= '</form>
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
    if( isset( $_POST['Submit']) && check_admin_referer("save_qcf")) {
        $options = array(
            'replytitle',
            'replyblurb',
            'replymessage',
            'replycopy',
            'replysubject',
            'fromemail',
            'messages',
            'tracker' , 
            'url',
            'page',
            'subject',
            'subjectoption',
            'qcf_redirect',
            'qcf_reload',
            'qcf_reload_time',
            'qcf_redirect_url',
            'qcfmail',
            'qcf_bcc',
            'sendcopy',
            'copy_message',
            'bodyhead'
        );
        foreach ( $options as $item) {
            $reply[$item] = stripslashes($_POST[$item]);
            $reply[$item] =filter_var($reply[$item],FILTER_SANITIZE_STRING);
        }
        update_option('qcf_reply'.$id, $reply);
        if ($id) qcf_admin_notice("The send settings for " . $id . " have been updated.");
        else qcf_admin_notice("The default form send settings have been updated.");
    }
    if( isset( $_POST['Reset']) && check_admin_referer("save_qcf")) {
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
    <tr>
    <td colspan="2"><h2>Send Options</h2></td>
    </tr>
    <tr>
    <td>Send Function</td>
    <td><input style="margin:0; padding:0; border:none" type="radio" name="qcfmail" value="wpemail" ' . $wpemail . '> WP-mail (should work for most email addresses)<br />
    <input style="margin:0; padding:0; border:none" type="radio" name="qcfmail" value="smtp" ' . $smtp . '> SMTP (Only use if you have <a href="?page=quick-contact-form/settings.php&tab=smtp">set up SMTP</a>)</td></tr>
    <tr>
    <td>BCC</td>
    <td><input style="margin:0; padding:0; border:none" type="checkbox" name="qcf_bcc" ' . $reply['qcf_bcc'] . ' value="checked"> Hide email address for multiple recipients.</td>
    </tr>
    <tr>
    <td>From Address</td>
    <td>Some hosts get very picky about the senders email address. Enter a safe email if your hosts requires it:<br>
    <input type="text" name="fromemail" value="' . $reply['fromemail'] . '"/><br>
    <span class="description">If you use this feature the senders email address will automatically appear as a \'Reply To\' line in the email header.</span></td>
    </tr>
    <tr>
    <td>Email subject</td>
    <td>The message subject has two parts: the bit in the text box plus the option below.<br>
    <input style="width:100%" type="text" name="subject" value="' . $reply['subject'] . '"/><br>
    <input style="margin:0; padding:0; border:none" type="radio" name="subjectoption" value="sendername" ' . $sendername . '> sender\'s name (the contents of the first field)<br />
    <input style="margin:0; padding:0; border:none" type="radio" name="subjectoption" value="sendersubj" ' . $sendersubj . '> Contents of the subject field (if used)<br />
    <input style="margin:0; padding:0; border:none" type="radio" name="subjectoption" value="senderpage" ' . $senderpage . '> page title (only works if sent from a post or a page)<br />
    <input style="margin:0; padding:0; border:none" type="radio" name="subjectoption" value="sendernone" ' . $sendernone . '> blank
    </td>
    </tr>
    <tr>
    <td>Email body header</td>
    <td>This is the introduction to the email message you receive.<br>
    <input type="text" name="bodyhead" value="' . $reply['bodyhead'] . '"/></td>
    </tr>
    <tr>
    <td>Tracking</td>
    <td>Adds the tracking information to the message you receive.<br>
    <input style="margin:0; padding:0; border: none"type="checkbox" name="page" ' . $reply['page'] . ' value="checked"> Show page title<br />
    <input style="margin:0; padding:0; border:none" type="checkbox" name="tracker" ' . $reply['tracker'] . ' value="checked"> Show IP address<br />
    <input style="margin:0; padding:0; border:none" type="checkbox" name="url" ' . $reply['url'] . ' value="checked"> Show URL
    </td>
    </tr>
    <tr>
    <td colspan="2"><h2>Redirection</h2></td>
    </tr>
    <tr>
    <td></td>
    <td><input style="margin:0; padding:0; border:none" type="checkbox" name="qcf_redirect" ' . $reply['qcf_redirect'] . ' value="checked"> Send your visitor to new page instead of displaying the thank-you message.</td>
    </tr>
    <tr>
    <td>URL:</td>
    <td><input type="text" name="qcf_redirect_url" value="' . $reply['qcf_redirect_url'] . '"/></td>
    </tr>
    <tr>
    <td colspan="2"><h2>On Screen Thank you message</h2></td>
    </tr>
    <tr>
    <td>Thank you header</td>
    <td><input type="text" name="replytitle" value="' . $reply['replytitle'] . '"/></td>
    </tr>
    <tr><td>Thank you message</td>
    <td><textarea height: 100px" name="replyblurb">' . $reply['replyblurb'] . '</textarea></td>
    </tr>
    <tr>
    <td></td>
    <td><input style="margin:0; padding:0; border:none" type="checkbox" name="messages" ' . $reply['messages'] . ' value="checked"> Show the sender the content of their message.</td>
    </tr>
    <tr>
    <td colspan="2"><h2>Reply Message</h2></td>
    </tr>
    <tr>
    <td  colspan="2">You can reply to the sender using the <a href="?page=quick-contact-form/settings.php&tab=autoresponce">Auto Responder</a>.</td>
    </tr>
    <tr>
    <td colspan="2"><h2>Reload Page</h2></td>
    </tr>
    <tr>
    <td></td>
    <td><input style="margin:0; padding:0; border:none" type="checkbox" name="qcf_reload" ' . $reply['qcf_reload'] . ' value="checked"> Refresh the page <input style="width:2em" type="text" name="qcf_reload_time" value="' . $reply['qcf_reload_time'] . '" /> seconds after the thank-you message.</td>
    </tr>
    </table>
    <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the reply settings for '.$id.'?\' );"/></p>';
    $content .= wp_nonce_field("save_qcf");
    $content .= '</form>
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
    if( isset( $_POST['Submit']) && check_admin_referer("save_qcf")) {
        for ($i=1; $i<=13; $i++) $error['field'.$i] = stripslashes($_POST['error'.$i]);
        $options = array( 'errortitle','errorblurb','email','telephone','mathsmissing','mathsanswer','emailcheck','phonecheck','spam');
        foreach ( $options as $item) {
            $error[$item] = stripslashes($_POST[$item]);
            $error[$item] =filter_var($error[$item],FILTER_SANITIZE_STRING);
        }
        update_option( 'qcf_error'.$id, $error );
        if ($id) qcf_admin_notice("The reply settings for " . $id . " have been updated.");
        else qcf_admin_notice("The default form error settings have been updated.");
    }
    if( isset( $_POST['Reset']) && check_admin_referer("save_qcf")) {
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
	<tr>
    <td colspan="2"><h2>Error Reporting</h2></td>
    </tr>
    <tr><td>Error header</td><td><input type="text" name="errortitle" value="' . $error['errortitle'] . '" /></td><td>
    <tr><td>Error Blurb</td><td><input type="text" name="errorblurb" value="' . $error['errorblurb'] . '" /></td></tr>
    <tr><td colspan="2"><h2>Error Messages</h2></td></tr>
    <tr><td>If <em>' . $qcf['label']['field1'] . '</em> is missing:</td><td>
    <input type="text" name="error1" value="' .  $error['field1'] . '" /></td></tr>
    <tr><td>If <em>' . $qcf['label']['field2'] . '</em> is missing:</td>
    <td><input type="text" name="error2" value="' .  $error['field2'] . '" /></td></tr>
    <tr><td>Invalid email address:</td><td>
    <input type="text" name="email" value="' .  $error['email'] . '" /></td></tr>
    <tr><td></td>
    <td><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="emailcheck"' . $error['emailcheck'] . ' value="checked" /> Check for invalid email even if field is not required</td></tr>
    <tr><td>If <em>' . $qcf['label']['field3'] . '</em> is missing:</td>
    <td><input type="text" name="error3" value="' .  $error['field3'] . '" /></td></tr>
    <tr><td>Invalid telephone number:</td>
    <td><input type="text" name="telephone" value="' .  $error['telephone'] . '" /></td></tr>
    <tr><td></td>
    <td><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="phonecheck"' . $error['phonecheck'] . ' value="checked" /> Check for invalid phone number even if field is not required</td></tr>
    <tr><td>If <em>' . $qcf['label']['field4'] . '</em> is missing:</td>
    <td><input type="text" name="error4" value="' .  $error['field4'] . '" /></td></tr>
    <tr><td>Drop dopwn list:</td>
    <td><input type="text" name="error5" value="' .  $error['field5'] . '" /></td></tr>
    <tr><td>Checkboxes:</td>
    <td><input type="text" name="error6" value="' .  $error['field6'] . '" /></td></tr>
    <tr><td>If <em>' .  $qcf['label']['field8'] . '</em> is missing:</td>
    <td><input type="text" name="error8" value="' .  $error['field8'] . '" /></td></tr>
    <tr><td>If <em>' .  $qcf['label']['field9'] . '</em> is missing:</td>
    <td><input type="text" name="error9" value="' .  $error['field9'] . '" /></td></tr>
    <tr><td>If <em>' .  $qcf['label']['field10'] . '</em> is required:</td>
    <td><input type="text" name="error10" value="' .  $error['field10'] . '" /></td></tr>
    <tr><td>If <em>' .  $qcf['label']['field11'] . '</em> is required:</td>
    <td><input type="text" name="error11" value="' .  $error['field11'] . '" /></td></tr>
    <tr><td>If <em>' .  $qcf['label']['field13'] . '</em> is required:</td>
    <td><input type="text" name="error13" value="' .  $error['field13'] . '" /></td></tr>
    <tr><td>Spam Captcha missing answer:</td><td>
    <p><input type="text" name="mathsmissing" value="' .  $error['mathsmissing'] . '" /></td></tr>
    <tr><td>Spam Captcha wrong answer:</td><td><input type="text" name="mathsanswer" value="' .  $error['mathsanswer'] . '" /></td></tr>
    <tr><td colspan="2"><h2>Akismet Spam Message</h2></td></tr>
    <tr>
    <td>If spam detected:</td>
    <td><input type="text" name="spam" value="' .  $error['spam'] . '" /></td>
    </tr>
    </table>
    <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the error settings for '.$id.'?\' );"/></p>';
    $content .= wp_nonce_field("save_qcf");
    $content .= '</form>
    </div>
    <div class="qcf-options" style="float:right"> 
    <h2 style="color:#B52C00">Error Checker</h2>
    <p>Send a blank form to test your error messages.</p>';
	$content .= qcf_loop($id);
	$content .= '</div></div>';
	echo $content;
}

function qcf_autoresponce_page($id) {
    qcf_change_form_update();
    if( isset( $_POST['Submit']) && check_admin_referer("save_qcf")) {
        $options = array('enable','subject','message','fromname','fromemail','sendcopy');
        foreach ( $options as $item) {
            $auto[$item] = stripslashes($_POST[$item]);
        }
        update_option( 'qcf_autoresponder'.$id, $auto );
        if ($id) qcf_admin_notice("The autoresponder settings for " . $id . " have been updated.");
        else qcf_admin_notice("The default form autoresponder settings have been updated.");
    }
    if( isset( $_POST['Reset']) && check_admin_referer("save_qcf")) {
        delete_option('qcf_autoresponder'.$id);
        qcf_admin_notice("The autoresponder settings for the form called ".$id. " have been reset.");
    }
	
    $qcf_setup = qcf_get_stored_setup();
	$id=$qcf_setup['current'];
	$qcf = qcf_get_stored_options($id);
	$auto = qcf_get_stored_autoresponder($id);
    $message = $auto['message'];
	$content ='<div class="qcf-settings"><div class="qcf-options" style="width:90%;">';
	if ($id) $content .='<h2 style="color:#B52C00">Autoresponse settings for ' . $id . '</h2>';
	else $content .='<h2 style="color:#B52C00">Default form autoresponse settings</h2>';
	$content .= qcf_change_form($qcf_setup);
	$content .='<p>The auto responder allows you to format a proper HTML message with media, links and so on.</p>
    <form method="post" action="">
	<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="enable"' . $auto['enable'] . ' value="checked" /> Enable Auto Responder.</p>
<p>From Name (<span class="description">Defaults to your <a href="'. get_admin_url().'options-general.php">Site Title</a> if left blank.</span>):<br>
    <input type="text" style="width:50%" name="fromname" value="' . $auto['fromname'] . '" /></p>
    <p>From Email (<span class="description">Defaults to the <a href="?page=quick-contact-form/settings.php&tab=setup">Setup Email</a> if left blank.</span>):<br>
    <input type="text" style="width:50%" name="fromemail" value="' . $auto['fromemail'] . '" /></p>    
<p>Subject:<br>
<input style="width:100%" type="text" name="subject" value="' . $auto['subject'] . '"/></p>
    <h2>Message Content</h2>';
    echo $content;
	wp_editor($message, 'message', $settings = array('textarea_rows' => '20','wpautop'=>false));
$content ='<p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="sendcopy"' . $auto['sendcopy'] . ' value="checked" /> Add senders message content to the email</p>
<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the error settings for '.$id.'?\' );"/></p>';
    $content .= wp_nonce_field("save_qcf");
    $content .= '</form>
    </div>
    </div>';
	echo $content;
}

function qcf_smtp_page() {
	if( isset( $_POST['Submit']) && check_admin_referer("save_qcf")) {
		$options = array('mailer','smtp_host','smtp_port','smtp_ssl','smtp_auth','smtp_user','smtp_pass');
		foreach ( $options as $item) {
            $qcfsmtp[$item] = stripslashes($_POST[$item]);
            $qcfsmtp[$item] =filter_var($qcfsmtp[$item],FILTER_SANITIZE_STRING);
        }
		update_option( 'qcf_smtp', $qcfsmtp );
		qcf_admin_notice("The SMTP settings have been updated.");
		}
	if( isset( $_POST['Reset']) && check_admin_referer("save_qcf")) {
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
    </tr>
    <tr valign="top">
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
    </tr>
    <tr valign="top">
    <td>Username</td><td><input name="smtp_user" type="text" value=" '.$qcfsmtp['smtp_user'].'" /></td>
    </tr><tr valign="top">
    <td>Password</td><td><input name="smtp_pass" type="text" value=" '.$qcfsmtp['smtp_pass'].'" /></td>
    </tr>
    <tr>
    <td colspan="2"><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" />  <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset these settings?\' );"/></p>
    <input type="hidden" name="action" value="update" /><input type="hidden" name="option_page" value="email">
    </td>
    </tr>
    </table>';
    $content .= wp_nonce_field("save_qcf");
    $content .= '</form>
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
    foreach ($arr as $item) qcf_delete_things($item);
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
sort($arr);
        foreach ($arr as $item) {
            if ($qcf_setup['current'] == $item) $checked = 'checked'; else $checked = '';
            if ($item == '') {$formname = 'default'; $item='';} else $formname = $item;
            $content .='<input style="margin:0; padding:0; border:none" type="radio" name="current" value="' .$item . '" ' .$checked . ' /> '.$formname . ' ';
        }
        $content .='<input type="hidden" name="alternative" value = "' . $qcf_setup['alternative'] . '" />
        <input type="hidden" name="dashboard" value = "' . $qcf_setup['dashboard'] . '" />&nbsp;&nbsp;
        <input type="submit" name="Select" class="button-secondary" value="Change Form" /></form>';
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
        foreach (explode( ',',$qcf['sort']) as $name) {
            if ($qcf['active_buttons'][$name] == "on" && $name != 'field12') 
            array_push($headerrow, $qcf['label'][$name]);
        }
        array_push($headerrow,'Date Sent');
        fputcsv($outstream,$headerrow, ',', '"');
        foreach(array_reverse( $message ) as $value) {
            $cells = array();
            foreach (explode( ',',$qcf['sort']) as $name) {
                if ($qcf['active_buttons'][$name] == "on"&& $name != 'field12')
                array_push($cells,$value[$name]);
            }
            array_push($cells,$value['field0']);
            fputcsv($outstream,$cells, ',', '"');
        }
        fclose($outstream); 
        exit;
    }
}

function qcf_donate_page() {
    $content = '<div class="qcf-settings"><div class="qcf-options">';
    $content .= donate_loop();
    $content .= '</div></div>';
    echo $content;
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
    <div class='qcf-style'>\r\t";
    if ($errors) $content .= "<h2 class='error'>Feed me...</h2>\r\t<p class='error'>...your donation details</p>\r\t";
    else $content .= "<h2 style=\"color:red\">Make a donation</h2>\r\t<p>Whilst I enjoy creating these plugins they don't pay the bills. So a paypal donation will always be gratefully received</p>\r\t";
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
$formvalues = array();
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

function add_admin_pages() {
    add_menu_page('Messages', 'Messages', 'manage_options','quick-contact-form/quick-contact-messages.php','','dashicons-email-alt');
}
function qcf_page_init() {
    add_options_page('Quick Contact', 'Quick Contact', 'manage_options', __FILE__, 'qcf_tabbed_page');
}
function qcf_settings_init() {
    qcf_generate_csv();
    return;
}
function qcf_settings_scripts() {
    qcf_admin_scripts();
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_style('qcf_settings',plugins_url('settings.css', __FILE__));
    wp_enqueue_style('wp-color-picker' );
    wp_enqueue_media();
    wp_enqueue_script('qcf-media',plugins_url('media.js', __FILE__ ), array( 'jquery','wp-color-picker' ), false, true );
}

add_action('plugin_row_meta', 'qcf_plugin_row_meta', 10, 2 );

function qcf_plugin_row_meta( $links, $file = '' ){
    if( false !== strpos($file , '/quick-contact-form.php') ){
        $new_links = array('<a href="http://quick-plugins.com/quick-contact-form/"><strong>Help and Support</strong></a>','<a href="'.get_admin_url().'options-general.php?page=quick-contact-form/settings.php&tab=donate"><strong>Donate</strong></a>');
$links = array_merge( $links, $new_links );  
} 
    return $links;
}

function qcf_clone ($id,$clone) {
    if ($clone == 'default') $clone = '';
    $update = qcf_get_stored_options ($clone);update_option( 'qcf_settings'.$id, $update );
    $update = qcf_get_stored_attach ($clone);update_option( 'qcf_attach'.$id, $update );
    $update = qcf_get_stored_style($clone);update_option( 'qcf_style'.$id, $update );
    $update = qcf_get_stored_reply ($clone);update_option( 'qcf_reply'.$id, $update );
    $update = qcf_get_stored_error ($clone);update_option( 'qcf_error'.$id, $update );
    qcf_create_css_file ('update');
}

function qcf_tabbed_page() {
    $qcf_setup = qcf_get_stored_setup();
    $id=$qcf_setup['current'];
    echo '<div class="wrapper"><h1>Quick Contact Form</h1>';
    if ( isset ($_GET['tab'])) {qcf_admin_tabs($_GET['tab']); $tab = $_GET['tab'];} else {qcf_admin_tabs('setup'); $tab = 'setup';}
    switch ($tab) {
        case 'setup' : qcf_setup($id); break;
        case 'settings' : qcf_form_settings($id); break;
        case 'styles' : qcf_styles($id); break;
        case 'reply' : qcf_reply_page($id); break;
        case 'error' : qcf_error_page ($id); break;
        case 'attach' : qcf_attach ($id); break;
        case 'help' : qcf_help ($id); break;
        case 'smtp' : qcf_smtp_page(); break;
        case 'reset' : qcf_reset_page($id); break;
        case 'donate' : qcf_donate_page(); break;
        case 'autoresponce' : qcf_autoresponce_page($id); break;
    }
    echo '</div>';
}

function qcf_admin_tabs($current = 'settings') { 
    $tabs = array( 
        'setup' => 'Setup',
        'settings' => 'Form Settings',
        'attach' => 'Attachments',
        'styles' => 'Styling',
        'reply' => 'Send Options',
        'autoresponce' => 'Auto Responder',
        'error' => 'Error Messages'
    ); 
    $links = array();
    echo '<h2 class="nav-tab-wrapper">';
    foreach( $tabs as $tab => $name ) {
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
        echo "<a class='nav-tab$class' href='?page=quick-contact-form/settings.php&tab=$tab'>$name</a>";
    }
    echo '</h2>';
}

add_action('init', 'qcf_settings_init');
add_action('admin_menu', 'qcf_page_init');
add_action('admin_notices', 'qcf_admin_notice' );
add_action( 'admin_menu', 'add_admin_pages' );
add_action('admin_enqueue_scripts', 'qcf_settings_scripts');

