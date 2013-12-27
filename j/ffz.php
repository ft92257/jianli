<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='ffz.php';
if($udb['uid']>0){
	$user_id=$udb['uid'];
	if(isset($_GET['zuid']) && intval($_GET['zuid'])>0 && $_GET['zuid']!=$user_id){
		$zuid=intval($_GET['zuid']);
		if(isset($_GET['dfzid']) && $_GET['dfzid']==1){
			$dSQL=sprintf('delete from %s where uid=%s and zuid=%s', $yjl_dbprefix.'ffz_f', $zuid, $user_id);
			$result=mysql_query($dSQL) or die('');
			echo '未分组';
		}elseif(isset($_GET['fzid']) && intval($_GET['fzid'])>0){
			$fzid=intval($_GET['fzid']);
			$q_res=sprintf('select name from %s where uid=%s and fzid=%s limit 1', $yjl_dbprefix.'ffz', $user_id, $fzid);
			$res=mysql_query($q_res) or die('');
			$r_res=mysql_fetch_assoc($res);
			if(mysql_num_rows($res)>0){
				$q_req=sprintf('select uid from %s where uid=%s and fzid=%s', $yjl_dbprefix.'ffz_f', $zuid, $fzid);
				$req=mysql_query($q_req) or die('');
				if(mysql_num_rows($req)==0){
					$dSQL=sprintf('delete from %s where uid=%s and zuid=%s', $yjl_dbprefix.'ffz_f', $zuid, $user_id);
					$result=mysql_query($dSQL) or die('');
					$iSQL=sprintf('insert into %s (fzid, uid, zuid) values (%s, %s, %s)', $yjl_dbprefix.'ffz_f',
						$fzid,
						$zuid,
						$user_id);
					$result=mysql_query($iSQL) or die('');
				}
				mysql_free_result($req);
				echo $r_res['name'];
			}
			mysql_free_result($res);
		}
	}else{
		if(isset($_POST['n']) && trim($_POST['n'])!=''){
			$name=htmlspecialchars(trim($_POST['n']),ENT_QUOTES);
			$q_res=sprintf('select uid from %s where uid=%s and name=%s', $yjl_dbprefix.'ffz', $user_id, yjl_SQLString($name, 'text'));
			$res=mysql_query($q_res) or die('');
			if(mysql_num_rows($res)==0){
				$iSQL=sprintf('insert into %s (uid, name) values (%s, %s)', $yjl_dbprefix.'ffz',
					$user_id,
					yjl_SQLString($name, 'text'));
				$result=mysql_query($iSQL) or die('');
			}
			mysql_free_result($res);
		}elseif(isset($_GET['del']) && intval($_GET['del'])>0){
			$fzid=intval($_GET['del']);
			$q_res=sprintf('select fzid from %s where uid=%s and fzid=%s', $yjl_dbprefix.'ffz', $user_id, $fzid);
			$res=mysql_query($q_res) or die('');
			if(mysql_num_rows($res)>0){
				$dSQL=sprintf('delete from %s where fzid=%s', $yjl_dbprefix.'ffz_f', $fzid);
				$result=mysql_query($dSQL) or die('');
				$dSQL=sprintf('delete from %s where fzid=%s', $yjl_dbprefix.'ffz', $fzid);
				$result=mysql_query($dSQL) or die('');
			}
			mysql_free_result($res);
		}
		$q_res=sprintf('select * from %s where uid=%s', $yjl_dbprefix.'ffz', $user_id);
		$res=mysql_query($q_res) or die('');
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0){
			do{
				echo $r_res['name'].'[<a href="#" style="color: #f00;" onclick="$(\'#fz_div\').load(\'j/ffz.php?del='.$r_res['fzid'].'\');return false;">删除</a>]&nbsp;&nbsp;';
			}while($r_res=mysql_fetch_assoc($res));
		}
		mysql_free_result($res);
	}
}
?>