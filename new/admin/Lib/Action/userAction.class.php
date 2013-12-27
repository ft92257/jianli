<?php
class userAction extends baseAction{
	public function index(){
        $mod=D("user");
        $res=$mod->get_list();
		$this->assign('list',$res['list']);
		$this->assign('page',$res['page']);
		$this->display();
	}
	function edit() {
		if (isset($_POST['dosubmit'])) {
			$mod = M('user');
			$data = $mod->create();

			$result = $mod->where("id=" . $data['id'])->save($data);
			if(false !== $result){
				$this->success(L('operation_success'), '', '', 'edit');
			}else{
				$this->error(L('operation_failure'));
			}
		} else {
			$mod = M('user');
			if (isset($_GET['id'])) {
				$id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : $this->error('请选择要编辑的链接');
			}
			$info = $mod->where('id=' . $id)->find();
			$this->assign('info', $info);
			$this->assign('show_header', false);
			$this->display();
		}
	}
}