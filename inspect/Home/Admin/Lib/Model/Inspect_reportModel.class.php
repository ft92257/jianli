<?php 
/**
 * 后台用户登录
 */
class Inspect_reportModel extends BaseModel {
	protected $aOptions = array(
		
	);
	/*
	 * 验证form字段规则
	*/
	protected $aValidate = array(

	);

	protected $formConfig = array(
		'name' => array('名称', 'text'),
		array('', 'submit'),
	);
	protected $listConfig = array(
			'id' => '编号',
			'name' => '名称',
			'createtime' => '添加时间',
			'status' => '状态',
			array('操作', array('edit', 'delete', 'picture')),
	);
	protected $searchConfig = array(
			'status' => array('状态：', 'radio_list'),
			'name' => array('名称：', 'text_submit'),
	);
	
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$this->_auto_process_data($value);
			$value['createtime'] = date('Y-m-d H:i', $value['createtime']);
		}
	}
}



?>
