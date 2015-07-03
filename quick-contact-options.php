<?php

function qcf_get_stored_options ($id) {
    $qcf = get_option('qcf_settings'.$id);
    if(!is_array($qcf)) $qcf = array();
    $default = qcf_get_default_options();
    $qcf = array_merge($default, $qcf);
    if (!strpos($qcf['sort'],'14')) {$qcf['sort'] = $qcf['sort'].',field14';$qcf['label']['field14'] = 'Select Value';update_option('qcf_settings'.$id,$qcf);}
    return $qcf;
}

function qcf_get_default_options () {
    $qcf = array();
    $qcf['active_buttons'] = array(
        'field1'=>'on' , 'field2'=>'on',
        'field3'=>'',
        'field4'=>'on',
        'field5'=>'',
        'field6'=>'',
        'field7'=>'',
        'field8'=>'',
        'field9'=>'',
        'field10'=>'',
        'field11'=>'',
        'field12'=>'',
        'field13'=>'',
        'field14'=>''
    );
    $qcf['required'] = array(
        'field1'=>'checked',
        'field2'=>'checked',
        'field3'=>'',
        'field4'=>'',
        'field5'=>'',
        'field6'=>'',
        'field7'=>'',
        'field8'=>'',
        'field9'=>'',
        'field10'=>'',
        'field11'=>'',
        'field12'=>'checked',
        'field13'=>'',
        'field14'=>''
    );
    $qcf['label'] = array(
        'field1'=>'Your Name',
        'field2'=>'Email',
        'field3'=>'Telephone',
        'field4'=>'Message' , 
        'field5'=>'Select a value' ,
        'field6'=>'Select a value' ,
        'field7'=>'Select a value' , 
        'field8'=>'Website' , 
        'field9'=>'Subject', 
        'field10'=>'Select date', 
        'field11'=>'Add text',
        'field12'=>'Spambot blocker question',
        'field13'=>'Add text',
        'field14' =>'Select Value');
    $qcf['sort'] = 'field1,field2,field3,field4,field5,field6,field7,field10,field8,field9,field11,field13,field14,field12';
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
    $qcf['min'] = '0';
    $qcf['max'] = '100';
    $qcf['initial'] = '50';
    $qcf['step'] = '10';
    $qcf['output-values'] = 'checked';
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
    $style['error-font-colour'] = '#D31900';
    $style['error-border'] = '1px solid #D31900';
    $style['width'] = 280;
    $style['widthtype'] = 'percent';
    $style['submitwidth'] = 'submitpercent';
    $style['submitposition'] = 'submitleft';
    $style['border'] = 'none';
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
    $style['slider-background'] = '#CCC';
    $style['slider-revealed'] = '#00ff00';
    $style['handle-background'] = 'white';
    $style['handle-border'] = '#CCC';
    $style['output-size'] = '1.2em';
    $style['output-colour'] = '#465069';
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
    $error['spam'] = 'Your Details have been flagged as spam';
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

