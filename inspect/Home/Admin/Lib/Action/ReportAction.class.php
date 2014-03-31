<?php 

class ReportAction extends BaseAction {
	
	public function __construct() {
		parent::__construct();
	
		$this->model = D('Inspect_report');
	}
	
	/*
	 * 举报列表
	 */
	public function index(){
		$params = array(
			'order' => 'createtime DESC',
		);

		$this->_getPageList($params);
	}
	
	public function edit() {
		$data = $this->model->getById(getRequest('id'));
		
		if ($this->isPost()) {
			$this->_edit($data);
		} else {
			$this->_display_form($data, 'add');
		}
	}
	
	public function add() {
		if ($this->isPost()) {
			$this->_add();
		} else {
			$this->_display_form();
		}
	}
	
	public function delete() {
		$this->_delete(array('id' => getRequest('id')));
	}
	
	/*
	 * 图库
	*/
	public function picture() {
		$this->_picture(4, getRequest('id'));
	}
	
	/*
	 * 图片管理
	*/
	protected function _picture($type, $id) {
		$this->model = D('Picture');
		$data = $this->model->getTarget($type, $id);
		$tab = $this->model->getTabTitle($type, $id, $data);
		$this->assign('tabTitle', $tab);
		$this->assign('condition', '/type/' . $type . '/target/' . $id);
	
		$params = array(
				'where' => array('type' => $type,	'target' => $id, 'status' => 0),
				'order' => 'ord,createtime DESC',
				'templete' => 'Picture:index',
				'vars' => array('focus' => $data['focus']),
		);
		$this->_getList($params);
	}
}
?>
