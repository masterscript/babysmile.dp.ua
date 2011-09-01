$(function() {

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
					$.post('?pname=ajaxUserAuth', $(this).serialize(), function(data) {
						if (data.error) {
							alert(data.error);
						} else if (data.returnUrl) {
							window.location = data.returnUrl;
						}
					}, 'json');
				},
				'class': 'switchable enter'
			},
			{
				text: 'Регистрация',
				click: function() {
					$.post('?pname=ajaxUserRegister', $(this).serialize(), function(data) {
						if (data.error) {
							alert(data.error);
						} else if (data.returnUrl) {
							window.location = data.returnUrl;
						}
					}, 'json');
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
	
});