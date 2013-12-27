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
$f='a_yz.php';
$esid=md5($f);
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
if(isset($_SESSION[$esid]) && $_SESSION[$esid]!=''){
	$m=array(1=>'业主已修改！', '业主已添加！');
	if(isset($m[$_SESSION[$esid]]))$t_m=$m[$_SESSION[$esid]];
	unset($_SESSION[$esid]);
}
$c=isset($t_m)?yjl_getMsg($t_m):'';
$q=(isset($_GET['q']) && trim($_GET['q'])!='')?htmlspecialchars(trim($_GET['q']),ENT_QUOTES):'';
$qdb=$q!=''?' and (a.nc like '.yjl_SQLString($q, 'search').' or b.nickname like '.yjl_SQLString($q, 'search').')':'';
$a_yz=array('普通业主', '准真实业主', '真实业主');
$tid=(isset($_GET['t']) && isset($a_yz[$_GET['t']]))?$_GET['t']:0;
$c.='<form method="get" action=""><table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td>'.$a_yz[$tid].($q!=''?'，搜索：'.$q:'').'</td></tr><tr class="altbg1"><td><input name="q" size="120" value="'.$q.'"/>'.($tid>0?'<input type="hidden" name="t" value="'.$tid.'"/>':'').'</td></tr></table><br><center><input type="submit" class="button" name="settingsubmit" value="搜 索">'.($q!=''?' <a href="'.$f.($tid>0?'?t='.$tid:'').'">查看全部</a>':'').'</center></form><br/><br/>';
foreach($a_yz as $k=>$v){
	if($k>0)$c.=' | ';
	if($k==$tid){
		$c.=$v;
	}else{
		$c.='<a href="?t='.$k.'">'.$v.'</a>';
	}
}
$c.='<br/><br/>';
$q_res=sprintf('select a.uid, a.isnc, a.zsyz, a.xqid, a.nc, b.nickname, b.regdate, b.lastactivity, b.email from %s as a, %s as b where a.uid=b.uid and a.qx=0 and a.zsyz=%s%s order by a.uid desc', $yjl_dbprefix.'members', $dbprefix.'members', $tid, $qdb);
$a_res=mysql_query($q_res) or die('');
$tr_res=mysql_num_rows($a_res);
if($tr_res>0){
	$c.='<table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td>姓名</td><td>状态</td><td>小区</td><td>注册</td><td>最近活动时间</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
	$tp_res=ceil($tr_res/$p_size);
	if($page>$tp_res)$page=$tp_res;
	$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
	$res=mysql_query($q_l_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	$i=0;
	do{
		if(isset($_GET['sh']) && $_GET['sh']==$r_res['uid']){
			$zsyz=(isset($_GET['m']) && isset($a_yz[$_GET['m']]))?$_GET['m']:0;
			$uSQL=sprintf('update %s set zsyz=%s where uid=%s', $yjl_dbprefix.'members', $zsyz, $r_res['uid']);
			$result=mysql_query($uSQL) or die('');
			$_SESSION[$esid]=1;
			echo '<script type="text/javascript">location.href=\''.$f.'?p='.$page.($tid>0?'&t='.$tid:'').'\';</script>';
			exit();
		}
		$c.='<tr class="altbg'.(($i%2)+1).'"><td><a href="user-'.$r_res['uid'].'.html" target="_blank">'.($r_res['nc']!=''?$r_res['nc']:$r_res['nickname']).'</a> '.$r_res['email'].'</td><td>'.($r_res['isnc']>0?'已':'未').'完成注册</td><td>';
		if($r_res['xqid']>0){
			if(!isset($xqname[$r_res['xqid']])){
				$q_rep=sprintf('select name from %s where xqid=%s and iskf=1', $yjl_dbprefix.'xq', $r_res['xqid']);
				$rep=mysql_query($q_rep) or die('');
				$r_rep=mysql_fetch_assoc($rep);
				if(mysql_num_rows($rep)>0)$xqname[$r_res['xqid']]=$r_rep['name'];
				mysql_free_result($rep);
			}
			$c.=$xqname[$r_res['xqid']];
		}else{
			$c.='-';
		}
		$c.='</td><td>'.date('Y-m-d', $r_res['regdate']).'</td><td>'.($r_res['lastactivity']>0?date('Y-m-d H:i', $r_res['lastactivity']):'未登录').'</td><td>';
		foreach($a_yz as $k=>$v){
			if($k!=$r_res['zsyz'])$c.='<a href="?p='.$page.($tid>0?'&amp;t='.$tid:'').'&amp;sh='.$r_res['uid'].'&amp;m='.$k.'">修改为'.$v.'</a>&nbsp; &nbsp;';
		}
		$c.='</td><td>';
		//$c.='<a href="#" onclick="$(\'#cztr_'.$r_res['uid'].'\').show();">充值</a> | <a href="a_clog.php?id='.$r_res['uid'].'">充值日志</a> | <a href="a_clog.php?t=1&amp;id='.$r_res['uid'].'">消费日志</a> | ';
		$c.='<a href="a_uinfo.php?id='.$r_res['uid'].'">查看用户详细资料</a></td></tr>';
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