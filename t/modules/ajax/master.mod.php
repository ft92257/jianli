<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename master.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-08-17 19:12:46 370865648 995923068 4395 $
 *******************************************************************/


if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class MasterObject
{
	
	var $Config=array();
	var $Get;
	var $Post;
	var $Cookie;
	var $Session;

	
	var $DatabaseHandler;
	
	var $MemberHandler;

	
	var $TemplateHandler;

	
	var $Title='';

	
	var $Module='index';

	
	var $Code='';
	var $Channels = array();
	var $Channel_enable;

	function MasterObject(&$config)
	{
		$this->Config=$config;
				$_GET = array_iconv('utf-8', $this->Config['charset'], $_GET, 1);
		$_POST = array_iconv('utf-8', $this->Config['charset'], $_POST, 1);

		

		$this->Get     =  &$_GET;

		$this->Post    =  &$_POST;

		$this->Cookie  =  &$_COOKIE;

		$this->Session =  &$_SESSION;

		$this->Request =  &$_REQUEST;

		$this->Server  =  &$_SERVER;

		$this->Files   =  &$_FILES;


		$this->Module = get_param('mod');
		$this->Code   = get_param('code');



				include_once ROOT_PATH . 'include/lib/template.han.php';
		$this->TemplateHandler=new TemplateHandler($config);
		Obj::register('TemplateHandler',$this->TemplateHandler);


				if($this->Config['ipbanned_enable']) {
			$ipbanned=ConfigHandler::get('access','ipbanned');
			if(!empty($ipbanned) && preg_match("~^({$ipbanned})~",client_ip())) {
				exit("您的IP已经被禁止访问。");
			}
			unset($ipbanned);
		}

				$this->Channel_enable = ConfigHandler::get('channel') && ConfigHandler::get('channels') ? true : false;
		$cachefile = ConfigHandler::get('channel');
		$this->Channels = $channel_one = is_array($cachefile['first']) ? $cachefile['first'] : array();
		$channel_two = is_array($cachefile['second']) ? $cachefile['second'] : array();
		foreach($channel_two as $k => $v){
			$this->Channels[$v['parent_id']]['child'][$k] = $v;
		}
		unset($channel_one);unset($channel_two);

		

		

				include_once ROOT_PATH . 'include/db/database.db.php';
		include_once ROOT_PATH . 'include/db/mysql.db.php';
		$this->DatabaseHandler = new MySqlHandler($this->Config['db_host'],$this->Config['db_port']);
		$this->DatabaseHandler->Charset($this->Config['charset']);
		$this->DatabaseHandler->doConnect($this->Config['db_user'],$this->Config['db_pass'],$this->Config['db_name'],$this->Config['db_persist']);
		Obj::register('DatabaseHandler',$this->DatabaseHandler);

	}

	function initMemberHandler() {
		include_once ROOT_PATH . 'include/lib/member.han.php';
		list($password,$uid)=explode("\t",authcode(jsg_getcookie('auth'),'DECODE'));
		$this->MemberHandler = new MemberHandler();
		$member = $this->MemberHandler->FetchMember($uid, $password);
		Obj::register("MemberHandler", $this->MemberHandler);
		return $member;
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
		
		$return .= '<script type="text/javascript">
$(document).ready(function(){
		$("ul.imgList img, div.avatar img.lazyload").lazyload({
		skip_invisible : false,
		threshold : 200,
		effect : "fadeIn"
	});
});
</script>';

		return $return;
	}
}
?>