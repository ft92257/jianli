<?php
class backimageModel extends Model{
private $param;

function update($param){
$this->param=$param;
$path='./data/xml';
$file=$path.'backimage.xml';
if(!file_exists($file)){
fopen($file);
}else{
echo 1;
};



}
function getlist(){
$bgimg_mod=M('bgimg');
$info=$bgimg_mod->select();
return($info);

}


function getBackImage(){






}



} 