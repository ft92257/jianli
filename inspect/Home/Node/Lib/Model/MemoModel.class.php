<?php
class MemoModel extends BaseModel {
	
	public function __construct(){
			parent::__construct();
	}

	protected function _after_find(&$resultSet,$options) {
		//$resultSet['focus'] = getFileUrl($resultSet['focus'], '80-80');
	}
}