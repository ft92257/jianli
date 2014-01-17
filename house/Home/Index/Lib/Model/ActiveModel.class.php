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
	protected $aOptions = array(
			//'schedule' => array('1'=>'隐蔽','2'=>'泥木','3'=>'油漆','4'=>'安装','5'=>'软装','6'=>'竣工'),
			'style' => array('1' => '地中海风格', '2' => '简约风格', '3' => '欧美风格'),
			'housetype' => array('1' =>'联体别墅','2'=> '三房两厅'),
	);
	protected $searchConfig = array(
			'style' => array('主题:', 'radio_list'),
			'cid' => array('公司:','radio_list'),
			'county'=> array('区域:','radio_list'),
			'housetype' => array('房型:','radio_list'),
	);
	public function getCondition(){
		$this->aOptions['cid'] = D("Company")->getFieldById('','name');
		$this->aOptions['county'] =  D("District")->getFieldById(9);
	}
	public function getConditionArray() {
		$aWhere = array(
			'tb_case.county' => (int) getRequest('county'),
			'tb_case.cid' => (int) getRequest('cid'),
			'tb_case.style' =>(int) getRequest('style'),
			'tb_case.housetype' =>(int) getRequest('housetype'),
		);
		$bWhere = array_diff($aWhere,array(0));
		$cWhere = array(
			'tb_case.appid' =>1,
			'tb_active.appid'=>1,
			'tb_case.status' =>0,
			'tb_active.status' =>0,
	 	);
	 	return array_merge($bWhere,$cWhere);
	}
	/*
	 * 查询结果处理
	 */
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$value['focus'] = getFileUrl($value['focus']);
			//$value['case']	= D('Case')->getById($value['caseid']);
			//$value['status'] = D('Reserve')->getHasReserve(3,$value['id']);
			$value['cid'] = D('Company')->getNameById($value['cid']);
			$value['county'] = D('District')->getNameById($value['county']);
			$value['style'] = $this->getOptions('style', $value['style']);
			$value['reserve'] = D('Reserve')->getHasReserve(3,$value['id']);
		}
	}
	protected function _after_find(&$data,$options) {
		$data['focus'] = getFileUrl($data['focus']);
		$aCase = D('Case')->getById($data['caseid']);
		$data['case']= $aCase;
	}
	/*
	 * 获取推荐的记录
	* @param int $limit 获取记录条数
	*/
	public function getTop($cid, $limit) {
		$condition = array(
				'tb_active.cid' => $cid,
				'tb_active.id' =>array('neq',getRequest('id')),
				'tb_case.appid' =>1,
				'tb_case.status'=>0,
				'tb_active.appid' => 1,
				'tb_active.status' =>0,
		);
		return $this->join('tb_case ON tb_active.caseid = tb_case.id')->where($condition)->limit($limit)->select();
	}
	
	/*
	 * 获取参观会首页推荐
	 * @param int $limit 获取记录条数
	 */
	public function getTotalTop($limit) {
		$condition = array(
			//大于结束时间
			//'observe_end' => array('gt', time()),
		);
		//TODO 排序方式待确认？
		return $this->where($condition)->order('createtime DESC')->limit($limit)->select();
	}

}



?>