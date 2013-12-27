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
$f='a_jl.php';
$esid=md5($f);
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
if(isset($_SESSION[$esid]) && $_SESSION[$esid]!=''){
	$m=array(1=>'专业人员已添加！', '专业人员已取消审核！', '专业人员已通过审核！', '专业人员已修改！');
	if(isset($m[$_SESSION[$esid]]))$t_m=$m[$_SESSION[$esid]];
	unset($_SESSION[$esid]);
}
$c=isset($t_m)?yjl_getMsg($t_m):'';
$c_pj=count($a_dpfl[0]);
$q=(isset($_GET['q']) && trim($_GET['q'])!='')?htmlspecialchars(trim($_GET['q']),ENT_QUOTES):'';
$qdb=$q!=''?' and (a.nc like '.yjl_SQLString($q, 'search').' or b.nickname like '.yjl_SQLString($q, 'search').')':'';
$tid=(isset($_GET['t']) && $_GET['t']==1)?1:0;
$c.='<form method="get" action=""><table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td>'.($tid>0?'待审核':'').'专业人员列表'.($q!=''?'，搜索：'.$q:'').'</td></tr><tr class="altbg1"><td><input name="q" size="120" value="'.$q.'"/>'.($tid>0?'<input type="hidden" name="t" value="'.$tid.'"/>':'').'</td></tr></table><br><center><input type="submit" class="button" name="settingsubmit" value="搜 索">'.($q!=''?' <a href="'.$f.($tid>0?'?t='.$tid:'').'">查看全部</a>':'').'</center></form><br/><br/>';
if($tid>0){
	$c.='<a href="'.$f.'">返回</a>';
}else{
	$c.='<a href="?t=1">待审核专业人员</a>';
}
$c.='<br/><br/>';
$tdb=$tid>0?'a.iswc=3':'(a.iswc=0 or a.iswc=1)';
$q_res=sprintf('select a.qx, a.gzfl, a.iswc, a.misyz, a.nc, a.iszxjl, b.nickname, b.email, c.* from %s as a, %s as b, %s as c where a.uid=b.uid and a.uid=c.uid and (a.qx=5 or a.qx=6) and %s%s order by a.uid desc', $yjl_dbprefix.'members', $dbprefix.'members', $yjl_dbprefix.'ujl', $tdb, $qdb);
$a_res=mysql_query($q_res) or die('');
$tr_res=mysql_num_rows($a_res);
if($tr_res>0){
	$c.='<table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td rowspan="2">姓名</td><td rowspan="2">工种</td><td rowspan="2">年龄</td><td rowspan="2">从业时间（月）</td><td colspan="'.$c_pj.'">点评</td><td rowspan="2">状态</td><td rowspan="2">&nbsp;</td><td rowspan="2">&nbsp;</td></tr><tr class="header">';
	foreach($a_dpfl[0] as $v)$c.='<td>'.$v.'</td>';
	$c.='</tr>';
	$tp_res=ceil($tr_res/$p_size);
	if($page>$tp_res)$page=$tp_res;
	$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
	$res=mysql_query($q_l_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	$i=0;
	do{
		if($tid>0){
			$iswc=($r_res['nc']!='' && $r_res['age']>0 && $r_res['cysj']>0 && $r_res['misyz']>0)?1:0;
		}else{
			$iswc=$r_res['iswc'];
		}
		if(isset($_GET['zxid']) && $_GET['zxid']==$r_res['uid']){
			$iszxjl=$r_res['iszxjl']>0?0:1;
			$uSQL=sprintf('update %s set iszxjl=%s where uid=%s', $yjl_dbprefix.'members', $iszxjl, $r_res['uid']);
			$result=mysql_query($uSQL) or die('');
			$_SESSION[$esid]=4;
			echo '<script type="text/javascript">location.href=\''.$f.'?p='.$page.($tid>0?'&t='.$tid:'').'\';</script>';
			exit();
		}elseif(isset($_GET['sh']) && $_GET['sh']==$r_res['uid']){
			$uSQL=sprintf('update %s set iswc=2 where uid=%s', $yjl_dbprefix.'members', $r_res['uid']);
			$result=mysql_query($uSQL) or die('');
			$_SESSION[$esid]=2;
			echo '<script type="text/javascript">location.href=\''.$f.'?p='.$page.($tid>0?'&t='.$tid:'').'\';</script>';
			exit();
		}elseif($tid>0 && isset($_GET['tgsh']) && $_GET['tgsh']==$r_res['uid']){
			$uSQL=sprintf('update %s set iswc=%s where uid=%s', $yjl_dbprefix.'members', $iswc, $r_res['uid']);
			$result=mysql_query($uSQL) or die('');
			$_SESSION[$esid]=3;
			echo '<script type="text/javascript">location.href=\''.$f.'?p='.$page.($tid>0?'&t='.$tid:'').'\';</script>';
			exit();
		}
		$c.='<tr class="altbg'.(($i%2)+1).'"><td><a href="user-'.$r_res['uid'].'.html" target="_blank">'.($r_res['nc']!=''?$r_res['nc']:$r_res['nickname']).'</a> '.$r_res['email'].'</td><td>'.$a_tsgz[$r_res['qx']][$r_res['gzfl']].'</td><td>'.($r_res['age']>0?$r_res['age']:'-').'</td><td>'.($r_res['cysj']>0?$r_res['cysj']:'-').'</td>';
		foreach($a_dpfl[0] as $k=>$v)$c.='<td>'.($r_res['pf'.$k.'c']>0?min(5, round($r_res['pf'.$k.'c']/$r_res['pf'.$k.'t'], 1)):'-').'</td>';
		$c.='<td>'.($iswc==0?'未':'已').'完成注册</td><td>'.($r_res['iszxjl']>0?'在线监理 | <a href="?p='.$page.'&amp;zxid='.$r_res['uid'].($tid>0?'&amp;t='.$tid:'').'">取消</a>':'<a href="?p='.$page.'&amp;zxid='.$r_res['uid'].($tid>0?'&amp;t='.$tid:'').'">设置为在线监理</a>').'</td><td>'.($tid>0?'<a href="?p='.$page.'&amp;tgsh='.$r_res['uid'].($tid>0?'&amp;t='.$tid:'').'">通过审核</a> | ':'').'<a href="?p='.$page.'&amp;sh='.$r_res['uid'].($tid>0?'&amp;t='.$tid:'').'">取消审核</a> | <a href="a_uinfo.php?id='.$r_res['uid'].'">查看用户详细资料</a></td></tr>';
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