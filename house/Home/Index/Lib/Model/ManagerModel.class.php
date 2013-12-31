<?php 

/**
 * 公司帐号模型
 *
 */
class ManagerModel extends BaseModel {
	
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
			//$value['avatar'] = getFileUrl($value['avatar']);
		}
	}
	
	/*
	 * 扣款
	 */
	public function charge($id, $money) {
		if ($money == 0) {
			//重复提交，不扣款
			return false;
		}

		$aManager = $this->getById($id);
		if ($aManager['money'] < $money) {
			return false;
		}
		$set = array(
			'money' => $aManager['money'] - $money,
		);

		return $this->where(array('id' => $id))->data($set)->save();
	}
}



?>