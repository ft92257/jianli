<?php 

/**
 * 	用户相关：登录，注册，资料修改
 */
class UserAction extends BaseAction {

	//需要验证的方法
	protected $aVerify = array(
	);
	
	public function __construct() {
		parent::__construct();
		
		$this->model = D('User');
	}
	
	/*
	 *
	 */
	public function register() {
		if (!$this->isPost()) {
			//$this->display();
		}
	
		if ($this->model->register(getRequest('account'), getRequest('password'))) {
			$this->success('注册成功！');
		} else {
			$this->error($this->model->error);
		}
	}
}

?>