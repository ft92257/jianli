<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once($yjl_tpath.'setting/face.php');
$no_getxq=1;
require_once('function.php');
$f='estate.php';
$c='<br /><h2 class="h2">服务流程</h2>
<div class="vge_inf" style="padding: 20px;text-align: center;">
<table align="center" width="100%">
<tr>
<td>在线或电话咨询</td>
<td><img src="images/jt.gif" alt=""/></td>
<td>协助业主进行预算及合同确定</td>
<td><img src="images/jt.gif" alt=""/></td>
<td>签订合同</td>
<td><img src="images/jt.gif" alt=""/></td>
<td>施工整体过程监理服务</td>
</tr>
</table>
</div>';
echo yjl_html($c, 'supervisor');
?>