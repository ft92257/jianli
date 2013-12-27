<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../'.$yjl_tpath.'setting/face.php');
require_once('../function.php');
$f='plike.php';
if($udb['uid']>0){
	$id=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
	$tid=(isset($_GET['t']) && intval($_GET['t'])>0)?intval($_GET['t']):0;
	if($tid==2){
		$tlid=2;
		$tlc='share';
	}elseif($tid==1){
		$tlid=1;
		$tlc='unlike';
	}else{
		$tlid=0;
		$tlc='like';
	}
	$q_rep=sprintf('select jpid, c_%s from %s where jpid=%s limit 1', $tlc, $yjl_dbprefix.'jl_photo', $id);
	$rep=mysql_query($q_rep) or die('');
	$r_rep=mysql_fetch_assoc($rep);
	if(mysql_num_rows($rep)>0){
		$q_req=sprintf('select plid from %s where jpid=%s and uid=%s and tlid=%s limit 1', $yjl_dbprefix.'jl_plike', $r_rep['jpid'], $udb['uid'], $tlid);
		$req=mysql_query($q_req) or die('');
		$r_req=mysql_fetch_assoc($req);
		if(mysql_num_rows($req)>0){
			$uSQL=sprintf('update %s set datetime=%s where plid=%s', $yjl_dbprefix.'jl_plike', time(), $r_req['plid']);
			$result=mysql_query($uSQL) or die('');
		}else{
			$iSQL=sprintf('insert into %s (jpid, uid, tlid, datetime) values (%s, %s, %s, %s)', $yjl_dbprefix.'jl_plike',
				$r_rep['jpid'],
				$udb['uid'],
				$tlid,
				time());
			$result=mysql_query($iSQL) or die('');
			$uSQL=sprintf('update %s set c_%s=c_%s+1 where jpid=%s', $yjl_dbprefix.'jl_photo', $tlc, $tlc, $r_rep['jpid']);
			$result=mysql_query($uSQL) or die('');
			$r_rep['c_'.$tlc]++;
		}
		mysql_free_result($req);
		echo $r_rep['c_'.$tlc];
	}
	mysql_free_result($rep);
}
?>