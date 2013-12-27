<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename oauth.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 1451180238 658273155 9125 $
 *******************************************************************/



include_once ROOT_PATH . 'include/xwb/lib/oauth.class.php';

 
class QQOAuthRequest { 
    public $parameters; 
    private $http_method; 
    private $http_url; 
        public $base_string; 
    public static $version = '1.0'; 
    public static $POST_INPUT = ''; 

    function __construct($http_method, $http_url, $parameters=NULL) { 
        @$parameters or $parameters = array(); 
        $this->parameters = $parameters; 
        $this->http_method = $http_method; 
        $this->http_url = $http_url; 
        
        $this->POST_INPUT = 'php:/'.'/input';
    } 


     
    public static function from_request($http_method=NULL, $http_url=NULL, $parameters=NULL) { 
        $scheme = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on") 
            ? 'http' 
            : 'https'; 
        @$http_url or $http_url = $scheme . 
            ':/'.'/' . $_SERVER['HTTP_HOST'] . 
            ':' . 
            $_SERVER['SERVER_PORT'] . 
            $_SERVER['REQUEST_URI']; 
        @$http_method or $http_method = $_SERVER['REQUEST_METHOD']; 

                                        if (!$parameters) { 
                        $request_headers = OAuthUtil::get_headers(); 

                        $parameters = OAuthUtil::parse_parameters($_SERVER['QUERY_STRING']); 

                                    if ($http_method == "POST" 
                && @strstr($request_headers["Content-Type"], 
                    "application/x-www-form-urlencoded") 
            ) { 
                $post_data = OAuthUtil::parse_parameters( 
                    file_get_contents(self::$POST_INPUT) 
                ); 
                $parameters = array_merge($parameters, $post_data); 
            } 

                                    if (@substr($request_headers['Authorization'], 0, 6) == "OAuth ") { 
                $header_parameters = OAuthUtil::split_header( 
                    $request_headers['Authorization'] 
                ); 
                $parameters = array_merge($parameters, $header_parameters); 
            } 

        } 

        return new QQOAuthRequest($http_method, $http_url, $parameters); 
    } 

     
    public static function from_consumer_and_token($consumer, $token, $http_method, $http_url, $parameters=NULL) { 
		@$parameters or $parameters = array();
        $defaults = array("oauth_version" => QQOAuthRequest::$version, 
            "oauth_nonce" => QQOAuthRequest::generate_nonce(), 
            "oauth_timestamp" => QQOAuthRequest::generate_timestamp(), 
            "oauth_consumer_key" => $consumer->key); 
        if ($token) 
            $defaults['oauth_token'] = $token->key; 

        $parameters = array_merge($defaults, $parameters); 
		        return new QQOAuthRequest($http_method, $http_url, $parameters); 
    } 

    public function set_parameter($name, $value, $allow_duplicates = true) { 
        if ($allow_duplicates && isset($this->parameters[$name])) { 
                        if (is_scalar($this->parameters[$name])) { 
                                                $this->parameters[$name] = array($this->parameters[$name]); 
            } 

            $this->parameters[$name][] = $value; 
        } else { 
            $this->parameters[$name] = $value; 
        } 
    } 

    public function get_parameter($name) { 
        return isset($this->parameters[$name]) ? $this->parameters[$name] : null; 
    } 

    public function get_parameters() { 
        return $this->parameters; 
    } 

    public function unset_parameter($name) { 
        unset($this->parameters[$name]); 
    } 

     
    public function get_signable_parameters() { 
                $params = $this->parameters; 
        
                if (isset($params['pic'])) { 
            unset($params['pic']); 
        }
        
          if (isset($params['image'])) 
         { 
            unset($params['image']); 
        }

                        if (isset($params['oauth_signature'])) { 
            unset($params['oauth_signature']); 
        } 

        return OAuthUtil::build_http_query($params); 
    } 

     
    public function get_signature_base_string() { 
        $parts = array( 
            $this->get_normalized_http_method(), 
            $this->get_normalized_http_url(), 
            $this->get_signable_parameters() 
        ); 
        
        
        $parts = OAuthUtil::urlencode_rfc3986($parts); 
        return implode('&', $parts); 
    } 

     
    public function get_normalized_http_method() { 
        return strtoupper($this->http_method); 
    } 

     
    public function get_normalized_http_url() { 
        $parts = parse_url($this->http_url); 

        $port = @$parts['port']; 
        $scheme = $parts['scheme']; 
        $host = $parts['host']; 
        $path = @$parts['path']; 

        $port or $port = ($scheme == 'https') ? '443' : '80'; 

        if (($scheme == 'https' && $port != '443') 
            || ($scheme == 'http' && $port != '80')) { 
                $host = "$host:$port"; 
            } 
        return "$scheme:/"."/$host$path"; 
    } 

     
    public function to_url() { 
        $post_data = $this->to_postdata(); 
        $out = $this->get_normalized_http_url(); 
        if ($post_data) { 
            $out .= '?'.$post_data; 
        } 
        return $out; 
    } 

     
    public function to_postdata( $multi = false ) {
    if( $multi )
    	return OAuthUtil::build_http_query_multi($this->parameters); 
    else 
        return OAuthUtil::build_http_query($this->parameters); 
    } 

     
    public function to_header() { 
        $out ='Authorization: OAuth realm=""'; 
        $total = array(); 
        foreach ($this->parameters as $k => $v) { 
            if (substr($k, 0, 5) != "oauth") continue; 
            if (is_array($v)) { 
                ___throwException('Arrays not supported in headers'); 
            } 
            $out .= ',' . 
                OAuthUtil::urlencode_rfc3986($k) . 
                '="' . 
                OAuthUtil::urlencode_rfc3986($v) . 
                '"'; 
        } 
        return $out; 
    } 

    public function __toString() { 
        return $this->to_url(); 
    } 


	public function sign_request($signature_method, $consumer, $token) { 
        $this->set_parameter( 
            "oauth_signature_method", 
            $signature_method->get_name(), 
            false 
		);
		$signature = $this->build_signature($signature_method, $consumer, $token); 
		$this->set_parameter("oauth_signature", $signature, false); 
    } 

    public function build_signature($signature_method, $consumer, $token) { 
        $signature = $signature_method->build_signature($this, $consumer, $token); 
        return $signature; 
    } 

     
    private static function generate_timestamp() { 
		return time(); 
    } 

     
    private static function generate_nonce() { 
		$mt = microtime(); 
        $rand = mt_rand(); 

        return md5($mt . $rand);     } 
} 

?>
