<?php
class FormAction extends BaseAction {

	public function __construct() {
		parent::__construct();
	}
	
	public function shutdownForm(){
		$this->assign("time", date("Y-m-d", time()));
		$this->assign('no', $_REQUEST['no']);
		$this->display();
	}
	
	public function returnworkForm(){
		$this->assign("time", date("Y-m-d", time()));
		$this->assign('no', $_REQUEST['no']);
		$this->display();
	}
	
	public function updatetimeForm(){
		$this->assign("step", $_REQUEST['step']);
		$this->assign('no', $_REQUEST['no']);
		$this->display();
	}
}