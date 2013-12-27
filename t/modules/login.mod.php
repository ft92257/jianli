<?php
/**
 * 文件名：login.mod.php
 * 版本号：(1.0)
 * 最后修改时间：2006年8月22日 18:58:20
 * 作者：狐狸<foxis@qq.com>
 * 功能描述：用户登录
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{


	
	var $Code = false;

	
	var $Username = '';

	
	var $Password = '';

	
	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->Username = isset($this->Post['username'])?trim($this->Post['username']):"";
		$this->Password = isset($this->Post['password'])?trim($this->Post['password']):"";

		if(MEMBER_ID > 0) {
			$this->IsAdmin = $this->MemberHandler->HasPermission('member','admin');
		}
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'dologin':
				$this->DoLogin();
				break;
			case 'logout':
				$this->LogOut();
				break;
			default:
				$this->login();
				break;
		}
		$body=ob_get_clean();

		$this->ShowBody($body);
	}
	
	function login() {
		if(MEMBER_ID != 0 AND false == $this->IsAdmin) {
			$this->Messager("您已经使用 ". MEMBER_NICKNAME ." 登录系统，无需再次登录！", null);
		}
		
		$loginperm = $this->_logincheck();
		if(!$loginperm) {
			$this->Messager("累计 5 次错误尝试，15 分钟内您将不能登录。", null);
		}

		$this->Title="用户登录";

		if (jsg_getcookie("referer")=="") {
			jsg_setcookie("referer", referer());
		}

		$action="index.php?mod=login&code=dologin";

		include($this->TemplateHandler->Template("global_login"));
	}


	
	function DoLogin()
	{

		
		if ($this->Config['seccode_login']) {
			$seccode = $this->Post['seccode'];
			if (!ckseccode($seccode)) {
				$this->Messager("验证码输入错误",-1);
			}
		}


        if($this->Username=="" || $this->Password=="")
		{
			$this->Messager("无法登录,用户名或密码不能为空", -1);
		}


		
		$username = $this->Username;
		$password = $this->Password;


		
		$referer=jsg_getcookie('referer');

		$rets = jsg_member_login($username, $password);
        $uid = (int) $rets['uid'];
        if($uid < 1) {
        	$this->Messager($rets['error'], null);
        }

        
		if($this->Config['reg_email_verify'] == '1')
		{
			

			
		 	$member_info = DB::fetch_first("select `uid`,`username` from ".DB::table('members')." where `uid`='$uid' limit 0,1");

		    if($member_info)
		    {
		     	$member_validate = DB::fetch_first("select `uid`,`status` from ".DB::table('member_validate')." where `uid`='{$uid}' ");
		    }

		    if($member_validate)
		    {
		    	if($member_validate['status'] != '1')
		    	{
		    		$this->Messager(null,'index.php?mod=member&code=setverify&ids='.$member_info['uid'],0);
		    	}
		    }
		}

		if($this->Config['extcredits_enable'] && $uid > 0)
		{
			
			update_credits_by_action('login',$uid);
		}

		
		Load::logic('other');
		$otherLogic = new OtherLogic();
		$sql = "SELECT m.id as medal_id,m.medal_img,m.medal_name,m.medal_depict,m.conditions,u.dateline,y.apply_id
				FROM ".TABLE_PREFIX."medal m
				LEFT JOIN ".TABLE_PREFIX."user_medal u ON (u.medalid = m.id AND u.uid = '$uid')
				LEFT JOIN ".TABLE_PREFIX."medal_apply y ON (y.medal_id = m.id AND y.uid = '$uid')
				WHERE m.is_open = 1
				ORDER BY u.dateline DESC,m.id";

		$query = $this->DatabaseHandler->Query($sql);
		while (false != ($rs = $query->GetRow())){
			$rs['conditions'] = unserialize($rs['conditions']);
			if(in_array($rs['conditions']['type'],array('topic','reply','tag','invite','fans')) && !$rs['dateline']){
				$result .= $otherLogic->autoCheckMedal($rs['medal_id'],$uid);
			}
		}


		
		$redirecto=($referer?$referer:referer());
		if(strpos($redirecto,'login')!==false)
        {
            $redirecto = "index.php?mod=topic&code=myhome" ;
        }

				if($this->Post['loginType'] == 'share')
		{
			$redirecto = $this->Post['return_url'];
			$this->Messager(null,$redirecto,0);
		}

				if($this->Post['loginType'] == 'show_login')
		{
			$this->Messager(NULL,$redirecto,0);
		}

		if($rets['uc_syn_html'])
        {
            $this->Messager("登录成功{$rets['uc_syn_html']}",$redirecto,3);
        }
        else
        {
            $this->Messager(null,$redirecto);
        }
	}


	
	function LogOut()
	{		
		$msg = null;
		$time = 0;
		$to = '?';
		
		
		$rets = jsg_member_logout();
		if($rets['uc_syn_html']) {
			$msg = "退出成功{$rets['uc_syn_html']}";
			$time = 3;
		}
		
		
		$rets = jsg_member_login_extract();
		if($rets && $rets['logout_url']) {
			$to = $rets['logout_url'];
		}
		

		$this->Messager($msg,$to,$time);
	}

	function _logincheck() {
		$onlineip= client_ip();
		$timestamp=time();
		$query = $this->DatabaseHandler->Query("SELECT count, lastupdate FROM ".TABLE_PREFIX.'failedlogins'." WHERE ip='$onlineip'");
		if(false != ($login = $query->GetRow())) {
			if($timestamp - $login['lastupdate'] > 900) {
				return 3;
			} elseif($login['count'] < 5) {
				return 2;
			} else {
				return 0;
			}
		} else {
			return 1;
		}
	}

}

?>