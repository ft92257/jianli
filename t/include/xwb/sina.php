<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename sina.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 93875704 1191895861 3550 $
 *******************************************************************/





if(!defined("ROOT_PATH")) 
{
	define("ROOT_PATH" , substr(dirname(__FILE__),0,-12) . "/");
}
define('IS_IN_XWB_PLUGIN',      true);
define('XWB_P_PROJECT', 	   'xwb4jsg');
define('XWB_P_VERSION',		   '1.0.0');
define('XWB_P_INFO_API',	   'http:/'.'/x.weibo.com/service/stdVersion.php?p='. XWB_P_PROJECT. '&v='. XWB_P_VERSION );
define('XWB_P_STAT_DISABLE',    true);

define('XWB_P_ROOT',			dirname(__FILE__) );
define('XWB_P_DIR_NAME',		'include/xwb' );
define('XWB_P_DATA',			XWB_P_ROOT. DIRECTORY_SEPARATOR. 'log' );
define('XWB_CLIENT_SESSION',	'XWB_P_SESSION');

define('XWB_R_GET_VAR_NAME',	'm');
define('XWB_R_DEF_MOD',			'test');
define('XWB_R_DEF_MOD_FUNC',	'default_action');

define('XWB_SITE_GLOBAL_V_NAME','XWB_SITE_GLOBAL_V_NAME');

define('XWB_API_URL', 	'http:/'.'/api.t.sina.com.cn/');
define('XWB_API_CHARSET',		'UTF8');


define('XWB_S_ROOT',	ROOT_PATH);


require_once XWB_P_ROOT.'/lib/compat.inc.php';require_once XWB_P_ROOT.'/lib/core.class.php';


require_once XWB_P_ROOT . '/jishigou.php';

session_start();
if ( !isset($_SESSION[XWB_CLIENT_SESSION]) ){
	$_SESSION[XWB_CLIENT_SESSION]= array();
}



$GLOBALS['__CLASS'] = array();
$GLOBALS['xwb_tips_type'] = '' ;

$sess = XWB_plugin::getUser();
if ( !defined('IN_XWB_INSTALL_ENV') ){
	
	if( defined('XWB_S_UID') &&  XWB_S_UID ){
		$bInfo = XWB_plugin::getBindInfo ();
		if (!empty ($bInfo) && is_array ($bInfo)) {
			$keys = array ('oauth_token' => $bInfo ['token'], 'oauth_token_secret' => $bInfo ['tsecret'] );
			$sess->setInfo( 'sina_uid', $bInfo ['sina_uid'] );
			$sess->setOAuthKey( $keys, true );
		}
	}
	
	$GLOBALS['xwb_tips_type']  = $sess->getInfo('xwb_tips_type');
	if( $GLOBALS['xwb_tips_type'] ){
		$sess->delInfo('xwb_tips_type');
		setcookie ('xwb_tips_type', '', time () - 3600);
	}
	
	$xwb_token = $sess->getToken ();
	if ( empty($xwb_token) ) {
		$sess->clearToken ();
		setcookie ('xwb_tips_type', '', time () - 3600);
	}
}