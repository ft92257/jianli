<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once('function.php');
$e='';
/**
if(file_exists($e.$yjl_tpath.'setting/sina.php')){
	require_once($e.$yjl_tpath.'setting/sina.php');
	$q_rex=sprintf('select sina_uid, access_token from %s where length(access_token)>0 limit 1', $dbprefix.'xwb_bind_info');
	$rex=mysql_query($q_rex) or die('');
	$r_rex=mysql_fetch_assoc($rex);
	if(mysql_num_rows($rex)>0){
		require_once($e.'lib/saetv2.ex.class.php');
		$so=new SaeTClientV2($r_main['sina_k'], $r_main['sina_s'], $r_rex['access_token']);
		$ma=$so->friends_by_id($r_rex['sina_uid']);
		var_dump($ma);
	}
	mysql_free_result($rex);
}
**/
/**
if(file_exists($e.$yjl_tpath.'setting/qqwb.php')){
	require_once($e.$yjl_tpath.'setting/qqwb.php');
	$q_rex=sprintf('select token, tsecret from %s where length(token)>0 and length(tsecret)>0 and synctoqq>0 limit 1', $dbprefix.'qqwb_bind_info');
	$rex=mysql_query($q_rex) or die('');
	$r_rex=mysql_fetch_assoc($rex);
	if(mysql_num_rows($rex)>0){
		require_once($e.'lib/tqq_opent.php');
		require_once($e.'lib/tqq_client.php');
		$tqq=new MBApiClient($r_main['tqq_k'], $r_main['tqq_s'], $r_rex['token'], $r_rex['tsecret']);
		$ma=$tqq->getUserInfo();
		var_dump($ma);
	}
	mysql_free_result($rex);
}
**/
/**
$q_res=sprintf('select * from %s where length(email)=0', $dbprefix.'members');
$res=mysql_query($q_res) or die('');
$r_res=mysql_fetch_assoc($res);
if(mysql_num_rows($res)>0){
	do{
		$email=$r_res['username'].'@'.substr(md5(time().rand(1,999)), 0, rand(5, 8)).'.com';
		$uSQL=sprintf('update %s set email=%s where uid=%s', $dbprefix.'members', yjl_SQLString($email, 'text'), $r_res['uid']);
		$result=mysql_query($uSQL) or die('');
	}while($r_res=mysql_fetch_assoc($res));
}
mysql_free_result($res);
**/
$q_res=sprintf('select a.*, b.iswc from %s as a, %s as b where a.uid=b.uid and b.qx=0 and b.xqid=0', $dbprefix.'members', $yjl_dbprefix.'members');
$res=mysql_query($q_res) or die('');
$r_res=mysql_fetch_assoc($res);
if(mysql_num_rows($res)>0){
	do{
		echo $r_res['username'].'-'.$r_res['iswc'].'<br>';
	}while($r_res=mysql_fetch_assoc($res));
}
mysql_free_result($res);
?>