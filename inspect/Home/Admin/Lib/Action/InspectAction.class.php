<?php 

class InspectAction extends BaseAction {
	
	public function __construct() {
		parent::__construct();
	
		$this->model = D('Inspect');
	}
	
	/*
	 * 列表
	 */
	public function index(){
		$params = array(
			'order' => 'createtime DESC',
			//'where' => array('status' => 0),
		);

		$this->_getPageList($params);
	}
	
	public function edit() {
		$data = $this->model->getById(getRequest('id'));
		
		if ($this->isPost()) {
			$database = array(
					'begin_date' => strtotime(getRequest('begin_date')),
					'end_date' => strtotime(getRequest('end_date')),
			);
			$this->_edit($data, $database);
		} else {
			$data['focus'] = getFileUrl($data['focus'], '600-360');
			$data['begin_date'] = date('Y-m-d', $data['begin_date']);
			$data['end_date'] = date('Y-m-d', $data['end_date']);
			$this->_display_form($data, 'add');
		}
	}
	
	public function add() {
		if ($this->isPost()) {
			$database = array(
					'begin_date' => strtotime(getRequest('begin_date')),
					'end_date' => strtotime(getRequest('end_date')),
				);
			$this->_add($database);
		} else {
			$this->_display_form();
		}
	}
	
	public function delete() {
		$this->_delete(array('id' => getRequest('id')));
	}
}
?>