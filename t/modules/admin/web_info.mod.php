<?php
/**
 * 文件名：income.mod.php
 * 版本号：1.0
 * 最后修改时间：Tue Oct 30 13:16:22 CST 2007
 * 作者：狐狸<foxis@qq.com>
 * 功能描述：网站信息块管理
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $ID = 0;
	var $_config=array();
	var $configPath="";

	
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		$this->ID = $this->Get['id']?(int)$this->Get['id']:(int)$this->Post['id'];
		$this->_setConfig();
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
			case 'google':
				$this->Google();
				break;
			case 'baidu':
				$this->Baidu();
				break;
			case 'aijuhe':
				$this->Aijuhe();
				break;
			case 'alimama':
				$this->Alimama();
				break;
			case 'vodone':
				$this->Vodone();
				break;			
			case 'other':
				$this->Other();
				break;
			default:
				$this->Code = 'web_info_setting';
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
		$_web_info = ConfigHandler::get('web_info');
		
		
		include($this->TemplateHandler->Template('admin/web_info'));
	}
	
	function DoModify()
	{
		$set['about']=$this->Post['about'];
		$set['contact']=$this->Post['contact'];
		$set['joins']=$this->Post['joins'];
		$set = jstripslashes($set);
		ConfigHandler::set('web_info',$set);
		
		$this->Messager("修改成功",'admin.php?mod=web_info');
	}
	
	
	function _setConfig()
	{

		$this->_config['about']=array('name'=>"关于我们","info_list"=>array("contents"=>array("name"=>"关于我们",'value'=>'contents','width'=>"800px"),));
		
		$this->_config['contact']=array('name'=>"联系我们","info_list"=>array("contents"=>array("name"=>"联系我们",'value'=>'contents','width'=>"800px"),));
		
		$this->_config['joins']=array('name'=>"加入我们","info_list"=>array("contents"=>array("name"=>"加入我们",'value'=>'contents','width'=>"800px"),));
		
	}
	
	

}
?>
