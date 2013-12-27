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
if($r_main['yzqc']==0 && $r_main['yzqc_jl']==0)exit();
$f='a_yqm.php';
$esid=md5($f);
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
if(isset($_SESSION[$esid]) && $_SESSION[$esid]!=''){
	$m=array(1=>'邀请码已生成！', '邀请码已删除！');
	if(isset($m[$_SESSION[$esid]]))$t_m=$m[$_SESSION[$esid]];
	unset($_SESSION[$esid]);
}
$c=isset($t_m)?yjl_getMsg($t_m):'';
$tid=(isset($_GET['t']) && $_GET['t']==1)?1:0;
if($tid>0){
	$c.='<b>已使用邀请码</b> | <a href="'.$f.'">查看未使用邀请码</a>';
}else{
	$c.='<b>未使用邀请码</b> | <a href="?t=1">查看已使用邀请码</a>';
}
$isjl=(isset($_GET['isjl']) && $_GET['isjl']==1)?1:0;
$c.='<br/>';
if($isjl>0){
	$c.='<a href="?t='.$tid.'">业主</a> | 专业人员';
}else{
	$c.='业主 | <a href="?isjl=1'.($tid>0?'&amp;t=1':'').'">专业人员</a>';
}
$c.='<br/><br/>';
if($tid>0){
	$q_res=sprintf('select a.uid, a.code, a.datetime, a.sytime, a.syuid, a.isjl, b.nc as bname, c.nc as cname, d.email as demail from %s as a, %s as b, %s as c, %s as d where a.uid=b.uid and a.syuid=c.uid and c.uid=d.uid and a.issy=1 and a.isjl=%s order by a.sytime desc', $yjl_dbprefix.'invite', $yjl_dbprefix.'members', $yjl_dbprefix.'members', $dbprefix.'members', $isjl);
}else{
	$q_res=sprintf('select a.inid, a.uid, a.code, a.datetime, a.isjl, b.nc as bname from %s as a, %s as b where a.uid=b.uid and a.issy=0 and a.isjl=%s order by a.datetime desc', $yjl_dbprefix.'invite', $yjl_dbprefix.'members', $isjl);
}
$a_res=mysql_query($q_res) or die('');
$tr_res=mysql_num_rows($a_res);
if($tr_res>0){
	$c.='<table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td>邀请码</td><td>类型</td><td>生成用户</td><td>时间</td>'.($tid>0?'<td>使用时间</td><td>注册用户</td>':'<td>&nbsp;</td>').'</tr>';
	$tp_res=ceil($tr_res/$p_size);
	if($page>$tp_res)$page=$tp_res;
	$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
	$res=mysql_query($q_l_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	$i=0;
	do{
		$c.='<tr class="altbg'.(($i%2)+1).'"><td>'.($tid>0?substr($r_res['code'], 0, 4).'******'.substr($r_res['code'], -4):$r_res['code'].'<br/><input onclick="$(this).select();" size="60" value="'.$yjl_url.($r_res['isjl']>0?'reg_pro':'reg').'.php?code='.$r_res['code'].'">').'</td><td>'.($r_res['isjl']>0?'专业人员':'业主').'</td><td><a href="user-'.$r_res['uid'].'.html" target="_blank">'.$r_res['bname'].'</a></td><td>'.date('Y-m-d H:i', $r_res['datetime']).'</td>';
		if($tid>0){
			$c.='<td>'.date('Y-m-d H:i', $r_res['sytime']).'</td><td><a href="user-'.$r_res['syuid'].'.html" target="_blank">'.($r_res['cname']!=''?$r_res['cname']:$r_res['demail']).'</a></td>';
		}else{
			if(isset($_GET['delid']) && $_GET['delid']==$r_res['inid']){
				$dSQL=sprintf('delete from %s where inid=%s', $yjl_dbprefix.'invite', $r_res['inid']);
				$result=mysql_query($dSQL) or die('');
				$_SESSION[$esid]=2;
				echo '<script type="text/javascript">location.href=\''.$f.'?p='.$page.'&t='.$tid.'&isjl='.$isjl.'\';</script>';
				exit();
			}
			$c.='<td><a href="?p='.$page.'&delid='.$r_res['inid'].'&t='.$tid.'&isjl='.$isjl.'" style="color: #f00;" onclick="if(!confirm(\'确定删除？\'))return false;">删除</a></td>';
		}
		$c.='</tr>';
		$i++;
	}while($r_res=mysql_fetch_assoc($res));
	mysql_free_result($res);
	if($tp_res>1)$paa[]=yjl_getpage($page, $tp_res);
	$c.='</table>'.(isset($paa)?'<center>'.join(' | ', $paa).'</center>':'').'<br/>';
}else{
	$c.='没有符合条件的结果<br/><br/>';
}
mysql_free_result($a_res);
if($tid==0){
	if($_SERVER['REQUEST_METHOD']=='POST'){
		if(isset($_POST['sl']) && intval($_POST['sl'])>0){
			$sl=intval($_POST['sl']);
			for($i=0;$i<$sl;$i++){
				$code=substr(md5(time.'|'.$i.'|'.$udb['uid'].'|'.rand(0,9999)), 0, 16);
				$iSQL=sprintf('insert into %s (uid, datetime, code, isjl) values (%s, %s, %s, %s)', $yjl_dbprefix.'invite',
					$udb['uid'],
					time(),
					yjl_SQLString($code, 'text'),
					$_POST['isjl']);
				$result=mysql_query($iSQL);
			}
			$_SESSION[$esid]=1;
		}
		echo '<script type="text/javascript">location.href=\''.$f.'\';</script>';
		exit();
	}
	$c.='<form method="post" action=""><table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td colspan="2">生成邀请码</td></tr><tr class="altbg1"><td>数量：</td><td><input name="sl" size="5"/></td></tr><tr class="altbg2"><td>&nbsp;</td><td><input name="isjl" type="radio" value="0"'.($isjl==0?' checked="checked"':'').'/>业主 <input name="isjl" type="radio" value="1"'.($isjl==1?' checked="checked"':'').'/>专业人员</td></tr></table><br><center><input type="submit" class="button" name="settingsubmit" value="生 成"></center><br></form>';
}
echo yjl_adminhtml($c);
?>