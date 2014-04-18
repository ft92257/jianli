<?php 

class GoodsModel extends BaseModel {

	protected $aOptions = array(
		'type' => array('1' => '商品', '2' => '服务', '3' => '其他'),
		'discount' => array(
				'100' => '100',
				'95' => '95',
				'90' => '90',
				'85' => '85',
				'80' => '80',
				'75' => '75',
				'70' => '70',
				'60' => '60',
				'55' => '55',
				'50' => '50',
				'45' => '45',
				'40' => '40',
				'35' => '35',
				'30' => '30',
				'25' => '25',
				'20' => '20',
				'15' => '15',
				'10' => '10',
		),
	);
	
	protected $formConfig = array(
		'name' => array('名称', 'text'),
		'type' => array('类别', 'select'),
		'oprice' => array('原价', 'text', array('int', 'once', '元')),
		'discount' => array('折扣系数', 'select', array('once', '%')),
		'unit' => array('单位', 'text'),
		'focus' => array('图片', 'file', '', array('thumbs' => '80-80')),
		'info' => array('介绍', 'textarea'),
		array('', 'submit'),
	);
	
	protected $listConfig = array(
		'id' => '编号',
		'focus' => array('图片', array('img')),
		'name' => '名称',
		'type' => '类别',
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