<?php 


/**
 * Session模型
 *
 */
class Session {
	
	protected $oDb;
	
	public function __construct($oDb) {
		$this->oDb = $oDb;
	}
	
	/*
	 * 验证key是否有效，有效则返回uid，否则返回false
	 */
	 /*
	public function check() {
		$key = $_COOKIE['SN_KEY'];
		if (empty($key)) {
			return false;
		}
		
		//$cSql = "SELECT * FROM tb_session WHERE key = '$key'";
		//$data = $this->oDb->fetchFirstArray($cSql);
		$data = $this->where(array('key'=>$key))->find();
		if (empty($data)) {
			return false;
		}
		
		//超过2小时过期
		if (($data['expire'] + 7200) < time()) {
			return false;
		} 
		//ip变化
		if (get_client_ip() != $data['ip']) {
			return false;
		}
		
		//更新有效期
		$this->where(array('id'=>$data['id']))->data(array('expire' => time() + 7200))->save();
		
		return $data['uid'];
	}*/
	
	public function getUser($uid) {
		$cSql ="SELECT * FROM tb_user WHERE id = '$uid'";
		$aUser = $this->oDb->fetchFirstArray($cSql);
		
		return (object) $aUser;
	}
	
	
	/*
	 * 生成sessionKey
	 */
	public function setKey($uid) {
		$oUser = $this->getUser($uid);
	
		$key = $oUser->id . '|' . $oUser->account . '|' . $oUser->password . '|' . time() . mt_rand();
		$key = md5($key);
		
		$data = array(
			'key' => $key,
			'expire' => time() + 7200,
			'ip' => get_client_ip(),
		);
		
		//$session = $this->getById($oUser->id, 'uid');
		$cSql = "SELECT * FROM tb_session WHERE uid = '$uid'";
		$session = $this->oDb->fetchFirstArray($cSql);
		if (empty($session)) {
			$data['appid'] = $this->oApp->id;
			$data['uid'] = $oUser->id;
			$data['createtime'] = time();
			$this->oDb->insert('tb_session', $data);
		} else {
			//$this->where(array('uid' => $oUser->id))->data($data)->save();
			$this->oDb->update('tb_session', $data, "uid = '$uid'");
		}
		
		$expire = time() + 36000;
		setcookie('SN_KEY', $key, $expire, '/');
		
		return $key;
	}
	
	/*
	 * 退出登录
	 */
	public function destroy($uid) {
		if ($uid) {
			$this->oDb->update('tb_session', array('expire' => 0), "uid = '$uid'");
			//$this->where(array('uid' => $uid))->data(array('expire' => 0))->save();
		}
	}
}



?>