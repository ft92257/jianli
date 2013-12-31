<?php 
/**
 * 	案例表相关：信息的查询、修改、添加、删除
 */
class Construction_memberModel extends BaseModel {
	protected $aValidate = array(
			array('name', 'required', '请填写姓名！'),
	);
	

	
	protected $formConfig = array(
			'name' => array('队员姓名', 'text'),
			'info' => array('介绍', 'textarea'),
			'pic' => array('队员头像','file','',array('thumbs' => '80-80')),
			array('', 'submit'),
	);
	
	protected $listConfig = array(
			'id' => '编号',
			'name' => array('队员姓名'),
			'info' => array('介绍'),
			'pic' =>array('头像',array('img')),
			'createtime' => array('创建时间'),
			//'pic' => array('队员头像','file','',array('thumbs' => '80-80')),
			array('操作', array('edit', 'delete')),
	);
	
	
	
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$this->_auto_process_data($value);
			$value['pic'] = getFileUrl($value['pic'], '80-80');
			$value['createtime'] = date('Y-m-d H:i', $value['createtime']);
		}
	}
}
?>
