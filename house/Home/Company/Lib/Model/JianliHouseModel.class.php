<?php 
/**
 * 	易监理样板房模型
 */
class JianliHouseModel extends BaseModel {
	protected $dbName = 'jianliapp';
	protected $trueTableName = 'tb_case';
	
	protected $aValidate = array(
	);
	
	protected $aOptions = array(
	);
	
	protected $formConfig = array(
	);
	
	protected $listConfig = array(
			'id' => '编号',
			'name' => '名称',
			'createtime' => '添加时间',
			array('操作', array(array('/House/bind/hid/[hid]/id/{id}', '绑定', array('checked' => "'{id}'=='[jl_id]'")))),
	);
	
	protected $searchConfig = array(
	);
	
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$value['createtime'] = date('Y-m-d H:i', $value['createtime']);
		}
	}
}
?>