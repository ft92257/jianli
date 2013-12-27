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
 * @Date 2012-04-23 17:49:34 2138079657 587368995 3805 $
 *******************************************************************/




if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $TopicLogic;
	var $FriendLogic;
	
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		Load::logic('topic');
		$this->TopicLogic = new TopicLogic($this);
		Mobile::logic('friend');
		$this->FriendLogic = new FriendLogic();

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
			case 'add_follow':
				$this->addFollow();
				break;
			case 'del_follow':
				$this->delFollow();
				break;
			case 'check_follow':
				$this->checkFollow();
				break;
			case 'add_blacklist':
				$this->addBlacklist();
				break;
			case 'del_blacklist':
				$this->delBlacklist();
				break;
			case 'check_blacklist':
				$this->checkBlacklist();
				break;
		}

        response_text(ob_get_clean());
	}
	
		function follow()
	{
		$param = array(
			'limit' => Mobile::config("perpage_member"),
			'uid' => intval($this->Get['uid']),
			'max_id' => intval($this->Get['max_id']),
		);
		$ret = $this->FriendLogic->getFollowList($param);
		if (is_array($ret)) {
			Mobile::output($ret);
		} else {
			Mobile::error("No Error Tips", $ret);
		}
	}
	
		function fans()
	{
		$param = array(
			'limit' => Mobile::config("perpage_member"),
			'uid' => intval($this->Get['uid']),
			'max_id' => intval($this->Get['max_id']),
		);
		$ret = $this->FriendLogic->getFansList($param);
		if (is_array($ret)) {
			Mobile::output($ret);
		} else {
			Mobile::error("No Error Tips", $ret);
		}
	}
	
	function blacklist()
	{
		$param = array(
			'limit' => Mobile::config("perpage_member"),
			'uid' => intval($this->Get['uid']),
			'max_id' => intval($this->Get['max_id']),
		);
		$ret = $this->FriendLogic->getBlackList($param);
		if (is_array($ret)) {
			Mobile::output($ret);
		} else {
			Mobile::error("No Error Tips", $ret);
		}
	}
	
		function addFollow()
	{
		$uid = intval($this->Get['uid']);
		$ret = $this->FriendLogic->addFollow($uid);
		if ($ret == 200) {
			Mobile::success("Success");
		} else {
			Mobile::error("Has a Error", $ret);
		}
	}
	
		function delFollow()
	{
		$uid = intval($this->Get['uid']);
		$ret = $this->FriendLogic->delFollow($uid);
		if ($ret == 200) {
			Mobile::success("Success");
		} else {
			Mobile::error("Has a Error", $ret);
		}
	}
	
		function addBlacklist()
	{
		$uid = intval($this->Get['uid']);
		$ret = $this->FriendLogic->addBlacklist($uid);
		if ($ret == 200) {
			Mobile::success("Success");
		} else {
			Mobile::error("Has a Error", $ret);
		}
	}
	
		function delBlacklist()
	{
		$uid = intval($this->Get['uid']);
		$ret = $this->FriendLogic->delBlacklist($uid);
		if ($ret == 200) {
			Mobile::success("Success");
		} else {
			Mobile::error("Has a Error", $ret);
		}

	}
	
		function checkBlacklist()
	{
		$uid = intval($this->Get['uid']);
		$ret = $this->FriendLogic->check($uid);
		if ($ret) {
			Mobile::success("Success", 552);
		} else {
			Mobile::success("Success", 553);
		}
	}
	
		function checkFollow()
	{
		$uid = intval($this->Get['uid']);
		$ret = $this->FriendLogic->checkFollow($uid);
		Mobile::success("Success", $ret);

	}
	
}

?>
