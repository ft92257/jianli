<?php 

/**
 * 	装修公司相关页面
 */
class CompanyAction extends BaseAction {
	//需要验证的方法
	protected $aVerify = array(
	);
	
	public function __construct() {
		parent::__construct();
		
		$this->model = D('Company');
	}
	
	/*
	 * 装修公司首页装修公司详情页
	 */
	public function index() {
		$cid = getRequest('cid');
		//装修公司详细信息
		$aCompany = $this->model->getById($cid);
		
		if (empty($aCompany)) {
			$this->error('没有对应的装修公司！');
		}
		
		//首页展示的活动
		$aActive = D('Active')->where(array('cid'=>$aCompany['id']))->order('ord desc')->find();
		//$aActive = D('Active')->where(array('ord'=>$maxId))->find();
		//样板工地
		$aCase = D('Case')->getTop($cid, 6);
		//施工队
		$aCons = D('Construction')->getTop($cid, 6);
		//dump($aCons);die;
		//材料
		$aMaterial = D('Picture')->getTop($cid,1,3);
		//业主评分
		$aScore = D('Score')->getTop($cid, 3);
		$this->assign('company', $aCompany);
		$this->assign('active', $aActive);
		$this->assign('case', $aCase);
		$this->assign('cons', $aCons);
		$this->assign('material', $aMaterial);
		$this->assign('score', $aScore);
		
		$this->display();
	}
}

?>