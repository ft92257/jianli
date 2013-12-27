<?php

/**
 * 部门管理
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
		if(@is_file(ROOT_PATH . 'include/logic/cp.logic.php') && $this->Config['company_enable'] && $this->Config['department_enable']){
			$lists = $this->CPLogic->GetTable('department');
			$action = '';
			include($this->TemplateHandler->Template('admin/department'));
		}else{
			if(!is_file(ROOT_PATH . 'include/logic/cp.logic.php')){
				$cp_not_install = true;
			}
			include($this->TemplateHandler->Template('admin/cp_ad'));
		}
	}
	function Add()
	{
		$pid = (int)$this->Get['did'];
		if($pid){$dep = $this->CPLogic->Getone($pid,'department');}
		$cid = (int)$this->Get['cid'];
		$cep = $this->CPLogic->Getone($cid);
						$action = 'add';
		$formpost = 'admin.php?mod=department&code=save';
		include($this->TemplateHandler->Template('admin/department'));
	}
	function Mod()
	{
		$did = (int)$this->Get['id'];
		$department = $this->CPLogic->Getrow($did,'department');
		if($department['parentid']){$dep = $this->CPLogic->Getone($department['parentid'],'department');}
		$cep = $this->CPLogic->Getone($department['cid']);
		$mname = $this->CPLogic->Getname($department['cid'],'managerid',$department['managerid']);
		$lname = $this->CPLogic->Getname($department['cid'],'leaderid',$department['leaderid']);
		$action = 'mod';
		$formpost = 'admin.php?mod=department&code=msave';
		include($this->TemplateHandler->Template('admin/department'));
	}
	function Save()
	{
		
		$data = $this->Post;
		if($data['cid'] && $data['name'] && $this->CPLogic->create('department',$data)){
			$this->Messager("添加成功","admin.php?mod=department");
		}else{
			$this->Messager("添加失败，数据填写不完整或不合法！");
		}
	}
	function Msave()
	{
		
		$data = $this->Post;
		if($data['name'] && $data['id'] && $this->CPLogic->modify('department',$data)){
			$this->Messager("修改成功","admin.php?mod=department");
		}else{
			$this->Messager("修改失败，数据填写不完整或不合法！");
		}
	}
	function Del()
	{
		$id = (int)$this->Get['id'];
		if($this->CPLogic->delete('department',$id)){
			$this->Messager("删除成功");
		}else{
			$this->Messager("删除失败，有下属部门，不可直接删除；要删除，请先删除下属部门！");
		}
	}
	function Cache()
	{
		$this->CPLogic->SetCache('department');
		$this->Messager("更新成功");
	}
}
?>