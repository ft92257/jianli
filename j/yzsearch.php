<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='yzsearch.php';
$xqid=(isset($_GET['xqid']) && intval($_GET['xqid'])>0)?intval($_GET['xqid']):1;
$q=(isset($_POST['q']) && trim($_POST['q'])!='')?htmlspecialchars(trim($_POST['q']),ENT_QUOTES):'';
$qdb=$q!=''?' and (a.nc like '.yjl_SQLString($q, 'search').' or b.nickname like '.yjl_SQLString($q, 'search').')':'';
echo '<div style="clear: both;margin-bottom: 10px;">'.($q==''?'本小区全部业主':'搜索：'.$q).'</div>';
$q_res=sprintf('select a.nc, b.uid, b.face from %s as a, %s as b where a.xqid=%s and a.qx=0%s and a.uid=b.uid and a.isnc>0', $yjl_dbprefix.'members', $dbprefix.'members', $xqid, $qdb);
$res=mysql_query($q_res) or die('');
$r_res=mysql_fetch_assoc($res);
if(mysql_num_rows($res)>0){
	do{
		$q_rep=sprintf('select hzid from %s where hzid=%s and isjs=0 limit 1', $yjl_dbprefix.'jl', $r_res['uid']);
		$rep=mysql_query($q_rep) or die('');
		$isjl=mysql_num_rows($rep)>0?1:0;
		mysql_free_result($rep);
		echo '<div style="float: left;text-align: center;width: 80px;margin: 4px;"><img src="'.yjl_face($r_res['uid'], $r_res['face']).'" width="'.$a_wh_utxs[0].'" height="'.$a_wh_utxs[1].'" title="'.$r_res['nc'].'"/><div style="margin-top: 10px;"><input type="radio" class="radio" name="hzuid" value="'.$r_res['uid'].'" jq_jl="'.$isjl.'"/>'.yjl_substrs($r_res['nc'], 5).'</div></div>';
	}while($r_res=mysql_fetch_assoc($res));
}else{
	echo '没有符合条件的结果';
}
mysql_free_result($res);
?>