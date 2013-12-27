<?php
/**
 *
 * 话题扩展逻辑操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: tag_extra.logic.php 597 2012-04-05 08:59:10Z wuliyong $
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

/**
 * 
 * 话题扩展的数据库逻辑操作类
 * 
 * @author 狐狸<foxis@qq.com>
 *
 */
class TagExtraLogic
{
	
	var $table;
	
	function TagExtraLogic()
	{
		$this->table = 'tag_extra';	
	}
	
	
	function get($p)
	{
		$wheres = array();		
		
		if(isset($p['id']))
		{
			$p['id'] = max(0, (int) $p['id']);
			if($p['id'] > 0) $wheres[] = " `id`='{$p['id']}' ";
		}
		if(isset($p['ids']))
		{
			$p['ids'] = $this->get_ids($p['ids'], 0);
			if($p['ids']) $wheres[] = " `id` in ({$p['ids']}) ";
		}
		
		$sql_where = ($wheres ? " where " . implode(" and ", $wheres) : "");
		
		$count = max(0, (int) $p['count']);
		if($count < 1)
		{
			$count = DB::result_first("select count(*) as `count` from ".DB::table($this->table)." $sql_where ");
		}
		$list = array();
		$page = array();		
		if($count > 0)
		{
			$sql_limit = '';
			if($p['per_page_num'])
			{
				$page = page($count, $p['per_page_num'], $p['page_url'], array('return' => 'Array'));
				
				$sql_limit = " {$page['limit']} ";
			}
			elseif($p['limit']) 
			{
				if(false !== strpos(strtolower($p['limit']), 'limit '))
				{
					$sql_limit = " {$p['limit']} ";
				}
				else 
				{
					$sql_limit = " limit {$p['limit']} ";
				}
			}
			
			$sql_order = '';
			if($p['order'])
			{
				if(false !== strpos(strtolower($p['order']), 'order by '))
				{
					$sql_order = " {$p['order']} ";
				}
				else 
				{
					$sql_order = " order by {$p['order']} ";
				}
			}
			
			$sql_fields = ($p['fields'] ? $p['fields'] : "*");
			
			$query = DB::query("select $sql_fields from ".DB::table($this->table)." $sql_where $sql_order $sql_limit ");
			while(false != ($r = DB::fetch($query)))
			{
				if(isset($r['data']))
				{
					$r['data'] = $this->_data_decode($r['data']);
				}
				
				$list[] = $r;
			}
			
			if($list)
			{
				return array('count'=>$count, 'list'=>$list, 'page'=>$page);
			}
		}
		
		return array();
	}
		
	
	function get_info($id)
	{
		$id = max(0, (int) $id);
		if($id < 1) return array();

		$p = array(
			'id' => $id,
			'count' => 1,
		);
		$rets = $this->get($p);
		
		$ret = $rets['list'][0];
		
		return $ret;
	}
	
	
	function add($id, $name, $data = array())
	{
		$id = is_numeric($id) ? $id : 0;
		if($id < 1) return 0;
				
		$arr = array(
			'id' => $id,
			'name' => $name,
			'data' => $this->_data_encode($data),
		);
		$ret = DB::insert($this->table, $arr);
		
		$this->set_tag_extra($id, 1);
		
		return $ret;
	}
	
	
	function modify($id, $data)
	{		
		$id = is_numeric($id) ? $id : 0;
		if($id < 1) return 0;
		
		$info = $this->get_info($id);
		if(!$info) return 0;
		
		$sets = array(
			'data' => $this->_data_encode($data)
		);
		$ret = DB::update($this->table, $sets, array('id' => $id));
		
		return $ret;
	}
	
	
	function delete($ids)
	{
		$p = array('ids' => $ids);
		$rets = $this->get($p);
		if(!$rets) return 0;
		
		
		$ret = 1;
		foreach($rets['list'] as $r)
		{
			$id = $r['id'];
			
			$ret = $ret && DB::query("delete from ".DB::table($this->table)." where `id`='$id'");
			
			$this->set_tag_extra($id, 0);
		}
		
		return $ret;
	}
	
	function set_tag_extra($id, $extra)
	{
		$id = is_numeric($id) ? $id : 0;
		if($id < 1) return 0;
		
		$extra = $extra ? 1 : 0;
		
		$ret = DB::query("update ".DB::table('tag')." set `extra`='$extra' where `id`='$id'");
		
		return $ret;
	}
	
	function _data_encode($data)
	{
		$ret = '';
		
		$ret = base64_encode(serialize($data));
		
		return $ret;
	}
	
	function _data_decode($data)
	{
		$ret = array();
		
		$ret = unserialize(base64_decode($data));
		
		return $ret;
	}

	
	function get_ids($ids, $check_uid = -1, $ret_arr = 0)
    {    	
    	$_ids = array();
    	if(is_numeric($ids))
    	{
    		$_ids[$ids] = $ids;
    	}
    	elseif(is_string($ids))
    	{
    		$_rs = explode(',', $ids);
            foreach($_rs as $_r)
            {
                $_ids[$_r] = $_r;
            }
    	}
        else
        {
            if($ids)
            {
                $_ids = (array) $ids;
            }
        }
    	
        $ids = array();
        if($_ids)
        {
            foreach($_ids as $_r)
            {
            	$_r = trim($_r , ' ,"\'');
                $_r = is_numeric($_r) ? $_r : 0;
                if($_r > 0)
                {
                    $ids[$_r] = $_r;
                }
            }
        }
        
        if($ids && $check_uid)
        {
        	if($check_uid > 0)
        	{
        		$check_uid_sql = " and `uid`='$check_uid'";
        	}
        	
            $query = DB::query("select `id` from ".DB::table('tag')." where `id` in ('".implode("','", $ids)."') $check_uid_sql ");
            $rets = array();
            while(false != ($rs = DB::fetch($query)))
            {
                $rets[$rs['id']] = $rs['id'];
            }
            
            $ids = $rets;
        }
        
    	if($ret_arr)
        {
        	return $ids;
        }
        else
        {
            return implode(",", $ids);
        }
    }
}

?>