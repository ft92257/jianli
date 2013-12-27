<?php 

/**
 * 	   管理员控制器
 */
class CompanyAction extends BaseAction {
     
	//需要验证的方法
	protected $aVerify = array('index','addnew'
	);
	
	public function __construct() {
		parent::__construct();
		
		$this->model = D('Company');
	}
	/*
	 * 装修公司列表
	 */
	public function index() {
			import('ORG.Util.Page');//导入分页类
	       	$count = $this->model->where(array('status'=>0))->count();
	       	$Page = new Page($count,5);
	       	$Page->setConfig('theme'," %upPage% %downPage% %first% %prePage% %linkPage% %nextPage% %end%");
	       	$show = $Page ->show();
	       	$aCompany = $this->model->where(array('status'=>0))->order('createtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
	        $this->assign('company',$aCompany);
	        $this->assign('page',$show); 
	        $this->display(); 
		
	}
	/*
	 * 增加新公司
	 */
	public function addnew() {
		
			$this->checkPost();
			$this->compare(getRequest('password'),getRequest('repassword'));
			$data = array(     //接受的账号、密码变成数组
						 'account' => getRequest('account'),
						 'password' => md5(getRequest('password')),
					);
			$oManager = D('Manager')->addnew($data);//返回数组
			if(!empty($oManager)) {
				$data = array(
							 'name' => getRequest('name'),//
							 'mid' => $oManager->id,
						);
	            if ($this->model->addnew($data)) {
	            	$this->success('添加成功');
	            } else {
	            	$this->error($this->model->getError());
	            }
			} else {
				$this->error((D('Manager')->getError()));
			}
	
		 		
	 }
	
	

}

?>