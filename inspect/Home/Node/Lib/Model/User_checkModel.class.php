<?php
class User_checkModel extends BaseModel {
	
	public function __construct(){
			parent::__construct();
	}

	protected function _after_find(&$resultSet,$options) {
		$resultSet['check'] = D('Check')->where(array('id' =>$resultSet['aid']))->find();
		$resultSet['check_case'] = D('Check_case')->where(array('case' => $resultSet['case']))->find();
	}
}