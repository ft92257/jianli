<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename sinaFaceSync.class.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 334242255 718573988 10192 $
 *******************************************************************/



class sinaFaceSync{
	
	
	var $uid = 0;
	
	
	var $sina_userinfo = array();
	
	
	var $http;
	
	
	var $faceTempPath = array();
	
	
	var $faceSize = array(
				1 => array( 'h' => 180, 'w' => 180 ) ,
				2 => array( 'h' => 150, 'w' => 150 ) ,
				3 => array( 'h' => 54, 'w' => 54 ) ,
				);
	
	
	function sinaFaceSync(){
		$this->_getSinaUserInfo();
		$this->http = XWB_plugin::O('fsockopenHttp');
	}
	
	
	
	function _getSinaUserInfo(){
		$xwb_user = XWB_plugin::getUser();
		$sina_id = (int)($xwb_user->getInfo('sina_uid'));
		if( $sina_id > 0 ){
			$wb = XWB_plugin::getWB();
			$wb->is_exit_error = false;
			$this->sina_userinfo = (array)$wb->getUserShow($sina_id);
		}
	}
	
	
	
	function sync( $uid ){
		if ( true===UCENTER  ){
						include_once(ROOT_PATH . './api/uc_client/client.php');

			return $this->syncToUC($uid);
		}else{
			return $this->syncToNoUC($uid);
		}
	}
	
	
	function syncToUC( $uid ){
		$step1result = $this->_getFaceAndCreateTemp( $uid );
		if( $step1result < 0 ){
			return $step1result;
		}
		
		$postdata = $this->_createUCAvatarPostdata();
		if( count($postdata) != 3 ){
			return -10;
		}

		$this->http->setUrl( $this->_createUCUrl() );
		$this->http->setData( $postdata );
		$this->_delTempFace();     		$response = $this->http->request('post');
		$code = (int)$this->http->getState();

		if (200 !== $code) {
			return -11;
		}
		
		if( preg_match('/type="error"[ ]+value="([\-\+0-9]+)"/', $response, $matchErr) ){
			$matchErr = isset($matchErr[1]) ? (int)$matchErr[1] : -99;
			switch ($matchErr){
				case '-1':
					return -14;
				case '-2':
					return -12;
				default:
					return -15;
			}
		}
		
		$match = array();
		if ( !preg_match('/success="([0-9]+)"/', $response, $match) ){
			return -15;
		}
		
		if( !isset($match[1]) || (int)$match[1] != 1 ){
			return -13;
		}else{
			return 0;
		}
		
	}
	
	
	function syncToNoUC( $uid ){
		$step1result = $this->_getFaceAndCreateTemp( $uid );
		if( $step1result < 0 ){
			return $step1result;
		}
		
		$db = XWB_plugin::getDB();
		
				if( !file_exists($this->faceTempPath[3]) ){
			$this->_delTempFace();
			return -20;	
		}
		
		$_destPrefix = './images/face/'. jsg_face_path($this->uid) . $this->uid;
		$image_file_small = $destPath = $_destPrefix . '_s.jpg';
        $image_file = $_destPrefix . '_b.jpg';		
		$destRealPath = XWB_S_ROOT . $destPath;
		if (!is_dir(dirname($destPath))) 
		{
			jmkdir(dirname($destPath));
		}
		copy($this->faceTempPath[2],XWB_S_ROOT . $image_file);
		$copyresult = copy( $this->faceTempPath[3], $destRealPath );
		$this->_delTempFace();
		
		if( false == $copyresult ){
			return -22;
		}else{
		  
            
            $face_url = '';
            if($GLOBALS['_J']['config']['ftp_enable'])
            {
                $face_url = ConfigHandler::get('ftp','attachurl');
                
                $ftp_result = ftpcmd('upload',$image_file);
                if($ftp_result > 0)
                {
                    ftpcmd('upload',$image_file_small);
                    
                    @unlink($image_file);
                    @unlink($image_file_small);
                }
            }
            
    
		  
			$destPath = mysql_real_escape_string($destPath);
			$db->result_first("UPDATE " . XWB_S_TBPRE . "members SET `face_url`='$face_url', `face`= '". $destPath ."' WHERE uid = '" . $this->uid . "' LIMIT 1");
							
			return 0;
		}

	}
	
	
	
	function _getFaceAndCreateTemp( $uid ){
		
		if (! extension_loaded ( 'gd' )) {
			return -5;
		}
		
		if( empty($this->sina_userinfo) || !isset($this->sina_userinfo['id']) ){
			 return -1;
		}
		$this->uid = (int)$uid;
		if( $this->uid < 1 ){
			return -2;
		}
		
				$faceurl = str_replace($this->sina_userinfo['id'].'/50/', $this->sina_userinfo['id'].'/180/', $this->sina_userinfo['profile_image_url']);
		$body = $this->http->Get($faceurl);
		if( $this->http->getState() !== 200 || empty($body)  ){
			return -3;
		}
		
				$this->faceTempPath[1] = XWB_P_DATA. '/temp/'. $this->uid. '_1_xwb_face_temp.jpg';
		file_put_contents( $this->faceTempPath[1], $body, LOCK_EX );
		
				$imageSize = getimagesize($this->faceTempPath[1]);
		if( false === $imageSize || $imageSize[0] < 30 || $imageSize[1] < 30 ){
			$this->_delTempFace();
			return -4;
		}
		
				foreach ( $this->faceSize as $key => $size ){
						if( 1 === $key ){
				continue;
			}
			$imgProc = XWB_plugin::N('images');
			$imgProc->loadFile($this->faceTempPath[1]);    						$imgProc->resize($size['w'], $size['h']);
			$this->faceTempPath[$key] = XWB_P_DATA. '/temp/'. $this->uid. '_'. $key. '_xwb_face_temp.jpg';
			$imgProc->save($this->faceTempPath[$key]);
			$imgProc = null;      		}
		
		return 0;

	}
	
	
	
	
	function _delTempFace(){
		foreach($this->faceTempPath as $face){
			if( file_exists($face) ){
				@unlink($face);
			}
		}
	}
	
	
	
	function _createUCAvatarPostdata(){
		$postdata = array();
		$imageEncoder = XWB_plugin::N('imageEncoder');
		foreach ( $this->faceTempPath as $key => $face ){
			$content = file_get_contents($face);
			if(empty($content)){
				break;
			}
			$postkey = 'avatar'. $key;
			$postdata[$postkey] = $imageEncoder->flashdata_encode($content);
		}
		$imageEncoder = null;

		return $postdata;
	}
	
	
	
	function _createUCUrl(){
		
		$db = XWB_plugin::getDB();
		
		$ucuid = $db->result_first("select `ucuid` from ".XWB_S_TBPRE."members where `uid`='{$this->uid}' limit 1");
		
				$ucinput = authcode( 'uid='. $ucuid
						. '&agent='. md5($_SERVER['HTTP_USER_AGENT'])
						. '&time='. time() , 
						'ENCODE', UC_KEY );

				$posturl = UC_API.'/index.php?m=user'
					. '&a=rectavatar'
					. '&inajax=1'
					. '&appid='. UC_APPID
					. '&agent='. urlencode( md5($_SERVER['HTTP_USER_AGENT']) )
					. '&input='. urlencode($ucinput)
		;
		
		return $posturl;
	}
	
}


