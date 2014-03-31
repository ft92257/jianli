<?php 

/**
 * 	充值
 */
class PayAction extends BaseAction {
	
	public function __construct() {
		parent::__construct();
	
		$this->model = D('Pay');
	}
	
	//列表
	public function index(){
		$params = array(
			'order' => 'createtime DESC',
		);

		$this->_getPageList($params);
	}
	
}
?>