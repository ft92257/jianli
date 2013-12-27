<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='chkreg.php';
if(isset($_GET['t']) && $_GET['t']==1){
	if(isset($_GET['c']) && trim($_GET['c'])!=''){
		$password=htmlspecialchars(trim($_GET['c']),ENT_QUOTES);
		if(strlen($password)<6){
			echo '密码不能少于6位';
		}else{
			$pn=preg_match("/^\\w+$/i",$password)?$password:'';
			if($pn=='')echo '只能使用字母，数字';
		}
	}else{
		echo '密码不能为空';
	}
}else{
	if(isset($_GET['c']) && trim($_GET['c'])!=''){
		$email=htmlspecialchars(trim($_GET['c']),ENT_QUOTES);
		if(yjl_cemail($email)){
			$q_res=sprintf('select uid from %s where email=%s limit 1', $dbprefix.'members', yjl_SQLString($email, 'text'));
			$res=mysql_query($q_res) or die('');
			if(mysql_num_rows($res)>0)echo '请使用其他的邮箱';
			mysql_free_result($res);
		}else{
			echo '邮箱格式错误';
		}
	}else{
		echo '邮箱不能为空';
	}
}
?>