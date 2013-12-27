<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename pm.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 1564211191 2109211541 1954 $
 *******************************************************************/




if (!defined('IN_JISHIGOU')) {
    exit('Access Denied');
}

class ModuleObject extends MasterObject
{	
	var $MyPmLogic;
	
	function ModuleObject($config)
	{
		
		$this->MasterObject($config);
		
		Mobile::logic('pm');
		$this->MyPmLogic = new MyPmLogic();
		Mobile::is_login();
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'list':
				$this->getPmList();
				break;
			case 'history':
				$this->getHistoryList();
				break;
			case 'send':
								break;		
			default:
								break;
		}
		$body=ob_get_clean();
		echo $body;
	}
	
		function getPmList()
	{
		$info = Mobile::convert($this->MyPmLogic->getPmList('inbox', array("per_page_num" => Mobile::config("perpage_pm"))));
		if (!empty($info)) {
			$pm_list = $info['pm_list'];
			$current_page = empty($info['current_page']) ? 1 : $info['current_page'];
			$next_page = $current_page + 1;
			$total_page = $info['total_page'];
			$list_count = count($info['pm_list']);
		} else {
			Mobile::show_message(400);
		}
		include(template('pm_list'));
	}
	
		function getHistoryList()
	{
		$uid = intval($this->Get['uid']);
		if (empty($uid)) {
			Mobile::show_message(300);
		}
		$info = Mobile::convert($this->MyPmLogic->getHistoryList(MEMBER_ID, $uid, array("per_page_num" => Mobile::config("perpage_pm"))));
		if (!empty($info)) {
			$pm_list = $info['pm_list'];
			$current_page = empty($info['current_page']) ? 1 : $info['current_page'];
			$next_page = $current_page + 1;
			$total_page = intval($info['total_page']);
			$list_count = count($info['pm_list']);
		} else {
			Mobile::show_message(400);
		}
		include(template('pm_list'));
	}
}
?>