<?php
	$c.='<div class="main_left">
			<div class="vilr_nav clearfix">
				<h2 class="h2">小区活动</h2>';
	if($user_id>0 && ($udb['isxg']>0 || $udb['xqid']==$xqid || $udb['qx']>0))$c.='<div class="flt_rt"><a href="active_create.php?xqid='.$xqid.'" class="btn bt_nomgray">发起活动</a></div>';
	$c.='</div><div class="box1 clearfix">';
	if($xqid>0){
		$q_res=sprintf('select * from %s where (xqid=%s or xqid=0) order by etime desc, isgf desc, c_zan desc', $yjl_dbprefix.'hd', $xqid);
	}else{
		$q_res=sprintf('select * from %s order by etime desc, isgf desc, c_zan desc', $yjl_dbprefix.'hd');
	}
	$a_res=mysql_query($q_res) or die('');
	$tr_res=mysql_num_rows($a_res);
	if($tr_res>0){
		$c.='<ul class="list_active hover">';
		$p_size=10;
		$tp_res=ceil($tr_res/$p_size);
		if($page>$tp_res)$page=$tp_res;
		$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
		$res=mysql_query($q_l_res) or die('');
		$r_res=mysql_fetch_assoc($res);
		do{
			$c.='<li>
						<div class="pic_text2 flt_lt">
							<a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html"><img src="'.($r_res['url']!=''?$r_res['url']:'images/blank.gif" style="background: url(images/pe_d.jpg) no-repeat center;').'" /></a>
							<p class="tit"><a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html">'.$r_res['name'].'</a>';
			if($r_res['etime']>time() && $r_res['c_cy']>5)$c.='<span class="hot"></span>';
			$c.='</p><p>'.yjl_hd_date($r_res).'<br />';
			if($r_res['xqid']>0){
				if(!isset($xqname[$r_res['xqid']])){
					if(isset($xqdb) && $xqid==$r_res['xqid']){
						$xqname[$r_res['xqid']]=$xqdb['name'];
					}else{
						$q_reu=sprintf('select name from %s where xqid=%s limit 1', $yjl_dbprefix.'xq', $r_res['xqid']);
						$reu=mysql_query($q_reu) or die('');
						$r_reu=mysql_fetch_assoc($reu);
						if(mysql_num_rows($reu)>0)$xqname[$r_res['xqid']]=$r_reu['name'];
						mysql_free_result($reu);
					}
				}
				$c.='<a href="'.($xqid>0?'square':'active').'-'.$r_res['xqid'].'.html">'.$xqname[$r_res['xqid']].'</a>';
			}
			$c.=$r_res['address'].'<br />
								'.$r_res['c_gz'].' 人感兴趣&nbsp;&nbsp;'.$r_res['c_cy'].' 人参加
							</p>
						</div>
						<div class="flt_rt">
							<div class="bt_active">';
			if($user_id>0){
				$isgz=0;
				$q_rep=sprintf('select uid from %s where hdid=%s and uid=%s', $yjl_dbprefix.'hd_fuser', $r_res['hdid'], $user_id);
				$rep=mysql_query($q_rep) or die('');
				if(mysql_num_rows($rep)>0)$isgz=1;
				mysql_free_result($rep);
				$c.='<div class="bt_ltblue">'.($isgz>0?'':'<a href="'.$f.'?xqid='.$r_res['xqid'].'&amp;id='.$r_res['hdid'].'&amp;gz=1">我感兴趣('.$r_res['c_gz'].')</a>');
				if($r_res['isxzrs']==0 || $r_res['c_cy']<$r_res['xzrs']){
					if($udb['xqid']==$r_res['xqid'] || $r_res['xqid']==0 || $udb['qx']>0){
						$iscy=0;
						$ispz=0;
						$q_rep=sprintf('select iscy from %s where hdid=%s and uid=%s', $yjl_dbprefix.'hd_user', $r_res['hdid'], $user_id);
						$rep=mysql_query($q_rep) or die('');
						$r_rep=mysql_fetch_assoc($rep);
						if(mysql_num_rows($rep)>0){
							if($r_rep['iscy']==0){
								$iscy=1;
							}else{
								$ispz=1;
							}
						}
						mysql_free_result($rep);
						if($iscy==0 && $ispz==0)$c.='<a href="'.$f.'?xqid='.$r_res['xqid'].'&amp;id='.$r_res['hdid'].'&amp;j=1">我要参加('.$r_res['c_cy'].')</a>';
					}
				}
				$c.='</div>';
			}else{
				$c.='<div class="bt_ltblue"><a href="login.php?u='.urlencode('active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html').'" rel="#overlay_login">我感兴趣('.$r_res['c_gz'].')</a><a href="'.$f.'?xqid='.$r_res['xqid'].'&amp;id='.$r_res['hdid'].'&amp;j=1">我要参加('.$r_res['c_cy'].')</a></div>';
			}
			$c.='</div>
							<div style="height: 90px;overflow: hidden;"><p>'.$r_res['content'].'</p></div>
						</div>
					</li>';
		}while($r_res=mysql_fetch_assoc($res));
		mysql_free_result($res);
		$c.='</ul>';
		if($tp_res>1)$c.=yjl_newhmpage('active-'.$xqid.'-p[p].html', $page, $tp_res, '', 1);
	}else{
		$c.='<br/><br/>';
	}
	mysql_free_result($a_res);
	$c.='<br /><br />
			</div>
		</div>
		<div class="main_right">';
	if($xqid>0)$c.=yjl_newr_xq();
	$c.=yjl_newr_jlzx().'</div>';
?>