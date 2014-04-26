<?php 

class BudgetModel extends BaseModel {

	protected $aOptions = array(

	);
	
	protected $formConfig = array(
		'name' => array('名称', 'text'),
		'price' => array('单价', 'text', array('int', '元')),
		'count' => array('数量', 'text', array('int')),
		'estimate' => array('预算', 'text', array('int', 'once', '元')),
		'realfee' => array('实际费用', 'text', array('int', '元')),
		'info' => array('介绍', 'textarea'),
		array('', 'submit'),
	);
	
	protected $listConfig = array(
		'id' => '编号',
		'name' => '名称',
		'price' => '单价',
		'count' => '数量',
		'estimate' => '预算',
		'realfee' => '实际费用',
		'createtime' => '添加时间',
		array('操作', array('edit')),
	);
	
	protected $searchConfig = array(

	);
	
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$this->_auto_process_data($value);
			$value['createtime'] = date('Y-m-d H:i', $value['createtime']);
		}
	}

}
?>