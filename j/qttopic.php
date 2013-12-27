<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../'.$yjl_tpath.'setting/face.php');
require_once('../function.php');
$f='qttopic.php';
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
$q_res=sprintf('select * from %s where qtid=%s limit 1', $yjl_dbprefix.'jl_qt', $id);
$res=mysql_query($q_res) or die('');
$r_res=mysql_fetch_assoc($res);
if(mysql_num_rows($res)>0){
	$iscy=$user_id>0?1:0;
	if($user_id>0 && $udb['qx']==0 && isset($_POST['c']) && trim($_POST['c'])!=''){
		$content=htmlspecialchars(trim($_POST['c']),ENT_QUOTES);
		foreach($a_dpfl[$r_res['t1id']] as $k=>$v){
			$pf[$k]=(isset($_GET['pf'.$k]) && intval($_GET['pf'.$k])>0 && intval($_GET['pf'.$k])<=$maxpf)?intval($_GET['pf'.$k]):1;
			$pf_a[]=$v.'：'.$pf[$k];
			$pdb_0[]='pf'.$k;
			$pdb_1[]=$pf[$k];
			$pdb_2[]='pf'.$k.'c=pf'.$k.'c+'.$pf[$k].', pf'.$k.'t=pf'.$k.'t+1';
			$r_res['pf'.$k.'c']+=$pf[$k];
			$r_res['pf'.$k.'t']+=1;
		}
		$q_rep=sprintf('select app_key, app_secret from %s where id=%s limit 1', $dbprefix.'app', $r_res['app_id']);
		$rep=mysql_query($q_rep) or die('');
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0){
			$app_k=$r_rep['app_key'];
			$app_s=$r_rep['app_secret'];
		}else{
			$app_a=yjl_app('点评'.($r_res['t1id']>0?'':$a_gz[$r_res['t2id']].' ').$r_res['name'], $r_res['qtid'], $yjl_url.'review_o.php?qtid='.$r_res['qtid'], 'dpqt');
			$uSQL=sprintf('update %s set app_id=%s where qtid=%s', $yjl_dbprefix.'jl_qt', $app_a[0], $r_res['qtid']);
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
				$uSQL=sprintf('update %s set content=%s where tid=%s', $dbprefix.'topic', yjl_SQLString('点评'.($r_res['t1id']>0?'':$a_gz[$r_res['t2id']].' ').$r_res['name'].' '.$content.' '.join('，', $pf_a), 'text'), $tid);
				$repult=mysql_query($uSQL) or die('');
			}
			mysql_free_result($rep);
			$isyx=$udb['zsyz'];
			$iSQL=sprintf('insert into %s (tid, dtid, did, uid, datetime, content, %s, isyx) values (%s, 1, %s, %s, %s, %s, %s, %s)', $yjl_dbprefix.'dp',
				join(', ', $pdb_0),
				$tid,
				$r_res['qtid'],
				$udb['uid'],
				time(),
				yjl_SQLString($content, 'text'),
				join(', ', $pdb_1),
				$isyx);
			$result=mysql_query($iSQL) or die('');
			if($isyx>0){
				$uSQL=sprintf('update %s set %s where qtid=%s', $yjl_dbprefix.'jl_qt', join(', ', $pdb_2), $r_res['qtid']);
				$result=mysql_query($uSQL) or die('');
			}
			yjl_uwb($udb['uid'], '点评'.($r_res['t1id']>0?'':$a_gz[$r_res['t2id']].' ').$r_res['name'].' '.$wbc.' '.join('，', $pf_a), $tid, '../');
		}
		if($udb['xqid']>0){
			$q_rep=sprintf('select dpid from %s where isyx=1 and dtid=1 and did=%s', $yjl_dbprefix.'dp', $r_res['qtid']);
			$rep=mysql_query($q_rep) or die('');
			echo '<input type="hidden" id="i_c_yxdp" value="'.mysql_num_rows($rep).'"/>';
			mysql_free_result($rep);
			$i=0;
			$zpf=0;
			foreach($a_dpfl[$r_res['t1id']] as $k=>$v){
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
		echo yjl_qtwx($r_res);
	}else{
		$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
		$wid=(isset($_GET['wx']) && $_GET['wx']==1)?1:0;
		echo yjl_qttopic($r_res, $wid, $page);
	}
}
mysql_free_result($res);
?>