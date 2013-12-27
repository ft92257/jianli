<?php 

/**
 * User模型
 *
 */
class UserModel extends BaseModel {
	
	/*
	 * 验证form字段规则
	 */
	protected $aValidate = array(
		array('account', 'required', '请填写用户名！'),
		array('account', 'unique', '该用户名已存在！'),
	);
	
	/*
	 * 根据帐号获取用户对象
	 */
	public function getUser($account) {
		$data = $this->where(array('account'=>$account, 'appid'=>$this->oApp->id, 'status' => 0,))->find();
		return empty($data) ? null : (object) $data;
	}
	
	/*
	 * 添加用户
	 * return 用户对象
	 */
	public function addUser($data) {
		$uid = $this->add($data);
		if (empty($uid)) {
			return false;
		}
	
		return $this->getObjectById($uid);
	}
	
	/*
	 * 查询结果处理
	 */
	/*
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$value['createtime'] = date('Y-m-d H:i:s', $value['createtime']);
			$value['avatar'] = getFileUrl($value['avatar']);
		}
	}*/
	
	/*
	 * 用户登陆
	 */
	public function login($account, $password) {
		$oUser = $this->getUser($account);
		if (empty($oUser)) {
			$this->error = '对不起，用户名不存在！';
			return false;
		}
		
		if ($oUser->password != md5($password)) {
			$this->error = '对不起，密码不正确！';
			return false;
		}
		
		$_SESSION['user'] = $oUser;
		return $oUser;
	}
	
	/*
	 * 用户注册
	 */
	public function register($account, $password) {
		$data = array(
			'account' => $account,
			'password' => $password,
		);
		
		if (!$this->checkData($data)) {
			return false;
		} else {
			$data = array(
				'appid' => $this->oApp->id,
				'account' => $account,
				'password' => md5($password),
				'type' => 1,//普通用户
				'reg_ip' => get_client_ip(),
				'createtime' => time(),
			);
			
			return $this->addUser($data);
		}
	}
	
	/*
	 * 用户退出
	 */
	public function logout() {
		$_SESSION['user'] = '';
	}
}



?>