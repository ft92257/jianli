<?php
function yjl_user_info(){
	global $user_id, $udb, $cuid, $uadb, $xqid, $xqdb, $a_tsgz, $yjl_dbprefix, $dbprefix, $isgz;
	$c='<div class="box2 owner clearfix">
				<div class="pic_text clearfix">
					<a href="user-'.$cuid.'.html"><img src="'.yjl_face($cuid, $uadb[$cuid]['face']).'" /></a>
					<p class="memb"><a href="user-'.$cuid.'.html">'.$uadb[$cuid]['nc'].'</a>';
	if($uadb[$cuid]['qx']==10){
		$c.='管理员';
	}elseif($uadb[$cuid]['qx']==5 || $uadb[$cuid]['qx']==6){
		$c.=$a_tsgz[$uadb[$cuid]['qx']][$uadb[$cuid]['gzfl']];
	}else{
		$c.='业主';
	}
	$c.='</p>';
	if($uadb[$cuid]['xqid']>0){
		if($uadb[$cuid]['xqid']!=$xqid){
			$q_reu=sprintf('select name from %s where xqid=%s limit 1', $yjl_dbprefix.'xq', $uadb[$cuid]['xqid']);
			$reu=mysql_query($q_reu) or die('');
			$r_reu=mysql_fetch_assoc($reu);
			if(mysql_num_rows($reu)>0)$xqname=$r_reu['name'];
			mysql_free_result($reu);
		}else{
			$xqname=$xqdb['name'];
		}
		$c.='<a href="square-'.$uadb[$cuid]['xqid'].'.html">'.$xqname.'</a>';
	}
	$q_res=sprintf('select a.hdid from %s as a, %s as b where a.hdid=b.hdid and b.uid=%s and b.iscy=0', $yjl_dbprefix.'hd', $yjl_dbprefix.'hd_user', $cuid);
	$res=mysql_query($q_res) or die('');
	$c0=mysql_num_rows($res);
	mysql_free_result($res);
	$q_res=sprintf('select uid from %s where uid=%s', $dbprefix.'buddys', $cuid);
	$res=mysql_query($q_res) or die('');
	$c1=mysql_num_rows($res);
	mysql_free_result($res);
	$q_res=sprintf('select uid from %s where uid=%s and type<>%s', $dbprefix.'topic', $cuid, yjl_SQLString('reply', 'text'));
	$res=mysql_query($q_res) or die('');
	$c2=mysql_num_rows($res);
	mysql_free_result($res);
	$c.='</div>';
	if($user_id>0){
		if($user_id!=$cuid)$c.='<div class="owner_btn"><span id="gz_'.$cuid.'"><a href="#" onclick="$(\'#gz_'.$cuid.'\').load(\'j/gz.php?id='.$cuid.'\');return false;"'.($isgz>0?'>取消关注':' class="btn bt_nomblue">关 注').'</a></span><a href="msg.php?id='.$cuid.'" class="btn bt_nomgray">发私信</a></div>';
		$c.='<div class="count">
					<a href="user_active.php?id='.$cuid.'" style="padding-left:0px;"><b>'.$c0.'</b><br />活动</a><a href="follow.php?id='.$cuid.'"><b>'.$c1.'</b><br />好友</a><a href="user-'.$cuid.'.html" style="border:none;"><b>'.$c2.'</b><br />微博</a>
				</div>
				<p>'.$uadb[$cuid]['aboutme'].'</p>';
		if($user_id==$cuid){
			$c.='<a href="profile.php" class="eadit">编辑</a>';
		}elseif($udb['qx']==10 || $udb['isxg']>0){
			$c.='<a href="a_uinfo.php?id='.$cuid.'" class="eadit">查看用户详细信息</a>';
		}
	}
	$c.='</div>';
	return $c;
}

function yjl_user_gz(){
	global $user_id, $cuid, $uadb, $yjl_dbprefix, $dbprefix;
	$q_res=sprintf('select b.uid, b.face, c.nc from %s as a, %s as b, %s as c where a.uid=%s and a.buddyid=b.uid and b.uid=c.uid order by a.dateline desc limit 6', $dbprefix.'buddys', $dbprefix.'members', $yjl_dbprefix.'members', $cuid);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$c='<div class="box2">
				<h3>'.($user_id==$cuid?'我':'TA').'关注的人</h3>
				<ul class="friend clearfix">';
		do{
			$c.='<li><a href="user-'.$r_res['uid'].'.html" title="'.$r_res['nc'].'"><img src="'.yjl_face($r_res['uid'], $r_res['face']).'" /><br />'.($r_res['nc']!=''?yjl_substrs($r_res['nc'], 4):'&nbsp;').'</a></li>';
		}while($r_res=mysql_fetch_assoc($res));
		$c.='</ul></div>';
		return $c;
	}
	mysql_free_result($res);
}

function yjl_user_jltj(){
	global $user_id, $u_lid, $udb, $yjl_dbprefix, $dbprefix, $a_wh_jltpt;
	$cuid=$user_id;
	$tj_m=4;
	$tj_i=0;
	if($udb['fxid']>0 && $udb['fg']>0 && $udb['ys']>0){
		$q_rep=sprintf('select a.name, a.jlid, a.xqid, a.lid, b.name as b_name from %s as a, %s as b where a.fxid=%s and a.ys=%s and a.fg=%s and a.lid>=%s and a.xqid=b.xqid and a.c_zp>4 and a.hzid<>%s and a.uid<>%s order by a.lasttime desc limit %s', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', $udb['fxid'], $udb['ys'], $udb['fg'], $u_lid, $cuid, $cuid, $tj_m);
		$rep=mysql_query($q_rep) or die('');
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0){
			do{
				if(!isset($a_tj[$r_rep['jlid']])){
					$a_tj[$r_rep['jlid']]=$r_rep;
					$a_tj[$r_rep['jlid']]['ly']='户型风格预算相同';
					$tj_i++;
				}
			}while($r_rep=mysql_fetch_assoc($rep));
		}
		mysql_free_result($rep);
	}
	if($tj_i<$tj_m){
		if($udb['fxid']>0 && $udb['fg']>0){
			$q_rep=sprintf('select a.name, a.jlid, a.xqid, a.lid, b.name as b_name from %s as a, %s as b where a.fxid=%s and a.fg=%s and a.lid>=%s and a.xqid=b.xqid and a.c_zp>4 and a.hzid<>%s and a.uid<>%s order by a.lasttime desc limit %s', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', $udb['fxid'], $udb['fg'], $u_lid, $cuid, $cuid, $tj_m);
			$rep=mysql_query($q_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				do{
					if(!isset($a_tj[$r_rep['jlid']]) && $tj_i<$tj_m){
						$a_tj[$r_rep['jlid']]=$r_rep;
						$a_tj[$r_rep['jlid']]['ly']='户型风格相同';
						$tj_i++;
					}
				}while($r_rep=mysql_fetch_assoc($rep));
			}
			mysql_free_result($rep);
		}
	}
	if($tj_i<$tj_m){
		if($udb['fxid']>0 && $udb['ys']>0){
			$q_rep=sprintf('select a.name, a.jlid, a.xqid, a.lid, b.name as b_name from %s as a, %s as b where a.fxid=%s and a.ys=%s and a.lid>=%s and a.xqid=b.xqid and a.c_zp>4 and a.hzid<>%s and a.uid<>%s order by a.lasttime desc limit %s', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', $udb['fxid'], $udb['ys'], $u_lid, $cuid, $cuid, $tj_m);
			$rep=mysql_query($q_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				do{
					if(!isset($a_tj[$r_rep['jlid']]) && $tj_i<$tj_m){
						$a_tj[$r_rep['jlid']]=$r_rep;
						$a_tj[$r_rep['jlid']]['ly']='户型预算相同';
						$tj_i++;
					}
				}while($r_rep=mysql_fetch_assoc($rep));
			}
			mysql_free_result($rep);
		}
	}
	if($tj_i<$tj_m){
		if($udb['fg']>0 && $udb['ys']>0){
			$q_rep=sprintf('select a.name, a.jlid, a.xqid, a.lid, b.name as b_name from %s as a, %s as b where a.fg=%s and a.ys=%s and a.lid>=%s and a.xqid=b.xqid and a.c_zp>4 and a.hzid<>%s and a.uid<>%s order by a.lasttime desc limit %s', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', $udb['fg'], $udb['ys'], $u_lid, $cuid, $cuid, $tj_m);
			$rep=mysql_query($q_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				do{
					if(!isset($a_tj[$r_rep['jlid']]) && $tj_i<$tj_m){
						$a_tj[$r_rep['jlid']]=$r_rep;
						$a_tj[$r_rep['jlid']]['ly']='风格预算相同';
						$tj_i++;
					}
				}while($r_rep=mysql_fetch_assoc($rep));
			}
			mysql_free_result($rep);
		}
	}
	if($tj_i<$tj_m){
		if($udb['fxid']>0){
			$q_rep=sprintf('select a.name, a.jlid, a.xqid, a.lid, b.name as b_name from %s as a, %s as b where a.fxid=%s and a.lid>=%s and a.xqid=b.xqid and a.c_zp>4 and a.hzid<>%s and a.uid<>%s order by a.lasttime desc limit %s', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', $udb['fxid'], $u_lid, $cuid, $cuid, $tj_m);
			$rep=mysql_query($q_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				do{
					if(!isset($a_tj[$r_rep['jlid']]) && $tj_i<$tj_m){
						$a_tj[$r_rep['jlid']]=$r_rep;
						$a_tj[$r_rep['jlid']]['ly']='户型相同';
						$tj_i++;
					}
				}while($r_rep=mysql_fetch_assoc($rep));
			}
			mysql_free_result($rep);
		}
	}
	if($tj_i<$tj_m){
		if($udb['fg']>0){
			$q_rep=sprintf('select a.name, a.jlid, a.xqid, a.lid, b.name as b_name from %s as a, %s as b where a.fg=%s and a.lid>=%s and a.xqid=b.xqid and a.c_zp>4 and a.hzid<>%s and a.uid<>%s order by a.lasttime desc limit %s', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', $udb['fg'], $u_lid, $cuid, $cuid, $tj_m);
			$rep=mysql_query($q_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				do{
					if(!isset($a_tj[$r_rep['jlid']]) && $tj_i<$tj_m){
						$a_tj[$r_rep['jlid']]=$r_rep;
						$a_tj[$r_rep['jlid']]['ly']='风格相同';
						$tj_i++;
					}
				}while($r_rep=mysql_fetch_assoc($rep));
			}
			mysql_free_result($rep);
		}
	}
	if($tj_i<$tj_m){
		if($udb['ys']>0){
			$q_rep=sprintf('select a.name, a.jlid, a.xqid, a.lid, b.name as b_name from %s as a, %s as b where a.ys=%s and a.lid>=%s and a.xqid=b.xqid and a.c_zp>4 and a.hzid<>%s and a.uid<>%s order by a.lasttime desc limit %s', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', $udb['ys'], $u_lid, $cuid, $cuid, $tj_m);
			$rep=mysql_query($q_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				do{
					if(!isset($a_tj[$r_rep['jlid']]) && $tj_i<$tj_m){
						$a_tj[$r_rep['jlid']]=$r_rep;
						$a_tj[$r_rep['jlid']]['ly']='预算相同';
						$tj_i++;
					}
				}while($r_rep=mysql_fetch_assoc($rep));
			}
			mysql_free_result($rep);
		}
	}
	if($tj_i<$tj_m){
		if($udb['xqid']>0){
			$q_rep=sprintf('select a.name, a.jlid, a.xqid, a.lid, b.name as b_name from %s as a, %s as b where a.xqid=%s and a.lid>=%s and a.xqid=b.xqid and a.c_zp>4 and a.hzid<>%s and a.uid<>%s order by a.lasttime desc limit %s', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', $udb['xqid'], $u_lid, $cuid, $cuid, $tj_m);
			$rep=mysql_query($q_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				do{
					if(!isset($a_tj[$r_rep['jlid']]) && $tj_i<$tj_m){
						$a_tj[$r_rep['jlid']]=$r_rep;
						$tj_i++;
					}
				}while($r_rep=mysql_fetch_assoc($rep));
			}
			mysql_free_result($rep);
		}
	}
	if($tj_i>0){
		$c='<div class="box2 clearfix">
			<h3>同类项目推荐</h3>
			<ul class="list_visit">';
		foreach($a_tj as $v){
			$pu='images/jl_d.jpg';
			$q_reu=sprintf('select * from %s where jlid=%s and is_del=0 order by datetime desc, jpid desc limit 1', $yjl_dbprefix.'jl_photo', $v['jlid']);
			$reu=mysql_query($q_reu) or die('');
			$r_reu=mysql_fetch_assoc($reu);
			if(mysql_num_rows($reu)>0)$pu=$r_reu['t_url'];
			mysql_free_result($reu);
			$c.='<li><a href="photo-'.$v['xqid'].'-'.$v['jlid'].'.html"><img src="images/blank.gif" width="'.$a_wh_jltpt[0].'" height="'.$a_wh_jltpt[1].'" style="background: url('.$pu.') no-repeat center;" /></a>
					<em class="percent'.$v['lid'].'"></em>
					<p><a href="photo-'.$v['xqid'].'-'.$v['jlid'].'.html" title="'.$v['name'].'">'.yjl_substrs($v['name'], 7).'</a></p>
					<p title="'.$v['b_name'].'">'.yjl_substrs($v['b_name'], 7).'</p>
					<p>'.(isset($v['ly'])?$v['ly']:'&nbsp;').'</p>
				</li>';
		}
		$c.='</ul></div>';
		return $c;
	}
}
?>