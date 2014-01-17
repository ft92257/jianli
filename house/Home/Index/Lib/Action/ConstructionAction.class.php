<?php 

/**
 * 	施工队相关页面
 */
class ConstructionAction extends BaseAction {

	//需要验证的方法
	protected $aVerify = array(
	);
	
	public function __construct() {
		parent::__construct();
		
		$this->model = D('Construction');
	}
	
	/*
	 * 施工队详情.html
	 */
	public function detail() {
		$id = getRequest('id');
		//施工队详细信息
		$aConstruction = $this->model->getById($id);
		if (empty($aConstruction)) {
			$this->error('没有对应的施工队！');
		}
		
		//所有成员信息
		$aMemebers = D('Construction_member')->getAll($id);
		
		//负责的样板工地,6个
		$aCase = D('Case')->getConsTop($id, 6);
		
		//评分记录3条
		$aScore = D('Score')->getConsTop($id, 3);
		$this->assign('count',count($aScore));
		$this->assign('construction', $aConstruction);
		$this->assign('members', $aMemebers);
		$this->assign('case', $aCase);
		$this->assign('score', $aScore);
		
		$this->display();
	}
}

?>