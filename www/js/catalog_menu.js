// JavaScript Document

$(document).ready(function() {
	
	$("ul#submenu>li").hover(
		function () {
			$(this).children("a").children("img").fadeIn();
			$(this).children("span").css({color: "black"});
		},
		function () {
			$(this).children("a").children("img").fadeOut();
			$(this).children("span:not(.active)").css({color: '#208DAB'});
		}
	);
	
	$("ul#submenu>li>span").click(
		function() {
			$("#submenu .sub").hide();
			if ($.browser.msie) {
				$(this).parent().find(".sub").slideToggle();
			} else {
				$(this).parent().next(".sub").slideToggle();
			}
		}
	);
	
	$(".produced #showAllBrands").click(
		function() {
			$(".produced p.hidden").fadeIn();
			$(this).parent().remove();
			return false;
		}
	);
	
});