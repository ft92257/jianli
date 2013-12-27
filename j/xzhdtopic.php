<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../'.$yjl_tpath.'setting/face.php');
require_once('../function.php');
$f='xzhdtopic.php';
$id=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
$m=(isset($_GET['m']) && $_GET['m']=='hd')?'hd':'xz';
$mn=$m=='hd'?'活动':'小组';
$tid=(isset($_GET['t']) && $_GET['t']==1)?1:0;
$q_res=sprintf('select %sid, name, tid, uid, uname, app_id, xqid from %s where %sid=%s limit 1', $m, $yjl_dbprefix.$m, $m, $id);
$res=mysql_query($q_res) or die('');
$r_res=mysql_fetch_assoc($res);
if(mysql_num_rows($res)>0){
	$iscy=0;
	if($user_id>0){
		$q_rep=sprintf('select uid from %s where uid=%s and %sid=%s and iscy=0 limit 1', $yjl_dbprefix.$m.'_user', $user_id, $m, $r_res[$m.'id']);
		$rep=mysql_query($q_rep) or die('');
		if(mysql_num_rows($rep)>0)$iscy=1;
		mysql_free_result($rep);
	}
	if($iscy>0 && isset($_POST['c']) && trim($_POST['c'])!=''){
		$content=htmlspecialchars(trim($_POST['c']),ENT_QUOTES);
		$q_req=sprintf('select app_key, app_secret from %s where id=%s limit 1', $dbprefix.'app', $r_res['app_id']);
		$req=mysql_query($q_req) or die('');
		$r_req=mysql_fetch_assoc($req);
		if(mysql_num_rows($req)>0){
			$app_k=$r_req['app_key'];
			$app_s=$r_req['app_secret'];
		}else{
			$app_a=yjl_app($mn.' '.$r_res['name'], $r_res[$m.'id'], $yjl_url.($m=='hd'?'active-'.$r_res['xqid'].'-'.$r_res[$m.'id'].'.html':'group.php?xqid='.$xqid.'&id='.$r_res[$m.'id']), $m);
			$uSQL=sprintf('update %s set app_id=%s where %sid=%s', $yjl_dbprefix.$m, $app_a[0], $m, $r_res[$m.'id']);
			$result=mysql_query($uSQL) or die('');
			$app_k=$app_a[1];
			$app_s=$app_a[2];
		}
		mysql_free_result($req);
		require_once('../lib/jishigouapi.class.php');
		$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $app_k, $app_s, $udb['nickname'], md5($udb['nickname'].$udb['password']));
		$type=$r_res['tid']>0?'both':'first';
		$jsg_result=$JishiGouAPI->AddTopic($_POST['c'], $r_res['tid'], $type);
		if(!isset($jsg_result['error']) && isset($jsg_result['result']['tid'])){
			$tid=$jsg_result['result']['tid'];
			if(isset($_GET['imgid']) && trim($_GET['imgid'])!=''){
				$imga=explode('_', trim($_GET['imgid']));
				foreach($imga as  $v){
					if(trim($v)!='' && intval($v)>0){
						$q_rep=sprintf('select id, tid from %s where id=%s limit 1', $dbprefix.'topic_image', intval($v));
						$rep=mysql_query($q_rep) or die('');
						$r_rep=mysql_fetch_assoc($rep);
						if(mysql_num_rows($rep)>0){
							$a_imgid[]=$r_rep['id'];
							if($r_rep['tid']==0){
								$uSQL=sprintf('update %s set tid=%s where id=%s', $dbprefix.'topic_image', $tid, $r_rep['id']);
								$result=mysql_query($uSQL) or die('');
							}
						}
						mysql_free_result($rep);
					}
				}
				if(isset($a_imgid)){
					$uSQL=sprintf('update %s set imageid=%s where tid=%s', $dbprefix.'topic', yjl_SQLString(join(',', $a_imgid), 'text'), $tid);
					$result=mysql_query($uSQL) or die('');
				}
			}
			$iSQL=sprintf('insert into %s (tid, %sid, uid, datetime, content) values (%s, %s, %s, %s, %s)', $yjl_dbprefix.$m.'_topic',
				$m,
				$tid,
				$r_res[$m.'id'],
				$user_id,
				time(),
				yjl_SQLString($content, 'text'));
			$result=mysql_query($iSQL) or die('');
			$uSQL=sprintf('update %s set lasttime=%s, c_wb=c_wb+1 where %sid=%s', $yjl_dbprefix.$m, time(), $m, $r_res[$m.'id']);
			$result=mysql_query($uSQL) or die('');
			yjl_addlog('[uid]发表评论：<a href="'.($m=='hd'?'active-'.$r_res['xqid'].'-'.$r_res[$m.'id'].'.html':'group.php?id='.$r_res[$m.'id'].'&xqid='.$r_res['xqid']).'">'.$r_res['name'].'</a>', md5('pl'.$m.'|'.$r_res['uid'].'|'.$user_id.'|'.$r_res[$m.'id']), 0, $r_res['uid']);
			yjl_uwb($user_id, $content, $tid, '../');
		}
	}
	$ispl=$iscy>0?0:1;
	$q_ret=sprintf('select b.* from %s as a, %s as b where a.tid=b.tid and a.%sid=%s order by a.datetime desc', $yjl_dbprefix.'hd_topic', $dbprefix.'topic', $m, $r_res[$m.'id']);
	$a_ret=mysql_query($q_ret) or die('');
	$tr_ret=mysql_num_rows($a_ret);
	if($tr_ret>0){
		$tp_ret=ceil($tr_ret/$p_size);
		if($page>$tp_ret)$page=$tp_ret;
		$q_l_ret=sprintf('%s limit %d, %d', $q_ret, ($page-1)*$p_size, $p_size);
		$ret=mysql_query($q_l_ret) or die('');
		$r_ret=mysql_fetch_assoc($ret);
		$c='<ul class="list_comment">';
		do{
			if(!isset($uadb[$r_ret['uid']]))$uadb[$r_ret['uid']]=yjl_udb($r_ret['uid']);
			$c.=yjl_newwb($r_ret, $ispl, $r_res['tid']);
		}while($r_ret=mysql_fetch_assoc($ret));
		mysql_free_result($ret);
		$c.='</ul>';
		if($tp_ret>1){
			if($m='hd'){
				$c.=yjl_newhmpage('active-'.$r_res['xqid'].'-'.$r_res[$m.'id'].'-p[p].html', $page, $tp_rep, 'hdtopic_'.$r_res[$m.'id'], 1);
			}else{
				$c.=yjl_newpage($page, $tp_ret, $m.'topic_'.$r_res[$m.'id'], 1);
			}
		}
		echo $c;
	}
	mysql_free_result($a_ret);
}
mysql_free_result($res);
?>