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
$f='a_tp.php';
$esid=md5($f);
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
if(isset($_SESSION[$esid]) && $_SESSION[$esid]!=''){
	$m=array(1=>'照片已恢复！', '照片已删除！');
	if(isset($m[$_SESSION[$esid]]))$t_m=$m[$_SESSION[$esid]];
	unset($_SESSION[$esid]);
}
$c=isset($t_m)?yjl_getMsg($t_m):'';
$c.='监理和其他业主可以在上传照片后'.round($jldimg_jg/3600, 1).'小时内删除照片，删除照片保留记录，删除'.round($jldimg_zs/3600, 1).'小时后将完全删除';
$q_res=sprintf('select a.jpid, a.t_url, a.o_url, a.width, a.height, a.jlid, a.uid, a.datetime, a.deltime, b.name, b.xqid, c.nickname, d.nc from %s as a, %s as b, %s as c, %s as d where (a.isjl=0 or a.isjl=2) and a.is_del=1 and a.jlid=b.jlid and a.uid=c.uid and c.uid=d.uid order by a.deltime desc', $yjl_dbprefix.'jl_photo', $yjl_dbprefix.'jl', $dbprefix.'members', $yjl_dbprefix.'members');
$a_res=mysql_query($q_res) or die('');
$tr_res=mysql_num_rows($a_res);
if($tr_res>0){
	$c.='<table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td>照片</td><td>照片式监理</td><td>上传用户</td><td>上传时间</td><td>删除时间</td><td>&nbsp;</td></tr>';
	$tp_res=ceil($tr_res/$p_size);
	if($page>$tp_res)$page=$tp_res;
	$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
	$res=mysql_query($q_l_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	$i=0;
	do{
		if(isset($_GET['hf']) && $_GET['hf']==$r_res['jpid']){
			$uSQL=sprintf('update %s set is_del=0, deltime=0 where jpid=%s', $yjl_dbprefix.'jl_photo', $r_res['jpid']);
			$result=mysql_query($uSQL) or die('');
			$_SESSION[$esid]=1;
			echo '<script type="text/javascript">location.href=\''.$f.'?p='.$page.'\';</script>';
			exit();
		}elseif(isset($_GET['del']) && $_GET['del']==$r_res['jpid']){
			$dSQL=sprintf('delete from %s where jpid=%s', $yjl_dbprefix.'jl_photo', $r_res['jpid']);
			$result=mysql_query($dSQL) or die('');
			$dSQL=sprintf('delete from %s where jpid=%s', $yjl_dbprefix.'jl_topic', $r_res['jpid']);
			$result=mysql_query($dSQL) or die('');
			unlink($r_res['url']);
			unlink($r_res['t_url']);
			unlink($r_res['o_url']);
			if($r_res['tid']>0){
				$q_rep=sprintf('select a.tid, b.username, b.password, d.app_key, d.app_secret from %s as a, %s as b, %s as c, %s as d where a.tid=%s and a.uid=b.uid and a.tid=c.tid and c.item_id=d.id limit 1', $dbprefix.'topic', $dbprefix.'members', $dbprefix.'topic_api', $dbprefix.'app', $r_res['tid']);
				$rep=mysql_query($q_rep) or die('');
				$r_rep=mysql_fetch_assoc($rep);
				if(mysql_num_rows($rep)>0){
					require_once('../lib/jishigouapi.class.php');
					$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $r_rep['app_key'], $r_rep['app_secret'], $r_rep['nickname'], md5($r_rep['nickname'].$r_rep['password']));
					$jsg_reqult=$JishiGouAPI->DeleteTopic($r_rep['tid']);
				}
				mysql_free_result($rep);
			}
			$_SESSION[$esid]=2;
			echo '<script type="text/javascript">location.href=\''.$f.'?p='.$page.'\';</script>';
			exit();
		}
		$c.='<tr class="altbg'.(($i%2)+1).'"><td><img src="images/blank.gif" width="'.$a_wh_jltpt[0].'" height="'.$a_wh_jltpt[1].'" class="user_pic_v" style="background-image: url('.$r_res['t_url'].');" alt="" title="点击查看大图" onclick="openimg(\''.$r_res['o_url'].'\', \''.$r_res['width'].'\', \''.$r_res['height'].'\');"></td><td><a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html" target="_blank">'.$r_res['name'].'</a></td><td><a href="user-'.$r_res['uid'].'.html" target="_blank">'.$r_res['nc'].'</a></td><td>'.date('Y-m-d H:i', $r_res['datetime']).'</td><td>'.date('Y-m-d H:i', $r_res['deltime']).'</td><td><a href="?p='.$page.'&amp;hf='.$r_res['jpid'].'">恢复</a> | <a href="?p='.$page.'&amp;del='.$r_res['jpid'].'" onclick="if(!confirm(\'确定删除？\'))return false;" style="color: #f00;">完全删除</a></td></tr>';
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