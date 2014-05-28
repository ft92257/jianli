<?php
class CityAction extends BaseAction {
	
	public function citySelect(){
			$this->model = D('District');
			if (!$_POST['id']){
				$res = $this->model->where(array('level' => $_POST['level']))->select();	
			} else {
				$res = $this->model->where(array('level' => $_POST['level'], 'upid' => $_POST['id']))->select();
			}
			echo json_encode($res);exit;
	}
}