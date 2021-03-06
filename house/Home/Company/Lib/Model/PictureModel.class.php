<?php 
/**
 * 	图片表模型
 */
class PictureModel extends BaseModel {
	protected $aValidate = array(
			array('fid', 'required', '请选择图片！'),
	);
	
	
	protected $aOptions = array(
		'type' => array('1' => '材料图', '2' => '案列图', '3' => '效果图','4'=>'平面图'),
	    'step'=>array('1'=>'隐蔽','2'=>'泥木','3'=>'油漆','4'=>'安装','5'=>'软装','6'=>'竣工'),
	);
	protected $formConfig = array(
			'fid' => array('选择图片', 'file', '', array('thumbs' => '88-88,540-340,200-200')),
			'title' => array('说明', 'textarea'),
			array('', 'submit'),
	);


	
	protected $listConfig = array(
				'id' => '编号',
				'fid' => array('图片', array('img')),
				'title' => '说明',
				'type' => '类型',
				'createtime' => '添加时间',
				array('操作', array(
						array('/Picture/edit/id/{id}', '编辑'),
						array('/Picture/delete/id/{id}', '删除', array('confirm' => '确定要删除该图片吗？')), 
						array('__URL__/focus/id/{id}', '设为焦点图', array('checked' => "'{fid_o}'=='[focus]'")),
						)),
				);

protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			//$value['fid_o'] = $value['fid'];
			$value['fid'] = getFileUrl($value['fid'], '88-88');
			$value['type'] = $this->getOptions('type', $value['type']);
			$value['step'] = $this->getOptions('step', $value['step']);
			$value['createtime'] = date('Y-m-d H:i', $value['createtime']);
		}
	}
	/*
	 * 获取图片信息所属对象
	 */
	
	public function getTarget($type, $target) {
		switch ($type) {
			case 1:
				$data = D('Active')->getById($target);
				break;
			case 2:
				$data = D('Case')->getById($target);
				break;
			case 3:
				$data = D('Case')->getById($target);
				break;
			case 4:
				$data = D('Case')->getById($target);
				break;
			default:
				$data = array();
				break;
		}
		return $data;
	}

	public function getTabTitle($type, $target, $data) {
		$tab = '';
		if ($type == 1) {
			$tab = '<a href="'.U('/Active/index').'">活动</a> >> <a href="'.U('/Active/picture/id/' . $target).'">' . $data['title']. '</a>';
		} elseif ($type == 2) {
			$tab = '<a href="'.U('/Case/index').'">案例</a> >> <a href="'.U('/Case/picture/id/' . $target).'">' . $data['name'] . '</a>';
		} elseif ($type == 3) {
			$tab = '<a href="'.U('/House/index').'">样板房</a> >> <a href="'.U('/House/picture/id/' . $target).'">' . $data['name']. '</a>';
		}
		
		return $tab;
	}
	
	public function getListUrl($type, $target) {
		switch ($type) {
			case 1:
				$url = U('/Active/picture/id') . '/' . $target;
				break;
			case 2:
				$url = U('/Case/picture/id') . '/' . $target;
				break;
			case 3:
				$url = U('/Case/picture/id') . '/' . $target;
				break;
			case 4:
				$url = U('/Case/picture/id') . '/' . $target;
				break;
			default:
				$url = '';
				break;
		}
		return $url;
	}
	public function setConfig($type) {
		if($type == 2){
			$this->aOptions['step'] = array('1'=>'隐蔽','2'=>'泥木','3'=>'油漆','4'=>'安装','5'=>'软装','6'=>'竣工');
			$this->formConfig = insertArray($this->formConfig, 'fid', array('step' =>array('选择阶段','select',array('all'))));
			//$this->listConfig = insertArray($this->listConfig, 'fid', array('step' => '阶段'));
		}
		if($type == 3 || $type == 4){
			return ture;
			//$this->aOptions['type'] = array('3' => '效果图','4'=>'平面图');
			//$this->formConfig = insertArray($this->formConfig, 'fid', array('type' =>array('选择类型','select',array('all'))));
		}
	} 
	public function setMaterialConfig() {
		$this->listConfig = array(
				'id' => '编号',
				'fid' => array('图片', array('img')),
				'type' => '类型',
				'title' => '说明',
				'createtime' => '添加时间',
				array('操作', array(
						array('/Picture/edit/id/{id}', '编辑'),
						array('/Picture/delete/id/{id}', '删除', array('confirm' => '确定要删除该图片吗？')), 
						)),
			);
	}
	
	
}
?>