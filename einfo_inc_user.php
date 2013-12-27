<?php
			$q_res=sprintf('select a.uid, a.face, b.nc from %s as a, %s as b where a.uid=b.uid and b.xqid=%s and b.qx=0 and b.isnc>0 order by a.uid desc', $dbprefix.'members', $yjl_dbprefix.'members', $xqid);
			$a_res=mysql_query($q_res) or die('');
			$tr_res=mysql_num_rows($a_res);
			if($tr_res>0){
				$c.='<ul class="list_friend hover">';
				$p_size=10;
				$tp_res=ceil($tr_res/$p_size);
				if($page>$tp_res)$page=$tp_res;
				$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
				$res=mysql_query($q_l_res) or die('');
				$r_res=mysql_fetch_assoc($res);
				do{
					$c.='<li><div class="flt_lt">
							<div class="pic_text clearfix">
								<a href="user-'.$r_res['uid'].'.html"><img src="'.yjl_face($r_res['uid'], $r_res['face']).'" /></a>
								<p class="memb"><a href="user-'.$r_res['uid'].'.html">'.$r_res['nc'].'</a></p>
								<p>'.(isset($a_dq)?join('，', $a_dq):'').'<br />
								'.$xqdb['name'].'
								</p>
							</div>
						</div>
						<div class="flt_rt" id="gz_'.$r_res['uid'].'">';
					if($user_id>0 && $user_id!=$r_res['uid']){
						$q_rep=sprintf('select uid from %s where uid=%s and buddyid=%s', $dbprefix.'buddys', $user_id, $r_res['uid']);
						$rep=mysql_query($q_rep) or die('');
						$isgz=mysql_num_rows($rep)>0?1:0;
						mysql_free_result($rep);
						$c.='<a href="#" onclick="$(\'#gz_'.$r_res['uid'].'\').load(\'j/gz.php?id='.$r_res['uid'].'\');return false;"'.($isgz>0?'>取消关注':' class="btn bt_nomblue">关 注').'</a>';
					}
					$c.='</div>
					</li>';
				}while($r_res=mysql_fetch_assoc($res));
				mysql_free_result($res);
				$c.='</ul>';
				if($tp_res>1)$c.=yjl_newhmpage('einfo-'.$xqid.'-user-p[p].html', $page, $tp_res, '', 1);
			}
			mysql_free_result($a_res);
?>