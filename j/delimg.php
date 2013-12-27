<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='delimg.php';
if($udb['uid']>0){
	$id=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
	$q_res=sprintf('select id from %s where id=%s and uid=%s and tid=0 limit 1', $dbprefix.'topic_image', $id, $user_id);
	$res=mysql_query($q_res) or die('');
	if(mysql_num_rows($res)>0){
		$up=yjl_imgpath($id);
		$nf='images/topic/'.$up[1].$id.'_';
		unlink('../'.$yjl_tpath.$nf.'o.jpg');
		unlink('../'.$yjl_tpath.$nf.'s.jpg');
		$dSQL=sprintf('delete from %s where id=%s', $dbprefix.'topic_image', $id);
		$result=mysql_query($dSQL) or die('');
	}
	mysql_free_result($res);
}
?>