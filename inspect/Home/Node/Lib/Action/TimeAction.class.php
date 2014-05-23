<?php
class TimeAction extends BaseAction {
	private $dataConf;
	private $user_info;
	private $user_step;


	public function __construct() {
		parent::__construct();
		$this->dataConf = require CONF_PATH.'dataConfig.inc.php';
		$this->oUser->id = 1;
		$this->user_info = D('User_info')->where(array("uid"=>$this->oUser->id))->find();
	}
	
	//查询每个阶段下的全部验收点
	private function getAcceptance($node){
		$id = array();
		foreach($node as $v){
			$acceptance = D("Acceptance")->where(array('step' => array('like', "%[{$v}]%")))->select();
			if ($acceptance) {
				foreach($acceptance as $k => $v){
					$id[$v['id']] =  array('id'=>$v['id'], 'step'=>$v['step']);
				}
			}
		}
		return $id;
	}
	
	//用户建档
	public function setUserInfo(){
		if ($this->isPost()){ 
			
			$this->model = D('User_info');
			//status = 0表示当前档案为用户选择数据，用户其他的档案数据设为-1，每次只查询用户当前选中的一份档案。
			$this->model->where(array('uid' => $this->oUser->id, 'status' => 0))->data(array('status' => -1))->save();
			//添加一份新档案
			$data = array(
					'uid' => $this->oUser->id,
					'realname' => $_POST['realname'],
					'mobile' => $_POST['mobile'],
					'community' => $_POST['community'],
					'province' => $_POST['province'],
					'city' => $_POST['city'],
					'county' => $_POST['county'],
					'address' => $_POST['address'],
					'room' => $_POST['room'],
					'apartment' => $_POST['apartment'],
					'style' => $_POST['style'],
					'budget' => $_POST['budget'],
					'budget_amount' => $_POST['budget_amount'],
					'area' => $_POST['area'],
					'begin_date' => strtotime($_POST['begin_date']),
					'cycle' => $_POST['cycle'],
					'contractor' => $_POST['contractor'],
					'PM' => $_POST['PM'],
					'house_status' => $_POST['house_status'],
					'renovation_type' => $_POST['renovation_type'],
					'stage' => 1,
					'no' => time().mt_rand(1000, 9999)
					);
			$this->model->addData($data);
			$this->user_info = $this->model->where(array('id' => $this->model->getLastInsID()))->find();
			//根据用户新建档案推算每阶段工期。
			$begin_date = $this->user_info['begin_date'];
			
			$this->model = D('User_step');
			foreach ($_POST["node"] as $id){
				$cycle = $this->getCycle($id);
				$end_date = $begin_date + $cycle * 86400;
				$data = array(
					'step' => $id,
			 		'uid' => $this->oUser->id,
			 		'schedule' => 0,
					'cycle' => $cycle,
					'begin_date' => $begin_date,
					'end_date' => $end_date,
					'no' => $this->user_info['no']
			 	); 
				$this->model->addData($data);
				$begin_date = $end_date;
			}
			//得到预定竣工时间更新档案
			$end_date = $this->model->where(array('uid' => $this->oUser->id, 'no' => $this->user_info['no']))->max('end_date');
			$this->model = D('User_info');
			$this->model->where(array('id' => $this->user_info['id']))->data(array('end_date' => $end_date))->save();
			
			//初始化验收报告
			$aid = $this->getAcceptance($_REQUEST['node']); 
			$this->model = D('User_report');
			foreach($aid as $k => $v){
				$data = array(
					'uid' => $this->oUser->id,
					'result' => 1, //未验收
					'aid' => $v['id'],
					'step' => $v['step'],
					'case' => $v['case']
				);
				$this->model->addData($data);
			}
			$this->redirect('index');
		} else {
			$this->assign('node', $this->dataConf['step']);
			$this->assign('budget', $this->dataConf['budget']);
			
			$this->assign('style', $this->dataConf['style']);
			$this->assign('renovation_type', $this->dataConf['renovation_type']);
			$this->assign('house_status', $this->dataConf['house_status']);
			$this->display();
		}
	}
	
	#获取阶段周期
	function getCycle($id){	
		switch($id){
			case '1': #准备
				$days = ceil($this->user_info['area']/100) + 9;
				break;
			case '2': #土建
				if ($this->user_info['room'] == 1){
					if ($this->user_info['renovation_type'] == 1){
						$days = 8;
					}
					if ($this->user_info['renovation_type'] == 2){
						$days = 12;
					}
				}
				if ($this->user_info['room'] == 2){
					$days = 20;
				}
				break;
			case "3": #水电
			case "4": #泥木
			case "5": #涂料
			    $days = ceil($this->user_info['area']/10);
			    break;
			case "6": #安装
				$days = ceil($this->user_info['area']/20);
				break;
			case "7": #软装
				$days = 7;
				break;
		}
		return $days;
	}
	
	#进度表主页面
	public function index(){
		
		$this->model = D("User_step");
		$days = ceil(($this->user_info['end_date'] - $this->user_info['begin_date'])/86400);
		$this->user_step = $this->model->where(array('uid' => $this->oUser->id, 'no' => $this->user_info['no']))->select();
		foreach($this->user_step as $k => $v){
			$arr[$v['step']] = $v;
		}
		$this->user_step = $arr;
		//print_r('<pre>');
		//print_r($this->user_step);
		$this->assign('user_step', $this->user_step);
		$this->assign('user_info', $this->user_info);
		if($this->user_info['stage'] == 1){
			$this->display();
		} else if($this->user_info['stage'] == 2){
			$this->display('indexShutdown');
		}
		
	}
}