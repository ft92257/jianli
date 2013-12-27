<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once('function.php');
$f='user_active.php';
if($udb['uid']>0){
	if(($udb['iswc']!=1 && ($udb['qx']==5 || $udb['qx']==6)) || $udb['isnc']==0){
		echo '<script type="text/javascript">location.href=\'./\';</script>';
		exit();
	}
}else{
	echo '<script type="text/javascript">location.href=\'login.php\';</script>';
	exit();
}
$cuid=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):$udb['uid'];
if($cuid!=$udb['uid']){
	$cudb=yjl_udb($cuid);
	if($cudb['uid']==0){
		echo '<script type="text/javascript">location.href=\''.$f.'\';</script>';
		exit();
	}
	$uadb[$cuid]=$cudb;
}
if($xqid==0 && $uadb[$cuid]['xqid']>0)$xqid=$uadb[$cuid]['xqid'];
if($xqid>0){
	$q_res=sprintf('select * from %s where xqid=%s and iskf=1 limit 1', $yjl_dbprefix.'xq', $xqid);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$xqdb=$r_res;
		$c_l1id=$xqdb['l1id'];
		$xqname[$xqid]=$r_res['name'];
	}
	mysql_free_result($res);
}
$page_title=$uadb[$cuid]['nc'].' '.(($user_id>0 && $user_id==$cuid)?'我参与的活动':'活动');
$c='';
if($user_id>0 && $user_id!=$cuid)yjl_vlog($cuid, 2);
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
if(isset($_GET['my']) && $_GET['my']==1){
	$q_res=sprintf('select a.hdid from %s as a, %s as b where a.hdid=b.hdid and b.uid=%s and b.iscy=0 and a.etime>=%s', $yjl_dbprefix.'hd', $yjl_dbprefix.'hd_user', $cuid, time());
	$res=mysql_query($q_res) or die('');
	$c0=mysql_num_rows($res);
	mysql_free_result($res);
	$q_res=sprintf('select a.hdid from %s as a, %s as b where a.hdid=b.hdid and b.uid=%s and b.iscy=0 and a.etime<%s', $yjl_dbprefix.'hd', $yjl_dbprefix.'hd_user', $cuid, time());
	$res=mysql_query($q_res) or die('');
	$c1=mysql_num_rows($res);
	mysql_free_result($res);
	$eid=(isset($_GET['e']) && $_GET['e']==1)?1:0;
	$c.='<div class="title">
				<h3>我参加的活动</h3>
				<div class="sub_title">
					<a href="?id='.$cuid.'">全部</a>|<span>我参加的</span>|<a href="?id='.$cuid.'&amp;fav=1">我关注的</a>|<a href="?id='.$cuid.'&amp;add=1">我发起的</a>
				</div>';
	if($udb['uid']==$cuid && ($udb['xqid']>0 || $udb['isxg']>0 || $udb['qx']>0))$c.='<div class="flt_rt"><a href="active_create.php?xqid='.$xqid.'" class="btn bt_smgray">发起活动</a></div>';
	$c.='</div>
			<div class="act_cont clearfix">
				<div class="state">'.($eid>0?'<a href="?id='.$cuid.'&amp;my=1">可参加（'.$c0.'）</a><span>已结束（'.$c1.'）</span>':'<span>可参加（'.$c0.'）</span>&nbsp;&nbsp;<a href="?id='.$cuid.'&amp;my=1&amp;e=1">已结束（'.$c1.'）</a>').'</div>';
	if(($c0+$c1)>0){
		$edb=$eid>0?'<':'>=';
		$q_res=sprintf('select a.* from %s as a, %s as b where a.hdid=b.hdid and b.uid=%s and b.iscy=0 and a.etime%s%s order by a.isgf desc, a.lasttime desc', $yjl_dbprefix.'hd', $yjl_dbprefix.'hd_user', $cuid, $edb, time());
		$a_res=mysql_query($q_res) or die('');
		$tr_res=mysql_num_rows($a_res);
		if($tr_res>0){
			$p_size=20;
			$c.='<br /><br /><ul class="list_active">';
			$tp_res=ceil($tr_res/$p_size);
			if($page>$tp_res)$page=$tp_res;
			$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
			$res=mysql_query($q_l_res) or die('');
			$r_res=mysql_fetch_assoc($res);
			do{
				$c.='<li><a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html"><img src="'.($r_res['url']!=''?$r_res['url']:'images/blank.gif" style="background: url(images/pe_d.jpg) no-repeat center;').'" /></a>
						<a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html">'.$r_res['name'].'</a>
						<p>'.yjl_hd_date($r_res);
				if($r_res['xqid']>0){
					if(!isset($xqname[$r_res['xqid']])){
						$q_req=sprintf('select name from %s where xqid=%s and iskf=1 limit 1', $yjl_dbprefix.'xq', $r_res['xqid']);
						$req=mysql_query($q_req) or die('');
						$r_req=mysql_fetch_assoc($req);
						if(mysql_num_rows($req)>0)$xqname[$r_res['xqid']]=$r_req['name'];
						mysql_free_result($req);
					}
					$c.='<br /><a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html">'.$xqname[$r_res['xqid']].'</a>';
				}
				$c.='</p>
						'.$r_res['c_gz'].' 人感兴趣&nbsp;&nbsp;'.$r_res['c_cy'].' 人参加
					</li>';
			}while($r_res=mysql_fetch_assoc($res));
			mysql_free_result($res);
			$c.='</ul>';
			if($tp_res>1)$c.=yjl_newpage($page, $tp_res);
		}
		mysql_free_result($a_res);
	}
	$c.='<br class="clear" /><br /><br /></div>';
}elseif(isset($_GET['fav']) && $_GET['fav']==1){
	$c.='<div class="title">
				<h3>我关注的活动</h3>
				<div class="sub_title">
					<a href="?id='.$cuid.'">全部</a>|<a href="?id='.$cuid.'&amp;my=1">我参加的</a>|<span>我关注的</span>|<a href="?id='.$cuid.'&amp;add=1">我发起的</a>
				</div>';
	if($udb['uid']==$cuid && ($udb['xqid']>0 || $udb['isxg']>0 || $udb['qx']>0))$c.='<div class="flt_rt"><a href="active_create.php?xqid='.$xqid.'" class="btn bt_smgray">发起活动</a></div>';
	$c.='</div>
			<div class="act_cont clearfix">';
	$q_res=sprintf('select a.hdid from %s as a, %s as b where a.hdid=b.hdid and b.uid=%s and a.etime>=%s', $yjl_dbprefix.'hd', $yjl_dbprefix.'hd_fuser', $cuid, time());
	$res=mysql_query($q_res) or die('');
	$c0=mysql_num_rows($res);
	mysql_free_result($res);
	$q_res=sprintf('select a.hdid from %s as a, %s as b where a.hdid=b.hdid and b.uid=%s and a.etime<%s', $yjl_dbprefix.'hd', $yjl_dbprefix.'hd_fuser', $cuid, time());
	$res=mysql_query($q_res) or die('');
	$c1=mysql_num_rows($res);
	mysql_free_result($res);
	$c.='<div class="state"><span>( 可参加<span class="color_red">'.$c0.'</span>·已结束<span>'.$c1.'</span> )</span></div>';
	if(($c0+$c1)>0){
		$q_res=sprintf('select a.* from %s as a, %s as b where a.hdid=b.hdid and b.uid=%s order by a.isgf desc, a.lasttime desc', $yjl_dbprefix.'hd', $yjl_dbprefix.'hd_fuser', $cuid);
		$a_res=mysql_query($q_res) or die('');
		$tr_res=mysql_num_rows($a_res);
		if($tr_res>0){
			$p_size=30;
			$c.='<ul class="list_lkactive">';
			$tp_res=ceil($tr_res/$p_size);
			if($page>$tp_res)$page=$tp_res;
			$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
			$res=mysql_query($q_l_res) or die('');
			$r_res=mysql_fetch_assoc($res);
			do{
				$c.='<li><a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html"><img src="'.($r_res['url']!=''?$r_res['url']:'images/blank.gif" style="background: url(images/pe_d.jpg) no-repeat center;').'" /></a>
						<a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html">'.$r_res['name'].'</a><br />
						'.yjl_hd_date($r_res).'
					</li>';
			}while($r_res=mysql_fetch_assoc($res));
			mysql_free_result($res);
			$c.='</ul>';
			if($tp_res>1)$c.=yjl_newpage($page, $tp_res);
		}
		mysql_free_result($a_res);
	}
	$c.='<br class="clear" /><br /><br /></div>';
}elseif(isset($_GET['add']) && $_GET['add']==1){
	$c.='<div class="title">
				<h3>我发起的活动</h3>
				<div class="sub_title">
					<a href="?id='.$cuid.'">全部</a>|<a href="?id='.$cuid.'&amp;my=1">我参加的</a>|<a href="?id='.$cuid.'&amp;fav=1">我关注的</a>|<span>我发起的</span>
				</div>';
	if($udb['uid']==$cuid && ($udb['xqid']>0 || $udb['isxg']>0 || $udb['qx']>0))$c.='<div class="flt_rt"><a href="active_create.php?xqid='.$xqid.'" class="btn bt_smgray">发起活动</a></div>';
	$c.='</div>
			<div class="act_cont clearfix">';
	$q_res=sprintf('select hdid from %s where uid=%s and etime>=%s', $yjl_dbprefix.'hd', $cuid, time());
	$res=mysql_query($q_res) or die('');
	$c0=mysql_num_rows($res);
	mysql_free_result($res);
	$q_res=sprintf('select hdid from %s where uid=%s and etime<%s', $yjl_dbprefix.'hd', $cuid, time());
	$res=mysql_query($q_res) or die('');
	$c1=mysql_num_rows($res);
	mysql_free_result($res);
	$c.='<div class="state"><span>( 可参加<span class="color_red">'.$c0.'</span>·已结束<span>'.$c1.'</span> )</span></div>';
	if(($c0+$c1)>0){
		$q_res=sprintf('select * from %s where uid=%s order by isgf desc, lasttime desc', $yjl_dbprefix.'hd', $cuid);
		$a_res=mysql_query($q_res) or die('');
		$tr_res=mysql_num_rows($a_res);
		if($tr_res>0){
			$p_size=20;
			$c.='<ul class="list_active">';
			$tp_res=ceil($tr_res/$p_size);
			if($page>$tp_res)$page=$tp_res;
			$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
			$res=mysql_query($q_l_res) or die('');
			$r_res=mysql_fetch_assoc($res);
			do{
				$c.='<li><a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html"><img src="'.($r_res['url']!=''?$r_res['url']:'images/blank.gif" style="background: url(images/pe_d.jpg) no-repeat center;').'" /></a>
						<a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html">'.$r_res['name'].'</a>
						<p>'.yjl_hd_date($r_res);
				if($r_res['xqid']>0){
					if(!isset($xqname[$r_res['xqid']])){
						$q_req=sprintf('select name from %s where xqid=%s and iskf=1 limit 1', $yjl_dbprefix.'xq', $r_res['xqid']);
						$req=mysql_query($q_req) or die('');
						$r_req=mysql_fetch_assoc($req);
						if(mysql_num_rows($req)>0)$xqname[$r_res['xqid']]=$r_req['name'];
						mysql_free_result($req);
					}
					$c.='<br /><a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html">'.$xqname[$r_res['xqid']].'</a>';
				}
				$c.='</p>
						'.$r_res['c_gz'].' 人感兴趣&nbsp;&nbsp;'.$r_res['c_cy'].' 人参加
					</li>';
			}while($r_res=mysql_fetch_assoc($res));
			mysql_free_result($res);
			$c.='</ul>';
			if($tp_res>1)$c.=yjl_newpage($page, $tp_res);
		}
		mysql_free_result($a_res);
	}
	$c.='<br class="clear" /><br /><br /></div>';
}else{
	$q_res=sprintf('select a.hdid from %s as a, %s as b where a.hdid=b.hdid and b.uid=%s and b.iscy=0 and a.etime>=%s', $yjl_dbprefix.'hd', $yjl_dbprefix.'hd_user', $cuid, time());
	$res=mysql_query($q_res) or die('');
	$c0=mysql_num_rows($res);
	mysql_free_result($res);
	$q_res=sprintf('select a.hdid from %s as a, %s as b where a.hdid=b.hdid and b.uid=%s and b.iscy=0 and a.etime<%s', $yjl_dbprefix.'hd', $yjl_dbprefix.'hd_user', $cuid, time());
	$res=mysql_query($q_res) or die('');
	$c1=mysql_num_rows($res);
	mysql_free_result($res);
	$c.='<div class="title">
				<h3>我参加的活动</h3>
				<div class="sub_title">
					<span>全部</span>|<a href="?id='.$cuid.'&amp;my=1">我参加的</a>|<a href="?id='.$cuid.'&amp;fav=1">我关注的</a>|<a href="?id='.$cuid.'&amp;add=1">我发起的</a>
				</div>';
	if($udb['uid']==$cuid && ($udb['xqid']>0 || $udb['isxg']>0 || $udb['qx']>0))$c.='<div class="flt_rt"><a href="active_create.php?xqid='.$xqid.'" class="btn bt_smgray">发起活动</a></div>';
	$c.='</div>
			<div class="act_cont clearfix">
				<div class="state"><a href="?id='.$cuid.'&amp;my=1">可参加（'.$c0.'）</a><a href="?id='.$cuid.'&amp;my=1&amp;e=1">已结束（'.$c1.'）</a></div>';
	if(($c0+$c1)>0){
		$q_res=sprintf('select a.* from %s as a, %s as b where a.hdid=b.hdid and b.uid=%s and b.iscy=0 order by a.isgf desc, a.lasttime desc limit 4', $yjl_dbprefix.'hd', $yjl_dbprefix.'hd_user', $cuid);
		$res=mysql_query($q_res) or die('');
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0){
			$c.='<br /><br /><ul class="list_active">';
			do{
				$c.='<li><a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html"><img src="'.($r_res['url']!=''?$r_res['url']:'images/blank.gif" style="background: url(images/pe_d.jpg) no-repeat center;').'" /></a>
						<a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html">'.$r_res['name'].'</a>
						<p>'.yjl_hd_date($r_res);
				if($r_res['xqid']>0){
					if(!isset($xqname[$r_res['xqid']])){
						$q_req=sprintf('select name from %s where xqid=%s and iskf=1 limit 1', $yjl_dbprefix.'xq', $r_res['xqid']);
						$req=mysql_query($q_req) or die('');
						$r_req=mysql_fetch_assoc($req);
						if(mysql_num_rows($req)>0)$xqname[$r_res['xqid']]=$r_req['name'];
						mysql_free_result($req);
					}
					$c.='<br /><a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html">'.$xqname[$r_res['xqid']].'</a>';
				}
				$c.='</p>
						'.$r_res['c_gz'].' 人感兴趣&nbsp;&nbsp;'.$r_res['c_cy'].' 人参加
					</li>';
			}while($r_res=mysql_fetch_assoc($res));
			$c.='</ul>';
		}
		mysql_free_result($res);
	}
	$q_res=sprintf('select a.hdid from %s as a, %s as b where a.hdid=b.hdid and b.uid=%s and a.etime>=%s', $yjl_dbprefix.'hd', $yjl_dbprefix.'hd_fuser', $cuid, time());
	$res=mysql_query($q_res) or die('');
	$c0=mysql_num_rows($res);
	mysql_free_result($res);
	$q_res=sprintf('select a.hdid from %s as a, %s as b where a.hdid=b.hdid and b.uid=%s and a.etime<%s', $yjl_dbprefix.'hd', $yjl_dbprefix.'hd_fuser', $cuid, time());
	$res=mysql_query($q_res) or die('');
	$c1=mysql_num_rows($res);
	mysql_free_result($res);
	if(($c0+$c1)>0){
		$c.='<div class="state">我关注的<span>( 可参加<span class="color_red">'.$c0.'</span>·已结束<span>'.$c1.'</span> )</span></div><ul class="list_lkactive">';
		$q_res=sprintf('select a.* from %s as a, %s as b where a.hdid=b.hdid and b.uid=%s order by a.isgf desc, a.lasttime desc limit 6', $yjl_dbprefix.'hd', $yjl_dbprefix.'hd_fuser', $cuid);
		$res=mysql_query($q_res) or die('');
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0){
			do{
				$c.='<li><a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html"><img src="'.($r_res['url']!=''?$r_res['url']:'images/blank.gif" style="background: url(images/pe_d.jpg) no-repeat center;').'" /></a>
						<a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html">'.$r_res['name'].'</a><br />
						'.yjl_hd_date($r_res).'
					</li>';
			}while($r_res=mysql_fetch_assoc($res));
		}
		mysql_free_result($res);
		$c.='</ul>';
	}
	$q_res=sprintf('select hdid from %s where uid=%s and etime>=%s', $yjl_dbprefix.'hd', $cuid, time());
	$res=mysql_query($q_res) or die('');
	$c0=mysql_num_rows($res);
	mysql_free_result($res);
	$q_res=sprintf('select hdid from %s where uid=%s and etime<%s', $yjl_dbprefix.'hd', $cuid, time());
	$res=mysql_query($q_res) or die('');
	$c1=mysql_num_rows($res);
	mysql_free_result($res);
	if(($c0+$c1)>0){
		$c.='<div class="state">我发起的<span>( 可参加<span class="color_red">'.$c0.'</span>·已结束<span>'.$c1.'</span> )</span></div><ul class="list_active">';
		$q_res=sprintf('select * from %s where uid=%s order by isgf desc, lasttime desc limit 2', $yjl_dbprefix.'hd', $cuid);
		$res=mysql_query($q_res) or die('');
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0){
			do{
				$c.='<li><a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html"><img src="'.($r_res['url']!=''?$r_res['url']:'images/blank.gif" style="background: url(images/pe_d.jpg) no-repeat center;').'" /></a>
						<a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html">'.$r_res['name'].'</a>
						<p>'.yjl_hd_date($r_res);
				if($r_res['xqid']>0){
					if(!isset($xqname[$r_res['xqid']])){
						$q_req=sprintf('select name from %s where xqid=%s and iskf=1 limit 1', $yjl_dbprefix.'xq', $r_res['xqid']);
						$req=mysql_query($q_req) or die('');
						$r_req=mysql_fetch_assoc($req);
						if(mysql_num_rows($req)>0)$xqname[$r_res['xqid']]=$r_req['name'];
						mysql_free_result($req);
					}
					$c.='<br /><a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html">'.$xqname[$r_res['xqid']].'</a>';
				}
				$c.='</p>'.$r_res['c_gz'].' 人感兴趣&nbsp;&nbsp;'.$r_res['c_cy'].' 人参加</li>';
			}while($r_res=mysql_fetch_assoc($res));
		}
		mysql_free_result($res);
		$c.='</ul>';
	}
	$q_res=sprintf('select *, c_cy+c_gz+c_zan as c_zs from %s order by c_zs desc, isgf desc, lasttime desc limit 2', $yjl_dbprefix.'hd');
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$c.='<div class="state">热门活动</div><ul class="list_active">';
		do{
			$c.='<li><a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html"><img src="'.($r_res['url']!=''?$r_res['url']:'images/blank.gif" style="background: url(images/pe_d.jpg) no-repeat center;').'" /></a>
						<a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html">'.$r_res['name'].'</a>
						<p>'.yjl_hd_date($r_res);
			if($r_res['xqid']>0){
				if(!isset($xqname[$r_res['xqid']])){
					$q_req=sprintf('select name from %s where xqid=%s and iskf=1 limit 1', $yjl_dbprefix.'xq', $r_res['xqid']);
					$req=mysql_query($q_req) or die('');
					$r_req=mysql_fetch_assoc($req);
					if(mysql_num_rows($req)>0)$xqname[$r_res['xqid']]=$r_req['name'];
					mysql_free_result($req);
				}
				$c.='<br /><a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html">'.$xqname[$r_res['xqid']].'</a>';
			}
			$c.='</p>'.$r_res['c_gz'].' 人感兴趣&nbsp;&nbsp;'.$r_res['c_cy'].' 人参加</li>';
		}while($r_res=mysql_fetch_assoc($res));
		$c.='</ul>';
	}
	mysql_free_result($res);
	$c.='<br class="clear" /><br /><br /></div>';
}
echo yjl_gehtml(yjl_userl($cuid, 'hd'), $c, '我的活动');
?>