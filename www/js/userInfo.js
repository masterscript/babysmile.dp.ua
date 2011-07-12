$(document).ready(function() {

	// fill cities
	$("select[name='region_id']").change(function(){
		var id = $(this).val() ? $(this).val() : ($(this).children(":selected").val() ? $(this).children(":selected").val() : $(this).children(":first").val());
		$.getJSON("?pname=ajaxGetCities",{region_id: id}, function(j){
		  var options = '';
		  for (var i = 0; i < j.length; i++) {
			options += '<option value="' + j[i].id + '">' + j[i].name + '</option>';
		  }
		  if (!options) options = '<option value="0">нет данных</option>';		  
		  $("select[name='city_id']").html(options).trigger("change");
		});		
	});
	if ($("select[name='city_id']").children().length==0) {
		$("select[name='region_id']").trigger("change");
	}

	// fill carriers
	$("select[name='city_id']").change(function(){
		var id = $(this).val() ? $(this).val() : ($(this).children(":selected").val() ? $(this).children(":selected").val() : $(this).children(":first").val());
		$.getJSON("?pname=ajaxGetCarriers",{city_id: id}, function(j){
		  var options = '';
		  for (var i = 0; i < j.length; i++) {
			options += '<option value="' + j[i].id + '">' + j[i].name + '</option>';
		  }
		  if (!options) options = '<option value="0">нет данных</option>';
		  $("select[name='carrier_id']").html(options).trigger("change");
		});		
	});
	
	// fill carrier office
	$("select[name='carrier_id']").change(function(){
		var carrier = $(this).val() ? $(this).val() : ($(this).children(":selected").val() ? $(this).children(":selected").val() : $(this).children(":first").val());
		$.getJSON("?pname=ajaxGetCarrierOffices",{carrier_id: carrier, city_id: $("select[name='city_id']").val()}, function(j){
		  var options = '';
		  for (var i = 0; i < j.length; i++) {
			options += '<option value="' + j[i].id + '">' + j[i].name + '</option>';
		  }
		  if (!options) options = '<option value="0">нет данных</option>';
		  $("select[name='carrier_office']").html(options);
		});		
	});			
	
	$("select[name='delivery']").change(function() {
		if ($(this).val()=='carrier') {
			$("div#carrier").show();
			if ($("select[name='carrier_id']").children().length==0) {
				$("select[name='carrier_id']").trigger("change");
			}
		} else {
			$("div#carrier").hide();
		}
	});
	$("select[name='delivery']").trigger("change");
		
});
