<?php 
class companyModel extends Model{
    protected $trueTableName = 'yjl_company';
	
	function getone($id){
		$map['id'] = $id;
		$data = $this->where($map)->find();

		return $data;
	}
/*
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$value['addtime'] = date('y-m-d H:i', $value['addtime']);
		}
	}*/

	function getlist(){
		$page = max(1, intval($_GET['p']));
		$length = 10;

		$count=$this->where("status = 1")->count();
		$info=$this->where("status = 1")->order('reserve_month DESC,reserve_total DESC')->page($page.','.$length)->select();

		$data['data']['data']=$info;
		$data['data']['length']=$length;
		$data['data']['count']=$count;

		return $data;
	}

}
