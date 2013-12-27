<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename friend.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 1858235183 190035287 2622 $
 *******************************************************************/




if (!defined('IN_JISHIGOU')) {
    exit('Access Denied');
}

class ModuleObject extends MasterObject
{
	var $ShowConfig;

	var $CacheConfig;

	var $TopicLogic;
	
	var $FriendLogic;

	var $ID = '';

	function ModuleObject($config)
	{
		
		$this->MasterObject($config);
		
		$this->ID = (int) ($this->Post['id'] ? $this->Post['id'] : $this->Get['id']);
		
		Mobile::logic('mblog');
		$this->MblogLogic = new MblogLogic();
		
		Mobile::logic('friend');
		$this->FriendLogic = new FriendLogic(); 

		$this->CacheConfig = ConfigHandler::get('cache');
		
				$this->ShowConfig = ConfigHandler::get('show');
		
				Mobile::is_login();
		
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'follow':
				$this->follow();
				break;
			case 'fans':
				$this->fans();
				break;
			case 'blacklist':
				$this->blacklist();
				break;			
			default:
				exit();
				break;
		}
		$body=ob_get_clean();
		echo $body;
	}
	
	function follow()
	{
		$param = array(
			'limit' => Mobile::config("perpage_member"),
			'uid' => intval($this->Get['uid']),
		);
		$ret = Mobile::convert($this->FriendLogic->getFollowList($param));
		if (is_array($ret)) {
			$member_list = $ret['member_list'];
			$list_count = count($member_list);
			$total_record = $ret['total_record'];
			$max_id = $ret['max_id'];
		} else {
			Mobile::show_message($ret);
		}
		include(template('friend_list'));
	}
	
	function fans()
	{
		$param = array(
			'limit' => Mobile::config("perpage_member"),
			'uid' => intval($this->Get['uid']),
		);
		$ret = Mobile::convert($this->FriendLogic->getFansList($param));
		if (is_array($ret)) {
			$member_list = $ret['member_list'];
			$list_count = count($member_list);
			$total_record = $ret['total_record'];
			$max_id = $ret['max_id'];
		} else {
			Mobile::show_message($ret);
		}
		include(template('friend_list'));
	}
	
	function blacklist()
	{
		$param = array(
			'limit' => Mobile::config("perpage_member"),
		);
		$ret = Mobile::convert($this->FriendLogic->getBlackList($param));
		if (is_array($ret)) {
			$member_list = $ret['member_list'];
			$list_count = count($member_list);
			$total_record = $ret['total_record'];
			$max_id = $ret['max_id'];
		} else {
			Mobile::show_message($ret);
		}
		include(template('friend_list'));
	}

}
?>