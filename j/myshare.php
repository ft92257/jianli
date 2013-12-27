<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../'.$yjl_tpath.'setting/face.php');
require_once('../function.php');
$f='myfav.php';
if($udb['uid']>0){
	$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
	$p_size=(isset($_GET['s']) && intval($_GET['s'])>0)?intval($_GET['s']):$pbl_s;
	$q_rep=sprintf('select a.*, b.xqid, b.hzid from %s as a, %s as b, %s as c, %s as d where a.is_del=0 and a.jlid=b.jlid and a.jpid=c.jpid and c.uid=d.buddyid and c.tlid=2 and d.uid=%s order by c.datetime desc limit %s, %s', $yjl_dbprefix.'jl_photo', $yjl_dbprefix.'jl', $yjl_dbprefix.'jl_plike', $dbprefix.'buddys', $udb['uid'], ($page-1)*$p_size, $p_size);
	$rep=mysql_query($q_rep) or die('');
	$r_rep=mysql_fetch_assoc($rep);
	if(mysql_num_rows($rep)>0){
		do{
			if(!isset($a_l[$r_rep['jpid']])){
				$uadb[$r_rep['hzid']]['nc']=$r_rep['nc'];
				echo yjl_pbl($r_rep);
				$a_l[$r_rep['jpid']]=1;
			}
		}while($r_rep=mysql_fetch_assoc($rep));
	}
	mysql_free_result($rep);
}
?>