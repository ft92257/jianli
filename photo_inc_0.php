<?php
				$htmlwc='</div>
		<div class="main_right">
			<div class="box2 clearfix">
				<h2>'.$r_res['name'].'</h2>
				<div class="pic_text02 clearfix">
					<img src="images/blank.gif" width="'.$a_wh_jltpt[0].'" height="'.$a_wh_jltpt[1].'" style="background: url('.$pu.') no-repeat center;" />
					<p>';
				if(isset($a_jlda))$htmlwc.=join('<br/>', $a_jlda);
				$htmlwc.='</p>
				</div>
				<ul class="perfocus">
					<li><a href="user-'.$r_res['hzid'].'.html"><img src="'.yjl_face($r_res['hzid'], $uadb[$r_res['hzid']]['face']).'" /></a>
						业主<br /><a href="user-'.$r_res['hzid'].'.html">'.$uadb[$r_res['hzid']]['nc'].'</a>
					</li>';
				if($r_res['uid']>0)$htmlwc.='<li><a href="user-'.$r_res['uid'].'.html"><img src="'.yjl_face($r_res['uid'], $uadb[$r_res['uid']]['face']).'"/></a>
						监理师<br /><a href="user-'.$r_res['uid'].'.html">'.$uadb[$r_res['uid']]['nc'].'</a>
					</li>';
				$htmlwc.='</ul><br class="clear" /><br /></div></div>';
			if($r_res['hzqr']==0){
				if($user_id>0 && $user_id==$r_res['hzid']){
					if(isset($_GET['acp']) && $_GET['acp']==1){
						$uSQL=sprintf('update %s set hzqr=1, lasttime=%s where jlid=%s', $yjl_dbprefix.'jl', time(), $jlid);
						$result=mysql_query($uSQL) or die('');
						$uSQL=sprintf('update %s set c_jl=c_jl+1 where xqid=%s', $yjl_dbprefix.'xq', $xqid);
						$result=mysql_query($uSQL) or die('');
						if($user_id!=$r_res['hzid'])yjl_follow($r_res['hzid'], $user_id);
						yjl_addlog('[uid]的监理项目：<a href="photo-'.$xqid.'-'.$jlid.'.html">'.$r_res['name'].'</a>', md5('jlyzty|'.$r_res['uid'].'|'.$user_id.'|'.$jlid), 0, $r_res['uid']);
						echo '<script type="text/javascript">location.href=\'photo-'.$xqid.'-'.$jlid.'.html\';</script>';
						exit();
					}elseif(isset($_GET['acp']) && $_GET['acp']==2){
						$dSQL=sprintf('delete from %s where jlid=%s', $yjl_dbprefix.'jl', $r_res['jlid']);
						$result=mysql_query($dSQL) or die('');
						yjl_addlog('[uid]不同意[luid]创建的的监理项目：<a href="photo-'.$xqid.'-'.$jlid.'.html">'.$r_res['name'].'</a>', md5('jlyzbty|'.$r_res['uid'].'|'.$user_id.'|'.$jlid), 1, $r_res['uid']);
						echo '<script type="text/javascript">location.href=\'photo-'.$xqid.'-'.$jlid.'.html\';</script>';
						exit();
					}
					$c.='<div class="document">
				<div class="title">
					<h3>等待业主确认</h3>
				</div>
				<ul class="list_doc hover">
					<li>是否同意<a href="user-'.$r_res['uid'].'.html">'.$uadb[$r_res['uid']]['nc'].'</a>为你创建的监理项目？</li>
				</ul><a href="'.$f.'?id='.$jlid.'&amp;xqid='.$xqid.'&amp;acp=1" class="btn bt_smblue">同 意</a> <a href="'.$f.'?id='.$jlid.'&amp;xqid='.$xqid.'&amp;acp=2" class="btn bt_smblue">不同意</a><br/><br/>
			</div>';
					echo yjl_html($c.$htmlwc, 'supervisor', '', 1);
				}elseif($user_id>0 && $user_id==$r_res['uid']){
					$c.='<div class="document">
				<div class="title">
					<h3>等待业主确认</h3>
				</div>
				<ul class="list_doc hover">
					<li>请等待业主确认</li>
				</ul>
			</div>';
					echo yjl_html($c.$htmlwc, 'supervisor', '', 1);
				}else{
					echo '<script type="text/javascript">location.href=\'photo-'.$xqid.'.html\';</script>';
				}
				exit();
			}elseif($r_res['jlqr']==0){
				if($user_id>0 && $user_id==$r_res['uid']){
					if(isset($_GET['acp']) && $_GET['acp']==1){
						$uSQL=sprintf('update %s set jlqr=1, lasttime=%s where jlid=%s', $yjl_dbprefix.'jl', time(), $jlid);
						$result=mysql_query($uSQL) or die('');
						if($user_id!=$r_res['hzid'])yjl_follow($r_res['hzid'], $user_id);
						yjl_addlog('[uid]成为[luid]的监理项目的监理师：<a href="photo-'.$xqid.'-'.$jlid.'.html">'.$r_res['name'].'</a>', md5('jljlty|'.$r_res['hzid'].'|'.$user_id.'|'.$jlid), 0, $r_res['hzid']);
						echo '<script type="text/javascript">location.href=\'photo-'.$xqid.'-'.$jlid.'.html\';</script>';
						exit();
					}elseif(isset($_GET['acp']) && $_GET['acp']==2){
						$uSQL=sprintf('update %s set uid=0, lasttime=%s where jlid=%s', $yjl_dbprefix.'jl', time(), $jlid);
						$result=mysql_query($uSQL) or die('');
						yjl_addlog('[uid]不同意成为[luid]的监理项目的监理师：<a href="photo-'.$xqid.'-'.$jlid.'.html">'.$r_res['name'].'</a>', md5('jljlbty|'.$r_res['hzid'].'|'.$user_id.'|'.$jlid), 1, $r_res['hzid']);
						echo '<script type="text/javascript">location.href=\'photo-'.$xqid.'-'.$jlid.'.html\';</script>';
						exit();
					}
					$c.='<div class="document">
				<div class="title">
					<h3>等待监理师确认</h3>
				</div>
				<ul class="list_doc hover">
					<li>是否同意成为'.$r_res['name'].'的监理师？</li>
				</ul><a href="'.$f.'?id='.$jlid.'&amp;xqid='.$xqid.'&amp;acp=1" class="btn bt_smblue">同 意</a> <a href="'.$f.'?id='.$jlid.'&amp;xqid='.$xqid.'&amp;acp=2" class="btn bt_smblue">不同意</a><br/><br/>
			</div>';
					echo yjl_html($c.$htmlwc, 'supervisor', '', 1);
					exit();
				}
			}
?>