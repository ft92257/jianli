<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../'.$yjl_tpath.'setting/face.php');
require_once('../function.php');
$f='hd.php';
$hdid=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
$q_res=sprintf('select * from %s where hdid=%s limit 1', $yjl_dbprefix.'hd', $hdid);
$res=mysql_query($q_res) or die('');
$r_res=mysql_fetch_assoc($res);
if(mysql_num_rows($res)>0){
	if(!isset($uadb[$r_res['uid']]))$uadb[$r_res['uid']]=yjl_udb($r_res['uid']);
	if($r_res['jluid']>0 && !isset($uadb[$r_res['jluid']]))$uadb[$r_res['jluid']]=yjl_udb($r_res['jluid']);
	$pid=(isset($_GET['pid']) && intval($_GET['pid'])>0)?intval($_GET['pid']):1;
	$q_rep=sprintf('select a.*, c.content, d.xqid from %s as a, %s as b, %s as c, %s as d where a.tid=b.tid and b.tid=c.tid and a.id=%s and b.hdid=%s and b.hdid=d.hdid order by a.dateline desc', $dbprefix.'topic_image', $yjl_dbprefix.'hd_topic', $dbprefix.'topic', $yjl_dbprefix.'hd', $pid, $hdid);
	$rep=mysql_query($q_rep) or die('');
	$r_rep=mysql_fetch_assoc($rep);
	if(mysql_num_rows($rep)>0){
		$up=yjl_imgpath($r_rep['id']);
		$r_rep['up']=$up;
		if(isset($_GET['t']) && $_GET['t']=='top'){
			echo yjl_hdimgrightu($r_rep, $r_res);
		}elseif(isset($_GET['t']) && $_GET['t']=='side'){
			echo yjl_hdimgside($r_rep, $r_res);
		}elseif(isset($_GET['t']) && $_GET['t']=='bottom'){
			$iscy=0;
			$ispz=0;
			if($user_id>0){
				$q_rem=sprintf('select iscy from %s where uid=%s and hdid=%s limit 1', $yjl_dbprefix.'hd_user', $user_id, $hdid);
				$rem=mysql_query($q_rem) or die('');
				$r_rem=mysql_fetch_assoc($rem);
				if(mysql_num_rows($rem)>0){
					if($r_rem['iscy']==0){
						$iscy=1;
					}else{
						$ispz=1;
					}
				}
				mysql_free_result($rem);
			}
			if($iscy>0 && isset($_POST['c']) && trim($_POST['c'])!=''){
				$q_req=sprintf('select app_key, app_secret from %s where id=%s limit 1', $dbprefix.'app', $r_res['app_id']);
				$req=mysql_query($q_req) or die('');
				$r_req=mysql_fetch_assoc($req);
				if(mysql_num_rows($req)>0){
					$app_k=$r_req['app_key'];
					$app_s=$r_req['app_secret'];
				}else{
					$app_a=yjl_app('活动 '.$r_res['name'], $r_res['hdid'], $yjl_url.'active-'.$xqid.'-'.$r_res['hdid'].'.html', 'hd');
					$uSQL=sprintf('update %s set app_id=%s where hdid=%s', $yjl_dbprefix.'hd', $app_a[0], $r_res['hdid']);
					$result=mysql_query($uSQL) or die('');
					$app_k=$app_a[1];
					$app_s=$app_a[2];
				}
				mysql_free_result($req);
				$content=htmlspecialchars(trim($_POST['c']),ENT_QUOTES);
				require_once('../lib/jishigouapi.class.php');
				$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $app_k, $app_s, $udb['nickname'], md5($udb['nickname'].$udb['password']));
				$iszf=(isset($_GET['iszf']) && $_GET['iszf']==1)?1:0;
				$fc=$iszf>0?'both':'reply';
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
					yjl_addlog('[uid]评论'.($iszf>0?'并转发':'').'了[luid]的微博', md5('pl|'.$r_rep['uid'].'|'.$user_id), 0, $r_rep['uid'], $user_id);
				}
			}
			unset($_GET['t']);
			$_GET['id']=$hdid;
			$_GET['pid']=$pid;
			$_GET['xqid']=$r_res['xqid'];
			echo yjl_hdimgrightc($r_rep, $r_res);
		}
	}
	mysql_free_result($rep);
}
mysql_free_result($res);
?>