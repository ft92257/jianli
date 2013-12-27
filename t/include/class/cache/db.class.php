<?php
/**
 *
 * 数据库缓存相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 * <code>
 * //数据库缓存类 使用样例
 * 
 * $cache_key = 'this-is-cache-key';
 * //读取缓存
 * if(false===($cache_data=Load::model('cache/db')->get($cache_key))) {
 * 		//重新生成数据（比如从数据库中读取大量的数据）
 * 		$cache_data = 'this is cache data, this time is ' . date('Y-m-d H:i:s');
 * 		
 * 		$cache_time = 60; //缓存时间，单位为秒
 * 		//写入缓存
 * 		Load::model('cache/db')->set($cache_key, $cache_data, $cache_time);
 * }
 * print_r($cache_data);
 * </code>
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id$
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

/**
 * 
 * 数据库缓存相关操作类
 * @author 狐狸<foxis@qq.com>
 *
 */
class cache_db {

	
	var $num = 16;

	
	var $table = 'cache';
	
	var $memory;
	var $prefix;


	
	function cache_db() {
		global $_J;
		
		$this->num = max(0, (int) $_J['config']['cache_table_num']);
		$this->prefix = 'cache_db_';
		
		if($_J['config']['memory_enable'] && $_J['config']['cache_db_to_memory']) {
			$this->memory = Load::model('memory');
		}
	}

	
	function get($key, $memory=0) {
		static $datas = null;
		if(!isset($datas[$key])) {
			if($memory && $this->memory && (0 == (int) $key)) {
				$cache = $this->memory->get($key, $this->prefix);
				if(!$cache) {
					return false;
				}
				$datas[$key] = $cache['val'];
			} else {
				$cache = DB::fetch_first("SELECT * FROM ".DB::table($this->_get_table($key))." WHERE `key`='$key'");
				if($cache) {					
					$datas[$key] = unserialize(base64_decode($cache['val']));
				} else {
					return false;
				}
			}
			if($datas[$key]['life']>0 && ($cache['dateline'] + $datas[$key]['life'] < TIMESTAMP)) {
				$datas[$key]['data'] = false;
			}
		}
		return $datas[$key]['data'];
	}

	
	function set($key, $val, $life=0, $memory=0) {
		$datas = array(
			'key' => $key,
			'dateline' => TIMESTAMP,
		);
		$data = array('data'=>$val, 'life'=>max(0, (int) $life));
		if($memory && $this->memory && (0 == (int) $key)) {
			$datas['val'] = $data;
			$ret = $this->memory->set($key, $datas, $life, $this->prefix);
		} else {
			$datas['val'] = base64_encode(serialize($data));
			$ret = DB::insert($this->_get_table($key), $datas, 0, 1, 1);
		}
		return $ret;
	}

	
	function del($key, $more=0, $memory=0) {
		if($memory && $this->memory && (0 == (int) $key)) {
			$this->memory->del($key, $this->prefix);
		} else {
			if($more) {
				$key = (false === strpos($key, '%') ? "{$key}%" : $key);
				$ret = DB::query("DELETE FROM ".DB::table($this->_get_table($key))." WHERE `key` LIKE '{$key}'");
			} else {
				$ret = DB::query("DELETE FROM ".DB::table($this->_get_table($key))." WHERE `key`='{$key}'");
			}
		}
		return $ret;
	}
	function rm($key, $more=0, $memory=0) {
		return $this->del($key, $more, $memory);
	}

	
	function clean() {
		DB::query("TRUNCATE TABLE ".DB::table($this->table));
		if($this->num) {
			for ($i=1; $i<$this->num; $i++) {
				DB::query("TRUNCATE TABLE ".DB::table($this->_sub_table($i)));
			}
		}
		if($this->memory) {
			$this->memory->clear();
		}
	}
	function clear() {
		return $this->clean();
	}

	
	function _get_table($key) {
		if($this->num) {
			$pos = strpos($key, '-');
			if(!$pos) {
				$pos = 4;
			}
			$num = (abs(crc32(substr((string) $key, 0, $pos))) % $this->num);
				
			return $this->_sub_table($num);
		} else {
			return $this->table;
		}
	}
	
	function _sub_table($num) {
		if($num) {
			$table = "{$this->table}_{$num}";
		} else {
			$table = $this->table;
		}
		return $table;
	}
}

?>