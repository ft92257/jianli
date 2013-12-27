<?php
session_start();
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once($yjl_tpath.'setting/face.php');
if(isset($_GET['rg']) && trim($_GET['rg'])!=''){
	$a_rg=explode('-', trim($_GET['rg']));
	if(isset($a_rg[0]))$_GET['xqid']=$a_rg[0];
	if(isset($a_rg[1]))$_GET['m']=$a_rg[1];
	if(isset($a_rg[2]) && substr($a_rg[2], 0, 1)=='p')$_GET['p']=substr($a_rg[2], 1);
}
require_once('function.php');
$f='einfo.php';
if($udb['uid']>0){
	if(($udb['iswc']!=1 && ($udb['qx']==5 || $udb['qx']==6)) || $udb['isnc']==0){
		echo '<script type="text/javascript">location.href=\'./\';</script>';
		exit();
	}
}
$a_pm=array(
	array('小区概况', ''),
	array('小区户型', 'style'),
	array('小区相册', 'photo'),
	array('小区业主', 'user')
);
$mid=0;
foreach($a_pm as $k=>$v){
	if(isset($_GET['m']) && $_GET['m']==$v[1])$mid=$k;
}
if($xqid>0){
	if($user_id>0)yjl_vlog($xqid);
	$uSQL=sprintf('update %s set c_fw=c_fw+1 where xqid=%s', $yjl_dbprefix.'xq', $xqid);
	$result=mysql_query($uSQL) or die('');
	$c_l1id=$xqdb['l1id'];
	$js_c='';
	$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
	$page_title=$xqdb['name'];
	$c='<div class="main_left">
			<div class="box1 clearfix">
				<div class="left"';
	if($xqdb['url']!=''){
		$c.='><img src="'.$xqdb['url'].'" width="80" height="80" />';
	}else{
		$c.=' style="padding-left: 20px;">';
	}
	$c.='<h2>'.$xqdb['name'].'</h2>
					<div class="count">
						<a href="photo-'.$xqid.'.html" style="padding-left:0;"><b>'.$xqdb['c_jl'].'</b><br />项目</a><a href="active-'.$xqid.'.html"><b>'.$xqdb['c_hd'].'</b><br />活动</a><a href="square-'.$xqid.'.html" style="border:none;"><b>'.$xqdb['c_wb'].'</b><br />微博</a>
					</div>
					
				</div>
				<div class="right">
					<p>';
	if($xqdb['l1id']>0){
		$q_rep=sprintf('select name from %s where level=1 and id=%s limit 1', $dbprefix.'common_district', $xqdb['l1id']);
		$rep=mysql_query($q_rep) or die('');
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0){
			$a_dz[]=$r_rep['name'];
			$a_dq[]=$r_rep['name'];
			$a_mu[]=$r_rep['name'];
		}
		mysql_free_result($rep);
	}
	if($xqdb['l2id']>0){
		$q_rep=sprintf('select name from %s where level=2 and id=%s limit 1', $dbprefix.'common_district', $xqdb['l2id']);
		$rep=mysql_query($q_rep) or die('');
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0){
			$a_dz[]=$r_rep['name'];
			$a_dq[]=$r_rep['name'];
			$a_mu[]=$r_rep['name'];
		}
		mysql_free_result($rep);
	}
	if($xqdb['l3id']>0){
		$q_rep=sprintf('select name from %s where level=3 and id=%s limit 1', $dbprefix.'common_district', $xqdb['l3id']);
		$rep=mysql_query($q_rep) or die('');
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0)$a_dq[]=$r_rep['name'];
		mysql_free_result($rep);
	}
	if($xqdb['l4id']>0){
		$q_rep=sprintf('select name from %s where level=4 and id=%s limit 1', $dbprefix.'common_district', $xqdb['l4id']);
		$rep=mysql_query($q_rep) or die('');
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0)$a_dq[]=$r_rep['name'];
		mysql_free_result($rep);
	}
	$a_mu[]=$xqdb['name'];
	$c.=(isset($a_dz)?join(', ', $a_dz):'').'<br />'.$xqdb['c_fw'].'<span>&nbsp;&nbsp;次浏览</span>&nbsp;&nbsp;&nbsp;&nbsp;'.$xqdb['c_user'].'<span>&nbsp;&nbsp;位注册业主</span></p>';
	if($user_id>0 && $udb['qx']==0 && $udb['xqid']==0){
		if(isset($_GET['j']) && $_GET['j']==1){
			$uSQL=sprintf('update %s set xqid=%s where uid=%s', $yjl_dbprefix.'members',
				$xqid,
				$udb['uid']);
			$result=mysql_query($uSQL) or die('');
			$uSQL=sprintf('update %s set c_user=c_user+1 where xqid=%s', $yjl_dbprefix.'xq', $xqid);
			$result=mysql_query($uSQL) or die('');
			echo '<script type="text/javascript">location.href=\'einfo-'.$xqid.($mid>0?'-'.$a_pm[$mid][1]:'').'.html\';</script>';
			exit();
		}
		$c.='<a href="'.$f.'?xqid='.$xqid.($mid>0?'&amp;m='.$a_pm[$mid][1]:'').'&amp;j=1" class="btn bt_nomblue">我住这里</a>';
	}
	$c.='</div>
			</div>
			<div class="vge_nav">';
	foreach($a_pm as $k=>$v)$c.='<a href="einfo-'.$xqid.($v[1]!=''?'-'.$v[1]:'').'.html"'.($k==$mid?' class="current"':'').'>'.$v[0].'</a>';
	$c.='</div>
			<div class="vge_inf tabcnt">';
	switch($mid){
		case 3:
			require_once('einfo_inc_user.php');
			break;
		case 2:
			require_once('einfo_inc_img.php');
			break;
		case 1:
			require_once('einfo_inc_fx.php');
			break;
		default:
			require_once('einfo_inc_info.php');
			break;
	}
	$c.='<br /><br />
			</div>
		</div>
		<div class="main_right">'.yjl_newr_tjhd().yjl_newr_visitor();
	$map_q=$xqdb['map_q']!=''?$xqdb['map_q']:join('，', $a_mu);
	$c.='<div class="map">
				<h3>小区位置</h3>
				<div class="map_cnt">
					<a href="http://maps.baidu.com/?newmap=1&amp;s='.urlencode('con&wd='.join(',', $a_mu)).'" target="_blank"><img src="http://api.map.baidu.com/staticimage?center='.urlencode($map_q).'&amp;markers='.urlencode($map_q).'&amp;width=250&amp;height=200&amp;zoom=16" /></a>
				</div>
			</div>
		</div>';
	echo yjl_html($c, 'village');
}else{
	echo '<script type="text/javascript">location.href=\'estate-all.html\';</script>';
}
?>