<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='gz.php';
if($udb['uid']>0){
	$cuid=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):$udb['uid'];
	if($cuid!=$udb['uid']){
		$cudb=yjl_udb($cuid);
		if($cudb['uid']>0){
			$q_res=sprintf('select uid from %s where uid=%s and buddyid=%s', $dbprefix.'buddys', $user_id, $cuid);
			$res=mysql_query($q_res) or die('');
			$isgz=mysql_num_rows($res)>0?1:0;
			mysql_free_result($res);
			if($isgz>0){
				$dSQL=sprintf('delete from %s where uid=%s and buddyid=%s', $dbprefix.'buddys', $user_id, $cuid);
				$result=mysql_query($dSQL) or die('');
				$uSQL=sprintf('update %s set follow_count=follow_count-1 where uid=%s and follow_count>0', $dbprefix.'members', $user_id);
				$result=mysql_query($uSQL) or die('');
				$uSQL=sprintf('update %s set fans_count=fans_count-1 where uid=%s and fans_count>0', $dbprefix.'members', $cuid);
				$result=mysql_query($uSQL) or die('');
			}else{
				yjl_follow($cuid, $user_id);
			}
			echo '<a href="#" onclick="$(\'#gz_'.$cuid.'\').load(\'j/'.$f.'?id='.$cuid.'\');return false;"'.($isgz>0?' class="btn bt_nomblue" style="color: #fff;">关 注':'>取消关注').'</a>';
		}
	}
}
?>