<?php 


class CertificationModel extends BaseModel {
	
	protected $aOptions = array(
		'type' => array(
			'1' => '施工队',
			'2' =>  '样板房',
		),
		'speed'=>array(
				'1' => '已提交',
				'2' =>  '正在审核',
				'3' => '审核通过',
				'4' =>  '审核未通过',
		),
	);
	/*
	 * 查询结果处理
	 */
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$value['type'] = $this->getOptions('type', $value['type']);
			$value['speed'] = $this->getOptions('speed', $value['speed']);
		}
	}

}



?>