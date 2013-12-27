<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='adviser.php';
$udb=yjl_chkulog();
if($udb['uid']>0 && $udb['qx']==0){
	$id=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
	if($id!=$udb['uid']){
		$q_res=sprintf('select * from %s where uid=%s limit 1', $dbprefix.'members', $id);
		$res=mysql_query($q_res) or die('');
		if(mysql_num_rows($res)>0){
			$q_rep=sprintf('select uid from %s where uid=%s and gwid=%s limit 1', $yjl_dbprefix.'gwt', $udb['uid'], $id);
			$rep=mysql_query($q_rep) or die('');
			if(mysql_num_rows($rep)>0){
				$dSQL=sprintf('delete from %s where uid=%s and gwid=%s', $yjl_dbprefix.'gwt', $udb['uid'], $id);
				$result=mysql_query($dSQL) or die('');
				echo '<a href="#" onclick="$(\'#gw_'.$id.'\').load(\'j/adviser.php?id='.$id.'\');return false;">加为我的顾问</a>';
			}else{
				yjl_follow($id, $udb['uid']);
				$iSQL=sprintf('insert into %s (uid, gwid, datetime) values (%s, %s, %s)', $yjl_dbprefix.'gwt',
					$udb['uid'],
					$id,
					time());
				$result=mysql_query($iSQL) or die('');
				echo '我的顾问 | <a href="#" onclick="$(\'#gw_'.$id.'\').load(\'j/adviser.php?id='.$id.'\');return false;">取消</a>';
			}
			mysql_free_result($rep);
		}
		mysql_free_result($res);
	}
}
?>