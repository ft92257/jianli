<?php 

class ReminderModel extends BaseModel {
	
	public function __construct(){
		parent::__construct();
	}
	
	protected $formConfig = array(
		'type' => array('阶段', 
				'<div id="div_type"></div>
				<input type="hidden" id="hide_type" name="type" value="{type}"/>'
				),
		'content' => array('内容', 'textarea'),
		array('', 'submit'),
	);
	
	protected $listConfig = array(
		'id' => '编号',
		'type' => '阶段/节点',
		'uid' => '发布者',
		'content' => '内容',
		'createtime' => '添加时间',
		'status' => '状态',
		array('操作', array('edit', 'delete')),
	);
	
	protected $searchConfig = array(
		'content' => array('关键字：', 'text_submit'),
	);
	
	public function strFormat($str){
		preg_match_all("/\[(\d*)\]/", $str , $res);
		return $res[1];
	}
	
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$this->_auto_process_data($value);
			$value['uid'] = D('User')->where(array('id' =>$value['uid']))->getField('account');
			$str = "";
			$arrType = $this->strFormat($value['type']);
			foreach($arrType as $v){
				$name = D('Node')->where(array('id' => $v))->getField('name');
				$str .= "-{$name}";
			}
			$value['type'] = substr($str, 1);
			$value['createtime'] = date('Y-m-d H:i', $value['createtime']);
			//$value['focus'] = getFileUrl($value['focus'], '80-80');
		}
	}
}
?>