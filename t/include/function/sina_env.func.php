<?php
/**
 * 文件名：sina_env.func.php
 * 版本号：1.0
 * 最后修改时间：2010年12月6日 17:15:24
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 新浪微博接口函数
 */
if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}



function sina_env()
{
	$msgs = array();

	
	$files = array(ROOT_PATH . 'include/xwb/sina.php',ROOT_PATH . 'include/xwb/jishigou.php',ROOT_PATH . 'include/xwb/set.data.php',ROOT_PATH . 'include/xwb/hack/newtopic.hack.php',ROOT_PATH . 'include/xwb/lib/xwbDB.class.php',);
	foreach ($files as $f)
	{
		if (!is_file($f))
		{
			$msgs[] = "文件<b>{$f}</b>不存在";
		}
	}

	
	$funcs = array('version_compare', array('fsockopen', 'pfsockopen'), 'preg_replace',array('iconv','mb_convert_encoding'),array("hash_hmac","mhash"));
	foreach ($funcs as $func)
	{
		if (!is_array($func))
		{
			if (!function_exists($func))
			{
				$msgs[] = "函数<b>{$func}</b>不可用";
			}
		}
		else
		{
			$t = false;
			foreach ($func as $f)
			{
				if(function_exists($f))
				{
					$t = true;
					break;
				}
			}
				
			if (!$t)
			{
				$msgs[] = "函数<b>".implode(" , ",$func)."</b>都不可用";
			}
		}
	}


	return $msgs;
}

?>