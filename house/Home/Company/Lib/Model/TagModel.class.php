<?php 
/**
 * 	标签模型
 */
class TagModel extends BaseModel {
	protected $aValidate = array(
			array('type', 'required', '标签类型不能为空！'),
			array('content', 'required', '请填写标签内容！'),
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
		}
	}
	
	/*
	 * 获取已选中标签的html
	 */
	public function getSelectedHtml($val, $field = 'tags') {
		$html = '<div class="selectedTags">已贴标签';
		$html .= '<input type="hidden" name="'.$field.'" value="'.$val.'" />';
		$aTags = explode('|', $val);
		foreach ($aTags as $tag) {
			if ($tag) {
				$html .= '<span onclick="deleteTag(this)">' . $tag . '</span>';
			}
		}
		$html .= '</div>';
		
		return $html;
	}
	
	/*
	 * 获取可选择热门标签的html
	 */
	public function getHotHtml($val, $classname) {
		switch ($classname) {
			case 'ActiveModel':
				$type = 1;
				break;
			case 'CaseModel':
				$type = 2;
				break;
			case 'CompanyModel':
				$type = 3;
				break;
			case 'ConstructionModel':
				$type = 4;
				break;
			default:
				$type = 0;
				break;
		}
		$data = $this->field('content')->where(array('type' => $type))->order('usecount DESC')->limit(10)->select();
		
		//已贴的标签
		$aTags = explode('|', $val);
		foreach ($aTags as $k => $tag) {
			if (!$tag) {
				unset($aTags[$k]);
			}
		}
		
		$html = '<div class="hotTags">选择标签：';
		$html .= '<input type="text" /><input type="button" value="贴上" tagType="'.$type.'" onclick="pasteTag(this)" /><br>';
		foreach ($data as $value) {
			if (in_array($value['content'], $aTags)) {
				$html .= '<span class="pasted" tagType="'.$type.'">' . $value['content'] . '</span>';
			} else {
				$html .= '<span onclick="pasteTag(this)" tagType="'.$type.'">' . $value['content'] . '</span>';
			}
		}
		$html .= '</div>';
		
		return $html;
	}
	
}
?>