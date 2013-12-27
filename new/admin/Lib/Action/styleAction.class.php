<?php
class styleAction extends baseAction{

function index(){
$style_mod=D('style');
$style=$style_mod->getlist();
$ass['style']=$style;
$this->assign($ass);
$this->display();
}

function edit(){
$style_mod=D('style');
	$id=$_GET['id'];
	if($_POST){
	$param=$_POST;
	$param['id']=$id;
	$su=$style_mod->edit($param);
		if($su['status']==1){
		$this->success('修改成功');
		}else{
		$this->error('修改失败');
		
		}
	
	}else{
	$param['id']=$id;
	$style=$style_mod->get_one($param);
	$ass['style']=$style;
	$this->assign($ass);
	$this->display();
	}
}


}