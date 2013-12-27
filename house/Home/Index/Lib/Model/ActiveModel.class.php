<?php 

/**
 * 样板参观会模型
 *
 */
class ActiveModel extends BaseModel {
	
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
			$value['case']	= D('case')->getById($value['caseid']);
			$value['reserve'] = D('Reserve')->getHasReserve(3,$value['id']);
			$value['cid'] = D('Company')->where(array('id'=>$value['cid']))->getField('name');
			
		}
	}
	protected function _after_find(&$data,$options) {
		$data['focus'] = getFileUrl($data['focus']);
		
		$aCase = D('Case')->getById($data['caseid']);
		$data['case'] = $aCase;
		
		
		
	}
	/*
	 * 获取推荐的记录
	* @param int $limit 获取记录条数
	*/
	public function getTop($cid, $limit) {
		$condition = array(
				'cid' => $cid,
				'id' =>array('neq',getRequest('id'))
		);
		return $this->where($condition)->order('createtime DESC')->limit($limit)->select();
	}
	
	/*
	 * 获取参观会首页推荐
	 * @param int $limit 获取记录条数
	 */
	public function getTotalTop($limit) {
		$condition = array(
			//大于结束时间
			'observe_end' => array('gt', time()),
		);
		//TODO 排序方式待确认？
		return $this->where($condition)->order('createtime DESC')->limit($limit)->select();
	}
	
}



?>