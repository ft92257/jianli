<?php 

class GoodsModel extends BaseModel {

	protected $aOptions = array(

	);
	
	protected $formConfig = array(
		'name' => array('名称', 'text'),
		'price' => array('价格', 'text', array('int', 'once', '元')),
		'unit' => array('单位', 'text'),
		'focus' => array('图片', 'file', '', array('thumbs' => '80-80')),
		'info' => array('介绍', 'textarea'),
		array('', 'submit'),
	);
	
	protected $listConfig = array(
		'id' => '编号',
		'focus' => array('图片', array('img')),
		'name' => '名称',
		'price' => '价格(元)',
		'unit' => '单位',
		'createtime' => '添加时间',
		'status' => '状态',
		array('操作', array('edit')),
	);
	
	protected $searchConfig = array(

	);
	
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$this->_auto_process_data($value);
			$value['createtime'] = date('Y-m-d H:i', $value['createtime']);
			$value['focus'] = getFileUrl($value['focus'], '80-80');
		}
	}

}
?>