<?php 

/**
 * 图文编辑模型
 */
class RichtextModel extends BaseModel {
	
	/*
	 * 验证form字段规则
	 */
	protected $aValidate = array(
		array('menu', 'required', '请填写菜单名称！'),
		array('menu', 'unique', '菜单名不能重复！'),
		array('title','required','请填写标题名称！'),
		array('text', 'required', '请填写文章内容！'),
	);
	
	/*
	 * 获取父菜单
	 */
	public function getParentMenus($id) {
		$data = $this->where(array('parentid' => 0))->select();
		$ret = array();
		foreach($data as $value) {
			$ret[] = array(
						'menu' => $value['menu'],
						'href' => U('/Richtext/edit/id/' . $value['id']),
						'current' => (($value['id'] == $id) ? 'current' : ''),
					);
		}
		
		return $ret;
	}
	
	/*
	 * 获取子菜单
	 */
	public function getChildrenMenus($pid, $id) {
		$data = $this->where(array('parentid' => $pid))->select();
		$ret = array();
		foreach ($data as $value) {
			$ret[] = array(
						'menu' => $value['menu'], 
						'href' => U('/Richtext/edit/id/' . $value['id']),
						'current' => (($value['id'] == $id) ? 'current' : ''),
					);
		}
		
		return $ret;
	}
	
	
}



?>