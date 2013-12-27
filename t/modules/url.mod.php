<?php

/**
 * 外链跳转模块
 *
 * @author 狐狸<foxis@qq.com>
 * @package jishigou.net
 */
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $UrlLogic;

	function ModuleObject($config)
	{
		$this->MasterObject($config);

		Load::logic('url');
		$this->UrlLogic = new UrlLogic();

		$this->Execute();
	}


	
	function Execute()
	{
		$load_file = array();
		switch ($this->Code)
        {
			default:
				$this->Main();
				break;
		}
	}

	function Main()
	{
		if (!$this->Code)
		{
			$this->Messager("错误的请求",null);
		}

		$url_info = $this->UrlLogic->get_info_by_key($this->Code);

		if (!$url_info)
		{
			$this->Messager("[错误请求]不存在的链接地址",null);
		}

		$this->UrlLogic->set_open_times($url_info['id'], '+1');

				$this->Messager(null,$url_info['url']);
	}

}
?>
