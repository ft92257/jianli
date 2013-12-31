<?php 
/**
 * 	活动模型
 */
class ActiveModel extends BaseModel {
	protected $aValidate = array(
			array('title', 'required', '请填写活动标题！'),
	);
	
	
	protected $formConfig = array(
			'title' => array('活动标题', 'text'),
			'organizer' => array('主办方', 'text'),
			'focus' =>array('活动焦点图','file','',array('thumbs' => '120-120')),
			'caseid' =>array('选择活动关联案列','select',array('all')),
			'spot' => array('亮点','text',array('long')),
			'address' => array('详细地址','text',array('long')),
			'info' => array('详细信息', 'textarea'),
			'observe_date' => array('开始时间','date'),
			'observe_end' => array('结束时间','date'),
			array('', 'submit'),
	);
	public function getCase(){
		    $this->aOptions['caseid'] = D('Case')->where(array('cid'=>$this->oCom->id))->getField('id,name');
	}
	protected $listConfig = array(
			'id' => '编号',
			'title' => '标题',
			'observe_date' => '开始日期',
			'observe_end' => '结束日期',
			'organizer' => '主办方',
			'spot'=> '亮点',
			'createtime' => '添加时间',
			array('操作', array('edit', 'delete')),
	);
	
	protected $searchConfig = array(
			'title' => array('活动标题：', 'text_submit'),
	);
	
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$value['observe_date'] = date('Y-m-d H:i', $value['observe_date']);
			$value['observe_end'] = date('Y-m-d H:i', $value['observe_end']);
			$value['createtime'] = date('Y-m-d H:i', $value['createtime']);
		}
	}
	
}
?>