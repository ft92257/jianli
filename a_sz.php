<?php
session_start();
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once('function.php');
$isgl=0;
if($udb['uid']>0){
	if($udb['qx']==10){
		$isgl=2;
	}elseif($udb['isxg']>0){
		$isgl=1;
	}
}
if($isgl==0)exit();
$f='a_sz.php';
$esid=md5($f);
if($_SERVER['REQUEST_METHOD']=='POST'){
	$_POST['dq'][]=$d_l1id;
	sort($_POST['dq']);
	$dq=join('|', $_POST['dq']);
	$site_name=htmlspecialchars(trim($_POST['site_name']),ENT_QUOTES);
	if($site_name=='')$site_name=$r_main['site_name'];
	$site_admin_email=htmlspecialchars(trim($_POST['site_admin_email']),ENT_QUOTES);
	$site_email_verify=(isset($_POST['site_email_verify']) && $_POST['site_email_verify']==1)?1:0;
	$jl_link=htmlspecialchars(trim($_POST['jl_link']),ENT_QUOTES);
	$jl_url=$r_main['jl_url'];
	$f_a=$_FILES['url'];
	$e=0;
	if($f_a['name']!=''){
		if(is_uploaded_file($f_a['tmp_name']) && $f_a['error']==0){
			$u_f='file/';
			$u_s=$max_file*1024;
			if($f_a['size']>$u_s && $u_s>0)$e=5;
			$f_e=explode('.', $f_a['name']);
			$f_e=strtolower($f_e[count($f_e)-1]);
			if(isset($u_ea) && !in_array($f_e, $u_ea))$e=5;
			if(!is_dir($u_f) && is_writeable($u_f))$e=4;
			if($e==0){
				$f_m=md5(time().rand(0,1000));
				if(@copy($f_a['tmp_name'], $u_f.$f_m.'.'.$f_e)){
					if($r_main['jl_url']!='' && file_exists($r_main['jl_url']))unlink($r_main['jl_url']);
					$jl_url=$u_f.$f_m.'.'.$f_e;
				}else{
					$e=5;
				}
			}
		}else{
			$e=5;
		}
	}
	$sina_k=htmlspecialchars(trim($_POST['sina_k']),ENT_QUOTES);
	$sina_s=htmlspecialchars(trim($_POST['sina_s']),ENT_QUOTES);
	$sina_uid=htmlspecialchars(trim($_POST['sina_uid']),ENT_QUOTES);
	$tqq_k=htmlspecialchars(trim($_POST['tqq_k']),ENT_QUOTES);
	$tqq_s=htmlspecialchars(trim($_POST['tqq_s']),ENT_QUOTES);
	$tqq_uid=htmlspecialchars(trim($_POST['tqq_uid']),ENT_QUOTES);
	$sms_n=htmlspecialchars(trim($_POST['sms_n']),ENT_QUOTES);
	$sms_p=htmlspecialchars(trim($_POST['sms_p']),ENT_QUOTES);
	$sms_qm=htmlspecialchars(trim($_POST['sms_qm']),ENT_QUOTES);
	$smtp_server=htmlspecialchars(trim($_POST['smtp_server']),ENT_QUOTES);
	$smtp_port=htmlspecialchars(trim($_POST['smtp_port']),ENT_QUOTES);
	$smtp_email=htmlspecialchars(trim($_POST['smtp_email']),ENT_QUOTES);
	$smtp_isa=(isset($_POST['smtp_isa']) && $_POST['smtp_isa']==1)?1:0;
	$smtp_user=htmlspecialchars(trim($_POST['smtp_user']),ENT_QUOTES);
	$smtp_pwd=htmlspecialchars(trim($_POST['smtp_pwd']),ENT_QUOTES);
	$uSQL=sprintf('update %s set dq=%s, site_name=%s, site_admin_email=%s, site_email_verify=%s, yzqc=%s, yzqc_jl=%s, jl_link=%s, jl_url=%s, sina_k=%s, sina_s=%s, sina_uid=%s, tqq_k=%s, tqq_s=%s, tqq_uid=%s, sms_n=%s, sms_p=%s, sms_qm=%s, smtp_server=%s, smtp_port=%s, smtp_email=%s, smtp_isa=%s, smtp_user=%s, smtp_pwd=%s', $yjl_dbprefix.'main',
		yjl_SQLString($dq, 'text'),
		yjl_SQLString($site_name, 'text'),
		yjl_SQLString($site_admin_email, 'text'),
		$site_email_verify,
		$_POST['yzqc'],
		$_POST['yzqc_jl'],
		yjl_SQLString($jl_link, 'text'),
		yjl_SQLString($jl_url, 'text'),
		yjl_SQLString($sina_k, 'text'),
		yjl_SQLString($sina_s, 'text'),
		yjl_SQLString($sina_uid, 'text'),
		yjl_SQLString($tqq_k, 'text'),
		yjl_SQLString($tqq_s, 'text'),
		yjl_SQLString($tqq_uid, 'text'),
		yjl_SQLString($sms_n, 'text'),
		yjl_SQLString($sms_p, 'text'),
		yjl_SQLString($sms_qm, 'text'),
		yjl_SQLString($smtp_server, 'text'),
		yjl_SQLString($smtp_port, 'text'),
		yjl_SQLString($smtp_email, 'text'),
		$smtp_isa,
		yjl_SQLString($smtp_user, 'text'),
		yjl_SQLString($smtp_pwd, 'text'));
	$result=mysql_query($uSQL) or die('');
	$_SESSION[$esid]=1;
	echo '<script type="text/javascript">location.href=\''.$f.'\';</script>';
	exit();
}
if(isset($_SESSION[$esid]) && $_SESSION[$esid]!=''){
	$m=array(1=>'设置已保存！');
	if(isset($m[$_SESSION[$esid]]))$t_m=$m[$_SESSION[$esid]];
	unset($_SESSION[$esid]);
}
if($r_main['dq']=='')$r_main['dq']=$d_l1id;
$a_dq=explode('|', $r_main['dq']);
$c=(isset($t_m)?yjl_getMsg($t_m):'').'<form method="post" action="" enctype="multipart/form-data" name="form1" onsubmit="if(document.form1.site_name.value==\'\'){alert(\'请输入网站名称。\');return false;}"><table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td>开通省份</td></tr><tr class="altbg1"><td>';
$q_res=sprintf('select id, name from %s where level=1 and upid=0', $dbprefix.'common_district');
$res=mysql_query($q_res) or die('');
$r_res=mysql_fetch_assoc($res);
if(mysql_num_rows($res)>0){
	do{
		$c.='<div style="float: left;padding: 10px;">';
		if($r_res['id']!=$d_l1id){
			$c.='<input type="checkbox" name="dq[]" value="'.$r_res['id'].'"'.(in_array($r_res['id'], $a_dq)?' checked="checked"':'').'/>';
		}else{
			$c.='<input type="checkbox" checked="checked" disabled="disabled"/>';
		}
		$c.=$r_res['name'].'</div>';
	}while($r_res=mysql_fetch_assoc($res));
}
mysql_free_result($res);
if(isset($_GET['delimg']) && $_GET['delimg']==1){
	$uSQL=sprintf('update %s set jl_url=%s', $yjl_dbprefix.'main',
		yjl_SQLString('', 'text'));
	$result=mysql_query($uSQL) or die('');
	if($r_main['jl_url']!='' && file_exists($r_main['jl_url']))unlink($r_main['jl_url']);
	$_SESSION[$esid]=1;
	echo '<script type="text/javascript">location.href=\''.$f.'\';</script>';
	exit();
}
$c.='</td></tr></table><br/><table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td colspan="2">系统设置</td></tr><tr class="altbg2"><td>网站名称：</td><td><input name="site_name" value="'.$r_main['site_name'].'" size="50"/></td></tr><tr class="altbg1"><td>网站邮件地址：</td><td><input name="site_admin_email" value="'.$r_main['site_admin_email'].'" size="50"/></td></tr><tr class="altbg2"><td>业主邀请码注册：</td><td><input type="radio" name="yzqc" value="1"'.($r_main['yzqc']==1?' checked="checked"':'').'/>是 <input type="radio" name="yzqc" value="0"'.($r_main['yzqc']==0?' checked="checked"':'').'/>否</td></tr><tr class="altbg1"><td>监理邀请码注册：</td><td><input type="radio" name="yzqc_jl" value="1"'.($r_main['yzqc_jl']==1?' checked="checked"':'').'/>是 <input type="radio" name="yzqc_jl" value="0"'.($r_main['yzqc_jl']==0?' checked="checked"':'').'/>否</td></tr><tr class="altbg2"'.($r_main['site_email_verify']>0?' style="display: none;"':'').'><td>新用户验证邮箱：</td><td><input type="radio" name="site_email_verify" value="1"'.($r_main['site_email_verify']==1?' checked="checked"':'').'/>是 <input type="radio" name="site_email_verify" value="0"'.($r_main['site_email_verify']==0?' checked="checked"':'').'/>否</td></tr><tr class="altbg1" style="display: none;"><td>照片式监理左侧广告链接：</td><td><input name="jl_link" value="'.$r_main['jl_link'].'" size="50"/></td></tr><tr class="altbg2" style="display: none;"><td valign="top">照片式监理左侧广告图片：</td><td>'.($r_main['jl_url']!=''?'<img src="'.$r_main['jl_url'].'" width="'.$fxt_w.'"/><br/><a href="?delimg=1" onclick="if(!confirm(\'确定删除？\'))return false;" style="color: #f00;">删除</a><br/>':'').'<input type="file" name="url"/><br/>图片宽度：'.$fxt_w.'px，允许类型：'.join('、', $u_ea).'，最大：'.$max_file.'KB<br/>上传新图片将自动删除原图片</td></tr></table><br/><table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td colspan="2">新浪微博</td></tr><tr class="altbg2"><td>App Key：</td><td><input name="sina_k" value="'.$r_main['sina_k'].'" size="50"/></td></tr><tr class="altbg1"><td>App Secret：</td><td><input name="sina_s" value="'.$r_main['sina_s'].'" size="50"/></td></tr><tr class="altbg2"><td>官方微博：</td><td>http://weibo.com/<input name="sina_uid" value="'.$r_main['sina_uid'].'" size="30"/></td></tr></table><br/><table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td colspan="2">腾讯微博</td></tr><tr class="altbg2"><td>App Key：</td><td><input name="tqq_k" value="'.$r_main['tqq_k'].'" size="50"/></td></tr><tr class="altbg1"><td>App Secret：</td><td><input name="tqq_s" value="'.$r_main['tqq_s'].'" size="50"/></td></tr><tr class="altbg2"><td>官方微博：</td><td>http://t.qq.com/<input name="tqq_uid" value="'.$r_main['tqq_uid'].'" size="30"/></td></tr></table><br/><table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td colspan="2">短信通道</td></tr><tr class="altbg2"><td>软件序列号：</td><td><input name="sms_n" value="'.$r_main['sms_n'].'" size="50"/></td></tr><tr class="altbg1"><td>密码：</td><td><input name="sms_p" value="'.$r_main['sms_p'].'" size="50"/></td></tr><tr class="altbg2"><td>签名：</td><td><input name="sms_qm" value="'.$r_main['sms_qm'].'" size="50"/></td></tr></table><br/><table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td colspan="2">邮件</td></tr><tr class="altbg2"><td>SMTP服务器：</td><td><input name="smtp_server" value="'.$r_main['smtp_server'].'" size="50"/></td></tr><tr class="altbg1"><td>SMTP端口：</td><td><input name="smtp_port" value="'.$r_main['smtp_port'].'" size="50"/></td></tr><tr class="altbg2"><td>邮箱：</td><td><input name="smtp_email" value="'.$r_main['smtp_email'].'" size="50"/></td></tr><tr class="altbg1"><td></td><td><input type="checkbox" name="smtp_isa" value="1"'.($r_main['smtp_isa']>0?' checked="checked"':'').'/>需要身份验证</td></tr><tr class="altbg2"><td>用户名：</td><td><input name="smtp_user" value="'.$r_main['smtp_user'].'" size="50"/></td></tr><tr class="altbg1"><td>密码：</td><td><input name="smtp_pwd" type="password" value="'.$r_main['smtp_pwd'].'" size="50"/></td></tr></table><br><center><input type="submit" class="button" name="settingsubmit" value="提 交"></center><br></form>';
echo yjl_adminhtml($c);
?>