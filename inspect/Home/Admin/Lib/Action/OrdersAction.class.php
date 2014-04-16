<?php 

class OrdersAction extends BaseAction {
	
	public function __construct() {
		parent::__construct();
	
		$this->model = D('Orders');
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
			
		$aGoods = M('Goods')->field('id,name,price')->where(array('id' => array('in', $ids)))->select();
		$oprice = 0; $info='';
		foreach ($aGoods as &$goods) {
			$goods['count'] = $counts[$aKeys[$goods['id']]];
			$goods['name_o'] = $goods['name'];
			$goods['name'] .= $goods['price'] . '元';
			
			$oprice += $goods['price'] * $goods['count'];
			$info .= $goods['name'] . ' x ' . $goods['count'] . ';';
		}

		//获取套餐优惠价格
		$lprice = D('Package')->getPrice($ids, $counts, $aGoods);
		
		$data = array(
			'original_price' => $oprice,
			'last_price' => $lprice,
			'content' => $info,
			'status' => -1,
		);
		$oid = $this->model->addData($data);
		
		$this->assign('id', $oid);
		$this->assign('oprice', $oprice);
		$this->assign('lprice', $lprice);
		$this->assign('info', $info);
		
		$this->display('check');
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
			$this->_getComb();
		} else {
			$this->assign('goods', $this->_getGoods());
			
			$this->display();
		}
	}
	
	public function commit() {
		$id = getRequest('id');
		if ($id > 0) {
			$ret = $this->model->where(array('id' => $id))->data(array('status' => 0))->save();
			if ($ret) {
				$this->success("提交成功！");
			} else {
				$this->error('提交失败！');
			}
		} else {
			$this->error('订单id不能为空！');
		}
	}
	
}
?>