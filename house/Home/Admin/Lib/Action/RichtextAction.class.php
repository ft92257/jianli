<?php 

/**
 * 	图文编辑
 */
class RichtextAction extends BaseAction {

	//需要验证的方法
	protected $aVerify = array('add','edit'
	);
	
	public function __construct() {
		parent::__construct();
		
		$this->model = D('Richtext');
	}
	
	/*
	 * 添加父级菜单
	 */
	public function add() {
			$pid = (int) getRequest('pid');
			if ($pid) {
				$parent = $this->model->getById($pid);
				if (empty($parent)) {
					$this->error('没有该父级菜单');
				}
				$this->assign('parent_menu', $parent['menu']);
			}
			
			if (!$this->isPost()) {
				$this->display();
				die;
			}
			
			$data = array(
						'parentid' => $pid,
						'menu' => getRequest('menu'),
						'title' => getRequest('title'),
						'text' => getRequest('text'),
					);
			$id = $this->model->addData($data);
			if (!$id) {
				$this->error($this->model->getError());
			} else {
				$this->success('添加成功！', U('/Richtext/edit/id/' . $id));
			}
		
		
	}
	
	/*
	 * 编辑菜单内容
	 */
	public function edit() {
		
			
			$id = getRequest('id');
			$cont = $this->model->getById($id);
			if (empty($cont)) {
				$this->error('没有该菜单！');
			}
			$cont['text'] = stripslashes($cont['text']); 
			
			if (!$this->isPost()) {
				$pid = $cont['parentid'] ? $cont['parentid'] : $id;
				
				$parent_menus = $this->model->getParentMenus($pid);
				
				$menus = $this->model->getChildrenMenus($pid, $id);
	
				$this->assign('addhref', U('/Richtext/add/pid/' . $pid));
				$this->assign('cont', $cont);
				$this->assign('parent_menus', $parent_menus);
				$this->assign('menus', $menus);
				$this->display();
				die;
			}
			
			$data = array(
						'title' => getRequest('title'),
						'text' => getRequest('text'),
					);
			if($this->model->checkData($data,false)){
				$ret = $this->model->where(array('id' => $id))->data($data)->save();
				if ($ret) {
					$this->success('修改成功！', $_SERVER['HTTP_REFERER']);
				} else {
					$this->error('内容没有更改或保存数据库失败！');
				}
			}else{
				$this->error($this->model->getError());
			}
		
	}
	
	/*
	 * 图片上传
	 */
	public function upload() {
		$info = D('File')->upload('imgFile');
		$arr = array(
			'error' => $info['status'],
		);
		if ($info['status'] != 0) {
			$arr['message'] = $info['msg'];
		} else {
			$arr['url'] = getFileUrl($info['data']['fileid']);
		}
		
		die(json_encode($arr));
	}
}

?>