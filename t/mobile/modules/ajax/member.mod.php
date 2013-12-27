<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename member.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-04 18:49:37 106562807 1290780917 13526 $
 *******************************************************************/




if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $TopicLogic;
	
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		$this->Execute();
	}

	
	function Execute()
	{
        ob_start();

		switch($this->Code)
		{
			case 'login':
				$this->login();
				break;
			case 'register':
				$this->register();
				break;
			case 'edit_profile':
				$this->editProfile();
			case 'userinfo':
				$this->getUserInfo();
				break;
		}

        response_text(ob_get_clean());
	}
	
		function login()
	{
		$username = trim($this->Post['user_name']);
		$password = trim($this->Post['password']);
		if($username == "" || $password == "") {
			Mobile::error("UserName Or PassWord Is Empty", 320);
		}
		
		Load::functions('member');
		$rets = jsg_member_login($username, $password, $username);
        if ($rets['uid'] < 1) {
        	$_msgs = array(
        		'0' => array('tips' => 'Unknown Error', 'code' => 403),
        		'-1' => array('tips' => 'Username Error', 'code' => 321),
        		'-2' => array('tips' => 'Password Error', 'code' => 322),
        		'-3' => array('tips' => 'Input Error', 'code' => 323),         	);
        	Mobile::error($_msgs[$rets['uid']]['tips'], $_msgs[$rets['uid']]['code']);
        }
                
        $ret = $this->_getMemberInfoByUid($rets['uid']);
        Mobile::output($ret);
	}
	
		function register()
	{
		
				$password = $this->Post['password'];
		$email = $this->Post['email'];
		$nickname = $this->Post['nickname'];
		
		
		if(strlen($password) < 5) {
						Mobile::error('Password Error', 324);
		}
		
		if($password != $this->Post['password2']) {
						Mobile::error('Password Confirm Error', 325);
		}
		
		
		Load::functions('member');
		$uid = jsg_member_register($nickname, $password, $email);
		if($uid < 1) {
			$rets = array(
	        	'0' => array('tips' => 'Unknown Error', 'code' => 403),
	        	'-1' => array('tips' => 'User name or nickname is not legitimate', 'code' => 326),				        	'-2' => array('tips' => 'User name or nickname not allowed to register', 'code' => 327),		        	'-3' => array('tips' => 'User name or nickname has been in existence', 'code' => 328),			        	'-4' => array('tips' => 'Email is not valid', 'code' => 329),									        	'-5' => array('tips' => 'Email not allowed to register', 'code' => 330),						        	'-6' => array('tips' => 'Email already exists', 'code' => 331),									        );
	        
	       Mobile::error($rets[$uid]['tips'], $rets[$uid]['code']);
		}
        $ret = $this->_getMemberInfo($nickname);
        Mobile::output($ret);
	}
	
		function getUserInfo()
	{
		Mobile::is_login();
		$uid = trim($this->Get['uid']);
		$nick = getSafeCode(trim($this->Get['nick']));
		if (!empty($uid) && $uid > 0) {
			$member = DB::fetch_first("SELECT * FROM ".DB::table('members')." WHERE uid='{$uid}'");
		} else if (!empty($nick)) {
			$member = DB::fetch_first("SELECT * FROM ".DB::table('members')." WHERE nickname='{$nick}'");
		}
		if (empty($member)) {
						Mobile::error("No User", 300);
		}
		
		$ret = array(
			'uid' => $member['uid'],
			'nick' => $member['nickname'],
        	'gender' => $member['gender'],
        	'face' => face_get($member),
			'face_original' => face_get($member, 'big'),
			'signature' => $member['signature'],
		    'province' => $member['province'],
        	'city' => $member['city'],
			'fans_num' => $member['fans_count'],
        	'follower_num' => $member['follow_count'],
        	'mblog_num' => $member['topic_count'],
			'topic_num' => $member['tag_favorite_count'],
		);
		Mobile::output($ret);
	}
	
		function _getMemberInfoByUid($uid)
	{
		       	$member = DB::fetch_first("SELECT * FROM ".DB::table('members')." WHERE uid='".addslashes($uid)."'");
        $jsg_session = authcode("{$member['password']}\t{$member['uid']}",'ENCODE');
        $ret = array(
        	"jsg_session" => $jsg_session,
        	"username" => $member['nickname'],
        	'nick' => $member['nickname'],
        	'gender' => $member['gender'],
        	'face' => $member['face'],
        	'email' => $member['email'],
        	'province' => $member['province'],
        	'city' => $member['city'],
        	'signature' => $member['signature'],
        	'replys' => $member['replys'],
        	'forwards' => $member['forwards'],
        	'fans_count' => $member['fans_count'],
        	'follow_count' => $member['follow_count'],
        	'mblog_count' => $member['topic_count'],
        	'uid' => $member['uid'],
        );
        return $ret;
	}
	
		function _getMemberInfo($username)
	{
		       	$member = DB::fetch_first("SELECT * FROM ".DB::table('members')." WHERE nickname='".addslashes($username)."'");
        $jsg_session = authcode("{$member['password']}\t{$member['uid']}",'ENCODE');
        $ret = array(
        	"jsg_session" => $jsg_session,
        	"username" => $member['nickname'],
        	'nick' => $member['nickname'],
        	'gender' => $member['gender'],
        	'face' => $member['face'],
        	'email' => $member['email'],
        	'province' => $member['province'],
        	'city' => $member['city'],
        	'signature' => $member['signature'],
        	'replys' => $member['replys'],
        	'forwards' => $member['forwards'],
        	'fans_count' => $member['fans_count'],
        	'follow_count' => $member['follow_count'],
        	'mblog_count' => $member['topic_count'],
        	'uid' => $member['uid'],
        );
        return $ret;
	}
	
		function editProfile()
	{
		Mobile::is_login();
		
		$member_info = DB::fetch_first("SELECT * FROM ".DB::table('members')." where `uid`='".MEMBER_ID."'");
		if(!$member_info) {
			Mobile::error('No User', 300);
		}
		
		$gender = in_array(($gender = (int) $this->Post['gender']),array(1,2)) ? $gender : 0;

				$signature = $signature = trim(strip_tags($this->Post['signature']));
		
        		if(($filter_msg = filter($signature))) {
                        Mobile::error('Illegal Strings', 334);
        }

				$nickname = trim($this->Post['nickname']);
		if (!empty($nickname)) {
			if($nickname != $member_info['nickname']) {
				Load::functions('member');
				$ret = jsg_member_checkname($nickname, 1);
				if($ret < 1) {
					$rets = array(
			        	'0' => 335,					        	'-1' => 336,				        	'-2' => 337,				        	'-3' => 338,				        );
			        			        Mobile::error('Nick Error', $rets[$ret]);
				}
			}
		}

		$arr = array (
			'gender' => $gender,
			'signature' => addslashes($signature),
		);
		
		if (!empty($nickname)) {
			$arr['nickname'] = $nickname;
		}
		
		$this->_update($arr);
		
				$field = "author";
		if (!empty($_FILES) && $_FILES[$field]['name']) {
			$this->_uploadImage();
		}
		
		Mobile::success("Success");
	}
	
	function _update($arr)
	{
		$sets = array();
		if (is_array($arr)) {
			foreach ($arr as $key=>$val) {
				$val = addslashes($val);
				$sets[$key] = "`{$key}`='{$val}'";
			}

			if ($sets) {
				$sql = "update `".TABLE_PREFIX."members` set ".implode(" , ",$sets)." where `uid`='".MEMBER_ID."'";
				$this->DatabaseHandler->Query($sql);
			}
		}
	}
	
		function _uploadImage()
	{
		$field = 'author';
		Load::lib('io');
		$IoHandler = new IoHandler();
		$type = trim(strtolower(end(explode(".",$_FILES[$field]['name']))));
		if($type != 'gif' && $type != 'jpg' && $type != 'png')
		{
			Mobile::error('Illegal Strings', 350);
		}

		$image_name = substr(md5($_FILES[$field]['name']),-10).".{$type}";
		
		$sub_path = './cache/temp_images/'.$image_name{0}.'/';
		$image_path = RELATIVE_ROOT_PATH . $sub_path;
		$image_path_abs = ROOT_PATH.$sub_path;
		
		$image_file = $image_path . $image_name;
		$image_file_abs = $image_path_abs.$image_name;

		if (!is_dir($image_path_abs))
		{
			$IoHandler->MakeDir($image_path_abs);
		}

		Load::lib('upload');
		$UploadHandler = new UploadHandler($_FILES,$image_path_abs,$field,true);
		$UploadHandler->setMaxSize(2048);
		$UploadHandler->setNewName($image_name);
		$result=$UploadHandler->doUpload();
		if($result)
        {
			$result = is_image($image_file_abs);
		}


		if(!$result)
        {
			$IoHandler->RemoveDir($image_path_abs);
			 Mobile::error('Illegal Strings', 352);
		}


        
        list($w,$h) = getimagesize($image_file_abs);
        if($w > 601)
        {
            $tow = 599;
            $toh = round($tow * ($h / $w));

            $result = makethumb($image_file_abs,$image_file_abs,$tow,$toh);

            if(!$result)
            {
                $IoHandler->RemoveDir($image_path_abs);
                Mobile::error('Illegal Strings', 351);
            }
        }


		$up_image_path = addslashes($image_file_abs);
		
        $src_file = $image_file_abs;

        
        Load::lib('io');
        $IoHandler = new IoHandler();

        
        $image_path = RELATIVE_ROOT_PATH . 'images/face/' . face_path(MEMBER_ID);
        $image_path_abs = ROOT_PATH.'./images/face/' . face_path(MEMBER_ID);
        if (!is_dir($image_path_abs)) {
            $IoHandler->MakeDir($image_path_abs);
        }

        
        $image_file = $dst_file = $image_path . MEMBER_ID . '_b.jpg';
        $image_file_abs = $dst_file_abs = $image_path_abs . MEMBER_ID . '_b.jpg';
        $make_result = autothumbnail($src_file,$dst_file_abs, 128, 128);
        
        $image_file_small = $dst_file = $image_path . MEMBER_ID . '_s.jpg';
        $image_file_small_abs = $dst_file_abs = $image_path_abs . MEMBER_ID . '_s.jpg';
                $make_result = autothumbnail($src_file,$dst_file_abs, 50, 50);
        
        
                $face_url = '';
        if ($this->Config['ftp_on']) {
            $face_url = ConfigHandler::get('ftp','attachurl'); 
            $ftp_result = ftpcmd('upload',$image_file_abs);
            if ($ftp_result > 0) {
                ftpcmd('upload',$image_file_small_abs);
                $IoHandler->DeleteFile($image_file_abs);
                $IoHandler->DeleteFile($image_file_small_abs);
            }
        }
        

        
        $sql = "update `".TABLE_PREFIX."members` set `face_url`='{$face_url}', `face`='{$dst_file}' where `uid`='".MEMBER_ID."'";
		$this->DatabaseHandler->Query($sql);
		
        
        $IoHandler->DeleteFile($src_file);

        
        if($this->Config['extcredits_enable'] && MEMBER_ID > 0) {
			
			update_credits_by_action('face',MEMBER_ID);
		}
		Mobile::success("Success");
	}
	
}

?>
