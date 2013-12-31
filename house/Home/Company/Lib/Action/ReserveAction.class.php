<?php 

/**
 * 	预约单相关：信息的查询、修改、添加、删除
 */
class ReserveAction extends BaseAction {
   
	public function __construct() {
		parent::__construct();
	
		$this->model = D('Reserve');
	}
	
	//列表
	public function index(){
		//TODO 处理未扣款订单
		if ($this->oUser->money > 0) {
			
		}
		
		
		$params = array(
				'where' => $this->getPurviewCondition(),
				'order' => 'createtime DESC',
		);
		
		$this->_getPageList($params);
	}
	
	/*
	 * 反馈
	*/
	public function feedback() {
		$data = $this->model->getById(getRequest('id'));
		$this->checkPurviewData($data);
	
		if ($this->isPost()) {
			$this->_edit($data);
		} else {
			$this->_display_form($data);
		}
	}

 }
?>