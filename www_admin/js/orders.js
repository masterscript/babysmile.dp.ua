$(document).ready ( function () {
	
	$(".setStatus0").click ( function () {
		var parentTr = $(this).parent("td").parent("tr");
		var src = $(this).attr("src");
		$(this).attr("src","/img/load_circle_16x16.gif");
		$(parentTr).find("img.order-status").attr("src","/img/load_circle_16x16.gif");
		$.post(
			"/ajax/order_status",
			{status:0,id: $(parentTr).find("input[name='order_id']").attr("value")},
			jQuery.proxy(function () {
				$(parentTr).find("img.order-status").attr("src","/img/status_0.gif");
				$(this).attr("src",src);
			},$(this)));
	});
	
	$(".setStatus1").click ( function () {
		var parentTr = $(this).parent("td").parent("tr");
		var src = $(this).attr("src");
		$(this).attr("src","/img/load_circle_16x16.gif");
		$(parentTr).find("img.order-status").attr("src","/img/load_circle_16x16.gif");
		$.post(
			"/ajax/order_status",
			{status:1,id: $(parentTr).find("input[name='order_id']").attr("value")},
			jQuery.proxy(function () {
				$(parentTr).find("img.order-status").attr("src","/img/status_1.gif");
				$(this).attr("src",src);
			},$(this)));
	});
	
	$(".setStatus2").click ( function () {
		var parentTr = $(this).parent("td").parent("tr");
		var src = $(this).attr("src");
		$(this).attr("src","/img/load_circle_16x16.gif");
		$(parentTr).find("img.order-status").attr("src","/img/load_circle_16x16.gif");
		$.post(
			"/ajax/order_status",
			{status:2,id: $(parentTr).find("input[name='order_id']").attr("value")},
			jQuery.proxy(function () {
				$(parentTr).find("img.order-status").attr("src","/img/status_2.gif");
				$(this).attr("src",src);
			},$(this)));
	});
	
	$(".deleteOrder").click ( function () {
		var parentTr = $(this).parent("td").parent("tr");
		var src = $(this).attr("src");
		$(this).attr("src","/img/load_circle_16x16.gif");
		$(parentTr).find("img.order-status").attr("src","/img/load_circle_16x16.gif");
		$.post(
			"/ajax/order_status",
			{status:'delete',id: $(parentTr).find("input[name='order_id']").attr("value")},
			jQuery.proxy(function () {
				$(parentTr).fadeOut();
			},$(this)));		
	});
	
	$(".notAvailable").click ( function () {
		var parentTr = $(this).parent("td").parent("tr");
		var src = $(this).attr("src");
		$(this).attr("src","/img/cart_empty.png");
		$.post(
			"/ajax/not_available",
			{id: $(parentTr).find("input[name='order_id']").attr("value")},
			jQuery.proxy(function () {
				$(this).attr("src",src);
				$(this).fadeOut();
			},$(this)));		
	});
	
	$("table.purchase td.first").mouseover(function(event) { 	   
		$(this).find("div.allOrderOptions").fadeIn();
    });
	
	function contains(e, c) {
  		return (e == c) ?
		true :
		(c.parentNode) ?
		  contains(e, c.parentNode) :
		  false;
	}
	
	$("table.purchase td.first").mouseout(function(event) { 
		if(!contains(this, event.relatedTarget || event.fromElement)) {
          event.stopPropagation();
          $(this).find("div.allOrderOptions").fadeOut();
        }
    });
	
	$(".setAllStatus0").click ( function () {
		var orderCode = $(this).parents("tr:eq(0)").find("input[name='order_code']").attr("value");
		$("table.purchase tr.order"+orderCode).find("img.order-status").attr("src","/img/load_circle_16x16.gif");
		$.post("/ajax/order_all_status",{status:0,code: orderCode},function () {
			$("table.purchase tr.order"+orderCode).find("img.order-status").attr("src","/img/status_0.gif");
		});
	});
	
	$(".setAllStatus1").click ( function () {
		var orderCode = $(this).parents("tr:eq(0)").find("input[name='order_code']").attr("value");
		$("table.purchase tr.order"+orderCode).find("img.order-status").attr("src","/img/load_circle_16x16.gif");
		$.post("/ajax/order_all_status",{status:1,code: orderCode},function () {
			$("table.purchase tr.order"+orderCode).find("img.order-status").attr("src","/img/status_1.gif");
		});
	});
	
	$(".setAllStatus2").click ( function () {
		var orderCode = $(this).parents("tr:eq(0)").find("input[name='order_code']").attr("value");
		$("table.purchase tr.order"+orderCode).find("img.order-status").attr("src","/img/load_circle_16x16.gif");
		$.post("/ajax/order_all_status",{status:2,code: orderCode},function () {
			$("table.purchase tr.order"+orderCode).find("img.order-status").attr("src","/img/status_2.gif");
		});
	});
	
	$(".deleteAllOrder").click ( function () {
		var orderCode = $(this).parents("tr:eq(0)").find("input[name='order_code']").attr("value");
		$("table.purchase tr.order"+orderCode).find("img.order-status").attr("src","/img/load_circle_16x16.gif");
		$.post("/ajax/order_all_status",{status:"delete",code: orderCode},function () {
			$("table.purchase tr.order"+orderCode).each(function(){$(this).fadeOut();});
		});
	});
	
});