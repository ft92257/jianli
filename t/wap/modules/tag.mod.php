<?php
/**
 * 文件名：tag.mod.php
 * 版本号：1.0
 * 文件创建时间：2006-9-22 14:46:11
 * 最后修改时间：2008年2月26日15时53分
 * 作者：狐狸<foxis@qq.com>
 * 功能描述：标签操作模块
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class ModuleObject extends MasterObject
{
	
	var $ID = 0;

	var $ModuleConfig;

	var $TagLogic;

	var $Item;
	var $ItemConfig;
	var $ItemName;
	var $ItemUrl;

	var $TopicLogic;

	
	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->ModuleConfig = ConfigHandler::get('tag');

		$this->ID = (int) ($this->Get['id'] ? $this->Get['id'] : $this->Post['id']);

		$this->Item = isset($this->Get['item']) ? $this->Get['item'] : $this->Post['item'];
		if(false == isset($this->ModuleConfig['item_list'][$this->Item]))
		{
			$this->Item = $this->ModuleConfig['item_default'];
		}

		$this->ItemConfig = $this->ModuleConfig['item_list'][$this->Item];
		$this->ItemName = $this->ItemConfig['name'];
		$this->ItemUrl = $this->ItemConfig['url'];
		global $rewriteHandler;
		if($rewriteHandler) $this->ItemUrl = $rewriteHandler->formatURL($this->ItemUrl);

		$this->TagLogic = Tag($this->Item);

		
		$this->TopicLogic = Load::logic('topic', 1);

		$this->CacheConfig = ConfigHandler::get('cache');

		$this->ShowConfig = ConfigHandler::get('show');

		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		if ('mytag' == $this->Code) {
			$this->Mytag();
		} elseif ($this->Code) {
			$this->View();
		} else {
			$this->Main();
		}
		$Contents=ob_get_clean();

		$this->ShowBody($Contents);

	}

	function Main()
	{

		$timestamp = time();

		$uid = MEMBER_ID;
		$member = $this->_topicLogicGetMember($uid);
			
				$limit = $this->ShowConfig['tag_index']['hot'];
		$cache_id = "wap-tag/tag_hot";
		if ($limit>0 && false == ($tag_list = cache_file('get', $cache_id))) {
			$tag_ids = array();
			$tag_list = array();

			$sql = "SELECT DISTINCT(`tag_id`) AS `tag_id`, COUNT(item_id) AS `count` FROM `".TABLE_PREFIX."topic_tag` WHERE dateline>='".($timestamp - 86400*20)."' GROUP BY `tag_id` ORDER BY `count` DESC LIMIT {$limit}";
			$query = $this->DatabaseHandler->Query($sql);
			$tag_ids = array();
			while (false != ($row = $query->GetRow()))
			{
				$tag_ids[$row['tag_id']] = $row['tag_id'];
			}
				
			if($tag_ids) {

				$sql = "select `id`,`name`,`topic_count`,`status`,`total_count`,`tag_count` from `".TABLE_PREFIX."tag` where id in('".implode("','",$tag_ids)."') order by `topic_count` desc";
				$query = $this->DatabaseHandler->Query($sql);
				$tag_lis = array();
				while (false != ($row = $query->GetRow()))
				{
					$row['name'] = wap_iconv($row['name']);
					$tag_list[] = $row;
						
				}
			}
				
			cache_file('set', $cache_id, $tag_list, $this->CacheConfig['tag_index']['hot']);
		}

				$limit = $this->ShowConfig['tag_index']['guanzhu'];
		$cache_id = "wap-tag/tag_guanzu";
		if ($limit>0 && false == ($tag_guanzu = cache_file('get', $cache_id))) {
			$sql = "select `id`,`name`,`topic_count`,`tag_count` from `".TABLE_PREFIX."tag`  ORDER BY `tag_count` DESC LIMIT {$limit}";
			$query = $this->DatabaseHandler->Query($sql);
			$tag_guanzu = array();
			while (false != ($row = $query->GetRow()))
			{
				$row['name'] = wap_iconv($row['name']);
				$tag_guanzu[$row['id']] = $row;
			}
			
			cache_file('set', $cache_id, $tag_guanzu, $this->CacheConfig['tag_index']['guanzhu']);
		}

				$limit = $this->ShowConfig['tag_index']['day7'];
		$cache_id = "wap-tag/tag_r_day7";
		if ($limit>0 && false == ($tag_r_day7 = cache_file('get', $cache_id))) {
			$sql = "select `id`,`name`,`topic_count`,`tag_count` from `".TABLE_PREFIX."tag`  WHERE last_post>='".(time() - 86400 * 7)."' GROUP BY `topic_count` DESC LIMIT {$limit}";
						$query = $this->DatabaseHandler->Query($sql);
			$tag_r_day7 = array();
			while (false != ($row = $query->GetRow()))
			{
				$row['name'] = wap_iconv($row['name']);
				$tag_r_day7[$row['id']] = $row;
			}

			cache_file('set', $cache_id, $tag_r_day7, $this->CacheConfig['tag_index']['day7']);
		}

				$limit = $this->ShowConfig['tag_index']['day7_guanzhu'];
		$cache_id = "wap-tag/day7_guanzhu";
		if ($limit>0 && false == ($day7_guanzhu = cache_file('get', $cache_id))) {

			$sql = "select `id`,`name`,`topic_count`,`tag_count` from `".TABLE_PREFIX."tag`  WHERE `tag_count` > 0 and last_post>='".(time() - 86400 * 7)."' GROUP BY `tag_count` DESC LIMIT {$limit}";
			$query = $this->DatabaseHandler->Query($sql);
			$day7_guanzhu = array();
			while (false != ($row = $query->GetRow()))
			{
				$row['name'] = wap_iconv($row['name']);
				$day7_guanzhu[$row['id']] = $row;
			}
				
			cache_file('set', $cache_id, $day7_guanzhu, $this->CacheConfig['tag_index']['day7_guanzhu']);
		}

				$limit = $this->ShowConfig['tag_index']['tag_tuijian'];
		$cache_id = "wap-tag/tag_tuijian";
		if ($limit>0 && false == ($tag_tuijian = cache_file('get', $cache_id))) {

			$sql = "select `id`,`name`,`topic_count`,`tag_count` from `".TABLE_PREFIX."tag`  WHERE `status` = 1 order by `topic_count` desc  Limit {$limit}";
			$query = $this->DatabaseHandler->Query($sql);
			$tag_tuijian = array();
			while (false != ($row = $query->GetRow()))
			{
				$row['name'] = wap_iconv($row['name']);
				$tag_tuijian[] = $row;
			}

			cache_file('set', $cache_id, $tag_tuijian, $this->CacheConfig['tag_index']['tag_tuijian']);
		}


		
		$tag_hb = 'hb';

		$this->Title = '话题榜';
		include($this->TemplateHandler->Template('tag_index'));

	}

	function View()
	{
		$uid = MEMBER_ID;
		$member = $this->_topicLogicGetMember($uid);
		$params = array();


				$tag = getSafeCode($this->Code);
		if (!$tag) {
			$this->Messager("请输入正确的链接地址",null);
		}


		$f_rets = filter($tag, 0, 0);
		if($f_rets && $f_rets['error']) {
			$this->Messager("输入的话题  " . $f_rets['msg'], null);
		}

			
		$sql = "select * from `".TABLE_PREFIX."tag` where `name`='".addslashes($tag)."'";
		$query = $this->DatabaseHandler->Query($sql);
		$tag_info = $query->GetRow();


		$tag_id = $tag_info['id'];
		$total_record = $tag_info['topic_count'];

		
		$this->TopicLogic = Load::logic('topic', 1);

		if($total_record > 0)
		{
			$params['tag_id'] = $tag_id;

			$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($tag ? "&amp;code=".urlencode($tag) : "");
			$per_page_num = 10;
			$page_arr = wap_page($total_record,$per_page_num,$query_link,array('return'=>"Array"));
				
			$sql = "select `item_id` from `".TABLE_PREFIX."topic_tag` where `tag_id`='{$tag_id}' order by `item_id` desc {$page_arr['limit']}";
			$query = $this->DatabaseHandler->Query($sql);
			$topic_ids = array();
			while (false != ($row = $query->GetRow()))
			{
				$topic_ids[$row['item_id']] = $row['item_id'];
			}
				
			Load::logic("topic_list");
			$TopicListLogic = new TopicListLogic();
			$options = array(
				'limit' => $per_page_num,
				'order' => " dateline DESC ",
				'type' => get_topic_type(),
				'tid' => $topic_ids,
			);
			$info = $TopicListLogic->get_data($options, 'wap');
			$topic_list = array();
			if (!empty($info)) {
				$topic_list = wap_iconv($info['list']);
			}
				
			$topic_list_count = 0;
			if($topic_list) {
								$parent_id_list = array();
				foreach ($topic_list as $row) {
					if(0 < ($p = (int) $row['parent_id'])) {
						$parent_id_list[$p] = $p;
					}
					if (0 < ($p = (int) $row['top_parent_id'])) {
						$parent_id_list[$p] = $p;
					}
					unset($topic_ids[$row['tid']]);
						
					$topic_list_count++;
				}
				if ($topic_ids) {
					$topic_ids_count = count($topic_ids);
					$total_record = $total_record-$topic_ids_count;
						
					$sql = "delete from `".TABLE_PREFIX."topic_tag` where `item_id` in('".implode("','",$topic_ids)."')";
					$this->DatabaseHandler->Query($sql);
						
					$sql = "update `".TABLE_PREFIX."tag` set `topic_count`=`topic_count`-$topic_ids_count where `id`='{$tag_info['id']}'";
					if($total_record>=0 && $tag_info) {
						$this->DatabaseHandler->Query($sql);
					}
				}

				if($parent_id_list) {
					$parent_list = $this->_topicLogicGet($parent_id_list);
				}
							}
				
		}

		$show_config = ConfigHandler::get('show');

		$is_favorite = false;
		if($tag_info) {
			if(MEMBER_ID > 0) {
				$sql = "select * from `".TABLE_PREFIX."tag_favorite` where `uid`='".MEMBER_ID."' and `tag`='{$tag}'";
				$query = $this->DatabaseHandler->Query($sql);
				$is_favorite = $query->GetRow();
			}
				
			$sql = "select TF.uid,M.username,M.face_url,M.face,M.ucuid from `".TABLE_PREFIX."tag_favorite` TF left join `".TABLE_PREFIX."members` M on M.uid=TF.uid where TF.tag='{$tag}' order by TF.id desc limit 12";
			$query = $this->DatabaseHandler->Query($sql);
			$tag_favorite_list = array();
			while (false != ($row = $query->GetRow()))
			{
				$row['face'] = face_get($row);

				$tag_favorite_list[] = $row;
			}
		}

				$tag= wap_iconv($tag);
		 
		$tag_value = '#'.$tag.'#';

		$topic_count = $tag_info['topic_count'];


		$this->Title =$tag;
		include($this->TemplateHandler->Template('tag_list_topic_box'));
	}

	function _topicListBox($data)
	{
		
		$this->TopicLogic = Load::logic('topic', 1);

		foreach ($data  as $row)
		{
			$row = $this->_topicLogicMake($row);
				
			$topic_list[]=$row;
		}

		include($this->TemplateHandler->Template('tag_list_topic_box'));
	}
}

?>