// order by registered user
$(document).ready (function() {		
		
	 var options = {
		url: $("#userInfo").attr("action"),
		beforeSend: function(XMLHttpRequest) {
			$("#userInfo button").attr("disabled", "disabled");
			$("#userInfo label").css("color", "#666666");
		},
		success: function(data, statusText) {
			if (data.is_errors) {
				$('#userInfo input[name="'+data.field+'"]').parent('label').css("color", "red");
				alert(data.error_msg);
			} else {
				$("#cartForm").fadeOut();
				$("#emptyCart").hide();
				$("#message-register").hide();
				$("#successOrder").show();
				refreshCart();
				$('#userInfo').dialog('close');
			}
			$("#userInfo .submit").removeAttr("disabled");
		},
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			alert('error: '+textStatus);
		},		
		dataType: 'json',
		resetForm: false
	};		
	
	$('#userInfo').dialog({
		modal: true,
		autoOpen: false,
		width: 350,
		resizable: false,
		show: 'explode',
		hide: 'explode',
		buttons: {
			'Завершить': function() {
				$(this).ajaxForm(options).submit();				
			},
			'Отменить': function() {
				$(this).dialog("close");
			}
		}
	});
	
});