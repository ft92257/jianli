<?php 

/**
 * 监理师管理
 * @author Administrator
 *
 */
class supervisorAction extends baseAction{
	
	function index(){
		$big_menu = array('javascript:window.top.art.dialog({id:\'add\',iframe:\'?m=supervisor&a=add\', title:\'添加监理师\', width:\'700px\', height:\'600px\', lock:true}, function(){var d = window.top.art.dialog({id:\'add\'}).data.iframe;var form = d.document.getElementById(\'dosubmit\');form.click();return false;}, function(){window.top.art.dialog({id:\'add\'}).close()});void(0);', '添加监理师');
		$M = D('supervisor');
		$p = getpage();
		$param['page'] = $p;
		$param['length'] = 10;
		$company = $M->getlist($param);

		$ass['data']=$company['data']['data'];
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
				'url' => IMAGE_ROOT . $info[0]['savename'],			
			);
		}
		
		echo json_encode($json);
	}

	private function upload() {
		if (!$_FILES['avatar']['name']) {
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
			$M = D('supervisor');
			$data = $this->getParam();
			$data['addtime'] = time();

			$su = $M->addnew($data);
			if ($su['status']==1) {
				$this->success($su['msg'],'','','add');
			} else {
				$this->error($su['msg']);
			}
		} else {
			$this->assign('profession_options', getSelectOptions('profession', supervisorModel::getProfession(), 0, array('0' => '请选择职业')));
			$this->display('edit');
		}
	}

	private function getParam() {
		$param = $_POST;

		if (empty($param['name'])) {
			$this->error('请填写监理师姓名！');
		}
			
		$cid = D('company')->where(array('name' => $param['company']))->getField('id');
		if (!$cid) {
			$this->error('该公司不存在！');
		}
		$param['cid'] = $cid;
		
		$profession = intval($param['profession']);
		if ($profession <= 0 || supervisorModel::getProfession($profession) == '') {
			$this->error('请选择监理师职业！');
		}
	
		if (MAGIC_QUOTES_GPC) {
			$param['case_info'] = stripslashes($param['case_info']);				
		}

		$data = array(
			'name' => htmlspecialchars($param['name']),
			'cid' => $cid,
			'introduction' => htmlspecialchars($param['introduction']),
			'profession' => $profession,
			'telephone' => htmlspecialchars($param['telephone']),
			'case_info' => htmlspecialchars($param['case_info']),
			'service_area' => $param['service_area'],
			'status' => (int) $param['status'],
			);

		$avatar = $this->upload();
		if ($avatar) {
			$data['avatar'] = $avatar;
		} 

		return $data;
	}

	function edit(){
		$id = (int) $_GET['id'];
		if (empty($id)) {
			$this->error('ID不能为空！');
		}

		$M = D('supervisor');

		if ($this->isPOST()) {
			$su = $M->update($id, $this->getParam());
			if ($su['status']==1) {
				$this->success($su['msg'],'','','add');
			} else {
				$this->error($su['msg']);
			}
		} else {
			$data = $M->getone($id);

			$this->assign('profession_options', getSelectOptions('profession', supervisorModel::getProfession(), $data['profession']));

			$data['company'] = $_GET['company'];
			$this->assign('data', $data);
			$this->display();
		}
	}

}
