<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='uploadjlimg.php';
ini_set("memory_limit", "128M");
if(isset($_POST['cookie_auth']) && trim($_POST['cookie_auth'])!=''){
	$udb=array('uid'=>0);
	$auth=str_replace(' ', '+', $_POST['cookie_auth']);
	$lk=yjl_authcode($auth, 'DECODE', $config['auth_key']);
	$ak=explode("\t", $lk);
	if(isset($ak[0]) && trim($ak[0])!='' && isset($ak[1]) && intval($ak[1])>0){
		$q_res=sprintf('select uid from %s where uid=%s and password=%s limit 1', $dbprefix.'members', yjl_SQLString($ak[1], 'int'), yjl_SQLString(trim($ak[0]), 'int'));
		$res=mysql_query($q_res) or die('');
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0)$udb=$r_res;
		mysql_free_result($res);
	}
	$user_id=$udb['uid'];
}
if($udb['uid']>0){
	$id=(isset($_POST['jlid']) && intval($_POST['jlid'])>0)?intval($_POST['jlid']):1;
	$q_res=sprintf('select uid, hzid from %s where jlid=%s limit 1', $yjl_dbprefix.'jl', $id);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		if($user_id==$r_res['uid'] || $user_id==$r_res['hzid'] || $udb['qx']==0){
			if(isset($_FILES['Filedata']) && $_FILES['Filedata']['name']!=''){
				$url='';
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
						$photo_t=$photo.'_t';
						$photo_o=$photo.'_o';
						if(@copy($f_i['tmp_name'], $photo_o)){
							$data=GetImageSize($photo_o);
							if($data && $data[2]<=3){
								$sw=$data[0];
								$sh=$data[1];
								$w=$a_wh_jltpt[0];
								$h=$a_wh_jltpt[1];
								if($sw>$w || $sh>$h){
									switch($data[2]){
										case 1:
											$im=@imagecreatefromgif($photo_o);
											break;
										case 2:
											$im=@imagecreatefromjpeg($photo_o);
											break;
										case 3:
											$im=@imagecreatefrompng($photo_o);
											break;
									}
									$xy=yjl_imgxy($sw, $sh, $w, $h);
									$ni=imagecreatetruecolor($xy[4],$xy[5]);
									imagecopyresampled($ni,$im,$xy[0],$xy[1],0,0,$xy[2],$xy[3],$sw,$sh);
									imagejpeg($ni, $photo_t);
									imagedestroy($ni);
									imagedestroy($im);
								}else{
									copy($photo_o, $photo_t);
								}
								switch($data[2]){
									case 1:
										$im=imagecreatefromgif($photo_o);
										break;
									case 2:
										$im=imagecreatefromjpeg($photo_o);
										break;
									case 3:
										$im=imagecreatefrompng($photo_o);
										break;
								}
								$w=$a_wh_jltpw;
								$h=round($w*$sh/$sw);
								if($sw>$w || $sh>$h){
									$xy=yjl_imgxy($sw, $sh, $w, $h, 1);
									$ni=imagecreatetruecolor($xy[4],$xy[5]);
									imagecopyresampled($ni,$im,$xy[0],$xy[1],0,0,$xy[2],$xy[3],$sw,$sh);
									imagedestroy($im);
									$isy=@imagecreatefrompng('../images/'.$sy_img0[0]);
									imagecopy($ni,$isy,5,5,0,0,$sy_img0[1],$sy_img0[2]);
									imagedestroy($isy);
									$isy=@imagecreatefrompng('../images/'.$sy_img1[0]);
									imagecopy($ni,$isy,($xy[4]-$sy_img1[1]-5),($xy[5]-$sy_img1[2]-5),0,0,$sy_img1[1],$sy_img1[2]);
									imagedestroy($isy);
									imagejpeg($ni, $photo);
									imagedestroy($ni);
								}else{
									$isy=@imagecreatefrompng('../images/'.$sy_img0[0]);
									imagecopy($im,$isy,5,5,0,0,$sy_img0[1],$sy_img0[2]);
									imagedestroy($isy);
									$isy=@imagecreatefrompng('../images/'.$sy_img1[0]);
									imagecopy($im,$isy,($data[0]-$sy_img1[1]-5),($data[1]-$sy_img1[2]-5),0,0,$sy_img1[1],$sy_img1[2]);
									imagedestroy($isy);
									imagejpeg($im, $photo);
									imagedestroy($im);
								}
								if($sw<=$w && $sh<=$h){
									copy($photo, $photo_o);
								}else{
									switch($data[2]){
										case 1:
											$im=@imagecreatefromgif($photo_o);
											break;
										case 2:
											$im=@imagecreatefromjpeg($photo_o);
											break;
										case 3:
											$im=@imagecreatefrompng($photo_o);
											break;
									}
									$w=$a_wh_maxw;
									$h=round($w*$sh/$sw);
									if($sw>$w){
										$sw=$w;
										$sh=$h;
										$xy=yjl_imgxy($sw, $sh, $w, $h, 1);
										$ni=imagecreatetruecolor($xy[4],$xy[5]);
										imagecopyresampled($ni,$im,$xy[0],$xy[1],0,0,$xy[2],$xy[3],$sw,$sh);
										imagedestroy($im);
										$isy=@imagecreatefrompng('../images/'.$sy_img0[0]);
										imagecopy($ni,$isy,5,5,0,0,$sy_img0[1],$sy_img0[2]);
										imagedestroy($isy);
										$isy=@imagecreatefrompng('../images/'.$sy_img1[0]);
										imagecopy($ni,$isy,($xy[4]-$sy_img1[1]-5),($xy[5]-$sy_img1[2]-5),0,0,$sy_img1[1],$sy_img1[2]);
										imagedestroy($isy);
										imagejpeg($ni, $photo_o);
										imagedestroy($ni);
									}else{
										$isy=@imagecreatefrompng('../images/'.$sy_img0[0]);
										imagecopy($im,$isy,5,5,0,0,$sy_img0[1],$sy_img0[2]);
										imagedestroy($isy);
										$isy=@imagecreatefrompng('../images/'.$sy_img1[0]);
										imagecopy($im,$isy,($data[0]-$sy_img1[1]-5),($data[1]-$sy_img1[2]-5),0,0,$sy_img1[1],$sy_img1[2]);
										imagedestroy($isy);
										imagejpeg($im, $photo_o);
										imagedestroy($im);
									}
								}
								echo $u_f.$f_m.'.'.$f_e.'|'.$sw.'|'.$sh;
							}else{
								$e=5;
								unlink($photo_o);
							}
						}else{
							$e=6;
						}
					}
				}
			}
		}
	}
	mysql_free_result($res);
}
?>