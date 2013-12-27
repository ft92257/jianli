<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename oauth.class.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 919251399 1397044323 23734 $
 *******************************************************************/




include_once ROOT_PATH . 'include/xwb/lib/compat.inc.php';

$GLOBALS['__CLASS']['OAuthRequest']['__STATIC'] = array(
'POST_INPUT' => 'php:/'.'/input',
'OAuthRequest_version'=>'1.0a',
'boundary'=>''
);

if (! function_exists ( '___throwException' )) {
	function ___throwException($str) {
		trigger_error ( $str, 256 );
	}
}




class OAuthConsumer { 
    var $key; 
    var $secret; 

    function __construct($key, $secret) { 
        $this->OAuthConsumer($key, $secret);
    } 

    function OAuthConsumer($key, $secret) { 
        $this->key = $key; 
        $this->secret = $secret; 
    } 

    function __toString() { 
        return "OAuthConsumer[key=$this->key,secret=$this->secret]"; 
    } 
}


class OAuthToken { 
        var $key; 
    var $secret; 

     
    function __construct($key, $secret) { 
        $this->OAuthToken($key, $secret); 
    } 

    function OAuthToken($key, $secret) { 
        $this->key = $key; 
        $this->secret = $secret; 
    } 

     
    function to_string() { 
        return "oauth_token=" . 
            OAuthUtil::urlencode_rfc3986($this->key) . 
            "&oauth_token_secret=" . 
            OAuthUtil::urlencode_rfc3986($this->secret); 
    } 

    function __toString() { 
        return $this->to_string(); 
    } 
}


class OAuthSignatureMethod {
    function check_signature(&$request, $consumer, $token, $signature) {
        $built = $this->build_signature($request, $consumer, $token);
        return $built == $signature;
    }
}


class OAuthSignatureMethod_HMAC_SHA1 extends OAuthSignatureMethod {
    function get_name() {
        return "HMAC-SHA1";
    }

    function build_signature(&$request, $consumer, $token) {
        $base_string = $request->get_signature_base_string();

		        $request->base_string = $base_string;
        
        $key_parts = array(
            $consumer->secret,
            ($token) ? $token->secret : ""
        );

        		$key_parts = OAuthUtil::urlencode_rfc3986($key_parts);


		$key = implode('&', $key_parts);
		$request->key_string = $key;
		
		return base64_encode(_xwb_hash_hmac('sha1', $base_string, $key, true));
				
    }
}


class OAuthSignatureMethod_PLAINTEXT extends OAuthSignatureMethod {
    function get_name() {
        return "PLAINTEXT";
    }

    function build_signature(&$request, $consumer, $token) {
        $sig = array(
            OAuthUtil::urlencode_rfc3986($consumer->secret)
        );

        if ($token) {
            array_push($sig, OAuthUtil::urlencode_rfc3986($token->secret));
        } else {
            array_push($sig, '');
        }

        $raw = implode("&", $sig);
                $request->base_string = $raw;

        return OAuthUtil::urlencode_rfc3986($raw);
    }
}


class OAuthSignatureMethod_RSA_SHA1 extends OAuthSignatureMethod { 
    function get_name() { 
        return "RSA-SHA1"; 
    } 

    function fetch_public_cert(&$request) { 
                                                        ___throwException("fetch_public_cert not implemented"); 
    } 

    function fetch_private_cert(&$request) { 
                                        ___throwException("fetch_private_cert not implemented"); 
    } 

    function build_signature(&$request, $consumer, $token) { 
        $base_string = $request->get_signature_base_string(); 
        $request->base_string = $base_string; 

                $cert = $this->fetch_private_cert($request); 

                $privatekeyid = openssl_get_privatekey($cert); 

                $ok = openssl_sign($base_string, $signature, $privatekeyid); 

                openssl_free_key($privatekeyid); 

        return base64_encode($signature); 
    } 

    function check_signature(&$request, $consumer, $token, $signature) { 
        $decoded_sig = base64_decode($signature); 

        $base_string = $request->get_signature_base_string(); 

                $cert = $this->fetch_public_cert($request); 

                $publickeyid = openssl_get_publickey($cert); 

                $ok = openssl_verify($base_string, $decoded_sig, $publickeyid); 

                openssl_free_key($publickeyid); 

        return $ok == 1; 
    } 
}


class OAuthRequest {
    var $parameters; 
    var $http_method; 
    var $http_url; 
        var $base_string; 
    var $key_string;
        
    function __construct($http_method, $http_url, $parameters=NULL) {
        $this->OAuthRequest($http_method, $http_url, $parameters);
    }

    function OAuthRequest($http_method, $http_url, $parameters=NULL) { 
        @$parameters or $parameters = array(); 
        $this->parameters = $parameters; 
        $this->http_method = $http_method; 
        $this->http_url = $http_url; 
    }


    
    function from_request($http_method=NULL, $http_url=NULL, $parameters=NULL) {
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
                    file_get_contents($GLOBALS['__CLASS']['OAuthRequest']['__STATIC']['POST_INPUT']) 
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

        return new OAuthRequest($http_method, $http_url, $parameters);
    }

    
    function from_consumer_and_token($consumer, $token, $http_method, $http_url, $parameters=NULL) {
        @$parameters or $parameters = array();
        $defaults = array("oauth_version" => $GLOBALS['__CLASS']['OAuthRequest']['__STATIC']['OAuthRequest_version'], 
            "oauth_nonce" => OAuthRequest::generate_nonce(),
            "oauth_timestamp" => OAuthRequest::generate_timestamp(),
            "oauth_consumer_key" => $consumer->key);
        if ($token)
            $defaults['oauth_token'] = $token->key;

        $parameters = array_merge($defaults, $parameters);

        return new OAuthRequest($http_method, $http_url, $parameters);
    }

    function set_parameter($name, $value, $allow_duplicates = true) {
        if ($allow_duplicates && isset($this->parameters[$name])) {
                        if (is_scalar($this->parameters[$name])) {
                                                $this->parameters[$name] = array($this->parameters[$name]);
            }

            $this->parameters[$name][] = $value;
        } else {
            $this->parameters[$name] = $value;
        }
    }

    function get_parameter($name) {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
    }

    function get_parameters() {
        return $this->parameters;
    }

    function unset_parameter($name) {
        unset($this->parameters[$name]);
    }

    
    function get_signable_parameters() {
                $params = $this->parameters;

                if (isset($params['pic'])) {
            unset($params['pic']);
        }

        if (isset($params['image'])) {
            unset($params['image']);
        }

                        if (isset($params['oauth_signature'])) {
            unset($params['oauth_signature']);
        }

        return OAuthUtil::build_http_query($params);
    }

    
    function get_signature_base_string() {
        $parts = array(
            $this->get_normalized_http_method(),
            $this->get_normalized_http_url(),
            $this->get_signable_parameters()
        );

        
        $parts = OAuthUtil::urlencode_rfc3986($parts);

        return implode('&', $parts);
    }

    
    function get_normalized_http_method() {
        return strtoupper($this->http_method);
    }

    
    function get_normalized_http_url() {
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

    
    function to_url() {
        $post_data = $this->to_postdata();
        $out = $this->get_normalized_http_url();
        if ($post_data) {
            $out .= '?'.$post_data;
        }
        return $out;
    }

    
    function to_postdata( $multi = false ) {
        if( $multi )
    	return OAuthUtil::build_http_query_multi($this->parameters);
    else
        return OAuthUtil::build_http_query($this->parameters);
    }

    
    function to_header() {
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

    function __toString() {
        return $this->to_url();
    }


    function sign_request($signature_method, $consumer, $token) {
        $this->set_parameter(
            "oauth_signature_method",
            $signature_method->get_name(),
            false
        );
		$signature = $this->build_signature($signature_method, $consumer, $token);
        		$this->set_parameter("oauth_signature", $signature, false);
    }

    function build_signature($signature_method, $consumer, $token) {
        $signature = $signature_method->build_signature($this, $consumer, $token);
        return $signature;
    }

    
    function generate_timestamp() {
        		return time();
    }

    
    function generate_nonce() {
        		$mt = microtime();
        $rand = mt_rand();

        return md5($mt . $rand);     }
}


class OAuthUtil {

	public static $boundary = '';

    function urlencode_rfc3986($input) {
        if (is_array($input)) {
            return array_map(array('OAuthUtil', 'urlencode_rfc3986'), $input);
        } else if (is_scalar($input)) {
            return str_replace(
                '+',
                ' ',
                str_replace('%7E', '~', rawurlencode($input))
            );
        } else {
            return '';
        }
    }


                function urldecode_rfc3986($string) {
        return urldecode($string);
    }

                function split_header($header, $only_allow_oauth_parameters = true) {
        $pattern = '/(([-_a-z]*)=("([^"]*)"|([^,]*)),?)/';
        $offset = 0;
        $params = array();
        while (preg_match($pattern, $header, $matches, PREG_OFFSET_CAPTURE, $offset) > 0) {
            $match = $matches[0];
            $header_name = $matches[2][0];
            $header_content = (isset($matches[5])) ? $matches[5][0] : $matches[4][0];
            if (preg_match('/^oauth_/', $header_name) || !$only_allow_oauth_parameters) {
                $params[$header_name] = OAuthUtil::urldecode_rfc3986($header_content);
            }
            $offset = $match[1] + strlen($match[0]);
        }

        if (isset($params['realm'])) {
            unset($params['realm']);
        }

        return $params;
    }

        function get_headers() {
        if (function_exists('apache_request_headers')) {
                                    return apache_request_headers();
        }
                        $out = array();
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) == "HTTP_") {
                                                                $key = str_replace(
                    " ",
                    "-",
                    ucwords(strtolower(str_replace("_", " ", substr($key, 5))))
                );
                $out[$key] = $value;
            }
        }
        return $out;
    }

                function parse_parameters( $input ) {
        if (!isset($input) || !$input) return array();

        $pairs = explode('&', $input);

        $parsed_parameters = array();
        foreach ($pairs as $pair) {
            $split = explode('=', $pair, 2);
            $parameter = OAuthUtil::urldecode_rfc3986($split[0]);
            $value = isset($split[1]) ? OAuthUtil::urldecode_rfc3986($split[1]) : '';

            if (isset($parsed_parameters[$parameter])) {
                                
                if (is_scalar($parsed_parameters[$parameter])) {
                                                            $parsed_parameters[$parameter] = array($parsed_parameters[$parameter]);
                }

                $parsed_parameters[$parameter][] = $value;
            } else {
                $parsed_parameters[$parameter] = $value;
            }
        }
        return $parsed_parameters;
    }

    function build_http_query_multi($params) {
        if (!$params) return '';

				
                $keys = array_keys($params);
        $values = array_values($params);
                        $params = array_combine($keys, $values);

                        uksort($params, 'strcmp');

        $pairs = array();

        self::$boundary = $GLOBALS['__CLASS']['OAuthRequest']['__STATIC']['boundary'] = $boundary = uniqid('------------------');
		$MPboundary = '--'.$boundary;
		$endMPboundary = $MPboundary. '--';
		$multipartbody = '';

        foreach ($params as $parameter => $value) {
			if( ($parameter == 'pic' || $parameter == 'image') ){
				if(is_string($value) && $value{0} == '@')
				{
					$url = ltrim( $value , '@' );
					
										$ctx_userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; .NET CLR 3.5.30729)';
					if( version_compare(PHP_VERSION, '5.0.0', '>=') ){
						$ctx_header = "Accept: *"."/"."*\r\nAccept-Language: zh-cn\r\nUser-Agent: {$ctx_userAgent}\r\n";
						$ctx = stream_context_create(array('http'=>array('timeout'=>8,'method'=>'GET','header'=>$ctx_header)));
						$content = file_get_contents( $url, 0, $ctx);
					}else{
						@ini_set('user_agent', $ctx_userAgent);
						$content = file_get_contents( $url );
					}
					
					$filename = reset( explode( '?' , basename( $url ) ));
					$mime = OAuthUtil::get_image_mime($url);
				}
				elseif(is_array($value))
				{
					$content = $value[2];					$filename = $value[1];					$mime = $value[0];				}
	
				$multipartbody .= $MPboundary . "\r\n";
				$multipartbody .= 'Content-Disposition: form-data; name="'.$parameter.'"; filename="' . $filename . '"'. "\r\n";
				$multipartbody .= 'Content-Type: '. $mime . "\r\n\r\n";
				$multipartbody .= $content. "\r\n";
			} else {
				$multipartbody .= $MPboundary . "\r\n";
				$multipartbody .= 'Content-Disposition: form-data; name="'.$parameter."\"\r\n\r\n";
				$multipartbody .= $value."\r\n";
	
			}
        }

        $multipartbody .=  "$endMPboundary\r\n";
                                return $multipartbody;
    }

    function build_http_query($params) {
        if (!$params) return '';

                $keys = OAuthUtil::urlencode_rfc3986(array_keys($params));
        $values = OAuthUtil::urlencode_rfc3986(array_values($params));
        $params = array_combine($keys, $values);

                        uksort($params, 'strcmp');

        $pairs = array();
        foreach ($params as $parameter => $value) {
            if (is_array($value)) {
                                                natsort($value);
                foreach ($value as $duplicate_value) {
                    $pairs[] = $parameter . '=' . $duplicate_value;
                }
            } else {
                $pairs[] = $parameter . '=' . $value;
            }
        }
                        return implode('&', $pairs);
    }

    function get_image_mime( $file )
    {
    	$ext = strtolower(pathinfo( $file , PATHINFO_EXTENSION ));
    	switch( $ext )
    	{
    		case 'jpg':
    		case 'jpeg':
    			$mime = 'image/jpg';
    			break;

    		case 'png':
    			$mime = 'image/png';
    			break;

    		case 'gif':
    		default:
    			$mime = 'image/gif';
    			break;
    	}
    	return $mime;
    }
}
