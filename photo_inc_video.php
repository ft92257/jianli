<?php
				if($user_id>0 && ($user_id==$r_res['hzid'] || $user_id==$r_res['uid'])){
					if(isset($_POST['isvideosc']) && $_POST['isvideosc']==1){
						if(isset($_POST['vurl']) && trim($_POST['vurl'])!=''){
							$vurl=htmlspecialchars(trim($_POST['vurl']),ENT_QUOTES);
							$url='';
							$name='';
							$ch=curl_init();
							curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
							curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
							curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
							curl_setopt($ch, CURLOPT_TIMEOUT, 8);
							curl_setopt($ch, CURLOPT_FILETIME, 1);
							curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
							curl_setopt($ch, CURLOPT_URL,$vurl);
							$store=curl_exec($ch);
							preg_match('/charset=([^\s\n\r]+)/i',curl_getinfo($ch,CURLINFO_CONTENT_TYPE),$matches);
							$charset='';
							if(isset($matches[1]) && trim($matches[1]))$charset=trim($matches[1]);
							curl_close($ch);
							if($store!=''){
								preg_match("/<title>(.*)<\/title>/smUi", $store, $matches);
								if(isset($matches[1])){
									$t=trim($matches[1]);
									if($charset!='' && strtoupper($charset)!='UTF-8')$t=@iconv(strtoupper($charset), 'UTF-8', $t);
									if(trim($t)!='')$name=htmlspecialchars(trim($t),ENT_QUOTES);
								}
								if(strstr($vurl, 'http://www.tudou.com/programs/')){
									$ha=explode('</head>', $store);
									if(isset($ha[1]) && trim($ha[1])!=''){
										preg_match("/<script>(.*)<\/script>/smUi", $ha[1], $script_a);
										if(isset($script_a[1]) && trim($script_a[1])!=''){
											$sc=trim($script_a[1]);
											if($charset!='' && strtoupper($charset)!='UTF-8')$sc=@iconv(strtoupper($charset), 'UTF-8', $sc);
											$sca=explode('pic = \'', $sc);
											if(isset($sca[1])){
												$sca_1=explode('\'', $sca[1]);
												if($sca_1[0]!='')$url=$sca_1[0];
											}
											$sca=explode('kw = "', $sc);
											if(isset($sca[1])){
												$sca_2=explode('"', $sca[1]);
												if($sca_2[0]!='')$name=htmlspecialchars(trim($sca_2[0]),ENT_QUOTES);
											}
											$sca=explode('icode = \'', $sc);
											if(isset($sca[1])){
												$sca_3=explode('\'', $sca[1]);
												if($sca_3[0]!='')$vid=$sca_3[0];
											}
										}
									}
								}elseif(strstr($vurl, 'http://www.tudou.com/listplay/')){
									$ha=explode('</head>', $store);
									if(isset($ha[1]) && trim($ha[1])!=''){
										preg_match("/<script>(.*)<\/script>/smUi", $ha[1], $script_a);
										if(isset($script_a[1]) && trim($script_a[1])!=''){
											$sc=trim($script_a[1]);
											if($charset!='' && strtoupper($charset)!='UTF-8')$sc=@iconv(strtoupper($charset), 'UTF-8', $sc);
											$sca=explode(',pic:"', $sc);
											if(isset($sca[1])){
												$sca_1=explode('"', $sca[1]);
												if($sca_1[0]!='')$url=$sca_1[0];
											}
											$sca=explode(',kw:"', $sc);
											if(isset($sca[1])){
												$sca_2=explode('"', $sca[1]);
												if($sca_2[0]!='')$name=htmlspecialchars(trim($sca_2[0]),ENT_QUOTES);
											}
										}
									}
								}elseif(strstr($vurl, 'http://v.youku.com/v_show/')){
									if($charset!='' && strtoupper($charset)!='UTF-8')$store=@iconv(strtoupper($charset), 'UTF-8', $store);
									$sca=explode('<meta name="title" content="', $store);
									if(isset($sca[1])){
										$sca_1=explode('"', $sca[1]);
										if($sca_1[0]!='')$name=htmlspecialchars(trim($sca_1[0]),ENT_QUOTES);
									}
									$sca=explode('&screenshot=', $store);
									if(isset($sca[1])){
										$sca_2=explode('"', $sca[1]);
										if($sca_2[0]!='')$url=$sca_2[0];
									}
									$sca=explode('videoId2= \'', $store);
									if(isset($sca[1])){
										$sca_3=explode('\'', $sca[1]);
										if($sca_3[0]!='')$vid=$sca_3[0];
									}
								}elseif(strstr($vurl, 'http://my.tv.sohu.com/')){
									if($charset!='' && strtoupper($charset)!='UTF-8')$store=@iconv(strtoupper($charset), 'UTF-8', $store);
									$sca=explode('<meta property="og:title" content="', $store);
									if(isset($sca[1])){
										$sca_1=explode('"', $sca[1]);
										if($sca_1[0]!='')$name=htmlspecialchars(trim($sca_1[0]),ENT_QUOTES);
									}
									$sca=explode('<meta property="og:image" content="', $store);
									if(isset($sca[1])){
										$sca_2=explode('"', $sca[1]);
										if($sca_2[0]!='')$url=$sca_2[0];
									}
									$sca=explode('vid = \'', $store);
									if(isset($sca[1])){
										$sca_3=explode('\'', $sca[1]);
										if($sca_3[0]!='')$vid=$sca_3[0];
									}
								}elseif(strstr($vurl, 'http://video.sina.com.cn/')){
									if($charset!='' && strtoupper($charset)!='UTF-8')$store=@iconv(strtoupper($charset), 'UTF-8', $store);
									$sca=explode('title:\'', $store);
									if(isset($sca[1])){
										$sca_1=explode('\'', $sca[1]);
										if($sca_1[0]!='')$name=htmlspecialchars(trim($sca_1[0]),ENT_QUOTES);
									}
									$sca=explode('pic: \'', $store);
									if(isset($sca[1])){
										$sca_2=explode('\'', $sca[1]);
										if($sca_2[0]!='')$url=$sca_2[0];
									}
									$sca=explode('vid :\'', $store);
									if(isset($sca[1])){
										$sca_3=explode('\'', $sca[1]);
										if($sca_3[0]!='')$vid=$sca_3[0];
									}
								}
							}
							if(trim($name)=='')$name=$a_lc[$_POST['lid']];
							$iSQL=sprintf('insert into %s (jlid, uid, lid, name, url, vurl, datetime) values (%s, %s, %s, %s, %s, %s, %s)', $yjl_dbprefix.'jl_video',
								$r_res['jlid'],
								$user_id,
								$_POST['lid'],
								yjl_SQLString($name, 'text'),
								yjl_SQLString($url, 'text'),
								yjl_SQLString($vurl, 'text'),
								time());
							$result=mysql_query($iSQL) or die('');
							$uSQL=sprintf('update %s set lasttime=%s where jlid=%s', $yjl_dbprefix.'jl', time(), $jlid);
							$result=mysql_query($uSQL) or die('');
							yjl_addlog('[uid]添加视频到监理项目：<a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-video.html">'.$r_res['name'].'</a>', md5('jlsp|'.$r_res['hzid'].'|'.$user_id.'|'.$jlid), 0, $r_res['hzid']);
						}
						echo '<script type="text/javascript">location.href=\'photo-'.$xqid.'-'.$r_res['jlid'].'-video.html\';</script>';
						exit();
					}
					$htmlfjc='<div class="overlay" id="overlay_newct">
	<h3>添加视频</h3><div class="overlay_cont"><form method="post" class="main_form" action=""><table>
				<tr><th width="50">流程</th>
				<td><select name="lid">';
					foreach($a_lc as $k=>$v)$htmlfjc.='<option value="'.$k.'">'.$v.'</option>';
					$htmlfjc.='</select></td></tr>
				<tr><th valign="top">视频网址</th>
				<td><input name="vurl" class="text"/><input type="hidden" name="isvideosc" value="1"/></td>
				<tr>
					<th></th>
					<td>请输入优酷、土豆等视频网站的视频播放页网址</td>
				<tr>
					<th></th>
					<td><input type="submit" value="添 加" class="submit sub_reg" /></td>
				</tr>
			 </table>
			 </form>
		</div></div>';
				}
				$mf='<div class="flt_rt"><a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html" class="color_gray">查看照片</a>';
				if($user_id>0 && ($user_id==$r_res['hzid'] || $user_id==$r_res['uid']))$mf.='<a href="#" class="color_gray" rel="#overlay_newct">添加视频</a>';
				$mf.='</div>';
				$c.='<div class="document">';
				$fc='';
				$i=0;
				foreach($a_lc as $k=>$v){
					$q_rep=sprintf('select viid, uid, name, url, vurl, datetime from %s where lid=%s and jlid=%s order by datetime desc', $yjl_dbprefix.'jl_video', $k, $r_res['jlid']);
					$rep=mysql_query($q_rep) or die('');
					$r_rep=mysql_fetch_assoc($rep);
					if(mysql_num_rows($rep)>0){
						$fc.='<div class="title" style="margin-bottom: 20px;"><h3>'.$v.'</h3>'.($i==0?$mf:'').'</div><ul class="list_vgepic clearfix">';
						do{
							$fc.='<li style="float: left;padding: 10px;width: 200px;text-align: center;"><a href="'.$r_rep['vurl'].'" target="_blank" title="'.$r_rep['name'].'"><img src="images/blank.gif" alt="" class="user_pic_v1" style="background-image: url('.($r_rep['url']!=''?$r_rep['url']:'images/vi_d.jpg').');" width="80" height="80"/></a><p>';
							if($r_rep['name']!='')$fc.='<a href="'.$r_rep['vurl'].'" target="_blank" title="'.$r_rep['name'].'">'.yjl_substrs($r_rep['name'], 14).'</a>';
							if($user_id>0 && ($user_id==$r_rep['uid'] || $udb['qx']==10 || $udb['isxg']>0)){
								if(isset($_GET['delviid']) && $_GET['delviid']==$r_rep['viid']){
									$dSQL=sprintf('delete from %s where viid=%s', $yjl_dbprefix.'jl_video', $r_rep['viid']);
									$result=mysql_query($dSQL) or die('');
									$_SESSION[$seid]=$lid;
									echo '<script type="text/javascript">location.href=\'photo-'.$xqid.'-'.$r_res['jlid'].'-video.html\';</script>';
									exit();
								}
								$fc.=' <a href="'.$f.'?xqid='.$xqid.'&amp;id='.$r_res['jlid'].'&amp;t=video&amp;delviid='.$r_rep['viid'].'" onclick="if(!confirm(\'确认删除？\'))return false;" style="color: #f00;">删除</a>';
							}
							$fc.='</p></li>';
						}while($r_rep=mysql_fetch_assoc($rep));
						$fc.='</ul><br class="clear" /><br />';
					}
					mysql_free_result($rep);
					$i++;
				}
				if($fc!=''){
					$c.=$fc;
				}else{
					$c.='<div class="title">
					<h3>视频监理</h3>'.$mf.'
				</div>
				<ul class="list_doc hover">
					<li>没有视频</li></ul>';
				}
				$c.='</div>';
?>