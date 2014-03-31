<?php 

/**
 * 	   管理员控制器
 */
class ManagerAction extends BaseAction {
     
	public function __construct() {
		parent::__construct();
		
		$this->model = D('Manager');
	}
	/* 
	 * 管理员登录
	 */
    public function  login()  {
    	$this->checkPost();
    	if ($this->model->login(getRequest('account'), getRequest('password'), getRequest('verify'))) {
			$this->success('登录成功', U('/Index/index'));//验证成功跳转到公司列表
		}else{
			$this->error($this->model->getError(),U('/Manager/login'));
		}
	}
	
	public function verify() {
		import('ORG.Util.Image');
		Image::buildImageVerify(4,0,'png',80,30,'verify');
	}

	public function logout() {
   		$this->model->logout();
   		$this->success('退出系统',U('Manager/login'));
	}

}

?>