<?php
class House_typeModel extends BaseModel {
	
	public function __construct(){
			parent::__construct();
	}

	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			//$value['focus'] = getFileUrl($value['focus'], '80-80');
		}
	}
}