<?php
/**
 * JishiGouAPI client
 *
 * @package
 * @author www.jishigou.com
 * @copyright foxis
 * @version 2011
 * @access public
 */
define('JISHIGOU_API_DEBUG', false);
class JishiGouAPI
{

	var $Http='';
	var $SiteUrl='';
	var $AppKey='';
	var $AppSecret='';
	var $ApiUsername='';
	var $ApiPassword='';
	var $ApiOutput='';
	var $ApiCharset='';
	var $ApiAuthType='';

	function JishiGouAPI($site_url,$app_key,$app_secret,$api_username='',$api_password='',$api_output='json',$api_charset='utf-8',$api_auth_type='jauth2'){
		$this->Http=new JishiGouAPI_Http_Client((true===JISHIGOU_API_DEBUG) ? 1 : 0);
		$this->SetSiteUrl($site_url);
		$this->SetAppKey($app_key);
		$this->SetAppSecret($app_secret);
		$this->SetApiUsername($api_username);
		$this->SetApiPassword($api_password);
		$this->SetApiOutput($api_output);
		$this->SetApiCharset($api_charset);
		$this->SetApiAuthType((defined('JISHIGOU_API_AUTH_TYPE') ? JISHIGOU_API_AUTH_TYPE : $api_auth_type));
	}

	function Test(){
		$params=array(
			'mod'=>'test',
			'code'=>'test',
		);
		return $this->Request($params);
	}

	function GetUserInfo($uid=null){
		$params=array(
			'mod'=>'user',
			'code'=>'show',
		);
		return $this->Request($params);
	}

	function Register($username, $password, $email, $nickname=''){
		$params=array(
			'mod'=>'public',
			'code'=>'register',
			'username'=>$username,
			'password'=>$password,
			'email'=>$email,
			'nickname'=>$nickname,
		);
		return $this->Request($params);
	}

	function GetAllTopic($count=null, $page=null, $id_min=null, $id_max=null){
		$params=array(
			'mod'=>'public',
			'code'=>'topic',
			'count'=>$count,
			'page'=>$page,
			'id_min'=>$id_min,
			'id_max'=>$id_max,
		);
		return $this->Request($params);
	}

	function GetComment($id, $count=null, $page=null){
		$params=array(
			'mod'=>'topic',
			'code'=>'comment',
			'id'=>$id,
			'count'=>$count,
			'page'=>$page,
		);
		return $this->Request($params);
	}

	function AddTopic($text,$totid=0,$type='first',$pic_url='', $pic=null){
		if(strlen($text)<3)$text.='　　';
		$params=array(
			'mod'=>'topic',
			'code'=>'add',
			'content'=>$text,
			'totid'=>$totid,
			'type'=>$type,
			'pic_url'=>$pic_url,
			'pic'=>$pic,
		);
		return $this->Request($params);
	}

	function DeleteTopic($id){
		$params=array(
			'mod'=>'topic',
			'code'=>'delete',
			'id'=>$id,
		);
		return $this->Request($params);
	}

	function GetTopicById($id){
		$params=array(
			'mod'=>'topic',
			'code'=>'show',
			'id'=>$id,
		);
		return $this->Request($params);
	}

	function GetMyFans(){
		$params=array(
			'mod'=>'user',
			'code'=>'fans',
		);
		return $this->Request($params);
	}

	function GetMyFollow(){
		$params=array(
			'mod'=>'user',
			'code'=>'follow',
		);
		return $this->Request($params);
	}

	function AddFollow($uid){
		$params=array(
			'mod'=>'user',
			'code'=>'follownew',
			'uid'=>$uid,
		);
		return $this->Request($params);
	}

	function DeleteFollow($uid){
		$params=array(
			'mod'=>'user',
			'code'=>'followdel',
			'uid'=>$uid,
		);
		return $this->Request($params);
	}

	function ShowFollow($target_id,$source_id=''){
		$params=array(
			'mod'=>'user',
			'code'=>'followshow',
			'source_id'=>$source_id,
			'target_id'=>$target_id,
		);
		return $this->Request($params);
	}

	function GetMyTopic($count=null, $page=null, $id_min=null, $id_max=null){
		$params=array(
			'mod'=>'user',
			'code'=>'topic',
			'count'=>$count,
			'page'=>$page,
			'id_min'=>$id_min,
			'id_max'=>$id_max,
		);
		return $this->Request($params);
	}

	function GetMyFriendTopic($count=null, $page=null){
		$params=array(
			'mod'=>'user',
			'code'=>'myfriendtopic',
			'count'=>$count,
			'page'=>$page,
		);
		return $this->Request($params);
	}

	function GetMyFavorite($count=null, $page=null){
		$params=array(
			'mod'=>'user',
			'code'=>'myfavorite',
			'count'=>$count,
			'page'=>$page,
		);
		return $this->Request($params);
	}

	function GetFavoriteMy($count=null, $page=null){
		$params=array(
			'mod'=>'user',
			'code'=>'favoritemy',
			'count'=>$count,
			'page'=>$page,
		);
		return $this->Request($params);
	}

	function GetMyComment($count=null, $page=null){
		$params=array(
			'mod'=>'user',
			'code'=>'mycomment',
			'count'=>$count,
			'page'=>$page,
		);
		return $this->Request($params);
	}

	function GetCommentMy($count=null, $page=null){
		$params=array(
			'mod'=>'user',
			'code'=>'commentmy',
			'count'=>$count,
			'page'=>$page,
		);
		return $this->Request($params);
	}

	function GetAtMy($count=null, $page=null){
		$params=array(
			'mod'=>'user',
			'code'=>'atmy',
			'count'=>$count,
			'page'=>$page,
		);
		return $this->Request($params);
	}

	function GetMyPm($count=null, $page=null){
		$params=array(
			'mod'=>'user',
			'code'=>'pm',
			'count'=>$count,
			'page'=>$page,
		);
		return $this->Request($params);
	}

	function GetMySentPm($count=null, $page=null){
		$params=array(
			'mod'=>'user',
			'code'=>'pmsent',
			'count'=>$count,
			'page'=>$page,
		);
		return $this->Request($params);
	}

	function SendPm($to_user,$text){
		$params=array(
			'mod'=>'user',
			'code'=>'pmnew',
			'to_user'=>$to_user,
			'text'=>$text,
		);
		return $this->Request($params);
	}

	function Request($posts=array()){
		settype($posts, 'array');
		$posts['__timestamp__']=time();
		$posts['__API__']=array(
			'charset'=>$this->ApiCharset,
			'output'=>$this->ApiOutput,
			'app_key'=>$this->AppKey,
			'username'=>$this->ApiUsername,
			'password'=>$this->ApiPassword,
			'auth_type'=>$this->ApiAuthType,
		);
		if(isset($_POST['pic']) && is_array($posts['pic'])){
			$this->Http->addPostFile('pic', $posts['pic']['name'], $posts['pic']['data']);
			unset($posts['pic']);
		}
		if('jauth2'==$this->ApiAuthType){
			$posts['__API__']['auth_sign']=$this->_sign($posts, $this->AppSecret);
		}elseif('oauth2'==$this->ApiAuthType){
			unset($posts['__API__']['username'], $posts['__API__']['password']);
			$posts['__API__']['access_token']=JISHIGOU_API_OAUTH2_ACCESS_TOKEN;
		}else{
			$posts['__API__']['app_secret']=$this->AppSecret;
		}
		foreach($posts as $k=>$v){
			$this->Http->addPostField($k,$v);
		}
		$result=$this->Http->Post($this->SiteUrl,false);
		$result=$this->_decode_output($result);
		if(isset($result['error'])){
			/**
			echo('<pre>调试信息来自：'.__FILE__.'('.__LINE__.')<br>');
			echo '输入值：'; print_r($posts);
			echo '返回值：'; print_r($result);
			echo '</pre>';
			exit;
			**/
		}
		return $result;
	}

	function SetSiteUrl($site_url){
		$this->SiteUrl=$site_url;
	}

	function SetAppKey($app_key){
		$this->AppKey=$app_key;
	}

	function SetAppSecret($app_secret){
		$this->AppSecret=$app_secret;
	}

	function SetApiUsername($api_username){
		$this->ApiUsername=$api_username;
	}

	function SetApiPassword($api_password){
		$this->ApiPassword=$api_password;
	}

	function SetApiOutput($api_output){
		$this->ApiOutput=$api_output;
	}

	function SetApiCharset($api_charset){
		$this->ApiCharset=$api_charset;
	}

	function SetApiAuthType($api_auth_type){
		$this->ApiAuthType=$api_auth_type;
	}

	function _decode_output($result){
		switch(strtolower($this->ApiOutput)){
			case 'json':
				{
					if( version_compare(PHP_VERSION, '5.2.0', '>=') && version_compare(PHP_VERSION, '5.2.3', '<=') ){
						$result=json_decode(preg_replace('#(?<=[,\{\[])\s*("\w+"):(\d{6,})(?=\s*[,\]\}])#si', '${1}:"${2}"', $result), true);
					}else{
						$result=json_decode ( $result, true );
					}
				}
				break;
			case 'xml':
				{
					$xml_parser=new JishiGouAPI_XML(false);
					$result=$xml_parser->parse($result);
					$xml_parser->destruct();
				}
				break;
			case 'serialize_base64':
				{
					$result=unserialize(base64_decode($result));
				}
				break;
			default :
				{
					;
				}
				break;
		}
		return $result;
	}

	function _sign($p, $secret_key){
		$str='';
		krsort($p);
		reset($p);
		foreach($p as $k=>$v){
			if(is_array($v)){
				krsort($v);
				reset($v);
				foreach($v as $_k=>$_v){
					$str.=("{$k}[{$_k}]={$_v}");
				}
			}else{
				$str.=("{$k}={$v}");
			}
		}
		$signv=md5($str . $secret_key);
		return $signv;
	}
}



/**
 * @author hightman <hightman@twomice.net>
 * @link http://www.hightman.cn/
 * @copyright Copyright &copy; 2008-2010 Twomice Studio
 * @version $Id: jishigouapi.class.php 926 2012-05-15 01:41:04Z wuliyong $
 */

define ('HC_PACKAGENAME', 'JishiGouAPI_Http_Client');
define ('HC_VERSION', '2.0-beta');
define ('HC_MAX_RETRIES', 3);

/**
 * @author hightman <hightman@twomice.net>
 * @version 2.0-beta $
 */
/**
 * @package
 * @author www.jishigou.com
 * @copyright foxis
 * @version 2011
 * @access public
 */
class JishiGouAPI_Http_Client
{
	var $headers, $status, $title, $cookies, $socks, $url, $filepath, $verbose;
	var $post_files, $post_fields;

	function JishiGouAPI_Http_Client($verbose=false){
		$this->_construct($verbose);
	}

	function _construct($verbose=false){
		$this->verbose=$verbose;
		$this->cookies=array();
		$this->socks=array();
		$this->_reset();
	}

	function _destruct(){
		foreach($this->socks as $host=>$sock){ @fclose($sock); }
		$this->socks=array();
	}

	function getStatus(){
		return $this->status;
	}

	function getTitle(){
		return $this->title;
	}

	function getUrl(){
		return $this->url;
	}

	function getFilepath(){
		return $this->filepath;
	}

	function setHeader($key, $value=null){
		$this->_reset();
		$key=strtolower($key);
		if(is_null($value)) unset($this->headers[$key]);
		else $this->headers[$key]=strval($value);
	}

	function getHeader($key=null){
		if(is_null($key)) return $this->headers;
		$key=strtolower($key);
		if(!isset($this->headers[$key])) return null;
		return $this->headers[$key];
	}

	function setCookie($key, $value){
		$this->_reset();
		if(!isset($this->headers['cookie'])) $this->headers['cookie']=array();
		$this->headers['cookie'][$key]=$value;
	}

	function getCookie($key=null, $host=null){
		if(!is_null($key)) $key=strtolower($key);
		if(is_null($host)){
			if(!isset($this->headers['cookie'])) return null;
			if(is_null($key)) return $this->headers['cookie'];
			if(!isset($this->headers['cookie'][$key])) return null;
			return $this->headers['cookie'][$key];
		}
		$host=strtolower($host);
		while(true){
			if(isset($this->cookies[$host])){
				if(is_null($key)) return $this->cookies[$host];
				if(isset($this->cookies[$host][$key])) return $this->cookies[$host][$key];
			}
			$pos=strpos($host, '.', 1);
			if($pos===false) break;
			$host=substr($host, $pos);
		}
		return null;
	}

	function saveCookie($fpath){
		if(false===($fd=@fopen($fpath, 'w')))
			return false;
		$data=serialize($this->cookies);
		fwrite($fd, $data);
		fclose($fd);
		return true;
	}

	function loadCookie($fpath){
		if(file_exists($fpath) && ($cookies=@unserialzie(file_get_contents($fpath))))
			$this->cookies=$cookies;
	}

	function addPostField($key, $value){
		$this->_reset();
		if(!is_array($value))
			$this->post_fields[$key]=strval($value);
		else
		{
			$value=$this->_format_array_field($value);
			foreach($value as $tmpk=>$tmpv){
				$tmpk=$key . '[' . $tmpk . ']';
				$this->post_fields[$tmpk]=strval($tmpv);
			}
		}
	}

	function addPostFile($key, $fname, $content=''){
		$this->_reset();
		if($content==='' && is_file($fname))$content=@file_get_contents($fname);
		$this->post_files[$key]=array(basename($fname), $content);
	}

	function Get($url, $redir=true){
		return $this->_do_url($url, 'get', null, $redir);
	}

	function Head($url, $redir=false){
		if($this->_do_url($url, 'head', null, $redir) !==false)
			return $this->getHeader(null);
		return false;
	}

	function Post($url, $redir=true){
		$data='';
		if(count($this->post_files) > 0){
			$boundary=md5($url . microtime());
			foreach($this->post_fields as $tmpk=>$tmpv){
				$data.="--{$boundary}\r\nContent-Disposition: form-data; name=\"{$tmpk}\"\r\n\r\n{$tmpv}\r\n";
			}
			foreach($this->post_files as $tmpk=>$tmpv){
				$type='application/octet-stream';
				$ext=strtolower(substr($tmpv[0], strrpos($tmpv[0],'.')+1));
				if(isset($GLOBALS['___HC_MIMES___'][$ext])) $type=$GLOBALS['___HC_MIMES___'][$ext];
				$data.="--{$boundary}\r\nContent-Disposition: form-data; name=\"{$tmpk}\"; filename=\"{$tmpv[0]}\"\r\nContent-Type: $type\r\nContent-Transfer-Encoding: binary\r\n\r\n";
				$data.=$tmpv[1] . "\r\n";
			}
			$data.="--{$boundary}--\r\n";
			$this->setHeader('content-type', 'multipart/form-data; boundary=' . $boundary);
		}else{
			foreach($this->post_fields as $tmpk=>$tmpv){
				$data.='&' . rawurlencode($tmpk) . '=' . rawurlencode($tmpv);
			}
			$data=substr($data, 1);
			$this->setHeader('content-type', 'application/x-www-form-urlencoded');
		}
		$this->setHeader('content-length', strlen($data));
		return $this->_do_url($url, 'post', $data, $redir);
	}

	function Download($url, $filepath=null, $overwrite=false){
		if($filepath===true){
			$overwrite=true;
			$filepath=null;
		}
		if(is_null($filepath) || empty($filepath)) $filepath='.';
		$savehead=$this->getHeader(null);
		if(!$this->Head($url, true)){
			if($this->verbose) echo "[ERROR] failed to get headers for downloading file.\n";
			return false;
		}
		elseif($this->getStatus() !=200){
			if($this->verbose) echo "[ERROR] can not get a valid 200 HTTP respond status.\n";
			return false;
		}
		$url=$this->getUrl();
		if($this->verbose) echo "[INFO] real download url is: $url\n";
		if(is_dir($filepath)){
			if(substr($filepath, -1, 1) !=DIRECTORY_SEPARATOR) $filepath.=DIRECTORY_SEPARATOR;
			if(($disposition=$this->getHeader('content-disposition'))
				&& preg_match('/filename=[\'"]?([^;\'" ]+)/', $disposition, $match)){
				$filename=$match[1];
				if($this->verbose) echo "[INFO] fetch filename from disposition header: $filename\n";
			}else{
				$tmpstr=($pos=strpos($url, '?')) ? substr($url, 0, $pos) : $url;
				$pos=strrpos($tmpstr, '/');
				$filename=substr($tmpstr, $pos + 1);
				if($filename=='') $filename='index.html';
				if($this->verbose) echo "[INFO] fetch filename from URL: $filename\n";
			}
			while (true){
				$filepath.=$filename;
				if(!is_dir($filepath)) break;
				$filepath.=DIRECTORY_SEPARATOR . $filename;
			}
		}
		if(!file_exists($filepath) || !($fsize=@filesize($filepath))){
			$savefd=@fopen($filepath, 'w');
			if($this->verbose) echo "[INFO] save file directly to: $filepath\n";
		}else{
			$length=$this->getHeader('content-length');
			$accept=$this->getHeader('accept-ranges');
			if($length && $fsize < $length && stristr($accept, 'bytes')){
				$this->setHeader('range', 'bytes=' . $fsize . '-');
				$savefd=@fopen($filepath, 'a');
				if($this->verbose) echo "[INFO] range download used, range: {$fsize}-\n";
			}elseif($overwrite){
				$savefd=@fopen($filepath, 'w');
				if($this->verbose) echo "[INFO] overwrite the exists file: $filepath\n";
			}else{
				for($i=1; @file_exists($filepath . '.' . $i); $i++);
				$filepath.='.' . $i;
				$savefd=@fopen($filepath, 'w');
				if($this->verbose) echo "[INFO] auto skip exists file, last save to: $filepath\n";
			}
		}
		if(!$savefd){
			if($this->verbose) echo "[ERROR] can not open the file to save data: $filename\n";
			return false;
		}
		foreach($savehead as $hk=>$hv) $this->setHeader($hk, $hv);
		if($this->_do_url($url, 'get', null, false, $savefd) !==false){
			$this->filepath=$filepath;
			fclose($savefd);
			if($this->verbose) echo "[INFO] downloaded file saved in: $filepath\n";
			return true;
		}else{
			if($this->verbose) echo "[ERROR] can not download the URL: $url\n";
			return false;
		}
	}

	function _sock_read($fd, $maxlen=4096, $wfd=false){
		$rlen=0;
		$data='';
		$ntry=HC_MAX_RETRIES;
		while (!feof($fd)){
			$part=fread($fd, $maxlen - $rlen);
			if($part===false || $part==='') $ntry--;
			else $data.=$part;
			$rlen=strlen($data);
			if($rlen==$maxlen || $ntry==0) break;
		}
		if($ntry==0 || feof($fd)) @fclose($fd);
		if(is_resource($wfd)){
			fwrite($wfd, $data);
			$data='';
		}
		return $data;
	}

	function _sock_write($fd, $buf){
		$wlen=0;
		$tlen=strlen($buf);
		$ntry=HC_MAX_RETRIES;
		while ($wlen < $tlen){
			$nlen=fwrite($fd, substr($buf, $wlen), $tlen - $wlen);
			if(!$nlen){ if(--$ntry==0) return false; }
			else $wlen +=$nlen;
		}
		return true;
	}

	function _reset(){
		if($this->status !==0){
			$this->status=0;
			$this->url=$this->title=$this->filepath=null;
			$this->headers=$this->post_files=$this->post_fields=array();
		}
	}

	function _belong_domain($host, $domain){
		if(!strcasecmp($domain, $host)) return true;
		if(substr($domain, 0, 1)=='.'){
			if(!strcasecmp($host, substr($domain, 1))) return true;
			$hlen=strlen($host);
			$dlen=strlen($domain);
			if($hlen > $dlen && !strcasecmp(substr($host, $hlen - $dlen), $domain))
				return true;
		}
		return false;
	}

	function _format_array_field($value, $pk=NULL){
		$ret=array();
		foreach($value as $k=>$v){
			$k=(is_null($pk) ? $k : $pk . $k);
			if(is_array($v)) $ret +=$this->_format_array_field($v, $k . '][');
			else $ret[$k]=$v;
		}
		return $ret;
	}

	function _do_url($url, $method, $data=null, $redir=true, $savefd=false){
		if(strncasecmp($url, 'http://', 7) && strncasecmp($url, 'https:/'.'/', 8) && isset($_SERVER['HTTP_HOST'])){
			$base='http://' . $_SERVER['HTTP_HOST'];
			if(substr($url, 0, 1) !='/')
				$url=substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/')+1) . $url;
			$url=$base . $url;
		}
		$url=str_replace('&amp;', '&', $url);
		$pa=@parse_url($url);
		if($pa['scheme'] && $pa['scheme'] !='http' && $pa['scheme'] !='https'){
			trigger_error("Invalid scheme `{$pa['scheme']}`", E_USER_WARNING);
			return false;
		}
		if(!isset($pa['host'])){
			trigger_error("Invalid request url, host required", E_USER_WARNING);
			return false;
		}
		if(!isset($pa['port'])) $pa['port']=($pa['scheme']=='https' ? 443 : 80);
		if(!isset($pa['path'])){
			$pa['path']='/';
			$url.='/';
		}
		$host=strtolower($pa['host']);
		if(isset($this->headers['x-server-addr'])) $addr=$this->headers['x-server-addr'];
		else $addr=gethostbyname($pa['host']);
		$port=intval($pa['port']);
		$skey=$addr . ':' . $port;
		if($pa['scheme'] && $pa['scheme']=='https') $host_conn='ssl://' . $addr;
		else $host_conn='tcp://' . $addr;
		$method=strtoupper($method);
		$buf=$method . ' ' . $pa['path'];
		if(isset($pa['query'])) $buf.='?' . $pa['query'];
		$buf.=" HTTP/1.1\r\nHost: {$host}\r\n";
		if(isset($pa['user']) && isset($pa['pass']))
			$this->headers['authorization']='Basic ' . base64_encode($pa['user'] . ':' . $pa['pass']);
		$savehead=$this->headers;
		$this->_reset();
		if(!isset($this->headers['user-agent'])){
			$buf.="User-Agent: Mozilla/5.0 (Compatible; " . HC_PACKAGENAME . "/" . HC_VERSION . "; +foxis) ";
			$buf.="php-" . php_sapi_name() . "/" . phpversion() . " ";
			$buf.=php_uname("s") . "/" . php_uname("r") . "\r\n";
		}
		if(!isset($this->headers['accept'])) $buf.="Accept: */*\r\n";
		if(!isset($this->headers['accept-language'])) $buf.="Accept-Language: zh-cn,zh\r\n";
		if(!isset($this->headers['connection'])) $buf.="Connection: Keep-Alive\r\n";
		if(isset($this->headers['accept-encoding'])) unset($this->headers['accept-encoding']);
		if(isset($this->headers['host'])) unset($this->headers['host']);
		$now=time();
		$ck_str='';
		foreach($this->cookies as $ck_host=>$ck_list){
			if(!$this->_belong_domain($host, $ck_host)) continue;
			foreach($ck_list as $ck=>$cv){
				if(isset($this->headers['cookie'][$ck])) continue;
				if($cv['expires'] > 0 && $cv['expires'] < $now) continue;
				if(strncmp($pa['path'], $cv['path'], strlen($cv['path']))) continue;
				$ck_str.='; ' . $cv['rawdata'];
			}
		}
		foreach($this->headers as $k=>$v){
			if($k !='cookie')
				$buf.=ucfirst($k) . ": " . $v . "\r\n";
			else
			{
				foreach($v as $ck=>$cv) $ck_str.='; ' . rawurlencode($ck) . '=' . rawurlencode($cv);
			}
		}
		if($ck_str !='') $buf.='Cookie:' . substr($ck_str, 1) . "\r\n";
		$buf.="\r\n";
		if($method=='POST') $buf.=$data . "\r\n";
		$this->status=-1;
		$this->url=$url;
		if($this->verbose){
			echo "[INFO] request url: $url\r\n";
			echo "[SEND] request buffer\r\n----\r\n";
			echo $buf;
			echo "----\r\n";
		}
		$ntry=HC_MAX_RETRIES;
		$sock=isset($this->socks[$skey]) ? $this->socks[$skey] : false;
		do
		{
			if(is_resource($sock) && $this->_sock_write($sock, $buf)) break;
			if($sock) @fclose($sock);
			if(function_exists('fsockopen')){
				$sock=fsockopen($host_conn, $port, $errno, $error, 3);
			}
			elseif(function_exists('pfsockopen')){
				$sock=pfsockopen($host_conn, $port, $errno, $error, 3);
			}else{
				$sock=false;
			}
			if($sock){
				stream_set_blocking($sock, 1);
				stream_set_timeout($sock, 10);
			}
		}
		while (--$ntry);
		if(!$sock){
			if(isset($this->socks[$skey])) unset($this->socks[$skey]);
			trigger_error("Cann't connect to `$host:$port'", E_USER_WARNING);
			return false;
		}
		$this->socks[$skey]=$sock;
		if($this->verbose){
			echo "[SEND] using socket={$sock}\r\n";
			echo "[RECV] http respond header\r\n----\r\n";
		}
		$with_range=isset($this->headers['range']);
		$this->headers=array();
		while ($line=fgets($sock, 2048)){
			if($this->verbose) echo $line;
			$line=trim($line);
			if($line==='') break;
			if(!strncasecmp('HTTP/', $line, 5)){
				$line=trim(substr($line, strpos($line, ' ')));
				list($this->status, $this->title)=explode(' ', $line, 2);
				$this->status=intval($this->status);
			}
			elseif(!strncasecmp('Set-Cookie: ', $line, 12)){
				$ck_key='';
				$ck_val=array('value'=>'', 'expires'=>0, 'path'=>'/', 'domain'=>$host);
				$tmpa=explode(';', substr($line, 12));
				foreach($tmpa as $tmp){
					$tmp=trim($tmp);
					if(empty($tmp)) continue;
					list($tmpk, $tmpv)=explode('=', $tmp, 2);
					$tmpk2=strtolower($tmpk);
					if($ck_key==''){
						$ck_key=rawurldecode($tmpk);
						$ck_val['value']=rawurldecode($tmpv);
						$ck_val['rawdata']=$tmpk . '=' . $tmpv;
					}
					elseif($tmpk2=='expires'){
						$ck_val['expires']=strtotime($tmpv);
						if($ck_val['expires'] < $now){
							$ck_val['value']='';
							break;
						}
					}
					elseif(isset($ck_val[$tmpk2]) && $tmpv !=''){
						$ck_val[$tmpk2]=$tmpv;
						if($tmpk2=='domain' && !$this->_belong_domain($host, $tmpv)) $ck_key='';
					}
				}
				if($ck_key=='') continue;
				if($ck_val['value']=='') unset($this->cookies[$ck_val['domain']][$ck_key]);
				else $this->cookies[$ck_val['domain']][$ck_key]=$ck_val;
				$this->headers['cookie'][$ck_key]=$ck_val;
			}else{
				list($k, $v)=explode(':', $line, 2);
				$k=strtolower(trim($k));
				$v=trim($v);
				$this->headers[$k]=$v;
			}
		}
		if($this->verbose) echo "----\r\n";
		if($savefd && $with_range){
			if($this->status==200){
				ftruncate($savefd, 0);
				fseek($savefd, 0, SEEK_SET);
			}
			elseif($this->status !=206) $savefd=false;
		}
		$connection=$this->getHeader('connection');
		$encoding=$this->getHeader('transfer-encoding');
		$length=$this->getHeader('content-length');
		if($method=='HEAD'){
			$body='';
		}
		elseif($encoding && !strcasecmp($encoding, 'chunked')){
			$body='';
			while (is_resource($sock)){
				if(!($line=fgets($sock, 1024))) break;
				if($this->verbose) echo "[RECV] Chunk Line: " . $line;
				if($p1=strpos($line, ';')) $line=substr($line, 0, $pos);
				$chunk_len=hexdec(trim($line));
				if($chunk_len <=0) break;
				$body.=$this->_sock_read($sock, $chunk_len, $savefd);
				fread($sock, 2);
			}
			if($this->verbose) echo "[RECV] chunk tailer\r\n----\r\n";
			while ($line=fgets($sock, 2048)){
				if($this->verbose) echo $line;
				$line=trim($line);
				if($line==='') break;
				list($k, $v)=explode(':', $line, 2);
				$k=strtolower(trim($k));
				$v=trim($v);
				$this->headers[$k]=$v;
			}
			if($this->verbose) echo "----\r\n";
		}
		elseif($length){
			$body='';
			$length=intval($length);
			while ($length > 0 && is_resource($sock)){
				$body.=$this->_sock_read($sock, ($length > 8192 ? 8192 : $length), $savefd);
				$length -=8192;
			}
		}else{
			$body='';
			while (is_resource($sock) && !feof($sock)) $body.=$this->_sock_read($sock, 8192, $savefd);
			$connection='close';
		}
		if($connection && !strcasecmp($connection, 'close')){
			@fclose($sock);
			unset($this->socks[$skey]);
		}
		if($redir && $this->status !=200 && ($location=$this->getHeader('location'))){
			if(!is_int($redir)) $redir=HC_MAX_RETRIES;
			if(!preg_match('/^http[s]?:\/\/'.'/i', $location)){
				$url2=$pa['scheme'] . ':/'.'/' . $pa['host'];
				if(strpos($url, ':', 8)) $url2.=':' . $pa['port'];
				if(substr($location, 0, 1)=='/') $url2.=$location;
				else $url2.=substr($pa['path'], 0, strrpos($pa['path'], '/') + 1) . $location;
				$location=$url2;
			}
			if(!isset($savehead['referer'])) $savehead['referer']=$url;
			foreach($savehead as $hk=>$hv) $this->setHeader($hk, $hv);
			return $this->_do_url($location, ($method=='HEAD' ? 'head' : 'get'), null, $redir - 1);
		}
		return $body;
	}
}

$GLOBALS['___HC_MIMES___']=array(
	'gif'=>'image/gif',
	'png'=>'image/png',
	'bmp'=>'image/bmp',
	'jpeg'=>'image/jpeg',
	'pjpg'=>'image/pjpg',
	'jpg'=>'image/jpeg',
	'tif'=>'image/tiff',
	'htm'=>'text/html',
	'css'=>'text/css',
	'html'=>'text/html',
	'txt'=>'text/plain',
	'gz'=>'application/x-gzip',
	'tgz'=>'application/x-gzip',
	'tar'=>'application/x-tar',
	'zip'=>'application/zip',
	'hqx'=>'application/mac-binhex40',
	'doc'=>'application/msword',
	'pdf'=>'application/pdf',
	'ps'=>'application/postcript',
	'rtf'=>'application/rtf',
	'dvi'=>'application/x-dvi',
	'latex'=>'application/x-latex',
	'swf'=>'application/x-shockwave-flash',
	'tex'=>'application/x-tex',
	'mid'=>'audio/midi',
	'au'=>'audio/basic',
	'mp3'=>'audio/mpeg',
	'ram'=>'audio/x-pn-realaudio',
	'ra'=>'audio/x-realaudio',
	'rm'=>'audio/x-pn-realaudio',
	'wav'=>'audio/x-wav',
	'wma'=>'audio/x-ms-media',
	'wmv'=>'video/x-ms-media',
	'mpg'=>'video/mpeg',
	'mpga'=>'video/mpeg',
	'wrl'=>'model/vrml',
	'mov'=>'video/quicktime',
	'avi'=>'video/x-msvideo'
);

/**
 * @package
 * @author www.jishigou.com
 * @copyright foxis
 * @version 2011
 * @access public
 */
class JishiGouAPI_XML {
	var $parser;
	var $document;
	var $stack;
	var $data;
	var $last_opened_tag;
	var $isnormal;
	var $attrs=array();
	var $failed=FALSE;

	function __construct($isnormal){
		$this->JishiGouAPI_XML($isnormal);
	}

	function JishiGouAPI_XML($isnormal){
		$this->isnormal=$isnormal;
		$this->parser=xml_parser_create('UTF-8');
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, 'open','close');
		xml_set_character_data_handler($this->parser, 'data');
	}

	function destruct(){
		xml_parser_free($this->parser);
	}

	function parse(&$data){
		$this->document=array();
		$this->stack=array();
		return xml_parse($this->parser, $data, true) && !$this->failed ? $this->document : '';
	}

	function open(&$parser, $tag, $attributes){
		$this->data='';
		$this->failed=FALSE;
		if(!$this->isnormal){
			if(isset($attributes['id']) && !is_string($this->document[$attributes['id']])){
				$this->document=&$this->document[$attributes['id']];
			}else{
				$this->failed=TRUE;
			}
		}else{
			if(!isset($this->document[$tag]) || !is_string($this->document[$tag])){
				$this->document=&$this->document[$tag];
			}else{
				$this->failed=TRUE;
			}
		}
		$this->stack[]=&$this->document;
		$this->last_opened_tag=$tag;
		$this->attrs=$attributes;
	}

	function data(&$parser, $data){
		if($this->last_opened_tag !=NULL){
			$this->data.=$data;
		}
	}

	function close(&$parser, $tag){
		if($this->last_opened_tag==$tag){
			$this->document=$this->data;
			$this->last_opened_tag=NULL;
		}
		array_pop($this->stack);
		if($this->stack){
			$this->document=&$this->stack[count($this->stack)-1];
		}
	}

}

class JishiGouAPIOAuth2 {
	var $client_id;
	var $client_secret;
	var $access_token;
	var $refresh_token;
	var $http_code;
	var $url;
	var $host="";
	var $access_token_url="";
	var $authorize_url="";
	var $timeout=30;
	var $connecttimeout=30;
	var $ssl_verifypeer=FALSE;
	var $format='json';
	var $decode_json=TRUE;
	var $http_info;
	var $useragent='JishiGou OAuth2 v0.1';
	var $debug=FALSE;

	function accessTokenURL()  { return $this->access_token_url; }

	function authorizeURL()	{ return $this->authorize_url; }

	function __construct($client_id, $client_secret, $access_token=NULL, $refresh_token=NULL){
		$this->JishiGouAPIOAuth2($client_id, $client_secret, $access_token, $refresh_token);
	}

	function JishiGouAPIOAuth2($client_id, $client_secret, $access_token=NULL, $refresh_token=NULL){
		$this->client_id=$client_id;
		$this->client_secret=$client_secret;
		$this->access_token=$access_token;
		$this->refresh_token=$refresh_token;
		$this->debug=(true===JISHIGOU_API_DEBUG ? true : false);
	}

	function getAuthorizeURL( $url, $response_type='code', $keys=array() ){
		$params=array();
		$params['client_id']=$this->client_id;
		$params['redirect_uri']=$url;
		$params['response_type']=$response_type;
		if($keys){
			$ps=array('scope', 'state', 'display', );
			foreach($ps as $k){
				if(isset($keys[$k])){
					$v=$keys[$k];
					if($v){
						$params[$k]=$v;
					}
				}
			}
		}
		return $this->_get_url($this->authorizeURL(), $params);
	}

	function getAccessToken( $type='code', $keys ){
		$params=array();
		$params['client_id']=$this->client_id;
		$params['client_secret']=$this->client_secret;
		if( $type==='token' ){
			$params['grant_type']='refresh_token';
			$params['refresh_token']=$keys['refresh_token'];
		}elseif( $type==='code' ){
			$params['grant_type']='authorization_code';
			$params['code']=$keys['code'];
			$params['redirect_uri']=$keys['redirect_uri'];
		}elseif( $type==='password' ){
			$params['grant_type']='password';
			$params['username']=$keys['username'];
			$params['password']=$keys['password'];
		}else{
		}
		$response=$this->oAuthRequest($this->accessTokenURL(), 'POST', $params);
		$token=json_decode($response, true);
		if( is_array($token) && !isset($token['error']) ){
			$this->access_token=$token['access_token'];
			$this->refresh_token=$token['refresh_token'];
		}else{
		}
		return $token;
	}

	function base64decode($str){
		return base64_decode(strtr($str.str_repeat('=',(4 - strlen($str) % 4)), '-_', '+/'));
	}

	function get($url, $parameters=array()){
		$response=$this->oAuthRequest($url, 'GET', $parameters);
		if($this->format==='json' && $this->decode_json){
			return json_decode($response, true);
		}
		return $response;
	}

	function post($url, $parameters=array() , $multi=false){
		$response=$this->oAuthRequest($url, 'POST', $parameters , $multi );
		if($this->format==='json' && $this->decode_json){
			return json_decode($response, true);
		}
		return $response;
	}

	function delete($url, $parameters=array()){
		$response=$this->oAuthRequest($url, 'DELETE', $parameters);
		if($this->format==='json' && $this->decode_json){
			return json_decode($response, true);
		}
		return $response;
	}

	function oAuthRequest($url, $method, $parameters , $multi=false){
		if(strrpos($url, 'https:/'.'/') !==0 && strrpos($url, 'http:/'.'/') !==0){
			$url="{$this->host}{$url}.{$this->format}";
		}
		switch ($method){
			case 'GET':
				$url=$this->_get_url($url, $parameters);
				return $this->http($url, 'GET');
			default:
				return $this->http($url, $method, $parameters);
		}
	}

	function http_socket($url, $method, $postfields=NULL){
		$http=new JishiGouAPI_Http_Client($this->debug);
		$http->setHeader('user-agent', $this->useragent);
		if($this->access_token){
			$http->setHeader('authorization', 'OAuth2 ' . $this->access_token);
		}
		$http->setHeader('API-RemoteIP', $this->_get_client_ip());
		if('POST'==$method){
			if($postfields && is_array($postfields)){
				foreach($postfields as $k=>$v){
					$http->addPostField($k, $v);
				}
			}
			return $http->Post($url, false);
		}else{
			if($postfields){
				$url=$this->_get_url($url, $postfields);
			}
			return $http->Get($url, false);
		}
	}

	function http($url, $method, $postfields=NULL){
		if(!function_exists('curl_exec')){
			return $this->http_socket($url, $method, $postfields);
		}
		$this->http_info=array();
		$ci=curl_init();
		curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
		curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_ENCODING, "");
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
		curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
		curl_setopt($ci, CURLOPT_HEADER, FALSE);
		switch ($method){
			case 'POST':
				curl_setopt($ci, CURLOPT_POST, TRUE);
				if(!empty($postfields)){
					$postfields=$this->_get_url('', $postfields);
					curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
					$this->postdata=$postfields;
				}
				break;
			case 'DELETE':
				curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if(!empty($postfields)){
					$url=$this->_get_url($url, $postfields);
				}
		}
		$headers=array();
		if( isset($this->access_token) && $this->access_token )
		$headers[]="Authorization: OAuth2 ".$this->access_token;
		$headers[]="API-RemoteIP: " . $this->_get_client_ip();
		curl_setopt($ci, CURLOPT_URL, $url );
		curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
		curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
		$response=curl_exec($ci);
		$this->http_code=curl_getinfo($ci, CURLINFO_HTTP_CODE);
		$this->http_info=array_merge($this->http_info, curl_getinfo($ci));
		$this->url=$url;
		if($this->debug){
			echo "=====post data======\r\n";
			var_dump($postfields);
			echo '=====info====='."\r\n";
			print_r( curl_getinfo($ci) );
			echo '=====$response====='."\r\n";
			print_r( $response );
		}
		curl_close ($ci);
		return $response;
	}

	function getHeader($ch, $header){
		$i=strpos($header, ':');
		if(!empty($i)){
			$key=str_replace('-', '_', strtolower(substr($header, 0, $i)));
			$value=trim(substr($header, $i + 2));
			$this->http_header[$key]=$value;
		}
		return strlen($header);
	}

	function _get_client_ip(){
		if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')){
			$onlineip=getenv('HTTP_CLIENT_IP');
		}elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')){
			$onlineip=getenv('HTTP_X_FORWARDED_FOR');
		}elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')){
			$onlineip=getenv('REMOTE_ADDR');
		}elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')){
			$onlineip=$_SERVER['REMOTE_ADDR'];
		}
		preg_match('/[\d\.]{7,15}/', $onlineip, $onlineipmatches);
		$onlineip=($onlineipmatches[0] ? $onlineipmatches[0] : 'unknown');

		return $onlineip;
	}

	function _get_url($url='', $p=null){
		if($p){
			$sep='';
			if($url){
				$sep=(false !==strpos($url, '?') ? '&' : '?');
			}
			if(is_array($p)){
				$url.=$sep . http_build_query($p);
			}else{
				$url.=$sep . $p;
			}
		}
		return $url;
	}
}
?>