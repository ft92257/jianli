<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../'.$yjl_tpath.'setting/face.php');
require_once('../function.php');
$f='wb.php';
$user_id=0;
if($udb['uid']>0){
	$user_id=$udb['uid'];
	$uadb[$user_id]=$udb;
}
$id=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
$q_res=sprintf('select * from %s where tid=%s limit 1', $dbprefix.'topic', $id);
$res=mysql_query($q_res) or die('');
$r_res=mysql_fetch_assoc($res);
if(mysql_num_rows($res)>0){
	$ctid=(isset($_GET['t']) && $_GET['t']==1)?1:0;
	$iscy=(isset($_GET['iscy']) && $_GET['iscy']==1)?1:0;
	if($user_id==0)$iscy=0;
	if($iscy>0){
		if($ctid==0)echo '<div id="reply_i_v_'.$r_res['tid'].'"><input class="text" style="width: 400px;" id="content_pl_'.$r_res['tid'].'" size="40"/> <input type="button" id="submit_pl_'.$r_res['tid'].'" value="发表" onclick="postpl(\''.$r_res['tid'].'\');"/><br/><input type="checkbox" name="iszf_'.$r_res['tid'].'" id="iszf_'.$r_res['tid'].'"/>同时转发微博</div>';
		if(isset($_POST['c']) && trim($_POST['c'])!=''){
			$content=htmlspecialchars(trim($_POST['c']),ENT_QUOTES);
			$q_rep=sprintf('select b.app_key, b.app_secret from %s as a, %s as b where a.tid=%s and a.item_id=b.id', $dbprefix.'topic_api', $dbprefix.'app', $r_res['tid']);
			$rep=mysql_query($q_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				require_once('../lib/jishigouapi.class.php');
				$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $r_rep['app_key'], $r_rep['app_secret'], $udb['nickname'], md5($udb['nickname'].$udb['password']));
				$iszf=(isset($_GET['iszf']) && $_GET['iszf']==1)?1:0;
				$type=$iszf>0?'both':'reply';
				$jsg_result=$JishiGouAPI->AddTopic($_POST['c'], $r_res['tid'], $type);
				if(!isset($jsg_result['error']) && isset($jsg_result['result']['tid'])){
					yjl_addlog('[uid]评论'.($iszf>0?'并转发':'').'了[luid]的微博', md5('pl|'.$r_res['uid'].'|'.$user_id), 0, $r_res['uid'], $user_id);
					$tid=$jsg_result['result']['tid'];
					$r_res['replys']++;
					yjl_uwb($udb['uid'], $content, $tid, '../');
				}
			}
			mysql_free_result($rep);
		}
	}
	if($ctid==0)echo '<div id="pllist_'.$r_res['tid'].'">';
	echo '<input type="hidden" id="plc_'.$r_res['tid'].'" value="'.$r_res['replys'].'"/>';
	if($r_res['replys']>0){
		$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
		echo '<br/>共有'.$r_res['replys'].'条评论';
		$p_size=10;
		$q_rep=sprintf('select b.*, c.nc from %s as a, %s as b, %s as c where a.tid=%s and a.replyid=b.tid and b.uid=c.uid order by b.dateline desc', $dbprefix.'topic_reply', $dbprefix.'topic', $yjl_dbprefix.'members', $r_res['tid']);
		$a_rep=mysql_query($q_rep) or die('');
		$tr_rep=mysql_num_rows($a_rep);
		if($tr_rep>0){
			$tp_rep=ceil($tr_rep/$p_size);
			if($page>$tp_rep)$page=$tp_rep;
			$q_l_rep=sprintf('%s limit %d, %d', $q_rep, ($page-1)*$p_size, $p_size);
			$rep=mysql_query($q_l_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			do{
				echo '<div style="padding: 5px;border-bottom: 1px dotted #e6e6e6;" id="wb_'.$r_rep['tid'].'"><a href="user-'.$r_rep['uid'].'.html">'.$r_rep['nc'].'</a>：'.yjl_wbdecode($r_rep['content']).' ('.yjl_wbdate($r_rep['dateline']).')';
				if($r_rep['imageid']!=''){
					$ai=explode(',', $r_rep['imageid']);
					foreach($ai as $v){
						$q_reu=sprintf('select photo, width, height from %s where id=%s limit 1', $dbprefix.'topic_image', $v);
						$reu=mysql_query($q_reu) or die('');
						$r_reu=mysql_fetch_assoc($reu);
						if(mysql_num_rows($reu)>0){
							$ou=str_replace('./', '', $r_reu['photo']);
							$bu=str_replace('_o.jpg', '_s.jpg', $ou);
							$img_a[$r_rep['tid']][$v]='<a href="#" onclick="openimg(\''.$yjl_tpath.$ou.'\', '.$r_reu['width'].', '.$r_reu['height'].');return false;" title="点击查看大图"><img src="'.$yjl_tpath.$bu.'" alt=""/></a>';
						}
						mysql_free_result($reu);
					}
					if(isset($img_a[$r_rep['tid']]))echo '<br/>'.join(' ', $img_a[$r_rep['tid']]);
				}
				echo '</div>';
			}while($r_rep=mysql_fetch_assoc($rep));
			mysql_free_result($rep);
			if($tp_rep>1){
				if(isset($_GET['isplink']) && $_GET['isplink']==1){
					echo '<div style="padding: 5px;border-bottom: 1px dotted #e6e6e6;">'.yjl_ajaxpage($page, $tp_rep, 'wbrdiv_'.$id, 'j/'.$f.'?id='.$id.'&isplink=1&iscy='.$iscy).'</div>';
				}else{
					echo '<div style="padding: 5px;border-bottom: 1px dotted #e6e6e6;"><a href="mblog-'.$r_res['uid'].'-'.$r_res['tid'].'.html">查看更多</a></div>';
				}
			}
		}
		mysql_free_result($a_rep);
	}
	if($ctid==0)echo '</div>';
}
mysql_free_result($res);
?>