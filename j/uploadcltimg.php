<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='uploadcltimg.php';
$udb=array('uid'=>0);
if(isset($_POST['cookie_auth']) && trim($_POST['cookie_auth'])!=''){
	$auth=str_replace(' ', '+', $_POST['cookie_auth']);
	$lk=yjl_authcode($auth, 'DECODE', $config['auth_key']);
	$ak=explode("\t", $lk);
	if(isset($ak[0]) && trim($ak[0])!='' && isset($ak[1]) && intval($ak[1])>0){
		$q_res=sprintf('select uid, username from %s where uid=%s and password=%s limit 1', $dbprefix.'members', yjl_SQLString($ak[1], 'int'), yjl_SQLString(trim($ak[0]), 'int'));
		$res=mysql_query($q_res) or die('');
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0)$udb=$r_res;
		mysql_free_result($res);
	}
}else{
	$udb=yjl_chkulog();
}
if($udb['uid']>0){
	$user_id=$udb['uid'];
	if(isset($_FILES['Filedata']) && $_FILES['Filedata']['name']!=''){
		$f_i=$_FILES['Filedata'];
		if(is_uploaded_file($f_i['tmp_name']) && $f_i['error']==0){
			$e=0;
			if($f_i['size']>$max_file*1024)$e=2;
			$f_e=explode('.', $f_i['name']);
			$f_e=strtolower($f_e[count($f_e)-1]);
			if(isset($u_ea) && !in_array($f_e, $u_ea))$e=3;
				$u_f='../file/';
				if(!is_dir($u_f) && is_writeable($u_f))$e=4;
				if($e==0){
				$f_m=md5(time().rand(0,1000));
				$photo=$u_f.$f_m.'.'.$f_e;
				$photo_t=$u_f.$f_m.'_t.'.$f_e;
				if(@copy($f_i['tmp_name'], $photo)){
					$data=GetImageSize($photo);
					if($data && $data[2]<=3){
						$sw=$data[0];
						$sh=$data[1];
						$w=$a_wh_wbtp[0];
						$h=$a_wh_wbtp[1];
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
							imagejpeg($ni, $photo_t);
							imagedestroy($ni);
							imagedestroy($im);
						}else{
							copy($photo, $photo_t);
						}
						$iSQL=sprintf('insert into %s (photo, name, filesize, width, height, uid, username, dateline) values (%s, %s, %s, %s, %s, %s, %s, %s)', $dbprefix.'topic_image',
							yjl_SQLString($photo, 'text'),
							yjl_SQLString($f_m.'.'.$f_e, 'text'),
							filesize($photo),
							$sw,
							$sh,
							$udb['uid'],
							yjl_SQLString($udb['username'], 'text'),
							time());
						$result=mysql_query($iSQL) or die('');
						$imageid=mysql_insert_id();
						$up=yjl_imgpath($imageid);
						foreach($up as $v){
							if(!is_dir('../'.$yjl_tpath.'images/topic/'.$v))mkdir('../'.$yjl_tpath.'images/topic/'.$v);
						}
						$nf='images/topic/'.$up[1].$imageid.'_';
						$uSQL=sprintf('update %s set photo=%s where id=%s', $dbprefix.'topic_image', yjl_SQLString('./'.$nf.'o.jpg', 'text'), $imageid);
						$result=mysql_query($uSQL) or die('');
						copy($photo, '../'.$yjl_tpath.$nf.'o.jpg');
						copy($photo_t, '../'.$yjl_tpath.$nf.'s.jpg');
						unlink($photo);
						unlink($photo_t);
						if(isset($_POST['is_nu']) && $_POST['is_nu']==1){
							echo '<script type="text/javascript">window.parent.upimg_a_10(\''.$imageid.'|'.$yjl_tpath.$nf.'s.jpg\');window.parent.upimg_ac_2();window.parent.hidenuv(\'6\');</script>';
							exit();
						}else{
							echo $imageid.'|'.$yjl_tpath.$nf.'s.jpg';
						}
					}else{
						$e=5;
						unlink($photo);
					}
				}
			}
		}
	}
}
if(isset($_POST['is_nu']) && $_POST['is_nu']==1)echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">window.parent.hidenuv(\'6\');alert(\'上传错误\');</script>';
?>