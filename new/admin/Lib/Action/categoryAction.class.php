<?php
class categoryAction extends Action{

function areaList(){
$area_mod=D('area');
$area=$area_mod->getList();

$this->assign('area',$area);
$this->display();
}

function addarea(){
	if($_POST){
		
	$area_mod=D('area');
	$su=$area_mod->add($_POST);
		if($su['status']==1){
		$this->success('添加成功');
		}else{
		$this->error($su['msg']);
		}
	}else{


	$this->display();
	}
}

function areaedit(){
$area_mod=D('area');
$id=trim($_GET['id']);

	if($_POST){
	$param=$_POST;
	$param['id']=$id;
    $su=$area_mod->edit($param);
		if($su['status']==1){
		$this->success('更新成功');
		}else{
		$this->error($su['msg']);
		}
		
	
	}else{
		$param['id']=$id;
	$area=$area_mod->getArea($param);
	$this->assign('area',$area);
	$this->display();
	}
}



}