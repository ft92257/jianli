<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename topic_list.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-18 15:49:15 121111685 1519079273 26744 $
 *******************************************************************/




if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class TopicListLogic
{
	
	var $Config;

	var $TopicLogic;

	
	function TopicListLogic()
	{
		$this->Config = &Obj::registry("config");


		$this->TopicLogic = Load::logic('topic', 1);
	}

	
	function _process_param($data)
	{
		$d = array();
		if (!empty($data)) {
			if (is_array($data)) {
				$d = (array) $data;
			} else {
				$d = explode(',', $data);
			}
		}
		return $d;
	}

	
	function GetVipUid(){
		$uid = array();
		$sql = "select distinct(uid) from `".TABLE_PREFIX."validate_category_fields` where is_audit = 1 order by dateline desc limit 9 ";
		$query = DB::query($sql);
		while ($value = DB::fetch($query)) {
			$uid[$value['uid']] = $value['uid'];
		}
		return $uid;
	}

	
	function GetTypeTid($type,$uid=array(),$param,$caller='web'){
				$tid = array();
		$where = '';
		if($uid) {
			$where = " WHERE `uid` IN ('".implode("','", (array) $uid)."') ";
		}
		$where = $where ? $where." and `tid`!='' "  : " where `tid`!=''";
		if ($type == 'pic') {
			$count = DB::result_first("SELECT count(DISTINCT(`tid`)) FROM `".TABLE_PREFIX."topic_image` $where ORDER BY `id` DESC");
			$sql = "SELECT DISTINCT(`tid`) FROM `".TABLE_PREFIX."topic_image` $where ORDER BY `id` DESC ";
		} else if($type == 'video') {
			$count = DB::result_first("SELECT count(DISTINCT(`tid`)) FROM `".TABLE_PREFIX."topic_video` $where ORDER BY `id` DESC");
			$sql = "SELECT DISTINCT(`tid`) FROM `".TABLE_PREFIX."topic_video` $where ORDER BY `id` DESC ";
		} else if($type == 'music') {
			$count = DB::result_first("SELECT count(DISTINCT(`tid`)) FROM `".TABLE_PREFIX."topic_music` $where ORDER BY `id` DESC");
			$sql = "SELECT DISTINCT(`tid`) FROM `".TABLE_PREFIX."topic_music` $where ORDER BY `id` DESC ";
		} else if($type == 'vote') {
			$count = DB::result_first("SELECT count(DISTINCT(`tid`)) FROM `".TABLE_PREFIX."topic_vote` $where ORDER BY `tid` DESC");
			$sql = "SELECT DISTINCT(`tid`) FROM `".TABLE_PREFIX."topic_vote` $where ORDER BY `tid` DESC ";
		} else if ($type == 'event') {
			$count = DB::result_first("SELECT count(DISTINCT(`tid`)) FROM `".TABLE_PREFIX."topic_event` $where ORDER BY `tid` DESC");
			$sql = "SELECT DISTINCT(`tid`) FROM `".TABLE_PREFIX."topic_event` $where ORDER BY `tid` DESC ";
											}  else if ('attach' == $type){
		$count = DB::result_first("SELECT count(DISTINCT(`tid`)) FROM `".TABLE_PREFIX."topic_attach` $where ORDER BY `id` DESC");
		$sql = "SELECT DISTINCT(`tid`) FROM `".TABLE_PREFIX."topic_attach` $where ORDER BY `id` DESC ";
		} else {
			return array();
		}


		if ($caller == 'web') {
			$page_arr = page($count, $param['perpage'], $param['page_url'], array('return'=>'array', 'extra'=>$param['page_extra']));
		} else if ($caller == "wap") {
			$page_arr = wap_page($count, $param['perpage'], $param['page_url'], array('return'=>'array'));
		}

		$sql = $sql . $page_arr['limit'];
		$query = DB::query($sql);
		while ($value = DB::fetch($query)) {
			$tid[$value['tid']] = $value['tid'];
		}
		$return['tid'] = $tid;
		$return['count'] = $count;
		$return['page'] = $page_arr;
		return $return;
	}

	
	function filter($type = '', $alias = '')
	{
		$where = '';
		if ($type == 'pic') {
			$where = (empty($alias) ? ' ' : $alias.'.')." `imageid` > 0 ";
		} else if($type == 'video') {
			$where = (empty($alias) ? ' ' : $alias.'.')." `videoid` > 0 ";
		} else if($type == 'music') {
			$where = (empty($alias) ? ' ' : $alias.'.')." (`musicid` > 0) ";
		} else if ($type == 'vote') {
			$where = (empty($alias) ? ' ' : $alias.'.')." `item`='vote' ";
		} else if ($type == 'event') {
			$where = (empty($alias) ? ' ' : $alias.'.')." `item`='event' ";
		} else if ($type == 'fenlei') {
			$where = (empty($alias) ? ' ' : $alias.'.')." `item`='fenlei' ";
		} else if ($type == 'longtext') {
			$where = (empty($alias) ? ' ' : $alias.'.')." `longtextid` > 0 ";
		}
		return $where;
	}

	
	function get_data($param, $caller = "web")
	{
		$cache_time = max(0, (int) $param['cache_time']);
		if($cache_time) {
			$cache_key = ($param['cache_key'] ? $param['cache_key'] : 'topic-list-get-data-' . md5(serialize($param)) . "-{$caller}");
		}

		$where = $order = "";
		$tids = $uids = $usernames = $roottids = $totids = $touids = $tousernames = $types = $item_ids = array();
		$tids = $this->_process_param($param['tid']);
		$uids = $this->_process_param($param['uid']);
		$content = trim($param['content']);
		$content2 = trim($param['content2']);
		$kw_content = trim($param['kw_content']);

				$filter = trim($param['filter']);
		$roottids = $this->_process_param($param['roottid']);
		$dateline = isset($param['dateline']) ? intval($param['dateline']) : 0;
		$lastupdate = isset($param['lastupdate']) ? intval($param['lastupdate']) : 0;
		$from = trim($param['from']);
		$types = $this->_process_param($param['type']);
		$item_ids = $this->_process_param($param['item_id']);
		$item = trim($param['item']);

				$sys_def_uid_flg = false;
		$param['my_uid'] = intval($param['my_uid']);
		$my_uid = $param['my_uid'];

				$perm_sql = '';
		if (empty($types)) {
			
			if (!empty($uids)) {
				$public_con = get_topic_type();
				$perm_sql .= " uid IN(".jimplode($uids).") AND `type` IN(".jimplode($public_con).") ";
			}
		} else {
			$perm_sql = " type IN(".jimplode($types).") ";
			if (!empty($uids) && !$sys_def_uid_flg) {
				$perm_sql .= " AND uid IN(".jimplode($uids).") ";
			}
		}

		$filter_sql = $this->filter($filter);

		$where_sql = ($perm_sql ? " AND {$perm_sql} " : '').
		(isset($param['tid']) ? ' AND `tid` IN ('.jimplode($tids).') ' : '').
		($roottids ? ' AND roottid IN ('.jimplode($roottids).') ' : '').
		($from ? " AND `from`='{$from}' " : '').
				($item_ids ? " AND `item_id` IN (".jimplode($item_ids).") " : '').
		($item ? " AND `item`='{$item}' " : '').
				($content ? " AND `content`='{$content}' " : '').
		($content2 ? " AND `content2`='{$content2}' " : '').
		($filter_sql ? ' and '.$filter_sql : '');

				if ($dateline) {
			$where_sql .= " AND `dateline`>{$dateline} ";
		}

				if ($lastupdate) {
			$where_sql .= " AND `lastupdate`>{$lastupdate} ";
		}

		if ($kw_content) {
						$kw_content = $kw_content ? build_like_query('content,content2', $kw_content) : '';
			$where_sql .= " AND {$kw_content} ";
		}

				if (!empty($param['where'])) {
			$where_sql .= " AND {$param['where']} ";
		}

		if (!empty($param['order'])) {
			$order = " ORDER BY {$param['order']} ";
		} else {
			$order = " ORDER BY dateline DESC ";
		}

		
		$where_sql = " WHERE 1 {$where_sql} ";

				$total_record = max(0, (int) $param['count']);
		if ($total_record < 1) {
			if(!$cache_time || (false === ($total_record = cache_db('mget', $cache_key . '-total_record')))) {
				$sql = " select count(*) from ".DB::table("topic")." {$where_sql} ";
				$total_record = DB::result_first($sql);

				if($cache_time) {
					cache_db('mset', $cache_key . '-total_record', $total_record, $cache_time);
				}
			}
		}

		#if NEDU
		if (defined('NEDU_MOYO'))
		{
			if ($param['@nedu~get~count'] == 921)
			{
				return (int)$total_record;
			}
		}
		#endif

				if ($total_record > 0) {

						$limit_sql = '';
			if ($param['perpage'] && (!$param['count'] || $param['page_force'])) {
				if ($caller == 'web') {
					$page_arr = page($total_record, $param['perpage'], $param['page_url'], array('return'=>'array', 'extra'=>$param['page_extra']));
				} else if ($caller == "wap") {
					$page_arr = wap_page($total_record, $param['perpage'], $param['page_url'], array('return'=>'array'));
				}
				$limit_sql = $page_arr['limit'];
			} else {
				if (!empty($param['limit'])) {
					if (strpos(strtolower($param['limit']), 'limit') !== false) {
						$limit_sql = " {$param['limit']} ";
					} else {
						$limit_sql = " LIMIT {$param['limit']} ";
					}
				} elseif ($param['count']) {
					$limit_sql = " LIMIT {$total_record} ";
				}
			}

			$condition = " {$where_sql} {$order} {$limit_sql} ";


						$fields = isset($param['fields']) ? $param['fields'] : ' * ';

						$table = isset($param['table']) ? $param['table'] : '';

			$proc_func = isset($param['proc_func']) ? trim($param['proc_func']) : 'Make';

			if(!$cache_time || (false === ($topic_list = cache_db('mget', $cache_key . '-topic_list-' . $limit_sql)))) {
								$topic_list = $this->TopicLogic->Get($condition, $fields, $proc_func, $table);
				if (empty($topic_list)) {
					return false;
				}
				if($cache_time) {
					cache_db('mset', $cache_key . '-topic_list-' . $limit_sql, $topic_list, $cache_time);
				}
			}
			
						if($this->Config['is_topic_user_follow'] && !$GLOBALS['_J']['disable_user_follow']) {
				$topic_list = Load::model('buddy')->follow_html($topic_list, 'uid', ('wap'==$caller ? 'wap_follow_html' : 'follow_html2'));
			}

			$list = array('list' => $topic_list, 'count' => $total_record);
			$list['page'] = ($page_arr ? $page_arr : $param['page']);
			return $list;
		}
		return false;
	}


	
	

	
	function get_recd_list($param, $caller = 'web')
	{
		$order_sql = ' tr.recd DESC,tr.display_order DESC,tr.dateline DESC  ';
		if (!empty($param['order_sql'])) {
			$order_sql = $param['order_sql'];
		}
		$where_sql = " tr.tid>0 ";
		if (!empty($param['where'])) {
			$where_sql .= " AND {$param['where']} ";
		}
		$where_sql .= " AND (tr.expiration>".TIMESTAMP." OR tr.expiration=0) ";

		$filter_sql = $this->filter($param['filter'], 't');

		$where_sql .= empty($filter_sql) ? '' : ' AND '.$filter_sql;

		$total_record = DB::result_first("SELECT COUNT(*)
										  FROM ".DB::table('topic')." AS t   
										  LEFT JOIN ".DB::table('topic_recommend')." AS tr
										  USING(tid) 
										  WHERE {$where_sql} ");
		$limit_sql = '';
		$topic_list = array();
		if ($total_record > 0) {
			if ($param['perpage']) {
				if($caller == 'wap'){
					$page_arr = wap_page($total_record, $param['perpage'], $param['page_url'], array('return'=>'array'));
				}else{
					$page_arr = page($total_record, $param['perpage'], $param['page_url'], array('return'=>'array'));
				}
				$limit_sql = $page_arr['limit'];
			} else {
				if ($param['limit']) {
					$limit_sql = ' LIMIT '.$param['limit'];
				}
			}
			$query = DB::query("SELECT t.*
								FROM ".DB::table('topic')." AS t 
								LEFT JOIN ".DB::table('topic_recommend')." AS tr 
								USING(tid) 
								WHERE {$where_sql} 
								ORDER BY {$order_sql} 
								{$limit_sql} ");
								while ($value = DB::fetch($query)) {
									$topic_list[$value['tid']] = $this->TopicLogic->Make($value);
								}
								$info = array(
				'list' => $topic_list,
				'count' => $total_record,
				'page' => $page_arr,
								);
								return $info;
		}
		return false;
	}

	
	function get_tc_data($param, $caller='web'){
		$cache_time = max(0, (int) $param['cache_time']);
		if($cache_time) {
			$cache_key = ($param['cache_key'] ? $param['cache_key'] : 'topic-list-get-tc-data-' . md5(serialize($param)));
		}

		if($param['area']){
			$where = " where m.area = '".$param['area']."' ";
		}elseif($param['city']){
			$where = " where m.city = '".$param['city']."' ";
		}else if($param['province']){
			$where = " where m.province = '".$param['province']."' ";
		}else{
			return false;
		}

		$types = $this->_process_param($param['type']);
		if (!empty($types)) {
			$where .= " AND t.type IN(".jimplode($types).") ";
		}

		if($param['vip']) {
			$where .= " AND m.validate='1' ";
		}

		if(!$cache_time || (false === ($total_record = cache_db('mget', $cache_key . '-total_record')))) {
			$sql = "SELECT count(*) FROM ".TABLE_PREFIX."topic t LEFT JOIN ".TABLE_PREFIX."members m ON t.uid = m.uid $where";
			$total_record = DB::result_first($sql);
				
			if($cache_time) {
				cache_db('mset', $cache_key . '-total_record', $total_record, $cache_time);
			}
		}

		$topic_list = array();
		if ($total_record > 0) {
			if ($param['perpage']) {
				$page_arr = page($total_record, $param['perpage'], $param['page_url'], array('return'=>'array'));
				$limit_sql = $page_arr['limit'];
			} else {
				if ($param['limit']) {
					$limit_sql = ' LIMIT '.$param['limit'];
				}
			}
			if(!$cache_time || (false === ($topic_list = cache_db('mget', $cache_key . '-topic_list-' . $limit_sql)))) {
				$query = DB::query("SELECT t.* FROM ".TABLE_PREFIX."topic t LEFT JOIN ".TABLE_PREFIX."members m ON t.uid = m.uid $where ORDER BY t.dateline DESC $limit_sql");
				$topic_list = array();
				while ($value = DB::fetch($query)) {
					$topic_list[$value['tid']] = $value;
				}
				$topic_list = $this->TopicLogic->MakeAll($topic_list);

				if($cache_time) {
					cache_db('mset', $cache_key . '-topic_list-' . $limit_sql, $topic_list, $cache_time);
				}
			}		
			
						if($this->Config['is_topic_user_follow'] && !$GLOBALS['_J']['disable_user_follow']) {
				$topic_list = Load::model('buddy')->follow_html($topic_list, 'uid', ('wap'==$caller ? 'wap_follow_html' : 'follow_html2'));
			}
			
			$info = array(
				'list' => $topic_list,
				'count' => $total_record,
				'page' => $page_arr,
			);
			return $info;
		}
	}

	
	function get_photo_list($param)
	{		
				$sql_where = '';
		$uid = max(0, (int) $param['uid']);
		
		$cache_key = "{$uid}-get_photo_list-" . md5(serialize($param));
		if(false === ($info = cache_db('get', $cache_key))) {
			if($uid > 0) {
								$uids = get_buddyids($uid, $this->Config['topic_myhome_time_limit']);
								if($uids){
					$sql_where = " AND t.uid in(".jimplode($uids).") ";
				}else{
					return array();
				}
			}
			$total_photo = (int) $param['count'];
			if($total_photo < 1) {
				if($param['vip']) {
					$total_photo = DB::result_first("select count(1) as `total` from ".DB::table('topic_image')." t left join ".DB::table('members')." m on m.uid=t.uid where t.tid>0 and m.validate='1'".$sql_where);
				} else {
					$total_photo = DB::result_first("SELECT COUNT(*) FROM ".DB::table('topic_image')." AS t WHERE t.tid > 0 ".$sql_where);
				}
			}
			$info = false;
			$limit_sql = '';
			$photo_i = 0;
			$topic_list = array();
			$user_lists = array();
			if ($total_photo > 0) {
				if ($param['perpage']) {
					$page_arr = page($total_photo, $param['perpage'], $param['page_url'], array('return'=>'array'));
					$limit_sql = $page_arr['limit'];
				} else {
					if ($param['limit']) {
						$limit_sql = ' LIMIT '.$param['limit'];
					} elseif ($param['count']) {
						$limit_sql = ' LIMIT '.$param['count'];
					}
				}
				if($param['vip']) {
					$query = DB::query("SELECT t.id,t.tid,t.uid,t.width,t.height,t.dateline,tr.content,tr.content2,tr.forwards,tr.replys
									FROM ".DB::table('topic_image')." AS t 
									LEFT JOIN ".DB::table('topic')." AS tr 
									ON t.tid = tr.tid
									left join ".DB::table('members')." as m
									on m.uid=t.uid
									WHERE t.tid > 0 and m.validate='1' ".$sql_where."
									ORDER BY t.id DESC  
									{$limit_sql} ");
				} else {
					$query = DB::query("SELECT t.id,t.tid,t.uid,t.width,t.height,t.dateline,tr.content,tr.content2,tr.forwards,tr.replys
									FROM ".DB::table('topic_image')." AS t 
									LEFT JOIN ".DB::table('topic')." AS tr 
									ON t.tid = tr.tid  
									WHERE t.tid > 0  ".$sql_where."
									ORDER BY t.id DESC  
									{$limit_sql} ");
				}
				while ($value = DB::fetch($query)) {
																				
					$value['content'] .= $value['content2'];					$value['content'] = htmlspecialchars(strip_tags($value['content']));					if(!is_file(topic_image($value['id'], 'photo', 1))){
						$image_file = RELATIVE_ROOT_PATH . 'images/topic/' . face_path($value['id']) . $value['id'] . "_o.jpg";
						$image_file_photo = RELATIVE_ROOT_PATH . 'images/topic/' . face_path($value['id']) . $value['id'] . "_p.jpg";
						if($value['width'] > 200) {
							$p_width = 200;
							$p_height = round(($value['height']*200)/$value['width']);
							$result = makethumb($image_file, $image_file_photo, $p_width, $p_height);
						}
						if($value['width'] <= 200 || (!$result && !is_file($image_file_photo))) {
							@copy($image_file, $image_file_photo);
						}
					}
					$value['photo'] = topic_image($value['id'], 'photo', 0);
					$value['height'] = ($value['width'] > 200) ? round(($value['height']*200)/$value['width']) : $value['height'];
					$value['width'] = ($value['width'] > 200) ? 200 : $value['width'];
					$value['dateline'] = my_date_format2($value['dateline']);
					if(false != strpos($value['content'], '</U>')) {
						$value['content'] = preg_replace('#\<U(.*?)\>(.*?)\</U\>#','<a href="\\2" target="_blank">Click Here</a>',$value['content']);
					}
										if(false !== strpos($value['content'], 'http:/'.'/')) {
						$value['content'] = preg_replace('~(http:/'.'/[a-z0-9-\.\?\=&;_@/%#]+?)\s+~i', '<a href="\\1" target="_blank">Click Here</a> ', $value['content']);
						$value['content'] = preg_replace("|\s*http:/"."/[a-z0-9-\.\?\=&;_@/%#]*\$|sim", "", $value['content']);
					}
					$topic_list[] = $value;	
				}
				if($topic_list) {
					$topic_list_count = count($topic_list);
					$topic_list = $this->TopicLogic->MakeAll($topic_list, 0);
									
					$info = array(
						'list' => $topic_list,
						'count' => ($param['count'] ? $topic_list_count : $total_photo),
						'page' => $page_arr,
					);
				}
			}
	
						cache_db('set', $cache_key, $info, ($uid > 0 ? 3600 : 600));
		}
		
		if($info['count'] > 0 && $info['list']) {			
						if($this->Config['is_topic_user_follow'] && !$GLOBALS['_J']['disable_user_follow']) {
				$info['list'] = Load::model('buddy')->follow_html($info['list'], 'uid', 'follow_html2');
			}
			
			$pi=0;
			$list = array();
			foreach($info['list'] as $v) {
				$list[$pi++ % 4][] = $v;
			}
			$info['list'] = $list;
		}

		return $info;
	}

	
	function get_options($options=array(), $cache_time=600, $cache_key='') {
		$cache_time = max(0, (int) $cache_time);
				if($cache_time > 0 && !$options['tid']) { 			$cache_key = ($cache_key ? $cache_key : 'topic-list-get-tids-'.md5(serialize($options)));
			if(false === ($_cache_info=cache_db('mget', $cache_key))) {
				$_options = $options;
				unset($_options['perpage'], $_options['limit']);
				$_options['fields'] = 'tid';
				$_options['count'] = ($options['perpage'] * ($this->Config['total_page_default'] ? min(200, max(1, (int) $this->Config['total_page_default'])) : 50));
				$_cache_info = $this->get_data($_options);
				$_tids = array();
				if($_cache_info) {
					foreach($_cache_info['list'] as $_row) {
						$_tids[$_row['tid']] = $_row['tid'];
					}
				}
								if($_tids) {
					$_cache_info['count'] = count($_tids);
					$_tids = array_chunk($_tids, $options['perpage']);
				}
				$_cache_info['list'] = $_tids;
				unset($_cache_info['page']);

				cache_db('mset', $cache_key, $_cache_info, $cache_time);
			}
			unset($options['uid'], $options['type'], $options['dateline']); 				
			$options['count'] = $options['perpage']; 			$options['page'] = page($_cache_info['count'], $options['perpage'], $options['page_url'], array('return'=>'Array')); 			$_tids = $_cache_info['list'][(max(0, (get_param('page')-1)))];
			$options['tid'] = ($_tids ? $_tids : array('0'));
		}
		
		return $options;
	}
}

?>