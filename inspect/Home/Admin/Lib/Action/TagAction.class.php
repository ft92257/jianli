<?php 

/**
 * 	标签
 */
class TagAction extends BaseAction {
	
	public function __construct() {
		parent::__construct();
	
		$this->model = D('Tag');
	}
	public function index() {
		$params = array(
			'order' => 'createtime DESC',
		);

		$this->_getPageList($params);
	}
	/*
	 * 添加案例
	 */
	public function add(){
		if ($this->isPost()) {
			$dataBase = array(
					'cid' => $this->oCom->id,
					'uid' => $this->oUser->id,
			);
			$this->_add($dataBase);
		} else {
			$this->_display_form();
		}
	}
	
	/*
	 * 审核
	 */
	public function audit() {
		$this->_audit();
	}
}
?>