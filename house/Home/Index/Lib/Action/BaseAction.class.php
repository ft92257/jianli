<?php 

/**
 * 基础类
 */
class BaseAction extends Action {
	
	protected $oApp;//项目信息
	protected $oUser;//用户信息
	protected $aVerify = array();//需要验证的方法
	protected $model;
	
	public function __construct() {
		parent::__construct();
		
		//加载项目信息
		if (empty($_SESSION['app'])) {
			$_SESSION['app'] = (object) C('APP_INFO');
			$this->oApp = $data;
		} else {
			$this->oApp = $_SESSION['app'];
		}
		
		//加载用户数据
		$uid = D('Session')->check();
		if ($uid) {
			$oUser = D('User')->getObjectById($uid);
			
			if (empty($oUser)) {
				$this->error('调试信息：请检查用户表数据！');
			}
			
			//session有效，赋值oUser
			$this->oUser = $oUser;
		}

		//对免验证模块，不进行登录验证。
		/*if (in_array(ACTION_NAME,$this->aVerify)) {
			if (!$this->isLogin()) {
				$this->error('您尚未登录！', U('User/login'));
			}
		}*/
	}
	
	/*
	 * 是否已登录
	 */
	protected function isLogin() {
		return !empty($this->oUser);
		//return !empty($_SESSION['user']);
	}
	
	
	/*
	 * ajax验证接口	必需设置$this->model
	 * @param _NAME 验证单个字段是否合法，验证规则在model层$aValidate设置
	 * html用法： <input type="text" name="account" id="account" onblur="ajaxValidate(this.id)" /> <span id="_prompt_account"></span>
	 */
	public function ajaxValidate() {
		$name = getRequest('_NAME');

		die($this->model->ajaxValidate($name));
	}
	
	/*
	 * 检测是否post
	 */
	public function checkPost($templateFile='',$charset='',$contentType='',$content='',$prefix='') {
		if (!$this->isPost()) {
			$this->display($templateFile, $charset, $contentType, $content, $prefix);
			die;
		}
	}

}

?>