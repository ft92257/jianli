<?php
class StepAction extends BaseAction {
	
	private $user_info;
	private $user_step;
	private $user_step_info;
	private $node;
	
	private $info_where;
	private $step_where;
	public function __construct() {
		parent::__construct();
		
		$this->oUser->id = 1;

		$this->info_where = array(
				'uid' => $this->oUser->id,
				'no' => $_REQUEST['no']
				);
		
		$this->step_where = array(
				'uid' => $this->oUser->id,
				'no' => $_REQUEST['no'],
				'step'=>$_REQUEST['step']
		);
		
		$this->user_step = D('User_step')->where($this->step_where)->find();
		
		$this->user_step_info = D('User_step')->where($this->info_where)->select();
		$this->user_info = D('User_info')->where($this->info_where)->find();
		$this->node = D('Node')->where(array('id' => $_REQUEST['step']))->find();
	}
	
	public function indexQuestion(){
		$this->model = D("Question");
		$question = $this->model->where(array('step'=>$_REQUEST['step']))->select();
		$this->assign('question', $question);
		$this->assign('node', $this->node);
		$this->display();
	}
	
	public function indexAcceptance(){
		$this->model = D("Acceptance_case");
		$acceptance = $this->model->where(array('step'=>$_REQUEST['step']))->select();
		$this->assign('acceptance', $acceptance);
		$this->assign('node', $this->node);
		$this->display();
	}
	
	public function questionInfo(){
		$this->model = D("Question");
		$question = $this->model->where(array('id'=>$_REQUEST['id']))->find();
		$this->assign('question', $question);
		$this->assign('node', $this->node);
		$this->display();
	}
	
	public function index(){
		if ($_REQUEST['step'] <= 0 && !empty($_REQUEST['no'])){
			$this->redirect('Time/index');
		}
		$days = ceil(($this->user_step['end_date'] - $this->user_step['begin_date']) / 86400);
		$this->assign('days', $days);
		$this->assign('user_step', $this->user_step);
		$this->assign('user_info', $this->user_info);
		$this->assign('node', $this->node);

		$this->display();
	}
	
	private function stepLog(){
		$data = array(
				'step' => $this->user_step['step'],
				'uid' => $this->user_step['uid'],
				'begin_date' => $this->user_step['begin_date'],
				'end_data' => $this->user_step['end_data'],
				'schedule' => $this->user_step['schedule'],
				'no' => $this->user_step['no']
				);	
		D('User_step_log')->addData($data);
	}
	
	public function edit(){
		$time = time();
		
		if ($this->isPost()) {
			switch ($_REQUEST['act']){
				//停工
				case 'shutdown':
					//在工期内（介于工程起止时间之间）且状态为施工
					if ($time >= $this->user_info['begin_date'] && $time <= $this->user_info['end_date'] && $this->user_info['stage'] == 1){
						//保存停工记录
						$data = array(
								'uid' => $this->oUser->id,
								'type' => 1,
								'description' => $_REQUEST['description'],
								'creatime' => $time,
								'no' => $this->user_info['no']
								);
						D('User_exception')->addData($data);
						//设置档案状态为停工，并将执行时间保存为档案的工期结束时间
						$data = array(
								'end_date' => $time,
								'stage' => 2
								);
						D("User_info")->where($this->info_where)->data($data)->save();
						echo "<script>parent.location.reload()</script>";
					} else {
						echo '非法操作'; exit;
					}
					break;
				//复工
				case 'returnwork':
					//状态为停工
					if ($this->user_info['stage'] == 2){
						//停工时长
						$shutdown_time = $time - $this->user_info['end_date'];
						//更新施工明细，如果某阶段工期（起止时间）晚于上次停工时间，则根据停工时长顺延工期，并备份更新前的数据。
						foreach($this->user_step_info as $k => $v){ 
							$begin_date = $v['begin_date'] >= $this->user_info['end_date'] ? $v['begin_date'] + $shutdown_time : $v['begin_date'];
							$end_date = $v['end_date'] >= $this->user_info['end_date'] ? $v['end_date'] + $shutdown_time : $v['end_date'];
							$data = array(
									'begin_date' => $begin_date,
									'end_date' => $end_date
									);
							$this->model = D('User_step');
							$where = array(
									'uid' => $this->oUser->id,
									'no' => $v['no'],
									'step'=>$v['step']
									);
							$this->model->where($where)->data($data)->save();
							$this->stepLog($v['step']);
						}
						//保存复工记录
						$data = array(
								'uid' => $this->oUser->id,
								'type' => 2,
								'description' => $_REQUEST['description'],
								'creatime' => $time,
								'no' => $this->user_info['no']
						);
						$this->model =D('User_exception');
						$this->model->addData($data);
						
						//根据实际施工明细获得工程的起止时间并更新档案
						$this->model = D("User_step");
						$begin_date = $this->model->where($this->info_where)->min('begin_date');
						$end_date = $this->model->where($this->info_where)->max('end_date');
						$data = array(
								'begin_date' => $begin_date,
								'end_date' => $end_date,
								'stage' => 1
								);
						$data['stage'] = 1;
						$this->model = D("User_info");
						$this->model->where($this->info_where)->data($data)->save();
						echo "<script>parent.location.reload()</script>";
					} else {
						echo '非法操作'; exit;
					}
					break;
				case 'updatetime': 
					if ($this->user_info['stage'] == 1){
						$data['begin_date'] = strtotime($_REQUEST['begin_date']);
						$data['end_date'] = $_REQUEST['end_date'] ? strtotime($_REQUEST['end_date'])+86400 : ($data['begin_date'] - $this->user_step['begin_date']) + $this->user_step['end_date'];
						D('User_step')->where($this->step_where)->data($data)->save();
						$this->stepLog($this->user_step['step']);
						echo "<script>parent.location.reload()</script>";
					} else {
							echo "停工状态下无法编辑工期";
					}
					break;
			}
			
		}
		
	}
}