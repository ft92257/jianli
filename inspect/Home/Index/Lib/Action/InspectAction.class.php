<?php 

class InspectAction extends BaseAction {

	//需要验证的方法
	protected $aVerify = array(
	);
	
	public function __construct() {
		parent::__construct();
		$this->model = D('Inspect');
	}
	

	public function index() {
		//9个推荐小区
		$data = $this->model->where(array())->order('createtime DESC')->limit(9)->select();
		
		//199元验房项目
		$data_199 = $this->model->where(array('price' => array('between', '1,199')))->order('createtime DESC')->limit(50)->select();
		
		//399元验房项目
		$data_399 = $this->model->where(array('price' => array('between', '200,399')))->order('createtime DESC')->limit(50)->select();
		
		$this->assign('data', $data);
		$this->assign('data_199', $data_199);
		$this->assign('data_399', $data_399);
		
		$this->display();
	}
	
	
	public function detail() {

		$this->display();
	}
	
}

?>