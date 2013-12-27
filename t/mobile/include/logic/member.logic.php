<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename member.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 1166865969 1747923795 3296 $
 *******************************************************************/




class MemberLogic
{
	var $TopicLogic;
	var $TopicListLogic;
	var $Config;
	var $DatabaseHandler;
	
	function MemberLogic()
	{
		Load::logic('topic');
		$this->TopicLogic = new TopicLogic();
		$this->Config = ConfigHandler::get();
		$this->DatabaseHandler = &Obj::registry('DatabaseHandler');
	}
	
		function getMemberList($param)
	{
				$member_list = array();
		
		$where_sql = " 1 ";
		
				$order_sql = " regdate DESC ";
		
		$max_id = intval($param['max_id']);
		
		$limit = intval($param['limit']);
		if (empty($limit)) {
			$limit = 20;
		}
		$nickname = trim($param['nickname']);
		if (!empty($nickname)) {
			$nickname= getSafeCode($nickname);
						$where_sql .= " AND ".build_like_query("nickname", $nickname)." ";
		}
		
		$sql = "select count(*) from `".TABLE_PREFIX."members`  WHERE {$where_sql}";
		$total_record = DB::result_first($sql);
		if ($total_record > 0) {
			if ($max_id > 0) {
				$where_sql .= " AND uid < {$max_id} ";
			}
			$sql = "select `uid`,`ucuid`,`username`,`nickname`,`face_url`,`face`,`fans_count`,`topic_count`,`province`,`city`,`validate` 
					from `".TABLE_PREFIX."members` 
					WHERE {$where_sql}  
					ORDER BY {$order_sql}  
					LIMIT {$limit} ";
			$query = DB::query($sql);
			$uids = array();
			while ($row = DB::fetch($query)) {
				$row['face'] = face_get($row);
				$member_list[] = $row;
				$uids[$row['uid']] = $row['uid'];
			}
			
			if($uids && MEMBER_ID>0) {
				
								$sql = "SELECT `id`,`uid`,`buddyid` 
						FROM ".DB::table('buddys')." WHERE `uid` IN (".jimplode($uids).")";
				$query = DB::query($sql);
				$buddys_list = array();
				while ($row = DB::fetch($query)) {
					$buddys_list[] = $row;
				}
				
				$buddys = array();
				$sql = "SELECT `buddyid` AS `id`,`remark` 
						FROM ".DB::table('buddys')."   
						WHERE `uid`='".MEMBER_ID."' AND `buddyid` IN(".jimplode($uids).")";
				$query = DB::query($sql);
				while ($row = DB::fetch($query)) {
					$buddys[$row['id']] = $row['id'];
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
			}
			$member_list = array_values($member_list);
			$tmp_ary = $member_list;
			$tmp = array_pop($tmp_ary);
			$max_id = $tmp['uid'];
			$ret = array(
				'member_list' => $member_list,
				'total_record' => $total_record,
				'list_count' => count($member_list),
				'max_id' => $max_id,
			);
			return $ret;
		}
		return 400;
	}
}


?>