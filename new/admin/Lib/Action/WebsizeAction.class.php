<?php 

/**
 * 网站管理
 *
 */
class WebsizeAction extends baseAction{
	 
	
/******
 * 对外方法
 */
	//数据编辑
	public function edit(){
		$Supervision = D('ProjectSupervision');

		if ($this->isPOST()) {
			$Supervision->create();
			$Supervision->edit() ? $this->success('修改成功！') : $this->error('没有做出修改');
		}
		$info = $Supervision->show();
		$this->assign('info',$info);
		$this->display();
	}

	
}

?>


