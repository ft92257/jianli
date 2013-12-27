<?php
session_start();
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once('function.php');
$u='./';
if(trim($r_main['sina_k'])!='' && trim($r_main['sina_s'])!=''){
	if($udb['uid']>0){
		$u='profile.php?sync=1';
	}else{
		$u='login.php?t=sina';
	}
	require_once('lib/saetv2.ex.class.php');
	$so=new SaeTOAuthV2($r_main['sina_k'], $r_main['sina_s']);
	if(isset($_REQUEST['code'])){
		$keys=array();
		$keys['code']=$_REQUEST['code'];
		$keys['redirect_uri']=$yjl_url.'sina_callback.php';
		try{
			$token=$so->getAccessToken('code', $keys) ;
		}catch(OAuthException $e){
		}
		if(isset($token['access_token']) && trim($token['access_token'])!=''){
			if($udb['uid']>0){
				$isjx=1;
				$q_res=sprintf('select a.uid from %s as a, %s as b where a.uid=b.uid and a.is_wb=1 and b.sina_uid=%s limit 1', $yjl_dbprefix.'members', $dbprefix.'xwb_bind_info', yjl_SQLString($token['uid'], 'text'));
				$res=mysql_query($q_res) or die(mysql_error());
				$r_res=mysql_fetch_assoc($res);
				if(mysql_num_rows($res)>0){
					if($r_res['uid']!=$udb['uid']){
						echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">alert(\'已有用户绑定此新浪微博账号。\');</script>';
						$isjx=0;
					}
				}
				mysql_free_result($res);
				if($isjx>0){
					$dSQL=sprintf('delete from %s where sina_uid=%s and uid<>%s', $dbprefix.'xwb_bind_info', yjl_SQLString($token['uid'], 'text'), $udb['uid']);
					$result=mysql_query($dSQL) or die('');
					$q_rex=sprintf('select uid from %s where uid=%s limit 1', $dbprefix.'xwb_bind_info', $udb['uid']);
					$rex=mysql_query($q_rex) or die('');
					if(mysql_num_rows($rex)>0){
						$uSQL=sprintf('update %s set sina_uid=%s, access_token=%s, expires_in=%s, dateline=%s where uid=%s', $dbprefix.'xwb_bind_info',
							yjl_SQLString($token['uid'], 'text'),
							yjl_SQLString($token['access_token'], 'text'),
							$token['expires_in'],
							time(),
							$udb['uid']);
						$result=mysql_query($uSQL) or die('');
					}else{
						$iSQL=sprintf('insert into %s (uid, sina_uid, access_token, expires_in, profile, dateline) values (%s, %s, %s, %s, %s, %s)', $dbprefix.'xwb_bind_info',
							$udb['uid'],
							yjl_SQLString($token['uid'], 'text'),
							yjl_SQLString($token['access_token'], 'text'),
							$token['expires_in'],
							yjl_SQLString('[]', 'text'),
							time());
						$result=mysql_query($iSQL) or die('');
					}
					mysql_free_result($rex);
				}
			}else{
				$_SESSION['sina_login_uid']=$token['uid'];
				$_SESSION['sina_login_at']=$token['access_token'];
				$_SESSION['sina_login_et']=$token['expires_in'];
			}
		}
	}
}
echo '<script type="text/javascript">location.href=\''.$u.'\';</script>';
?>