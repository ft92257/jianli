<?php
/**
 * 绉诲姩瀹㈡埛绔痑pi
 * 
 * @author 		~ZZ~<505171269@qq.com>
 * @version		v1.0 $Date:2011-09-30
 */

error_reporting(E_ERROR); 
ini_set("arg_seperator.output", "&amp;");
ini_set("magic_quotes_runtime", 0);
header('Content-Type: text/html; charset=utf-8');
define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

define('ROOT_PATH',substr(dirname(__FILE__),0,-6) . '/');

define('SYS_ROOT_PATH', ROOT_PATH . 'mobile/');

define('RELATIVE_ROOT_PATH','../');

define('IN_JISHIGOU_MOBILE',true);

define('IN_JISHIGOU_MOBILE_AJAX', true);

class initialize
{

	
	function init()
	{
		$config=array();

				require ROOT_PATH . 'setting/settings.php';		
		@header('Content-Type: text/html; charset=utf-8');

				require ROOT_PATH . 'setting/constants.php';
		
        		if ($config['extcredits_enable']) {
			include(ROOT_PATH . 'setting/credits.php');
		}
		
				require_once ROOT_PATH . 'include/function/global.func.php';
		
		require_once SYS_ROOT_PATH . 'include/function/mobile.func.php';
		
				require_once SYS_ROOT_PATH . 'modules/master.mod.php';
		
				require_once SYS_ROOT_PATH . 'modules/ajax/' . $this->SetEvent($config['default_module']) . '.mod.php';
		
						
						if ($_GET) {
			$_GET = array_iconv('utf-8',$config['charset'],$_GET);
			$_GET = jaddslashes($_GET, 1, TRUE);
		}
		
		if ($_POST) {
			$_POST	= array_iconv('utf-8',$config['charset'],$_POST);
			$_POST	= jaddslashes($_POST, 1, TRUE);
		}
		
		$moduleobject = new ModuleObject($config);
		
	}

	
	function SetEvent($default='topic')
	{
		$modss = array(
			'topic' => 1,
			'friend' => 1,
			'member' => 1,
			'search' => 1,
			'tag' => 1,
			'pm' => 1,
			'square' => 1,
			'misc' => 1,
		);
		$mod = (isset($_POST['mod']) ? $_POST['mod'] : $_GET['mod']);
		
				if (!isset($modss[$mod])) {
			if ($mod) {
				$_POST['mod_original'] = $_GET['mod_original'] = $mod;
			}
			$mod = ($default ? $default : 'topic');
		}
		$_POST['mod'] = $_GET['mod'] = $mod;	
		return $mod;
	}
}

$init=new initialize;
$init->init();
?>