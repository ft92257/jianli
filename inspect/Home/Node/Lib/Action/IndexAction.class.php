<?php
class IndexAction extends BaseAction {
	
	private $node;		#工程阶段
	private $userInfo;		#用户信息
	private $userStep;		#用户工程阶段信息
	private $beginDate;		#开工日期（时间戳）
	private $endDate;		#竣工日期（时间戳）
	private $cycle;		#总工期（天数）
	private $timeLine;		#进度表
	
	public function __construct() {
		parent::__construct();
		$this->oUser->id = 1;
		$this->node = D("Node")->where(array('pid' =>0))->select();
		$this->userStep = D('User_step')->where(array('uid' => $this->oUser->id, 'status' => array('neq', '-2')))->order('begin_date')->select();
		//$this->beginDate = $this->userInfo['begin_date'];
		foreach ($this->userStep as $k => $v) {
			$begin_date[] = $v['begin_date'];
			$end_date[] = $v['end_date'];
			$arr[$v['step']][] = $v;
		}
		$this->userStep = $arr;
		unset($arr);
		asort($begin_date);
		$this->beginDate = reset($begin_date);
		asort($end_date);
		$this->endDate = end($end_date);
		$this->cycle = $this->getdays($this->beginDate, $this->endDate);

		for ($i = 1; $i <= $this->cycle; $i++) {
			$arr[$i]['day'] = $i;
			$arr[$i]['time'] = $this->beginDate + ($i - 1) * 86400;
			$arr[$i]['date'] = date("Y-m-d", $arr[$i]['time']);
			foreach ($this->userStep as $k => $v) {
				foreach($v as $o){
					if ($arr[$i]['time'] >= $o['begin_date'] && $arr[$i]['time'] <= $o['end_date']) {
						$arr[$i]['step'][] = $o['step'];
					}
				}
			}
		}
		$this->timeLine = $arr;
	}
	
	private function getdays($begin_date, $end_date){
		return round(($end_date - $begin_date)/3600/24) + 1;
	}
	
	public function test(){
		print_r('<pre>');
		print_r($this->userStep);
	}
	
	public function timeLine(){
		if ($this->isPost()) {
			$step = explode(',', $_POST['step']);
			$this->model = D('User_step');
			$where = array(
					'uid' => $this->oUser->id, 
					'step' => array('not in', $_POST['step'])
					);
			$this->model->where($where)->data(array('status' => -2))->save();
			//echo $this->model->getLastSql().'<br/><br/>';
			foreach($step as $k => $v){
				$where = array(
						'uid' => $this->oUser->id, 
						'step' => $v
						);
				$res = $this->model->where($where)->find();
				//echo $this->model->getLastSql().'</br>';
				if (empty($res)){
					$data = array(
							'uid' => $this->oUser->id,
							'step' => $v,
							'begin_date' => strtotime($_POST['begin_date_'.$v]),
							'end_date' => strtotime($_POST['end_date_'.$v]),
							'schedule' => 0
					);
					$this->model->addData($data);
				} else {
					$data = array(
							'uid' => $res['uid'],
							'begin_date' => strtotime($_POST['begin_date_'.$v]),
							'end_date' => strtotime($_POST['end_date_'.$v]),
							'schedule' => $res['schedule']
					);
					$this->model->where(array('uid' => $this->oUser->id, 'step' => $v))->data($data)->save();
				}
				
			}
			$this->redirect('timeLine');
		}
		$this->assign('node', $this->node);
		$this->assign('nodeJson', json_encode($this->node));
		$this->assign('userNode', $this->userNode);
		$this->assign('userNodeJson', json_encode($this->userNode));
		$this->assign('timeLine', $this->timeLine);
		$this->assign('timeLineJson', json_encode($this->timeLine));
		$this->assign('userStep', $this->userStep);
		$this->assign('userStepJson', json_encode($this->userStep));
		$this->display();
	}
	
}