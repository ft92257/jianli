<?php
$s = 'http://' . $_SERVER['HTTP_HOST'];
if ($_SERVER['SERVER_PORT'] != '80') {
	$s .= ':' . $_SERVER['SERVER_PORT'];
}
$s .= dirname($_SERVER['SCRIPT_NAME']);

$url = dirname($s) . '/new.php';
$url .= "?u=". urlencode("http://" .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
?>
<script>
if (window == parent){
	window.location.href = "<?php echo $url;?>";
}
</script>
<?php
defined('ROOT_PATH') or define('ROOT_PATH', dirname(__FILE__));

define('IS_GZ', 1);

define('THINK_PATH', './includes/thinkphp/');
define('APP_NAME', 'index');
define('APP_DEBUG',true);
define('APP_PATH', './index/');
require( THINK_PATH."ThinkPHP.php");
