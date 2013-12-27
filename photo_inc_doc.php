<?php
				if($user_id>0 && $user_id==$r_res['hzid']){
					if(isset($_POST['isfilesc']) && $_POST['isfilesc']==1){
						if(isset($_FILES['fname']) && $_FILES['fname']['name']!=''){
							$url='';
							$etid=0;
							$f_i=$_FILES['fname'];
							$e=0;
							if(is_uploaded_file($f_i['tmp_name']) && $f_i['error']==0){
								if($f_i['size']>$max_file*1024)$e=2;
								$f_e=explode('.', $f_i['name']);
								$f_e=strtolower($f_e[count($f_e)-1]);
								if(isset($u_ea) && !in_array($f_e, $u_jlea))$e=3;
								$u_f='file/';
								if(!is_dir($u_f) && is_writeable($u_f))$e=4;
								if($e==0){
									$f_m=$user_id.'_'.md5(time().rand(0,1000));
									$ufile=$u_f.$f_m.'.'.$f_e;
									if(@copy($f_i['tmp_name'], $ufile)){
										$url=$ufile;
										foreach($u_jlea as $k=>$v){
											if($v==$f_e)$etid=$k;
										}
									}
								}
							}
							if($url!=''){
								$iSQL=sprintf('insert into %s (jlid, uid, caid, etid, name, url, datetime, fsize) values (%s, %s, %s, %s, %s, %s, %s, %s)', $yjl_dbprefix.'jl_file',
									$r_res['jlid'],
									$user_id,
									$_POST['caid'],
									$etid,
									yjl_SQLString($_FILES['fname']['name'], 'text'),
									yjl_SQLString($url, 'text'),
									time(),
									$f_i['size']);
								$result=mysql_query($iSQL) or die('');
								$uSQL=sprintf('update %s set lasttime=%s where jlid=%s', $yjl_dbprefix.'jl', time(), $jlid);
								$result=mysql_query($uSQL) or die('');
								yjl_addlog('[uid]上传文件到监理项目：<a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-doc.html">'.$r_res['name'].'</a>', md5('jlsp|'.$r_res['hzid'].'|'.$user_id.'|'.$jlid), 0, $r_res['hzid']);
							}else{
								$an='上传错误';
							}
						}
						echo '<script type="text/javascript">'.(isset($an)?'alert(\''.$an.'\');':'').'location.href=\'photo-'.$xqid.'-'.$r_res['jlid'].'-doc.html\';</script>';
						exit();
					}
					$htmlfjc='<div class="overlay" id="overlay_newct">
	<h3>上传文件</h3><div class="overlay_cont"><form method="post" class="main_form" action="" enctype="multipart/form-data"><table>
				<tr><th width="50">分类</th>
				<td><select name="caid">';
					foreach($a_file as $k=>$v)$htmlfjc.='<option value="'.$k.'">'.$v.'</option>';
					$htmlfjc.='</select></td></tr>
				<tr><th valign="top">文件</th>
				<td><input type="file" name="fname"/><input type="hidden" name="isfilesc" value="1"/><br/>允许类型：'.join('、', $u_jlea).'，最大：'.$max_file.'KB</td>
				<tr>
					<th></th>
					<td><input type="submit" value="上 传" class="submit sub_reg" /></td>
				</tr>
			 </table>
			 </form>
		</div></div>';
				}
				$mf='<div class="flt_rt"><a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html" class="color_gray">查看照片</a>';
				if($user_id>0 && $user_id==$r_res['hzid'])$mf.='<a href="#" class="color_gray" rel="#overlay_newct">上传文件</a>';
				$mf.='</div>';
				$fc_c='';
				$adb=$user_id>0?'(uid='.$user_id.' or isgk=1)':'isgk=1';
				$i=0;
				foreach($a_file as $k=>$v){
					$q_rep=sprintf('select fiid, uid, etid, name, url, datetime, fsize, isgk from %s where caid=%s and jlid=%s order by datetime desc', $yjl_dbprefix.'jl_file', $k, $r_res['jlid'], $adb);
					$rep=mysql_query($q_rep) or die('');
					$r_rep=mysql_fetch_assoc($rep);
					if(mysql_num_rows($rep)>0){
						$fc='';
						do{
							if(!isset($a_iskj[$r_rep['uid']][$r_rep['isgk']])){
								$a_iskj[$r_rep['uid']][$r_rep['isgk']]=0;
								if($r_rep['isgk']==1)$a_iskj[$r_rep['uid']][$r_rep['isgk']]=1;
								if($r_rep['isgk']==0 && $user_id>0 && $user_id==$r_rep['uid'])$a_iskj[$r_rep['uid']][$r_rep['isgk']]=1;
								if($r_rep['isgk']==2 && $user_id>0){
									if($user_id==$r_rep['uid']){
										$a_iskj[$r_rep['uid']][$r_rep['isgk']]=1;
									}else{
										$q_rem=sprintf('select uid from %s where uid=%s and buddyid=%s limit 1', $dbprefix.'buddys', $r_rep['uid'], $user_id);
										$rem=mysql_query($q_rem) or die('');
										if(mysql_num_rows($rem)>0)$a_iskj[$r_rep['uid']][$r_rep['isgk']]=1;
										mysql_free_result($rem);
									}
								}
							}
							if(isset($a_iskj[$r_rep['uid']][$r_rep['isgk']]) && $a_iskj[$r_rep['uid']][$r_rep['isgk']]>0){
								$fc.='<li><span class="flt_rt">';
								if($user_id>0 && $user_id==$r_rep['uid']){
									$fc.='<span class="pri" id="file_td_'.$r_rep['fiid'].'" style="width: 170px;">';
									switch($r_rep['isgk']){
										case 2:
											$fc.='好友可见 修改：<a href="#" onclick="$(\'#file_td_'.$r_rep['fiid'].'\').load(\'j/filegk.php?fiid='.$r_rep['fiid'].'&gk=0\');return false;">不公开</a> <a href="#" onclick="$(\'#file_td_'.$r_rep['fiid'].'\').load(\'j/filegk.php?fiid='.$r_rep['fiid'].'&gk=1\');return false;">公开</a>';
											break;
										case 1:
											$fc.='公开 修改：<a href="#" onclick="$(\'#file_td_'.$r_rep['fiid'].'\').load(\'j/filegk.php?fiid='.$r_rep['fiid'].'&gk=0\');return false;">不公开</a> <a href="#" onclick="$(\'#file_td_'.$r_rep['fiid'].'\').load(\'j/filegk.php?fiid='.$r_rep['fiid'].'&gk=2\');return false;">好友可见</a>';
											break;
										default:
											$fc.='不公开 修改：<a href="#" onclick="$(\'#file_td_'.$r_rep['fiid'].'\').load(\'j/filegk.php?fiid='.$r_rep['fiid'].'&gk=1\');return false;">公开</a> <a href="#" onclick="$(\'#file_td_'.$r_rep['fiid'].'\').load(\'j/filegk.php?fiid='.$r_rep['fiid'].'&gk=2\');return false;">好友可见</a>';
											break;
									}
									$fc.='</span>';
								}
								$fc.='<span class="size">'.($r_rep['fsize']>1024?round($r_rep['fsize']/1024, 1).'KB':$r_rep['fsize'].'字节').'</span><span class="tim">'.date('Y-m-d', $r_rep['datetime']).'</span><span class="down"><a href="file.php?id='.$r_rep['fiid'].'">下载</a></span></span><a href="file.php?id='.$r_rep['fiid'].'" title="'.$r_rep['name'].'">'.yjl_substrs($r_rep['name'], 12).'</a>';
								if($user_id>0 && $user_id==$r_rep['uid'] || $udb['qx']==10 || $udb['isxg']>0){
									if(isset($_GET['delfiid']) && $_GET['delfiid']==$r_rep['fiid']){
										if(file_exists($r_rep['url']))unlink($r_rep['url']);
										$dSQL=sprintf('delete from %s where fiid=%s', $yjl_dbprefix.'jl_file', $r_rep['fiid']);
										$result=mysql_query($dSQL) or die('');
										$_SESSION[$seid]=$lid;
										echo '<script type="text/javascript">location.href=\'photo-'.$xqid.'-'.$r_res['jlid'].'-doc.html\';</script>';
										exit();
									}
									$fc.=' [<a href="'.$f.'?xqid='.$xqid.'&amp;id='.$r_res['jlid'].'&amp;t=doc&amp;delfiid='.$r_rep['fiid'].'" onclick="if(!confirm(\'确认删除？\'))return false;" style="color: #f00;">删除</a>]';
								}
								$fc.='</li>';
							}
						}while($r_rep=mysql_fetch_assoc($rep));
						if($fc!=''){
							$fc_c.='<div class="title"><h3>'.$v.'</h3>'.($i==0?$mf:'').'</div><ul class="list_doc hover">'.$fc.'</ul>';
							$i++;
						}
					}
					mysql_free_result($rep);
				}
				$c.='<div class="document">';
				if($fc_c!=''){
					$c.=$fc_c;
				}else{
					$c.='<div class="title">
					<h3>文档资料</h3>'.$mf.'
				</div>
				<ul class="list_doc hover">
					<li>没有'.(($user_id>0 && $user_id==$r_res['hzid'])?'':'公开').'文件</li></ul>';
				}
				$c.='</div>';
?>