<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename topic.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 551747741 1576422414 5015 $
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
	
	var $perpage;

	function ModuleObject($config)
	{
		
		$this->MasterObject($config);
		
		$this->ID = (int) ($this->Post['id'] ? $this->Post['id'] : $this->Get['id']);
		
		Mobile::logic('mblog');
		$this->MblogLogic = new MblogLogic();

		$this->CacheConfig = ConfigHandler::get('cache');
		
				if (!in_array($this->Code, array('new', 'hot_comments', 'hot_forwards'))) {
			Mobile::is_login();
		}
		
		if (empty($this->Code)) {
			$this->Code = "home";
		}
		$this->Execute();
	}

	
	function Execute()
	{	
		ob_start();
		switch($this->Code)
		{
			case 'favorite':
				$this->favorite();
				break;
			case 'detail':
				$this->detail();
				break;
			case 'comment':
				$this->getCommentList();
				break;
			case 'detail':
				$this->detail();
				break;
			case 'publish':
				$this->publish();
				break;
			case 'home':
			case 'at_my':
			case 'comment_my':
			case 'my_blog':
			case 'tag':
			case 'my_favorite':
			case 'new':
			case 'hot_comments':
			case 'hot_forwards':				
			default:
				$this->Main();
				break;
		}
		$body=ob_get_clean();
		echo $body;
	}

	function Main()
	{	
		extract($this->Get);
		extract($this->Post);
		$nick_name = $this->MemberHandler->MemberFields['nickname'];
		$user_name = $this->MemberHandler->MemberFields['username'];
		$this->Title = Mobile::convert(!empty($nick_name) ? $nick_name : $user_name);
		$type = $this->Code;
		$this->Get['limit'] = Mobile::config("perpage_mblog");
		$param_uid = 0;
		if ($this->Code == 'my_blog' || $this->Code == 'tag') {
			$param_uid = intval($this->Get['uid']);
			if ($param_uid < 1) {
				$param_uid = MEMBER_ID;
			}
		}
		
		$uncode_tag_key = Mobile::convert($this->Get['tag_key']);
		$tag_key = urlencode($uncode_tag_key);
		if (!empty($this->Get['tag_key'])) {
						$tag_id = DB::result_first("SELECT id FROM ".DB::table('tag')." WHERE name='{$this->Get['tag_key']}'");
		}
		$ret = Mobile::convert($this->MblogLogic->getListByType($type, $this->Get));
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
		include(template('topic_index'));
	}


		function detail()
	{
		$tid = intval($this->Get['tid']);
		if (empty($tid)) {
			Mobile::show_message(400);
		}
		$ret = Mobile::convert($this->MblogLogic->getDetail($tid, MEMBER_ID));
		$detail = array();
		if (is_array($ret)) {
			$detail = $ret['topic_info'];
			$parent_info = $ret['parent_info'];
		} else {
			Mobile::show_message($ret);
		}
		
		include(template('topic_detail'));
	}
	
		function getCommentList()
	{
		$tid = intval($this->Get['tid']);
		$topic_info = $this->MblogLogic->TopicLogic->Get($tid);
		if (empty($topic_info)) {
			Mobile::show_message(400);
		}

		if ($topic_info['replys'] > 0) {
			$param = array(
				'tid' => $tid,
				'limit' => Mobile::config("perpage_mblog"),
			);
			$ret = Mobile::convert($this->MblogLogic->getCommentList($param));
			$error_code = 0;
			if (is_array($ret)) {
				$topic_list = $ret['topic_list'];
				$parent_list = $ret['parent_list'];
				$total_record = $ret['total_record'];
				$list_count = count($topic_list);
				$max_tid = $ret['max_tid'];
				$next_page = $ret['next_page'];
			} 
		} else {
					}
		$huifu_flg = "is_huifu";
		include(template('topic_comment'));
	}
	
	function publish()
	{
		$topic_type = "web";
		if (in_array($this->Get['pt'], array('forward', 'reply'))) {
			$topic_type = $this->Get['pt'];
		}
		
		$mblog_content = "";
		if ($this->Get['pt'] == "new") {
			if (!empty($this->Get['atuid'])) {
				$atuid = intval($this->Get['atuid']);
				if ($atuid > 0) {
					$member = Mobile::convert($this->MblogLogic->TopicLogic->GetMember($atuid));
					$mblog_content = "@".$member['nickname'];
				}
			} else if (!empty($this->Get['tagid'])) {
				$tag_id = intval($this->Get['tagid']);
				$name = DB::result_first("SELECT name FROM ".DB::table('tag')." WHERE id='{$this->Get['tagid']}'");	
				if (!empty($name)) {
					$mblog_content = "#".$name."#";
				}
			}
		}
		
		if (!empty($mblog_content)) {
			$mblog_content = Mobile::convert($mblog_content);
		}
		
		$totid = intval($this->Get['totid']);
		
		include(template('publish'));
	}
}
?>