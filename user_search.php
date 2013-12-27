<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once('function.php');
$f='user_search.php';
if($udb['uid']>0){
	if(($udb['iswc']!=1 && ($udb['qx']==5 || $udb['qx']==6)) || $udb['isnc']==0){
		echo '<script type="text/javascript">location.href=\'./\';</script>';
		exit();
	}
}else{
	echo '<script type="text/javascript">location.href=\'login.php\';</script>';
	exit();
}
$cuid=$user_id;
$q=(isset($_GET['q']) && trim($_GET['q'])!='')?htmlspecialchars(trim($_GET['q']),ENT_QUOTES):'';
$page_title=$uadb[$cuid]['nc'].' 找好友';
$c='';
if($xqid>0){
	$c='<div class="title">
				<h3>好友</h3>
				<div class="sub_title">
					<span>'.$xqdb['name'].($q!=''?'，搜索：'.$q:'全部业主').'</span>
				</div>
				<div class="flt_rt">
				<form method="get" class="main_form" action="user_search.php">
				<input type="text" class="text" name="q" value="'.$q.'" />
				<input type="submit" value="" class="search_submit" />
				</form>
				</div>
				</div><div class="act_cont clearfix" style="overflow:visible;">';
	$qdb=$q!=''?' and (a.nc like '.yjl_SQLString($q, 'search').' or b.nickname like '.yjl_SQLString($q, 'search').')':'';
	$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
	$q_rep=sprintf('select a.nc, a.qx, b.uid, b.face from %s as a, %s as b where a.xqid=%s and a.uid=b.uid and a.qx=0%s order by b.uid desc', $yjl_dbprefix.'members', $dbprefix.'members', $xqid, $qdb);
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
			$c.='<li><div class="flt_lt">
							<div class="pic_text clearfix">
								<a href="user-'.$r_rep['uid'].'.html"><img src="'.yjl_face($r_rep['uid'], $r_rep['face']).'" /></a>
								<p class="memb"><a href="user-'.$r_rep['uid'].'.html">'.$r_rep['nc'].'</a></p>
								<p>'.$xqdb['name'].'</p>
							</div>
						</div>';
			$c.='<div class="flt_rt" id="gz_'.$r_rep['uid'].'">';
			if($user_id>0 && $user_id!=$r_rep['uid']){
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
	}else{
		if($q!='')$c.='<ul class="list_friend"><li><a href="'.$f.'">查看全部</a></li></ul>';
	}
	mysql_free_result($a_rep);
	$c.='<br class="clear" /><br /><br /></div>';
	echo yjl_gehtml(yjl_userl($cuid, 'hy'), $c, '好友');
}else{
	echo '<script type="text/javascript">location.href=\'./\';</script>';
	exit();
}
?>