var edit_mode = true;
$(document).ready( function () {
	
	$(".process-delete").click( function () {
		if (confirm("Вы уверены, что хотите удалить комментарий?")) {
			var parentTr = $(this).parent(".rowControls").parent("tr");
			$.post(
				"/ajax/comment_delete",
				{ comment_id : $(parentTr).find("input[name='comment_id']").attr("value") }			
			);
			$(parentTr).remove();
		}
	});
	
	$(".process-edit").click( function () {
		var parentTr = $(this).parent(".rowControls").parent("tr");
		var isCtrlExist = $(parentTr).find("textarea").length>0;
		if (edit_mode && !isCtrlExist) {
			$(parentTr).children(".editable").each( function () {
				var innerDiv = $(this).children("div");
				if ($(innerDiv).attr("class")=="text") {
					var ctrlElementHtml = '<textarea cols="50" rows="7" name="'+$(innerDiv).attr("class")+'">'+$(innerDiv).text()+'</textarea>';
				} else {
					var ctrlElementHtml = '<input name="'+$(innerDiv).attr("class")+'" value="'+$(innerDiv).text()+'" />';
				}
				$(innerDiv).html(ctrlElementHtml);
			});
			edit_mode = false;
		} else {
			if (isCtrlExist) {
				// посылаем запрос на сервер
				$.post(
					"/ajax/comment_update",
					{ 
						comment_id : $(parentTr).find("input[name='comment_id']").attr("value"),
						text : $(parentTr).find("textarea[name='text']").attr("value"),
						user_email : $(parentTr).find("input[name='user_email']").attr("value"),
						user_name : $(parentTr).find("input[name='user_name']").attr("value")
					}
					
				);
				// убираем редактируемые поля
				$(parentTr).children(".editable").each( function () {
					var innerDiv = $(this).children("div");
					if ($(innerDiv).attr("class")=="text") {
						$(innerDiv).html($(innerDiv).find("textarea").attr("value"));
					} else {
						$(innerDiv).html($(innerDiv).find("input").attr("value"));
					}
				});
				edit_mode = true;
			}
		}
	});
	
});