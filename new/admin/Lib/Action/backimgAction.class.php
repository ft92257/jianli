<?php
/*
set intro
*/
class backimgAction extends Action{

	function index(){
    $bcImg_mod=D('backimage');
    $img=$bcImg_mod->getlist();

    $this->assign('img',$img);
    $this->display();
	}

function change(){
$type=$_GET['type'];
$t_array=array('apple','ipad','android');
	if(in_array($type,$t_array)){
		if($_FILES['file']['name']!=''){
        $img_mod=D('img');
		$su=$img_mod->BgUpdata($_FILES['file'],$type);
			if($su['status']==1){
			$this->success('修改成功');
			}else{
            $this->error($su['msg']);			
			}
		}else{

     
		$this->display();
		}
	}

}

}