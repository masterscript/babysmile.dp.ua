
$(function() {
	
	var initSlider = function() {
		var minPrice = parseInt($('#filters .price input[name="priceMin"]').val());
		var maxPrice = parseInt($('#filters .price input[name="priceMax"]').val());
		var mminPrice = parseInt($('#filters input[name="priceMmin"]').val());
		var mmaxPrice = parseInt($('#filters input[name="priceMmax"]').val());
		$('#filters .price .slider').slider({
			range: true,
			min: mminPrice,
			max: mmaxPrice,
			values: [minPrice, maxPrice],
			step: 10,
			slide: function(event, ui) {
				$('#filters .price input[name="price"]').trigger('click');
				$('#filters .price .range').text(ui.values[0] + ' - ' + ui.values[1]);
				$('#filters .price input[name="priceMin"]').val(ui.values[0]);
				$('#filters .price input[name="priceMax"]').val(ui.values[1]);
			}
		}).trigger('slide');
	}
	
	$('#setFilter').click(function() {
		initSlider();
		$('#filterControls').show();
		return false;
	});
	$('#filterControls .buttons a.apply').click(function() {
		$('#filters form').submit();
		return false;
	});
	$('#filterControls .buttons a.close').click(function() {
		$('#filterControls').hide();
		return false;
	});
	$('#filterControls .buttons a.cancel').click(function() {
		$('#filterControls').hide();
		return false;
	});
	
	$('#filters input:checkbox').click(function() {
		$(this).parents('td').toggleClass('grey');
		if ($(this).parents('td').hasClass('grey')) {
			$(this).parents('td').next('td').find('input').attr('disabled', 'disabled');
		} else {
			$(this).parents('td').next('td').find('input').removeAttr('disabled');
		}
	});
});