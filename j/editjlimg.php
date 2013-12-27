<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='editjlimg.php';
$udb=array('uid'=>0);
if(isset($_POST['cookie_auth']) && trim($_POST['cookie_auth'])!=''){
	$auth=str_replace(' ', '+', $_POST['cookie_auth']);
	$lk=yjl_authcode($auth, 'DECODE', $config['auth_key']);
	$ak=explode("\t", $lk);
	if(isset($ak[0]) && trim($ak[0])!='' && isset($ak[1]) && intval($ak[1])>0){
		$q_res=sprintf('select uid from %s where uid=%s and password=%s limit 1', $dbprefix.'members', yjl_SQLString($ak[1], 'int'), yjl_SQLString(trim($ak[0]), 'int'));
		$res=mysql_query($q_res) or die(mysql_error());
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0){
			$q_rep=sprintf('select qx, isxg from %s where uid=%s limit 1', $yjl_dbprefix.'members', $r_res['uid']);
			$rep=mysql_query($q_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				$r_res['qx']=$r_rep['qx'];
				$r_res['isxg']=$r_rep['isxg'];
			}else{
				$r_res['qx']=$r_res['role_id']==2?10:0;
				$r_res['isxg']=0;
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
	$id=(isset($_POST['jpid']) && intval($_POST['jpid'])>0)?intval($_POST['jpid']):1;
}else{
	$udb=yjl_chkulog();
	$id=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
	if(isset($_POST['jpid']) && intval($_POST['jpid'])>0)$id=intval($_POST['jpid']);
}
if($udb['uid']>0){
	$user_id=$udb['uid'];
	$adb=($udb['qx']==10 || $udb['isxg']>0)?'':' and (a.uid='.$user_id.' or (a.isjl=0 and b.hzid='.$user_id.'))';
	$q_res=sprintf('select a.jpid, a.url, a.t_url, a.o_url, a.isjl, a.tid, a.datetime, a.uid, b.hzid from %s as a, %s as b where a.jpid=%s and a.jlid=b.jlid%s limit 1', $yjl_dbprefix.'jl_photo', $yjl_dbprefix.'jl', $id, $adb);
	$res=mysql_query($q_res) or die(mysql_error());
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		if($r_res['isjl']==1 || $udb['qx']==10 || $udb['isxg']>0 || $r_res['uid']==$r_res['hzid'] || $r_res['datetime']>(time()-$jldimg_jg)){
			if(isset($_FILES['Filedata']) && $_FILES['Filedata']['name']!=''){
				$url='';
				$f_i=$_FILES['Filedata'];
				if(is_uploaded_file($f_i['tmp_name']) && $f_i['error']==0){
					$e=0;
					if($f_i['size']>$max_file*1024)$e=2;
					$f_e=explode('.', $f_i['name']);
					$f_e=strtolower($f_e[count($f_e)-1]);
					if(isset($u_ea) && !in_array($f_e, $u_ea))$e=3;
					$u_f='file/';
					if(!is_dir('../'.$u_f) && is_writeable('../'.$u_f))$e=4;
					if($e==0){
						$f_m=$user_id.'_'.md5(time().rand(0,1000));
						$photo='../'.$u_f.$f_m.'.'.$f_e;
						$photo_t='../'.$u_f.$f_m.'_t.'.$f_e;
						$photo_o='../'.$u_f.$f_m.'_o.'.$f_e;
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
								$w=$a_wh_jltp[0];
								$h=$a_wh_jltp[1];
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
									$xy=yjl_imgxy($sw, $sh, $w, $h, 1);
									$ni=imagecreatetruecolor($xy[4],$xy[5]);
									imagecopyresampled($ni,$im,$xy[0],$xy[1],0,0,$xy[2],$xy[3],$sw,$sh);
									imagejpeg($ni, $photo);
									imagedestroy($ni);
									imagedestroy($im);
								}else{
									copy($photo_o, $photo);
								}
								if(file_exists('../'.$r_res['url']))@unlink('../'.$r_res['url']);
								if(file_exists('../'.$r_res['t_url']))@unlink('../'.$r_res['t_url']);
								if(file_exists('../'.$r_res['o_url']))@unlink('../'.$r_res['o_url']);
								$uSQL=sprintf('update %s set url=%s, t_url=%s, o_url=%s, width=%s, height=%s where jpid=%s', $yjl_dbprefix.'jl_photo',
									yjl_SQLString($u_f.$f_m.'.'.$f_e, 'text'),
									yjl_SQLString($u_f.$f_m.'_t.'.$f_e, 'text'),
									yjl_SQLString($u_f.$f_m.'_o.'.$f_e, 'text'),
									$sw,
									$sh,
									$r_res['jpid']);
								$result=mysql_query($uSQL) or die(mysql_error());
								if($r_res['tid']>0){
									$q_rep=sprintf('select imageid from %s where tid=%s and length(imageid)>0 limit 1', $dbprefix.'topic', $r_res['tid']);
									$rep=mysql_query($q_rep) or die('');
									$r_rep=mysql_fetch_assoc($rep);
									if(mysql_num_rows($rep)>0){
										$a=explode(',', $r_rep['imageid']);
										$imgid=$a[0];
										if($imgid>0){
											$up=yjl_imgpath($imgid);
											foreach($up as $v){
												if(!is_dir('../'.$yjl_tpath.'images/topic/'.$v))mkdir('../'.$yjl_tpath.'images/topic/'.$v);
											}
											$nf='images/topic/'.$up[1].$imgid.'_';
											if(file_exists('../'.$yjl_tpath.$nf.'o.jpg'))unlink('../'.$yjl_tpath.$nf.'o.jpg');
											if(file_exists('../'.$yjl_tpath.$nf.'s.jpg'))unlink('../'.$yjl_tpath.$nf.'s.jpg');
											copy($photo_o, '../'.$yjl_tpath.$nf.'o.jpg');
											copy($photo_t, '../'.$yjl_tpath.$nf.'s.jpg');
											$uSQL=sprintf('update %s set width=%s, height=%s where id=%s', $dbprefix.'topic_image', $sw, $sh, $imgid);
											$result=mysql_query($uSQL) or die('');
										}
									}
									mysql_free_result($rep);
								}
								if(isset($_POST['is_nu']) && $_POST['is_nu']==1){
									echo '<script type="text/javascript">window.parent.upimg_a_3(\''.$u_f.$f_m.'.'.$f_e.'|'.$u_f.$f_m.'_t.'.$f_e.'|'.$u_f.$f_m.'_o.'.$f_e.'|'.$sw.'|'.$sh.'\');window.parent.hidenuv(\'2\');</script>';
									exit();
								}else{
									echo $u_f.$f_m.'.'.$f_e.'|'.$u_f.$f_m.'_t.'.$f_e.'|'.$u_f.$f_m.'_o.'.$f_e.'|'.$sw.'|'.$sh;
								}
							}else{
								$e=5;
								unlink($photo_o);
							}
						}else{
							$e=6;
						}
					}
				}
			}else{
				$content=isset($_GET['c'])?htmlspecialchars(trim($_GET['c']),ENT_QUOTES):'';
				$uSQL=sprintf('update %s set content=%s where jpid=%s', $yjl_dbprefix.'jl_photo', yjl_SQLString($content, 'text'), $r_res['jpid']);
				$result=mysql_query($uSQL) or die(mysql_error());
				echo $content;
				exit();
			}
		}
	}
	mysql_free_result($res);
}
if(isset($_POST['is_nu']) && $_POST['is_nu']==1)echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">window.parent.hidenuv(\'2\');alert(\'上传错误\');</script>';
?>