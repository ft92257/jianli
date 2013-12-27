<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename qqoauth.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 2108189262 989951801 12578 $
 *******************************************************************/





require_once(ROOT_PATH . 'include/qqwb/oauth.php');


class QQOAuth {    
	public $http_code;
	
	public $url;
	
	public $host = "http:/'.'/open.t.qq.com/cgi-bin/";
	
	public $timeout = 30;
	
	public $connecttimeout = 30;
	
	public $ssl_verifypeer = FALSE;
	
	public $format = 'json';
	
	public $decode_json = TRUE;
	
	public $http_info;
	
	public $useragent = 'JishiGou OAuth v0.2';
	
	



	
	
	function accessTokenURL()  { return 'http:/'.'/open.t.qq.com/cgi-bin/access_token'; }
	
	function authenticateURL() { return 'http:/'.'/open.t.qq.com/cgi-bin/authenticate'; }
	
	function authorizeURL()    { return 'http:/'.'/open.t.qq.com/cgi-bin/authorize'; }
	
	function requestTokenURL() { return 'http:/'.'/open.t.qq.com/cgi-bin/request_token'; }


	
	
	function lastStatusCode() { return $this->http_status; }
	
	function lastAPICall() { return $this->last_api_call; }

	
	function __construct($consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL) {
		$this->sha1_method = new OAuthSignatureMethod_HMAC_SHA1();
		$this->consumer = new OAuthConsumer($consumer_key, $consumer_secret);
		if (!empty($oauth_token) && !empty($oauth_token_secret)) {
			$this->token = new OAuthConsumer($oauth_token, $oauth_token_secret);
		} else {
			$this->token = NULL;
		}
	}


	
	function getRequestToken($oauth_callback = NULL) {
		$parameters = array();
		if (!empty($oauth_callback)) {
			$parameters['oauth_callback'] = $oauth_callback;
		}

		$request = $this->oAuthRequest($this->requestTokenURL(), 'GET', $parameters);
		$token = OAuthUtil::parse_parameters($request);
		$this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
		return $token;
	}

	
	function getAuthorizeURL($token, $url) {
		if (is_array($token)) {
			$token = $token['oauth_token'];
		}

		return $this->authorizeURL() . "?oauth_token={$token}&oauth_callback=" . urlencode($url);

	}

	
	function getAccessToken($oauth_verifier = FALSE, $oauth_token = false) {
		$parameters = array();
		if (!empty($oauth_verifier)) {
			$parameters['oauth_verifier'] = $oauth_verifier;
		}


		$request = $this->oAuthRequest($this->accessTokenURL(), 'GET', $parameters);
		$token = OAuthUtil::parse_parameters($request);
		$this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
		return $token;
	}

	
	function get($url, $parameters = array()) {
		$response = $this->oAuthRequest($url, 'GET', $parameters);
		if ($response && $this->format === 'json' && $this->decode_json) {
			return json_decode($response, true);
		}
		return $response;
	}

	
	function post($url, $parameters = array() , $multi = false) {

		$response = $this->oAuthRequest($url, 'POST', $parameters , $multi );
		if ($response && $this->format === 'json' && $this->decode_json) {
			return json_decode($response, true);
		}
		return $response;
	}

	
	function delete($url, $parameters = array()) {
		$response = $this->oAuthRequest($url, 'DELETE', $parameters);
		if ($response && $this->format === 'json' && $this->decode_json) {
			return json_decode($response, true);
		}
		return $response;
	}

	
	function oAuthRequest($url, $method, $parameters , $multi = false) {
				$request = QQOAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $parameters);
		$request->sign_request($this->sha1_method, $this->consumer, $this->token);
		switch ($method) {
			case 'GET':
								return $this->http($request->to_url(), 'GET');
			default:
				return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata($multi) , $multi );
		}
	}

	
	function http($url, $method, $postfields = NULL , $multi = false) {
		$this->http_info = array();
		if(!function_exists('curl_exec')) {
			return $this->http_socket($url, $postfields, $method, $multi);
		}
		
		$ci = curl_init();
		
		curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
		curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);

		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);

		curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));

		curl_setopt($ci, CURLOPT_HEADER, FALSE);

		switch ($method) {
			case 'POST':
				curl_setopt($ci, CURLOPT_POST, TRUE);
				if (!empty($postfields)) {
					curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
				}
				break;
			case 'DELETE':
				curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if (!empty($postfields)) {
					$url = "{$url}?{$postfields}";
				}
		}

		$header_array = array();

		$header_array2=array();
		if( $multi )
		$header_array2 = array("Content-Type: multipart/form-data; boundary=" . OAuthUtil::$boundary , "Expect: ");
		foreach($header_array as $k => $v)
		array_push($header_array2,$k.': '.$v);

		curl_setopt($ci, CURLOPT_HTTPHEADER, $header_array2 );
		curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );

		curl_setopt($ci, CURLOPT_URL, $url);

		$response = curl_exec($ci);
		$this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		$this->http_info = array_merge($this->http_info, curl_getinfo($ci));
		$this->url = $url;

		curl_close ($ci);
		return $response;
	}

	
	function http_socket( $url , $params , $method='GET' , $multi=false )
	{
		$method = strtoupper($method);
		$postdata = '';
		$urls = @parse_url($url);
		$httpurl = $urlpath = $urls['path'] . ($urls['query'] ? '?' . $urls['query'] : '');
		if( !$multi ) {
			if(is_array($params)) {
				$parts = array();
				foreach ($params as $key => $val) {
					$parts[] = urlencode($key) . '=' . urlencode($val);
				}
				$postdata = implode('&', $parts);
			} else {
				$postdata = $params;
			}
				
			if($postdata) {
				$httpurl = $httpurl . (strpos($httpurl, '?') ? '&' : '?') . $postdata;
			}
		}

		$host = $urls['host'];
		$port = $urls['port'] ? $urls['port'] : 80;
		$version = '1.1';
		if($urls['scheme'] === 'https')
		{
			$port = 443;
		}
		$headers = array();
		if($method == 'GET')
		{
			$headers[] = "GET $httpurl HTTP/$version";
		}
		else if($method == 'DELETE')
		{
			$headers[] = "DELETE $httpurl HTTP/$version";
		}
		else
		{
			$headers[] = "POST $urlpath HTTP/$version";
		}
		$headers[] = 'Host: ' . $host;
		$headers[] = 'User-Agent: ' . $this->useragent;
		$headers[] = 'Connection: Close';

		if($method == 'POST')
		{
			if($multi)
			{
				$boundary = uniqid('------------------');
				$MPboundary = '--' . $boundary;
				$endMPboundary = $MPboundary . '--';
				$multipartbody = '';
				$headers[]= 'Content-Type: multipart/form-data; boundary=' . $boundary;
				foreach($params as $key => $val)
				{
					$multipartbody .= $MPboundary . "\r\n";
					$multipartbody .= 'Content-Disposition: form-data; name="' . $key . "\"\r\n\r\n";
					$multipartbody .= $val . "\r\n";
				}
				foreach($multi as $key => $data)
				{
					$multipartbody .= $MPboundary . "\r\n";
					$multipartbody .= 'Content-Disposition: form-data; name="' . $key . '"; filename="' . $data['name'] . '"' . "\r\n";
					$multipartbody .= 'Content-Type: ' . $data['type'] . "\r\n\r\n";
					$multipartbody .= $data['data'] . "\r\n";
				}
				$multipartbody .= $endMPboundary . "\r\n";
				$postdata = $multipartbody;
			}
			else
			{
				$headers[]= 'Content-Type: application/x-www-form-urlencoded';
			}
		}

		$ret = '';
		$fp = jfsockopen($host, $port, $errno, $errstr, 5);

		if(! $fp)
		{
			$error = 'Open Socket Error';
			return '';
		}
		else
		{
			if( $method != 'GET' && $postdata )
			{
				$headers[] = 'Content-Length: ' . strlen($postdata);
			}
			fwrite($fp, implode("\r\n", $headers));
			fwrite($fp, "\r\n\r\n");
			if( $method != 'GET' && $postdata )
			{
				fwrite($fp, $postdata);
			}
						while(! feof($fp))
			{
				$ret .= fgets($fp, 1024);
			}
			if($this->_debug)
			{
				echo $ret;
			}
			fclose($fp);
			$pos = strpos($ret, "\r\n\r\n");
			if($pos)
			{
				$rt = trim(substr($ret , $pos+1));
				$responseHead = trim(substr($ret, 0 , $pos));
				$responseHeads = explode("\r\n", $responseHead);
				$httpcode = explode(' ', $responseHeads[0]);
				$this->_httpcode = $httpcode[1];
				if(strpos( substr($ret , 0 , $pos), 'Transfer-Encoding: chunked'))
				{
					$response = explode("\r\n", $rt);
					$t = array_slice($response, 1, - 1);

					return implode('', $t);
				}
				return $rt;
			}
			return '';
		}
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


	function userInfo()
	{
		$url = 'http:/'.'/open.t.qq.com/api/user/info?format='.$this->format.'&clientip='.client_ip();

		return $this->get($url);
	}

	function tAdd($content = '')
	{
		$url = 'http:/'.'/open.t.qq.com/api/t/add';

		$params = array();
		$params['format'] = $this->format;
		$params['content'] = $content;
		$params['clientip'] = client_ip();


		return $this->post($url,$params);
	}

	function tAddPic($content = '',$pic=array())
	{
		$url = 'http:/'.'/open.t.qq.com/api/t/add_pic';

		$params = array();
		$params['format'] = $this->format;
		$params['content'] = $content;
		$params['clientip'] = client_ip();
		$params['pic'] = $pic;

		return $this->post($url,$params,true);
	}

	function tReply($reid,$content)
	{
		$url = 'http:/'.'/open.t.qq.com/api/t/reply';

		$params = array(
            'format' => $this->format,
            'reid' => $reid,
            'content' => $content,
            'clientip' => client_ip(),
		);

		return $this->post($url,$params);
	}
}