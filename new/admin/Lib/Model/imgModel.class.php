<?php
class imgModel {
function advimg($file){
$path='/data/img/advimg/';
$su=upload($file,$path);
if($su['status']==1){
$info['status']=1;
$info['data']['img']=$path.$su['data']['0']['savename'];
}else{
$info['msg']=$su['msg'];
}
return $info;
}

function bgimg($file){
$path=__ROOT__.'/data/bgimg/';
$img=upload($file,$path);
return($img);
}

function exaimg($file){
$path='/data/img/';
$su=upload($file,$path);
if($su['status']==1){
$info['status']=1;
$info['data']['img']=$path.$su['data']['0']['savename'];
}else{
$info['msg']=$su['msg'];
}
return $info;
}

function BgUpdata($file,$type){
$path='/data/bgimg/';
$su=upload($file,$path);
if($su['status']==1){

$data['img']=$path.$su['data']['0']['savename'];
$bgimg_mod=M('bgimg');
$map['type']=$type;
$su=$bgimg_mod->where($map)->save($data);
	if($su){
	$info['status']=1;
	return($info);
}
}

return $info;
}


}