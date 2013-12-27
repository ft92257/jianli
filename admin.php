<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once('function.php');
$isgl=0;
if($udb['uid']>0){
	if($udb['qx']==10){
		$isgl=2;
		if(!isset($_COOKIE[$config['cookie_prefix'].'ajhAuth']) || $_COOKIE[$config['cookie_prefix'].'ajhAuth']==''){
			if(!isset($config['safe_key']))$config['safe_key']='';
			$ajhAuthKey=md5($config['auth_key'].$_SERVER['HTTP_USER_AGENT'].'_IN_ADMIN_PANEL_'.date('Y-m-Y-m').'_'.$config['safe_key']);
			$aac=yjl_authcode("{$udb['password']}\t{$udb['uid']}", 'ENCODE', $ajhAuthKey);
			setcookie($config['cookie_prefix'].'ajhAuth', $aac, time()+365*86400);
		}
	}elseif($udb['isxg']>0){
		$isgl=1;
	}
}
if($isgl>0){
?><!-- <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"> -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $r_main['site_name']; ?></title>
<link href="<?php echo $yjl_tpath; ?>templates/default/admin/admin_m.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="lib/jquery.js"></script>
</head>
<body scroll="no">
<table width="100%" height="100%"  border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td colspan="2" height="80" valign="top"><div id="header">
			<div class="logo fl">
				<div class="png"><img width="160" height="43" src="<?php echo $yjl_tpath; ?>templates/default/admin/images/logo.gif" alt="<?php echo $r_main['site_name']; ?>" /></div>
			</div>
			<ul class="nav">
				<li><em><a href="./">网站首页</a></em></li>
				<?php if($isgl==2 && isset($_GET['debug']) && $_GET['debug']=='1'){ ?><li><em><a href="<?php echo $yjl_tpath; ?>admin.php">微博功能</a></em></li><?php } ?>
				<li class="navon"><em><a href="admin.php">网站功能</a></em></li>
			</ul>
			<div class="wei fl"><a title="在新窗口中打开访问首页" href="./" style="cursor: pointer;" class="s0" target="_blank">访问网站首页</a> &nbsp;|&nbsp; <a title="后退到前一页" onClick="history.go(-1);" style="cursor: pointer;" >后退一页</a> &nbsp;</div>

		</div></td>
	</tr>
	<tr>
		<td valign="top" id="main-fl"><div id="left">
			<div id="con_nav_1">
				<h1 onclick="if($('#yjl_menu_0').is(':visible')){$('#yjl_menu_0').slideUp(500);}else{$('#yjl_menu_0').slideDown(500);}">小区</h1>
				<div class="cc"/></div>
				<ul id="yjl_menu_0">
					<li><a href="a_xq.php" target="main">小区列表</a></li>
					<li style="display: none;"><a href="a_shxq.php" target="main">待审核小区</a></li>
					<li><a href="a_tjxq.php" target="main">添加小区</a></li>
					<li><a href="a_fx.php" target="main">户型管理</a></li>
				</ul>
				<h1 onclick="if($('#yjl_menu_1').is(':visible')){$('#yjl_menu_1').slideUp(500);}else{$('#yjl_menu_1').slideDown(500);}">用户</h1>
				<div class="cc"/></div>
				<ul id="yjl_menu_1">
					<li><a href="a_yz.php" target="main">业主列表</a></li>
					<li><a href="a_tjyz.php" target="main">添加业主</a></li>
					<li><a href="a_jl.php" target="main">专业人员列表</a></li>
					<li><a href="a_tjjl.php" target="main">添加专业人员</a></li><?php if($r_main['yzqc']==1 || $r_main['yzqc_jl']==1){ ?>
					<li><a href="a_yqm.php" target="main">邀请码</a></li><?php } ?>
					<li><a href="a_jlsm.php" target="main">请监理上门</a></li>
				</ul>
				<h1 onclick="if($('#yjl_menu_2').is(':visible')){$('#yjl_menu_2').slideUp(500);}else{$('#yjl_menu_2').slideDown(500);}">照片式监理</h1>
				<div class="cc"/></div>
				<ul id="yjl_menu_2">
					<li><a href="a_tp.php" target="main">已删除照片</a></li>
				</ul>
				<h1 onclick="if($('#yjl_menu_3').is(':visible')){$('#yjl_menu_3').slideUp(500);}else{$('#yjl_menu_3').slideDown(500);}">系统设置</h1>
				<div class="cc"/></div>
				<ul id="yjl_menu_3">
					<li><a href="a_dwz.php" target="main">短网址</a></li>
					<li><a href="a_rlog.php" target="main">短信发送记录</a></li>
<?php if($isgl==2){ ?>
					<li><a href="a_xz.php" target="main">协助管理员</a></li>
<?php } ?>
					<li><a href="a_sz.php" target="main">设置</a></li>
				</ul>
			</div>
		</td>
		<td valign="top" id="mainright" style="height:94%; "><iframe name="main" frameborder="0" width="100%" height="100%" scrolling="yes" style="overflow: visible;" src="<?php if($isgl==2){ ?>a_sz.php<?php }else{ ?>a_xq.php<?php } ?>"></iframe></td>
	</tr>
</table>
</body>
</html><?php
}else{
	echo '<script type="text/javascript">location.href=\'./\';</script>';
}
?>