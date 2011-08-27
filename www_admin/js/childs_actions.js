
$(document).ready ( function () {
	
	// фильтрация только по значению текущего элемента
	$(".actionFilter").click ( function () {
		// текущее поле ввода
		var thisInput = $(this).parent("div").find("input");
		$("#childs-form input[type='text']").each( function () {
			if ($(this).attr("id")!=thisInput.attr("id")) {
				$(this).attr("value","");
			}
		});
		$("#childs-form").submit();
	});
	
	// фильтрация по всем полям
	$(".actionFilterAdd").click ( function () {		
		$("#childs-form").submit();
	});
	
	// сортировка
	$(".actionSortDesc").click ( function () {
		$("input[name='sort_name']").attr("value",$(this).parents("th").find("input:hidden").attr("value"));
		$("input[name='sort_direction']").attr("value","desc");
		$("#childs-form").submit();
	});
	$(".actionSortAsc").click ( function () {
		$("input[name='sort_name']").attr("value",$(this).parents("th").find("input:hidden").attr("value"));
		$("input[name='sort_direction']").attr("value","asc");
		$("#childs-form").submit();
	});
	
});