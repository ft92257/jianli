<?php 

/**
 * 	相关：信息的查询、修改、添加、删除
 */
class ConstructionAction extends BaseAction {
	public function __construct() {
		parent::__construct();
	
		$this->model = D('Construction');
	}
	
	/*
	 * 添加施工队
	 */
	public function add(){
		if ($this->isPost()) {
			$dataBase = array(
					'cid' => $this->oCom->id,
			);
			$this->_add($dataBase);
		} else {
			$this->_display_form();
		}
	}
	
	/*
	 * 施工队列表
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
			$data['focus'] = getFileUrl($data['focus'],'200-200');
			$this->_display_form($data);
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