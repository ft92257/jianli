<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../'.$yjl_tpath.'setting/face.php');
require_once('../function.php');
$f='cl.php';
$jlid=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
$q_res=sprintf('select * from %s where jlid=%s limit 1', $yjl_dbprefix.'jl', $jlid);
$res=mysql_query($q_res) or die('');
$r_res=mysql_fetch_assoc($res);
if(mysql_num_rows($res)>0){
	$q_rep=sprintf('select app_key, app_secret from %s where id=%s limit 1', $dbprefix.'app', $r_res['app_id']);
	$rep=mysql_query($q_rep) or die('');
	$r_rep=mysql_fetch_assoc($rep);
	if(mysql_num_rows($rep)>0){
		$app_k=$r_rep['app_key'];
		$app_s=$r_rep['app_secret'];
	}else{
		$app_a=yjl_app('照片式监理 '.$r_res['name'], $jlid, $yjl_url.'photo-'.$r_res['xqid'].'-'.$jlid.'.html');
		$uSQL=sprintf('update %s set app_id=%s where jlid=%s', $yjl_dbprefix.'jl', $app_a[0], $jlid);
		$result=mysql_query($uSQL) or die('');
		$app_k=$app_a[1];
		$app_s=$app_a[2];
	}
	mysql_free_result($rep);
	if($r_res['uid']>0 && !isset($uadb[$r_res['uid']]))$uadb[$r_res['uid']]=yjl_udb($r_res['uid']);
	if(!isset($uadb[$r_res['hzid']]))$uadb[$r_res['hzid']]=yjl_udb($r_res['hzid']);
	$clid=(isset($_GET['clid']) && intval($_GET['clid'])>0)?intval($_GET['clid']):1;
	$q_rep=sprintf('select * from %s where jlid=%s and clid=%s limit 1', $yjl_dbprefix.'jl_cl', $jlid, $clid);
	$rep=mysql_query($q_rep) or die('');
	$r_rep=mysql_fetch_assoc($rep);
	if(mysql_num_rows($rep)>0){
		if(isset($_GET['t']) && $_GET['t']=='top'){
			echo yjl_yfimgrightu($r_rep, $r_res);
		}elseif(isset($_GET['t']) && $_GET['t']=='bottom'){
			if($user_id>0 && isset($_POST['c']) && trim($_POST['c'])!=''){
				$content=htmlspecialchars(trim($_POST['c']),ENT_QUOTES);
				require_once('../lib/jishigouapi.class.php');
				$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $app_k, $app_s, $udb['nickname'], md5($udb['nickname'].$udb['password']));
				if($r_rep['tid']>0){
					$iszf=(isset($_GET['iszf']) && $_GET['iszf']==1)?1:0;
					$fc=$iszf>0?'both':'reply';
				}else{
					$fc='first';
				}
				$jsg_result=$JishiGouAPI->AddTopic($_POST['c'], $r_rep['tid'], $fc);
				if(!isset($jsg_result['error']) && isset($jsg_result['result']['tid'])){
					$tid=$jsg_result['result']['tid'];
					if(isset($_GET['imgid']) && trim($_GET['imgid'])!=''){
						$imga=explode('_', trim($_GET['imgid']));
						foreach($imga as  $v){
							if(trim($v)!='' && intval($v)>0){
								$q_rem=sprintf('select id, tid from %s where id=%s limit 1', $dbprefix.'topic_image', intval($v));
								$rem=mysql_query($q_rem) or die('');
								$r_rem=mysql_fetch_assoc($rem);
								if(mysql_num_rows($rem)>0){
									$a_imgid[]=$r_rem['id'];
									if($r_rem['tid']==0){
										$uSQL=sprintf('update %s set tid=%s where id=%s', $dbprefix.'topic_image', $tid, $r_rem['id']);
										$result=mysql_query($uSQL) or die('');
									}
								}
								mysql_free_result($rem);
							}
						}
						if(isset($a_imgid)){
							$uSQL=sprintf('update %s set imageid=%s where tid=%s', $dbprefix.'topic', yjl_SQLString(join(',', $a_imgid), 'text'), $tid);
							$result=mysql_query($uSQL) or die('');
						}
					}
					$oid=0;
					if($user_id==$r_res['uid']){
						$oid=1;
						$uSQL=sprintf('update %s set oid=0 where oid>0 and clid=%s', $yjl_dbprefix.'jl_topic', $r_rep['clid']);
						$result=mysql_query($uSQL) or die('');
					}
					$iSQL=sprintf('insert into %s (tid, clid, uid, datetime, content, oid) values (%s, %s, %s, %s, %s, %s)', $yjl_dbprefix.'jl_topic',
						$tid,
						$r_rep['clid'],
						$user_id,
						time(),
						yjl_SQLString($content, 'text'),
						$oid);
					$result=mysql_query($iSQL) or die('');
					$uSQL=sprintf('update %s set lasttime=%s where jlid=%s', $yjl_dbprefix.'jl', time(), $r_res['jlid']);
					$result=mysql_query($uSQL) or die('');
					/**
					$iSQL=sprintf('insert into %s (tid, xqid, uid, datetime, content) values (%s, %s, %s, %s, %s)', $yjl_dbprefix.'xq_topic',
						$tid,
						$r_res['xqid'],
						$user_id,
						time(),
						yjl_SQLString($content, 'text'));
					$result=mysql_query($iSQL) or die('');
					$uSQL=sprintf('update %s set c_wb=c_wb+1 where xqid=%s', $yjl_dbprefix.'xq', $r_res['xqid']);
					$result=mysql_query($uSQL) or die('');
					**/
					yjl_addlog('[uid]评论了验房照片：<a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-home-'.$r_rep['clid'].'.html">'.yjl_substrs($content, 10).'</a>', md5('plcl|'.$r_rep['uid'].'|'.$user_id.'|'.$clid), 0, $r_rep['uid']);
					yjl_uwb($user_id, $content, $tid, '../');
				}
			}
			$_GET['t']='home';
			$_GET['id']=$jlid;
			$_GET['clid']=$clid;
			$_GET['xqid']=$r_res['xqid'];
			echo yjl_yfimgrightc($r_rep, $r_res);
		}
	}
	mysql_free_result($rep);
}
mysql_free_result($res);
?>