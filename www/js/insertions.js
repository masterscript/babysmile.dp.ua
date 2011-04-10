$(document).ready(function() {
	
	function formReset() {
		$("#btnSubmit").removeAttr("disabled");	
		$("#btnSubmit").css("background-image","none");
		$("#btnSubmit").val("Добавить объявление");
	}
	
	formReset();
	
	$('#insertionForm').submit(function() { 
		// submit the form 
		//$(this).ajaxSubmit(); 
		// return false to prevent normal browser submit and page navigation 
		return false; 
	});
	
	var options = {
		url: '/ajax/insertion/add',
		beforeSend: function(XMLHttpRequest) {
			$("#btnSubmit").attr("disabled","disabled");
			$("#btnSubmit").val("");
			$("#btnSubmit").css("background-image","url(/i/ajax-loader.gif)");
			$("#insertionForm td").css("border","none");
		},
		success: function(data, statusText) {
			if (data.is_errors) {
				$("#insertionForm [name='"+data.field+"']:eq("+data.index+")").parent("td").css("border","1px solid red");
				alert(data.error_msg);
				formReset();
			} else {
				$("#insertionForm").animate(
					{width:0,height:0,opacity:0},1500,
					function() {
						$("#insertionForm").hide();
						$("#msgAdded").show();
					}
				);
			}
		},
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			alert('error: '+textStatus);
			$("#debug").html(XMLHttpRequest.responseText);
		},		
		dataType: 'json',
		resetForm: false
	};
	
	$("#insertionForm").ajaxForm(options);
	
	
	$("#add_photo").click(function() {
		
		$("input:file:last").parents("tr").after('<tr><td class="right">&nbsp;</td><td class="left"><input type="file" name="photo[]" /></td></tr>');
		
		if ($("input:file").length>=3) {
			$(this).hide();
		}
		
		return false;
		
	});
	
	$.datepicker.regional['ru'];
	date = new Date();
	$("#insertionForm input[name='date']").datepicker({ 
		showOn: "both", 
		buttonImage: "/i/calendar.gif", 
		buttonImageOnly: true,
		minDate: "0d",
		maxDate: "3m",
		dateFormat: 'yy-mm-dd'
	});
	
	$("#btnPreview").click(function() {
		$("#type_name").text($(":input[name='type'] option:selected").text());
		$("#region_name").text($(":input[name='region'] option:selected").text());
		$("#author").text($("input[name='author']").val());
		$("#phone").text($("input[name='phone']").val());
		$("#text").text($("textarea[name='text']").val());
		$("#preview").fadeIn();
	});
	
	$("#preview .close").click(function() {
		$("#preview").fadeOut();
	});
	
});