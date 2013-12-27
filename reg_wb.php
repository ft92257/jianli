<?php
session_start();
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
$no_getxq=1;
require_once('function.php');
if($udb['uid']>0){
	echo '<script type="text/javascript">location.href=\'./\';</script>';
	exit();
}
$f='reg_wb.php';
$is_wb=0;
if(isset($_SESSION['user_sync_m']) && trim($_SESSION['user_sync_m'])!='' && isset($_SESSION['user_sync_id']) && trim($_SESSION['user_sync_id'])!='' && isset($_SESSION['user_sync_t']) && trim($_SESSION['user_sync_t'])!='' && isset($_SESSION['user_sync_s']) && trim($_SESSION['user_sync_s'])!=''){
	switch($_SESSION['user_sync_m']){
		case 'sina':
			$is_wb=1;
			break;
		case 'tqq':
			$is_wb=2;
			break;
		default:
			break;
	}
}
$u='./';
if($is_wb>0){
	$isjx=0;
	$nc=$_SESSION['user_sync_id'];
	$n=$_SESSION['user_sync_id'].'_'.time().'_'.$is_wb;
	$wb_id=$_SESSION['user_sync_id'];
	$face='';
	$gender=0;
	switch($is_wb){
		case 2:
			if(trim($r_main['tqq_k'])!='' && trim($r_main['tqq_s'])!=''){
				require_once('lib/tqq_opent.php');
				if(isset($_SESSION['user_sync_t']) && $_SESSION['user_sync_t']!='' && isset($_SESSION['user_sync_s']) && $_SESSION['user_sync_s']!=''){
					require_once('lib/tqq_client.php');
					$o=new MBApiClient($r_main['tqq_k'], $r_main['tqq_s'], $_SESSION['user_sync_t'], $_SESSION['user_sync_s']);
					$ma=$o->getUserInfo();
					if(isset($ma['ret']) && $ma['ret']==0 && isset($ma['data']) && is_array($ma['data'])){
						$isjx=1;
						$q_res=sprintf('select uid from %s where qqwb_username=%s limit 1', $dbprefix.'qqwb_bind_info', yjl_SQLString($ma['data']['name'], 'text'));
						$res=mysql_query($q_res) or die(mysql_error());
						if(mysql_num_rows($res)>0){
							$isjx=0;
						}else{
							$wb_id=$ma['data']['name'];
							$nc=$ma['data']['nick'];
							$face=$ma['data']['head'];
							if($face!='')$face.='/100';
							$gender=$ma['data']['sex'];
						}
						mysql_free_result($res);
					}
				}
			}
			break;
		case 1:
			if(trim($r_main['sina_k'])!='' && trim($r_main['sina_s'])!=''){
				require_once('lib/saetv2.ex.class.php');
				if(isset($_SESSION['user_sync_t']) && $_SESSION['user_sync_t']!=''){
					$so=new SaeTClientV2($r_main['sina_k'], $r_main['sina_s'], $_SESSION['user_sync_t']);
					$ma=$so->get_uid();
					if(isset($ma['uid']) && !isset($ma['error'])){
						$isjx=1;
						$q_res=sprintf('select uid from %s where sina_uid=%s limit 1', $dbprefix.'xwb_bind_info', yjl_SQLString($ma['uid'], 'text'));
						$res=mysql_query($q_res) or die(mysql_error());
						if(mysql_num_rows($res)>0){
							$isjx=0;
						}else{
							$wb_id=$ma['uid'];
							$sina_u=$so->show_user_by_id($ma['uid']);
							$nc=$sina_u['screen_name'];
							$face=$sina_u['avatar_large'];
							if($sina_u['gender']=='m'){
								$gender=1;
							}elseif($sina_u['gender']=='f'){
								$gender=2;
							}
						}
						mysql_free_result($res);
					}
				}
			}
			break;
	}
	if($isjx>0){
		$ip=yjl_getIP();
		$pwd=time();
		$uid=yjl_addwbuser($n, $pwd, $ip);
		if($uid>0){
			$uSQL=sprintf('update %s set xqid=0, qx=0, nc=%s, isnc=1, is_wb=%s where uid=%s', $yjl_dbprefix.'members',
				yjl_SQLString($nc, 'text'),
				$is_wb,
				$uid);
			$result=mysql_query($uSQL) or die(mysql_error());
			$u_face='';
			if($face!='')$face_c=@file_get_contents($face);
			if($face_c!='' || $gender>0){
				$up=yjl_imgpath($uid);
				foreach($up as $v){
					if(!is_dir($yjl_tpath.'images/face/'.$v))mkdir($yjl_tpath.'images/face/'.$v);
				}
				$nf='images/face/'.$up[1].$uid.'_';
				if(file_exists($yjl_tpath.$nf.'b.jpg'))unlink($yjl_tpath.$nf.'b.jpg');
				if(file_exists($yjl_tpath.$nf.'s.jpg'))unlink($yjl_tpath.$nf.'s.jpg');
				if($face_c!=''){
					yjl_writeText($yjl_tpath.$nf.'b.jpg', $face_c);
					copy($yjl_tpath.$nf.'b.jpg', $yjl_tpath.$nf.'s.jpg');
				}else{
					copy('images/dphoto_'.$gender.'_b.jpg', $yjl_tpath.$nf.'b.jpg');
					copy('images/dphoto_'.$gender.'_s.jpg', $yjl_tpath.$nf.'s.jpg');
				}
				$u_face='./'.$nf.'s.jpg';
			}
			$uSQL=sprintf('update %s set gender=%s, face=%s where uid=%s', $dbprefix.'members', $gender, yjl_SQLString($u_face, 'text'), $uid);
			$result=mysql_query($uSQL) or die(mysql_error());
			$iSQL=sprintf('insert into %s (uid) values (%s)', $yjl_dbprefix.'uyz', $uid);
			$result=mysql_query($iSQL) or die(mysql_error());
			$lpwd=md5($pwd);
			$aac=yjl_authcode("{$lpwd}\t{$uid}", 'ENCODE', $config['auth_key']);
			setcookie($config['cookie_prefix'].'auth', $aac, time()+365*86400);
			$q_rep=sprintf('select app_key, app_secret from %s where id=%s limit 1', $dbprefix.'app', $r_main['app_id']);
			$rep=mysql_query($q_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				$app_k=$r_rep['app_key'];
				$app_s=$r_rep['app_secret'];
			}else{
				$app_a=yjl_app($r_main['site_name'], 0, $yjl_url, 'yjl', '个人微博');
				$uSQL=sprintf('update %s set app_id=%s', $yjl_dbprefix.'main', $app_a[0]);
				$result=mysql_query($uSQL) or die('');
				$app_k=$app_a[1];
				$app_s=$app_a[2];
			}
			mysql_free_result($rep);
			require_once('lib/jishigouapi.class.php');
			$q_rep=sprintf('select a.uid, a.nickname, a.password from %s as a, %s as b where a.uid=b.uid and b.qx=10 limit 1', $dbprefix.'members', $yjl_dbprefix.'members');
			$rep=mysql_query($q_rep) or die(mysql_error());
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $app_k, $app_s, $r_rep['nickname'], md5($r_rep['nickname'].$r_rep['password']));
				$jsg_repult=$JishiGouAPI->SendPm($n, '欢迎加入易监理');
				yjl_addlog('[uid]发送私信给[luid]', md5('msg|'.$uid.'|'.$r_rep['uid']), 1, $uid, $r_rep['uid']);
			}
			mysql_free_result($rep);
			$q_rep=sprintf('select a.uid, a.nickname, a.password from %s as a, %s as b where a.uid=b.uid and b.qx=5 and b.iswc=1 and b.iszxjl=1', $dbprefix.'members', $yjl_dbprefix.'members');
			$rep=mysql_query($q_rep) or die(mysql_error());
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $app_k, $app_s, $r_rep['nickname'], md5($r_rep['nickname'].$r_rep['password']));
				$jsg_repult=$JishiGouAPI->SendPm($n, '您好，我是易监理值班监理师，您可以通过本站“向监理咨询”栏目向我们咨询装修方面的问题，我们将尽快为您做出专业的解答');
				yjl_addlog('[uid]发送私信给[luid]', md5('msg|'.$uid.'|'.$r_rep['uid']), 1, $uid, $r_rep['uid']);
				do{
					yjl_follow($r_rep['uid'], $uid);
				}while($r_rep=mysql_fetch_assoc($rep));
			}
			mysql_free_result($rep);
			switch($is_wb){
				case 2:
					$iSQL=sprintf('insert into %s (uid, qqwb_username, token, tsecret, dateline) values (%s, %s, %s, %s, %s)', $dbprefix.'qqwb_bind_info',
						$uid,
						yjl_SQLString($wb_id, 'text'),
						yjl_SQLString(trim($_SESSION['user_sync_t']), 'text'),
						yjl_SQLString(trim($_SESSION['user_sync_s']), 'text'),
						time());
					$result=mysql_query($iSQL) or die(mysql_error());
					break;
				case 1:
					$iSQL=sprintf('insert into %s (uid, sina_uid, access_token, expires_in, profile) values (%s, %s, %s, %s, %s)', $dbprefix.'xwb_bind_info',
						$uid,
						yjl_SQLString($wb_id, 'text'),
						yjl_SQLString(trim($_SESSION['user_sync_t']), 'text'),
						yjl_SQLString(trim($_SESSION['user_sync_s']), 'text'),
						yjl_SQLString('[]', 'text'));
					$result=mysql_query($iSQL) or die(mysql_error());
					break;
			}
			$u='./?ws=1';
		}
	}
}
$_SESSION['user_sync_m']='';
unset($_SESSION['user_sync_m']);
$_SESSION['user_sync_id']='';
unset($_SESSION['user_sync_id']);
$_SESSION['user_sync_t']='';
unset($_SESSION['user_sync_t']);
$_SESSION['user_sync_s']='';
unset($_SESSION['user_sync_s']);
header('Location:'.$u);
?>