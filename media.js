jQuery(document).ready(function($){
	var custom_uploader;
	$('#qcf_upload_background').click(function(e) {
		e.preventDefault();
		if (custom_uploader) {custom_uploader.open();return;}
		custom_uploader = wp.media.frames.file_frame = wp.media({
		title: 'Background Image',button: {text: 'Insert Image'},multiple: false});
		custom_uploader.on('select', function() {
			attachment = custom_uploader.state().get('selection').first().toJSON();
			$('#qcf_background').val(attachment.url);
			});
		custom_uploader.open();
		});
	var button_uploader;
	$('#qcf_upload_submit_button').click(function(e) {
		e.preventDefault();
		if (button_uploader) {button_uploader.open();return;}
		button_uploader = wp.media.frames.file_frame = wp.media({
		title: 'Submit Button Image',button: {text: 'Insert Image'},multiple: false});
		button_uploader.on('select', function() {
			attachment = button_uploader.state().get('selection').first().toJSON();
			$('#qcf_submit_button').val(attachment.url);
			});
		button_uploader.open();
		});
    $('.qcf-color').wpColorPicker();
    $('.qcfdate').datepicker({
        monthNames: objectL10n.monthNames,
        monthNamesShort: objectL10n.monthNamesShort,
        dayNames: objectL10n.dayNames,
        dayNamesShort: objectL10n.dayNamesShort,
        dayNamesMin: objectL10n.dayNamesMin,
        dateFormat: objectL10n.dateFormat,
    });
});


