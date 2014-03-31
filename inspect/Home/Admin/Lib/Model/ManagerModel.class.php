<?php 

/**
 * User模型
 *
 */
class ManagerModel extends BaseModel {
	
	/*
	 * 根据帐号获取用户对象
	 */
	public function getUser($account) {
		$data = $this->where(array('account'=>$account, 'appid'=>$this->oApp->id, 'status' => 0,))->find();
		return empty($data)? null : (object) $data;
	}
	
	/*
	 * 用户登陆
	 */
	public function login($account, $password, $verify) {
		$data = array('account'=>$account,'password'=>$password);
		$oUser = $this->getUser($account);//这条记录对象
		if (empty($oUser)) {
			$this->error = '对不起,用户名不存在！';
			return false;
		}
		if ($oUser->password != md5($password)) {
			$this->error = '密码不正确！';
			return false;
		}
	    if($_SESSION['verify'] != md5($verify)) {
  			 $this->error = '验证码错误！';
		}
		$_SESSION['admin_user'] = $oUser;
		return $oUser;
	}
	
	/*
	 * 用户退出
	 */
	public function logout() {
		unset($_SESSION['admin_user']);
	}
}



?>