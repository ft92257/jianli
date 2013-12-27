<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='qzno.php';
if($udb['uid']>0 && $udb['iswc']==1 && (($udb['qx']==5 && $udb['iszxjl']==1) || $udb['qx']==10 || $udb['isxg']>0)){
	$jdb=($udb['qx']==10 || $udb['isxg']>0)?'':' and (jluid=0 or jluid='.$udb['uid'].')';
	$q_res=sprintf('select qzid from %s where c_hd=0%s', $yjl_dbprefix.'qz', $jdb);
	$res=mysql_query($q_res) or die('');
	$c_res=mysql_num_rows($res);
	if($c_res>0)echo $c_res;
	mysql_free_result($res);
}
?>