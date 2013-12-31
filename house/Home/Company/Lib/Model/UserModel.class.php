<?php 
/**
 * 后台用户登录
 */
class UserModel extends BaseModel {
	/*
	 * 验证form字段规则
	*/
	protected $aValidate = array(
			array('account', 'required', '请填写用户名！'),
			array('account', 'unique', '该用户名已存在！'),
			array('password', 'required', '请填写密码！'),
			array('repassword', 'repeat', '两次密码输入不一致！', 'password'),
			array('realname', 'required', '请填写姓名！'),
			array('nickname', 'required', '请填写昵称！'),
	);

	protected $formConfig = array(
		'account' => array('用户名：', 'text'),
		'password' => array('密码：', 'password'),
		'repassword' => array('确认密码：', 'password'),
		'realname' => array('真实姓名', 'text'),
		'nickname' => array('昵称', 'text'),
		array('', 'submit'),
	);
	
	/*
	 * 扣款
	*/
	public function charge($id, $money) {
		$aManager = $this->getById($id);
		if ($aManager['money'] < $money) {
			return false;
		}
		$set = array(
				'money' => $aManager['money'] - $money,
		);
	
		return $this->where(array('id' => $id))->data($set)->save();
	}
	
	/*
	 * 根据帐号获取用户对象
	*/
	public function getUser($account) {
		$data = $this->where(array('account'=>$account))->find();
		return empty($data) ? null : (object) $data;
	}
	
	/*
	 * 用户登陆
	*/
	public function login($account, $password) {
		$account = getRequest('account');
		$password = getRequest('password');
	
		$oUser = $this->getUser($account);
		if (empty($oUser)) {
			$this->error = '对不起，用户名不存在！';
			return false;
		}
	
		if ($oUser->password != md5($password)) {
			$this->error = '对不起，密码不正确！';
			return false;
		}
	
		D('Session')->setKey($oUser);
	
		return $oUser;
	}
	
}



?>