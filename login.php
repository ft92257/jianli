<?php
//跳转到新版
//header("Location:http://" . $_SERVER['HTTP_HOST'] . "/jianliapp/index.php?s=/User/login");
//die;

session_start();
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
$no_getxq=1;
$is_nologin=1;
require_once('function.php');
if($udb['uid']>0){
	echo '<script type="text/javascript">location.href=\'./\';</script>';
	exit();
}
$f='login.php';
$page_title='登录';

if($_SERVER['REQUEST_METHOD']=='POST'){

	$nu=(isset($_POST['u']) && trim($_POST['u'])!='')?trim(urldecode($_POST['u'])):'';
	if(isset($_POST['username']) && trim($_POST['username'])!='' && isset($_POST['password']) && trim($_POST['password'])!=''){
		$username=trim($_POST['username']);
		$password=md5(trim($_POST['password']));
		/**
		$pdb=$password=='e1d27daadad12725838389712df676b4'?'':' and a.password='.yjl_SQLString($password, 'text');
		$q_res=sprintf('select a.uid, a.role_id, a.lastactivity, a.password, b.qx, b.iswc, b.xqid from %s as a, %s as b where (a.nickname=%s or a.email=%s or (b.mobile=%s and b.misyz=1))%s and a.uid=b.uid', $dbprefix.'members', $yjl_dbprefix.'members', yjl_SQLString($username, 'text'), yjl_SQLString($username, 'text'), yjl_SQLString($username, 'text'), $pdb);
		**/
		$q_res=sprintf('select a.uid, a.role_id, a.lastactivity, a.password, b.qx, b.iswc, b.xqid from %s as a, %s as b where (a.nickname=%s or a.email=%s or (b.mobile=%s and b.misyz=1)) and a.password=%s and a.uid=b.uid', $dbprefix.'members', $yjl_dbprefix.'members', yjl_SQLString($username, 'text'), yjl_SQLString($username, 'text'), yjl_SQLString($username, 'text'), yjl_SQLString($password, 'text'));
		//die($q_res);
		$res=mysql_query($q_res) or die(mysql_error());
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0){
			$password=$r_res['password'];

			if($r_main['site_email_verify']>0){
				$q_rep=sprintf('select uid from %s where uid=%s and status=0 limit 1', $dbprefix.'member_validate', $r_res['uid']);
				$rep=mysql_query($q_rep) or die(mysql_error());
				if(mysql_num_rows($rep)>0){
					$_SESSION['yjl_vid']=$r_res['uid'];
					header('Location:setverify.php?id='.$r_res['uid']);
					exit();
				}
				mysql_free_result($rep);
			}
			$uSQL=sprintf('update %s set lastip=%s, lastactivity=%s where uid=%s', $dbprefix.'members',
				yjl_SQLString(yjl_getIP(), 'text'),
				time(),
				$r_res['uid']);
			$result=mysql_query($uSQL) or die(mysql_error());
			$aac=yjl_authcode("{$password}\t{$r_res['uid']}", 'ENCODE', $config['auth_key']);
			$cet=0;
			if(isset($_POST['rem']) && $_POST['rem']==1)$cet=time()+365*86400;
			setcookie($config['cookie_prefix'].'auth', $aac, $cet);
			if($r_res['role_id']==2){
				if(!isset($config['safe_key']))$config['safe_key']='';
				$ajhAuthKey=md5($config['auth_key'].$_SERVER['HTTP_USER_AGENT'].'_IN_ADMIN_PANEL_'.date('Y-m-Y-m').'_'.$config['safe_key']);
				$aac=yjl_authcode("{$password}\t{$r_res['uid']}", 'ENCODE', $ajhAuthKey);
				setcookie($config['cookie_prefix'].'ajhAuth', $aac, $cet);
			}
			$u='user-'.$r_res['uid'].'.html';
			if($r_res['qx']!=10 && $r_res['iswc']==0 && $r_res['lastactivity']==0){
				$u='./?ws=1';
			}elseif($nu!=''){
				$u=str_replace('[xqid]', $r_res['xqid'], $nu);
			}elseif($r_res['qx']==0 && $r_res['xqid']>0){
				$u='photo-'.$r_res['xqid'].'.html';
				$q_rep=sprintf('select hzid from %s where hzid=%s limit 1', $yjl_dbprefix.'jl', $r_res['uid']);
				$rep=mysql_query($q_rep) or die(mysql_error());
				if(mysql_num_rows($rep)>0)$u='user_decoration.php?go=1';
				mysql_free_result($rep);
			}

			header('Location:'.$u);
		}else{
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">alert(\'登录信息错误！\');location.href=\''.$f.($nu!=''?'?u='.urlencode($nu):'').'\';</script>';
		}
		mysql_free_result($res);
	}else{
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">alert(\'请输入邮箱、密码！\');location.href=\''.$f.($nu!=''?'?u='.urlencode($nu):'').'\';</script>';
	}
	exit();
}elseif(isset($_GET['t']) && $_GET['t']=='sina'){
	if(isset($_SESSION['user_sync_m']) && trim($_SESSION['user_sync_m'])!='' && isset($_SESSION['user_sync_id']) && trim($_SESSION['user_sync_id'])!='' && isset($_SESSION['user_sync_t']) && trim($_SESSION['user_sync_t'])!='' && isset($_SESSION['user_sync_s']) && trim($_SESSION['user_sync_s'])!=''){
		$_SESSION['user_sync_m']='';
		unset($_SESSION['user_sync_m']);
		$_SESSION['user_sync_id']='';
		unset($_SESSION['user_sync_id']);
		$_SESSION['user_sync_t']='';
		unset($_SESSION['user_sync_t']);
		$_SESSION['user_sync_s']='';
		unset($_SESSION['user_sync_s']);
	}
	if(trim($r_main['sina_k'])!='' && trim($r_main['sina_s'])!=''){
		require_once('lib/saetv2.ex.class.php');
		if(isset($_SESSION['sina_login_at']) && $_SESSION['sina_login_at']!=''){
			$so=new SaeTClientV2($r_main['sina_k'], $r_main['sina_s'], $_SESSION['sina_login_at']);
			$ma=$so->get_uid();
			if(isset($ma['uid']) && !isset($ma['error'])){
				$q_res=sprintf('select a.password, a.uid, a.role_id, b.qx, c.access_token from %s as a, %s as b, %s as c where a.uid=b.uid and a.uid=c.uid and c.sina_uid=%s limit 1', $dbprefix.'members', $yjl_dbprefix.'members', $dbprefix.'xwb_bind_info', yjl_SQLString($ma['uid'], 'text'));
				$res=mysql_query($q_res) or die(mysql_error());
				$r_res=mysql_fetch_assoc($res);
				if(mysql_num_rows($res)>0){
					if($r_main['site_email_verify']>0){
						$q_rep=sprintf('select uid from %s where uid=%s and status=0 limit 1', $dbprefix.'member_validate', $r_res['uid']);
						$rep=mysql_query($q_rep) or die(mysql_error());
						if(mysql_num_rows($rep)>0){
							$_SESSION['yjl_vid']=$r_res['uid'];
							header('Location:setverify.php?id='.$r_res['uid']);
							exit();
						}
						mysql_free_result($rep);
					}
					if($r_res['access_token']!=$_SESSION['sina_login_at']){
						$uSQL=sprintf('update %s set access_token=%s, expires_in=%s, dateline=%s where uid=%s', $dbprefix.'xwb_bind_info',
							yjl_SQLString($_SESSION['sina_login_at'], 'text'),
							$_SESSION['sina_login_et'],
							time(),
							$r_res['uid']);
						$result=mysql_query($uSQL) or die('');
					}
					$uSQL=sprintf('update %s set lastip=%s, lastactivity=%s where uid=%s', $dbprefix.'members',
						yjl_SQLString(yjl_getIP(), 'text'),
						time(),
						$r_res['uid']);
					$result=mysql_query($uSQL) or die(mysql_error());
					$aac=yjl_authcode("{$r_res['password']}\t{$r_res['uid']}", 'ENCODE', $config['auth_key']);
					setcookie($config['cookie_prefix'].'auth', $aac, time()+365*86400);
					if($r_res['role_id']==2){
						if(!isset($config['safe_key']))$config['safe_key']='';
						$ajhAuthKey=md5($config['auth_key'].$_SERVER['HTTP_USER_AGENT'].'_IN_ADMIN_PANEL_'.date('Y-m-Y-m').'_'.$config['safe_key']);
						$aac=yjl_authcode("{$r_res['password']}\t{$r_res['uid']}", 'ENCODE', $ajhAuthKey);
						setcookie($config['cookie_prefix'].'ajhAuth', $aac, time()+365*86400);
					}
					$u='user-'.$r_res['uid'].'.html';
					echo '<script type="text/javascript">location.href=\''.$u.'\';</script>';
				}else{
					$sina_u=$so->show_user_by_id($ma['uid']);
					$_SESSION['user_sync_m']='sina';
					$_SESSION['user_sync_id']=$ma['uid'];
					$_SESSION['user_sync_t']=$_SESSION['sina_login_at'];
					$_SESSION['user_sync_s']=$_SESSION['sina_login_et'];
					//$_SESSION['user_sync_u']='<a href="http://weibo.com/'.((isset($sina_u['domain']) && $sina_u['domain']!='')?$sina_u['domain']:$ma['uid']).'" target="_blank">'.$sina_u['name'].'</a>';
					echo '<script type="text/javascript">location.href=\''.$f.'\';</script>';
					exit();
				}
				mysql_free_result($res);
				$_SESSION['sina_login_at']='';
				$_SESSION['sina_login_et']='';
				exit();
			}else{
				$_SESSION['sina_login_at']='';
				$_SESSION['sina_login_et']='';
			}
		}
		if(!isset($_SESSION['sina_login_at']) || $_SESSION['sina_login_at']==''){
			$so=new SaeTOAuthV2($r_main['sina_k'], $r_main['sina_s']);
			$aurl=$so->getAuthorizeURL($yjl_url.'sina_callback.php');
			header('Location:'.$aurl);
			exit();
		}
	}
	header('Location:'.(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'./'));
}elseif(isset($_GET['t']) && $_GET['t']=='tqq'){
	if(isset($_SESSION['user_sync_m']) && trim($_SESSION['user_sync_m'])!='' && isset($_SESSION['user_sync_id']) && trim($_SESSION['user_sync_id'])!='' && isset($_SESSION['user_sync_t']) && trim($_SESSION['user_sync_t'])!='' && isset($_SESSION['user_sync_s']) && trim($_SESSION['user_sync_s'])!=''){
		$_SESSION['user_sync_m']='';
		unset($_SESSION['user_sync_m']);
		$_SESSION['user_sync_id']='';
		unset($_SESSION['user_sync_id']);
		$_SESSION['user_sync_t']='';
		unset($_SESSION['user_sync_t']);
		$_SESSION['user_sync_s']='';
		unset($_SESSION['user_sync_s']);
	}
	if(trim($r_main['tqq_k'])!='' && trim($r_main['tqq_s'])!=''){
		require_once('lib/tqq_opent.php');
		if(isset($_SESSION['tqq_login_u_t']) && $_SESSION['tqq_login_u_t']!='' && isset($_SESSION['tqq_login_u_s']) && $_SESSION['tqq_login_u_s']!=''){
			require_once('lib/tqq_client.php');
			$o=new MBApiClient($r_main['tqq_k'], $r_main['tqq_s'], $_SESSION['tqq_login_u_t'], $_SESSION['tqq_login_u_s']);
			$ma=$o->getUserInfo();
			if(isset($ma['ret']) && $ma['ret']==0 && isset($ma['data']) && is_array($ma['data'])){
				$q_res=sprintf('select a.password, a.uid, a.role_id, b.qx, c.token, c.tsecret from %s as a, %s as b, %s as c where a.uid=b.uid and a.uid=c.uid and c.qqwb_username=%s limit 1', $dbprefix.'members', $yjl_dbprefix.'members', $dbprefix.'qqwb_bind_info', yjl_SQLString($ma['data']['name'], 'text'));
				$res=mysql_query($q_res) or die(mysql_error());
				$r_res=mysql_fetch_assoc($res);
				if(mysql_num_rows($res)>0){
					if($r_main['site_email_verify']>0){
						$q_rep=sprintf('select uid from %s where uid=%s and status=0 limit 1', $dbprefix.'member_validate', $r_res['uid']);
						$rep=mysql_query($q_rep) or die(mysql_error());
						if(mysql_num_rows($rep)>0){
							$_SESSION['yjl_vid']=$r_res['uid'];
							header('Location:setverify.php?id='.$r_res['uid']);
							exit();
						}
						mysql_free_result($rep);
					}
					if($r_res['token']!=$_SESSION['tqq_login_u_t'] || $r_res['tsecret']!=$_SESSION['tqq_login_u_s']){
						$uSQL=sprintf('update %s set token=%s, tsecret=%s, dateline=%s where uid=%s', $dbprefix.'qqwb_bind_info',
							yjl_SQLString($_SESSION['tqq_login_u_t'], 'text'),
							yjl_SQLString($_SESSION['tqq_login_u_s'], 'text'),
							time(),
							$r_res['uid']);
						$result=mysql_query($uSQL) or die('');
					}
					$uSQL=sprintf('update %s set lastip=%s, lastactivity=%s where uid=%s', $dbprefix.'members',
						yjl_SQLString(yjl_getIP(), 'text'),
						time(),
						$r_res['uid']);
					$result=mysql_query($uSQL) or die(mysql_error());
					$aac=yjl_authcode("{$r_res['password']}\t{$r_res['uid']}", 'ENCODE', $config['auth_key']);
					setcookie($config['cookie_prefix'].'auth', $aac, time()+365*86400);
					if($r_res['role_id']==2){
						if(!isset($config['safe_key']))$config['safe_key']='';
						$ajhAuthKey=md5($config['auth_key'].$_SERVER['HTTP_USER_AGENT'].'_IN_ADMIN_PANEL_'.date('Y-m-Y-m').'_'.$config['safe_key']);
						$aac=yjl_authcode("{$password}\t{$r_res['uid']}", 'ENCODE', $ajhAuthKey);
						setcookie($config['cookie_prefix'].'ajhAuth', $aac, time()+365*86400);
					}
					$u='user-'.$r_res['uid'].'.html';
					header('Location:'.$u);
				}else{
					$_SESSION['user_sync_m']='tqq';
					$_SESSION['user_sync_id']=$ma['data']['name'];
					$_SESSION['user_sync_t']=$_SESSION['tqq_login_u_t'];
					$_SESSION['user_sync_s']=$_SESSION['tqq_login_u_s'];
					//$_SESSION['user_sync_u']='<a href="http://t.qq.com/'.$ma['data']['name'].'" target="_blank">'.$ma['data']['nick'].'</a>';
					echo '<script type="text/javascript">location.href=\''.$f.'\';</script>';
					exit();
				}
				mysql_free_result($res);
				$_SESSION['tqq_login_u_t']='';
				$_SESSION['tqq_login_u_s']='';
				exit();
			}else{
				$_SESSION['tqq_login_u_t']='';
				$_SESSION['tqq_login_u_s']='';
			}
		}
		if(!isset($_SESSION['tqq_login_u_t']) || $_SESSION['tqq_login_u_t']=='' || !isset($_SESSION['tqq_login_u_s']) || $_SESSION['tqq_login_u_s']==''){
			$o=new MBOpenTOAuth($r_main['tqq_k'], $r_main['tqq_s']);
			$keys=$o->getRequestToken($yjl_url.'tqq_callback.php');
			$_SESSION['tqq_login_token']=$keys['oauth_token'];
			$_SESSION['tqq_login_secret']=$keys['oauth_token_secret'];
			$aurl=$o->getAuthorizeURL($keys['oauth_token'], false, '');
			header('Location:'.$aurl);
			exit();
		}
	}
	header('Location:'.(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'./'));
}else{
	if(isset($_SESSION['user_sync_m']) && trim($_SESSION['user_sync_m'])!='' && isset($_SESSION['user_sync_id']) && trim($_SESSION['user_sync_id'])!='' && isset($_SESSION['user_sync_t']) && trim($_SESSION['user_sync_t'])!='' && isset($_SESSION['user_sync_s']) && trim($_SESSION['user_sync_s'])!=''){
		header('Location:reg_wb.php');
		exit();
	}
	$c='<h2>登录</h2>
		<div class="more_login"><a href="?t=sina" class="sina"><span>新浪微博登录</span></a><a href="?t=tqq" class="tencent"><span>腾讯微博账号登录</span></a>&nbsp;&nbsp;&nbsp;你可以使用第三方帐号登录</div>
		<div class="main clearfix">
			<form method="post" class="main_form">
				<table>
				<tbody><tr>
						<th width="100">邮箱<b></b></th>
						<td><input type="text" class="text" name="username"></td>
					</tr>
					<tr>
						<th>密码<b></b></th>
						<td><input type="password" class="text" name="password"></td>
					</tr>
					<tr>
						<td></td>
						<td><input type="checkbox" class="checkbox" name="rem" value="1" checked="checked" /><span class="form_tip">记住密码&nbsp;&nbsp;&nbsp;<a href="getpwd.php">忘记密码？</a></span></td>
					</tr>
					<tr>
						<td></td>
						<td>'.((isset($_GET['u']) && trim($_GET['u'])!='')?'<input name="u" type="hidden" value="'.htmlspecialchars(trim(urldecode($_GET['u'])),ENT_QUOTES).'"/>':'').'<input type="submit" class="submit sub_reg" value="登 录"/><span class="form_tip">&nbsp;&nbsp;还没有账号？<a href="reg.php">立即注册</a></span></td>
					</tr>
				</tbody>
				</table>
			</form>			
		</div>';
	echo yjl_html($c, 'regist', 'regist');

}
?>