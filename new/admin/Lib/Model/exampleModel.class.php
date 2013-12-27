<?php
class exampleModel {

function addExample($param){
	
$exList_mod=M('example_list');
$exList_mod->create($param);
if(empty($exList_mod->name)){
$info['msg']='1';
return $info;
}
/*
if(empty($exList_mod->img)){
$info['msg']='';
return $info;
}
*/
if(empty($exList_mod->address)){
$info['msg']='1';
return $info;
}

$su=$exList_mod->add();
if($su){
$info['status']='1';
}else{
$info['msg']='添加失败';
}
return($info);
}


function getExamImg($param){
$id=$param['id'];
$param['page']>1?$page=$param['page']:$page=1;
$param['length']?$length=$param['length']:$length=10;
$map['id']=$id;
$exaImg_mod=M('example_img');
$count=$exaImg_mod->where($map)->count();
$img_lis=$exaImg_mod->where($map)->page($page.','.$length)->select();
$info['data']['count']=$count;
$info['data']['length']=$length;
$info['data']['data']=$example_list;
return $Info;
}

function getExampleList($param){
$param['page']>1?$page=$param['page']:$page=1;
$param['length']?$length=$param['length']:$length=10;
$example_mod=M('example_list');
$count=$example_mod->count();
$example_list=$example_mod->page($page.','.$length)->select();
$info['data']['count']=$count;
$info['data']['length']=$length;
$info['data']['data']=$example_list;

return $info;
}

function getExample($param){
$id=$param['id'];
$exa_mod=M('example_list');
$exa=$exa_mod->getById($id);
	if($exa){
	$info['status']=1;
	$info['data']=$exa;
	}else{
    $info['msg']='没有此组';
	}
	return($info);
}

function edit($param){
	if(!is_numeric($param['id'])&&empty($param['id'])){
	$info['msg']='错误';
	return($info);
	}
$exa_mod=M('example_list');
$map['id']=$param['id'];
	unset($param['id']);
	if(empty($param['img'])){
	unset($param['img']);	
	}
$su=$exa_mod->where($map)->save($param);
	if($su){
	$info['status']=1;
	}else{
	$info['msg']='更新失败或数据未更改';
	}
	return ($info);
}
}