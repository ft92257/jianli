<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='zf.php';
if($udb['uid']>0){
	$id=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
	$q_res=sprintf('select tid, uid, username, forwards from %s where tid=%s limit 1', $dbprefix.'topic', $id);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		if(isset($_GET['c']) && trim($_GET['c'])!=''){
			$content=htmlspecialchars(trim($_GET['c']),ENT_QUOTES);
			$q_rep=sprintf('select b.app_key, b.app_secret from %s as a, %s as b where a.tid=%s and a.item_id=b.id', $dbprefix.'topic_api', $dbprefix.'app', $r_res['tid']);
			$rep=mysql_query($q_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				require_once('../lib/jishigouapi.class.php');
				$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $r_rep['app_key'], $r_rep['app_secret'], $udb['nickname'], md5($udb['nickname'].$udb['password']));
				$ispl=(isset($_GET['ispl']) && $_GET['ispl']==1)?1:0;
				$type=$ispl>0?'both':'forward';
				$jsg_result=$JishiGouAPI->AddTopic($_GET['c'], $r_res['tid'], $type);
				if(!isset($jsg_result['error']) && isset($jsg_result['result']['tid'])){
					$tid=$jsg_result['result']['tid'];
					yjl_addlog('[uid]转发'.($ispl>0?'并评论':'').'了[luid]的微博', md5('zf|'.$r_res['uid'].'|'.$user_id), 0, $r_res['uid'], $user_id);
					yjl_uwb($udb['uid'], $content, $tid, '../');
					$r_res['forwards']++;
				}
			}
			mysql_free_result($rep);
		}
		echo $r_res['forwards'];
	}
	mysql_free_result($res);
}
?>