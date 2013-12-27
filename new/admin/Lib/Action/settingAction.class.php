<?php

class settingAction extends baseAction
{
	function index()
	{
		$setting_mod = M('setting');
		$setting = $setting_mod->select();
		foreach( $setting as $val )
		{
			$set[$val['name']] = $val['data'];
		}
		$this->assign('set',$set);
		$this->display($_REQUEST['type']);
	}
	function edit()
	{
		$setting_mod = M('setting');
		foreach($this->_stripcslashes($_POST['site']) as $key=>$val ){
			$setting_mod->where("name='".$key."'")->save(array('data'=>$val));
		}
		$this->success('修改成功',U('setting/index'));
	}
}
?>