<?php 
class supervisorModel extends Model{
   	protected $trueTableName = 'yjl_supervisors';
	
	public static function getProfession($key = NULL, $default = '') {
		$arr = array(
			'1' => '工装监理师',
			'2' => '别墅监理师',
			'3' => '公寓监理师',
			'4' => '预算师',
			'5' => '验房师',
			'6' => '维权律师',
		);
		
		if ($key === NULL) {
			return $arr;
		} else {
			return isset($arr[$key]) ? $arr[$key] : $default;
		}
	}

	function addnew($param) {
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
			$value['profession'] = self::getProfession($value['profession']);
		}
	}

	function getlist($param){
		$param['page'] ? $page=$param['page'] : $page=1;
		$param['length'] ? $length=$param['length'] : $length=4;

		$count=$this->count();
		
		$info=$this->field('yjl_company.name as company,yjl_supervisors.*')->join('yjl_company ON yjl_company.id = yjl_supervisors.cid')->order('addtime DESC')->page($page.','.$length)->select();

		$data['data']['data']=$info;
		$data['data']['length']=$length;
		$data['data']['count']=$count;

		return $data;
	}

	
	
	/**
	 * 小张添加2013-6-6 14:41添加
	 */
	//增加与减少一条记录
	public function change($condition,$type) {
		
		switch ($type) {
			case 'add' :
				$info = array('exp','project_count+1');
				break;
			case 'reduce' :
				$info = array('exp','project_count-1');
				break;
			default:
				return false;
		}
		
		$data['project_count'] = $info;
		return $this->where($condition)->save($data);
	}
	
	
}
