<?php 


class BaseModel extends Model {
	
	public $oApp;
	public $oCom;//公司信息
	//搜索配置
	protected $searchConfig = array();
	//选项配置数组
	protected $aOptions = array(
	);
	//公共配置数据
	private $_aBaseOptions = array(
		'status' => array(
			'0' => '正常',
			'-2' => '删除',
		),
	);
	
	/*
	 * 验证规则，由$this->checkData调用
	 * $aValidate[0] 字段名,string $aValidate[1] 方法名，string $aValidate[2] 错误提示消息
 	 * array $aValidate[3] 其他参数, array $aValidate[4] 功能控制参数	
	 */
	protected $aValidate = array(
		//例：array('target', 'unique', '对不起，您已经评过分了！', array('uid' => '{uid}', 'type' => '{type}'), array('replace')),
	);
	
	public function __construct() {
		parent::__construct();
		
		$this->oApp = $_SESSION['app'];
		$this->oCom = $_SESSION['company'];
	}
	
	//删除方法
	public function del($condition) {
		return $this->where($condition)->data(array('status'=>-2))->save();
	}
	
	/*
	 * 根据id获取数组数据
	 * @return array 
	 */
	public function getById($id, $pk = 'id') {
		$id = (int) $id;
		if ($id <= 0) {
			return array();
		}
		
		return $this->where(array($pk => $id))->find();
	}
	
	/*
	 * 根据id获取数据对象
	 */
	public function getObjectById($id, $pk = 'id') {
		$data = $this->getById($id, $pk);

		return empty($data) ? null : (object) $data;
	}
	
	/*
	 * 检测并添加数据
	 */
	public function addData($data, $addbase = true) {
		if ($this->checkData($data)) {
			if ($addbase) {
				$data['appid'] = $this->oApp->id;
				$data['createtime'] = time();
			}
			
			$ret = $this->add($data);
			if ($ret) {
				return $ret;
			} else {
				$this->error = '数据库添加失败,请检查数据格式！';
				return false;
			}
		} else {
			return false;
		}
	}
	
	/*
	 * 验证单个字段
	 */
	public function ajaxValidate($name) {
		$ret = $this->checkData(array($name => getRequest($name)), false);
		if (!$ret) {
			return $this->error;
		} else {
			return '';
		}
	}
	
	/*
	 * @param $checkall true 验证所有规则，false只验证存在的字段
	 */
	public function checkData($data, $checkall = true) {
		import('Public.Library.Validate', './');
		$oValidate = new Validate($this);

		foreach ($this->aValidate as $key => &$aValue) {
			if (!$checkall && !in_array($aValue[0], array_keys($data))) {
				//如果只验证存在的字段，则删除无关的规则
				unset($this->aValidate[$key]);
			} else {
				//验证规则
				$func = $aValue[1];
				
				if (isset($aValue[4]) && in_array('replace', $aValue[4])) {
					//$data内部数据调用处理 例：{uid} 替换为 $data['uid']
					$this->replaceValue($aValue, $data);
				}
				
				if (!$oValidate->$func($aValue[0], $data, $aValue)) {
					return false;
				}
			}
		}
		
		return true;
	}
	
	/*
	 * $data内部数据调用处理 例：{uid} 替换为 $data['uid']
	 */
	protected function replaceValue(&$aValue, $data) {
		foreach ($aValue as $key => &$mVal) {
			if ($key > 1) {
				_replaceValue($mVal, $data);
			}
		}
	}
	
	
	/*
	 * 查询条件，添加基础字段
	 */
	public function where($condition, $parse=null) {
		if (is_array($condition) && !array_key_exists('tb_case.appid',$condition)) {
			$base = array(
						'appid' => $this->oApp->id,
						'status' => 0,
					);
			$condition = array_merge($base, $condition);
		}
		
		
		return parent::where($condition, $parse);
	}
	
	/*
	 * 获取配置数据
	 */
	public function getOptions($option_name, $key = NULL, $default = '') {
		$options = array_merge($this->_aBaseOptions, $this->aOptions);
		$arr = isset($options[$option_name]) ? $options[$option_name] : array();
		if ($key === NULL) {
			return $arr;
		} else {
			return isset($arr[$key]) ? $arr[$key] : $default;
		}
	}
	/*
	 * 获取分页列表的搜索栏html
	*/
	public function getSearchHtml() {
		$s = '';
		foreach ($this->searchConfig as $field => $config) {
			$s .= '<div class="box_hang"><div class="tiaojian">'.$config[0].'</div>';
			switch ($config[1]) {
				case 'text':
					$s .= '<input type="text" name="'.$field.'" value="'.getRequest($field).'" />';
					break;
				case 'text_submit':
					$s .= '<input type="text" name="'.$field.'" value="'.getRequest($field).'" />&nbsp;<input type="submit" value="查询">';
					break;
				case 'date':
					$s .= '<input type="text" name="'.$field.'_BEGIN" value="'.getRequest($field . '_BEGIN').'" style="width:68px;" onfocus="HS_setDate(this)" />至';
					$s .= '<input type="text" name="'.$field.'_END" value="'.getRequest($field . '_END').'" style="width:68px;" onfocus="HS_setDate(this)" />&nbsp;<input type="submit" value="查询">';
					break;
				case 'radio':
					$val = getRequest($field);
					$checked = $val === '' ? ' checked="checked"' : '';
					$s .= '<input type="radio" '.$checked.' name="' . $field . '" value="" />全部&nbsp;&nbsp;';
					foreach ($this->getOptions($field) as $i => $option) {
						$checked = $val !== '' && $val == $i ? ' checked="checked"' : '';
						$s .= '<input type="radio" '.$checked.' name="' . $field . '" value="' . $i . '" />' . $option . '&nbsp;&nbsp;';
					}
					break;
				case 'radio_list':
					$s .= '<div class="tiaojian_list">';
					$val = getRequest($field);
					$params = array_merge($_GET, $_POST);
					unset($params['_URL_']);unset($params['p']);
					unset($params[$field]);
					$url = U('', $params) . '/'.$field.'/';
					if ($val === '') {
						$s .= '<a class="alistmoren">全部</a>';
					} else {
						$s .= '<a href="'.$url.'">全部</a>';
					}
					foreach ($this->getOptions($field) as $i => $option) {
						$url = U('', $params) . '/'.$field.'/'.$i;
						if ($val !== '' && $val == $i) {
							$s .= '<a class="alistmoren">'.$option . '</a>';
						} else {
							$s .= '<a href="'.$url.'">'.$option.'</a>';
						}
					}
					$s .= '</div>';
					break;
				case 'select':
					$val = getRequest($field);
					$checked = $val === '' ? ' selected="selected"' : '';
					$s .= '<select name="'.$field.'" onchange="this.parentNode.parentNode.parentNode.parentNode.parentNode.submit();">';
					$s .= '<option value="">全部</option>';
					foreach ($this->getOptions($field) as $i => $option) {
						$checked = $val !== '' && $val == $i ? ' selected="selected"' : '';
						$s .= '<option '.$checked.' value="'.$i.'">'.$option.'</option>';
					}
					$s .= '</select>';
					break;
				default:
					$s .= $config[1];
					break;
			}
			$s .= '</div>';
		}
	
		return $s;
	}
	
	/*
	 * 获取搜索条件
	*/
	public function getSearchCondition() {
		$search = array();
		foreach ($this->searchConfig as $field => $config) {
			switch ($config[1]) {
				case 'text':
				case 'text_submit':
					$val = getRequest($field);
					if ($val !== '') {
						$search[$field] = array('like', '%' . $val . '%');
					}
					break;
				case 'date':
					$begin = getRequest($field . '_BEGIN');
					$end = getRequest($field . '_END');
					if ($begin || $end) {
						$begin = (int) strtotime($begin);
						if ($end) {
							$end = strtotime($end . ' 23:59:59');
						} else {
							$end = strtotime(date('Y-m-d') . ' 23:59:59');
						}
						$search[$field] = array('between', array($begin, $end));
					}
					break;
				default:
					$val = getRequest($field);
					if ($val !== '') {
						$search[$field] = $val;
					}
					break;
			}
		}
	
		return $search;
	}
	
}
?>