<?php
/**
 * @author tuguska
 */
require_once 'OAuth.php';
define("MB_RETURN_FORMAT", 'json');
define("MB_API_HOST", 'open.t.qq.com');

class MBOpenTOAuth {
	public $host='http://open.t.qq.com/';
	public $timeout=30;
	public $connectTimeout=30;
	public $sslVerifypeer=FALSE;
	public $format=MB_RETURN_FORMAT;
	public $decodeJson=TRUE;
	public $httpInfo;
	public $userAgent='oauth test';
	public $decode_json=FALSE;

	function accessTokenURL(){ return 'http://open.t.qq.com/cgi-bin/access_token'; }
	function authenticateURL(){ return 'http://open.t.qq.com/cgi-bin/authenticate'; }
	function authorizeURL(){ return 'http://open.t.qq.com/cgi-bin/authorize'; }
	function requestTokenURL(){ return 'http://open.t.qq.com/cgi-bin/request_token'; }

	function lastStatusCode(){ return $this->http_status; }

	function __construct($consumer_key, $consumer_secret, $oauth_token=NULL, $oauth_token_secret=NULL){
		$this->sha1_method=new OAuthSignatureMethod_HMAC_SHA1();
		$this->consumer=new OAuthConsumer($consumer_key, $consumer_secret);
		if(!empty($oauth_token) && !empty($oauth_token_secret)){
			$this->token=new OAuthConsumer($oauth_token, $oauth_token_secret);
		}else{
			$this->token=NULL;
		}
	}

	function getRequestToken($oauth_callback=NULL){
		$parameters=array();
		if(!empty($oauth_callback)){
			$parameters['oauth_callback']=$oauth_callback;
		}
		$request=$this->oAuthRequest($this->requestTokenURL(), 'GET', $parameters);
		$token=OAuthUtil::parse_parameters($request);
		$this->token=new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
		return $token;
	}

	function getAuthorizeURL($token, $signInWithWeibo=TRUE , $url=''){
		if(is_array($token)){
			$token=$token['oauth_token'];
		}
		if(empty($signInWithWeibo)){
			return $this->authorizeURL()."?oauth_token={$token}";
		}else{
			return $this->authenticateURL()."?oauth_token={$token}";
		}
	} 

	function getAccessToken($oauth_verifier=FALSE, $oauth_token=false){
		$parameters=array();
		if(!empty($oauth_verifier)){
			$parameters['oauth_verifier']=$oauth_verifier;
		}
		$request=$this->oAuthRequest($this->accessTokenURL(), 'GET', $parameters);
		$token=OAuthUtil::parse_parameters($request);
		$this->token=new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
		return $token;
	}

	function jsonDecode($response, $assoc=true){
		$response=preg_replace('/[^\x20-\xff]*/', "", $response);
		$jsonArr=json_decode($response, $assoc);
		if(!is_array($jsonArr)){
			throw new Exception('格式错误!');
		}
		$ret=$jsonArr["ret"];
		$msg=$jsonArr["msg"];
		switch($ret){
			case 0:
				return $jsonArr;;
				break;
			case 1:
				throw new Exception('参数错误!');
				break;
			case 2:
				throw new Exception('频率受限!');
				break;
			case 3:
				throw new Exception('鉴权失败!');
				break;
			default:
				$errcode=$jsonArr["errcode"];
				if(isset($errcode)){
					throw new Exception("发表失败");
					break;
				}
				throw new Exception('服务器内部错误!');
				break;
		}
	}

	function get($url, $parameters){
		$response=$this->oAuthRequest($url, 'GET', $parameters);
		if(MB_RETURN_FORMAT==='json'){
			return $this->jsonDecode($response, true);
		}
		return $response;
	}

	function post($url, $parameters=array() , $multi=false){
		$response=$this->oAuthRequest($url, 'POST', $parameters , $multi );
		if(MB_RETURN_FORMAT==='json'){
			return $this->jsonDecode($response, true);
		}
		return $response;
	}

	function delete($url, $parameters=array()){
		$response=$this->oAuthRequest($url, 'DELETE', $parameters);
		if(MB_RETURN_FORMAT==='json'){
			return $this->jsonDecode($response, true);
		}
		return $response;
	}

	function oAuthRequest($url, $method, $parameters , $multi=false){
		if(strrpos($url, 'http://')!==0 && strrpos($url, 'https://')!==0){
			$url="{$this->host}{$url}.{$this->format}";
		}
		$request=OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $parameters);
		$request->sign_request($this->sha1_method, $this->consumer, $this->token);
		switch ($method){
		case 'GET':
			return $this->http($request->to_url(), 'GET');
		default:
			return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata($multi) , $multi );
		}
	}

	function http($url, $method, $postfields=NULL , $multi=false){
		if(strrpos($url, 'https://')===0){
			$port=443;
			$version='1.1';
			$host=MB_API_HOST;
		}else{
			$port=80;
			$version='1.0';
			$host=MB_API_HOST;
		}
		$header="$method $url HTTP/$version\r\n";
		$header.="Host: ".MB_API_HOST."\r\n";
		if($multi){
			$header.="Content-Type: multipart/form-data; boundary=" . OAuthUtil::$boundary . "\r\n";
		}else{
			$header.="Content-Type: application/x-www-form-urlencoded\r\n";
		}
		if(strtolower($method)=='post' ){
			$header.="Content-Length: ".strlen($postfields)."\r\n";
			$header.="Connection: Close\r\n\r\n";
			$header.=$postfields;
		}else{
			$header.="Connection: Close\r\n\r\n";
		}
		$ret='';
		$fp=fsockopen($host,$port,$errno,$errstr,30);
		if(!$fp){
			$error='建立sock连接失败';
			throw new Exception($error);
		}else{
			fwrite ($fp, $header);
			while (!feof($fp)){
				$ret.=fgets($fp, 4096);
			}
			fclose($fp);
			if(strrpos($ret,'Transfer-Encoding: chunked')){
				$info=split("\r\n\r\n",$ret);
				$response=split("\r\n",$info[1]);
				$t=array_slice($response,1,-1);

				$returnInfo=implode('',$t);
			}else{
				$response=explode("\r\n\r\n",$ret);
				$returnInfo=$response[1];
			}
			return iconv("utf-8","utf-8//ignore",$returnInfo);
		}

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
}
?>