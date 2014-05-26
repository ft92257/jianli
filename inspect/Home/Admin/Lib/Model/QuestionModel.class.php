<?php 

class QuestionModel extends BaseModel {
	
	protected $aOptions = array();
	
	public function __construct(){
		parent::__construct();
		$dataConf = require CONF_PATH.'dataConfig.inc.php';
		$this->aOptions['step'] = $dataConf['step'];
	}
	
	protected $formConfig = array(
		'step' => array('阶段', 'select'),
		'title' => array('词条', 'text'),
		'content' => array('内容', 'richtext'),
		array('', 'submit'),
	);
	
	protected $listConfig = array(
		'id' => '编号',
		'title' => '词条',
		'step' => '阶段',
		'uid' => '发布者',
		'createtime' => '添加时间',
		'status' => '状态',
		array('操作', array('edit', 'delete')),
	);
	
	protected $searchConfig = array(
		'词条' => array('词条', 'text_submit'),
		'content' => array('关键字：', 'text_submit')
	);
	
	protected function _after_select(&$resultSet,$options) {
		
		foreach ($resultSet as &$value) {
			$this->_auto_process_data($value);
			$value['uid'] = D('User')->where(array('id' =>$value['uid']))->getField('account');
			$value['createtime'] = date('Y-m-d H:i', $value['createtime']);
			//$value['focus'] = getFileUrl($value['focus'], '80-80');
		}
	}
}
?>