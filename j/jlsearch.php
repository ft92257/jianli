<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='jlsearch.php';
$q=(isset($_POST['q']) && trim($_POST['q'])!='')?htmlspecialchars(trim($_POST['q']),ENT_QUOTES):'';
$qdb=$q!=''?' and (a.nc like '.yjl_SQLString($q, 'search').' or b.nickname like '.yjl_SQLString($q, 'search').')':'';
$tid=(isset($_GET['t']) && $_GET['t']==1)?6:5;
echo '<div style="clear: both;margin-bottom: 10px;">'.($q==''?'全部'.$a_tsgz[$tid][0]:'搜索：'.$q).'</div>';
$q_res=sprintf('select a.nc, b.uid, b.face from %s as a, %s as b where a.uid=b.uid and a.qx=%s and a.iswc=1%s', $yjl_dbprefix.'members', $dbprefix.'members', $tid, $qdb);
$res=mysql_query($q_res) or die('');
$r_res=mysql_fetch_assoc($res);
if(mysql_num_rows($res)>0){
	do{
		echo '<div style="float: left;text-align: center;width: 80px;margin: 3px;"><img src="'.yjl_face($r_res['uid'], $r_res['face']).'" width="'.$a_wh_utxs[0].'" height="'.$a_wh_utxs[1].'" title="'.$r_res['nc'].'"/><div style="margin-top: 10px;"><input type="radio" class="radio" name="jluid" value="'.$r_res['uid'].'"/>'.yjl_substrs($r_res['nc'], 5).'</div></div>';
	}while($r_res=mysql_fetch_assoc($res));
}else{
	echo '没有符合条件的结果';
}
mysql_free_result($res);
?>