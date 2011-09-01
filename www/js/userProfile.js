$(function() {
	var options = {
		url: $("#userInfo").attr("action"),
		beforeSend: function(XMLHttpRequest) {
			$("#btnSubmit").attr("disabled","disabled");
			$("#userInfo label").css("color","#666666");
			$("#userInfo input[type='password']").attr("value",'');
		},
		success: function(data, statusText) {
			if (data.is_errors) {
				$("#userInfo label[for='"+data.field+"']").css("color","red");
				alert(data.error_msg);
				$("#btnSubmit").removeAttr("disabled");
			} else {
				alert('Профиль успешно сохранен');
				$("#btnSubmit").removeAttr("disabled");
			}
		},
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			alert('error: '+textStatus);
			$("#debug").html(XMLHttpRequest.responseText);
		},		
		dataType: 'json',
		resetForm: false
	};	
	$("#userInfo").ajaxForm(options);
	
	$.datepicker.regional['ru'];
	date = new Date();
	$("#userInfo input[name='birthday']").datepicker({ 
		//minDate: "0d",
		maxDate: "-2y",
		dateFormat: 'yy-mm-dd',
		changeYear: true,
		yearRange: '1900:2010'
	});
});