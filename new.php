<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once($yjl_tpath.'setting/face.php');
$no_getxq=1;
require_once('function.php');

$u = $_GET['u'];
$c = '<iframe src="'.$u.'" id="iframepage" name="iframepage" frameBorder=0 scrolling=no width="100%" onLoad="iFrameHeight()" target="_top"></iframe>';

echo yjl_html($c, 'index', 'newnav') . '</body></html>';
?>
<script type="text/javascript" language="javascript"> 
function iFrameHeight() { 
	var ifm= document.getElementById("iframepage"); 
	var subWeb = document.frames ? document.frames["iframepage"].document : ifm.contentDocument; 
	if(ifm != null && subWeb != null) { 
	ifm.height = subWeb.body.scrollHeight; 
	} 
} 
</script>