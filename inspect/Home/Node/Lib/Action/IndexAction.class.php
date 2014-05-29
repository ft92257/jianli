<?php
class IndexAction extends BaseAction {

	public function __construct() {
		parent::__construct();
		$this->oUser->id = 1;
	}
	
	public function index(){
		echo 'welcome!!';
		$this->display();
	}
	
	public function login(){
		if($_REQUEST){
			$user = D("User")->login($_REQUEST['account'], $_REQUEST['password']);
			if($user){
				$this->redirect('index');
			}else {
				echo 'login fail';
			}
		}
		$this->display();
	}
	
	public function register(){
		if($_REQUEST){
			$user = D("User")->register($_REQUEST['account'], $_REQUEST['password']);
		}
		$this->display();
	}
}