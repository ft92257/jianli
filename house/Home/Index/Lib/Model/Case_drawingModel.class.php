<?php 

/**
 * 样板工地 平面图，效果图模型
 *
 */
class Case_drawingModel extends BaseModel {
	
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
			$value['fid'] = getFileUrl($value['fid']);
		}
	}
	
	protected function _after_find(&$data,$options) {
		$data['cid'] = D('Company')->where(array('id'=>$data['cid']))->getField('name');
	}
	/*
	 * 获取所有平面图或效果图
	 */
	public function getAllByType($caseid, $type) {
		$aWhere = array(
			'caseid' => $caseid,
			'type' => $type,
		);
		return $this->where($aWhere)->select();
	}
}



?>