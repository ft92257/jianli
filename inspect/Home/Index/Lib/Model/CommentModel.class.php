<?php 

class CommentModel extends BaseModel {
	
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
			$value['time'] = getFormatTime($value['createtime']);
			
			$user = D('User')->getFormat($value['uid']);
			$value['avatar'] = $user['avatar'];
			$value['nickname'] = $user['nickname'];
		}
	}

}



?>