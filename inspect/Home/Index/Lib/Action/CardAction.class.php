<?php 

class CardAction extends BaseAction {

	//需要验证的方法
	protected $aVerify = array(
	);
	
	public function __construct() {
		parent::__construct();
		
	}
	

	public function index() {

		$this->display();
	}
	
	public function detail() {

		$this->display();
	}
	
}

?>