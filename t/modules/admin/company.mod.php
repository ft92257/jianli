<?php

/**
 * 单位管理
 *
 * @author 狐狸<foxis@qq.com>
 * @package JishiGou
 */
if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $CPLogic;
	
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		if(@is_file(ROOT_PATH . 'include/logic/cp.logic.php')){
			$this->CPLogic = Load::logic('cp',1);
		}
		$this->Execute();
	}
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'add':
				$this->Add();
				break;
			case 'mod':
				$this->Mod();
				break;
			case 'del':
				$this->Del();
				break;
			case 'save':
				$this->Save();
				break;
			case 'msave':
				$this->Msave();
				break;
			case 'cache':
				$this->Cache();
				break;
			default:
				$this->Main();
				break;
		}
		$body = ob_get_clean();

		$this->ShowBody($body);

	}
	function Main()
	{
		if(@is_file(ROOT_PATH . 'include/logic/cp.logic.php') && $this->Config['company_enable']){
			$lists = $this->CPLogic->GetTable();
			if($lists){
				$action = '';
			}else{
				$action = 'add';
				$formpost = 'admin.php?mod=company&code=save';
			}
			include($this->TemplateHandler->Template('admin/company'));
		}else{
			if(!is_file(ROOT_PATH . 'include/logic/cp.logic.php')){
				$cp_not_install = true;
			}
			include($this->TemplateHandler->Template('admin/cp_ad'));
		}
	}
	function Add()
	{
		$pid = jget('cid','int','G');
		if($pid){
			$cep = $this->CPLogic->Getone($pid);
								}
		$action = 'add';
		$formpost = 'admin.php?mod=company&code=save';
		include($this->TemplateHandler->Template('admin/company'));
	}
	function Mod()
	{
		$cid = jget('id','int','G');
		$company = $this->CPLogic->Getrow($cid);
		$pid = $comp['parentid'];
		if($pid){$cep = $this->CPLogic->Getone($pid);}
				$mname = $this->CPLogic->Getname($cid,'managerid',$company['managerid']);
		$lname = $this->CPLogic->Getname($cid,'leaderid',$company['leaderid']);
		$action = 'mod';
		$formpost = 'admin.php?mod=company&code=msave';
		include($this->TemplateHandler->Template('admin/company'));
	}
	function Save()
	{
		
		$data = $this->Post;
		if($data['name'] && $this->CPLogic->create('company',$data)){
			$this->Messager("添加成功","admin.php?mod=company");
		}else{
			$this->Messager("添加失败，数据填写不完整或不合法！");
		}
	}
	function Msave()
	{
		
		$data = $this->Post;
		if($data['name'] && $data['id'] && $this->CPLogic->modify('company',$data)){
			$this->Messager("修改成功","admin.php?mod=company");
		}else{
			$this->Messager("修改失败，数据填写不完整或不合法！");
		}
	}
	function Del()
	{
		$id = jget('id','int','G');
		if($this->CPLogic->delete('company',$id)){
			$this->Messager("删除成功");
		}else{
			$this->Messager("删除失败，有下属单位，不可直接删除；要删除，请先删除下属单位！");
		}
	}
	function Cache()
	{
		$this->CPLogic->SetCache();
		$this->Messager("更新成功");
	}
}
?>