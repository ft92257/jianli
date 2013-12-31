<?php 

/**
 * 	后台首页展示
 */
class IndexAction extends BaseAction {
   
	public function index(){
		$aCom = (array) $this->oCom;
		$aCom['logo'] = getFileUrl($aCom['logo']);
		$this->assign('com', $aCom);	

		$basewhere = $this->getPurviewCondition();
		$this->assign('num', D('Reserve')->getUndealCount($basewhere));
		
		$this->display();
	}
}

?>
