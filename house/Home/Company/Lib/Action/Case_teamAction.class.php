<?php 

/**
 * 	活动
 */
class Case_teamAction extends BaseAction {
	
	public function __construct() {
		parent::__construct();
	
		$this->model = D('Case_team');
	}
	
	/*
	 * 添加
	 */
	public function add(){
		if ($this->isPost()) {
			$_POST['observe_date'] = strtotime(getRequest('observe_date'));
			$_POST['observe_end'] = strtotime(getRequest('observe_end'));
			$dataBase = array(
						'cid' => $this->oCom->id,
			);
			$this->_add($dataBase);
		} else {
			$this->_display_form();
		}
	}
	
	/*
	 * 活动列表
	 */
	public function index(){
		$params = array(
				'where' => $this->getPurviewCondition(),
				'order' => 'createtime DESC',
		);
		
		$this->_getPageList($params);
	}
	
	/*
	 * 修改
	 */
	public function edit() {
		$data = $this->model->getById(getRequest('id'));
		$this->checkPurviewData($data);
		
		if ($this->isPost()) {
			$this->_edit($data);
		} else {
			$this->_display_form($data, 'add');
		}
	}
	
	/*
	 * 删除
	 */
	public function delete() {
		$id = getRequest('id');
		$data = $this->model->getById($id);
		$this->checkPurviewData($data);
		
		$this->_delete(array('id' => $id));
	}
	
	

}
?>
