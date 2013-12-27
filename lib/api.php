<?php
session_start();
$f='log/'.time().'.htm';
$fc='';
if(isset($_GET)){
	$fc.='<h1>GET</h1><br/>';
	foreach($_GET as $k=>$v)$fc.='<br/>'.$k.': '.$v;
	$fc.='<hr>';
}
if(isset($_POST)){
	$fc.='<h1>POST</h1><br/>';
	foreach($_POST as $k=>$v){
		$fc.='<br/>'.$k.': '.$v;
		if(is_array($v)){
			foreach($v as $vk=>$vv){
				$fc.='<br/>- '.$vk.': '.$vv;
			}
		}
	}
	$fc.='<hr>';
}
if(isset($_HTTP_RAW_POST_DATA)){
	$fc.='<h1>HTTP_RAW_POST_DATA</h1><br/>';
	if(is_array($_HTTP_RAW_POST_DATA)){
		foreach($_HTTP_RAW_POST_DATA as $k=>$v)$fc.='<br/>'.$k.': '.$v;
	}else{
		$fc.=htmlspecialchars($$_HTTP_RAW_POST_DATA);
	}
	$fc.='<hr>';
}
if(isset($_SERVER)){
	$fc.='<h1>SERVER</h1><br/>';
	foreach($_SERVER as $k=>$v)$fc.='<br/>'.$k.': '.$v;
	$fc.='<hr>';
}
if(isset($_COOKIE)){
	$fc.='<h1>COOKIE</h1><br/>';
	foreach($_COOKIE as $k=>$v)$fc.='<br/>'.$k.': '.$v;
	$fc.='<hr>';
}

function writeText($f,$c){
	if(is_writable($f) || !file_exists($f)){
		if(!$h=fopen($f,'w'))return false;
		if(!fwrite($h,$c))return false;
		fclose($h);
	}else{
		return false;
	}
	return true;
}
writeText($f,$fc);
//$a['abcd']=time();
//echo json_encode($a);
?>