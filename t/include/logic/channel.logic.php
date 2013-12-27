<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename channel.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-08-17 19:12:46 1840527567 1164224462 5943 $
 *******************************************************************/




if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ChannelLogic
{
	
	function ChannelLogic()
	{
	}

	
	function is_exists($ch_id)
	{
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('channel')." WHERE ch_id='{$ch_id}'");
		return $count > 0 ? true : false;
	}

	
	function id2subject($ch_id)
	{
		static $channelname;
		if($channelname[$ch_id]){
			$subject = $channelname[$ch_id];
		}else{
			$subject = DB::result_first("SELECT ch_name FROM ".DB::table('channel')." WHERE ch_id='{$ch_id}' ");
			$channelname[$ch_id] = $subject;
		}
		return $subject;
	}

	
	function mychannel()
	{
		$channels = array();
		$query = DB::query("SELECT bc.ch_id,c.ch_name FROM ".DB::table('buddy_channel')." bc LEFT JOIN ".DB::table('channel')." c ON bc.ch_id=c.ch_id where bc.uid='".MEMBER_ID."'");
		while ($value = DB::fetch($query))
		{
			$channels[$value['ch_id']] = $value;
		}
		return $channels;
	}
	
	
	function category_exists($ch_name, $pid = 0)
	{
		$count = DB::result_first("SELECT COUNT(*) 
								   FROM ".DB::table('channel')." 
								   WHERE ch_name='{$ch_name}' AND parent_id='{$pid}'");
		return $count > 0 ? true : false;
	}
	
	
	function id2category($ch_id)
	{
		$category = array();
		$category = DB::fetch_first("SELECT * FROM ".DB::table('channel')." WHERE ch_id='{$ch_id}'");
		return $category;
	}
	
	
	function &get_category_tree()
	{
		$tree = $cat_ary = array();
		$query = DB::query("SELECT *  
							FROM ".DB::table('channel')." 
							ORDER BY display_order ASC");
		while ($value = DB::fetch($query)) {
			$cat_ary[] = $value;
		}
		
		if (!empty($cat_ary)) {
			$tree = $this->category_tree($cat_ary);
		}
		return $tree;
	}
	
	
	function category_tree($data, $parent_id = 0)
	{
		$tree = array();
		foreach ($data as $value) {
			if ($value['parent_id'] == $parent_id) {
				$tmp = array();
				$tmp = $value;
				$tmp['child'] = $this->category_tree($data, $value['ch_id']);
				$tree[$value['ch_id']] = $tmp;
			}
		}
		return $tree;
	}
	
	
	function add_category($ch_name, $display_order = 0, $parent_id = 0)
	{	
		$set_ary = array(
			'ch_name' => $ch_name,
			'parent_id' => $parent_id,
			'display_order' => $display_order,
		);
		$qid = DB::insert('channel', $set_ary, true);
		return $qid;
	}
	
	
	function update_category($ch_id, $ch_name, $display_order)
	{
		$set_ary = array(
			'ch_name' => $ch_name,
			'display_order' => $display_order,
		);
		DB::update('channel', $set_ary, array('ch_id' => $ch_id));
	}
	
	
	function delete_category($ch_id)
	{
				$category = $this->id2category($ch_id);
		if (empty($category)) {
			return -1;
		}
		
		if ($category['topic_num'] > 0) {
			return -2;
		}
		
				$sub_count = DB::result_first("SELECT COUNT(*) 
									   FROM ".DB::table('channel')." 
									   WHERE parent_id='{$ch_id}'");
		if ($sub_count) {
			return -3;
		}
		
		DB::query("DELETE FROM ".DB::table('channel')." WHERE ch_id='{$ch_id}'");
		DB::query("DELETE FROM ".DB::table('buddy_channel')." WHERE ch_id='{$ch_id}'");
		return 1;
	}
	
	
	function update_category_cache()
	{
		$cat_ary = array(); $channles = array();
		$query = DB::query("SELECT * FROM ".DB::table('channel')." ORDER BY display_order ASC");
		while ($value = DB::fetch($query)) {
			$channles[$value['ch_id']][$value['ch_id']] = $value['ch_id'];
			if ($value['parent_id'] == 0) {
				$cat_ary['first'][$value['ch_id']] = $value;	
			} else {
				$cat_ary['second'][$value['ch_id']] = $value;
				$channles[$value['parent_id']][$value['ch_id']] = $value['ch_id'];
			}
		}
		ConfigHandler::set('channel', $cat_ary);
		ConfigHandler::set('channels', $channles);
	}
	
	
	function get_category()
	{
		$cat_ary = array();
		$cat_ary = ConfigHandler::get('channel');
		if (empty($cat_ary)) {
			$cat_ary = $this->update_category_cache();
		}
		return $cat_ary;
	}

	
	function get_catselect($parent_id = 0, $sub_id = 0, $in_ajax = false)
	{
		$cat_ary = array();
		$cat_ary = $this->get_category();
		
		if (empty($cat_ary)) {
			return false;
		}
		
		$first_cat = $cat_ary['first'];
		$second_cat = $cat_ary['second'];
		$r = array();
		if ($parent_id == 0 && $sub_id == 0) {
			$tmp = current($first_cat);
			$parent_id = $tmp['ch_id'];
			unset($tmp);
		} else if ($parent_id == 0 && $sub_id != 0) {
			$parent_id = $second_cat[$sub_id]['parent_id'];
		}
		
		if ($in_ajax == false) {
						foreach ($first_cat as $value) {
				if ($value['parent_id'] == 0) {
					$ps = '';
					if ($value['ch_id'] == $parent_id) {
						$ps = 'selected="selected"';
					}
					$r['first'] .= "<option value='{$value['ch_id']}' {$ps} >{$value['ch_name']}</option>";
				}
			}
		}
		
				$r['second'] = "<option value=''>请选择...</option>";
		foreach ($second_cat as $value) {
			if ($parent_id == $value['parent_id']) {
				$ss = '';
				if ($value['ch_id'] == $sub_id) {
					$ss = 'selected="selected"';
				}
				$r['second'] .= "<option value='{$value['ch_id']}' {$ss} >{$value['ch_name']}</option>";
			}
		}
		return $r;
	}
}
?>