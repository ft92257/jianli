//www.dookay.com
;(function(){
	$.fn.extend({
		"scrollexpansion":function(options){
				options=$.extend({
						n:6
					},options);					
			var $Spt=this;
			var $pr_tit=$(".pr_tit").children();
			$Spt.each(function(){
			var $Spta=$(this).children("a");
			if($Spta.length==0){
					var nIndex=$Spt.index(this);
					$Spt.eq(nIndex).parents(".pr_pic").removeClass();
					var $html=$pr_tit.eq(nIndex).html();
					$pr_tit.eq(nIndex).replaceWith("<div class='hide'>"+$html+"</div>");
					}
				var nCount=Math.ceil($Spta.length/options.n);	
				for(var i=0; i< nCount;i++){
					var newSpt=$Spta.slice(options.n*i,options.n*i+options.n);
					var $div=$("<div></div>")
					$div.append(newSpt)
					$(this).append($div)
					}
					
				});			
			return this;
		}
	});
})(jQuery);//扩展scrollable的插件

;(function(){
	$.fn.extend({
		"sildeZYJ":function(options){
				options=$.extend({
						slideblock:null,//幻灯片
						events:"mouseover",//事件（所有可用的jq事件）
						speed:400,//切换速度（设置为0变成tab切换）
						effect:null,//fade淡入淡出，silde滑动（滑动必须把要滑动的元素放在一个统一的元素里，如 ul li）
						
						autotime:true,//是否自动轮播
						time:2000//轮播时间（autotime设置为true有效）
					},options);
				var $silde=$(options.slideblock);
				var $tabs=this;
				
				$tabs.first().addClass("current");
				if(!options.effect){$silde.not(":first").css("display","none");
					}else{
					var $sildparnt=$silde.css("float","left").parent().css({"position":"abslute","width":"10000px"})
					}
				
				function tabs(n){
					$tabs.eq(n).addClass("current").siblings().removeClass("current");
					}//按钮切换
					
				function fade(n){
					$silde.eq(n).stop(false,true).fadeIn(options.speed)
						.siblings().stop(false,true).fadeOut(options.speed);
					}//fade效果幻灯片
					
				function sildechg(n){
					$sildparnt.stop(true,false).animate({"left":-n*$silde.outerWidth()},options.speed)
					}//切换效果
						
				function silde(n){
					options.effect?sildechg(n):fade(n);//判断是哪种效果
					tabs(n);
					}
					
				$tabs.bind(options.events,function(){
					pager=$(this).index();//获取当前数组下标
					silde(pager);
					});//小按钮“事件”切换
					
				var pager=0;
				var change=function(){
					if(pager==$tabs.length){//如果最后一页
						silde(0);//切换第一页
						pager=0;
						}else{
						silde(pager);
						}
					pager++;
					}
					
				if(options.autotime){var timer = setInterval(change,options.time);//自动轮播
					$tabs.hover(function(){
								clearInterval(timer);
							},function(){
							timer=setInterval(change,options.time);			
							});//鼠标悬停停在自动轮播
					}
					
			return this;
		}
	});
})(jQuery);//幻灯篇插件

$(function(){
//隐藏显示------------------------------------
		$(".show",this).hover(function(){$(this).css({"z-index":"1","margin-left":"10px"}).children(".hide").css("display","block")},function(){$(this).attr("style","").children(".hide").css("display","none")});

//幻灯片--------------------------------------
$(".slidetabs > a").sildeZYJ({slideblock:".slide > ul > li",speed:400});
//覆盖---------------------------------------
		$("a[rel]").overlay({mask: '#000'});
//给li添加个鼠标滑动事件---------------------------------------
		$(".hover li").hover(function(){
			$(this).addClass("hover").siblings().removeClass("hover");
			},function(){
			$(this).removeClass("hover")	
			});
		
//tab切换------------------------------------------------------
		$(".tabs").tabs(".tabcnt",{event:'mouseover'});
//滑动------------------------------------------------------
		$(".spt").scrollexpansion();
		$(".sptsml").scrollexpansion({n:2});
		$(".scrollable").scrollable();		

//隔行换色------------------------------------------------------
		$(".tdhover > tbody > tr:even").addClass("even");
		$(".tdhover tr").hover(function(){
			$(this).addClass("hover").siblings().removeClass("hover");
			},function(){
			$(this).removeClass("hover")	
			});
//输入框-------------------------------------------------	

});