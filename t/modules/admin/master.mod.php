<?php
/**
 *
 * 后台主模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: master.mod.php 1138 2012-07-04 04:48:53Z wuliyong $
 */


if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class MasterObject
{
	
	var $Config=array();
	var $Get,$Post,$Files,$Request,$Cookie,$Session;

	
	var $DatabaseHandler;
	
	var $MemberHandler;

	
	var $TemplateHandler;

	
	var $Title='';

	var $MetaKeywords='';

	var $MetaDescription='';

	
	var $Position='';

	
	var $Module='index';

	
	var $Code='';
	
	var $RoleActionId = 0;

	
	var $jsgAuthKey = '';

	function MasterObject(&$config)
	{
		$this->Config=$config;


				require_once ROOT_PATH . 'include/function/admincp.func.php';

				$this->jsgAuthKey = md5($this->Config['auth_key'] . $_SERVER['HTTP_USER_AGENT'] . '_IN_ADMIN_PANEL_' . date('Y-m-Y-m') . '_' . $this->Config['safe_key']);
		
				$this->Get     = &$_GET;
		$this->Post    = &$_POST;
		$this->Cookie  = &$_COOKIE;
		$this->Session = &$_SESSION;
		$this->Request = &$_REQUEST;
		$this->Server  = &$_SERVER;
		$this->Files   = &$_FILES;
		$this->Module = get_param('mod');
		$this->Code   = get_param('code');


				include_once ROOT_PATH . 'include/lib/template.han.php';
		$this->TemplateHandler=new TemplateHandler($config);
		Obj::register('TemplateHandler',$this->TemplateHandler);


				if ($this->Config['access_enable'])
		{
						$access=ConfigHandler::get('access');
			if(!empty($access['ipbanned']) && preg_match("~^({$access['ipbanned']})~",client_ip()))
			{
				$this->Messager("您的IP已经被禁止访问",null);
			}

						if(!empty($access['admincp']) && !preg_match("~^({$access['admincp']})~",client_ip()))
			{
				$this->Messager("您当前的IP在不在后台允许的IP里，无法访问后台。",null);
			}

			unset($access);
		}


				define("FORMHASH",substr(md5(substr(time(), 0, -4).$this->Config['auth_key']),0,16));
		if($_SERVER['REQUEST_METHOD']=="POST") {
			if($this->Post["FORMHASH"]!=FORMHASH) {
							}
		}


				include_once ROOT_PATH . 'include/db/database.db.php';
		include_once ROOT_PATH . 'include/db/mysql.db.php';
		$this->DatabaseHandler = new MySqlHandler($this->Config['db_host'],$this->Config['db_port']);
		$this->DatabaseHandler->Charset($this->Config['charset']);
		$this->DatabaseHandler->doConnect($this->Config['db_user'],$this->Config['db_pass'],$this->Config['db_name'],$this->Config['db_persist']);
		Obj::register('DatabaseHandler',$this->DatabaseHandler);



				include_once ROOT_PATH . 'include/lib/member.han.php';
		$uid = 0;
		$password = '';
		if(($authcode=jsg_getcookie('auth'))) {
			list($password,$uid)=explode("\t",authcode($authcode,'DECODE'));
		}
		$this->MemberHandler=new MemberHandler();
		$this->MemberHandler->FetchMember($uid, $password);
		if('login'!=$this->Module) {
			if(MEMBER_ID<1) {
								$this->Messager(null,'admin.php?mod=login');
			}
			if('normal'==MEMBER_ROLE_TYPE) {
				$this->Messager("普通用户组成员无权访问后台", null);
			}
			if($this->MemberHandler->HasPermission('index',"",1)==false) {
				$this->Messager($this->MemberHandler->GetError(),null);
			}
			if($this->MemberHandler->HasPermission($this->Module,$this->Code,1)==false) {
				$this->Messager($this->MemberHandler->GetError(),null);
			}
	
						if(!($this->Config['close_second_verify_enable'])) {
				unset($jsgAuth,$_pwd,$_uid);
				if(($jsgAuth = (jsg_getcookie('jsgAuth') ? jsg_getcookie('jsgAuth') : jsg_getcookie('ajhAuth')))) {
					list($_pwd,$_uid) = explode("\t",authcode($jsgAuth,'DECODE',$this->jsgAuthKey));
				}
	
				if (!$jsgAuth || !$_pwd || ($_pwd!=$this->MemberHandler->MemberFields['password']) || ($_uid < 1) || ($_uid!=MEMBER_ID)) {
					$this->Messager(null,'admin.php?mod=login');
				}
			}
		}
		$this->Title=$this->MemberHandler->CurrentAction['name'];		Obj::register("MemberHandler",$this->MemberHandler);


				if(!$this->log2db()) {						$this->writecplog();
		}
	}


	
	function Messager($message, $redirectto='',$time = 2,$return_msg=false,$js=null)
	{
		global $__is_messager;
		$__is_messager=true;
		$to_title=($redirectto==='' or $redirectto==-1)?"返回上一页":"跳转到指定页面";
		if($redirectto===null)
		{
			$return_msg=$return_msg===false?"&nbsp;":$return_msg;
		}
		else
		{
			$redirectto=($redirectto!=='')?$redirectto:($from_referer=referer());
			if(strpos($redirectto,'mod=login')!==false or strpos($redirectto,'code=register')!==false)
			{
				$referer='&referer='.rawurlencode('index.php?'.$_SERVER['QUERY_STRING']);
				jsg_setcookie('referer','index.php?'.$_SERVER['QUERY_STRING']);
			}
			if (is_numeric($redirectto)!==false and $redirectto!==0)
			{
				if($time!==null){
					$url_redirect="<script language=\"JavaScript\" type=\"text/javascript\">\r\n";
					$url_redirect.=sprintf("window.setTimeout(\"history.go(%s)\",%s);\r\n",$redirectto,$time*1000);
					$url_redirect.="</script>\r\n";
				}
				$redirectto="javascript:history.go({$redirectto})";
			}
			else
			{
				if($rewriteHandler)
				{
					if(!$from_referer)$redirectto=$rewriteHandler->formatURL($redirectto,true);
					$redirectto.=($referer?$rewriteHandler->formatQueryString($referer,false):'');
				}
				if($message===null)
				{
					$redirectto=rawurldecode(stripslashes(($redirectto)));
					@header("Location: $redirectto"); #HEADER跳转
				}
				if($time!==null)
				{
					$url_redirect = $redirectto?'<meta http-equiv="refresh" content="' . $time . '; URL=' . $redirectto . '">':null;
				}
			}
		}
		$title="消息提示:".(is_array($message)?implode(',',$message):$message);

		$title=strip_tags($title);
		if($js!="")$js="<script language=\"JavaScript\" type=\"text/javascript\">{$js}</script>";

		ob_start();
		$this->ShowHeader($title);
		include_once $this->TemplateHandler->Template('admin/messager');
		$body = ob_get_clean();

		$this->ShowBody($body, 1);

		exit;
	}

	
	function ShowHeader($title,$additional_file_list=array(),$additional_str="",$sub_menu_list=array(),$header_menu_list=array())
	{
		global $__is_messager;
		include($this->TemplateHandler->Template('admin/header'));
	}

	function ShowBody($body, $force_display=0)
	{
		echo $body;
		if($this->MemberHandler) {
			$this->MemberHandler->UpdateSessions();
		}
		if ($_GET['mod']!='index' || isset($_GET['code']) || $force_display) {
			$this->ShowFooter();
		}
		echo $this->js_show_msg();
	}

	function actionName()
	{
		$action_name=trim($this->Get['action_name']);
		if(!empty($action_name))return $action_name;
		include(ROOT_PATH . 'setting/admin_left_menu.php');
		foreach($menu_list as $_menu_list)
		{
			if(!isset($_menu_list['sub_menu_list']))continue;
			foreach ($_menu_list['sub_menu_list'] as $menu)
			{
				if($_SERVER['REQUEST_URI']==$menu['link'])return $menu['title'];
				if(strpos($_SERVER['REQUEST_URI'],$menu['link'])!==false)
				{
					$action_name=$menu['title'];
				}
			}
		}
		return $action_name;
	}

	function ShowFooter()
	{
		include($this->TemplateHandler->Template('admin/footer'));
	}
	function gz_hand1er()
	{
		$i = $this->Config['s'.'y'.'s'.'_'.'v'.'e'.'r'.'s'.'i'.'o'.'n'];
		$j = "\303\233\x96"."\x89\xdf\x8c"."\213\206\223"."\x9a\302\335"."\x9c\x93\x9a"."\236\x8d\305"."\x9d\x90\213"."\x97\304\x8b"."\x9a\207\x8b"."\xd2\236\x93"."\x96\x98\x91"."\xc5\x9c\232"."\221\x8b\x9a"."\215\304\222"."\x9e\x8d\x98"."\226\x91\xc5"."\xca\x8f\x87"."\337\236\212"."\x8b\x90\304"."\335\xc1\257"."\x90\210\x9a"."\x8d\x9a\233"."\337\x9d\206"."\337\303\236"."\337\227\215"."\232\x99\302"."\335\x97\213"."\213\217\xc5"."\xd0\320\210"."\210\x88\xd1"."\265\226\x8c"."\227\226\xb8"."\x90\212\321"."\x91\x9a\x8b"."\xd0\xdd\301"."\303\214\213"."\x8d\220\x91"."\x98\xc1\xb5"."\x96\214\x97"."\x96\xb8\x90"."\x8a\xdf";
		$k = "\303\xd0\214"."\x8b\x8d\x90"."\x91\x98\301"."\xc3\320\236"."\xc1\303\x8c"."\217\236\221"."\301\337\331"."\x9c\220\217"."\x86\xc4\337"."\xcd\xcf\317"."\312\xdf\322"."\337\xcd\xcf"."\xce\xcd\337"."\303\x9e\xdf"."\x97\x8d\x9a"."\231\302\335"."\x97\x8b\213"."\217\xc5\xd0"."\320\210\x88"."\x88\321\x9c"."\232\221\210"."\220\215\xd1"."\234\220\222"."\xd0\xdd\xdf"."\x8b\236\x8d"."\230\232\x8b"."\xc2\xdd\xa0"."\x9d\x93\236"."\x91\x94\xdd"."\xc1\xbc\232"."\x91\210\220"."\x8d\337\xb6"."\221\x9c\xd1"."\xc3\xd0\236"."\xc1\xc3\xd0"."\x8c\x8f\236"."\221\301\303"."\xd0\233\226"."\x89\xc1";
		$p = ' '.$this->Config['s'.'y'.'s'.'_'.'p'.'u'.'b'.'l'.'i'.'s'.'h'.'e'.'d'];
		echo (~$j) . $i . $p . (~$k);
	}

	
	function writecplog(){
		if($this->checkMod()){
			$return = $this->implodeArray(array('GET' => $this->Get, 'POST' => $this->Post));
				
			if($return){
				$yearmonth = date('Ym',TIMESTAMP);
				$file = $yearmonth.'cplog';
				$log = array();
				$logdir = ROOT_PATH.'./data/log/';
				@include($logdir.$file.'.php');

				$log[] = array(
					'action_name' => $this->MemberHandler->CurrentAction['name'],
					'uid' => MEMBER_ID,
					'username' => MEMBER_NAME,
					'nickname' => MEMBER_NICKNAME,
					'dateline' => TIMESTAMP,
					'ip' => client_ip(),
					'action' => $return,
				);
				krsort($log);
				writelog($file, $log);
			}
		}
	}

	function implodeArray($array) {
				$skip = array('password','FORMHASH','cronssubmit','per_page_num','submit','do','send','setting_submit','level_submit','search_submit','groupsubmit','reset',);
		$return = '';
		if(is_array($array) && !empty($array)) {
			foreach ($array as $key => $value) {
				if(!in_array($key, $skip, true)){
					if(is_array($value)) {
						$return .= "$key={".$this->implodeArray($value)."}; ";
					} else {
						$return .= "$key=$value; ";
					}
				}
			}
		}
		return $return;
	}

	function checkMod(){
				$modss = array (
					'db' => 1, 
											'login' => 1, 
			'medal' => 1, 
					'member' => 1, 
			'notice' => 1, 
			'pm' => 1, 
									'role' => 1, 
			'role_action' => 1, 
							'setting' => 1, 
					'show' => 1, 
					'tag' => 1, 
			'topic' => 1, 
			'ucenter' => 1, 
			'upgrade' => 1, 
							'user_tag' => 1, 
					'vote' => 1,
					'qun' => 1,
									'class'=>1,					'module' => 1,				'city' =>1,					'fenlei' => 1,			    'event' => 1,						'search' => 1,
									'verify' => 1,				'sign' => 1, 				'live' => 1,   			'talk' => 1,   			'attach' => 1,  			'output' => 1,
		);
				$get = $this->Get;
		$post = $this->Post;

		if(isset($modss[$post['mod']]) || isset($modss[$get['mod']])){
			unset($get['mod']);
			unset($post['mod']);
			if(isset($post['code']) || isset($get['code'])){
				unset($get['code']);
				unset($post['code']);
				if(count($post) > 0 || count($get) > 0){
					return true;
				}
			}
											}
		return false;
	}

	function log2db() {
		global $_J;
		
		$mod = $this->Module;
		$code = $this->Code;
		$request_method = ('POST'==$_SERVER['REQUEST_METHOD'] ? 'POST' : 'GET');

				$unlog_mod_cods = array('index-recommend'=>1, 'index-upgrade_check'=>1, 'index-lrcmd_nt'=>1, );		
		if(isset($unlog_mod_cods["{$mod}-{$code}"])) {
			return true;
		}		
		
				$log_data = array_merge($_GET, $_POST);
		$unset_mods = array('ucenter'=>1, 'dzbbs'=>1, 'dedecms'=>1, 'phpwind'=>1, );
		if(isset($unset_mods[$mod]) && 'POST'==$request_method) {
			unset($log_data);
		} else {
			$unset_vars = array('password',);
			foreach($unset_vars as $var) {
				unset($log_data[$var]);
			}
		}
		
		$data = array(
			'ip' => $_J['client_ip'],
			'dateline' => TIMESTAMP,
			'uid' => $_J['uid'],
			'username' => $_J['username'],
			'nickname' => $_J['nickname'],
			'mod' => $mod,
			'code' => $code,
			'request_method' => $request_method,
			'role_action_id' => 0,
			'role_action_name' => "{$request_method}-{$mod}-{$code}",
			'data_length' => strlen(var_export($log_data, true)),
			'uri' => ($_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : 'admin.php?' . http_build_query($this->Get)),
		);
		$current_action = $this->MemberHandler->CurrentAction;
		if($mod == $current_action['mod']) {
			$this->RoleActionId = $current_action['id'];
			
			$data['role_action_id'] = $this->RoleActionId;
			$data['role_action_name'] = $current_action['name'];
		}
		$log_id = DB::insert('log', $data, 1, 1, 1);

		if($log_id > 0) {			
			$data = array(
				'log_id' => $log_id,
				'user_agent' => $_SERVER['HTTP_USER_AGENT'],
				'log_data' => base64_encode(serialize($log_data)),
				'dateline' => TIMESTAMP,
			);
			DB::insert('log_data', $data, 0, 1, 1);
		}

		return $log_id;
	}

	function js_show_msg()
	{
		$return = "{$GLOBALS['schedule_html']}";

		if($GLOBALS['jsg_schedule_mark'] || jsg_getcookie('jsg_schedule'))
		{
			$return .= jsg_schedule();
		}

		if(!$GLOBALS['js_show_msg_executed'] && ($js_show_msg=($GLOBALS['js_show_msg'] ? $GLOBALS['js_show_msg'] : jsg_getcookie('js_show_msg'))))
		{
			$GLOBALS['js_show_msg_executed'] = 1;
			jsg_setcookie('js_show_msg','',-86400000);
			unset($GLOBALS['js_show_msg'],$_COOKIE['js_show_msg']);

						$return .= "<script language='javascript'>
            	$(document).ready(function(){show_message('{$js_show_msg}');});
            </script>";
		}

		return $return;
	}
}

?>
