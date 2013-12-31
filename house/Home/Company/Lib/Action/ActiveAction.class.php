<?php 

/**
 * 	活动
 */
class ActiveAction extends BaseAction {
	
	public function __construct() {
		parent::__construct();
	
		$this->model = D('Active');
	}
	
	/*
	 * 添加
	 */
	public function add(){
		if ($this->isPost()) {
			$_POST['observe_date'] = strtotime(getRequest('observe_date'));
			$_POST['observe_end'] = strtotime(getRequest('observe_end'));
			$dataBase = array(
					'cid' => $this->oCom->id,
					'uid' => $this->oUser->id,
			);
			$this->_add($dataBase);
		} else {
			$this->model->getCase();
			$this->_display_form();
		}
	}
	
	/*
	 * 活动列表
	 */
	public function index(){
		$params = array(
				'where' => $this->getPurviewCondition(),
				'order' => 'createtime DESC',
		);
		
		$this->_getPageList($params);
	}
	
	/*
	 * 修改
	 */
	public function edit() {
		$data = $this->model->getById(getRequest('id'));
		$this->checkPurviewData($data);
		
		if ($this->isPost()) {
			$this->_edit($data);
		} else {
			$data['focus'] = getFileUrl($data['focus'],'120-120');
			$data['observe_date'] = date('Y年m月d日', $data['observe_date']);
			$data['observe_end'] = date('Y年m月d日', $data['observe_end']);
			$this->_display_form($data, 'add');
		}
	}
	
	/*
	 * 删除
	 */
	public function delete() {
		$id = getRequest('id');
		$data = $this->model->getById($id);
		$this->checkPurviewData($data);
		
		$this->_delete(array('id' => $id));
	}
	
	/*
	 * 图库
	 */
	public function picture() {
		$this->_picture(1, getRequest('id'));
	}
	
	/*
	 * 设为焦点图
	*/
	public function focus() {
		$id = getRequest('id');
		$data = D('Picture')->getById($id);
		$this->checkPurviewData($data);
		
		$where = array('id' => $data['target']);
		$set = array('focus' => $data['fid']);
		$this->model->where($where)->data($set)->save();

		$this->success('设置成功！');
	}

}
?>