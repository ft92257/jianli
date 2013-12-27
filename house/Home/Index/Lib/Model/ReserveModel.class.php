<?php 

/**
 * 预约模型
 *
 */
class ReserveModel extends BaseModel {
	
	/*
	 * 验证form字段规则
	 */
	protected $aValidate = array(
	);
	
	/*
	 * 查询结果处理
	 */
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$name = substr($value['name'],0,3);
			if($value['sex'] == '1'){
			$value['name'] = $name.'先生';
			}else {
				$value['name'] = $name.'女士';
			}
				$pattern = "/(1\d{1,2})\d\d(\d{0,3})/";
				$replacement = "\$1****\$3";
				$value['telephone'] = preg_replace($pattern, $replacement, $value['telephone']);
			//$value['avatar'] = getFileUrl($value['avatar']);
		}
	}
	
	/*
	 * 获取有效已预约次数
	 */
	public function getValidCount($uid) {
		//money > 0
		$where = array(
					'uid' => $uid,
					'money' => array('gt', 0),
				);
		return $this->where($where)->count();
	}
	/*
	 * 获取已预约人数
	 */
	public function getHasReserve($type,$target,$limit){
		$where = array(
				'type' =>3,
				'target'=>$target,
				'money'=> array('gt',0),
		);
		$count = $this->where($where)->count();
		$aReserve = $this->where($where)->limit($limit)->select();
		$aReserve['count'] = $count;
		return $aReserve;
		
	}
	/*
	 * 是否已有有效扣款记录
	 */
	public function checkValidUnique($uid, $type, $target) {
		//记录不存在返回true
		$where = array(
					'uid' => $uid,
					'type' => $type,
					'target' => $target,
				);
		
		$ret = $this->where($where)->find();
		
		return empty($ret) ? true : false;
	}
}



?>