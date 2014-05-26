<?php 
class AcceptanceModel extends BaseModel {
	
	protected $aOptions;
	
	public function __construct(){
		parent::__construct();
		$arr = D('Acceptance_case')->select();
		$dataConf = require CONF_PATH.'dataConfig.inc.php';
		$this->aOptions['step'] = $dataConf['step'];
		foreach($arr as $k => $v){
			$this->aOptions['case'][$v['id']] =  $v['name'];
		}
	}
	
	protected $formConfig = array(
		'step' => array('阶段', 'select'),
		'case' => array('分项', 'select'),
		'name' => array('项目', 'text'),
		'norms' => array('标准', 'textarea'),
		'tool' => array('量具', 'textarea'),
		'method' => array('方法', 'textarea'),
		array('', 'submit'),
	);
	
	protected $listConfig = array(
		'id' => '编号',
		'step' => '阶段',
		'case' => '分项',
		'uid' => '发布者',
		'name' => '项目',
		'norms' => '标准',
		'tool' => '量具',
		'method' => '方法',
		'createtime' => '添加时间',
		'status' => '状态',
		array('操作', array('edit', 'delete')),
	);
	
	protected $searchConfig = array(
		'name' => array('项目：', 'text_submit'),
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