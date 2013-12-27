<?php
			$c.='<ul class="list_vgepic clearfix">';
			$q_rep=sprintf('select a.id, b.uid from %s as a, %s as b where a.tid=b.tid and b.hdid=%s order by a.dateline desc', $dbprefix.'topic_image', $yjl_dbprefix.'hd_topic', $hdid);
			$a_rep=mysql_query($q_rep) or die('');
			$tr_rep=mysql_num_rows($a_rep);
			if($tr_rep>0){
				$tp_rep=ceil($tr_rep/$p_size);
				if($page>$tp_rep)$page=$tp_rep;
				$q_l_rep=sprintf('%s limit %d, %d', $q_rep, ($page-1)*$p_size, $p_size);
				$rep=mysql_query($q_l_rep) or die('');
				$r_rep=mysql_fetch_assoc($rep);
				do{
					if(!isset($uadb[$r_rep['uid']]))$uadb[$r_rep['uid']]=yjl_udb($r_rep['uid']);
					$up=yjl_imgpath($r_rep['id']);
					$c.='<li><a href="activeimg-'.$xqid.'-'.$hdid.'-'.$r_rep['id'].'.html"'.($user_id>0?'':' rel="#overlay_login"').'><img src="'.$yjl_tpath.'images/topic/'.$up[1].$r_rep['id'].'_s.jpg" /></a>
						<p>来自 <a href="user-'.$r_rep['uid'].'.html">'.$uadb[$r_rep['uid']]['nc'].'</a></p>
					</li>';
				}while($r_rep=mysql_fetch_assoc($rep));
				mysql_free_result($rep);
			}else{
				$c.='<li>没有照片</li>';
			}
			mysql_free_result($a_rep);
			$c.='</ul>';
			if(isset($tp_rep) && $tp_rep>1)$c.=yjl_newhmpage('activeimg-'.$xqid.'-'.$hdid.'-p[p].html', $page, $tp_rep);
			$c.='</div>
		<div class="main_right">
			<div class="back"><a href="active-'.$xqid.'-'.$hdid.'.html">返回活动页 &#187;</a></div>';
			$q_rep=sprintf('select a.* from %s as a, %s as b, %s as c where (a.type=%s or a.type=%s) and a.totid=b.tid and b.tid=c.tid and c.hdid=%s order by a.dateline desc limit 10', $dbprefix.'topic', $dbprefix.'topic_image', $yjl_dbprefix.'hd_topic', yjl_SQLString('reply', 'text'), yjl_SQLString('both', 'text'), $hdid);
			$rep=mysql_query($q_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				$c.='<div class="box2">
				<h4>最新评论</h4>
				<ul class="list_reply">';
				do{
					if(!isset($uadb[$r_rep['uid']]))$uadb[$r_rep['uid']]=yjl_udb($r_rep['uid']);
					$c.='<li>
							<div class="pic_text clearfix">
								<a href="user-'.$r_rep['uid'].'.html"><img src="'.yjl_face($r_rep['uid'], $uadb[$r_rep['uid']]['face']).'" /></a>
								<p class="memb"><a href="user-'.$r_rep['uid'].'.html">'.$uadb[$r_rep['uid']]['nc'].'</a><span>'.yjl_wbdate($r_rep['dateline']).'</span></p>
								<p>'.yjl_wbdecode($r_rep['content']).'</p>
							</div>
						</li>';
				}while($r_rep=mysql_fetch_assoc($rep));
				$c.='</ul></div>';
			}
			mysql_free_result($rep);
?>