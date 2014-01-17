<?php 

/**
 * 施工队模型
 *
 */
class DistrictModel extends BaseModel {
	
	/*
	 * 验证form字段规则
	 */
	protected $aValidate = array(
	);
	

	/*
	 * 首页施工队排行榜
	 */
	public function getNameById($id) {
	
		return $this->where(array('id'=>$id))->getField('name');
	}
	public function getFieldById($id) {
			return $this->where(array("upid"=>$id))->limit(13)->getField("id,name");
	}
	
}



?>