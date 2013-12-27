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
$q_rep=sprintf('select uid, username from %s where role_id=2 limit 1', $dbprefix.'members');
$rep=mysql_query($q_rep) or die('');
$r_rep=mysql_fetch_assoc($rep);
if(mysql_num_rows($rep)>0)$admin_db=$r_rep;
mysql_free_result($rep);
$f='a_tjyz.php';
$esid=md5($f);
if(isset($_SESSION[$esid]) && $_SESSION[$esid]!=''){
	$m=array(1=>'请输入相关信息！', '请使用其他Email！', '用户名只可以使用数字、字母和下划线！', '邮箱格式错误！');
	if(isset($m[$_SESSION[$esid]]))$t_m=$m[$_SESSION[$esid]];
	unset($_SESSION[$esid]);
}
$c=isset($t_m)?yjl_getMsg($t_m):'';
if($_SERVER['REQUEST_METHOD']=='POST'){
	if(isset($_POST['password']) && trim($_POST['password'])!='' && isset($_POST['email']) && trim($_POST['email'])!=''){
		$p=htmlspecialchars(trim($_POST['password']),ENT_QUOTES);
		$email=htmlspecialchars(trim($_POST['email']),ENT_QUOTES);
		if(yjl_cemail($email)){
			$ip=yjl_getIP();
			$u=str_replace('@', '_', $email);
			$u=str_replace('.', '_', $u);
			$ue=preg_match("/^\\w+$/i",$u)?$u:'';
			if($ue=='')$u=substr(md5(time().rand(1,1000)), 0, 16);
			$u=yjl_chkusername($u);
			if($u!=''){
				$un_c=0;
				$em_c=0;
				$q_res=sprintf('select uid from %s where username=%s limit 1', $dbprefix.'members', yjl_SQLString($u, 'text'));
				$res=mysql_query($q_res) or die('');
				if(mysql_num_rows($res)>0)$un_c=1;
				mysql_free_result($res);
				$q_res=sprintf('select uid from %s where email=%s limit 1', $dbprefix.'members', yjl_SQLString($email, 'text'));
				$res=mysql_query($q_res) or die('');
				if(mysql_num_rows($res)>0)$em_c=1;
				mysql_free_result($res);
				if($un_c==0 && $em_c==0){
					if($yjl_isdebug==0)require_once('lib/smtp.php');
					$uid=yjl_adduser($u, $p, $ip, $email, 1);
					$iSQL=sprintf('insert into %s (uid) values (%s)', $yjl_dbprefix.'uyz', $uid);
					$result=mysql_query($iSQL) or die('');
					$a_nc=explode('@', $email);
					$nc=$a_nc[0];
					$uSQL=sprintf('update %s set nc=%s, isnc=1, qx=0 where uid=%s', $yjl_dbprefix.'members',
						yjl_SQLString($nc, 'text'),
						$uid);
					$result=mysql_query($uSQL) or die('');

					$nf='a_yz.php';
					$_SESSION[md5($nf)]=2;
					echo '<script type="text/javascript">location.href=\''.$nf.'\';</script>';
					exit();
				}else{
					$_SESSION[$esid]=2;
				}
			}else{
				$_SESSION[$esid]=3;
			}
		}else{
			$_SESSION[$esid]=4;
		}
	}else{
		$_SESSION[$esid]=1;
	}
	echo '<script type="text/javascript">location.href=\''.$f.'\';</script>';
	exit();
}
$c.='<form method="post" action=""><table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td colspan="2">添加业主</td></tr><tr class="altbg1"><td>邮箱：</td><td><input name="email" size="60"/></td></tr><tr class="altbg0"><td valign="top">密码：</td><td><input name="password" type="password" size="60"/></td></tr></table><br><center><input type="submit" class="button" name="settingsubmit" value="提 交"></center></form>';
echo yjl_adminhtml($c);
?>