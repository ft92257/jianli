<?php 
/**
 * 	公司基本资料相关：信息的查询、修改
 */
class CompanyModel extends BaseModel {
	protected $aValidate = array(
		array('telephone', 'mobile', '请输入正确格式的手机号！'),
	);
	
	protected $formConfig = array(
			'name' => array('公司名称：', 'span'),
			'logo' => array('公司logo：', 'file', '', array('thumbs' => '120-120')),
			'info' => array('公司介绍：', 'textarea'),
			'region' => array('服务区域：', 'text'),
			'contact' => array('联系人姓名：', 'text'),
			'telephone' => array('联系电话：', 'text', array('int')),
			array('', 'submit'),
	);
}

?>