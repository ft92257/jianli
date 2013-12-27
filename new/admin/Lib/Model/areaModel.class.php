<?php
class areaModel {

function add($param){
if($param['name']){
$data['area_name']=$param['name'];
}else{
$info['msg']='请输入地区名';
return($info);
}
$data['ordid']=$param['ordid'];
$area_mod=M('area');
$su=$area_mod->add($data);
	if($su){
	$info['status']=1;
	}else{
	$info['msg']=$area_mod->getlastsql();
	}
return ($info);
}

function getList(){
$area_mod=M('area');
$area=$area_mod->order('ordid')->select();
return $area;

}

function getArea($param){
if(is_numeric($param['id'])&&!empty($param['id']))
{$map['area_id']=$param['id'];
}else{

return;
}

$area_mod=M('area');
$area=$area_mod->where($map)->find();
return $area;
}


function edit($param){
    
	if(is_numeric($param['id'])&&!empty($param['id'])){
	$map['area_id']=$param['id'];
	
	}else{
		
	$info['msg']='错误';
	return $info;
	}
	$data['area_name']=$param['name'];
	$data['ordid']=$param['ordid'];
$area_mod=M('area');
$su=$area_mod->where($map)->save($data);

	if($su){
	$info['status']=1;
	}else{
	$info['msg']='更新失败或值未更改';
	}

	return($info);
}

}