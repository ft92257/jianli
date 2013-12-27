<?php
class supervisorAction extends Action{
	function index()
	{
		header("Content-type:text/html;charset=utf-8");
		$M = D('supervisor');

		$f = max(1, intval($_GET['f']));
		
		//foreach ($data['data'] AS $key=>$val) {
		//	$data['data'][$key]['img'] = M()->table('yjl_project_img AS m')->join('yjl_project AS p ON p.pid=m.pid')->where(array('p.sid'=>$val['id']))->order('m.sort ASC')->limit('6')->select();
		//}
		$data = $M->getPageList($f);

		//获取当前监理师下项目图片数据
		//$sid = 	$data['data'][0]['id'];
		//$imgList = M()->table('yjl_project_img AS m')->join('yjl_project AS p ON p.pid=m.pid')->where(array('p.sid'=>$sid))->order('m.sort ASC')->limit('6')->select();
		foreach ($data['data'] AS $key=>$val) {
			$data['data'][$key]['img'] = M()->table('yjl_project_img AS m')->join('yjl_project AS p ON p.pid=m.pid')->where(array('p.sid'=>$val['id']))->order('m.sort ASC')->limit('6')->select();
		}
		
		$this->assign('url',C('TMPL_PARSE_STRING.__IMAGES__'));
		$this->assign('imgList',$imgList);
		
		$this->assign('f', $f);
		$this->assign('data', $data['data']);
		
		

		import('ORG.Util.Page');
		$Page = new Page($data['count'],$data['length']);
		$show = $Page->show();
		$this->assign('page', $show);

		$this->display();

	}
	
	function supervisor() {
		
		$sid = intval($_GET['sid']);
		$supervisor = D('supervisor')->where("id = $sid")->find();
		if (empty($supervisor)) {
			$this->error('参数不正确，没有该监理师！');
		}
		$supervisor['service_area'] = $supervisor['service_area'] ? $supervisor['service_area'] : '上海';
		
		//获取当前监理师下所有项目列表
		$pid = $supervisor['id'];
		$allData = M()->table('yjl_project AS p')->join('yjl_project_img AS m ON p.pid=m.pid')->where(array('p.sid'=>$pid))->order('p.pid ASC,m.sort ASC')->select();
	
		$projectList = array();
		foreach ($allData AS $key=>$val) {
			$projectList[$val['pid']]['pid'] = $val['pid'];
			$projectList[$val['pid']]['title'] = $val['title'];
			$projectList[$val['pid']]['content'] = $val['content'];
			$projectList[$val['pid']]['img'][] = $val['url'];
		}
		
		$this->assign('url',C('TMPL_PARSE_STRING.__IMAGES__'));
		$this->assign('projectList',$projectList);
		
		
		$this->assign('supervisor', $supervisor);
		$this->display();
	}
	
	
	/**
	 * 监理师类型
	 */
	public function consult() {
		$type = $this->_get('type');
		$DB = M();
	
		//字段信息
		$fielsInfo = $DB->query("select COLUMN_NAME from information_schema.COLUMNS where table_name = 'yjl_project_supervision' and table_schema = 'yjl2' ");
		$arrField = array();
		foreach ($fielsInfo AS &$val) {
			array_push($arrField,$val['COLUMN_NAME']);
		}
		//当前URL定位的字段名
		$field = $arrField[$type];
		if (empty($field)) $field = 'houser';

		if (in_array($field,array('houser', 'why', 'green','contact','recruit'))) {
			$html = $DB->table('yjl_project_supervision')->getField($field);
			
			$this->assign('html',$html);
			$this->display();
		} else {
			$html = $DB->table('yjl_project_supervision')->getField($field);
				
			$this->assign('html',$html);
			$this->display('worker');
		}
	
	}
	
}
