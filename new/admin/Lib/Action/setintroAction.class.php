<?php
/*
set intro
*/
class setintroAction extends Action{

	function index(){
    $intro_mod=D('intro');
    $intro=$intro_mod->getIntro();
	$this->assign('intro',$intro);
    $this->display(	);
	}

	function test(){
	$param['page']=getpage();
	$company_mod=D('company');
    $info=$company_mod->getlist($param);
	$ass['page']=page($info['data']['count'],$info['data']['length']);
	$ass['info']=$info['data']['data'];
	$this->assign($ass);

	$this->display();
	}

function getinfo(){
$company_mod=D('company');

	if($_POST){
	foreach($_POST['data'] as $k=>$v){
		
		 if(empty($v['id'])){
		 $su=$company_mod->add($v);
			 if($su['status']==1){
			 $a['key']=$v['key'];
			 $a['id']=$su['data']['id'];
			 $arr[]=$a;
			 }
		
		 }else{
		 $su=$company_mod->update($v);
	
		 }

    }
    $info=json_encode($arr);
	echo( $info);
	}
}
	
function backimg()
{
$bcImg_mod=D('backimage');
$bcImg_mod->update();
$this->display('edit');
}


function update(){
$intro_mod=D('intro');
$data=stripslashes($_POST['text']);
$su=$intro_mod->update($data);
	if($su['status']==1){
	$this->success('修改成功');
	}else{
	$this->error($su['msg']);
	}

}

function intro(){
$param['page']=getpage();
$param['length']='10';
$company_mod=D('company');
$data=$company_mod->getlist($param);
$ass['company']=$data['data']['data'];
$ass['page']=page($data['data']['count'],$data['data']['length']);
$this->assign($ass);
$this->display('list');
}

function ttt(){
$this->display('intro');
}

function edit(){
	
$id=$_GET['id'];

if(floor($id)==$id&&$id>0){
    $company_mod=D('company');
	if($_POST){
	$param=$_POST;
	$param['id']=$id;
	$su=$company_mod->update($param);
	if($su['status']==1){
	$this->success('修改成功');
	}else{
	$this->error($su['msg']);
	}
	}else{
	$param['id']=$id;
	$info=$company_mod->getone($param);
	$ass['info']=$info;
	$area=D('area')->getlist();
	$ass['area']=$area;
	$this->assign($ass);
	$this->display();
	}

}

}

}