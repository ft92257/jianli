<?php

/**
 * 搜索
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
	var $TopicLogic;

	var $TopicListLogic;
	
	var $cache_ids_limit=300;

	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->TopicLogic = Load::logic('topic', 1);

		$this->TopicListLogic = Load::logic('topic_list', 1);

		$this->Execute();
	}


	
	function Execute()
	{
		$load_file = array();
		switch ($this->Code) {
			case 'topic':
				$this->TopicSearch();
				break;
			case 'tag':
				$this->TagSearch();
				break;
			case 'user':
				$this->UserSearch();
				break;
			case 'usertag':
				$this->UserTagSearch();
				break;
			case 'vote':
				$this->VoteSearch();
				break;
			case 'qun':
				$this->QunSearch();
				break;
			default:
				$this->Code = '';
				$this->Main();
				break;
		}
	}

	function Main()
	{
		$member = jsg_member_info(MEMBER_ID);
				if ($member['medal_id']) {
			$medal_list = $this->TopicLogic->GetMedal($member['medal_id'],$member['uid']);
		}
		include($this->TemplateHandler->Template('search_list'));
	}

	function UserSearch()
	{
			$member = jsg_member_info(MEMBER_ID);
						if ($member['medal_id']) {
				$medal_list = $this->TopicLogic->GetMedal($member['medal_id'],$member['uid']);
			}

		$query_link = 'index.php?mod=search&code=user';
		$where = '';
		$where_list = array();
		$cache_time = 0;
		$cache_key = '';


				$tags = trim(get_param('tags'));
		if($tags) {
			$tags = getSafeCode($tags);
			$tags = $this->_filterKeyword($tags);

			$search_keyword = $tags;

						$sql = "select distinct(`uid`) as `uid` from `".TABLE_PREFIX."tag_favorite` where `tag` = '{$tags}' order by `id` desc ";
			$query = $this->DatabaseHandler->Query($sql);
			$uids = array();
			while (false != ($row = $query->GetRow())) {
				$uids[$row['uid']] = $row['uid'];
			}

			if(!empty($uids)) {
				$where = " WHERE `uid` IN (".jimplode($uids).") ";
				$query_link .= "&tags=" . urlencode($tags);
			}
		} else {
						$keyword = trim(get_param('nickname'));
			if ($keyword) {
				$keyword = getSafeCode($keyword);
				$keyword = $this->_filterKeyword($keyword);

				$search_keyword = $keyword;

				$where_list['keyword'] = false!==strpos($keyword,'@') ? "`email`='".addslashes("{$keyword}")."'" : build_like_query("nickname",$keyword);
				$query_link .= "&nickname=" . urlencode($keyword);
			}
			$province = (int) get_param('province');
			if($province > 0) {
				$province = $this->DatabaseHandler->ResultFirst("select `name` from ".TABLE_PREFIX."common_district where id = '$province'");
				$where_list['province'] = "`province`='".addslashes("{$province}")."'";
				$query_link .= "&province=" . urlencode($province);

				$city = (int) get_param('city');
				if ($city > 0) {
					$city = $this->DatabaseHandler->ResultFirst("select `name` from ".TABLE_PREFIX."common_district where id = '$city'");
					$where_list['city'] = "`city`='".addslashes("{$city}")."'";
					$query_link .= "&city=" . urlencode($city);

					$area = (int) get_param('area');
					if ($area > 0) {
						$area = $this->DatabaseHandler->ResultFirst("select `name` from ".TABLE_PREFIX."common_district where id = '$area'");
						$where_list['area'] = "`area`='".addslashes("{$area}")."'";
						$query_link .= "&area=" . urlencode($area);

						$street = (int) get_param('street');
						if ($street > 0) {
							$street = $this->DatabaseHandler->ResultFirst("select `name` from ".TABLE_PREFIX."common_district where id = '$street'");
							$where_list['street'] = "`street`='".addslashes("{$street}")."'";
							$query_link .= "&street=" . urlencode($street);
						}
					}
				}
			}
			$where = (empty($where_list) ? '' : ' WHERE '.implode(' AND ',$where_list).' ');


			$cache_time = 1800;
			$cache_key = "member-search-{$keyword}-{$province}-{$city}-{$area}-{$street}";
		}

				$total_record = 0;
		$member_list = array();
		$page_arr = array();
		$member_tag = array();
		if($where) {
			$rets = $this->_MemberList($where, $query_link, $cache_time, $cache_key);
			if($rets) {
				$total_record = $rets['total_record'];
				$member_list = $rets['member_list'];
				$page_arr = $rets['page_arr'];
				$member_tag = $rets['member_tag'];
			}
		}

		$this->Title = "用户搜索";
		include($this->TemplateHandler->Template('search_list'));
	}

		function UserTagSearch() {
		$member = jsg_member_info(MEMBER_ID);
				if ($member['medal_id']) {
			$medal_list = $this->TopicLogic->GetMedal($member['medal_id'],$member['uid']);
		}

		$query_link = 'index.php?mod=search&code=usertag';

				$usertag = trim(get_param('usertag'));
		$usertag = getSafeCode($usertag);
		if($usertag) {
			$usertag = $this->_filterKeyword($usertag);
			$search_keyword = $usertag;
			$cache_time = 1800;
			$cache_key = "usertag-search-{$usertag}";
			
			if(false === ($uids = cache_db('mget', $cache_key))) {
				$uids = array();
								$query = DB::query("SELECT `id` FROM ".DB::table('user_tag')." WHERE ".build_like_query('`name`', $usertag)." ORDER BY `id` DESC LIMIT {$this->cache_ids_limit} ");
				$tag_ids = array();
				while(false != ($row=DB::fetch($query))) {
					$tag_ids[$row['id']] = $row['id'];
				}
				if($tag_ids) {
					$query = DB::query("SELECT `uid` FROM ".DB::table('user_tag_fields')." WHERE `tag_id` IN ('".implode("','", $tag_ids)."') ORDER BY `id` DESC LIMIT {$this->cache_ids_limit} ");
					while (false != ($row = DB::fetch($query))) {
						$uids[$row['uid']] = $row['uid'];
					}
				}
				
				cache_db('mset', $cache_key, $uids, $cache_time);
			}

			if($uids) {
				$where = " WHERE `uid` IN ('".implode("','",$uids)."') ";
				$query_link .= "&usertag=" . urlencode($usertag);
			}
		}

				$total_record = 0;
		$member_list = array();
		$page_arr = array();
		$member_tag = array();
		if($where) {
			$rets = $this->_MemberList($where, $query_link);
			if($rets) {
				$total_record = $rets['total_record'];
				$member_list = $rets['member_list'];
				$page_arr = $rets['page_arr'];
				$member_tag = $rets['member_tag'];
			}
		}

				$mytag = $this->_MyUserTag(MEMBER_ID);

		$this->Title = "个人标签搜索";
		include($this->TemplateHandler->Template('search_list'));
	}


		function TopicSearch()
	{
		$member = jsg_member_info(MEMBER_ID);
				if ($member['medal_id']) {
			$medal_list = $this->TopicLogic->GetMedal($member['medal_id'],$member['uid']);
		}

		$per_page_num = 10;
		$query_link = 'index.php?mod=search&code=topic';
		$topic_parent_disable = false;
		$starttime = strtotime($this->Get['starttime']);
		$endtime   = strtotime($this->Get['endtime']);

		$keyword = trim($this->Get['topic']);
		$keyword = getSafeCode($keyword);
		if($keyword)
		{
			
						$return = $this->_filterKeyword($keyword);
			if(is_array($return)){
				$shield = $return['shield'];
				$keyword = $return['keyword'];
			}else{
				$keyword = $return;
			}
			
			$search_keyword = $keyword;
						$_GET['highlight'] = $search_keyword;

			$where_list['keyword'] = build_like_query('`content`,`content2`',$keyword);
			
			$cache_time = 300;
			$cache_key = 'topic-search-'.$keyword;
			if($cache_time > 0) {
				if(false === ($tids = cache_db('mget', $cache_key))) {
					$options = array(
						'count' => $this->cache_ids_limit,
						'fields' => 'tid',
						'where' => " {$where_list['keyword']} ",
						'order' => ' `dateline` DESC ',
					);
					$info = $this->TopicListLogic->get_data($options);
					$tids = array();
					if($info) {
						foreach($info['list'] as $v) {
							$tids[$v['tid']] = $v['tid'];
						}
					}
					
					cache_db('mset', $cache_key, $tids, $cache_time);
				}
				$where_list['keyword'] = " `tid` IN ('".implode("','", $tids)."') ";
			}
			$query_link .= "&topic=" . urlencode($keyword);
		}

		if ($starttime) {
			$where_list['starttime'] = "`dateline` > '{$starttime}'";
			$query_link .= "&starttime=" . urlencode($starttime);
		}

		if ($endtime) {
			$endtime += 86400;
			$where_list['endtime'] = "`dateline` < '{$endtime}'";
			$query_link .= "&endtime=" . urlencode($endtime);
		}

		if ($where_list && !$shield) {
			$where = (empty($where_list)) ? '' : ' '.implode(' AND ',$where_list).' ';
			$options = array(
				'where' => $where,
				'type' => get_topic_type(),
				'order' => ' `dateline` desc ',
				'page_url' => $query_link,
				'perpage' => $per_page_num,
			);
			$info = $this->TopicListLogic->get_data($options);
			$topic_list = array();
			$total_record = 0;
			if (!empty($info)) {
				$topic_list = $info['list'];
				$total_record = $info['count'];
				$page_arr = $info['page'];
			}
		}


		$topic_list_count = 0;
		if($topic_list)
		{
			$topic_list_count = count($topic_list);

			if(!$topic_parent_disable)
			{
								$parent_list = $this->TopicLogic->GetParentTopic($topic_list);
							}
		}

		$this->Title = "搜索微博";
		include($this->TemplateHandler->Template('search_list'));
	}


		function TagSearch()
	{
		$per_page_num = 10;
		$topic_parent_disable = false;
		$query_link = 'index.php?mod=search&code=tag';

		$tag = trim(get_param('tag'));
		if($tag) {
			$tag = getSafeCode($tag);
			$tag = $this->_filterKeyword($tag);
			$search_keyword = $tag;
						$_GET['highlight'] = $search_keyword;

			$cache_time = 600;
			$cache_key = "tag-search-{$tag}";
			if(false === ($topic_ids = cache_db('mget', $cache_key))) {
				$sql = "select `id` from `".TABLE_PREFIX."tag` WHERE ".build_like_query('`name`',$tag)." ORDER BY `last_post` DESC LIMIT {$this->cache_ids_limit} ";
				$query = $this->DatabaseHandler->Query($sql);
				$tag_id = array();
				while (false != ($row = $query->GetRow())) {
					$tag_id[$row['id']] = $row['id'];
				}	
				$topic_ids = array();
				if($tag_id) {
					$sql = "SELECT `item_id` FROM `".TABLE_PREFIX."topic_tag` WHERE `tag_id` in(".jimplode($tag_id).") ORDER BY `item_id` DESC LIMIT {$this->cache_ids_limit} ";
					$query = $this->DatabaseHandler->Query($sql);
					while (false != ($row = $query->GetRow())) {
						$topic_ids[$row['item_id']] = $row['item_id'];
					}
				}
				
				cache_db('mset', $cache_key, $topic_ids, $cache_time);
			}
			
			if($topic_ids) {
				$where = " `tid` in('".implode("','",$topic_ids)."') ";
			}
			$query_link .= "&tag=" . urlencode($tag);
		}

		if ($where) {
			$options = array(
				'where' => $where,
				'type' => get_topic_type(),
				'order' => ' `dateline` desc ',
				'page_url' => $query_link,
				'perpage' => $per_page_num,
			);
			$info = $this->TopicListLogic->get_data($options);
			$topic_list = array();
			$total_record = 0;
			if (!empty($info)) {
				$topic_list = $info['list'];
				$total_record = $info['count'];
				$page_arr = $info['page'];
			}

			$topic_list_count = 0;
			if($topic_list)
			{
				$topic_list_count = count($topic_list);

				if(!$topic_parent_disable)
				{
										$parent_list = $this->TopicLogic->GetParentTopic($topic_list);
									}
			}
		}

		$member = jsg_member_info(MEMBER_ID);
				if ($member['medal_id']) {
			$medal_list = $this->TopicLogic->GetMedal($member['medal_id'],$member['uid']);
		}

		$this->Title = "话题搜索";
		include($this->TemplateHandler->Template('search_list'));

	}

	
	function VoteSearch()
	{
		if (!$this->Config['vote_open']) {
			$this->Messager("当前站点没有开放投票功能");
		}
		$perpage = 10;
		$q = trim($this->Get['q']);
		$q = getSafeCode($q);
		$gets = array(
			'mod' => 'search',
			'code' => 'vote',
			'q' => $this->Get['q'],
		);
		$page_url = 'index.php?'.url_implode($gets);
		$count = 0;
		if (!empty($q)) {

			$q = $this->_filterKeyword($q);
			$search_keyword = $q;
			
			$VoteLogic = Load::logic("vote", 1);
			$where = ' '.build_like_query('v.subject', $q).' ';
			$order = " ORDER BY dateline DESC ";
			
			$cache_time = 3600;
			if($cache_time > 0) {
				$cache_key = "vote-search-{$q}";
				if(false === ($vids = cache_db('mget', $cache_key))) {
					$param = array(
						'where' => $where,
						'order' => $order,
						'count' => $this->cache_ids_limit,
					);
					$vote_info = $VoteLogic->find($param);
					$vids = array();
					if($vote_info) {
						foreach($vote_info['vote_list'] as $row) {
							$vids[$row['vid']] = $row['vid'];
						}
					}
					
					cache_db('mset', $cache_key, $vids, $cache_time);
				}
				
				$where = ($vids ? " v.`vid` IN ('".implode("','", $vids)."') " : '');
			}
			
			$vote_list = array();
			$page_arr['html'] = '';
			$uid_ary = array();
			if($where) {
				$param = array(
					'where' => $where,
					'order' => $order,
					'page' => true,
					'perpage' => $perpage,
					'page_url' => $page_url,
				);
				$vote_info = $VoteLogic->find($param);
				if (!empty($vote_info)) {
					$count = $vote_info['count'];
					$vote_list = $vote_info['vote_list'];
					$page_arr['html'] = $vote_info['page']['html'];
					$uid_ary = $vote_info['uids'];
				}
			}


						if (!empty($uid_ary)) {
				$members = $this->TopicLogic->GetMember($uid_ary);
			}
		}
		$member = jsg_member_info(MEMBER_ID);
				if ($member['medal_id']) {
			$medal_list = $this->TopicLogic->GetMedal($member['medal_id'],$member['uid']);
		}

		$this->Title = '投票搜索';
		include($this->TemplateHandler->Template('search_list'));
	}

		function QunSearch()
	{
		$qun_setting = $this->Config['qun_setting'];
		if (!$qun_setting['qun_open']) {
			$this->Messager("当前站点没有开放微群功能");
		}

		$perpage = 10;
		$q = trim($this->Get['q']);
		$q = getSafeCode($q);
		$gets = array(
			'mod' => 'search',
			'code' => 'qun',
			'q' => $this->Get['q'],
		);
		$page_url = 'index.php?'.url_implode($gets);
		$count = 0;
		if (!empty($q)) {
			$q = $this->_filterKeyword($q);
			$search_keyword = $q;
			$QunLogic = Load::logic("qun", 1);
			
						$where = ' gview_perm=0 AND '.build_like_query('name', $q).' ';
			$order = " ORDER BY dateline DESC ";
			
			$cache_time = 3600;
			if($cache_time > 0) {
				$cache_key = "qun-search-{$q}";
				if(false === ($qids = cache_db('mget', $cache_key))) {
					$query = DB::query("SELECT `qid` FROM ".DB::table('qun')." WHERE {$where} {$order} LIMIT {$this->cache_ids_limit} ");
					$qids = array();
					while(false != ($row=DB::fetch($query))) {
						$qids[$row['qid']] = $row['qid'];
					}
					
					cache_db('mset', $cache_key, $qids, $cache_time);
				}
				
				$where = ($qids ? " `gview_perm`='0' AND `qid` IN ('".implode("','", $qids)."') " : "");
			}
			
			
			$qun_list = array();
			if($where) {
				$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('qun')." WHERE {$where}");
				if ($count > 0) {
					$page_arr = page($count, $perpage, $page_url, array('return'=>'array'));
					$query = DB::query("SELECT * FROM ".DB::table('qun')." WHERE {$where} {$order} {$page_arr['limit']}");
					while ($value=DB::fetch($query)) {
						if (empty($value['icon'])) {
							$value['icon'] = $QunLogic->qun_avatar($value['qid'], 's');
						}
						$value['dateline'] = my_date_format2($value['dateline']);
						$qun_list[] = $value;
					}
				}
			}
		}

			$member = jsg_member_info(MEMBER_ID);
						if ($member['medal_id']) {
				$medal_list = $this->TopicLogic->GetMedal($member['medal_id'],$member['uid']);
			}

		$this->Title = '微群搜索';
		include($this->TemplateHandler->Template('search_list'));
	}

	function _MemberList($where='', $query_link='', $cache_time=0, $cache_key='') {
		$cache_time = max(0, (int) $cache_time);
		$cache_key = ($cache_key ? $cache_key : 'member-search-'.md5($where));
		if($cache_time > 0) {
			if(false === ($rets = cache_db('mget', $cache_key))) {
				$uids_limit = $this->cache_ids_limit;
				$rets = array();
				$rets['count'] = DB::result_first("SELECT COUNT(1) AS `count` FROM ".DB::table('members')." $where ");
				$uids = array();
				if($rets['count'] > 0) {
					$query = DB::query("SELECT `uid` FROM ".DB::table('members')." $where LIMIT $uids_limit ");
					while (false != ($row = DB::fetch($query))) {
						$uids[$row['uid']] = $row['uid'];
					}
				}
				$rets['uids'] = $uids;

				cache_db('mset', $cache_key, $rets, $cache_time);
			}
			$total_record = $rets['count'];
			$where = " WHERE `uid` IN ('".implode("','", $rets['uids'])."') ";
		} else {
			$total_record = DB::result_first("select count(*) as `total_record` from `".TABLE_PREFIX."members` {$where}");
		}

				$return = array();
		$per_page_num = 10;
		$order = " ORDER BY `lastactivity` DESC ";
		$member_list = array();
		$member_tag = array();
		if($total_record > 0) {
			$return['total_record'] = $total_record;

			$page_arr = page($total_record, $per_page_num, $query_link, array('return' => 'Array'));
			$return['page_arr'] = $page_arr;

			$member_list = Load::logic('topic', 1)->GetMember(" {$where} {$order} {$page_arr['limit']} ", "`uid`,`ucuid`,`username`,`nickname`,`face_url`,`face`,`fans_count`,`topic_count`,`province`,`city`,`validate`,`validate_category`");
			if($member_list) {
				$member_list = Load::model('buddy')->follow_html($member_list);

				$uids = array();
				foreach($member_list as $row) {
					$uids[$row['uid']] = $row['uid'];
				}
				if($uids) {
					$query = DB::query("SELECT * FROM ".DB::table('user_tag_fields')." WHERE `uid` IN ('".implode("','", $uids)."') ");
					while (false != ($row = DB::fetch($query))) {
						$member_tag[] = $row;
					}
				}
			}
		}
		$return['member_list'] = $member_list;
		$return['member_tag'] = $member_tag;

		return $return;
	}


		function _MyUserTag($uid) {
		$sql = "select * from `".TABLE_PREFIX."user_tag_fields` where `uid` = '{$uid}'";
		$query = $this->DatabaseHandler->Query($sql);
		$mytag = array();
		while(false != ($row = $query->GetRow())) {
			$mytag[] = $row;
		}

		return $mytag;
	}

	function _filterKeyword($keyword) {
		$keyword = str_replace(array('"', "'", '\\', ), '', $keyword);
		$keyword = jstripslashes($keyword);
		$keyword = strip_tags($keyword);
		$keyword = trim($keyword);

		if(2 > strlen($keyword)) {
			$this->Messager("请输入至少三个字符以上的关键词", -1);
		}

		$shield = ($this->Code == 'topic') ? 1 :0;
		$f_rets = filter($keyword, 0, 0, $shield);
		if($f_rets && $f_rets['error']) {
			$this->Messager("输入的搜索词 " . $f_rets['msg'], null);
		}

		if($f_rets && $f_rets['shield']) {
			return array('shield'=>1,'keyword'=>$keyword,);
		}

		$keyword = jaddslashes($keyword);

		return $keyword;
	}

}
?>
