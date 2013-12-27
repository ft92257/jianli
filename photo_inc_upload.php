<?php
//db
require_once "./lib/dbmysql.class.php";
$oDb = DbMysql::getInstance($aDbConfig);

				if(isset($_POST['issc']) && $_POST['issc']==1){
					$isupload=0;
					require_once('lib/jishigouapi.class.php');
					$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $app_k, $app_s, $udb['nickname'], md5($udb['nickname'].$udb['password']));
					if(isset($_POST['isfile']) && $_POST['isfile']==1 && isset($_FILES['Filedata']) && $_FILES['Filedata']['name']!=''){
						$url='';
						$f_i=$_FILES['Filedata'];
						if(is_uploaded_file($f_i['tmp_name']) && $f_i['error']==0){
							$e=0;
							if($f_i['size']>$max_file*1024)$e=2;
							$f_e=explode('.', $f_i['name']);
							$f_e=strtolower($f_e[count($f_e)-1]);
							if(isset($u_ea) && !in_array($f_e, $u_ea))$e=3;
							$u_f='file/temp/';
							if(!is_dir($u_f) && is_writeable($u_f))$e=4;
							if($e==0){
								$f_m=$user_id.'_'.md5(time().rand(0,1000));
								$photo=$u_f.$f_m.'.'.$f_e;
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
												$im=@imagecreatefromgif($photo_o);
												break;
											case 2:
												$im=@imagecreatefromjpeg($photo_o);
												break;
											case 3:
												$im=@imagecreatefrompng($photo_o);
												break;
										}
										$w=$a_wh_jltpw;
										$h=round($w*$sh/$sw);
										if($sw>$w || $sh>$h){
											$xy=yjl_imgxy($sw, $sh, $w, $h, 1);
											$ni=imagecreatetruecolor($xy[4],$xy[5]);
											imagecopyresampled($ni,$im,$xy[0],$xy[1],0,0,$xy[2],$xy[3],$sw,$sh);
											imagedestroy($im);
											$isy=@imagecreatefrompng('images/'.$sy_img0[0]);
											imagecopy($ni,$isy,5,5,0,0,$sy_img0[1],$sy_img0[2]);
											imagedestroy($isy);
											$isy=@imagecreatefrompng('images/'.$sy_img1[0]);
											imagecopy($ni,$isy,($xy[4]-$sy_img1[1]-5),($xy[5]-$sy_img1[2]-5),0,0,$sy_img1[1],$sy_img1[2]);
											imagedestroy($isy);
											imagejpeg($ni, $photo);
											imagedestroy($ni);
										}else{
											$isy=@imagecreatefrompng('images/'.$sy_img0[0]);
											imagecopy($im,$isy,5,5,0,0,$sy_img0[1],$sy_img0[2]);
											imagedestroy($isy);
											$isy=@imagecreatefrompng('images/'.$sy_img1[0]);
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
												$isy=@imagecreatefrompng('images/'.$sy_img0[0]);
												imagecopy($ni,$isy,5,5,0,0,$sy_img0[1],$sy_img0[2]);
												imagedestroy($isy);
												$isy=@imagecreatefrompng('images/'.$sy_img1[0]);
												imagecopy($ni,$isy,($xy[4]-$sy_img1[1]-5),($xy[5]-$sy_img1[2]-5),0,0,$sy_img1[1],$sy_img1[2]);
												imagedestroy($isy);
												imagejpeg($ni, $photo_o);
												imagedestroy($ni);
											}else{
												$isy=@imagecreatefrompng('images/'.$sy_img0[0]);
												imagecopy($im,$isy,5,5,0,0,$sy_img0[1],$sy_img0[2]);
												imagedestroy($isy);
												$isy=@imagecreatefrompng('images/'.$sy_img1[0]);
												imagecopy($im,$isy,($data[0]-$sy_img1[1]-5),($data[1]-$sy_img1[2]-5),0,0,$sy_img1[1],$sy_img1[2]);
												imagedestroy($isy);
												imagejpeg($im, $photo_o);
												imagedestroy($im);
											}
										}
										$_POST['img_c']=1;
										$_POST['tt_url_0']=$u_f.$f_m.'.'.$f_e;
										$_POST['w_0']=$sw;
										$_POST['h_0']=$sh;
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
					if(isset($_POST['img_c']) && intval($_POST['img_c'])>0){
						$ic=intval($_POST['img_c']);
						$u_f='file/f' . date('Y-m-d') . '/';
						if (!is_dir('./' . $u_f)) {
							mkdir('./' . $u_f);
						}
						
						$isjl=0;
						if($user_id==$r_res['uid']){
							$isjl=2;
						}elseif($user_id==$r_res['hzid']){
							$isjl=1;
						}
						for($i=0;$i<$ic;$i++){
							if(isset($_POST['tt_url_'.$i]) && trim($_POST['tt_url_'.$i])!='' && file_exists(trim($_POST['tt_url_'.$i])) && file_exists(trim($_POST['tt_url_'.$i]).'_t') && file_exists(trim($_POST['tt_url_'.$i]).'_o')){
								$f_m=md5(time().rand(0,1000));
								$photo=$u_f.$f_m.'.jpg';
								$photo_t=$u_f.$f_m.'_t.jpg';
								$photo_o=$u_f.$f_m.'_o.jpg';
								$tf=trim($_POST['tt_url_'.$i]);
								rename($tf, $photo);
								rename($tf.'_t', $photo_t);
								rename($tf.'_o', $photo_o);
								$sw=$_POST['w_'.$i];
								$sh=$_POST['h_'.$i];
								$content=isset($_POST['content_'.$i])?htmlspecialchars(trim($_POST['content_'.$i]),ENT_QUOTES):'';
								$iSQL=sprintf('insert into %s (jlid, uid, uname, isjl, url, t_url, o_url, width, height, datetime, lid, content) values (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)', $yjl_dbprefix.'jl_photo',
									$jlid,
									$user_id,
									yjl_SQLString($udb['username'], 'text'),
									$isjl,
									yjl_SQLString($photo, 'text'),
									yjl_SQLString($photo_t, 'text'),
									yjl_SQLString($photo_o, 'text'),
									$sw,
									$sh,
									time(),
									$_POST['lid'],
									yjl_SQLString($content, 'text'));
								$result=mysql_query($iSQL) or die('');
								$jpid=mysql_insert_id();
								
								//查找当前阶段报告，没有则添加
								$cTableReport = $yjl_dbprefix . "jl_report";
								$cSql = "SELECT * FROM $cTableReport WHERE jlid = $jlid AND stepdate = '" . date('Y-m-d') . "'";
								//die($cSql);
								$aReport = $oDb->fetchFirstArray($cSql);
								if (empty($aReport)) {
									//获取当前的阶段
									$cSql = "SELECT max(step) FROM $cTableReport WHERE jlid = $jlid";
									$step = $oDb->fetchFirstField($cSql);
									if (empty($step)) {
										$step = 0;
									}
									$step++;
								
									if ($step > 50) {
										//$cSql = "SELECT * FROM $cTableReport WHERE jlid = $jlid AND step = 50";
										//$aData = $oDb->fetchFirstArray($cSql);
										$step = 50;
									} else {
										//没有当前阶段报告
										$aData = array(
												'jlid' => $jlid,
												'step' => $step,
												'jpid' => $jpid,
												'stepdate' => date('Y-m-d'),
												'content' => date('Y-m-d') . '的监理报告',
										);
										$oDb->insert($cTableReport, $aData);
									}
								} else {
									$step = $aReport['step'];
								}
									
								//更新所属阶段
								$oDb->update($yjl_dbprefix . 'jl_photo', array('step' => $step), "jpid = $jpid");
									
								
								
								if($_POST['lid']>$r_res['lid'])$r_res['lid']=$_POST['lid'];
								$uSQL=sprintf('update %s set lasttime=%s, lid=%s where jlid=%s', $yjl_dbprefix.'jl', time(), $r_res['lid'], $jlid);
								$result=mysql_query($uSQL) or die('');
								$jsg_result=$JishiGouAPI->AddTopic($r_res['name'].' '.$a_lc[$_POST['lid']].'阶段 '.(isset($_POST['content_'.$i])?' '.$_POST['content_'.$i]:''), 0, 'first', $yjl_url.$photo);
								if(!isset($jsg_result['error']) && isset($jsg_result['result']['tid'])){
									$tid=$jsg_result['result']['tid'];
									$uSQL=sprintf('update %s set tid=%s where jpid=%s', $yjl_dbprefix.'jl_photo', $tid, $jpid);
									$result=mysql_query($uSQL) or die('');
									yjl_addlog('[uid]上传照片到监理项目：<a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-'.$jpid.'.html">'.$r_res['name'].'</a>', md5('pljp|'.$r_res['hzid'].'|'.$user_id.'|'.$jpid), 0, $r_res['hzid']);
									yjl_uwb($user_id, '上传图片'.(isset($_POST['content_'.$i])?' '.$_POST['content_'.$i]:''), $tid, '', $photo_o);
									$isupload=1;
								}
							}
						}
					}
					if($isupload>0)echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">alert(\'照片已上传。\');</script>';
					$_SESSION['jl_isnormupload']=1;
					echo '<script type="text/javascript">location.href=\'photo-'.$xqid.'-'.$jlid.'-upload.html\';</script>';
					exit();
				}
				if(yjl_chkuag()){
					$isupimg=1;
					$js_a='upimg_a_2(response);';
					$js_s=', \'jlid\':\''.$jlid.'\'';
					$js_c.=yjl_uploadjs($js_a, '', $js_s, 'uploadjlimg.php', 'jlimg_upload', '', 1);
					$c.='<div class="document">
				<div class="title">
					<h3>上传照片</h3>
					<div class="flt_rt"><a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-'.($user_id==$r_res['hzid']?'y':'j').'.html" class="color_gray">查看照片</a></div>
				</div><div style="padding: 20px;"><form method="post" enctype="multipart/form-data" style="padding-left: 20px;" class="main_form"><table><tr><th width="100">流程</th><td width="400"><select name="lid">';
					foreach($a_lc as $k=>$v)$c.='<option value="'.$k.'">'.$v.'</option>';
					$c.='</select></td></tr><tr><th valign="top">照片</th><td>'.yjl_uploadv_2(4, 'jlimg_upload', '<input type="hidden" name="img_c" id="jlimg_c" value="0"/><input type="hidden" id="img_i" value="0"/><div id="jlpu_upload" style="display: none;"></div>').'<input type="hidden" name="isfile" id="upload_t_4" value="0"/></td></tr><tr><td></td><td><input type="hidden" name="issc" value="1"/><input type="submit" value="上传" class="submit sub_smbe"/></td></tr></table></form></div>
			</div>';
				}else{
					$fj_js='var vid=$(\'#div_id\').val();if(vid==\'up_form\'){$(\'#upwc_bt\').click();}else{$(\'#lightbox_vbg\').fadeOut(500);$(\'#lightbox_v\').fadeOut(500);}';
					$c.='<style type="text/css">
.file_u_div {
	width: 225px;
	height: 260px;
}
.qq-upload-drop-area {
	width: 225px;
	height: 260px;
}
.qq-uploader {
	height: 255px;
}
.qq-upload-list {
	height: 200px;
}
</style><link href="fileuploader.css" rel="stylesheet" type="text/css"><script src="lib/fileuploader.js" type="text/javascript"></script><span id="upload_s_0"><h2>上传照片</h2><div style="clear: both;"> (将文件拖动到流程对应的框内上传，允许类型：'.join('、', $u_ea).'，最大：'.$max_file.'KB)</div>';
					foreach($a_lc as $k=>$v){
						$c.='<div style="border: 1px solid #e6e6e6;background: #fff;margin: 3px;width: 225px;height: 260px;float: left;color: #666;"><b>“'.$v.'工程”阶段，请在这里上传！<input type="hidden" id="file_list_'.$k.'" value="0"/><input type="hidden" id="file_list_'.$k.'_id" value="0"/></b><div id="file-uploader-demo'.$k.'" class="file_u_div"><noscript><p>Please enable JavaScript to use file uploader.</p></noscript></div></div>';
						$js_c.='
	new qq.FileUploader({
		element: document.getElementById(\'file-uploader-demo'.$k.'\'),
		action: \'j/uploadjlimg_m.php\',
		params: {
			jlid: \''.$jlid.'\',
			lid: \''.$k.'\'
		},
		debug: true,
		onSubmit: function(id, fileName){
			$(\'.qq-upload-drop-area\').hide();
		},
		onProgress: function(id, fileName, loaded, total){
			upload_p(\''.$k.'\', id, fileName, loaded, total);
		},
		onComplete: function(id, fileName, responseJSON){
			upload_c(\''.$k.'\', id, fileName, responseJSON)
		},
		onCancel: function(id, fileName){},
		showMessage: function(message){}
	});';
					}
					$c.='<div style="clear: both;"><a href="photo-'.$xqid.'-'.$jlid.'-'.($user_id==$r_res['hzid']?'y':'j').'.html" class="btn bt_smblue">完 成</a> 如果您不能上传图片，可以<a href="#" onclick="$(\'#upload_s_0\').hide();$(\'#upload_s_1\').show();return false;">点击这里</a>尝试普通模式上传</div></span><span id="upload_s_1" style="display: none;"><div class="document">
				<div class="title">
					<h3>上传照片</h3>
					<div class="flt_rt"><a href="photo-'.$xqid.'-'.$jlid.'-'.($user_id==$r_res['hzid']?'y':'j').'.html" class="color_gray">查看照片</a></div>
				</div><div style="padding: 20px;"><form method="post" enctype="multipart/form-data" style="padding-left: 20px;" class="main_form"><table><tr><th width="100">流程</th><td width="400"><select name="lid">';
					foreach($a_lc as $k=>$v)$c.='<option value="'.$k.'">'.$v.'</option>';
					$c.='</select></td></tr><tr><th valign="top">照片</th><td><input type="file" name="Filedata"/><br/>如果您不能上传图片，可以<a href="#" onclick="$(\'#upload_s_1\').hide();$(\'#upload_s_0\').show();return false;">点击这里</a>尝试极速模式上传<br/>允许类型：jpg、gif、png，最大：2048KB<input type="hidden" name="isfile" value="1"/><input type="hidden" name="img_c" value="1"/></td></tr><tr><td></td><td><input type="hidden" name="issc" value="1"/><input type="submit" value="上传" class="submit sub_smbe"/></td></tr></table></form></div></div></span>';
					if(isset($_SESSION['jl_isnormupload']) && $_SESSION['jl_isnormupload']==1){
						$js_c.='
	$(\'#upload_s_0\').hide();
	$(\'#upload_s_1\').show();';
						$_SESSION['jl_isnormupload']=0;
						unset($_SESSION['jl_isnormupload']);
					}
				}
?>