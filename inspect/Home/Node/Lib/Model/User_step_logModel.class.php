<?php
class User_step_logModel extends BaseModel {

	protected function _after_find(&$resultSet,$options) {
		$this->oUser->id = 1;
		//$value['focus'] = getFileUrl($value['focus']);
		$resultSet['begin_time'] = date("Y-m-d", $resultSet['begin_date']); 
		$resultSet['end_time'] = date("Y-m-d", $resultSet['end_date']-1); 
		$resultSet['node_name'] = D('Node')->where(array('id' => $resultSet['step']))->getField('name');
		$where = array('uid' => $this->oUser->id, 'expire' => array('gt', time()), 'reminder_time' => array('between', $resultSet['begin_date'].",".($resultSet['end_date']-1)));
		$resultSet['memo_c'] = D('Memo')->where($where)->count();
	}
}