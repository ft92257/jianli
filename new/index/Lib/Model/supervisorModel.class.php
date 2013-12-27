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

	function getone($id){
		$map['id'] = $id;
		$data = $this->where($map)->find();

		return $data;
	}

	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$value['addtime'] = date('y-m-d H:i', $value['addtime']);
			$value['profession'] = self::getProfession($value['profession']);
			$value['service_area'] = $value['service_area'] ? $value['service_area'] : '上海';
		}
	}

	function getPageList($f){
		$page = max(1, intval($_GET['p']));
		$length = 20;

		$count=$this->where("profession = $f")->count();
		
		$this->field('yjl_company.name as company,yjl_supervisors.*');
		$this->join('yjl_company ON yjl_company.id = yjl_supervisors.cid');
		$this->where("profession = $f");
		$this->order('reserve_month DESC,reserve_total DESC')->page($page.','.$length);
		$info = $this->select();

		$data['data']=$info;
		$data['length']=$length;
		$data['count']=$count;

		return $data;
	}

}
