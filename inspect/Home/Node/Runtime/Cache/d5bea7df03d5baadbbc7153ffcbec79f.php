<?php if (!defined('THINK_PATH')) exit();?>﻿<!doctype html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<script type="text/javascript" src="__STATICS__/js/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="__STATICS__/js/common.js"></script>

<link rel="stylesheet" type="text/css" href="__STATICS__/css/main.css" />
<link rel="stylesheet" href="__STATICS__/css/style.css" />

<title><?php echo ($Title); ?> | 易监理 - 装修行业中代表良心的力量，家装监理，连锁店装修监理，别墅装修监理，别墅监理，上海家装监理公司，家装监理公司，上海装修监理公司，家装监理，装潢监理，上海家装监理，上海装潢监理，上海装饰监理，上海装修监理，上海家庭装潢监理，装修监理，上海装修监理，验房，上海验房，家装监理师，装饰监理师，别墅监理，别墅装饰监理，家装工程监理，家庭装修监理，水电监理，家装监理费，家装施工监理，装修第三方监理</title>
<meta name="keywords" content="易监理,上海家装监理公司,家装监理公司,上海装修监理公司,家装监理,装潢监理,上海家装监理,上海装潢监理,上海装饰监理,上海装修监理,上海家庭装潢监理,装修监理,上海装修监理,验房,上海验房,家装监理师,装饰监理师,别墅监理,别墅装饰监理,家装工程监理,家庭装修监理,水电监理,家装监理费,家装施工监理,装修第三方监理"/>
<meta name="description" content="易监理，上海家装监理公司，家装监理公司，上海装修监理公司，家装监理，装潢监理，上海家装监理，上海装潢监理，上海装饰监理，上海装修监理，上海家庭装潢监理，装修监理，上海装修监理，验房，上海验房，家装监理师，装饰监理师，别墅监理，别墅装饰监理，家装工程监理，家庭装修监理，水电监理，家装监理费，家装施工监理，装修第三方监理"/>
<meta charset="utf-8">
<!–[if IE7 and IE10]>
<style>
 .topnav li{ float:left; cursor:pointer}
 .topnav li a{ display:block; cursor:pointer}
</style>
<![endif]–>
</head>
<body>
<iframe src="__HOST__/yjlnew.php" width="100%" height="135" frameborder=0 scrolling="no" target="_top" ></iframe>
<script>
	var URL = '__URL__';
	var GROUP = '__GROUP__';
</script>

<form method="post" action="">
硬装预算：<?php echo ($hard); ?>元<br>
软装预算：<?php echo ($soft); ?>元<br>
总预算：<?php echo ($total); ?>元  <a href="<?php echo U('set');?>">重设</a><br>

<table>
	<tr><td width="100">名称</td><td width="200">预算</td><td width="100">档次</td><td width="100">实际花费</td></tr>
	<tr><td>设计费</td><td><input type="text" name="" />元</td><td>高 中 低</td><td>0.00元</td></tr>
	<tr><td>人工费</td><td><input type="text" name="" />元</td><td>高 中 低</td><td>0.00元</td></tr>
	<tr><td>材料费</td><td><input type="text" name="" />元</td><td>高 中 低</td><td>0.00元</td></tr>
	<tr><td>家具</td><td><input type="text" name="" />元</td><td>高 中 低</td><td>0.00元</td></tr>
	<tr><td>家电</td><td><input type="text" name="" />元</td><td>高 中 低</td><td>0.00元</td></tr>
	<tr><td>软饰</td><td><input type="text" name="" />元</td><td>高 中 低</td><td>0.00元</td></tr>
	<tr><td>其他</td><td><input type="text" name="" />元</td><td>高 中 低</td><td>0.00元</td></tr>
</table>

</form>

</body>
</html>