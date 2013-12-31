<?php 
/*
 * 消费记录模型
 */
class MoneyModel extends BaseModel {
	
	protected $aValidate = array(
	);
	
	protected $aOptions = array(
			'type' => array('1' => '预约扣费',),
	);
	
	protected $formConfig = array(
	);
	
	protected $listConfig = array(
			'id' => '编号',
			'type' => '扣款项目',
			'money' => '金额',
			'name' => '说明',
			'createtime' => '扣款时间',
	);
	
	protected $searchConfig = array(
			'createtime' => array('选择时间：', 'date'),
	);
	
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$this->_auto_process_data($value);
			$value['createtime'] = date('Y-m-d H:i', $value['createtime']);
		}
	}

}
?>