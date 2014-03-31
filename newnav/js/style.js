// JavaScript Document
$(function(){
	$(".supervisor ul li a").hover(function(){
		$(this).find(".abstract").stop(true,false).animate({"top":"0"},300);
	},function(){
		$(this).find(".abstract").stop(true,false).animate({"top":"173"},300);
	});
	
	$(".fh").click(function(){
		$(window).scrollTop(0);
	});
	$(window).scroll(function(){
		if($(window).scrollTop()>1){
			$(".fh").show();
		}else{
			$(".fh").hide();
		}
	});
	
	if ($("#moBox").length) {
		var divFixed=$("#moBox").offset().top;
		$(window).scroll(function(){
			if($(window).scrollTop()>divFixed){
				$("#moBox").css({"position":"fixed","top":"0"});
			}else{
				$("#moBox").css({"position":"relative","top":"0"});
			}
		});
	}
	
});