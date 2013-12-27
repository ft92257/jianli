<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='deljpimg.php';
if($udb['uid']>0){
	$id=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
	$adb=($udb['qx']==10 || $udb['isxg']>0)?'':' and (a.uid='.$user_id.' or (a.isjl=0 and b.hzid='.$user_id.'))';
	$q_res=sprintf('select a.jpid, a.url, a.t_url, a.o_url, a.isjl, a.tid, a.datetime, a.uid, b.hzid from %s as a, %s as b where a.jpid=%s and a.jlid=b.jlid%s limit 1', $yjl_dbprefix.'jl_photo', $yjl_dbprefix.'jl', $id, $adb);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		if($r_res['isjl']==1 || $udb['qx']==10 || $udb['isxg']>0 || $r_res['uid']==$r_res['hzid']){
			$dSQL=sprintf('delete from %s where jpid=%s', $yjl_dbprefix.'jl_photo', $r_res['jpid']);
			$result=mysql_query($dSQL) or die('');
			$dSQL=sprintf('delete from %s where jpid=%s', $yjl_dbprefix.'jl_topic', $r_res['jpid']);
			$result=mysql_query($dSQL) or die('');
			unlink('../'.$r_res['url']);
			unlink('../'.$r_res['t_url']);
			unlink('../'.$r_res['o_url']);
			if($r_res['tid']>0){
				$q_rep=sprintf('select a.tid, b.username, b.password, d.app_key, d.app_secret from %s as a, %s as b, %s as c, %s as d where a.tid=%s and a.uid=b.uid and a.tid=c.tid and c.item_id=d.id limit 1', $dbprefix.'topic', $dbprefix.'members', $dbprefix.'topic_api', $dbprefix.'app', $r_res['tid']);
				$rep=mysql_query($q_rep) or die('');
				$r_rep=mysql_fetch_assoc($rep);
				if(mysql_num_rows($rep)>0){
					require_once('../lib/jishigouapi.class.php');
					$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $r_rep['app_key'], $r_rep['app_secret'], $r_rep['nickname'], md5($r_rep['nickname'].$r_rep['password']));
					$jsg_reqult=$JishiGouAPI->DeleteTopic($r_rep['tid']);
				}
				mysql_free_result($rep);
			}
		}elseif($r_res['datetime']>(time()-$jldimg_jg)){
			$uSQL=sprintf('update %s set is_del=1, deltime=%s where jpid=%s', $yjl_dbprefix.'jl_photo', time(), $id);
			$result=mysql_query($uSQL) or die('');
		}
	}
	mysql_free_result($res);
}
?>