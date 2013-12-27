<?php 
class companyModel extends Model{
    protected $trueTableName = 'yjl_company';
	
	function add($param){
		foreach($param as $k=>$v){
			$param[$k]=trim($v);
		}
		
		$su=parent::add($param);

		if($su){
			$info['status']=1;
			$info['data']['id']=$su;
			$info['msg']='添加成功';
		}else{
			$info['msg']='添加失败';
		}
		return $info;
	}

	function update($id, $data) {
		$map['id'] = $id;
		$su = $this->where($map)->data($data)->save();
		if ($su) {
			$info['status']=1;
			$info['msg'] = '修改成功！';
		} else {
			$info['msg'] = '修改失败！';
		}

		return $info;
	}

	function getone($id){
		$map['id'] = $id;
		$data = $this->where($map)->find();

		return $data;
	}

	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$value['addtime'] = date('y-m-d H:i', $value['addtime']);
		}
	}

	function getlist($param){
		$param['page'] ? $page=$param['page'] : $page=1;
		$param['length'] ? $length=$param['length'] : $length=4;

		$count=$this->count();
		$info=$this->order('addtime DESC')->page($page.','.$length)->select();

		$data['data']['data']=$info;
		$data['data']['length']=$length;
		$data['data']['count']=$count;

		return $data;
	}

}
