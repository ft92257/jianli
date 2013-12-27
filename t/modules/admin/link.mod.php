<?php
/**
 *
 * 友情链接管理模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: link.mod.php 947 2012-05-21 09:58:43Z wuliyong $
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{	
	var $ID = 0;

	
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		$this->ID = $this->Get['id']?(int)$this->Get['id']:(int)$this->Post['id'];
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'modify':
				$this->Main();
				break;
			case 'domodify':
				$this->DoModify();
				break;
			default:
				$this->Code = 'link_setting';
				$this->Main();
				break;
		}
		$body = ob_get_clean();
		
		$this->ShowBody($body);
		
	}
	
	
	function Main()
	{
		$this->Modify();
	}
	
	function Modify()
	{
				$config = array();
		include(ROOT_PATH . 'setting/link.php');
		if($config['link_list'] && !$config['link']) {
			ConfigHandler::set('link', $config['link_list']);
		}
	
		$current_domain=preg_replace("~^www\.~i","",$_SERVER['HTTP_HOST']);
		$link_list = ConfigHandler::get('link');
		
		include($this->TemplateHandler->Template('admin/link'));
	}
	
	function DoModify()
	{
		$link=$this->Post['link'];
		if($link['new']['name']!="" && $link['new']['url']!="" )
		{
			$new_link=$link['new'];
			
			$link[]=$new_link;
		}
		unset($link['new']);
		if($this->Post['delete'])
		{
			foreach ($this->Post['delete'] as $link_id)
			{
				unset($link[$link_id]);
			}
		}
		
				$n=100;
		$i=0;
		$link_list=array();
		foreach (@$link as $l)
		{			
			if(!empty($l['logo']))
			{
				$key = $i++;
			}
			else 
			{
				$key = $n++;
			}
			
			$l['order'] = (int) ($l['order'] ? $l['order'] : $key);
			
			$link_list[$key]=$l;
		}
				if($link_list) {
			foreach ($link_list as $k=>$n)
			{
				$order[$k]=$n['order'];
			}
			@array_multisort($order,SORT_ASC,$link_list);
		}
		
		$link_list = jaddslashes($link_list);
		ConfigHandler::set('link', $link_list);
		
		$this->Messager("修改成功");
	}
}
?>
