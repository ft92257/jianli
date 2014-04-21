<?php 
/**
 * 	图片表模型
 */
class PictureModel extends BaseModel {
	protected $aValidate = array(
			array('fid', 'required', '请选择图片！'),
	);
	
	
	
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$value['spic'] = getFileUrl($value['fid'], '80-80');
			$value['bpic'] = getFileUrl($value['fid'], '650-426');
		}
	}
	

	public function getPicture($type,$target){
		$aPicture = $this->where(array('type'=>$type,'target'=>$target))->select();
		
		return $aPicture;
	}
	
}
?>