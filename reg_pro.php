<?php
//跳转到新版
//header("Location:http://" . $_SERVER['HTTP_HOST'] . "/jianliapp/index.php?s=/User/register");
//die;

session_start();
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
$no_getxq=1;
require_once('function.php');
if($udb['uid']>0){
	echo '<script type="text/javascript">location.href=\'./\';</script>';
	exit();
}
$f='reg_pro.php';
$page_title='专业人员注册';
$regcode='';
$rcid=0;
if(isset($_GET['code']) && trim($_GET['code'])!=''){
	$regcode=htmlspecialchars(trim($_GET['code']),ENT_QUOTES);
	$q_res=sprintf('select inid from %s where code=%s and isjl>0 and issy=0 limit 1', $yjl_dbprefix.'invite', yjl_SQLString($regcode, 'text'));
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0)$rcid=$r_res['inid'];
	mysql_free_result($res);
}
$iszc=1;
if($rcid==0 && $r_main['yzqc_jl']>0)$iszc=0;
if($_SERVER['REQUEST_METHOD']=='POST'){
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
	$p_rcid=0;
	if(isset($_POST['rcode']) && trim($_POST['rcode'])!=''){
		$p_regcode=htmlspecialchars(trim($_POST['rcode']),ENT_QUOTES);
		$q_res=sprintf('select inid from %s where code=%s and isjl>0 and issy=0 limit 1', $yjl_dbprefix.'invite', yjl_SQLString($p_regcode, 'text'));
		$res=mysql_query($q_res) or die('');
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0){
			$p_rcid=$r_res['inid'];
			$regcode=$p_regcode;
		}
		mysql_free_result($res);
	}
	$p_iszc=1;
	if($p_rcid==0 && $r_main['yzqc_jl']>0)$p_iszc=0;
	if($p_iszc==0){
		echo '<script type="text/javascript">alert(\'邀请码错误！\');</script>';
	}elseif(isset($_POST['email']) && trim($_POST['email'])!='' && isset($_POST['password']) && trim($_POST['password'])!=''){
		$email=htmlspecialchars(trim($_POST['email']),ENT_QUOTES);
		$p=htmlspecialchars(trim($_POST['password']),ENT_QUOTES);
		if(yjl_cemail($email)){
			$u=str_replace('@', '_', $email);
			$u=str_replace('.', '_', $u);
			$ue=preg_match("/^\\w+$/i",$u)?$u:'';
			if($ue=='')$u=substr(md5(time().rand(1,1000)), 0, 16);
			$u=yjl_chkusername($u);
			$em_c=0;
			$q_res=sprintf('select uid from %s where email=%s limit 1', $dbprefix.'members', yjl_SQLString($email, 'text'));
			$res=mysql_query($q_res) or die('');
			if(mysql_num_rows($res)>0)$em_c=1;
			mysql_free_result($res);
			if($em_c==0){
				$ip=yjl_getIP();
				if($yjl_isdebug==0)require_once('lib/smtp.php');
				$uid=yjl_adduser($u, $p, $ip, $email);
				if($uid>0){
					if($p_rcid>0){
						$uSQL=sprintf('update %s set issy=1, sytime=%s, syuid=%s where inid=%s', $yjl_dbprefix.'invite',
							time(),
							$uid,
							$p_rcid);
						$result=mysql_query($uSQL) or die('');
					}
					$a_qx=explode('|', $_POST['qx']);
					$uSQL=sprintf('update %s set qx=%s, gzfl=%s where uid=%s', $yjl_dbprefix.'members',
						$a_qx[0],
						$a_qx[1],
						$uid);
					$result=mysql_query($uSQL) or die('');
					$_SESSION['yjl_vid']=$uid;
					echo '<script type="text/javascript">location.href=\'setverify.php?id='.$uid.'\';</script>';
					exit();
				}
			}else{
				echo '<script type="text/javascript">alert(\'请使用其他的邮箱！\');</script>';
			}
		}else{
			echo '<script type="text/javascript">alert(\'邮箱格式错误！\');</script>';
		}
	}else{
		echo '<script type="text/javascript">alert(\'请输入注册信息！\');</script>';
	}
	echo '<script type="text/javascript">location.href=\''.$f.($regcode!=''?'?code='.$regcode:'').'\';</script>';
	exit();
}
$js_c='
	$(\'#chki_0\').blur(function(){
		var c=$.trim($(\'#chki_0\').val());
		if(c!=\'\'){
			$.get(\'j/chkreg.php\', {c:c}, function(data){
				showermsg(data, 0);
			});
		}else{
			showermsg(\'邮箱不能为空\', 0);
		}
	}).focus(function(){
		$(\'#msg_0\').html(\'\');
	});
	$(\'#chki_2\').blur(function(){
		var c=$.trim($(\'#chki_2\').val());
		if(c!=\'\'){
			$.get(\'j/chkreg.php?t=1\', {c:c}, function(data){
				showermsg(data, 2);
			});
			$(\'#chki_3\').blur();
		}else{
			showermsg(\'密码不能为空\', 2);
		}
	}).focus(function(){
		$(\'#msg_2\').html(\'\');
	});
	$(\'#chki_3\').blur(function(){
		var c=$.trim($(\'#chki_3\').val());
		if(c!=\'\'){
			if(c!=$.trim($(\'#chki_2\').val())){
				showermsg(\'确认必须与密码一致\', 3);
			}else{
				showermsg(\'\', 3);
			}
		}else{
			showermsg(\'确认密码不能为空\', 3);
		}
	}).focus(function(){
		$(\'#msg_3\').html(\'\');
	});
	$(\'#chki_c\').click(function(){
		if($(this).is(\':checked\')){
			$(\'#is_er_c\').val(\'1\');
		}else{
			$(\'#is_er_c\').val(\'0\');
		}
	});';
if($r_main['yzqc_jl']>0)$js_c.='
	$(\'#chki_4\').blur(function(){
		var c=$.trim($(\'#chki_4\').val());
		if(c!=\'\'){
			showermsg(\'\', 4);
		}else{
			showermsg(\'邀请码不能为空\', 4);
		}
	}).focus(function(){
		$(\'#msg_4\').html(\'\');
	});';
$js_c.='
	$(\'#regform\').submit(function(){
		if($(\'#is_er_0\').val()==\'0\' || $(\'#is_er_2\').val()==\'0\' || $(\'#is_er_3\').val()==\'0\''.($r_main['yzqc_jl']>0?' || $(\'#is_er_4\').val()==\'0\'':'').'){
			if($(\'#is_er_0\').val()==\'0\')$(\'#chki_0\').blur();
			if($(\'#is_er_2\').val()==\'0\')$(\'#chki_2\').blur();
			if($(\'#is_er_3\').val()==\'0\')$(\'#chki_3\').blur();'.($r_main['yzqc_jl']>0?'
			if($(\'#is_er_4\').val()==\'0\')$(\'#chki_4\').blur();':'').'
			return false;
		}else if($(\'#is_er_c\').val()==\'0\'){
			alert(\'请阅读并同意《使用协议》\');
			return false;
		}
	});';
$c='<h2>专业人员注册</h2>
		<div class="more_login"><a href="login.php?t=sina" class="sina"><span>新浪微博登录</span></a><a href="login.php?t=tqq" class="tencent"><span>腾讯微博账号登录</span></a>&nbsp;&nbsp;&nbsp;你可以使用第三方帐号登录</div>
		<div class="main clearfix">
			<div class="left">
			<form method="post" action="" class="main_form" id="regform">
				<table>
				<tbody>
					<tr>
						<th width="100">工种<b></b></th>
						<td><select name="qx">';
foreach($a_tsgz as $k=>$v){
	foreach($v as $vk=>$vv)$c.='<option value="'.$k.'|'.$vk.'">'.$vv.'</option>';
}
$c.='</select></td>
					</tr>
					<tr>
						<th>注册邮箱<b></b></th>
						<td><input type="text" class="text" name="email" id="chki_0"><span id="msg_0"></span><input type="hidden" id="is_er_0" value="0"/></td>
					</tr>
					<tr>
						<th>密码<b></b></th>
						<td><input type="password" class="text" name="password" id="chki_2"><span id="msg_2"></span><input type="hidden" id="is_er_2" value="0"/></td>
					</tr>
					<tr>
						<th>确认密码<b></b></th>
						<td><input type="password" class="text" id="chki_3"><span id="msg_3"></span><input type="hidden" id="is_er_3" value="0"/></td>
					</tr>';
if($r_main['yzqc_jl']>0)$c.='<tr>
						<th>邀请码<b></b></th>
						<td><input type="text" class="text" name="rcode"'.($regcode!=''?' value="'.$regcode.'"':'').' id="chki_4"><span id="msg_4"></span><input type="hidden" id="is_er_4" value="0"/></td>
					</tr>';
$c.='<tr>
						<td></td>
						<td><input type="checkbox" class="checkbox" id="chki_c" checked="checked" /><input type="hidden" id="is_er_c" value="1"/><span class="form_tip">我已阅读并同意<a href="#" rel="#overlay_loginOrReg" class="color_gray">《用户使用协议》</a></span></td>
					</tr>
					<tr>
						<td></td>
						<td><input type="submit" class="submit sub_reg" value="注 册"/></td>
					</tr>
				</tbody>
				</table>
			</form>
			</div>
			<div class="right">
				<p>如果你是：业主</p>
				<br />
				<a href="reg.php">请从这里注册 &gt;&gt;</a>
			</div>
		</div>
	</div>
	<div class="overlay" id="overlay_loginOrReg">';
require_once('xy.php');
$c.='</div>';
echo yjl_html($c, 'regist', 'regist');
?>