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
	 * 硬装实际费用保存
	 */
	public function hard_edit() {
		$hard_fields = $this->getParentConfig('hard');
		
		$parent = $this->model->getParent('hard');
		$list = $this->model->getChildren($parent['id']);
		$hard_budget = array();
		$hb_total = 0;
		foreach ($list as $value) {
			$hard_budget[$value['name']] = $value['estimate'];
			$hb_total += $value['estimate'];
		}
		
		if ($this->isPost()) {
			$hard_realfee = getRequestData(array_keys($hard_fields));
			D('User_info')->where(array('uid' => $this->oUser->id))->data(array('hard_realfee' => json_encode($hard_realfee)))->save();
			$this->success('修改成功！', U('fee'));exit;
		}
/*
		 else {
			if (empty($this->oUser->info->hard_realfee)) {
				$hard_realfee = $hard_budget;
				D('User_info')->where(array('uid' => $this->oUser->id))->data(array('hard_realfee' => json_encode($hard_realfee)))->save();
			} else {
				$hard_realfee = json_decode($this->oUser->info->hard_realfee, true);
			}
		}
		
		$this->assign('hard_fields', $hard_fields);
		$this->assign('hard_budget', $hard_budget);
		$this->assign('hard_realfee', $hard_realfee);
		
		$this->display();*/
	}
	
	/*
	 * 软装实际费用保存
	 */
	public function soft_edit() {
		$soft_fields = $this->getParentConfig('soft');
		
		$parent = $this->model->getParent('soft');
		$list = $this->model->getChildren($parent['id']);
		$soft_budget = array();
		$sb_total = 0;
		foreach ($list as $value) {
			$soft_budget[$value['name']] = $value['estimate'];
			$sb_total += $value['estimate'];
		}
		
		if ($this->isPost()) {
			$soft_realfee = getRequestData(array_keys($soft_fields));
			D('User_info')->where(array('uid' => $this->oUser->id))->data(array('soft_realfee' => json_encode($soft_realfee)))->save();
			$this->success('修改成功！', U('fee'));exit;
		} 
		/*
		else {
			if (empty($this->oUser->info->soft_realfee)) {
				$soft_realfee = $soft_budget;
				D('User_info')->where(array('uid' => $this->oUser->id))->data(array('soft_realfee' => json_encode($soft_realfee)))->save();
			} else {
				$soft_realfee = json_decode($this->oUser->info->soft_realfee, true);
			}
		}
		
		$this->assign('soft_fields', $soft_fields);
		$this->assign('soft_budget', $soft_budget);
		$this->assign('soft_realfee', $soft_realfee);
		$this->display();*/
	}
	
	/*
	 * 费用主页
	 */
	public function fee() {
		//第一层配置
		$hard_fields = $this->getParentConfig('hard');
		$soft_fields = $this->getParentConfig('soft');
		
		//硬装预算信息
		$parent = $this->model->getParent('hard');
		$list = $this->model->getChildren($parent['id']);
		$hard_budget = array();
		$hb_total = 0;
		foreach ($list as $value) {
			$hard_budget[$value['name']] = $value['estimate'];
			$hb_total += $value['estimate'];
		}
		//软装预算信息
		$parent = $this->model->getParent('soft');
		$list = $this->model->getChildren($parent['id']);
		$soft_budget = array();
		$sb_total = 0;
		foreach ($list as $value) {
			$soft_budget[$value['name']] = $value['estimate'];
			$sb_total += $value['estimate'];
		}
		/*
		if ($this->isPost()) {
			$hard_realfee = getRequestData(array_keys($hard_fields));
			D('User_info')->where(array('uid' => $this->oUser->id))->data(array('hard_realfee' => json_encode($hard_realfee)))->save();
			
			$soft_realfee = getRequestData(array_keys($soft_fields));
			D('User_info')->where(array('uid' => $this->oUser->id))->data(array('soft_realfee' => json_encode($soft_realfee)))->save();
		} else {	*/
			//硬装实际费用
			if (empty($this->oUser->info->hard_realfee)) {
				$hard_realfee = $hard_budget;
				D('User_info')->where(array('uid' => $this->oUser->id))->data(array('hard_realfee' => json_encode($hard_realfee)))->save();
			} else {
				$hard_realfee = json_decode($this->oUser->info->hard_realfee, true);
			}
			//软装实际费用
			if (empty($this->oUser->info->soft_realfee)) {
				$soft_realfee = $soft_budget;
				D('User_info')->where(array('uid' => $this->oUser->id))->data(array('soft_realfee' => json_encode($soft_realfee)))->save();
			} else {
				$soft_realfee = json_decode($this->oUser->info->soft_realfee, true);
			}
		//}
		
		//硬装实际总费用
		$total = 0;$hard_total = 0;
		foreach ($hard_realfee as $key => $value) {
			$total += (int) $value;
			$hard_total += (int) $value;;
		}
		//软装实际总费用
		$soft_total = 0;
		foreach ($soft_realfee as $key => $value) {
			$total += (int) $value;
			$soft_total += (int) $value;
		}
		
		//总实际费用
		$this->assign('total', $total);
		//硬装预算总计
		$this->assign('hb_total', ($hb_total/10000) . '万');
		//软装预算总计
		$this->assign('sb_total', ($sb_total/10000) . '万');
		//硬装实际费用总和
		$this->assign('hard_total', ($hard_total/10000) . '万');
		//软装实际费用总和
		$this->assign('soft_total', ($soft_total/10000) . '万');
		
		//分类配置信息
		$this->assign('hard_fields', $hard_fields);
		$this->assign('soft_fields', $soft_fields);
		
		//预算明细信息
		$this->assign('hard_budget', $hard_budget);
		$this->assign('soft_budget', $soft_budget);
		//实际费用明细信息
		$this->assign('hard_realfee', $hard_realfee);
		$this->assign('soft_realfee', $soft_realfee);
		//用户信息
		$this->assign('info',  (array) $this->oUser->info);
		$this->assign('user', (array) $this->oUser);
		//$aGrade = array('1' => '低档', '2' => '中档', '3' => '高档');
		//$grade_text = $aGrade[$this->oUser->info->grade];
		//$this->assign('grade_text', $grade_text);
		$this->display();
	}
	
	
	/*
	 * 硬装预算工具
	 */
	public function hard() {
		$this->_parent('hard', '硬装预算');
	}
	
	/*
	 * 预算工具
	 */
	public function _parent($type, $title) {
		$fields = $this->getParentConfig($type);
		$parent = $this->model->getParent($type);
	
		if ($this->isPost()) {
			$budget = getRequestData(array_keys($fields));
				
			$total = 0;
			foreach ($budget as $key => $value) {
				$total += (int) $value;
	
				//更新子类预算
				$this->model->updateChild($key, $value, $parent);
			}
			
			//更新父级预算
			$this->model->where(array('id' => $parent['id']))->data(array('estimate' => $total))->save();
		
			//保存总预算
			$total_budget = $this->model->field('sum(estimate) as total')->where(array('uid' => $this->oUser->id, 'pid' => 0))->find();
			D('User_info')->where(array('uid' => $this->oUser->id))->data(array('budget_amount' => $total_budget['total']))->save();
		} else {
			//获取子类预算明细
			$list = $this->model->getChildren($parent['id']);
				
			$budget = array();$total = 0;
			foreach ($list as $value) {
				$budget[$value['name']] = $value['estimate'];
				$total += (int) $value['estimate'];
			}
		}
		$budget['total'] = $total;
		
		//实际费用信息
		if ($type == 'hard') {
			$realfee = json_decode($this->oUser->info->hard_realfee, true);
		} else {
			$realfee = json_decode($this->oUser->info->soft_realfee, true);
		}
		$total_realfee = 0;
		foreach ($realfee as $key => $value) {
			$total_realfee += (int) $value;
		}
		
		$this->assign('realfee', $realfee);
		$this->assign('total_realfee', $total_realfee);
		
		$this->assign('budget', $budget);
		$this->assign('fields', $fields);
		$acreage = $this->oUser->info->area;
		$this->assign('acreage', $acreage);
		$this->assign('title', $title);
	
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
	 * 软装预算工具
	 */
	public function soft() {
		$this->_parent('soft', '软装预算');
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
			$rate[$field] = round($total * $value[2][$grade] / 100) * 100;
			$arr['data'][$field] = $rate[$field];
			$arr['data']['grade'][$field] = $grade;
			$sum += $rate[$field];
			$last_field = $field;
		}
	
		$arr['data'][$last_field] += $total - $sum;
	
		echo json_encode($arr);
	}
	
	protected function _calculateBudget($budget, $grade, $acreage, $fields, $aPrice) {
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
		} else {
			$grade = $grade > 0 ? $grade : 1;
			//已知档次和面积->各项预算
			$total = $aPrice[$grade] * $acreage;
		}
	
		$arr = array();
		$rate = array();$sum = 0;$last_field = '';
		foreach ($fields as $field => $value) {
			$rate[$field] = round($total * $value[2][$grade] / 100) * 100;
			$arr[$field] = $rate[$field];
			//$arr['grade'][$field] = $grade;
			$sum += $rate[$field];
			$last_field = $field;
		}
	
		$arr[$last_field] += $total - $sum;
	
		return $arr;
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
	 * @param $type 分类键值
	 * @param $isChild true时读取子分类配置
	 * 配置：array('名称', '#颜色', array('低档' => 比例, '中档' => 比例, '高档' => 比例), '所属大类')
	 */
	public function getParentConfig($type, $isChild = false) {
		$configs = array(
			'soft' => array(
				'electric' => array('家电', '#db832a', array('1' => 0.5, '2' => 0.3, '3' => 0.2), 'soft'),
				'furniture' => array('家具', '#6e77ba', array('1' => 0.2, '2' => 0.21, '3' => 0.16), 'soft'),
				'fabric' => array('布艺', '#c5497d', array('1' => 0.125, '2' => 0.14, '3' => 0.08), 'soft'),
				'green' => array('绿化', '#a7b64f', array('1' => 0.025, '2' => 0.07, '3' => 0.08), 'soft'),
				'illumination' => array('照明', '#4cb5b8', array('1' => 0.1, '2' => 0.14, '3' => 0.08), 'soft'),
				'furnishing' => array('陈设', '#708b18', array('1' => 0.05, '2' => 0.14, '3' => 0.4), 'soft'),
			),
			'hard' => array(
				'design' => array('设计', '#6e77ba', array('1' => 0.5/11, '2' => 1/14, '3' => 1.5/25), 'hard'),
				'artificial' => array('人工', '#c5497d', array('1' => 3/11, '2' => 3/14, '3' => 3.5/25), 'hard'),
				'material' => array('材料', '#4cb5b8', array('1' => 7.5/11, '2' => 10/14, '3' => 20/25), 'hard'),
			),	
		);
		
		if ($isChild) {
			$configs = array_merge($configs['soft'], $configs['hard']);
		} 

		return $configs[$type];
	}
	
	/*
	 * 第二层配置
	 * @param $type 键值
	 * 配置：array('名称', '#颜色', array('低档' => 比例, '中档' => 比例, '高档' => 比例), '描述')
	 */
	public function getChildConfig($type) {
		$configs = array(
			'artificial' => array(
				'arti1' => array('水电工', '#db832a', array('1' => 0.18, '2' => 0.18, '3' => 0.18), '250元/人/天'),
				'arti2' => array('泥木工', '#6e77ba', array('1' => 0.24, '2' => 0.24, '3' => 0.24), '300元/人/天'),
				'arti3' => array('木工', '#c5497d', array('1' => 0.2, '2' => 0.2, '3' => 0.2), '230元/人/天'),
				'arti4' => array('油漆工', '#a7b64f', array('1' => 0.22, '2' => 0.22, '3' => 0.22), '250元/人/天'),
				'arti5' => array('管理费', '#4cb5b8', array('1' => 0.16, '2' => 0.16, '3' => 0.16), ''),
			),
			'electric' => array(
				'elec1' => array('家电1', '#db832a', array('1' => 0.3, '2' => 0.2, '3' => 0.1), 'ddddd'),
				'elec2' => array('家电2', '#6e77ba', array('1' => 0.3, '2' => 0.3, '3' => 0.2), 'hhhhh'),
				'elec3' => array('家电3', '#c5497d', array('1' => 0.4, '2' => 0.5, '3' => 0.7), 'sssss'),
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
			die('没有该页面！');
		}
		
		$parent = $this->model->getParent($type, 0);
		if ($this->isPost()) {
			$budget = getRequestData(array_keys($fields));
				
			$total = 0;
			foreach ($budget as $key => $value) {
				$total += (int) $value;
	
				$this->model->updateChild($key, $value, $parent);
			}
			$this->model->where(array('id' => $parent['id']))->data(array('estimate' => $total))->save();
		} else {
			$list = $this->model->getChildren($parent['id']);
				
			$budget = array();
			//$total = 0;
			foreach ($list as $value) {
				$budget[$value['name']] = $value['estimate'];
				//$total += (int) $value['estimate'];
			}
			$total = $parent['estimate'];
		}
		$budget['total'] = $total;
		
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