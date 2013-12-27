<?php
/**
 *
 * 长文逻辑操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: longtext.logic.php 1374 2012-08-15 07:07:59Z wuliyong $
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

/**
 * 
 * 长文本的数据库逻辑操作类
 * 
 * @author 狐狸<foxis@qq.com>
 *
 */
class LongtextLogic
{
	var $table = 'topic_longtext';
	
	function LongtextLogic() {
		;	
	}
	
	function get($p) {
		;
	}
	
	function get_info($id, $make=0) {
		$id = is_numeric($id) ? $id : 0;
		if($id < 1) {
			return array();
		}
		
		$ret = DB::fetch_first("select * from ".DB::table($this->table)." where `id`='$id'");		
		if($make && $ret['longtext']) {
			$ret['longtext'] = $this->make($ret['longtext']);
		}
		
		return $ret;
	}
	
	function add($longtext, $uid=0)
	{
		$longtext = $this->_longtext($longtext);
		if(!$longtext)
		{
			return 0;
		}
		$uid = (is_numeric($uid) ? $uid : 0);
		
		$arr = array(
			'longtext' => $longtext,
			'uid' => ($uid > 0 ? $uid : MEMBER_ID),
			'dateline' => time(),
			'tid' => 0,
			'views' => 0,
		);
		$ret = (int) (DB::insert($this->table, $arr, 1));
		
		return $ret;
	}
	
	function modify($tid, $longtext)
	{
		$tid = is_numeric($tid) ? $tid : 0;
		if($tid < 1) return 0;
		
		$info = DB::fetch_first("select * from ".DB::table($this->table)." where `tid`='$tid'");
		if(!$info) return 0;
		
		$id = $info['id'];
		
		$longtext = $this->_longtext($longtext);
		
		$arr = array(
			'longtext' => $longtext,
			'last_modify' => time(),
			'modify_times' => (int) $info['modify_times'] + 1,		
		);
		$ret = DB::update($this->table, $arr, array('id'=>$id));
		
		return $id;
	}
	
	function make($longtext) {
		if(!$longtext) {
			return '';
		}
		
		$row = array(
			'content' => $longtext,		
		);
		
		
		$TopicLogic = Load::logic('topic', 1);
		
		$row = $TopicLogic->Make($row, array(), 0);
		
		return $row['content'];
	}
	
	function set_tid($id, $tid)
	{
		$id = is_numeric($id) ? $id : 0;
		if($id < 1) return 0;
		
		$tid = is_numeric($tid) ? $tid : 0;
		
		return DB::query("update ".DB::table($this->table)." set `tid`='$tid' where `id`='$id'");
	}
	
	function set_views($id, $views)
	{
		$id = is_numeric($id) ? $id : 0;
		if($id < 1) return 0;
		
		$views = is_numeric($views) ? $views : 0;
		
		return DB::query("update ".DB::table($this->table)." set `views`='$views' where `id`='$id'");
	}
	
	function _longtext($longtext)
	{
		$longtext = trim($longtext);
		
		$search = array(
			'~[\t]+~',
			'~([\r\n]){3,}~',
		);
		$replace = array(
			' ',
			'\\1\\1',			
		);
		$longtext = preg_replace($search, $replace, $longtext);
		
		
		return $longtext;
	}
	
	function clear_invalid($time = 300) {
		$time = TIMESTAMP - max(0, (int) $time);
		return DB::query("DELETE FROM ".DB::table($this->table)." WHERE `tid`=0 AND `dateline`<'$time'");
	}
	
}

?>