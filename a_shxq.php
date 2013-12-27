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
$q_rep=sprintf('select uid, username from %s where role_id=2 limit 1', $dbprefix.'members');
$rep=mysql_query($q_rep) or die('');
$r_rep=mysql_fetch_assoc($rep);
if(mysql_num_rows($rep)>0)$admin_db=$r_rep;
mysql_free_result($rep);
$f='a_shxq.php';
$esid=md5($f);
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
if(isset($_SESSION[$esid]) && $_SESSION[$esid]!=''){
	$m=array(1=>'小区已通过审核！');
	if(isset($m[$_SESSION[$esid]]))$t_m=$m[$_SESSION[$esid]];
	unset($_SESSION[$esid]);
}
$c=isset($t_m)?yjl_getMsg($t_m):'';
$l1id=(isset($_GET['l1id']) && intval($_GET['l1id'])>0)?intval($_GET['l1id']):0;
$l2id=(isset($_GET['l2id']) && intval($_GET['l2id'])>0)?intval($_GET['l2id']):0;
$l3id=(isset($_GET['l3id']) && intval($_GET['l3id'])>0)?intval($_GET['l3id']):0;
$l4id=(isset($_GET['l4id']) && intval($_GET['l4id'])>0)?intval($_GET['l4id']):0;
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
$c.='<table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td>待审核小区</td></tr><tr class="altbg1"><td><form method="get" action="">'.yjl_sqse($a_l1p, $l1id, $l2id, $l3id, $l4id).'<input type="submit" value="查看"/></form></td></tr></table><br/>';
if($l1id>0){
	$ldb.=' and a.l1id='.$l1id;
	if($l2id>0){
		$ldb.=' and a.l2id='.$l2id;
		if($l3id>0){
			$ldb.=' and a.l3id='.$l3id;
			if($l4id>0)$ldb.=' and a.l4id='.$l4id;
		}
	}
	$c.='查看：'.yjl_getsq($l1id, $l2id, $l3id, $l4id).' | <a href="'.$f.'">查看全部</a><br/><br/>';
}
$q_res=sprintf('select a.misyz, b.nickname, c.* from %s as a, %s as b, %s as c where a.uid=b.uid and a.uid=c.uid and a.xqid=0 and length(c.xqname)>0%s order by a.uid desc', $yjl_dbprefix.'members', $dbprefix.'members', $yjl_dbprefix.'uyz', $ldb);
$a_res=mysql_query($q_res) or die('');
$tr_res=mysql_num_rows($a_res);
if($tr_res>0){
	$c.='<table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td>小区名称</td><td>位置</td><td>地址</td><td>添加用户</td><td>&nbsp;</td></tr>';
	$tp_res=ceil($tr_res/$p_size);
	if($page>$tp_res)$page=$tp_res;
	$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
	$res=mysql_query($q_l_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	$i=0;
	do{
		if(isset($_GET['shid']) && $_GET['shid']==$r_res['uid']){
			$iSQL=sprintf('insert into %s (name, l1id, l2id, l3id, l4id, address, iskf) values (%s, %s, %s, %s, %s, %s, 1)', $yjl_dbprefix.'xq',
				yjl_SQLString($r_res['xqname'], 'text'),
				$r_res['l1id'],
				$r_res['l2id'],
				$r_res['l3id'],
				$r_res['l4id'],
				yjl_SQLString($r_res['address'], 'text'));
			$result=mysql_query($iSQL) or die('');
			$xqid=mysql_insert_id();

			$iswc=$r_res['misyz']>0?1:0;
			$uSQL=sprintf('update %s set iswc=%s, xqid=%s where uid=%s', $yjl_dbprefix.'members',
				$iswc,
				$xqid,
				$r_res['uid']);
			$result=mysql_query($uSQL) or die('');
			$uSQL=sprintf('update %s set xqname=%s, l1id=0, l2id=0, l3id=0, l4id=0, address=%s where uid=%s', $yjl_dbprefix.'uyz',
				yjl_SQLString('', 'text'),
				yjl_SQLString('', 'text'),
				$r_res['uid']);
			$result=mysql_query($uSQL) or die('');

			$u=$yjl_url.'square-'.$xqid.'.html';
			$app_s=md5(time().'-'.rand(1,1000).'-xq-'.$xqid);
			$app_k=md5($app_s);
			$iSQL=sprintf('insert into %s (uid, username, app_name, source_url, show_from, app_desc, app_key, app_secret, status, create_time) values (%s, %s, %s, %s, 1, %s, %s, %s, 1, %s)', $dbprefix.'app',
				$admin_db['uid'],
				yjl_SQLString($admin_db['username'], 'text'),
				yjl_SQLString($r_res['xqname'], 'text'),
				yjl_SQLString($u, 'text'),
				yjl_SQLString($r_res['xqname'].' 小区广场', 'text'),
				yjl_SQLString($app_k, 'text'),
				yjl_SQLString($app_s, 'text'),
				time());
			$result=mysql_query($iSQL) or die('');
			$app_id=mysql_insert_id();

			$uSQL=sprintf('update %s set app_id=%s where xqid=%s', $yjl_dbprefix.'xq', $app_id, $xqid);
			$result=mysql_query($uSQL) or die('');
			$_SESSION[$esid]=1;
			echo '<script type="text/javascript">location.href=\''.$f.'?p='.$$page.'\';</script>';
			exit();
		}elseif(isset($_GET['bid']) && $_GET['bid']==$r_res['uid']){
			$bbd=$r_res;
		}
		if(!isset($w[$r_res['l1id'].'-'.$r_res['l2id'].'-'.$r_res['l3id'].'-'.$r_res['l4id']]))$w[$r_res['l1id'].'-'.$r_res['l2id'].'-'.$r_res['l3id'].'-'.$r_res['l4id']]=yjl_getsq($r_res['l1id'], $r_res['l2id'], $r_res['l3id'], $r_res['l4id']);
		$wz=$w[$r_res['l1id'].'-'.$r_res['l2id'].'-'.$r_res['l3id'].'-'.$r_res['l4id']];
		$c.='<tr class="altbg'.(($i%2)+1).'"><td>'.$r_res['xqname'].'</td><td>'.($wz!=''?$wz:'-').'</td><td>'.($r_res['address']!=''?$r_res['address']:'-').'</td><td>'.$r_res['nc'].' ('.$r_res['nickname'].')'.'</td><td><a href="?p='.$page.'&amp;shid='.$r_res['uid'].'">通过审核</a> | <a href="?p='.$page.'&amp;bid='.$r_res['uid'].'">并入现有小区</a></td></tr>';
		$i++;
	}while($r_res=mysql_fetch_assoc($res));
	mysql_free_result($res);
	if($tp_res>1)$paa[]=yjl_getpage($page, $tp_res);
	$c.='</table>'.(isset($paa)?'<center>'.join(' | ', $paa).'</center>':'').'<br/>';
	if(isset($bbd)){
		if(isset($_POST['xqid']) && intval($_POST['xqid'])>0){
			$xqid=intval($_POST['xqid']);
			$iswc=$bbd['misyz']>0?1:0;
			$uSQL=sprintf('update %s set iswc=%s, xqid=%s where uid=%s', $yjl_dbprefix.'members',
				$iswc,
				$xqid,
				$bbd['uid']);
			$result=mysql_query($uSQL) or die('');
			$uSQL=sprintf('update %s set xqname=%s, l1id=0, l2id=0, l3id=0, l4id=0, address=%s where uid=%s', $yjl_dbprefix.'uyz',
				yjl_SQLString('', 'text'),
				yjl_SQLString('', 'text'),
				$bbd['uid']);
			$result=mysql_query($uSQL) or die('');
			$_SESSION[$esid]=1;
			echo '<script type="text/javascript">location.href=\''.$f.'?p='.$$page.'\';</script>';
			exit();
		}
		$n_l1id=$bbd['l1id']>0?$bbd['l1id']:$d_l1id;
		$c.='<form method="post" action="" onsubmit="if($(\'#xqid\').val()==\'0\'){alert(\'请选择小区。\');return false;}"><table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td colspan="2">'.$bbd['xqname'].' 并入现有小区</td></tr><tr class="altbg1"><td valign="top">现有小区：</td><td><span id="xq_s">无</span><input type="hidden" name="xqid" id="xqid" value="0"/><br/><br/><select id="l1id" onchange="changel1();search_xq_h(1);">';
		foreach($a_l1p as $k=>$v)$c.='<option value="'.$k.'"'.($k==$n_l1id?' selected="selected"':'').'>'.$v.'</option>';
		$c.='</select> <span id="l2_s"></span><input style="display: none;" id="xq_q" onkeyup="search_xq_h(1);"/><div id="xqs_div" style="width: 600px;height: 300px; padding: 10px;border: 1px solid #eee;margin: 3px;overflow: auto;"></div></td></tr></table><br><center><input type="submit" class="button" name="settingsubmit" value="提 交"> <a href="?p='.$page.'">取消</a></center></form><div id="xq_v" style="position: absolute;top: 0;left: 0;border: 1px solid #333;padding: 10px;display: none;background: #fff;"></div>';
		$js_c='changel1();search_xq_h(1);';
	}
}else{
	$c.='没有符合条件的结果';
}
mysql_free_result($a_res);
echo yjl_adminhtml($c);
?>