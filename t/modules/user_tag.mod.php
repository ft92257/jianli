<?php

/**
 * 个人标签
 *
 * @author 狐狸<foxis@qq.com>
 * @package jishigou.net
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class ModuleObject extends MasterObject
{

	function ModuleObject($config)
	{
		$this->MasterObject($config);

		if (MEMBER_ID < 1) {
			$this->Messager(null,$this->Config['site_url'] . "/index.php?mod=login");
		}

		
		$this->TopicLogic = Load::logic('topic', 1);

		$this->Member = $this->TopicLogic->GetMember(MEMBER_ID);

		$this->Execute();
	}


	
	function Execute()
	{
		ob_start();
		switch ($this->Code) {
			case 'seach':
				$this->Seach();
				break;
			default:
				$this->Main();
				break;
		}
		$body=ob_get_clean();

		$this->ShowBody($body);
	}

	function Main()
	{
		$this->Title = "我的标签";

		$this->Code = 'user_tag';
		$uids = (int) MEMBER_ID;
		$member = jsg_member_info($uids);
		if ($member['validate'] && $member['validate_extra'])
		{
			$act_list['validate_extra'] = '专题设置';
		}

		$sql = "Select * From `".TABLE_PREFIX."user_tag` Where id >= (Select floor(RAND() * (Select MAX(id) From `".TABLE_PREFIX."user_tag`)))  Order By id Limit 20;";
		$query = $this->DatabaseHandler->Query($sql);
		$user_tag = array();
		while(false != ($row = $query->GetRow()))
		{
			$user_tag[] = $row;
		}

				$sql = "select * from `".TABLE_PREFIX."user_tag_fields` where `uid` = '".MEMBER_ID."'";
		$query = $this->DatabaseHandler->Query($sql);
		$user_tag_fields = array();
		while(false != ($row = $query->GetRow()))
		{
			$user_tag_fields[] = $row;
		}

		include($this->TemplateHandler->Template('topic_user_tag'));

	}

		function Seach()
	{

		$per_page_num = 6;
		$query_link = 'index.php?mod=user_tag&code=seach';
		$order = " order by `fans_count` desc ";

		$tag_name = trim($this->Get['k']);
		$tag_name = getSafeCode($tag_name);

		if(empty($tag_name))
		{
			$this->Messager("请输入需要查找的标签",-1);
		}
				if ($tag_name) {
			if (strlen($tag_name) < 2) {
				$this->Messager("请输入至少2个字符的关键词",-1);
			}
			$where_list['tag_name'] = "`tag_name`='".addslashes("{$tag_name}")."'";
			$query_link .= "&k=" . urlencode($tag_name);
		}

				if($where_list)
		{
			$where = (empty($where_list)) ? null : ' WHERE '.implode(' AND ',$where_list).' ';
			$sql = "select * from `".TABLE_PREFIX."user_tag_fields` {$where} ";
			$query = $this->DatabaseHandler->Query($sql);
			$uids = array();
			$user_tag_fields = array();
			while(false != ($row = $query->GetRow()))
			{
				$user_tag_fields[] = $row;
				$uids[$row['uid']] = $row['uid'];
			}

			if(empty($uids))
			{
				include($this->TemplateHandler->Template('search_list'));
			}
		}

				$member_list = array();
		if ($uids) {

			$sql = "select count(*) as `total_record` from `".TABLE_PREFIX."members` where `uid` in (".jimplode($uids).")";
			$total_record = DB::result_first($sql);

			if($total_record > 0) {
				$_config = array (
						'return' => 'array',
				);

				$page_arr = page($total_record,$per_page_num,$query_link,$_config);

				$sql = "select `uid`,`ucuid`,`username`,`nickname`,`face_url`,`face`,`fans_count`,`topic_count`,`province`,`city`,`validate` from `".TABLE_PREFIX."members` where `uid` in(".jimplode($uids).") {$order} {$page_arr['limit']}";
				$query = $this->DatabaseHandler->Query($sql);
				$uids = array();
				while (false != ($row = $query->GetRow()))
				{
					$row['face'] = face_get($row);
					$member_list[$row['uid']] = $row;
					$uids[$row['uid']] = $row['uid'];
				}

				if($uids && MEMBER_ID>0) {
					$member_list = Load::model('buddy')->follow_html($member_list);
				}
			}

						$sql = "select * from `".TABLE_PREFIX."user_tag_fields` where `uid` in (".jimplode($uids).")";
			$query = $this->DatabaseHandler->Query($sql);
			$member_tag = array();
			while(false != ($row = $query->GetRow()))
			{
				$member_tag[] = $row;
			}

		}



				$sql = "select * from `".TABLE_PREFIX."user_tag_fields` where `uid` = '".MEMBER_ID."'";
		$query = $this->DatabaseHandler->Query($sql);
		$mytag = array();
		$mytag_ids = array();
		while(false != ($row = $query->GetRow()))
		{
			$mytag[] = $row;
			$mytag_ids[$row['tag_id']] = $row['tag_id'];
		}

		$this->Title = "个人标签 - {$tag_name}";
		include($this->TemplateHandler->Template('search_list'));
	}

}
?>
