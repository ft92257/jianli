<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename validate.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-04 18:49:37 1005005098 1389285360 5425 $
 *******************************************************************/





if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class ModuleObject extends MasterObject
{

	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->initMemberHandler();

		
		$this->TopicLogic = Load::logic('topic', 1);

		$this->CacheConfig = ConfigHandler::get('cache');


				Load::logic('validate_category');
		$this->ValidateLogic = new ValidateLogic($this);


				Load::logic('validate_extra');
		$this->ValidateExtraLogic = new ValidateExtraLogic();


		$this->Execute();
	}

	
	function Execute()
	{
		switch ($this->Code)
		{

						case 'validate_remark':
				$this->Validate_Remark();
				break;
					
							case 'validate_cement':
				$this->Validate_Cement();
				break;


							case 'validate_link':
				$this->Validate_Link();
				break;


							case 'validate_video':
				$this->Validate_Video();
				break;
					

							case 'validate_vote':
				$this->Validate_Vote();
				break;
					
					
							case 'category':
				$this->Ajax_Category();
				break;
					
			default:
				$this->Main();
		}
	}

	function Main()
	{
		response_text("正在建设中");
	}


		function Validate_Remark()
	{
		$code = 'validate_remark';

		$uid = $this->Post['uid'];

		$extra = $this->ValidateExtraLogic->get_info($uid);

		$data = $extra['data'];

		$remark_enable = $data['validate_remark']['enable'];
		$remark_content = $data['validate_remark']['content'];

		include($this->TemplateHandler->Template("member_validate_left_ajax"));
	}

		function Validate_Cement()
	{
		$code = 'validate_link';

		$uid = $this->Post['uid'];

		$extra = $this->ValidateExtraLogic->get_info($uid);

		$data = $extra['data'];

		$link_enable = $data['validate_link']['enable'];

		$link = $data['validate_link']['link'];

		include($this->TemplateHandler->Template("member_validate_left_ajax"));
	}

		function Validate_Link()
	{
		$code = 'validate_cement';

		$uid = $this->Post['uid'];

		$extra = $this->ValidateExtraLogic->get_info($uid);

		$data = $extra['data'];

		$cement_enable = $data['validate_cement']['enable'];
		$cement_content = $data['validate_cement']['content'];

		include($this->TemplateHandler->Template("member_validate_left_ajax"));
	}

		function Validate_Video()
	{
		$code = 'validate_video';

		$uid = $this->Post['uid'];

		$extra = $this->ValidateExtraLogic->get_info($uid);

		$data = $extra['data'];

		$video_enable = $data['validate_video']['enable'];

		$_title = $data['validate_video']['title'];
		$video_title = cut_str($data['validate_video']['title'],20);
		$video_list = $data['validate_video']['rlist'];

		include($this->TemplateHandler->Template("member_validate_left_ajax"));
	}


		function Validate_Vote()
	{

		$uid = $this->Post['uid'];

		if($this->Post['vid'])
		{
			$ajax_code = 'ajax_vote';
			$vid = empty($this->Post['vid']) ? 0 : intval($this->Post['vid']);
		}
		else
		{
			$code = 'validate_vote';

			$extra = $this->ValidateExtraLogic->get_info($uid);
			$data = $extra['data'];
			$vid = empty($data['validate_vote']['content']) ? 0 : intval($data['validate_vote']['content']);
		}


		Load::logic('vote');
		$this->VoteLogic = new VoteLogic();

				$vote = $this->VoteLogic->id2voteinfo($vid);

				if($vote){
			$vote['datelines'] = date('Y-m-d H:i',$vote['dateline']);
			$this->item_id = $vid;
			$option = $this->VoteLogic->process_detail($vote, MEMBER_ID);
			extract($option);
		}

		include($this->TemplateHandler->Template("member_validate_left_ajax"));
	}


		function Ajax_Category()
	{
		
		if($this->Post['list_type'] == 'category')
		{
			$code = 'category';
			$id = (int) $this->Post['id'];
			
			$category_list = $this->ValidateLogic->CategoryList($id);

		}

		if($this->Post['list_type'] == 'province')
		{

			$code = 'province';

			$proviect_type = $this->Config['validate_people_setting']['proviect_type'];

			

			if($proviect_type == 2)
			{
				$proviect_id = $this->Config['validate_people_setting']['proviect_id'];

				$where_list = " where `upid` = '{$proviect_id}' order by list ";
			}
			else
			{
				$where_list = " where `upid` = 0 order by list ";
			}

			
			$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."common_district {$where_list} ");
			while ($rsdb = $query->GetRow()){
				$province[$rsdb['id']]['id']  = $rsdb['id'];
				$province[$rsdb['id']]['name']  = $rsdb['name'];
			}


		}


		include($this->TemplateHandler->Template("member_validate_left_ajax"));
	}


}


?>
