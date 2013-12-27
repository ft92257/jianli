<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../'.$yjl_tpath.'setting/face.php');
require_once('../function.php');
$f='dptopic.php';
$user_id=0;
$udb=yjl_chkulog();
if($udb['uid']>0){
	$user_id=$udb['uid'];
	$uadb[$user_id]=$udb;
}
$id=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
$wtid=(isset($_GET['t']) && $_GET['t']==1)?1:0;
$wid=(isset($_GET['wx']) && $_GET['wx']==1)?1:0;
$q_res=sprintf('select a.qx, a.nc, b.username, c.* from %s as a, %s as b, %s as c where a.uid=%s and a.uid=b.uid and a.uid=c.uid and (a.qx=5 || a.qx=6)', $yjl_dbprefix.'members', $dbprefix.'members', $yjl_dbprefix.'ujl', $id);
$res=mysql_query($q_res) or die('');
$r_res=mysql_fetch_assoc($res);
if(mysql_num_rows($res)>0){
	$iscy=$user_id>0?1:0;
	if($user_id>0 && $udb['qx']==0 && isset($_POST['c']) && trim($_POST['c'])!=''){
		$content=htmlspecialchars(trim($_POST['c']),ENT_QUOTES);
		foreach($a_dpfl[0] as $k=>$v){
			$pf[$k]=(isset($_GET['pf'.$k]) && intval($_GET['pf'.$k])>0 && intval($_GET['pf'.$k])<=$maxpf)?intval($_GET['pf'.$k]):1;
			$pf_a[]=$v.'：'.$pf[$k];
			$pdb_0[]='pf'.$k;
			$pdb_1[]=$pf[$k];
			$pdb_2[]='pf'.$k.'c=pf'.$k.'c+'.$pf[$k].', pf'.$k.'t=pf'.$k.'t+1';
			$r_res['pf'.$k.'c']+=$pf[$k];
			$r_res['pf'.$k.'t']+=1;
		}
		$gz=$a_tsgz[$r_res['qx']][$r_res['gzfl']];
		$q_rep=sprintf('select app_key, app_secret, app_name from %s where id=%s limit 1', $dbprefix.'app', $r_res['app_id']);
		$rep=mysql_query($q_rep) or die('');
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0){
			$app_k=$r_rep['app_key'];
			$app_s=$r_rep['app_secret'];
			if($r_rep['app_name']!='点评'.$gz.$r_res['nc']){
				$uSQL=sprintf('update %s set app_name=%s where id=%s', $dbprefix.'app', yjl_SQLString('点评'.$gz.$r_res['nc'], 'text'), $r_res['app_id']);
				$result=mysql_query($uSQL) or die('');
			}
		}else{
			$app_a=yjl_app('点评'.$gz.$r_res['nc'], $r_res['uid'], $yjl_url.'review_u.php?id='.$r_res['uid'], 'dp');
			$uSQL=sprintf('update %s set app_id=%s where uid=%s', $yjl_dbprefix.'ujl', $app_a[0], $r_res['uid']);
			$result=mysql_query($uSQL) or die('');
			$app_k=$app_a[1];
			$app_s=$app_a[2];
		}
		mysql_free_result($rep);
		require_once('../lib/jishigouapi.class.php');
		$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $app_k, $app_s, $udb['nickname'], md5($udb['nickname'].$udb['password']));
		$jsg_result=$JishiGouAPI->AddTopic($_POST['c']);
		$wbc=$content;
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
			$q_rep=sprintf('select content from %s where tid=%s limit 1', $dbprefix.'topic', $tid);
			$rep=mysql_query($q_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				$content=$r_rep['content'];
				$uSQL=sprintf('update %s set content=%s where tid=%s', $dbprefix.'topic', yjl_SQLString('点评<M '.$r_res['username'].'>@'.$r_res['nc'].'</M> '.$content.' '.join('，', $pf_a), 'text'), $tid);
				$repult=mysql_query($uSQL) or die('');
				$iSQL=sprintf('insert into %s (tid, uid, dateline) values (%s, %s, %s)', $dbprefix.'topic_mention',
					$tid,
					$r_res['uid'],
					time());
				$result=mysql_query($iSQL) or die('');
			}
			mysql_free_result($rep);
			$isyx=$udb['zsyz'];
			$iSQL=sprintf('insert into %s (tid, dtid, did, uid, datetime, content, %s, isyx) values (%s, 0, %s, %s, %s, %s, %s, %s)', $yjl_dbprefix.'dp',
				join(', ', $pdb_0),
				$tid,
				$r_res['uid'],
				$udb['uid'],
				time(),
				yjl_SQLString($content, 'text'),
				join(', ', $pdb_1),
				$isyx);
			$result=mysql_query($iSQL) or die('');
			if($isyx>0){
				$uSQL=sprintf('update %s set %s where uid=%s', $yjl_dbprefix.'ujl', join(', ', $pdb_2), $r_res['uid']);
				$result=mysql_query($uSQL) or die('');
			}
			yjl_uwb($udb['uid'], '点评@'.$r_res['nc'].' '.$wbc.' '.join('，', $pf_a), $tid, '../');
		}
		if($udb['xqid']>0){
			$q_rep=sprintf('select dpid from %s where isyx=1 and dtid=0 and did=%s', $yjl_dbprefix.'dp', $r_res['uid']);
			$rep=mysql_query($q_rep) or die('');
			echo '<input type="hidden" id="i_c_yxdp" value="'.mysql_num_rows($rep).'"/>';
			mysql_free_result($rep);
			$i=0;
			$zpf=0;
			foreach($a_dpfl[0] as $k=>$v){
				echo '<input type="hidden" id="i_c_pf_'.$k.'" value="'.($r_res['pf'.$k.'c']>0?min(5, round($r_res['pf'.$k.'c']/$r_res['pf'.$k.'t'], 1)):'-').'"/>';
				if($r_res['pf'.$k.'c']>0){
					$zpf+=min($maxpf, $r_res['pf'.$k.'c']/$r_res['pf'.$k.'t']);
					$i++;
				}
			}
			echo '<input type="hidden" id="i_c_pf" value="'.($i>0?round($zpf/$i, 1):'-').'"/>';
		}
	}
	if($wtid>0){
		echo yjl_dpwx($r_res);
	}else{
		$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
		$wid=(isset($_GET['wx']) && $_GET['wx']==1)?1:0;
		echo yjl_dptopic($r_res, $wid, $page);
	}
}
mysql_free_result($res);
?>