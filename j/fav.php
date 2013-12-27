<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='fav.php';
$udb=yjl_chkulog();
if($udb['uid']>0){
	$id=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
	$q_res=sprintf('select tid, uid from %s where tid=%s limit 1', $dbprefix.'topic', $id);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$q_reu=sprintf('select uid from %s where tid=%s and uid=%s limit 1', $dbprefix.'topic_favorite', $r_res['tid'], $udb['uid']);
		$reu=mysql_query($q_reu) or die('');
		if(mysql_num_rows($reu)==0){
			$iSQL=sprintf('insert into %s (tid, tuid, uid, dateline) values (%s, %s, %s, %s)', $dbprefix.'topic_favorite',
				$r_res['tid'],
				$r_res['uid'],
				$udb['uid'],
				time());
			$result=mysql_query($iSQL);
		}
		mysql_free_result($reu);
		echo '已收藏';
	}
	mysql_free_result($res);
}
?>