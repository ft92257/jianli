<?php
/**
 *
 * 微博SITE相关的数据库操作
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: site.logic.php 161 2012-02-22 03:08:39Z wuliyong $
 */
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class SiteLogic
{
	
	var $table;

	var $_cache;


	function SiteLogic()
	{
		$this->table = 'site';
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
			$p['ids'] = $this->_ids($p['ids'], 0);
			if($p['ids']) $wheres[] = " `id` in ({$p['ids']}) ";
		}
		if(isset($p['host']))
		{
			$p['host'] = $this->_host($p['host']);
			if($p['host']) $wheres[] = " `host`='{$p['host']}' ";
		}
		if(isset($p['dateline_min']))
		{
			$p['dateline_min'] = max(0, (int) $p['dateline_min']);
			$wheres[] = " `dateline`>='{$p['dateline_min']}' ";
		}
		if(isset($p['dateline_max']))
		{
			$p['dateline_max'] = max(0, (int) $p['dateline_max']);
			$wheres[] = " `dateline`<='{$p['dateline_max']}' ";
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
				$page = page($count, $p['per_page_num'], $p['page_site'], array('return' => 'Array'));

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
				$list[] = $r;
			}

			if($list)
			{
				return array('count'=>$count, 'list'=>$list, 'page'=>$page);
			}
		}

		return array();
	}

	
	function get_info($id, $is = 'id')
	{
		$id = trim($id);
		if(!$id) return array();

		$iss = array('id' => 1, 'host' => 1, );
		if(!isset($iss[$is]))
		{
			return array();
		}

		$p = array(
			'count' => 1,
		);

		if('id' == $is)
		{
			$id = max(0, (int) $id);
			if($id < 1) return array();
		}

		$_cache_id = "info_{$is}_" . md5($id);
		if(null === ($ret = $this->_cache[$_cache_id]))
		{
			$p[$is] = $id;

			$rets = $this->get($p);

			$ret = $rets['list'][0];

			$this->_cache[$_cache_id] = $ret;
		}

		return $ret;
	}
	function get_info_by_id($id)
	{
		return $this->get_info($id, 'id');
	}
	
	function get_info_by_host($host, $add_or_update=1, $name=null, $description=null)
	{
		$host = $this->_host($host);
		if(!$host)
		{
			return 0;
		}

		$site_info = $this->get_info($host, 'host');
		if($add_or_update)
		{
			$re_get = 0;
			if(!$site_info)
			{
				$re_get = $this->add($host, $name, $description);
			}
			else
			{
				$p = array();
				if(isset($name) && $name != $site_info['name'])
				{
					$p['name'] = $name;
				}
				if(isset($description) && $description != $site_info['description'])
				{
					$p['description'] = $description;
				}
				if($p)
				{
					$p['id'] = $site_info['id'];

					$re_get = $this->modify($p);
				}
			}

			if($re_get)
			{
				$site_info = $this->get_info_by_host($host, 0);
			}
		}

		return $site_info;
	}
	
	function info($host, $name=null, $description=null)
	{
		return $this->get_info_by_host($host, 1, $name, $description);
	}

	
	function add($host, $name='', $description='')
	{
		$host = $this->_host($host);
		if(!$host)
		{
			return 0;
		}

		$site_info = array(
			'host' => $host,
			'name' => $name,
			'description' => $description,
			'dateline' => TIMESTAMP,
		);

		$id = DB::insert($this->table, $site_info, 1, 1, 1);

		return $id;
	}

	
	function modify($p)
	{
		$id = (is_numeric($p['id']) ? $p['id'] : 0);
		if($id < 1) return 0;

		$info = $this->get_info($id);
		if(!$info) return 0;

		$sets = array();

		$_int_fields = array('dateline', 'url_count',);
		foreach($_int_fields as $_field)
		{
			if(isset($p[$_field]))
			{
				$sets[$_field] = (int) $p[$_field];
			}
		}

		$_str_fields = array('name', 'description', );
		foreach($_str_fields as $_field)
		{
			if(isset($p[$_field]))
			{
				$sets[$_field] = trim(strip_tags($p[$_field]));
			}
		}

		$ret = 0;

		if($sets)
		{
			$ret = DB::update($this->table, $sets, array('id' => $id));
		}

		return $ret;
	}

	
	function set_url_count($ids, $url_count)
	{
		$ids = $this->_ids($ids);
		if(!$ids) return 0;

		$url_count = is_numeric($url_count) ? $url_count : 0;
		$sign = substr((string) $url_count, 0, 1);
		$url_count_set = " `url_count`=" . (('-' == $sign || '+' == $sign) ? "`url_count`+{$url_count}" : "$url_count") . " ";

		$ret = DB::query("update ".DB::table($this->table)." set $url_count_set where `id` in ($ids)");

		return $ret;
	}

	function _site($site, $strip_fragment = 1)
	{
		$site = trim(strip_tags($site));

		if($strip_fragment)
		{
			$strpos1 = strpos($site, '#');
			if(false !== $strpos1)
			{
				$site = substr($site, 0, $strpos1);
			}
		}

		$strpos2 = strpos($site, ':/'.'/');
		if(false === $strpos2)
		{
			return '';
		}

		return $site;
	}

	function _host($host)
	{
		if(false !== strpos($host, ':/'.'/'))
		{
			$urls = parse_url($host);

			$host = $urls['host'];
		}

		$host = strtolower(trim(strip_tags($host)));

		return $host;
	}

	function _ids($ids, $ret_arr = 0)
	{
		$ids = (array) $ids;

		$_ids = array();
		foreach($ids as $id)
		{
			if(false !== strpos($id, ','))
			{
				$_id_arr = explode(',', $id);
				foreach($_id_arr as $_id)
				{
					$_id = trim($_id, ' "\'');
					$_ids[] = $_id;
				}
			}
			else
			{
				$_ids[] = $id;
			}
		}

		$_ids = array_unique($_ids);

		if($ret_arr)
		{
			return $_ids;
		}
		else
		{
			$ret = "'".implode("','", $_ids)."'";

			return $ret;
		}
	}
}

?>