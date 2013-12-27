<?php 


class companyAction extends Action{
	function index(){
		$M = D('company');
		$company = $M->getlist();

		//获取当前公司下的图片数据
		$Project_img = M();
		foreach ($company['data']['data'] AS $key=>$val) {
			 $company['data']['data'][$key]['img'] = $Project_img->table('yjl_project_img')->table('yjl_project_img')->field('url')->where(array('cid'=>$val['id']))->order('sort ASC')->limit('6')->select();
		}
		
		$ass['data']=$company['data']['data'];

		import('ORG.Util.Page');
		$Page = new Page($company['data']['count'],$company['data']['length']);
		$show = $Page->show();
		$ass['page'] = $show;

		$this->assign($ass);
		$this->display();
	}
	
	function company() {
		$cid = intval($_GET['cid']);
		$company = D('company')->where("id = $cid")->find();
		if (empty($company)) {
			$this->error('参数不正确，没有该公司！');
		}
		
		$company ['feature_info'] = htmlspecialchars_decode($company['feature_info']);
		$company ['price_info'] = htmlspecialchars_decode($company['price_info']);
		$company ['process_info'] = htmlspecialchars_decode($company['process_info']);
		$company ['case_info'] = htmlspecialchars_decode($company['case_info']);
		$company ['contact_info'] = htmlspecialchars_decode($company['contact_info']);
		$this->assign('company', $company);
		$this->display();
	}

}
