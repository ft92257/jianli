<?php
/**
 * 移动客户端公用模块
 * 
 * @author 		~ZZ~<505171269@qq.com>
 * @version		v1.0 $Date:2011-09-30
 */

if(!defined('IN_JISHIGOU')) {
    exit('Access Denied');
}
define("OUT_CHARSET", "UTF-8");
class MasterObject
{
	
	var $Config=array();

	var $Get,$Post,$Files,$Request,$Cookie,$Session;

	
	var $DatabaseHandler;
	
	var $MemberHandler;

	
	var $TemplateHandler;

	
	var $CookieHandler;

	
	var $Title='';

	var $MetaKeywords='';

	var $MetaDescription='';

	
	var $Position='';

	
	var $Module='index';

	
	var $Code='';

	var $hookall_temp = '';

	function MasterObject(&$config)
	{
		global $TemplateHandler;
		require_once ROOT_PATH . 'mobile/include/function/mobile.func.php';
				$config['client_type'] = '';
		
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		
				if (empty($user_agent)) {
			exit('Access Denied');	
		}
		
				$pc_browser = false;
		if (preg_match("/android/i", $user_agent)) {
			$config['client_type'] = "android";	
		} else if (preg_match("/iphone/i", $user_agent)) {
			$config['client_type'] = "iphone";
		} else {
			$pc_browser = true;
		}
		
				$config['is_mobile_client'] = false;
		if (isset($_GET['JSG_SESSION']) && isset($_GET['iv']) && isset($_GET['app_key']) && isset($_GET['app_secret']) &&isset($_GET['bt'])) {
			$config['is_mobile_client'] = true;
			define("IS_MOBILE_CLIENT", true);
		} else {
						if (DEBUG !== true && $pc_browser) {
															}
		}
		
				define("CLIENT_TYPE", $config['client_type']);
		
		        $config['sys_version'] = sys_version();
        $config['sys_published'] = SYS_PUBLISHED;
        
                if(!$config['mobile_url']) {
       		$config['mobile_url'] = $config['site_url'] . "/mobile";
        }

		        if(!$config['topic_length']) {
            $config['topic_length'] = 140;
        }
        
        		$this->Config = $config;
		
				$this->Config = array_merge($this->Config, Mobile::config());
		
				define("CHARSET", $this->Config['charset']);
		
		Obj::register('config',$this->Config);
		
		if ($_GET) {
			$_GET = array_iconv('utf-8',$config['charset'],$_GET);
					}
		
		if ($_POST) {
			$_POST	= array_iconv('utf-8',$config['charset'],$_POST);
					}
		

				$this->Get     = &$_GET;
		$this->Post    = &$_POST;
		$this->Cookie  = &$_COOKIE;
		$this->Session = &$_SESSION;
		$this->Request = &$_REQUEST;
		$this->Server  = &$_SERVER;
		$this->Files   = &$_FILES;
		$this->Module = trim($this->Post['mod'] ? $this->Post['mod'] : $this->Get['mod']);
		$this->Code   = trim($this->Post['code'] ? $this->Post['code'] : $this->Get['code']);

		

				include_once ROOT_PATH . 'include/lib/template.han.php';
		$TemplateHandler = $this->TemplateHandler=new TemplateHandler($config);
		
		Obj::register('TemplateHandler',$this->TemplateHandler);

				if ($this->Config['ipbanned_enable']) {
			$ipbanned=ConfigHandler::get('access','ipbanned');
			if(!empty($ipbanned) && preg_match("~^({$ipbanned})~",client_ip())) {
								Mobile::show_message(404);
			}
			unset($ipbanned);
		}
		
				
		
				include_once ROOT_PATH . 'include/db/database.db.php';
		include_once ROOT_PATH . 'include/db/mysql.db.php';
		$this->DatabaseHandler = new MySqlHandler($this->Config['db_host'], $this->Config['db_port']);
		$this->DatabaseHandler->Charset($this->Config['charset']);
		$this->DatabaseHandler->doConnect($this->Config['db_user'], $this->Config['db_pass'], $this->Config['db_name'], $this->Config['db_persist']);
		Obj::register('DatabaseHandler',$this->DatabaseHandler);

				include_once ROOT_PATH . 'include/lib/member.han.php';
		$uid = 0;
		$password = '';
		$authcode = '';
		
				$implicit_pass = true;
		if (!empty($this->Get['JSG_SESSION']) && $config['is_mobile_client']) {
									$authcode = $this->Get['JSG_SESSION'];
			$authcode = rawurldecode($authcode);
			$implicit_pass = false;
		} else {
			$authcode = jsg_getcookie('auth');
		}
		
		if (!empty($authcode)) {
			list($password,$uid)=explode("\t",authcode($authcode,'DECODE'));
		}
		
		$this->MemberHandler = new MemberHandler($this->Config);
		$MemberFields = $this->MemberHandler->FetchMember($uid,$password);
		if ($this->MemberHandler->HasPermission($this->Module,$this->Code) == false) {
									Mobile::show_message(411);
			exit;
		}
		
				$this->Title = $this->MemberHandler->CurrentAction['name'];
		Obj::register("MemberHandler", $this->MemberHandler);
		
					$rets = jsg_member_login_extract();
		if($rets) {
			if(MEMBER_ID < 1) {
				$func = $rets['login_direct'];
			} else {
				$func = $rets['logout_direct'];
			}
			if($func && function_exists($func)) {
				$ret = $func();
			}
		}

		if (MEMBER_ID > 0) {
			jsg_member_login_set_status($MemberFields);
		}
		
				if ($this->Config['extcredits_enable']) {
			if(MEMBER_ID>0 && jsg_getcookie('login_credits')+3600<time()) {
				update_credits_by_action('login',MEMBER_ID);
				jsg_setcookie('login_credits',time(),3600);
			}
		}
		
	}
}
?>