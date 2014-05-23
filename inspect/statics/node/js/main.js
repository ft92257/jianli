// JavaScript Document
$(function(){
	/*登录*/
	$(".logIn").on("click",function(){
		layerIfram(".log-in-show");
	});
	/*登录切换到注册*/
	$(".register-to").on("click",function(){
		$(this).parents(".xubox_page").siblings("a").click();
		layerIfram(".register-show");
	});
	/*注册切换到登录*/
	$(".log-in-to").on("click",function(){
		$(this).parents(".xubox_page").siblings("a").click();
		layerIfram(".log-in-show");
	});
	/*费用-软装，硬装切换*/
	$(".show1-box").on("click",function(){
		$(".show-box1").show().siblings(".show-box2").hide();;
	});	
	$(".show2-box").on("click",function(){
		$(".show-box2").show().siblings(".show-box1").hide();;
	});
	/*预算切换*/
	$(".show-boxto-2").on("click",function(){
		$(".show2").show().siblings(".show1").hide();
	});
	$(".show-boxto-1").on("click",function(){
		$(".show1").show().siblings(".show2").hide();
	});
	/*验证码倒计时*/
	if($("#btn").length>0){
	var wait=60;
	document.getElementById("btn").disabled = false;   
	function time(o) {
			if (wait == 0) {
				o.removeAttribute("disabled");           
				o.value="获取验证码";
				wait = 5;
			} else {
				o.setAttribute("disabled", true);
				o.value="重新发送(" + wait + ")";
				wait--;
				setTimeout(function() {
					time(o)
				},
				1000)
			}
		}
	document.getElementById("btn").onclick=function(){time(this);}
	}
});
/*弹层调用*/
function layerIfram(obj){
	$.layer({
    type : 1,
	zIndex: 19891014,
    shade : [0.62,'#000',true],
    area : ['auto', 'auto'],
    title : false,
    border : [0],
    page : {dom : obj}
});
}