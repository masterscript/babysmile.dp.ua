// order by registered user
$(document).ready (function() {		
	
	var options = {
		url: $("#userInfo").attr("action"),
		beforeSend: function(XMLHttpRequest) {
			$("#userInfo .submit").attr("disabled","disabled");
			$("#userInfo label").css("color","#666666");
		},
		success: function(data, statusText) {
			if (data.is_errors) {
				$("#userInfo label[for='"+data.field+"']").css("color","red");
				alert(data.error_msg);							
			} else {
				var dowloadLink = '<div><a target="_blank" href="/downloads/account?order_code='+data.order_code+'">загрузить счет</a></div>';
				$.getJSON("?pname=ajaxIsCityTop",{city_id: $("select[name='city_id']").val()}, function(data){
					if (data.top!=1) {
						$("#successOrder").append(dowloadLink);
					}
				});
				$("#cartForm").fadeOut();
				$("#emptyCart").hide();
				$("#successOrder").show();
				refreshCart();
				$('#userInfo').dialog('close');
			}
			$("#userInfo .submit").removeAttr("disabled");
		},
		/*error: function (XMLHttpRequest, textStatus, errorThrown) {
			alert('error: '+textStatus);
			console.log(errorThrown);
		},	*/	
		dataType: 'json',
		resetForm: false
	};	
	$("#userInfo").ajaxForm(options);
	$("select[name='city_id']").change(function(){					
		$.getJSON("?pname=ajaxIsCityTop",{city_id: $(this).val()}, function(data){
			if (data.top==1) {
				$("#sendAccount").removeAttr("checked").fadeOut().next("label").fadeOut();
			} else {
				$("#sendAccount").attr("checked","checked").fadeIn().css("display","inline").next("label").css("display","inline").fadeIn();
			}
		});
	});
	
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