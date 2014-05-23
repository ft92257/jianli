<?php 
class QuestionAction extends BaseAction {
	
	public function __construct() {
		parent::__construct();
		$this->model = D('Question');
	}
	
	public function  queryByNode($step){
		$where = array('type' => array('like', "%[{$step}]%"));
		import('ORG.Util.Page');
		$count = $this->model->where($where)->count();
		$data['total'] = $count;
		$limit = 10;
		$Page = new Page($count, $limit);
		$data['page'] = $Page->show();
		$this->model->limit("{$Page->firstRow}, {$Page->listRows}");
		$data['data'] = $this->model->where($where)->select();
		return $data;
	}
	
	public function queryCountByNode($step){
		$where = array('type' => array('like', "%[{$step}]%"));
		return $this->model->where($where)->count();
	}
}
?>