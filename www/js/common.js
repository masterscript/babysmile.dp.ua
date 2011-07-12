$(document).ready(function() {

	// top images toggle
	function toggleSrc(img) {
		var src1 = img.next("input:hidden").val();
		if (!src1) return;
		var src2 = img.attr("src");
		img.attr("src",src1);
		img.next("input:hidden").val(src2);
	}	
	$("img.hover").hover(
		function() { toggleSrc($(this)) },
		function() { toggleSrc($(this)) }
	);
	$("img.hover").load();
	
	// buttons
	$('button').button();
	$('input:submit').button();
	$('input:button').button();
	
	// enter form
	$('#formEnter').dialog({
		modal: true,
		autoOpen: false,
		resizable: false,
		width: 350,
		show: 'explode',
		hide: 'explode',
		buttons: [
			{
				text: 'Вход',
				click: function() {
					$.post('/enter', $(this).serialize());
					$(this).dialog("close");
				},
				'class': 'switchable enter'
			},
			{
				text: 'Регистрация',
				click: function() {
					$.post('/register', $(this).serialize());
					$(this).dialog("close");
				},
				'class': 'switchable register'
			},
			{
				text: 'Отмена',
				click: function() {
					$(this).dialog("close");
				}
			}
		]
	});
	$('#header_icons a.enter').click(function() {
		$('#formEnter').dialog('open');
		return false;
	});
	$('#formEnter div.switchable a').click(function() {
		$('#formEnter .switchable').toggle();
		$('#formEnter').next('div').find('.switchable').toggle();
		if ($(this).hasClass('register')) {
			$('#formEnter').dialog('option', 'title', 'Регистрация');
		} else {
			$('#formEnter').dialog('option', 'title', 'Вход на сайт');
		}
		return false;
	});
	//***
	
});