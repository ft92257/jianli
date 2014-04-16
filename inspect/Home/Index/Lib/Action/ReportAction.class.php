<?php 

class ReportAction extends BaseAction {

	//需要验证的方法
	protected $aVerify = array(
	);
	
	public function __construct() {
		parent::__construct();
		
		$this->model = D('Inspect_report');
	}
	
	public function index() {
		$this->redirect('/Report/detail/id/1');
	}
	
	public function detail() {
		$id = getRequest('id');
		$data = $this->model->getById($id);
		if (empty($data)) {
			$this->error('没有该记录！');
		}
		
		$comment = D('Comment')->where(array('target' => $id))->select();
		$picture = D('Picture')->getPicture(4, $id);

		$data['createtime'] = getFormatTime($data['createtime']);
		$this->assign('picture', $picture);
		$this->assign('data', $data);
		$this->assign('comment', $comment);
		$this->display('detail');
	}
	
}

?>