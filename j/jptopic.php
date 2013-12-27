<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../'.$yjl_tpath.'setting/face.php');
require_once('../function.php');
$f='jptopic.php';
$user_id=0;
$udb=yjl_chkulog();
if($udb['uid']>0){
	$user_id=$udb['uid'];
	$uadb[$user_id]=$udb;
}
$id=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
$q_res=sprintf('select a.*, b.hzid, b.uid as jluid, b.app_id, b.xqid, b.name as jlname from %s as a, %s as b where a.jpid=%s and a.jlid=b.jlid', $yjl_dbprefix.'jl_photo', $yjl_dbprefix.'jl', $id);
$res=mysql_query($q_res) or die('');
$r_res=mysql_fetch_assoc($res);
if(mysql_num_rows($res)>0){
	$iscy=$user_id>0?1:0;
	if($user_id>0 && isset($_POST['c']) && trim($_POST['c'])!=''){
		$q_req=sprintf('select app_key, app_secret from %s where id=%s limit 1', $dbprefix.'app', $r_res['app_id']);
		$req=mysql_query($q_req) or die('');
		$r_req=mysql_fetch_assoc($req);
		if(mysql_num_rows($req)>0){
			$app_k=$r_req['app_key'];
			$app_s=$r_req['app_secret'];
		}else{
			$app_a=yjl_app('照片式监理 '.$r_res['jlname'], $r_res['jlid'], $yjl_url.'photo.php?xqid='.$r_res['xqid'].'&id='.$r_res['jlid']);
			$uSQL=sprintf('update %s set app_id=%s where jlid=%s', $yjl_dbprefix.'jl', $app_a[0], $r_res['jlid']);
			$result=mysql_query($uSQL) or die('');
			$app_k=$app_a[1];
			$app_s=$app_a[2];
		}
		mysql_free_result($req);
		$content=htmlspecialchars(trim($_POST['c']),ENT_QUOTES);
		require_once('../lib/jishigouapi.class.php');
		$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $app_k, $app_s, $udb['nickname'], md5($udb['nickname'].$udb['password']));
		if($r_res['tid']>0){
			$iszf=(isset($_GET['iszf']) && $_GET['iszf']==1)?1:0;
			$fc=$iszf>0?'both':'reply';
		}else{
			$fc='first';
		}
		$jsg_result=$JishiGouAPI->AddTopic($_POST['c'], $r_res['tid'], $fc);
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
							$imgid[]=$r_rep['id'];
							if($r_rep['tid']==0){
								$uSQL=sprintf('update %s set tid=%s where id=%s', $dbprefix.'topic_image', $tid, $r_rep['id']);
								$result=mysql_query($uSQL) or die('');
							}
						}
						mysql_free_result($rep);
					}
				}
				if(isset($imgid)){
					$uSQL=sprintf('update %s set imageid=%s where tid=%s', $dbprefix.'topic', yjl_SQLString(join(',', $imgid), 'text'), $tid);
					$result=mysql_query($uSQL) or die('');
				}
			}
			$oid=0;
			if($user_id==$r_res['jluid'] && $r_res['isjl']==2){
				$oid=2;
				$uSQL=sprintf('update %s set oid=0 where uid=%s and jpid=%s', $yjl_dbprefix.'jl_topic', $user_id, $r_res['jpid']);
				$result=mysql_query($uSQL) or die('');
			}elseif($user_id==$r_res['hzid']){
				$oid=1;
				$uSQL=sprintf('update %s set oid=0 where uid=%s and jpid=%s', $yjl_dbprefix.'jl_topic', $user_id, $r_res['jpid']);
				$result=mysql_query($uSQL) or die('');
			}
			$iSQL=sprintf('insert into %s (tid, jpid, uid, datetime, content, oid) values (%s, %s, %s, %s, %s, %s)', $yjl_dbprefix.'jl_topic',
				$tid,
				$r_res['jpid'],
				$user_id,
				time(),
				yjl_SQLString($content, 'text'),
				$oid);
			$result=mysql_query($iSQL) or die('');
			$uSQL=sprintf('update %s set lasttime=%s where jlid=%s', $yjl_dbprefix.'jl', time(), $r_res['jlid']);
			$result=mysql_query($uSQL) or die('');
			$iSQL=sprintf('insert into %s (tid, xqid, uid, datetime, content) values (%s, %s, %s, %s, %s)', $yjl_dbprefix.'xq_topic',
				$tid,
				$r_res['xqid'],
				$user_id,
				time(),
				yjl_SQLString($content, 'text'));
			$result=mysql_query($iSQL) or die('');
			$uSQL=sprintf('update %s set c_wb=c_wb+1 where xqid=%s', $yjl_dbprefix.'xq', $r_res['xqid']);
			$result=mysql_query($uSQL) or die('');
			yjl_uwb($user_id, $content, $tid, '../');
		}
		if($uadb[$user_id]['qx']==0 && isset($_GET['isqz']) && $_GET['isqz']==1){
			$q_req=sprintf('select app_key, app_secret from %s where id=%s limit 1', $dbprefix.'app', $r_main['qz_app_id']);
			$req=mysql_query($q_req) or die('');
			$r_req=mysql_fetch_assoc($req);
			if(mysql_num_rows($req)>0){
				$app_k=$r_req['app_key'];
				$app_s=$r_req['app_secret'];
			}else{
				$app_a=yjl_app('咨询监理', 0, $yjl_url.'faq.php', 'yjl');
				$uSQL=sprintf('update %s set qz_app_id=%s', $yjl_dbprefix.'main', $app_a[0]);
				$result=mysql_query($uSQL) or die('');
				$app_k=$app_a[1];
				$app_s=$app_a[2];
			}
			mysql_free_result($req);
			$content=htmlspecialchars('向易监理提问：'.trim($_POST['c']),ENT_QUOTES);
			$jsg_result=$JishiGouAPI->AddTopic('向易监理提问：'.$_POST['c'], 0, 'first', $yjl_url.$r_res['o_url']);
			if(!isset($jsg_result['error']) && isset($jsg_result['result']['tid'])){
				$tid=$jsg_result['result']['tid'];
				$iSQL=sprintf('insert into %s (uid, xqid, cid, content, datetime, tid) values (%s, %s, 2, %s, %s, %s)', $yjl_dbprefix.'qz',
					$user_id,
					$udb['xqid'],
					yjl_SQLString($content, 'text'),
					time(),
					$tid);
				$result=mysql_query($iSQL) or die('');
			}
		}
	}
	echo yjl_jptopic($r_res['jpid'], $r_res['hzid'], $page);
}
mysql_free_result($res);
?>