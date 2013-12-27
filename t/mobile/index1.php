<?php
/**
 * 移动客户端入口
 * 
 * @author 		~ZZ~<505171269@qq.com>
 * @version		v1.0 $Date:2011-09-30
 */




define('ROOT_PATH',substr(dirname(__FILE__),0,-6) . '/');

define('TEMPLATE_ROOT_PATH', ROOT_PATH . 'mobile/');

define('SYS_ROOT_PATH', ROOT_PATH . 'mobile/');

define('RELATIVE_ROOT_PATH','../');

define('IN_JISHIGOU_MOBILE',true);

define('CHARSET', 'utf-8');

require ROOT_PATH . 'include/jishigou.php';
$jishigou = new jishigou();

$jishigou->run('mobile');

?>