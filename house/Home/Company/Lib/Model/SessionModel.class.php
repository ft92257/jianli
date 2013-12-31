<?php 


/**
 * Session模型
 *
 */
class SessionModel extends BaseModel {
	
	/*
	 * 验证key是否有效，有效则返回uid，否则返回false
	 */
	public function check() {
		$key = $_COOKIE['SN_KEY'];
		if (empty($key)) {
			return false;
		}
		
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
	}
	
	/*
	 * 生成sessionKey
	 */
	public function setKey($oUser) {
		$key = $oUser->id . '|' . $oUser->account . '|' . $oUser->password . '|' . time() . mt_rand();
		$key = md5($key);
		
		$data = array(
			'key' => $key,
			'expire' => time() + 7200,
			'ip' => get_client_ip(),
		);
		
		$session = $this->getById($oUser->id, 'uid');
		if (empty($session)) {
			$data['appid'] = $this->oApp->id;
			$data['uid'] = $oUser->id;
			$data['createtime'] = time();
			$this->add($data);
		} else {
			$this->where(array('uid' => $oUser->id))->data($data)->save();
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
			$this->where(array('uid' => $uid))->data(array('expire' => 0))->save();
		}
	}
}



?>