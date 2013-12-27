<?php
/**
 *
 * 数据区块操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id$
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class data_block {

	function data_block() {
		;
	}
	
	
	function hot_tag_recommend() {
		
		$hot_tag_recommend = ConfigHandler::get('hot_tag_recommend');
		$for_count = $hot_tag_recommend['num'];
		foreach ($hot_tag_recommend['list'] as $key=>$val) {
			if($for_count < 1 ) break;
			$tag_id[$val['tag_id']] = $val['tag_id'];
			if($val['tag_id']){
				$hot_tag_recommend['list'][$key]['topic_count'] = DB::result_first(" select `topic_count` from `".TABLE_PREFIX."tag` where id='{$val[tag_id]}' ");
			}else{
				$hot_tag_recommend['list'][$key]['topic_count'] = 0;
			}
			$for_count--;
		}
	}

	
	function recommend_topic_user($day=1, $limit=12, $cache_time=0) {
		$day = (is_numeric($day) ? $day : 0);
		$limit = (is_numeric($limit) ? $limit : 0);
		$cache_time = (is_numeric($cache_time) ? $cache_time : 0);
		if($day < 1 || $limit < 1) {
			return false;
		}

		$time = $day * 86400;
		$cache_time = max(300, ($cache_time ? $cache_time : ($time / 24)));

		$cache_id = "data_block/recommend_topic_user-{$day}-{$limit}";
		if (false === ($list = cache_file('get', $cache_id))) {
			$dateline = TIMESTAMP - $time;
			$sql = "SELECT DISTINCT(T.uid) AS `uid` , COUNT(T.tid) AS `topics` FROM `".TABLE_PREFIX."topic` T LEFT JOIN `".TABLE_PREFIX."members` M ON T.uid=M.uid WHERE T.dateline>=$dateline AND M.face!='' GROUP BY `uid` ORDER BY `topics` DESC LIMIT {$limit} ";
			$query = DB::query($sql);
			$uids = array();
			while (false != ($row = DB::fetch($query))) {
				$uids[$row['uid']] = $row['uid'];
			}
			$list = array();
			if($uids) {
				$list = Load::logic('topic', 1)->GetMember($uids, "`uid`,`ucuid`,`username`,`face_url`,`face`,`aboutme`,`validate`,`validate_category`,`nickname`");
			}
			cache_file('set', $cache_id, $list, $cache_time);
		}

		return $list;
	}
	
	
	function may_interest_user($retry=3) {		
		$uid = MEMBER_ID; 		if($uid < 1) {
			return array();
		}

		
		$buddyids = get_buddyids($uid, $GLOBALS['_J']['config']['topic_myhome_time_limit']);

		$cache_time = 1800;
		$cache_key = "{$uid}-may_interest_user";
		if(false === ($cache_data=cache_db('get', $cache_key))) {
			$uids = array();
			$uids_limit = 300; 			
			
			$type_array = array('follow','tag','user_tag','city');
			$refresh_type = $type_array[array_rand($type_array,1)];
			
						if($refresh_type == 'follow') {
				
				if($buddyids) {
					$p = array(
						'count' => $uids_limit,
						'fields' => 'buddyid',
						'uid' => $buddyids,
						'buddy_lastuptime' => (TIMESTAMP - 86400 * 30),
						'order' => ' `buddy_lastuptime` DESC ',
					);
					$uids = Load::model('buddy')->get_ids($p);
				}
			}
	
	
						elseif($refresh_type == 'tag') {
								$query = DB::query("SELECT `tag` FROM ".DB::table('tag_favorite')." where uid='{$uid}'");
				$touser_tag = array();
				while ($value = DB::fetch($query)) {
					$touser_tag[] = $value['tag'];
				}
	
								if($touser_tag) {
					$query = DB::query("SELECT `uid` FROM ".DB::table('tag_favorite')." where `tag` in ('".implode("','",$touser_tag)."') ORDER BY `id` DESC LIMIT $uids_limit ");
					while ($value = DB::fetch($query)) {
						$uids[$value['uid']] = $value['uid'];
					}
				}
			}
	
	
						elseif($refresh_type == 'user_tag') {
								$query = DB::query("SELECT `tag_id`,`uid` FROM ".DB::table('user_tag_fields')." where uid='{$uid}'");
				$touser_usertag_uid = array();
				while ($value = DB::fetch($query)) {
					$touser_usertag_uid[$value['tag_id']] = $value['tag_id'];
				}
	
								if($touser_usertag_uid) {
					$query = DB::query("SELECT `uid` FROM ".DB::table('user_tag_fields')." where `tag_id` in ('".implode("','",$touser_usertag_uid)."') ORDER BY `id` DESC LIMIT $uids_limit ");
					while ($value = DB::fetch($query)) {
						$uids[$value['uid']] = $value['uid'];
					}
				}
			}
	
	
						elseif($refresh_type == 'city') {
								$member_info = jsg_member_info($uid);
	
								if($member_info['city']) {
					$query = DB::query("select `uid` from ".DB::table('members')." where `city` = '{$member_info['city']}' ORDER BY `lastactivity` DESC LIMIT $uids_limit ");
					while ($value = DB::fetch($query)) {
						$uids[$value['uid']] = $value['uid'];
					}
				}
			}
						unset($uids[$uid]);
		} else {
			$uids = $cache_data['uids'];
			$refresh_type = $cache_data['refresh_type'];
		}

				$member_list = array();
		$black_list = array();
		$query = DB::query(" select `touid` from `".TABLE_PREFIX."blacklist` where `uid` = '$uid'");
		while ($rs=DB::fetch($query)) {
			$black_list[$rs['touid']] = $rs['touid'];
		}
		if($uids) {
			
			if($buddyids || $black_list) {
				foreach($uids as $k=>$v) {
					if(isset($buddyids[$v])) {
						unset($uids[$k]);
					}
					if(isset($black_list[$v])) {
						unset($uids[$k]);
					}
				}
			}
			
			if($uids) {
				if(false===$cache_data) {
					$cache_data['uids'] = $uids;
					$cache_data['refresh_type'] = $refresh_type;
					
					cache_db('set', $cache_key, $cache_data, $cache_time);
				}
				
				
				$rand_number = 4;
				$rand_uids = array_rand($uids, min(count($uids), $rand_number));
	
				if($rand_uids) {
										$condition = " WHERE `uid` IN ('" . implode("','", $rand_uids) . "') LIMIT {$rand_number} ";
					$member_list = Load::logic('topic', 1)->GetMember($condition);
					$member_list = Load::Model('buddy')->follow_html($member_list, 'uid', 'follow_html2');
		
										foreach($member_list as $k=>$row) {
						if($row['is_follow']) {
							unset($member_list[$k]);
						} else {						
							$_uid = $row['uid'];
							$count = 0;
														if('follow' == $refresh_type) {
								$count = DB::result_first("SELECT COUNT(1) AS `count` FROM ".DB::table('buddys')." A, ".DB::table('buddys')." B WHERE A.buddyid='$_uid' AND B.uid='$uid' AND B.buddyid=A.uid");
															} elseif ('user_tag' == $refresh_type) {
								$count = DB::result_first("SELECT COUNT(1) AS `count` FROM ".DB::table('user_tag_fields')." A, ".DB::table('user_tag_fields')." B WHERE A.uid='$_uid' AND B.uid='$uid' AND B.tag_id=A.tag_id");
															} elseif ('tag' == $refresh_type) {
								$count = DB::result_first("SELECT COUNT(1) AS `count` FROM ".DB::table('tag_favorite')." A, ".DB::table('tag_favorite')." B WHERE A.uid='$_uid' AND B.uid='$uid' AND B.tag=A.tag");
							}
							$row['count'] = $count;
							$row['refresh_type'] = $refresh_type;
							$member_list[$k] = $row;
						}
					}
				}
			}
		}

				if(!$member_list && --$retry > 0) {
			$member_list = $this->may_interest_user($retry);
		}

		return $member_list;
	}

	
	function _param_id($d) {
		if(is_string($d)) {
			$d = explode(',', str_replace(array("'", '"'), '', $d));
		}
		if($d) {
			$d = (array) $d;
		}
		return $d;
	}
}

?>