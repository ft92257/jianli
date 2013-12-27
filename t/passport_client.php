<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename passport_client.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:35 1426081768 1842108445 7648 $
 *******************************************************************/


define('ROOT_PATH',dirname(__FILE__) . '/');
define('IN_JISHIGOU',true);
$action = isset($_POST['action']) ? $_POST['action'] : $_GET['action'];
$userdb = isset($_POST['userdb']) ? $_POST['userdb'] : $_GET['userdb'];
$forward = isset($_POST['forward']) ? $_POST['forward'] : $_GET['forward'];
$verify = isset($_POST['verify']) ? $_POST['verify'] : $_GET['verify'];
$forward = str_replace('&#61;', '=', $forward);
require(ROOT_PATH.'setting/phpwind.php');
$key = $config['phpwind']['pw_pptkey'];
if(md5($action.$userdb.urldecode($forward).$key) == $verify){
	require(ROOT_PATH.'setting/settings.php');
	$db_charset = strtolower(str_replace('-','',$config['charset']));
	if(!defined('JSG_DB_CHARSET')) define("JSG_DB_CHARSET",$db_charset);
	$db_prefix = $config['db_table_prefix'];
	if(!defined('JSG_DB_PRE')) define("JSG_DB_PRE",$db_prefix);
	parse_str(StrCode($userdb,$key,'DECODE'),$userdb);
	if($action=='login'){
		$userdb = escapeChar($userdb);
		if(is_array($userdb) && $userdb['username'] && $userdb['password']){
			synlogin($userdb['username'], $userdb['password']);
		}
	}
	if($action=='quit'){
		synlogout();
	}
}
header('Location: '.$forward);exit;
function escapeChar($mixed, $isint = false, $istrim = false) {
	if (is_array($mixed)) {
		foreach ($mixed as $key => $value) {
			$mixed[$key] = escapeChar($value, $isint, $istrim);
		}
	} elseif ($isint) {
		$mixed = (int) $mixed;
	} elseif (!is_numeric($mixed) && ($istrim ? $mixed = trim($mixed) : $mixed) && $mixed) {
		$mixed = escapeStr($mixed);
	}
	return $mixed;
}
function escapeStr($string) {
	$string = str_replace(array("\0","%00","\r"), '', $string);
	$string = preg_replace(array('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/','/&(?!(#[0-9]+|[a-z]+);)/is'), array('', '&amp;'), $string);
	$string = str_replace(array("%3C",'<'), '&lt;', $string);
	$string = str_replace(array("%3E",'>'), '&gt;', $string);
	$string = str_replace(array('"',"'","\t",'  '), array('&quot;','&#39;','    ','&nbsp;&nbsp;'), $string);
	return $string;
}
function StrCode($string, $key, $action = 'ENCODE') {
	$action != 'ENCODE' && $string = base64_decode($string);
	$code = '';
	$key = substr(md5($_SERVER['HTTP_USER_AGENT'].$key), 8, 18);
	$keyLen = strlen($key);
	$strLen = strlen($string);
	for ($i = 0; $i < $strLen; $i++) {
		$k = $i % $keyLen;
		$code .= $string[$i] ^ $key[$k];
	}
	return ($action != 'DECODE' ? base64_encode($code) : $code);
}
function synlogout() {
	header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
	jsg_setcookie('sid', '', -86400000);
	jsg_setcookie('auth', '', -86400000);
}
function synlogin($username, $password) {
	@header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
	require(ROOT_PATH.'setting/settings.php');
	global $db;    
	$db = new jsg_db;
	$db->connect($config['db_host'], $config['db_user'],$config['db_pass'],$config['db_name']);
	$query = $db->query("SELECT `uid`, `password` FROM `".JSG_DB_PRE."members` WHERE `nickname`='$username' AND `password`='$password'");
	$UserFields = $db->fetch_array($query);
	if($UserFields)
	{
		$auth = authcode("{$UserFields['password']}\t{$UserFields['uid']}","ENCODE",'',2592000);
        jsg_setcookie('sid', '', -86400000);
        jsg_setcookie('auth',$auth,86400000);
	}
}
function jsg_setcookie($var, $value, $life = 0, $prefix = 1)
{
    require(ROOT_PATH.'setting/settings.php');
    $expire = 0;
    if($life)
    {
        $expire = time() + $life;
    }
	@setcookie(($prefix ? $config['cookie_prefix'] : '').$var, $value,
		$expire, ($config['cookie_path'] ? $config['cookie_path'] : '/'),
		($config['cookie_domain'] ? $config['cookie_domain'] : ''), ($_SERVER['SERVER_PORT'] == 443 ? 1 : 0));
}
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;
	if(!$key)
    {
		require(ROOT_PATH.'setting/settings.php');
		$key = $config['auth_key'];
	}
	$key = md5($key);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);
	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);
	$result = '';
	$box = range(0, 255);
	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}
	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}
	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}
	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}
}
class jsg_db {
	var $querynum = 0;
	var $link;
	function connect($dbhost, $dbuser, $dbpw, $dbname = '', $pconnect = 0, $halt = TRUE) {
		if($pconnect) {
			if(!$this->link = @mysql_pconnect($dbhost, $dbuser, $dbpw)) {
				$halt && $this->halt('Can not connect to MySQL server');
			}
		} else {
			if(!$this->link = @mysql_connect($dbhost, $dbuser, $dbpw, 1)) {
				$halt && $this->halt('Can not connect to MySQL server');
			}
		}
		if($this->version() > '4.1') {
			@mysql_query("SET character_set_connection=".JSG_DB_CHARSET.", character_set_results=".JSG_DB_CHARSET.", character_set_client=binary", $this->link);
			if($this->version() > '5.0.1') {
				@mysql_query("SET sql_mode=''", $this->link);
			}
		}
		if($dbname) {
			@mysql_select_db($dbname, $this->link);
		}
	}
	function select_db($dbname) {
		return mysql_select_db($dbname, $this->link);
	}
	function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}
	function query($sql, $type = '') {
		global $debug, $starttime, $sqldebug, $sqlspenttimes;
		$func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ?
			'mysql_unbuffered_query' : 'mysql_query';
		if(!($query = $func($sql, $this->link))) {
			if(in_array($this->errno(), array(2006, 2013)) && substr($type, 0, 5) != 'RETRY') {
				$this->close();
				$config = array();
				require(ROOT_PATH . 'setting/settings.php');
				$this->connect($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name'], $config['db_persist']);
				$this->query($sql, 'RETRY'.$type);
			} elseif($type != 'SILENT' && substr($type, 5) != 'SILENT') {
				$this->halt('MySQL Query Error', $sql);
			}
		}
		$this->querynum++;
		return $query;
	}
	function error() {
		return (($this->link) ? mysql_error($this->link) : mysql_error());
	}
	function errno() {
		return intval(($this->link) ? mysql_errno($this->link) : mysql_errno());
	}
	function version() {
		return mysql_get_server_info($this->link);
	}
	function close() {
		return mysql_close($this->link);
	}
	function halt($msg = '', $sql = '') {
		echo('<br>JishiGou Login : <br>'.$msg."<br>".$sql.'<br><hr><br>');
	}
}
?>