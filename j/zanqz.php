<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../'.$yjl_tpath.'setting/face.php');
require_once('../function.php');
$f='zanqz.php';
if($user_id>0 && $uadb[$user_id]['qx']==0){
	$id=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
	$q_res=sprintf('select c_zan from %s where qzid=%s limit 1', $yjl_dbprefix.'qz', $id);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		if(!isset($_COOKIE['zan_qz_'.$id])){
			setcookie('zan_qz_'.$id, 1, time()+86400*365);
			$uSQL=sprintf('update %s set c_zan=c_zan+1 where qzid=%s', $yjl_dbprefix.'qz', $id);
			$result=mysql_query($uSQL) or die('');
			$r_res['c_zan']++;
		}
		echo '赞('.($r_res['c_zan']).')';
	}
	mysql_free_result($res);
}
?>