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
	 * 预算设置
	 */
	public function set() {
		if ($this->isPost()) {
			$this->redirect('detail');
		} else {
			$this->display();
		}
	}
	
	public function detail() {
		$this->display();
	}
	
	/*
	 * 大饼图
	 */
	public function pie() {
		import('Public.Library.PieImage', './');
		
		PieImage::make();
	}
	
	public function calculate() {
		$arr = array(
			'status' => 0,
			'msg' => '预估完成！',
			'data' => array(
				'design' => 6000,
				'artificial' => 18000,
				'material' => 60000,
				'grade' => array(
					'design' => 2,
					'artificial' => 2,
					'material' => 2,
				),
			),
		);
		echo json_encode($arr);
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