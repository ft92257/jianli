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
$f='a_uinfo.php';
$esid=md5($f);
$c=isset($t_m)?yjl_getMsg($t_m):'';
$id=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
if($id==$udb['uid']){
	$adb=$udb;
}else{
	$adb=yjl_udb($id);
}
if($adb['uid']>0){
	$c.='<table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td colspan="2">';
	if($adb['qx']==10){
		$c.='管理员';
	}elseif($adb['qx']==5 || $adb['qx']==6){
		$c.=$a_tsgz[$adb['qx']][$adb['gzfl']];
	}else{
		$c.='业主';
	}
	$c.='：'.($adb['nc']!=''?$adb['nc']:$adb['email']).'</td></tr><tr class="altbg1"><td>'.(($adb['qx']==5 || $adb['qx']==6)?'真实姓名':'昵称').'：</td><td>'.$adb['nc'].'</td></tr>
	<tr class="altbg2"><td>性别：</td><td>';
	if($adb['gender']==1){
		$c.='男';
	}elseif($adb['gender']==2){
		$c.='女';
	}else{
		$c.='保密';
	}
	$c.='</td></tr>
	<tr class="altbg1"><td>邮箱：</td><td>'.$adb['email'].'</td></tr>
	<tr class="altbg2"><td>手机号：</td><td>'.$adb['mobile'].(($adb['misyz']>0 && $adb['mobile']!='')?'':' 未验证').'</td></tr>';
	if($adb['qx']==5 || $adb['qx']==6){
		$q_res=sprintf('select age, cysj from %s where uid=%s limit 1', $yjl_dbprefix.'ujl', $adb['uid']);
		$res=mysql_query($q_res) or die(mysql_error());
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0){
			$adb['age']=$r_res['age'];
			$adb['cysj']=$r_res['cysj'];
		}
		mysql_free_result($res);
		$c.='<tr class="altbg1"><td>年龄：</td><td>'.$adb['age'].'岁</td></tr>
	<tr class="altbg2"><td>从业时间：</td><td>'.$adb['cysj'].'年</td></tr>
	<tr class="altbg1"><td>工种：</td><td>'.$a_tsgz[$adb['qx']][$adb['gzfl']].'</td></tr>';
	}else{
		$q_res=sprintf('select validate_true_name from %s where uid=%s limit 1', $dbprefix.'memberfields', $adb['uid']);
		$res=mysql_query($q_res) or die(mysql_error());
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0)$adb['zsname']=$r_res['validate_true_name'];
		mysql_free_result($res);
		$c.='<tr class="altbg1"><td>真实姓名：</td><td>'.$adb['zsname'].'</td></tr>
	<tr class="altbg2"><td>生日：</td><td>'.$adb['bday'].'</td></tr>
	<tr class="altbg1"><td>居住小区：</td><td>';
		if($adb['xqid']>0){
			$q_rep=sprintf('select name from %s where xqid=%s and iskf=1', $yjl_dbprefix.'xq', $adb['xqid']);
			$rep=mysql_query($q_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0)$c.=$r_rep['name'];
			mysql_free_result($rep);
		}
		$c.='</td></tr>
	<tr class="altbg2"><td>详细地址：</td><td>'.$adb['xq_0'].'栋/号 '.$adb['xq_1'].'室</td></tr>
	<tr class="altbg1"><td>邮编：</td><td>'.$adb['postcode'].'</td></tr>';
	}
	$c.='<tr class="altbg2"><td>QQ：</td><td>'.$adb['qq'].'</td></tr>
	<tr class="altbg1"><td>MSN：</td><td>'.$adb['msn'].'</td></tr>
	<tr class="altbg2"><td>'.(($adb['qx']==5 || $adb['qx']==6)?'特长':'自我介绍').'：</td><td>'.$adb['aboutme'].'</td></tr>
	<tr class="altbg1"><td>头像：</td><td><img src="'.yjl_face($adb['uid'], $adb['face'], 1).'"/></td></tr>
	</table>';
	echo yjl_adminhtml($c);
}
?>