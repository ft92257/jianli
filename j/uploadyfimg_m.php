<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='uploadyfimg_m.php';
$u_result=array('errorid'=>1, 'error'=>'用户错误');
if($udb['uid']>0){
	$id=(isset($_GET['jlid']) && intval($_GET['jlid'])>0)?intval($_GET['jlid']):1;
	$q_res=sprintf('select uid, hzid, jlid, app_id, name, lid, xqid from %s where jlid=%s limit 1', $yjl_dbprefix.'jl', $id);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$jlid=$r_res['jlid'];
		if($user_id==$r_res['uid'] || $user_id==$r_res['hzid']){
			require_once('../lib/qq_upload.php');
			$u_f='file/';
			$sizeLimit=$max_file*1024;
			$uploader=new qqFileUploader($u_ea, $sizeLimit);
			$u_result=$uploader->handleUpload('../'.$u_f, md5(time().rand(1,9999)));
			if(isset($u_result['errorid']) && $u_result['errorid']==0 && isset($u_result['filename']) && trim($u_result['filename'])!='' && file_exists('../'.$u_f.$u_result['filename'].'.'.$u_result['fileext'])){
				rename('../'.$u_f.$u_result['filename'].'.'.$u_result['fileext'], '../'.$u_f.$u_result['filename'].'_o.jpg');
				$photo=$u_f.$u_result['filename'].'.jpg';
				$photo_t=$u_f.$u_result['filename'].'_t.jpg';
				$photo_o=$u_f.$u_result['filename'].'_o.jpg';
				$data=GetImageSize('../'.$photo_o);
				if($data && $data[2]<=3){
					$sw=$data[0];
					$sh=$data[1];
					$w=$a_wh_jltpt[0];
					$h=$a_wh_jltpt[1];
					if($sw>$w || $sh>$h){
						switch($data[2]){
							case 1:
								$im=@imagecreatefromgif('../'.$photo_o);
								break;
							case 2:
								$im=@imagecreatefromjpeg('../'.$photo_o);
								break;
							case 3:
								$im=@imagecreatefrompng('../'.$photo_o);
								break;
						}
						$xy=yjl_imgxy($sw, $sh, $w, $h);
						$ni=imagecreatetruecolor($xy[4],$xy[5]);
						imagecopyresampled($ni,$im,$xy[0],$xy[1],0,0,$xy[2],$xy[3],$sw,$sh);
						imagejpeg($ni, '../'.$photo_t);
						imagedestroy($ni);
						imagedestroy($im);
					}else{
						copy('../'.$photo_o, '../'.$photo_t);
					}
					switch($data[2]){
						case 1:
							$im=@imagecreatefromgif('../'.$photo_o);
							break;
						case 2:
							$im=@imagecreatefromjpeg('../'.$photo_o);
							break;
						case 3:
							$im=@imagecreatefrompng('../'.$photo_o);
							break;
					}
					$w=$a_wh_jltpw;
					$h=round($w*$sh/$sw);
					if($sw>$w){
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
						imagejpeg($ni, '../'.$photo);
						imagedestroy($ni);
					}else{
						$isy=@imagecreatefrompng('../images/'.$sy_img0[0]);
						imagecopy($im,$isy,5,5,0,0,$sy_img0[1],$sy_img0[2]);
						imagedestroy($isy);
						$isy=@imagecreatefrompng('../images/'.$sy_img1[0]);
						imagecopy($im,$isy,($data[0]-$sy_img1[1]-5),($data[1]-$sy_img1[2]-5),0,0,$sy_img1[1],$sy_img1[2]);
						imagedestroy($isy);
						imagejpeg($im, '../'.$photo);
						imagedestroy($im);
					}
					if($sw<=$w){
						copy('../'.$photo, '../'.$photo_o);
					}else{
						switch($data[2]){
							case 1:
								$im=@imagecreatefromgif('../'.$photo_o);
								break;
							case 2:
								$im=@imagecreatefromjpeg('../'.$photo_o);
								break;
							case 3:
								$im=@imagecreatefrompng('../'.$photo_o);
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
							imagejpeg($ni, '../'.$photo_o);
							imagedestroy($ni);
						}else{
							$isy=@imagecreatefrompng('../images/'.$sy_img0[0]);
							imagecopy($im,$isy,5,5,0,0,$sy_img0[1],$sy_img0[2]);
							imagedestroy($isy);
							$isy=@imagecreatefrompng('../images/'.$sy_img1[0]);
							imagecopy($im,$isy,($data[0]-$sy_img1[1]-5),($data[1]-$sy_img1[2]-5),0,0,$sy_img1[1],$sy_img1[2]);
							imagedestroy($isy);
							imagejpeg($im, '../'.$photo_o);
							imagedestroy($im);
						}
					}
					$iSQL=sprintf('insert into %s (jlid, uid, uname, url, t_url, o_url, width, height, datetime) values (%s, %s, %s, %s, %s, %s, %s, %s, %s)', $yjl_dbprefix.'jl_cl',
						$jlid,
						$user_id,
						yjl_SQLString($udb['username'], 'text'),
						yjl_SQLString($photo, 'text'),
						yjl_SQLString($photo_t, 'text'),
						yjl_SQLString($photo_o, 'text'),
						$sw,
						$sh,
						time());
					$result=mysql_query($iSQL) or die('');
					$clid=mysql_insert_id();
					$uSQL=sprintf('update %s set lasttime=%s where jlid=%s', $yjl_dbprefix.'jl', time(), $jlid);
					$result=mysql_query($uSQL) or die('');
					$q_rep=sprintf('select app_key, app_secret from %s where id=%s limit 1', $dbprefix.'app', $r_res['app_id']);
					$rep=mysql_query($q_rep) or die('');
					$r_rep=mysql_fetch_assoc($rep);
					if(mysql_num_rows($rep)>0){
						$app_k=$r_rep['app_key'];
						$app_s=$r_rep['app_secret'];
					}else{
						$app_a=yjl_app('照片式监理 '.$r_res['name'], $jlid, $yjl_url.'photo-'.$xqid.'-'.$jlid.'.html');
						$uSQL=sprintf('update %s set app_id=%s where jlid=%s', $yjl_dbprefix.'jl', $app_a[0], $jlid);
						$result=mysql_query($uSQL) or die('');
						$app_k=$app_a[1];
						$app_s=$app_a[2];
					}
					mysql_free_result($rep);
					require_once('../lib/jishigouapi.class.php');
					$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $app_k, $app_s, $udb['nickname'], md5($udb['nickname'].$udb['password']));
					$jsg_result=$JishiGouAPI->AddTopic('验房 '.(isset($_POST['content'])?' '.$_POST['content']:''), 0, 'first', $yjl_url.$photo_o);
					if(!isset($jsg_result['error']) && isset($jsg_result['result']['tid'])){
						$tid=$jsg_result['result']['tid'];
						$uSQL=sprintf('update %s set tid=%s where clid=%s', $yjl_dbprefix.'jl_cl', $tid, $clid);
						$result=mysql_query($uSQL) or die('');
						yjl_addlog('[uid]上传验房照片到监理项目：<a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-home-'.$clid.'.html">'.$r_res['name'].'</a>', md5('plcl|'.$r_res['hzid'].'|'.$user_id.'|'.$clid), 0, $r_res['hzid']);
						yjl_uwb($user_id, '上传验房照片'.(isset($_POST['content'])?' '.$_POST['content']:''), $tid, '', $photo_o);
					}
				}else{
					unlink('../'.$photo_o);
					$u_result=array('errorid'=>5, 'error'=>'文件类型错误');
				}
			}
		}
	}
	mysql_free_result($res);
}
$c=htmlspecialchars(json_encode($u_result), ENT_NOQUOTES);
echo $c;
?>