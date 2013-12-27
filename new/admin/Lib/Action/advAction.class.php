<?php
class advAction extends Action{
function index(){
$adv_mod=D('adv');
$list=$adv_mod->getlist();
$this->assign('img',$list);
$this->display();
}

function add(){

	if($_FILES['file']['name']!=''){
	$img_mod=D('img');
	$su=$img_mod->advimg($_FILES['file']);
		if($su['status']==1){
		$param=$_POST;
		$param['img']=$su['data']['img'];
		$adv_mod=D('adv');
		$su=$adv_mod->add($param);
			if($su['status']==1){
			$this->success('添加成功');
			}else{
			$this->error($su['msg']);
			}
		}

	}else{

	$this->display('edit');
	}

}

function edit(){
$id=trim($_GET['id']);
$adv_mod=D('adv');
if($_POST){
$param=$_POST;
$param['name']=trim($_POST['name']);
$param['id']=$id;
   if($_FILES['file']['name']!=''){
   $img_mod=D('img');
   $img=$img_mod->advimg($_FILES['file']);
     if($img['status']==1){
	 $param['img']=$img['data']['img'];
	 }else{
	 $this->error($img['msg']);
	 }
   }

$su=$adv_mod->edit($param);
if($su['status']==1){
$this->success('更新成功');
}else{
$this->error($su['msg']);
}
}else{

$this->display();
}
}


}