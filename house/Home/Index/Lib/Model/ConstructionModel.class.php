<?php 

/**
 * 施工队模型
 *
 */
class ConstructionModel extends BaseModel {
	
	/*
	 * 验证form字段规则
	 */
	protected $aValidate = array(
	);
	
	/*
	 * 查询结果处理 
	 */
	protected function _after_select(&$resultSet,$options) {
		foreach($resultSet as &$value) {
			$value['cid'] = D('Company')->where(array('id'=>$value['cid']))->getField('name');
			$value['tags'] = array_diff(explode('|',$value['tags']),array(''));
			$value['focus'] = getFileUrl($value['focus']);
		}
		
		
	}
	protected function _after_find(&$data,$options) {
		$data['focus'] = getFileUrl($data['focus']);
		$data['cid'] = D('Company')->where(array('id'=>$data['cid']))->getField('name');
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
	
	/*
	 * 首页施工队排行榜
	 */
	public function getIndexTop($limit, $iOrd) {
		$iOrd = (int) $iOrd;
		if ($iOrd == 1) {
			//专家评分
			$order = 'score_expert DESC';
		} elseif ($iOrd == 2) {
			//业主评分
			$order = 'score_owner DESC';
		} else {
			//综合评分
			$order = 'score_complex DESC';
		}
	
		$aWhere = array();
	
		return $this->where($aWhere)->order($order)->limit($limit)->select();
	}
}



?>