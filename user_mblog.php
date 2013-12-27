<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once('function.php');
require_once('fun_user.php');
$f='user.php';
if($udb['uid']>0){
	if(($udb['iswc']!=1 && ($udb['qx']==5 || $udb['qx']==6)) || $udb['isnc']==0){
		echo '<script type="text/javascript">location.href=\'./\';</script>';
		exit();
	}
}else{
	echo '<script type="text/javascript">location.href=\'login.php\';</script>';
	exit();
}
$js_c='';
$cuid=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):$udb['uid'];
if($cuid!=$udb['uid']){
	$cudb=yjl_udb($cuid);
	if($cudb['uid']==0){
		echo '<script type="text/javascript">location.href=\'./\';</script>';
		exit();
	}
	$uadb[$cuid]=$cudb;
}
if($xqid==0 && $uadb[$cuid]['xqid']>0)$xqid=$uadb[$cuid]['xqid'];
if($xqid>0){
	$q_rep=sprintf('select * from %s where xqid=%s and iskf=1 limit 1', $yjl_dbprefix.'xq', $xqid);
	$rep=mysql_query($q_rep) or die('');
	$r_rep=mysql_fetch_assoc($rep);
	if(mysql_num_rows($rep)>0){
		$xqdb=$r_rep;
		$c_l1id=$xqdb['l1id'];
	}
	mysql_free_result($rep);
}
$a_uys=yjl_getys($uadb[$cuid], $udb);
$c='';
$page_title=$uadb[$cuid]['nc'].' 微博';
$tid=(isset($_GET['tid']) && intval($_GET['tid'])>0)?intval($_GET['tid']):1;
$q_res=sprintf('select * from %s where uid=%s and tid=%s limit 1', $dbprefix.'topic', $cuid, $tid);
$res=mysql_query($q_res) or die('');
$r_res=mysql_fetch_assoc($res);
if(mysql_num_rows($res)>0){
	$c.='<div class="main_left">
			<div class="comm">
				<div class="vge_nav">
					<a href="user.php?id='.$cuid.'" class="current">微博</a>'.($user_id!=$cuid?'<a href="user_party.php?id='.$cuid.'">TA的活动</a><a href="user_decoration.php?id='.$cuid.'">TA的项目</a>':'').'
				</div>
				<div class="vge_inf">
					<ul class="list_comment">';
	if(in_array($uadb[$cuid]['ys_0'], $a_uys)){
		$js_c.='show_reply(\''.$r_res['tid'].'\', 1, 1);';
		$isplink=1;
		$c.=yjl_newwb($r_res);
	}else{
		$c.='<li>无权查看</li>';
	}
	$c.='<br /><br />
				</div>
			</div>
		</div>
		<div class="main_right margtop">'.yjl_user_info().'</div>';
	echo yjl_html($c, 'per_index');
}else{
	echo '<script type="text/javascript">location.href=\'./\';</script>';
	exit();
}
mysql_free_result($res);
?>