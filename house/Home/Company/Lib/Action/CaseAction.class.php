<?php 

/**
 * 	案例表相关：信息的查询、修改、添加、删除
 */
class CaseAction extends BaseAction {
	public function __construct() {
		parent::__construct();
	
		$this->model = D('Case');
	}
	/*
	 * 添加案例
	 */
	public function add(){
		if ($this->isPost()) {
			$dataBase = array(
					'cid' => $this->oCom->id,
			);
			$this->_add($dataBase);
		} else {
			$this->model->seTGroups();
			$this->_display_form();
		}
	}
	
	//案例列表
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
			$this->model->seTGroups();
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
	
	/*
	 * 图库
	 */
	public function picture() {
		$this->_picture(2, getRequest('id'));
	}
	
	/*
	 * 设为焦点图
	*/
	public function focus() {
		$id = getRequest('id');
		$data = D('Picture')->getById($id);
		$this->checkPurviewData($data);
		
		$where = array('id' => $data['target']);
		$set = array('focus' => $data['fid']);
		$this->model->where($where)->data($set)->save();

		$this->success('设置成功！');
	}
}
?>