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
	//右侧菜单
	if($(".r-box-move").length>0){
		$(".r-box-u>li").hover(function(){
				var sbox=$(this).find(".show-box");
				$(this).children("a").addClass("hmove");
				if($(this).index()==$(".r-box-u>li:last").index()){
					$(this).children("a").css({"backgroundColor":"#E4E3E5"});
				};
				sbox.show();
			},function(){
				var sbox=$(this).find(".show-box");
				$(this).children("a").removeClass("hmove");
				sbox.hide();
		});
		//返回顶部
		$(window).scroll(function(){
			if($(this).scrollTop()>0){
				$(".r-box-move").fadeIn();
			}else{
				$(".r-box-move").hide();
			};
		});
		$(".mv-top").click(function(){
			$("html,body").animate({"scrollTop":"0"},400);
		});
	};
});