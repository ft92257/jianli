<?php 
/**
 * 设计师模型
 */
class User_designerModel extends BaseModel {
	
	protected $aValidate = array(
	);
	
	protected $formConfig = array(
	);
	
	/*
	 * 获取公司所有设计师
	 */
	public function getAll() {
		$where = array(
					'cid' => $this->oCom->id,
				);
		
		return $this->where($where)->select();
	}
	
	protected function _after_select(&$resultSet,$options) {
		$User = D('User');
		foreach ($resultSet as &$value) {
			$aUser = $User->getById($value['uid']);
			$value['account'] = $aUser['account'];
			$value['realname'] = $aUser['realname'];
			$value['nickname'] = $aUser['nickname'];
		}
	}
	
}



?>