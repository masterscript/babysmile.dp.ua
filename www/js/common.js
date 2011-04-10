$(document).ready(function() {
	function toggleSrc(img) {
		var src1 = img.next("input:hidden").val();
		if (!src1) return;
		var src2 = img.attr("src");
		img.attr("src",src1);
		img.next("input:hidden").val(src2);
	}	
	$("img.hover").hover(
		function() { toggleSrc($(this)) },
		function() { toggleSrc($(this)) }
	);
	$("img.hover").load();
});