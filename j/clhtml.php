<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../'.$yjl_tpath.'setting/face.php');
require_once('../function.php');
$f='clhtml.php';
$udb=yjl_chkulog();
$user_id=0;
if($udb['uid']>0)$user_id=$udb['uid'];
$jlid=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
$q_res=sprintf('select * from %s where jlid=%s limit 1', $yjl_dbprefix.'jl', $jlid);
$res=mysql_query($q_res) or die('');
$r_res=mysql_fetch_assoc($res);
if(mysql_num_rows($res)>0){
	if($r_res['uid']>0 && !isset($uadb[$r_res['uid']]))$uadb[$r_res['uid']]=yjl_udb($r_res['uid']);
	if(!isset($uadb[$r_res['hzid']]))$uadb[$r_res['hzid']]=yjl_udb($r_res['hzid']);
	$lid=(isset($_GET['l']) && isset($a_clys[$_GET['l']]))?$_GET['l']:1;
	$clid=(isset($_GET['cl']) && intval($_GET['cl'])>0)?intval($_GET['cl']):0;
	if(isset($_GET['t']) && $_GET['t']=='righttop'){
		if($clid>0){
			$q_rep=sprintf('select * from %s where jlid=%s and lid=%s and clid=%s limit 1', $yjl_dbprefix.'jl_cl', $jlid, $lid, $clid);
		}else{
			$q_rep=sprintf('select * from %s where jlid=%s and lid=%s order by datetime desc, clid desc limit 1', $yjl_dbprefix.'jl_cl', $jlid, $lid);
		}
		$rep=mysql_query($q_rep) or die('');
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0){
			echo yjl_climgrightu($r_rep);
		}else{
			echo '<div class="pic_titel"></div><div class="big_img"><img src="images/blank.gif" width="'.$a_wh_jltp[0].'" height="'.$a_wh_jltp[1].'" style="background: #fff;"/></div>';
		}
		mysql_free_result($rep);
	}elseif(isset($_GET['t']) && $_GET['t']=='rightcenter'){
		if($clid>0){
			$q_rep=sprintf('select * from %s where jlid=%s and lid=%s and clid=%s limit 1', $yjl_dbprefix.'jl_cl', $jlid, $lid, $clid);
		}else{
			$q_rep=sprintf('select * from %s where jlid=%s and lid=%s order by datetime desc, clid desc limit 1', $yjl_dbprefix.'jl_cl', $jlid, $lid);
		}
		$rep=mysql_query($q_rep) or die('');
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0)echo yjl_climgrightc($r_rep, $r_res);
		mysql_free_result($rep);
	}elseif(isset($_GET['t']) && $_GET['t']=='righttopic'){
		$iscy=$user_id>0?1:0;
		if($clid>0){
			$q_rep=sprintf('select * from %s where jlid=%s and lid=%s and clid=%s limit 1', $yjl_dbprefix.'jl_cl', $jlid, $lid, $clid);
		}else{
			$q_rep=sprintf('select * from %s where jlid=%s and lid=%s order by datetime desc, clid desc limit 1', $yjl_dbprefix.'jl_cl', $jlid, $lid);
		}
		$rep=mysql_query($q_rep) or die('');
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0)echo yjl_cltopic($r_rep['clid'], $r_res['hzid']);
		mysql_free_result($rep);
	}
}
mysql_free_result($res);
?>