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
	protected $aOptions = array(
			//'schedule' => array('1'=>'隐蔽','2'=>'泥木','3'=>'油漆','4'=>'安装','5'=>'软装','6'=>'竣工'),
			'style' => array('1' => '地中海风格', '2' => '简约风格', '3' => '欧美风格'),
	);
	/*
	 * 查询结果处理
	 */
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			//$value['schedule'] = $this->getOptions('schedule', $value['schedule']);
			$value['style'] = $this->getOptions('style', $value['style']);
			$value['focus'] = getFileUrl($value['focus'],'200-200');
			$value['county'] = D('District')->getNameById($value['county']);
			$value['cid'] = D("Company")->getNameById($value['cid']);
			//$value['reserve'] = D('Reserve')->getHasReserve(3,$value['id']);
		}
	}
	protected function _after_find(&$data,$options) {
		$data['county'] = D('District')->getNameById($data['county']);
		$data['focus'] = getFileUrl($data['focus'],'200-200');
		$data['tags'] = array_diff(explode('|',$data['tags']),array(''));
		$data['style'] = $this->getOptions('style',$data['style']);
	}
	public function getConditionArray(){
		$aWhere = array(
			//"county"=> (int)getRequest('county'),
			"comtype" => (int)getRequest('comtype'),
			//"town" => (int)getRequest('town'),
		);
		$aWhere = array_diff($aWhere,array(0));
		return $aWhere;
	}
	/*
	 * 获取阶段
	 * 
	 */
	public function getStep($step) {
		switch($step) {
			case 1:return "隐蔽阶段";break;
			case 2:return "泥木阶段";break;
			case 3:return "油漆阶段";break;
			case 4:return "安装阶段";break;
			case 5:return "软装阶段";break;
			case 6:return "竣工阶段";break;
		}
	}
	public function getTownName($county){
		if($county){
			$county=(int)$county;
			return D("District")->getFieldById($county);
		}
			return '';
	}
	/*
	 * 获取置顶记录
	 * @param int $limit 获取记录条数
	 */
	public function getTop($cid, $limit, $area = '') {
		$condition = array(
			"county"=> (int)getRequest('county'),
			'cid' => $cid,
			"town" => (int)getRequest('town'),
			
		);
		$condition = array_diff($condition,array(0));
		
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
			//'observe_end' => array('gt', time()),
		);
		//TODO 排序方式待确认？
		return $this->where($condition)->order('score_owner_count DESC')->limit($limit)->select();
	}
	
}

	

?>