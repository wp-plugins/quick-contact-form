jQuery(document).ready(function($){
	var custom_uploader;
	$('#qcf_upload_background').click(function(e) {
		e.preventDefault();
		if (custom_uploader) {custom_uploader.open();return;}
		custom_uploader = wp.media.frames.file_frame = wp.media({
		title: 'Select Background Image',button: {text: 'Insert Image'},multiple: false});
		custom_uploader.on('select', function() {
			attachment = custom_uploader.state().get('selection').first().toJSON();
			$('#qcf_background').val(attachment.url);
			});
		custom_uploader.open();
		});
	$('#qcf_upload_submit_button').click(function(e) {
		e.preventDefault();
		if (custom_uploader) {custom_uploader.open();return;}
		custom_uploader = wp.media.frames.file_frame = wp.media({
		title: 'Select Submit Button Image',button: {text: 'Insert Image'},multiple: false});
		custom_uploader.on('select', function() {
			attachment = custom_uploader.state().get('selection').first().toJSON();
			$('#qcf_submit_button').val(attachment.url);
			});
		custom_uploader.open();
		});
	});
