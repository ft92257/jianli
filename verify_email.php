<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
$no_getxq=1;
require_once('function.php');
$f='verify_email.php';
$vc=(isset($_GET['key']) && trim($_GET['key'])!='')?htmlspecialchars(trim($_GET['key']),ENT_QUOTES):'';
if($vc!='' && strlen($vc)==32){
	$q_res=sprintf('select uid, email_ls from %s where is_wb>0 and email_code=%s and length(email_ls)>0 limit 1', $yjl_dbprefix.'members', yjl_SQLString($vc, 'text'));
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$uSQL=sprintf('update %s set email=%s where uid=%s', $dbprefix.'members',
			yjl_SQLString($r_res['email_ls'], 'text'),
			$r_res['uid']);
		$result=mysql_query($uSQL) or die('');
		$uSQL=sprintf('update %s set email_ls=%s, email_code=%s, is_wb=0 where uid=%s', $yjl_dbprefix.'members',
			yjl_SQLString('', 'text'),
			yjl_SQLString('', 'text'),
			$r_res['uid']);
		$result=mysql_query($uSQL) or die('');
		$mc='邮箱验证通过'.($udb['uid']>0?'':'，请使用邮箱和密码<a href="login.php" rel="#overlay_cont">登录</a>');
	}else{
		$mc='验证已过期或者验证信息不符合要求';
	}
	mysql_free_result($res);
}elseif($vc==''){
	$mc='验证字符串不能为空';
}else{
	$mc='验证字符长度不符合标准，请检查';
}
$c='<h2>邮箱验证</h2><div class="main clearfix"><span class="form_tip" style="margin:0 0 0 100px;">'.$mc.'</span></div>';
echo yjl_html($c, 'regist', 'regist');
?>