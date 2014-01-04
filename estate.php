<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once($yjl_tpath.'setting/face.php');
$no_getxq=1;
if(isset($_GET['rg']) && trim($_GET['rg'])!=''){
	$a_rg=explode('-', trim($_GET['rg']));
	if(isset($a_rg[0]))$_GET['t']=$a_rg[0];
	if(isset($a_rg[1]))$_GET['s']=$a_rg[1];
	if(isset($a_rg[2]))$_GET['l2id']=$a_rg[2];
	if(isset($a_rg[3]))$_GET['py']=$a_rg[3];
}
require_once('function.php');
$f='estate.php';
$js_c='';
$c='';
$c_l1id=(isset($_GET['s']) && intval($_GET['s'])>0)?intval($_GET['s']):$d_l1id;
$a_t=array('square-[xqid].html', 'photo-[xqid].html', 'active-[xqid].html', 'photo_create.php?xqid=[xqid]', 'active_create.php?xqid=[xqid]');
$tid=(isset($_GET['t']) && isset($a_t[$_GET['t']]))?$_GET['t']:0;
if ($c_l1id == 9) {
	$q_res=sprintf('select name from %s where level=1 and upid=0 and id=%s limit 1', $dbprefix.'common_district', $c_l1id);
} else {
	$q_res=sprintf('select name from %s where id=%s limit 1', $dbprefix.'common_district', $c_l1id);
}
$res=mysql_query($q_res) or die('');
$r_res=mysql_fetch_assoc($res);
if(mysql_num_rows($res)>0){
	$pyid=(isset($_GET['py']) && isset($a_py[$_GET['py']]))?$_GET['py']:0;
	$q=(isset($_GET['q']) && trim($_GET['q'])!='')?htmlspecialchars(trim($_GET['q']),ENT_QUOTES):'';
	$js_c.='
	$(\'#xq_search_q\').focus(function(){
		if($(this).attr(\'jq_ise\')==\'1\')$(this).val(\'\');
	}).blur(function(){
		if($(this).val()!=\'\'){
			$(this).attr(\'jq_ise\', \'0\');
		}else{
			$(this).attr(\'jq_ise\', \'1\');
			$(this).val(\'输入关键字\');
		}
	});
	$(\'#xq_search_bt\').click(function(){
		if($(\'#xq_search_q\').attr(\'jq_ise\')==\'1\')return false;
	});';
	$c.='<br />
		<h2 class="h2">'.$r_res['name'].' 选择小区</h2>
		<div class="vge_inf">
			<div class="area clearfix"><form class="main_form search" action="'.$f.'" method="get">
				<input type="text" id="xq_search_q" class="text" name="q" value="'.($q!=''?$q:'输入关键字').'" jq_ise="'.($q!=''?'0':'1').'" />'.($tid>0?'<input name="t" value="'.$tid.'" type="hidden"/>':'').'<input type="submit" id="xq_search_bt" class="submit" value="搜 索" />
			</form>';
	$l2id=0;
	$a_l2[0]='全部';
	if ($c_l1id == 9) {
		$q_rep=sprintf('select id, name from %s where level=2 and upid=%s order by list', $dbprefix.'common_district', $c_l1id);
	}  else {
		$q_rep=sprintf('select id, name from %s where upid=%s order by list', $dbprefix.'common_district', $c_l1id);
	}
	$rep=mysql_query($q_rep) or die('');
	$r_rep=mysql_fetch_assoc($rep);
	if(mysql_num_rows($rep)>0){
		do{
			if(isset($_GET['l2id']) && $_GET['l2id']==$r_rep['id'])$l2id=$r_rep['id'];
			$a_l2[$r_rep['id']]=$r_rep['name'];
		}while($r_rep=mysql_fetch_assoc($rep));
	}
	mysql_free_result($rep);
	$c.='<h3 class="pytit">按区域筛选<span>'.($l2id>0?''.$a_l2[$l2id]:'全部').'</span>'.($q!=''?'&nbsp;&nbsp;&nbsp;搜索<span>'.$q.'</span>':'').'</h3><ul>';
	foreach($a_l2 as $k=>$v)$c.='<li><a href="estate-'.$tid.'-'.$c_l1id.'-'.$k.'-'.$pyid.'.html"'.($k==$l2id?' class="current"':'').'>'.$v.'</a></li>';
	$c.='</ul><h3 class="pytit">拼音索引筛选<span>'.($pyid>0?$a_py[$pyid]:'全部').'</span></h3><ul class="pinyin"><li><a href="estate-'.$tid.'-'.$c_l1id.'-'.$l2id.'-0.html"'.($pyid>0?'':' class="current"').'>全部</a></li>';
	foreach($a_py as $k=>$v)$c.='<li><a href="estate-'.$tid.'-'.$c_l1id.'-'.$l2id.'-'.$k.'.html"'.($pyid==$k?' class="current"':'').'>'.$v.'</a></li>';
	$c.='</ul></div>';
	if ($c_l1id == 9) {
		$ldb=$l2id>0?' and l2id='.$l2id:'';
		$q_rep=sprintf('select xqid, name, c_user, c_user+c_wb+c_jl+c_xz+c_hd as c_hot from %s where c_user+c_wb+c_jl+c_xz+c_hd>0 and iskf=1%s order by c_hot desc limit 12', $yjl_dbprefix.'xq', $ldb);
	} else {
		$ldb=$l2id>0?' and l3id='.$l2id:'';
		$q_rep=sprintf('select xqid, name, c_user, c_user+c_wb+c_jl+c_xz+c_hd as c_hot from %s where l2id = 175 and c_user+c_wb+c_jl+c_xz+c_hd>0 and iskf=1%s order by c_hot desc limit 12', $yjl_dbprefix.'xq', $ldb);
	}

	$rep=mysql_query($q_rep) or die('');
	$r_rep=mysql_fetch_assoc($rep);
	if(mysql_num_rows($rep)>0){
		$c.='<div class="hotvge clearfix"><h3>热门小区</h3><ul>';
		do{
			$c.='<li><a href="'.str_replace('[xqid]', $r_rep['xqid'], $a_t[$tid]).'">'.$r_rep['name'].'('.$r_rep['c_user'].')</a></li>';
		}while($r_rep=mysql_fetch_assoc($rep));
		$c.='</ul></div>';
	}
	mysql_free_result($rep);
	$c.='<div class="list_village clearfix"><ul>';
	$pydb=$pyid>0?' and pyid='.$pyid:'';
	$qdb=$q!=''?' and (name like '.yjl_SQLString($q, 'search').' or address like '.yjl_SQLString($q, 'search').')':'';
	
	if ($c_l1id == 9) {
		$q_rep=sprintf('select xqid, name, c_user from %s where l1id=%s%s%s%s and iskf=1 order by name', $yjl_dbprefix.'xq', $c_l1id, $ldb, $pydb, $qdb);
	} else {
		$q_rep=sprintf('select xqid, name, c_user from %s where l2id=%s%s%s%s and iskf=1 order by name', $yjl_dbprefix.'xq', $c_l1id, $ldb, $pydb, $qdb);
	}
	$rep=mysql_query($q_rep) or die(mysql_error());
	$r_rep=mysql_fetch_assoc($rep);
	$c_rep=mysql_num_rows($rep);
	if($c_rep>0){
		$m=4;
		$p=ceil($c_rep/$m);
		$i=0;
		do{
			if($i>0 && ($i%$p)==0)$c.='<ul>';
			$c.='<li><a href="'.str_replace('[xqid]', $r_rep['xqid'], $a_t[$tid]).'">'.$r_rep['name'].'('.$r_rep['c_user'].')</a></li>';
			$i++;
			if(($i%$p)==0)$c.='</ul>';
		}while($r_rep=mysql_fetch_assoc($rep));
		if(($i%$p)>0)$c.='</ul>';
	}else{
		$c.='<li>没有小区</li></ul>';
	}
	mysql_free_result($rep);
	$c.='</div><br /><br /></div>';
}else{
	echo '<script type="text/javascript">location.href=\'estate-all.html\';</script>';
	exit();
}
mysql_free_result($res);
echo yjl_html($c, 'supervisor');
?>