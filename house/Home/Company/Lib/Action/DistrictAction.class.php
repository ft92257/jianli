<?php 

/**
 * 	地区
 */
class DistrictAction extends BaseAction {
   
	public function __construct() {
		parent::__construct();
	
		$this->model = D('District');
	}
	
	public function getChildren() {
		$upid = getRequest('upid');
		$level = getRequest('level');
		$html = $this->model->getChildren($upid, $level);
		
		echo $html;
	}

}
 ?>