<?php 
	/**
	 * 	团队模型
	 */

	class Case_teamModel extends BaseModel {
		protected $aValidate = array(
				array('name', 'required', '请填写团队名称！'),
		);
		protected $aOptions = array(
				
				'type' => array('1' => '监理团队', '2' =>'设计团队' ),
		);
		
		protected $formConfig = array(
				'name' => array('团队名称', 'text'),
				'company' => array('公司名称', 'text'),
				'type' => array('类型', 'radio'),
				'speciality' => array('擅长领域:','text',array('long')),
				'info' => array('详细信息:', 'textarea'),
				array('', 'submit'),
		);
		
		protected $listConfig = array(
				'id' => '编号:',
				'name' => '团队名称:',
				'company' => '所属公司名称:',
				'speciality' => '擅长领域:',
				'info' => '简介:',
				'createtime' => '添加时间',
				array('操作', array('edit', 'delete')),
		);
		
		protected $searchConfig = array(
				'name' => array('团队名称：', 'text_submit'),
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