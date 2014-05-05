<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once($yjl_tpath.'setting/face.php');
$no_getxq=1;
require_once('function.php');


$c = file_get_contents('newnav/'.intval($_GET['p']).'.html');
$c = str_replace('{include support.html}', file_get_contents('newnav/html/support.html'), $c);
$c = str_replace('{include jianli_gz.html}', file_get_contents('newnav/html/jianli_gz.html'), $c);
$c = str_replace('{include jianli_bs.html}', file_get_contents('newnav/html/jianli_bs.html'), $c);

echo yjl_html($c, 'index', 'newnav') . '</body></html>';
?>