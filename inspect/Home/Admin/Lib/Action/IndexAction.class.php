<?php 

/**
 * 	   管理员控制器
 */
class IndexAction extends BaseAction {
     
	public function __construct() {
		parent::__construct();

	}

	public function index() {
		$this->redirect('/Question/index');
	}
	
	
}

?>