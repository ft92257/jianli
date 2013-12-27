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
$f='a_jlsm.php';
$esid=md5($f);
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
if(isset($_SESSION[$esid]) && $_SESSION[$esid]!=''){
	$m=array(1=>'已修改！');
	if(isset($m[$_SESSION[$esid]]))$t_m=$m[$_SESSION[$esid]];
	unset($_SESSION[$esid]);
}
$c=isset($t_m)?yjl_getMsg($t_m):'';
$a_m=array('未联系', '已联系', '已上门');
$tid=(isset($_GET['t']) && isset($a_m[$_GET['t']]))?$_GET['t']:0;
foreach($a_m as $k=>$v){
	if($k>0)$c.=' | ';
	if($k==$tid){
		$c.=$v;
	}else{
		$c.='<a href="'.($k>0?'?t='.$k:$f).'">'.$v.'</a>';
	}
}
$c.='<br/><br/>';
switch($tid){
	case 2:
		$tdb='a.iswc=1';
		break;
	case 1:
		$tdb='a.islx=1 and a.iswc=0';
		break;
	default:
		$tdb='a.islx=0';
		break;
}
$q_res=sprintf('select a.*, b.nc from %s as a, %s as b where a.uid=b.uid and %s order by a.datetime desc', $yjl_dbprefix.'jlsm', $yjl_dbprefix.'members', $tdb);
$a_res=mysql_query($q_res) or die('');
$tr_res=mysql_num_rows($a_res);
if($tr_res>0){
	$c.='<table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td>姓名</td><td>时间</td><td>联系方法</td><td>地址</td><td>详情</td><td>&nbsp;</td></tr>';
	$tp_res=ceil($tr_res/$p_size);
	if($page>$tp_res)$page=$tp_res;
	$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
	$res=mysql_query($q_l_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	$i=0;
	do{
		$c.='<tr class="altbg'.(($i%2)+1).'"><td>'.$r_res['name'].'（<a href="user-'.$r_res['uid'].'.html" target="_blank">'.$r_res['nc'].'）</a></td><td>'.date('Y-m-d H:i', $r_res['datetime']).'</td><td>'.$r_res['mobile'].'</td><td>'.$r_res['address'].'</td><td>'.$r_res['content'].'</td><td>';
		switch($tid){
			case 2:
				$c.='已上门';
				break;
			case 1:
				$c.='已联系，';
				if($r_res['istysm']>0){
					if(isset($_GET['smid']) && $_GET['smid']=$r_res['smid']){
						$uSQL=sprintf('update %s set iswc=1 where smid=%s', $yjl_dbprefix.'jlsm', $r_res['smid']);
						$result=mysql_query($uSQL) or die('');
						$_SESSION[$esid]=1;
						echo '<script type="text/javascript">location.href=\''.$f.'?p='.$page.($tid>0?'&t='.$tid:'').'\';</script>';
						exit();
					}
					$c.='同意上门 | <a href="?t='.$tid.'&amp;smid='.$r_res['smid'].'&amp;p='.$page.'">已上门</a>';
				}else{
					$c.='不同意上门';
				}
				break;
			default:
				if(isset($_GET['lxid']) && $_GET['lxid']=$r_res['smid']){
					$istysm=(isset($_GET['tysm']) && $_GET['tysm']==1)?0:1;
					$uSQL=sprintf('update %s set islx=1, istysm=%s where smid=%s', $yjl_dbprefix.'jlsm', $istysm, $r_res['smid']);
					$result=mysql_query($uSQL) or die('');
					$_SESSION[$esid]=1;
					echo '<script type="text/javascript">location.href=\''.$f.'?p='.$page.'\';</script>';
					exit();
				}
				$c.='<a href="?lxid='.$r_res['smid'].'&amp;p='.$page.'">已联系，同意上门</a> | <a href="?lxid='.$r_res['smid'].'&amp;tysm=1&amp;p='.$page.'">已联系，不同意上门</a>';
				break;
		}
		$c.='</td></tr>';
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