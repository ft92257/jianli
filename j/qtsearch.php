<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='qtsearch.php';
$q=(isset($_POST['q']) && trim($_POST['q'])!='')?htmlspecialchars(trim($_POST['q']),ENT_QUOTES):'';
$qdb=$q!=''?' and (name like '.yjl_SQLString($q, 'search').' or mobile like '.yjl_SQLString($q, 'search').')':'';
$t1id=(isset($_GET['t1id']) && $_GET['t1id']==1)?1:0;
if($t1id>0){
	$a_fl=$a_cp;
}else{
	$a_fl=$a_gz;
}
$t2id=(isset($_GET['t2id']) && isset($a_fl[$_GET['t2id']]))?$_GET['t2id']:1;
$xqid=(isset($_GET['xqid']) && intval($_GET['xqid'])>0)?intval($_GET['xqid']):0;
echo '<div class="clear_v">'.($q==''?'全部'.$a_fl[$t2id]:'搜索：'.$q).'</div>';
$dface=$t1id==0?$yjl_tpath.'images/no.gif':'images/jl_d.jpg';
$q_res=sprintf('select qtid, uid, name, face from %s where t1id=%s and t2id=%s%s', $yjl_dbprefix.'jl_qt', $t1id, $t2id, $qdb);
$res=mysql_query($q_res) or die('');
$r_res=mysql_fetch_assoc($res);
if(mysql_num_rows($res)>0){
	do{
		echo '<div class="photo_v" style="float: left;"><span id="qtvp_'.$t1id.'_'.$r_res['qtid'].'"><a href="review_o.php?id='.$r_res['qtid'].($xqid>0?'&amp;xqid='.$xqid:'').'"><img src="'.($r_res['face']!=''?$r_res['face']:$dface).'" width="'.$a_wh_utxs[0].'" height="'.$a_wh_utxs[1].'"/></a></span><br/><input type="radio" name="qtid" value="'.$r_res['qtid'].'" onclick="$(\'#qt_'.$t1id.'_id\').val(\''.$r_res['qtid'].'\');$(\'#qt_'.$t1id.'_mc\').html(\'<div class=photo_v>\'+$(\'#qtvp_'.$t1id.'_'.$r_res['qtid'].'\').html()+\'<br/>\'+$(\'#qtv_'.$t1id.'_'.$r_res['qtid'].'\').html()+\'</div>\');"'.((isset($_GET['c']) && $_GET['c']==$r_res['uid'])?' checked="checked"':'').'/><span id="qtv_'.$t1id.'_'.$r_res['qtid'].'">'.$r_res['name'].'</span></div>';
	}while($r_res=mysql_fetch_assoc($res));
	echo '<div class="clear_v"></div>';
}else{
	echo '没有符合条件的结果';
}
mysql_free_result($res);
?>