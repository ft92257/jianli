<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once($yjl_tpath.'setting/face.php');
$no_getxq=1;
require_once('function.php');
$f='estate.php';
$c='<br /><h2 class="h2">支付宝支付</h2>
<div class="vge_inf" style="padding: 20px;">公司名称：上海易至居工程监理有限公司<br/><br/>支付宝账号：5678107@qq.com</div>';
echo yjl_html($c, 'supervisor');
?>