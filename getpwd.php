<?php
session_start();
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
$no_getxq=1;
require_once('function.php');
$f='getpwd.php';
$seid=md5($f);
if($udb['uid']>0){
	echo '<script type="text/javascript">location.href=\'./\';</script>';
	exit();
}
$yxts=3;
if(isset($_GET['c']) && trim($_GET['c'])!=''){
	$page_title='重设密码';
	$code=htmlspecialchars(trim($_GET['c']),ENT_QUOTES);
	$mc='';
	$q_res=sprintf('select a.uid, b.status, b.role_id from %s as a, %s as b where a.pwdrcode=%s and a.pwdrdate>%s and a.uid=b.uid', $yjl_dbprefix.'members', $dbprefix.'member_validate', yjl_SQLString($code, 'text'), (time()-86400*$yxts));
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		if($_SERVER['REQUEST_METHOD']=='POST'){
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			if(isset($_POST['npwd']) && trim($_POST['npwd'])!='' && isset($_POST['npwd_c']) && trim($_POST['npwd_c'])==trim($_POST['npwd'])){
				$npwd=htmlspecialchars(trim($_POST['npwd']),ENT_QUOTES);
				$password=md5($npwd);
				$uSQL=sprintf('update %s set password=%s where uid=%s', $dbprefix.'members', yjl_SQLString($password, 'text'), $r_res['uid']);
				$result=mysql_query($uSQL) or die('');
				$uSQL=sprintf('update %s set pwdrcode=%s, pwdrdate=0 where uid=%s', $yjl_dbprefix.'members', yjl_SQLString('', 'text'), $r_res['uid']);
				$result=mysql_query($uSQL) or die('');
				if($r_res['status']==0){
					$uSQL=sprintf('update %s set verify_time=%s, status=1 where uid=%s', $dbprefix.'member_validate', time(), $r_res['uid']);
					$result=mysql_query($uSQL) or die('');
					$uSQL=sprintf('update %s set role_id=%s where uid=%s', $dbprefix.'members', $r_res['role_id'], $r_res['uid']);
					$result=mysql_query($uSQL) or die('');
				}
				$_SESSION[$seid]=2;
				echo '<script type="text/javascript">location.href=\''.$f.'\';</script>';
				exit();
			}elseif(!isset($_POST['npwd_c']) || trim($_POST['npwd_c'])!=trim($_POST['npwd'])){
				echo '<script type="text/javascript">alert(\'确认密码必须和新密码一样。\');</script>';
			}else{
				echo '<script type="text/javascript">alert(\'请输入新密码。\');</script>';
			}
			echo '<script type="text/javascript">location.href=\''.$f.'?c='.$code.'\';</script>';
			exit();
		}
		$mc.='<form method="post" action="" class="main_form" name="form1" onsubmit="if(document.form1.npwd.value==\'\'){alert(\'请输入新密码。\');return false;}else if(document.form1.npwd_c.value!=document.form1.npwd.value){alert(\'确认密码必须和新密码一样。\');return false;}">
				<table>
				<tbody>
					<tr>
					  <th width="100"></th>
					  <td><span class="form_tip" style="margin:0;">请输入新密码</span></td>
					</tr>
					<tr>
					  <th>新密码</th>
					  <td><input type="password" class="text" name="npwd"></td>
					</tr>
					<tr>
					  <th>确　认</th>
					  <td><input type="password" class="text" name="npwd_c"></td>
					</tr>
					<tr>
					  <td></td>
					  <td><input type="submit" class="submit sub_reg" value="重 设"/></td>
					</tr>
				</tbody>
				</table>
			  </form>';
	}else{
		$mc.='<span class="form_tip" style="margin:0 0 0 100px;">重设密码信息已过期或者不符合要求</span>';
	}
	mysql_free_result($res);
	$c='<h2>重设密码</h2><div class="main clearfix">'.$mc.'</div>';
}else{
	$page_title='找回密码';
	$mc='';
	if(isset($_SESSION[$seid]) && $_SESSION[$seid]==2){
		$mc.='<span class="form_tip" style="margin:0 0 0 100px;">密码已重设，您可以使用新密码登录'.$r_main['site_name'].'</span>';
		unset($_SESSION[$seid]);
	}elseif(isset($_SESSION[$seid]) && $_SESSION[$seid]==1){
		$mc.='<span class="form_tip" style="margin:0 0 0 100px;">重设密码的邮件已发送到您的邮箱中，按其中的介绍操作即可重设密码</span>';

		//delete
		if($yjl_isdebug>0 && isset($_SESSION[$seid.'_code'])){
			$mc.='<br/><span style="margin:0 100px;">本地测试，没办法发邮件，<a href="?c='.$_SESSION[$seid.'_code'].'">点击重设密码</a></span>';
			unset($_SESSION[$seid.'_code']);
		}
		unset($_SESSION[$seid]);
	}else{
		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(isset($_POST['email']) && trim($_POST['email'])!=''){
				$email=htmlspecialchars(trim($_POST['email']),ENT_QUOTES);
				$q_res=sprintf('select a.uid, b.pwdrcode, b.pwdrdate from %s as a, %s as b where a.email=%s and a.uid=b.uid limit 1', $dbprefix.'members', $yjl_dbprefix.'members', yjl_SQLString($email, 'text'));
				echo $q_res;
				$res=mysql_query($q_res) or die('');
				$r_res=mysql_fetch_assoc($res);
				if(mysql_num_rows($res)>0){
					if($r_res['pwdrdate']<=(time()-86400*$yxts) || $r_res['pwdrcode']=='')$r_res['pwdrcode']=md5($email.'|'.time().'|'.rand(0, 9999));
					$uSQL=sprintf('update %s set pwdrcode=%s, pwdrdate=%s where uid=%s', $yjl_dbprefix.'members',
						yjl_SQLString($r_res['pwdrcode'], 'text'),
						time(),
						$r_res['uid']);
					$result=mysql_query($uSQL) or die('');
					$ec="您好：
您收到这封邮件，是因为在“".$r_main['site_name']."”网站的用户注册中使用了该邮箱地址
且用户请求使用找回密码功能所致。

如果您没有进行上述操作，请忽略这封邮件。您不需要退订或进行其他进一步的操作。
------------------------------------------------------
重设密码说明：
如果是您发起了找回密码申请，请在".$yxts."天之内，通过点击下面的链接重设您的密码：
".$yjl_url.$f."?c=".$r_res['pwdrcode']."

(如果上面不是链接形式，请将地址手工粘贴到浏览器地址栏再访问)
上面的页面打开后，输入新的密码后提交，之后您即可使用新的密码登录".$r_main['site_name']."了。您可以在个人中心中随时修改您的密码。
本请求提交者的 IP 为：".yjl_getIP()."

感谢您的访问，祝您使用愉快！

此致，
".$r_main['site_name']." 管理团队.
".$yjl_url."
";
					if($yjl_isdebug==0){
						require_once('lib/smtp.php');
						yjl_mail($email, $r_main['site_name'].' 找回密码', $ec);
					}
					$_SESSION[$seid]=1;

					//delete
					if($yjl_isdebug>0)$_SESSION[$seid.'_code']=$r_res['pwdrcode'];
				}else{
					echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">alert(\'没有用户使用此邮箱地址。\');</script>';
				}
				mysql_free_result($res);
			}
			echo '<script type="text/javascript">location.href=\''.$f.'\';</script>';
			exit();
		}
		$mc.='<form method="post" action="" class="main_form">
				<table>
				<tbody>
					<tr>
					  <th width="100"></th>
					  <td><span class="form_tip" style="margin:0;">请输入你的注册邮箱，重设密码的邮件会发送到您的邮箱中，按其中的介绍操作<br/>即可重设密码</span></td>
					</tr>
					<tr>
					  <th></th>
					  <td><input type="text" class="text" name="email"></td>
					</tr>
					<tr>
					  <td></td>
					  <td><input type="submit" class="submit sub_reg" value="发 送"/></td>
					</tr>
				</tbody>
				</table>
			  </form>';
	}
	$c='<h2>找回密码</h2><div class="main clearfix">'.$mc.'</div>';
}
echo yjl_html($c, 'regist', 'regist');
?>