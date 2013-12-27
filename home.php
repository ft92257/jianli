<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once($yjl_tpath.'setting/face.php');
$no_getxq=1;
require_once('function.php');
require_once('jg.php');
$f='home.php';
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>线下监理 | 易监理，上海家装监理公司，家装监理公司，上海装修监理公司，家装监理，装潢监理，上海家装监理，上海装潢监理，上海装饰监理，上海装修监理，上海家庭装潢监理，装修监理，上海装修监理，验房，上海验房，家装监理师，装饰监理师，别墅监理，别墅装饰监理，家装工程监理，家庭装修监理，水电监理，家装监理费，家装施工监理，装修第三方监理</title>
<meta name="keywords" content="易监理,线下监理,上海家装监理公司,家装监理公司,上海装修监理公司,家装监理,装潢监理,上海家装监理,上海装潢监理,上海装饰监理,上海装修监理,上海家庭装潢监理,装修监理,上海装修监理,验房,上海验房,家装监理师,装饰监理师,别墅监理,别墅装饰监理,家装工程监理,家庭装修监理,水电监理,家装监理费,家装施工监理,装修第三方监理"/>
<meta name="description" content="线下监理 | 易监理，上海家装监理公司，家装监理公司，上海装修监理公司，家装监理，装潢监理，上海家装监理，上海装潢监理，上海装饰监理，上海装修监理，上海家庭装潢监理，装修监理，上海装修监理，验房，上海验房，家装监理师，装饰监理师，别墅监理，别墅装饰监理，家装工程监理，家庭装修监理，水电监理，家装监理费，家装施工监理，装修第三方监理"/>
<link rel="shortcut icon" href="favicon.ico" />
<link rel="apple-touch-icon" href="images/iphone_logo.png" />
<link rel="apple-touch-icon" sizes="72x72" href="images/ipad_logo.png" />
<link rel="apple-touch-icon" sizes="114x114" href="images/iphone_retina_logo.png" />
<link rel="apple-touch-icon" sizes="144x144" href="images/ipad_retina_logo.png" />
<link href="css/main.css" rel="stylesheet" type="text/css" />
<link href="css/old.css" rel="stylesheet" type="text/css" />
<style type="text/css">
.menu a {
	display: block;
	color: #333;
	text-align: center;
	font-size: 14px;
	float: left;
}
.menu a:hover, .hover {
	color: #fff;
	background: #f62 url(images/home/tbg.jpg) repeat-x center bottom;
}
.jg_menu {
	display: block;
	float: left;
	width: 82px;
	height: 34px;
	background-image: url(images/home/jg_menu.jpg);
}
.jgtd {
	background: #808080;
}
.jgtd td {
	background: #fff;
	padding: 5px;
	font-size: 14px;
	line-height: 25px;
}
.jgtd th {
	background: #ee6c00;
	color: #fff;
	font-weight: bold;
	padding: 5px;
	font-size: 14px;
	line-height: 25px;
}
.jg_div {
	width: 850px;
	border: 1px solid #d5d5d5;
	border-top: 0;
	border-bottom: 0;
	padding-left: 150px;
	background: #fff;
}
</style>
<script type="text/javascript" src="scripts/jquery.dookay.min.js"></script>
<script type="text/javascript" src="scripts/jquery.dookay.plugin.js"></script>
<script type="text/javascript" src="lib/jquery.rotate.js"></script>
<script type="text/javascript" src="lib/function.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	var pw=$(window).width();
	$('#phead').css('width', pw+'px');
	$('.jg_menu').mouseover(function(){
		$('.jg_div').hide();
		var id=$(this).attr('jq_id');
		$('#jg_div_'+id).show();
		if(id=='0'){
			$('#jg_menu0').css('background-position', '0 0');
			$('#jg_menu1').css('background-position', '0 -102px');
			$('#jg_menu2').css('background-position', '0 -170px');
		}else if(id=='1'){
			$('#jg_menu0').css('background-position', '0 -34px');
			$('#jg_menu1').css('background-position', '0 -68px');
			$('#jg_menu2').css('background-position', '0 -170px');
		}else if(id=='2'){
			$('#jg_menu0').css('background-position', '0 -34px');
			$('#jg_menu1').css('background-position', '0 -102px');
			$('#jg_menu2').css('background-position', '0 -136px');
		}
		var p3=$('#page-3-v').offset();
		$('#page-3').css('top', (p3.top-70)+'px');
	}).click(function(){
		return false;
	});
	var p1=$('#page-1-v').offset();
	$('#page-1').css('top', (p1.top-70)+'px');
	var p2=$('#page-2-v').offset();
	$('#page-2').css('top', (p2.top-70)+'px');
	var p3=$('#page-3-v').offset();
	$('#page-3').css('top', (p3.top-70)+'px');
	$(window).scroll(function(){
		var p1=$('#page-1-v').offset();
		$('#page-1').css('top', (p1.top-70)+'px');
		var p2=$('#page-2-v').offset();
		$('#page-2').css('top', (p2.top-70)+'px');
		var p3=$('#page-3-v').offset();
		$('#page-3').css('top', (p3.top-70)+'px');
		var st=document.documentElement.scrollTop+document.body.scrollTop;
		$('#phead').css('top', st+'px');
		if(st>=(p3.top-71)){
			$('#am_0').removeClass();
			$('#am_1').removeClass();
			$('#am_2').removeClass();
			$('#am_3').addClass('hover');
			$('#am_0').css('color', '#333');
			$('#am_1').css('color', '#333');
			$('#am_2').css('color', '#333');
			$('#am_3').css('color', '#fff');
			$('#poid').val('3');
		}else if(st>=(p2.top-71)){
			$('#am_0').removeClass();
			$('#am_1').removeClass();
			$('#am_2').addClass('hover');
			$('#am_3').removeClass();
			$('#am_0').css('color', '#333');
			$('#am_1').css('color', '#333');
			$('#am_2').css('color', '#fff');
			$('#am_3').css('color', '#333');
			$('#poid').val('2');
		}else if(st>=(p1.top-71)){
			$('#am_0').removeClass();
			$('#am_1').addClass('hover');
			$('#am_2').removeClass();
			$('#am_3').removeClass();
			$('#am_0').css('color', '#333');
			$('#am_1').css('color', '#fff');
			$('#am_2').css('color', '#333');
			$('#am_3').css('color', '#333');
			$('#poid').val('1');
		}else{
			$('#am_0').addClass('hover');
			$('#am_1').removeClass();
			$('#am_2').removeClass();
			$('#am_3').removeClass();
			$('#am_0').css('color', '#fff');
			$('#am_1').css('color', '#333');
			$('#am_2').css('color', '#333');
			$('#am_3').css('color', '#333');
			$('#poid').val('0');
		}
	});
	$('#am_0').mouseover(function(){
		$(this).css('color', '#fff');
	}).mouseout(function(){
		if($('#poid').val()!='0')$(this).css('color', '#333');
	});
	$('#am_1').mouseover(function(){
		$(this).css('color', '#fff');
	}).mouseout(function(){
		if($('#poid').val()!='1')$(this).css('color', '#333');
	});
	$('#am_2').mouseover(function(){
		$(this).css('color', '#fff');
	}).mouseout(function(){
		if($('#poid').val()!='2')$(this).css('color', '#333');
	});
	$('#am_3').mouseover(function(){
		$(this).css('color', '#fff');
	}).mouseout(function(){
		if($('#poid').val()!='3')$(this).css('color', '#333');
	});
});
</script>
<!--[if IE 6]>
<script type="text/javascript" src="scripts/dookay.png.js" ></script>
<script type="text/javascript">
	DD_belatedPNG.fix(' #phead,.hd_ico,.mn_ico,.show ');
</script>
<![endif]-->
</head>
<body>
<div id="page-1" style="position: absolute;top: 0;left: 0;"></div>
<div id="page-2" style="position: absolute;top: 0;left: 0;"></div>
<div id="page-3" style="position: absolute;top: 0;left: 0;"></div>
<div id="wrap" style="margin-top: 100px;">
	<div id="phead" style="height: 70px;position: absolute;top: 0;left: 0;">
		<div class="head clearfix menu">
			<a href="../" style="width: 149px;height: 70px;line-height: 70px;">首页</a>
			<a id="am_0" href="#" class="hover" style="width: 150px;height: 70px;line-height: 70px;color: #fff;">易监理是什么</a>
			<a id="am_1" href="#page-1" style="width: 190px;padding-top: 10px;height: 60px;line-height: 25px;">易监理使装修质量<br/>平均提升20%</a>
			<a id="am_2" href="#page-2" style="width: 190px;height: 70px;line-height: 70px;">易监理流程与价格</a>
			<a id="am_3" href="#page-3" style="width: 160px;height: 70px;line-height: 70px;">联系我们</a>
			<a href="camp" style="width: 140px;height: 70px;line-height: 70px;">装修训练营</a>
		</div>
	</div>
	<!--内容-->
	<div id="pbody">
<div style="background: url(images/home/home_1.jpg) no-repeat;width: 1002px;height: 444px;"></div>
<div style="background: url(images/home/home_2.jpg) no-repeat;width: 1002px;height: 520px;"></div>
<div style="background: url(images/home/home_3.jpg) no-repeat;width: 1002px;height: 542px;"></div>
<div style="background: url(images/home/home_4.jpg) no-repeat;width: 1002px;height: 565px;" id="page-1-v"></div>
<div style="background: url(images/home/home_5.jpg) no-repeat;width: 1002px;height: 583px;"></div>
<div style="background: url(images/home/home_6.jpg) no-repeat;width: 1002px;height: 477px;"></div>
<div style="background: url(images/home/home_7.jpg) no-repeat;width: 1002px;height: 533px;" id="page-2-v"></div>
<div style="background: url(images/home/home_8_t.jpg) no-repeat;width: 1002px;height: 285px;"></div>
<div style="background: url(images/home/home_8_m.jpg) no-repeat;width: 1002px;height: 34px;">
	<div style="padding-left: 248px;">
		<a href="#" class="jg_menu" id="jg_menu0" jq_id="0" style="background-position: 0 0;"></a>
		<a href="#" class="jg_menu" id="jg_menu1" jq_id="1" style="background-position: 0 -102px;"></a>
		<a href="#" class="jg_menu" id="jg_menu2" jq_id="2" style="background-position: 0 -170px;"></a>
	</div>
</div>
<div id="jg_div_0" class="jg_div"><br/><table width="769" cellspacing="1" class="jgtd">
<tr>
<th width="30" align="center">序号</th>
<th width="120" align="center">监理类型</th>
<th align="center">服务内容</th>
<th width="180" align="center">收费标准</th>
</tr><?php echo $jg_bs; ?></table><br/><br/><br/><br/><br/><br/></div>
<div id="jg_div_1" class="jg_div" style="display: none;"><br/><table width="769" cellspacing="1" class="jgtd">
<tr>
<th width="30" align="center">序号</th>
<th width="120" align="center">监理类型</th>
<th align="center">服务内容</th>
<th width="180" align="center">收费标准</th>
</tr><?php echo $jg_gy; ?></table><br/><br/><br/><br/><br/><br/></div>
<div id="jg_div_2" class="jg_div" style="display: none;"><br/><table width="769" cellspacing="1" class="jgtd"><?php echo $jg_yf; ?></table><br/><br/><br/><br/><br/><br/></div>
<div style="background: url(images/home/home_9.jpg) no-repeat;width: 1002px;height: 695px;" id="page-3-v"></div><input type="hidden" id="poid" value="0">
</div>
<!--底部-->
<div id="pfoot" style="clear: both;">
	<div class="left">
		<address>易监理<a href="http://www.miitbeian.gov.cn/" target="_blank">沪ICP备12034482号</a> <a href="about-yijianli.html">关于易监理</a> |  <a href="about-service.html">服务流程</a> |  <a href="about-pay.html">收费标准</a> | <a href="about-paypal.html">支付宝支付</a> | <a href="about-contact.html">联系我们</a></address>
	</div>
	<div class="right">Copyright &copy; <?php echo date('Y'); ?> 易监理</div>
</div>
</body></html>