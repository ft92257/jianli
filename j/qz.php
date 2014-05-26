<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../'.$yjl_tpath.'setting/face.php');
require_once('../function.php');
$f='qz.php';
if($user_id>0 && $uadb[$user_id]['qx']==0 && isset($_GET['c']) && trim($_GET['c'])!=''){
	$content=htmlspecialchars(trim($_GET['c']),ENT_QUOTES);
	$q_req=sprintf('select app_key, app_secret from %s where id=%s limit 1', $dbprefix.'app', $r_main['qz_app_id']);
	$req=mysql_query($q_req) or die('');
	$r_req=mysql_fetch_assoc($req);
	if(mysql_num_rows($req)>0){
		$app_k=$r_req['app_key'];
		$app_s=$r_req['app_secret'];
	}else{
		$app_a=yjl_app('咨询监理', 0, $yjl_url.'faq-new.html', 'yjl');
		$uSQL=sprintf('update %s set qz_app_id=%s', $yjl_dbprefix.'main', $app_a[0]);
		$result=mysql_query($uSQL) or die('');
		$app_k=$app_a[1];
		$app_s=$app_a[2];
	}
	mysql_free_result($req);
	require_once('../lib/jishigouapi.class.php');
	$jln='易监理';
	$jluid=0;
	if(isset($_GET['jluid']) && intval($_GET['jluid'])>0){
		$q_res=sprintf('select a.uid, b.nickname from %s as a, %s as b where a.uid=b.uid and a.qx=5 and a.iswc=1 and a.iszxjl=1 and a.uid=%s limit 1', $yjl_dbprefix.'members', $dbprefix.'members', intval($_GET['jluid']));
		$res=mysql_query($q_res) or die($q_res);
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0){
			$jluid=$r_res['uid'];
			$jln='@'.$r_res['nickname'].' ';
		}
		mysql_free_result($res);
	}
	
	$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $app_k, $app_s, $udb['nickname'], md5($udb['nickname'].$udb['password']));
	$jsg_result=$JishiGouAPI->AddTopic('向'.$jln.'提问：'.$_GET['c']);
//var_dump( $jsg_result);//debug
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
		$iSQL=sprintf('insert into %s (uid, jluid, xqid, cid, content, datetime, tid) values (%s, %s, %s, %s, %s, %s, %s)', $yjl_dbprefix.'qz',
			$user_id,
			$jluid,
			$udb['xqid'],
			$_GET['cid'],
			yjl_SQLString($content, 'text'),
			time(),
			$tid);
		$result=mysql_query($iSQL) or die('');
		$qzid=mysql_insert_id();
		if($jluid>0){
			yjl_addlog('[uid]向[luid]提问：<a href="faq-'.$qzid.'.html">'.yjl_substrs($content, 10).'</a>', md5('qztw|'.$jluid.'|'.$user_id.'|'.$qzid), 0, $jluid);
		}else{
			yjl_addlog('[uid]向易监理提问：<a href="faq-'.$qzid.'.html">'.yjl_substrs($content, 10).'</a>', md5('qztw|'.$user_id.'|'.$user_id.'|'.$qzid));
		}
	}
}
?>