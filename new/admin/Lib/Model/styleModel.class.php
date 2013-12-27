<?php
class styleModel {

function add($param){
	if($param['name']){
	$data['name']=$param['name'];
	}else{
	
	$info['msg']='请输入风格名';
	return($info);
	}
$data['ordid']=$param['ordid'];
$style_mod=M('style');
$su=$style_mod->add($data);
	if($su){
	$info['status']=1;
	}else{
	$info['msg']='错误';
	}
return ($info);
}

function getList(){
$style_mod=M('style');
$style=$style_mod->order('ordid')->select();
return $style;

}

function get_one($param){
if(is_numeric($param['id'])&&!empty($param['id']))
{$map['id']=$param['id'];
$style=M('style')->getById($map['id']);

return $style;
}else{

return;
}

$style_mod=M('style');
$style=$style_mod->where($map)->find();
return $style;
}


function edit($param){
    
	if(is_numeric($param['id'])&&!empty($param['id'])){
	$map['id']=$param['id'];
	
	}else{
	
	$info['msg']='错误';
	return $info;
	}

	//$data['style']=$param['style'];

$style_mod=M('style');

$su=$style_mod->where($map)->save($param);

	if($su){
	$info['status']=1;
	}else{
	$info['msg']='更新失败或值未更改'.$style_mod->getlastsql();
	}

	return($info);
}

}