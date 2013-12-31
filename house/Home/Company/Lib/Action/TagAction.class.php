<?php 

/**
 * 	标签
 */
class TagAction extends BaseAction {
	
	public function __construct() {
		parent::__construct();
	
		$this->model = D('Tag');
	}
	
	/*
	 * 添加
	 */
	public function add(){
		if ($this->isPost()) {
			$data = getRequestData('type,content');
			$aTag = $this->model->get($data);
			if (!empty($aTag)) {
				$set = array(
						'usecount' => $aTag['usecount'] + 1,
						);
				$this->model->where(array('id' => $aTag['id']))->data($set)->save();
				die('1');
			}
			
			$data['usecount'] = 1;
			if ($this->model->addData($data)) {
				echo '1';
			} else {
				echo $this->model->getError();
			}
		} else {
			$this->error('非法操作！');
		}
	}
	
	/*
	 * 删除
	 */
	public function delete() {
		$id = getRequest('id');

	}
	
}
?>