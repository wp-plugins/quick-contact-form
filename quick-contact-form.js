function qcfclear(thisfield, defaulttext) {if (thisfield.value == defaulttext) {thisfield.value = "";}}
function qcfrecall(thisfield, defaulttext) {if (thisfield.value == "") {thisfield.value = defaulttext;}}

jQuery('.datepicker').datepicker({
// Show the 'close' and 'today' buttons
showButtonPanel: true,
closeText: objectL10n.closeText,
currentText: objectL10n.currentText,
monthNames: objectL10n.monthNames,
monthNamesShort: objectL10n.monthNamesShort,
dayNames: objectL10n.dayNames,
dayNamesShort: objectL10n.dayNamesShort,
dayNamesMin: objectL10n.dayNamesMin,
dateFormat: objectL10n.dateFormat,
firstDay: objectL10n.firstDay,
isRTL: objectL10n.isRTL,
});