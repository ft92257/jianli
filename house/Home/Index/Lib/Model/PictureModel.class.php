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
			$value['ofid'] = getFileUrl($value['fid']);
			$length = strlen($value['ofid']);
			$start = substr($value['ofid'],0,$length-4);
			$end = substr($value['ofid'],$length-4,$length-1);
			$value['tfid'] = $start.'_88-88'.$end;
			$value['pfid'] = $start.'_200-200'.$end;
			$value['bfid'] = $start.'_540-340'.$end; 
			$value['createtime'] = date('Y-m-d H:i', $value['createtime']);
		}
	}
	

	/*
	 * 获取首页图片材料
	 */
	public function  getTop($cid,$type,$limit) {
		return $this->where(array('cid'=>$cid,'type'=>$type))->limit($limit)->select();
	}
	public function getPicture($type,$target,$step,$limit){
		if($limit>0){
			$aPicture = $this->where(array('type'=>$type,'target'=>$target,'step'=>$step))->limit($limit)->select();
		}else
			$aPicture = $this->where(array('type'=>$type,'target'=>$target,'step'=>$step))->select();
		
		return $aPicture;
	}
	
}
?>