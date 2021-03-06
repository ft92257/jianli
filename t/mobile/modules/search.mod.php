<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename search.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 108921958 188225523 2445 $
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
		
		Mobile::logic('mblog');
		$this->MblogLogic = new MblogLogic();

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
			case 'topic':
				$this->searchTopic();
				break;
			case 'user':
				$this->searchUser();
				break;			
			default:
				exit();
				break;
		}
		$body=ob_get_clean();
		echo $body;
	}
	
		function searchTopic() 
	{
		$this->Title = "";
		$this->Get['limit'] = Mobile::config("perpage_mblog");
		$keyword = addslashes($this->Get['q']);
		$ret = Mobile::convert($this->MblogLogic->search($this->Get));
		$error_code = 0;
		if (is_array($ret)) {
			$topic_list = $ret['topic_list'];
			$parent_list = $ret['parent_list'];
			$total_record = $ret['total_record'];
			$list_count = count($topic_list);
			$max_tid = $ret['max_tid'];
			$next_page = $ret['next_page'];
		} else {
			Mobile::show_message($ret);
		}
		
		include(template('search_topic'));
	}
	
		function searchUser()
	{
		$this->Title = "Search User";
		Mobile::logic('member');
		$MemberLogic = new MemberLogic();
		$q = trim($this->Get['q']);
		if (empty($q)) {
			Mobile::show_message($ret);
		} else {
			$param = array(
				'limit' => Mobile::config("perpage_member"),
				'nickname' => $q,
			);
			$keyword = urlencode($param['nickname']);
			$ret = Mobile::convert($MemberLogic->getMemberList($param));
			if (is_array($ret)) {
				$member_list = $ret['member_list'];
				$total_record = $ret['total_record'];
				$list_count = count($member_list);
				$max_tid = $ret['max_tid'];
			} else {
				Mobile::show_message($ret);
			}
		}
		include(template('search_user'));
	}

}
?>