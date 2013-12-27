<?php
class reserveAction extends Action{
	function index()
	{
		
		$sid = (int) $_GET['sid'];
		if ($sid > 0) {
			$supervisor = D('supervisor')->where("id = $sid")->find();
			if (empty($supervisor)) {
				$this->error('参数错误，没有该监理师！');
			}

			$supervisor['profession'] = supervisorModel::getProfession($supervisor['profession']);
			$cid = $supervisor['cid'];
			$company = D('company')->where("id = $cid")->find();
			
			$this->assign('sid', $sid);
			$this->assign('supervisor', $supervisor);
		} else {
			$cid = (int) $_GET['cid'];
			$company = D('company')->where("id = $cid")->find();
			if (empty($company)) {
				$this->error('参数错误，没有该公司！');
			}
			$this->assign('company', $company);
		}

		if ($this->isPost()) {
			$M = D('reserve');
			$param = htmlString($_POST);
			if (empty($param['name'])) {
				$this->error('请填写您的名字！');
			}
			
			if (empty($param['telephone'])) {
				$this->error('请填写您的电话！');
			}
			if (empty($param['region'])) {
				$this->error('请选择区域！');
			}
			if (empty($param['area'])) {
				$this->error('请选择小区范围！');
			}
			if (!$M->checkLimit($param['telephone'], $sid)) {
				$this->error('一天内同一个监理师只能预约一次！');
			}
			
			$param['sid'] = $sid;
			$param['cid'] = $cid;
			$param['addtime'] = time();
			$param['region_area'] = D('Area')->getName($param['region'], $param['area']);
			if ($M->add($param)) {
				if ($sid > 0) {
					$data = array();
					if ($supervisor['last_reserve'] > strtotime(date('Y-m-01 00:00:00'))) {
						$data['reserve_month'] = $supervisor['reserve_month'] + 1;
					} else {
						$data['reserve_month'] = 1;
					}
					$data['last_reserve'] = time();
					$data['reserve_total'] = $supervisor['reserve_total'] + 1;
					
					D('supervisor')->where("id = $sid")->data($data)->save();
				}
				
				$data = array();
				if ($company['last_reserve'] > strtotime(date('Y-m-01 00:00:00'))) {
					$data['reserve_month'] = $company['reserve_month'] + 1;
				} else {
					$data['reserve_month'] = 1;
				}
				$data['last_reserve'] = time();
				$data['reserve_total'] = $company['reserve_total'] + 1;
				
				D('company')->where("id = $cid")->data($data)->save();
				
				sendMobileMsg(array('18918618060'), "业主" . $param['name'] . "提交了一个预约申请，手机号码是：" . $param['telephone']);
				
				$this->success('已成功提交预约信息！', $sid > 0 ? U('supervisor/index') : U('company/index'));
			} else {
				$this->error('信息提交失败！');
			}
		} else {
			$this->assign('regionHtml', D('Area')->getHtml());
			$this->display();
		}
		
	}
}
