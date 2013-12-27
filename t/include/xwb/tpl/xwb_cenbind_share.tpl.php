<?php if (!defined('IS_IN_XWB_PLUGIN')) {die('access deny!');}?><?php if('UTF-8' != strtoupper($GLOBALS['_J']['config']['charset'])){@header("Content-type: text/html; charset=utf-8");}?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>绑定成功提示</title>
<link type="text/css" rel="stylesheet" href="<?php echo $GLOBALS['_J']['site_url'] . ('/images/xwb/xwb_'. XWB_S_VERSION .'.css');?>" />
<style>

	
</style>
</head>

<body>

<div class="bind-setting xwb-plugin">
	<p class="alert-tips">与新浪微博帐号绑定成功！</p>
    <div class="bing-text">
    	<h4>您现在可以：</h4>
        <p>使用新浪微博帐号登录本站，不用担心忘记密码</p>
        <p>在本站发微博可选同步发到新浪微博上，吸引更多人关注</p>
    </div>
    <p class="txtb">&nbsp;<a href="<?php echo XWB_plugin::URL('xwbSiteInterface.bind&share=1');?>"><strong>发一条微博告诉我的粉丝</strong></a>&nbsp;&nbsp;<a href="<?php echo XWB_plugin::URL('xwbSiteInterface.bind&skip_share=1');?>">或者点此进入设置页面</a>&nbsp;</p>
</div>

</body>
</html>
