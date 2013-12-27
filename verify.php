<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
$no_getxq=1;
require_once('function.php');
$f='verify.php';
if($udb['uid']>0){
	echo '<script type="text/javascript">location.href=\'./\';</script>';
	exit();
}
$id=(isset($_GET['uid']) && intval($_GET['uid'])>0)?intval($_GET['uid']):1;
$vc=(isset($_GET['key']) && trim($_GET['key'])!='')?htmlspecialchars(trim($_GET['key']),ENT_QUOTES):'';
if($vc!='' && strlen($vc)==16){
	$q_res=sprintf('select a.uid, a.password, a.nickname, b.status, b.role_id, c.qx from %s as a, %s as b, %s as c where a.uid=b.uid and a.uid=c.uid and a.uid=%s and b.`key`=%s', $dbprefix.'members', $dbprefix.'member_validate', $yjl_dbprefix.'members', $id, yjl_SQLString($vc, 'text'));
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		if($r_res['status']==0){
			$uSQL=sprintf('update %s set verify_time=%s, status=1 where uid=%s', $dbprefix.'member_validate', time(), $id);
			$result=mysql_query($uSQL) or die('');
			$uSQL=sprintf('update %s set role_id=%s where uid=%s', $dbprefix.'members', $r_res['role_id'], $id);
			$result=mysql_query($uSQL) or die('');
		}
		$aac=yjl_authcode("{$r_res['password']}\t{$r_res['uid']}", 'ENCODE', $config['auth_key']);
		setcookie($config['cookie_prefix'].'auth', $aac, time()+365*86400);
		if($r_res['role_id']==2){
			if(!isset($config['safe_key']))$config['safe_key']='';
			$ajhAuthKey=md5($config['auth_key'].$_SERVER['HTTP_USER_AGENT'].'_IN_ADMIN_PANEL_'.date('Y-m-Y-m').'_'.$config['safe_key']);
			$aac=yjl_authcode("{$r_res['password']}\t{$r_res['uid']}", 'ENCODE', $ajhAuthKey);
			setcookie($config['cookie_prefix'].'ajhAuth', $aac, time()+365*86400);
		}
		if($r_res['qx']==0){
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
				$jsg_repult=$JishiGouAPI->SendPm($r_res['nickname'], '欢迎加入易监理');
				yjl_addlog('[uid]发送私信给[luid]', md5('msg|'.$r_res['uid'].'|'.$r_rep['uid']), 1, $r_res['uid'], $r_rep['uid']);
			}
			mysql_free_result($rep);
			$q_rep=sprintf('select a.uid, a.nickname, a.password from %s as a, %s as b where a.uid=b.uid and b.qx=5 and b.iswc=1 and b.iszxjl=1', $dbprefix.'members', $yjl_dbprefix.'members');
			$rep=mysql_query($q_rep) or die(mysql_error());
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $app_k, $app_s, $r_rep['nickname'], md5($r_rep['nickname'].$r_rep['password']));
				$jsg_repult=$JishiGouAPI->SendPm($r_res['nickname'], '您好，我是易监理值班监理师，您可以通过本站“向监理咨询”栏目向我们咨询装修方面的问题，我们将尽快为您做出专业的解答');
				yjl_addlog('[uid]发送私信给[luid]', md5('msg|'.$r_res['uid'].'|'.$r_rep['uid']), 1, $r_res['uid'], $r_rep['uid']);
				do{
					yjl_follow($r_rep['uid'], $r_res['uid']);
				}while($r_rep=mysql_fetch_assoc($rep));
			}
			mysql_free_result($rep);
		}
		header('Location:./?ws=1');
		exit();
	}else{
		$mc='验证已过期或者验证信息不符合要求';
	}
	mysql_free_result($res);
}elseif($vc==''){
	$mc='验证字符串不能为空';
}else{
	$mc='验证字符长度不符合标准，请检查';
}
$c='<h2>注册验证</h2><div class="main clearfix"><span class="form_tip" style="margin:0 0 0 100px;">'.$mc.'</span></div>';
echo yjl_html($c, 'regist', 'regist');
?>