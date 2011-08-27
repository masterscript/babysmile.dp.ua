$(document).ready(function() {
	$("#tags textarea").autocomplete("/ajax/autodata",
	{
		multiple: true,
		mustMatch: false,
		autoFill: true 
	});
});