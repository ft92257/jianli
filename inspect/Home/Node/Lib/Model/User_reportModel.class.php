<?php
class User_reportModel extends BaseModel {
	
	public function __construct(){
			parent::__construct();
	}

	protected function _after_find(&$resultSet,$options) {
		$resultSet['acceptance'] = D('Acceptance')->where(array('id' =>$resultSet['aid']))->find();
	}
}