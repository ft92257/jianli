<?php
/**
 *
 * 记事狗常量定义文件
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: constants.php 872 2012-04-27 09:07:13Z wuliyong $
 */

if(true === DEBUG) {
	error_reporting(E_ALL ^ E_NOTICE);
} else {
	error_reporting(E_ERROR);
}

if(!defined('ROOT_PATH')) {
	define(ROOT_PATH, substr(dirname(__FILE__), 0, -8) . '/');
}

if(!isset($config['auth_key'])) {
	include(ROOT_PATH . 'setting/settings.php');
}

//IN_JISHIGOU
define('IN_JISHIGOU',       true);

//软件的信息
define('SYS_VERSION',		'3.6.6');
define('SYS_PUBLISHED',     '');
define('SYS_BUILD',			'build 20120829');

//输出控制
define('GZIP',				(boolean) $config['gzip']);

//数据表前辍
define('TABLE_PREFIX',				$config['db_table_prefix']);

//时区设置
if(function_exists('date_default_timezone_set')) {
    $config['timezone'] = ((isset($config['timezone']) && is_numeric($config['timezone'])) ? $config['timezone'] : 8);

	@date_default_timezone_set('Etc/GMT'.($config['timezone'] > 0 ? '-' : '+').(abs($config['timezone'])));
}


//Ucenter 设置
if($config['ucenter_enable']) {

	include(ROOT_PATH . './setting/ucenter.php');

	define('UCENTER' , 			($config['ucenter']['enable'] ? true : false));//标识Ucenter是否已经开启

	if (true === UCENTER) {
        define('UCENTER_MODIFY_NICKNAME', ($config['ucenter']['modify_nickname'] ? true : false)); //整合Ucenter后是否再允许用户修改昵称？

        define('UCENTER_FACE' , 	($config['ucenter']['face'] ? true : false));//标识Ucenter是否开启调用UC头像

		define('UC_CONNECT', 		$config['ucenter']['uc_connect']);	// 连接 UCenter 的方式: mysql/NULL, 默认为空时为 fscoketopen()

		//数据库相关 (mysql 连接时, 并且没有设置 UC_DBLINK 时, 需要配置以下变量)
		define('UC_DBHOST',		 	$config['ucenter']['uc_db_host']);			// UCenter 数据库主机
		define('UC_DBUSER', 		$config['ucenter']['uc_db_user']);				// UCenter 数据库用户名
		define('UC_DBPW', 			$config['ucenter']['uc_db_password']);					// UCenter 数据库密码
		define('UC_DBNAME', 		$config['ucenter']['uc_db_name']);				// UCenter 数据库名称
		define('UC_DBCHARSET',		str_replace('-','',$config['charset']));				// UCenter 数据库字符集
		define('UC_DBTABLEPRE', 	$config['ucenter']['uc_db_table_prefix']);			// UCenter 数据库表前缀

		//通信相关
		define('UC_KEY', 			$config['ucenter']['uc_key']);				// 与 UCenter 的通信密钥, 要与 UCenter 保持一致
		define('UC_API', 			$config['ucenter']['uc_api']);	// UCenter 的 URL 地址, 在调用头像时依赖此常量
		define('UC_CHARSET', 		$config['charset']);				// UCenter 的字符集
		define('UC_IP', 			$config['ucenter']['uc_ip']);					// UCenter 的 IP, 当 UC_CONNECT 为非 mysql 方式时, 并且当前应用服务器解析域名有问题时, 请设置此值
		define('UC_APPID', 			$config['ucenter']['uc_app_id']);					// 当前应用的 ID
	}
}

//Phpwind 设置
elseif($config['phpwind_enable']) {

	include(ROOT_PATH . './setting/phpwind.php');

	define('PWUCENTER' , 			($config['phpwind']['enable'] ? true : false));//标识Phpwind是否已经开启

	if (true === PWUCENTER) {

        define('UCENTER_FACE' , 	($config['phpwind']['face'] ? true : false));//标识phpwind是否开启调用用户头像

		define('UC_CONNECT', 		'mysql');	// 连接 phpwind 的方式: mysql/NULL, 默认为空时为 fscoketopen()

		define('UC_DBHOST',		 	$config['phpwind']['pw_db_host']);			// phpwind 数据库主机
		define('UC_DBUSER', 		$config['phpwind']['pw_db_user']);				// phpwind 数据库用户名
		define('UC_DBPW', 			$config['phpwind']['pw_db_password']);			// phpwind 数据库密码
		define('UC_DBNAME', 		$config['phpwind']['pw_db_name']);				// phpwind 数据库名称
		define('UC_DBCHARSET',		str_replace('-','',$config['phpwind']['pw_db_charset']));	// phpwind 数据库字符集
		define('UC_DBTABLEPRE', 	$config['phpwind']['pw_db_table_prefix']);		// phpwind 数据库表前缀

		//通信相关
		define('UC_KEY', 			$config['phpwind']['pw_key']);				// 与 phpwind 的通信密钥, 要与 phpwind 保持一致
		define('UC_API', 			$config['phpwind']['pw_api']);	// phpwind 的 URL 地址, 在调用头像时依赖此常量
		define('UC_CHARSET', 		$config['pw_charset']);				// phpwind 的字符集
		define('UC_IP', 			$config['phpwind']['pw_ip']);		// phpwind 的 IP, 当应用服务器解析域名有问题时, 请设置此值
		define('UC_APPID', 			$config['phpwind']['pw_app_id']);	// 当前应用的 ID
	}
}

//全局时间戳
define('TIMESTAMP', time());

//插件开发者(0：关闭；1：开启)
define('PLUGINDEVELOPER', 0);

//UC_KEY
if(!defined('UC_KEY')) {
	define('UC_KEY', $config['auth_key'] . $config['safe_key']);
}

?>