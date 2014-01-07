<?php
session_start();
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once('function.php');
$f='setverify.php';
if($udb['uid']>0){
	echo '<script type="text/javascript">location.href=\'./\';</script>';
	exit();
}
if($r_main['site_email_verify']==0){
	echo '<script type="text/javascript">location.href=\'./\';</script>';
	exit();
}
$a_eu=array(
	'163.com'=>'mail.163.com',
	'vip.163.com'=>'vip.163.com/?b08abh1',
	'sina.com'=>'mail.sina.com.cn',
	'sina.cn'=>'mail.sina.com.cn/cnmail/index.html',
	'vip.sina.com'=>'vip.sina.com.cn',
	'2008.sina.com'=>'mail.2008.sina.com.cn',
	'sohu.com'=>'mail.sohu.com',
	'vip.sohu.com'=>'vip.sohu.com',
	'tom.com'=>'mail.tom.com',
	'vip.tom.com'=>'vip.tom.com',
	'sogou.com'=>'mail.sogou.com',
	'126.com'=>'www.126.com',
	'vip.126.com'=>'vip.126.com/?b09abh1',
	'139.com'=>'mail.10086.cn',
	'gmail.com'=>'www.google.com/accounts/ServiceLogin?service=mail',
	'hotmail.com'=>'www.hotmail.com',
	'189.cn'=>'webmail2.189.cn/webmail/',
	'qq.com'=>'mail.qq.com/cgi-bin/loginpage',
	'yahoo.com'=>'mail.cn.yahoo.com',
	'yahoo.cn'=>'mail.cn.yahoo.com',
	'yahoo.com.cn'=>'mail.cn.yahoo.com',
	'21cn.com'=>'mail.21cn.com',
	'eyou.com'=>'www.eyou.com',
	'188.com'=>'www.188.com',
	'yeah.net'=>'www.yeah.net',
	'foxmail.com'=>'mail.qq.com/cgi-bin/loginpage?t=fox_loginpage',
	'wo.com.cn'=>'mail.wo.com.cn/smsmail/login.html',
	'263.net'=>'www.263.net',
	'x263.net'=>'www.263.net',
	'263.net.cn'=>'www.263.net'
);
$id=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
$q_res=sprintf('select a.email, b.`key` as vkey from %s as a, %s as b where a.uid=b.uid and a.uid=%s and b.status=0', $dbprefix.'members', $dbprefix.'member_validate', $id);
$res=mysql_query($q_res) or die('');
$r_res=mysql_fetch_assoc($res);
if(mysql_num_rows($res)>0){
	$is_br=(isset($_SESSION['yjl_vid']) && $_SESSION['yjl_vid']==$id)?1:0;
	$eu=yjl_emailurl($r_res['email']);
	$c='<h2>查收确认信</h2>
		<div class="main clearfix">
			<div class="find_emil">
			确认信已经发到你的邮箱'.($is_br>0?' '.$r_res['email']:'').'，<br />
			你需要点击邮件中的确认链接来完成注册。<br />
			<a href="'.$eu.'" class="btn bt_wdgray" target="_blank">去邮箱查收确认信 </a>
			</div>
			<div class="not_find">
				<b>没有收到确认信怎么办？</b>
				<p>尝试到广告邮件垃圾邮件目录里找找看';
	if($is_br>0){
		if(isset($_GET['send']) && $_GET['send']==1){
			if($yjl_isdebug==0){
				require_once('lib/smtp.php');
				yjl_vmail($id, $r_res['email'], $r_res['vkey']);
			}
			header("Content-type:text/html;charset=utf-8");
			echo '<script type="text/javascript">alert(\'确认邮件已经发送到你的邮箱!\');location.href=\''.$f.'?id='.$id.'\';</script>';
			exit();
		}
		if(isset($_POST['iscemail']) && $_POST['iscemail']==1 && isset($_POST['email']) && trim($_POST['email'])!=''){
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			$email=htmlspecialchars(trim($_POST['email']),ENT_QUOTES);
			$em_c=0;
			$q_res=sprintf('select uid from %s where email=%s and uid<>%s limit 1', $dbprefix.'members', yjl_SQLString($email, 'text'), $id);
			$res=mysql_query($q_res) or die('');
			if(mysql_num_rows($res)>0)$em_c=1;
			mysql_free_result($res);
			if($em_c==0){
				$uSQL=sprintf('update %s set email=%s where uid=%s', $dbprefix.'members', yjl_SQLString($email, 'text'), $id);
				$result=mysql_query($uSQL) or die('');
				$uSQL=sprintf('update %s set email=%s where uid=%s', $dbprefix.'member_validate', yjl_SQLString($email, 'text'), $id);
				$result=mysql_query($uSQL) or die('');
				if($yjl_isdebug==0){
					require_once('lib/smtp.php');
					yjl_vmail($id, $email, $r_res['vkey']);
				}
				echo '<script type="text/javascript">alert(\'确认邮件已经发送到你的邮箱!\');</script>';
			}else{
				echo '<script type="text/javascript">alert(\'请使用其他的Email！\');</script>';
			}
			echo '<script type="text/javascript">location.href=\''.$f.'?id='.$id.'\';</script>';
			exit();
		}
		$c.='<br/>邮件地址写错了？修改邮箱，重新发送验证 <a href="#" onclick="if($(\'#email_mform\').is(\':hidden\')){$(\'#email_mform\').slideDown(500);}else{$(\'#email_mform\').slideUp(500);}return false;">修改邮箱</a>。<br/>或者再次发送<a href="?id='.$id.'&amp;send=1">确认邮件</a>';

		//delete
		if($yjl_isdebug>0)$c.='<br/>本地测试，没办法发邮件，<a href="verify.php?uid='.$id.'&amp;key='.$r_res['vkey'].'">点击验证</a>';
		$c.='<form method="post" class="main_form" action="" id="email_mform" style="display: none;"><table>
				<tbody><tr><th></th><td><span class="form_tip" style="margin:0;">修改邮箱，重新发送验证。</span></td></tr><tr>
						<th width="100">邮箱<b></b></th>
						<td><input name="email" class="text"/></td>
					</tr>
					<tr>
						<td></td>
						<td><input type="submit" class="submit sub_reg" value="修改并发送"/><input type="hidden" name="iscemail" value="1"/></td>
					</tr>
				</tbody>
				</table>
			</form>';
	}
	$c.='</p>
			</div>
		</div>';
	echo yjl_html($c, 'regist', 'regist');
}else{
	echo '<script type="text/javascript">location.href=\'./\';</script>';
}
mysql_free_result($res);
?>