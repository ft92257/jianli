<?php 

/**
 * 	谁施工好首页
 */
class IndexAction extends BaseAction {

	//需要验证的方法
	protected $aVerify = array(
	);
	
	public function __construct() {
		parent::__construct();
		
		//$this->model = D('Company');
	}
	
	/*
	 * 首页排行榜.html
	 */
	public function index() {
		$Company = D('Company');
		$Construction = D('Construction');
		//读取置顶的装修公司，组成菜单，排序方式：后台自定义
		$aComplex = $Company->getTopByType(1, 25);
		$aDesign =  $Company->getTopByType(2, 25);
		//公司排行榜
		$iCompOrder = getRequest('cmpord');
		$aCompany = $Company->getIndexTop(10, $iCompOrder);
		//施工队排行榜
		$iConsOrder = getRequest('cnsord');
		$aConstruction = $Construction->getIndexTop(10, $iConsOrder);
		$this->assign('cmpord',$iCompOrder);
		$this->assign('cnsord',$iConsOrder);
		$this->assign('complex', $aComplex);
		$this->assign('design', $aDesign);
		$this->assign('company', $aCompany);
		$this->assign('acompany',$iCompany);
		$this->assign('construction', $aConstruction);
		
		$this->display();
	}
	
}

?>