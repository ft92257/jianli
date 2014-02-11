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
		import('ORG.Util.Page');// 导入分页类
		$aCounty = (int)getRequest('county');
		$aWhere = $this->model->getConditionArray();
		$aTown = $this->model->getTownName($aCounty);
		//查询4家公司信息（瀑布流分页）
		$count = D('Company')->where($aWhere)->count();
		$Page = new page($count,2);
		$Page->setConfig('theme'," %upPage% %downPage% %first% %prePage% %linkPage% %nextPage% %end%");
		$show = $Page->show();
		//$aCompany = D('Company')->where($aWhere)->order('ord DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		if($_GET['caseord']){
			unset($_GET['caseord']);
			$aCompany = D('Company')->where($aWhere)->order('reserve_count DESC,ord DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		}else 
				$aCompany = D('Company')->where($aWhere)->order('ord DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		//每家公司查询5条记录
		foreach ($aCompany as $key=>$aValue) {
			$case = $this->model->getTop($aValue['id'], 5);
			$aCompany[$key]['case'] = $case;
		}
		//dump($aCompany);die;
		$comtype = getRequest('comtype');
		$aDistrict = D("District")->getFieldById(9);
		unset($_GET['_URL_']);
		$Acondition = $_GET;$Bcondition = $_GET;$Ccondition = $_GET;
		unset($Acondition['comtype']);unset($Acondition['p']);
		$comtypeurl = U("",$Acondition);
		unset($Bcondition['county']);unset($Bcondition['p']);
		$countyurl = U("",$Bcondition);
		unset($Ccondition['town']);unset($Ccondition['p']);
		$townurl = U("",$Ccondition);
		$this->assign('page',$show);
		$this->assign('comtypeurl',$comtypeurl);
		$this->assign('countyurl',$countyurl);
		$this->assign('townurl',$townurl);
		$this->assign('town',$aTown);
		$this->assign('comtype',$comtype);
		$this->assign('district',$aDistrict);
		$this->assign('company', $aCompany);
		$this->assign('county',$aCounty);
		$this->display();
	}
	
	/*
	 * 具体样板工地detail.html
	 */
	public function detail() {
		//当前图片信息
		$id = getRequest('id');
		$step = getRequest('step');
		if(empty($step)){
			$step = 1;
		}
		$aStep = $this->model->getStep($step);
		$aCase = D('Case')->getById($id);
		//dump($aCase);die;
		$aPic = D('Picture')->getPicture(2,$id,$step);
		$aCount = count($aPic);
		$fPic = D('Picture')->getPicture(3,$id,0);
		//dump($fPic);die;
		$ePic = D('Picture')->getPicture(4,$id,0);
		//施工队
		$aConstruction = D('Construction')->getById($aCase['consid']);
		$Case_team = D('Case_team');
		//监理团队
		$aJlgroup = $Case_team->getById($aCase['jlgroup']);
		
		//设计团队
		$aDesign = $Case_team->getById($aCase['design']);
		//评分
		$aScore = D('Score')->getCaseTop($aCase['id'], 3);
		$this->assign('step',$aStep);
		$this->assign('id',$id);
		$this->assign('apic', $aPic);
		$this->assign('count',$aCount);
		$this->assign('fpic', $fPic);
		$this->assign('epic', $ePic);
		$this->assign('case', $aCase);
		$this->assign('construction', $aConstruction);
		$this->assign('jlgroup', $aJlgroup);
		$this->assign('design', $aDesign);
		$this->assign('score', $aScore);
		
		$this->display();
	}
	/*
	 * 样板工地认证
	 */
	public function audit() {
		$id = getRequest('id');
		$aCase = D('Case')->getById($id);
		$aCons = D('Construction')->getById($aCase['consid']);
		for($i=1;$i<=$aCase['schedule'];$i++){
			$step = 'step'.$i;
			//$step = D('Picture')->getPicture(2,$id,$i,4);
			$aCase[$step]=D('Picture')->getPicture(2,$id,$i,4);
		}
		//dump($aCase);die;
		$this->assign('case',$aCase);
		$this->assign('cons',$aCons);
		$this->display();
	}
	/*
	 * 详细质量认证
	 */
	public function auditdetail() {
		$id = getRequest('id');
		$step = getRequest('step');
		switch ($step){
			case 1:
			  $detail = "水电阶段认证";
			  break;
			case 2:
			  $detail = "泥木阶段认证";
			 break;
			case 3:
			  $detail = "油漆阶段认证";
			  break;
			case 4:
			 $detail = "软装阶段认证";
			  break;
			case 5:
			 $detail = "安装阶段认证";
			default:
			  break;
		}
		$aPicture = D('Picture')->getPicture(2,$id,$step);
		$aCase = D('Case')->getById($id);
		$aCons = D('Construction')->getById($aCase['consid']);
		$this->assign('cons',$aCons);
		$this->assign('case',$aCase);
		$this->assign('pic',$aPicture);
		$this->assign('detail',$detail);
		$this->display();
	}
}

?>