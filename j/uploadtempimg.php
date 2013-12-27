<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='uploadtempimg.php';
if(isset($_POST['cookie_auth']) && trim($_POST['cookie_auth'])!=''){
	$udb=array('uid'=>0);
	$auth=str_replace(' ', '+', $_POST['cookie_auth']);
	$lk=yjl_authcode($auth, 'DECODE', $config['auth_key']);
	$ak=explode("\t", $lk);
	if(isset($ak[0]) && trim($ak[0])!='' && isset($ak[1]) && intval($ak[1])>0){
		$q_res=sprintf('select uid from %s where uid=%s and password=%s limit 1', $dbprefix.'members', yjl_SQLString($ak[1], 'int'), yjl_SQLString(trim($ak[0]), 'int'));
		$res=mysql_query($q_res) or die('');
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0){
			$q_rep=sprintf('select qx from %s where uid=%s limit 1', $yjl_dbprefix.'members', $r_res['uid']);
			$rep=mysql_query($q_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				$r_res['qx']=$r_rep['qx'];
			}else{
				$r_res['qx']=$r_res['role_id']==2?10:0;
				$iSQL=sprintf('insert into %s (uid, qx) values (%s, %s)', $yjl_dbprefix.'members',
					$r_res['uid'],
					$r_res['qx']);
				$result=mysql_query($iSQL);
			}
			mysql_free_result($rep);
			$udb=$r_res;
		}
		mysql_free_result($res);
	}
	$user_id=$udb['uid'];
}
if($udb['uid']>0){
	if(isset($_FILES['Filedata']) && $_FILES['Filedata']['name']!=''){
		$is_nosize=(isset($_POST['nosize']) && $_POST['nosize']==1)?1:0;
		if($is_nosize==0){
			$w=(isset($_POST['width']) && intval($_POST['width'])>0)?intval($_POST['width']):$a_wh_jltt[0];
			$h=(isset($_POST['height']) && intval($_POST['height'])>0)?intval($_POST['height']):$a_wh_jltt[1];
		}
		$f_i=$_FILES['Filedata'];
		if(is_uploaded_file($f_i['tmp_name']) && $f_i['error']==0){
			$e=0;
			if($f_i['size']>$max_file*1024)$e=2;
			$f_e=explode('.', $f_i['name']);
			$f_e=strtolower($f_e[count($f_e)-1]);
			if(isset($u_ea) && !in_array($f_e, $u_ea))$e=3;
			$u_f='file/temp/';
			if(!is_dir('../'.$u_f) && is_writeable('../'.$u_f))$e=4;
			if($e==0){
				$f_m=$user_id.'_'.md5(time().rand(0,1000));
				$photo='../'.$u_f.$f_m.'.'.$f_e;
				if(@copy($f_i['tmp_name'], $photo)){
					$data=GetImageSize($photo);
					if($data && $data[2]<=3){
						$sw=$data[0];
						$sh=$data[1];
						if($is_nosize>0){
							if(isset($_POST['is_nu']) && $_POST['is_nu']==1){
								echo '<script type="text/javascript">window.parent.upimg_a_5(\''.$u_f.$f_m.'.'.$f_e.'|'.$sw.'|'.$sh.'\');window.parent.hidenuv(\'0\');</script>';
								exit();
							}else{
								echo $u_f.$f_m.'.'.$f_e.'|'.$sw.'|'.$sh;
							}
						}else{
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
							echo $u_f.$f_m.'.'.$f_e;
						}
					}else{
						$e=5;
						unlink($photo);
					}
				}else{
					$e=6;
				}
			}
		}
	}
}
if(isset($_POST['is_nu']) && $_POST['is_nu']==1)echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">window.parent.hidenuv(\'0\');alert(\'上传错误\');</script>';
?>