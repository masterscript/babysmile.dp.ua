$(document).ready(function(){
	// initialize tree
	$('#tree-move').simpleTree({
		animate: true,
		autoclose:true,
		afterClick: function (el) {
			$("#move_item_id").attr("value",(el).find("span").attr("id"));
		}
	});
});