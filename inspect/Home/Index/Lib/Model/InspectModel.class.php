<?php 

class InspectModel extends BaseModel {
	
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
			$value['end_date'] = date('m/d/Y H:i:s', $value['end_date']);
			$value['focus'] = getFileUrl($value['focus']);
		}
	}
	
}



?>