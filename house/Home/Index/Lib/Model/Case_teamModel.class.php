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
	protected function _after_find(&$data,$options) {
		$data['cid'] = D('Company')->getNameById($data['cid']);
	}
}



?>