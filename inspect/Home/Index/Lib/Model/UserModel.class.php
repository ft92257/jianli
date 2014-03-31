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
		array('password', 'required', '请填写密码！'),
		array('repassword', 'repeat', '两次密码输入不一致！', 'password'),
	);
	
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
	
	/*
	 * 用户注册
	 */
	public function register($account, $password) {
		$request = array(
			'account' => getRequest('account'),
			'password' => getRequest('password'),
			'repassword' => getRequest('repassword'),
		);

		if (!$this->checkData($request)) {
			return false;
		} else {
			$data = array(
				'appid' => $this->oApp-id,
				'account' => $request['account'],
				'password' => md5($request['password']),
				'type' => 1,//普通用户
				'reg_ip' => get_client_ip(),
				'createtime' => time(),
			);
			
			$uid = $this->add($data);
			if (!$uid) {
				return false;
			}
			
			//同步添加旧版数据
			$member = array(
						'uid' => $uid,
						'nickname' => $data['account'],
					);
			D('Yjl_member')->add($member);
			
			return $uid;
		}
	}
	
	public function getFormat($id) {
		$user = $this->getById($id);
		$user['avatar'] = getFileUrl($user['avatar'], '25-25', 'avatar.jpg');
		$user['nickname'] = $user['nickname'] ? $user['nickname'] : $user['account'];
		
		return $user;
	}
}



?>