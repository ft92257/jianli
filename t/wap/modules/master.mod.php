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
 * @Date 2012-04-23 17:49:35 563917743 542609600 9377 $
 *******************************************************************/


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

	function MasterObject(&$config)
	{
		if(!$config['wap']) {
			include(ROOT_PATH . 'wap/include/error_wap.php');
			exit;
		}
		
		
		$this->Config=$config;

		
		
		require_once ROOT_PATH . 'wap/include/function/wap_global.func.php';

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


				if($this->Config['ipbanned_enable']) {
			$ipbanned=ConfigHandler::get('access','ipbanned');
			if(!empty($ipbanned) && preg_match("~^({$ipbanned})~",client_ip())) {
				$this->Messager("您的IP已经被禁止访问。",null);
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
		if(($authcode=jsg_getcookie('auth'))) {
			list($password,$uid)=explode("\t",authcode($authcode,'DECODE'));
		}
		$this->MemberHandler=new MemberHandler();
		$this->MemberHandler->FetchMember($uid,$password);
		if($this->MemberHandler->HasPermission($this->Module,$this->Code)==false) {
			$member_error = $this->MemberHandler->GetError();
			$member_error = array_iconv($this->Config['charset'],'utf-8',$member_error);
			$this->Messager($member_error,null);
		}
				if($this->MemberHandler->MemberFields['role_id'] == 118){
	    	$this->Messager("您已经被永久禁止访问。",null);
	    }
		$this->Title=$this->MemberHandler->CurrentAction['name'];		Obj::register("MemberHandler", $this->MemberHandler);
		
	}

	
	function Messager($message, $redirectto='',$time = -1,$return_msg=false,$js=null)
	{
		global $rewriteHandler;

		
		if ($time===-1) $time = (is_numeric($this->Config['msg_time'])?$this->Config['msg_time']:5);
		$to_title=($redirectto==='' or $redirectto==-1)?"返回上一页":"跳转到指定页面";
		if($redirectto===null)
		{
			$return_msg=$return_msg===false?"  ":$return_msg;
		}
		else
		{
			$redirectto=($redirectto!=='')?$redirectto:($from_referer=referer());
			if(str_exists($redirectto,'mod=login','code=register','/login','/register'))
			{
				$referer='&referer='.urlencode('index.php?'.'mod=topic&code=new');

				jsg_setcookie('referer','index.php?'.'mod=topic&code=new');
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
					$redirectto .= $referer;
					if(!$from_referer && !$referer) {
						$redirectto=$rewriteHandler->formatURL($redirectto,true);
					}
				}
				if($message===null)
				{
					$redirectto=rawurldecode(jstripslashes(($redirectto)));

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
		
		$this->Title = '操作提示';
		
				$return_Url = $_SERVER['HTTP_REFERER'];
		
		include $this->TemplateHandler->Template('messager');

		exit;
	}	

	function ShowBody($body)
	{
		echo $body;

		if (upsCtrl()->ccDSP()) echo "P"."o"."w"."e"."r"."e"."d"." b"."y"." J"."i"."s"."h"."i"."G"."o"."u";
	}

    function _topicLogicGet($ids,$fields='*',$process='Make',$table="",$prikey='tid') {
        $data = $this->TopicLogic->Get($ids,$fields,$process,$table,$prikey);

        if($data) {
            $data = wap_iconv($data);
        }

        return $data;
    }
    function _topicLogicMake($topic,$actors=array()) {
        $data = $this->TopicLogic->Make($topic,$actors);

        if($data) {
            $data = wap_iconv($data);
        }

        return $data;
    }
    function _topicLogicGetMember($ids,$fields = '*') {
        $data = $this->TopicLogic->GetMember($ids,$fields);

        if($data) {
            $data = wap_iconv($data);
        }

        return $data;
    }
    
        function _longtextLogic($ids) {
        $data = Load::logic('longtext', 1)->get_info($ids, 1);

        if($data) {
            $data = wap_iconv($data);
        }

        return $data;
    }
    
    
    
         function _PmLogic($folder,$page='')
    {
        $data = $this->PmLogic->getPmList($folder,$page);

        if($data)
        {
            $data = wap_iconv($data);
        }

        return $data;
    }
    
        function _GetHistory($uid=0,$touid=0,$page='')
    {
        $data = $this->PmLogic->getHistory($uid,$touid,$page);

        if($data)
        {
            $data = wap_iconv($data);
        }

        return $data;
    }
    
    
	    function _DoPmSend($touser='',$message='')
    {
        $data = $this->PmLogic->pmSend($touser,$message);

        if($data)
        {
            $data = wap_iconv($data);
        }

        return $data;
    }
    
    
     	function _TopicListLogic($param='')
    {
        $data = $this->TopicListLogic->get_recd_list($param);

        if($data)
        {
            $data = wap_iconv($data);
        }

        return $data;
    }
    
    
        function _OtherLogicFavorite($uid=0,$tid=0,$act='')
    {		
    		include_once ROOT_PATH . 'include/logic/other.logic.php';
    		
			$OtherLogic = new OtherLogic();
    		$data = $OtherLogic->TopicFavorite($uid,$tid,$act);
    		
    		if($data)
    		{
    			$data = wap_iconv($data);
    		}
    		
    		return $data;
    }
    
}
?>