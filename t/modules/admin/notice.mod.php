<?php
/**
 *
 * 网站公告模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: notice.mod.php 1104 2012-07-03 02:42:22Z wuliyong $
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class ModuleObject extends MasterObject
{

	function ModuleObject($config)
	{
		$this->MasterObject($config);

		
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'delete':
				$this->Delete();
				break;
			case 'add':
				$this->Add();
				break;
			case 'modify':
				$this->Modify();
				break;
			case 'domodify':
				$this->DoModify();
				break;
			default:
				$this->Code = 'notice_setting';
				$this->Main();
				break;
		}
		$body = ob_get_clean();

		$this->ShowBody($body);

	}

	function Main()
	{
		$ButtonTitle = '添加';
		$sql = "select `id`,`title`,`dateline` from `".TABLE_PREFIX."notice` order by `id` desc";
		$query = $this->DatabaseHandler->Query($sql);

		$notice_list=array();
		while($row=$query->GetRow())
		{
			$row['dateline'] = date('Y-m-d H:s:i',$row['dateline']);
			$notice_list[] = $row;
		}

		include $this->TemplateHandler->Template('admin/notice');
	}



	function Add()
	{
		$title   = $this->Post['title'];
		$content = $this->Post['content'];
		$timestamp = time();


		if(empty($title))
		{
			$this->Messager("请输入公告标题",-1);
		}

		if(empty($content))
		{
			$this->Messager("请输入公告内容",-1);
		}

		$content = unfilterHtmlChars($content);


				$f_rets = filter($content);
		if($f_rets && $f_rets['error'])
		{
			$this->Messager($f_rets['msg'],-1);
		}


		$sql = "insert into `".TABLE_PREFIX."notice`(`title`,`content`,`dateline`) values ('{$title}','{$content}','{$timestamp}')";
		$this->DatabaseHandler->Query($sql);

		$this->_update_cache();
		$this->Messager("添加成功",'admin.php?mod=notice');
	}


	function Modify()
	{
		$ids = jget('ids', 'int');

		$sql="SELECT * FROM ".TABLE_PREFIX.'notice'." WHERE id='$ids'";
		$query = $this->DatabaseHandler->Query($sql);
		$notice_info=$query->GetRow();

		if($notice_info==false)
		{
			$this->Messager("您要编辑的信息已经不存在!");
		}

		$ButtonTitle = "编辑";
		$action = "admin.php?mod=notice&code=domodify";

		$notice_id = $notice_info['id'];
		$notice_title = $notice_info['title'];
		$notice_content = $notice_info['content'];

		include $this->TemplateHandler->Template('admin/notice_info');
	}

	function DoModify()
	{

		$title   = $this->Post['title'];
		$content = $this->Post['content'];

		if(empty($title))
		{
			$this->Messager("请输入公告标题",-1);
		}

		if(empty($content))
		{
			$this->Messager("请输入公告内容",-1);
		}

				$f_rets = filter($content);
		if($f_rets && $f_rets['error'])
		{
			$this->Messager($f_rets['msg'],-1);
		}

		$content = unfilterHtmlChars($content);

		$dateline = time();

		$sql = "update `".TABLE_PREFIX."notice` set  `title`='{$title}' ,`content`='{$content}' ,`dateline` ='{$dateline}'  where `id`='" . (int) $this->Post['notice_id'] . "'";
		$this->DatabaseHandler->Query($sql);

		$this->_update_cache();
		$this->Messager("编辑成功",'admin.php?mod=notice');

	}

	function Delete()
	{
		$ids = (array) ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);

		if(!$ids) {
			$this->Messager("请指定要删除的对象");
		}

		$sql = "delete from `".TABLE_PREFIX."notice` where `id` in (".jimplode($ids).")";

		$this->DatabaseHandler->Query($sql);

		$this->_update_cache();
		$this->Messager($return ? $return : "操作成功");

	}

	function _update_cache() {
		cache_file('rm', 'notice/', 1);
	}

}

?>
