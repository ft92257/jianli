<?php 

/**
 * 样板工地图片模型
 *
 */
class Case_picModel extends BaseModel {
	
	/*
	 * 验证form字段规则
	 */
	protected $aValidate = array(
	);
	
	/*
	 * 查询结果处理
	 */
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			//缩略图
			$value['thumb'] = getFileUrl($value['fid'], '80-80');
			$value['pic'] = getFileUrl($value['fid']);
		}
	}
	
	/*
	 * 获取第一张图片
	 */
	public function getFirst($caseid) {
		$aWhere = array(
			'caseid' => $caseid,		
		);
		return $this->where($aWhere)->find();
	}
	
	/*
	 * 获取所有缩略图, case_pic
	 */
	public function getAllThumbs($caseid) {
		$aWhere = array('caseid' => $caseid);
		
		return $this->where($aWhere)->order('createtime')->select();
	}
	
}



?>