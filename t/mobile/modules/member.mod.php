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
 * @Date 2012-04-23 17:49:34 1169591915 593939265 2234 $
 *******************************************************************/




if (!defined('IN_JISHIGOU')) {
    exit('Access Denied');
}

class ModuleObject extends MasterObject
{
	var $ShowConfig;

	var $CacheConfig;

	var $TopicLogic;
	
	var $MblogLogic;

	var $ID = '';

	function ModuleObject($config)
	{
		
		$this->MasterObject($config);
		
		$this->ID = (int) ($this->Post['id'] ? $this->Post['id'] : $this->Get['id']);
		
		Load::logic("topic");
		$this->TopicLogic = new TopicLogic();

				
				$this->ShowConfig = ConfigHandler::get('show');
		
				if ($this->Code != 'login') {
			Mobile::is_login();
		}
		
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case "login":
				$this->login();
				break;
			case "logout":
				$this->logout();
				break;
			case 'userinfo':
				$this->getUserInfo();
				break;		
			default:
				$this->Code = "userinfo";
				$this->getUserInfo();
				break;
		}
		$body=ob_get_clean();
		echo $body;
	}

	function Main()
	{	
		exit;
	}
	
		function getUserInfo()
	{
		$uid = intval($this->Get['uid']);
		if ($uid < 1) {
			$uid = MEMBER_ID;
		}
		$is_follow = false;
		$is_blacklist = false;
		if ($uid > 0) {
			$member = Mobile::convert($this->TopicLogic->GetMember($uid));
			if (empty($member)) {
				$error_code = 400;
			} else {
								if ($member['uid'] != MEMBER_ID) {
					$is_follow = chk_follow(MEMBER_ID, $member['uid']);
					
					if (!$is_follow) {
						Mobile::logic('friend');
						$FriendLogic = new FriendLogic();
						$is_blacklist = $FriendLogic->check($member['uid']);
					}
				}
			}
		} else {
			Mobile::show_message(400);
		}
		include(template('user_info'));
	}
	
	function login()
	{
		include(template('login'));
	}
	
	function logout()
	{	
		$rets = jsg_member_logout();
		$rets = jsg_member_login_extract();
		header("Location:index.php?mod=member&code=login");
		exit;
	}
}
?>