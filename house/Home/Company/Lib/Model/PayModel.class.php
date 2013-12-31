<?php 
/*
 * 充值记录模型
 */
class PayModel extends BaseModel {
	
	protected $aValidate = array(
	);
	
	protected $aOptions = array(
			'result' => array('1' => '成功', '-1' => '失败', '0' => '未处理'),
	);
	
	protected $formConfig = array(
	);
	
	protected $listConfig = array(
			'id' => '编号',
			'money' => '金额',
			'out_trade_no' => '订单号',
			'result' => '结果',
			'createtime' => '充值时间',
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