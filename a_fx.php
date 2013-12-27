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
$f='a_fx.php';
$esid=md5($f);
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
if(isset($_SESSION[$esid]) && $_SESSION[$esid]!=''){
	$m=array(1=>'户型已添加！', '户型已修改！', '户型已删除！', '请输入名称！', '上传错误！');
	if(isset($m[$_SESSION[$esid]]))$t_m=$m[$_SESSION[$esid]];
	unset($_SESSION[$esid]);
}
$c=isset($t_m)?yjl_getMsg($t_m):'';
if(isset($_GET['id']) && intval($_GET['id'])>0){
	$q_res=sprintf('select xqid, name from %s where xqid=%s limit 1', $yjl_dbprefix.'xq', intval($_GET['id']));
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0)$exqdb=$r_res;
	mysql_free_result($res);
}
$exqid=isset($exqdb)?$exqdb['xqid']:0;
$c.='<b>'.($exqid>0?$exqdb['name'].' ':'默认').'户型</b>';
$q_res=sprintf('select * from %s where xqid=%s', $yjl_dbprefix.'xq_fx', $exqid);
$a_res=mysql_query($q_res) or die('');
$tr_res=mysql_num_rows($a_res);
if($tr_res>0){
	$c.='<table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"><tr class="header"><td>户型</td><td>描述</td><td>图片</td><td>&nbsp;</td></tr>';
	$tp_res=ceil($tr_res/$p_size);
	if($page>$tp_res)$page=$tp_res;
	$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
	$res=mysql_query($q_l_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	$i=0;
	do{
		if(isset($_GET['eid']) && $_GET['eid']==$r_res['fxid']){
			$edb=$r_res;
		}elseif(isset($_GET['did']) && $_GET['did']==$r_res['fxid']){
			$uSQL=sprintf('update %s set fxid=0 where fxid=%s', $yjl_dbprefix.'members', $r_res['fxid']);
			$result=mysql_query($uSQL) or die('');
			$uSQL=sprintf('update %s set fxid=0 where fxid=%s', $yjl_dbprefix.'jl', $r_res['fxid']);
			$result=mysql_query($uSQL) or die('');
			$dSQL=sprintf('delete from %s where fxid=%s', $yjl_dbprefix.'xq_fx', $r_res['fxid']);
			$result=mysql_query($dSQL) or die('');
			$_SESSION[$esid]=3;
			echo '<script type="text/javascript">location.href=\''.$f.'?p='.$page.($exqid>0?'&id='.$exqid:'').'\';</script>';
			exit();
		}
		$c.='<tr class="altbg'.(($i%2)+1).'"><td>'.$r_res['name'].'</td><td>'.($r_res['content']!=''?$r_res['content']:'-').'</td><td>'.($r_res['url']!=''?'<img src="'.$r_res['url'].'" width="'.$fxt_w.'" title="点击查看大图" onclick="openimg(\''.$r_res['url'].'\', \''.$r_res['width'].'\', \''.$r_res['height'].'\');"/>':'-').'</td><td><a href="?eid='.$r_res['fxid'].($exqid>0?'&amp;id='.$exqid:'').($page>1?'&amp;p='.$page:'').'">修改</a> | <a href="?did='.$r_res['fxid'].($exqid>0?'&amp;id='.$exqid:'').($page>1?'&amp;p='.$page:'').'" style="color: #f00;" onclick="if(!confirm(\'确认删除？\'))return false;">删除</a></td></tr>';
		$i++;
	}while($r_res=mysql_fetch_assoc($res));
	mysql_free_result($res);
	if($tp_res>1)$paa[]=yjl_getpage($page, $tp_res);
	$c.='</table>'.(isset($paa)?'<center>'.join(' | ', $paa).'</center>':'').'<br/><br/>';
}
mysql_free_result($a_res);
if($_SERVER['REQUEST_METHOD']=='POST'){
	if(isset($_POST['name']) && trim($_POST['name'])!=''){
		$name=htmlspecialchars(trim($_POST['name']),ENT_QUOTES);
		$content=htmlspecialchars(trim($_POST['content']),ENT_QUOTES);
		$url='';
		$width=0;
		$height=0;
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
						$url=$u_f.$f_m.'.'.$f_e;
						$data=GetImageSize($url);
						if($data && $data[2]<=3){
							$width=$data[0];
							$height=$data[1];
						}else{
							$e=5;
							unlink($url);
							$url='';
						}
					}else{
						$e=5;
					}
				}
			}else{
				$e=5;
			}
		}
		if(isset($edb)){
			if($url!='' && $edb['url']!='' && file_exists($edb['url']))unlink($edb['url']);
			$uSQL=sprintf('update %s set name=%s, content=%s, url=%s, width=%s, height=%s where fxid=%s', $yjl_dbprefix.'xq_fx',
				yjl_SQLString($name, 'text'),
				yjl_SQLString($content, 'text'),
				yjl_SQLString($url, 'text'),
				$width,
				$height,
				$edb['fxid']);
			$result=mysql_query($uSQL) or die('');
			$_SESSION[$esid]=2;
		}else{
			$iSQL=sprintf('insert into %s (name, content, url, width, height, xqid) values (%s, %s, %s, %s, %s, %s)', $yjl_dbprefix.'xq_fx',
				yjl_SQLString($name, 'text'),
				yjl_SQLString($content, 'text'),
				yjl_SQLString($url, 'text'),
				$width,
				$height,
				$exqid);
			$result=mysql_query($iSQL) or die('');
			$_SESSION[$esid]=1;
		}
		echo '<script type="text/javascript">location.href=\''.$f.'?p='.$page.($exqid>0?'&id='.$exqid:'').'\';</script>';
		exit();
	}else{
		$_SESSION[$esid]=4;
		echo '<script type="text/javascript">location.href=\''.$f.'?p='.$page.($exqid>0?'&id='.$exqid:'').(isset($edb)?'&eid='.$edb['fxid']:'').'\';</script>';
		exit();
	}
}
$c.='<form method="post" actin="" onsubmit="if(document.form1.name.value==\'\'){alert(\'请输入名称！\');return false;}" enctype="multipart/form-data" name="form1"><table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder">';
if(isset($edb)){
	$c.='<tr class="header"><td colspan="2">修改户型：'.$edb['name'].'</td></tr><tr class="altbg1"><td>名称：</td><td><input name="name" value="'.$edb['name'].'"/></td></tr><tr class="altbg2"><td>描述：</td><td><input name="content" value="'.$edb['content'].'"/></td></tr><tr class="altbg1"><td valign="top">图片：</td><td>'.($edb['url']!=''?'<img src="'.$edb['url'].'" width="'.$fxt_w.'" title="点击查看大图" onclick="openimg(\''.$edb['url'].'\', \''.$edb['width'].'\', \''.$edb['height'].'\');"/><br/>':'').'<input type="file" name="url"/><br/>图片宽度：'.$fxt_w.'px，允许类型：'.join('、', $u_ea).'，最大：'.$max_file.'KB<br/>上传新图片将自动删除原图片</td></tr>';
}else{
	$c.='<tr class="header"><td colspan="2">增加户型</td></tr><tr class="altbg1"><td>名称：</td><td><input name="name"/></td></tr><tr class="altbg2"><td>描述：</td><td><input name="content"/></td></tr><tr class="altbg1"><td valign="top">图片：</td><td><input type="file" name="url"/><br/>图片宽度：'.$fxt_w.'px，允许类型：'.join('、', $u_ea).'，最大：'.$max_file.'KB</td></tr>';
}
$c.='</table><br><center><input type="submit" class="button" name="settingsubmit" value="提 交">'.(isset($edb)?' <a href="?p='.$page.($exqid>0?'&amp;id='.$exqid:'').'">取消</a>':'').'</center><br></form>';
echo yjl_adminhtml($c);
?>