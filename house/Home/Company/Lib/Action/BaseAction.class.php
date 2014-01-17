<?php
/**
 * 基础类
 */
class BaseAction extends Action {
	
	protected $oApp;//项目信息
	protected $oUser;//用户信息
	protected $oCom;//用户信息
	protected $aVerify = array();//需要验证的方法
	protected $model;
	
	public function __construct() {
		parent::__construct();
		//加载项目信息
		if (empty($_SESSION['app'])) {
			$_SESSION['app'] = (object) C('APP_INFO');
			$this->oApp = $data;
		} else {
			$this->oApp = $_SESSION['app'];
		}
		
		//加载用户数据
		$uid = D('Session')->check();
		if ($uid) {
			$oUser = D('User')->getObjectById($uid);
			$this->oUser = $oUser;
			$_SESSION['user'] = $oUser;

 			if(in_array($this->oUser->type, array(3,5))){
				$aCom =  D('Company')->getObjectById($this->oUser->id, 'uid');
				if (empty($aCom)) {
					$this->error('ERROR:没有公司信息！');
				}
				$_SESSION['company'] = $aCom;
				$this->oCom = $aCom;
			} else {
				D('Session')->destroy($this->oUser->id);
				$this->error('禁止操作！', U('User/login'));
			}
		} else {
			if (MODULE_NAME != 'User') {
				redirect('index.php?s=/User/login');
				//$this->error('请先登录', 'index.php?s=/User/login');
			}
		} 
		
		$this->assign('user', (array) $this->oUser);
		$this->assign('isCompany', ($this->oCom->type == 1 ? 1 : 0));
	}
	
	/*
	 * 是否装修公司管理员
	 */
	public function isAdmin() {
		return $this->oUser->id == $this->oCom->uid ? 1 : 0;
	}
	
	/*
	 * 获取对应权限的where条件
	 */
	public function getPurviewCondition() {
		$where = array('cid' => $this->oCom->id);
		if (!$this->isAdmin()) {
			$where['uid'] = $this->oUser->id;
		}
		
		return $where;
	}
	
	/*
	 * 判断数据是否有操作权限
	 */
	public function checkPurviewData($data) {
		if (empty($data)) {
			$this->error('没有该记录');
		}
		if ($data['cid'] != $this->oCom->id) {
			$this->error('非法操作！');
		}
		if (!$this->isAdmin() && $data['uid'] != $this->oUser->id) {
			$this->error('非法操作！');
		}
	}
	
	/*
	 * 是否已登录
	 */
	protected function isLogin() {
		return !empty($this->oUser);
	}
	

	/*
	 * ajax验证接口
	 * @param _NAME 验证单个字段是否合法，验证规则在model层$aValidate设置
	 * html用法： <input type="text" name="account" onblur="ajaxValidate(this)" />
	 */
	public function ajaxValidate() {
		$field = getRequest('FIELD');
		$value = getRequest('VALUE');

		die($this->model->ajaxValidate($field, $value));
	}

	/*
	 * 检测是否post
	 */
	public function checkPost($templateFile='',$charset='',$contentType='',$content='',$prefix='') {
		if (!$this->isPost()) {
			$this->display($templateFile, $charset, $contentType, $content, $prefix);
			die;
		}
	}

	/*
	 * 添加操作
	 */
	protected function _add($dataBase = array(), $returl = '', $return = false) {
		$data = $this->model->getFormData();
		if (!empty($dataBase)) {
			$data = array_merge($data, $dataBase);
		}
		
		if (!$this->model->getError()) {
			$newid = $this->model->addData($data);
			//echo $this->model->getLastSql();die;
			if ($newid) {
				if ($return) {
					return $newid;
				} else {
					$this->success('添加成功！', $returl);
					return;
				}
			}
		}
		if ($return) {
			return false;
		} else {
			$this->error($this->model->getError());
		}
	}
	
	/*
	 * 输出表单模版
	 */
	protected function _display_form($data = array(), $template = '') {
		$this->assign('formHtml', $this->model->getFormHtml($data));
		$this->display($template);
	}
	
	/*
	 * 修改记录
	 */
	protected function _edit($data, $dataBase = array(), $returl = '', $return = false) {
		if (isset($dataBase['_WHERE_'])) {
			$where = $dataBase['_WHERE_'];
			unset($dataBase['_WHERE_']);
		} else {
			$where = array('id' => $data['id']);
		}

		
		$newdata = $this->model->getFormData($data);
		if ($this->model->getError() || !$this->model->checkData($newdata)) {
			$this->error($this->model->getError());
		}
		
		$newdata = array_merge($newdata, $dataBase);
		if ($this->model->where($where)->data($newdata)->save() !== false) {
			if ($return) {
				return $newdata;
			} else {
				$this->success('编辑成功！', $returl);
				return;
			}
		} else {
			if ($return) {
				return false;
			} else {
				$this->error('编辑失败！', $returl);
			}
		}
	}
	
	/*
	 * 删除记录
	 */
	protected function _delete($where) {
		if ($this->model->del($where)) {
			$this->success('删除成功!');
		} else {
			$this->error('删除失败!');
		}
	}
	
	/*
	 * 获取分页列表
	 */
	protected function _getPageList($params) {
		$where = $params['where'];
		$params['templete'] = isset($params['templete']) ? $params['templete'] : '';
		$params['pagesize'] = isset($params['pagesize']) ? $params['pagesize'] : 10;
		$params['vars'] = isset($params['vars']) ? $params['vars'] : array();
		
		$search = $this->model->getSearchCondition();
		$where = array_merge($where, $search);
		import('ORG.Util.Page');//导入分页类
		$count = $this->model->where($where)->count();
		$Page = new Page($count, $params['pagesize']);
		$this->assign('page', $Page->show());
		
		$data = $this->model->where($where)->order($params['order'])->limit($Page->firstRow.','.$Page->listRows)->select();
		
		$searchHtml = $this->model->getSearchHtml();
		$listHtml = $this->model->getListHtml($data, $params['vars']);
		
		$this->assign('searchHtml', $searchHtml);
		$this->assign('listHtml', $listHtml);
		$this->display($params['templete']);
	}
	
	/*
	 * 获取数据列表，不分页
	*/
	protected function _getList($params) {
		$params['templete'] = isset($params['templete']) ? $params['templete'] : '';
		$params['vars'] = isset($params['vars']) ? $params['vars'] : array();
		
		$data = $this->model->where($params['where'])->order($params['order'])->select();
		
		$listHtml = $this->model->getListHtml($data, $params['vars']);
		$this->assign('listHtml', $listHtml);
		
		$this->display($params['templete']);
	}
	
/*
	 * 图片管理
	 */
	protected function _picture($type, $id) {
		$this->model = D('Picture');
		$data = $this->model->getTarget($type, $id);
		$this->checkPurviewData($data);
		$tab = $this->model->getTabTitle($type, $id, $data);
		$this->assign('tabTitle', $tab);
		$this->assign('condition', '/type/' . $type . '/target/' . $id);
		$this->assign('condition1','/type/3'.'/target/' . $id);
		$this->assign('condition2','/type/4'.'/target/' . $id);
		$params = array(
				'where' => array('type' => array('in','2,3,4'),	'target' => $id),
				'order' => 'createtime DESC',
				'templete' => 'Picture:index',
				'vars' => array('focus' => $data['focus']),
				);
		$this->_getList($params);
	}
	
}
?>