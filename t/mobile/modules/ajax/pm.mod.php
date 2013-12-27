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
 * @Date 2012-04-23 17:49:34 229093088 1507275096 2640 $
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
				$this->send();
				break;
			case 'delmsg':
				$this->deleteMsg();
				break;		
			default:
								break;
		}
		$body=ob_get_clean();
		echo $body;
	}
	
		function getPmList()
	{
		$error_code = 0;
		$info =	$this->MyPmLogic->getPmList('inbox', array("per_page_num" => Mobile::config("perpage_pm")));
		if (!empty($info)) {
			$info['current_page'] = empty($info['current_page']) ? 1 : $info['current_page'];
			Mobile::output($info);
		} else {
			Mobile::error("No Error Tips", $info);
		}
	}
	
		function getHistoryList()
	{
		$uid = intval($this->Get['uid']);
		if (empty($uid)) {
			Mobile::error("No Error Tips", 321);
		}
		$info = $this->MyPmLogic->getHistoryList(MEMBER_ID, $uid, array("per_page_num" => Mobile::config("perpage_pm")));
		if (!empty($info)) {
			$info['current_page'] = empty($info['current_page']) ? 1 : $info['current_page'];
			Mobile::output($info);
		} else {
			Mobile::error("No Error Tips", 400);
		}
	}
	
		function send()
	{
		$uid = intval($this->Post['uid']);
				$member = DB::fetch_first("SELECT nickname FROM ".DB::table("members")." WHERE uid='{$uid}'");
		if (empty($member)) {
			Mobile::error("No User", 300);
		}
		$to_user = $member['nickname'];
		$data = array(
			'to_user' => $to_user,
			'message' => trim($this->Post['message']),
		);
		$ret = $this->MyPmLogic->pmSend($data);
		if ($ret == 0) {
			Mobile::success("Success");
		} else if ($ret == 1) {
			Mobile::error("Content not emtpy", 420);
		} else if ($ret == 2) {
			Mobile::error("Content not emtpy", 321);
		} else if (ret == 3) {
			Mobile::error("Content not emtpy", 321);
		}
		Mobile::error("Unkonw error", 250);
	}
	
		function deleteMsg()
	{
		$pmid = intval($this->Post['pmid']);
		$this->MyPmLogic->delMsg($pmid);
		Mobile::success("Success");
	}
}
?>