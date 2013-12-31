<?php 

/*
 * 验证类
 * 所有方法都带3个参数 字段名称$key, $data 键值对数组, 验证规则数组$aValidate
 * $aValidate[0] 字段名,string $aValidate[1] 方法名，string $aValidate[2] 错误提示消息
 * array $aValidate[3] 其他参数, array $aValidate[4] 功能控制参数
 */

class Validate {
	
	protected $model;
	
	public function __construct($model) {
		$this->model = $model;
	}
	
	protected function error($msg, $default = '') {
		$error = empty($msg) ? $default : $msg;
		$this->model->setProperty('error', $error);

		return false;
	}
	
	/*
	 * 验证字段值是否唯一，即不存在该值得记录
	 * @param $key 验证字段的名称
	 * @param $data 键值对数组
	 * @param $aValidate 验证规则数组 
	 */
	public function unique($key, $data, $aValidate) {
		$value = $data[$key];
		
		$condition = array(
			$key => $value,
		);

		if (isset($aValidate[3]) && is_array($aValidate[3])) {
			$condition = array_merge($condition, $aValidate[3]);
		}

		$ret = $this->model->where($condition)->find();
		if (empty($ret)) {
			//记录不存在，验证通过
			return true;
		} else {
			return $this->error($aValidate[2], $key . '记录已存在！');
		}
	}
	
	/*
	 * 不能为空
	 */
	public function required($key, $data, $aValidate) {
		$value = $data[$key];
		
		if (empty($value)) {
			return $this->error($aValidate[2], $key . '值必需！');
		} else {
			return true;
		}
	}
	
	/*
	 * email
	 */
	public function email($key, $data, $aValidate) {
		$value = $data[$key];

		if (preg_match("/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/", $value)) {
			return true;
		} else {
			return $this->error($aValidate[2], 'Email格式不正确！');
		}
	}
	
	/*
	 * 手机号
	 */
	public function mobile($key, $data, $aValidate) {
		$value = $data[$key];
		
		if (preg_match("/^1[358]\d{9}$/", $value)) {
			return true;
		} else {
			return $this->error($aValidate[2], '手机号码格式不正确！');
		}
	}
	
	/*
	 * 验证重复输入是否相等
	 */
	public function repeat($key, $data, $aValidate) {
		$value = $data[$key];

		if ($value == $data[$aValidate[3]]) {
			return true;
		} else {
			return $this->error($aValidate[2], $key . '确认不正确！');
		}
	}
}
?>