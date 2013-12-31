<?php 

/**
 * 	评论相关：信息的查询、修改、添加、删除
 */
class ScoreAction extends BaseAction {
	public function __construct() {
		parent::__construct();
	
		$this->model = D('Score');
	}
	
	public function index(){
		$params = array(
				'where' => $this->getPurviewCondition(),
				'order' => 'createtime DESC',
		);
		
		$this->_getPageList($params);
	}
	
	public function reply() {
		$id = getRequest('id');
		$data = $this->model->getById($id);
		$this->checkPurviewData($data);
	
		if ($this->isPost()) {
			$newdata = array();
			if ($data['reply'] == '') {
				$newdata['reply'] = getRequest('reply');
			}
			if ($data['additional_reply'] == '') {
				$newdata['additional_reply'] = getRequest('additional_reply');
			}
			if (($data['comment'] && !$newdata['reply']) || ($data['additional'] && !$newdata['additional_reply'])) {
				$newdata['isreply'] = 0;
			} else {
				$newdata['isreply'] = 1;
			}
						
			$returl = U('index');
			if ($this->model->where(array('id' => $id))->data($newdata)->save() !== false) {
				$this->success('回复成功！', $returl);
			} else {
				$this->error('操作失败！', $returl);
			}
		} else {
			$this->assign('data', $data);
			$reply_disable = $data['comment'] && !$data['reply'] ? 0 : 1;
			$additional_disable = $data['additional'] && !$data['additional_reply'] ? 0 : 1;
			$this->assign('reply_disable', $reply_disable);
			$this->assign('additional_disable', $additional_disable);
			
			$this->display();
		}
	}
   
}
?>
