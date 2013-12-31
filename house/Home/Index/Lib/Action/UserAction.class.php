<?php 

/**
 * 	用户相关：登录，注册，资料修改
 */
class UserAction extends BaseAction {
	
	//需要验证的方法
	protected $aVerify = array(
			'password'
	);
	
	public function __construct() {
		parent::__construct();
		
		$this->model = D('User');
	}
	
	/*
	 * 注册
	 */
	public function register() {
		if (!$this->isPost()) {
			$this->display();
			die;
		}
	
		if ($this->model->register()) {
			$this->success('注册成功！', U('User/login'));
		} else {
			$this->error($this->model->getError());
		}
	}
	
	/*
	 * 登录
	 */
	public function login() {
		redirect(C('TMPL_PARSE_STRING.__HOST__') . '/login.php');
		/*
		if ($this->isLogin()) {
			$this->error('您已经登录了', U('/User/center'));
		}
		
		if (!$this->isPost()) {
			$this->display();
			die;
		}
		
		if ($this->model->login()) {
			$this->success('登录成功！', U('/User/center'));
		} else {
			$this->error($this->model->getError());
		}*/
	}
	
	/*
	 * 退出
	 */
	public function logout() {
		D('Session')->destroy($this->oUser->id);
		$this->success('退出成功！');
	}
	
	/*
	 * 密码修改
	 */
	public function password() {
		$this->checkPost();
		
		if (getRequest('newpassword') != getRequest('repassword')) {
			$this->error('两次密码输入不一致！');
		}
		
		if ($this->oUser->password == md5(getRequest('password'))) {
			$where = array(
						'id' => $this->oUser->id,
					);
			$data = array(
						'password' => md5(getRequest('newpassword')),
					);
			$this->model->where($where)->data($data)->save();
			
			$this->success('修改成功！');
		} else {
			$this->error('旧密码不正确！');
		}
	}
	
}

?>