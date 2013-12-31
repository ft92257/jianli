<?php 

/**
 * 	预约款相关
 */
class PayAction extends BaseAction {
   
	public function __construct() {
		parent::__construct();
	
		$this->model = D('Pay');
	}
	
	public function index() {
		$params = array(
				'where' => $this->getPurviewCondition(),
				'order' => 'createtime DESC',
		);
		
		$this->_getPageList($params);
	}
	
	public function charge() {
		$this->model = D('Money');
		$params = array(
				'where' => $this->getPurviewCondition(),
				'order' => 'createtime DESC',
		);
		
		$this->_getPageList($params);
	}

}
 ?>