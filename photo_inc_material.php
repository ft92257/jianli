<?php
				if($issc>0){
					if(isset($_POST['isclys']) && $_POST['isclys']==1){
						if(isset($_FILES['fname']) && $_FILES['fname']['name']!=''){
							$url='';
							$f_i=$_FILES['fname'];
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
											$w=$a_wh_jltpw;
											$h=round($w*$sh/$sw);
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
											$_POST['file_u']=$u_f.$f_m.'.'.$f_e;
											$_POST['file_w']=$sw;
											$_POST['file_h']=$sh;
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
						$u_f='file/';
						if(isset($_POST['file_u']) && trim($_POST['file_u'])!='' && file_exists(trim($_POST['file_u'])) && file_exists(trim($_POST['file_u']).'_t') && file_exists(trim($_POST['file_u']).'_o')){
							$f_m=md5(time().rand(0,1000));
							$photo=$u_f.$f_m.'.jpg';
							$photo_t=$u_f.$f_m.'_t.jpg';
							$photo_o=$u_f.$f_m.'_o.jpg';
							$tf=trim($_POST['file_u']);
							rename($tf, $photo);
							rename($tf.'_t', $photo_t);
							rename($tf.'_o', $photo_o);
							$sw=$_POST['file_w'];
							$sh=$_POST['file_h'];
							$content=isset($_POST['content'])?htmlspecialchars(trim($_POST['content']),ENT_QUOTES):'';
							$iSQL=sprintf('insert into %s (jlid, uid, uname, url, t_url, o_url, width, height, datetime, content) values (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)', $yjl_dbprefix.'jl_cl',
								$jlid,
								$user_id,
								yjl_SQLString($udb['username'], 'text'),
								yjl_SQLString($photo, 'text'),
								yjl_SQLString($photo_t, 'text'),
								yjl_SQLString($photo_o, 'text'),
								$sw,
								$sh,
								time(),
								yjl_SQLString($content, 'text'));
							$result=mysql_query($iSQL) or die('');
							$clid=mysql_insert_id();
							$uSQL=sprintf('update %s set lasttime=%s where jlid=%s', $yjl_dbprefix.'jl', time(), $jlid);
							$result=mysql_query($uSQL) or die('');
							require_once('lib/jishigouapi.class.php');
							$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $app_k, $app_s, $udb['nickname'], md5($udb['nickname'].$udb['password']));
							$jsg_result=$JishiGouAPI->AddTopic('验房 '.(isset($_POST['content'])?' '.$_POST['content']:''), 0, 'first', $yjl_url.$photo_o);
							if(!isset($jsg_result['error']) && isset($jsg_result['result']['tid'])){
								$tid=$jsg_result['result']['tid'];
								$uSQL=sprintf('update %s set tid=%s where clid=%s', $yjl_dbprefix.'jl_cl', $tid, $clid);
								$result=mysql_query($uSQL) or die('');
								yjl_addlog('[uid]上传验房照片到监理项目：<a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-material-'.$clid.'.html">'.$r_res['name'].'</a>', md5('plcl|'.$r_res['hzid'].'|'.$user_id.'|'.$clid), 0, $r_res['hzid']);
								yjl_uwb($user_id, '上传验房照片'.(isset($_POST['content'])?' '.$_POST['content']:''), $tid, '', $photo_o);
							}
						}
						echo '<script type="text/javascript">location.href=\'photo-'.$xqid.'-'.$jlid.'-material.html\';</script>';
						exit();
					}
					$htmlfjc='<div class="overlay" id="overlay_newct">
	<h3>上传验房照片</h3><div class="overlay_cont"><form method="post" class="main_form" action="" enctype="multipart/form-data"><table>
				<tr><th valign="top" width="50">文件</th>
				<td><input type="file" name="fname"/><br/>允许类型：'.join('、', $u_ea).'，最大：'.$max_file.'KB</td>
				<tr><th>评论</th>
				<td><input type="text" name="content" class="text"/></td>
				<tr>
					<th></th>
					<td><input type="submit" value="上 传" class="submit sub_reg" /><input type="hidden" name="isclys" value="1"></td>
				</tr>
			 </table>
			 </form>
		</div></div>';
				}
				if(isset($_GET['clid']) && intval($_GET['clid'])>0){
					$q_rep=sprintf('select * from %s where jlid=%s and clid=%s limit 1', $yjl_dbprefix.'jl_cl', $r_res['jlid'], intval($_GET['clid']));
					$rep=mysql_query($q_rep) or die('');
					$r_rep=mysql_fetch_assoc($rep);
					if(mysql_num_rows($rep)>0)$d_cldb=$r_rep;
					mysql_free_result($rep);
				}
				if(!isset($d_cldb)){
					$q_rep=sprintf('select * from %s where jlid=%s order by datetime desc, clid desc limit 1', $yjl_dbprefix.'jl_cl', $r_res['jlid']);
					$rep=mysql_query($q_rep) or die('');
					$r_rep=mysql_fetch_assoc($rep);
					if(mysql_num_rows($rep)>0)$d_cldb=$r_rep;
					mysql_free_result($rep);
				}
				$tcl=isset($d_cldb)?1:0;
				$q_reu=sprintf('select * from %s where jlid=%s order by datetime desc, clid desc', $yjl_dbprefix.'jl_cl', $r_res['jlid']);
				$reu=mysql_query($q_reu) or die('');
				$r_reu=mysql_fetch_assoc($reu);
				$c_reu=mysql_num_rows($reu);
				if($c_reu>0){
					do{
						$a_jlcl[]='<a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-material-'.$r_reu['clid'].'.html#piclist" '.($user_id>0?'onclick="loadclp(\''.$r_reu['clid'].'\', \''.$r_res['jlid'].'\');return false;"':'rel="#overlay_login"').'><img src="images/blank.gif" width="'.$a_wh_jltpt[0].'" height="'.$a_wh_jltpt[1].'" style="background: url('.$r_reu['t_url'].') no-repeat center;" /></a>';
					}while($r_reu=mysql_fetch_assoc($reu));
				}
				mysql_free_result($reu);
				if($tcl==0 && $issc>0){
					for($i=0;$i<6;$i++)$a_jlcl[]='<a href="#" rel="#overlay_newct"><img src="images/blank.gif" width="'.$a_wh_jltpt[0].'" height="'.$a_wh_jltpt[1].'" style="background: #fafafa url(images/upload.jpg) no-repeat center;" title="上传验房照片" /></a>';
				}
				$c.='<div class="pr_cont" id="piclist">
				<div class="pr_tit tabs"><a><span class="mn_ico ico13"></span><br />验房</a></div><div class="progress"><a href="photo-'.$xqid.'-'.$jlid.'.html">返回</a>'.($issc>0?' <a href="#" rel="#overlay_newct">上传验房照片</a>':'').'</div>';
				if(isset($a_jlcl)){
					$c.='<div class="pr_pic tabcnt"><a class="mn_ico prev"></a><div class="scrollable" id="scrollable"><div class="items spt">';
					if(isset($a_jlcl))$c.=join(' ', $a_jlcl);
					$c.='</div></div><a class="mn_ico next"></a></div>';
					$c.='<div class="image_wrap" id="image_wrap">';
					if(isset($d_cldb)){
						if($user_id>0 && ($user_id==$d_cldb['uid'] || $user_id==$r_res['hzid'] || $udb['isxg']>0 || $udb['qx']==10)){
							if(isset($_GET['del']) && $_GET['del']==1){
								if($user_id==$d_cldb['uid'] || $udb['isxg']>0 || $udb['qx']==10){
									$dSQL=sprintf('delete from %s where clid=%s', $yjl_dbprefix.'jl_cl', $d_cldb['clid']);
									$result=mysql_query($dSQL) or die('');
									$dSQL=sprintf('delete from %s where clid=%s', $yjl_dbprefix.'jl_topic', $d_cldb['clid']);
									$result=mysql_query($dSQL) or die('');
									unlink($d_cldb['url']);
									unlink($d_cldb['t_url']);
									unlink($d_cldb['o_url']);
									if($d_cldb['tid']>0){
										$q_rep=sprintf('select a.tid, b.nickname, b.password, d.app_key, d.app_secret from %s as a, %s as b, %s as c, %s as d where a.tid=%s and a.uid=b.uid and a.tid=c.tid and c.item_id=d.id limit 1', $dbprefix.'topic', $dbprefix.'members', $dbprefix.'topic_api', $dbprefix.'app', $d_cldb['tid']);
										$rep=mysql_query($q_rep) or die('');
										$r_rep=mysql_fetch_assoc($rep);
										if(mysql_num_rows($rep)>0){
											require_once('lib/jishigouapi.class.php');
											$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $r_rep['app_key'], $r_rep['app_secret'], $r_rep['nickname'], md5($r_rep['nickname'].$r_rep['password']));
											$jsg_reqult=$JishiGouAPI->DeleteTopic($r_rep['tid']);
										}
										mysql_free_result($rep);
									}
								}
								echo '<script type="text/javascript">location.href=\'photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-material.html\';</script>';
								exit();
							}
						}
						$c.=yjl_climgrightu($d_cldb, $r_res);
					}else{
						$c.='<a href="#" rel="#overlay_newct"><img src="images/blank.gif" style="height: 450px;background: #fafafa url(images/upload.jpg) no-repeat center;" title="上传验房照片"/></a>';
					}
					$c.='</div>
			</div>
			<div class="comm_wrap clearfix">';
					if(isset($d_cldb)){
						$c.='<div id="jp_topic">'.yjl_climgrightc($d_cldb, $r_res, $page).'</div><br /><br />';
						if($user_id>0){
							$isupimg=1;
							$js_a='upimg_a_0(response);';
							$js_ac='upimg_ac_0();';
							$js_c.=yjl_uploadjs($js_a, $js_ac);
							$c.='<div class="broadcast">
				<table>
					<tr>
						<td><textarea style="padding: 5px;" id="content"></textarea></td>
					</tr>
					<tr>
						<td><input type="submit" value="评 论" class="submit sub_smbe" id="submit_fb" onclick="postclwb();" /><input type="hidden" id="clid" value="'.$d_cldb['clid'].'"/></td>
					</tr>
				</table>
				<div class="spdin"><a href="#" onclick="if($(\'#imgu_div\').is(\':hidden\'))$(\'#imgu_div\').show();return false;"><span class="mn_ico ico22"></span>图片</a><a href="#" onclick="if($(\'#wb_v_div\').is(\':hidden\'))$(\'#wb_v_div\').show();return false;"><span class="mn_ico ico23"></span>视频</a><input type="checkbox" name="iszf" id="tpiszf" checked="checked" class="radio"/>同时转发微博</div><div class="wb_imgv">'.yjl_uploadv_3().'</div><div id="wb_v_div" style="display: none;padding-top: 10px;">请复制视频播放页网站地址即可 <input class="text" id="vurl"/></div>
			</div>';
						}
					}
					$c.='<br /><br /></div>';
				}else{
					$c.='<div class="pr_pic tabcnt" style="height: 1px;line-height; 1px;"></div></div><div class="comm_wrap clearfix" style="padding-bottom: 40px;">该项目还没有验房照片</div>';
				}
?>