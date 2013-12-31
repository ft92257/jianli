<?php 
/*
 *按今天，本周，本月，本季度，本年，全部查询评论回复数据
 * $usertype 代表查询条件  $cid 代表 公司id
 *返回array $data 查询条件 数组
 */
class ScoreModel extends BaseModel {
	protected $aValidate = array(
	);
	
	protected $aOptions = array(
			'isreply' => array('0' => '未回复', '1' => '已回复'),
	);
	
	protected $formConfig = array(
	);
	
	protected $listConfig = array(
			'id' => '编号',
			'case_name' => '项目名称',
			//'case_begin' => array('项目开始'),
			//'case_end' => array('项目结束'),
			'score_skill' => '技能评分',
			'score_service' => '服务评分',
			'comment' => '评论',
			'reply' => '回复',
			'additional' => '追评',
			'additional_reply' => '追评回复',
			'createtime' => '评论时间',
			array('操作', array(array('/Score/reply/id/{id}', '回复', array('checked' => '{isreply}')))),
	);
	
	protected $searchConfig = array(
			'isreply' => array('回复类型：', 'radio_list'),
			'case_name' => array('作品名称：', 'text'),
			'createtime' => array('选择时间：', 'date'),
	);
	
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			//$this->_auto_process_data($value);
			$value['createtime'] = date('Y-m-d H:i', $value['createtime']);
		}
	}
}
?>