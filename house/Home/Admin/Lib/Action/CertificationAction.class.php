<?php 

/**
 * 	   管理员控制器
 */
class CertificationAction extends BaseAction {
     
	//需要验证的方法
	protected $aVerify = array( 'cerList','updateSpeed'
	);
	
	public function __construct() {
		parent::__construct();
		
		$this->model = D('Certification');
	}

	
	
	/*
	 * 进度列表
	 */
	public function cerList() {
		import('ORG.Util.Page');//导入分页类
       	$count = $this->model->where(array('status'=>0))->count();
       	$Page = new Page($count,5);
       	$show = $Page ->show();
       	$list = $this->model->where(array('status'=>0))->order('createtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('list',$list);
        $this->assign('page',$show);  
        $this->display();
        
        
    
	}
	/*
	 * 更新进度
	 */
   public function updateSpeed() {
    	
    		
    		$this->model->startTrans();
    		
    		if(getRequest('id')){
		    	$id = getRequest('id');
		    	$res = $this->model->find($id);
		    	$speed = $res['speed'];
		    	$nid = $res['nid'];
		    	$type = $res['type'];
		    	if($speed){
		    		$speed != 3? $data = array('speed'=>3):$data = array('speed'=>2);
		    		$speed != 3? $data1 = array('is_approve'=>3):$data1 = array('is_approve'=>2);
		    		$res = $this->model->where(array('id'=>$id))->data($data)->save();
		    		if($type ==1){
		    			$res1 = D('Construction')->where(array('id'=>$nid))->data($data1)->save();
		    		}
		    		if($type == 2){
		    			$res1 = D('Case')->where(array('id'=>$nid))->data($data1)->save();
		    		}
		    		if($res1){
		    			$this->model->commit();
		    			$this->success('更新成功', $_SERVER['HTTP_REFERER']);
		    		}else{
		    			$this->model->rollback();
		    			$this->error('更新失败');
		    		}
		    	}
    		}
    		if(getRequest('cid')){
    			$id = getRequest('cid');
    			$res = $this->model->find($id);
		    	$speed = $res['speed'];
		    	$type = $res['type'];
		    	$nid = $res['nid'];
    			$res = $this->model->where(array('id'=>$id))->data(array('speed'=>4))->save();
    			if($type ==1){
		    		$res1 = D('Construction')->where(array('id'=>$nid))->data(array('is_approve'=>4))->save();
		    	}
		    	if($type == 2){
		    		$res1 = D('Case')->where(array('id'=>$nid))->data(array('is_approve'=>4))->save();
		    	}
    			if($res1 !== false){
	    			$this->model->commit();
	    			$this->success('更新成功', $_SERVER['HTTP_REFERER']);
	    		}else{
	    			$this->model->rollback();
	    			$this->error('更新失败');
	    		}
    		}
    		
  	   
	
	

	}
}
?>