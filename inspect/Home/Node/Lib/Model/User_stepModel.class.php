<?php
class User_stepModel extends BaseModel {

	protected function _after_find(&$resultSet,$options) {
		$this->oUser->id = 1;
		//$value['focus'] = getFileUrl($value['focus']);
		$resultSet['begin_time'] = date("Y-m-d", $resultSet['begin_date']); 
		$resultSet['end_time'] = date("Y-m-d", $resultSet['end_date']-1); 
		$resultSet['question_c'] = D('Question')->where(array('step' => $resultSet['step']))->count();
		
		$resultSet['aceptance_no_c'] = D('User_report')->where(array('step' => $resultSet['step'], 'uid' => $this->oUser->id, 'result' => 1))->count();
		$resultSet['aceptance_pass_c'] = D('User_report')->where(array('step' => $resultSet['step'], 'uid' => $this->oUser->id, 'result' => 2))->count();
		$resultSet['aceptance_nopass_c'] = D('User_report')->where(array('step' => $resultSet['step'], 'uid' => $this->oUser->id, 'result' => 3))->count();
		
		$resultSet['check_no_c'] = D('User_check')->where(array('step' => $resultSet['step'], 'uid' => $this->oUser->id, 'result' => 1))->count();
		$resultSet['check_pass_c'] = D('User_check')->where(array('step' => $resultSet['step'], 'uid' => $this->oUser->id, 'result' => 2))->count();
		$resultSet['check_nopass_c'] = D('User_check')->where(array('step' => $resultSet['step'], 'uid' => $this->oUser->id, 'result' => 3))->count();
		
		$resultSet['cycle'] = round(($resultSet['end_date'] - $resultSet['begin_date'])/86400);
	}
}