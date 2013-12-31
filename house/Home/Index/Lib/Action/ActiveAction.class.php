<?php 

/**
 * 	参观会相关页面
 */
class ActiveAction extends BaseAction {

	//需要验证的方法
	protected $aVerify = array(
	);
	
	public function __construct() {
		parent::__construct();
		
		$this->model = D('Active');
	}
	
	/*
	 * 参观会首页列表未完成
	 */
	public function lists() {
		$aWhere = array(
			'region' => (int) getRequest('region'),
			'cid' => (int) getRequest('cid'),
			'style' => (int) getRequest('style'),
			'apartment' => (int) getRequest('apartment'),
		);
		$aWhere = array_diff($aWhere,array(0));
		import('ORG.Util.Page');
		$count  = $this->model->where($aWhere)->count();
		$Page = new Page($count,4);
		$Page->setConfig('theme'," %upPage% %downPage% %first% %prePage% %linkPage% %nextPage% %end%");
		//分页html
		$show = $Page->show();
		//列表数据
		$aData = $this->model->where($aWhere)->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('show', $show);
		$this->assign('data', $aData);
		$this->display();
	}
	
	/*
	 * 参观会详细内容canguanhuixiangqing
	 */
	public function detail() {
		$id = getRequest('id');
		//活动详细内容
		$aActive = $this->model->getById($id);
		if (empty($aActive)) {
			$this->error('没有对应的样板参观会！');
		}
		//公司推荐的样板工地,6个
		$oActive = D('Active')->getTop($aActive['cid'], 6);
		$aReserve = D('Reserve')->getHasReserve(3,$aActive['id'],5);
		$this->assign('active', $aActive);
		$this->assign('oactive', $oActive);
		$this->assign('reserve',$aReserve);
		$this->display();
	}
	
	/*
	 * 活动首页预约visit_canguanhui
	 */
	public function index() {
		//图片推荐4个
		$aActive = $this->model->getTotalTop(6);
		//二级推荐2个
		$aRecommend = array($aActive[4], $aActive[5]);
		unset($aActive[4]);
		unset($aActive[5]);
		//推荐的样板工地6个
	
		$aCase = D('Case')->getTotalTop(8);
		$this->assign('active', $aActive);
		$this->assign('recommend', $aRecommend);
		$this->assign('case', $aCase);
		
		$this->display();
	}
	
}

?>