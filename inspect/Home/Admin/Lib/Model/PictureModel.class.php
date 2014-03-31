<?php 
/**
 * 	图片表模型
 */
class PictureModel extends BaseModel {

	protected $aValidate = array(
			array('fid', 'required', '请选择图片！'),
	);
	
	protected $formConfig = array(
			'fid' => array('选择图片', 'file', '', array('thumbs' => '80-80,650-426')),
			'title' => array('说明', 'textarea'),
			array('', 'submit'),
	);
	
	protected $listConfig = array(
			'id' => '编号',
			'fid' => array('图片', array('img')),
			'title' => array('说明', array('textarea' => '/Picture/autoUpdate/id/{id}')),
			'createtime' => '添加时间',
			'ord' => array('排序', array('text' => '/Picture/autoUpdate/id/{id}')),
			array('操作', array(
					array('/Picture/edit/id/{id}', '编辑'),
					array('/Picture/delete/id/{id}', '删除', array('confirm' => '确定要删除该图片吗？')), 
				//	array('__URL__/focus/id/{id}', '设为焦点图', array('checked' => "'{fid_o}'=='[focus]'")),
					)),
		);
	
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$value['fid_o'] = $value['fid'];
			$value['fid'] = getFileUrl($value['fid'], '80-80');
			$value['createtime'] = date('Y-m-d H:i', $value['createtime']);
		}
	}
	
	/*
	 * 获取图片信息所属对象
	 */
	public function getTarget($type, $target) {
		$aType = array(
			'4' => 'Inspect_report',
		);
		
		if (isset($aType[$type])) {
			$data = D($aType[$type])->getById($target);
		} else {
			$data = array();
		}

		return $data;
	}
	
	public function getTabTitle($type, $target, $data) {
		$tab = '';
		if ($type == 4) {
			$tab = '<a href="'.U('/Report/index').'">验房报告</a> >> <a href="'.U('/Report/picture/id/' . $target).'">' . $data['title']. '</a>';
		}
		
		return $tab;
	}
	
	public function getListUrl($type, $target) {
		switch ($type) {
			case 4:
				$url = U('/Report/picture/id') . '/' . $target;
				break;
			default:
				$url = '';
				break;
		}
		return $url;
	}
}
?>