<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../'.$yjl_tpath.'setting/face.php');
require_once('../function.php');
$f='jlhtml.php';
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
	$lid=(isset($_GET['l']) && isset($a_lc[$_GET['l']]))?$_GET['l']:1;
	$isut=2;
	if(isset($_GET['ut']) && ($_GET['ut']==0 || $_GET['ut']==1 || $_GET['ut']==2))$isut=$_GET['ut'];
	if(isset($_GET['t']) && $_GET['t']=='left'){
		echo yjl_jlimgleft($jlid, $lid, $isut, $r_res);
	}elseif(isset($_GET['t']) && $_GET['t']=='righttop'){
		$jpid=(isset($_GET['jp']) && intval($_GET['jp'])>0)?intval($_GET['jp']):0;
		if($jpid>0){
			$q_rep=sprintf('select * from %s where jlid=%s and lid=%s and isjl=%s and is_del=0 and jpid=%s limit 1', $yjl_dbprefix.'jl_photo', $jlid, $lid, $isut, $jpid);
		}else{
			$q_rep=sprintf('select * from %s where jlid=%s and lid=%s and isjl=%s and is_del=0 order by isjl desc, datetime desc, jpid desc limit 1', $yjl_dbprefix.'jl_photo', $jlid, $lid, $isut);
		}
		$rep=mysql_query($q_rep) or die('');
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0){
			$r_rep['xqid']=$r_res['xqid'];
			$r_rep['hzid']=$r_res['hzid'];
			$r_rep['nc']=$uadb[$r_res['hzid']]['nc'];
			echo yjl_jlimgrightu($r_rep);
		}else{
			echo '<div class="pic_titel"></div><div class="big_img"><img src="images/blank.gif" width="'.$a_wh_jltp[0].'" height="'.$a_wh_jltp[1].'" style="background: #fff;"/></div>';
		}
		mysql_free_result($rep);
	}elseif(isset($_GET['t']) && $_GET['t']=='rightcenter'){
		$jpid=(isset($_GET['jp']) && intval($_GET['jp'])>0)?intval($_GET['jp']):0;
		if($jpid>0){
			$q_rep=sprintf('select * from %s where jlid=%s and lid=%s and isjl=%s and is_del=0 and jpid=%s limit 1', $yjl_dbprefix.'jl_photo', $jlid, $lid, $isut, $jpid);
		}else{
			$q_rep=sprintf('select * from %s where jlid=%s and lid=%s and isjl=%s and is_del=0 order by isjl desc, datetime desc, jpid desc limit 1', $yjl_dbprefix.'jl_photo', $jlid, $lid, $isut);
		}
		$rep=mysql_query($q_rep) or die('');
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0)echo yjl_jlimgrightc($r_rep, $r_res);
		mysql_free_result($rep);
	}elseif(isset($_GET['t']) && $_GET['t']=='righttopic'){
		$iscy=$user_id>0?1:0;
		$jpid=(isset($_GET['jp']) && intval($_GET['jp'])>0)?intval($_GET['jp']):0;
		if($jpid>0){
			$q_rep=sprintf('select * from %s where jlid=%s and lid=%s and isjl=%s and is_del=0 and jpid=%s limit 1', $yjl_dbprefix.'jl_photo', $jlid, $lid, $isut, $jpid);
		}else{
			$q_rep=sprintf('select * from %s where jlid=%s and lid=%s and isjl=%s and is_del=0 order by isjl desc, datetime desc, jpid desc limit 1', $yjl_dbprefix.'jl_photo', $jlid, $lid, $isut);
		}
		$rep=mysql_query($q_rep) or die('');
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0)echo yjl_jptopic($r_rep['jpid'], $r_res['hzid']);
		mysql_free_result($rep);
	}
}
mysql_free_result($res);
?>