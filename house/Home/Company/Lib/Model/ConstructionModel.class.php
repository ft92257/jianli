<?php 
/**
 * 	案例表相关：信息的查询、修改、添加、删除
 */
class ConstructionModel extends BaseModel {
	
	
	protected $aValidate = array(
			array('name', 'required', '请填写施工队名称！'),
	);
	
	protected $aOptions = array(
			'decoration_type' => array('1' => '家装', '2' => '工装'),
			'is_original' => array('1' => '原创', '0' => '转发'),
			'authorize' => array(
					'1' => '禁止匿名转载；禁止商业用途；禁止个人使用',
					'2' => '禁止匿名转载；禁止商业用途',
					'0' => '不限制用途',
			),
			'style' => array('1' => '地中海风格', '2' => '简约风格', '3' => '欧美风格'),
	);
	
	protected $formConfig = array(
			'name' => array('施工队名称', 'text'),
			'focus' =>array('队长头像','file','',array('thumbs' => '200-200',)),
			'info' => array('介绍', 'textarea'),
			'captain' => array('施工队长','text'),
			'memcount' => array('人数','text',array('int')),
			'specialty' => array('擅长','text'),
			'tags' => array('标签', 'tags'),
			'ord'=>array('排序值','text',array('int', '数值大的优先出现在首页')),
			array('', 'submit'),
	);
	
	protected $listConfig = array(
			'id' => '编号',
			'name' => '施工队名称',
			'info' => '介绍',
			'captain' => '施工队长',
			'specialty' => '擅长领域',
			'tags' => '标签',
			'memcount' => '人数',
			'createtime' => '添加时间',
			array('操作', array(array('/Construction_member/index/id/{id}/', '详细'),'edit', 'delete')),
	);
	
	
	
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$this->_auto_process_data($value);
			$value['createtime'] = date('Y-m-d H:i', $value['createtime']);
		}
	}
}
?>