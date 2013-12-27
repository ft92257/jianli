<?php
/**
 * 文件名：oauth2.han.php
 * 版本号：1.0
 * 最后修改时间：2011年9月14日
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: Oauth2接口函数
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class JishiGouOAuth {

	var $client_id;

	var $client_secret;

	var $access_token;

	var $refresh_token;

	var $http_code;

	var $url;

	var $host = "";
	
	var $access_token_url = "";
	
	var $authorize_url = "";

	var $timeout = 30;

	var $connecttimeout = 30;

	var $ssl_verifypeer = FALSE;

	var $format = 'json';

	var $decode_json = TRUE;

	var $http_info;

	var $useragent = 'JishiGou OAuth2 v0.1';
	
	
	var $debug = FALSE;

	function accessTokenURL()  { return $this->access_token_url; }

	function authorizeURL()    { return $this->authorize_url; }

	function __construct($client_id, $client_secret, $access_token = NULL, $refresh_token = NULL) {
		$this->JishiGouOAuth($client_id, $client_secret, $access_token, $refresh_token);
	}
	
	function JishiGouOAuth($client_id, $client_secret, $access_token = NULL, $refresh_token = NULL) 
	{
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
		$this->access_token = $access_token;
		$this->refresh_token = $refresh_token;
	}

	
	function getAuthorizeURL( $url, $response_type = 'code', $keys = array() ) {
		$params = array();
		$params['client_id'] = $this->client_id;
		$params['redirect_uri'] = $url;
		$params['response_type'] = $response_type;	
		
				if($keys)
		{
			$ps = array('scope', 'state', 'display', );
			foreach($ps as $k)
			{
				if (isset($keys[$k]))
				{
					$v = $keys[$k];
					if($v)
					{
						$params[$k] = $v;
					}
				}
			}
		}
		
		return $this->_get_url($this->authorizeURL(), $params);
	}

	
	function getAccessToken( $type = 'code', $keys ) {
		$params = array();
		$params['client_id'] = $this->client_id;
		$params['client_secret'] = $this->client_secret;
		if ( $type === 'token' ) {
			$params['grant_type'] = 'refresh_token';
			$params['refresh_token'] = $keys['refresh_token'];
		} elseif ( $type === 'code' ) {
			$params['grant_type'] = 'authorization_code';
			$params['code'] = $keys['code'];
			$params['redirect_uri'] = $keys['redirect_uri'];
		} elseif ( $type === 'password' ) {
			$params['grant_type'] = 'password';
			$params['username'] = $keys['username'];
			$params['password'] = $keys['password'];
		} else {
					}

		$response = $this->oAuthRequest($this->accessTokenURL(), 'POST', $params);
		$token = json_decode($response, true);
		if ( is_array($token) && !isset($token['error']) ) {
			$this->access_token = $token['access_token'];
			$this->refresh_token = $token['refresh_token'];
		} else {
					}
		return $token;
	}

	
	function base64decode($str) {
		return base64_decode(strtr($str.str_repeat('=',(4 - strlen($str) % 4)), '-_', '+/'));
	}

	
	function get($url, $parameters = array()) {
		$response = $this->oAuthRequest($url, 'GET', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response, true);
		}
		return $response;
	}

	
	function post($url, $parameters = array() , $multi = false) {
		$response = $this->oAuthRequest($url, 'POST', $parameters , $multi );
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response, true);
		}
		return $response;
	}

	
	function delete($url, $parameters = array()) {
		$response = $this->oAuthRequest($url, 'DELETE', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response, true);
		}
		return $response;
	}

	
	function oAuthRequest($url, $method, $parameters , $multi = false) {

		if (strrpos($url, 'https:/'.'/') !== 0 && strrpos($url, 'http:/'.'/') !== 0) {
			$url = "{$this->host}{$url}.{$this->format}";
		}

		switch ($method) {
			case 'GET':
				$url = $this->_get_url($url, $parameters);
				return $this->http($url, 'GET');
			default:			
				return $this->http($url, $method, $parameters);
		}
	}
	
	function http_socket($url, $method, $postfields = NULL)
	{
		include_once ROOT_PATH . 'include/lib/http_client.class.php';
		
		$http = new Http_Client($this->debug);
		$http->setHeader('user-agent', $this->useragent);
		if($this->access_token)
		{
			$http->setHeader('authorization', 'OAuth2 ' . $this->access_token);
		}
		$http->setHeader('API-RemoteIP', $this->_get_client_ip());
		
		if('POST' == $method)
		{
			if($postfields && is_array($postfields))
			{
				foreach($postfields as $k=>$v)
				{
					$http->addPostField($k, $v);
				}
			}
			
			return $http->Post($url, false);
		}
		else
		{
			if($postfields)
			{
				$url = $this->_get_url($url, $postfields);
			}
			
			return $http->Get($url, false);
		}
	}

	
	function http($url, $method, $postfields = NULL) {
		if(!function_exists('curl_exec'))
		{
			return $this->http_socket($url, $method, $postfields);
		}
		$this->http_info = array();
		$ci = curl_init();
		
		curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
		curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_ENCODING, "");
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
		curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
		curl_setopt($ci, CURLOPT_HEADER, FALSE);

		switch ($method) {
		case 'POST':
			curl_setopt($ci, CURLOPT_POST, TRUE);
			if (!empty($postfields)) {
				$postfields = $this->_get_url('', $postfields);
				curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
				$this->postdata = $postfields;
			}
			break;
		case 'DELETE':
			curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
			if (!empty($postfields)) {
				$url = $this->_get_url($url, $postfields);
			}
		}

		$headers=array();
		if ( isset($this->access_token) && $this->access_token )
			$headers[] = "Authorization: OAuth2 ".$this->access_token;

		$headers[] = "API-RemoteIP: " . $this->_get_client_ip();
		curl_setopt($ci, CURLOPT_URL, $url );
		curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
		curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );

		$response = curl_exec($ci);
		$this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		$this->http_info = array_merge($this->http_info, curl_getinfo($ci));
		$this->url = $url;

		if ($this->debug) {
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

	
	function getHeader($ch, $header) {
		$i = strpos($header, ':');
		if (!empty($i)) {
			$key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
			$value = trim(substr($header, $i + 2));
			$this->http_header[$key] = $value;
		}
		return strlen($header);
	}
	
	
	function _get_client_ip()
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
	
	function _get_url($url = '', $p = null)
	{
		if($p)
		{
			$sep = '';
			
			if($url) 
			{
				$sep = (false !== strpos($url, '?') ? '&' : '?');
			}
			
			if(is_array($p))
			{
				$url .= $sep . http_build_query($p);
			}
			else 
			{
				$url .= $sep . $p;
			}
		}
		
		return $url;
	}
}



?>
