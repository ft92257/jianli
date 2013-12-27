<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../'.$yjl_tpath.'setting/face.php');
require_once('../function.php');
$f='homeqz.php';
if($user_id==0){
	$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
	$p_size=(isset($_GET['s']) && intval($_GET['s'])>0)?intval($_GET['s']):5;
	$q_res=sprintf('select a.qzid, a.c_zan, a.jluid, a.cid, b.* from %s as a, %s as b where a.tid=b.tid and a.c_hd>0 order by a.datetime desc, a.qzid desc', $yjl_dbprefix.'qz', $dbprefix.'topic', $p_size);
	$a_res=mysql_query($q_res) or die('');
	$tr_res=mysql_num_rows($a_res);
	if($tr_res>0){
		$tp_res=ceil($tr_res/$p_size);
		if($page>=$tp_res){
			$page=$tp_res;
			echo '<input type="hidden" id="qz_iso" value="1"/>';
		}
		$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
		$res=mysql_query($q_l_res) or die('');
		$r_res=mysql_fetch_assoc($res);
		do{
			if(!isset($uadb[$r_res['uid']]))$uadb[$r_res['uid']]=yjl_udb($r_res['uid']);
			echo yjl_homeqz($r_res);
		}while($r_res=mysql_fetch_assoc($res));
		mysql_free_result($res);
	}
	mysql_free_result($a_res);
}
?>