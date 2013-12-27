<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once($yjl_tpath.'setting/face.php');
if(isset($_GET['rg']) && trim($_GET['rg'])!=''){
	$a_rg=explode('-', trim($_GET['rg']));
	if(isset($a_rg[0]))$_GET['xqid']=$a_rg[0];
	if(isset($a_rg[1]))$_GET['id']=$a_rg[1];
	if(isset($a_rg[2])){
		if(substr($a_rg[2], 0, 1)=='p'){
			$_GET['p']=substr($a_rg[2], 1);
		}elseif($a_rg[2]=='upload'){
			$_GET['t']=$a_rg[2];
		}else{
			$_GET['pid']=$a_rg[2];
		}
	}
	if(isset($a_rg[3])){
		if(substr($a_rg[3], 0, 1)=='p'){
			$_GET['p']=substr($a_rg[3], 1);
		}elseif(substr($a_rg[3], 0, 1)=='i'){
			$_GET['upid']=substr($a_rg[3], 1);
		}
	}
}
require_once('function.php');
$f='active_img.php';
if($udb['uid']>0){
	if(($udb['iswc']!=1 && ($udb['qx']==5 || $udb['qx']==6)) || $udb['isnc']==0){
		echo '<script type="text/javascript">location.href=\'./\';</script>';
		exit();
	}
}
if($xqid>0){
	if($user_id>0)yjl_vlog($xqid);
	$uSQL=sprintf('update %s set c_fw=c_fw+1 where xqid=%s', $yjl_dbprefix.'xq', $xqid);
	$result=mysql_query($uSQL) or die('');
	$page_title=$xqdb['name'].' 小区活动';
	$c_l1id=$xqdb['l1id'];
}else{
	$page_title='小区活动';
}
$js_c='';
$c='';
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
$hdid=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
$q_res=sprintf('select * from %s where hdid=%s', $yjl_dbprefix.'hd', $hdid);
$res=mysql_query($q_res) or die('');
$r_res=mysql_fetch_assoc($res);
if(mysql_num_rows($res)>0){
	if($r_res['xqid']>0 && $r_res['xqid']!=$xqid){
		echo '<script type="text/javascript">location.href=\'activeimg-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html\';</script>';
		exit();
	}
	if(!isset($uadb[$r_res['uid']]))$uadb[$r_res['uid']]=yjl_udb($r_res['uid']);
	if($r_res['jluid']>0 && !isset($uadb[$r_res['jluid']]))$uadb[$r_res['jluid']]=yjl_udb($r_res['jluid']);
	$page_title.=' '.$r_res['name'].' 活动照片';
	$iscy=0;
	$ispz=0;
	if($user_id>0){
		$q_rep=sprintf('select iscy from %s where uid=%s and hdid=%s limit 1', $yjl_dbprefix.'hd_user', $user_id, $hdid);
		$rep=mysql_query($q_rep) or die('');
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0){
			if($r_rep['iscy']==0){
				$iscy=1;
			}else{
				$ispz=1;
			}
		}
		mysql_free_result($rep);
	}
	$issc=(isset($_GET['t']) && $_GET['t']=='upload' && $iscy>0)?1:0;
	$c.='<div class="main_left">
			<div class="vilr_nav clearfix">
				<h2 class="h2"><span>活动照片 '.$r_res['name'].'</span></h2>';
	if($issc==0 && $iscy>0)$c.='<div class="flt_lt"></div><div class="flt_rt"><a href="activeimg-'.$xqid.'-'.$hdid.'-upload.html" class="btn bt_nomgray">添加照片</a></div>';
	$c.='</div>';
	if($issc>0){
		require_once('party_img_inc_0.php');
	}elseif(isset($_GET['pid']) && intval($_GET['pid'])>0){
		require_once('party_img_inc_1.php');
	}else{
		require_once('party_img_inc_2.php');
	}
	$c.='</div>';
}else{
	echo '<script type="text/javascript">location.href=\'active-'.$xqid.'.html\';</script>';
	exit();
}
mysql_free_result($res);
echo yjl_html($c, 'active', '', 2);
?>