<?php
class advModel{
function add($param){
$adv_mod=M('adv');
$su=$adv_mod->add($param);
if($su){
$info['status']=1;
}else{
$info['msg']='错误';
}
return($info);
}

function edit($param){
	if(!empty($param['id'])&&is_numeric($param['id'])){
	$map['id']=$param['id'];
	unset($param['id']);
	$adv_mod=M('adv');
	$su=$adv_mod->where($map)->save($param);
		if($su){
         $info['status']=1;
		}else{
		$info['msg']='更新失败或未更改';
		}
	}else{
	$info['msg']='参数错误';
	}
	return($info);
}

function getlist(){
$adv_mod=M('adv');
$list=$adv_mod->select();
return($list);
}



}