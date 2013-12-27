<?php 

/**
 * 	图文编辑
 */
class ScoreAction extends BaseAction {

	//需要验证的方法
	protected $aVerify = array('clist','single','fresh'
	);
	
	public function __construct() {
		parent::__construct();
		
		$this->model = D('Score');
	}
	/*根据时间、用户名、评论内容查询记录
	 * 
	 */
	public function clist()	{
		
			$aWhere = $this->model->listWhere();
			import('ORG.Util.Page');//导入分页类
	        $count = $this->model->where($aWhere)->count();
	        $Page = new Page($count,5);
	        $show = $Page ->show();
	        $data = $this->model->where($aWhere)->order('createtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
	        $this->assign('page',$show); 
			$this->assign('data',$data);
			$this->display();
		
	}
	
	
	/*查询
	 * 单个用户评论条数详情
	 */
	public function single() {
		
		
			
			$nickname = getRequest('nickname');
			import('ORG.Util.Page');
			$aWhere = array();
			$aWhere['nickname'] = $nickname;
			$User = D('User');
			$uid = $User->where($aWhere)->getField('id');
			$count = $this->model->where(array('uid'=>$uid))->count();
			$Page = new Page($count,5);
			$show = $Page ->show();
			$res = $this->model->where(array('uid'=>$uid))->order('createtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			$this->assign('page',$show);
			$this->assign('len',$count);
			$this->assign('res',$res);
			$this->display();
		
				
	}
	/*
	 * 刷新评分
	 */
	public function fresh() {
		
		
			$this->checkPost();
			
			set_time_limit(0);
			$res = $this->model->query("SELECT  type,target,usertype ,avg(score) avgs,count(*) amount FROM `tb_score` where isreal=1  group by type,target,usertype" );
			foreach($res as $k=>&$v){
				if($v['type'] == 1 ){
					if($v['usertype'] == 1){
						$suc = D('Company')->where(array('id'=>$v['target']))->data(array('score_owner'=>$v['avgs'],'score_owner_count'=>$v['amount']))->save();
					}else{
						$suc = D('Company')->where(array('id'=>$v['target']))->data(array('score_expert'=>$v['avgs'],'score_expert_count'=>$v['amount']))->save();
					}
					if($suc === false){
						$this->error('更新失败');
					}
				}
				if($v['type'] == 2 ){
					if($v['usertype'] == 1){
						$suc = D('Construction')->where(array('id'=>$v['target']))->data(array('score_owner'=>$v['avgs'],'score_owner_count'=>$v['amount']))->save();
					}else{
						$suc = D('Construction')->where(array('id'=>$v['target']))->data(array('score_expert'=>$v['avgs'],'score_expert_count'=>$v['amount']))->save();
					}
					if($suc === false){
						$this->error('更新失败');
					}
				}
				if($v['type'] == 3){
					if($v['usertype'] == 1){
						$suc = D('Case')->where(array('id'=>$v['target']))->data(array('score_owner'=>$v['avgs'],'score_owner_count'=>$v['amount']))->save();
					}else{
						$suc = D('Case')->where(array('id'=>$v['target']))->data(array('score_expert'=>$v['avgs'],'score_expert_count'=>$v['amount']))->save();
					}
					if($suc === false){
						$this->error('更新失败');
					}
				}
			}
			$res1 = $this->model->query("SELECT  type,target,avg(score) avgs FROM `tb_score` where isreal=1 group by type,target" );
			foreach($res1 as $k=>&$v){
				if($v['type'] == 1){
					$suc = D('Company')->where(array('id'=>$v['target']))->data(array('score_complex'=>$v['avgs']))->save();
					if($suc === false){
						$this->error('更新失败');
					}
				}
				if($v['type'] == 2){
					$suc = D('Construction')->where(array('id'=>$v['target']))->data(array('score_complex'=>$v['avgs']))->save();
					if($suc === false){
						$this->error('更新失败');
					}
				}
				if($v['type'] == 3){
					$suc = D('Case')->where(array('id'=>$v['target']))->data(array('score_complex'=>$v['avgs']))->save();
					if($suc === false){
						$this->error('更新失败');
					}
				}
			}
			$this->success('更新成功',$_SERVER['HTTP_REFERER']);
		
	}
}
?>