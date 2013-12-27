<?php
/**
 *[JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * UCenter 应用程序开发 API BY JishiGou
 *
 * 此文件为 api/uc.php ，处理 UCenter 通知给JishiGou的任务
 *
 * @author 狐狸<foxis@qq.com>
 *
 * @Last modified 2010年9月7日
 *
 * @version $Id: uc.php 1275 2012-07-23 08:27:53Z wuliyong $
 */

error_reporting(E_ERROR);

define('UC_CLIENT_VERSION', '1.6.0');
define('UC_CLIENT_RELEASE', '20110501');

define('API_DELETEUSER', 1);
define('API_RENAMEUSER', 1);
define('API_GETTAG', 0);
define('API_SYNLOGIN', 1);
define('API_SYNLOGOUT', 1);
define('API_UPDATEPW', 1);
define('API_UPDATEBADWORDS', 1);
define('API_UPDATEHOSTS', 1);
define('API_UPDATEAPPS', 1);
define('API_UPDATECLIENT', 1);
define('API_UPDATECREDIT', 0);
define('API_GETCREDIT', 0);
define('API_GETCREDITSETTINGS', 0);
define('API_UPDATECREDITSETTINGS', 0);
define('API_ADDFEED', 0);

define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '1');

define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');

if(!defined('IN_UC')) {
	ini_set("magic_quotes_runtime", 0);
	defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
	require_once ROOT_PATH . 'include/function/global.func.php';
	require ROOT_PATH . './setting/settings.php';
	if(true !== UCENTER) {
		exit('UCENTER is invalid');
	}
	if(!defined('UC_KEY') || '' == UC_KEY) {
		exit('UC_KEY is empty');
	}

	$get = $post = array();

	$code = @$_GET['code'];
	parse_str(authcode($code, 'DECODE', UC_KEY), $get);
	if(MAGIC_QUOTES_GPC) {
		$get = _uc_api_stripslashes($get);
	}

	$timestamp = time();
	if(empty($get)) {
		exit('Invalid Request');
	} elseif($timestamp - $get['time'] > 3600) {
		exit('Authracation has expiried');
	}
	$action = $get['action'];

	if(!function_exists('xml_serialize')) {
		include_once ROOT_PATH.'./api/uc_client/lib/xml.class.php';
	}
	$post = xml_unserialize(file_get_contents('php:/'.'/input'));

	if(in_array($get['action'], array('test', 'deleteuser', 'renameuser', 'gettag', 'synlogin', 'synlogout', 'updatepw', 'updatebadwords', 'updatehosts', 'updateapps', 'updateclient', 'updatecredit', 'getcredit', 'getcreditsettings', 'updatecreditsettings')))
	{
		
		include_once(ROOT_PATH.'./api/uc_api_db.php');
		$GLOBALS['uc_api_db'] = new JSG_UC_API_DB();
		$GLOBALS['uc_api_db']->connect(($config['db_host'].($config['db_port'] ? ":{$config['db_port']}" : '')),$config['db_user'],$config['db_pass'],$config['db_name'],$config['charset'],$config['db_persist'],$config['db_table_prefix'],$timestamp);
		$GLOBALS['tablepre'] = $config['db_table_prefix'];
		unset($config['db_host'],$config['db_user'],$config['db_pass'],$config['db_name'],$config['charset'],$config['db_persist'],$config['db_table_prefix']);
		

		$uc_note = new uc_note();

		exit($uc_note->$get['action']($get, $post));
	}
	else
	{
		exit(API_RETURN_FAILED);
	}
}
else
{
	;
}

class uc_note {

	var $db = '';
	var $tablepre = '';
	var $appdir = '';

	function _serialize($arr, $htmlon = 0) {
		if(!function_exists('xml_serialize')) {
			include_once $this->appdir.'./api/uc_client/lib/xml.class.php';
		}
		return xml_serialize($arr, $htmlon);
	}

	function uc_note() {
		$this->appdir = ROOT_PATH;
		$this->db = $GLOBALS['uc_api_db'];
		$this->tablepre = $GLOBALS['tablepre'];
	}

	function test($get, $post) {
		return API_RETURN_SUCCEED;
	}

	function deleteuser($get, $post) {
		if(!API_DELETEUSER) {
			return API_RETURN_FORBIDDEN;
		}

		$ids = stripslashes(trim($get['ids']));
		if($ids) {
			$ids = (array) (explode(',', str_replace(array("'", '"',), '', $ids)));

			$query = $this->db->query("select `uid` from `{$this->tablepre}members` where `ucuid` in ('".implode("','", $ids)."')");
			$uids = array();
			while (false != ($row = $this->db->fetch_array($query))) {
				$uids[$row['uid']] = $row['uid'];
			}

			if($uids) {
				jsg_member_delete($uids);
			}
		}

		return API_RETURN_SUCCEED;
	}

	function renameuser($get, $post) {
		$uid = (int) $get['uid'];
		$oldusername = $get['oldusername'];
		$newusername = $get['newusername'];
		if(!API_RENAMEUSER) {
			return API_RETURN_FORBIDDEN;
		}

		$member_info = $this->db->fetch_first("SELECT * FROM `{$this->tablepre}members` WHERE `ucuid`='$uid'");
		if($member_info) {
			$tables = array(
				'members' => array('id'=>'uid', 'name'=>'nickname'),
				'members_verify' => array('id'=>'uid', 'name'=>'nickname'),
				'medal_apply' => array('id'=>'uid', 'name'=>'nickname'),
				'user_medal' => array('id'=>'uid', 'name'=>'nickname'),
				'log' => array('id'=>'uid', 'name'=>'nickname'),
			);
			foreach($tables as $tb=>$conf) {
				$sql = "UPDATE `{$this->tablepre}{$tb}` SET `{$conf['name']}`='{$newusername}' WHERE `{$conf['id']}`='{$member_info['uid']}' AND `{$conf['name']}`='{$oldusername}'";
				$this->db->query($sql);
			}
		}

		return API_RETURN_SUCCEED;
	}

	function gettag($get, $post) {
		$name = $get['id'];
		if(!API_GETTAG) {
			return API_RETURN_FORBIDDEN;
		}

		return $this->_serialize($return, 1);
	}

	function synlogin($get, $post) {

		if(!API_SYNLOGIN)
		{
			return API_RETURN_FORBIDDEN;
		}

		@header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');

		$uid = (int) $get['uid'];
		$query = $this->db->query("SELECT `uid`, `password` FROM `{$this->tablepre}members` WHERE `ucuid`='$uid'");
		$UserFields = $this->db->fetch_array($query);
		if($UserFields)
		{
			$auth = authcode("{$UserFields['password']}\t{$UserFields['uid']}","ENCODE",'',2592000);
			jsg_setcookie('sid', '', -86400000);
			jsg_setcookie('auth',$auth,86400000);
		}
	}


	function synlogout($get, $post) {
		if(!API_SYNLOGOUT) {
			return API_RETURN_FORBIDDEN;
		}

				@header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		jsg_setcookie('sid', '', -86400000);
		jsg_setcookie('auth', '', -86400000);
	}

	function updatepw($get, $post) {
		if(!API_UPDATEPW) {
			return API_RETURN_FORBIDDEN;
		}

				$username = $get['username'];
		$password = md5($get['password']);
		$this->db->query("UPDATE `{$this->tablepre}members` SET `password`='{$password}' WHERE `nickname`='{$username}'");

		return API_RETURN_SUCCEED;
	}

	function updatebadwords($get, $post) {
		if(!API_UPDATEBADWORDS) {
			return API_RETURN_FORBIDDEN;
		}
		
		$data = array();
		if(is_array($post)) {
			foreach($post as $k => $v) {
				$data['findpattern'][$k] = $v['findpattern'];
				$data['replace'][$k] = $v['replacement'];
			}
		}
		$cachefile = ROOT_PATH.'./api/uc_client/data/cache/badwords.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'badwords\'] = '.var_export($data, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);
		
		return API_RETURN_SUCCEED;
	}

	function updatehosts($get, $post) {
		if(!API_UPDATEHOSTS) {
			return API_RETURN_FORBIDDEN;
		}

		$cachefile = ROOT_PATH.'./api/uc_client/data/cache/hosts.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'hosts\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);
		
		return API_RETURN_SUCCEED;
	}

	function updateapps($get, $post) {
		if(!API_UPDATEAPPS) {
			return API_RETURN_FORBIDDEN;
		}

		$UC_API = '';
		if($post['UC_API']) {
			$UC_API = $post['UC_API'];
			unset($post['UC_API']);
		}

		$cachefile = ROOT_PATH.'./api/uc_client/data/cache/apps.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'apps\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);
		
		if($UC_API && preg_match('~^https?\:\/'.'\/~is')) {
			$ucenter = ConfigHandler::get('ucenter');
			if($UC_API != $ucenter['uc_api']) {
				$ucenter['uc_api'] = $UC_API;			
				ConfigHandler::set('ucenter', $ucenter);
			}
		}
		
		return API_RETURN_SUCCEED;
	}

	function updateclient($get, $post) {
		if(!API_UPDATECLIENT) {
			return API_RETURN_FORBIDDEN;
		}

		$cachefile = ROOT_PATH.'./api/uc_client/data/cache/settings.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'settings\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);
		
		return API_RETURN_SUCCEED;
	}

	function updatecredit($get, $post) {
		if(!API_UPDATECREDIT) {
			return API_RETURN_FORBIDDEN;
		}
		$i = (int) $get['credit'];
		$amount = intval($get['amount']);
		$uid = intval($get['uid']);

		$credit_field = " `" . ($i < 1 ? 'credits' : 'extcredits' . $i) . "` ";
		$this->db->query("UPDATE `{$this->tablepre}members` SET {$credit_field}={$credit_field}+'{$amount}' WHERE `ucuid`='{$uid}' ");

				return API_RETURN_SUCCEED;
	}

	function getcredit($get, $post) {
		if(!API_GETCREDIT) {
			return API_RETURN_FORBIDDEN;
		}

	}

	function getcreditsettings($get, $post) {
		if(!API_GETCREDITSETTINGS) {
			return API_RETURN_FORBIDDEN;
		}
		include_once($this->appdir . './setting/credits.php');

		$credits = array();
		for ($i=0;$i<20;$i++)
		{
			$_tmp_val = $config[($i < 1 ? 'credits' : 'extcredits' . $i)];
			if($_tmp_val)
			{
				$credits[$i] = array($_tmp_val,'');
			}
		}

				return $this->_serialize($credits);
	}

	function updatecreditsettings($get, $post) {
		if(!API_UPDATECREDITSETTINGS) {
			return API_RETURN_FORBIDDEN;
		}
		$outextcredits = array();

		foreach($get['credit'] as $appid => $credititems) {
			if($appid == UC_APPID) {
				foreach($credititems as $value) {
					$outextcredits[$value['appiddesc'].'|'.$value['creditdesc']] = array(
						'creditsrc' => $value['creditsrc'],
						'title' => $value['title'],
						'unit' => $value['unit'],
						'ratio' => $value['ratio']
					);
				}
			}
		}

		$cachefile = $this->appdir.'./api/uc_client/data/cache/creditsettings.php';
		$fp = @fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'creditsettings\'] = '.var_export($outextcredits, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);

				return API_RETURN_SUCCEED;
	}
}

function _uc_api_stripslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = _uc_api_stripslashes($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}


?>