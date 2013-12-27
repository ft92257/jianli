<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename global.func.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-06-20 16:52:55 237727291 630598261 12732 $
 *******************************************************************/





if(!function_exists('jfsockopen'))
{
	function jfsockopen($hostname, $port, $errno, $errstr, $timeout)
	{
		$fp = false;

		if(function_exists('fsockopen'))
		{
			@$fp = fsockopen($hostname, $port, $errno, $errstr, $timeout);
		}
		elseif(function_exists('pfsockopen'))
		{
			@$fp = pfsockopen($hostname, $port, $errno, $errstr, $timeout);
		}
		
		return $fp;
	}
}

function createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP', 'MEMORY')) ? $type : 'MYISAM';
	$dbcharset = strtolower($dbcharset);
	if('utf-8' == $dbcharset) {
		$dbcharset = 'utf8';
	}
    
    $search = ' CHARACTER SET gbk COLLATE gbk_bin ';
    if(false!==strpos($sql, $search)) {
        if(mysql_get_server_info() <= '4.1') {
            $sql = str_replace($search, ' binary ', $sql);
        } else {
            if('gbk' != $dbcharset) {
                $sql = str_replace($search, " CHARACTER SET {$dbcharset} COLLATE {$dbcharset}_bin ", $sql);
            }
        }
    }
    
    $search = ' COMMENT ';
    if(false !== strpos($sql, $search)) {
    	$sql = preg_replace('~ COMMENT \'.*?\'~i', ' ', $sql);
    }
    
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
		(mysql_get_server_info() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=$dbcharset" : " TYPE=$type");
}

function dir_writeable($dir) {
	if(!is_dir($dir)) {
		mkdir($dir, 0777);
	}
	if(is_dir($dir)) {
		if($fp = @fopen("$dir/test.txt", 'w')) {
			fclose($fp);
			@unlink("$dir/test.txt");
			$writeable = 1;
		} else {
			$writeable = 0;
		}
	}
	return $writeable;
}

function dir_clear($dir_name)
{
	if(is_dir($dir_name) == false)Return false;
	$dir_handle = opendir($dir_name);
	while(($file = readdir($dir_handle)) !== false)
	{
		if($file != '.' and $file != "..")
		{
			if(is_dir($dir_name . '/' . $file))
			{
				dir_clear($dir_name . '/' . $file);
			}
			if(is_file($dir_name . '/' . $file))
			{
				@unlink($dir_name . '/' . $file);
			}
		}
	}
	closedir($dir_handle);
		Return true;
}
function instheader() {
	global $charset, $lang, $version;

	echo "<html><head>".
		"<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$charset\">".
		"<title>JishiGou Installation Wizard $version </title>".
		"<link rel=\"stylesheet\" type=\"text/css\" id=\"css\" href=\"install/style.css\"></head>".
		"<body bgcolor=\"#3A4273\" text=\"#000000\">".
		"<table width=\"95%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#FFFFFF\" align=\"center\"><tr><td>".
      		"<table width=\"98%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\"><tr>".
          	"<td class=\"install\" height=\"30\" valign=\"bottom\"><font color=\"#FF0000\">&gt;&gt;</font> JishiGou $version&nbsp;$lang[install_wizard] ".
          	"</td></tr><tr><td><hr noshade align=\"center\" width=\"100%\" size=\"1\"></td></tr>";
}

function instfooter() {
	global $version, $config;

	echo "<tr><td><hr noshade align=\"center\" width=\"100%\" size=\"1\"></td></tr>".
        	"<tr><td align=\"center\">".
            	"<b style=\"font-size: 11px\">Powered by <a href=\"http:/"."/JishiGou.net\" target=\"_blank\">JishiGou $version".
          	"</a> &nbsp; Copyright &copy; <a href='http:/'.'/www.cenwor.com' target='_blank'>Cenwor Inc.</a> 2005 - 2012</b><br /><br />".
          	"</td></tr></table></td></tr></table>".
		"</body></html>";
	echo "<div style='display:none;'>{$config['tongji']}</div>";
}

function instmsg($message, $url_forward = '') {
	global $lang, $msglang;

	instheader();

	$message = $msglang[$message] ? $msglang[$message] : $message;

	if($url_forward) {
		$message .= "<br /><br /><br /><a href=\"$url_forward\">$message</a>";
		$message .= "<script>setTimeout(\"redirect('$url_forward');\", 1250);</script>";
	} elseif(strpos($message, $lang['return'])) {
		$message .= "<br /><br /><br /><a href=\"javascript:history.go(-1);\" class=\"mediumtxt\">$lang[message_return]</a>";
	}

	echo 	"<tr><td style=\"padding-top:100px; padding-bottom:100px\"><table width=\"560\" cellspacing=\"1\" bgcolor=\"#000000\" border=\"0\" align=\"center\">".
		"<tr bgcolor=\"#3A4273\"><td width=\"20%\" style=\"color: #FFFFFF; padding-left: 10px\">$lang[error_message]</td></tr>".
  		"<tr align=\"center\" bgcolor=\"#E3E3EA\"><td class=\"message\">$message</td></tr></table></tr></td>";

	instfooter();
	exit;
}

function loginit($logfile) {
	global $lang;
	showjsmessage($lang['init_log'].' '.$logfile);
	$fp = @fopen('./forumdata/logs/'.$logfile.'.php', 'w');
	fwrite($fp, '<'.'?PHP exit(); ?'.">\n");
	fclose($fp);
	result(1, 1, 0);
}

function showjsmessage($message) {
	echo '<script type="text/javascript">showmessage(\''.addslashes($message).' \');</script>'."\r\n";
	flush();
	ob_flush();
}

function random($length) {
	$hash = '';
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	$max = strlen($chars) - 1;
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	for($i = 0; $i < $length; $i++) {
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}

function result($result = 1, $output = 1, $html = 1) {
	global $lang;

	if($result) {
		$text = $html ? '<font color="#0000EE">'.$lang['writeable'].'</font><br />' : $lang['writeable']."\n";
		if(!$output) {
			return $text;
		}
		echo $text;
	} else {
		$text = $html ? '<font color="#FF0000">'.$lang['unwriteable'].'</font><br />' : $lang['unwriteable']."\n";
		if(!$output) {
			return $text;
		}
		echo $text;
	}
}

function redirect($url) {

	echo "<script>".
		"function redirect() {window.location.replace('$url');}\n".
		"setTimeout('redirect();', 0);\n".
		"</script>";
	exit();

}

function runquery($sql) {
	global $lang, $dbcharset, $tablepre, $db,$config;

	$sql = str_replace(array("\r\n", "\r"), "\n", str_replace('`jishigou_', "`" . $config['db_table_prefix'], $sql));
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= (($query[0] == '#' || $query[0].$query[1] == '--') ? '' : $query . ' ');
		}
		$num++;
	}
	unset($sql);
	
	foreach($ret as $query) {
		$query = trim($query);
		if($query) {
			$_msg = '';
			$name = preg_replace("/(?:CREATE TABLE|REPLACE INTO|INSERT INTO)[\w\s`]*?([a-z0-9_]+)`?\s?.*/is", "\\1", $query);
			$w12 = trim(strtoupper(substr($query, 0, 12)));
			if($w12 == 'CREATE TABLE') {
				$query = createtable($query, $dbcharset);
				$db->query($query);
				$_msg = $lang['create_table'];				
			} else {
				$db->query($query);
				if(false !== strpos($w12, ' INTO')) {
					$_msg = $lang['db_insert'];
				}
			}
			if($name && $_msg) {
				showjsmessage($_msg.' '.$name.' ... '.$lang['succeed']);
			}
		}
	}
}

function setconfig($string) {
	if(!get_magic_quotes_gpc()) {
		$string = str_replace('\'', '\\\'', $string);
	} else {
		$string = str_replace('\"', '"', $string);
	}
	return $string;
}
function saveconfig($config,$file='./setting/settings.php',$var_name='$config')
{
	if(function_exists('var_export')==false) {
		die("PHP版本不能低于4.2.0");
	}
	if(is_writable($file)==false) {
		die("配置文件{$file}不可写，请修改其属性为0777");
	}
	ksort($config);
	$configfile="<?php\r\n$var_name=".var_export($config,true).";\r\n?>";
	$fp = @fopen($file, 'wb');
	if(!$fp) {
		die("配置文件{$file}不可写");
	}
	$len = fwrite($fp, trim($configfile));
	fclose($fp);
	if(!$len) {
		die("配置文件{$file}写入失败");
	}
    
        if(false !== strpos('/setting/',$file)) {
        @copy($file,str_replace('/setting/','/data/backup/setting/',$file));
    }
    
	return $len;
}

function _getaarrayrandval($arr) {
	$rnd_key = array_rand($arr);
	return $arr[$rnd_key];
}
function install_request($post=array(),&$error) {
	settype($post,"array");
	$post['system_env'] = install_get_system_env();
	$data='_POST='.urlencode(base64_encode(serialize($post)));
	$server_url = "http:/"."/www.jishigou.net/server.php";
	$response=@install_dfopen($server_url,5000000,$data);
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
function install_get_system_env( )
{
	$e = array();
	$e['time'] = gmdate( "Y-m-d", time( ) );
	$e['os'] = PHP_OS;
	$e['ip'] = gethostbyname($_SERVER['SERVER_NAME']) or ($e['ip'] = getenv( "SERVER_ADDR" )) or ($e['ip'] = getenv('LOCAL_ADDR'));
	$e['sapi'] = php_sapi_name( );
	$e['host'] = strtolower(getenv('HTTP_HOST') ? getenv('HTTP_HOST') : $_SERVER['HTTP_HOST']);
	$e['path'] = substr(dirname(__FILE__),0,-8);
	$e['cpu'] = $_ENV['PROCESSOR_IDENTIFIER']."/".$_ENV['PROCESSOR_REVISION'];
	$e['name'] = $_ENV['COMPUTERNAME'];
	if(defined('SYS_VERSION')) $e['sys_version']=SYS_VERSION;
	if(defined('SYS_BUILD')) $e['sys_build']=SYS_BUILD;	
	unset($config);
	include('./setting/settings.php');
	$sys_conf = $config;
	if($sys_conf['site_name']) $e['sys_name'] = $sys_conf['site_name'];
	if($sys_conf['site_admin_email']) $e['sys_email'] = $sys_conf['site_admin_email'];
	if($sys_conf['site_url']) $e['sys_url'] = $sys_conf['site_url'];

	return $e;
}
function install_dfopen($url, $limit = 10485760 , $post = '', $cookie = '', $bysocket = false,$timeout=2,$agent="") {
	if(ini_get('allow_url_fopen') && !$bysocket && !$post) {
		$fp = @fopen($url, 'r');
		$s = $t = '';
		if($fp) {
			while ($t=fread($fp,2048)) {
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
		$out .= "Accept-Encoding:\r\n";
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
?>