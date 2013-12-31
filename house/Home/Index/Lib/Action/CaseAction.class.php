<?php 

/**
 * 	样板工地相关页面
 */
class CaseAction extends BaseAction {

	//需要验证的方法
	protected $aVerify = array(
	);
	
	public function __construct() {
		parent::__construct();
		
		$this->model = D('Case');
	}
	
	/*
	 * 样板工地列表页list.html
	 */
	public function index() {
		
		//查询4家公司信息（瀑布流分页）
		$aCompany = D('Company')->getPage(4);
		//每家公司查询5条记录
		foreach ($aCompany as $aValue) {
			$case = $this->model->getTop($aValue['id'], 5, getRequest('area'));
			$aCompany['cases'] = $case;
		}
		
		$this->assign('company', $aCompany);
		
		$this->display();
	}
	
	/*
	 * 具体样板工地detail.html
	 */
	public function detail() {
		$Case_pic = D('Case_pic');
		//当前图片信息
		$pid = getRequest('pid');
		$aPic = $Case_pic->getById($pid);
		if (empty($aPic)) {
			//项目信息
			$caseid = getRequest('id');
			$aCase = $this->model->getById($caseid);
			//选择第一张图片
			$aPic = $Case_pic->getFirst($id);
		} else {
			$caseid = $aPic['caseid'];
			$aCase = $this->model->getById($aPic['caseid']);
		}
		//所有该项目的缩略图信息
		$aThumbs = $Case_pic->getAllThumbs($caseid);
		
		$Case_drawing = D('Case_drawing');
		//平面图
		$aFlat = $Case_drawing->getAllByType($caseid, 1);
		
		//效果图
		$aEffect = $Case_drawing->getAllByType($caseid, 2);
		
		//施工队
		$aConstruction = D('Construction')->getById($aCase['consid']);
		
		$Case_team = D('Case_team');
		//监理团队
		$aJlgroup = $Case_team->getById($aCase['jlgroup']);
		
		//设计团队
		$aDesign = $Case_team->getById($aCase['design']);
		
		//评分
		$aScore = D('Score')->getCaseTop($caseid, 3);
		
		$this->assign('pic', $aPic);
		$this->assign('case', $aCase);
		$this->assign('thumbs', $aThumbs);
		$this->assign('flat', $aFlat);
		$this->assign('effect', $aEffect);
		$this->assign('construction', $aConstruction);
		$this->assign('jlgroup', $aJlgroup);
		$this->assign('design', $aDesign);
		$this->assign('score', $aScore);
		
		$this->display();
	}
	public function reserve(){
		$this->display();
	} 
}

?>