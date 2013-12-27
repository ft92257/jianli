<?php
session_start();
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once('function.php');
$isgl=0;
if($udb['uid']>0){
	if($udb['qx']==10){
		$isgl=1;
	}
}
if($isgl==0)exit();
$f='a_xz.php';
$esid=md5($f);
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
if(isset($_SESSION[$esid]) && $_SESSION[$esid]!=''){
	$m=array(1=>'协助管理员已添加！', '协助管理员已取消！');
	if(isset($m[$_SESSION[$esid]]))$t_m=$m[$_SESSION[$esid]];
	unset($_SESSION[$esid]);
}
$c=isset($t_m)?yjl_getMsg($t_m):'';
$q=(isset($_GET['q']) && trim($_GET['q'])!='')?htmlspecialchars(trim($_GET['q']),ENT_QUOTES):'';
$qdb=$q!=''?' and (a.nc like '.yjl_SQLString($q, 'search').' or b.nickname like '.yjl_SQLString($q, 'search').')':'';
$tid=(isset($_GET['t']) && $_GET['t']==1)?1:0;
$tdb=$tid>0?'0':'1';
$c.='<form method="get" action=""><table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td>'.($tid>0?'普通用户':'协助管理员').($q!=''?'，搜索：'.$q:'').'</td></tr><tr class="altbg1"><td><input name="q" size="120" value="'.$q.'"/>'.($tid>0?'<input type="hidden" name="t" value="1"/>':'').'</td></tr></table><br><center><input type="submit" class="button" name="settingsubmit" value="搜 索">'.($q!=''?' <a href="'.$f.($tid>0?'?t=1':'').'">查看全部</a>':'').'</center></form><br/><br/>';
if($tid>0){
	$c.='<a href="'.$f.'">返回</a>';
}else{
	$c.='<a href="?t=1">从普通用户添加协助管理员</a>';
}
$c.='<br/><br/>';
$q_res=sprintf('select a.uid, a.qx, a.gzfl, a.isxg, a.nc, b.regdate, b.lastactivity from %s as a, %s as b where a.uid=b.uid and a.qx<9 and a.isxg=%s%s order by a.uid desc', $yjl_dbprefix.'members', $dbprefix.'members', $tdb, $qdb);
$a_res=mysql_query($q_res) or die('');
$tr_res=mysql_num_rows($a_res);
if($tr_res>0){
	$c.='<table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td>姓名</td><td>类型</td><td>注册</td><td>最近活动时间</td><td>&nbsp;</td></tr>';
	$tp_res=ceil($tr_res/$p_size);
	if($page>$tp_res)$page=$tp_res;
	$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
	$res=mysql_query($q_l_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	$i=0;
	do{
		if(isset($_GET['sh']) && $_GET['sh']==$r_res['uid']){
			$isxg=$tid>0?1:0;
			$uSQL=sprintf('update %s set isxg=%s where uid=%s', $yjl_dbprefix.'members', $isxg, $r_res['uid']);
			$result=mysql_query($uSQL) or die('');
			$_SESSION[$esid]=$tid>0?1:2;
			echo '<script type="text/javascript">location.href=\''.$f.'?p='.$page.($tid>0?'&t=1':'').'\';</script>';
			exit();
		}
		$c.='<tr class="altbg'.(($i%2)+1).'"><td><a href="user-'.$r_res['uid'].'.html" target="_blank">'.$r_res['nc'].'</a></td><td>'.(isset($a_tsgz[$r_res['qx']])?$a_tsgz[$r_res['qx']][$r_res['gzfl']]:'业主').'</td><td>'.date('Y-m-d', $r_res['regdate']).'</td><td>'.($r_res['lastactivity']>0?date('Y-m-d H:i', $r_res['lastactivity']):'未登录').'</td><td><a href="?p='.$page.'&amp;sh='.$r_res['uid'].($tid>0?'&amp;t=1':'').'">'.($tid>0?'添加':'取消').'协助管理员</a></td></tr>';
		$i++;
	}while($r_res=mysql_fetch_assoc($res));
	mysql_free_result($res);
	if($tp_res>1)$paa[]=yjl_getpage($page, $tp_res);
	$c.='</table>'.(isset($paa)?'<center>'.join(' | ', $paa).'</center>':'').'<br/>';
}else{
	$c.='没有符合条件的结果';
}
mysql_free_result($a_res);
echo yjl_adminhtml($c);
?>