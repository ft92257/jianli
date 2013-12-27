<?php
/**
 *
 * 核心函数
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: global.func.php 1411 2012-08-22 01:30:57Z wuliyong $
 */



if(!function_exists('jaddslashes')) {
	function jaddslashes($string) {
		if(is_array($string)) {
			$keys = array_keys($string);
			foreach($keys as $key) {
				$val = $string[$key];
				unset($string[$key]);
				$string[jjaddslashes($key)] = jaddslashes($val);
			}
		} else {
			$string = jjaddslashes($string);
		}
		return $string;
	}
}
if(!function_exists('jstripslashes')) {
	function jstripslashes($string) {
		if(empty($string)) return $string;
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = jstripslashes($val);
			}
		} else {
			$string = stripslashes($string);
		}
		return $string;
	}
}
function jjaddslashes($str) {
	if(empty($str)) return $str;
	if(is_numeric($str)) return $str;
	if(MAGIC_QUOTES_GPC) $str = stripslashes($str);
	if('gbk'==$GLOBALS['_J']['charset']) {
		$str = gbk_addslashes($str);
	} else {
		$str = addslashes($str);
	}
	return $str;
}
function gbk_addslashes($text) {
	for ( ; ; ) {
		$i = mb_strpos($text, chr(92), 0, "GBK");
		if ($i === false) break;
		$T = mb_substr($text, 0, $i, "GBK") . chr(92) . chr(92);
		$text = substr($text, strlen($T) - 1);
		$OK .= $T;
	}
	$text = $OK . $text;
	$text = str_replace(chr(39), chr(92) . chr(39), $text);
	$text = str_replace(chr(34), chr(92) . chr(34), $text);
	return $text;
}


if(!function_exists('file_put_contents')) {

	!defined('FILE_APPEND') && define('FILE_APPEND', 8);

	function file_put_contents($filename, $data, $flag = false) {
		$mode = ($flag == FILE_APPEND || strtoupper ( $flag ) == 'FILE_APPEND') ? 'ab' : 'wb';
		if ( is_array ( $data )){
			$data = implode ( '', $data );
		}
		return Load::lib('io', 1)->WriteFile($filename, $data, $mode);
	}
}
if(!function_exists('jfsockopen')) {
	function jfsockopen($hostname, $port, $errno, $errstr, $timeout) {
		$fp = false;

		if(function_exists('fsockopen')) {
			@$fp = fsockopen($hostname, $port, $errno, $errstr, $timeout);
		} elseif(function_exists('pfsockopen')) {
			@$fp = pfsockopen($hostname, $port, $errno, $errstr, $timeout);
		}

		return $fp;
	}
}

if(!function_exists('jstrpos')) {
	function jstrpos($haystack, $needle, $offset = null) {
		$jstrpos = false;

		if(function_exists('mb_strpos')) {
			$jstrpos = mb_strpos($haystack, $needle, $offset, $GLOBALS['_J']['charset']);
		} elseif(function_exists('strpos')) {
			$jstrpos = strpos($haystack, $needle, $offset);
		}

		return $jstrpos;
	}
}


function jget($key, $filter='', $method='PG') { 
	return get_param($key, $method, $filter); 
}

function jfilter($val, $filter) {
	$filter = strtolower($filter);
	switch ($filter) {
		case 'int': $val = (int) $val; break;
		case 'float': $val = (float) $val; break;
		case 'bool': case 'boolean': $val = (bool) $val; break;
		case 'num': case 'number': $val = (is_numeric($val) ? $val : 0); break;
		case 'txt': $val = htmlspecialchars($val); break;
		case 'trim': $val = trim($val); break;
				case 'url': $val = (($val && preg_match("/^(https?\:\/\/|www\.)([A-Za-z0-9_\-]+\.)+[A-Za-z]{2,4}(\/[\w\d\/=\?%\-\&_~`@\[\]\:\+\#]*([^<>\'\"\n])*)?$/", $val)) ? $val : false); break;
				case 'email': $val = (($val && false !== strpos($val, '@') && preg_match('~^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+([a-z]{2,4})|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$~i', $val)) ? $val : false); break;
				case 'zip': $val = (($val && is_numeric($val) && preg_match('~^\d{6}$~', $val)) ? $val : false); break;
				case 'qq': $val = (($val && is_numeric($val) && preg_match('~^[1-9]\d{4,10}$~')) ? $val : false); break;
				case 'mobile': $val = (($val && is_numeric($val) && preg_match('~^((?:13|15|18)\d{9}|0(?:10|2\d|[3-9]\d{2})[1-9]\d{6,7})$~', $val)) ? $val : false); break;
				case 'chinese': $val = (($val && preg_match('~^(?:[\x7f-\xff][\x7f-\xff])+$~')) ? $val : false); break;
				case 'english': $val = (($val && preg_match('~^[A-Za-z]+$~')) ? $val : false); break;
				case 'username': $val = (($val && !is_numeric($val) && preg_match('~^[\w\d_]{1, 30}$~', $val)) ? $val : false); break;
		default: 
						break;
	}
	return $val;
}

function get_param($key, $method='PG', $filter='') {
	
	$method = strtoupper($method);
	switch ($method) {
		case 'POST': case 'P': $var = &$_POST; break;
		case 'GET': case 'G': $var = &$_GET; break;
		case 'COOKIE': case 'C': $var = &$_COOKIE; break;
		default:
			if(isset($_POST[$key])) {
				$var = &$_POST;
			} else {
				$var = &$_GET;
			}
			break;
	}
	$val = (isset($var[$key]) ? $var[$key] : null);
	
	if($filter) {
		$val = jfilter($val, $filter);
	}
	return $val;
}

function array_remove_empty($array){
	foreach($array as $key=>$val){
		if (!$val) {
			unset($array[$key]);
		}
	}
	return $array;
}




function cache($name,$lifetime=null,$only_get=false)
{
	static $S_filelist=null, $S_lastfile=null, $S_file=null, $S_caches=null;

	$path = (defined('TEMPLATE_ROOT_PATH') ? TEMPLATE_ROOT_PATH : ROOT_PATH) . "data/cache/";

	if($lifetime!==null)
	{
		if($S_file!==null) $S_lastfile = $S_file;
		$S_file = $path.$name.'.cache.php';
		$S_filelist[$S_file] = $S_lastfile;
		$file=$S_file;
		if($only_get) $S_file=null;
		if ($lifetime==0) return @unlink($file);
		if($S_caches[$name.$lifetime]!==null) return $S_caches[$name.$lifetime];
		@include($file);
		if(null!==$cache && (-1==$lifetime || @filemtime($file)+$lifetime>time())) return ($S_caches[$name.$lifetime]=$cache);
	}
	else
	{
		if($S_file===null)if($S_lastfile===null)return false;else $S_lastfile=$S_filelist[$S_file=$S_lastfile];
		if(is_writeable($path)===false && is_dir($path))return trigger_error("缓存目录 $path 不可写",E_USER_WARNING);
		if(is_dir($cache_dir=dirname($S_file))==false) jmkdir($cache_dir);
		$data=var_export($name,true);
		$data="<?php if(!defined('IN_JISHIGOU')) exit('invalid request'); \r\n\$cache=$data;\r\n?>";
		$len = Load::lib('io', 1)->WriteFile($S_file, $data);
		@chmod($S_file, 0777);
		$S_file=null;
		return $len;
	}
	return false;
}

function jcache($cmd, $key='', $val='', $life=0, $type='file') {
	$cmds = array('get'=>1, 'mget'=>1, 'set'=>1, 'mset'=>1, 'rm'=>1, 'mrm'=>1, 'del'=>1, 'clear'=>1, 'clean'=>1);
	if(isset($cmds[$cmd])) {
		$type = ('db' == $type ? 'db' : 'file');
		switch ($cmd) {
			case 'get': return Load::model('cache/' . $type)->get($key); break;
			case 'mget': return Load::model('cache/' . $type)->get($key, 1); break;
			case 'set': return Load::model('cache/' . $type)->set($key, $val, $life); break;
			case 'mset': return Load::model('cache/' . $type)->set($key, $val, $life, 1); break;
			case 'rm' : case 'del': return Load::model('cache/' . $type)->rm($key, $val); break;
			case 'mrm' : return Load::model('cache/' . $type)->rm($key, $val, 1); break;
			case 'clear': case 'clean': return Load::model('cache/' . $type)->clear(); break;
		}
	}
	return null;
}

function cache_file($cmd, $key='', $val='', $life=0) {
	return jcache($cmd, $key, $val, $life, 'file');
}

function cache_db($cmd, $key='', $val='', $life=0) {
	return jcache($cmd, $key, $val, $life, 'db');
}

function clearcache() {
	cache_clear();
}
function cache_clear() {
	$dirs = array(
		'data/cache/',
		'wap/data/cache/',
		'mobile/data/cache/',
		'images/temp/face_images/',
		'api/uc_client/data/cache/',
	);
	foreach($dirs as $dir) {
		@Load::lib('io', 1)->ClearDir(ROOT_PATH . $dir);
	}
	cache_file('clear');
}


function &Tag($type)
{
	include_once(ROOT_PATH . 'include/logic/tag.logic.php');

	return new TagLogic($type);
}

function order($order_by_list,$query_link='',$config=array())
{
	include_once(ROOT_PATH . 'include/function/order.func.php');

	return __order($order_by_list,$query_link,$config);
}

function pre($string)
{
	$string=nl2br($string);
	$string = str_replace(array("&amp;","&gt;","&lt;","&quot;","&#39;","\s","\t",),
	array("&", ">","<","\"","'","&nbsp;","&nbsp;&nbsp;&nbsp;&nbsp;",),  $string);
	return $string;
}

if(false == function_exists('http_build_query'))
{
	
	function http_build_query($form_data, $numeric_prefix = null)
	{
		static $_query = '';

		if(is_array($form_data)==false)Return false;
		foreach($form_data as $key => $values)
		{
			if(is_array($values))
			{
				$_query = http_build_query($values, isset($numeric_prefix)?sprintf('%s[%s]', $numeric_prefix, urlencode($key)):$key);
			}
			else
			{
				$key = isset($numeric_prefix)?sprintf('%s[%s]', $numeric_prefix, urlencode($key)):$key;
				$_query .= (isset($_query) ? '&' : null) . $key . '=' . urlencode(stripslashes($values));
			}
		}
		Return $_query;
	}

}
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;
	if(!$key) {
		$sys_config = ConfigHandler::get();
		$key = $sys_config['auth_key'];
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

function random($length, $numeric = 0) {
	mt_srand((double)microtime() * 1000000);
	if($numeric) {
		$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
	} else {
		$hash = '';
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$max = strlen($chars) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
	}
	return $hash;
}



function build_like_query($fields,$keywords,$binary=false)
{
	if(trim($keywords)==false)Return '';
	$binary = ($binary ?' binary ' : '');
	$keywords=preg_replace('~[\t\s　]+and[\t\s　]+~i','\%',$keywords);
	$keyword_list=preg_split('~([\t\s　]+or[\t\s　]+)|\|~i',$keywords);
	if(count($keyword_list)>1 and $fields==false)die("搜索多个关键字其中部分，必须指定参数\$field");
	
	foreach($keyword_list as $key=>$keyword)
	{
						$keyword = addcslashes($keyword, '_%"\'\\');
				$keyword = str_replace(array('*', ), array('%', ), $keyword);
		$temp_list[] = $keyword;
	}

	$keywords = '';
	if(strpos($fields,',')!==false)
	{
		$field_list=explode(',',$fields);
		foreach($field_list as $field)
		{
			$keywords_list[]=$binary." ".$field.'  like "%'.implode("%\" OR \r\n".$binary.' '.$field.'  like "%',$temp_list)."%\"";
		}
		$keywords='(('.implode(') or (',$keywords_list).'))';
	}
	else
	{
		$keywords=$binary." ".$fields.' like "%'.implode("%\" OR \r\n".$binary.' '.$fields.' like "%',$temp_list)."%\"";
	}
	$keywords=preg_replace("~[%]+~",'%',$keywords);

	return $keywords;
}


function response_text($response)
{
	ob_clean();

	echo $response; exit;
}


function debug($mixed,$halt=true)
{
	static $num=1;
	if (function_exists("debug_backtrace"))
	{
		$debug=debug_backtrace();
		echo "<div style=\"background:#FF6666;color:#fff;margin-top:5px;padding:5px\">".$num++.".debug position: {$debug[0]["file"]}({$debug[0]["line"]})</div>";
	}
	echo "<div style=\"border:1px solid #ff6666;background:#fff;padding:10px\"><pre>";
	if (is_array($mixed))
	{
		echo str_replace(array("&lt;?php","?&gt;"),"",highlight_string("<?php\r\n".var_export($mixed,true).";\r\n?>",true));
	}
	else
	{
		var_dump($mixed);
	}
	echo "</pre></div>";
	$halt && exit;
}

if (function_exists('iconv')==false)
{
	
	function iconv($in_charset,$out_charset,$str)
	{
		if($str && strtoupper($in_charset)!=strtoupper($out_charset))
		{
			if(false!==strpos($out_charset,'/'.'/'))
			{
				$out_charset = str_replace(array('/'.'/IGNORE','/'.'/TRANSLIT'),'',strtoupper($out_charset));
			}

			include_once(ROOT_PATH . 'include/encoding/chinese.class.php');

			$CharEncoding=new Chinese($in_charset,$out_charset);

			return $CharEncoding->Convert($str);
		}
		return $str;
	}
}


function array_iconv($in_charset,$out_charset,$array, $addslashes=0) {
	if($array && strtoupper($in_charset)!=strtoupper($out_charset) && (function_exists('mb_convert_encoding') || function_exists('iconv'))) {
		if(is_array($array)) {
			foreach($array as $key=>$val) {
				$key = lconv($in_charset, $out_charset, $key);
				$array[$key] = array_iconv($in_charset,$out_charset,$val,$addslashes);
			}
		} else {
			$array = lconv($in_charset,$out_charset,$array,$addslashes);
		}
	}
	return $array;
}
function lconv($in_charset,$out_charset,$string,$addslashes=0) {
	$return = '';

	if($string) {
		if (!is_numeric($string) && !is_bool($string) && is_string($string)) {
			if(function_exists('mb_convert_encoding')) {
				$return = mb_convert_encoding($string, $out_charset, $in_charset);
			} elseif (function_exists('iconv')) {
				$return = iconv($in_charset,$out_charset . (false!==strpos($out_charset,'/'.'/') ? '' : "/"."/TRANSLIT"), $string);
			}
		} else {
			$return = $string;
		}

		if($addslashes) $return = jaddslashes($return);
	}

	if(!$return) {
		$return = $string;
	}

	return $return;
}



function referer($default = '?', $ignore_domain = 0) {
	$ignore_domain = (isset($_POST['ignore_domain']) ? $_POST['ignore_domain'] : (isset($_GET['ignore_domain']) ? $_GET['ignore_domain'] : $ignore_domain));
	$referer=$_POST['referer']?$_POST['referer']:$_GET['referer'];
	if($referer=='')$referer=$_SERVER['HTTP_REFERER'];
	if($referer=="" ||
	strpos($referer,'code=register')!==false ||
	strpos($referer,'mod=login')!==false ||
	(!$ignore_domain &&
	strpos($referer,":/"."/")!==false &&
	($DOMAIN = preg_replace('~^www\.~','',strtolower(getenv('HTTP_HOST') ? getenv('HTTP_HOST') : $_SERVER['HTTP_HOST']))) &&
	strpos($referer,$DOMAIN)===false))
	{
		global $rewriteHandler;
		if($rewriteHandler) $default = $rewriteHandler->formatURL($default,false);

		return $default;
	}
	return $referer;
}



function my_date_format($timestamp,$format="Y-m-d H:i:s") {
	return gmdate($format,($timestamp+$GLOBALS['_J']['config']['timezone']*3600));
}

function cut_str($string, $length, $dot = ' ...')
{
	if(strlen($string) <= $length) {
		return $string;
	}

	
	$strcut = '';
	if(strtolower($GLOBALS['_J']['charset']) == 'utf-8') {
		$n = $tn = $noc = 0;
		while($n < strlen($string)) {
			$t = ord($string[$n]);
			if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1; $n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 2;
			} elseif(224 <= $t && $t < 239) {
				$tn = 3; $n += 3; $noc += 2;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 2;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 2;
			} elseif($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 2;
			} else {
				$n++;
			}

			if($noc >= $length)
			{
				break;
			}
		}
		if($noc > $length) {
			$n -= $tn;
		}

		$strcut = substr($string, 0, $n);
	} else {
		for($i = 0; $i < $length; $i++) {
			$strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
		}
	}

	
	return $strcut.$dot;
}
function cutstr($string,$length,$dot=''){Return cut_str($string,$length,$dot);};

function strip_selected_tags(&$str,$disallowable="<script><iframe><style><link>")
{
	$disallowable=trim(str_replace(array(">","<"),array("","|"),$disallowable),'|');
	$str=str_replace(array('&lt;', '&gt;'),array('<', '>'),$str);
	$str=preg_replace("~<({$disallowable})[^>]*>(.*?<\s*\/(\\1)[^>]*>)?~is",'',$str);
	return $str;
}

function page($total_record, $per_page_num,$url='', $_config = array(), $per_page_nums = "")
{
	if(true===IN_JISHIGOU_INDEX || true===IN_JISHIGOU_AJAX){
		global $rewriteHandler;
	}

	$sys_config = ConfigHandler::get();
		if(true === IN_JISHIGOU_ADMIN && isset($sys_config['total_page_default']))
	{
		unset($sys_config['total_page_default']);
	}

	$result = array();

	$total_record = intval($total_record);
	$per_page_num = intval($per_page_num);
	if($per_page_num < 1) $per_page_num = 10;
	$config['total_page'] = max(0,(int) (isset($_config['total_page']) ? $_config['total_page'] : $sys_config['total_page_default']));	$config['page_display'] = isset($_config['page_display']) ? (int) $_config['page_display'] : 5;	$config['char'] = isset($_config['char']) ? (string) $_config['char'] : ' ';	$config['url_postfix'] = isset($_config['url_postfix']) ? (string) $_config['url_postfix'] : '';	$config['extra'] = isset($_config['extra']) ? (string) $_config['extra'] : '';	$config['idencode'] = (bool) $_config['idencode'];	$config['var'] = isset($_config['var']) ? (string) $_config['var'] : 'page';	$config['return'] = isset($_config['return']) ? (string) $_config['return'] : 'html';
	extract($config);

	$total_page = ceil($total_record / $per_page_num);
	if($config['total_page']>1 && $total_page > $config['total_page'])
	{
		$total_page = $config['total_page'];
	}

	$result['total_record'] = $total_record;
	$result['total_page'] = $total_page;
	$current_page=$_GET[$var]?$_GET[$var]:$_POST[$var];
	$current_page = max(1,(int) ((true == $idencode) ? iddecode($current_page) :$current_page));
	$current_page = ($total_page > 0 && $current_page > $total_page) ? $total_page : $current_page;
	$result['current_page'] = $current_page;
	$result['title_postfix'] = $current_page > 1 ? "_第{$current_page}页" : "";
	$result['offset'] = (int) (($current_page - 1) * $per_page_num);

	$result['limit'] = " LIMIT ".$result['offset'].",{$per_page_num} ";

	if(isset($result[$return])) return $result[$return];

	if('' == $url)
	{
		$request = count($_POST) ? array_merge($_GET,$_POST) : $_GET;
		$query_string = '';
		foreach($request as $_var => $_val)
		{
			if(is_string($_val) && $var!==$_var) $query_string .= "&{$_var}=" . urlencode($_val);
		}
		$url = '?'.($result['query_string'] = trim($query_string,'&'));
	}

	$p_val = "V01001page10010V";
	if('/#'!=$url) {
		$url = ('' == $url) ? "?$var={$p_val}" : (($url_no_page = (false !== strpos($url,"&{$var}=") ? preg_replace("/\&?{$var}\=[^\&]*/i",'',$url) : $url)) . "&{$var}={$p_val}");
		if($rewriteHandler)
		{
			$url_no_page = $rewriteHandler->formatURL($url_no_page,false);
			$url=$rewriteHandler->formatURL($url,false);
		}
	} else {
		$url_no_page = $url;
	}
	$result['url'] = $url;

	if(isset($result[$return])) return $result[$return];

	$html = '';
	if($total_record > $per_page_num)
	{
		$halfper = (int) ($config['page_display'] / 2);

		$html=($current_page - 1 >= 1) ? "\n<a href='{$url_no_page}{$url_postfix}' title=1 {$extra}>首页</a>{$char}\n<a href='".(1 == ($previous_page = ($current_page - 1)) ? $url_no_page : str_replace($p_val,(true===$idencode?idencode($previous_page):$previous_page),$url))."{$url_postfix}' title=$previous_page {$extra}>上一页</a>{$char}" : "首页{$char}上一页{$char}";

		for ($i=$current_page-$halfper,$i>0 or $i=1,$j=$current_page + $halfper,$j<$total_page or $j=$total_page;$i<=$j;$i++) {
			$html.=($i==$current_page)?"\n<B>".($i)."</B>{$char}":"\n<a href='".(1 == $i ? $url_no_page : str_replace($p_val,(true===$idencode?idencode($i):$i),$url))."{$url_postfix}' title=$i {$extra}>".($i)."</a>{$char}";
		}

		$html.=(($next_page=($current_page + 1)) > $total_page)?"下一页{$char}尾页":"\n<a href='".str_replace($p_val,(true===$idencode?idencode($next_page):$next_page),$url)."{$url_postfix}' title=$next_page {$extra}>下一页</a>{$char}\n<a href='".str_replace($p_val,(true===$idencode?idencode($total_page):$total_page),$url)."{$url_postfix}' title=$total_page {$extra}>尾页</a>";

		if(!empty($per_page_nums))
		{
			$per_page_num_list=is_array($per_page_nums)?$per_page_nums:explode(" ",$per_page_nums);
			$current_url=str_replace($p_val,(true===$idencode?idencode($current_page):$current_page),$url).$url_postfix;
			$pn_postfix=$rewriteHandler?$rewriteHandler->argSeparator."pn".$rewriteHandler->varSeparator:"&pn=";
			$per_page_num_select="<select name='per_page_num' onchange=\"window.location='{$current_url}{$pn_postfix}'+this.value\">";
			foreach ($per_page_num_list as $_per_page_num)
			{
				$selected=$_per_page_num==$per_page_num?"selected":"";
				$per_page_num_select.="<option value={$_per_page_num} $selected>{$_per_page_num}";
			}
			$per_page_num_select.="</select>";
		}
		else {
			$per_page_num_select="<i>{$per_page_num}</i>";
		}

		$html ="<div id='page'> 当前<i>{$current_page}</i>/共<i>{$total_page}</i>页 {$html} 每页显示${per_page_num_select}条/共<i>{$total_record}</i>条</div>";
}
$result['html'] = $html;

if(isset($result[$return])) return $result[$return];

return $result;
}


function strexists($haystack, $needle)
{
	return !(strpos($haystack, $needle) === FALSE);
}


function makethumb($srcfile,$dstfile,$thumbwidth,$thumbheight,$maxthumbwidth=0,$maxthumbheight=0,$src_x=0,$src_y=0,$src_w=0,$src_h=0, $thumb_cut_type=0) {
		if (!is_file($srcfile)) {
		return '';
	}

		$tow = (int) $thumbwidth;
	$toh = (int) $thumbheight;
	if($tow < 30) {
		$tow = 30;
	}
	if($toh < 30) {
		$toh = 30;
	}

	$make_max = 0;
	$maxtow = (int) $maxthumbwidth;
	$maxtoh = (int) $maxthumbheight;
	if($maxtow >= 300 && $maxtoh >= 300)
	{
		$make_max = 1;
	}

		$im = '';
	if(false != ($data = getimagesize($srcfile))) {
		if($data[2] == 1) {
			$make_max = 0;			if(function_exists("imagecreatefromgif")) {
				$im = imagecreatefromgif($srcfile);
			}
		} elseif($data[2] == 2) {
			if(function_exists("imagecreatefromjpeg")) {
				$im = imagecreatefromjpeg($srcfile);
			}
		} elseif($data[2] == 3) {
			if(function_exists("imagecreatefrompng")) {
				$im = imagecreatefrompng($srcfile);
			}
		}
	}
	if(!$im) return '';

	$srcw = ($src_w ? $src_w : imagesx($im));
	$srch = ($src_h ? $src_h : imagesy($im));

	$towh = $tow/$toh;
	$srcwh = $srcw/$srch;
	if($towh <= $srcwh) {
		$ftow = $tow;
		$ftoh = round($ftow*($srch/$srcw),2);
	} else {
		$ftoh = $toh;
		$ftow = round($ftoh*($srcw/$srch),2);
	}


	if($make_max) {
		$maxtowh = $maxtow/$maxtoh;
		if($maxtowh <= $srcwh) {
			$fmaxtow = $maxtow;
			$fmaxtoh = round($fmaxtow*($srch/$srcw),2);
		} else {
			$fmaxtoh = $maxtoh;
			$fmaxtow = round($fmaxtoh*($srcw/$srch),2);
		}

		if($srcw <= $maxtow && $srch <= $maxtoh) {
			$make_max = 0;		}
	}


	$maxni = '';
	if($srcw >= $tow || $srch >= $toh) {
		if(function_exists("imagecreatetruecolor") && function_exists("imagecopyresampled") && ($ni = imagecreatetruecolor($ftow, $ftoh))) {
			imagecopyresampled($ni, $im, 0, 0, $src_x, $src_y, $ftow, $ftoh, $srcw, $srch);
						if($make_max && ($maxni = imagecreatetruecolor($fmaxtow, $fmaxtoh))) {
				imagecopyresampled($maxni, $im, 0, 0, $src_x, $src_y, $fmaxtow, $fmaxtoh, $srcw, $srch);
			}
		} elseif(function_exists("imagecreate") && function_exists("imagecopyresized") && ($ni = imagecreate($ftow, $ftoh))) {
			imagecopyresized($ni, $im, 0, 0, $src_x, $src_y, $ftow, $ftoh, $srcw, $srch);
						if($make_max && ($maxni = imagecreate($fmaxtow, $fmaxtoh))) {
				imagecopyresized($maxni, $im, 0, 0, $src_x, $src_y, $fmaxtow, $fmaxtoh, $srcw, $srch);
			}
		} else {
			return '';
		}
		if(function_exists('imagejpeg')) {
			imagejpeg($ni, $dstfile, 100);
						if($make_max && $maxni) {
				imagejpeg($maxni, $srcfile, 100);
			}
		} elseif(function_exists('imagepng')) {
			imagepng($ni, $dstfile);
						if($make_max && $maxni) {
				imagepng($maxni, $srcfile);
			}
		}
		imagedestroy($ni);
		if($make_max && $maxni) {
			imagedestroy($maxni);
		}
	}
	imagedestroy($im);

	if(!is_file($dstfile)) {
		return '';
	} else {
		return $dstfile;
	}
}

function remove_xss($val) {
	$val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
	$search = 'abcdefghijklmnopqrstuvwxyz';
	$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$search .= '1234567890!@#$%^&*()';
	$search .= '~`";:?+/={}[]-_|\'\\';
	for ($i = 0; $i < strlen($search); $i++) {
		$val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); 		$val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); 	}
	$ra1 = ConfigHandler::get("xss",'tag');
	$ra2 = ConfigHandler::get("xss",'attribute');
	$ra = array_merge($ra1, $ra2);

	$found = true;
	while ($found == true) {
		$val_before = $val;
		for ($i = 0; $i < sizeof($ra); $i++) {
			$pattern = '/';
			for ($j = 0; $j < strlen($ra[$i]); $j++) {
				if ($j > 0) {
					$pattern .= '(';
					$pattern .= '(&#[xX]0{0,8}([9ab]);)';
					$pattern .= '|';
					$pattern .= '|(&#0{0,8}([9|10|13]);)';
					$pattern .= ')*';
				}
				$pattern .= $ra[$i][$j];
			}
			$pattern .= '/i';
			$replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2);
			$val = preg_replace($pattern, $replacement, $val);
			if ($val_before == $val) {
				$found = false;
			}
		}
	}
	return $val;
}


function filter(&$string, $verify=1, $replace=1,$shield=0)
{
	static $filter = null;

	$rets = array();

		$string=trim($string);
	if($string) {
		if(false!==strpos($string,'<')) {
			$string=strip_selected_tags($string,"<script><iframe><style><link><meta>");
			$string=remove_xss($string);
		}

		if($filter===null) {
			$filter = (array) ConfigHandler::get('filter');
		}

		if(!$filter['enable']) {
			return false;
		}

				if($replace && $filter['replace_list'])
		{
			foreach($filter['replace_list'] as $search=>$replace)
			{
				$strpos = jstrpos($string, $search);

				if($strpos!==false)
				{
					$string = str_replace($search, $replace, $string);
				}
			}
		}

				if(!empty($filter['keywords']))
		{
			if($filter['keyword_list']===null)
			{
				$filter['keyword_list'] =  explode("|",str_replace(array("\r\n","\r","\n","\t","\\|"),"|",trim($filter['keywords'])));
			}

			foreach ($filter['keyword_list'] as $keyword)
			{
				$strpos = jstrpos($string, $keyword);

				if($strpos!==false)
				{
					$rets['error'] = 1;
					$rets['type'] = 'filter';
					$rets['keyword'] = $keyword;
					$rets['msg'] = "含有禁止的内容 ".($filter['keyword_disable'] ? "" : " {$keyword} ")."，请修改后重新提交！";

					return $rets;
				}
			}
		}

				if($verify && $filter['verify_list'])
		{
			foreach($filter['verify_list'] as $keyword)
			{
				$strpos = jstrpos($string, $keyword);

				if($strpos!==false)
				{
					$rets['verify'] = 1;
					$rets['type'] = 'verify';
					$rets['keyword'] = $keyword;
					$rets['msg'] = "含审核内容 ".($filter['keyword_disable'] ? "" : " {$keyword} ")."需管理员审核后才会对外显示，<a href='index.php?mod=".MEMBER_ID."&type=my_verify'>点此查看</a>";

					return $rets;
				}
			}
		}

		if($shield && $shield!=0 && $filter['shield_list']){
			foreach($filter['shield_list'] as $keyword)
			{
				$strpos = jstrpos($string, $keyword);

				if($strpos!==false)
				{
					$rets['shield'] = 1;
					$rets['type'] = 'shield';
					$rets['keyword'] = $keyword;
					$rets['msg'] = "含有屏蔽的内容 ".($filter['keyword_disable'] ? "" : " {$keyword} ");

					return $rets;
				}
			}
		}
	}

	return false;
}

function request($action, $post=array(), &$error) {
	settype($post,"array");
	$post['system_env'] = ($post['system_env'] ? array_merge((array) $post['system_env'],(array) get_system_env()) : (array) get_system_env());
		$aclData = upsCtrl()->Account();
	$post['__acl__']['account'] = $aclData['account'];
	$post['__acl__']['token'] = $aclData['token'];
		$data='_POST='.urlencode(base64_encode(serialize($post)));
	$config = ConfigHandler::get();
	$charset = strtolower(str_replace('-', '', $config['charset']));
	$version = urlencode(SYS_VERSION);
	$server_url = base64_decode('aHR0cDovL3VwZGF0ZS5jZW53b3IuY29tL3NlcnZlci5yZXF1ZXN0LnBocA==')."?do=$action&pid=2&charset=$charset&iver=$version";
	$response=dfopen($server_url,5000000,$data);
	$error_msg=array(1=>"error_nodata",2=>"error_format",);
	if($response == "") {
		$result = $error_msg[($error = 1)];
	}else{
		$int = preg_match("/<DATA>(.*)<\/DATA>/s", $response, $m);
		if($int < 1){
			$result = $error_msg[($error = 2)];
		}else{
						if(false!==strpos($m[1],"\n")) {
				$m[1] = preg_replace('~\s+\w{1,10}\s+~','',$m[1]);
			}
			$response = unserialize(base64_decode($m[1]));
			$result = $response['data'];
			if($response['type']) {
				$error = 3;
			}
		}
	}

	return $result;
}
function get_system_env( )
{
	$e = array();
	$e['time'] = gmdate( "Y-m-d", time( ) );
	$e['os'] = PHP_OS;
	$e['ip'] = gethostbyname($_SERVER['SERVER_NAME']) or ($e['ip'] = getenv( "SERVER_ADDR" )) or ($e['ip'] = getenv('LOCAL_ADDR'));
	$e['sapi'] = php_sapi_name( );
	$e['host'] = strtolower(getenv('HTTP_HOST') ? getenv('HTTP_HOST') : $_SERVER['HTTP_HOST']);
	$e['path'] = substr(dirname(__FILE__),0,-17);
	$e['cpu'] = $_ENV['PROCESSOR_IDENTIFIER']."/".$_ENV['PROCESSOR_REVISION'];
	$e['name'] = $_ENV['COMPUTERNAME'];
	if(defined('SYS_VERSION')) $e['sys_version']=SYS_VERSION;
	if(defined('SYS_BUILD')) $e['sys_build']=SYS_BUILD;
	$sys_conf = ConfigHandler::get();
	if($sys_conf['site_name']) $e['sys_name'] = $sys_conf['site_name'];
	if($sys_conf['site_admin_email']) $e['sys_email'] = $sys_conf['site_admin_email'];
	if($sys_conf['site_url']) $e['sys_url'] = $sys_conf['site_url'];
	if($sys_conf['charset']) $e['sys_charset'] = $sys_conf['charset'];

	return get_system_count($e);
}
function get_system_count($data) {
	$cache_id = 'misc/system_count';
	if(false === ($cdata = cache_file('get', $cache_id))) {
		$ctbs = array('api_oauth2_token', 'app', 'buddys', 'cache', 'event', 'event_member', 'failedlogins', 'force_out', 'group', 'invite', 'live', 'log', 'medal', 'media', 'members', 'notice', 'output', 'plugin', 'pms', 'qqwb_bind_info', 'qun', 'qun_category', 'qun_user', 'report', 'robot', 'sessions', 'share', 'site', 'sms_client_user', 'tag', 'talk', 'topic', 'topic_attach', 'topic_image', 'topic_music', 'topic_video', 'url', 'validate', 'vote', 'wall', 'xwb_bind_info');
		$cdata = array();
		foreach($ctbs as $ctb) {
			$TCT = 0;
			$sql = 'SELECT COUNT(1) AS `TCT` FROM ' . DB::table($ctb);
						$query = DB::query($sql, 'SILENT');
			if($query) {
				$row = DB::fetch($query);
				$TCT = $row['TCT'];
			}
			$cdata['count_' . $ctb] = $TCT;
		}
		$cdata['count_data_length'] = cache_file('get', 'misc/data_length');
		
		cache_file('set', $cache_id, $cdata, 86400);
	}
	return array_merge($data, $cdata);
}

function dfopen($url, $limit = 10485760 , $post = '', $cookie = '', $bysocket = false,$timeout=5,$agent="") {
	if(ini_get('allow_url_fopen') && !$bysocket && !$post) {
		$fp = @fopen($url, 'r');
		$s = $t = '';
		if($fp) {
			while ($t=@fread($fp,2048)) {
				$s.=$t;
			}
			fclose($fp);
		}
		if($s) {
			return $s;
		}
	}

	$return = '';
	$agent=$agent?$agent:"Mozilla/5.0 (compatible; Googlebot/2.1; +http:/"."/www.google.com/bot.html)";
	$matches = parse_url($url);
	$host = $matches['host'];
	$script = $matches['path'].($matches['query'] ? '?'.$matches['query'] : '').($matches['fragment'] ? '#'.$matches['fragment'] : '');
	$script = $script ? $script : '/';
	$port = !empty($matches['port']) ? $matches['port'] : 80;
	if($post) {
		$out = "POST $script HTTP/1.1\r\n";
		$out .= "Accept: */"."*\r\n";
		$out .= "Referer: $url\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out .= "Accept-Encoding: none\r\n";
		$out .= "User-Agent: $agent\r\n";
		$out .= "Host: $host\r\n";
		$out .= 'Content-Length: '.strlen($post)."\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cache-Control: no-cache\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
		$out .= $post;
	} else {
		$out = "GET $script HTTP/1.1\r\n";
		$out .= "Accept: */"."*\r\n";
		$out .= "Referer: $url\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "Accept-Encoding: none\r\n";
		$out .= "User-Agent: $agent\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
	}

	$fp = jfsockopen($host, $port, $errno, $errstr, $timeout);

	if(!$fp) {
		return false;
	} else {
		fwrite($fp, $out);
		$return = '';
		while(!feof($fp) && $limit > -1) {
			$limit -= 8192;
			$return .= @fread($fp, 8192);
			if(!isset($status)) {
				preg_match("|^HTTP/[^\s]*\s(.*?)\s|",$return, $status);
				$status=$status[1];
				if($status!=200) {
					return false;
				}
			}
		}
		fclose($fp);
				preg_match("/^Location: ([^\r\n]+)/m",$return,$match);
		if(!empty($match[1]) && $location=$match[1]) {
			if(strpos($location,":/"."/")===false) {
				$location=dirname($url).'/'.$location;
			}
			$args=func_get_args();
			$args[0]=$location;
			return call_user_func_array("dfopen",$args);
		}
		if(false!==($strpos = strpos($return, "\r\n\r\n"))) {
			$return = substr($return,$strpos);
			$return = preg_replace("~^\r\n\r\n(?:[\w\d]{1,8}\r\n)?~","",$return);
			if("\r\n\r\n"==substr($return,-4)) {
				$return = preg_replace("~(?:\r\n[\w\d]{1,8})?\r\n\r\n$~","",$return);
			}
		}

		return $return;
	}
}

function str_exists($haystack,$needle)
{
	$arg_list = func_get_args();
	while(($needle=$arg_list[++$i])!==null)
	{
		if(strpos($haystack,$needle)!==false)return true;
	}
	return false;
}

function client_ip()
{
	if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
		$onlineip = getenv('HTTP_CLIENT_IP');
	} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
		$onlineip = getenv('HTTP_X_FORWARDED_FOR');
	} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
		$onlineip = getenv('REMOTE_ADDR');
	} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
		$onlineip = $_SERVER['REMOTE_ADDR'];
	}

	preg_match('/[\d\.]{7,15}/', $onlineip, $onlineipmatches);
	$onlineip = ($onlineipmatches[0] ? $onlineipmatches[0] : 'unknown');

	return $onlineip;
}

function face_path($uid) {
	$key = "ww"."w.jis"."higo"."u.c"."om"; 	$hash = md5($key."\t".$uid."\t".strlen($uid)."\t".$uid % 10);
	$path = $hash{$uid % 32} . "/" . abs(crc32($hash) % 100) . "/";

	return $path;
}
function jsg_uc_face_path($uid, $size = 'middle', $type = '') {
	$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';
	$uid = abs(intval($uid));
	$uid = sprintf("%09d", $uid);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);
	$typeadd = $type == 'real' ? '_real' : '';
	return $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_$size.jpg";
}
function face_get($users=array(), $type='small') {
	if(is_numeric($users)) {
		$users = jsg_member_info($users);
	}

	if(is_array($users)) {
		$uid = $users['uid'];
		$ucuid = $users['ucuid'];
		$face_url = $users['face_url'];
		$face = $users['face'];

		unset($users);
	}

		$file = $GLOBALS['_J']['site_url'] . '/images/noavatar.gif';
	if($uid < 1) {
		return $file;
	}

	$mods = array('share'=>1, 'show'=>1, 'output'=>1, );

		if(true === UCENTER_FACE && true === UCENTER) {
		if(null === $ucuid) {
			$ucuid = DB::result_first("select `ucuid` from ".TABLE_PREFIX."members where `uid`='$uid'");
		}

		if($ucuid > 0) {
			if('small'!=$type) {
								$type = 'big';
			}

						if(!isset($mods[$_GET['mod']]) && (TRUE===IN_JISHIGOU_INDEX || TRUE===IN_JISHIGOU_AJAX)) {
				$file = UC_API . '/data/avatar/' . jsg_uc_face_path($ucuid, $type, 'virtual');
							} else {
				$file = UC_API . "/avatar.php?uid={$ucuid}&type=virtual&size={$type}";
							}

			return $file;
		}
	}

		if(true === UCENTER_FACE && true === PWUCENTER)
	{
		if(null === $ucuid)
		{
			$ucuid = DB::result_first("select `ucuid` from ".TABLE_PREFIX."members where `uid`='$uid'");
		}

		if($ucuid > 0)
		{
			if('small'!=$type)
			{
				$type = 'middle';
			}

						$phpwind_config = ConfigHandler::get('phpwind');
			if($phpwind_config['face'] && $phpwind_config['enable']){
				Load::logic("topic_bbs");
				$PwBbsLogic = new TopicBbsLogic();
				$icon = $PwBbsLogic->get_pw_uicon($ucuid);
			}
			if($icon && (TRUE===IN_JISHIGOU_INDEX || TRUE===IN_JISHIGOU_AJAX))
			{
				$file = strncmp($icon,'http',4) == 0 ? $icon : UC_API . $icon;
			}
			else
			{
				$file = UC_API . '/images/face/none.gif';
			}

			return $file;
		}
	}

		$type = ('small' == $type ? 's' : 'b');
	$file = 'images/face/' . face_path($uid) . $uid . "_{$type}.jpg";


		if($GLOBALS['_J']['config']['ftp_on']) {
		if($face && null === $face_url) {
			$face_url = DB::result_first("select `face_url` from ".TABLE_PREFIX."members where `uid`='$uid'");
		}
	} else {
		if(!isset($mods[$_GET['mod']]) && (TRUE===IN_JISHIGOU_INDEX || TRUE===IN_JISHIGOU_AJAX)) {
			;
		} else {
			if(!file_exists(ROOT_PATH . $file)) {
				$file = 'images/noavatar.gif';
			}
		}
	}

	if(!$face_url) {
		$face_url = $GLOBALS['_J']['site_url'];
	}

	$file = ($face_url . "/" . $file);

	return $file;
}

function topic_image($id,$type='small',$relative=true)
{
	$type = ('photo' == $type ? 'p' : ('small' == $type ? 's' : 'o'));
	$file = 'images/topic/' . face_path($id) . $id . "_{$type}.jpg";
	if($relative)
	{
		$file = RELATIVE_ROOT_PATH . $file;
	}
	else
	{
		static $sys_config=null;
		if(is_null($sys_config)) {
			$sys_config = ConfigHandler::get();
		}

				if($sys_config['ftp_on'])
		{
			if(!($site_url = $GLOBALS['ftp_site_urls'][$id]))
			{
				$site_url = DB::result_first("select `site_url` from " . TABLE_PREFIX . "topic_image where `id`='$id'");

				$GLOBALS['ftp_site_urls'][$id] = $site_url;
			}
		}
		if(!$site_url)
		{
			$site_url = $sys_config['site_url'];
		}

		$file = $site_url . '/' . $file;
	}

	return $file;
}
function topic_attach($id,$str='file',$relative=true)
{
		$file = DB::result_first("select `$str` from " . TABLE_PREFIX . "topic_attach where `id`='$id'");
	if('file' == $str){
		if($relative)
		{
			$file = RELATIVE_ROOT_PATH . $file;
		}
		else
		{
			static $sys_config=null;
			if(is_null($sys_config)) {
				$sys_config = ConfigHandler::get();
			}

						if($sys_config['ftp_on'])
			{
				if(!($site_url = $GLOBALS['ftp_site_urls'][$id]))
				{
					$site_url = DB::result_first("select `site_url` from " . TABLE_PREFIX . "topic_attach where `id`='$id'");

					$GLOBALS['ftp_site_urls'][$id] = $site_url;
				}
			}
			if(!$site_url)
			{
				$site_url = $sys_config['site_url'];
			}

			$file = $site_url . '/' . $file;
		}
	}
	return $file;
}
function medal_image($id,$type='small')
{
	$type = ('small' == $type ? 'o' : 's');

	$file = RELATIVE_ROOT_PATH . 'images/medal/' . face_path($id) . $id . "_{$type}.jpg";

	return $file;
}


function get_safe_code($value) { return getSafeCode($value); }
function getSafeCode($value)
{
	if(is_numeric($value)) return $value;

	if(preg_match('~^[\x01-\x7f]+$~',$value)) return $value;

	$is_utf8 = 0;
	if(preg_match('~^([\x01-\x7f]|[\xc0-\xdf][\xa0-\xbf])+$~',$value))
	{
		;
	}
	else
	{
		if(preg_match('~^([\x01-\x7f]|[\xc0-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xf7][\x80-\xbf]{3}|[\xf8-\xfb][\x80-\xbf]{4}|[\xfc-\xfd][\x80-\xbf]{5})+$~',$value))
		{
			$is_utf8 = 1;
		}
	}

	if('utf-8'==strtolower($GLOBALS['_J']['charset']))
	{
		return ($is_utf8 ? $value : array_iconv("gbk", "utf-8", $value));

		
	}
	else
	{
		return ($is_utf8 ? array_iconv("utf-8", "gbk", $value) : $value);

		
	}
}
function follow_html($uid,$follow=0,$follow_me=0,$addhtml=true) {
	$html = "";
	if(MEMBER_ID>0 && MEMBER_ID!=$uid && $uid>0) {
		if ($follow) {
			if($follow_me) { 				$html = "<a href='javascript:void(0)' title=\"已互相关注，点击取消关注\" onclick=\"follow({$uid},'follow_{$uid}','');return false;\" class='follow_html2_3'></a>";
			} else {
								$html = "<a href='javascript:void(0)' title=\"已关注，点击取消关注\" onclick=\"follow({$uid},'follow_{$uid}','');return false;\" class='follow_html2_2'></a>";
			}
		} else {
						$html = "<a href='javascript:void(0)' title=\"加关注\" onclick=\"follow({$uid},'follow_{$uid}','add');return false;\" class='follow_html2_1'></a>";
		}

		if($addhtml) $html = "<span id='follow_{$uid}' class='follow_{$uid}'>{$html}</span>";
	}

	return $html;
}

function follow_html2($uid,$follow=0,$follow_me=0,$addhtml=true) {
	$html = "";
	if(MEMBER_ID>0 && MEMBER_ID!=$uid && $uid>0) {
		if ($follow) {
			if($follow_me) { 				$html = "<a href='javascript:void(0)' class=\"follow_html2_0\" title=\"已互相关注，点击取消关注\" onclick=\"follow({$uid},'follow_{$uid}','','xiao');return false;\"></a>";
			} else {
								$html = "<a href='javascript:void(0)' class=\"follow_html2_2\" title=\"已关注，点击取消关注\" onclick=\"follow({$uid},'follow_{$uid}','','xiao');return false;\"></a>";			
			}
		} else {
						$html = "<a href='javascript:void(0)' class=\"follow_html2_1\" title=\"加关注\" onclick=\"follow({$uid},'follow_{$uid}','add','xiao');return false;\"></a>";
		}

		if($addhtml) $html = "<span id='follow_{$uid}' class='follow_{$uid}'>{$html}</span>";
	}

	return $html;
}

function follow_department($did,$follow=0)
{
	$html = '';
	if(MEMBER_ID>0){
		if ($follow) {
			$html = "<a href='javascript:void(0)' class='follow_html_d' onclick=\"follow({$did},'follow_d_{$did}','','department');return false;\">√已关注</a>";
		}else{
			$html = "<a href='javascript:void(0)' class='follow_html_n' onclick=\"follow({$did},'follow_d_{$did}','add','department');return false;\">＋关注</a>";
		}
	}
	return $html;
}

function follow_channel($ch_id,$follow=0)
{
	$html = '';
	if(MEMBER_ID>0){
		if ($follow) {
			$html = "<a href='javascript:void(0)' class='follow_html_d' onclick=\"follow({$ch_id},'follow_c_{$ch_id}','','channel');return false;\">√已关注,取消</a>";
		}else{
			$html = "<a href='javascript:void(0)' class='follow_html_n' onclick=\"follow({$ch_id},'follow_c_{$ch_id}','add','channel');return false;\">＋关注本频道</a>";
		}
	}
	return $html;
}


function user_exp($user_level=0,$user_credits=0)
{
		$experience = ConfigHandler::get('experience');
	$exp_list = $experience['list'];

		$my_exp = $user_level;

		$my_credits = $user_credits;

		$next_exp = $my_exp + 1;

		$next_exp_credits = $exp_list[$next_exp]['start_credits'];

		$percent = round($my_credits/$next_exp_credits, 2);

		$exp_width = round($percent * 100);

		$liter_exp  = $next_exp_credits - $my_credits;

	$exp_arr = array(
					'exp_width' => $exp_width,

					'nex_exp_credit' => $liter_exp,

				'nex_exp_level' => $next_exp ,

	);



	return $exp_arr;
}

function my_date_format2($time) {
	if(empty($time)) return '';
	$t = TIMESTAMP - $time;
	$r = '';
	if ($t >= 3600) {
		$f = 'm月d日 H时i分';
		if($t >= 31536000 || date('Y', TIMESTAMP)>date('Y', $time)) {
			$f = 'Y年m月d日 H时i分';
		}
		$r = my_date_format($time, $f);
	} elseif ($t < 3600 && $t >= 60) {
		$r = floor($t / 60) . '分钟前';
	} elseif ($t < 60) {
		$r = '刚刚';
	}
	return $r;
}

function get_url_info($url, $title=null, $description=null) {
	$ret = Load::logic('url', 1)->info($url, $title, $description);

	return $ret;
}

function get_site_info($host, $name=null, $description=null) {
	$ret = Load::logic('site', 1)->info($host, $name, $description);

	return $ret;
}
function buddy_add($buddyid, $uid=0, $delete_if_exists=0) {
	$p = array(
		'buddyid' => (int) $buddyid,
		'uid' => (int) $uid,
	);
	$ret = Load::model('buddy')->add($p, $delete_if_exists);

	return $ret;
}
function buddy_del($buddyid, $uid) {
	$ret = Load::model('buddy')->del_info($buddyid, $uid);

	return $ret;
}


function is_blacklist($touid, $uid=0) {
	$ret = array();

	$touid = (int) $touid;
	$uid = (int) ($uid ? $uid : MEMBER_ID);

	if($uid > 0 && $touid > 0) {
		$ret = Load::model('buddy')->blacklist($touid, $uid);
	}

	return $ret;
}


function is_image($filename,$allow_types=array('gif'=>1,'jpg'=>1,'png'=>1,'bmp'=>1,'jpeg'=>1)) {
	clearstatcache();
	if(!is_file($filename)) {
		return false;
	}

	$imagetypes = array('1'=>'gif','2'=>'jpg','3'=>'png','4'=>'swf','5'=>'psd','6'=>'bmp','7'=>'tiff','8'=>'tiff','9'=>'jpc','10'=>'jp2','11'=>'jpx','12'=>'jb2','13'=>'swc','14'=>'iff','15'=>'wbmp','16'=>'xbm',);
	if(!$allow_types) {
		$allow_types = array('gif'=>1,'jpg'=>1,'png'=>1,'bmp'=>1,'jpeg'=>1);
	}
	$typeid = 0;
	$imagetype = '';
	if(function_exists('exif_imagetype')) {
		$typeid = exif_imagetype($filename);
	} elseif (function_exists('getimagesize')) {
		$_tmps = getimagesize($filename);
		$typeid = (int) $_tmps[2];
	} else {
		$str2 = Load::lib('io', 1)->ReadFile($filename, 2);
		if($str2) {
			$strInfo = unpack("C2chars", $str2);
			$fileTypes = array(7790=>'exe',7784=>'midi',8297=>'rar',255216=>'jpg',7173=>'gif',6677=>'bmp',13780=>'png',);
			$imagetype = $fileTypes[intval($strInfo['chars1'] . $strInfo['chars2'])];
		}
	}
	$file_ext = strtolower(trim(substr(strrchr($filename, '.'), 1)));
	if($typeid > 0) {
		$imagetype = $imagetypes[$typeid];
	} else {
		if(!$imagetype) {
			$imagetype = $file_ext;
		}
	}

	if($allow_types && $file_ext && $imagetype && isset($allow_types[$file_ext]) && isset($allow_types[$imagetype])) {
		return true;
	}

	return false;
}

function get_full_url($site_url='',$url='',$rewrite_url_postfix='')
{
	if(false !== strpos($url, ':/'.'/')) {
		return $url;
	}

	global $rewriteHandler;

	if(!$site_url) {
		$site_url = $GLOBALS['_J']['site_url'];
	} else {
		if('/'==substr($site_url,-1)) {
			$site_url = rtrim($site_url,'/');
		}
	}


	$full_url = "{$site_url}/{$url}";

	if($rewriteHandler && $url) {
		$url = ltrim($rewriteHandler->formatURL($url),'/');

		$full_url = (((false!==($_tmp_pos = strpos($site_url,'/',10))) ? substr($site_url,0,$_tmp_pos) : $site_url) . '/' . $url) . $rewrite_url_postfix;
	}

	return $full_url;
}

function get_invite_url($url='',$site_url='')
{
	return get_full_url($site_url,$url,'/');
}

function jurl($url, $site_url='', $rewrite_url_postfix='') {
	return get_full_url($site_url, $url, $rewrite_url_postfix);
}

function grayJpeg($imgname)
{
	$im = @imagecreatefromjpeg($imgname);

	if(!$im)
	{
		$im  = imagecreatetruecolor(150, 30);
		$bgc = imagecolorallocate($im, 255, 255, 255);
		$tc  = imagecolorallocate($im, 0, 0, 0);

		imagefilledrectangle($im, 0, 0, 150, 30, $bgc);
		imagestring($im, 10, 5, 5, 'Error loading ' . $imgname, $tc);
	}
	else{
		$img_width = ImageSX($im);
		$img_height = ImageSY($im);
		for ($y = 0; $y <$img_height; $y++) {
			for ($x = 0; $x <$img_width; $x++) {
				$gray = (ImageColorAt($im, $x, $y) >> 8) & 0xFF;
				imagesetpixel ($im, $x, $y, ImageColorAllocate ($im, $gray,$gray,$gray));
			}
		}
	}
	return $im;
}





class Load {
	function functions($name) {
		return @include_once(ROOT_PATH . 'include/function/' .$name.'.func.php');
	}
	function logic($name, $init=0) {
		if(!$init) {
			return @include_once(ROOT_PATH . 'include/logic/' .$name.'.logic.php');
		} else {
			static $S_logics = array();
			if(is_null($S_logics[$name])) {
				$class_name = '';
				if(false !== strpos($name, '_')) {
					$ns = explode('_', $name);
					foreach($ns as $n) {
						$class_name .= ucfirst($n);
					}
				} else {
					$class_name = ucfirst($name);
				}
				$class_name .= 'Logic';
				if(!(@include_once ROOT_PATH . 'include/logic/' . $name . '.logic.php') && !class_exists($class_name)) {
					exit('logic ' . $name . ' is not exists');
				}
				$S_logics[$name] = new $class_name();
			}
			return $S_logics[$name];
		}
	}
	function lib($name, $init=0) {
		if(!$init) {
			return @include_once(ROOT_PATH . 'include/lib/' .$name . '.han.php');
		} else {
			static $S_libs = array();
			if(is_null($S_libs[$name])) {
				$class_name = ucfirst($name) . 'Handler';
				if(!(@include_once ROOT_PATH . 'include/lib/' . $name . '.han.php') && !class_exists($class_name)) {
					exit('lib ' . $name . ' is not exists');
				}
				$S_libs[$name] = new $class_name();
			}
			return $S_libs[$name];
		}
	}
	function model($name) {
		static $S_models = array();
		if(is_null($S_models[$name])) {
			$class_name = str_replace(array('/'), '_', $name);
			if(!(@include_once ROOT_PATH . 'include/class/' . $name . '.class.php') && !class_exists($class_name)) {
				exit('model ' . $name . ' is not exists');
			}
			$S_models[$name] = new $class_name();
		}
		return $S_models[$name];
	}
}




class Obj
{
	function &Obj($name=null)
	{
		Return Obj::_share($name,$null,'get');
	}

	function &_share($name=null,&$mixed,$type='set')
	{
		static $_register=array();

		if($name==null)
		{
			Return $_register;
		}

		if('get' == $type)
		{
			if(isset($_register[$name]))
			{
				Return $_register[$name];
			}

			return null;
		}

		if('set' == $type)
		{
			$_register[$name]=&$mixed;
		}

		return true;
	}
	
	function register($name,&$obj)
	{
		Obj::_share($name,$obj,"set");
	}
	
	function &registry($name=null)
	{
		Return Obj::_share($name,$null,'get');
	}
	
	function isRegistered($name)
	{
		Return isset($_register[$name]);
	}
}

/**
 *[JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 配置的读取及写入
 *
 * @author 狐狸<foxis@qq.com>
 * @package www.jishigou.net
 */

class ConfigHandler
{
	function ConfigHandler() {
		;
	}

	function file($type=null) {
		if($type) $type = str_replace(array('.','\\','/',),'',$type);

		return ROOT_PATH . 'setting/' .($type===null?'settings':$type).'.php';
	}

	
	function get() {
		global $_J;

		$config = array();
		$type = null;
		$func_num_args = func_num_args();
		if(0 === $func_num_args || (($func_args = func_get_args()) && is_null(($type = $func_args[0])))) {
			if(!$_J['config']['auth_key']) {
				include(ConfigHandler::file());

				$_J['config'] = & $config;
			}
			return $_J['config'];
		} else {
			if(!isset($_J['config'][$type])) {
				if($_J['config']['setting_to_cache']) {
										$cache_id = 'setting/' . $type;
					if(false === ($config[$type] = cache_file('get', $cache_id))) {
						$row = DB::fetch_first("select * from ".DB::table('setting')." where `key`='$type'");					
						if(!$row) {
							@include(ConfigHandler::file($type));						
							DB::query("replace into ".DB::table('setting')." (`key`, `val`) values ('$type', '".(base64_encode(serialize($config[$type])))."')");
						} else {
							$config[$type] = unserialize(base64_decode($row['val']));
						}
						$config[$type] = ($config[$type] ? $config[$type] : array());
						cache_file('set', $cache_id, $config[$type]);
					}
														} else {
					@include(ConfigHandler::file($type));
				}			

				if($config && isset($config[$type])) {
					$_J['config'][$type] = & $config[$type];
				} elseif (isset(${$type})) {
					$_J['config'][$type] = & ${$type};
				}
			}

			if($func_num_args===1) {
				return $_J['config'][$type];
			}

			if(isset($_J['config'][$type])) {
				$path_str = '';
				foreach($func_args as $arg) {
					$arg = str_replace(array(';', '"', "'", ), '', $arg);
					$path_str.="['$arg']";
				}
				return eval('return $_J["config"]' . $path_str . ";");
			}
		}

		return null;
	}

	
	function set() {
		$func_args=func_get_args();
		$value=array_pop($func_args);
		$type=array_shift($func_args);

		ConfigHandler::backup($type);

		$file=ConfigHandler::file($type);
		$data = '';
		if($type===null) {
			if(true===DEBUG) {
				foreach($value as $k=>$v) {
					if(!$v) unset($value[$k]);
				}
			}
			if($value && $value['auth_key']) {
				ksort($value);
				$data="<?php \r\n  \r\n \$config=".var_export($value,true)."; \r\n ?>";
			}
		} else {
			global $_J;

			$config = ConfigHandler::get($type);
			$path_str = '';
			foreach($func_args as $arg) {
				$arg = str_replace(array(';', '"', "'", ), '', $arg);
				$path_str.="['$arg']";
			}
			eval($value===null?'unset($config'.$path_str.');':'$config'.$path_str.'=$value;');
			if(!is_null($config) && $_J['config'][$type] != $config) {
				$_J['config'][$type] = $config;
				$data="<?php \r\n  \r\n\$config['$type']=".var_export($config,true).";\r\n ?>";
				
				if($_J['config']['setting_to_cache']) {
										DB::query("replace into ".DB::table('setting')." (`key`, `val`) values ('$type', '".(base64_encode(serialize($config)))."')");
					cache_file('rm', 'setting/' . $type);
														}
			}
		}

		if($data) {
			$len = Load::lib('io', 1)->WriteFile($file, $data);
			if(false === $len) {
				die($file." 文件无法写入,请检查是否有可写权限。");
			}
		}

		return $len;
	}

		function backup($type=null) {
		if(null===$type) {
			include(ConfigHandler::file());
		} else {
			$config = ConfigHandler::get($type);
		}
		if($config) {
			$dir = ROOT_PATH . 'data/backup/setting/';
			if(!is_dir($dir)) {
				jmkdir($dir);
			}

			return Load::lib('io', 1)->WriteFile(($dir . (null===$type ? 'settings' : $type) . '.php'), '<?php $config'.(null===$type ? '' : "['$type']").' = '.var_export($config,true).'; ?>');
		}
	}

	
	function update($var, $val=null) {
		if(!$var) {
			return array();
		}
			
		$arrs = array();
		if(is_array($var)) {
			$arrs = $var;
		} else {
			$arrs[$var] = $val;
		}
			
		$update = 0;
		$config = array();
		include(ConfigHandler::file());
		if($config) {
			foreach($arrs as $var=>$val) {
				if(is_array($val)) {
					foreach ($val as $key=>$value) {
						if($config[$var][$key] != $value) {
							$update = 1;
							$config[$var][$key] = $value;
						}
					}
				} else {
					if($config[$var] != $val) {
						$update = 1;
						$config[$var] = $val;
					}
				}
			}
		}
		if($update && $config) {
			ConfigHandler::set($config);
		}
			
		return $config;
	}
}








function update_credits_by_action($action,$uid=0,$coef=1) {
	return Load::logic('credits', 1)->ExecuteRule($action,$uid,$coef);
}


function sina_weibo_init($sys_config=array()) {
	return init_item_func($sys_config, 'sina');
}
function sina_init($sys_config=array()) {
	return sina_weibo_init($sys_config);
}


function qqwb_init($sys_config=array())
{
	return init_item_func($sys_config, 'qqwb');
}


function yy_init($sys_config=array())
{
	return init_item_func($sys_config, 'yy');
}


function renren_init($sys_config=array())
{
	return init_item_func($sys_config, 'renren');
}


function kaixin_init($sys_config=array())
{
	return init_item_func($sys_config, 'kaixin');
}


function imjiqiren_init($sys_config=array())
{
	return init_item_func($sys_config, 'imjiqiren');
}


function sms_init($sys_config=array())
{
	return init_item_func($sys_config, 'sms');
}


function fjau_init($sys_config=array()) {
	return init_item_func($sys_config, 'fjau');
}


function init_item_func($sys_config = array(), $item)
{
	$func = "{$item}_enable";
	if(!function_exists($func))
	{
		Load::functions($item);

		clearstatcache();

		if(function_exists($func))
		{
			return $func($sys_config);
		}
	}
	else
	{
		return $func($sys_config);
	}

	return false;
}


function js_alert_output($alert_msg, $msg_func='MessageBox') {
	echo "<script language='javascript'>";
	if('alert' == $msg_func) {
		echo "alert('{$alert_msg}');";
	} elseif('show_message' == $msg_func) {
		echo "show_message('{$alert_msg}');";
	} else {
		echo "MessageBox('notice', '{$alert_msg}');";
	}
	echo "</script>";
	exit;
}
function js_alert_showmsg($alert_msg) {
	js_alert_output($alert_msg, 'show_message');
}



function jsg_setcookie($var, $value, $life = 0, $prefix = 1) {
	global $_J;

	$expire = 0;

	if($life) {
		$expire = time() + $life;
	}

	@setcookie(($prefix ? $_J['config']['cookie_prefix'] : '').$var, $value,
	$expire, ($_J['config']['cookie_path'] ? $_J['config']['cookie_path'] : '/'),
	($_J['config']['cookie_domain'] ? $_J['config']['cookie_domain'] : ''), ($_SERVER['SERVER_PORT'] == 443 ? 1 : 0));
}
function jsg_getcookie($var, $prefix = 1) {
	if($prefix) {
		global $_J;

		$var = $_J['config']['cookie_prefix'] . $var;
	}

	return $_COOKIE[$var];
}

function jsg_schedule($vars=array(), $type='', $uid=0)
{
	if(!function_exists('schedule_add'))
	{
		Load::functions('schedule');
	}

	if($vars)
	{
		return schedule_add($vars, $type, $uid);
	}
	else
	{
		return schedule_html();
	}
}

function ftpcmd($cmd, $arg1 = '', $arg2 = '') {
	static $ftp = null;
	$ftpon = ConfigHandler::get('ftp','on');
	if(!$ftpon) {
		return $cmd == 'error' ? -101 : 0;
	} elseif($ftp === null) {
		Load::lib('ftp');
		$ftp = FtpHandler::instance();
	}
	if(!$ftp->enabled) {
		if('error' != $cmd)
		{
			return 0;
		}
	} elseif($ftp->enabled && !$ftp->connectid) {
		$ftp->connect();
	}
	switch ($cmd) {
		case 'upload' : return $ftp->upload(ROOT_PATH . $arg1, $arg2 ? $arg2 : $arg1); break;
		case 'delete' : return $ftp->ftp_delete($arg1); break;
		case 'mkdir'  : return $ftp->ftp_mkdir($arg1); break;
		case 'close'  : return $ftp->ftp_close(); break;
		case 'error'  : return $ftp->error(); break;
		case 'object' : return $ftp; break;
		default       : return false;
	}
}




class DB
{

	
	function table($table)
	{
		$table_name = TABLE_PREFIX.$table;
		return $table_name;
	}

	
	function delete($table, $condition, $limit = 0, $unbuffered = true)
	{
		if(empty($condition)) {
			$where = '1';
		} elseif(is_array($condition)) {
			$where = DB::implode_field_value($condition, ' AND ');
		} else {
			$where = $condition;
		}
		$sql = "DELETE FROM ".DB::table($table)." WHERE $where ".($limit ? "LIMIT $limit" : '');
		return DB::query($sql, ($unbuffered ? 'UNBUFFERED' : ''));
	}

	
	function insert($table, $data, $return_insert_id = false, $replace = false, $silent = false)
	{

		$sql = DB::implode_field_value($data);

		$cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';

		$table = DB::table($table);
		$silent = $silent ? 'SILENT' : '';

		$return = DB::query("$cmd $table SET $sql", $silent);

		return $return_insert_id ? DB::insert_id() : $return;

	}

	
	function update($table, $data, $condition, $unbuffered = false, $low_priority = false)
	{
		$sql = DB::implode_field_value($data);
		$cmd = "UPDATE ".($low_priority ? 'LOW_PRIORITY' : '');
		$table = DB::table($table);
		$where = '';
		if(empty($condition)) {
			$where = '1';
		} elseif(is_array($condition)) {
			$where = DB::implode_field_value($condition, ' AND ');
		} else {
			$where = $condition;
		}
		$res = DB::query("$cmd $table SET $sql WHERE $where", $unbuffered ? 'UNBUFFERED' : '');
		return $res;
	}

	
	function implode_field_value($array, $glue = ',')
	{
		$sql = $comma = '';
		foreach ($array as $k => $v) {
						$sql .= $comma."`$k`='$v'";
			$comma = $glue;
		}
		return $sql;
	}

		function insert_id()
	{
				return DB::_execute('Insert_ID');
	}

	
	function fetch($resourceid, $type = 'assoc')
	{
		return DB::_execute('GetRow', $resourceid, $type);
	}

	
	function fetch_first($sql)
	{
		return DB::_execute('FetchFirst', $sql);
	}

	function fetch_all($sql, $keyfield='') {
		return DB::_execute('FetchAll', $sql, $keyfield);
	}

	function result($resourceid, $row = 0)
	{
		return DB::_execute('result', $resourceid, $row);
	}

	function result_first($sql)
	{
		$query = DB::query($sql);
		return DB::result($query);
	}

	function query($sql, $type = '')
	{
		return DB::_execute('Query', $sql, $type);
	}

	function num_rows($resourceid)
	{
		return DB::_execute('GetNumRows', $resourceid);
	}

	function affected_rows()
	{
		return DB::_execute('AffectedRows');
	}

	function free_result($query)
	{
		return DB::_execute('FreeResult', $query);
	}

	function error()
	{
		return DB::_execute('GetLastErrorString');
	}

	function errno() {
		return DB::_execute('GetLastErrorNo');
	}

	function _execute($cmd , $arg1 = '', $arg2 = '') {
		static $db=null;
		if(empty($db)) $db = & DB::object();
		if ($cmd == 'GetRow') {
			$res = $arg1->GetRow($arg2);
		} else if ($cmd == 'result') {
			$res = $arg1->result($arg2);
		} else if ($cmd == 'GetNumRows') {
			$res = $arg1->GetNumRows();
		} else if ($cmd == 'FreeResult') {
			$res = $arg1->FreeResult();
		} else {
			$res = $db->$cmd($arg1, $arg2);
		}
		return $res;
	}

	function &object() {
		static $db=null;
		if(empty($db)) {
			$db = & Obj::registry('DatabaseHandler');
			if (empty($db)) {
								$sys_config = ConfigHandler::get();
				include_once ROOT_PATH . 'include/db/database.db.php';
				include_once ROOT_PATH . 'include/db/mysql.db.php';
				$db = new MySqlHandler($sys_config['db_host'],$sys_config['db_port']);
				$db->Charset($sys_config['charset']);
				$db->DoConnect($sys_config['db_user'],$sys_config['db_pass'],$sys_config['db_name'],$sys_config['db_persist']);
				Obj::register('DatabaseHandler',$db);
			}
		}
		return $db;
	}

	
	function checkquery($sql) {
		return DB::_execute('CheckQuery');
	}
}


function template($tpl_name)
{
	static $tpl;
	if(empty($tpl)) {
		$tpl = & Obj::registry('TemplateHandler');
		if (empty($tpl)) {
			$sys_config = ConfigHandler::get();
			Load::lib('template');
			$tpl = new TemplateHandler($sys_config);
			Obj::register('TemplateHandler', $tpl);
		}
		if(empty($tpl)) {
			exit("Template init fail!");
		}
	}
	$path = $tpl->Template($tpl_name);
	return $path;
}


function get_list($table_name, $parma)
{
	$where_sql = ' 1 ';
	$order_sql = ' ';
	$limit_sql = ' ';

	if (empty($parma['field'])) {
		$field = ' * ';
	} else {
		$field = $parma['field'];
	}

	if (!empty($parma['where'])) {
		$where_sql .= " AND {$parma['where']} ";
	}

	if (!empty($parma['order'])) {
		$order_sql = " ORDER BY {$parma['order']} ";
	}

	if (!empty($parma['limit'])) {
		$limit_sql = " LIMIT {$parma['limit']} ";
	}

	$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table($table_name)." WHERE {$where_sql}");
	$keyword_ary = array();
	if ($count) {
				if ($parma['page']) {
			$_config = array(
				'return' => 'array',
			);
			$page_ary = page($count, $parma['perpage'], $parma['page_url'], $_config);
			$limit_sql = $page_ary['limit'];
		}
		$query = DB::query("SELECT {$field} FROM ".DB::table($table_name)." WHERE {$where_sql} {$order_sql} {$limit_sql}");
		while ($value = DB::fetch($query)) {
			$keyword_ary[] = $value;
		}
		$r = array('list' => $keyword_ary, 'count' => $count);

		if ($parma['page']) {
			$r['page'] = $page_ary['html'];
		}
		return $r;
	}
	return false;
}




function unfilterHtmlChars($str)
{
	return str_replace(array('&lt;', '&gt;'), array('<', '>'), $str);
}


function getstr($string, $length, $in_slashes=0, $out_slashes=0,  $html=0)
{
	$string = trim($string);
	if($in_slashes) {
		$string = jstripslashes($string);
	}
	if($html < 0) {
		$string = preg_replace("/(\<[^\<]*\>|\r|\n|\s|\[.+?\])/is", ' ', $string);
	} elseif ($html == 0) {
		$string = htmlspecialchars($string);
	}

	if($length) {
		$string = cut_str($string, $length);
	}
	filter($string);
	if($out_slashes) {
		$string = jaddslashes($string);
	}
	return trim($string);
}

function jstrtotime($string)
{
	$time = '';
	if($string) {
		$time = strtotime($string);
		$timezone = $GLOBALS['_J']['config']['timezone'];
		if(gmdate('H:i', TIMESTAMP + $timezone * 3600) != date('H:i', TIMESTAMP)) {
			$time = $time - $timezone * 3600;
		}
	}
	return $time;
}

function url_implode($gets)
{
	$arr = array();
	foreach ($gets as $key => $value) {
		if($value) {
			$arr[] = $key.'='.urlencode(jstripslashes($value));
		}
	}
	return implode('&', $arr);
}

function jimplode($array)
{
	if(!empty($array)) {
		return "'".implode("','", is_array($array) ? $array : array($array))."'";
	} else {
		return 0;
	}
}



function chk_follow($uid, $buddyid) {
	$info = Load::model('buddy')->info($buddyid, $uid);

	return ($info ? 1 : 0);
}


function mk_time_select($type = 'hour', $def_val = false,$name='')
{
	$html = '';
	$time = 0;
	if (defined(TIMESTAMP)) {
		$time = TIMESTAMP;
	} else {
		$time = time();
	}

	if ($type == 'hour') {
		$range = 24;
		if ($def_val === false) {
			$def_val = my_date_format($time, 'H');
		}
	} else if ($type == 'min') {
		$range = 60;
		if ($def_val === false) {
			$def_val = my_date_format($time, 'i');
		}
	} else {
		return '';
	}

	$name = $name ? $name : $type;
	$html = "<select name=\"{$name}\" id=\"{$name}\" defaultvalue=\"{$def_val}\">";
	for ($i=0;$i<$range;++$i) {
		$selected = '';
		$value = $i;
		if (strlen($value) < 2) {
			$value = '0'.$value;
		}
		if ($value == $def_val) {
			$selected = 'selected="selected"';
		}
		$html .= " <option value=\"{$value}\" {$selected} >{$value}</option>";
	}
	$html .= '</select>';
	return $html;
}


function get_buddyids($uid, $uptime_limit=0) {
	$ret = Load::model('buddy')->get_buddyids($uid, $uptime_limit);

	return $ret;
}


function table_exists($table_name)
{
	$row = DB::fetch_first("SHOW TABLES LIKE '".DB::table($table_name)."'");
	if (empty($row)) {
		return false;
	}
	return true;
}



function jsg_json_encode($value)
{
	if(!class_exists('servicesJSON')) {
		Load::lib('servicesJSON');
	}
	$json = new servicesJSON(0, false);
	return $json->encode($value);
}

function jsg_json_decode($value)
{
	if(!class_exists('servicesJSON')) {
		Load::lib('servicesJSON');
	}
	$json = new servicesJSON(0, false);
	return $json->decode($value);
}


function json_error ($msg = '', $retval = null, $jqremote = false)
{
	$result = array("done" => false , "msg" => $msg);
	if (isset($retval)) $result["retval"] = $retval;

	json_header();
	$json = jsg_json_encode($result);
	if ($jqremote === false) {
		$jqremote = isset($_GET['jsoncallback']) ? trim($_GET['jsoncallback']) : false;
	}
	if ($jqremote) {
		$json = $jqremote . '(' . $json . ')';
	}
	echo $json;
	exit;
}

function js_show_login($msg='')
{
	echo "<script language='Javascript'>";
	echo "show_message('{$msg}',1);";
	echo "ShowLoginDialog();";
	echo "</script>";
	exit;
}

function json_result($msg = '', $retval = '', $jqremote = false)
{
	json_header();
	$json = jsg_json_encode(array("done" => true , "msg" => $msg , "retval" => $retval));
	if ($jqremote === false) {
		$jqremote = isset($_GET['jsoncallback']) ? trim($_GET['jsoncallback']) : false;
	}
	if ($jqremote) {
		$json = $jqremote . '(' . $json . ')';
	}
	echo $json;
	exit;
}


function json_header()
{
	ob_clean();

	@header("Cache-Control: no-cache, must-revalidate");
	@header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
}


function ajax_page($count, $perpage, $page, $js_code, $parma = '')
{
	$multi = '';
	$str_parma = '{}';
	if (!empty($parma)) {
		$str_parma = jsg_json_encode($parma);
	}

	if ($count > $perpage) {
		if ($page > 1) {
			$prev = $page - 1;
			$multi .= '<a href=\'javascript:;\' onclick=\''.$js_code.'('.$prev.','.$str_parma.')\'>上一页</a>';
		}

		if ($page * $perpage < $count) {
			$next = $page + 1;
			$multi .= '&nbsp;&nbsp;<a href=\'javascript:;\' onclick=\''.$js_code.'('.$next.','.$str_parma.')\'>下一页</a>';
		}
	}
	return $multi;
}


function autothumbnail($src_img, $dst_img, $w, $h)
{
	Load::lib('image');
	$im = new image();
	return $im->Thumb($src_img, $dst_img, $w, $h, 'fixwr');
}



if(!function_exists('json_encode'))
{
	function json_encode($value)
	{
		if(!class_exists('servicesJSON'))
		{
			Load::lib('servicesJSON');
		}
		$json = new servicesJSON();
		return $json->encode($value);
	}
}

if(!function_exists('json_decode'))
{
	function json_decode($json_value,$bool = false)
	{
		if(!class_exists('servicesJSON'))
		{
			Load::lib('servicesJSON');
		}
		$assoc = ($bool ? 16 : 32);
		$json = new servicesJSON($assoc);
		return $json->decode($json_value);
	}
}


function topic_type()
{
	$types = array(
		'first',
		'forward',
		'both',
	);
	return $types;
}


function get_topic_type($type = '')
{
	$topic_types = array(
		'first',
		'forward',
		'both',
	);

	$not_visible_topic_types = array(
		'reply',
		'qun',
		'vote',

	);

	if ($type == 'personal') {
		$topic_types[] = 'personal';
	} else if ($type == 'forward') {
		$topic_types[] = 'reply';
		$topic_types[] = 'qun';
	} else if ($type == 'sys_not_visible') {
		$topic_types = $not_visible_topic_types;
	}
	return $topic_types;
}


function get_def_follow_group()
{
	$g = array(
	1 => '现同事',
	2 => '好友',
	3 => '同行',
	4 => '其他',
	);
	return $g;
}







function mkseccode()
{
	$seccode = random(6, 1);
	$s = sprintf('%04s', base_convert($seccode, 10, 24));
	$seccode = '';
	$seccodeunits = 'BCEFGHJKMPQRTVWXY2346789';
	for($i = 0; $i < 4; $i++) {
		$unit = ord($s{$i});
		$seccode .= ($unit >= 0x30 && $unit <= 0x39) ? $seccodeunits[$unit - 0x30] : $seccodeunits[$unit - 0x57];
	}
	return $seccode;
}

function ckseccode($seccode)
{
	$check = true;
	$c = jsg_getcookie('seccode');
	$cookie_seccode = empty($c)?'':authcode($c, 'DECODE');
	if(empty($cookie_seccode) || strtolower($cookie_seccode) != strtolower($seccode)) {
		$check = false;
	}
	return $check;
}


$__TMP_OBJ_OF_UPS_CTRL = null;
function upsCtrl()
{
	global $__TMP_OBJ_OF_UPS_CTRL;
	if (is_null($__TMP_OBJ_OF_UPS_CTRL))
	{
		include_once(ROOT_PATH.'include/logic/ups.ctrl.moyo.php');
		$__TMP_OBJ_OF_UPS_CTRL = new xUpdateControlLogic();
	}
	return $__TMP_OBJ_OF_UPS_CTRL;
}

function jmkdir($dir, $mode = 0777, $makeindex = TRUE)
{
	if(!is_dir($dir)) {
		clearstatcache();
		jmkdir(dirname($dir));
		@mkdir($dir, $mode);
		if(!empty($makeindex)) {
			$ret = @touch($dir.'/index.html');
			@chmod($dir.'/index.html', 0777);
			return $ret;
		}
	}
	return true;
}

function process_url($content)
{
	if(false != strpos($content, ':/'.'/'))
	{
		$pattern = '~((?:https?\:\/\/)(?:[A-Za-z0-9\_\-]+\.)+[A-Za-z0-9]{1,4}(?:\:\d{1,6})?(?:\/[\w\d\/=\?%\-\&_\~\`\:\+\#\.]*(?:[^\;\@\[\]\<\>\'\"\n\r\t\s\x7f-\xff])*)?)~i';
		$replacement = '<a target="_blank" href="\\1">\\1</a>';

		$content = preg_replace($pattern, $replacement, $content);
	}

	return $content;
}

function sys_version($v = null) {
	$v = ($v ? $v : SYS_VERSION);

	return $v;

	$srp = strrpos($v, '.');
	if(false !== $srp) {
		$v = substr($v, 0, $srp);
	}

	return $v;
}

function writelog($file, $log) {
	$logdir = ROOT_PATH.'./data/log/';
	$file = dir_safe($file);
	$logfile = $logdir.$file.'.php';
	if(!is_dir($logdir)){
		jmkdir($logdir);
	}
	$log = is_array($log) ? $log : array($log);
	return Load::lib('io', 1)->WriteFile($logfile, '<?php $log='.var_export($log,'true').'?>');
}

function rewriteDisable() {
	global $rewriteHandler;
	$rewriteHandler = null;
}

function dir_safe($dir, $safe=1) {
	if($safe) {
		$search1 = array('..', '*', '?', '"', '<', '>', '|',  );
		$dir = str_replace($search1, '', $dir);
		$dir = str_replace($search1, '', $dir);
	}

	if(false !== strpos($dir, '/')) {
		$search2 = array('\\', '/./', '/'.'/'.'/'.'/', '/'.'/'.'/', '/'.'/', );
		$dir = str_replace($search2, '/', $dir);
		$dir = str_replace($search2, '/', $dir);
	}

	return $dir;
}

function str_safe($str) {
	$str = trim(strip_tags($str));
	if($str) {
		return htmlspecialchars(trim(str_replace(array('&gt;','<','&lt;','>','"',"'",'%3C','%3E','%22','%27','%3c','%3e'), '', $str)));
	}
	return '';
}
	
function jstrlen($str) {
	global $_J;
	
	$l = strlen($str);
	if(strtolower($_J['charset']) != 'utf-8') {
		return $l;
	}
	$count = 0;
	for($i = 0; $i < $l; $i++){
		$value = ord($str[$i]);
		if($value > 127) {
			$count++;
			if($value >= 192 && $value <= 223) $i++;
			elseif($value >= 224 && $value <= 239) $i = $i + 2;
			elseif($value >= 240 && $value <= 247) $i = $i + 3;
	    	}
    		$count++;
	}
	return $count;
}

function jerror($msg, $output='') {
	$rets = array(
		'error' => 1,
		'msg' => $msg,
	);
	if($output && in_array($output, array('exit', 'die', 'json_error'))) {
		$output($msg);
	} else {
		return $rets;
	}
}


if(!defined('JISHIGOU_GLOBAL_FUNCTION')) {
	define('JISHIGOU_GLOBAL_FUNCTION', true);

	if(!defined('IN_JISHIGOU')) {
		if(!defined('ROOT_PATH')) {
			define('ROOT_PATH', substr(dirname(__FILE__), 0, -17) . '/');
		}
		require_once ROOT_PATH . 'include/jishigou.php';
		$jishigou = new jishigou();
	}
}

#设置广告位
function SetADV($page,$op){
	if (!$GLOBALS['_J']['config']['ad_enable']) {
		echo '';
		return ;
	}
	$ad_list = $GLOBALS['_J']['config']['ad']['ad_list'][$page][$op];
	
	if ($op == 'header' || $op == 'footer') {
		$div_class="T_AD";
	} else if ($op == 'middle_right' || 'middle_right_top' == $op || 'middle_right_center' == $op){
		$div_class="R_AD";
	} else if ($op == 'middle_center1') {
		$div_class="L_AD";
	} else if ('middle_left_top'==$op || 'middle_left'==$op) {
		$div_class="Ir_AD";
	} else if ('middle' == $op) {
		$div_class="T_AD";
	}
	
	if($ad_list){
		foreach ($ad_list as $k=>$adv) {
			if($adv['ftime'] && $adv['ftime'] > TIMESTAMP){
				continue;
			}
			if($adv['ttime'] && $adv['ttime'] < TIMESTAMP){
				continue;
			}
			echo "<div class='$div_class'>".stripslashes($adv['html'])."</div>";
		}
	} else {
		echo '';
	}
	return '';
}

Load::functions('member');

#if NEDU
$__nedu_file = ROOT_PATH.'nedu/nedu.load.php';
is_file($__nedu_file) && require_once $__nedu_file;
#endif
?>