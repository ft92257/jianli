<?php 

class GoodsAction extends BaseAction {
	
	public function __construct() {
		parent::__construct();
	
		$this->model = D('Goods');
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
			$this->_add();
		} else {
			$this->_display_form();
		}
	}
	
	public function edit() {
		$data = $this->model->getById(getRequest('id'));
	
		if ($this->isPost()) {
			$this->_edit($data);
		} else {
			$data['focus'] = getFileUrl($data['focus'], '80-80');
			$this->_display_form($data, 'add');
		}
	}
}
?>