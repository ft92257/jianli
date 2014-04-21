// JavaScript Document
$(function(){
	//首页banner start
	var ulLength=$(".img_list_box li").length;
	var viewport=$(".img_list_box").width();
	var imgUlWidth=viewport*ulLength;
	var index=0;
	for(var i=0; i<ulLength; i++){
		$(".subscript").append("<a href='#'></a>");
	}
	var sptWidth=$(".subscript").width();
	$(".subscript").css({marginLeft:-sptWidth/2});
	function dong(index){
		 var nowleft=-index*viewport;
		 $(".img_list_box ul").stop(true,false).animate({"left":nowleft},900);
		 $(".subscript a").removeClass("subMR").eq(index).addClass("subMR");
	};
	$(".subscript a").mouseover(function(){
		 var index=$(this).index();
		 $(this).addClass("subMR").siblings(".subscript a").removeClass("subMR");
		 dong(index);
		 page=$(this).index()+1;
	}).eq(0).trigger("mouseover");
	$("#banner").hover(function(){
		 clearInterval(clear);
		},function(){
			 clear=setInterval(function(){
				  if(index==ulLength){
						index=0;
					  }else{
						   dong(index);
						   index++;
						  }
				 },3500);
	}).trigger("mouseout");
	//首页banner stop
	
	//倒计时
	function lxfEndtime(){
		$(".down").each(function(){
			var endtime = new Date($(this).find(".downtime").attr("data-time")).getTime();
			var nowtime = new Date().getTime();
			var youtime = endtime-nowtime;
			var youstime= parseInt(youtime);
			if(youstime<=0){youstime=0;};
			var seconds = youstime/1000;
			var minutes = Math.floor(seconds/60);
			var hours = Math.floor(minutes/60);
			var days = Math.floor(hours/24);
			var CDay= days ;
			var CHour= hours % 24;
			var CMinute= minutes % 60;
			var CSecond= Math.floor(seconds%60);
			$(this).find(".day").html(CDay);
			$(this).find(".hour").html(CHour);
			$(this).find(".minute").html(CMinute);
			$(this).find(".second").html(CSecond);
		});
	};
	$(function(){
		  lxfEndtime();
		  setInterval(lxfEndtime,1000);
	});
	//倒计时结束

	//团验详情 时间列表 start
	var num=5;
	var movedis= $(".listBox").width()-1;
	var leng=$(".time_list_ul li").length;
	var liwidth=$(".time_list_ul li").width();
	var pagination=Math.ceil(leng/num);
	var pgm=1;
	$(".time_list_ul").css("width",liwidth*leng);
	$(".next").click(function(){
		 if(!$(".time_list_ul").is(":animated")){
			  if(pgm==pagination){
				   $(".time_list_ul").stop(true,true).animate({left:"0"},800);
				   pgm=1;
				  }
				  else{
					   $(".time_list_ul").stop(true,true).animate({left:"-="+movedis},800);
					   pgm++;
					  }
			 }
	});
	$(".prev").click(function(){
		 if(!$(".time_list_ul").is(":animated")){
			  if(pgm==1){
				   $(".time_list_ul").stop(true,true).animate({left:"-="+movedis*(pagination-1)},800);
				   pgm=pagination;
				  }
				  else{
					   $(".time_list_ul").stop(true,true).animate({left:"+="+movedis},800);
					   pgm--;
					  }
			 }
	});
	//团验详情 时间列表 stop
	
	//团验详情 tab
	$(".howTy li").click(function(){
		 var howtyLiIndex=$(this).index();
		 $(this).addClass("bortop").siblings("li").removeClass("bortop");
		 $(".list_box .show").eq(howtyLiIndex).show().siblings(".show").hide();
		});
	//团验详情 tab stop
	
	//验房卡
	var cardLength=$(".viewport-box img").length;
	var ind=0;
	for(var i=0; i<cardLength; i++){
		$(".mouseUl").append("<li></li>");
	}
	$(".mouseUl li:first").addClass("add");
	function moTo(ind){
		 $(".mouseUl li").eq(ind).addClass("add").siblings("li").removeClass("add");
		 $(".viewport-box a").eq(ind).stop(true,false).animate({"opacity":1,"z-index":12},1500).siblings("a").stop(true,false).animate({"opacity":0,"z-index":11},1500);
	}
	$(".mouseUl li").click(function(){
		ind=$(this).index();
		moTo(ind);
		});
	$("#card_banner").hover(function(){
		 clearInterval(clear);
		},function(){
			 clear=setInterval(function(){
				  if(ind==cardLength){
					   ind=0;
					  }else{
						   moTo(ind);
						   ind++;
						  }
				 },3000);
			}).trigger("mouseout");
			
	//验房卡详情	
	var idCardlength=$("#cardBuy .left img").length;
	var xindex=0;
	for(var i=0; i<idCardlength; i++){
		$("#cardBuy .left ul").append("<li></li>");
	}
	$("#cardBuy .left ul li:first").addClass("add");
	function fromTo(xindex){
		 $("#cardBuy .left ul li").eq(xindex).addClass("add").siblings("li").removeClass("add");
		 $("#cardBuy .left .imgBox img").eq(xindex).stop(true,false).animate({"opacity":1,"z-index":12},1500).siblings("img").stop(true,false).animate({"opacity":0,"z-index":11},1500);
	}
	$("#cardBuy .left ul li").click(function(){
		xindex=$(this).index();
		fromTo(xindex);
		});
	$("#cardBuy .left").hover(function(){
		 clearInterval(clear);
		},function(){
			 clear=setInterval(function(){
				  if(xindex==idCardlength){
					   xindex=0;
					  }else{
						   fromTo(xindex);
						   xindex++;
						  }
				 },3000);
			}).trigger("mouseout");
	
	//验房报告
	var smallImgNum=$(".sImgBox li").length;
	var pageNum=Math.ceil(smallImgNum/6);
	var liWidth=$(".sImgBox li").outerWidth(true);
	var pNum=1;
	$(".allPage").html(smallImgNum);
	$(".sImgBox ul").width(smallImgNum*liWidth);
	$(".prveSimg,.nextSimg").click(function(){
		 if(!$(".sImgBox ul").is(":animated")){
			  if(pNum==pageNum){
				   $(".sImgBox ul").stop(true,true).animate({left:"0"},800);
				   pNum=1;
				  }
				  else{
					   $(".sImgBox ul").stop(true,true).animate({left:"-="+6*liWidth},800);
					   pNum++;
					  }
			 }
	});
	$(".sImgBox ul li").click(function(){
		$(this).addClass("debor").siblings("li").removeClass("debor");
		var nowPage=$(this).index()+1;
		var bigUrl=$(this).find("img").attr("data-bigimg");
		var content=$(this).find("img").attr("alt");
		$(".nowPage").html(nowPage);
		$(".bigImg img").attr("src",bigUrl);
		$(".bigImg p").html(content);
	});
});