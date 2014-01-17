<?php 
/**
 * 	案例表相关：信息的查询、修改、添加、删除
 */
class CaseModel extends BaseModel {
	protected $aValidate = array(
			array('name', 'required', '请填写案例名称！'),
	);
	
	protected $aOptions = array(
		'schedule' => array('1'=>'隐蔽','2'=>'泥木','3'=>'油漆','4'=>'安装','5'=>'软装','6'=>'竣工'),
		'style' => array('1' => '地中海风格', '2' => '简约风格', '3' => '欧美风格'),
	);
	protected $formConfig = array(
		'name' => array('案列名称', 'text'),
		'fee_info' => array('装修费用说明', 'textarea'),
		'property' => array('楼盘','text'),
        'schedule' => array('阶段','select',array('all')),
		'jlgroup' => array('监理团队','select',array('all')),
		'design' => array('设计团队','select',array('all')),
		'consid' =>array('施工队团队','select',array('all')),
		'province' => array(array('所在地区', 'BEGIN'), 'province'),
		'city' => array(array('', 'APPEND'), 'city'),
		'county' => array(array('', 'APPEND'), 'county'),
		'town' => array(array('', 'END'), 'town'),
		'place' => array('详细地址','text',array('long')),
		'housetype' => array('户型', 'text', '例：3室1厅'),
		'style' => array('风格', 'select', array('all')),
		'tags' => array('标签', 'tags'),
		'cost' => array('费用', 'text', array('int', '元')),
        'ord'=>array('排序值','text',array('int', '数值大的优先出现在首页')),
		array('', 'submit'),
	);
	
	protected $listConfig = array(
			'id' => '编号',
			'name' => '案例名称',
			'place'=> '详细地址',
			'property' => '楼盘',
			'schedule' => '当前阶段',
			'housetype' => '户型',
			'style' => '风格',
			'cost' => '费用(元)',
			'createtime' => '添加时间',
			array('操作', array('edit', 'delete', 'picture')),
	);
	
	protected $searchConfig = array(
			'name' => array('案列名称：', 'text'),
			'createtime' => array('选择时间：', 'date'),
	);
	public function setGroups() {
		$this->aOptions['jlgroup'] = D('Case_team')->where(array('cid'=>$this->oCom->id,'type'=>1))->getField('id,name');
		$this->aOptions['design'] = D('Case_team')->where(array('cid'=>$this->oCom->id,'type'=>2))->getField('id,name');
		$this->aOptions['consid'] = D('Construction')->where(array('cid'=>$this->oCom->id))->getField('id,name');
	}
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$this->_auto_process_data($value);
			$value['createtime'] = date('Y-m-d H:i', $value['createtime']);
		}
	}
}
?>