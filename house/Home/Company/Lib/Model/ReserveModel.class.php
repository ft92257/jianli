<?php 
/*
 * 预约模型
 */
class ReserveModel extends BaseModel {
	protected $aValidate = array(
	);
	
	protected $aOptions = array(
		'handle' => array('0' => '未处理', '1' => '已处理'),
		'isclinch' => array('0' => '未成交', '1' => '已成交'),
		'type' => array('1' => '公司', '2' => '设计师'),
	);
	
	protected $formConfig = array(
			'id' => array('编号：', 'span'),
			'name' => array('业主姓名：', 'span'),
			'handle' => array('是否处理：', 'select'),
			'feedback' => array('反馈信息', 'textarea', array('once')),
			'reply' => array('管理员回复：', 'span'),
			array('', 'submit'),
	);
	
	protected $listConfig = array(
			'id' => '编号',
			'type' => '类型',
			'name' => '业主姓名',
			'telephone' => '联系电话',
			'handle' => '是否处理',
			'isclinch' => '是否成交',
			'createtime' => '预约时间',
			array('操作', array(array('/Reserve/feedback/id/{id}', '反馈'))),
	);
	
	protected $searchConfig = array(
			'handle' => array('是否处理：', 'radio_list'),
			'name' => array('业主姓名：', 'text'),
			'telephone' => array('联系电话：', 'text'),
			'createtime' => array('预约时间：', 'date'),
	);
	
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$this->_auto_process_data($value);
			$value['createtime'] = date('Y-m-d H:i', $value['createtime']);
			$value['telephone'] = $value['charged'] ? $value['telephone'] : '*';
		}
	}
	
	/*
	 * 获取未处理的预约单数量
	 */
	public function getUndealCount($basewhere) {
		$where = array('handle' => 0);
		$where = array_merge($where, $basewhere);
		return $this->where($where)->count();
	}

}
?>