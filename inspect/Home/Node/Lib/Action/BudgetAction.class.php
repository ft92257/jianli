<?php 

/**
 * 	预算
 */
class BudgetAction extends BaseAction {
	
	public function __construct() {
		parent::__construct();
	
		$this->model = D('Budget');
	}
	
	/*
	 * 添加
	 */
	public function add(){
		if ($this->isPost()) {
			$dataBase = array(

			);

			$this->_add($dataBase);
		} else {
			$this->_display_form();
		}
	}
	
	//列表
	public function index(){
		$params = array(
				'order' => 'createtime DESC',
		);
		
		$this->_getPageList($params);
	}
	
	/*
	 * 修改
	 */
	public function edit() {
		$data = $this->model->getById(getRequest('id'));

		if ($this->isPost()) {
			$dataBase = array();

			$this->_edit($data, $dataBase);
		} else {
			$this->_display_form($data, 'add');
		}
	}
	
	/*
	 * 删除
	 */
	public function delete() {
		$id = getRequest('id');
		
		$this->_delete(array('id' => $id));
	}
	
}
?>