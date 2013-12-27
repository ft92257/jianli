<?php
/**
 *
 * 微博URL相关的数据库操作
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: url.logic.php 161 2012-02-22 03:08:39Z wuliyong $
 */
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class UrlLogic
{
	
	var $table;

	var $_cache;


	function UrlLogic()
	{
		$this->table = 'url';
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
		if(isset($p['key']))
		{
			$wheres[] = " `key`='{$p['key']}' ";
		}
		if(isset($p['url']))
		{
			$p['url_hash'] = $this->_hash($p['url']);
		}
		if(isset($p['url_hash']))
		{
			$wheres[] = " `url_hash`='{$p['url_hash']}' ";
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

		$iss = array('id' => 1, 'key' => 1, 'url' => 1, 'url_hash' => 1, );
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
	function get_info_by_key($key)
	{
		return $this->get_info($key, 'key');
	}
	
	function get_info_by_url($url, $add_or_update=1, $title=null, $description=null)
	{
		$url = $this->_url($url);
		if(!$url)
		{
			return 0;
		}

		$url_info = $this->get_info($url, 'url');
		if($add_or_update)
		{
			$re_get = 0;
			if(!$url_info)
			{
				$re_get = $this->add($url, $title, $description);
			}
			else
			{
				$p = array();
				if(isset($title) && $title != $url_info['title'])
				{
					$p['title'] = $title;
				}
				if(isset($description) && $description != $url_info['description'])
				{
					$p['description'] = $description;
				}
				if($p)
				{
					$p['id'] = $url_info['id'];

					$re_get = $this->modify($p);
				}
			}

			if($re_get)
			{
				$url_info = $this->get_info_by_url($url, 0);
			}
		}

		return $url_info;
	}
	function info($url, $title=null, $description=null)
	{
		return $this->get_info_by_url($url, 1, $title, $description);
	}
	function get_info_by_url_hash($url_hash)
	{
		return $this->get_info($url_hash, 'url_hash');
	}

	
	function add($url, $title='', $description='')
	{
		$url = $this->_url($url);
		if(!$url)
		{
			return 0;
		}

		$url_hash = $this->_hash($url);

		$url_info = array(
			'url' => $url,
			'url_hash' => $url_hash,
			'title' => $title,
			'description' => $description,
			'dateline' => TIMESTAMP,
		);

		$ret = 0;

		$id = DB::insert($this->table, $url_info, 1, 1, 1);
		if($id > 0)
		{
			$ret = $this->set_key($id);

			if($ret)
			{
				$site_info = get_site_info($url);

				$this->set_site_id($id, $site_info['id']);
			}
		}

		if($id < 1 || !$ret)
		{
			$this->clear_invalid();
		}

		return $id;
	}

	
	function modify($p)
	{
		$id = (is_numeric($p['id']) ? $p['id'] : 0);
		if($id < 1) return 0;

		$info = $this->get_info($id);
		if(!$info) return 0;

		$sets = array();

		$_int_fields = array('dateline', 'open_times',);
		foreach($_int_fields as $_field)
		{
			if(isset($p[$_field]))
			{
				$sets[$_field] = (int) $p[$_field];
			}
		}

		$_str_fields = array('title', 'description', );
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

	
	function set_views($ids, $views)
	{
		return $this->set_open_times($ids, $views);
	}
	function set_open_times($ids, $open_times)
	{
		$ids = $this->_ids($ids);
		if(!$ids) return 0;

		$open_times = is_numeric($open_times) ? $open_times : 0;
		$sign = substr((string) $open_times, 0, 1);
		$open_times_set = " `open_times`=" . (('-' == $sign || '+' == $sign) ? "`open_times`+{$open_times}" : "$open_times") . " ";

		$ret = DB::query("update ".DB::table($this->table)." set $open_times_set where `id` in ($ids)");

		return $ret;
	}

	function set_key($id, $key='')
	{
		$id = is_numeric($id) ? $id : 0;
		if($id < 1) return 0;

		$key = $key ? $key : $this->_key($id);

		$ret = DB::query("update ".DB::table($this->table)." set `key`='$key' where `id`='$id'");

		return $ret;
	}

	function set_site_id($id, $site_id=0)
	{
		$id = is_numeric($id) ? $id : 0;
		if($id < 1) return 0;

		$site_id = is_numeric($site_id) ? $site_id : 0;

		$ret = DB::query("update ".DB::table($this->table)." set `site_id`='$site_id' where `id`='$id'");

		return $ret;
	}

	function clear_invalid()
	{
		$ret = DB::query("delete from ".DB::table($this->table)." where `key`=''");

		return $ret;
	}

	function get_url($url, $strip_fragment = 0)
	{
		return $this->_url($url, $strip_fragment);
	}

	function _url($url, $strip_fragment = 1)
	{
		$url = trim(strip_tags($url));

		if($strip_fragment)
		{
			$strpos1 = strpos($url, '#');
			if(false !== $strpos1)
			{
				$url = substr($url, 0, $strpos1);
			}
		}

		if(false === strpos($url, ':/'.'/'))
		{
			if(0 === strpos(strtolower($url), 'www.'))
			{
				$url = 'http:/'.'/' . $url;
			}
			else
			{
				return '';
			}
		}

		if (false == preg_match('~^(?:https?\:\/\/|www\.)(?:[A-Za-z0-9\_\-]+\.)+[A-Za-z0-9]{1,4}(?:\:\d{1,6})?(?:\/[\w\d\/=\?%\-\&_\~\`\:\+\#\.]*(?:[^\;\@\[\]\<\>\'\"\n\r\t\s\x7f-\xff])*)?$~i',
                $url))
                {
                	return '';
                }

		return $url;
	}

	function _key($id,$op="ENCODE")
	{
		$index = 'z6OmlGsC9xqLPpN7iw8UDAb4HIBXfgEjJnrKZSeuV2Rt3yFcMWhakQT1oY5v0d';
		$base = 62;

		$out = "";
		if('ENCODE' == $op) {
		   for ( $t = floor( log10( $id ) / log10( $base ) ); $t >= 0; $t-- ) {
		       $a = floor( $id / pow( $base, $t ) );
		       $out = $out . substr( $index, $a, 1 );
		       $id = $id - ( $a * pow( $base, $t ) );
		   }
		} elseif ('DECODE' == $op) {
			;
		}

	   return $out;
	}

	function _hash($url)
	{
		$url_hash = md5($url);

		return $url_hash;
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