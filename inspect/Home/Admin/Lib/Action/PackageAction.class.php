<?php 

class PackageAction extends BaseAction {
	
	public function __construct() {
		parent::__construct();
	
		$this->model = D('Package');
	}
	
	//列表
	public function index(){
		$params = array(
			'order' => 'createtime DESC',
			'where' => array('status' => 0),
		);

		$this->_getPageList($params);
	}
	
	protected function _getComb() {
		$ids = getRequest('ids');
		$counts = getRequest('counts');
		$aKeys = array_flip($ids);
		$oprice = 0;
		
		$aGoods = M('Goods')->field('id,name,price')->where(array('id' => array('in', $ids)))->select();
		foreach ($aGoods as &$goods) {
			$goods['count'] = $counts[$aKeys[$goods['id']]];
			$goods['name_o'] = $goods['name'];
			$goods['name'] .= $goods['price'] . '元';
			
			$oprice += $goods['price'] * $goods['count'];
		}
		if (getRequest('price') >= $oprice) {
			$this->error('套餐价格不能大于原价！');
		}
		
		$comb = json_encode($aGoods);

		return $comb;
	}
	
	protected function _getGoods() {
		$aGoods = M('Goods')->select();
		
		foreach ($aGoods as &$goods) {
			$goods['name'] .= $goods['price'] . '元';
		}
		return $aGoods;
	}
	
	public function add() {
		if ($this->isPost()) {
			$database = array(
				'combination' => $this->_getComb(),
			);
			$this->_add($database);
		} else {
			$this->assign('goods', $this->_getGoods());
			
			$this->_display_form();
		}
	}
	
	public function edit() {
		$data = $this->model->getById(getRequest('id'));
	
		if ($this->isPost()) {
			$database = array(
				'combination' => $this->_getComb(),
			);
			
			$this->_edit($data, $database);
		} else {
			$this->assign('goods', $this->_getGoods());
			$this->assign('comb', json_decode($data['combination'], true));
			
			$this->_display_form($data, 'add');
		}
	}
	
	public function delete() {
		$this->_delete(array('id' => getRequest('id')));
	}
}
?>