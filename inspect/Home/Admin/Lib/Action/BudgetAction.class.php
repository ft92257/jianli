<?php 

class BudgetAction extends BaseAction {
	
	public function __construct() {
		parent::__construct();
	
		$this->model = D('Budget');
	}
	
	//列表
	public function index(){
		$params = array(
			//'order' => 'createtime DESC',
		);

		$this->_getPageList($params);
	}
	
	public function add() {
		if ($this->isPost()) {
			$database = array(
				);
			$this->_add($database);
		} else {
			$this->_display_form();
		}
	}
	
	public function edit() {
		$data = $this->model->getById(getRequest('id'));
	
		if ($this->isPost()) {
			$this->_edit($data);
		} else {
			$this->_display_form($data, 'add');
		}
	}
}
?>