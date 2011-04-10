<?php

/**
 * Два связанных списка для выбора города.
 * @author Darkside
 */
class Admin_Forms_Elements_cityselect extends HTML_QuickForm_select {
	
	public function __construct ($elementName = null,$elementLabel = null, $options=null, $attributes=null) {
	
	    parent::__construct($elementName,$elementLabel,$options,$attributes);
	    $this->_type = 'select';
	    
	}
	
	public function toHtml() {
		
		$regions = Admin_Core::getObjectDatabase()->selectCol('SELECT id AS ARRAY_KEYS,name FROM items WHERE pid = (SELECT id FROM items where template = ?)',
			'regions_main');
		
		$office = Admin_Core::getObjectDatabase()->selectRow('
			SELECT city_id,r.id region_id FROM items i
			JOIN carrier_offices co ON co.id = i.id
			JOIN items city ON city.id = co.city_id
			JOIN items r ON r.id = city.pid
			WHERE i.id = ?d',
			Admin_Controller_Collection::getInstance()->model->getId());
		
			
		$helperSelect = new HTML_QuickForm_select('__select_helper');
		$helperSelect->loadArray($regions);
		if ($office) {
			$cities = Admin_Core::getObjectDatabase()->selectCol('SELECT id AS ARRAY_KEYS,name FROM items WHERE pid = ?d',$office['region_id']);
			$helperSelect->setSelected($office['region_id']);
			$this->loadArray($cities);
			$this->setSelected($office['city_id']);
		}
		
		$jsCode = "
			<script type='text/javascript'>
			$(document).ready(function() {				
				$('select[name=\"__select_helper\"]').change(function(){
					var region_id = $(this).val() ? $(this).val() : ($(this).children(':selected').val() ? $(this).children(':selected').val() : $(this).children(':first').val());
					$.getJSON('/ajax/get_cities',{region_id: region_id}, function(j){
					  var options = '';
					  for (var i = 0; i < j.length; i++) {
						options += '<option value=\"' + j[i].id + '\">' + j[i].name + '</option>';
					  }
					  if (!options) options = '<option value=\"0\">нет данных</option>';
					  $('select[name=\"carrier_offices::city_id\"]').html(options);
					});		
				});
		";
		
		if (!$office) {
			$jsCode .= "$('select[name=\"__select_helper\"]').trigger('change');";
		}
		$jsCode .= "});
			</script>";
		
		$this->updateAttributes('id="carrier_offices::city_id" meta:validator="'.$this->getAttribute('meta:validator').'" meta:dynamic');
		
		return $jsCode.$helperSelect->toHtml().parent::toHtml();
		
	}
	
}
