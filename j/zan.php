<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../'.$yjl_tpath.'setting/face.php');
require_once('../function.php');
$f='zan.php';
if($user_id>0){
	$id=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
	$fj=(isset($_GET['t']) && $_GET['t']==1)?'1':'';
	$q_res=sprintf('select c_zan'.$fj.' from %s where hdid=%s limit 1', $yjl_dbprefix.'hd', $id);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		if(!isset($_COOKIE['zan'.$fj.'_hd_'.$id])){
			setcookie('zan'.$fj.'_hd_'.$id, 1, time()+86400*365);
			$uSQL=sprintf('update %s set c_zan'.$fj.'=c_zan'.$fj.'+1 where hdid=%s', $yjl_dbprefix.'hd', $id);
			$result=mysql_query($uSQL) or die('');
			$r_res['c_zan'.$fj]++;
		}
		echo '<span class="mn_ico '.($fj!=''?'b':'g').'d"></span><br />'.$r_res['c_zan'.$fj];
	}
	mysql_free_result($res);
}
?>