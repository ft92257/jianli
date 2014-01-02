<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once($yjl_tpath.'setting/face.php');
$no_getxq=1;
require_once('function.php');

echo yjl_html_head('', 'index') . '</body></html>';
echo "<script>$('a').attr('target', '_top');$('#link_login').removeAttr('rel');</script>";
?>