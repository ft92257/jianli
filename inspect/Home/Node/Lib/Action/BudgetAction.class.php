<?php 

/**
 * 	预算
 */
class BudgetAction extends BaseAction {
	
	public function __construct() {
		parent::__construct();
	
		$this->model = D('Budget');
	}
	
	/*
	 * 预算设置
	 */
	public function set() {
		if ($this->isPost()) {
			$this->redirect('detail');
		} else {
			$this->display();
		}
	}
	
	public function detail() {
		$this->display();
	}
	
	/*
	 * 大饼图
	 */
	public function pie() {
		import('Public.Library.PieImage', './');
		
		$data = getRequest('data');
		if (!empty($data)) {
			$data = explode(",", $data);
		}		
		
		PieImage::make($data);
	}
	
	public function calculate() {
		$acreage = (int) getRequest('acreage');
		$budget = (int) getRequest('budget');
		$grade = (int)getRequest('grade');
		
		if (!($acreage > 0)) {
			die(json_encode(array('status' => 1001, 'msg' => '面积不能为空！')));
		}
		
		if ($budget > 0) {
			//已知预算和面积->各项档次
			$total = $budget;
			$price = $total / $acreage;
			if ($price < 1200) {
				$grade = 1;
				$msg = '预算太低，可能不满足最低需求！';
			} elseif ($price < 1600) {
				$grade = 1;
			} elseif ($price < 2500) {
				$grade = 2;
			} else {
				$grade = 3;
			}
		} elseif ($grade > 0) {
			//已知档次和面积->各项预算
			$aPrice = array('1' => '1200', '2' => '1600', '3' => '2500');
			$total = $aPrice[$grade] * $acreage;
		} else {
			die(json_encode(array('status' => 1002, 'msg' => '预算或档次至少填写一项！')));
		}
		
		if ($grade == 1) {
			$design_rate = 0.5 / 11;
			$artificial_rate = 3 / 11;
		} elseif($grade == 2) {
			$design_rate = 1 / 14;
			$artificial_rate = 3 / 14;
		} elseif ($grade == 3) {
			$design_rate = 1.5 / 25;
			$artificial_rate = 3.5 / 25;
		}
		$design = round($total * $design_rate / 1000) * 1000;
		$artificial = round($total * $artificial_rate / 1000) * 1000;
		$material = $total - $design - $artificial;
		
		$arr = array(
			'status' => 0,
			'msg' => $msg ? $msg : '预估成功！',
			'data' => array(
				'design' => $design,
				'artificial' => $artificial,
				'material' => $material,
				//'other' => 10000,
				'grade' => array(
					'design' => $grade,
					'artificial' => $grade,
					'material' => $grade,
					//'other' => 2,
				),
			),
		);
		echo json_encode($arr);
	}
	
	/*
	 * 添加
	 */
	public function add(){
		if ($this->isPost()) {
			$dataBase = array(

			);

			$this->_add($dataBase);
		} else {
			$this->_display_form();
		}
	}
	
	//列表
	public function index(){
		$params = array(
				'order' => 'createtime DESC',
		);
		
		$this->_getPageList($params);
	}
	
	/*
	 * 修改
	 */
	public function edit() {
		$data = $this->model->getById(getRequest('id'));

		if ($this->isPost()) {
			$dataBase = array();

			$this->_edit($data, $dataBase);
		} else {
			$this->_display_form($data, 'add');
		}
	}
	
	/*
	 * 删除
	 */
	public function delete() {
		$id = getRequest('id');
		
		$this->_delete(array('id' => $id));
	}
	
}
?>