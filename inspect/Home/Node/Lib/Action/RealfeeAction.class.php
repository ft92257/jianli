<?php 

/**
 * 	实际费用
 */
class RealfeeAction extends BaseAction {
	
	public function __construct() {
		parent::__construct();
	
		$this->model = D('User_budget');
		$this->model->oUser = $this->oUser;
	}
	
	public function index() {
		$type = getRequest('type');
		$parent = $this->model->getParent($type, 0);
		$data = $this->model->getRealfee($parent['id']);
		
		$this->assign('data', $data);
		$this->display();
	}
	
}
?>