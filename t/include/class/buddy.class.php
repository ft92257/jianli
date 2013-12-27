<?php
/**
 *
 * 好友相关操作类
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

class buddy {

	var $table = 'buddys';

	function buddy() {
		;
	}

	
	function info($buddyid, $uid=0) {
		$buddyid = is_numeric($buddyid) ? $buddyid : 0;
		if($buddyid < 1) {
			return false;
		}
		$uid = (int) ($uid ? $uid : MEMBER_ID);
		if($uid < 1) {
			return false;
		}

		$p = array(
			'count' => 1,
			'uid' => $uid,
			'buddyid' => $buddyid,
		);
		$rets = $this->get($p);
		if(!$rets) {
			return false;
		}

		return $rets['list'][0];
	}

	
	function get($p, $cache_time=0) {
		$wheres = array();

		$ids = $this->_param_id($p['id'] ? $p['id'] : $p['ids']);
		if($ids) {
			$wheres['id'] = " `id` IN ('".implode("','", $ids)."') ";
		}
		$uids = $this->_param_id($p['uid'] ? $p['uid'] : $p['uids']);
		if($uids) {
			$wheres['uid'] = " `uid` IN ('".implode("','", $uids)."') ";
		}
		$buddyids = $this->_param_id($p['buddyid'] ? $p['buddyid'] : $p['buddyids']);
		if($buddyids) {
			$wheres['buddyid'] = " `buddyid` IN ('".implode("','", $buddyids)."') ";
		}
		if($p['dateline']) {
			$wheres['dateline'] = " `dateline`>='".(max(0, (int) $p['dateline']))."' ";
		}
		if($p['buddy_lastuptime'] || $p['lastuptime']) {
			$wheres['buddy_lastuptime'] = " `buddy_lastuptime`>='".(max(0, (int) $p['buddy_lastuptime'], (int) $p['lastuptime']))."' ";
		}
		if($p['where']) {
			$wheres['where'] = $p['where'];
		}

		$sql_where = ($wheres ? " WHERE ".implode(" AND ", $wheres)." " : "");
		if($p['return_where']) {
			return $sql_where;
		}

		if($cache_time) {
			$cache_id = "{$this->table}-" . md5(serialize($p));
			if(false !== ($rets = cache_db('mget', $cache_id))) {
				return $rets;
			}
		}

		$count = max(0, (int) $p['count']);
		if($count < 1) {
			$count = DB::result_first("SELECT COUNT(1) AS `count` FROM ".DB::table($this->table)." $sql_where ");

			if($p['return_count']) {
				return $count;
			}
		}

		$rets = array();
		if($count > 0) {
			$page = array();
			$sql_limit = '';
			if($p['per_page_num']) {
				$page = page($count, $p['per_page_num'], $p['page_url'], array('return' => 'Array', 'extra'=>$param['page_extra']));
				$sql_limit = " {$page['limit']} ";
			} elseif($p['limit']) {
				if(false !== strpos(strtolower($p['limit']), 'limit ')) {
					$sql_limit = " {$p['limit']} ";
				} else {
					$sql_limit = " LIMIT {$p['limit']} ";
				}
			} elseif($p['count']) {
				$sql_limit = " LIMIT $count ";
			}

			$sql_order = '';
			if($p['order']) {
				if(false !== strpos(strtolower($p['order']), 'order by ')) {
					$sql_order = " {$p['order']} ";
				} else {
					$sql_order = " ORDER BY {$p['order']} ";
				}
			}

			$sql_fields = ($p['fields'] ? $p['fields'] : "*");

			$sql = "SELECT $sql_fields FROM ".DB::table($this->table)." $sql_where $sql_order $sql_limit ";
			if($p['return_sql']) {
				return $sql;
			}
			$list = DB::fetch_all($sql);

			if($list) {
				$rets = array('count'=>$count, 'list'=>$list, 'page'=>$page);
			}
		}

		if($cache_time) {
			cache_db('mset', $cache_id, $rets, $cache_time);
		}

		return $rets;
	}

	
	function add($p, $delete_if_exists=0) {
		$rets = array();
		$buddyid = (int) $p['buddyid'];
		$uid = ($p['uid'] > 0 ? $p['uid'] : MEMBER_ID);
		if($uid < 1 || $buddyid < 1 || $uid == $buddyid) {
			$rets['error'] = '您不能关注自己';
			return $rets;
		}

		$query = DB::query("SELECT * FROM `".TABLE_PREFIX."members` WHERE `uid` IN ('{$uid}','{$buddyid}')");
		$members = array();
		while (false != ($row = DB::fetch($query))) {
			$members[$row['uid']] = $row;
		}

		$info = $this->info($buddyid, $uid);
		if (!$info) {
			$sys_config = ConfigHandler::get();
			
			if(count($members) < 2) {
				$rets['error'] = '关注失败，TA已经消失不见了';
				return $rets;
			}
			
			if($sys_config['follow_limit']>0 && $members[$uid]['follow_count']>=$sys_config['follow_limit']) {
				$rets['error'] = '本站限制关注数量为<b>'.$sys_config['follow_limit'].'</b>人，您不能再关注更多的好友了';
				return $rets;
			}

			if($members[$buddyid]['disallow_beiguanzhu']) {
				$rets['error'] = '关注失败，TA设置了禁止被关注';
				return $rets;
			}

			if(is_blacklist($uid, $buddyid)) {
				$rets['error'] = '关注失败，对方已将您拉入了黑名单';
				return $rets;
			}

			$_tmps = jsg_role_check_allow('follow', $buddyid, $uid);
			if($_tmps && $_tmps['error']) {
				return $_tmps;
			}

			$data = array(
				'uid' => $uid,
				'buddyid' => $buddyid,
				'dateline' => TIMESTAMP,
				'buddy_lastuptime' => $members[$buddyid]['lastactivity'],
			);
			$bid = DB::insert($this->table, $data, 1, 1, 1);

			$this->count($uid);
			$this->count($buddyid);
			
			if($sys_config['extcredits_enable'] && $uid>0) {
				
				$update_credits = false;
				if($members[$buddyid]['nickname']) {
					$update_credits = update_credits_by_action(("_U".crc32($members[$buddyid]['nickname'])),$uid);
				}

				if(!$update_credits) {
					
					update_credits_by_action('buddy',$uid);
				}
			}

			if($sys_config['imjiqiren_enable'] && imjiqiren_init($sys_config)) {
				imjiqiren_send_message($members[$buddyid],'f');
			}

			if($sys_config['sms_enable'] && sms_init($sys_config)) {
				sms_send_message($members[$buddyid],'f');
			}
		} else {
			if($delete_if_exists) {
				$this->del_info($buddyid, $uid);
			} elseif($members[$buddyid]['lastactivity'] && $info['buddy_lastuptime']!=$members[$buddyid]['lastactivity']) {
				$p = array(
					'id' => $info['id'],
				);
				$this->update_lastuptime($p, $members[$buddyid]['lastactivity']);
			}
		}

		return $info;
	}
	
	
	function del_info($buddyid, $uid) {
		$buddyid = (is_numeric($buddyid) ? $buddyid : 0);
		$uid = (is_numeric($uid) ? $uid : 0);
		if($buddyid < 1 || $uid < 1) {
			return 0;
		}
		$p = array(
			'count' => 10,
			'buddyid' => $buddyid,
			'uid' => $uid,
		);
		return $this->del($p);
	}

	
	function del($p) {
		$p['count'] = ($p['count'] ? $p['count'] : 999999);
		$rets = $this->get($p);
		if(!$rets) {
			return false;
		}

		$list = $rets['list'];
		foreach($list as $row) {
			$uid = $row['uid'];
			$buddyid = $row['buddyid'];

			DB::query("DELETE FROM ".DB::table($this->table)." WHERE `uid`='{$uid}' AND `buddyid`='{$buddyid}'");

			$this->count($uid);
			$this->count($buddyid);

			$query = DB::query("select `gid` from `".TABLE_PREFIX."groupfields` where `touid`='{$buddyid}' and `uid`='{$uid}'");
			$gids = array();
			if(false != ($row = DB::fetch($query))) {
				$gids[] = $row['gid'];
			}
			if($gids) {
				DB::query("update `".TABLE_PREFIX."group` set `group_count`=if(`group_count`>1,`group_count`-1,0) where `id` in('".implode("','",$gids)."')");
				DB::query("delete from `".TABLE_PREFIX."groupfields` where `touid`='{$buddyid}' and `uid`='{$uid}'");
			}

			if($GLOBALS['_J']['config']['extcredits_enable'] && $uid>0) {
				
				update_credits_by_action('buddy_del', $uid);
			}
		}

		return true;
	}

	
	function count($uid) {
		$uid = max(0, (int) $uid);
		if($uid < 1) {
			return false;
		}

		$member = DB::fetch_first("SELECT `uid`, `follow_count`, `fans_count` FROM ".DB::table('members')." WHERE `uid`='$uid'");
		if(!$member) {
			return false;
		}
		$member['follow_count'] = max(0, (int) $member['follow_count']);
		$member['fans_count'] = max(0, (int) $member['fans_count']);

		$follow_count = $this->get(array('uid'=>$uid, 'return_count'=>1));
		if($follow_count != $member['follow_count']) {
			DB::query("update `".TABLE_PREFIX."members` set `follow_count`='{$follow_count}' where `uid`='{$uid}'");

			cache_db('rm', "{$uid}-buddyids-%", 1);
			cache_db('rm', "{$uid}-topic-%", 1);
		}

		$fans_count = $this->get(array('buddyid'=>$uid, 'return_count'=>1));
		if($fans_count != $member['fans_count']) {
			$fans_new = 0;
			$fans_new_update = '';
			if($fans_count > $member['fans_count']) {
				$fans_new = max(0, (int) (($fans_count-$member['fans_count'])));

				if($fans_new > 0) {
					$fans_new_update = " , `fans_new` = `fans_new` + '{$fans_new}'";
				}
			}

			DB::query("update `".TABLE_PREFIX."members` set `fans_count`='{$fans_count}' {$fans_new_update} where `uid`='{$uid}'");
		}

		return true;
	}

	
	function get_ids($p, $cache_time=0) {
		$p['count'] = ($p['count'] ? $p['count'] : 999999);
				$by = ($p['fields'] ? $p['fields'] : 'buddyid');
		$p['fields'] = " DISTINCT (`{$by}`) AS `{$by}` ";
		$rets = $this->get($p, $cache_time);
		if(!$rets) {
			return false;
		}
		$list = array();
		foreach($rets['list'] as $row) {
			$list[$row[$by]] = $row[$by];
		}
		return $list;
	}

	
	function get_buddyids($uid, $uptime_limit=0) {
		$uid = (is_numeric($uid) ? $uid : 0);
		if($uid < 1) {
			return false;
		}
		$uptime_limit = max(0, (int) $uptime_limit);

		$cache_id = "{$uid}-buddyids-{$uptime_limit}";
		if(false !== ($ret = cache_db('get', $cache_id))) {
			return $ret;
		}

		$p = array(
			'fields' => 'buddyid',
			'uid' => $uid,
			'order' => ' `buddy_lastuptime` DESC ',
		);
		if($uptime_limit) {
			$p['buddy_lastuptime'] = (TIMESTAMP - 86400 * $uptime_limit);
			$p['count'] = 1000;
		}
		$ret = $this->get_ids($p);

		cache_db('set', $cache_id, $ret, 3600);

		return $ret;
	}

	
	function update_lastuptime($p, $time=0) {
		$p['return_where'] = 1;
		$sql_where = $this->get($p);
		if(!$sql_where) {
			return false;
		}

		$time = ($time ? $time : TIMESTAMP);

		$ret = DB::query("UPDATE LOW_PRIORITY ".DB::table($this->table)." SET `buddy_lastuptime`='$time' $sql_where ");

		return $ret;
	}

	
	function set_remark($p, $remark='') {
		$p['return_where'] = 1;
		$sql_where = $this->get($p);
		if(!$sql_where) {
			return false;
		}

		if($remark) {
			$remark = trim(strip_tags($remark));
			$remark = cutstr($remark, 30, '');
			
			$f_rets = filter($remark);
			if ($f_rets && $f_rets['error']) {
				$remark = '';
			}
		}

		$ret = DB::query("UPDATE ".DB::table($this->table)." SET `remark`='$remark' $sql_where ");

		return $ret;
	}

	function follow_html2($member_list, $uid_field='uid', $follow_func='follow_html2', $is_one_row = 0) {
		return $this->follow_html($member_list, $uid_field, $follow_func, $is_one_row);
	}
	
	function follow_html($member_list, $uid_field='uid', $follow_func='follow_html', $is_one_row = 0) {
		if(!$member_list || MEMBER_ID < 1) {
			return $member_list;
		}
		
		if(!$uid_field) {
			$uid_field = 'uid';
		}
		
		if($is_one_row) {
			$one_row_key = false;
			if(isset($member_list[$uid_field])) {
				$one_row_key = 'one_row_key';
				$member_list = array(
					$one_row_key => $member_list,
				);
			} else {
				return $member_list;
			}
		}

		$uids = array();
		foreach($member_list as $v) {
			if(!isset($v[$uid_field]) || (!$is_one_row && isset($v['follow_html']))) {
				return $member_list;
			}

			$uid = (int) $v[$uid_field];
			if($uid > 0 && $uid != MEMBER_ID) {
				$uids[$uid] = $uid;
			}
		}

		$buddyids = array();
		$buddy_uids = array();
		if($uids) {
			$p = array(
				'fields' => 'buddyid',
				'count' => count($uids),
				'uid' => MEMBER_ID,
				'buddyid' => $uids,
			);
			$buddyids = $this->get_ids($p); 
			$p = array(
				'fields' => 'uid',
				'count' => count($uids),
				'uid' => $uids,
				'buddyid' => MEMBER_ID,
			);
			$buddy_uids = $this->get_ids($p); 		}

		if(!$follow_func) {
			$follow_func = 'follow_html';
		}
		foreach($member_list as $k=>$v) {
			$uid = $v[$uid_field];
			if($uid > 0) {
				$member_list[$k]['is_follow'] = (isset($buddyids[$uid]) ? 1 : 0);
				$member_list[$k]['is_follow_me'] = (isset($buddy_uids[$uid]) ? 2 : 0);
								$member_list[$k]['is_follow_relation'] = ($member_list[$k]['is_follow'] + $member_list[$k]['is_follow_me']);
				$member_list[$k]['follow_html'] = $follow_func($uid, $member_list[$k]['is_follow'], $member_list[$k]['is_follow_me']);
			}
		}
		
		if($is_one_row && $one_row_key) {
			$member_list = $member_list[$one_row_key];
		}

		return $member_list;
	}

	
	function blacklist($touid, $uid) {
		$ret = array();

		$touid = (is_numeric($touid) ? $touid : 0);
		$uid = (int) ($uid ? $uid : MEMBER_ID);
		if($touid > 0 && $uid > 0) {
			$ret = DB::fetch_first("SELECT * FROM ".DB::table('blacklist')." WHERE `touid`='$touid' AND `uid`='$uid'");
		}
		return $ret;
	}
	
	function add_blacklist($touid, $uid=MEMBER_ID) {
		$touid = (is_numeric($touid) ? $touid : 0);
		$uid = (int) ($uid ? $uid : MEMBER_ID);
		if($touid < 1 || $uid < 1 || $touid == $uid) {
			return false;
		}

		$info = $this->blacklist($touid, $uid);
		if(!$info) {
			$data = array(
				'touid' => $touid,
				'uid' => $uid,
			);
			$ret = DB::insert('blacklist', $data, 1, 1, 1);
			if($ret) {
				$this->del_info($touid, $uid);

				$this->del_info($uid, $touid);
			}
		}
		return $info;
	}
	
	function del_blacklist($touid, $uid=MEMBER_ID) {
		$ret = false;
		$touid = (is_numeric($touid) ? $touid : 0);
		$uid = (int) ($uid ? $uid : MEMBER_ID);
		if($touid < 1 || $uid < 1) {
			return false;
		}

		$info = $this->blacklist($touid, $uid);
		if($info) {
			$ret = DB::query("DELETE FROM ".DB::table('blacklist')." WHERE `id`='{$info['id']}'");
		}
		return $ret;
	}

	
	function check_new_topic($uid=MEMBER_ID, $update_lastactivity=0) {
		$uid = (int) ($uid ? $uid :MEMBER_ID);
		if($uid < 1) {
			return 0;
		}

		$info = jsg_member_info($uid);
		if(!$info) {
			return 0;
		}
		$t = $info['lastactivity'];

		$count = 0;
		if($t > 0 && ($t + 29 < TIMESTAMP)) {
			$p = array(
				'fields' => 'buddyid',
				'uid' => $uid,
				'count' => 100,
				'buddy_lastuptime' => $t,
			);
			$buddy_uids = $this->get_ids($p); 						
			if($buddy_uids) {
				$count = DB::result_first("SELECT COUNT(*) AS `count` FROM `".TABLE_PREFIX."topic` WHERE `uid` IN ('".implode("','",$buddy_uids)."') AND `type`!='reply' AND `dateline`>'{$t}'");
			}
		}
		if($update_lastactivity) {
			DB::query("UPDATE `".TABLE_PREFIX."members` SET `lastactivity`='".TIMESTAMP."' WHERE `uid`='$uid'");
		}

		return $count;
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