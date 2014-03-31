<?php 

class IndexAction extends BaseAction {

	//需要验证的方法
	protected $aVerify = array(
	);
	
	public function __construct() {
		parent::__construct();
		
		$this->model = D('Inspect');
	}
	

	public function index() {
		//获取5条推荐小区的团验
		$data = $this->model->where(array('xqid' => 1))->order('createtime DESC')->limit(5)->select();

		$this->assign('data', $data);
		$this->display();
	}
	
}

?>