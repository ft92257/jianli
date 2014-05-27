<?php
class AcceptanceAction extends BaseAction {

	public function __construct() {
		parent::__construct();
		$this->oUser->id = 1;
	}
	
	#验收报告列表（编辑）
	public function info(){
		$this->model = D("User_report");
		$report = $this->model->where(array('case' => $_REQUEST['case']))->select();
		$this->assign('report', $report);
		$this->display();
	}
	
	public function edit(){
		if ($_REQUEST['act'] == 'pass'){
			$this->model = D("User_report");
			$this->model->where(array('id' => $_REQUEST['id']))->data(array('result' => 2))->save();
			echo '1';exit;
		}
		if ($_REQUEST['act'] == 'nopass'){
			
			$this->model = D("User_report");
			$this->model->where(array('id' => $_REQUEST['id']))->data(array('result' => 3))->save();
			$report = $this->model->where(array('id' => $_REQUEST['id']))->find();
			$this->model = D("User_exception");
			$data = array(
					'type' => '3',
					'description' => $_REQUEST['description'],
					'uid' => $this->oUser->id,
					'no' => $report['no']
					);
			$this->model->addData($data);
			echo '1'; exit;
		}
		if ($_REQUEST['act'] == 'repass'){
			$this->model = D("User_report");
			$this->model->where(array('id' => $_REQUEST['id']))->data(array('result' => 2))->save();
			$report = $this->model->where(array('id' => $_REQUEST['id']))->find();
			$this->model = D("User_exception");
			$data = array(
					'type' => '4',
					'description' => $_REQUEST['description'],
					'uid' => $this->oUser->id,
					'no' => $report['no']
			);
			$this->model->addData($data);
			echo '1'; exit;
		}
	}
	
	public function nopass(){
		
	}
	
	
	
}