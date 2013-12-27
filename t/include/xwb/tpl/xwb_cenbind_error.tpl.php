<?php if (!defined('IS_IN_XWB_PLUGIN')) {die('access deny!');}?><?php if('UTF-8' != strtoupper($GLOBALS['_J']['config']['charset'])){@header("Content-type: text/html; charset=utf-8");}?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>绑定错误提示</title>
<link type="text/css" rel="stylesheet" href="<?php echo $GLOBALS['_J']['site_url'] . ('/images/xwb/xwb_'. XWB_S_VERSION .'.css');?>" />
<script language="javascript">
	function tips(){
		var s = document.getElementById('tips_succ').style;
		s.display='block';
		function hideTips() {
			s.display='none';
		}
		setTimeout(hideTips, 2000);
	}
</script>

</head>

<body>
<div class="bind-setting xwb-plugin">
	<p class="alert-tips">与新浪微博API通讯时发生错误！</p>
	<div class="bing-text">
            <?php if ( $sina_user_info['error_code'] == '-1' ) { ?>
				<p>服务器无法连接到新浪微博API服务器；或新浪微博API服务器无响应。</p>
				<p>稍候一下，然后重新打开此页面；如果此错误信息重复出现，<strong>请联系网站管理员处理。</strong></p>
    		<?php } elseif ( false !== strpos($sina_user_info['error'], 'token_')) { ?>
				<p>你可能已经、或曾经取消该站点的应用授权，导致程序在访问新浪微博api时被拒绝。</p>
				<p><strong>为了能够在&nbsp;<?php echo XWB_S_TITLE; ?>&nbsp;正常使用新浪微博，请立刻点击下面的“解除绑定”，然后重新进行绑定操作。</strong></p>
				<!--
				<p>备用方法：退出当前论坛登录的帐号，然后用和此论坛帐号绑定的新浪微博帐号重新进行api登录，即可更新应用授权。</p>
				-->
			<?php } else { ?>
				<p>系统在读取api信息时发生了错误，请稍后再试。</p>
				<p><strong>如果此错误信息重复出现，请点击下面的“解除绑定”，然后重新进行绑定操作。</strong></p>
			<?php } ?>
    </div>
    
    <div class="setting-box">
        <form id="unbindFrm" action="<?php echo XWB_plugin::URL('xwbSiteInterface.unbind');?>" method="post" target="xwbSiteRegister" >
			<input type="hidden" name="FORMHASH" value="<?php echo FORMHASH; ?>" />
			<h3>解除绑定</h3>
			<p>已绑定新浪微博帐号链接：<a target="_blank" href="http://weibo.com/u/<?php echo $sina_id;?>"><?php echo $sina_id;?></a></p>
			<div onclick="parent.XWBcontrol.confirm({msg:'你确认要解除与新浪帐号的绑定吗？',onok:function(){ document.getElementById('unbindFrm').submit(); var self = this; setTimeout(function(){ self.close(); parent.XWBcontrol.close('bind');}, 100); }})" class="xwb-plugin-btn"><input type="button" class="button" value="解除绑定"></div>
			<p class="tips"></p>
		</form>
    </div>
</div>
<iframe src="" name="xwbSiteRegister" frameborder="0" height="0" width="0"></iframe>
</body>
</html>
