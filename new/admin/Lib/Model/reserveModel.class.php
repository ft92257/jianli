<?php 
class reserveModel extends Model{
    protected $trueTableName = 'yjl_reserve';
	
	function status($id, $data) {
		$map['id'] = $id;
		$su = $this->where($map)->data($data)->save();
		if ($su) {
			$info['status']=1;
			$info['msg'] = '操作成功！';
		} else {
			$info['msg'] = '操作失败！';
		}

		return $info;
	}

	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$value['addtime'] = date('y-m-d H:i', $value['addtime']);
		}
	}

	function getlist($param){
		$page = max(1, intval($_GET['p']));
		$length = 10;

		$count = $this->count();
		$this->join(" yjl_company ON yjl_reserve.cid = yjl_company.id");
		$this->join(" yjl_supervisors ON yjl_reserve.sid = yjl_supervisors.id");
		$this->field("yjl_reserve.*,yjl_supervisors.name as supervisor,yjl_company.name as company");
		$this->where("yjl_reserve.status <> -2");
		$info = $this->order('addtime DESC')->page($page.','.$length)->select();

		$data['data']=$info;
		$data['length']=$length;
		$data['count']=$count;

		return $data;
	}

}
