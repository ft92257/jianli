<?php 

/**
 * 	图片表模型
 */
class PictureAction extends BaseAction {
	public function __construct() {
		parent::__construct();
	
		$this->model = D('Picture');
	}
	
	/*
	 * 添加图片
	 */
	public function add(){
		$type = getRequest('type');
		$this->model->setConfig($type);
		$target = getRequest('target');
		$data = $this->model->getTarget($type, $target);
		$this->checkPurviewData($data);
		
		if ($this->isPost()) {
			$dataBase = array(
					'cid' => $this->oCom->id,
					'uid' => $this->oUser->id,
					'type' => $type,
					'target' => $target,
			);
			$url = $this->model->getListUrl($type, $target);
			$this->_add($dataBase, $url);
		} else {
			$tab = $this->model->getTabTitle($type, $target, $data);
			$this->assign('tabTitle', $tab);
			
			$this->_display_form();
		}
	}
	
	/*
	 * 修改
	 */
	public function edit() {
		$data = $this->model->getById(getRequest('id'));
		$this->checkPurviewData($data);
		$type = $data['type'];
		$this->model->setConfig($type);
		$target = $data['target'];
		
		if ($this->isPost()) {
			$url = $this->model->getListUrl($type, $target);
			$this->_edit($data, array(), $url);
		} else {
			$aTarget = $this->model->getTarget($type, $target);
			$tab = $this->model->getTabTitle($type, $target, $aTarget);
			$this->assign('tabTitle', $tab);
			
			$data['fid'] = getFileUrl($data['fid'], '80-80');
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

}
?>