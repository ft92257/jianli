<?php
/**
 * 文件名：renren_env.func.php
 * 版本号：1.0
 * 最后修改时间：2011年9月14日
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 人人接口函数
 */
if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}



function renren_env()
{
	$msgs = array();

	
	$files = array(ROOT_PATH . 'include/lib/oauth2.han.php', ROOT_PATH . 'include/function/renren.func.php', ROOT_PATH . 'modules/renren.mod.php', );
	foreach ($files as $f)
	{
		if (!is_file($f))
		{
			$msgs[] = "文件<b>{$f}</b>不存在";
		}
	}

	
	$funcs = array('version_compare',array('fsockopen', 'pfsockopen'),'curl_init','preg_replace',array('iconv','mb_convert_encoding'),array("hash_hmac","mhash"));
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