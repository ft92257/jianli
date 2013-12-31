<?php 

/**
 * 	样板房
 */
class HouseAction extends BaseAction {
	
	public function __construct() {
		parent::__construct();
	
		$this->model = D('House');
	}
	
	/*
	 * 添加
	*/
	public function add(){
		if ($this->isPost()) {
			$dataBase = array(
					'cid' => $this->oCom->id,
					'uid' => $this->oUser->id,
			);
			$this->_add($dataBase);
		} else {
			$this->_display_form();
		}
	}
	
	//列表
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
		$this->_picture(3, getRequest('id'));
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
	
	/*
	 * 施工信息
	 */
	public function cons() {
		if (!$this->oCom->jl_cid) {
			$this->error('未绑定易监理公司帐号，不能进行该操作！');
		}
		$id = getRequest('id');
		$data = D('House')->getById($id);
		$this->checkPurviewData($data);
		
		$this->model = D('JianliHouse');
		
		$params = array(
					'where' => array('cid' => $this->oCom->jl_cid),
					'order' => 'createtime DESC',
					'vars' => array('hid' => $id, 'jl_id' => $data['jl_id']),
				);

		$this->_getList($params);
	}

	/*
	 * 绑定施工信息
	 */
	public function bind() {
		if (!$this->oCom->jl_cid) {
			$this->error('未绑定易监理公司帐号，不能进行该操作！');
		}
		$hid = getRequest('hid');
		$data = $this->model->getById($hid);
		$this->checkPurviewData($data);
		
		$id = getRequest('id');
		$jl_data = D('JianliHouse')->getById($id);
		if (empty($jl_data)) {
			$this->error('该监理项目不存在！');
		}
		if ($jl_data['cid'] != $this->oCom->jl_cid) {
			$this->error('非法操作！');
		}
		
		//绑定id
		$ret = $this->model->where(array('id' => $hid))->data(array('jl_id' => $id))->save();
		if ($ret !== false) {
			$this->success('绑定成功！');
		} else {
			$this->error('绑定失败！');
		}
	}
}
?>