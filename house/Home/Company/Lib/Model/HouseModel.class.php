<?php 
/**
 * 	样板房模型
 */
class HouseModel extends BaseModel {
	protected $aValidate = array(
			array('name', 'required', '请填写样板房名称！'),
	);
	
	protected $aOptions = array(
			'decoration_type' => array('1' => '家装', '2' => '工装'),
			'style' => array('1' => '地中海风格', '2' => '简约风格', '3' => '欧美风格'),
			'step' => array('1' => '隐蔽', '2' => '泥木', '3' => '油漆', '4' => '安装', '5' => '软装', '6' => '竣工'),
	);
	
	protected $formConfig = array(
			'name' => array('样板房名称', 'text'),
			'decoration_type' => array('装修类型', 'select'),
			'step' => array('阶段', 'select', array('all')),
			'province' => array(array('所在地区', 'BEGIN'), 'province'),
			'city' => array(array('', 'APPEND'), 'city'),
			'county' => array(array('', 'APPEND'), 'county'),
			'town' => array(array('', 'END'), 'town'),
			'community' => array('地址', 'text', array('long')),
			'housetype' => array('户型', 'text', '例：3室1厅'),
			'style' => array('风格', 'select', array('all')),
			'design_fee' => array('设计费用', 'text', array('int', '元')),
			'contract_amount' => array('合同金额', 'text', array('int', '元')),
			'material' => array('主材费用', 'textarea', '每项一行，最多6项。例：瓷砖：￥30/㎡'),
			'tags' => array('标签', 'tags'),
			'recommend' => array('推荐（排序值）', 'text', array('int', '数值大的优先出现在首页')),
			'hot' => array('热门（排序值）', 'text', array('int', '数值大的优先出现在热门栏目')),
			array('', 'submit'),
	);
	
	protected $listConfig = array(
			'id' => '编号',
			'name' => '名称',
			'decoration_type' => '装修类型',
			'step' => '阶段',
			'housetype' => '户型',
			'style' => '风格',
			'design_fee' => '设计费用(元)',
			'createtime' => '添加时间',
			array('操作', array('edit', 'delete', 'picture', array('/House/cons/id/{id}', '施工'))),
	);
	
	protected $searchConfig = array(
			'name' => array('样板房名称：', 'text_submit'),
	);
	
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$this->_auto_process_data($value);
			$value['createtime'] = date('Y-m-d H:i', $value['createtime']);
		}
	}
}
?>