<?php
			$c.='<div class="box1'.(($issc>0 && isset($_GET['upid']) && trim($_GET['upid'])!='')?' save':'').' clearfix">';
			if(isset($_GET['upid']) && trim($_GET['upid'])!=''){
				$upa=explode('_', trim($_GET['upid']));
				foreach($upa as $v){
					if(trim($v)!='' && intval($v)>0){
						$up_i=intval($v);
						if(!isset($up_a[$up_i])){
							$up_a[$up_i]=1;
							$q_rep=sprintf('select a.id, a.tid, c.content from %s as a, %s as b, %s as c where a.tid=b.tid and b.tid=c.tid and b.hdid=%s and a.id=%s and b.uid=%s limit 1', $dbprefix.'topic_image', $yjl_dbprefix.'hd_topic', $dbprefix.'topic', $hdid, $up_i, $user_id);
							$rep=mysql_query($q_rep) or die('');
							$r_rep=mysql_fetch_assoc($rep);
							if(mysql_num_rows($rep)>0){
								$up=yjl_imgpath($r_rep['id']);
								$r_rep['up']=$up;
								$a_imgl[$r_rep['id']]=$r_rep;
							}
							mysql_free_result($rep);
						}
					}
				}
				if(isset($a_imgl)){
					if($_SERVER['REQUEST_METHOD']=='POST'){
						foreach($a_imgl as $v){
							if(isset($_POST['content'.$v['id']]) && trim($_POST['content'.$v['id']])!=''){
								$content=htmlspecialchars(trim($_POST['content'.$v['id']]),ENT_QUOTES);
								$uSQL=sprintf('update %s set content=%s where tid=%s', $dbprefix.'topic',
									yjl_SQLString($content, 'text'),
									$v['tid']);
								$result=mysql_query($uSQL) or die('');
							}
						}
						echo '<script type="text/javascript">location.href=\'activeimg-'.$xqid.'-'.$hdid.'.html\';</script>';
						exit();
					}
					$c.='<form class="main_form" method="post" action=""><table>';
					foreach($a_imgl as $v)$c.='<tr>
						<td width="80"><img src="'.$yjl_tpath.'images/topic/'.$v['up'][1].$v['id'].'_s.jpg" /></td>
						<td><textarea name="content'.$v['id'].'">'.strip_tags($v['content']).'</textarea></td>
					</tr>';
					$c.='<tr>
						<td></td>
						<td><input type="submit" class="submit sub_pre" value="保 存" /></td>
					</tr>
				</table>
				</form>';
				}else{
					echo '<script type="text/javascript">location.href=\'activeimg-'.$xqid.'-'.$hdid.'-upload.html\';</script>';
					exit();
				}
			}else{
				$isupimg=1;
				$js_a='upimg_a_11(response);';
				$js_ac='upimg_ac_3();';
				$js_s=', \'hdid\':\''.$hdid.'\'';
				$js_c.=yjl_uploadjs($js_a, $js_ac, $js_s, 'uploadhdimg.php', 'hdimg_upload', '', 1);
				$c.='<div id="upload_v" style="clear: both;padding: 40px;">'.yjl_uploadv_0(0, 'hdimg_upload', 'j/uploadhdimg.php', '<input type="hidden" name="is_nu" value="1"/><input type="hidden" name="hdid" value="'.$r_res['hdid'].'"/>').'</div><input type="hidden" id="nu" value="activeimg-'.$xqid.'-'.$hdid.'-upload-i"/>';
			}
			$c.='<br /><br />
			</div>
		 </div>
		<div class="main_right">
			<div class="back"><a href="active-'.$xqid.'-'.$hdid.'.html">返回活动页 &#187;</a></div>';
?>