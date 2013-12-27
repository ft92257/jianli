<?php
class exampleAction extends Action{

function index(){
$area_mod=D('area');
$style_mod=D('style');
$ass['area']=$area_mod->getList();
$ass['style']=$style_mod->getList();
$this->assign($ass);
$this->display();
}


function  exlist(){
$example_mod=D('example');
$info=$example_mod->getExampleList();
$data=$info['data']['data'];
$count=$info['data']['count'];
$length=$info['data']['length'];
$page=page($count,$length);

$ass['list']=$data;
$ass['page']=$page;
$this->assign($ass);
$this->display('list');
}

function addexa(){
$example_mod=D('example');
$su=$example_mod->addExample($_POST);
if($su['status']){
$this->success('添加成功');
}else{
$this->error($info['msg']);
}
}
function edit(){
$exa_mod=D('example');
$id=trim($_GET['id']);
if($_POST){
$param=$_POST;
if($_FILES['file']['name']!=''){
$img_mod=D('img');
$img=$img_mod->exaimg($_FILES['file']);
	if($img['status']==1){
	$param['img']=$img['data']['img'];
	}else{
	$this->error('图片上传失败');
	}
}
$param['id']=$id;
$info=$exa_mod->edit($param);
if($info['status']==1){
$this->success('修改成功');
}else{
$this->error($info['msg']);

}
}else{
$area_mod=D('area');
$style_mod=D('style');
$ass['area']=$area_mod->getList();
$ass['style']=$style_mod->getList();
$param['id']=trim($_GET['id']);
$info=$exa_mod->getExample($param);
$ass['info']=$info['data'];
$this->assign($ass);
$this->display();
}
}

function exainfo(){
$id=trim($_GET['id']);

$this->display();
}



}