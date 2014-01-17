<?php 

/**
 * 	用户相关：登录，注册，资料修改
 */
class UserAction extends BaseAction {

	//需要验证的方法
	protected $aVerify = array('userlist','updateReal','userDel','adduser'
	);
	
	public function __construct() {
		parent::__construct();
		
		$this->model = D('User');
	}
	/*
	 * 用户列表
	 */
    public function userlist() {
    	
    		$account = getRequest('account');
    		$aWhere = array();
    		$aWhere['account'] = array('like',"%$account%");
    		import('ORG.Util.Page');//导入分页类
    		$count = $this->model->where($aWhere)->count();
    		$Page = new Page($count,5);
    		$show = $Page ->show();
    		$list = $this->model->where($aWhere)->order('createtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
    		$this->assign('list',$list);
    		$this->assign('page',$show);
    		$this->display();
    	
    	
		
    }
    /*
     * 更新用户
     */
    public function updateReal() {
    	
	    	$id = getRequest('id');
	    	$aWhere = array();
	    	if(!empty($id)){
	    		$aWere['id'] = $id;
	    	}
	    	$data = array('isreal'=>0);
	    	$aUser = $this->model->where($aWhere)->data($data)->save();
	    	$aScore = D('Score')->where(array('uid'=>$id))->data($data)->save();
	    	if($res !== false && $aScore !== false){
	    		$this->success('更新成功', $_SERVER['HTTP_REFERER']);
	    	}else{
	    		$this->error('更新失败');
	    	}
    	
    }
    public function userDel() {
   
    		$id = getRequest('id');
    		$res = $this->model->where(array('id'=>$id))->find();
    		$res['status'] !=-2 ?$res1 = $this->model->where(array('id'=>$id))->data(array('status'=>-2))->save():$res2 = $this->model->where(array('id'=>$id))->data(array('status'=>0))->save();
    		if($res1 !== false && $res2 !== false){
    			$this->success('更新成功', $_SERVER['HTTP_REFERER']);
    		}else{
    			$this->error('更新失败');
    		}
    	
    }
    /*
     * 添加监理
     */
    public function adduser() {
    	
	    	$this->checkPost();
	    	 $this->compare(getRequest('password'),getRequest('repassword'));
	         $data = array('type'=>4,
	         			  'password'=>md5(getRequest('password')),
	    				  'account'=>getRequest('account'),
	    				  'nickname'=>getRequest('nickname'),
	    				  'realname'=>getRequest('realname'),
	         			  'isreal'=>1,
	         			  'info'=>getRequest('info'),
	    				  'sex'=>getRequest('sex'),
	    				);
	    	$res = $this->model->addData($data);
	    	if(!$res) {
	    			 $this->error($this->model->getError());
	    	}else{
	    	   $info = D('File')->upload('pic');
				if ($info['status'] != 0) {
					$this->error($info['msg']);
				} else {
					$this->model->where(array('id' => $res))->data(array('avatar' => $info['data']['fileid']))->save();
				}
				
				$this->success('添加成功！',$_SERVER['HTTP_REFERER']);
		}
   
   }
    public function update() {
     	
	     	$id = getRequest('id');
	     	$Comp = D("Company");
	     	$aCompany = $Comp->getById($id);
	    	if (!$this->isPost()) {
				$aManager = $this->model->getById($aCompany['uid']);
		     	$this->assign('company', $aCompany);
				$this->assign('manager', $aManager);
		    	$this->display();
				die;
			}
			
			$repassword = getRequest('repassword');
			$password = getRequest('password');
			$aWhere = array('id' => $id);
			
			
			$mid= $Comp->where($aWhere)->getField('uid');
			$name = getRequest('name');
			if($name){
				$data = array('name' => $name);
				$Comp->where($aWhere)->data($data)->save();
			}else{
				$this->error('请输入公司名');
			}
	
			$account = getRequest('account');
			$aWhere = array('id' => $aCompany['uid']);
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
	
}

?>