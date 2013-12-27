<?php 
class introModel {
private $path;

function __construct(){
$this->path='./data/intro/intro.html';
$su=$this->checkIntro();
}

private function checkIntro(){
$is=file_exists($this->path);
$re=is_writable($this->path);
$re?'':$info['msg']='文件不可写';
$is?'':$info['msg']='文件不存在';
$info?'':$info['status']=1;
}


function getIntro(){
$file=file_get_contents($this->path);
return $file;
}

function update($data){
$su=file_put_contents($this->path,$data);
if($su){
$info['status']=1;
}else{
$info['msg']='写入失败';
}
return $info;
}



}