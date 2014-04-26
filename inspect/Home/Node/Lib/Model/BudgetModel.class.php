<?php 

/**
 * 预算模型
 *
 */
class BudgetModel extends BaseModel {
	
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
			//$value['createtime'] = date('Y-m-d H:i:s', $value['createtime']);
			//$value['avatar'] = getFileUrl($value['avatar']);
		}
	}
	
}



?>