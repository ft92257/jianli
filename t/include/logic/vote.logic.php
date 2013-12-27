<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename vote.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-04 18:49:37 851964884 1370825317 22373 $
 *******************************************************************/




if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class VoteLogic
{
	
	var $Config;
	
	function VoteLogic()
	{
		$this->Config = &Obj::registry("config");
	}
	
	
	function &get_list($param)
	{
		$list = array();
		extract($param);
		$where_sql = ' WHERE 1 ';
		$order_sql = '';
		$limit_sql = '';
		if ($where) {
			$where_sql .= ' AND '.$where;
		}
		
		if ($order) {
			$order_sql = ' ORDER BY '.$order;
		}
		
		if ($limit) {
			$limit_sql = ' LIMIT '.$limit;
		}
		
		$query = DB::query("SELECT * FROM ".DB::table('vote')." {$where_sql} {$order_sql} {$limit_sql}");
		while($value = DB::fetch($query)) {
			$list[] = $value;
		}
		return $list;
	}
	
	
	function &get_new_list($num = 10)
	{
		$num = intval($num);
		if (empty($num)) {
			return false;
		}
		$list = array();
		$list = $this->get_list(array('order' => 'dateline DESC ', 'limit' => $num));
		return $list;
	}
	
	
	function &get_hot_list($num = 10)
	{
		$num = intval($num);
		if (empty($num)) {
			return false;
		}
		$list = array();
		$timerange = TIMESTAMP - 2592000;
		$where = " lastvote >= '{$timerange}' ";
		$order = " voter_num DESC ";
		$where = array(
			'where' => $where,
			'order' => $order,
			'limit' => $num,
		);
		$list = $this->get_list($where);
		return $list;
	}
	
	
	function &get_recd_list()
	{
		$show_config = ConfigHandler::get('show');
		$num = $show_config['vote']['recd']; 
		$list = array();
		$where = " recd=1 ";
		$order = " lastvote DESC ";
		$where = array(
			'where' => $where,
			'order' => $order,
			'limit' => $num,
		);
		$list = $this->get_list($where);
		return $list;
	}
	
	
	function find($param)
	{
		if (!empty($param['where'])) {
			$where_sql .= " {$param['where']} ";
		}
		
		$order_sql = " ";
		if (!empty($param['order'])) {
			$order_sql = " {$param['order']} ";
		}
		
		$limit_sql = " ";
		if (!empty($param['limit'])) {
			$limit_sql = " {$param['limit']} ";
		}
		
		$vote_list = array();
		$count = max(0, (int) $param['count']);
		if($count < 1) {
			$count_sql = "SELECT COUNT(*) 
						  FROM ".DB::table('vote')." AS v
						  WHERE {$where_sql}";
			$count = DB::result_first($count_sql);
		}
		
		if ($count) {
			if ($param['page']) {
								$_config = array(
					'return' => 'array',
				);
				$page_arr = page($count, $param['perpage'], $param['page_url'], $_config);
				$limit_sql = $page_arr['limit'];
			} elseif ($param['count']) {
				$limit_sql = " LIMIT {$count} ";
			}
			
			$uid_ary = array();
			$sql = "SELECT v.*,vf.*  
				    FROM ".DB::table('vote')." AS v
					LEFT JOIN ".DB::table('vote_field')." AS vf
					USING (vid)   
					WHERE {$where_sql} 
					{$order_sql}  
					{$limit_sql} ";
			
			$query = DB::query($sql);
			while ($value = DB::fetch($query)) {
				$last_update_time = $value['lastvote'] ? $value['lastvote'] : $value['dateline'];
								$value['last_update_time'] = my_date_format2($last_update_time);
				$value['option'] = unserialize($value['option']);
				$value['input_type'] = $value['multiple'] ? 'checkbox' : 'radio';
				
								$value['is_expiration'] = false;
				if ($value['expiration'] <= TIMESTAMP) {
					$value['is_expiration'] = true;	
				}
				
				$vote_list[] = $value;
				$uid_ary[] = $value['uid'];
			}
			
			
						$def_items = 2;
			
						foreach ($vote_list as $key => $val) {
				$vote_items = $this->get_vote_item($val['vid'], MEMBER_ID);
				$vote_list[$key]['is_vote'] = false;
				$vote_list[$key]['vote_show'] = $val['option'];
				
				if (!empty($vote_items)) {
					$vi_count = count($vote_items);
					$vote_list[$key]['vi_count'] = $vi_count;
					$vote_list[$key]['is_vote'] = true;
					if ($vi_count >= $def_items) {
						$vote_list[$key]['vote_show'] = array_slice($vote_items, 0, $def_items);
					} else {
						$item = $vote_items[0];
						$index = array_search($item, $vote_list[$key]['vote_show']);
						if ($index !== false) {
							unset($vote_list[$key]['vote_show'][$index]);
							array_unshift($vote_list[$key]['vote_show'], $item);
						} else {
							unset($vote_list[$key]['vote_show'][$def_items-1]);
							array_unshift($vote_list[$key]['vote_show'], $item);
						}
					}
				}
			}
			return array(
				'count' => $count,
				'vote_list' => $vote_list,
				'uids' => array_unique($uid_ary),
				'page' => $page_arr,
			);
		}
		return false;	
	}
	
	
	function &id2voteinfo($vid, $type = 'all')
	{
		if ($type == 'all') {
			$vote = DB::fetch_first("SELECT vf.*, v.* 
							 		 FROM ".DB::table('vote')." v 
							 		 LEFT JOIN ".DB::table('vote_field')." vf 
							 		 USING (vid)
							 		 WHERE v.vid='{$vid}'");
		} else if ($type == 'm') {
			$vote = DB::fetch_first("SELECT * 
				 		 			 FROM ".DB::table('vote')."  
				 		 			 WHERE vid='{$vid}'");
		}
		return $vote;
	}
	
	
	function id2subject($vid)
	{
		$subject = DB::result_first("SELECT subject FROM ".DB::table('vote')." WHERE vid='{$vid}' ");
		return $subject;
	}
	
	
	function is_exists($vid)
	{
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('vote')." WHERE vid='{$vid}'");
		return $count;
	}
	
	
	function &get_option_by_vid($vid)
	{
				$allvote = 0;
		
				$query = DB::query("SELECT * 
							FROM ".DB::table('vote_option')." 
							WHERE vid='{$vid}' ORDER BY oid");
		while ($value = DB::fetch($query)) {
			$allvote += intval($value['vote_num']);
			$option[] = $value;
		}
		return array('option' => $option, 'allvote' => $allvote);	
	}
	
		function option_nums($vid)
	{
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('vote_option')." WHERE vid='{$vid}'");
		return $count;
	}
	
	
	function chk_post(&$post, $type = "create", $params = null)
	{
				$maxoption = 20;
		$option_nums = 0;
		if ($type == 'modify') {
			$option_nums = $this->option_nums($post['vid']);
			$maxoption = 20 - $option_nums;
		}
		
		$newoption = $optionarr = array();
		
				$post['subject'] = getstr(trim($post['subject']), 50, 1, 1);
		
				if (strlen($post['subject']) < 2) {
			return -1;
		}
		
				if (!$params['no_chk_option']) {
			$post['preview'] = array();
			$post['option'] = array_unique($post['option']);
			foreach ($post['option'] as $key => $val) {
				$option = getstr(trim($val), 40, 1, 1);
				if(strlen($option) && count($newoption) < $maxoption) {
					$newoption[] = $option;
					if(count($post['preview']) < 2 ) {
						$post['preview'][] = $option;
					}
				}
			}
		
			$maxoption = count($newoption);
			if ($type == 'modify') {
				$maxoption += $option_nums;
			}
		
						if (count($newoption) < 2 && $type == 'create') {
				return -2;
			}
			
			$post['newoption'] = $newoption;
		}
		
				$post['message'] = getstr(trim($post['message']), 800, 1, 1);
		
				if (!$params['no_chk_maxchoice']) {
			$post['maxchoice'] = $post['maxchoice'] < $maxoption ? intval($post['maxchoice']) : $maxoption;
		}
		
				$expiration = 0;
		if($post['expiration']) {
			$expiration = jstrtotime(trim($post['expiration']));
			if($expiration <= TIMESTAMP) {
				return -3;
			}
		}
		$post['expiration'] = $expiration;
		
		return 1;
	}
	
	
	function create($post, &$ret)
	{	
		$r = $this->chk_post($post);
		if ($r != 1) {
			return $r;
		}
		
		$ret['subject'] = $post['subject'];
		$setarr = array(
			'uid' => $post['uid'],
			'username' => $post['username'],
			'subject' => $post['subject'],
			'maxchoice' => $post['maxchoice'],
			'multiple' => $post['maxchoice'] > 1 ? 1 : 0,
			'is_view' => $post['is_view'],
			'expiration' => $post['expiration'],
			'dateline' => TIMESTAMP,
			'postip' => client_ip(),
			'item' => $post['item'],
			'item_id' => $post['item_id'],
			'verify' => isset($post['verify']) ? 0 : 1,
		);
		
		$vid = DB::insert('vote', $setarr, true);
		$ret['vid'] = $vid;
		
				if($setarr['verify'] == 0){
			if($notice_to_admin = $this->Config['notice_to_admin']){
				$pm_post = array(
					'message' => MEMBER_NICKNAME."发布了一个投票进入待审核状态，<a href='admin.php?mod=vote&code=verify' target='_blank'>点击</a>进入审核。",
					'to_user' => str_replace('|',',',$notice_to_admin),
				);
								$admin_info = DB::fetch_first('select `uid`,`username`,`nickname` from `'.TABLE_PREFIX.'members` where `uid` = 1');
				load::logic('pm');
				$PmLogic = new PmLogic();
				$PmLogic->pmSend($pm_post,$admin_info['uid'],$admin_info['username'],$admin_info['nickname']);
			}
		}
		
		$setarr = array(
			'vid' => $vid,
			'message' => $post['message'],
			'option' => jaddslashes(serialize($post['preview']))
		);
		DB::insert('vote_field', $setarr);
		
				if($post['item'] == 'qun' && $post['item_id']){
			$qun_vote = array(
				'qid' => $post['item_id'],
				'vid' => $vid,
				'recd' => 0,
			);
			DB::insert('qun_vote', $qun_vote);
		}
		
		$optionarr = array();
		foreach($post['newoption'] as $key => $value) {
			$optionarr[] = "('$vid', '$value')";
		}
		
				DB::query("INSERT INTO ".DB::table('vote_option')." 
				   (`vid`, `option`) VALUES ".implode(',', $optionarr));
                   
                update_credits_by_action('vote_add',$post['uid']);
                   
		return 1;
	}
	
	
	function modify($post)
	{
		$setarr = array(
			'subject' => $post['subject'],
			'maxchoice' => $post['maxchoice'],
			'multiple' => $post['maxchoice'] > 1 ? 1 : 0,
			'is_view' => $post['is_view'],
			'recd' => isset($post['recd']) ? 1 : 0,
			'expiration' => $post['expiration'],
		);
		DB::update('vote', $setarr, array('vid' => $post['vid']));
		DB::update('vote_field', array('message' => $post['message']), array('vid' => $post['vid']));
		
				if (!empty($post['newoption'])) {
			$optionarr = array();
			foreach($post['newoption'] as $key => $value) {
				$optionarr[] = "('{$post['vid']}', '$value')";
			}
			
						DB::query("INSERT INTO ".DB::table('vote_option')." 
					   (`vid`, `option`) VALUES ".implode(',', $optionarr));
		}
	}
	
		function update_options($vid, $old_options, $new_options, $is_voted = false)
	{
		$options = $old_options;
		$preview_updata_flg = false;
		
				if (!empty($options)) {
			if (!$is_voted || MEMBER_ROLE_TYPE == 'admin') {
				$preview = array();
				$keys = array_keys($options);
				$options = array_unique($options);
				
								if (count($options) > 1) {
					foreach ($keys as $i) {
						if (!empty($options[$i])) {
														$val = $options[$i];
							$p = getstr(trim($val), 40, 1, 1);
							if (empty($p)) {
								continue;
							}
							DB::update('vote_option', array('option'=>$p), array('oid'=>$i));
						} else {
														DB::query("DELETE FROM ".DB::table('vote_option')." WHERE oid='{$i}'");
						}
					}
					$preview_updata_flg = true;
				}
			}
		}
		
				if (!empty($new_options)) {
			$new_options = array_unique($new_options);
			foreach ($new_options as $val) {
				$ret = $this->add_opt($vid, $val);
			}
		}
		
		if ($preview_updata_flg) {
						$preview = array();
			$options = $this->get_option_by_vid($vid);
			foreach ($options['option'] as $val) {
				if(count($preview) < 2 ) {
					$preview[] = $val['option'];
				}
			}
			$str_options = jaddslashes(serialize($preview));
			DB::update('vote_field', array('option'=>$str_options), array('vid'=>$vid));
		}
	}
	
	
	function get_joined($uid)
	{
		$vids = array();
		$where_sql = ' 1 ';
		
		if (is_array($uid)) {
			$where_sql .= " AND uid IN(".jimplode($uid).")";
		} else {
			$where_sql .= " AND uid='{$uid}' ";
		}
		
		$query = DB::query("SELECT vid     
						    FROM ".DB::table('vote_user')." 
						    WHERE {$where_sql} ");
		while ($value = DB::fetch($query)) {
			$vids[] = $value['vid'];
		}
		return $vids;
	}
	
	
	function get_vote_item($vid, $uid)
	{
		$ret = DB::fetch_first("SELECT *    
						    	FROM ".DB::table('vote_user')." 
						    	WHERE uid='{$uid}' AND vid='{$vid}'");
		if ($ret) {
			$vote_items = unserialize($ret['option']);
			return $vote_items;
		}
		return false;
	}
	
	
	function is_voted($vid, $uid)
	{
		$count = DB::result_first("SELECT COUNT(*) 
								   FROM ".DB::table('vote_user')." 
								   WHERE uid='{$uid}' AND vid='{$vid}'");
		return $count;
	}
	
	
	function do_vote($param, &$result)
	{	
		extract($param);
		
				if ($this->is_voted($vid, $uid)) {
			return -1;
		}
		
				$list = $optionarr = $setarr = array();
		foreach($option as $key => $val) {
			$optionarr[] = intval($val);
			if(count($optionarr) > $maxchoice) {
				return -2;
			}
		}
		
				$query = DB::query("SELECT `option` 
							FROM ".DB::table('vote_option')." 
							WHERE oid IN ('".implode("','", $optionarr)."') AND vid='{$vid}'");
		while($value = DB::fetch($query)) {
			$list[] = jaddslashes($value['option']);
		}
		
		if(empty($list)) {
			return -3;
		}
		
		$result['voted_option'] = array();
		if (count($list) == 1) {
			$result['voted_option'] = $list;	
		} else {
			$result['voted_option'] = array_slice($list, 0, 2);
		}
		
				DB::query("UPDATE ".DB::table('vote_option')." 
				   SET vote_num=vote_num+1 
				   WHERE oid IN ('".implode("','", $optionarr)."') AND vid='{$vid}'");
		
		$joined_uids = array();
		$query = DB::query("SELECT uid FROM ".DB::table('vote_user')." WHERE vid='{$vid}'");
		while ($value=DB::fetch($query)) {
			$joined_uids[$value['uid']] = $value['uid'];
		}
		
		
		$setarr = array(
			'uid' => $uid,
			'username' => $anonymous ? '': $username,
			'vid' => $vid,
			'option' => jaddslashes(serialize($list)),			'dateline' => TIMESTAMP
		);
		
		DB::insert('vote_user', $setarr);
		
		DB::query("UPDATE ".DB::table('vote')." 
				   SET voter_num=voter_num+1, lastvote='".TIMESTAMP."'  
				   WHERE vid='{$vid}'");
		
				if ($uid != $create_uid && !isset($joined_uids[$create_uid])) {
			$joined_uids[] = $create_uid;
		}
		DB::query("UPDATE ".DB::table('members')." SET vote_new=vote_new+1 WHERE uid IN(".jimplode($joined_uids).")");
		
		return 1;
	}
	
	
	function delete($ids)
	{
				$sparecredit = $spaces = $polls = $newpids = array();
		$delnum = 0;
		if (!is_array($ids)) {
			$ids = (array)$ids;
		}
		$query = DB::query("SELECT * FROM ".DB::table('vote')." WHERE vid IN (".jimplode($ids).")");
		while ($value = DB::fetch($query)) {
			if($value['uid'] == MEMBER_ID || MEMBER_ROLE_TYPE == 'admin') {
				$polls[] = $value;
                
                                update_credits_by_action('vote_del',$value['uid']);
			}            
		}
		if (empty($polls)) {
			return false;
		}
		
				foreach($polls as $key => $value) {
			$newpids[] = $value['vid'];
		}
	
				DB::query("DELETE FROM ".DB::table('vote')." WHERE vid IN (".jimplode($newpids).")");
		DB::query("DELETE FROM ".DB::table('vote_field')." WHERE vid IN (".jimplode($newpids).")");
		DB::query("DELETE FROM ".DB::table('vote_option')." WHERE vid IN (".jimplode($newpids).")");
		DB::query("DELETE FROM ".DB::table('vote_user')." WHERE vid IN (".jimplode($newpids).")");
		
				$tids = array();
		$query = DB::query("SELECT tid FROM ".DB::table('topic_vote')." WHERE item_id IN (".jimplode($newpids).") ");
		while ($value = DB::fetch($query)) {
			$tids[] = $value['tid'];
		}
		
		if (!empty($tids)) {
						$topic_reply_ids = array();
			$query = DB::query("SELECT tid,type FROM ".DB::table('topic')." WHERE tid IN(".jimplode($tids).")");
			while ($value = DB::fetch($query)) {
				if ($value['type'] == 'reply') {
					$topic_reply_ids[] = $value['tid'];
				}
			}
			
			if (!empty($topic_reply_ids)) {
				
				$TopicLogic = Load::logic('topic', 1);
				$TopicLogic->Delete($topic_reply_ids);
			}
			
						DB::query("DELETE FROM ".DB::table('topic_vote')." WHERE item_id IN (".jimplode($newpids).") ");
		}		
		
		return $polls;
	}
	
	
	function modify_expiration($vid, $expiration)
	{
		$expiration = jstrtotime(trim($expiration));
		if($expiration <= TIMESTAMP) {
			return -1;
		}
		DB::update('vote', array('expiration' => $expiration), array('vid' => $vid));
		return 1;
	}
	
	
	function add_opt($vid, $newoption)
	{
				$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('vote_option')." WHERE vid='{$vid}'");
		if($count >= 20) {
			return -1;
		}
		$newoption = getstr(trim($newoption), 40, 1, 1);
		if(strlen($newoption) < 1) {
			return -2;
		}
		$setarr = array(
			'vid' => $vid,
			'option' => $newoption
		);
		DB::insert('vote_option', $setarr);
		return 1;
	}
	
	
	function update_recd($data)
	{
		DB::query("UPDATE ".DB::table("vote")." SET recd='{$data['recd']}' WHERE vid='{$data['vid']}'");
	}
	
	
	function get_publish_form_param($dateline = '')
	{
		if (empty($dateline)) {
						$expiration = my_date_format(TIMESTAMP+7*24*3600, 'Y-m-d');
			$hour_select = mk_time_select();
			$min_select =  mk_time_select('min');
		} else {
			$expiration = my_date_format($dateline, 'Y-m-d ');
			$hour_select = mk_time_select('hour', my_date_format($dateline, 'H'));
			$min_select =  mk_time_select('min', my_date_format($dateline, 'i'));
		}

		return array(
			'expiration' => $expiration,
			'hour_select' => $hour_select,
			'min_select' => $min_select,
		);
	}
	
	
	function process_detail($vote, $uid)
	{
		$vid = $vote['vid'];
		if('qun' == $vote['item'] && $vote['item_id']){
			$qun_info = $this->Vote_Qun_Info($vote['item_id']);
			$vote['from_html'] = "来自群：<a href='index.php?mod=qun&qid=$qun_info[qid]' target='_blank'>".$qun_info['name']."</a>";
		}elseif('event' == $vote['item'] && $vote['item_id']){
			$event_info = $this->Vote_Event_Info($vote['item_id']);
			$vote['from_html'] = "来自活动：<a href='index.php?mod=event&code=detail&id=$event_info[id]' target='_blank'>".$event_info['title']."</a>";
		}
		
				if ($vote['multiple']) {
			$vote['input_type'] = 'checkbox';
		} else {
			$vote['input_type'] = 'radio';
		}
		
				$allowedvote = true;
		
		$expiration = false;
				if($vote['expiration'] && $vote['expiration'] < TIMESTAMP) {
			$allowedvote = false;
			$expiration = true;
		}
		
				$hasvoted = $this->is_voted($vid, $uid);
		
		$info = $this->get_option_by_vid($vid);
		$allvote = $info['allvote'];
		$option = $info['option'];
		
		$allow_view = true;
		if (!$vote['is_view'] && !$hasvoted) {
			$allow_view = false;
		}
		
				foreach ($option as $key => $value) {
			if ($allow_view) {
				if ($value['vote_num'] && $allvote) {
					$value['percent'] = round($value['vote_num']/$allvote, 2);
					$value['width'] = round($value['percent']*160);
					$value['percent'] = $value['percent']*100;
				} else {
					$value['width'] = $value['percent'] = 0;
				}
			} else {
				$value['vote_num'] = $value['width'] = $value['percent'] = 0;
			}
			$option[$key] = $value;
		}
		
		return array(
			'vote' => $vote,
			'option' => $option,
			'allow_view' => $allow_view,
			'allowedvote' => $allowedvote,
			'hasvoted' => $hasvoted,
		);
	}
	
	
	function Vote_Qun_Info($qid){
		$qun_info = array();
		$sql = "select `qid`,`name` from `".TABLE_PREFIX."qun` where `qid` = '$qid'";
		$qun_info = DB::fetch_first($sql);
		return $qun_info;
	}
	
	
	function Vote_Event_Info($eid){
		$event_info = array();
		$sql = "select `id`,`title` from `".TABLE_PREFIX."event` where `id` = '$eid'";
		$event_info = DB::fetch_first($sql);
		return $event_info;
	}
	
	
	function allowedCreate($uid = MEMBER_ID){
		$member = DB::fetch_first("SELECT validate FROM ".DB::table('members')." WHERE uid='{$uid}'");
		$config = ConfigHandler::get();
		if($config['vote_vip']){
			if(!$member['validate']){
				return "非V认证用户不允许发起投票,<a href='index.php?mod=other&code=vip_intro'>点此申请V认证</a>";
			}
		}
	}
}

?>