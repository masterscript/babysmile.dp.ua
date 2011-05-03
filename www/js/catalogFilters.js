
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
				$('#filters .price .range').text(ui.values[0] + ' - ' + ui.values[1]);
				$('#filters .price input[name="priceMin"]').val(ui.values[0]);
				$('#filters .price input[name="priceMax"]').val(ui.values[1]);
				var label = $('#filters tr.price>td:even label');
				if (label.parents('td').hasClass('grey')) {
					label.trigger('click');
				}
				$('#filters div.status').trigger('filterChanged');
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
	});
	
	$('#filters div.status').bind('filterChanged', function (event) {
		$(this).fadeIn();
	});
	
	$('#filters div.status').click(function () {
		$(this).fadeOut();
	});
	
	$('#filters tr>td:even label').click(function() {	
		$(this).parents('td').toggleClass('grey');
		if ($(this).parents('td').hasClass('grey')) {
			$(this).parents('td').next('td').find('input.canDisabled').attr('disabled', 'disabled');
		} else {
			$(this).parents('td').next('td').find('input.canDisabled').removeAttr('disabled');
		}
		$('#filters div.status').trigger('filterChanged');
	});
		
	$('#filters tr>td:odd').click(function() {
		var label = $(this).parent().find('td:even label');
		if (label.parents('td').hasClass('grey')) {
			label.trigger('click');
		}
		$('#filters div.status').trigger('filterChanged');
	});
	
	$('#filters tr.vendors>td:even label').click(function() {
		if ($(this).parents('td').hasClass('grey')) {
			$(this).parents('td').next('td').find('a').removeClass('checked');		
		}		
	});
	
	$('#filters tr.price>td:even label').click(function() {
		if ($(this).parents('td').hasClass('grey')) {
			$('#filters .price .slider').slider('option', 'disabled', true);
			$(this).parents('td').next('td').find('input:checkbox').attr('disabled', 'disabled');
		} else {
			$('#filters .price .slider').slider('option', 'disabled', false);
			$(this).parents('td').next('td').find('input:checkbox').removeAttr('disabled');
		}
	});
	
	$('#filters tr.vendors>td:odd a').click(function(event) {
		$(this).toggleClass('checked');		
		if ($(this).hasClass('checked')) {
			$(this).next().removeAttr('disabled');
		} else {
			$(this).next().attr('disabled', 'disabled');
		}
		event.preventDefault();
	});
});