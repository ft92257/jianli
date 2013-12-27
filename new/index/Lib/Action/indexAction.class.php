<?php
class indexAction extends Action{
	function index()
	{
		$this->redirect('supervisor/index');
		//header("Location:http://www.yijianli.cn/new/index.php?s=/supervisor/index");
	}
}
