<?php
//跳转到新版
//header("Location:http://" . $_SERVER['HTTP_HOST'] . "/jianliapp/index.php?s=/User/logout");
//die;

require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once('function.php');
if(isset($_COOKIE[$config['cookie_prefix'].'sid']) && $_COOKIE[$config['cookie_prefix'].'sid']!=''){
	$dSQL=sprintf('delete from %s where sid=%s', $dbprefix.'sessions', yjl_SQLString($_COOKIE[$config['cookie_prefix'].'sid'], 'text'));
	$result=mysql_query($dSQL) or die('');
}
setcookie($config['cookie_prefix'].'sid', '', time()-86400);
setcookie($config['cookie_prefix'].'auth', '', time()-86400);
setcookie($config['cookie_prefix'].'ajhAuth', '', time()-86400);

//同步退出新版
syn_logout();

header('Location:'.(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'./'));
?>