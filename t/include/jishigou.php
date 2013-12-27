<?php
/**
 *
 * 记事狗核心入口类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id$
 */

class jishigou {

	var $var = array();

	function jishigou() {
		if(!defined('IN_JISHIGOU')) {
			$this->_init_env();
			$this->_init_config();
			$this->_init_input();
			$this->_init_output();
		}
	}

	function run($type='') {
		$types = array(
			'index'=>array('mod_default'=>'topic', ), 
			'admin'=>array('mod_exit'=>1, ), 
			'ajax'=>array('mod_default'=>'topic', ), 
			'api'=>array('mod_default'=>'test', 'mod_exit'=>1, ), 
			'imjiqiren'=>array('mod_default'=>'imjiqiren', 'mod_exit'=>1, ), 
			'sms'=>array('mod_default'=>'sms', 'mod_exit'=>1, ), 
			'widget'=>array('mod_default'=>'qun', 'mod_exit'=>1, ),
			'wap'=>array('mod_default'=>'topic', 'mod_path'=>'wap/modules/', ), 
			
			'mobile'=>array('mod_default'=>'topic', 'mod_path'=>'mobile/modules/', ), 
			'mobile_ajax'=>array('mod_default'=>'topic', 'mod_path'=>'mobile/modules/ajax/', ), 
		);
		
		if(!isset($types[$type])) {
			$type = 'index';
		}
		
		$modules_path = ROOT_PATH . ($types[$type]['mod_path'] ? $types[$type]['mod_path'] : ('modules/' . ('index' == $type ? '' : $type . '/')));
		define('IN_JISHIGOU_' . strtoupper($type), true);
		
		if(!(@include_once $modules_path . 'master.mod.php') && !class_exists('MasterObject')) {
			exit('modules path is invalid');
		}
		
		if($this->var['config']['rewrite_enable'] && (true===IN_JISHIGOU_INDEX || true===IN_JISHIGOU_AJAX || true===IN_JISHIGOU_ADMIN)) {
			include(ROOT_PATH . 'include/rewrite.php');
		}
		
		if(!(@include_once $modules_path . ($this->_init_mod($types[$type])) . '.mod.php') && !class_exists('ModuleObject')) {
			exit('mod is invalid');
		}
		
		if ($this->var['config']['upgrade_lock_time'] > 0 && true!==IN_JISHIGOU_UPGRADE && true!==IN_JISHIGOU_ADMIN) {
			if(($this->var['config']['upgrade_lock_time'] + 600 > TIMESTAMP) ||
			(is_file(ROOT_PATH . './data/cache/upgrade.lock') &&
			@filemtime(ROOT_PATH . './data/cache/upgrade.lock') + 600 > TIMESTAMP)) {
				die('System upgrade. Please wait...');
			}
		}
		
		if ($this->var['config']['site_closed'] && true!==IN_JISHIGOU_ADMIN) {
			if ('login' != $this->var['mod'] && ($site_closed_msg=file_get_contents(ROOT_PATH . 'data/cache/site_enable.txt'))) {
				exit($site_closed_msg);
			}
		}
		
		$allow_gzip = 0;
		$un_gzip_mods = array('share'=>1, 'output'=>1, 'download'=>1, 'attachment'=>1, );
		if(true===GZIP && true===IN_JISHIGOU_INDEX && !isset($un_gzip_mods[$this->var['mod']]) && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
			$allow_gzip = 1;
		}
		ob_start(($allow_gzip ? 'ob_gzhandler' : null));
		$ModuleObject = new ModuleObject($this->var['config']);
		if(true===DEBUG) $ModuleObject->DatabaseHandler->Debug();
	}

	function _init_env() {
		error_reporting(E_ERROR);
		@set_time_limit(300);
		if(PHP_VERSION < '5.3.0') {
			set_magic_quotes_runtime(0);
		}

		

		define('IN_JISHIGOU', true);
		define('ROOT_PATH', substr(dirname(__FILE__), 0, -8) . '/');
		define('PLUGIN_DIR', ROOT_PATH . 'plugin/');
		define('RELATIVE_ROOT_PATH', './');
		define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
		define('TIMESTAMP', time());

		if(!defined('JISHIGOU_GLOBAL_FUNCTION') && !@include(ROOT_PATH . 'include/function/global.func.php')) {
			exit('global.func.php is not exists');
		}

		if(function_exists('ini_set')) {
			ini_set('memory_limit', '256M');
			ini_set("arg_seperator.output", "&amp;");
			ini_set("magic_quotes_runtime", 0);
		}

		$superglobal = array(
				'GLOBALS' => 1,
				'_GET' => 1,
				'_POST' => 1,
						'_COOKIE' => 1,
				'_SERVER' => 1,
				 		'_FILES' => 1,
		);
		foreach($GLOBALS as $k=>$v) {
			if(!isset($superglobal[$k])) {
				$GLOBALS[$k] = null; unset($GLOBALS[$k]);
			}
		}

		global $_J;
		$_J = array(
			'timestamp' => TIMESTAMP,
			'time_start' => microtime(true),
			'client_ip' => client_ip(),
			'uid' => 0,
			'username' => '',
			'nickname' => '',
			'role_id' => 0,
			'charset' => '',
			'site_name' => '',
			'site_url' => '',
			'wap_url' => '',
			'mobile_url' => '',
			'mod' => '',
			'code' => '',
		);

		$this->var = & $_J;
	}

	function _init_config() {
		$config = array();
		require ROOT_PATH . 'setting/settings.php';

		define('CHARSET', $config['charset']);

		@header('Content-Type: text/html; charset=' . CHARSET);
		@header('P3P: CP="CAO PSA OUR"');

		if($config['install_lock_time'] < 1) {
			if (!is_file(ROOT_PATH . 'data/install.lock') &&
			is_file(ROOT_PATH . 'install.php')) {
				die("<meta http-equiv='refresh' content=\"5; URL='./install.php'\"><a href='./install.php'>请点此进行系统的安装</a>");
			}
		}
		
		require ROOT_PATH . 'setting/constants.php';

				$config['sys_version'] = sys_version();
		$config['sys_published'] = SYS_PUBLISHED;
		if(!$config['wap_url']) {
			$config['wap_url'] = $config['site_url'] . "/wap";
		}
		if(!$config['mobile_url']) {
			$config['mobile_url'] = $config['site_url'] . "/mobile";
		}
				if($config['extra_domains']) {
			$http_host = (getenv('HTTP_HOST') ? getenv('HTTP_HOST') : $_SERVER['HTTP_HOST']);
			if($config['site_domain'] != $http_host && in_array($http_host, $config['extra_domains'])) {
				$config['site_url'] = str_replace($config['site_domain'], $http_host, $config['site_url']);
				$config['wap_url'] = str_replace($config['site_domain'], $http_host, $config['wap_url']);
				$config['mobile_url'] = str_replace($config['site_domain'], $http_host, $config['mobile_url']);
				$config['site_domain'] = $http_host;
			}
		}
		if(!$config['topic_cut_length']) {
			$config['topic_cut_length'] = 140;
			if(!isset($config['topic_input_length'])) {
				$config['topic_input_length'] = 140;
			}
		}
		$config['topic_input_length'] = (int) $config['topic_input_length'];
		
		Obj::register('config', $config);

		if($config['robot_enable']) {
			include ROOT_PATH . 'setting/robot.php';
		}
		if($config['ad_enable']) {
			include ROOT_PATH . 'setting/ad.php';
		}
		if($config['extcredits_enable']) {
			include ROOT_PATH . 'setting/credits.php';
		}

		
		$this->var['charset'] = strtolower($config['charset']);
		$this->var['site_name'] = $config['site_name'];
		$this->var['site_url'] = $config['site_url'];
		$this->var['wap_url'] = $config['wap_url'];
		$this->var['mobile_url'] = $config['mobile_url'];

		$this->var['config'] = & $config;
	}

	function _init_input() {
		if (isset($_GET['GLOBALS']) || isset($_POST['GLOBALS']) || isset($_COOKIE['GLOBALS']) || isset($_FILES['GLOBALS'])) {
			die('request is invalid');
		}		
		
		$_GET = jaddslashes($_GET);
		$_POST = jaddslashes($_POST);
		$_COOKIE = jaddslashes($_COOKIE);
	}

	function _init_mod($options = array()) {
		$mod_default = ($options['mod_default'] ? $options['mod_default'] : 'index');
		$mods = array ( 
			'cache' => 1, 
			'db' => 1, 
			'imjiqiren' => 1, 
			'income' => 1, 
			'index' => 1, 
			'link' => 1, 
			'login' => 1, 
			'medal' => 1, 
			'media' => 1, 
			'member' => 1, 
			'notice' => 1, 
			'pm' => 1, 
			'report' => 1, 
			'rewrite' => 1, 
			'robot' => 1, 
			'role' => 1, 
			'role_action' => 1, 
			'role_module' => 1, 
			'sessions' => 1, 
			'setting' => 1, 
			'share' => 1, 
			'show' => 1, 
			'sms' => 1, 
			'tag' => 1, 
			'topic' => 1, 
			'ucenter' => 1, 
			'upgrade' => 1, 
			'web_info' => 1, 
			'task' => 1, 
			'user_tag' => 1, 
			'api' => 1, 
			'vote' => 1, 
			'vipintro' => 1, 
			'qun' => 1, 
			'recdtopic' => 1, 
			'plugin' => 1, 
			'plugindesign' => 1, 
			'class' => 1, 
			'module' => 1, 
			'city' => 1, 
			'fenlei' => 1, 
			'event' => 1, 
			'account' => 1, 
			'search' => 1, 
			'dzbbs' => 1, 
			'phpwind' => 1, 
			'dedecms' => 1, 
			'verify' => 1, 
			'sign' => 1, 
			'live' => 1, 
			'talk' => 1, 
			'attach' => 1, 
			'output' => 1, 
			'log' => 1, 
			'logs' => 1, 
			'schedule' => 1, 
			'face' => 1, 
			'reminded' => 1, 
			'test' => 1, 
			'app' => 1, 
			'wall' => 1, 
			'misc' => 1, 
			'longtext' => 1, 
			'uploadify' => 1, 
			'uploadattach' => 1, 
			'view' => 1, 
			'user' => 1, 
			'topic_manage' => 1, 
			'item' => 1, 
			'validate' => 1, 
			'public' => 1, 
			'profile' => 1, 
			'get_password' => 1, 
			'url' => 1, 
			'other' => 1, 
			'skin' => 1, 
			'blacklist' => 1, 
			'xwb' => 1, 
			'settings' => 1, 
			'qqwb' => 1, 
			'tools' => 1, 
			'qmd' => 1, 
			'yy' => 1, 
			'renren' => 1, 
			'kaixin' => 1, 
			'update' => 1, 
			'fjau' => 1, 
			'people' => 1, 
			'nedu' => 1, 
			'oauth2' => 1,
			'company' => 1,
			'department' => 1,
			'channel' => 1,
		
						'friend' => 1,
			'member' => 1,
			'square' => 1,
			'more' => 1,
			
			'reward' => 1,
			'block' => 1,
		);

		$mod = $this->_get('mod');
		if(!$mod) {
			$mod = $mod_default;
		}
		if(!isset($mods[$mod])) {
			if($options['mod_exit']) {
				include ROOT_PATH . 'include/error_404.php';
				exit;
			} else {
				if($mod) {
					$_POST['mod_original'] = $_GET['mod_original'] = $mod;
						
					$this->var['mod_original'] = $mod;
				}
				$mod = $mod_default;
			}
		}
		$_POST['mod'] = $_GET['mod'] = $mod;
		$_POST['code'] = $_GET['code'] = $this->_get('code');

		define('CURMODULE', $mod);

		$this->var['mod'] = $mod;
		$this->var['code'] = $_GET['code'];

		return $mod;
	}

	function _init_output() {
		if('GET' == $_SERVER['REQUEST_METHOD'] && !empty($_SERVER['REQUEST_URI'])) {
			$temp = strtoupper(urldecode(urldecode($_SERVER['REQUEST_URI'])));
			if(strpos($temp, '<') !== false || strpos($temp, '"') !== false || strpos($temp, 'CONTENT-TRANSFER-ENCODING') !== false) {
				die('request is invalid');
			}
		}
	}
	
	function _get($var, $ifemptyval='') {
		$val = get_param($var);
		if($val) {
			return str_safe($val);
		}

		return $ifemptyval;
	}
}


?>