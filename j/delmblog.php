<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='delmblog.php';
if($user_id>0){
	$adb=($udb['qx']==10 || $udb['isxg']>0)?'':' and a.uid='.$user_id;
	$id=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
	$q_res=sprintf('select a.tid, b.nickname, b.password from %s as a, %s as b where a.uid=b.uid and a.tid=%s%s limit 1', $dbprefix.'topic', $dbprefix.'members', $id, $adb);
	$fc=$q_res;
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$q_rep=sprintf('select b.id, b.app_key, b.app_secret from %s as a, %s as b where a.tid=%s and a.item_id=b.id', $dbprefix.'topic_api', $dbprefix.'app', $id);
		$rep=mysql_query($q_rep) or die('');
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0){
			require_once('../lib/jishigouapi.class.php');
			$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $r_rep['app_key'], $r_rep['app_secret'], $r_res['nickname'], md5($r_res['nickname'].$r_res['password']));
			$jsg_result=$JishiGouAPI->DeleteTopic($r_res['tid']);
			$issc=0;
			if($issc==0){
				$q_req=sprintf('select jlid from %s where app_id=%s limit 1', $yjl_dbprefix.'jl', $r_rep['id']);
				$req=mysql_query($q_req) or die('');
				if(mysql_num_rows($req)>0){
					$issc=1;
					$dSQL=sprintf('delete from %s where tid=%s', $yjl_dbprefix.'jl_topic', $id);
					$result=mysql_query($dSQL) or die('');
					$dSQL=sprintf('delete from %s where tid=%s', $yjl_dbprefix.'xq_topic', $id);
					$result=mysql_query($dSQL) or die('');
				}
				mysql_free_result($req);
			}
			if($issc==0){
				$q_req=sprintf('select xzid from %s where app_id=%s limit 1', $yjl_dbprefix.'xz', $r_rep['id']);
				$req=mysql_query($q_req) or die('');
				$r_req=mysql_fetch_assoc($req);
				if(mysql_num_rows($req)>0){
					$issc=1;
					$dSQL=sprintf('delete from %s where xzid=%s and tid=%s', $yjl_dbprefix.'xz_topic', $r_req['xzid'], $id);
					$result=mysql_query($dSQL) or die('');
				}
				mysql_free_result($req);
			}
			if($issc==0){
				$q_req=sprintf('select hdid from %s where app_id=%s limit 1', $yjl_dbprefix.'hd', $r_rep['id']);
				$req=mysql_query($q_req) or die('');
				$r_req=mysql_fetch_assoc($req);
				if(mysql_num_rows($req)>0){
					$issc=1;
					$dSQL=sprintf('delete from %s where hdid=%s and tid=%s', $yjl_dbprefix.'hd_topic', $r_req['hdid'], $id);
					$result=mysql_query($dSQL) or die('');
				}
				mysql_free_result($req);
			}
			if($issc==0){
				$q_req=sprintf('select * from %s where app_id=%s limit 1', $yjl_dbprefix.'ujl', $r_rep['id']);
				$req=mysql_query($q_req) or die('');
				$r_req=mysql_fetch_assoc($req);
				if(mysql_num_rows($req)>0){
					$issc=1;
					$q_rem=sprintf('select * from %s where dtid=0 and tid=%s and did=%s limit 1', $yjl_dbprefix.'dp', $id, $r_req['uid']);
					$rem=mysql_query($q_rem) or die('');
					$r_rem=mysql_fetch_assoc($rem);
					if(mysql_num_rows($rem)>0){
						foreach($a_dpfl[0] as $k=>$v){
							$r_req['pf'.$k.'t']-=$r_rem['pf'.$k];
							if($r_req['pf'.$k.'t']<0)$r_req['pf'.$k.'t']=0;
							$r_req['pf'.$k.'c']-=1;
							if($r_req['pf'.$k.'c']<0)$r_req['pf'.$k.'c']=0;
							$a_pdb[$r_req['uid']][]='pf'.$k.'t='.$r_req['pf'.$k.'t'].', pf'.$k.'c='.$r_req['pf'.$k.'c'];
						}
						$uSQL=sprintf('update %s set %s where uid=%s', $yjl_dbprefix.'ujl', join(', ', $a_pdb[$r_req['uid']]), $r_req['uid']);
						$result=mysql_query($uSQL) or die('');
						$dSQL=sprintf('delete from %s where dpid=%s', $yjl_dbprefix.'dp', $r_rem['dpid']);
						$result=mysql_query($dSQL) or die('');
					}
					mysql_free_result($rem);
				}
				mysql_free_result($req);
			}
			if($issc==0){
				$q_req=sprintf('select * from %s where app_id=%s limit 1', $yjl_dbprefix.'jl_qt', $r_rep['id']);
				$req=mysql_query($q_req) or die('');
				$r_req=mysql_fetch_assoc($req);
				if(mysql_num_rows($req)>0){
					$issc=1;
					$q_rem=sprintf('select * from %s where dtid=1 and tid=%s and did=%s limit 1', $yjl_dbprefix.'dp', $id, $r_req['qtid']);
					$rem=mysql_query($q_rem) or die('');
					$r_rem=mysql_fetch_assoc($rem);
					if(mysql_num_rows($rem)>0){
						foreach($a_dpfl[$r_req['t1id']] as $k=>$v){
							$r_req['pf'.$k.'t']-=$r_rem['pf'.$k];
							if($r_req['pf'.$k.'t']<0)$r_req['pf'.$k.'t']=0;
							$r_req['pf'.$k.'c']-=1;
							if($r_req['pf'.$k.'c']<0)$r_req['pf'.$k.'c']=0;
							$a_mdb[$r_req['qtid']][]='pf'.$k.'t='.$r_req['pf'.$k.'t'].', pf'.$k.'c='.$r_req['pf'.$k.'c'];
						}
						$uSQL=sprintf('update %s set %s where qtid=%s', $yjl_dbprefix.'jl_qt', join(', ', $a_mdb[$r_req['qtid']]), $r_req['qtid']);
						$result=mysql_query($uSQL) or die('');
						$dSQL=sprintf('delete from %s where dpid=%s', $yjl_dbprefix.'dp', $r_rem['dpid']);
						$result=mysql_query($dSQL) or die('');
					}
					mysql_free_result($rem);
				}
				mysql_free_result($req);
			}
			if($issc==0){
				if($r_rep['id']==$r_main['qz_app_id']){
					$issc=1;
					$dSQL=sprintf('delete from %s where tid=%s', $yjl_dbprefix.'qz_topic', $id);
					$result=mysql_query($dSQL) or die('');
					$q_req=sprintf('select qzid from %s where tid=%s limit 1', $yjl_dbprefix.'qz', $id);
					$req=mysql_query($q_req) or die('');
					$r_req=mysql_fetch_assoc($req);
					if(mysql_num_rows($req)>0){
						$dSQL=sprintf('delete from %s where qzid=%s', $yjl_dbprefix.'qz_topic', $r_req['qzid']);
						$result=mysql_query($dSQL) or die('');
						$dSQL=sprintf('delete from %s where qzid=%s', $yjl_dbprefix.'qz', $r_req['qzid']);
						$result=mysql_query($dSQL) or die('');
					}
					mysql_free_result($req);
				}
			}
			if($issc==0){
				$q_req=sprintf('select xqid from %s where app_id=%s limit 1', $yjl_dbprefix.'xq', $r_rep['id']);
				$req=mysql_query($q_req) or die('');
				$r_req=mysql_fetch_assoc($req);
				if(mysql_num_rows($req)>0){
					$issc=1;
					$dSQL=sprintf('delete from %s where xqid=%s and tid=%s', $yjl_dbprefix.'xq_topic', $r_req['xqid'], $id);
					$result=mysql_query($dSQL) or die('');
				}
				mysql_free_result($req);
			}
		}
		mysql_free_result($rep);
	}
	mysql_free_result($res);
}
?>