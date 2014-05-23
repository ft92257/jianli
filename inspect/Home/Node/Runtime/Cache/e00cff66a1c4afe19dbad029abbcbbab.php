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

<div class="log-in w-41 log-in-show">
    	<p class="ts-tit clearfix ml-5 mr-6-5 mt-2"><span class="left f-s">登录</span><span class="left mt-1-5 ml-2">没有账号？<a href="#" class="register-to">请注册</a></span></p>
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
    <div class="log-in w-41 register-show">
    	<p class="ts-tit clearfix ml-5 mr-6-5 mt-2"><span class="left f-s">注册</span><span class="left mt-1-5 ml-2">已注册？<a href="#" class="log-in-to">请登录</a></span></p>
        <div class="from-box ml-5 mb-3-5">
        	<label class="l-bg mt-2"><input type="text" value="用户名" /></label>
            <label class="l-bg mt-1"><input type="text" value="密码" /></label>
            <label class="l-bg mt-1"><input type="text" value="确认密码" /></label>
            <div class="clearfix mt-1">
            	<label class="l-bg-yz-zc left"><input type="text" value="验证码" /></label>
                <!--<a href="#" class="left ml-1 mt-0-5"><img src="images/hq-yz.jpg" width="87" height="27" /></a>-->
                <input type="button" id="btn" value="获取验证码" style="border:#E4E4E3 1px solid; background-color:#EFEFEF; height:25px; line-height:25px; width:90px; color:#434446; margin-top:5px; margin-left:15px" />
            </div>
            <input type="submit" class="sub-mit" value="注&nbsp;&nbsp;册" />
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
                	<a href="javascript:;" class="logIn"><span class="nav-ico ico5"></span><span>登录</span></a>
                </li>
            </ul>
        </nav>
    </header>
<title>操作失败</title>
</head><body>

<div class="showMsg"><h5><?php echo ($msgTitle); ?></h5>
	<div class="main clearfix">
		<?php if(isset($error)): ?><span style="font-size:24px;color:#f00;"><?php echo ($error); ?></span><?php endif; ?><br><br><br><br>
		<?php if(isset($closeWin)): ?><input type="button" name="close" value="<?php echo ($lang["close"]); ?>" onClick="window.close();"><?php endif; ?>
		<?php if(isset($jumpUrl)): ?><p style="color:#666;">
		系统将在  <span style="color:blue;font-weight:bold"><?php echo ($waitSecond); ?></span>秒后自动跳转，如果不想等待，直接点击<a href="<?php echo ($jumpUrl); ?>">这里</a>
		</p>
		<script language="javascript">setTimeout("location.href = '<?php echo ($jumpUrl); ?>';",<?php echo ($waitSecond*1000); ?>);</script><?php endif; ?>
		<?php if(isset($returnjs)): ?><script style="text/javascript"><?php echo ($returnjs); ?>;</script><?php endif; ?>
		<?php if(isset($dialog)): ?><script style="text/javascript">
		var dialog = "<?php echo ($dialog); ?>";
		if (dialog!='') {
			window.top.right.location.reload();window.top.art.dialog({id:dialog}).close();
		}
		</script><?php endif; ?>
	</div>
</div>

</body>
</html>