<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename weibo.class.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 935240700 1038067893 38424 $
 *******************************************************************/



require_once "oauth.class.php";
require_once "fsockopenHttp.class.php";


class weibo
 {

	var $http;
	var $token;
	var $shal_method;
	var $consumer;
	var $storage;
	var $format = 'json';
	var $error;
	
	var $is_exit_error = true;
	var $last_req_url = '';
		var $req_error_count = 0;
	
	
	function weibo($oauth_token = NULL, $oauth_token_secret = NULL)
	{
        $this->sha1_method = new OAuthSignatureMethod_HMAC_SHA1();
        $this->consumer = new OAuthConsumer(XWB_APP_KEY, XWB_APP_SECRET_KEY);

		$this->setConfig();

		$this->http = new fsockopenHttp();
	}
	
		function setTempToken($oauth_token, $oauth_token_secret){
		$this->token = new OAuthConsumer($oauth_token, $oauth_token_secret);
	}

	
	function setConfig()
	{	
		        $sess = XWB_plugin::getUser();
		$tk = $sess->getToken();
		if (!empty($tk['oauth_token']) && !empty($tk['oauth_token_secret'])) {
            $this->token = new OAuthConsumer($tk['oauth_token'], $tk['oauth_token_secret']);
        } else {
            $this->token = NULL;
        }
	}
	
	
	function setError($error)
	{
		$errmsg = isset($error['error']) ? strtolower($error['error']) : 'UNDEFINED ERROR';
		if (strpos($errmsg, 'token_')) {
			$msg = XWB_plugin::L('xwb_token_error');
		}elseif (strpos($errmsg, 'user does not exists')) {
			$msg = XWB_plugin::L('xwb_user_not_exists');
		} elseif (strpos($errmsg, 'target weibo does not exist')) {
			$msg = XWB_plugin::L('xwb_target_weibo_not_exist');
		} elseif (strpos($errmsg, 'weibo id is null')) {
			$msg = XWB_plugin::L('xwb_weibo_id_null');
		} elseif (strpos($errmsg, 'system error')) {
			$msg = XWB_plugin::L('xwb_system_error');
		} elseif (strpos($errmsg, 'consumer_key')) {
			$msg = XWB_plugin::L('xwb_app_key_error');
		} elseif (strpos($errmsg, 'ip request')) {
			$msg = XWB_plugin::L('xwb_request_reach_api_maxium');
		} elseif (strpos($errmsg, 'update comment')) {
			$msg = XWB_plugin::L('xwb_comment_reach_api_maxium');
		} elseif (strpos($errmsg, 'update weibo')) {
			$msg = XWB_plugin::L('xwb_update_reach_api_maxium');
		} elseif (strpos($errmsg, 'high level')){
			$msg = XWB_plugin::L('xwb_access_resource_api_denied');
		} else {
			$msg = XWB_plugin::L('xwb_system_error');
		}
		
				$req_url = $this->last_req_url;
		XWB_plugin::LOG("[WEIBO CLASS]\t[ERROR]\t#{$this->req_error_count}\t{$msg}\t{$req_url}\tERROR ARRAY:\r\n".print_r($error, 1));
				
		if (!$this->is_exit_error) {return false;}
		
		if( 'utf8' != strtolower(XWB_S_CHARSET) ){
			$msg = XWB_plugin::convertEncoding( $msg, XWB_S_CHARSET, 'UTF-8' );
		}
		XWB_plugin::showError($msg);
		
	}
	
	
	function logRespond( $url, $method, $respondCode, $respondResult = array() , $extraMsg = array() ){
		if( !defined('XWB_DEV_LOG_ALL_RESPOND') || XWB_DEV_LOG_ALL_RESPOND != true ){
			return 0;
		}
		
				$callURL = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '__UNKNOWN__';
		
				$oauth_short_url = str_replace( XWB_API_URL, '', ( strpos($url, '?') !== false ? substr( $url, 0, strpos($url, '?') ) : $url) );
		
		if( $respondCode == 0 ){
						$respondResult = '__CONNECTION MAYBE TIME OUT ?__';
		}elseif ( $respondCode == -1 ){
			$respondResult = '__CAN NOT CONNECT TO API SERVER; OR CREATE A WRONG OAUTH REQUEST URL. PLEASE INSPECT THE LOG__';
		}
		
		if( empty($respondResult) ){
			$respondResult = '__NO RESPOND RESULT__';
		}
		
				if( isset($extraMsg['triggered_error']) &&  empty($extraMsg['triggered_error']) ){
			unset($extraMsg['triggered_error']);
		}
		
		$time_process = isset($extraMsg['time_process']) ? round((float)$extraMsg['time_process'], 6) : 0;
		unset($extraMsg['time_process']);
		
		$error_count_log = '';
		if( $this->req_error_count > 0 ){
			$error_count_log = '[REQUEST ERROR COUNT IN THIS PHP LIFETIME] '. $this->req_error_count."\r\n";
		}
		
		$msg = $method. "\t".
				$respondCode. "\t".
				$time_process. " sec.\t".
				$oauth_short_url. "\t".
				"\r\n". str_repeat('-', 5). '[EXTRA MESSAGE START]'. str_repeat('-', 5)."\r\n".
				$error_count_log.
				'[CALL URL]'. $callURL. "\r\n".
				'[OAUTH REQUEST URL]'. $url. "\r\n".
				'[RESPOND RESULT]'. "\r\n". print_r($respondResult, 1). "\r\n\r\n".
				'[EXTRA LOG MESSAGE]'. "\r\n". print_r($extraMsg, 1). "\r\n".
				str_repeat('-', 5). '[EXTRA MESSAGE END]'. str_repeat('-', 5)."\r\n\r\n\r\n"
				;
		
		$logFile = XWB_P_DATA.'/oauth_respond_log_'. date("Y-m-d_H"). '.txt.php';
		XWB_plugin::LOG($msg, $logFile);
		
		return 1;
		
	}


	
	function getError($useType = 'array')
	{
		if ('array' == $useType) {
			return json_decode($this->error, true);
		}
		return $this->error;
	}


	
	
	 function getPublicTimeline($useType = true)
	 {
		$url = XWB_API_URL.'statuses/public_timeline.'.$this->format;
		$params = array();
		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	
	 function getHomeTimeline($count = null, $page = null, $since_id = null, $max_id = null, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/home_timeline.'.$this->format;
		$params = array();
		if ($since_id) {
			$params['since_id'] = $since_id;
		}
		if ($max_id) {
			$params['max_id'] = $max_id;
		}
		if ($count) {
			$params['count'] = $count;
		}
		if ($page) {
			$params['page'] = $page;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	
	 function getFriendsTimeline($count = null, $page = null, $since_id = null, $max_id = null, $useType = true)
	 {
		return $this->getHomeTimeline($count, $page, $since_id, $max_id, $useType);
	 }


	
	 function getUserTimeline($id = null, $user_id = null, $name = null, $since_id = null, $max_id = null, $count = null, $page = null, $useType = true)
	 {
		if ($id) {
			$url = XWB_API_URL.'statuses/user_timeline/'.$id.'.'.$this->format;
		} else {
			$url = XWB_API_URL.'statuses/user_timeline.'.$this->format;
		}

		$params = array();
		if ($user_id) {
			$params['user_id'] = $user_id;
		}
		if ($name) {
			$params['screen_name'] = $name;
		}
		if ($since_id) {
			$params['since_id'] = $since_id;
		}
		if ($max_id) {
			$params['max_id'] = $max_id;
		}
		if ($count) {
			$params['count'] = $count;
		}
		if ($page) {
			$params['page'] = $page;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	 
	 function getMentions($count = null, $page = null, $since_id = null, $max_id = null, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/mentions.'.$this->format;

		$params = array();
		if ($since_id) {
			$params['since_id'] = $since_id;
		}
		if ($max_id) {
			$params['max_id'] = $max_id;
		}
		if ($count) {
			$params['count'] = $count;
		}
		if ($page) {
			$params['page'] = $page;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	
	 function getCommentsTimeline($count = null, $page = null, $since_id = null, $max_id = null, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/comments_timeline.'.$this->format;

		$params = array();
		if ($since_id) {
			$params['since_id'] = $since_id;
		}
		if ($max_id) {
			$params['max_id'] = $max_id;
		}
		if ($count) {
			$params['count'] = $count;
		}
		if ($page) {
			$params['page'] = $page;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	
	 function getCommentsByMe($count = null, $page = null, $since_id = null, $max_id = null, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/comments_by_me.'.$this->format;

		$params = array();
		if ($since_id) {
			$params['since_id'] = $since_id;
		}
		if ($max_id) {
			$params['max_id'] = $max_id;
		}
		if ($count) {
			$params['count'] = $count;
		}
		if ($page) {
			$params['page'] = $page;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	
	function getCommentsToMe($list = null, $count = null, $page = null, $since_id = null, $max_id = null, $useType = true)
	{
		if (empty($list)) {
			$url = XWB_API_URL.'statuses/comments_timeline.'.$this->format;

			$params = array();
			if ($since_id) {
				$params['since_id'] = $since_id;
			}
			if ($max_id) {
				$params['max_id'] = $max_id;
			}
			if ($count) {
				$params['count'] = $count;
			}
			if ($page) {
				$params['page'] = $page;
			}

			$response = $this->oAuthRequest($url, 'get', $params, $useType);
		} else {
			$response = $list;
		}

		if (is_array($response) && $response) {
						$storage = XWB_plugin::getUser();
			$result = array();
			foreach ($response as $var) {
				if ($var['user']['id'] == $storage->getInfo('sina_uid')) {
					continue;
				}
				$result[] = $var;
			}
			return $result;
		}
		return $response;
	}


	
	 function getComments($id, $count = null, $page = null, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/comments.'.$this->format;

		$params = array();
		$params['id'] = $id;

		if ($count) {
			$params['count'] = $count;
		}
		if ($page) {
			$params['page'] = $page;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	
	 function getCounts($ids, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/counts.'.$this->format;

		$params = array();
		if (is_array($ids)) {
			$params['ids'] = implode(',', $ids);
		} else {
			$params['ids'] = $ids;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	
	 function getUnread($useType = true)
	 {
		$url = XWB_API_URL.'/statuses/unread.'.$this->format;

		$params = array();
		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	 
	
	 function getStatuseShow($id, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/show/'.$id.'.'.$this->format;

		$params = array();

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	
	 function update($status, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/update.'.$this->format;

		$params = array();
		$params['status'] = urlencode($status);

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	 
	 function upload($status, $pic, $lat = null, $long = null, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/upload.'.$this->format;

		$params = array();
		$params['status'] = urlencode($status);
		$params['pic'] = '@'.$pic;

		if ($lat) {
			$params['lat'] = $lat;
		}
		if ($long) {
			$params['long'] = $long;
		}
		$response = $this->oAuthRequest($url, 'post', $params, $useType, true);

		return $response;
	 }


	
	 function destroy($id, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/destroy/'.$id.'.'.$this->format;

		$params = array();

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	
	 function repost($id, $status = null, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/repost.'.$this->format;

		$params = array();
		$params['id'] = $id;
		if ($status) {
			$params['status'] = urlencode($status);
		}

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	
	 function comment($id, $comment, $cid = null, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/comment.'.$this->format;

		$params = array();
		$params['id'] = $id;
		$params['comment'] = urlencode($comment);
		if ($cid) {
			$params['cid'] = $cid;
		}

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	
	 function comment_destroy($id, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/comment_destroy/'.$id.'.'.$this->format;

		$params = array();

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	 
	 function reply($id, $cid, $comment, $useType = true)
	 {
		$url = XWB_API_URL.'statuses/reply.'.$this->format;

		$params = array();
		$params['id'] = $id;
		$params['cid'] = $cid;
		$params['comment'] = urlencode($comment);

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }



	 
	
	function getUserShow($id = null, $user_id = null, $name = null, $useType = true)
	{
		if ($id) {
			$url = XWB_API_URL.'users/show/'.$id.'.'.$this->format;
		} else {
			$url = XWB_API_URL.'users/show.'.$this->format;
		}

		$params = array();
		if ($user_id) {
			$params['user_id'] = $user_id;
		}
		if ($name) {
			$params['screen_name'] = $name;
		}
		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	}


	
	 function getFriends($id = null, $user_id = null, $name = null, $cursor = null, $count = null, $useType = true)
	 {
		if ($id) {
			$url = XWB_API_URL.'statuses/friends/'.$id.'.'.$this->format;
		} else {
			$url = XWB_API_URL.'statuses/friends.'.$this->format;
		}

		$params = array();
		if ($user_id) {
			$params['user_id'] = $user_id;
		}
		if ($name) {
			$params['screen_name'] = $name;
		}
		if ($cursor) {
			$params['cursor'] = $cursor;
		}
		if ($count) {
			$params['count'] = $count;
		}


		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	
	 function getFollowers($id = null, $user_id = null, $name = null, $cursor = null, $count = null, $useType = true)
	 {
		if ($id) {
			$url = XWB_API_URL.'statuses/followers/'.$id.'.'.$this->format;
		} else {
			$url = XWB_API_URL.'statuses/followers.'.$this->format;
		}

		$params = array();
		if ($user_id) {
			$params['user_id'] = $user_id;
		}
		if ($name) {
			$params['screen_name'] = $name;
		}
		if ($cursor) {
			$params['cursor'] = $cursor;
		}
		if ($count) {
			$params['count'] = $count;
		}


		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }



	 
	
	 function getDirectMessages($count = null, $page = null, $since_id = null, $max_id = null, $useType = true)
	 {
		$url = XWB_API_URL.'direct_messages.'.$this->format;

		$params = array();
		if ($since_id) {
			$params['since_id'] = $since_id;
		}
		if ($max_id) {
			$params['max_id'] = $max_id;
		}
		if ($count) {
			$params['count'] = $count;
		}
		if ($page) {
			$params['page'] = $page;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	
	 function getSentDirectMessages($count = null, $page = null, $since_id = null, $max_id = null, $useType = true)
	 {
		$url = XWB_API_URL.'direct_messages/sent.'.$this->format;

		$params = array();
		if ($since_id) {
			$params['since_id'] = $since_id;
		}
		if ($max_id) {
			$params['max_id'] = $max_id;
		}
		if ($count) {
			$params['count'] = $count;
		}
		if ($page) {
			$params['page'] = $page;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	
	 function sendDirectMessage($id, $text, $name = null, $user_id = null, $useType = true)
	 {
		$url = XWB_API_URL.'direct_messages/new.'.$this->format;

		$params = array();
		$params['id'] = $id;
		$params['text'] = $text;
		if ($name) {
			$params['screen_name'] = $name;
		}
		if ($user_id) {
			$params['user_id'] = $user_id;
		}

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	
	 function deleteDirectMessage($id, $useType = true)
	 {
		$url = XWB_API_URL.'direct_messages/destroy/'.$id.'.'.$this->format;

		$params = array();

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }



	 
	
	 function createFriendship($id = null, $user_id = null, $name = null, $follow = null, $useType = true)
	 {
		if ($id) {
			$url = XWB_API_URL.'friendships/create/'.$id.'.'.$this->format;
		} else {
			$url = XWB_API_URL.'friendships/create.'.$this->format;
		}

		$params = array();
		if ($user_id) {
			$params['user_id'] = $user_id;
		}
		if ($name) {
			$params['screen_name'] = $name;
		}
		if ($follow) {
			$params['follow'] = $follow;
		}

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	
	 function deleteFriendship($id = null, $user_id = null, $name = null, $useType = true)
	 {
		if ($id) {
			$url = XWB_API_URL.'friendships/destroy/'.$id.'.'.$this->format;
		} else {
			$url = XWB_API_URL.'friendships/destroy.'.$this->format;
		}

		$params = array();
		if ($user_id) {
			$params['user_id'] = $user_id;
		}
		if ($name) {
			$params['screen_name'] = $name;
		}

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	
	 function existsFriendship($user_a, $user_b, $useType = true)
	 {
		$url = XWB_API_URL.'friendships/exists.'.$this->format;

		$params = array();
		$params['user_a'] = $user_a;
		$params['user_b'] = $user_b;

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	
	 function getFriendship($target_id = null, $target_screen_name = null, $source_id = null, $source_screen_name = null, $useType = true)
	 {
		$url = XWB_API_URL.'friendships/show.'.$this->format;

		$params = array();
		if ($target_id) {
			$params['target_id'] = $target_id;
		}
		if ($target_screen_name) {
			$params['target_screen_name'] = $target_screen_name;
		}
		if ($source_id) {
			$params['source_id'] = $source_id;
		}
		if ($source_screen_name) {
			$params['source_screen_name'] = $source_screen_name;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }



	 
	
	 function getFriendIds($id = null, $user_id = null, $name = null, $cursor = null, $count = null, $useType = true)
	 {
		if ($id) {
			$url = XWB_API_URL.'friends/ids/'.$id.'.'.$this->format;
		} else {
			$url = XWB_API_URL.'friends/ids.'.$this->format;
		}

		$params = array();
		if ($user_id) {
			$params['user_id'] = $user_id;
		}
		if ($name) {
			$params['screen_name'] = $name;
		}
		if ($cursor) {
			$params['cursor'] = $cursor;
		}
		if ($count) {
			$params['count'] = $count;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	
	 function getFollowerIds($id = null, $user_id = null, $name = null, $cursor = null, $count = null, $useType = true)
	 {
		if ($id) {
			$url = XWB_API_URL.'followers/ids/'.$id.'.'.$this->format;
		} else {
			$url = XWB_API_URL.'followers/ids.'.$this->format;
		}

		$params = array();
		if ($user_id) {
			$params['user_id'] = $user_id;
		}
		if ($name) {
			$params['screen_name'] = $name;
		}
		if ($cursor) {
			$params['cursor'] = $cursor;
		}
		if ($count) {
			$params['count'] = $count;
		}

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }



	 
	
	 function verifyCredentials($useType = true)
	 {
		$url = XWB_API_URL.'account/verify_credentials.'.$this->format;

		$params = array();
		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	
	 function getRateLimitStatus($useType = true)
	 {
		$url = XWB_API_URL.'account/rate_limit_status.'.$this->format;

		$params = array();
		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	
	 function endSession($useType = true)
	 {
		$url = XWB_API_URL.'account/end_session.'.$this->format;

		$params = array();
		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	
	 function updateProfileImage($image, $useType = true)
	 {
		$url = XWB_API_URL.'account/update_profile_image.'.$this->format;

		$params = array();
		$params['image'] = '@'.$image;

		$response = $this->oAuthRequest($url, 'post', $params, $useType, true);

		return $response;
	 }


	
	 function updateProfile($params, $useType = true)
	 {
		$url = XWB_API_URL.'account/update_profile.'.$this->format;

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	
	 function register($params, $useType = true)
	 {
		$url = XWB_API_URL.'account/register.'.$this->format;

		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }



	 
	
	 function getFavorites($page = null, $useType = true)
	 {
		$url = XWB_API_URL.'favorites.'.$this->format;

		$params = array();
		if ($page) {
			$params['page'] = $page;
		}
		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	 }


	
	 function createFavorite($id, $useType = true)
	 {
		$url = XWB_API_URL.'favorites/create.'.$this->format;

		$params = array();
		$params['id'] = $id;
		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	
	 function deleteFavorite($id, $useType = true)
	 {
		$url = XWB_API_URL.'favorites/destroy/'.$id.'.'.$this->format;

		$params = array();
		$response = $this->oAuthRequest($url, 'post', $params, $useType);

		return $response;
	 }


	 
    
    
    function accessTokenURL()  { return XWB_API_URL.'oauth/access_token'; }
    
    function authenticateURL() { return XWB_API_URL.'oauth/authenticate'; }
    
    function authorizeURL()    { return XWB_API_URL.'oauth/authorize'; }
    
    function requestTokenURL() { return XWB_API_URL.'oauth/request_token'; }

    
    function getRequestToken($oauth_callback = NULL, $useType = 'string')
	{
        $parameters = array();
        if (!empty($oauth_callback)) {
            $parameters['oauth_callback'] = $oauth_callback;
        }

        $request = $this->oAuthRequest($this->requestTokenURL(), 'GET', $parameters, $useType);
        $token = OAuthUtil::parse_parameters($request);
        $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
        return $token;
    }

    
    function getAuthorizeURL($token, $sign_in_with_Weibo = TRUE , $url)
	{
        if (is_array($token)) {
            $token = $token['oauth_token'];
        }
        if (empty($sign_in_with_Weibo)) {
            return $this->authorizeURL() . "?oauth_token={$token}&oauth_callback=" . urlencode($url);
        } else {
            return $this->authenticateURL() . "?oauth_token={$token}&oauth_callback=". urlencode($url);
        }
    }

	
	function getAuthorizeToken($token, $user, $password, $useType = 'json')
	{
        if (is_array($token)) {
            $token = $token['oauth_token'];
        }

		$url = $this->authorizeURL();
		$params = array();
		$params['oauth_token'] = $token;
		$params['oauth_callback'] = $useType;
		$params['display'] = 'web';
		$params['userId'] = $user;
		$params['passwd'] = $password;

		$this->http->setUrl($url);
		$this->http->setData($params);
		$response = $this->http->request();

		$code = $this->http->getState();
		if (200 != $code) {
			$this->setError($response);
								}else{
			$response = json_decode($response, true);
		}
		return $response;
	}

    
    function getAccessToken($oauth_verifier = FALSE, $oauth_token = false, $useType = 'string')
	{
        $parameters = array();
        if (!empty($oauth_verifier)) {
            $parameters['oauth_verifier'] = $oauth_verifier;
        }


        $request = $this->oAuthRequest($this->accessTokenURL(), 'GET', $parameters, $useType);
        $token = OAuthUtil::parse_parameters($request);
        $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
        return $token;
    }

    
    function oAuthRequest($url, $method, $parameters , $useType = true, $multi = false)
	{
		
		$request = OAuthRequest::from_consumer_and_token ( $this->consumer, $this->token, $method, $url, $parameters );
		$request->sign_request ( $this->sha1_method, $this->consumer, $this->token );
		$method = strtoupper ( $method );
		switch ($method) {
			case 'GET' :
								$this->last_req_url = $request->to_url ();
				$this->http->setUrl ( $request->to_url () );
				break;
			
			case 'POST' :
				$this->last_req_url = $request->get_normalized_http_url ();
				$this->http->setUrl ( $request->get_normalized_http_url () );
				$this->http->setData ( $request->to_postdata ( $multi ) );
				if ($multi) {
					$header_array = array ();
					$header_array2 = array ();
					if ($multi)
						$header_array2 = array ("Content-Type: multipart/form-data; boundary=" . $GLOBALS['__CLASS']['OAuthRequest']['__STATIC']['boundary'], "Expect: " );
					foreach ( $header_array as $k => $v ) {
						array_push ( $header_array2, $k . ': ' . $v );
					}
					if( !defined('CURLOPT_HTTPHEADER') ){
						define ('CURLOPT_HTTPHEADER', 10023);
					}
					$config = array (CURLOPT_HTTPHEADER => $header_array2 );
					$this->http->setConfig ( $config );
				}
				break;
				
			default:
				trigger_error('WRONG REQUEST METHOD IN WEIBO CLASS!', E_USER_ERROR);
				break;
		}
		
		$time_start = microtime ();
		$result = $this->http->request( strtolower($method) );
		$time_end = microtime ();
		$time_process = array_sum ( explode ( " ", $time_end ) ) - array_sum ( explode ( " ", $time_start ) );
		
		if ($useType === false || $useType === true) {
						if( version_compare(PHP_VERSION, '5.2.0', '>=') && version_compare(PHP_VERSION, '5.2.3', '<=') ){
				$result = json_decode(preg_replace('#(?<=[,\{\[])\s*("\w+"):(\d{6,})(?=\s*[,\]\}])#si', '${1}:"${2}"', $result), true);
			}else{
				$result = json_decode ( $result, true );
			}
		}
		$code = $this->http->getState ();
		
		if( 200 != $code ){
			$this->req_error_count++;
		}
		
		$this->logRespond ( $this->last_req_url,
							$method,
							( int ) $code,
							$result,
							array ('param' => $parameters,
									'time_process' => $time_process,
									'triggered_error' => $this->http->get_triggered_error (),
									'base_string' => $request->base_string,
									'key_string' => $request->key_string,
									)
							);
		
		if (200 != $code) {
			if (0 == $code) {
				$result = array("error_code" => "50000", "error" => "timeout" );
			}
			
			if( $useType === true ) {
				if ( !is_array( $result ) ) {
					$result = array ('error' => (string)$result, 'error_code'=> $code ) ;
				}
				$this->setError( $result );
			}
		}
		
		return $result;
        
    }
    
    
	
	function searchUser($params, $useType = true)
	{
		$url = XWB_API_URL.'users/search.'.$this->format;
		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	}


	
	function search($q = null, $page = null, $rpp = null, $callback = null, $geocode = null, $useType = true)
	{
		$url = XWB_API_URL.'search.'.$this->format;
		$params = array();
		if ($q) {
			$params['q'] = urlencode($q);
		}
		if ($page) {
			$params['page'] = $page;
		}
		if ($rpp) {
			$params['rpp'] = $rpp;
		}
		if ($callback) {
			$params['callback'] = $callback;
		}
		if ($geocode) {
			$params['geocode'] = $geocode;
		}
		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	}


	
	function searchStatuse($params, $useType = true)
	{
		$url = XWB_API_URL.'statuses/search.'.$this->format;
		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	}


	
	function getProvinces($useType = true)
	{
		$url = XWB_API_URL.'provinces.'.$this->format;
		$params = array();

		$response = $this->oAuthRequest($url, 'get', $params, $useType);

		return $response;
	}
}
