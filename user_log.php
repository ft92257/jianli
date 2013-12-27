<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once('function.php');
$f='user_log.php';
if($udb['uid']>0){
	if(($udb['iswc']!=1 && ($udb['qx']==5 || $udb['qx']==6)) || $udb['isnc']==0){
		echo '<script type="text/javascript">location.href=\'./\';</script>';
		exit();
	}
}else{
	echo '<script type="text/javascript">location.href=\'login.php\';</script>';
	exit();
}
$cuid=$user_id;
$page_title=$uadb[$cuid]['nc'].' 最新动态';
$c='';
if($user_id>0 && $user_id!=$cuid)yjl_vlog($cuid, 2);
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
$mbu[$cuid]=$cuid;
$q_res=sprintf('select buddyid from %s where uid=%s', $dbprefix.'buddys', $cuid);
$res=mysql_query($q_res) or die('');
$r_res=mysql_fetch_assoc($res);
if(mysql_num_rows($res)>0){
	do{
		$mbu[$r_res['buddyid']]=$r_res['buddyid'];
	}while($r_res=mysql_fetch_assoc($res));
}
mysql_free_result($res);
$c.='<h3 class="tit">最新动态</h3>';
$q_res=sprintf('select * from %s where (luid in (%s) or uid in (%s)) and (uid=%s or luid=%s or isgk=0) order by datetime desc', $yjl_dbprefix.'log', join(', ', $mbu), join(', ', $mbu), $cuid, $cuid);
$a_res=mysql_query($q_res) or die('');
$tr_res=mysql_num_rows($a_res);
if($tr_res>0){
	$p_size=20;
	$c.='<ul class="list_dynamic hover">';
	$tp_res=ceil($tr_res/$p_size);
	if($page>$tp_res)$page=$tp_res;
	$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
	$res=mysql_query($q_l_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	do{
		if($r_res['isnew']>0){
			$uSQL=sprintf('update %s set isnew=0 where loid=%s', $yjl_dbprefix.'log', $r_res['loid']);
			$result=mysql_query($uSQL) or die('');
		}
		$uname='用户';
		if($r_res['uid']>0){
			if(!isset($uadb[$r_res['uid']]))$uadb[$r_res['uid']]=yjl_udb($r_res['uid']);
			if(isset($uadb[$r_res['uid']]['nc']))$uname=$uadb[$r_res['uid']]['nc']!=''?$uadb[$r_res['uid']]['nc']:'用户';
		}
		if($r_res['luid']>0){
			if(!isset($uadb[$r_res['luid']]))$uadb[$r_res['luid']]=yjl_udb($r_res['luid']);
			$luname=$uadb[$r_res['luid']]['nc']!=''?$uadb[$r_res['luid']]['nc']:'用户';
		}
		$c.='<li><div class="flt_rt">'.yjl_wbdate($r_res['datetime']).'</div>'.str_replace(array('[uid]', '[luid]'), array('<a href="user-'.$r_res['uid'].'.html">'.$uname.'</a>', ($r_res['luid']>0?'<a href="user-'.$r_res['luid'].'.html">'.$luname.'</a>':'')), $r_res['content']).'</li>';
	}while($r_res=mysql_fetch_assoc($res));
	mysql_free_result($res);
	$c.='</ul>';
	if($tp_res>1)$c.=yjl_newpage($page, $tp_res);
}
mysql_free_result($a_res);
$c.='<br class="clear" /><br /><br /></div>';
echo yjl_gehtml(yjl_userl($cuid, 'dt'), $c, '最新动态');
?>