<?php 

/**
 * 	   管理员控制器
 */
class ManagerAction extends BaseAction {
     
	//需要验证的方法
	protected $aVerify = array('update'
	);
	
	public function __construct() {
		parent::__construct();
		
		$this->model = D('Manager');
	}
	/* 
	 * 管理员登录
	 */
    public function  login()  {
    	$this->checkPost();
    	if ($this->model->login(getRequest('account'), getRequest('password'))) {
	    	if($_SESSION['verify'] != md5($_POST['verify'])) {
	  			 $this->error('验证码错误！',U('/Manager/login'));
			}else{
				
				$this->success('登录成功', U('/Index/index'));//验证成功跳转到公司列表
			}
		}else{
			$this->error($this->model->getError(),U('/Manager/login'));
		}
	}
	/*
	 * 修改装修公司信息
	 */
     public function update() {
     	
	     	$id = getRequest('id');
	     	$aCompany = D("Company")->getById($id);
	    	if (!$this->isPost()) {
				$aManager = $this->model->getById($aCompany['mid']);
		     	$this->assign('company', $aCompany);
				$this->assign('manager', $aManager);
		    	$this->display();
				die;
			}
	
			$repassword = getRequest('repassword');
			$aWhere = array('id' => $id);
			
			$Comp = D("Company");
			$mid= $Comp->where($aWhere)->getField('Mid');
			$name = getRequest('name');
			if($name){
				$data = array('name' => $name);
				$Comp->where($aWhere)->data($data)->save();
			}else{
				$this->error('请输入公司名');
			}
	
			$account = getRequest('account');
			$aWhere = array('id' => $aCompany['mid']);
			if($account){
			 	$data = array('account' => getRequest('account'));
				$this->model->where($aWhere)->data($data)->save();
			}
			$password = getRequest('password');
	        if($this->compare($password,$repassword)){
		    	
		    	$data = array('password' => md5(getRequest('password')));
	            
				$this->model->where($aWhere)->data($data)->save();
		    
		    }
	        
		    $this->success('修改成功', U('/Company/company'));
     
	}
	

	/*
	 * 增加新公司
	*/
	/*
	public function addnew() {
		if($this->isLogin()){
			if (!$this->isPost()) {
				if(isset($_SESSION['user'])){
				$this->display();
				die;
				}
			}
			
			
			$data = array(     //接受的账号、密码变成数组
				'account' => getRequest('account'),
				'password' => md5(getRequest('password')),
			);
			$repassword = getRequest('repassword');
			$password = getRequest('password');
			
			$this->compare($password,$repassword);
			$oManager = $this->model->addnew($data);//返回这条记录的对象
			if(empty($oManager)) {
				$this->error($this->model->getError());
			}else{
				$data = array(
					'name' => getRequest('name'),//
					'mid' => $oManager->id,
				);
	            if (D('Company')->addnew($data)) {
	            	$this->success('添加成功');
	            } else {
	            	$this->error(D('Company')->getError());
	            }
			} 
		}else 
				$this->error('请先登录',U('/Manager/login'));
	}*/
	Public function verify() {
	    import('ORG.Util.Image');
	    Image::buildImageVerify(4,0,'png',80,30);
   }

   public function loginout() {
   		$this->model->logout();
   		$this->success('退出系统',U('Manager/login'));
   }

}

?>