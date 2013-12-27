<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename mblog.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 1586810451 312852650 13460 $
 *******************************************************************/




class MblogLogic
{
	var $TopicLogic;
	var $TopicListLogic;
	var $Config;
	
	function MblogLogic()
	{
		Load::logic('topic');
		$this->TopicLogic = new TopicLogic();
		
		Load::logic("topic_list");
		$this->TopicListLogic = new TopicListLogic();
		
		$this->Config = ConfigHandler::get();
	}
	
		function getListByType($type, $param)
	{
		$uid = intval($param['uid']);
		$uid < 1 && $uid = MEMBER_ID;
		
		$max_tid = intval($param['max_tid']);
		
				$perpage = intval($param['perpage']);
		
		$code = $type;
		$code_ary = array(
			'home',
			'at_my',
			'comment_my',
			'my_blog',
			'tag',
			'new',
			'hot_comments',
			'hot_forwards',
			'my_favorite',
		);
		
		if (!in_array($code, $code_ary)) {
						return 404;
		}
		
				if (isset($param['topic_parent_disable'])) {
			$topic_parent_disable = (bool) $param['topic_parent_disable'];
		} else {
			$topic_parent_disable = false;
		}

				$topic_list_get = false;
		
		$options = array();
		if ($perpage > 0) {
			$options['perpage'] = $perpage;
		}
		
		$limit = intval($param['limit']);
		if ($limit > 0) {
			$options['limit'] = $limit;
		}
		
		if ($code == "home") {
			
			
			
						$topic_myhome_time_limit = 0;
			if ($this->Config['topic_myhome_time_limit'] > 0) {
				$topic_myhome_time_limit = (time() - ($this->Config['topic_myhome_time_limit'] * 86400));
          		if ($topic_myhome_time_limit > 0) {
					$options['dateline'] = $topic_myhome_time_limit;
          		}
			}
			
						$options['uid'] = array($uid);
				
			            if ($this->Config['ajax_topic_time']) {
            	DB::query("update ".DB::table('members')." set `lastactivity`='".time()."' where `uid`='$uid'");
            }
			$sql_buddy_lastuptime = '';
			if($this->Config['topic_myhome_time_limit']) {
				$sql_buddy_lastuptime = " and `buddy_lastuptime`>'" . (time() - 86400 * $this->Config['topic_myhome_time_limit']) ."'";
			}
			$sql = "select `buddyid` from ".DB::table('buddys')." where `uid`='{$uid}' $sql_buddy_lastuptime ";
			$query = DB::query($sql);
			while($row = DB::fetch($query)) {
				$options['uid'][] = $row['buddyid'];
			}
		} else if ($code == 'at_my') {
			
			

			$sql = "select * from ".DB::table('topic_mention')." where `uid`='".MEMBER_ID."'";
			$query = DB::query($sql);
			$topic_ids = array();
			while ($row = DB::fetch($query)) {
				$topic_ids[$row['tid']] = $row['tid'];
			}
			if (empty($topic_ids)) {
								return 400;
			}
			$options['tid'] = $topic_ids;
		} else if ($code == "comment_my") {

			
			$options['where'] = "`touid`='".MEMBER_ID."' and `type` in ('both','reply')";
		} else if ($code == 'my_blog') {
			
			
			$uid = intval($param['uid']);
			if (empty($uid)) {
				$uid = MEMBER_ID;
			} else {
								$member = DB::fetch_first("SELECT * FROM ".DB::table('members')." WHERE uid='{$uid}'");
				if (empty($member)) {
										return 300;
				}
			}
			$options['uid'] = $uid;
		} else if ($code == "tag") {
			
			
			$tag_key = $param['tag_key'];
			
						$tag_id = DB::result_first("SELECT id FROM ".DB::table('tag')." WHERE name='{$tag_key}'");
			if (empty($tag_id)) {
								return 500;
			}
			
						$tag_info = DB::fetch_first("SELECT * FROM ".DB::table('tag')." WHERE id='{$tag_id}'");
			if (empty($tag_info)) {
				return 400;
			}
			
						$sql = "SELECT `item_id` FROM ".DB::table('topic_tag')." WHERE  `tag_id`='{$tag_id}'";
			$query = DB::query($sql);
			$topic_ids = array();
			while ($row = DB::fetch($query)) {
				$topic_ids[$row['item_id']] = $row['item_id'];
			}
			
			if (empty($topic_ids)) {
				return 400;
			}
			$options['tid'] = $topic_ids;
		} else if ($code == 'my_favorite') {
			
			
			$info = $this->_getMyFavorite(array('limit'=>$options['limit'], 'max_id' => $max_tid));
			if (empty($info)) {
				return 400;
			}
			$topic_list = $info['list'];
			$total_record = $info['count'];
			$topic_list_get = true;
		} else if ($code == 'new') {
						$options['type'] = get_topic_type();
		} else if ($code == 'hot_comments') {
						$time = 30 * 86400;
		  	$dateline = time() - $time;
			$options['type'] = 'first';
			$options['where'] = " `replys` > 0 AND  dateline >= {$dateline} ";
			$options['order'] = " `replys` DESC , `dateline` DESC ";
			$options['perpage'] = $param['limit'];
		} else if ($code == 'hot_forwards') {
						$time = 30 * 86400;
		  	$dateline = time() - $time;
		  	$options['type'] = 'first';
			$options['where'] = " `forwards` > 0 AND  dateline >= {$dateline} ";
			$options['order'] = " `forwards` DESC , `dateline` DESC ";
			$options['perpage'] = $param['limit'];	
		}
		
		$keys = array();
		if (!$topic_list_get) {
			if ($max_tid > 0) {
				$where = " tid<'$max_tid' ";
				if (empty($options['where'])) {
					$options['where'] = $where;
				} else {
					$options['where'] .= " AND ".$where;
				}
			} 
			$info = $this->TopicListLogic->get_data($options);
			$topic_list = array();
			$total_record = 0;
			if (!empty($info)) {
				$topic_list = $info['list'];
								$keys = array_keys($topic_list);
				$topic_list = array_values($topic_list);
				$total_record = $info['count'];
				$page_arr = $info['page'];
			}
		}
		$topic_list_count = 0;
		if ($topic_list) {
			$topic_list_count = count($topic_list);
			if (!$topic_parent_disable) {
				$parent_list = $this->TopicLogic->GetParentTopic($topic_list, ('mycomment' == $this->Code));
			}
			
			$tmp_ary = $topic_list;
			$tmp_topic = array_pop($tmp_ary);
			if ($code == 'my_favorite') {
				$max_tid = $tmp_topic['ft_id'];
			} else {
				if (!empty($keys)) {
					$max_tid = min($keys);
				} else {
					$max_tid = $tmp_topic['tid'];
				}
			}
						$result = array(
				'total_record' => $total_record, 
				'topic_list' => $topic_list,
				'parent_list' => $parent_list,
				'max_tid' => $max_tid,
				'next_page' => 0,
			);
			if (!empty($page_arr)) {
				$result['next_page'] = $page_arr['current_page'] + 1;
			}
			return $result;
		} else {
			return 400;
		}
	}
	
		function getDetail($tid, $uid = 0)
	{
		define("IN_JISHIGOU_MOBILE_TOPIC_DETAIL", true);
		$topic_info = $this->TopicLogic->Get($tid);
		if (empty($topic_info)) {
			return 400;
		}
		
				$roottid = $topic_info['roottid'];
		$parent_info = array();
		if ($roottid > 0) {
			$parent_info = $this->TopicLogic->Get($roottid);
			if ($parent_info['longtextid'] > 0) {
				$parent_info['content'] = DB::result_first("SELECT `longtext` FROM ".DB::table('topic_longtext')." WHERE tid='{$parent_info['tid']}'");
			}
		}
		
		$longid = $topic_info['longtextid'];
		if ($longid > 0) {
			$topic_info['content'] = DB::result_first("SELECT `longtext` FROM ".DB::table('topic_longtext')." WHERE tid='{$topic_info['tid']}'");
		}
		
		if (!empty($uid)) {
						$sql = "SELECT COUNT(*) from ".DB::table('topic_favorite')." WHERE `uid`='{$uid}' AND `tid`='{$topic_info['tid']}'";
			$topic_info['is_favorite'] = DB::result_first($sql);
		}
		
		$list = array(
			'topic_info' => $topic_info,
		);
		
		if (!empty($parent_info)) {
			$list['parent_info'] = $parent_info;	
		}
		
		return $list;
	}
	
	function search($param)
	{	
		$max_tid = intval($param['max_tid']);
		
				$perpage = intval($param['perpage']);
		
				if (isset($param['topic_parent_disable'])) {
			$topic_parent_disable = (bool) $param['topic_parent_disable'];
		} else {
			$topic_parent_disable = false;
		}

				$topic_list_get = false;
		
		$options = array();
		if ($perpage > 0) {
			$options['perpage'] = $perpage;
		}
		
		$limit = intval($param['limit']);
		if ($limit > 0) {
			$options['limit'] = $limit;
		}
		
		$keyword = trim($param['q']);
		$keyword = getSafeCode($keyword);		
		if ($keyword) {			
			$search_keyword = $keyword;
				  		$_GET['highlight'] = $search_keyword;
	  		
			$where_list['keyword'] = build_like_query('`content`,`content2`',$keyword);
   		}		

   		if ($where_list) {
			$where = (empty($where_list)) ? '' : ' '.implode(' AND ',$where_list).' ';	
			$options = array(
				'where' => $where,
				'type' => get_topic_type(),
				'order' => ' `dateline` desc ',
				'limit' => $limit,
			);
			if ($max_tid > 0) {
				$where = " tid<'$max_tid' ";
				if (empty($options['where'])) {
					$options['where'] = $where;
				} else {
					$options['where'] .= " AND ".$where;
				}
			}
			 
			$info = $this->TopicListLogic->get_data($options);
			$topic_list = array();
			$total_record = 0;
			if (!empty($info)) {
				$topic_list = $info['list'];
				$topic_list = array_values($topic_list);
				$total_record = $info['count'];
				$page_arr = $info['page'];
			}
			
			$topic_list_count = 0;
			if($topic_list){
				$topic_list_count = count($topic_list);
				if(!$topic_parent_disable) {
										$parent_list = $this->TopicLogic->GetParentTopic($topic_list);
									}
				$tmp_ary = $topic_list;
				$tmp_topic = array_pop($tmp_ary);
				$max_tid = $tmp_topic['tid'];
				$result = array(
					'total_record' => $total_record, 
					'topic_list' => $topic_list,
					'parent_list' => $parent_list,
					'max_tid' => $max_tid,
					'next_page' => 0,
				);
				if (!empty($page_arr)) {
					$result['next_page'] = $page_arr['current_page'] + 1;
				}
				return $result;
			}
		}
		return 400;
	}
	
		function getCommentList($param)
	{
		$tid = $param['tid'];
		$max_tid = intval($param['max_tid']);
		$tids = $this->TopicLogic->GetReplyIds($tid);
		$limit = intval($param['limit']);
		if ($limit > 0) {
			$options['limit'] = $limit;
		}
		
				if (isset($param['topic_parent_disable'])) {
			$topic_parent_disable = (bool) $param['topic_parent_disable'];
		} else {
			$topic_parent_disable = false;
		}
		
		if ($tids) {
			$condition = " `tid` IN ('".implode("','",$tids)."') ";
			$options['where'] = $condition;
			if ($max_tid > 0) {
				$where = " tid<'$max_tid' ";
				if (empty($options['where'])) {
					$options['where'] = $where;
				} else {
					$options['where'] .= " AND ".$where;
				}
			}
			    		$info = $this->TopicListLogic->get_data($options);
			$topic_list = array();
			$total_record = 0;
			if (!empty($info)) {
				$topic_list = $info['list'];
				$topic_list = array_values($topic_list);
				$total_record = $info['count'];
				$page_arr = $info['page'];
			}
			
			$topic_list_count = 0;
			if($topic_list){
				$topic_list_count = count($topic_list);
				if(!$topic_parent_disable) {
										$parent_list = $this->TopicLogic->GetParentTopic($topic_list, 1);
									}
				$tmp_ary = $topic_list;
				$tmp_topic = array_pop($tmp_ary);
				$max_tid = $tmp_topic['tid'];
				$result = array(
					'total_record' => $total_record, 
					'topic_list' => $topic_list,
					'parent_list' => $parent_list,
					'max_tid' => $max_tid,
					'next_page' => 0,
				);
				if (!empty($page_arr)) {
					$result['next_page'] = $page_arr['current_page'] + 1;
				}
				return $result;
			}
		}
		return 400;
	}
	
		function _getMyFavorite($param)
	{
		$topic_list = array();
		
				$uid = MEMBER_ID;
		$total_record = DB::result_first("SELECT COUNT(*) FROM ".DB::table("topic_favorite")." WHERE uid='{$uid}'");
		$limit = $param['limit'];
		$max_id = $param['max_id'];
		if ($total_record > 0) {
			
			$where = " ";
			if ($max_id > 0) {
				$where = " AND TF.id<'{$max_id}' ";	
			}
			
						$sql = "select TF.dateline as favorite_time,TF.id as ft_id,T.* 
					from ".DB::table('topic_favorite')." TF 
					left join ".DB::table('topic')." T 
					on T.tid=TF.tid 
					where TF.uid='{$uid}' {$where}  
					order by TF.id desc Limit {$limit}";
			$query = DB::query($sql);
			while ($row = DB::fetch($query)) {
				if ($row['tid']<1) {
					continue;
				}
				$row['favorite_time'] = my_date_format2($row['favorite_time']);
				$row = $this->TopicLogic->Make($row);
				$topic_list[] = $row;
			}
			$ret = array(
				'list' => $topic_list,
				'total_record' => $total_record, 
			);
			return $ret;
		}
		return false;
	}
}


?>