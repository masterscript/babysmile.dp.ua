
// обновление статистики корзины
function refreshCart () {	
	$("#header_icons a.cart span").load("/ajax/refresh_cart", {data_type: "count"});	
}

// заказ товара
$(document).ready(function() {		
		
	$(".button-buy").each(function () {
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
		$(this).bind('click', function() {
			$(form).dialog('open');
			return false;
		});
	});		
	
	$('.cart_bottom input[name="doProcess"]').click(function(){
		$('form#userInfo').dialog('open');
	});
	
	$('#cart div.delete a').click(function() {
		$.post('/ajax/delete_from_cart', {key: $(this).next('input').val()},
			$.proxy(function (data) {
				if (data.result) {
					$(this).parents('#cart div.block').fadeOut(function() {
						$(this).remove();
						if (!$('#cart div.block').length) {
							$('#cart').html('<p id="emptyCart">Корзина пуста</p>');
						}						
					});
					refreshCart();
					$('.cart_bottom span.price').load('/ajax/refresh_cart', {data_type: 'price'});
				}
			}, $(this)),
			'json'
		);
		return false;
	});
	
	$('#cart .num .up').click(function() {
		var current = Number($(this).next().val());
		$(this).next().val(current < 999 ? current+1 : current);
		return false;
	});	
	$('#cart .num .down').click(function() {
		var current = Number($(this).prev().val());
		$(this).prev().val(current > 1 ? current-1 : current);
		return false;
	});
	
});