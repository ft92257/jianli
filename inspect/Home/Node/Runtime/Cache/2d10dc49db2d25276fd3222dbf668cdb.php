<?php if (!defined('THINK_PATH')) exit();?>﻿<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>费用</title>
<link type="text/css" rel="stylesheet" href="__STATICS__/css/screen.css" />
<link type="text/css" rel="stylesheet" href="__STATICS__/layer/skin/layer.css" />
<link type="text/css" rel="stylesheet" href="__STATICS__/css/main.css" />
<script type="text/javascript" src="__STATICS__/js/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="__STATICS__/js/common.js"></script>
<!--[if lt ie 9]>
<script type="text/javascript" src="__STATICS__/js/html5shiv.js"></script>
<![endif]-->
</head>
<body>
<script>
	var URL = '__URL__';
	var GROUP = '__GROUP__';
</script>

<div class="log-in w-41">
	<p class="ts-tit clearfix ml-5 mr-6-5 mt-2"><span class="left f-s">登录</span><span class="left mt-1-5 ml-2">没有账号？<a href="#">请注册</a></span></p>
    <div class="from-box ml-5 mb-3-5">
    	<label class="l-bg mt-2"><input type="text" value="用户名" /></label>
        <label class="l-bg mt-1"><input type="text" value="密码" /></label>
        <div class="clearfix mt-1">
        	<label class="l-bg-yz left"><input type="text" value="验证码" /></label>
            <img class="left ml-1" src="__STATICS__/images/yz-img.jpg" width="68" height="34" />
            <a href="#" class="left ml-1 mt-0-5"><img src="__STATICS__/images/hyzimg.jpg" width="108" height="27" /></a>
        </div>
        <input type="submit" class="sub-mit" value="登&nbsp;&nbsp;录" />
    </div>
</div>
<header>
	<nav class="w-96 mga">
    	<ul class="clearfix ml-7-5">
        	<li>
            	<a href="#" class="bg-col-sty"><span class="nav-ico ico1"></span><span>档案</span></a>
            </li>
            <li>
            	<a href="#"><span class="nav-ico ico2"></span><span>知识库</span></a>
            </li>
            <li>
            	<a href="#"><span class="nav-ico ico3"></span><span>帮助</span></a>
            </li>
            <li>
            	<a href="#"><span class="nav-ico ico4"></span><span>备忘录</span></a>
            </li>
            <li>
            	<a href="javascript:;" class="logIn"><span class="nav-ico ico5"></span><span>登陆</span></a>
            </li>
        </ul>
    </nav>
</header>
<h2>硬装费用</h2>
<div style="width:980px;margin:auto;">
<form action="__URL__/hard_edit" method="post">
<table>
	<tr><td>类别</td><td>预计费用</td><td>实际费用</td></tr>	
	<?php if(is_array($hard_fields)): foreach($hard_fields as $field=>$vo): ?><tr><td><?php echo ($vo[0]); ?></td><td>￥<?php echo ($hard_budget[$field]); ?></td><td><input type="text" name="<?php echo ($field); ?>" value="<?php echo ($hard_realfee[$field]); ?>" /></td></tr><?php endforeach; endif; ?>
</table>
<input type="submit" value="保存" />

</form>
</div>

</body>
</html>