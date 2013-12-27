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
 * @Date 2012-06-20 16:52:55 1973938887 1408394844 5481 $
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

	
	var $Position='';

	
	var $Module='index';

	
	var $Code='';

	var $hookall_temp = '';

	function MasterObject(&$config)
	{
		if(!$config['widget_enable']) {
			$msg = 'Widget功能没有启用';
			if(get_param('in_ajax')) {
				widget_error($msg);
			} else {
				exit($msg);
			}
		}
		
		global $TemplateHandler;
		
		$this->Config=$config;
		

				$this->Get     = &$_GET;
		$this->Post    = &$_POST;
		$this->Cookie  = &$_COOKIE;
		$this->Session = &$_SESSION;
		$this->Request = &$_REQUEST;
		$this->Server  = &$_SERVER;
		$this->Files   = &$_FILES;
		$this->Module = get_param('mod');
		$this->Code   = get_param('code');


		
		


				if($this->Config['ipbanned_enable']) {
			$ipbanned=ConfigHandler::get('access','ipbanned');
			if(!empty($ipbanned) && preg_match("~^({$ipbanned})~",client_ip())) {
								widget_error('Ip error', 201);
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
										widget_error('Access Denied', 202);
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
						widget_error($this->MemberHandler->GetError(), 203);
		}
		Obj::register("MemberHandler",$this->MemberHandler);


        		define("FORMHASH",substr(md5(substr(time(), 0, -4).$this->Config['auth_key']),0,16));
		if($_SERVER['REQUEST_METHOD']=="POST") {
			if($this->Post["FORMHASH"]!=FORMHASH) {
							}
		}
	}
	
	    function _page($total, $perpage)
    {
        $return  = array();
        
        $page_count = max(1,ceil($total / $perpage));
        if($this->Config['total_page_default'] > 1 && $page_count > $this->Config['total_page_default'])
        {
            $page_count = $this->Config['total_page_default'];
        }
        
        $page = max(1,min($page_count, (int) $this->Get['page']));        
        $page_next = min($page + 1,$page_count);
        $page_previous = max(1,$page - 1);
        
        $offset = max(0, (int) (($page - 1) * $perpage));
        
        $return = array(
            'total' => $total,
            'perpage' => $perpage,
            'page_count' => $page_count,
            'page' => $page,
            'page_next' => $page_next,
            'page_previous' => $page_previous,
            'offset' => $offset,
            'limit' => $perpage,
        );
        
        return $return;
    }
	
}


function widget_output($result,$status='',$code=0)
{
	$outputs = array();
	if($status) {
		$outputs['status'] = $status;
        $outputs[$status] = true;
	}
    if($code) {
    	$outputs['code'] = $code;
    }
    
    $outputs['result'] = $result;
    
	$outputs = array_iconv($GLOBALS['_J']['charset'], 'utf-8', $outputs);

	ob_clean();
	echo json_encode($outputs);
}


function widget_error($msg,$code=0,$halt=true)
{
	widget_output($msg,'error',$code);
	$halt && exit;
}
?>