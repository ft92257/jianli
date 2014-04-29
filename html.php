<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once($yjl_tpath.'setting/face.php');
$no_getxq=1;
require_once('function.php');


$c = file_get_contents('newnav/html/'.$_GET['p'].'.html');

echo yjl_html($c, 'index', 'newnav') . '</body></html>';
?>