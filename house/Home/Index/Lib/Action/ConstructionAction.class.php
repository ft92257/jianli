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
	/*
	 * 施工队质量认证
	 */
	public function audit() {
		import('ORG.Util.Page');// 导入分页类
		$count = D('Construction')->where(array('is_approve'=>3))->order('ord')->count();
		$Page = new page($count,3);
		$Page->setConfig('theme'," %upPage% %downPage% %first% %prePage% %linkPage% %nextPage% %end%");
		$show = $Page->show();
		$aConstruction = $this->model->where(array('is_approve'=>3))->order('ord')->limit($Page->firstRow.','.$Page->listRows)->select();
		//$aConstruction = $this->model->join('tb_case on tb_construction.id=tb_case.consid' )->where(array('tb_construction.is_approve'=>3,'tb_case.is_approve'=>3,'tb_construction.status'=>0,'tb_case.status'=>0,'tb_construction.appid'=>1,'tb_case.appid'=>1))->order('tb_construction.ord')->limit($Page->firstRow.','.$Page->listRows)->field('tb_construction.id,tb_case.consid,tb_construction.name cname,tb_construction.focus cfocus,tb_case.focus focus,tb_case.name,tb_case.housetype')->select();
		//echo $this->model->getLastSql();die;
		foreach ($aConstruction as $key=>$val){
			$aConstruction[$key]['case'] = D('Case')->where(array('is_approve'=>3,'consid'=>$val['id']))->limit(4)->select();
		}
		//dump($aConstruction);die;
		$this->assign('cons',$aConstruction);
		$this->assign('page',$show);
		$this->display();
	}

}

?>