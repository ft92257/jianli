<?php 

/**
 * 施工队成员模型
 *
 */
class Construction_memberModel extends BaseModel {
	
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
			$value['pic'] = getFileUrl($value['pic']);
		}
	}
	
	/*
	 * 获取所有施工队成员
	 */
	public function getAll($consid) {
		$condition = array(
			'consid' => $consid,
		);
		return $this->where($condition)->select();
	}
}



?>