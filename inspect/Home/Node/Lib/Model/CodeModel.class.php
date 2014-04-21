<?php 


/**
 * 手机验证码
 *
 */
class CodeModel extends BaseModel {
	/*
	 * 查询结果处理
	*/
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$value['focus'] = getFileUrl($value['focus']);
		}
	}
	
	public function getcode($mobile) {
		//每个号码每天限制5次, 最短间隔60秒
		$where = array(
					'mobile' => $mobile,
					'expired' => array('egt', strtotime(date('Y-m-d'))),
					'status' => array('in', '0,1'),
				);
		$count = $this->where($where)->count();
		if ($count >= 5) {
			return '';
		}
		//存在未过期验证码，则不能再次获取
		$aCode = $this->where($where)->order('createtime DESC')->find();
		if ($aCode['expired'] >= time()) {
			return '';
		}
		
		$code = mt_rand(100001,999999);
		$data = array(
			'appid' => $this->oApp->id,
			'mobile' => $mobile,
			'code' => $code,	
			'expired' => time() + 60,	
		);
		
		$this->add($data);
		
		return $code;
	}
	
	public function check($mobile, $code) {
		$aWhere = array(
			'mobile' => $mobile,
			'code' => $code,
			'status' => 0,
		);
		
		$data = $this->where($aWhere)->order('createtime DESC')->find();
		
		if (empty($data) || $data['expired'] < time()) {
			return false;
		} else {
			$this->data(array('status' => 1))->where($aWhere)->save();
			return true;
		}
	}
}



?>