<?php
class Check_caseModel extends BaseModel {
	
	public function __construct(){
			parent::__construct();
	}

	protected function _after_find(&$resultSet,$options) {
		//$resultSet['focus'] = getFileUrl($resultSet['focus'], '80-80');
		$this->oUser->id = 1;
		$resultSet['check_no_c'] = D('User_check')->where(array('step' => $resultSet['step'], 'uid' => $this->oUser->id, 'result' => 1, 'case'=>$resultSet['id']))->count();
		$resultSet['check_pass_c'] = D('User_check')->where(array('step' => $resultSet['step'], 'uid' => $this->oUser->id, 'result' => 2, 'case'=>$resultSet['id']))->count();
		$resultSet['check_nopass_c'] = D('User_check')->where(array('step' => $resultSet['step'], 'uid' => $this->oUser->id, 'result' => 3, 'case'=>$resultSet['id']))->count();
	}
}