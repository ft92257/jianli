<?php 

/**
 * 公司材料模型
 *
 */
class Company_materialModel extends BaseModel {
	
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
	
	/*
	 * 获取置顶记录
	* @param int $limit 获取记录条数
	*/
	public function getTop($cid, $limit) {
		$condition = array(
			'cid' => $cid,
		);
		return $this->where($condition)->order('ord DESC')->limit($limit)->select();
	}
}



?>