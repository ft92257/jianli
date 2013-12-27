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
$f='a_tjxq.php';
$esid=md5($f);
if(isset($_SESSION[$esid]) && $_SESSION[$esid]!=''){
	$m=array(1=>'请输入相关信息！');
	if(isset($m[$_SESSION[$esid]]))$t_m=$m[$_SESSION[$esid]];
	unset($_SESSION[$esid]);
}
$c=isset($t_m)?yjl_getMsg($t_m):'';
if($_SERVER['REQUEST_METHOD']=='POST'){
	if(isset($_POST['name']) && trim($_POST['name'])!='' && isset($_POST['l1id']) && intval($_POST['l1id'])>0){
		$name=htmlspecialchars(trim($_POST['name']),ENT_QUOTES);
		$l1id=intval($_POST['l1id']);
		$l2id=isset($_POST['l2id'])?$_POST['l2id']:0;
		$l3id=isset($_POST['l3id'])?$_POST['l3id']:0;
		$l4id=isset($_POST['l4id'])?$_POST['l4id']:0;
		$address=htmlspecialchars(trim($_POST['address']),ENT_QUOTES);

		$iSQL=sprintf('insert into %s (name, l1id, l2id, l3id, l4id, address) values (%s, %s, %s, %s, %s, %s)', $yjl_dbprefix.'xq',
			yjl_SQLString($name, 'text'),
			$l1id,
			$l2id,
			$l3id,
			$l4id,
			yjl_SQLString($address, 'text'));
		$result=mysql_query($iSQL) or die('');
		$xqid=mysql_insert_id();

		$u=$yjl_url.'square-'.$xqid.'.html';
		$app_s=md5(time().'-'.rand(1,1000).'-xq-'.$xqid);
		$app_k=md5($app_s);
		$iSQL=sprintf('insert into %s (uid, username, app_name, source_url, show_from, app_desc, app_key, app_secret, status, create_time) values (%s, %s, %s, %s, 1, %s, %s, %s, 1, %s)', $dbprefix.'app',
			$admin_db['uid'],
			yjl_SQLString($admin_db['username'], 'text'),
			yjl_SQLString($name, 'text'),
			yjl_SQLString($u, 'text'),
			yjl_SQLString($name.' 小区广场', 'text'),
			yjl_SQLString($app_k, 'text'),
			yjl_SQLString($app_s, 'text'),
			time());
		$result=mysql_query($iSQL) or die('');
		$app_id=mysql_insert_id();

		$uSQL=sprintf('update %s set app_id=%s where xqid=%s', $yjl_dbprefix.'xq', $app_id, $xqid);
		$result=mysql_query($uSQL) or die('');
		$nf='a_xq.php';
		$_SESSION[md5($nf)]=1;
		echo '<script type="text/javascript">location.href=\''.$nf.'\';</script>';
	}else{
		$_SESSION[$esid]=1;
		echo '<script type="text/javascript">location.href=\''.$f.'\';</script>';
	}
	exit();
}
if($r_main['dq']=='')$r_main['dq']=$d_l1id;
$a_dq=explode('|', $r_main['dq']);
foreach($a_dq as $v){
	$q_res=sprintf('select id, name from %s where id=%s and level=1 and upid=0 limit 1', $dbprefix.'common_district', $v);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0)$a_l1p[$r_res['id']]=$r_res['name'];
	mysql_free_result($res);
}
$c.='<form method="post" action=""><table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td colspan="2">添加小区</td></tr><tr class="altbg1"><td valign="top">名称：</td><td><input name="name" size="60"/></td></tr><tr class="altbg0"><td>地区：</td><td><select name="l1id" id="o_l1id" onchange="och1id();">';
foreach($a_l1p as $k=>$v)$c.='<option value="'.$k.'"'.($k==$d_l1id?' selected="selected"':'').'>'.$v.'</option>';
$c.='</select> <span id="ndq2"></span></td></tr><tr class="altbg1"><td valign="top">地址：</td><td><input name="address" size="60"/></td></tr></table><br><center><input type="submit" class="button" name="settingsubmit" value="提 交"></center></form>';
$js_c='och1id();';
echo yjl_adminhtml($c);
?>