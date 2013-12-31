<?php 

/**
 * User模型
 *
 */
class UserModel extends BaseModel {
	
	/*
	 * 验证form字段规则
	 */
	protected $aValidate = array(
								array('account', 'required', '请填写监理账号'),
								array('account','unique','账号已经存在'),
								array('nickname', 'unique', '昵称已经存在'),
								array('nickname','required','昵称没有填写'),
							);
	protected $aOptions = array(
		'type' => array(
			'1' => '普通用户',
			'2' => '设计师',
			'3' => '装修公司',
		)
	);
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$value['type'] = $this->getOptions('type', $value['type']);
			$value['status'] = $this->getOptions('status', $value['status']);
		}
	}
	
	
}



?>