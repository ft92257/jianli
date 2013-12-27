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
$f='a_xgxq.php';
$esid=md5($f);
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
if(isset($_SESSION[$esid]) && $_SESSION[$esid]!=''){
	$m=array(1=>'小区已修改！', '请输入名称！');
	if(isset($m[$_SESSION[$esid]]))$t_m=$m[$_SESSION[$esid]];
	unset($_SESSION[$esid]);
}
$c=isset($t_m)?yjl_getMsg($t_m):'';
if(isset($_GET['id']) && intval($_GET['id'])>0){
	$q_res=sprintf('select * from %s where xqid=%s limit 1', $yjl_dbprefix.'xq', intval($_GET['id']));
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$exqdb=$r_res;
	}else{
		echo '<script type="text/javascript">location.href=\'a_xq.php\';</script>';
		exit();
	}
	mysql_free_result($res);
}
$exqid=$exqdb['xqid'];
$img_wh=80;
if($_SERVER['REQUEST_METHOD']=='POST'){
	$name=htmlspecialchars(trim($_POST['name']),ENT_QUOTES);
	$l1id=intval($_POST['l1id']);
	if($name!='' && $l1id>0){
		$l2id=isset($_POST['l2id'])?$_POST['l2id']:0;
		$l3id=isset($_POST['l3id'])?$_POST['l3id']:0;
		$l4id=isset($_POST['l4id'])?$_POST['l4id']:0;
		$address=htmlspecialchars(trim($_POST['address']),ENT_QUOTES);
		$xqjj=htmlspecialchars(trim($_POST['xqjj']),ENT_QUOTES);
		$jtzk=htmlspecialchars(trim($_POST['jtzk']),ENT_QUOTES);
		$zbxx=htmlspecialchars(trim($_POST['zbxx']),ENT_QUOTES);
		$map_q=htmlspecialchars(trim($_POST['map_q']),ENT_QUOTES);
		$url=$exqdb['url'];
		$f_a=$_FILES['url'];
		$e=0;
		if($f_a['name']!=''){
			if(is_uploaded_file($f_a['tmp_name']) && $f_a['error']==0){
				$u_f='file/';
				$u_s=$max_file*1024;
				if($f_a['size']>$u_s && $u_s>0)$e=5;
				$f_e=explode('.', $f_a['name']);
				$f_e=strtolower($f_e[count($f_e)-1]);
				if(isset($u_ea) && !in_array($f_e, $u_ea))$e=5;
				if(!is_dir($u_f) && is_writeable($u_f))$e=4;
				if($e==0){
					$f_m=md5(time().rand(0,1000));
					if(@copy($f_a['tmp_name'], $u_f.$f_m.'.'.$f_e)){
						$photo=$u_f.$f_m.'.'.$f_e;
						$data=GetImageSize($photo);
						if($data && $data[2]<=3){
							$sw=$data[0];
							$sh=$data[1];
							$w=$img_wh;
							$h=$img_wh;
							if($sw>$w || $sh>$h){
								switch($data[2]){
									case 1:
										$im=@imagecreatefromgif($photo);
										break;
									case 2:
										$im=@imagecreatefromjpeg($photo);
										break;
									case 3:
										$im=@imagecreatefrompng($photo);
										break;
								}
								$xy=yjl_imgxy($sw, $sh, $w, $h);
								$ni=imagecreatetruecolor($xy[4],$xy[5]);
								imagecopyresampled($ni,$im,$xy[0],$xy[1],0,0,$xy[2],$xy[3],$sw,$sh);
								imagejpeg($ni, $photo);
								imagedestroy($im);
								imagedestroy($ni);
							}
							if($exqdb['url']!='' && file_exists($exqdb['url']))unlink($exqdb['url']);
							$url=$photo;
						}else{
							$e=5;
							unlink($photo);
						}
					}else{
						$e=5;
					}
				}
			}else{
				$e=5;
			}
		}
		$uSQL=sprintf('update %s set name=%s, l1id=%s, l2id=%s, l3id=%s, l4id=%s, address=%s, url=%s, xqjj=%s, jtzk=%s, zbxx=%s, map_q=%s, pyid=%s where xqid=%s', $yjl_dbprefix.'xq',
			yjl_SQLString($name, 'text'),
			$l1id,
			$l2id,
			$l3id,
			$l4id,
			yjl_SQLString($address, 'text'),
			yjl_SQLString($url, 'text'),
			yjl_SQLString($xqjj, 'text'),
			yjl_SQLString($jtzk, 'text'),
			yjl_SQLString($zbxx, 'text'),
			yjl_SQLString($map_q, 'text'),
			$_POST['pyid'],
			$exqdb['xqid']);
		$result=mysql_query($uSQL) or die('');
		if($name!=$exqdb['name']){
			$uSQL=sprintf('update %s set app_name=%s, app_desc=%s where id=%s', $dbprefix.'app', yjl_SQLString($name, 'text'), yjl_SQLString($name.' 小区广场', 'text'), $exqdb['app_id']);
			$result=mysql_query($uSQL) or die('');
		}
		$_SESSION[$esid]=1;
	}else{
		$_SESSION[$esid]=2;
	}
	echo '<script type="text/javascript">location.href=\''.$f.'?id='.$exqid.'\';</script>';
	exit();
}
if(isset($_GET['delimg']) && $_GET['delimg']==1){
	$uSQL=sprintf('update %s set url=%s where xqid=%s', $yjl_dbprefix.'xq',
		yjl_SQLString('', 'text'),
		$exqdb['xqid']);
	$result=mysql_query($uSQL) or die('');
	if($exqdb['url']!='' && file_exists($exqdb['url']))unlink($exqdb['url']);
	$_SESSION[$esid]=1;
	echo '<script type="text/javascript">location.href=\''.$f.'?id='.$exqid.'\';</script>';
	exit();
}
if($r_main['dq']=='')$r_main['dq']=$d_l1id;
$a_dq=explode('|', $r_main['dq']);
foreach($a_dq as $v){
	$q_res=sprintf('select id, name from %s where id=%s and level=1 and upid=0 limit 1', $dbprefix.'common_district', $v);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0)$a_l1p[$r_res['id']]=$r_res['name'];
	mysql_free_result($res);
}
$c.='<form method="post" actin="" onsubmit="if(document.form1.name.value==\'\'){alert(\'请输入名称！\');return false;}" enctype="multipart/form-data" name="form1"><table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td colspan="2">修改小区：'.$exqdb['name'].'</td></tr><tr class="altbg1"><td>名称：</td><td><input name="name" value="'.$exqdb['name'].'"/></td></tr><tr class="altbg2"><td>地区：</td><td>'.yjl_sqse($a_l1p, $exqdb['l1id'], $exqdb['l2id'], $exqdb['l3id'], $exqdb['l4id']).'</td></tr><tr class="altbg1"><td>地址：</td><td><input name="address" value="'.$exqdb['address'].'"/></td></tr><tr class="altbg2"><td>拼音索引：</td><td><select name="pyid"><option value="0"></option>';
foreach($a_py as $k=>$v)$c.='<option value="'.$k.'"'.($k==$exqdb['pyid']?' selected="selected"':'').'>'.$v.'</option>';
$c.='</select></td></tr><tr class="altbg1"><td>百度地图关键词：</td><td><input name="map_q" value="'.$exqdb['map_q'].'"/></td></tr><tr class="altbg2"><td valign="top">图片：</td><td>'.($exqdb['url']!=''?'<img src="'.$exqdb['url'].'" width="'.$img_wh.'" height="'.$img_wh.'"/><br/><a href="?id='.$exqid.'&amp;delimg=1" onclick="if(!confirm(\'确定删除？\'))return false;" style="color: #f00;">删除</a><br/>':'').'<input type="file" name="url"/><br/>图片尺寸：'.$img_wh.'px*'.$img_wh.'px，允许类型：'.join('、', $u_ea).'，最大：'.$max_file.'KB<br/>上传新图片将自动删除原图片</td></tr><tr class="altbg1"><td>小区简介：</td><td><textarea name="xqjj" cols="100" rows="5">'.$exqdb['xqjj'].'</textarea></td></tr><tr class="altbg2"><td>交通状况：</td><td><textarea name="jtzk" cols="100" rows="5">'.$exqdb['jtzk'].'</textarea></td></tr><tr class="altbg1"><td>周边信息：</td><td><textarea name="zbxx" cols="100" rows="5">'.$exqdb['zbxx'].'</textarea></td></tr></table><br><center><input type="submit" class="button" name="settingsubmit" value="提 交"></center><br></form>';
echo yjl_adminhtml($c);
?>