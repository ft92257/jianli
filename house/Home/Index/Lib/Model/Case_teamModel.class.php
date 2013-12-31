<?php 

/**
 * 样板工地 监理，设计团队模型
 *
 */
class Case_teamModel extends BaseModel {
	
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
	
}



?>