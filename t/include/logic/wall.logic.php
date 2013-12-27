<?php
/**
 *
 * 上墙逻辑操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: wall.logic.php 861 2012-04-27 02:28:31Z wuliyong $
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

/**
 * 
 * 上墙的数据库逻辑操作类
 * 
 * @author 狐狸<foxis@qq.com>
 *
 */
class WallLogic
{
	function WallLogic()
	{
		;	
	}
	
	
	function get_wall_info($id, $isuid = 0, $if_not_exists_add = 0)
	{
		$id = is_numeric($id) ? $id : 0;
		if($id < 1) return array();
				
		$wall_info = DB::fetch_first("select * from ".DB::table('wall')." where ".($isuid ? "`uid`" : "`id`")."='$id'");
		if(!$wall_info && $isuid && $if_not_exists_add)
		{
			$wall_info['uid'] = $id;
			$wall_info['id'] = $this->add_wall($id);
		}
		return $wall_info;
	}
	
	
	function add_wall($uid)
	{
		$wall_info = $this->get_wall_info($uid, 1);
		if($wall_info)
		{
			return -1;
		}
		$ret = DB::query("insert into ".DB::table('wall')." (`uid`) values ('$uid')");
		if(!$ret)
		{
			return 0;
		}
		return DB::insert_id();
	}
	
	
	function modify_wall($p)
	{
		$p['id'] = is_numeric($p['id']) ? $p['id'] : 0;
		if($p['id'] < 1) return 0;
		
		$sets = array(
			'wall_reload_time' => max(1, (int) $p['wall_reload_time']),
			'auto_wall_tag' => trim(strip_tags($p['auto_wall_tag'])),
			'screen_ad_top' => $p['screen_ad_top'],
			'screen_ad_left' => $p['screen_ad_left'],
			'screen_ad_right' => $p['screen_ad_right'],
		);
		if(isset($p['auto_wall_tid'])) {
			$sets['auto_wall_tid'] = max(0, (int) $p['auto_wall_tid']);
		}
		
		return DB::update('wall', $sets, array('id' => $p['id']));
	}
	
	
	function set_wall_status($wall_id, $status)
	{
		$wall_id = is_numeric($wall_id) ? $wall_id : 0;
		if($wall_id < 1) return 0;
		
		$status = $status ? 1 : 0;
		
		return DB::query("update ".DB::table('wall')." set `status`='$status' where `id`='$wall_id'");
	}
	
	
	function set_wall_last_load_time($wall_id, $time = null)
	{
		$wall_id = is_numeric($wall_id) ? $wall_id : 0;
		if($wall_id < 1) return 0;
		
		$time = is_null($time) ? time() : $time;
		
		return DB::query("update ".DB::table('wall')." set `last_load_time`='$time' where `id`='$wall_id'");
	}
	
	
	function set_wall_last_load_tid($wall_id, $tid = 0)
	{
		$wall_id = is_numeric($wall_id) ? $wall_id : 0;
		if($wall_id < 1) return 0;
		
		$tid = is_numeric($tid) ? $tid : 0;
		
		return DB::query("update ".DB::table('wall')." set `last_load_tid`='$tid' where `id`='$wall_id'");
	}
	
	
	function set_wall_auto_wall_tid($wall_id, $tid = 0)
	{
		$wall_id = is_numeric($wall_id) ? $wall_id : 0;
		if($wall_id < 1) return 0;
		
		$tid = is_numeric($tid) ? $tid : 0;
		
		return DB::query("update ".DB::table('wall')." set `auto_wall_tid`='$tid' where `id`='$wall_id'");
	}
	
	
	function get_wall_material_info($wall_id, $type, $key)
	{
		$wall_id = is_numeric($wall_id) ? $wall_id : 0;
		if($wall_id < 1) return 0;
		
		$type = is_numeric($type) ? $type : 0;
		
		$akey = addslashes($key);
		
		$wall_material_info = DB::fetch_first("select * from ".DB::table('wall_material')." where `wall_id`='$wall_id' and `type`='$type' and `key`='$akey'");
		return $wall_material_info;
	}
	
	
	function get_wall_material($wall_id, $type = 0)
	{
		$wall_id = is_numeric($wall_id) ? $wall_id : 0;
		if($wall_id < 1) return 0;
		
		$type = is_numeric($type) ? $type : 0;
		
		$query = DB::query("select * from ".DB::table('wall_material')." where `wall_id`='$wall_id' and `type`='$type'");
		$list = array();
		while (false != ($row = DB::fetch($query)))
		{
			$list[] = $row;
		}
		return $list;
	}	
	
	
	function add_wall_material($wall_id, $type, $key)
	{
		$wall_id = is_numeric($wall_id) ? $wall_id : 0;
		if($wall_id < 1) return 0;
		
		$type = is_numeric($type) ? $type : 0;
		
		$akey = addslashes($key);
		
		$wall_material_info = $this->get_wall_material_info($wall_id, $type, $key);
		if($wall_material_info)
		{
			return -1;
		}
		
		$ret = DB::query("insert into ".DB::table('wall_material')." (`wall_id`,`type`,`key`) values ('$wall_id','$type','$akey')");
		if($ret)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	
	
	function del_wall_material($wall_id, $type, $key)
	{
		$wall_id = is_numeric($wall_id) ? $wall_id : 0;
		if($wall_id < 1) return 0;
		
		$type = is_numeric($type) ? $type : 0;
		
		$akey = addslashes($key);
		
		return DB::query("delete from ".DB::table('wall_material')." where `wall_id`='$wall_id' and `type`='$type' and `key`='$akey'");
	}
	
	
	function get_wall_draft_info($wall_id, $tid, $mark)
	{
		$wall_id = is_numeric($wall_id) ? $wall_id : 0;
		if($wall_id < 1) return 0;
		
		$tid = is_numeric($tid) ? $tid : 0;
		if($tid < 1) return 0;
		
		$mark = is_numeric($mark) ? $mark : 0;
		
		$wall_draft_info = DB::fetch_first("select * from ".DB::table('wall_draft')." where `wall_id`='$wall_id' and `tid`='$tid' and `mark`='$mark'");
		return $wall_draft_info;
	}
	
	
	function get_wall_draft($wall_id, $mark)
	{
		$wall_id = is_numeric($wall_id) ? $wall_id : 0;
		if($wall_id < 1) return 0;
				
		$mark = is_numeric($mark) ? $mark : 0;
		
		$query = DB::query("select * from ".DB::table('wall_draft')." `wall_id`='$wall_id' and `mark`='$mark'");
		$list = array();
		while(false != ($row = DB::fetch($query)))
		{
			$list[] = $row;
		}
		return $list;
	}
	
	
	function get_wall_draft_tids($wall_id, $mark, $build_in = 0)
	{
		$wall_id = is_numeric($wall_id) ? $wall_id : 0;
		if($wall_id < 1) return 0;
				
		$mark = is_numeric($mark) ? $mark : 0;
		
		$list = $this->get_wall_draft($wall_id, $mark);
		$tids = array();
		if($list)
		{
			foreach($list as $v)
			{
				$tids[$v['tid']] = $v['tid'];
			}
		}			
		
		if($build_in)
		{
			return "'".implode("','",$tids)."'";
		}
		else
		{
			return $tids;
		}
	}
	
	
	function add_wall_draft($wall_id, $tid, $mark)
	{
		$wall_id = is_numeric($wall_id) ? $wall_id : 0;
		if($wall_id < 1) return 0;
		
		$tid = is_numeric($tid) ? $tid : 0;
		if($tid < 1) return 0;
		
		$mark = is_numeric($mark) ? $mark : 0;
		
		$wall_draft_info = $this->get_wall_draft_info($wall_id, $tid, $mark);
		if($wall_draft_info)
		{
			return -1;
		}
		
		$ret = DB::query("insert into ".DB::table('wall_draft')." (`wall_id`,`tid`,`mark`) values ('$wall_id','$tid','$mark')");
		if($ret)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	
	
	function del_wall_draft($wall_id, $tid, $mark)
	{
		$wall_id = is_numeric($wall_id) ? $wall_id : 0;
		if($wall_id < 1) return 0;
		
		$tid = is_numeric($tid) ? $tid : 0;
		if($tid < 1) return 0;
		
		$mark = is_numeric($mark) ? $mark : 0;
		
		return DB::query("delete from ".DB::table('wall_draft')." where `wall_id`='$wall_id' and `tid`='$tid' and `mark`='$mark'");
	}
	
	
	function get_wall_playlist_info($wall_id, $tid)
	{
		$wall_id = is_numeric($wall_id) ? $wall_id : 0;
		if($wall_id < 1) return 0;
		
		$tid = is_numeric($tid) ? $tid : 0;
		if($tid < 1) return 0;
		
		$wall_playlist_info = DB::fetch_first("select * from ".DB::table('wall_playlist')." where `wall_id`='$wall_id' and `tid`='$tid'");
		return $wall_playlist_info;
	}
	
	
	function get_wall_playlist($wall_id, $limit = 0)
	{
		$wall_id = is_numeric($wall_id) ? $wall_id : 0;
		if($wall_id < 1) return 0;		
		
		$limit = is_numeric($limit) ? $limit : 0;
		
		$sql_limit = '';		
		if($limit > 0)
		{
			$sql_limit = " limit 0, $limit ";
		}
		
		$query = DB::query("select * from ".DB::table('wall_playlist')." where `wall_id`='$wall_id' order by `order` asc $sql_limit ");
		$list = array();
		while(false != ($row = DB::fetch($query)))
		{
			$list[] = $row;
		}
		return $list;
	}
	
	
	function get_wall_playlist_tids($wall_id, $build_id = 0, $limit = 0)
	{
		$wall_id = is_numeric($wall_id) ? $wall_id : 0;
		if($wall_id < 1) return 0;		
		
		$limit = is_numeric($limit) ? $limit : 0;
		
		$list = $this->get_wall_playlist($wall_id, $limit);
		$tids = array();
		if($list)
		{
			foreach($list as $v)
			{
				$tids[$v['tid']] = $v['tid'];
			}
		}		
	
		if($build_id)
		{
			return "'".implode("','",$tids)."'";
		}
		else
		{
			return $tids;
		}
	}
	
	
	function get_wall_playlist_order($wall_id, $return_min = 0)
	{
		$wall_id = is_numeric($wall_id) ? $wall_id : 0;
		if($wall_id < 1) return 0;		
		
		$list = $this->get_wall_playlist($wall_id);
		if(!$list)
		{
			return 9999;
		}
		
		$max = $min = null;		
		foreach($list as $v)
		{
			$od = $v['order'];
			if(is_null($max) || $od >= $max) $max = $od;
			if(is_null($min) || $od <= $min) $min = $od;
		}

		if($return_min)
		{
			return $min - 1;
		}
		return $max + 1;
	}
	
	
	function add_wall_playlist($wall_id, $tid, $unshift = 0)
	{
		$wall_id = is_numeric($wall_id) ? $wall_id : 0;
		if($wall_id < 1) return 0;
		
		$tid = is_numeric($tid) ? $tid : 0;
		if($tid < 1) return 0;
		
		$wall_playlist_info = $this->get_wall_playlist_info($wall_id, $tid);
		if($wall_playlist_info)
		{
			return -1;
		}
		
		$order = $this->get_wall_playlist_order($wall_id, $unshift);
		$ret = DB::query("insert into ".DB::table('wall_playlist')." (`wall_id`,`tid`,`order`) values ('$wall_id','$tid','$order')");
		if($ret)
		{
			return 1;
		}
		return 0;
	}
	
	
	function del_wall_playlist($wall_id, $tid)
	{
		$wall_id = is_numeric($wall_id) ? $wall_id : 0;
		if($wall_id < 1) return 0;
		
		if(!$tid) return 0;
		
		$tids = (array) $tid;
		
		return DB::query("delete from ".DB::table('wall_playlist')." where `wall_id`='$wall_id' and `tid` in('".implode("','", $tids)."')");
	}
	
	
	function clear_wall_playlist($wall_id)
	{
		$wall_playlist_tids = $this->get_wall_playlist_tids($wall_id);
		
		$ret = 1;
		if($wall_playlist_tids)
		{
			foreach($wall_playlist_tids as $tid)
			{
				$ret = ($ret && $this->del_wall_playlist($wall_id, $tid));
			}
		}
		
		return $ret;
	}
	
}

?>