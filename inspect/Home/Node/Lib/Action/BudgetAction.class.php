<?php 

/**
 * 	预算
 */
class BudgetAction extends BaseAction {
	
	public function __construct() {
		parent::__construct();
	
		$this->model = D('Budget');
	}
	
	public function detail() {
		if ($this->isPost()) {
			$hard_budget = array(
					'design' => (int) getRequest('design'),
					'artificial' => (int) getRequest('artificial'),
					'material' => (int) getRequest('material'),
			);
			$hard_budget['total'] = $hard_budget['design'] + $hard_budget['artificial'] + $hard_budget['material'];
		} else {
			//$hard_budget = $this->oUser->hard_budget;
			//$hard_budget = json_decode($hard_budget, true);
		
			$hard_budget = array(
				'design' => 11000,
				'artificial' => 34000,
				'material' => 115000,
				'total' => 160000,
			);
		}
		$acreage = 100;
		
		$this->assign('acreage', $acreage);
		$this->assign('hard_budget', $hard_budget);
		
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
	
	/*
	 * 大饼图
	*/
	public function softpie() {
		import('Public.Library.PieImage', './');
	
		$data = getRequest('data');
		if (!empty($data)) {
			$data = explode(",", $data);
		}
	
		PieImage::soft($data);
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
	
	public function softCalculate() {
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
				$msg = '预算偏低！';
			} elseif ($price < 2300) {
				$grade = 1;
			} elseif ($price < 4600) {
				$grade = 2;
			} else {
				$grade = 3;
			}
		} elseif ($grade > 0) {
			//已知档次和面积->各项预算
			$aPrice = array('1' => '1200', '2' => '2300', '3' => '4600');
			$total = $aPrice[$grade] * $acreage;
		} else {
			die(json_encode(array('status' => 1002, 'msg' => '预算或档次至少填写一项！')));
		}

		if ($grade == 1) {
			$electric_rate = 5 / 10;
			$furniture_rate = 2 / 10;
			$fabric_rate = 1.25 / 10;
			$green_rate = 0.25 / 10;
			$illumination_rate = 1 / 10;
			$furnishing_rate = 0.5 / 10;
		} elseif($grade == 2) {
			$electric_rate = 3 / 10;
			$furniture_rate = 2.1 / 10;
			$fabric_rate = 1.4 / 10;
			$green_rate = 0.7 / 10;
			$illumination_rate = 1.4 / 10;
			$furnishing_rate = 1.4 / 10;
		} elseif ($grade == 3) {
			$electric_rate = 2 / 10;
			$furniture_rate = 1.6 / 10;
			$fabric_rate = 0.8 / 10;
			$green_rate = 0.8 / 10;
			$illumination_rate = 0.8 / 10;
			$furnishing_rate = 4 / 10;
		}
		$electric = round($total * $electric_rate / 1000) * 1000;
		$furniture = round($total * $furniture_rate / 1000) * 1000;
		$fabric = round($total * $fabric_rate / 1000) * 1000;
		$green = round($total * $green_rate / 1000) * 1000;
		$illumination = round($total * $illumination_rate / 1000) * 1000;
		
		$furnishing = $total - $electric - $furniture - $fabric - $green - $illumination;

		$arr = array(
				'status' => 0,
				'msg' => $msg ? $msg : '预估成功！',
				'data' => array(
						'electric' => $electric,
						'furniture' => $furniture,
						'fabric' => $fabric,
						'green' => $green,
						'illumination' => $illumination,
						'furnishing' => $furnishing,
						'grade' => array(
							'electric' => $grade,
							'furniture' => $grade,
							'fabric' => $grade,
							'green' => $grade,
							'illumination' => $grade,
							'furnishing' => $grade,
						),
				),
		);
		echo json_encode($arr);
	}
	
	public function soft() {
		$fields = array(
				'electric' => array('家电', '#ff0000'),
				'furniture' => array('家居', '#ffff00'),
				'fabric' => array('布艺', '#0000ff'),
				'green' => array('绿化', '#00ff00'),
				'illumination' => array('照明', '#ff00ff'),
				'furnishing' => array('陈设', '#00ffff'),
			);

		if ($this->isPost()) {
			$hard_budget = getRequestData(array_keys($fields));
		} else {
			//$hard_budget = $this->oUser->hard_budget;
			//$hard_budget = json_decode($hard_budget, true);
	
			$hard_budget = array(
				'electric' => '30000',
				'furniture' => '21000',
				'fabric' => '14000',
				'green' => '7000',
				'illumination' => '14000',
				'furnishing' => '14000',
			);
		}

		$hard_budget['total'] = 0;
		foreach ($hard_budget as $value) {
			$hard_budget['total'] += (int) $value;
		}
		$this->assign('hard_budget', $hard_budget);
		$this->assign('fields', $fields);
		$acreage = 100;
		$this->assign('acreage', $acreage);
		
		$this->display();
	}
}
?>