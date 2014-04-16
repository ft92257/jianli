<?php 

class OrdersModel extends BaseModel {

	protected $aOptions = array(

	);
	
	protected $formConfig = array(
	);
	
	protected $listConfig = array(
		'id' => '编号',
		'content' => '内容',
		'original_price' => '原价',
		'last_price' => '优惠价',
		'createtime' => '添加时间',
		'status' => '状态',
		//array('操作', array('edit')),
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