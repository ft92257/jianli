<?php 

class InspectModel extends BaseModel {
	
	protected $aOptions = array(
	);
	
	protected $formConfig = array(
			'name' => array('名称', 'text'),
			'xqid' => array('小区', 'xiaoqu', '请输入小区名称'),
			'focus' => array('图片', 'file', '', array('thumbs' => '600-360')),
			'price' => array('价格', 'text', array('int', '元')),
			'begin_date' => array('开始日期', 'date'),
			'end_date' => array('结束日期', 'date'),
			'quota' => array('名额', 'text', array('int')),
			array('', 'submit'),
	);
	
	protected $listConfig = array(
			'id' => '编号',
			'name' => '名称',
			'price' => '价格',
			'quota' => '名额',
			'begin_date' => '开始日期',
			'end_date' => '结束日期',
			'createtime' => '添加时间',
			'status' => '状态',
			array('操作', array('edit', 'delete')),
	);
	
	protected $searchConfig = array(
			'status' => array('状态：', 'radio_list'),
			'name' => array('名称：', 'text_submit'),
			//'createtime' => array('选择时间：', 'date'),
	);
	
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$this->_auto_process_data($value);
			$value['begin_date'] = date('Y-m-d', $value['begin_date']);
			$value['end_date'] = date('Y-m-d', $value['end_date']);
			$value['createtime'] = date('Y-m-d H:i', $value['createtime']);
		}
	}
}
?>