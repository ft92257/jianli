<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='deltempimg.php';
if($udb['uid']>0){
	$sf='file/temp/'.$user_id.'_';
	if(isset($_GET['f']) && trim($_GET['f'])!='' && file_exists('../'.trim($_GET['f'])) && substr(trim($_GET['f']), 0, strlen($sf))==$sf){
		$u='../'.trim($_GET['f']);
		unlink($u);
		if(file_exists($u.'_t'))unlink($u.'_t');
		if(file_exists($u.'_o'))unlink($u.'_o');
	}
}
?>