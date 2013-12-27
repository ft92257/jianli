<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='xzhdtopic.php';
$user_id=0;
if($udb['uid']>0){
	$user_id=$udb['uid'];

	$id=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
	$m=(isset($_GET['m']) && $_GET['m']=='hd')?'hd':'xz';
	$tid=(isset($_GET['t']) && $_GET['t']==1)?1:0;
	$q_res=sprintf('select %sid, name, tid, uid, uname, app_id, xqid from %s where %sid=%s and uid=%s limit 1', $m, $yjl_dbprefix.$m, $m, $id, $user_id);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		if(isset($_GET['u']) && intval($_GET['u'])>0){
			$huid=intval($_GET['u']);
			$q_rep=sprintf('select huid, uid, name from %s where huid=%s and %sid=%s and iscy=1 limit 1', $yjl_dbprefix.$m.'_user', $huid, $m, $r_res[$m.'id']);
			$rep=mysql_query($q_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				if($tid>0){
					$dSQL=sprintf('delete from %s where huid=%s and %sid=%s', $yjl_dbprefix.$m.'_user', $huid, $m, $r_res[$m.'id']);
					$result=mysql_query($dSQL) or die('');
				}else{
					$uSQL=sprintf('update %s set iscy=0 where huid=%s and %sid=%s', $yjl_dbprefix.$m.'_user', $huid, $m, $r_res[$m.'id']);
					$result=mysql_query($uSQL) or die('');
					$uSQL=sprintf('update %s set c_cy=c_cy+1, lasttime=%s where %sid=%s', $yjl_dbprefix.$m, time(), $m, $r_res[$m.'id']);
					$result=mysql_query($uSQL) or die('');
					if($r_rep['uid']>0){
						yjl_addlog('[uid]通过了[luid]参加'.($m=='hd'?'活动':'小组').'的申请：<a href="'.($m=='hd'?'active-'.$r_res['xqid'].'-'.$id.'.html':'group.php?xqid='.$xqid.'&id='.$id).'">'.$r_res['name'].'</a>', md5($m.'sqtg|'.$uid.'|'.$user_id.'|'.$id), 1, $uid, $user_id);
						yjl_addlog('[uid]参加'.($m=='hd'?'活动':'小组').'：<a href="'.($m=='hd'?'active-'.$r_res['xqid'].'-'.$id.'.html':'group.php?xqid='.$xqid.'&id='.$id).'">'.$r_res['name'].'</a>', md5($m.'cj|'.$uid.'|'.$uid.'|'.$id), 0, $uid, $uid);
					}else{
						yjl_addlog('[uid]通过了'.$r_rep['name'].'参加'.($m=='hd'?'活动':'小组').'的申请：<a href="'.($m=='hd'?'active-'.$r_res['xqid'].'-'.$id.'.html':'group.php?xqid='.$xqid.'&id='.$id).'">'.$r_res['name'].'</a>', md5($m.'sqtg|'.time().'|'.$user_id.'|'.$id), 1, $user_id);
					}
				}
			}
			mysql_free_result($rep);
		}
	}
	mysql_free_result($res);
}
?>