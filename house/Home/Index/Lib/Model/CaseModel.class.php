<?php 

/**
 * 样板工地模型
 *
 */
class CaseModel extends BaseModel {
	
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
			$value['focus'] = getFileUrl($value['focus']);
		}
	}
	protected function _after_find(&$data,$options) {
		$data['tags'] = array_diff(explode('|',$data['tags']),array(''));
		
	}
	/*
	 * 获取置顶记录
	 * @param int $limit 获取记录条数
	 */
	public function getTop($cid, $limit, $area = '') {
		$condition = array(
			'cid' => $cid,
		);
		if ($area) {
			$condition['area'] = $area;
		}
		
		return $this->where($condition)->order('ord DESC')->limit($limit)->select();
	}
	
	/*
	 * 获取施工队项目置顶记录
	* @param int $limit 获取记录条数
	*/
	public function getConsTop($consid, $limit) {
		$condition = array(
			'consid' => $consid,
		);
		return $this->where($condition)->order('ord DESC')->limit($limit)->select();
	}
	
	/*
	 * 获取样板工地首页推荐(可参观工地)
	* @param int $limit 获取记录条数
	*/
	public function getTotalTop($limit) {
		$condition = array(
			//大于结束时间
			'observe_end' => array('gt', time()),
		);
		//TODO 排序方式待确认？
		return $this->where($condition)->order('omment_count DESC')->limit($limit)->select();
	}
}

	

?>