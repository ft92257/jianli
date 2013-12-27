<?php
class publicAction extends baseAction
{
	// 菜单页面
	public function menu() {
		//$this->checkUser();

		//显示菜单项
		$id	=	intval($_REQUEST['tag'])==0?6:intval($_REQUEST['tag']);
		$menu  = array();

		$role_id = D('admin')->where('id='.$_SESSION['admin_info']['id'])->getField('role_id');
		$node_ids_res = D("access")->where("role_id=".$role_id)->field("node_id")->select();

		$node_ids = array();
		foreach ($node_ids_res as $row) {
			array_push($node_ids,$row['node_id']);
		}

		//读取数据库模块列表生成菜单项
		$node    =   M("node");
		$where = "auth_type<>2 AND status=1 AND is_show=0 AND group_id=".$id;
		$list	=	$node->where($where)->field('id,action,action_name,module,module_name,data')->order('sort DESC')->select();

		foreach($list as $key=>$action) {
			$data_arg = array();
			if ($action['data']) {
				$data_arr = explode('&', $action['data']);
				foreach ($data_arr as $data_one) {
					$data_one_arr = explode('=', $data_one);
					$data_arg[$data_one_arr[0]] = $data_one_arr[1];
				}
			}
			$action['url'] = U($action['module'].'/'.$action['action'], $data_arg);
			if ($action['action']) {
				$menu[$action['module']]['navs'][] = $action;
			}
			$menu[$action['module']]['name']	= $action['module_name'];
			$menu[$action['module']]['id']	= $action['id'];
		}

		$this->assign('menu',$menu);
		$this->display('left');
	}

	/**
	 +----------------------------------------------------------
	 * 控制面板
	 +----------------------------------------------------------
	 */
	public function panel()
	{
		$security_info=array();
		if(is_dir(ROOT_PATH."/install")){
			$security_info[]="强烈建议删除安装文件夹,点击<a href='".u('public/delete_install')."'>【删除】</a>";
		}
		if(APP_DEBUG==true){
			$security_info[]="强烈建议您网站上线后，建议关闭 DEBUG （前台错误提示）";
		}

		$this->assign('security_info',$security_info);

		$server_info = array(
		//    'PINPHP版本'=>'2.2 [<a href="http://www.pinphp.com/" target="_blank">查看最新版本</a>]',
		// '关于我们'=>'<a href="http://www.pinphp.com" class="blue" target="_blank">官方网站</a>&nbsp;&nbsp;<a href="http://bbs.pinphp.com" class="blue" target="_blank">支持论坛</a>',
            '操作系统'=>PHP_OS,
            '运行环境'=>$_SERVER["SERVER_SOFTWARE"],
		//'PHP运行方式'=>php_sapi_name(),
            '上传附件限制'=>ini_get('upload_max_filesize'),
            '执行时间限制'=>ini_get('max_execution_time').'秒',
		//'服务器时间'=>date("Y年n月j日 H:i:s"),
		//'北京时间'=>gmdate("Y年n月j日 H:i:s",time()+8*3600),
            '服务器域名/IP'=>$_SERVER['SERVER_NAME'].' [ '.gethostbyname($_SERVER['SERVER_NAME']).' ]',
            '剩余空间'=>round((@disk_free_space(".")/(1024*1024)),2).'M',
		);
		$this->assign('server_info',$server_info);
		$role_mod=d('role');
		$res=$role_mod->where('id='.$_SESSION['admin_info']['role_id'])->find();
		$this->assign('role',$res);
		$this->display();
	}
	public function login()
	{
		//unset($_SESSION);
		$admin_mod=M('admin');
		if ($_POST) {
			$username = $_POST['username'] && trim($_POST['username']) ? trim($_POST['username']) : '';
			$password = $_POST['password'] && trim($_POST['password']) ? trim($_POST['password']) : '';
			if (!$username || !$password) {
				redirect(u('public/login'));
			}
			//生成认证条件
			$map  = array();
			// 支持使用绑定帐号登录
			$map['user_name']	= $username;
			$map["status"]	=	array('gt',0);
			$admin_info=$admin_mod->where("user_name='$username'")->find();

			//使用用户名、密码和状态的方式进行认证
			if(false === $admin_info) {
				$this->error('帐号不存在或已禁用！');
			}else {
				if($admin_info['password'] != md5($password)) {
					$this->error('密码错误！');
				}

				$_SESSION['admin_info'] =$admin_info;
				if($authInfo['user_name']=='admin') {
					$_SESSION['administrator'] = true;
				}
				$this->success('登录成功！',u('index/index'));
				exit;
			}
		}
		$this->display();
	}

	public function logout()
	{
		if(isset($_SESSION['admin_info'])) {
			unset($_SESSION['admin_info']);
			//unset($_SESSION);
			$this->success('登出成功！',u('public/login'));
		}else {
			$this->error('已经登出！');
		}
	}
	public function delete_install(){
		import("ORG.Io.Dir");
		$dir = new Dir;
		$dir->delDir(ROOT_PATH."/install");
		@unlink(ROOT_PATH.'/install.php');
		if(!is_dir(ROOT_PATH."/install")){
			$this->success(L('operation_success'));
		}
	}
}
?>