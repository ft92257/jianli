<?php
class MemoAction extends BaseAction {

	public function __construct() {
		parent::__construct();
		$this->oUser->id = 1;
	}
	
	public function memoAdd(){
		$this->model = D("Memo");
		if ($this->isPost()){
			$reminder_time = strtotime($_POST['reminder_time']);
			if ($reminder_time <= time()){
				echo "<script>alert('提醒日期必须晚于当前日期'); </script>";
				exit;
			}
			$data = array(
					'content' => $_POST['content'],
					'reminder_time' => $reminder_time,
					'expire' => $reminder_time + 86400
			);
			if ($_POST['id'] > 0){
				$expire = $this->model->where(array('id' => $_POST['id']))->getField('expire');
				if ($expire < time()){
					echo "<script>alert('无法编辑已经过期的备忘录'); </script>";
					exit;
				}
				$this->model->where(array('id' => $_POST['id']))->data($data)->save();
			} else {
				$data['uid'] = $this->oUser->id;
				$this->model->addData($data);
			}
			$this->redirect('Time/index');
		} else {
			$memo = $_GET['id'] > 0 ? $this->model->where(array('id' => $_POST['id']))->find() : array();
			$this->assign('memo', $memo);
			$this->display();
		}
	}
	
	public function index(){
		$this->model = D("Memo");
		$where = array();
		$where = $_GET['uid'] > 0 ? array_merge($where, array('uid' => $_GET['uid'])) : $where;
		$where = $_GET['begin_date'] > 0 && $_GET['end_date'] > 0 ? array_merge($where, array('reminder_time' => array('between', $_GET['begin_date'].",".($_GET['end_date']-1)))) : $where;
		$where = $_GET['expire'] > 0 ? array_merge($where, array('expire' => array('gt', time()))) : $where;
		$memo = $this->model->queryAll($where); 
		$this->assign('memo', $memo);
		$this->display();
	}
}