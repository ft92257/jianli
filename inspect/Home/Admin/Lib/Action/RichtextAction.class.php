<?php 

/**
 * 	图文编辑
 */
class RichtextAction extends BaseAction {

	//需要验证的方法
	protected $aVerify = array(
	);
	
	public function __construct() {
		parent::__construct();
	}
	
	/*
	 * 图片上传
	 */
	public function upload() {
		$info = D('File')->upload('imgFile');
		$arr = array(
			'error' => $info['status'],
		);
		if ($info['status'] != 0) {
			$arr['message'] = $info['msg'];
		} else {
			$arr['url'] = getFileUrl($info['data']['fileid']);
		}
		
		die(json_encode($arr));
	}
}

?>