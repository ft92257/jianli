<?php 

/**
 * 	预约功能相关页面
 */
class ReserveAction extends BaseAction {

	//需要验证的方法
	protected $aVerify = array(
			
	);
	
	public function __construct() {
		parent::__construct();
		
		$this->model = D('Reserve');
	}
	
	/*
	 * 添加预约
	 */
	public function add() {
		//TODO 增加预约次数
		$type = (int) getRequest('type');
		$target = (int) getRequest('id');
		if ($type == 1) {
			$cid = $target;
			$aCom = D('Company')->getById($cid);
			if (empty($aCom)) {
				$this->error('装修公司不存在！');
			}
			$project = $aCom['name'];
		} elseif ($type == 2) {
			$aCons = D('Construction')->getById($target);
			if (empty($aCons)) {
				$this->error('施工队不存在！');
			}
			
			$cid = $aCons['cid'];
			$project = $aCons['name'];
		} elseif ($type == 3) {
			//样板房
			$aActive = D('Active')->getById($target);
			if (empty($aActive)) {
				$this->error('样板房参观会不存在！');
			}
			
			$cid = $aActive['cid'];
			$project = $aActive['name'];
		} else {
			$this->error('type参数不正确！');
		}
		/*
		if (!isset($aCom)) {
			$aCom = D('Company')->getById($target);
			if (empty($aCom)) {
				$this->error('装修公司不存在！');
			}
		}
		*/
		//装修公司需支付金额
		$cost = C('RESERVE_MONEY');
		
		//预约次数限制判断
		$reserve_count = $this->model->getValidCount($this->oUser->id);
		if ($reserve_count >= 3) {
			$user_cost = 100;
		} else {
			$user_cost = 0;
		}
		
		
		if (!$this->isPost()) {
			//模版显示
			//$this->assign('user', (array) $this->oUser);
			//$this->assign('project', $project);
			//$this->assign('user_cost', $user_cost);
			//$this->assign('reserve_count', $reserve_count);
			
		//	$this->display();
			die;
		}
		
		$name = getRequest('name');
		if (empty($name)) {
			$this->error('请填写您的姓名！');
		}
		$telephone = getRequest('telephone');
		//检查手机号格式
		if (!checkMobile($telephone)) {
			$this->error('手机号码格式不正确！');
		}
		$email = getRequest('email');
		
		
		/*if ($telephone != $this->oUser->mobile) {
			//验证手机号
			$code = getRequest('code');
			if (D('Code')->check($telephone, $code)) {
				$this->error('验证码不正确！');
			}
		}*/
		
		if (!$this->model->checkValidUnique($this->oUser->id, $type, $target)) {
			//重复预约，不扣款
			$cost = 0;
		}
		
		//添加预约记录
		$data = array(
					'cid' => $cid,
					'own_uid' => $this->oUser->id,
					'type' => $type,
					'target' => $target,
					'sex' => $this->oUser->sex,
					'region_area'=> getRequest('region'),
					'email' => $email,
					//'message' => getRequest('message'),
					'money' => $cost,
					'name' => $name,
					'telephone' => $telephone,
				);
			//	dump($data);die;
		//需更新的用户数据
		$updata = array();
		if ($this->oUser->realname == '') {
			$updata['realname'] = $data['name'];
		}
		if ($this->oUser->mobile == '') {
			$updata['mobile'] = $data['telephone'];
		}
		//dump($data);die;
		//添加记录并验证数据
		$rid = $this->model->addData($data);
		if ($rid) {
			//扣装修公司款
			if ($cost > 0) {
				$result = D('Manager')->charge($aCom['mid'], $cost);
				if ($result) {
					//更新预约单支付状态
					$charge = array(
						'charged' => 1,
					);
					$this->model->where(array('id' => $rid))->data($charge)->save();
				}
			}
			
			//保存用户信息
			if (!empty($updata)) {
				D('User')->where(array('id' => $this->oUser->id))->data($updata)->save();
			}
			$this->success('预约成功！');
		} else {
			$this->error($this->model->getError());
		}
	}
	
	public function getcode() {
		$mobile = getRequest('mobile');
		
		//检测手机号码格式
		if (!checkMobile($mobile)) {
			die('手机号码格式不正确！');
		}
		
		$code = D('Code')->getCode($mobile);
		if (empty($code)) {
			die('您的操作太频繁了，请稍后再试！');
		} else {
			$msg = "您的验证码是：" . $code;
			$ret = sendMobileMsg($mobile, $msg);
		}
	}
}

?>