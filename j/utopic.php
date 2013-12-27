<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../'.$yjl_tpath.'setting/face.php');
require_once('../function.php');
$f='utopic.php';
$user_id=0;
if($udb['uid']>0){
	$user_id=$udb['uid'];
	$uadb[$user_id]=$udb;
}
$iscy=$user_id>0?1:0;
if($user_id>0){
	$cuid=$user_id;
	$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
	if(isset($_POST['c']) && trim($_POST['c'])!=''){
		$content=htmlspecialchars(trim($_POST['c']),ENT_QUOTES);
		$q_rep=sprintf('select app_key, app_secret from %s where id=%s limit 1', $dbprefix.'app', $r_main['app_id']);
		$rep=mysql_query($q_rep) or die('');
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0){
			$app_k=$r_rep['app_key'];
			$app_s=$r_rep['app_secret'];
		}else{
			$app_a=yjl_app($r_main['site_name'], 0, $yjl_url, 'yjl', '个人微博');
			$uSQL=sprintf('update %s set app_id=%s', $yjl_dbprefix.'main', $app_a[0]);
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
			yjl_uwb($udb['uid'], $content, $tid, '../');
		}
	}
	$mbu[$cuid]=$cuid;
	$q_res=sprintf('select buddyid from %s where uid=%s', $dbprefix.'buddys', $cuid);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		do{
			$mbu[$r_res['buddyid']]=$r_res['buddyid'];
		}while($r_res=mysql_fetch_assoc($res));
	}
	mysql_free_result($res);
	$q_res=sprintf('select * from %s where uid in (%s) and type<>%s order by dateline desc', $dbprefix.'topic', join(', ', $mbu), yjl_SQLString('reply', 'text'));
	$a_res=mysql_query($q_res) or die('');
	$tr_res=mysql_num_rows($a_res);
	if($tr_res>0){
		$tp_res=ceil($tr_res/$p_size);
		if($page>$tp_res)$page=$tp_res;
		$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
		$res=mysql_query($q_l_res) or die('');
		$r_res=mysql_fetch_assoc($res);
		$c='<ul class="list_comment">';
		do{
			if(!isset($uadb[$r_res['uid']]))$uadb[$r_res['uid']]=yjl_udb($r_res['uid']);
			$c.=yjl_newwb($r_res);
		}while($r_res=mysql_fetch_assoc($res));
		mysql_free_result($res);
		$c.='</ul>';
		$_GET['id']=$cuid;
		if($tp_res>1)$c.=yjl_newhmpage('user-'.$cuid.'-p[p].html', $page, $tp_res, 'mblog_list');
		echo $c;
	}
	mysql_free_result($a_res);
}
?>