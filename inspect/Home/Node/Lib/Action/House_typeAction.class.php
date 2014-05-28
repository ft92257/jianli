<?php
class House_typeAction extends BaseAction {
	
	public function house_typeSelect(){
		$this->model = D('House_type');
		if (!$_POST['id']){
			$res = $this->model->where(array('level' => $_POST['level']))->select();
		} else {
			$res = $this->model->where(array('level' => $_POST['level'], 'pid' => $_POST['id']))->select();
		}
		echo json_encode($res);exit;
	}
}