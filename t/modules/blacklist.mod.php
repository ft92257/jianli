<?php

/**
 * 黑名单
 *
 * @author 狐狸<foxis@qq.com>
 * @package jishigou.net
 */
if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class ModuleObject extends MasterObject
{

	function ModuleObject($config)
	{
		$this->MasterObject($config);

		
		$this->TopicLogic = Load::logic('topic', 1);

		$this->Execute();
	}


	
	function Execute()
	{
		switch ($this->Code) {

			default:
				$this->Code = '';
				$this->Main();
				break;
		}
	}

	function Main()
	{
				$uid = MEMBER_ID;

		if($uid < 1){
			$this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",'index.php?mod=login');
		}

		$member = jsg_member_info($uid);
		

		$sql = "select * from `".TABLE_PREFIX."blacklist` where `uid` = '".MEMBER_ID."' ";
		$query = $this->DatabaseHandler->Query($sql);
		$uids = array();
		while (false != ($row = $query->GetRow()))
		{
			$uids[$row['touid']] = $row['touid'];
		}

		if($uids)
		{
			$where = "where `uid` in (".jimplode($uids).")";
				
			$member_list = $this->_MemberList($where);


			if($uids && MEMBER_ID>0) {
								$sql = "select `uid`,`tid`,`content`,`dateline` from `".TABLE_PREFIX."topic` where `uid` in (".jimplode($uids).") group by `uid` order by `dateline` desc";
				$query = $this->DatabaseHandler->Query($sql);
				$topic_list = array();
				while (false != ($row = $query->GetRow())) {
					$row['content'] 	= cut_str($row['content'],100);
					$row['dateline'] 	= my_date_format2($row['dateline']);
					$topic_list[] = $row;
				}
			}
		}


				$sql="Select `id`,`group_name`,`group_count` from ".TABLE_PREFIX.'group'." where `uid` = ".MEMBER_ID."";
		$query = $this->DatabaseHandler->Query($sql);
		$group_list = array();
		while (false != ($row = $query->GetRow()))
		{
			$group_list[] = $row;
		}

		include($this->TemplateHandler->Template('blacklist'));
	}


	function _MemberList($where='')
	{

				$member_list = array();

		$sql = "select count(*) as `total_record` from `".TABLE_PREFIX."members` {$where}";
		$total_record = DB::result_first($sql);

		if($total_record > 0)
		{
			$_config = array (
				'return' => 'array',
			);
				
			$page_arr = page($total_record,$per_page_num,$query_link,$_config);
			$sql = "select `uid`,`ucuid`,`username`,`nickname`,`face_url`,`face`,`fans_count`,`topic_count`,`follow_count`,`province`,`city`,`validate` from `".TABLE_PREFIX."members` {$where} {$order} {$page_arr['limit']}";
			$query = $this->DatabaseHandler->Query($sql);
			$uids = array();
			while (false != ($row = $query->GetRow()))
			{
				$row['face'] = face_get($row);
				$member_list[] = $row;
			}

		}

		return $member_list;

	}
}
?>
