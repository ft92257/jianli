<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='filegk.php';
if($udb['uid']>0){
	$id=(isset($_GET['fiid']) && intval($_GET['fiid'])>0)?intval($_GET['fiid']):1;
	$isgk=(isset($_GET['gk']) && intval($_GET['gk'])>0 && intval($_GET['gk'])<=2)?intval($_GET['gk']):0;
	$q_res=sprintf('select fiid, isgk from %s where fiid=%s and uid=%s limit 1', $yjl_dbprefix.'jl_file', $id, $udb['uid']);
	$res=mysql_query($q_res) or die(mysql_error());
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$uSQL=sprintf('update %s set isgk=%s where fiid=%s', $yjl_dbprefix.'jl_file', $isgk, $r_res['fiid']);
		$result=mysql_query($uSQL) or die(mysql_error());
		switch($isgk){
			case 2:
				echo '好友可见 修改：<a href="#" onclick="$(\'#file_td_'.$r_res['fiid'].'\').load(\'j/filegk.php?fiid='.$r_res['fiid'].'&gk=0\');return false;">不公开</a> <a href="#" onclick="$(\'#file_td_'.$r_res['fiid'].'\').load(\'j/filegk.php?fiid='.$r_res['fiid'].'&gk=1\');return false;">公开</a>';
				break;
			case 1:
				echo '公开 修改：<a href="#" onclick="$(\'#file_td_'.$r_res['fiid'].'\').load(\'j/filegk.php?fiid='.$r_res['fiid'].'&gk=0\');return false;">不公开</a> <a href="#" onclick="$(\'#file_td_'.$r_res['fiid'].'\').load(\'j/filegk.php?fiid='.$r_res['fiid'].'&gk=2\');return false;">好友可见</a>';
				break;
			default:
				echo '不公开 修改：<a href="#" onclick="$(\'#file_td_'.$r_res['fiid'].'\').load(\'j/filegk.php?fiid='.$r_res['fiid'].'&gk=1\');return false;">公开</a> <a href="#" onclick="$(\'#file_td_'.$r_res['fiid'].'\').load(\'j/filegk.php?fiid='.$r_res['fiid'].'&gk=2\');return false;">好友可见</a>';
				break;
		}
	}
	mysql_free_result($res);
}
?>