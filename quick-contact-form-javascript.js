function qcfclear(thisfield, defaulttext) {if (thisfield.value == defaulttext) {thisfield.value = "";}}
function qcfrecall(thisfield, defaulttext) {if (thisfield.value == "") {thisfield.value = defaulttext;}}
jQuery(document).ready(function($){$('.qcf-color').wpColorPicker(myOptions);});