<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename tag.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 616617263 1117754062 4047 $
 *******************************************************************/




class MTagLogic
{
	var $TopicLogic;
	var $TopicListLogic;
	var $Config;
	var $DatabaseHandler;
	
	function MTagLogic()
	{
		Load::logic('topic');
		$this->TopicLogic = new TopicLogic();
		$this->Config = ConfigHandler::get();
		$this->DatabaseHandler = &Obj::registry('DatabaseHandler');
	}
	
		function getTagList($param)
	{
				$max_id = intval($param['max_id']);
		$uid = intval($param['uid']);
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('members')." WHERE uid='{$uid}'");
		if (!$count) {
						return 300;
		}
		
		$limit = intval($param['limit']);
		if (empty($limit)) {
			$limit = 20;
		}
		
		$tag_count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('tag_favorite')." WHERE uid='{$uid}'");
		$tag_list = array();
		if ($tag_count > 0) {
			$where = " ";
			if ($max_id > 0) {
				$where .= " AND id<{$max_id} ";
			}
			$query = DB::query("SELECT * FROM ".DB::table('tag_favorite')." WHERE uid='{$uid}' {$where} ORDER BY dateline DESC LIMIT {$limit} ");
			while ($row = DB::fetch($query)) {
				$tag_list[] = $row;
			}
			if (!empty($tag_list)) {
				$tmp_ary = $tag_list;
				$tmp = array_pop($tmp_ary);
				$max_id = $tmp['id'];
				$r = array(
					'tag_list' => $tag_list,
					'total_record' => $tag_count,
					'list_count' => count($tag_list),
					'max_id' => $max_id,
				);
				return $r;
			}
		}
		return 400;
	}
	
		function favorite($param)
	{
		$uid = MEMBER_ID;
		$timestamp = time();

		$tag = trim($param['tag']);
		$sql = "select * from `".TABLE_PREFIX."tag` where `name`='{$tag}'";
		$query = $this->DatabaseHandler->Query($sql);
		$tag_info = $query->GetRow();
		if(!$tag_info) {
						return 500;
		}

		$sql = "select * from `".TABLE_PREFIX."tag_favorite` where `uid`='{$uid}' and `tag`='{$tag}'";
		
		$query = $this->DatabaseHandler->Query($sql);
		$is_favorite = ($query->GetNumRows()>0);
		$tag_favorite = $query->GetRow();
		

		$sql = "select count(*) as `tag_favorite_count` from `".TABLE_PREFIX."tag_favorite` where `uid`='{$uid}'";
		$query = $this->DatabaseHandler->Query($sql);
		$row = $query->GetRow();
		if($row) {
			$sql = "update `".TABLE_PREFIX."members` set `tag_favorite_count`='{$row['tag_favorite_count']}' where `uid`='{$uid}'";
			$this->DatabaseHandler->Query($sql);
		}
				if ('delete' == $param['op']) {
			if ($is_favorite) {
				$id = $tag_favorite['id'];

				$sql = "delete from `".TABLE_PREFIX."tag_favorite` where `id`='{$id}'";
				$this->DatabaseHandler->Query($sql);

				$sql = "update `".TABLE_PREFIX."members` set `tag_favorite_count`=if(`tag_favorite_count`>1,`tag_favorite_count`-1,0) where `uid`='{$uid}'";
				$this->DatabaseHandler->Query($sql);


								$sql = "update `".TABLE_PREFIX."tag` set `tag_count`=`tag_count`-1 where `id`='{$tag_info['id']}'";
				$this->DatabaseHandler->Query($sql);

			}
		} else {
			if(!$is_favorite) {
				
				$sql = "insert into `".TABLE_PREFIX."tag_favorite` (`uid`,`tag`,`dateline`) values ('{$uid}','{$tag}','{$timestamp}')";
				$this->DatabaseHandler->Query($sql);
				$favorite_tag_id = $this->DatabaseHandler->Insert_ID();
				
				$sql = "update `".TABLE_PREFIX."members` set `tag_favorite_count`=`tag_favorite_count`+1 where `uid`='{$uid}'";
				$this->DatabaseHandler->Query($sql);

								$sql = "update `".TABLE_PREFIX."tag` set `tag_count`=`tag_count`+1 where `id`='{$tag_info['id']}'";
				$this->DatabaseHandler->Query($sql);
			
			}
		}
		return 200;
	}
	
	function checkFavorite($uid, $tag)
	{
		$sql = "select COUNT(*) from `".TABLE_PREFIX."tag_favorite` where `uid`='{$uid}' and `tag`='{$tag}'";
		$count = DB::result_first($sql);
		return $count > 0 ? true : false;
	}
}


?>