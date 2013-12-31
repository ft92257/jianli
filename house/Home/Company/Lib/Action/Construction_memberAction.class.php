<?php 

/**
 * 	相关：信息的查询、修改、添加、删除
 */
class Construction_memberAction extends BaseAction {
	
	public function __construct() {
		parent::__construct();
		$this->model = D('Construction_member');
	}
	
	/*
	 * 删除
	*/
	public function delete() {
		$id = getRequest('id');
		$data = $this->model->getById($id);
	
		$this->_delete(array('id' => $id));
	}
	/*
	 * 施工队成员信息
	 */
	public function index() {
		$params = array(
				'where' =>array('consid'=>getRequest('id')),
				'order' => 'createtime DESC',
		);
		$consid = getRequest('id');
		$this->assign('consid',$consid);
		$this->_getPageList($params);
	}
	/*
	 * 修改
	 */
	public function edit() {
		$data = $this->model->getById(getRequest('id'));
		if ($this->isPost()) {
			$this->_edit($data,array(),$returl);
		} else {
			$data['pic'] = getFileUrl($data['pic'],'80-80');
			$this->_display_form($data);
		}
	}
	public function add(){
		if ($this->isPost()) {
			$dataBase = array(
				'consid' => getRequest('consid')
			);
			$returl = U('Construction_member/index/').'/id/'.$dataBase['consid'];
			$this->_add($dataBase,$returl);
		} else {
			$this->_display_form();
		}
	}
	public function detail() {
		
	}
}
?>