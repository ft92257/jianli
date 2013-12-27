<?php
			$q_res=sprintf('select a.id, a.width, a.height, a.tid, c.uid, c.nc from %s as a, %s as b, %s as c where a.tid=b.tid and b.uid=c.uid and b.xqid=%s order by a.dateline desc', $dbprefix.'topic_image', $yjl_dbprefix.'xq_topic', $yjl_dbprefix.'members', $xqid);
			$a_res=mysql_query($q_res) or die('');
			$tr_res=mysql_num_rows($a_res);
			if($tr_res>0){
				$c.='<ul class="list_vgepic clearfix">';
				$p_size=9;
				$tp_res=ceil($tr_res/$p_size);
				if($page>$tp_res)$page=$tp_res;
				$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
				$res=mysql_query($q_l_res) or die('');
				$r_res=mysql_fetch_assoc($res);
				do{
					$up=yjl_imgpath($r_res['id']);
					if(isset($_GET['did']) && $_GET['did']==$r_res['id']){
						$q_rep=sprintf('select imageid from %s where tid=%s limit 1', $dbprefix.'topic', $r_res['tid']);
						$rep=mysql_query($q_rep) or die('');
						$r_rep=mysql_fetch_assoc($rep);
						if(mysql_num_rows($rep)){
							$ai=explode(',', $r_rep['imageid']);
							foreach($ai as $v){
								if($v!=$r_res['id'] && $v!='')$ai_n[]=$v;
							}
							$imageid=isset($ai_n)?join(',', $ai_n):'';
							if($imageid!=$r_rep['imageid']){
								$uSQL=sprintf('update %s set imageid=%s where tid=%s', $dbprefix.'topic', yjl_SQLString($imageid, 'text'), $r_res['tid']);
								$result=mysql_query($uSQL) or die('');
							}
						}
						mysql_free_result($rep);
						if(file_exists($yjl_tpath.'images/topic/'.$up[1].$r_res['id'].'_s.jpg'))unlink($yjl_tpath.'images/topic/'.$up[1].$r_res['id'].'_s.jpg');
						if(file_exists($yjl_tpath.'images/topic/'.$up[1].$r_res['id'].'_o.jpg'))unlink($yjl_tpath.'images/topic/'.$up[1].$r_res['id'].'_o.jpg');
						$dSQL=sprintf('delete from %s where id=%s', $dbprefix.'topic_image', $r_res['id']);
						$result=mysql_query($dSQL) or die('');
						echo '<script type="text/javascript">location.href=\''.$f.'?xqid='.$xqid.'&m='.$a_pm[$mid][1].'&p='.$page.'\';</script>';
						exit();
					}
					$c.='<li><a href="#" onclick="openimg($(this).attr(\'jq_o\'), \''.$r_res['width'].'\', \''.$r_res['height'].'\');return false;" title="点击查看大图" jq_o="'.$yjl_tpath.'images/topic/'.$up[1].$r_res['id'].'_o.jpg"><img src="'.$yjl_tpath.'images/topic/'.$up[1].$r_res['id'].'_s.jpg" /></a>
						<p>来自<a href="user-'.$r_res['uid'].'.html">'.$r_res['nc'].'</a>'.(($user_id>0 && ($udb['isxg']>0 || $udb['qx']==10))?' <a href="?xqid='.$xqid.'&amp;m='.$a_pm[$mid][1].'&amp;did='.$r_res['id'].'&amp;p='.$page.'" onclick="if(!confirm(\'确定删除？\'))return false;" style="color: #f00;">删除</a>':'').'</p>
					</li>';
				}while($r_res=mysql_fetch_assoc($res));
				mysql_free_result($res);
				$c.='</ul>';
				if($tp_res>1)$c.=yjl_newhmpage('einfo-'.$xqid.'-photo-p[p].html', $page, $tp_res, '', 1);
			}
			mysql_free_result($a_res);
?>