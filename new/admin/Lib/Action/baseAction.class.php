<?php
/**
 * 基础Action
 */
 ini_set( 'display_errors', '1' );
error_reporting( E_ALL );

class baseAction extends Action {
	public $user_mode;
	public $admin_mod;
	public $items_cate_mod;
	public $album_mod;
	public $album_cate_mod;
	
	public $shops_sale_mod;
	public $shops_mod;
	public $shops_cate_mod;
	public $coupon_list_mod;
	public $coupon_type_mod;


	public function __construct() {
		parent::__construct();
		
		$this->assign('IMAGE_ROOT', IMAGE_ROOT);
	}	


	function mod_init(){
		$this->admin_mod=D('admin');
		$this->items_cate_mod=D('items_cate');
		$this->user_mode=D('user');
		$this->album_mod=D('album');
		$this->album_cate_mod=D('album_cate');
		
		$this->shops=D('shops');
		$this->shops_sale_mod=D('shops_sale');
		$this->shops_cate_mod=D('shops_cate');
		$this->coupon_list_mod =D('coupon_list');
		$this->coupon_type_mod =D('coupon_type');
	}
	function _initialize() {
		include ROOT_PATH.'/includes/lib_common.php';	
		$this->mod_init();
	
		$this->site_root="http://".$_SERVER['SERVER_NAME'].($_SERVER['SERVER_PORT']==80?'':':'.$_SERVER['SERVER_PORT']).__ROOT__."/";

		$this->assign('site_root',$this->site_root);

		if($_GET['debug']==true){
		dump($_POST);
		dump(M()->getlastsql());
		
		}
		
		// 读取项目公共语言包
		$langSet = C('DEFAULT_LANG');
		if (is_file(LANG_PATH . $langSet . '/common.php')) {
			L(include LANG_PATH . $langSet . '/common.php');
		}
		// 用户权限检查
		$this->check_priv();
		// 载入菜单语言包
		if (is_file(LANG_PATH . $langSet . '/menu.php')) {
			L(include LANG_PATH . $langSet . '/menu.php');
		}
		//需要登陆
		$admin_info =$_SESSION['admin_info'];
		//print_r($admin_info);exit;
		$this->assign('my_info', $admin_info);

		// 顶部菜单
		$model	=	M("group");
		$top_menu	=$model->field('id,title')->where('status=1')->order('sort ASC')->select();
		$this->assign('top_menu',$top_menu);

		//网站配置
		$setting_mod = M('setting');
		$setting = $setting_mod->select();
		foreach ( $setting as $val ) {
			$set[$val['name']] = $val['data'];
		}
		$this->setting = $set;

		$this->assign('show_header', true);
		$this->assign('const',get_defined_constants());

		$this->assign('iframe',$_REQUEST['iframe']);
		$def=array(
			'request'=>$_REQUEST
		);
		$this->assign('def',json_encode($def));
	}
	//检查权限
	public function check_priv()
	{
		if ((!isset($_SESSION['admin_info']) || !$_SESSION['admin_info']) && !in_array(ACTION_NAME, array('login','verify_code'))) {
			$this->redirect('public/login');
		}
		if($_SESSION['admin_info']['id'] == 1) {
			return true;
		}
		if(in_array(ACTION_NAME,array('status','sort_order','ordid'))){
			return true;
		}
		//排除一些不必要的权限检查,/admin/conf/config.php
		foreach (C('IGNORE_PRIV_LIST') as $key=>$val){

			if(MODULE_NAME==$val['module_name']){
				if(count($val['action_list'])==0)return true;

				foreach($val['action_list'] as $action_item){
					if(ACTION_NAME==$action_item)return true;
				}
			}
		}

		$node_mod = D('node');

		$node_id = $node_mod->where(array('module'=>MODULE_NAME, 'action'=>ACTION_NAME))->getField('id');

		$access_mod = D('access');
		$r = $access_mod->where(array('node_id'=>$node_id, 'role_id'=>$_SESSION['admin_info']['role_id']))->count();

		if ($r==0) {
			$this->error(L('_VALID_ACCESS_'));
		}
	}

	public function taobao_client()
	{
		vendor('Taobaotop.TopClient');
		vendor('Taobaotop.RequestCheckUtil');
		vendor('Taobaotop.Logger');
		$tb_top = new TopClient;
		$tb_top->appkey = $this->setting['taobao_appkey'];
		$tb_top->secretKey = $this->setting['taobao_appsecret'];
		return $tb_top;
	}

	//截取中文字符串
	public function mubstr($str,$start,$length)
	{
		import('ORG.Util.String');
		$a = new String();
		$b = $a->msubstr($str,$start,$length);
		return($b);
	}

	protected function error($message, $url_forward='',$ms = 3, $dialog=false, $ajax=false, $returnjs = '')
	{
		$this->jumpUrl = $url_forward;
		$this->waitSecond = $ms;
		$this->assign('dialog',$dialog);
		$this->assign('returnjs',$returnjs);
		parent::error($message, $ajax);
	}

	protected function success($message, $url_forward='',$ms = 3, $dialog=false, $ajax=false, $returnjs = '')
	{
		$this->jumpUrl = $url_forward;
		$this->waitSecond = $ms;
		$this->assign('dialog',$dialog);
		$this->assign('returnjs',$returnjs);
		parent::success($message, $ajax);
	}

	public function simplexml_obj2array($obj)
	{
		if ($obj instanceof SimpleXMLElement) {
			$obj = (array)$obj;
		}

		if (is_array($obj)) {
			$result = $keys = array();
			foreach ( $obj as $key=>$value) {
				isset($keys[$key]) ? ($keys[$key] += 1) : ($keys[$key] = 1);

				if ( $keys[$key] == 1 ) {
					$result[$key] = self::simplexml_obj2array($value);
				} elseif ( $keys[$key] == 2 ) {
					$result[$key] = array($result[$key], self::simplexml_obj2array($value));
				} else if( $keys[$key] > 2 ) {
					$result[$key][] = self::simplexml_obj2array($value);
				}
			}
			return $result;
		} else {
			return $obj;
		}
	}

	public function saddslashes($value)
	{
		if (empty($value)) {
			return $value;
		} else {
			return is_array($value) ? array_map(array('BaseAction','saddslashes'), $value) : addslashes($value);
		}
	}
	/*
	 * 通用删除操作
	 * */
	function delete(){
		$mod=D(MODULE_NAME);

		if (isset($_POST['id']) && is_array($_POST['id'])) {
			$ids = implode(',', $_POST['id']);
			$result=$mod->delete($ids);
		} else {
			$id = intval($_GET['id']);
			$result=$mod->delete($id);
		}

		if($result){
			$this->success(L('operation_success'));
		}else{
			$this->error(L('operation_failure'));
		}
	}
	function check_res($result){
		if($result){
			$this->success(L('operation_success'));
		}else{
			$this->error(L('operation_failure'));
		}
	}
	function exist(){
		
	}
	/*
	 * 通用改变状态
	 * */
	function status(){
		$mod = D(MODULE_NAME);
		$id     = intval($_REQUEST['id']);
		$type   = trim($_REQUEST['type']);
		$sql    = "update ".C('DB_PREFIX').MODULE_NAME." set $type=($type+1)%2 where id='$id'";
		$res    = $mod->execute($sql);
		$values = $mod->where('id='.$id)->find();
		$this->ajaxReturn($values[$type]);
	}
	/*
	 * 通用检查值是否存在,存在则返回true
	 * */
	function ajax_check_exist(){
		$mod = D(MODULE_NAME);
		$clientid=$_REQUEST['clientid'];
		if(!isset($clientid))exit;

		$clientid_val=$_REQUEST[$clientid];
		$id=intval($_REQUEST['id']);
		//print_r($id);exit;
		if($id>0){
			//edit
			$where="$clientid='$clientid_val' and id!=$id";
		}else{
			//add
			$where="$clientid='$clientid_val'";
		}

		//$mod->where($where)->count();
		//print_r($mod->getLastSql());exit;
		$this->ajaxReturn($mod->where($where)->count()>0);
	}
	/*
	 * 通用排序
	 * */
	function sort_order(){
		$mod = D(MODULE_NAME);
		if (isset($_POST['listorders'])) {
			foreach ($_POST['listorders'] as $id=>$sort_order) {
				$data['sort_order'] = $sort_order;
				$mod->where('id='.$id)->save($data);
			}
			$this->success(L('operation_success'));
		}
		$this->error(L('operation_failure'));
	}
	function _stripcslashes($arr){
		if(ini_get('magic_quotes_gpc')!='1')return $arr;
		foreach ($arr as $key=>$val){
			$arr[$key]=stripcslashes($val);
		}
		return $arr;
	}
	function pager($count,$pagesize=20){
		import("ORG.Util.Page");
		$pager=new Page($count,$pagesize);
		$this->assign('page', $pager->show());
		return $pager;
	}
	function append_user($res){
		foreach($res as $key=>$val){
			$res[$key]['user']=$this->user_mode->where('id='.$val['uid'])->find();
		}
		return $res;
	}
	function append_cate($res,$mod){
		
	}
}

//截取中文字符串
function mubstr($str,$start,$length)
{
	import('ORG.Util.String');
	$a = new String();
	$b = $a->msubstr($str,$start,$length);
	return($b);
}

function select($val){
if($val['cate']){$sql['cate']=$val['cate'];}
//if($val['cate']){$sql['cate']=$val['cate'];}
}
?>
