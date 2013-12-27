<?php 

/**
 * User模型
 *
 */
class ManagerModel extends BaseModel {
	
	/*
	 * 验证form字段规则
	 */
	protected $aValidate = array(
		array('account','required', '请填写账号！'),
		array('password','required','请填写密码！'),
		array('account','unique','账号已经存在！')
	);
	
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
	public function login($account, $password) {
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
			if($oUser->type != 1){
				$this->error = '管理员才能登录';
				return false;
			}
	     	if($_SESSION['verify'] != md5($_POST['verify'])) {
  					 $this->error = '验证码错误！';
			}
			$_SESSION['user'] = $oUser;
			return $oUser;
		
	}
	/*
	 * 添加用户
	 * return 用户对象
	 */
	public function addManager($data) {
		$uid = $this->addData($data);//返回主键id;
		if (empty($uid)) {
			return false;
		}
	
		return $this->getObjectById($uid);//返回一行数据对象
	}
	
	/*
	 * 用户注册
	 */
	public function addnew($data) {
		if (!$this->checkData($data)) {
			return false;
		} else {
			$database = array(
							'type' => 2,//装修公司1总管理员3独立施工团队
						);
			
			return $this->addManager(array_merge($data, $database));
		}
	}
	/*更新公司信息
	 * 
	 */
	public function updatecompany(){
		
		if (!$this->checkData($data)) {
			return false;
		}  else {
			$database = array(
				'appid' => $this->oApp->id,
				'type' => 2,//装修公司1总管理员3独立施工团队
				'createtime' => time(),
			);
			return $this->updateManager(array_merge($data,$database));
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