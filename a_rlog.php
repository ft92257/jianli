<?php
session_start();
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once('function.php');
$isgl=0;
if($udb['uid']>0){
	if($udb['qx']==10){
		$isgl=2;
	}elseif($udb['isxg']>0){
		$isgl=1;
	}
}
if($isgl==0)exit();
$f='a_rlog.php';
$esid=md5($f);
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
$c=isset($t_m)?yjl_getMsg($t_m):'';
$q_res=sprintf('select a.*, b.nc, c.email from %s as a, %s as b, %s as c where a.uid=b.uid and a.uid=c.uid order by a.datetime desc', $yjl_dbprefix.'rlog', $yjl_dbprefix.'members', $dbprefix.'members');
$a_res=mysql_query($q_res) or die('');
$tr_res=mysql_num_rows($a_res);
if($tr_res>0){
	$c.='<table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td>用户</td><td>手机号</td><td>发送数量</td><td>发送内容</td><td>时间</td><td>发送状态</td></tr>';
	$tp_res=ceil($tr_res/$p_size);
	if($page>$tp_res)$page=$tp_res;
	$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
	$res=mysql_query($q_l_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	$i=0;
	do{
		$c.='<tr class="altbg'.(($i%2)+1).'"><td><a href="user-'.$r_res['uid'].'.html" target="_blank">'.$r_res['nc'].' '.$r_res['email'].'</a></td><td>'.$r_res['mobile'].'</td><td>'.$r_res['c_mob'].'</td><td>'.$r_res['content'].'</td><td>'.date('Y-m-d H:i', $r_res['datetime']).'</td><td>'.$r_res['sid'].' '.$r_res['sinfo'].'</td></tr>';
		$i++;
	}while($r_res=mysql_fetch_assoc($res));
	mysql_free_result($res);
	if($tp_res>1)$paa[]=yjl_getpage($page, $tp_res);
	$c.='</table>'.(isset($paa)?'<center>'.join(' | ', $paa).'</center>':'').'<br/>';
}
mysql_free_result($a_res);
echo yjl_adminhtml($c);
?>