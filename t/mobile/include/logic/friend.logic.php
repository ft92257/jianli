<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename friend.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 753630277 1666527197 12912 $
 *******************************************************************/




class FriendLogic
{
	var $TopicLogic;
	var $TopicListLogic;
	var $Config;
	
	function FriendLogic()
	{
		Load::logic('topic');
		$this->TopicLogic = new TopicLogic();
		$this->Config = ConfigHandler::get();
	}
	
		function getFollowList($param)
	{
		$id = intval($param['max_id']);
		$uid = intval($param['uid']);
		if (empty($uid)) {
			$uid = MEMBER_ID;
			$member = $this->TopicLogic->GetMember($uid);
		} else {
			$member = $this->TopicLogic->GetMember($uid);
			if (empty($member)) {
												return 300;
			}
		}
		
		$limit = 20;
		if ($param['limit'] > 0) {
			$limit = $param['limit'];
		}
		
				$count = $member['follow_count'];	
		if ($count > 0) {
			$where_sql = ' 1 ';
		 	if ($id > 0) {
		 		$where_sql = " b.id<{$id} ";
		 	}
			$sql = "SELECT b.remark,b.id AS fl_id,m.uid,m.nickname,m.username,m.face,m.fans_count,m.signature,m.topic_count,m.province,m.city    
					FROM ".DB::table('buddys')." AS b  
					LEFT JOIN ".DB::table('members')." AS m 
					ON m.`uid` = b.`buddyid` 
					WHERE b.`uid`='{$member['uid']}' AND {$where_sql}   
					ORDER BY b.id DESC 
					LIMIT {$limit}";
			$query = DB::query($sql);
			while ($row = $query->GetRow()) {
				$member_list[$row['uid']] = $this->TopicLogic->MakeMember($row);
				$uids[] = $row['uid'];
			}
			if (empty($member_list)) {
								return 401;
			} else {
				
								$sql = "SELECT `id`,`uid`,`buddyid` 
						FROM ".DB::table('buddys')." WHERE `uid` IN (".jimplode($uids).")";
				$query = DB::query($sql);
				$buddys_list = array();
				while ($row = DB::fetch($query)) {
					$buddys_list[] = $row;
				}
				
				$buddys = array();
				if(MEMBER_ID > 0) {
					$sql = "SELECT `buddyid` AS `id`,`remark` 
							FROM ".DB::table('buddys')."   
							WHERE `uid`='".MEMBER_ID."' AND `buddyid` IN(".jimplode($uids).")";
					$query = DB::query($sql);
					while ($row = DB::fetch($query)) {
						$buddys[$row['id']] = $row['id'];
					}
				}
				
				foreach ($member_list as $key => $m) {
										if ($m['uid'] == MEMBER_ID) {
												$member_list[$key]['friendship'] = 1;
					} else if (isset($buddys[$m['uid']])) {
												$member_list[$key]['friendship'] = 2;
						if (!empty($buddys_list)) {
							foreach ($buddys_list as $v) {
								if ($v['uid'] == $m['uid'] && $v['buddyid'] == MEMBER_ID) {
									$member_list[$key]['friendship'] = 4;
									break;
								}
							}
						}
					} else {
												$member_list[$key]['friendship'] = 0;
					}
				}
				$member_list = array_values($member_list);
				$tmp_ary = $member_list;
				$tmp = array_pop($tmp_ary);
				$max_id = $tmp['fl_id'];
				$ret = array(
					'member_list' => $member_list,
					'total_record' => $count,
					'list_count' => count($member_list),
					'max_id' => $max_id,
				);
				return $ret;
							}
		}
				return 400;
	}
	
		function getFansList($param)
	{
		$id = intval($param['max_id']);
		$uid = intval($param['uid']);
		if (empty($uid)) {
			$uid = MEMBER_ID;
			$member = $this->TopicLogic->GetMember($uid);
		} else {
			$member = $this->TopicLogic->GetMember($uid);
			if (empty($member)) {
												return 300;
			}
		}
		
		$limit = 20;
		if ($param['limit'] > 0) {
			$limit = $param['limit'];
		}
		
		$count = $member['fans_count'];
		if ($count > 0) {
			$where_sql = ' 1 ';
		 	if ($id > 0) {
		 		$where_sql = " b.id<{$id} ";
		 	}
			$limit = " LIMIT {$limit} ";
			$sql = "SELECT b.`uid` AS id,remark,b.id AS fn_id,m.uid,m.nickname,m.username,m.face,m.fans_count,m.signature,m.topic_count,m.province,m.city     
					FROM ".DB::table('buddys')." AS b 
					LEFT JOIN ".DB::table('members')." AS m 
					ON m.`uid` = b.`uid` 
					where b.`buddyid`='{$member['uid']}' AND {$where_sql}   
					ORDER BY id DESC 
					{$limit}";
			$query = DB::query($sql);
			$uids = array();
			$member_list = array();
			while ($row = DB::fetch($query)) {
				$member_list[$row['uid']] = $this->TopicLogic->MakeMember($row);
				$uids[$row['id']] = $row['id'];
			}
			if (empty($member_list)) {
								return 401;
			} else {
								$sql = "SELECT `id`,`uid`,`buddyid` 
						FROM ".DB::table('buddys')." WHERE `uid` IN (".jimplode($uids).")";
				$query = DB::query($sql);
				$buddys_list = array();
				while ($row = DB::fetch($query)) {
					$buddys_list[] = $row;
				}
				
				$buddys = array();
				if(MEMBER_ID > 0) {
					$sql = "SELECT `buddyid` AS `id`,`remark` 
							FROM ".DB::table('buddys')."   
							WHERE `uid`='".MEMBER_ID."' AND `buddyid` IN(".jimplode($uids).")";
					$query = DB::query($sql);
					while ($row = DB::fetch($query)) {
						$buddys[$row['id']] = $row['id'];
					}
				}
				
				foreach ($member_list as $key => $m) {
										if ($m['uid'] == MEMBER_ID) {
												$member_list[$key]['friendship'] = 1;
					} else if (isset($buddys[$m['uid']])) {
												$member_list[$key]['friendship'] = 2;
						if (!empty($buddys_list)) {
							foreach ($buddys_list as $v) {
								if ($v['uid'] == $m['uid'] && $v['buddyid'] == MEMBER_ID) {
									$member_list[$key]['friendship'] = 4;
									break;
								}
							}
						}
					} else {
												$member_list[$key]['friendship'] = 0;
					}
				}
				$member_list = array_values($member_list);
				$tmp_ary = $member_list;
				$tmp = array_pop($tmp_ary);
				$max_id = $tmp['fn_id'];
				$ret = array(
					'member_list' => $member_list,
					'total_record' => $count,
					'list_count' => count($member_list),
					'max_id' => $max_id,
				);
				return $ret;
							}
		}
				return 400;
	}
	
		function addFollow($uid)
	{
				if ($uid == MEMBER_ID) {
									return 401;
		} else {
			$member = $this->TopicLogic->GetMember($uid);
			if (empty($member)) {
												return 300;
			}
		}
		
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('buddys')." WHERE `uid`='".MEMBER_ID."' AND `buddyid`='{$uid}'");
		if ($count == 0) {
        	$timestamp = time();
            $data = array(
            	'uid' => MEMBER_ID,
            	'buddyid' => $uid,
            	'dateline' => $timestamp,
            	'buddy_lastuptime' => $timestamp,
            );
        	DB::insert("buddys", $data);
        	Load::model('buddy')->count(MEMBER_ID);
    		Load::model('buddy')->count($uid);
    		    		return 200;
		}
						return 310;
	}
	
		function delFollow($uid)
	{
				if ($uid == MEMBER_ID) {
									return 401;
		} else {
			$member = $this->TopicLogic->GetMember($uid);
			if (empty($member)) {
												return 300;
			}
		}
		
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('buddys')." WHERE `uid`='".MEMBER_ID."' AND `buddyid`='{$uid}'");
		if ($count == 1) {
			DB::query("DELETE FROM ".DB::table('buddys')." WHERE `uid`='".MEMBER_ID."' AND `buddyid`='{$uid}'");
        	Load::model('buddy')->count(MEMBER_ID);
    		Load::model('buddy')->count($uid);
    		    		return 200;
		}
						return 311;
	}
	
		function checkFollow($uid) {
		if ($uid == MEMBER_ID) {
			return 401;
		} else {
			$member = $this->TopicLogic->GetMember($uid);
			if (empty($member)) {
				return 300;
			}
		}
		$isBlackList = $this->check($uid);
		if ($isBlackList) {
			return -1;
		}
		$fllow_count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('buddys')." WHERE `uid`='".MEMBER_ID."' AND `buddyid`='{$uid}'");
		$fan_count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('buddys')." WHERE `uid`='".$uid."' AND `buddyid`='".MEMBER_ID."'");
		if ($fllow_count && $fan_count) {
			return 2;
		} else if ($fllow_count > 0) {
			return 1;
		}
		return 0;
	}
	
		function getBlackList($param)
	{
		$uid = MEMBER_ID;
		$id = intval($param['max_id']);
		$where_sql = " bl.uid='{$uid}' ";
		$order_sql = " bl.id DESC ";
		$limit = intval($param['limit']);
		$count = DB::result_first("SELECT COUNT(*) 
								   FROM ".DB::table('blacklist')." AS bl
								   LEFT JOIN ".DB::table("members")." AS m 
								   USING(uid) 
								   WHERE {$where_sql}");
		if ($count > 0) {
			$member_list = array();
			if ($id > 0) {
				$where_sql .= " AND bl.id<{$id} ";
			}
			$query = DB::query("SELECT bl.id AS bl_id,m.uid,m.nickname,m.username,m.face,m.fans_count,m.signature,m.topic_count,m.province,m.city  
								FROM ".DB::table('blacklist')." AS bl
								LEFT JOIN ".DB::table("members")." AS m 
								ON bl.touid = m.uid  
								WHERE {$where_sql}
								ORDER BY {$order_sql}
								LIMIT  {$limit} ");
			while ($row = DB::fetch($query)) {
				$raw = $this->TopicLogic->MakeMember($row);
				$raw['friendship'] = -1;
				$member_list[] = $raw;
							}
			if (empty($member_list)) {
								return 401;
			} else {
				$member_list = array_values($member_list);
				$tmp_ary = $member_list;
				$tmp = array_pop($tmp_ary);
				$max_id = $tmp['bl_id'];
				$r = array(
					'total_record' => $count,
					'member_list' => $member_list,
					'max_id' => $max_id,
				);
								return $r;
			}
		}
				return 400;
	}
	
		function addBlacklist($uid)
	{
				if ($uid == MEMBER_ID) {
									return 402;
		} else {
			$member = $this->TopicLogic->GetMember($uid);
			if (empty($member)) {
												return 300;
			}
		}
		
				$touid = $uid;
		$uid = MEMBER_ID;
		
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('blacklist')." WHERE uid='{$uid}' AND touid='{$touid}'");
		if ($count == 0) {
			$sql = "insert into `".TABLE_PREFIX."blacklist` (`uid`,`touid`) values ('{$uid}','{$touid}')";
			DB::query($sql);
	
						$sql = "delete from `".TABLE_PREFIX."buddys` where `buddyid`='{$touid}' and `uid` = '{$uid}'";
			DB::query($sql);
			
			$sql = "delete from `".TABLE_PREFIX."buddys` where `buddyid`='{$uid}' and `uid` = '{$touid}'";
			DB::query($sql);
	
						Load::model('buddy')->count($touid);
    		Load::model('buddy')->count($uid);
						return 200;
		}
				return 312;
	}
	
		function delBlacklist($uid)
	{
				if ($uid == MEMBER_ID) {
									return 402;
		} else {
			$member = $this->TopicLogic->GetMember($uid);
			if (empty($member)) {
												return 300;
			}
		}
		
				$touid = $uid;
		$uid = MEMBER_ID;
		
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('blacklist')." WHERE uid='{$uid}' AND touid='{$touid}'");
		if ($count > 0) {
			$sql = "delete from `".TABLE_PREFIX."blacklist` where `touid`='{$touid}' and `uid` = '".MEMBER_ID."'";
			DB::query($sql);
						return 200;
		}
				return 313;
	}
	
	function check($uid)
	{
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('blacklist')." WHERE uid='".MEMBER_ID."' AND touid='{$uid}'");
		return $count > 0 ? true : false;
	}
	
	
	
}


?>