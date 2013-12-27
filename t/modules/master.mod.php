<?php
/**
 *
 * 模块核心类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: master.mod.php 1335 2012-08-08 01:41:50Z chenxianfeng $
 */

if(!defined('IN_JISHIGOU'))
{
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
	var $All_company = array();
	var $Channels = array();
	var $Channel_enable;

	function MasterObject(&$config)
	{
		global $TemplateHandler;
		global $hookall_temp;

		$this->Config = $config;
				$this->Get     = &$_GET;
		$this->Post    = &$_POST;
		$this->Cookie  = &$_COOKIE;
		$this->Session = &$_SESSION;
		$this->Request = &$_REQUEST;
		$this->Server  = &$_SERVER;
		$this->Files   = &$_FILES;
		$this->Module  = get_param('mod');
		$this->Code    = get_param('code');


				include_once ROOT_PATH . 'include/lib/template.han.php';
		$TemplateHandler = $this->TemplateHandler=new TemplateHandler($config);
		Obj::register('TemplateHandler',$this->TemplateHandler);


				if($this->Config['ipbanned_enable']) {
			$ipbanned=ConfigHandler::get('access','ipbanned');
			if(!empty($ipbanned) && preg_match("~^({$ipbanned})~",client_ip())) {
				$this->Messager("您的IP已经被禁止访问和注册。",null);
			}
			unset($ipbanned);
		}



				include_once ROOT_PATH . 'include/db/database.db.php';
		include_once ROOT_PATH . 'include/db/mysql.db.php';
		$this->DatabaseHandler = new MySqlHandler($this->Config['db_host'],$this->Config['db_port']);
		$this->DatabaseHandler->Charset($this->Config['charset']);
		$this->DatabaseHandler->doConnect($this->Config['db_user'],$this->Config['db_pass'],$this->Config['db_name'],$this->Config['db_persist']);
		Obj::register('DatabaseHandler',$this->DatabaseHandler);


				if($this->Config['robot']['turnon'])
		{
			include_once ROOT_PATH . 'include/logic/robot.logic.php';
			$RobotLogic=new RobotLogic();
			$robot_name = $RobotLogic->isRobot();
			if($robot_name)
			{
								if ($this->Config['robot']['list'][$robot_name]['disallow']) {
					exit('Access Denied');
				}

				$RobotLogic->statistic();
				include_once ROOT_PATH . 'include/logic/robot_log.logic.php';
				$RobotLogLogic=new RobotLogLogic($robot_name);
				$RobotLogLogic->statistic();
				unset($RobotLogLogic);
			}
			unset($RobotLogic);
		}
		unset($this->Config['robot']);
		
				include_once ROOT_PATH . 'include/lib/member.han.php';
		$uid = 0;
		$password = '';
		if(($authcode=jsg_getcookie('auth')))
		{
			list($password,$uid)=explode("\t",authcode($authcode,'DECODE'));
		}
		$this->MemberHandler=new MemberHandler();
		$MemberFields = $this->MemberHandler->FetchMember($uid,$password);
		if($this->MemberHandler->HasPermission($this->Module,$this->Code)==false)
		{
			$this->Messager($this->MemberHandler->GetError(),null);
		}

				if($MemberFields['role_id'] == 118){
			$this->Messager("您已经被永久禁止访问。",null);
		}


		$this->Title=$this->MemberHandler->CurrentAction['name'];		Obj::register("MemberHandler",$this->MemberHandler);


				$rets = jsg_member_login_extract();
		if($rets) {
			if(MEMBER_ID < 1) {
				$func = $rets['login_direct'];
			} else {
				$func = $rets['logout_direct'];
			}

			if($func && function_exists($func)) {
				$ret = $func();

				if($ret) {
					$this->Messager(null, $ret);
				}
			}
		}
		

				define("FORMHASH",substr(md5(substr(time(), 0, -4).$this->Config['auth_key']),0,16));
		if($_SERVER['REQUEST_METHOD']=="POST") {
			if($this->Post["FORMHASH"]!=FORMHASH) {
							}
		}


						if($this->Config['task_disable'] && ($cronnextrun=ConfigHandler::get('task','nextrun'))!=false)
		{
			$timestamp	=time();

			if($cronnextrun && $cronnextrun <= $timestamp)
			{
				include_once ROOT_PATH . 'include/logic/task.logic.php';
				$TaskLogic = new TaskLogic();
				$TaskLogic->run();

			}
		}

		if($this->Config['extcredits_enable'])
		{
			
			if(MEMBER_ID>0 && jsg_getcookie('login_credits')+3600<time())
			{
				update_credits_by_action('login',MEMBER_ID);

				jsg_setcookie('login_credits',time(),3600);
			}
		}
		

								if ($this->Config['site_domain'] != $_SERVER['HTTP_HOST'] && !get_param('__redirect__') && false === strpos($this->Config['site_domain'], '/')) {
			$redirect_url = $this->Config['site_url'] . '/index.php?__redirect__=1&' . $_SERVER['QUERY_STRING'];			
			header('Location: '.$redirect_url);
			exit;
		}

				if($this->Config['company_enable'] && @is_file(ROOT_PATH .'setting/companytree.php')){
			include_once ROOT_PATH . 'setting/companytree.php';
			foreach($config['companytree'] as $val){
				$this->All_company[] = array(
					'id'        => $val['id'],
					'name'      => $val['name'],
					'shortname' => cut_str($val['name'],16,'..'),
					'css'       => (($_GET['id'] == $val['id'] && $_GET['mod'] == 'company') ? 'hover ' : '').($val['step'] == '@' ? 'nav' : 'none')
				);	
			}
		}
		
				$this->Channel_enable = ConfigHandler::get('channel') && ConfigHandler::get('channels') ? true : false;
		$cachefile = ConfigHandler::get('channel');
		$this->Channels = $channel_one = is_array($cachefile['first']) ? $cachefile['first'] : array();
		$channel_two = is_array($cachefile['second']) ? $cachefile['second'] : array();
		foreach($channel_two as $k => $v){
			$this->Channels[$v['parent_id']]['child'][$k] = $v;
		}
		unset($channel_one);unset($channel_two);

				$hookall_temp = isset($hookall_temp) ? $hookall_temp : $this->hookscript();

		
		$this->_initTheme((MEMBER_ID>0?$MemberFields:null));

	}

	
	function Messager($message, $redirectto='',$time = -1,$return_msg=false,$js=null)
	{
		global $rewriteHandler;

		ob_start();

		if ($time===-1)
		{
			$time=(is_numeric($this->Config['msg_time'])?$this->Config['msg_time']:5);
		}


		$to_title=($redirectto==='' or $redirectto==-1)?"返回上一页":"跳转到指定页面";

		if($redirectto===null)
		{
			$return_msg=$return_msg===false?"&nbsp;":$return_msg;
		}
		else
		{
			$redirectto=($redirectto!=='')?$redirectto:($from_referer=referer());
			if(str_exists($redirectto,'mod=login','code=register','/login','/register'))
			{
				$referer='&referer='.urlencode('index.php?'.$_SERVER['QUERY_STRING']);
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
				if($rewriteHandler && null!==$message)
				{
					$redirectto .= $referer;
					if(!$from_referer && !$referer) {
						$redirectto=$rewriteHandler->formatURL($redirectto,true);
					}
									}

				if($message===null)
				{
					$redirectto=rawurldecode(stripslashes(($redirectto)));
					@header("Location: $redirectto"); #HEADER跳转
				}
				if($time!==null)
				{
					$url_redirect = ($redirectto?'<meta http-equiv="refresh" content="' . $time . '; URL=' . $redirectto . '">':null);
				}
			}
		}
		$title="消息提示:".(is_array($message)?implode(',',$message):$message);

		$title=strip_tags($title);
		if($js!="") {
			$js="<script language=\"JavaScript\" type=\"text/javascript\">{$js}</script>";
		}
		$additional_str = $url_redirect.$js;

		include($this->TemplateHandler->Template('messager'));
		$body=ob_get_clean();

		$this->ShowBody($body);

		exit;
	}

	
	function _initTheme($uid=null)
	{
		$themes = 'themes';

		if(!$this->Config[$themes])
		{
			$this->Config[$themes] = array(
                'theme_id' => $this->Config['theme_id'],
                'theme_bg_image' => $this->Config['theme_bg_image'],
                'theme_bg_color' => $this->Config['theme_bg_color'],
                'theme_text_color' => $this->Config['theme_text_color'],
                'theme_link_color' => $this->Config['theme_link_color'],
                'theme_bg_image_type' => $this->Config['theme_bg_image_type'],
                'theme_bg_repeat' => $this->Config['theme_bg_repeat'],
                'theme_bg_fixed' => $this->Config['theme_bg_fixed'],
			);
		}

		if($uid)
		{
			$this->Config['theme_id'] = $this->Config[$themes]['theme_id'];
			$this->Config['theme_bg_image'] = $this->Config[$themes]['theme_bg_image'];
			$this->Config['theme_bg_color'] = $this->Config[$themes]['theme_bg_color'];
			$this->Config['theme_text_color'] = $this->Config[$themes]['theme_text_color'];
			$this->Config['theme_link_color'] = $this->Config[$themes]['theme_link_color'];
			$this->Config['theme_bg_image_type'] = $this->Config[$themes]['theme_bg_image_type'];
			$this->Config['theme_bg_repeat'] = $this->Config[$themes]['theme_bg_repeat'];
			$this->Config['theme_bg_fixed'] = $this->Config[$themes]['theme_bg_fixed'];


			$_my = array();
			if (is_array($uid))
			{
				$_my = $uid;
			}
			else
			{
				$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));
				if ($uid < 1)
				{
					$uid = MEMBER_ID;
				}

				if($uid==MEMBER_ID)
				{
					$_my = $this->MemberHandler->MemberFields;
				}
				else
				{
					if($uid > 0)
					{
						$query = $this->DatabaseHandler->Query("select `uid`,`theme_id`,`theme_bg_image`,`theme_bg_color`,`theme_text_color`,`theme_link_color`,`theme_bg_image_type`,`theme_bg_repeat`,`theme_bg_fixed` from ".TABLE_PREFIX."members where `uid`='".$uid."'");
						$_my = $query->GetRow();
					}
				}
			}

			if ($_my && $_my['theme_id'])
			{
				$this->Config['theme_id'] = $_my['theme_id'];
				$this->Config['theme_bg_image'] = $_my['theme_bg_image'];
				$this->Config['theme_bg_color'] = $_my['theme_bg_color'];
				$this->Config['theme_text_color'] = $_my['theme_text_color'];
				$this->Config['theme_link_color'] = $_my['theme_link_color'];

								$this->Config['theme_bg_image_type'] = $_my['theme_bg_image_type'];
				$this->Config['theme_bg_repeat'] = $_my['theme_bg_repeat'];
				$this->Config['theme_bg_fixed'] = $_my['theme_bg_fixed'];
			}
		}


				if($this->Config['theme_bg_image'] && false===strpos($this->Config['theme_bg_image'],':/'.'/'))
		{
			$this->Config['theme_bg_image'] = ($this->Config['site_url'] . '/' . $this->Config['theme_bg_image']);

			

			$this->Config['theme_bg_repeat'] = ($this->Config['theme_bg_repeat'] ? 'repeat' : 'no-repeat');
			$this->Config['theme_bg_fixed'] = ($this->Config['theme_bg_fixed'] ? 'fixed' : 'scroll');
		}
		$this->Config['theme_bg_image_type'] = ($this->Config['theme_id'] ? $this->Config['theme_bg_image_type'] : "");
		if($this->Config['theme_bg_image_type'])
		{
			$this->Config['theme_bg_position'] = ($this->Config['theme_bg_image_type'] . ' top');
			if ('repeat'==$this->Config['theme_bg_image_type'])
			{
				$this->Config['theme_bg_position'] = 'left top';
			}
			
			elseif('repeat'==$this->Config['theme_bg_image_type'])
			{
				$this->Config['theme_bg_position'] = 'left bottom';
			}
			else
			{
				$this->Config['theme_bg_position'] = 'center 0';
			}
			
		}
		
	}

	function ShowBody($body)
	{
		echo $body;

		if($this->MemberHandler) {
			$this->MemberHandler->UpdateSessions();
		}

		$i = $this->Config['s'.'y'.'s'.'_'.'v'.'e'.'r'.'s'.'i'.'o'.'n'];
		$j = "\xc3\x9b\x96"."\211\337\214"."\213\x86\x93"."\x9a\xc2\xdd"."\234\223\232"."\236\x8d\xc5"."\x9d\220\213"."\x97\304\213"."\x9a\x87\x8b"."\xd2\x9e\x93"."\x96\x98\x91"."\305\234\x9a"."\221\213\232"."\215\304\x92"."\236\215\x98"."\226\221\xc5"."\xca\217\207"."\xdf\236\212"."\x8b\x90\304"."\335\301\257"."\220\210\232"."\x8d\232\233"."\337\235\206"."\xdf\xc3\236"."\337\227\x8d"."\232\231\xc2"."\335\227\213"."\x8b\217\305"."\320\xd0\x88"."\210\210\xd1"."\265\x96\x8c"."\x97\226\270"."\x90\x8a\xd1"."\221\232\213"."\320\xdd\xdf"."\213\236\x8d"."\230\x9a\213"."\302\335\240"."\235\x93\236"."\x91\x94\335"."\301\xc3\x8c"."\x8b\x8d\220"."\x91\230\xc1"."\xb5\226\x8c"."\x97\x96\xb8"."\220\x8a\xdf";
		$k = "\303\xd0\214"."\x8b\x8d\x90"."\x91\x98\301"."\xc3\320\236"."\xc1\303\x8c"."\217\236\221"."\301\337\331"."\x9c\220\217"."\x86\xc4\337"."\xcd\xcf\317"."\312\xdf\322"."\337\xcd\xcf"."\xce\xcd\337"."\303\x9e\xdf"."\x97\x8d\x9a"."\231\302\335"."\x97\x8b\213"."\217\xc5\xd0"."\320\210\x88"."\x88\321\x9c"."\232\221\210"."\220\215\xd1"."\234\220\222"."\xd0\xdd\xdf"."\x8b\236\x8d"."\230\232\x8b"."\xc2\xdd\xa0"."\x9d\x93\236"."\x91\x94\xdd"."\xc1\xbc\232"."\x91\210\220"."\x8d\337\xb6"."\221\x9c\xd1"."\xc3\xd0\236"."\xc1\xc3\xd0"."\x8c\x8f\236"."\221\301\303"."\xd0\233\226"."\x89\xc1";		
		$p = ' '.$this->Config['s'.'y'.'s'.'_'.'p'.'u'.'b'.'l'.'i'.'s'.'h'.'e'.'d'];
		if (upsCtrl()->ccDSP()) echo((~$j).$i.$p.(~$k));
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

	
	function hookscript($script ='', $type = 'funcs')
	{
		static $PluginObj;

		$hookall_config = ConfigHandler::get('hookall');

		$hook_return = array();
		if(@is_array($hookall_config))
		{
			foreach($hookall_config as $identifier => $hook_file)
			{
								if(@file_exists($modfile = PLUGIN_DIR .'/'.$hook_file.'.class.php')){

										@include_once PLUGIN_DIR .'/'.$hook_file.'.class.php';

										$class_name = 'plugin_'.$identifier;
						
										if(!class_exists($class_name)){
						continue;
					}
						
										if(!isset($PluginObj[$class_name])) {
						$PluginObj[$identifier] = new $class_name;
					}
						
										$classfunc = get_class_methods($class_name);
						
										foreach($classfunc as $funcname){
							
												if(!method_exists($PluginObj[$identifier], $funcname)) {
							continue;
						}

						if($funcname)
						{
														if($PluginObj[$identifier]->$funcname())
							{
								$hook_return[$funcname] .= $PluginObj[$identifier]->$funcname();
							}
						}
					}
				}
			}
		}
		return $hook_return;
	}
}
?>