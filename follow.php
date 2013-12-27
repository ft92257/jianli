<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once('function.php');
$f='follow.php';
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
$a_uys=yjl_getys($uadb[$cuid], $udb);
$page_title=$uadb[$cuid]['nc'].' 关注';
$js_c='';
$c='<div class="title">
				<h3>好友</h3>
				<div class="sub_title">
					<span>我关注的</span><a href="fan.php?id='.$cuid.'">关注我的</a>
				</div>';
if($xqid>0)$c.='<div class="flt_rt">
				<form method="get" class="main_form" action="user_search.php">
				<input type="text" class="text" name="q" />
				<input type="submit" value="" class="search_submit" />
				</form>
				</div>';
$c.='</div><div class="act_cont clearfix" style="overflow:visible;">';
if($user_id>0 && $user_id==$cuid){
	$q_res=sprintf('select * from %s where uid=%s', $yjl_dbprefix.'ffz', $user_id);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		do{
			$q_req=sprintf('select a.uid from %s as a, %s as b where a.fzid=%s and a.uid=b.buddyid and b.uid=%s', $yjl_dbprefix.'ffz_f', $dbprefix.'buddys', $r_res['fzid'], $user_id);
			$req=mysql_query($q_req) or die('');
			$r_res['c_user']=mysql_num_rows($req);
			mysql_free_result($req);
			$a_fz[$r_res['fzid']]=$r_res;
		}while($r_res=mysql_fetch_assoc($res));
	}
	mysql_free_result($res);
	$fzid=(isset($_GET['fz']) && isset($a_fz[$_GET['fz']]))?$_GET['fz']:0;
	$c.='<div class="state"><span><a href="'.$f.'">全部（'.$udb['follow_count'].'）</a>';
	if(isset($a_fz)){
		foreach($a_fz as $v)$c.='&nbsp;&nbsp;&nbsp;<a href="?fz='.$v['fzid'].'">'.$v['name'].'（'.$v['c_user'].'）</a>';
	}
	$c.='</span></div><a href="?fzgl=1" class="frd_more"'.((isset($_GET['fzgl']) && $_GET['fzgl']==1)?' rel="#overlay_newct" id="open_ldiv"':'').'>分组管理</a>';
}
if($user_id>0 && $user_id!=$cuid)yjl_vlog($cuid, 2);
if(in_array($uadb[$cuid]['ys_1'], $a_uys)){
	$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
	if($user_id>0 && $user_id==$cuid && $fzid>0){
		$q_rep=sprintf('select b.uid, b.face, c.xqid, c.nc, c.qx, c.gzfl from %s as a, %s as b, %s as c, %s as d where a.uid=%s and a.buddyid=b.uid and b.uid=c.uid and a.buddyid=d.uid and d.fzid=%s order by a.dateline desc', $dbprefix.'buddys', $dbprefix.'members', $yjl_dbprefix.'members', $yjl_dbprefix.'ffz_f', $cuid, $fzid);
	}else{
		$q_rep=sprintf('select b.uid, b.face, c.xqid, c.nc, c.qx, c.gzfl from %s as a, %s as b, %s as c where a.uid=%s and a.buddyid=b.uid and b.uid=c.uid order by a.dateline desc', $dbprefix.'buddys', $dbprefix.'members', $yjl_dbprefix.'members', $cuid);
	}
	$a_rep=mysql_query($q_rep) or die('');
	$tr_rep=mysql_num_rows($a_rep);
	if($tr_rep>0){
		$c.='<ul class="list_friend hover">';
		$tp_rep=ceil($tr_rep/$p_size);
		if($page>$tp_rep)$page=$tp_rep;
		$q_l_rep=sprintf('%s limit %d, %d', $q_rep, ($page-1)*$p_size, $p_size);
		$rep=mysql_query($q_l_rep) or die('');
		$r_rep=mysql_fetch_assoc($rep);
		do{
			if($r_rep['xqid']>0 && !isset($xqname[$r_rep['xqid']])){
				if(isset($xqdb) && $xqid==$r_rep['xqid']){
					$xqname[$r_rep['xqid']]=$xqdb['name'];
				}else{
					$q_res=sprintf('select name from %s where xqid=%s and iskf=1 limit 1', $yjl_dbprefix.'xq', $r_rep['xqid']);
					$res=mysql_query($q_res) or die('');
					$r_res=mysql_fetch_assoc($res);
					if(mysql_num_rows($res)>0)$xqname[$r_rep['xqid']]=$r_res['name'];
					mysql_free_result($res);
				}
			}
			$xqn=$r_rep['xqid']>0?$xqname[$r_rep['xqid']]:'';
			$c.='<li><div class="flt_lt">
							<div class="pic_text clearfix">
								<a href="user-'.$r_rep['uid'].'.html"><img src="'.yjl_face($r_rep['uid'], $r_rep['face']).'" /></a>
								<p class="memb"><a href="user-'.$r_rep['uid'].'.html">'.$r_rep['nc'].'</a></p>';
			if(isset($a_tsgz[$r_rep['qx']]))$c.='<p class="memb">'.$a_tsgz[$r_rep['qx']][$r_rep['gzfl']].'</p>';
			$c.='<p>'.$xqn.'</p>
							</div>
						</div>';
			$c.='<div class="flt_rt" id="gz_'.$r_rep['uid'].'">';
			if($user_id>0 && $user_id==$cuid){
				if(isset($a_fz)){
					$ufzid=0;
					if($fzid>0){
						$ufzid=$fzid;
					}else{
						$q_res=sprintf('select fzid from %s where zuid=%s and uid=%s limit 1', $yjl_dbprefix.'ffz_f', $user_id, $r_rep['uid']);
						$res=mysql_query($q_res) or die('');
						$r_res=mysql_fetch_assoc($res);
						if(mysql_num_rows($res)>0)$ufzid=$r_res['fzid'];
						mysql_free_result($res);
					}
					$c.='<div class="show"><a href="#" id="fz_'.$r_rep['uid'].'">'.($ufzid>0?$a_fz[$ufzid]['name']:'未分组').'</a><div class="hide tooltip">';
					$c.='<a href="#" onclick="$(\'#fz_'.$r_rep['uid'].'\').load(\'j/ffz.php?zuid='.$r_rep['uid'].'&dfzid=1\');$(\'#wfz_'.$r_rep['uid'].'\').hide();return false;" id="wfz_'.$r_rep['uid'].'"'.($ufzid>0?'':' style="display: none;"').'>取消分组</a>';
					foreach($a_fz as $v)$c.='<a href="#" onclick="$(\'#fz_'.$r_rep['uid'].'\').load(\'j/ffz.php?zuid='.$r_rep['uid'].'&fzid='.$v['fzid'].'\');$(\'#wfz_'.$r_rep['uid'].'\').show();return false;">'.$v['name'].'</a>';
					$c.='</div></div>';
				}
			}elseif($user_id!=$r_rep['uid']){
				$q_req=sprintf('select uid from %s where uid=%s and buddyid=%s', $dbprefix.'buddys', $user_id, $r_rep['uid']);
				$req=mysql_query($q_req) or die('');
				$isgz=mysql_num_rows($req)>0?1:0;
				mysql_free_result($req);
				$c.='<a href="#" onclick="$(\'#gz_'.$r_rep['uid'].'\').load(\'j/gz.php?id='.$r_rep['uid'].'\');return false;"'.($isgz>0?'>取消关注':' class="btn bt_nomblue" style="color: #fff;">关 注').'</a>';
			}
			$c.='</div>';
			$c.='</li>';
		}while($r_rep=mysql_fetch_assoc($rep));
		mysql_free_result($rep);
		$c.='</ul>';
		if($tp_rep>1)$c.=yjl_newpage($page, $tp_rep);
	}
	mysql_free_result($a_rep);
}else{
	$c.='<ul class="list_friend hover"><li>无权查看</li></ul>';
}
$c.='<br class="clear" /><br /><br /></div>';
if($user_id>0 && $user_id==$cuid && isset($_GET['fzgl']) && $_GET['fzgl']==1){
	$js_c.='
	$(\'#open_ldiv\').click();
	$(\'.close\').click(function(){
		location.href=\''.$f.'\';
	});
	$(\'#fz_add_l\').click(function(){
		var c=$.trim($(\'#q\').val());
		if(c!=\'\'){
			$(\'#q\').val(\'\');
			$(\'#fz_div\').load(\'j/ffz.php\', {n:c});
		}
		return false;
	});
	$(\'.fz_del\').click(function(){
		var id=$(this).attr(\'jq_id\');
		$(\'#fz_div\').load(\'j/ffz.php?del=\'+id);
		return false;
	});';
	$c.='<div class="overlay" id="overlay_newct"><h3>分组管理</h3><div class="overlay_cont"><input type="text" id="q" class="text"/>&nbsp;<a href="#" id="fz_add_l">添加</a><div id="fz_div" style="padding-top: 20px;">';
	if(isset($a_fz)){
		foreach($a_fz as $v)$c.=$v['name'].'[<a href="#" style="color: #f00;" class="fz_del" jq_id="'.$v['fzid'].'">删除</a>]&nbsp;&nbsp;';
	}
	$c.='</div></div></div>';
}
echo yjl_gehtml(yjl_userl($cuid, 'hy'), $c, '好友');
?>