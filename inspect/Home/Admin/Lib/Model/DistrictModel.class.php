<?php 
/*
 * 省市区模型
 */
class DistrictModel extends BaseModel {
	
	protected $aValidate = array(
	);
	
	protected $aOptions = array(
	);
	
	protected $formConfig = array(
	);
	
	protected $listConfig = array(
	);
	
	protected $searchConfig = array(
	);
	
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			//$this->_auto_process_data($value);
		}
	}

	/*
	 * 获取省html
	 */
	public function getProvinceHtml($val = '', $field = 'province') {
		$html = '<select name="'.$field.'" onchange="getAreaChildren(this, 2)">';
		$html .= '<option value="">请选择省</option>'."\n";
		$html .= $this->_getOptionHtml(0, $val, 1, $field);
		$html .= '</select>';
		
		return $html;
	}
	
	/*
	 * 获取市html
	*/
	public function getCityHtml($val, $field = 'city') {
		$options = $this->_getOptionHtml(0, $val, 2, $field);
		if (!$options) {
			$hide = ' style="display:none;"';
		}
		
		$html = '<select'.$hide.' name="'.$field.'" onchange="getAreaChildren(this, 3)">';
		$html .= '<option value="">请选择市</option>'."\n";
		$html .= $options;
		$html .= '</select>';
		
		return $html;
	}
	
	/*
	 * 获取区html
	*/
	public function getCountyHtml($val, $field = 'county') {
		$options = $this->_getOptionHtml(0, $val, 3, $field);
		if (!$options) {
			$hide = ' style="display:none;"';
		}
		
		$html = '<select'.$hide.' name="'.$field.'" onchange="getAreaChildren(this, 4)">';
		$html .= '<option value="">请选择区县</option>'."\n";
		$html .= $options;
		$html .= '</select>';
		
		return $html;
	}
	
	public function getTownHtml($val, $field = 'town') {
		$options = $this->_getOptionHtml(0, $val, 4, $field);
		if (!$options) {
			$hide = ' style="display:none;"';
		}
		
		$html = '<select'.$hide.' name="'.$field.'">';
		$html .= '<option value="">请选择街道</option>'."\n";
		$html .= $options;
		$html .= '</select>';
		
		return $html;
	}
	
	protected function _getOptionHtml($upid, $val, $level, $field) {
		if ($level == 1) {
			$where = "level = 1";
		} else {
			if (!$upid) {
				$aDist = $this->where('id = ' . intval($val))->find();
				if (empty($aDist)) {
					return '';
				} else {
					$upid = $aDist['upid'];
				}
			}
			$where = "upid = '$upid'";
		}
		$data = $this->where($where)->order('ord DESC')->select();
		foreach ($data as $value) {
			$selected = $val == $value['id'] ? ' selected="selected"' : '';
			$html .= '<option'.$selected.' value="'.$value['id'].'">'.$value['name'].'</option>'."\n";
		}
		
		return $html;
	}
	
	public function getChildren($upid, $level) {
		switch ($level) {
			case 1:
				$name = '省';
				break;
			case 2:
				$name = '市';
				break;
			case 3:
				$name = '区';
				break;
			case 4:
				$name = '街道';
				break;
			default:
				return '';
		}
		$html = '<option value="">请选择'.$name.'</option>'."\n";
		$html .= $this->_getOptionHtml($upid, '', $level);
		return $html;
	}
}
?>