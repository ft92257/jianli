<?php 


class BaseModel extends Model {
	
	public $oApp;
	public $oUser;//用户信息
	
	//选项配置数组
	protected $aOptions = array(
	);
	//公共配置数据
	private $_aBaseOptions = array(
		'status' => array(
			'0' => '正常',
			'-2' => '已删除',
		),
	);
	
	//表单配置
	protected $formConfig = array();

	//列表配置
	protected $listConfig = array();
	
	//搜索配置
	protected $searchConfig = array();
	
	/*
	 * 验证规则，由$this->checkData调用
	 * $aValidate[0] 字段名,string $aValidate[1] 方法名，string $aValidate[2] 错误提示消息
 	 * array $aValidate[3] 其他参数, array $aValidate[4] 功能控制参数	
	 */
	protected $aValidate = array(
		//例：array('target', 'unique', '对不起，您已经评过分了！', array('uid' => '{uid}', 'type' => '{type}'), array('replace')),
	);
	//所有需要验证字段，临时存储
	private $_validateFields = null;
	
	public function __construct() {
		parent::__construct();
		
		$this->oApp = $_SESSION['admin_app'];
		$this->oUser = $_SESSION['admin_user'];
	}
	
	//删除方法
	public function del($condition) {
		return $this->where($condition)->data(array('status'=>-2))->save();
	}
	
	/*
	 * 获取一条记录
	 */
	public function get($where) {
		return $this->where($where)->find();
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
	public function ajaxValidate($field, $value) {
		$ret = $this->checkData(array($field => $value), false);
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
	 * 查询条件
	 * $where = array(
	 * 			'uid' => 2,//等于
	 * 			'id >' => 1,//大于
	 * 			'fid in' => '1,2,3',//in 可以用数字列表
				'type not in' => array(1,2,3),//或者数组
				'qq like' => "%r'rr%",
				'money between' => array(1,2),//或者 1,2格式
				"`account` = 'ffff'",//直接原始字符串
				'id >|uid <' => array(1,2),//or条件
				);
	 */
	public function w($where = array(), $addbase = true) {
		if (!is_array($where)) {
			return parent::where($where);
		}

		$s = $this->options['where']['_string'];
		foreach ($where as $key => $value) {
			$s .= $s ? " AND (" : "(";
			$s .= $this->_getWhere($key, $value) . ")";
		}
		if ($s) {
			$s = substr($s, 1, -1);
			$this->options['where']['_string'] = $s;
		}
		if ($addbase) {
			//$this->options['where']['status'] = 0;
			$this->options['where']['appid'] = $this->oApp->id;
		} else {
			//unset($this->options['where']['status']);
			unset($this->options['where']['appid']);
		}

		return $this;
	}
	
	protected function _filedWhere($key, $value) {
		$s = '';
		if (is_numeric($key)) {
			$s .= $value;
		} else {
			if (is_array($value)) {
				foreach ($value as &$val) {
					$val = $this->db->escapeString($val);
				}
			} else {
				$value = $this->db->escapeString($value);
			}
		
			$pos = strpos($key, ' ');
			if ($pos === false) {
				$s .= "`$key` = '$value'";
			} else {
				$opt = strtolower(strrchr($key, " "));
				$optall = '`' . substr($key, 0, $pos) . '`' . strtoupper(substr($key, $pos));
				if ($opt == ' in') {
					if (is_array($value)) {
						$value = "'" . join("','", $value) . "'";
					}
					$s .= "$optall ($value)";
				} elseif ($opt == ' between') {
					if (is_array($value)) {
						$value = "'" . $value[0] . "' AND '" . $value[1] . "'";
					}
					$s .= "$optall $value";
				} else {
					$s .= "$optall '$value'";
				}
			}
		}
		
		return $s;
	}
	
	protected function _getWhere($key, $value) {
		$aKeys = explode('|', $key);
		if (count($aKeys) > 1) {
			$ret = array();
			foreach ($aKeys as $i => $key) {
				$ret[] = $this->_filedWhere(trim($key, ' '), $value[$i]);
			}
			
			return join(' OR ', $ret);
		} else {
			return $this->_filedWhere($key, $value);
		}
	}
	
	/*
	 * 查询条件，添加基础字段
	 */
	public function where($condition, $parse=null) {
		if (is_array($condition)) {
			$base = array(
				//	'status' => 0,
					'appid' => $this->oApp->id,
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
	 * 获取表单字段
	 */
	public function getFormFields() {
		$keys = array();
		foreach ($this->formConfig as $field => $aValue) {
			if (!is_numeric($field) && $aValue[1] != 'span' && $aValue[1] != 'file') {
				$keys[] = $field;
			}
		}
		
		return $keys;
	}
	
	/*
	 * 获取表单提交的数据
	 */
	public function getFormData($oldData = array()) {
		$this->error = '';//清空错误信息
		$data = getRequestData($this->getFormFields());
		foreach ($this->formConfig as $field => $aValue) {
			switch ($aValue[1]) {
				case 'checkbox':
					$data[$field] = join(',', $data[$field]);
					break;
				case 'file':
					$aConfig = isset($aValue[3]['thumbs']) ? array('thumbs' => $aValue[3]['thumbs']) : array();
					$fileType = isset($aValue[3]['type']) ? $aValue[3]['type'] : 'image';
					$info = $this->_upload($field, $aConfig, $fileType);
					if($info['status'] == 0){
						$data[$field] = $info['data']['fileid'];
						if ($oldData[$field]) {
							//删除旧图
							D('File')->del(array('id' => $oldData[$field]));
						}
					} elseif ($info['status'] == -1) {
						//未上传时使用原来的值
						$data[$field] = empty($oldData) ? 0 : $oldData[$field];
					} else {
						$this->error = $info['msg'];
						$data[$field] = 0;
					}
					break;
				case 'password':
					$data[$field] = md5($data[$field]);
					break;
				case 'xiaoqu':
					$data[$field . '_name'] = getRequest($field . '_name');
					break;
				default:
					break;
			}
			if (is_array($aValue[2]) && in_array('once', $aValue[2]) && $oldData[$field]) {
				$data[$field] = $oldData[$field];
			}
		}

		return $data;
	}
	
	public function _upload($filename = 'filename', $aConfig = array(), $fileType = 'image') {
		if(!empty($_FILES[$filename]['name'])){
			$this->parseThumbConfig($aConfig);
		
			$info = D('File')->upload($filename, $fileType, $aConfig);
		
			return $info;
		} else {
			return array('status' => -1, 'msg' => '未上传文件！');
		}
	}
	
	/*
	 * 解析缩略图配置
	* //thumbs字段格式：120-120,40-60
	*/
	protected function parseThumbConfig(&$aConfig) {
		if (isset($aConfig['thumbs']) && $aConfig['thumbs']) {
			$aSuffix = array();
			$aWidth = array();
			$aHeight = array();
				
			$aThumbs = explode(',', $aConfig['thumbs']);
			foreach ($aThumbs as $value) {
				$aSuffix[] = '_' . $value;
				$aT = explode('-', $value);
				$aWidth[] = $aT[0];
				$aHeight[] = $aT[1];
			}
			$aConfig['thumb'] = true;
			$aConfig['thumbMaxWidth'] = join(',', $aWidth);
			$aConfig['thumbMaxHeight'] = join(',', $aHeight);
			$aConfig['thumbSuffix'] = join(',', $aSuffix);
	
			unset($aConfig['thumbs']);
		}
	}
	
	/*
	 * 获取表单html
	 */
	public function getFormHtml($data = array()) {
		if ($this->_validateFields === null) {
			$this->_validateFields = $this->_getValidateFields();
		}
		
		$s = '';
		foreach ($this->formConfig as $field => $aValue) {
			if (is_array($aValue)) {
				$s .= $this->getFieldHtml($field, $aValue, $data);
			} else {
				$s .= $aValue;
			}
		}
		
		return $s;
	}
	
	/*
	 * 获取所有需验证的字段
	 */
	protected function _getValidateFields() {
		$this->_validateFields = array();
		foreach ($this->aValidate as $aValue) {
			$this->_validateFields[] = $aValue[0];
		}
		
		return array_unique($this->_validateFields);
	}
	
	/*
	 * 绑定验证事件html
	 */
	protected function _bindValidate($field) {
		$validate = '';
		if (in_array($field, $this->_validateFields)) {
			$validate = ' onblur="ajaxValidate(this)"';
		}
		
		return $validate;
	}
	
	/*
	 * 获取单个字段html 
	 */
	protected function getFieldHtml($field, $aValue, $data) {
		$validate = $this->_bindValidate($field);
		$captionClass = isset($data['_caption_class']) ? $data['_caption_class'] : 'Caption';
		
		if (is_array($aValue[0])) {
			$caption = $aValue[0][0];
			if ($aValue[0][1] == 'APPEND' || $aValue[0][1] == 'END') {
				$html = $caption;
			}
		} else {
			$caption = $aValue[0];
		}
		if (!isset($html)) {
			$html = '<tr><td class="'.$captionClass.'">' . $caption . '</td><td>';
		}
		
		$val = isset($data[$field]) ? $data[$field] : '';
		
		$explain = '';//字段说明
		$long = '';//长text框
		$int = '';//只允许输入数字 text
		$br = '';//单选项每项一行 radio
		$all = '';//选择框请选择 select
		$once = '';//值为空才允许编辑 text,textarea
		if (is_array($aValue[2])) {
			foreach ($aValue[2] as $operate) {
				switch ($operate) {
					case 'long':
						$long = ' style="width:371px;"';
						break;
					case 'int':
						$int = ' onkeyup="this.value=this.value.replace(/\D/g,\'\')" onafterpaste="this.value=this.value.replace(/\D/g,\'\')"';
						break;
					case 'br':
						$br = '<br>';
						break;
					case 'all':
						$all = '<option value="">请选择'.$caption.'</option>';
						break;
					case 'once':
						$once = $val!=='' ? ' disabled="disabled"' : '';
						break;
					default:
						$explain = $operate;
						break;
				}
			}
		} else {
			$explain = $aValue[2];
		}
		
		switch ($aValue[1]) {
			case 'text':
				$value = $val ? ' value="'.$val.'"' : '';
				$html .= '<input type="text"'.$validate.$long.$int.$once.' name="' . $field . '"'.$value.' />';
				break;
			case 'radio':
				foreach ($this->getOptions($field) as $i => $option) {
					$checked = $val !== '' && $val == $i ? ' checked="checked"' : '';
					$html .= '<input type="radio" '.$checked.' name="' . $field . '" value="' . $i . '" />' . $option . '&nbsp;&nbsp;' . $br;
				}
				break;
			case 'select':
				$html .= '<select'.$validate.$once.' name="'.$field.'">';
				$html .= $all;
				foreach ($this->getOptions($field) as $i => $option) {
					$selected = $val !== '' && $val == $i ? ' selected="selected"' : '';
					$html .= '<option '.$selected.'value="'.$i.'">'.$option.'</option>';
				}
				$html .= '</select>';
				break;
			case 'date':
				$value = $val ? ' value="'.$val.'"' : '';
				$html .= '<input type="text" '.$validate.$value.'name="' . $field . '" onfocus="HS_setDate(this)" />';
				break;
			case 'textarea':
				$html .= '<textarea '.$validate.$once.' name="' . $field . '" rows="6" cols="50">'.$val.'</textarea>';
				break;
			case 'richtext':
				$html .= '<textarea '.$validate.$once.' id="richtext_'.$field.'" name="' . $field . '" style="width:100%;height:500px;">'.$val.'</textarea>';
				$html .= '
				<script>
				KindEditor.ready(function(K) {
					window.editor = K.create("#richtext_'.$field.'", {
						"uploadJson" : "'.U('/Richtext/upload').'"
					});
				});
				</script>';
				break;
			case 'file':
				if ($val) {
					$html .= '<img src="'.$val.'" width="100" height="100" />';
				}
				$html .= '<input type="file" name="'.$field.'" />';
				break;
			case 'span':
				$html .= '<span>'.$val.'</span>';
				break;
			case 'tags':
				//已选中的html
				$html .= D('Tag')->getSelectedHtml($val, $field);
				//可选热门标签html
				$html .= D('Tag')->getHotHtml($val, get_class($this));
				break;
			case 'submit':
				$html .= '<input type="submit" value=" 保 存 " />';
				break;
			case 'province':
				$html .= D('District')->getProvinceHtml($val, $field);
				break;
			case 'city':
				$html .= D('District')->getCityHtml($val, $field);
				break;
			case 'county':
				$html .= D('District')->getCountyHtml($val, $field);
				break;
			case 'town':
				$html .= D('District')->getTownHtml($val, $field);
				break;
			case 'checkbox':
				$aVals = explode(',', $val);
				foreach ($this->getOptions($field) as $i => $option) {
					$checked = $val !== '' && in_array($i, $aVals) ? ' checked="checked"' : '';
					
					$html .= '<input type="checkbox" '.$checked.' name="' . $field . '[]" value="' . $i . '" />' . $option . '&nbsp;&nbsp;' . $br;
				}
				break;
			case 'password':
				$html .= '<input type="password" name="' . $field . '" />';
				break;
			case 'xiaoqu':
				$html .= '<input type="text" name="'.$field.'_name" value="'.$data[$field . '_name'] .'" onkeyup="selectXq(this)" />
					<div id="xqs_sdiv" style="position:absolute;background:#fff;border:1px solid #999;width:300px;height:150px;display:none;overflow-y:auto;"></div>
					<input type="hidden" name="' . $field . '" value="'.$val.'" />';
				break;
			default:
				$html .= $aValue[1];
				break;
		}
	
		$html .= ' <span>' . $explain . '</span>';
	
		if (is_array($aValue[0])) {
			if ($aValue[0][1] == 'BEGIN') {
				return $html;
			} elseif ($aValue[0][1] == 'APPEND') {
				return $html ;
			} elseif ($aValue[0][1] == 'END') {
				return $html . '</td></tr>' . "\n";
			}
		}
		
		return $html . '</td></tr>' . "\n";
	}
	
	/*
	 * 自动处理数据
	 */
	protected function _auto_process_data(&$resultSet) {
		foreach ($resultSet as $key => $value) {
			$arr = $this->getOptions($key);
			if (!empty($arr)) {
				$resultSet[$key] = $arr[$value];
				$resultSet[$key . '_o'] = $value;
			}
		}
	}
	
	/*
	 * 获取分页列表的搜索栏html
	 */
	public function getSearchHtml() {
		$s = '';
		foreach ($this->searchConfig as $field => $config) {
			$s .= '<tr><td>'.$config[0].'</td><td>';
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
					$val = getRequest($field);
					$params = array_merge($_GET, $_POST);
					unset($params['_URL_']);
					unset($params[$field]);
					$url = U('', $params) . '/'.$field.'/';
					if ($val === '') {
						$s .= '全部&nbsp;&nbsp;';
					} else {
						$s .= '<a href="'.$url.'">全部</a>&nbsp;&nbsp;';
					}
					foreach ($this->getOptions($field) as $i => $option) {
						$url = U('', $params) . '/'.$field.'/'.$i;
						if ($val !== '' && $val == $i) {
							$s .= $option . '&nbsp;&nbsp;';
						} else {
							$s .= '<a href="'.$url.'">'.$option.'</a>&nbsp;&nbsp;';
						}
					}
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
				case 'checkbox':
					$val = getRequest($field);
					$val = empty($val) ? array() : $val;
					$checked = in_array('ALL', $val, true) ? ' checked="checked"' : '';
					$s .= '<label for="'.$field.'_ALL">全部</label><input type="checkbox" onclick="checkAll(this)"'.$checked.' id="'.$field.'_ALL" name="'.$field.'[]" value="ALL" />&nbsp;&nbsp;';
					foreach ($this->getOptions($field) as $i => $option) {
						$checked = in_array((string)$i, $val, true) ? ' checked="checked"' : '';
						$s .= '<label for="'.$field.'_'.$i.'">'.$option.'</label>' . '<input type="checkbox" onclick="checkNotAll(this)"'.$checked.' id="'.$field.'_'.$i.'" name="'.$field.'[]" value="'.$i.'" />&nbsp;&nbsp;';
					}
					break;
				default:
					$s .= $config[1];
					break;
			}
			$s .= '</td></tr>';
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
				case 'checkbox':
					$val = getRequest($field);
					$k = array_search('ALL', $val);
					if ($k !== false) {
						unset($val[$k]);
					}
					if (!empty($val)) {
						$search[$field] = array('in', $val);
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
	
	/*
	 * 获取列表html
	 */
	public function getListHtml($data, $vars = array()) {
		$captionClass = isset($vars['_caption_class']) ? $vars['_caption_class'] : 'Caption';
		
		$s = '<tr class="' . $captionClass . '">';
		foreach ($this->listConfig as $field => $config) {
			if (!is_array($config)) {
				$s .= '<td>' . $config . '</td>';
			} else {
				$s .= '<td>' . $config[0] . '</td>';
			}
		}
		$s .= '</tr>';
		
		foreach ($data as $aValue) {
			$s .= '<tr>';
			foreach ($this->listConfig as $field => $config) {
				if (is_array($config[1])) {
					$s .= '<td>'. $this->getOperationHtml($config, $aValue, $field, $vars) .'</td>';
				} else {
					$s .= '<td>' . $aValue[$field] . '</td>';
				}
			}

			$s .= '</tr>' . "\n";
		}
		
		return $s;
	}
	
	/*
	 * 获取操作选项html
	*/
	protected function getOperationHtml($config, $aValue, $field, $vars) {
		$s = '';
		if (is_array($config[1])) {
			foreach ($config[1] as $k => $operate) {
				if (!is_numeric($k)) {
					$cfg = $operate;
					$operate = $k;
				}
	
				$s .= '&nbsp;';
				switch ($operate) {
					case 'edit':
						$s .= '<a href="'.__URL__ . '/edit/id/' . $aValue['id'] .'">编辑</a>';
						break;
					case 'delete':
						$s .= '<a href="'.__URL__ . '/delete/id/' . $aValue['id'] .'" onclick="if(!confirm(\'确定删除该记录？\')){return false;}">删除</a>';
						break;
					case 'picture':
						$s .= '<a href="'.__URL__ . '/picture/id/' . $aValue['id'] .'">图库</a>';
						break;
					case 'img';
					$s .= '<img src="'.$aValue[$field].'" width="80" height="80" />';
					break;
					case 'textarea':
						$url = $this->getHref($cfg, $aValue, $vars);
						$s .= '<textarea style="width:100%;height:100%" field="'.$field.'" oldval="'.$aValue[$field].'" onblur="autoUpdate(this, \''.$url.'\')">'.$aValue[$field].'</textarea>';
						break;
					case 'text':
						$url = $this->getHref($cfg, $aValue, $vars);
						$s .= '<input type="text" style="width:100%;" field="'.$field.'" oldval="'.$aValue[$field].'" onblur="autoUpdate(this, \''.$url.'\')" value="'.$aValue[$field].'" />';
						break;
					case 'select':
						$url = $this->getHref($cfg, $aValue, $vars);
						$s .= '<select name="'.$field.'" oldval="'.$aValue[$field . '_o'].'" onchange="autoUpdate(this, \''.$url.'\')">';
						$s .= '<option value=""></option>';
						foreach ($this->getOptions($field) as $i => $option) {
							$checked = $aValue[$field. '_o'] !== '' && $aValue[$field. '_o'] == $i ? ' selected="selected"' : '';
							$s .= '<option '.$checked.' value="'.$i.'">'.$option.'</option>';
						}
						$s .= '</select>';
						break;
					default:
						if (is_array($operate)) {
							//处理链接地址
							$href = $this->getHref($operate[0], $aValue, $vars);
								
							//提示确认信息
							$confirm = '';
							if (isset($operate[2]['confirm'])) {
								$confirm = ' onclick="if(!confirm(\''.$operate[2]['confirm'].'\')){return false;}"';
							}
								
							//选中状态判断
							$checked = false;
							if (isset($operate[2]['checked'])) {
								$expression = $operate[2]['checked'];
								_replaceValue($expression, $aValue);
								_replaceValue($expression, $vars, '[', ']');
	
								//echo "\$checked = ($expression);";
								eval("\$checked = ($expression);");
							}
							if ($checked) {
								$s .= $operate[1];
							} else {
								$s .= '<a'.$confirm.' href="'.$href.'">'.$operate[1].'</a>';
							}
						} else {
							//非数组则输出原始字符串
							_replaceValue($operate, $aValue);
							_replaceValue($operate, $vars, '[', ']');
							$s .= $operate;
						}
						break;
				}
				$s .= '&nbsp;';
			}
		}
	
		return $s;
	}
	
	
	
	/*
	 * 处理链接地址
	*/
	protected function getHref($href, $aValue, $vars) {
		if (substr($href, 0, 7) == '__URL__') {
			$urlbase = __URL__;
			$href = substr($href, 7);
		} else {
			$urlbase = __APP__ ;
		}
		_replaceValue($href, $aValue);
		_replaceValue($href, $vars, '[', ']');
	
		return $urlbase.$href;
	}
	
	/*
	 * 获取redis单一实例
	 */
	public function getRedis() {
		if (!C('REDIS_OPEN') || !$this->useRedis || !class_exists('Redis', false)) {
			return null;	
		}
		
		if (self::$soRedis === null) {
			$redis = new Redis();
			$redis->connect('127.0.0.1', 6379);
			
			self::$soRedis = $redis;
		}
		
		return self::$soRedis;
	}
	
	/*
	 * 更新redis缓存
	 */
	protected function _after_update($data,$options) {
		$this->updateRedisCache($data, $options);
	}
	
	protected function updateRedisCache($data, $options, $ret = null) {
		$redis = $this->getRedis();
		if ($redis !== null) {
			if (isset($data['status']) && $data['status'] == -2) {
				//删除操作
				$id = $options['where']['id'];
				if ($id) {
					$key = $this->trueTableName . ':' . $id;
					$redis->del($key);
				}
			} else {
				if ($ret === null) {
					$this->options = $options;
					$ret = $this->select();
				}
				
				foreach ($ret as $aValue) {
					$key = $this->trueTableName . ':' . $aValue['id'];
					if ($redis->exists($key)) {
						$redis->hmset($key, $aValue);
					}
				}
			}
		}
	}
	
}
?>