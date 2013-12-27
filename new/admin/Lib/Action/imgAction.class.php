<?php
class imgAction extends Action{
function getimg(){
	$img_mod=D('img');
	if($_FILES['img']){
    $a=$img_mod->bgimg($_FILES['img']);
	}

	dump($a);
}

function applebg(){
$img_mod=D('img');
$a=$img_mod->bgimg($_FILES['apple']);


dump($_FILES);

}
function apidbg(){
$img_mod=D('img');
$a=$img_mod->bgimg($_FILES['apple']);


dump($_FILES);

}

function androidbg(){
$img_mod=D('img');
$a=$img_mod->bgimg($_FILES['apple']);


dump($_FILES);

}



}