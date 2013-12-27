<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename people.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-04 18:49:38 102679338 257507273 10211 $
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

		
		$this->TopicLogic = Load::logic('topic', 1);
		
	
		Load::logic('validate_category');
		$this->ValidateLogic = new ValidateLogic();
		
		$this->CacheConfig = ConfigHandler::get('cache');
		
		

		$this->Execute();
	}


	

	function Execute()
	{
		switch($this->Code)
		{
			case 'view':
				$this->View();
				break;
			
						case 'province':
				$this->Province();
				break;
				
						case 'city':
				$this->City();
				break;
				
			default:
				$this->Code = 'people';
				$this->Main();
				break;
		}

		exit;
	}

	
	function Main()
	{
		$config = $this->Config;				$people_config = $config['validate_people_setting'];
				$category_list = array();
		$category_list = $this->ValidateLogic->CategoryList();
		
		
		$where =  " where `is_push` = 1 ";
		$limit =  $people_config['people_user_limit'] ? $people_config['people_user_limit'] : 20;
		$members = $this->ValidateLogic->CategoryUserList($where,$limit,$people_config['proviect_user_orderby']);					$member_list = $members['member'];		
				$member_uids = $members['uids'];
		
		
		$validated_member_uis = $this->ValidateLogic->getValidatedUid();
		if($validated_member_uis){
			Load::logic("topic_list");
			$TopicListLogic = new TopicListLogic($this);
	
			$per_page_num = $people_config['people_topic_limit'] ? $people_config['people_topic_limit'] : 20;
			
			$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&amp;code={$this->Code}&d={$d}" : "");
			
			$options = array(
				'perpage' 	=> $per_page_num,
				'page_url' 	=> $query_link,
				'type' 		=> 'first',
				'where' 	=> " `uid` in ('".implode("','",$validated_member_uis)."') ",
				'order' 	=> "`dateline` DESC ",
			);
			
			$topic_list = $TopicListLogic->get_data($options);
			
			if (!empty($topic_list)) 
			{
				$page_arr = $topic_list['page'];
				$topics = $topic_list['list'];
				$total_record = $topic_list['count'];
				unset($topic_list);
				$topic_list = $topics;
			}
		}
		
				
		$hot_members = $this->_memberlist("where `validate` != '' ",10,'fans_count');
		$hot_members_list = $hot_members['member'];

		$this->Title = '名人堂';
		include($this->TemplateHandler->Template("people"));

	}

	
	
		function View()
	{	
		
				$ids = empty($this->Get['ids']) ? 0 : intval($this->Get['ids']);
	
		if($ids < 1 && $pid < 1)
		{
			$this->Messager("未找到指定信息",-1,3);	
		}
	
		
				$where =  "where `is_push` = 1";
		$limit =  $people_config['proviect_user_limit'] ? $people_config['proviect_user_limit'] : 20;
		$members_arr = $this->ValidateLogic->CategoryUserList($where,$limit,$people_config['proviect_user_orderby']);
		
				$member_list = $members_arr['member'];
	
		if($ids)
		{
						$category_view = $this->ValidateLogic->CategoryView($ids);
			$categoryname = $category_view['category_name'];
	
						$where = "where `category_id` = '{$ids}' ";
			$return_uid = $this->ValidateLogic->CategoryUserList($where);	
			$category_uid = $return_uid['uids'];

			
					
			$member_num = 30;				
			
			$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&amp;code={$this->Code}&ids={$this->Get['ids']}" : "");						
	
			$where = "where `uid` in ('".implode("','",$category_uid)."') and `validate` !='' ";		
			
			$members = $this->_memberlist($where,$member_num,'fans_count',$query_link);			
			
						$memberlist = $members['member'];		
						$page_html = $members['pagearr']['html'];
			
			
		}
		
	
		$this->Title = "名人堂";
		include($this->TemplateHandler->Template("people_view"));
	}
	
	
		function Province()
	{
				$pid = (int) $this->Get['pid'];
		
		if($pid)
		{	
		
						$province_ary = DB::fetch_first("SELECT *  
						FROM ".DB::table('common_district')." 
						where `id` = '{$pid}' ");	
			
			$categoryname =	$province_ary['name'];

						$province_where_list = "where `upid` = 0";
			$province_ary = $this->ValidateLogic->CategoryCityList($province_where_list);
			
						$city_where_list = "where `upid` = '{$pid}'";
			$is_check_user = 1;
			$city_ary = $this->ValidateLogic->CategoryCityList($city_where_list,$is_check_user);

			$config = ConfigHandler::get();
			$people_config = $config['validate_people_setting'];

						$where =  "where `is_push` = 2";
			$limit =  $people_config['proviect_user_limit'] ? $people_config['proviect_user_limit'] : 20;
			$members_arr = $this->ValidateLogic->CategoryUserList($where,$limit,$people_config['proviect_user_orderby']);
			
						$member_list = $members_arr['member'];
			
						$member_uids = $members_arr['uids'];

			
						Load::logic("topic_list");
			$TopicListLogic = new TopicListLogic($this);
			$per_page_num = $people_config['proviect_topic_limit'] ? $people_config['proviect_topic_limit'] : 10;		
			$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&amp;code={$this->Code}&d={$d}" : "");			
			$options = array(
			
				'perpage' 	=> $per_page_num,
				'page_url' 	=> $query_link,
				'type' 		=> 'first',
				'where' 	=> " `uid` in('".implode("','",$member_uids)."') ",
				'order' 	=> "`dateline` DESC ",
			);
			
			$topic_list = $TopicListLogic->get_data($options);			
			if (!empty($topic_list)) 
			{
				$page_arr = $topic_list['page'];
				$topics = $topic_list['list'];
				$total_record = $topic_list['count'];
			}

			
	
						$limit = 9;
			if($city_ary)
			{
				foreach ($city_ary as $row)
				{
					$where_list['city'] = build_like_query('city',$row['name']);
			
					$where = ' where '.implode(' AND ',$where_list).' and `validate` !="" order by `fans_count` desc limit 0,9';
		
					$_list = $this->TopicLogic->GetMember($where,"`uid`,`ucuid`,`city`,`username`,`aboutme`,`nickname`,`face_url`,`face`,`validate`,`validate_category`");
					if($_list)
					{
						foreach ($_list as $row) {
			
							$row['validate_html'] = $row['validate_html'];
							$members[] = $row;
						}
					}
				}
			}
		}
	
	
		$this->Title = "名人堂";
		include($this->TemplateHandler->Template("people_province_view"));
	}
	
	
	
	
		function City()
	{	
				$pid = (int) $this->Get['pid'];
		
				$cid = (int) $this->Get['cid'];
		
		if($cid)
		{	
			
		
						$province_ary = DB::fetch_first("SELECT *  
						FROM ".DB::table('common_district')." 
						where `id` = '{$pid}' ");
			$province_id = 	$province_ary['id'];
			$province_name = $province_ary['name'];
			
			
						$city_where_list = "where `upid` = '{$pid}'";
			$city_ary = $this->ValidateLogic->CategoryCityList($city_where_list);
			$city_name = $city_ary[$cid]['name'];
			
						$config = ConfigHandler::get();
			$people_config = $config['validate_people_setting'];

			
			$cat_where =  "where `is_push` = 2";
			$cat_member_limit =  $people_config['proviect_user_limit'] ? $people_config['proviect_user_limit'] : 20;
			$cat_member = $this->ValidateLogic->CategoryUserList($cat_where,$cat_member_limit,$people_config['proviect_user_orderby']);			
						$cat_member_list = $cat_member['member'];			
						$cat_member_uids = $cat_member['uids'];

			
			
			$member_num = 30;				
			$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&amp;code={$this->Code}&pid={$this->Get['pid']}&cid={$this->Get['cid']}" : "");						
			$where_list['city'] = build_like_query('city',$city_name);	
			$where = ' where '.implode(' AND ',$where_list).' and `validate` !="" ';		
			$members = $this->_memberlist($where,$member_num,'fans_count',$query_link);			
						$member_list = $members['member'];		
						$page_html = $members['pagearr']['html'];
		
		}
		
		$this->Title = "名人堂";
		include($this->TemplateHandler->Template("people_city_view"));
	}
	
	
	
	function _memberlist($where='',$limit=20,$orderby='uid',$query_link='') 
	{		
		$per_page_num = $limit;
	
				$sql = " select count(*) as `total_record` from `".TABLE_PREFIX."members` {$where} ";
		$total_record = DB::result_first($sql);

				
		$page_arr = page ($total_record,$per_page_num,$query_link,array('return'=>'array',));
		
		$wherelist = " {$where} order by `{$orderby}` desc {$page_arr['limit']} ";

		$members = $this->TopicLogic->GetMember($wherelist,"`uid`,`ucuid`,`media_id`,`aboutme`,`username`,`nickname`,`face_url`,`face`,`validate`,`validate_category`,`province`,`city`");
		$members = Load::model('buddy')->follow_html($members, 'uid', 'follow_html2');

		$ret_ary = array('member'=>$members,'pagearr'=>$page_arr);
		
		return $ret_ary;
	}

	
	function _member()
	{		
		$member = $this->TopicLogic->GetMember(MEMBER_ID);
		
		return $member;
	}
}
?>
