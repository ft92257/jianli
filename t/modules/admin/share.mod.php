<?php
/**
 *
 * 微博站外展示调用
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: share.mod.php 954 2012-05-24 09:47:12Z wuliyong $
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $TopicLogic;

	function ModuleObject($config)
	{
		$this->MasterObject($config);

		if('share_setting' == $this->Code || '' == $this->Code) {
						;
		} else {
			if(true !== JISHIGOU_FOUNDER) {
				$this->Messager('特别说明：鉴于安全考虑，站外展示调用模块仅允许“网站创始人”进行添加、编辑、删除等操作。', '', 10);
			}
		}
		
		$this->TopicLogic = Load::logic('topic', 1);

		Load::lib('form');
		$this->FormHandler = new FormHandler;

		$this->CacheConfig = ConfigHandler::get('cache');
		$this->ShareConfig = ConfigHandler::get('share');

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
			case 'do_add':
				$this->DoAdd();
				break;
			case 'modify':
				$this->Modify();
				break;
			case 'domodify':
				$this->DoModify();
				break;
			default:
				$this->Code = 'share_setting';
				$this->Main();
				break;
		}
		$body = ob_get_clean();

		$this->ShowBody($body);

	}

	function Main()
	{
		$sql = " select * from `".TABLE_PREFIX."share`";
		$query = $this->DatabaseHandler->Query($sql);
		$sharelist = array();
		while (false != ($row = $query->GetRow())) {
			$sharelist[] = $row;
		}


		include $this->TemplateHandler->Template('admin/share');

	}

	function Add() {
		$action = "admin.php?mod=share&code=do_add";

				$share['topic_charset'] = $this->Config['charset'];
		$share['string'] = '100';
		$share['limit'] = '20';

				$_loop  = "loop";
		$_loop2 = "/loop";
		$_if	= "if";
		$_if2 	= "/if";
		$_val	= '$val';
		$_iv 	= '$iv';		$_arrlist = '$topic_list';
		$site_url = '{$this->Config[site_url]}';
		
		include  $this->TemplateHandler->Template('iframe_recommend');
		$content = ob_get_contents();
		ob_clean();

		$web_share_temp =  $content;

		include $this->TemplateHandler->Template('admin/share_info');
	}


	function DoAdd()
	{

		$name = $this->Post['name'];
		$type = $this->Post['type'];

		$dateline = time();

		if(empty($name))
		{
			$this->Messager("请写一下描述说明，方便自己管理",-1,3);
		}

				$style = "";

				$condition = "";

				$show = $this->Post['share']['show'];
		$show = serialize($show);

				$nickname = $type == 'topic' ? trim($this->Post['share']['nickname']) : '';

				$tag = $type == 'tag' ? trim($this->Post['share']['tag']) : '';

		$sql = "insert into `".TABLE_PREFIX."share`
					(`name`,`type`,`topic_style`,`show_style`,`condition`,`nickname`,`tag`,`dateline`) 
				values 
					('{$name}','{$type}','{$style}','{$show}','{$condition}','{$nickname}','{$tag}','{$dateline}')";

		$query = $this->DatabaseHandler->Query($sql);
		$shareid = $this->DatabaseHandler->Insert_ID();

				$set=$this->Post['sharetemp'];
		$set = jstripslashes($set);

		$file = ROOT_PATH . 'templates/default/share/sharetemp_'.$shareid.'.html';
				if(!is_dir(dirname($file))) {
			jmkdir(dirname($file));
		}
		$len = Load::lib('io', 1)->WriteFile($file, $set);
		if(!$len) {
			die($file." 文件无法写入,请检查是否有可写权限。");
		}

		$sets['sharetemp'] = $set;
		ConfigHandler::set('sharetemp_'.$shareid,$sets);
			
		$this->Messager("添加成功","admin.php?mod=share");

	}


	function Modify()
	{
		$ids = max(0, (int) $this->Get['ids']);
		if(!$ids) $this->Messager("请指定一个ID",null);

		$action = "admin.php?mod=share&code=domodify";

		$sql = " select * from `".TABLE_PREFIX."share`  where  `id` = '{$ids}'";
		$query = $this->DatabaseHandler->Query($sql);
		$sharelist = $query->GetRow();

				$share = @unserialize($sharelist['show_style']);
				if(!$share['topic_charset']) $share['topic_charset'] = $this->Config['charset'];
				if(!$share['limit']) $share['limit'] = '20';
		$topic_charset = $share['topic_charset'];


		$_loop = "loop";
		$_loop2 = "/loop";
		$_if	= "if";
		$_if2 	= "/if";
		$_val	= '$val';
		$_iv 	= '$iv';		$_arrlist = '$topic_list';
		$site_url = '{$this->Config[site_url]}';


				$share_temp = ConfigHandler::get('sharetemp_'.$ids);
		$web_share_temp = $share_temp['sharetemp'];
				if(!$web_share_temp)
		{
			include  $this->TemplateHandler->Template('iframe_recommend');

			$web_share_temp = ob_get_contents();

			ob_clean();
		}


		include $this->TemplateHandler->Template('admin/share_info');
	}

	function DoModify()
	{

		$ids = max(0, (int) $this->Post['ids']);
		if(!$ids) $this->Messager("请指定一个ID",null);

		$name = $this->Post['name'];
		$type = $this->Post['type'];
		$dateline = time();

				$style = $this->Post['share']['style'];
		$style = serialize($style);

				$show = $this->Post['share']['show'];
		$show = serialize($show);

				$module = $this->Post['share']['condition'];
		$condition = serialize($module);

				$nickname = $type == 'topic' ? trim($this->Post['share']['nickname']) : '';

				$tag = $type == 'tag' ? trim($this->Post['share']['tag']) : '';

		$sql = "update `".TABLE_PREFIX."share`
    			set `name`='{$name}',
    				`type`='{$type}' ,
    				`topic_style`='{$style}' ,
    				`show_style`='{$show}' ,
    				`condition`='{$condition}' ,
    				`nickname`='{$nickname}',
    				`tag`='{$tag}',
    				`dateline`={$dateline} 
    			where `id` = '{$ids}'";	
		$this->DatabaseHandler->Query($sql);

				$set=$this->Post['sharetemp'];
		$set = jstripslashes($set);

		$file = ROOT_PATH . 'templates/default/share/sharetemp_'.$ids.'.html';
				if(!is_dir(dirname($file))) {
			jmkdir(dirname($file));
		}
		$len = Load::lib('io', 1)->WriteFile($file, $set);
		if(!$len) {
			die($file." 文件无法写入,请检查是否有可写权限。");
		}

		$sets['sharetemp'] = $set;
		ConfigHandler::set('sharetemp_'.$ids,$sets);

		$this->Messager("编辑成功","admin.php?mod=share&code=modify&ids={$ids}");

	}

	function Delete()
	{
				$ids = $this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids'];
		if(!$ids) {
			$this->Messager("请指定要删除的对象");
		}
		$ids = (array) $ids;

		foreach($ids as $id)
		{
			$id = is_numeric($id) ? $id : 0;

			if($id > 0)
			{
				$sql = "delete from `".TABLE_PREFIX."share` where `id` = '{$id}'";
				$this->DatabaseHandler->Query($sql);


								$file = ROOT_PATH . 'templates/default/share/sharetemp_'.$id.'.html';

				Load::lib('io', 1)->DeleteFile($file);
				Load::lib('io', 1)->DeleteFile('./setting/sharetemp_'.$id.'.php');
			}
		}


		$this->Messager("删除成功","admin.php?mod=share");

	}

}

?>
