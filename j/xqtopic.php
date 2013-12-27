<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../'.$yjl_tpath.'setting/face.php');
require_once('../function.php');
$f='xqtopic.php';
$id=(isset($_GET['xqid']) && intval($_GET['xqid'])>0)?intval($_GET['xqid']):1;
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
$xqtcid=(isset($_GET['cid']) && isset($a_xqgc[$_GET['cid']]))?$_GET['cid']:0;
$q_res=sprintf('select xqid, name, app_id from %s where xqid=%s and iskf=1 limit 1', $yjl_dbprefix.'xq', $id);
$res=mysql_query($q_res) or die('');
$r_res=mysql_fetch_assoc($res);
if(mysql_num_rows($res)>0){
	$iscy=$user_id>0?1:0;
	if($user_id>0 && isset($_POST['c']) && trim($_POST['c'])!=''){
		$content=htmlspecialchars(trim($_POST['c']),ENT_QUOTES);
		$q_rep=sprintf('select app_key, app_secret from %s where id=%s limit 1', $dbprefix.'app', $r_res['app_id']);
		$rep=mysql_query($q_rep) or die('');
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0){
			$app_k=$r_rep['app_key'];
			$app_s=$r_rep['app_secret'];
		}else{
			$app_a=yjl_app($r_res['name'], $r_res['xqid'], $yjl_url.'square-'.$r_res['xqid'].'.html', 'xq', $r_res['name'].' 小区广场');
			$uSQL=sprintf('update %s set app_id=%s where xqid=%s', $yjl_dbprefix.'xq', $app_a[0], $r_res['xqid']);
			$result=mysql_query($uSQL) or die('');
			$app_k=$app_a[1];
			$app_s=$app_a[2];
		}
		mysql_free_result($rep);
		require_once('../lib/jishigouapi.class.php');
		$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $app_k, $app_s, $udb['nickname'], md5($udb['nickname'].$udb['password']));
		$jsg_result=$JishiGouAPI->AddTopic($_POST['c']);
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
			$iSQL=sprintf('insert into %s (tid, xqid, uid, cid, datetime, content) values (%s, %s, %s, %s, %s, %s)', $yjl_dbprefix.'xq_topic',
				$tid,
				$r_res['xqid'],
				$udb['uid'],
				$xqtcid,
				time(),
				yjl_SQLString($content, 'text'));
			$result=mysql_query($iSQL) or die('');
			$uSQL=sprintf('update %s set c_wb=c_wb+1 where xqid=%s', $yjl_dbprefix.'xq', $r_res['xqid']);
			$result=mysql_query($uSQL) or die('');
			yjl_uwb($udb['uid'], $content, $tid, '../');
		}
	}
	$cdb=$xqtcid>0?' and a.cid='.$xqtcid:'';
	$q_ret=sprintf('select b.* from %s as a, %s as b where a.tid=b.tid and a.xqid=%s%s order by a.datetime desc', $yjl_dbprefix.'xq_topic', $dbprefix.'topic', $r_res['xqid'], $cdb);
	$ret=mysql_query($q_ret) or die('');
	$r_ret=mysql_fetch_assoc($ret);
	if(mysql_num_rows($ret)>0){
		do{
			if(!isset($uadb[$r_ret['uid']]))$uadb[$r_ret['uid']]=yjl_udb($r_ret['uid']);
			$a_ret[$r_ret['tid']]=yjl_newwb($r_ret);
		}while($r_ret=mysql_fetch_assoc($ret));
	}
	mysql_free_result($ret);
	$q_ret=sprintf('select b.* from %s as a, %s as b where a.tid=b.tid and a.xqid<>%s%s order by a.datetime desc', $yjl_dbprefix.'xq_topic', $dbprefix.'topic', $r_res['xqid'], $cdb);
	$ret=mysql_query($q_ret) or die('');
	$r_ret=mysql_fetch_assoc($ret);
	if(mysql_num_rows($ret)>0){
		do{
			if(!isset($uadb[$r_ret['uid']]))$uadb[$r_ret['uid']]=yjl_udb($r_ret['uid']);
			$a_ret[$r_ret['tid']]=yjl_newwb($r_ret);
		}while($r_ret=mysql_fetch_assoc($ret));
	}
	mysql_free_result($ret);
	if(isset($a_ret)){
		$c='<ul class="list_comment">';
		$tr_ret=count($a_ret);
		$p_size=10;
		$tp_ret=ceil($tr_ret/$p_size);
		if($page>$tp_ret)$page=$tp_ret;
		$i=0;
		foreach($a_ret as $v){
			if($i>=($page-1)*$p_size && $i<$page*$p_size)$c.=$v;
			$i++;
		}
		$c.='</ul>';
		if($tp_ret>1)$c.=yjl_newhmpage('square-'.$xqid.($xqtcid>0?'-'.$xqtcid:'').'-p[p]'.'.html', $page, $tp_ret, 'xqtopic_'.$xqid);
		echo $c;
	}
}
mysql_free_result($res);
?>