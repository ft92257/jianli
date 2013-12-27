<?php if (!defined('IS_IN_XWB_PLUGIN')) {die('access deny!');}?><?php if('UTF-8' != strtoupper($GLOBALS['_J']['config']['charset'])){@header("Content-type: text/html; charset=utf-8");}?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>绑定设置</title>
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
	<div class="setting-box">    	
        <div id="tips_succ" class="success-tipx hidden">保存设置成功！</div>
        <form action="<?php echo XWB_plugin::URL('xwbSiteInterface.bindTopic');?>" method="post" target="xwbSiteRegister" >
		<input type="hidden" name="FORMHASH" value="<?php echo FORMHASH; ?>" />
        <h3>发新微博是否默认发到新浪微博？</h3>
        <div class="radio-box">
        <div class="radio"><label for="setting_1"><input name="setting" id="setting_1" type="radio" value="1" <?php if ($setting == 1) {?> checked="checked" <?php }?> />是</label> &nbsp; <label for="setting_0"><input name="setting" id="setting_0" type="radio" value="0" <?php if ($setting == 0) {?> checked="checked" <?php }?> />否</label></div>
        </div>
        <?php if(XWB_plugin::pCfg('is_synctopic_tojishigou') && ($MemberHandler = & Obj::registry('MemberHandler')) && ($MemberHandler->HasPermission('xwb','__synctopic'))) {?>
        <h3>读取新浪微博的内容到本站？</h3>
        <div class="radio-box">
        <div class="radio"><label for="tojishigou_1"><input name="tojishigou" id="tojishigou_1" type="radio" value="1" <?php if ($tojishigou == 1) {?> checked="checked" <?php }?> />是</label> &nbsp; <label for="tojishigou_0"><input name="tojishigou" id="tojishigou_0" type="radio" value="0" <?php if ($tojishigou == 0) {?> checked="checked" <?php }?> />否</label></div>
        </div>
        <?php }?> 
        <?php if(XWB_plugin::pCfg('is_syncreply_tojishigou') && ($MemberHandler = & Obj::registry('MemberHandler')) && ($MemberHandler->HasPermission('xwb','__syncreply'))) {?>
        <h3>读取新浪微博的评论内容到本站？</h3>
        <div class="radio-box">
        <div class="radio"><label for="reply_tojishigou_1"><input name="reply_tojishigou" id="reply_tojishigou_1" type="radio" value="1" <?php if ($reply_tojishigou == 1) {?> checked="checked" <?php }?> />是</label> &nbsp; <label for="reply_tojishigou_0"><input name="reply_tojishigou" id="reply_tojishigou_0" type="radio" value="0" <?php if ($reply_tojishigou == 0) {?> checked="checked" <?php }?> />否</label></div>
        </div>
        <?php }?>        
        <span class="xwb-plugin-btn"><input type="submit" class="button" value="保存设置"></span>
        </form>
    </div>
    <div class="setting-box">
        <form id="unbindFrm" action="<?php echo XWB_plugin::URL('xwbSiteInterface.unbind');?>" method="post" target="xwbSiteRegister" >
			<input type="hidden" name="FORMHASH" value="<?php echo FORMHASH; ?>" />
			<p>您已绑定新浪微博帐号昵称为：<a target="_blank" href="http://weibo.com/u/<?php echo $sina_id;?>"><?php echo $screen_name;?></a></p>
			<div onclick="parent.XWBcontrol.confirm({msg:'你确认要解除与新浪帐号的绑定吗？',onok:function(){ document.getElementById('unbindFrm').submit(); var self = this; setTimeout(function(){ self.close(); parent.XWBcontrol.close('bind');}, 100); }})" class="xwb-plugin-btn"><input type="button" class="button" value="解除绑定"></div>
			<p class="tips">解除绑定后，您将无法使用新浪微博帐号登录本站</p>
		</form>
    </div>
</div>
<iframe src="" name="xwbSiteRegister" frameborder="0" height="0" width="0"></iframe>
<?php echo $share_msg; ?>
</body>
</html>
