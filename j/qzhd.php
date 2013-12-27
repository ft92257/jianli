<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../'.$yjl_tpath.'setting/face.php');
require_once('../function.php');
$f='qz.php';
if($user_id>0 && isset($_GET['id']) && intval($_GET['id'])>0){
	$id=intval($_GET['id']);
	$page=1;
	$q_res=sprintf('select a.qzid, a.c_zan, a.jluid, a.c_hd, a.cid, a.content as a_content, b.* from %s as a, %s as b where a.tid=b.tid and a.qzid=%s', $yjl_dbprefix.'qz', $dbprefix.'topic', $id);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		if((isset($_POST['c']) && trim($_POST['c'])!='') && ($user_id==$r_res['uid'] || ($uadb[$user_id]['qx']==5 && $udb['iszxjl']>0 && ($user_id==$r_res['jluid'] || $r_res['jluid']==0)))){
			$content=htmlspecialchars(trim($_POST['c']),ENT_QUOTES);
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
			$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $app_k, $app_s, $udb['nickname'], md5($udb['nickname'].$udb['password']));
			$jsg_result=$JishiGouAPI->AddTopic($_POST['c'], $r_res['tid'], 'both');
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
				if($user_id!=$r_res['uid']){
					yjl_addlog('[uid]回答了[luid]的提问：<a href="faq-'.$id.'.html">'.yjl_substrs($r_res['a_content'], 10).'</a>', md5('qzhd|'.$r_res['uid'].'|'.$user_id.'|'.$id), 0, $r_res['uid']);
					if($r_res['jluid']==0){
						$uSQL=sprintf('update %s set jluid=%s where qzid=%s', $yjl_dbprefix.'qz', $user_id, $r_res['qzid']);
						$result=mysql_query($uSQL) or die('');
					}
					$uSQL=sprintf('update %s set c_hd=c_hd+1 where qzid=%s', $yjl_dbprefix.'qz', $r_res['qzid']);
					$result=mysql_query($uSQL) or die('');
				}else{
					if($r_res['jluid']>0){
						yjl_addlog('[uid]补充问题：<a href="faq-'.$id.'.html">'.yjl_substrs($r_res['a_content'], 10).'</a>', md5('qzbc|'.$user_id.'|'.$id), 0, $r_res['jluid']);
					}else{
						yjl_addlog('[uid]补充问题：<a href="faq-'.$id.'.html">'.yjl_substrs($r_res['a_content'], 10).'</a>', md5('qzbc|'.$user_id.'|'.$id));
					}
				}
				$iSQL=sprintf('insert into %s (qzid, uid, content, datetime, tid) values (%s, %s, %s, %s, %s)', $yjl_dbprefix.'qz_topic',
					$r_res['qzid'],
					$user_id,
					yjl_SQLString($content, 'text'),
					time(),
					$tid);
				$result=mysql_query($iSQL) or die('');
			}
		}
		echo yjl_qzdf($r_res);
	}
	mysql_free_result($res);
}
?>