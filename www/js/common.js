$(function() {

	// buttons
	$('button').button();
	$('input:submit').button();
	$('input:button').button();
	
	$('#formSearch').dialog({
		modal: true,
		autoOpen: false,
		resizable: false,
		width: 350,
		show: 'explode',
		hide: 'explode',
		buttons: [
			{ text: 'Поиск', click: function() { $(this).submit(); } },
			{ text: 'Отмена', click: function() { $(this).dialog("close"); } }
		]
	});
	$('#header_icons a.search').click(function() {
		$('#formSearch').dialog('open');
		return false;
	});
	
});