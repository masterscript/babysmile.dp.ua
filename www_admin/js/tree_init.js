var simpleTreeCollection;
$(document).ready(function(){
	// initialize tree
	simpleTreeCollection = $('#tree').simpleTree({
		animate: false,
		autoclose:true,
		afterClick: function (el) {
			window.location = '/view?id='+$(el).find("span").attr("id");		
		}
	});
	
	// scroll div to selected element
	//$("#tree").scrollTo("span.active-node");
	
	// not using now	
	function callBackF () {
	
		$("li.folder-open ul.ajax, li.folder-open-last ul.ajax").SimpleTree({
			success: callBackF
		});
	
	}
	
	$("#doRequest").click( function () {
		
	});
	
});