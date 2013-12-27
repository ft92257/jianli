<?php
session_start();
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once('function.php');
$u='./';
if(trim($r_main['tqq_k'])!='' && trim($r_main['tqq_s'])!=''){
	if($udb['uid']>0){
		$u='profile.php?sync=1';
		$token=$_SESSION['tqq_token'];
		$secret=$_SESSION['tqq_secret'];
		$_SESSION['tqq_token']='';
		unset($_SESSION['tqq_token']);
		$_SESSION['tqq_secret']='';
		unset($_SESSION['tqq_secret']);
	}else{
		$u='login.php?t=tqq';
		$token=$_SESSION['tqq_login_token'];
		$secret=$_SESSION['tqq_login_secret'];
		$_SESSION['tqq_login_token']='';
		unset($_SESSION['tqq_login_token']);
		$_SESSION['tqq_login_secret']='';
		unset($_SESSION['tqq_login_secret']);
	}
	if($token!='' && $secret!=''){
		require_once('lib/tqq_opent.php');
		$o=new MBOpenTOAuth($r_main['tqq_k'], $r_main['tqq_s'], $token, $secret);
		$last_key=$o->getAccessToken($_REQUEST['oauth_verifier']);
		if($last_key['oauth_token']!='' && $last_key['oauth_token_secret']!='' && $last_key['name']!=''){
			if($udb['uid']>0){
				$isjx=1;
				$q_res=sprintf('select a.uid from %s as a, %s as b where a.uid=b.uid and a.is_wb=2 and b.qqwb_username=%s limit 1', $yjl_dbprefix.'members', $dbprefix.'qqwb_bind_info', yjl_SQLString($last_key['name'], 'text'));
				$res=mysql_query($q_res) or die(mysql_error());
				$r_res=mysql_fetch_assoc($res);
				if(mysql_num_rows($res)>0){
					if($r_res['uid']!=$udb['uid']){
						echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">alert(\'已有用户绑定此腾讯微博账号。\');</script>';
						$isjx=0;
					}
				}
				mysql_free_result($res);
				if($isjx>0){
					$dSQL=sprintf('delete from %s where qqwb_username=%s and uid<>%s', $dbprefix.'qqwb_bind_info', yjl_SQLString($last_key['name'], 'text'), $udb['uid']);
					$result=mysql_query($dSQL) or die('');
					$q_rex=sprintf('select uid from %s where uid=%s limit 1', $dbprefix.'qqwb_bind_info', $udb['uid']);
					$rex=mysql_query($q_rex) or die('');
					if(mysql_num_rows($rex)>0){
						$uSQL=sprintf('update %s set qqwb_username=%s, token=%s, tsecret=%s where uid=%s', $dbprefix.'qqwb_bind_info',
							yjl_SQLString($last_key['name'], 'text'),
							yjl_SQLString($last_key['oauth_token'], 'text'),
							yjl_SQLString($last_key['oauth_token_secret'], 'text'),
							$udb['uid']);
						$result=mysql_query($uSQL) or die('');
					}else{
						$iSQL=sprintf('insert into %s (uid, qqwb_username, token, tsecret, dateline) values (%s, %s, %s, %s, %s)', $dbprefix.'qqwb_bind_info',
							$udb['uid'],
							yjl_SQLString($last_key['name'], 'text'),
							yjl_SQLString($last_key['oauth_token'], 'text'),
							yjl_SQLString($last_key['oauth_token_secret'], 'text'),
							time());
						$result=mysql_query($iSQL) or die('');
					}
					mysql_free_result($rex);
				}
			}else{
				$_SESSION['tqq_login_u_t']=$last_key['oauth_token'];
				$_SESSION['tqq_login_u_s']=$last_key['oauth_token_secret'];
			}
		}
	}
}
echo '<script type="text/javascript">location.href=\''.$u.'\';</script>';
?>