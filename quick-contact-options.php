<?php

function qcf_get_stored_options ($id) {
    $qcf = get_option('qcf_settings'.$id);
    if(!is_array($qcf)) $qcf = array();
    $default = qcf_get_default_options();
    $qcf = array_merge($default, $qcf);
    return $qcf;
}

function qcf_get_default_options () {
    $qcf = array(
        'active_buttons'=> array(
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
        ),
        'required'=> array(
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
        ),
        'label'=> array(
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
            'field14' =>'Select Value'
        ),
        'sort'=> 'field1,field2,field3,field4,field5,field6,field7,field10,field8,field9,field11,field13,field14,field12',
        'lines'=> 6,
        'htmltags'=> '<a><b><i>',
        'datepicker'=> 'checked',
        'dropdownlist'=> 'Pound,Dollar,Euro,Yen,Triganic Pu',
        'checklist'=> 'Donald Duck,Mickey Mouse,Goofy',
        'radiolist'=> 'Large,Medium,Small',
        'title'=> 'Enquiry Form',
        'blurb'=> 'Fill in the form below and we will be in touch soon',
        'send'=> 'Send it!',
        'fieldtype'=> 'ttext',
        'fieldtypeb'=> 'btext',
        'selectora'=> 'dropdowna',
        'selectorb'=> 'checkboxb',
        'selectorc'=> 'radioc',
        'min'=> '0',
        'max'=> '100',
        'initial'=> '50',
        'step'=> '10',
        'output-values'=> 'checked'
    );
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
    $attach = array(
        'qcf_attach' => '',
        'qcf_number' => '3',
        'qcf_attach_label' => 'Attach an image (Max 100kB)',
        'qcf_attach_size' => '100000',
        'qcf_attach_type' => 'jpg,gif,png,pdf',
        'qcf_attach_width' => '15',
        'qcf_attach_error' => 'There is a problem with your attachment. Please check file formats and size.',
        'qcf_attach_error_size' => 'File is too big',
        'qcf_attach_error_type' => 'Filetype not permitted'
    );
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
    $style = array(
        'font' => 'plugin',
        'font-family' => 'arial, sans-serif',
        'font-size' => '1.2em',
        'font-colour' => '#465069',
        'header' => '',
        'header-type' => 'h2',
        'header-size' => '1.6em',
        'header-colour' => '#465069',
        'text-font-family' => 'arial, sans-serif',
        'text-font-size' => '1.2em',
        'text-font-colour' => '#465069',
        'error-font-colour' => '#D31900',
        'error-border' => '1px solid #D31900',
        'width' => 280,
        'widthtype' => 'percent',
        'submitwidth' => 'submitpercent',
        'submitposition' => 'submitleft',
        'border' => 'none',
        'form-border' => '1px solid #415063',
        'input-border' => '1px solid #415063',
        'input-required' => '1px solid #00C618',
        'bordercolour' => '#415063',
        'inputborderdefault' => '1px solid #415063',
        'inputborderrequired' => '1px solid #00C618',
        'inputbackground' => '#FFFFFF',
        'inputfocus' => '#FFFFCC',
        'background' => 'white',
        'backgroundhex' => '#FFF',
        'submit-colour' => '#FFF',
        'submit-background' => '#343838',
        'submit-button' => '',
        'submit-border' => '1px solid #415063',
        'submitwidth' => 'submitpercent',
        'submitposition' => 'submitleft',
        'corners' => 'corner',
        'slider-background' => '#CCC',
        'slider-revealed' => '#00ff00',
        'handle-background' => 'white',
        'handle-border' => '#CCC',
        'output-size' => '1.2em',
        'output-colour' => '#465069',
        'use_custom' => '',
        'styles' => ".qcf-style {\r\n\r\n}"
    );
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
    $reply = array(
        'replytitle' => 'Message sent!',
        'replyblurb' => 'Thank you for your enquiry, I&#146,ll be in contact soon',
        'sendcopy' => '',
        'replycopy' => '',
        'replysubject' => 'Thank you for your enquiry',
        'replymessage' => 'I&#146,ll be in contact soon. If you have any questions please reply to this email.',
        'messages' => 'checked',
        'tracker' => 'checked',
        'page' => 'checked',
        'url' => '',
        'subject' => 'Enquiry from',
        'subjectoption' => 'sendername',
        'qcf_redirect' => '',
        'qcf_redirect_url' => '',
        'copy_message' => 'Thank you for your enquiry. This is a copy of your message',
        'qcf_reload' => '',
        'qcf_reload_time' => '5',
        'qcfmail' => 'wpemail',
        'bodyhead' => 'The message is:'
    );
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
    $error = array(
        'field1' => 'Giving me '. strtolower($qcf['label']['field1']) . ' would really help.',
        'field2' => 'Please enter your '. strtolower($qcf['label']['field2']) . ' address',
        'field3' => 'A telephone number is needed',
        'field4' => 'What is the '. strtolower($qcf['label']['field4']),
        'field5' => 'Select a option from the list',
        'field6' => 'Check at least one box',
        'field7' => 'There is an error',
        'field8' => 'The ' . strtolower($qcf['label']['field8']) . ' is missing',
        'field9' => 'What is your '. strtolower($qcf['label']['field9']) . '?',
        'field10' => 'Please select a date',
        'field11' => 'Enter a value',
        'field13' => 'Enter a value',
        'email' => 'There&#146,s a problem with your email address',
        'telephone' => 'Please check your phone number',
        'mathsmissing' => 'Answer the sum please',
        'mathsanswer' => 'That&#146,s not the right answer, try again',
        'errortitle' => 'Oops, got a few problems here',
        'errorblurb' => 'Can you sort out the details highlighted below.',
        'emailcheck' => '',
        'phonecheck' => '',
        'spam' => 'Your Details have been flagged as spam'
    );
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
    $qcf_setup = array(
        'current' => '',
        'alternative' => '',
        'noui' => '',
        'nostyling' => ''
    );
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
    $messageoptions = array(
        'messageqty' => 'fifty',
        'messageorder' => 'newest'
    );
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
    $smtp = array(
        'smtp_host' => 'localhost',
        'smtp_port' => '25',
        'smtp_ssl' => 'none',
        'smtp_auth' => 'authfalse',
        'smtp_user' => '',
        'smtp_pass' => ''
    );
    return $smtp;
}

function qcf_get_stored_autoresponder2 ($id) {
    $auto = get_option('qcf_autoresponder'.$id);
    if(!is_array($auto)) $auto = array();
    $default = qcf_get_default_autoresponce();
    $auto = array_merge($default, $auto);
    return $auto;
}

function qcf_get_default_autoresponce2 () {
    $auto = array(
        'enable' => '',
        'subject' => '<p>Thank you for your enquiry.</p>',
        'message' => '<p>I&#146,ll be in contact soon. If you have any questions please reply to this email.</p>'
    );
    return $auto;
}

function qcf_get_stored_autoresponder ($id) {
    $auto = get_option('qcf_autoresponder'.$id);
    if(!is_array($auto)) {
        $send = qcf_get_stored_reply ($id);
$qcfemail = qcf_get_stored_email();
$fromemail = $qcfemail[$id];
if (empty($fromemail)) {
        global $current_user;
        get_currentuserinfo();
        $fromemail = $current_user->user_email;
    } 

        if ($send['sendcopy']) {
            $auto = array(
                'enable' => $send['sendcopy'],
                'subject' => $send['replysubject'],
                'message' => $send['replymessage'],
                'sendcopy' => $send['replycopy'],
                'fromname' => '',
                'fromemail' => $fromemail,
            );
            $send['thankyou'] = '';
            update_option( 'qcf_reply'.$id, $send );
update_option( 'qcf_autoresponder'.$id, $auto );
        } else {
            $auto = array(
                'enable' => '',
                'subject' => 'Thank you for your enquiry.',
                'message' => 'We will be in contact soon. If you have any questions please reply to this email.',
                'sendcopy' => 'checked',
                'fromname' => '',
                'fromemail' => '',
            );
        }
    }
    return $auto;
}