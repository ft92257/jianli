<?php 

/**
 * 预算模型
 *
 */
class User_budgetModel extends BaseModel {
	
	/*
	 * 验证form字段规则
	 */
	protected $aValidate = array(

	);
	
	/*
	 * 查询结果处理
	 */
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			//$value['createtime'] = date('Y-m-d H:i:s', $value['createtime']);
			//$value['avatar'] = getFileUrl($value['avatar']);
		}
	}
	
	public function getParent($type, $estimate = null) {
		$ret = $this->where(array('uid' => $this->oUser->id , 'name' => $type))->find();
		
		if (empty($ret)) {
			if ($estimate === null) {
				$estimate = (int) $this->oUser->info->budget_amount / 2;
			}
			$data = array(
				'uid' => $this->oUser->id,
				'name' => $type,
				'estimate' => $estimate,
			);
			
			$id = $this->addData($data);
			
			$ret = $this->getById($id);
		}
				
		return $ret;
	}
	
	public function getChildren($pid) {
		$ret = $this->where(array('uid' => $this->oUser->id , 'pid' => $pid))->select();
	
		return $ret;
	}
	
	public function updateChild($key, $value, $parent) {
		$where = array(
				'name' => $key,
				'uid' => $this->oUser->id,
				'pid' => $parent['id'],
			);
		
		$ret = $this->where($where)->find();
		if (empty($ret)) {
			$data = array(
					'uid' => $this->oUser->id,
					'pid' => $parent['id'],
					'estimate' => (int) $value,
					'name' => $key,
				);
			$this->addData($data);
		} else {
			$this->where($where)->data(array('estimate' => (int) $value))->save();
		}
	}
	
	public function getRealfee($pid) {
		return $this->where(array('pid' => $pid, 'uid' => $this->oUser->id))->select();
	}
}



?>