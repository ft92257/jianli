<?php 

/**
 * 	预算
 */
class BudgetAction extends BaseAction {
	
	public function __construct() {
		parent::__construct();
	
		$this->model = D('User_budget');
		$this->model->oUser = $this->oUser;
	}
	
	/*
	 * 硬装显示页面
	 */
	public function hard() {
		$fields = $this->getParentConfig('hard');
		
		if ($this->isPost()) {
			$budget = getRequestData(array_keys($fields));
		} else {
			//$hard_budget = $this->oUser->hard_budget;
			//$hard_budget = json_decode($hard_budget, true);
		
			$budget = array(
				'design' => 11000,
				'artificial' => 34000,
				'material' => 115000,
			);
		}
		
		$budget['total'] = 0;
		foreach ($budget as $value) {
			$budget['total'] += (int) $value;
		}
		$this->assign('budget', $budget);
		$this->assign('fields', $fields);
		$acreage = 100;
		$this->assign('acreage', $acreage);
		$this->assign('title', '硬装费用');
		
		$this->display();
	}
	
	/*
	 * 硬装大饼图
	 */
	public function pie() {
		import('Public.Library.PieImage', './');
		
		$data = getRequest('data');
		if (!empty($data)) {
			$data = explode(",", $data);
		}		
		
		PieImage::make($data, $this->getParentConfig('hard'));
	}
	
	/*
	 * 软装大饼图
	*/
	public function softpie() {
		import('Public.Library.PieImage', './');
	
		$data = getRequest('data');
		if (!empty($data)) {
			$data = explode(",", $data);
		}
	
		PieImage::soft($data, $this->getParentConfig('soft'));
	}
	
	/*
	 * 硬装预算拆分
	 */
	public function calculate() {
		$acreage = (int) getRequest('acreage');
		$fields = $this->getParentConfig('hard');
		$aPrice = $this->getPriceConfig('hard');
		
		$this->_calculate($fields, $aPrice, $acreage);
	}
	
	/*
	 * 软装预算拆分
	 */
	public function softCalculate() {
		$acreage = (int) getRequest('acreage');
		$fields = $this->getParentConfig('soft');
		$aPrice = $this->getPriceConfig('soft');
		
		$this->_calculate($fields, $aPrice, $acreage);
	}
	
	/*
	 * 软装显示页面
	 */
	public function soft() {
		$fields = $this->getParentConfig('soft');

		if ($this->isPost()) {
			$budget = getRequestData(array_keys($fields));
		} else {
			//$hard_budget = $this->oUser->hard_budget;
			//$hard_budget = json_decode($hard_budget, true);
	
			$budget = array(
				'electric' => '30000',
				'furniture' => '21000',
				'fabric' => '14000',
				'green' => '7000',
				'illumination' => '14000',
				'furnishing' => '14000',
			);
		}

		$budget['total'] = 0;
		foreach ($budget as $value) {
			$budget['total'] += (int) $value;
		}
		$this->assign('budget', $budget);
		$this->assign('fields', $fields);
		$acreage = 100;
		$this->assign('acreage', $acreage);
		$this->assign('title', '软装费用');
		
		$this->display();
	}
	
	/*
	 * 拆分算法基础
	 */
	protected function _calculate($fields, $aPrice, $acreage = 100) {
		$budget = (int) getRequest('budget');
		$grade = (int)getRequest('grade');
	
		if (!($acreage > 0)) {
			die(json_encode(array('status' => 1001, 'msg' => '面积不能为空！')));
		}
	
		if ($budget > 0) {
			//已知预算和面积->各项档次
			$total = $budget;
			$price = $total / $acreage;
			if ($price < $aPrice[1]) {
				$grade = 1;
				$msg = '预算偏低！';
			} elseif ($price < $aPrice[2]) {
				$grade = 1;
			} elseif ($price < $aPrice[3]) {
				$grade = 2;
			} else {
				$grade = 3;
			}
		} elseif ($grade > 0) {
			//已知档次和面积->各项预算
			$total = $aPrice[$grade] * $acreage;
		} else {
			die(json_encode(array('status' => 1002, 'msg' => '预算或档次至少填写一项！')));
		}
	
		$arr = array(
				'status' => 0,
				'msg' => $msg ? $msg : '预估成功！',
		);
	
		$rate = array();$sum = 0;$last_field = '';
		foreach ($fields as $field => $value) {
			$rate[$field] = round($total * $value[2][$grade] / 1000) * 1000;
			$arr['data'][$field] = $rate[$field];
			$arr['data']['grade'][$field] = $grade;
			$sum += $rate[$field];
			$last_field = $field;
		}
	
		$arr['data'][$last_field] += $total - $sum;
	
		echo json_encode($arr);
	}
	
	/**************************第二层*******************************/
	/*
	 * 第一层单价配置
	 */
	public function getPriceConfig($type) {
		$configs = array(
			'soft' => array(
				'1' => '1200', '2' => '2300', '3' => '4600',
			),
			'hard' => array(
				'1' => '1200', '2' => '1600', '3' => '2500',
			),
		);
		
		return $configs[$type];
	}
	
	/*
	 * 第一层配置
	 */
	public function getParentConfig($type, $isChild = false) {
		$configs = array(
			'soft' => array(
				'electric' => array('家电', '#ff0000', array('1' => 0.5, '2' => 0.3, '3' => 0.2), 'soft'),
				'furniture' => array('家居', '#ffff00', array('1' => 0.2, '2' => 0.21, '3' => 0.16), 'soft'),
				'fabric' => array('布艺', '#0000ff', array('1' => 0.125, '2' => 0.14, '3' => 0.08), 'soft'),
				'green' => array('绿化', '#00ff00', array('1' => 0.025, '2' => 0.07, '3' => 0.08), 'soft'),
				'illumination' => array('照明', '#ff00ff', array('1' => 0.1, '2' => 0.14, '3' => 0.08), 'soft'),
				'furnishing' => array('陈设', '#00ffff', array('1' => 0.05, '2' => 0.14, '3' => 0.4), 'soft'),
			),
			'hard' => array(
				'design' => array('设计', '#ff0000', array('1' => 0.5/11, '2' => 1/14, '3' => 1.5/25), 'hard'),
				'artificial' => array('人工', '#00ff00', array('1' => 3/11, '2' => 3/14, '3' => 3.5/25), 'hard'),
				'material' => array('材料', '#0000ff', array('1' => 7.5/11, '2' => 10/14, '3' => 20/25), 'hard'),
			),	
		);
		
		if ($isChild) {
			$configs = array_merge($configs['soft'], $configs['hard']);
		} 

		return $configs[$type];
	}
	
	/*
	 * 第二层配置
	 */
	public function getChildConfig($type) {
		$configs = array(
			'electric' => array(
				'elec1' => array('家电1', '#ff0000', array('1' => 0.3, '2' => 0.2, '3' => 0.1)),
				'elec2' => array('家电2', '#00ff00', array('1' => 0.3, '2' => 0.3, '3' => 0.2)),
				'elec3' => array('家电3', '#0000ff', array('1' => 0.4, '2' => 0.5, '3' => 0.7)),
			),
		);
		
		return $configs[$type];
	}
	
	/*
	 * 第二层显示页
	 */
	public function child() {
		$type = getRequest('type');
		$fields = $this->getChildConfig($type);
		if (empty($fields)) {
			$this->error('没有该页面！');
		}
		
		if ($this->isPost()) {
			$budget = getRequestData(array_keys($fields));
		} else {
			//$hard_budget = $this->oUser->hard_budget;
			//$hard_budget = json_decode($hard_budget, true);
	
			//todo 初始化
			$budget = array();
		}
	
		$budget['total'] = 0;
		foreach ($budget as $value) {
			$budget['total'] += (int) $value;
		}
		$this->assign('budget', $budget);
		$this->assign('fields', $fields);
		
		$this->assign('type', $type);
		$parent = $this->getParentConfig($type, true);
		$this->assign('title', $parent[0] . '费用');
		
		$this->display();
	}
	
	/*
	 * 第二层大饼图
	*/
	public function childpie() {
		$type = getRequest('type');
		$fields = $this->getChildConfig($type);
		if (empty($fields)) {
			die('没有该页面！');
		}
		
		import('Public.Library.PieImage', './');
	
		$data = getRequest('data');
		if (!empty($data)) {
			$data = explode(",", $data);
		}
	
		PieImage::child($data, $fields);
	}
	
	/*
	 * 第二层预算拆分
	 */
	public function childCalculate() {
		$type = getRequest('type');
		$fields = $this->getChildConfig($type);
		if (empty($fields)) {
			die(json_encode(array('status' => 1004, 'msg' => '没有该页面！')));
		}
		
		$parent = $this->getParentConfig($type, true);
		$parentPrice = $this->getPriceConfig($parent[3]);
		$aPrice = array(
			'1' => $parent[2]['1'] * $parentPrice['1'],
			'2' => $parent[2]['2'] * $parentPrice['2'],
			'3' => $parent[2]['3'] * $parentPrice['3'],
		);
		
		$this->_calculate($fields, $aPrice);
	}
}
?>