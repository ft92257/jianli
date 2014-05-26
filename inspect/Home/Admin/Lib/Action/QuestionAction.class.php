<?php 

class QuestionAction extends BaseAction {
	
	public function __construct() {
		parent::__construct();
		$this->model = D('Question');
	}
	
	//列表
	public function index(){
		$params = array(
			//'order' => 'createtime DESC',
		);
		$where = array();
		if (isset($_POST['type']) && !empty($_POST['type'])){
			$where = array_merge($where, array('type' => array('like', "%{$_POST['type']}%")));
		}
		
		$params['where'] = $where;
		$this->_getPageList($params);
	}
	
	public function add() {
		if ($this->isPost()) {
			$arr = array('uid' => $this->oUser->id);
			$this->_add($arr);
		} else {
			$this->_display_form();
		}
	}
	
	public function edit() {
		$data = $this->model->getById(getRequest('id'));
		if ($this->isPost()) {
			$this->_edit($data);
		} else {
			//$data['focus'] = getFileUrl($data['focus'], '80-80');
			$this->assign('sel_type', json_encode($this->model->strFormat($data['type'])));
			$this->_display_form($data, 'add');
		}
	}
}
?>