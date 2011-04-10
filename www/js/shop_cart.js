// Shop Cart Script

// обновление статистики корзины
function refreshCart () {
	
	$("#cart-count").load("/ajax/refresh_cart",{data_type: "count"});
	$("#cart-price").load("/ajax/refresh_cart",{data_type: "price"});
	
}

// заказ товара
$(document).ready ( function() {	
	
	refreshCart();		
	
	//$("form.modal input.spinner").spinner({max: 999, min: 1});
	
	$(".button-buy").each( function () {
		var form = $(this).parent().find("form");
		$(form).dialog({
			modal: true,
			autoOpen: false,
			resizable: false,
			title: $(this).attr("title"),
			show: 'explode',
			hide: 'explode',
			buttons: {
				'Добавить': function() {
					$.post("/ajax/add_to_cart",$(this).serialize(),refreshCart);
					$(this).dialog("close");
				},
				'Отменить': function() {
					$(this).dialog("close");
				}
			}
		});
		$(this).bind('click',function(){$(form).dialog('open')});
	});
	
	$(".deleteFromCart").click( function() {
		var parentTr = $(this).parent("td").parent("tr");
		var good_id = $(parentTr).find("input[type='hidden']").attr("value");
		$.post("/ajax/delete_from_cart",{good_id: good_id},
			   	function () {
					$(parentTr).fadeOut(function() {
						if ($(parentTr).parent("tbody").find("tr").length==2) {
							$("#table-cart").remove();
							$("form[name='fmProcessOrder']").remove();
							$("#message-register").remove();
							$("#sum_cart").remove();
							$("#empty_cart_text").text('Корзина пуста');
						}
						$(this).remove();
					});
					refreshCart();
						$("#sum_cart span").load("/ajax/up_sum_cart");
				});		
	});
	
});