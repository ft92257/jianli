<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once('function.php');
if(isset($_GET['rg']) && trim($_GET['rg'])!=''){
	$a_rg=explode('-', trim($_GET['rg']));
	if(isset($a_rg[0]))$_GET['id']=$a_rg[0];
	if(isset($a_rg[1]) && substr($a_rg[1], 0, 1)=='p')$_GET['p']=substr($a_rg[1], 1);
}
require_once('fun_user.php');
$f='user.php';
if($udb['uid']>0){
	if(($udb['iswc']!=1 && ($udb['qx']==5 || $udb['qx']==6)) || $udb['isnc']==0){
		echo '<script type="text/javascript">location.href=\'./\';</script>';
		exit();
	}
}else{
	echo '<script type="text/javascript">location.href=\'login.php\';</script>';
	exit();
}
$js_c='';
$cuid=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):$udb['uid'];
if($cuid!=$udb['uid']){
	$cudb=yjl_udb($cuid);
	if($cudb['uid']==0){
		echo '<script type="text/javascript">location.href=\'user-'.$udb['uid'].'.html\';</script>';
		exit();
	}
	$uadb[$cuid]=$cudb;
}
if($xqid==0 && $uadb[$cuid]['xqid']>0)$xqid=$uadb[$cuid]['xqid'];
if($xqid>0){
	$q_rep=sprintf('select * from %s where xqid=%s and iskf=1 limit 1', $yjl_dbprefix.'xq', $xqid);
	$rep=mysql_query($q_rep) or die('');
	$r_rep=mysql_fetch_assoc($rep);
	if(mysql_num_rows($rep)>0){
		$xqdb=$r_rep;
		$c_l1id=$xqdb['l1id'];
	}
	mysql_free_result($rep);
}
$a_uys=yjl_getys($uadb[$cuid], $udb);
$c='';
$page_title=$uadb[$cuid]['nc'].' 个人中心';
$c_lc=count($a_lc);
if($cuid!=$user_id){
	$c.='<div class="main_left">
			<div class="comm">
				<div class="vge_nav">
					<a href="user-'.$cuid.'.html" class="current">微博</a><a href="user_active.php?id='.$cuid.'">TA的活动</a><a href="user_decoration.php?id='.$cuid.'">TA的项目</a>
				</div>
				<div class="vge_inf">';
	if(in_array($uadb[$cuid]['ys_0'], $a_uys)){
		$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
		$q_res=sprintf('select * from %s where uid=%s and type<>%s order by dateline desc', $dbprefix.'topic', $cuid, yjl_SQLString('reply', 'text'));
		$a_res=mysql_query($q_res) or die('');
		$tr_res=mysql_num_rows($a_res);
		if($tr_res>0){
			$tp_res=ceil($tr_res/$p_size);
			if($page>$tp_res)$page=$tp_res;
			$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
			$res=mysql_query($q_l_res) or die('');
			$r_res=mysql_fetch_assoc($res);
			$c.='<ul class="list_comment">';
			do{
				$c.=yjl_newwb($r_res);
			}while($r_res=mysql_fetch_assoc($res));
			mysql_free_result($res);
			$c.='</ul>';
			if($tp_res>1)$c.=yjl_newhmpage('user-'.$cuid.'-p[p].html', $page, $tp_res);
		}
		mysql_free_result($a_res);
	}else{
		$c.='<ul class="list_comment"><li>无权查看</li></ul>';
	}
	$c.='<br /><br />
				</div>
			</div>
		</div>
		<div class="main_right margtop">'.yjl_user_info();
	if($uadb[$cuid]['qx']==0){
		$q_res=sprintf('select * from %s where uid=%s or hzid=%s order by lasttime desc limit 1', $yjl_dbprefix.'jl', $cuid, $cuid);
		$res=mysql_query($q_res) or die('');
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0){
			$clid=$r_res['lid']>0?$r_res['lid']:1;
			$jd=0;
			foreach($a_lc as $k=>$v){
				$q_reu=sprintf('select * from %s where jlid=%s and lid=%s and is_del=0 limit 1', $yjl_dbprefix.'jl_photo', $r_res['jlid'], $k);
				$reu=mysql_query($q_reu) or die('');
				if(mysql_num_rows($reu)>0)$jd++;
				mysql_free_result($reu);
			}
			$c.='<div class="box2 clearfix">
				<h3>Ta的装修进度</h3>
				<div class="prog clearfix">
					<div><span class="mn_ico ico1'.($clid+2).'"></span><br />进行到'.$a_lc[$clid].'阶段</div>
					<div class="progress"><strong>'.round($jd*100/$c_lc).'%</strong><br />项目完成度</div>
				</div>';
			if($jd>0){
				$q_reu=sprintf('select * from %s where jlid=%s and lid=%s and is_del=0 order by datetime desc, jpid desc', $yjl_dbprefix.'jl_photo', $r_res['jlid'], $r_res['lid']);
				$reu=mysql_query($q_reu) or die('');
				$r_reu=mysql_fetch_assoc($reu);
				if(mysql_num_rows($reu)>0){
					$c.='<div class="prog_pic clearfix">
					<a class="mn_ico prev"></a>
					<div class="scrollable" id="scrollable">
						<div class="items sptsml">';
					do{
						$c.=' <a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-'.$r_reu['jpid'].'.html"><img src="images/blank.gif" width="'.$a_wh_jltpt[0].'" height="'.$a_wh_jltpt[1].'" style="background: url('.$r_reu['t_url'].') no-repeat center;" /></a>';
					}while($r_reu=mysql_fetch_assoc($reu));
					$c.='</div>
					</div>
					<a class="mn_ico next"></a>
				</div>';
				}
				mysql_free_result($reu);
			}
			$c.='</div>';
		}
		mysql_free_result($res);
	}
	if(in_array($uadb[$cuid]['ys_1'], $a_uys))$c.=yjl_user_gz();
	$c.='</div>';
}else{
	$c.='<div class="main_left">';
	$q_res=sprintf('select * from %s where luid=%s and uid<>%s and isnew=1 order by datetime desc limit %s', $yjl_dbprefix.'log', $cuid, $cuid, $p_size);
	$res=mysql_query($q_res) or die(mysql_error());
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$c.='<div class="box3">
				<h3 class="tit"><a href="user_log.php">查看全部</a>最新动态</h3>
				<ul class="list_dynamic hover">';
		do{
			$uSQL=sprintf('update %s set isnew=0 where loid=%s', $yjl_dbprefix.'log', $r_res['loid']);
			$result=mysql_query($uSQL) or die('');
			$uname='用户';
			if($r_res['uid']>0){
				if(!isset($uadb[$r_res['uid']]))$uadb[$r_res['uid']]=yjl_udb($r_res['uid']);
				if(isset($uadb[$r_res['uid']]['nc']))$uname=$uadb[$r_res['uid']]['nc']!=''?$uadb[$r_res['uid']]['nc']:'用户';
			}
			$c.='<li><div class="flt_rt">'.yjl_wbdate($r_res['datetime']).'</div>'.str_replace(array('[uid]', '[luid]'), array('<a href="user-'.$r_res['uid'].'.html">'.$uname.'</a>', '我'), $r_res['content']).'</li>';
		}while($r_res=mysql_fetch_assoc($res));
		$c.='</ul></div>';
	}
	mysql_free_result($res);
	$u_lid=0;
	$q_res=sprintf('select * from %s where uid=%s or hzid=%s order by lasttime desc limit 1', $yjl_dbprefix.'jl', $cuid, $cuid);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$u_lid=$r_res['lid'];
		$jd=0;
		$cjd=0;
		$ttp=0;
		foreach($a_lc as $k=>$v){
			$q_reu=sprintf('select * from %s where jlid=%s and lid=%s and is_del=0 order by datetime desc, jpid desc', $yjl_dbprefix.'jl_photo', $r_res['jlid'], $k);
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
		$c.='<div class="pr_cont">
					<p><span><a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html">查看</a></span>'.$r_res['name'];
		if($r_res['hzqr']==0)$c.='（等待业主确认）';
		if($r_res['jlqr']==0 && $r_res['uid']>0)$c.='（等待监理确认）';
		$c.='</p>
					<div class="pr_tit'.($ttp>0?' tabs':'').'">'.join(' ', $a_jlm[$r_res['jlid']]).'</div>
					<div class="progress">完成度<strong>'.round($jd*100/$c_lc).'%</strong></div>';
		if($ttp>0){
			foreach($a_lc as $k=>$v){
				$c.='<div class="pr_pic tabcnt" style="display: none;"><a class="mn_ico prev"></a><div class="scrollable" id="scrollable"><div class="items spt">';
				if(isset($a_jlp[$r_res['jlid']][$k]))$c.=join(' ', $a_jlp[$r_res['jlid']][$k]);
				$c.='</div></div><a class="mn_ico next"></a></div>';
			}
		}
		$c.='</div>';
	}
	mysql_free_result($res);
	$isupimg=1;
	$js_a='upimg_a_0(response);';
	$js_ac='upimg_ac_0();';
	$js_c.=yjl_uploadjs($js_a, $js_ac);
	$c.='<div class="comm">
				<div class="vge_nav tabs">
					<a href="#" onclick="return false;">微博</a>
				</div>
				<div class="vge_inf tabcnt">
					<div class="broadcast" style="margin-top: 20px;">
						<table>
							<tr>
								<td><textarea id="content" style="padding: 5px;"></textarea></td>
							</tr>
							<tr>
								<td><input type="submit" onclick="postuwb();" value="广 播" id="submit_fb" class="submit sub_smbe" /></td>
							</tr>
						</table>
						<div class="spdin"><a href="#" onclick="if($(\'#imgu_div\').is(\':hidden\'))$(\'#imgu_div\').show();return false;"><span class="mn_ico ico22"></span>图片</a><a href="#" onclick="if($(\'#wb_v_div\').is(\':hidden\'))$(\'#wb_v_div\').show();return false;"><span class="mn_ico ico23"></span>视频</a></div><div class="wb_imgv">'.yjl_uploadv_3().'</div><div id="wb_v_div" style="display: none;padding-top: 10px;">请复制视频播放页网站地址即可 <input class="text" id="vurl"/></div>
					</div>';
	$c.='<div id="mblog_list">';
	$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
	$mbu[$cuid]=$cuid;
	$q_res=sprintf('select buddyid from %s where uid=%s', $dbprefix.'buddys', $cuid);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		do{
			$mbu[$r_res['buddyid']]=$r_res['buddyid'];
		}while($r_res=mysql_fetch_assoc($res));
	}
	mysql_free_result($res);
	$q_res=sprintf('select * from %s where uid in (%s) and type<>%s order by dateline desc', $dbprefix.'topic', join(', ', $mbu), yjl_SQLString('reply', 'text'));
	$a_res=mysql_query($q_res) or die('');
	$tr_res=mysql_num_rows($a_res);
	if($tr_res>0){
		$tp_res=ceil($tr_res/$p_size);
		if($page>$tp_res)$page=$tp_res;
		$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
		$res=mysql_query($q_l_res) or die('');
		$r_res=mysql_fetch_assoc($res);
		$c.='<ul class="list_comment">';
		do{
			if(!isset($uadb[$r_res['uid']]))$uadb[$r_res['uid']]=yjl_udb($r_res['uid']);
			$c.=yjl_newwb($r_res);
		}while($r_res=mysql_fetch_assoc($res));
		mysql_free_result($res);
		$c.='</ul>';
		if($tp_res>1)$c.=yjl_newhmpage('user-'.$cuid.'-p[p].html', $page, $tp_res, 'mblog_list');
	}
	mysql_free_result($a_res);
	$c.='</div><br /><br />
				</div>
			</div>
		</div>
		<div class="main_right">'.yjl_user_info();
	$q_res=sprintf('select a.*, b.name as b_name from %s as a, %s as b, %s as c where a.xqid=b.xqid and a.jlid=c.jlid and c.uid=%s order by a.lasttime desc limit 4', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', $yjl_dbprefix.'jl_gz', $cuid);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$c.='<div class="box2 clearfix">
				<h3><a href="user_decoration.php?id='.$cuid.'&fav=1">查看</a>我关注的项目</h3>
				<ul class="list_visit list_proj">';
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
		$c.='</ul></div>';
	}
	mysql_free_result($res);
	if($udb['qx']==0)$c.=yjl_user_jltj();
	$q_res=sprintf('select a.* from %s as a, %s as b where a.hdid=b.hdid and b.uid=%s order by a.isgf desc, a.lasttime desc limit 2', $yjl_dbprefix.'hd', $yjl_dbprefix.'hd_fuser', $cuid);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$c.='<div class="box2">
				<h3>我关注的活动</h3>
				<ul class="pic_text list_lkact">';
		do{
			$c.='<li><a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html"><img src="'.($r_res['url']!=''?$r_res['url']:'images/blank.gif" style="background: url(images/pe_d.jpg) no-repeat center;').'" /></a>
						<p><a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html">'.$r_res['name'].'</a><br />
						'.yjl_hd_date($r_res).' </p>
					</li>';
		}while($r_res=mysql_fetch_assoc($res));
		$c.='</ul></div>';
	}
	mysql_free_result($res);
	$c.=yjl_user_gz();
	$c.='</div>';
}
echo yjl_html($c, 'per_index');
?>