<?php 

class CommentAction extends BaseAction {

	//需要验证的方法
	protected $aVerify = array(
			'add'
	);
	
	public function __construct() {
		parent::__construct();
		$this->model = D('Comment');
	}
	
	public function add() {
		$data = array(
				'uid' => $this->oUser->id,
				'type' => getRequest('type'),
				'target' => getRequest('target'),
				'content' => getRequest('content'),
			);
		
		$this->model->addData($data);
		$this->success('评论成功！');
	}
	
}

?>