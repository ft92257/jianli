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
$f='a_dwz.php';
$esid=md5($f);
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
if(isset($_SESSION[$esid]) && $_SESSION[$esid]!=''){
	$m=array(1=>'短网址已添加！', '短网址已修改！', '短网址已删除！', '请输入相关信息！', '请使用其他短网址！');
	if(isset($m[$_SESSION[$esid]]))$t_m=$m[$_SESSION[$esid]];
	unset($_SESSION[$esid]);
}
$c=isset($t_m)?yjl_getMsg($t_m):'';
$c.='<b>短网址</b>';
$q_res=sprintf('select * from %s order by datetime desc', $yjl_dbprefix.'surl');
$a_res=mysql_query($q_res) or die('');
$tr_res=mysql_num_rows($a_res);
if($tr_res>0){
	$c.='<table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td>短网址</td><td>对应网址</td><td>最后访问</td><td>访问次数</td><td>&nbsp;</td></tr>';
	$tp_res=ceil($tr_res/$p_size);
	if($page>$tp_res)$page=$tp_res;
	$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
	$res=mysql_query($q_l_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	$i=0;
	do{
		if(isset($_GET['eid']) && $_GET['eid']==$r_res['suid']){
			$edb=$r_res;
		}elseif(isset($_GET['did']) && $_GET['did']==$r_res['suid']){
			$dSQL=sprintf('delete from %s where suid=%s', $yjl_dbprefix.'surl', $r_res['suid']);
			$result=mysql_query($dSQL) or die('');
			$_SESSION[$esid]=3;
			echo '<script type="text/javascript">location.href=\''.$f.'?p='.$page.'\';</script>';
			exit();
		}
		$c.='<tr class="altbg'.(($i%2)+1).'"><td><a href="'.$yjl_url.'u/'.$r_res['code'].'" target="_blank">'.$yjl_url.'u/'.$r_res['code'].'</a></td><td>'.$r_res['url'].'</td><td>'.($r_res['vdate']>0?date('Y-m-d H:i', $r_res['vdate']):'-').'</td><td>'.$r_res['c_v'].'</td><td><a href="?eid='.$r_res['suid'].($page>1?'&amp;p='.$page:'').'">修改</a> | <a href="?did='.$r_res['suid'].($page>1?'&amp;p='.$page:'').'" style="color: #f00;" onclick="if(!confirm(\'确认删除？\'))return false;">删除</a></td></tr>';
		$i++;
	}while($r_res=mysql_fetch_assoc($res));
	mysql_free_result($res);
	if($tp_res>1)$paa[]=yjl_getpage($page, $tp_res);
	$c.='</table>'.(isset($paa)?'<center>'.join(' | ', $paa).'</center>':'').'<br/><br/>';
}
mysql_free_result($a_res);
if($_SERVER['REQUEST_METHOD']=='POST'){
	if(isset($_POST['code']) && trim($_POST['code'])!='' && isset($_POST['url']) && trim($_POST['url'])!=''){
		$code=htmlspecialchars(trim($_POST['code']),ENT_QUOTES);
		$url=yjl_getfurl(htmlspecialchars(trim($_POST['url']),ENT_QUOTES));
		if(isset($edb)){
			$q_rep=sprintf('select code from %s where code=%s and suid<>%s limit 1', $yjl_dbprefix.'surl', yjl_SQLString($code, 'text'), $edb['suid']);
			$rep=mysql_query($q_rep) or die('');
			$c_rep=mysql_num_rows($rep);
			mysql_free_result($rep);
			if($c_rep>0){
				$_SESSION[$esid]=5;
			}else{
				$uSQL=sprintf('update %s set code=%s, url=%s where suid=%s', $yjl_dbprefix.'surl',
					yjl_SQLString($code, 'text'),
					yjl_SQLString($url, 'text'),
					$edb['suid']);
				$result=mysql_query($uSQL) or die('');
				$_SESSION[$esid]=2;
			}
		}else{
			$q_rep=sprintf('select code from %s where code=%s limit 1', $yjl_dbprefix.'surl', yjl_SQLString($code, 'text'));
			$rep=mysql_query($q_rep) or die('');
			$c_rep=mysql_num_rows($rep);
			mysql_free_result($rep);
			if($c_rep>0){
				$_SESSION[$esid]=5;
			}else{
				$iSQL=sprintf('insert into %s (uid, code, url, datetime) values (%s, %s, %s, %s)', $yjl_dbprefix.'surl',
					$user_id,
					yjl_SQLString($code, 'text'),
					yjl_SQLString($url, 'text'),
					time());
				$result=mysql_query($iSQL) or die('');
				$_SESSION[$esid]=1;
			}
		}
		echo '<script type="text/javascript">location.href=\''.$f.'?p='.$page.($exqid>0?'&id='.$exqid:'').((isset($edb) && $_SESSION[$esid]==5)?'&eid='.$edb['suid']:'').'\';</script>';
		exit();
	}else{
		$_SESSION[$esid]=4;
		echo '<script type="text/javascript">location.href=\''.$f.'?p='.$page.($exqid>0?'&id='.$exqid:'').(isset($edb)?'&eid='.$edb['suid']:'').'\';</script>';
		exit();
	}
}
$c.='<form method="post" actin="" onsubmit="if(document.form1.code.value==\'\' || document.form1.url.value==\'\'){alert(\'请输入相关信息！\');return false;}" name="form1"><table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder">';
if(isset($edb)){
	$c.='<tr class="header"><td colspan="2">修改短网址</td></tr><tr class="altbg1"><td>短网址：</td><td>'.$yjl_url.'u/<input name="code" value="'.$edb['code'].'"/></td></tr><tr class="altbg2"><td>对应网址：</td><td><input name="url" value="'.$edb['url'].'"/></td></tr>';
}else{
	$c.='<tr class="header"><td colspan="2">增加短网址</td></tr><tr class="altbg1"><td>短网址：</td><td>'.$yjl_url.'u/<input name="code"/></td></tr><tr class="altbg2"><td>对应网址：</td><td><input name="url"/></td></tr>';
}
$c.='</table><br><center><input type="submit" class="button" name="settingsubmit" value="提 交">'.(isset($edb)?' <a href="?p='.$page.'">取消</a>':'').'</center><br></form>';
echo yjl_adminhtml($c);
?>