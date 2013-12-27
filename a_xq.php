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
$f='a_xq.php';
$esid=md5($f);
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
if(isset($_SESSION[$esid]) && $_SESSION[$esid]!=''){
	$m=array(1=>'小区已添加！', '小区已修改！', '小区已删除！');
	if(isset($m[$_SESSION[$esid]]))$t_m=$m[$_SESSION[$esid]];
	unset($_SESSION[$esid]);
}
$c=isset($t_m)?yjl_getMsg($t_m):'';
$l1id=(isset($_GET['l1id']) && intval($_GET['l1id'])>0)?intval($_GET['l1id']):0;
$l2id=(isset($_GET['l2id']) && intval($_GET['l2id'])>0)?intval($_GET['l2id']):0;
$l3id=(isset($_GET['l3id']) && intval($_GET['l3id'])>0)?intval($_GET['l3id']):0;
$l4id=(isset($_GET['l4id']) && intval($_GET['l4id'])>0)?intval($_GET['l4id']):0;
$q=(isset($_GET['q']) && trim($_GET['q'])!='')?htmlspecialchars(trim($_GET['q']),ENT_QUOTES):'';
if($r_main['dq']=='')$r_main['dq']=$d_l1id;
$a_dq=explode('|', $r_main['dq']);
foreach($a_dq as $v){
	$q_res=sprintf('select id, name from %s where id=%s and level=1 and upid=0 limit 1', $dbprefix.'common_district', $v);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0)$a_l1p[$r_res['id']]=$r_res['name'];
	mysql_free_result($res);
}
$ldb='';
$qdb='';
$tid=(isset($_GET['t']) && $_GET['t']==1)?1:0;
if($tid>0){
	$c.='<b>未开通小区</b> | <a href="'.$f.'">查看已开通小区</a>';
}else{
	$c.='<b>已开通小区</b> | <a href="?t=1">查看未开通小区</a>';
}
$c.='<br/><br/><table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td>小区列表</td></tr><tr class="altbg1"><td><form method="get" action="">'.yjl_sqse($a_l1p, $l1id, $l2id, $l3id, $l4id).'<input name="q" value="'.$q.'"><input type="submit" value="查看"/>'.($tid>0?'<input type="hidden" name="t" value="'.$tid.'"/>':'').'</form></td></tr></table><br/>';
if($l1id>0 || $q!=''){
	if($l1id>0){
		$ldb.=' and l1id='.$l1id;
		if($l2id>0){
			$ldb.=' and l2id='.$l2id;
			if($l3id>0){
				$ldb.=' and l3id='.$l3id;
				if($l4id>0)$ldb.=' and l4id='.$l4id;
			}
		}
		$a_qc[]=yjl_getsq($l1id, $l2id, $l3id, $l4id);
	}
	if($q!=''){
		$a_qc[]=$q;
		$qdb=' and name like '.yjl_SQLString($q, 'search');
	}
	$c.='查看：'.join('，', $a_qc).' | <a href="'.$f.($tid>0?'?t='.$tid:'').'">查看全部</a><br/><br/>';
}
$tdb=$tid>0?0:1;
$q_res=sprintf('select * from %s where iskf=%s%s%s order by xqid desc', $yjl_dbprefix.'xq', $tdb, $ldb, $qdb);
$a_res=mysql_query($q_res) or die('');
$tr_res=mysql_num_rows($a_res);
if($tr_res>0){
	$c.='<table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td>小区名称</td><td>地区</td><td>业主数</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
	$tp_res=ceil($tr_res/$p_size);
	if($page>$tp_res)$page=$tp_res;
	$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
	$res=mysql_query($q_l_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	$i=0;
	do{
		$q_rep=sprintf('select uid from %s where xqid=%s', $yjl_dbprefix.'members', $r_res['xqid']);
		$rep=mysql_query($q_rep) or die('');
		$c_rep=mysql_num_rows($rep);
		mysql_free_result($rep);
		if(isset($_GET['ktid']) && $_GET['ktid']==$r_res['xqid']){
			$iskf=$tid>0?1:0;
			$uSQL=sprintf('update %s set iskf=%s where xqid=%s', $yjl_dbprefix.'xq', $iskf, $r_res['xqid']);
			$result=mysql_query($uSQL) or die('');
			$_SESSION[$esid]=2;
			echo '<script type="text/javascript">location.href=\''.$f.'?p='.$page.($tid>0?'&t='.$tid:'').(isset($_GET['l1id'])?'&l1id='.$_GET['l1id']:'').(isset($_GET['l2id'])?'&l2id='.$_GET['l2id']:'').(isset($_GET['l3id'])?'&l3id='.$_GET['l3id']:'').(isset($_GET['l4id'])?'&l4id='.$_GET['l4id']:'').(isset($_GET['q'])?'&q='.$_GET['q']:'').'\';</script>';
			exit();
		}elseif(isset($_GET['del']) && $_GET['del']==$r_res['xqid'] && $tid>0 && $c_rep==0){
			if($r_res['app_id']>0){
				$dSQL=sprintf('delete from %s where id=%s', $dbprefix.'app', $r_res['app_id']);
				$result=mysql_query($dSQL) or die(mysql_error());
			}
			$dSQL=sprintf('delete from %s where xqid=%s', $yjl_dbprefix.'xq', $r_res['xqid']);
			$result=mysql_query($dSQL) or die(mysql_error());
			$_SESSION[$esid]=3;
			echo '<script type="text/javascript">location.href=\''.$f.'?p='.$page.($tid>0?'&t='.$tid:'').(isset($_GET['l1id'])?'&l1id='.$_GET['l1id']:'').(isset($_GET['l2id'])?'&l2id='.$_GET['l2id']:'').(isset($_GET['l3id'])?'&l3id='.$_GET['l3id']:'').(isset($_GET['l4id'])?'&l4id='.$_GET['l4id']:'').(isset($_GET['q'])?'&q='.$_GET['q']:'').'\';</script>';
			exit();
		}
		if(!isset($w[$r_res['l1id'].'-'.$r_res['l2id'].'-'.$r_res['l3id'].'-'.$r_res['l4id']]))$w[$r_res['l1id'].'-'.$r_res['l2id'].'-'.$r_res['l3id'].'-'.$r_res['l4id']]=yjl_getsq($r_res['l1id'], $r_res['l2id'], $r_res['l3id'], $r_res['l4id']);
		$c.='<tr class="altbg'.(($i%2)+1).'"><td>'.($tid>0?$r_res['name']:'<a href="einfo-'.$r_res['xqid'].'.html" target="_blank">'.$r_res['name'].'</a>').'</td><td>'.$w[$r_res['l1id'].'-'.$r_res['l2id'].'-'.$r_res['l3id'].'-'.$r_res['l4id']].'</td><td>'.$c_rep.'</td><td>'.($c_rep>0?'<a href="einfo-'.$r_res['xqid'].'-user.html" target="_blank">查看业主</a>':'-').'</td><td><a href="a_xgxq.php?id='.$r_res['xqid'].'">修改详情</a> | <a href="a_fx.php?id='.$r_res['xqid'].'">户型管理</a> | <a href="?p='.$page.'&amp;ktid='.$r_res['xqid'].($tid>0?'&amp;t='.$tid:'').(isset($_GET['l1id'])?'&amp;l1id='.$_GET['l1id']:'').(isset($_GET['l2id'])?'&amp;l2id='.$_GET['l2id']:'').(isset($_GET['l3id'])?'&amp;l3id='.$_GET['l3id']:'').(isset($_GET['l4id'])?'&amp;l4id='.$_GET['l4id']:'').(isset($_GET['q'])?'&amp;q='.$_GET['q']:'').'">'.($tid>0?'':'取消').'开通</a>'.(($tid>0 && $c_rep==0)?' | <a href="?p='.$page.'&amp;del='.$r_res['xqid'].($tid>0?'&amp;t='.$tid:'').(isset($_GET['l1id'])?'&amp;l1id='.$_GET['l1id']:'').(isset($_GET['l2id'])?'&amp;l2id='.$_GET['l2id']:'').(isset($_GET['l3id'])?'&amp;l3id='.$_GET['l3id']:'').(isset($_GET['l4id'])?'&amp;l4id='.$_GET['l4id']:'').(isset($_GET['q'])?'&amp;q='.$_GET['q']:'').'" onclick="if(!confirm(\'确认删除？\'))return false;" style="color: #f00;">删除</a>':'').'</td></tr>';
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