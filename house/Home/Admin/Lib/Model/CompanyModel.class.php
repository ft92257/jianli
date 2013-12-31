<?php


class CompanyModel extends BaseModel {

	protected $aValidate = array(
								array('name', 'required', '请填写公司名称'),
								array('name', 'unique', '公司名称已经存在'),
							);


	public function addCompany($data) {
		$uid = $this->addData($data);
		if (empty($uid)) {
			return false;
		}

		return $this->getObjectById($uid);
	}

	public function addnew($data) {
		if (!$this->checkData($data)) {
			return false;
		}else
			return $this->addCompany($data);
		
	}
}
?>