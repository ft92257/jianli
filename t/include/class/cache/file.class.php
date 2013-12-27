<?php
/**
 *
 * 文件缓存相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id$
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class cache_file {
	
	var $io = null;
	var $path = '';
	var $prefix;
	var $memory;
	
	function cache_file() {
		global $_J;
		
		$this->io = Load::lib('io', 1);
		$this->path = (defined('TEMPLATE_ROOT_PATH') ? TEMPLATE_ROOT_PATH : ROOT_PATH) . 'data/cache/cache_file/';
		$this->prefix = 'cache_file_' . substr(md5($this->path), 0, 6) . '_';
		
		if($_J['config']['memory_enable'] && $_J['config']['cache_file_to_memory']) {
			$this->memory = Load::model('memory');
		}
	}
	
	function get($key) {
		static $datas = null;
		if(!isset($datas[$key])) {
			if($this->memory) {
				$cache = $this->memory->get($key, $this->prefix);
			} else {
				@include($this->_file($key));
			}
			if(!$cache) {
				return false;
			}
			$datas[$key] = $cache['val'];
			if($datas[$key]['life']>0 && ($cache['dateline'] + $datas[$key]['life'] < TIMESTAMP)) {
				$datas[$key]['data'] = false;
			}
		}
		return $datas[$key]['data'];
	}
	
	function set($key, $val, $life=0) {
		$life = max(0, (int) $life);
		if($life < 1 || $life > 2592000) {
			$life = 2592000;
		}
		$datas = array(
			'key' => $key,
			'dateline' => TIMESTAMP,
			'val' => array('life'=>$life, 'data'=>$val, ),
		);
		if($this->memory) {
			$ret = $this->memory->set($key, $datas, $life, $this->prefix);
		} else {
			$data = "<?php if(!defined('IN_JISHIGOU')) { exit('invalid request'); } \r\n\$cache = " . var_export($datas, true) . ";\r\n?>";
			$file = $this->_file($key);
			if(!is_dir(($dir = dirname($file)))) {
				$this->io->MakeDir($dir);
			}
			$ret = $this->io->WriteFile($file, $data);
			if(false === $ret) {
				exit("缓存文件 $file 写入失败，请检查相应目录的可写权限。");
			}
			@chmod($file, 0777);
		}

		return $ret;
	}
	
	function del($key, $more=0) {
		if($this->memory) {
			$this->memory->del($key, $this->prefix);
		} else {
			if($more && is_dir(($dir = $this->path . $key))) {
				$ret = $this->io->ClearDir($dir);
			} else {
				$ret = $this->io->DeleteFile($this->_file($key));
			}
		}
		
		return $ret;
	}
	function rm($key, $more=0) {
		return $this->del($key, $more);
	}
	
	function clean() {
		if($this->memory) {
			return $this->memory->clear();
		} else {
			return $this->io->ClearDir($this->path);
		}
	}
	function clear() {
		return $this->clean();
	}
	
	function _file($key) {
		return $this->path . $key . '.cache.php';
	}
}

?>