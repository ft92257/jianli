<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once('function.php');
$f='user_decoration.php';

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
	}
	mysql_free_result($res);
}
$page_title=$uadb[$cuid]['nc'].' 监理项目';
$c='';
$js_c='';
if($user_id>0 && $user_id!=$cuid)yjl_vlog($cuid, 2);
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
$iscj=0;
if($user_id==$cuid){
	if($udb['qx']==5){
		$iscj=1;
	}elseif($udb['qx']==0){
		$q_res=sprintf('select hzid from %s where hzid=%s and isjs=0 limit 1', $yjl_dbprefix.'jl', $udb['uid']);
		
		$res=mysql_query($q_res) or die('');
		if(mysql_num_rows($res)==0)$iscj=1;
		mysql_free_result($res);
	}
}
$c_lc=count($a_lc);
if(isset($_GET['my']) && $_GET['my']==1){
	$c.='<div class="title">
				<h3>我关注的监理项目</h3>
				<div class="sub_title">
					<a href="?id='.$cuid.'">全部</a>|<span>我自己的</span>|<a href="?id='.$cuid.'&amp;fav=1">我关注的</a>
				</div>'.($iscj>0?'<div class="flt_rt"><a href="photo_create.php?xqid='.$xqid.'" class="btn bt_gray">创建新的项目</a></div>':'').'
			</div>
			<div class="act_cont clearfix">';
	$q_res=sprintf('select * from %s where uid=%s or hzid=%s order by lasttime desc', $yjl_dbprefix.'jl', $cuid, $cuid);
	$a_res=mysql_query($q_res) or die('');
	$tr_res=mysql_num_rows($a_res);
	if($tr_res>0){
		$p_size=4;
		$tp_res=ceil($tr_res/$p_size);
		if($page>$tp_res)$page=$tp_res;
		$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
		$res=mysql_query($q_l_res) or die('');
		$r_res=mysql_fetch_assoc($res);
		do{
			if($tr_res==1){
				echo '<script type="text/javascript">location.href=\'photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html\';</script>';
				exit();
			}
			$jd=0;
			$cjd=0;
			$ttp=0;
			foreach($a_lc as $k=>$v){
				$q_reu=sprintf('select * from %s where jlid=%s and lid=%s and is_del=0 order by datetime', $yjl_dbprefix.'jl_photo', $r_res['jlid'], $k);
				$reu=mysql_query($q_reu) or die('');
				$r_reu=mysql_fetch_assoc($reu);
				$c_reu=mysql_num_rows($reu);
				if($c_reu>0){
					$ttp++;
					$jd++;
					do{
						$a_jlp[$r_res['jlid']][$k][]='<a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-'.$r_reu['jpid'].'.html"><img src="images/blank.gif" width="'.$a_wh_jltpt[0].'" height="'.$a_wh_jltpt[1].'" style="background: url('.$r_reu['t_url'].') no-repeat center;" /></a>';
					}while($r_reu=mysql_fetch_assoc($reu));
				}
				mysql_free_result($reu);
				if($cjd==0 && $c_reu>0)$cjd=$k;
				$a_jlm[$r_res['jlid']][]='<a id="jl_'.$r_res['jlid'].'_'.$k.'"'.($c_reu>0?'':' class="hide"').'><span class="mn_ico ico1'.($k+2).'"></span><br />'.$v.'</a>';
			}
			if($cjd>1)$js_c.='
		$(\'#jl_'.$r_res['jlid'].'_'.$cjd.'\').mouseover();';
			$c.='<br class="clear" />
					<div class="pr_cont">
						<p><span><a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html">查看</a></span>'.$r_res['name'];
			if($r_res['hzqr']==0)$c.='（等待业主确认）';
			if($r_res['jlqr']==0 && $r_res['uid']>0)$c.='（等待监理确认）';
			$c.='</p>
						<div class="pr_tit tabs">'.join(' ', $a_jlm[$r_res['jlid']]).'</div>
						<div class="progress">完成度<strong>'.round($jd*100/$c_lc).'%</strong></div>';
				foreach($a_lc as $k=>$v){
					$c.='<div class="pr_pic tabcnt" style="display: none;"><a class="mn_ico prev"></a><div class="scrollable" id="scrollable"><div class="items spt">';
					if(isset($a_jlp[$r_res['jlid']][$k]))$c.=join(' ', $a_jlp[$r_res['jlid']][$k]);
					$c.='</div></div><a class="mn_ico next"></a></div>';
				}
			$c.='</div>';
		}while($r_res=mysql_fetch_assoc($res));
		mysql_free_result($res);
		if($tp_res>1)$c.=yjl_newpage($page, $tp_res);
	}
	mysql_free_result($a_res);
	$c.='<br class="clear" /><br /><br /></div>';
}elseif(isset($_GET['fav']) && $_GET['fav']==1){
	$c.='<div class="title">
				<h3>我关注的监理项目</h3>
				<div class="sub_title">
					<a href="?id='.$cuid.'">全部</a>|<a href="?id='.$cuid.'&amp;my=1">我自己的</a>|<span>我关注的</span>
				</div>'.($iscj>0?'<div class="flt_rt"><a href="photo_create.php?xqid='.$xqid.'" class="btn bt_gray">创建新的项目</a></div>':'').'
			</div>
			<div class="act_cont clearfix">';
	$q_res=sprintf('select a.*, b.name as b_name from %s as a, %s as b, %s as c where a.xqid=b.xqid and a.jlid=c.jlid and c.uid=%s order by a.lasttime desc', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', $yjl_dbprefix.'jl_gz', $cuid);
	$a_res=mysql_query($q_res) or die('');
	$tr_res=mysql_num_rows($a_res);
	if($tr_res>0){
		$p_size=20;
		$c.='<br/><ul class="list_visit">';
		$tp_res=ceil($tr_res/$p_size);
		if($page>$tp_res)$page=$tp_res;
		$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
		$res=mysql_query($q_l_res) or die('');
		$r_res=mysql_fetch_assoc($res);
		do{
			$pu='images/jl_d.jpg';
			$q_reu=sprintf('select * from %s where jlid=%s and is_del=0 order by datetime desc, jpid desc limit 1', $yjl_dbprefix.'jl_photo', $r_res['jlid']);
			$reu=mysql_query($q_reu) or die('');
			$r_reu=mysql_fetch_assoc($reu);
			if(mysql_num_rows($reu)>0)$pu=$r_reu['t_url'];
			mysql_free_result($reu);
			$c.='<li><a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html"><img src="images/blank.gif" width="'.$a_wh_jltpt[0].'" height="'.$a_wh_jltpt[1].'" style="background: url('.$pu.') no-repeat center;" /></a>
						<em class="percent'.$r_res['lid'].'"></em>
						<p><a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html" title="'.$r_res['name'].'">'.yjl_substrs($r_res['name'], 7).'</a></p>
						<p title="'.$r_res['b_name'].'">'.yjl_substrs($r_res['b_name'], 7).'</p>
					</li>';
		}while($r_res=mysql_fetch_assoc($res));
		mysql_free_result($res);
		$c.='</ul>';
		if($tp_res>1)$c.=yjl_newpage($page, $tp_res);
	}
	mysql_free_result($a_res);
	$c.='<br class="clear" /><br /><br /></div>';
}else{
	$c.='<div class="title">
				<h3>我的监理项目</h3>
				<div class="sub_title">
					<span>全部</span>|<a href="?id='.$cuid.'&amp;my=1">我自己的</a>|<a href="?id='.$cuid.'&amp;fav=1">我关注的</a>
				</div>'.($iscj>0?'<div class="flt_rt"><a href="photo_create.php?xqid='.$xqid.'" class="btn bt_gray">创建新的项目</a></div>':'').'
			</div>
			<div class="act_cont clearfix">';
	$q_res=sprintf('select * from %s where uid=%s or hzid=%s order by lasttime desc limit 1', $yjl_dbprefix.'jl', $cuid, $cuid);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$jd=0;
		$cjd=0;
		$ttp=0;
		foreach($a_lc as $k=>$v){
			$q_reu=sprintf('select * from %s where jlid=%s and lid=%s and is_del=0 order by datetime', $yjl_dbprefix.'jl_photo', $r_res['jlid'], $k);
			$reu=mysql_query($q_reu) or die('');
			$r_reu=mysql_fetch_assoc($reu);
			$c_reu=mysql_num_rows($reu);
			if($c_reu>0){
				$ttp++;
				$jd++;
				do{
					$a_jlp[$r_res['jlid']][$k][]='<a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-'.$r_reu['jpid'].'.html"><img src="images/blank.gif" width="'.$a_wh_jltpt[0].'" height="'.$a_wh_jltpt[1].'" style="background: url('.$r_reu['t_url'].') no-repeat center;" /></a>';
				}while($r_reu=mysql_fetch_assoc($reu));
			}else{
				$wc_css[$r_res['jlid']][]='hide0'.$k;
			}
			mysql_free_result($reu);
			if($cjd==0 && $c_reu>0)$cjd=$k;
			$a_jlm[$r_res['jlid']][]='<a id="jl_'.$r_res['jlid'].'_'.$k.'"'.($c_reu>0?'':' class="hide"').'><span class="mn_ico ico1'.($k+2).'"></span><br />'.$v.'</a>';
		}
		if($cjd>1)$js_c.='
	$(\'#jl_'.$r_res['jlid'].'_'.$cjd.'\').mouseover();';
		$c.='<br />
				<div class="pr_cont">
					<p><span><a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html">查看</a></span>'.$r_res['name'];
			if($r_res['hzqr']==0)$c.='（等待业主确认）';
			if($r_res['jlqr']==0 && $r_res['uid']>0)$c.='（等待监理确认）';
			$c.='</p>
					<div class="pr_tit'.($ttp>0?' tabs':'').'">'.join(' ', $a_jlm[$r_res['jlid']]).'</div>
					<div class="progress'.(isset($wc_css[$r_res['jlid']])?' '.join(' ', $wc_css[$r_res['jlid']]):'').'">完成度<strong>'.round($jd*100/$c_lc).'%</strong></div>';
		if($ttp>0){
			foreach($a_lc as $k=>$v){
				$c.='<div class="pr_pic tabcnt" style="display: none;"><a class="mn_ico prev"></a><div class="scrollable" id="scrollable"><div class="items spt">';
				if(isset($a_jlp[$r_res['jlid']][$k]))$c.=join(' ', $a_jlp[$r_res['jlid']][$k]);
				$c.='</div></div><a class="mn_ico next"></a></div>';
			}
		}
		$c.='</div>';
	}elseif($user_id==$cuid){
		$c.='<center style="margin: 40px;"><a href="photo_create.php?xqid='.$xqid.'" class="btn bt_orange">创建新的项目</a></center>';
	}
	mysql_free_result($res);
	$q_res=sprintf('select a.*, b.name as b_name from %s as a, %s as b, %s as c where a.xqid=b.xqid and a.jlid=c.jlid and c.uid=%s order by a.lasttime desc limit 6', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', $yjl_dbprefix.'jl_gz', $cuid);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$c.='<div class="state">我关注的</div><ul class="list_visit">';
		do{
			$pu='images/jl_d.jpg';
			$q_reu=sprintf('select * from %s where jlid=%s and is_del=0 order by datetime desc, jpid desc limit 1', $yjl_dbprefix.'jl_photo', $r_res['jlid']);
			$reu=mysql_query($q_reu) or die('');
			$r_reu=mysql_fetch_assoc($reu);
			if(mysql_num_rows($reu)>0)$pu=$r_reu['t_url'];
			mysql_free_result($reu);
			$c.='<li><a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html"><img src="images/blank.gif" width="'.$a_wh_jltpt[0].'" height="'.$a_wh_jltpt[1].'" style="background: url('.$pu.') no-repeat center;" /></a>
						<em class="percent'.$r_res['lid'].'"></em>
						<p><a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html" title="'.$r_res['name'].'">'.yjl_substrs($r_res['name'], 7).'</a></p>
						<p title="'.$r_res['b_name'].'">'.yjl_substrs($r_res['b_name'], 7).'</p>
					</li>';
		}while($r_res=mysql_fetch_assoc($res));
		$c.='</ul>';
	}
	mysql_free_result($res);
	$q_res=sprintf('select a.*, a.c_ck+a.c_gz as c_zs, b.name as b_name from %s as a, %s as b where a.xqid=b.xqid order by c_zs desc, a.lasttime desc limit 3', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', $cuid);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$c.='<div class="state">热门项目</div><ul class="list_visit">';
		do{
			$pu='images/jl_d.jpg';
			$q_reu=sprintf('select * from %s where jlid=%s and is_del=0 order by datetime desc, jpid desc limit 1', $yjl_dbprefix.'jl_photo', $r_res['jlid']);
			$reu=mysql_query($q_reu) or die('');
			$r_reu=mysql_fetch_assoc($reu);
			if(mysql_num_rows($reu)>0)$pu=$r_reu['t_url'];
			mysql_free_result($reu);
			$c.='<li><a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html"><img src="images/blank.gif" width="'.$a_wh_jltpt[0].'" height="'.$a_wh_jltpt[1].'" style="background: url('.$pu.') no-repeat center;" /></a>
						<em class="percent'.$r_res['lid'].'"></em>
						<p><a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html" title="'.$r_res['name'].'">'.yjl_substrs($r_res['name'], 7).'</a></p>
						<p title="'.$r_res['b_name'].'">'.yjl_substrs($r_res['b_name'], 7).'</p>
					</li>';
		}while($r_res=mysql_fetch_assoc($res));
		$c.='</ul>';
	}
	mysql_free_result($res);
	$c.='<br class="clear" /><br /><br /></div>';
}
echo yjl_gehtml(yjl_userl($cuid, 'jl'), $c, '我的项目');
?>