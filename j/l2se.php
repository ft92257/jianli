<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='l2se.php';
$tid=(isset($_GET['t']) && $_GET['t']==1)?1:0;
if(isset($_GET['l1id']) && intval($_GET['l1id'])>0){
	$l1id=intval($_GET['l1id']);
	$q_rep=sprintf('select id, name from %s where level=2 and upid=%s', $dbprefix.'common_district', $l1id);
	$rep=mysql_query($q_rep) or die('');
	$r_rep=mysql_fetch_assoc($rep);
	if(mysql_num_rows($rep)>0){
		echo '<select id="'.($tid>0?'l2id" onchange="search_xq_h(1);':'o_l2id" onchange="och2id();" name="l2id').'"><option value="0">-选择地区-</option>';
		do{
			echo '<option value="'.$r_rep['id'].'">'.$r_rep['name'].'</option>';
		}while($r_rep=mysql_fetch_assoc($rep));
		echo '</select> <span id="ndq3"></span>';
	}
	mysql_free_result($rep);
}elseif(isset($_GET['l2id']) && intval($_GET['l2id'])>0){
	$l2id=intval($_GET['l2id']);
	$q_rep=sprintf('select id, name from %s where level=3 and upid=%s', $dbprefix.'common_district', $l2id);
	$rep=mysql_query($q_rep) or die('');
	$r_rep=mysql_fetch_assoc($rep);
	if(mysql_num_rows($rep)>0){
		echo '<select id="o_l3id" onchange="och3id();" name="l3id"><option value="0">-不限地区-</option>';
		do{
			echo '<option value="'.$r_rep['id'].'">'.$r_rep['name'].'</option>';
		}while($r_rep=mysql_fetch_assoc($rep));
		echo '</select> <span id="ndq4"></span>';
	}
	mysql_free_result($rep);
}elseif(isset($_GET['l3id']) && intval($_GET['l3id'])>0){
	$l3id=intval($_GET['l3id']);
	$q_rep=sprintf('select id, name from %s where level=4 and upid=%s', $dbprefix.'common_district', $l3id);
	$rep=mysql_query($q_rep) or die('');
	$r_rep=mysql_fetch_assoc($rep);
	if(mysql_num_rows($rep)>0){
		echo '<select name="l4id"><option value="0">-不限地区-</option>';
		do{
			echo '<option value="'.$r_rep['id'].'">'.$r_rep['name'].'</option>';
		}while($r_rep=mysql_fetch_assoc($rep));
		echo '</select>';
	}
	mysql_free_result($rep);
}
?>