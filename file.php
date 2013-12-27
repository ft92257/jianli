<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once('function.php');
$f='file.php';
$udb=yjl_chkulog();
$adb=$udb['uid']>0?'(uid='.$udb['uid'].' or isgk=1)':'isgk=1';
$id=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
$q_res=sprintf('select name, url, etid from %s where fiid=%s and %s limit 1', $yjl_dbprefix.'jl_file', $id, $adb);
$res=mysql_query($q_res) or die(mysql_error());
$r_res=mysql_fetch_assoc($res);
if(mysql_num_rows($res)>0){
	header('Content-Disposition: '.$a_filet[$r_res['etid']].'; filename='.$r_res['name']);
	header('Content-Type: '.$a_filet[$r_res['etid']].';');
	header('Location:'.$r_res['url']);
}else{
	header('Location:./');
}
mysql_free_result($res);
?>