<?php 
class companyAction extends baseAction{
	function index(){
		$big_menu = array('javascript:window.top.art.dialog({id:\'add\',iframe:\'?m=company&a=add\', title:\'添加公司\', width:\'700px\', height:\'600px\', lock:true}, function(){var d = window.top.art.dialog({id:\'add\'}).data.iframe;var form = d.document.getElementById(\'dosubmit\');form.click();return false;}, function(){window.top.art.dialog({id:\'add\'}).close()});void(0);', '添加公司');
		$M = D('company');
		$p = getpage();
		$param['page'] = $p;
		$param['length'] = 10;
		$company = $M->getlist($param);

		$ass['company']=$company['data']['data'];
		$ass['big_menu']=$big_menu;

		import('ORG.Util.Page');
		$Page = new Page($company['data']['count'],$company['data']['length']);
		$show = $Page->show();
		$ass['page'] = $show;

		$this->assign($ass);
		$this->display();
	}
	
	public function KE_upload() {
		import('ORG.Net.UploadFile');
		$upload = new UploadFile();// 实例化上传类
		$upload->maxSize  = 20000000 ;// 设置附件上传大小
		$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->savePath =  ROOT_PATH .'/data/uploads/';// 设置附件上传目录
		$upload->autoSub = true;
		$upload->saveRule = 'uniqid';
		if(!$upload->upload()) {// 上传错误提示错误信息
			$json = array(
				'error' => 1,
				'message' => $upload->getErrorMsg(),			
			);
		}else{// 上传成功 获取上传文件信息
			$info =  $upload->getUploadFileInfo();
			$json = array(
				'error' => 0,
				'url' =>  C('TMPL_PARSE_STRING.__IMAGES__').$info[0]['savename'],			
			);
		}
		
		echo json_encode($json);
	}

	private function upload() {
		if (!$_FILES['logo']['name']) {
			return '';
		}

		import('ORG.Net.UploadFile');
		$upload = new UploadFile();// 实例化上传类
		$upload->maxSize  = 6000000 ;// 设置附件上传大小
		$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->savePath =  ROOT_PATH .'/data/uploads/';// 设置附件上传目录
		$upload->autoSub = true;
		$upload->saveRule = 'uniqid';
		if(!$upload->upload()) {// 上传错误提示错误信息
			$this->error($upload->getErrorMsg());
		}else{// 上传成功 获取上传文件信息
			$info =  $upload->getUploadFileInfo();
		}

		return $info[0]['savename'];
	}

	function add(){
		if ($this->isPOST()) {
			$M = D('company');
			$param = $this->getParam();
			$param['addtime'] = time();
			$su = $M->add($param);
			if ($su['status']==1) {
				$this->success($su['msg'],'','','add');
			} else {
				$this->error($su['msg']);
			}
		} else {
			$this->assign('data', array('status' => 1));
			$this->display('edit');
		}
	}
	
	private function getParam() {
		$param = $_POST;

		if (empty($param['name'])) {
			$this->error('请填写公司名称！');
		}
		if (MAGIC_QUOTES_GPC) {
 			$param['feature_info'] = stripslashes($param['feature_info']);
			$param['price_info'] = stripslashes($param['price_info']);
			$param['process_info'] = stripslashes($param['process_info']);
			$param['case_info'] = stripslashes($param['case_info']);				
			$param['contact_info'] = stripslashes($param['contact_info']);
		}
		$param['feature_info'] = htmlspecialchars($param['feature_info']);
		$param['price_info'] = htmlspecialchars($param['price_info']);
		$param['process_info'] = htmlspecialchars($param['process_info']);
		$param['case_info'] = htmlspecialchars($param['case_info']);
		$param['contact_info'] = htmlspecialchars($param['contact_info']);

		$logo = $this->upload();
		if ($logo) {
			$param['logo'] = $logo;
		}

		return $param;
	}
		
	function edit(){
		$id = (int) $_GET['id'];

		if (empty($id)) {
			$this->error('ID不能为空！');
		}

		$M = D('company');

		if ($this->isPOST()) {
			$su = $M->update($id, $this->getParam());
			if ($su['status']==1) {
				$this->success($su['msg'],'','','add');
			} else {
				$this->error($su['msg']);
			}
		} else {
			$data = $M->getone($id);
			$this->assign('data', $data);
			$this->display();
		}
	}

}
