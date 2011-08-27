$(document).ready ( function () {
	
	$(".changeAvailable").click ( function () {
		$(this).find("td").css("background","#f6afb9");
		var parentTr = $(this);
		$.post("/ajax/availability",{id: $(this).find("input[name='good_id']").attr("value")},
			function (data) {
				$(parentTr).find("img.status").attr("src","/img/status_"+data+".gif");
			});
	});	
		
});