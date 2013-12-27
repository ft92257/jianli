<?php 
class reserveModel extends Model{
	protected $trueTableName = 'yjl_reserve';
	
	public function addnew($param) {
		return parent::add($param);
	}	

	public function checkLimit($tel, $sid) {
		$where = "telephone = '$tel' AND sid = '$sid' AND addtime >= ";
		$where .= strtotime(date('Y-m-d')) . " AND addtime < " . (strtotime(date('Y-m-d')) + 3600 * 24);
		$this->where($where);
		$data = $this->find();

		return empty($data);
	}
}
