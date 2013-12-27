<?php
session_start();
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once($yjl_tpath.'setting/face.php');
require_once('function.php');
$f='photo_search.php';
$jl_search_q=(isset($_GET['q']) && trim($_GET['q'])!='')?htmlspecialchars(trim($_GET['q']),ENT_QUOTES):'';
if($udb['uid']>0){
	if(($udb['iswc']!=1 && ($udb['qx']==5 || $udb['qx']==6)) || $udb['isnc']==0){
		echo '<script type="text/javascript">location.href=\'./\';</script>';
		exit();
	}
}else{
	echo '<script type="text/javascript">location.href=\'login.php?u='.urlencode($f.'?q='.$jl_search_q).'\';</script>';
	exit();
}
$c_lc=count($a_lc);
$js_c='';
$c='';
if($jl_search_q==''){
	echo '<script type="text/javascript">location.href=\'photo-'.$xqid.'.html\';</script>';
	exit();
}
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
$page_title='搜索监理项目';
$c.='<div class="main_left"><h2 class="h2">搜索监理项目：'.$jl_search_q.'</h2><div class="vilr_nav clearfix">
				<div class="flt_rt" style="margin-top: 2px;"><form method="get" action=""><input type="text" class="text" name="q" value="'.$jl_search_q.'"/>&nbsp;<input type="submit" class="submit sub_reg" value="搜 索"/></form></div></div>';
$q_res=sprintf('select a.* from %s as a, %s as b where a.hzqr=1 and a.xqid=b.xqid and (a.name like %s or b.name like %s) order by a.lasttime desc', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', yjl_SQLString($jl_search_q, 'search'), yjl_SQLString($jl_search_q, 'search'));
$a_res=mysql_query($q_res) or die(mysql_error().'-'.$q_res);
$tr_res=mysql_num_rows($a_res);
if($tr_res>0){
	$c.='<ul class="list_vilrspr">';
	$p_size=10;
	$tp_res=ceil($tr_res/$p_size);
	if($page>$tp_res)$page=$tp_res;
	$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
	$res=mysql_query($q_l_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	do{
		if($r_res['uid']>0 && !isset($uadb[$r_res['uid']]))$uadb[$r_res['uid']]=yjl_udb($r_res['uid']);
		if($r_res['hzid']>0 && !isset($uadb[$r_res['hzid']]))$uadb[$r_res['hzid']]=yjl_udb($r_res['hzid']);
		$c.=yjl_jllist($r_res);
	}while($r_res=mysql_fetch_assoc($res));
	mysql_free_result($res);
	$c.='</ul>';
	if($tp_res>1)$c.=yjl_newpage($page, $tp_res);
}else{
	$c.='没有找到符合条件的监理项目';
}
mysql_free_result($a_res);
$c.='</div><div class="main_right">'.yjl_newr_jlzx().'</div>';
echo yjl_html($c, 'supervisor', '', 2);
?>