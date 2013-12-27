<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once($yjl_tpath.'setting/face.php');
$no_getxq=1;
require_once('function.php');
$f='home.php';
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>装修训练营 | 易监理，上海家装监理公司，家装监理公司，上海装修监理公司，家装监理，装潢监理，上海家装监理，上海装潢监理，上海装饰监理，上海装修监理，上海家庭装潢监理，装修监理，上海装修监理，验房，上海验房，家装监理师，装饰监理师，别墅监理，别墅装饰监理，家装工程监理，家庭装修监理，水电监理，家装监理费，家装施工监理，装修第三方监理</title>
<meta name="keywords" content="易监理,装修训练营,上海家装监理公司,家装监理公司,上海装修监理公司,家装监理,装潢监理,上海家装监理,上海装潢监理,上海装饰监理,上海装修监理,上海家庭装潢监理,装修监理,上海装修监理,验房,上海验房,家装监理师,装饰监理师,别墅监理,别墅装饰监理,家装工程监理,家庭装修监理,水电监理,家装监理费,家装施工监理,装修第三方监理"/>
<meta name="description" content="装修训练营 | 易监理，上海家装监理公司，家装监理公司，上海装修监理公司，家装监理，装潢监理，上海家装监理，上海装潢监理，上海装饰监理，上海装修监理，上海家庭装潢监理，装修监理，上海装修监理，验房，上海验房，家装监理师，装饰监理师，别墅监理，别墅装饰监理，家装工程监理，家庭装修监理，水电监理，家装监理费，家装施工监理，装修第三方监理"/>
<link rel="shortcut icon" href="favicon.ico" />
<link rel="apple-touch-icon" href="images/iphone_logo.png" />
<link rel="apple-touch-icon" sizes="72x72" href="images/ipad_logo.png" />
<link rel="apple-touch-icon" sizes="114x114" href="images/iphone_retina_logo.png" />
<link rel="apple-touch-icon" sizes="144x144" href="images/ipad_retina_logo.png" />
<link href="css/main.css" rel="stylesheet" type="text/css" />
<link href="css/old.css" rel="stylesheet" type="text/css" />
<style type="text/css">
.menu {
	display: block;
	color: #333;
	text-align: center;
	font-size: 14px;
	float: left;
}
.menu:hover, .hover {
	color: #fff;
	background: #f62 url(images/home/tbg.jpg) repeat-x center bottom;
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
	$(window).scroll(function(){
		var st=document.documentElement.scrollTop+document.body.scrollTop;
		$('#phead').css('top', st+'px');
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
<div id="wrap" style="margin-top: 100px;">
	<div id="phead" style="height: 70px;position: absolute;top: 0;left: 0;">
		<div class="head clearfix">
			<a href="../" class="menu" style="width: 149px;height: 70px;line-height: 70px;">首页</a>
			<a href="home" class="menu" style="width: 150px;height: 70px;line-height: 70px;">易监理是什么</a>
			<a href="home#page-1" class="menu" style="width: 190px;padding-top: 10px;height: 60px;line-height: 25px;">易监理使装修质量<br/>平均提升20%</a>
			<a href="home#page-2" class="menu" style="width: 190px;height: 70px;line-height: 70px;">易监理流程与价格</a>
			<a href="home#page-3" class="menu" style="width: 160px;height: 70px;line-height: 70px;">联系我们</a>
			<a href="camp" class="menu hover" style="width: 140px;height: 70px;line-height: 70px;">装修训练营</a>
		</div>
	</div>
	<!--内容-->
	<div id="pbody">
<div style="background: url(images/home/camp_1.jpg) no-repeat;width: 1002px;height: 509px;padding-top: 108px;">
	<a href="active.php?xqid=0&id=2&j=1" title="参加活动" style="display: block;width: 202px;height: 71px;margin-left: 697px;"></a>
</div>
<div style="background: url(images/home/camp_2.jpg) no-repeat;width: 1002px;height: 593px;"></div>
<div style="background: url(images/home/camp_3.jpg) no-repeat;width: 1002px;height: 523px;"></div>
<div style="background: url(images/home/camp_4.jpg) no-repeat;width: 1002px;height: 800px;"></div>
<div style="background: url(images/home/camp_5.jpg) no-repeat;width: 1002px;height: 603px;"></div>
</div>
<!--底部-->
<div id="pfoot" style="clear: both;">
	<div class="left">
		<address>易监理<a href="http://www.miitbeian.gov.cn/" target="_blank">沪ICP备12034482号</a> <a href="about-yijianli.html">关于易监理</a> |  <a href="about-service.html">服务流程</a> |  <a href="about-pay.html">收费标准</a> | <a href="about-paypal.html">支付宝支付</a> | <a href="about-contact.html">联系我们</a></address>
	</div>
	<div class="right">Copyright &copy; <?php echo date('Y'); ?> 易监理</div>
</div>
</body></html>