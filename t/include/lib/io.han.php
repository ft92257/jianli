<?php
/**
 *
 * 文件目录相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: io.han.php 954 2012-05-24 09:47:12Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class IoHandler {

	function IoHandler() {
		;
	}

	
	function SizeConvert($filesize) {
		if ($filesize >= 1073741824) {
			$filesize = round($filesize / 1073741824 , 2) . "G";
		} elseif ($filesize >= 1048576) {
			$filesize = round($filesize / 1048576, 2) . "M";
		} elseif ($filesize >= 1024) {
			$filesize = round($filesize / 1024, 2) . "k";
		} else {
			$filesize = $filesize . "b";
		}
		return $filesize;
	}
	
	function ReadDir($dir, $children = 0) {
		if (!is_dir($dir) || !($dp = @opendir($dir))) {
			trigger_error("目录 {$dir} 不存在或者没有相应权限，请检查<br>", E_USER_NOTICE);
			return false;
		}
		while (false !== ($file = readdir($dp))) {
			if ($file != '.' and $file != '..') {
				$abspath = $dir . '/' . $file;
				if (is_file($abspath) !== false) {
					$files[] = $abspath ;
				}
				if(is_dir($abspath) !== false) {
					if ($children == '1') {
						$files = array_merge((array) $files, (array) IoHandler::ReadDir($abspath, $children));
					}
				}
			}
		}
		closedir($dp);
		return (array) $files;
	}
	
	function ReadFile($filename, $length=0) {
		if(false != ($fp = @fopen($filename, 'rb'))) {
			$length = max(0, (int) $length);
			if($length < 1) {
				$length = filesize($filename);
			}
			$contents = @fread($fp, $length);
			fclose($fp);
			return $contents;
		}
		return false;
	}
	
	function WriteFile($filename, $file_contents, $mode = 'wb', $length=null) {
		if(false != ($fp = @fopen($filename, $mode))) {
			flock($fp, LOCK_EX);
			if(isset($length)) {
				$len = @fwrite($fp, $file_contents, $length);
			} else {
				$len = @fwrite($fp, $file_contents);
			}
			flock($fp, LOCK_UN);
			fclose($fp);
			return $len;
		}
		return false;
	}

	
	function CopyFile($from, $to) {
		$copy_count = 0;
		if (is_string($from)) {
			if (copy($from, $to . '/' . IoHandler::BaseName($from))) {
				$copy_count = 1;
				return $copy_count;
			}
		} else {
			if (is_array($from)) {
				if (is_dir($to) == false) {
					if (IoHandler::MakeDir($to) == false) {
						return $copy_count;
					}
				}
				foreach($from as $filename) {
					if (copy($filename, $to . '/' . IoHandler::BaseName($filename))) {
						$copy_count++;
					}
				}
			}
		}
		return $copy_count;
	}

	
	function DeleteFile($file) {
		if('' == trim($file)) return ;

		$delete = @unlink($file);

				clearstatcache();
		@$filesys = preg_replace("~\/+~","\\", $file);
		if(is_file($filesys) and file_exists($filesys)) {
			$delete = @system("del $filesys");
			clearstatcache();
			if(file_exists($file)) {
				$delete = @chmod($file, 0777);
				$delete = @unlink($file);
				$delete = @system("del $filesys");
			}
		}
		clearstatcache();

		return file_exists($file);
	}

	
	function MakeDir($dirname, $mode = 0777) {
		
		return jmkdir($dirname, $mode, 0);
	}

	
	function ClearDir($dirname) {
		return IoHandler::RemoveDir($dirname, 0);
	}

	
	function RemoveDir($dirname, $rm_self=1) {
		clearstatcache();
		if(is_dir($dirname) && ($dp = @opendir($dirname))) {
			while(($file = readdir($dp)) !== false) {
				if($file != '.' and $file != "..") {
					clearstatcache();
					if(is_dir($dirname . '/' . $file)) {
						IoHandler::RemoveDir($dirname . '/' . $file);
					}
					if(is_file($dirname . '/' . $file)) {
						IoHandler::DeleteFile($dirname . '/' . $file);
					}
				}
			}
			closedir($dp);
			if($rm_self) {
				rmdir($dirname);
			}
		}
		return true;
	}

	
	function FileExt($filename, $no_point=1) {
		return addslashes(strtolower(substr(strrchr($filename, '.'), $no_point, 10)));
	}
	
	function BaseName($path, $suffix = false) {
		$name = trim($path);
		$name = str_replace('\\', '/', $name);
		if(strpos($name, '/') !== false) {
			$name = substr(strrchr($path, '/'), 1);
		} else {
			$name = ltrim($path, '.');
		}
		if($suffix) {
			$suffix = strrchr($name, '.');
			$name = str_replace($suffix, '', $name);
		}
		return $name;
	}
	
	function initPath($path) {
		$ret = $path;
		$path = (substr($path, -1) == '/') ? $path : dirname($path);
		if ( !is_dir($path) ) {
			IoHandler::MakeDir($path);
		}
		return $ret;
	}
}

?>