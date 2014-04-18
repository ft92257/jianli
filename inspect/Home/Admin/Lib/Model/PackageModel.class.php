<?php 

class PackageModel extends BaseModel {

	protected $aOptions = array(

	);
	
	protected $formConfig = array(
			'name' => array('套餐名称', 'text'),
			'price' => array('套餐总价', 'text', array('元')),
			'info' => array('套餐说明', 'textarea'),
			'oprice' => array('原价', 'span'),
			//array('', 'submit'),
	);
	
	protected $listConfig = array(
			'id' => '编号',
			'name' => '名称',
			'price' => '价格(元)',
			'oprice' => '原价(元)',
			'info' => '说明',
			'createtime' => '添加时间',
			array('操作', array('edit', 'delete')),
	);
	
	protected $searchConfig = array(

	);
	
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$this->_auto_process_data($value);
			$value['createtime'] = date('Y-m-d H:i', $value['createtime']);
		}
	}

	public function getPrice($oids, $ocounts, $aGoods) {
		$counts = array();
		foreach ($oids as $k => $id) {
			$counts[$id] += $ocounts[$k];
		}
		$ids = array_unique($oids);
		$aCounts = $counts;
		$tprice = 0;

		
		$aPacks = $this->where(array('status' => 0))->select();		//dump($aPacks);
		foreach ($aPacks as $aPack) {
			$pack = json_decode($aPack['combination'], true);
			$ismatch = true;
			foreach ($pack as $goods) {
				if (in_array($goods['id'], $ids)) {
					if ($goods['count'] > $aCounts[$goods['id']]) {
						$ismatch = false;
						break;
					} else {
						$aCounts[$goods['id']] -= $goods['count'];
					}
				} else {
					$ismatch = false;
					break;
				}
			}
			
			if (!$ismatch) {
				//不匹配则恢复数据
				$aCounts = $counts;
			} else {
				$tprice += $aPack['price'];
				$counts = $aCounts;
			}
		}
		
		foreach ($aGoods as $goods) {
			$tprice += $goods['price'] * $aCounts[$goods['id']];
		}

		return $tprice;
	}
	
}
?>