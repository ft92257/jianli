<?php
/**
 * 文件名：members.mod.php
 * 版本号：1.0
 * 最后修改时间：2006年7月18日 21:00:05
 * 作者：狐狸<foxis@qq.com>
 * 功能描述：用户组操作模块
 */
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	
	var $ID = 0;


	
	var $ImagePath;
	
	var $sms_register = null;


	
	function ModuleObject($config)
	{
		$this->MasterObject($config);


		if(isset($this->Get['id']))
		{
			$this->ID = (int)$this->Get['id'];
		}elseif(isset($this->Post['id']))
		{
			$this->ID = (int)$this->Post['id'];
		}


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
			case 'doregister':
				$this->DoRegister();
				break;

			default:
				$this->Register();
				break;
		}
		$Contents=ob_get_clean();
		$this->ShowBody($Contents);
	}

	
	function Register()
	{
				if(MEMBER_ID != 0 AND false == $this->IsAdmin)
		{
			$this->Messager('您已经是注册用户，无需再注册！', -1);
		}
		
		
				$regstatus = jsg_member_register_check_status();
		if($regstatus['error'])
		{
			$this->Messager($regstatus['error'], null);
		}
		
		
				if($this->Config['ipbanned_enable']) {
			$ipbanned=ConfigHandler::get('access','ipbanned');
			if(!empty($ipbanned) && preg_match("~^({$ipbanned})~",client_ip())) {
				$this->Messager("您的IP已经被禁止访问和注册。",null);
			}
			unset($ipbanned);
		}
		

				$inviter_member = array();
		$action="index.php?mod=member&code=doregister";

		$check_result = jsg_member_register_check_invite($this->Code);			
		
		if($regstatus['invite_enable'] && !$regstatus['normal_enable']) 		{
			if(!$this->Code)
			{
				$this->Messager("非常抱歉，本站目前需要有邀请链接才能注册。" . jsg_member_third_party_reg_msg(), null);
			}
			
			if(!$check_result)
			{
				$this->Messager("对不起，您访问的邀请链接不正确或者因邀请数已满而失效，请重新与邀请人索取链接。", null);
			}
		}
		
		if($check_result['uid'] > 0) 
		{
			$inviter_member = jsg_member_info($check_result['uid']);
		}
		$action .= "&invite_code=" . urlencode($this->Code);
		
		

		if(jsg_getcookie('referer')=='')
		{
			jsg_setcookie('referer',referer());
		}
		
		
		$noemail = 0;
		if($this->_sms_register())
		{
			$sms_bind_num = $this->Post['sms_bind_num'] ? $this->Post['sms_bind_num'] : $this->Get['sms_bind_num'];
			if(!sms_is_phone($sms_bind_num))
			{
				$action = "index.php?mod=member&invite_code=".urlencode($this->Code);
				$this->Title = "请输入您的手机号码";
				include $this->TemplateHandler->Template('member_register_sms');
				return ;
			}
			else
			{
				if(($_user_info = _sms_client_user($sms_bind_num)) && $_user_info['uid'])
				{
					$this->Messager('此手机号已经绑定了其他的帐号');
				}
				
				if($_user_info['bind_key_time'] + 60 > time())
				{
					$this->Messager('60秒内仅发送一次，请稍候再试');
				}
				
				$bind_key = mt_rand(100000,999999);
				
				$sets = array(
					'user_im' => $sms_bind_num,
					'bind_key' => $bind_key,
					'bind_key_time' => time(),
				);
				
				sms_client_user_update($sets, $_user_info);
				
				
				$sms_msg = "您的验证码为 {$bind_key}";
				$sms_msg = array_iconv('UTF-8', $this->Config['charset'], $sms_msg);
				
				
				sms_send($sms_bind_num, $sms_msg, 0);
			}

			$noemail = ConfigHandler::get('sms', 'register_verify', 'noemail');
		}
		
		
		$action="index.php?mod=member&code=doregister&invite_code=".urlencode($this->Code);
		$this->Title="注册新用户";
		include $this->TemplateHandler->Template('member_register');
	}


	
	function DoRegister()
	{		
				if(MEMBER_ID != 0 AND false == $this->IsAdmin)
		{
			$this->Messager('您已经是注册用户，无需再注册！', -1);
		}
		
		
				$regstatus = jsg_member_register_check_status();
		if($regstatus['error'])
		{
			$this->Messager($regstatus['error'], null);
		}
		
		
				if($this->Config['ipbanned_enable']) {
			$ipbanned=ConfigHandler::get('access','ipbanned');
			if(!empty($ipbanned) && preg_match("~^({$ipbanned})~",client_ip())) {
				$this->Messager("您的IP已经被禁止访问和注册。",null);
			}
			unset($ipbanned);
		}

		
		$message = array();
		$timestamp = time();
		
		$noemail = 0;
		$sms_ckret = 0;
		if($this->_sms_register())
		{
						$sms_bind_num = $this->Post['sms_bind_num'];
			$sms_bind_key = $this->Post['sms_bind_key'];
			
			$sms_ckret = sms_check_bind_key($sms_bind_num, $sms_bind_key);
			if($sms_ckret)
			{
				$this->Messager($sms_ckret, -1);
			}

			$noemail = ConfigHandler::get('sms', 'register_verify', 'noemail');
			if($noemail)
			{
				$this->Post['email'] = $sms_bind_num . '@139.com';
			}
		}
				

		
		$inviter_member = array();
		$invite_code = ($this->Post['invite_code'] ? $this->Post['invite_code'] : $this->Get['invite_code']);
		$check_result = jsg_member_register_check_invite($invite_code);
		
		if($regstatus['invite_enable'] && !$regstatus['normal_enable'])
		{
			if(!$invite_code)
			{
				$this->Messager("非常抱歉，本站目前需要有好友邀请链接才能注册。<br><br>看看<a href=\"?mod=topic&code=top\">达人榜</a>中有没有你认识的人，让他给你发一个好友邀请。", null);
			}	
			
			if(!$check_result)
			{
				$this->Messager("对不起，您访问的邀请链接不正确或者因邀请数已满而失效，请重新与邀请人索取链接。", null);
			}
		}
		
		if($check_result['uid'] > 0)
		{
			$inviter_member = jsg_member_info($check_result['uid']);
		}
		if(!$inviter_member && $this->Config['register_invite_input'])
		{
			$inviter_member = jsg_member_info($this->Post['inviter_nickname'], 'nickname');
		}
		
		
		
        $this->Post = array_iconv('UTF-8', $this->Config['charset'], $this->Post);
		$password = $this->Post['password'];
		$email = $this->Post['email'];
		$username = $nickname = $this->Post['nickname'];

		
		if(strlen($password) < 5) 
        {
			$this->Messager("密码过短，请设置至少5位",-1);
		}
        		
		
		
		$uid = jsg_member_register($nickname, $password, $email);
		if($uid < 1)
		{
			$rets = array(
	        	'0' => '【注册失败】有可能是站点关闭了注册功能',
	        	'-1' => '帐户/昵称 不合法，含有不允许注册的字符，请尝试更换一个。',
	        	'-2' => '帐户/昵称 不允许注册，含有被保留的字符，请尝试更换一个。',
	        	'-3' => '帐户/昵称 已经存在了，请尝试更换一个。',
	        	'-4' => 'Email 不合法，请输入正确的Email地址。',
	        	'-5' => 'Email 不允许注册，请尝试更换一个。',
	        	'-6' => 'Email 已经存在了，请尝试更换一个。',
	        );
	        
	        $this->Messager($rets[$uid], null);
		}
		
		
		$datas = array();	
		$datas['uid'] = $uid;	
		$datas['province'] = $this->Post['province']; 		$datas['city'] = $this->Post['city'];		if($this->_sms_register())
		{
			$datas['phone'] = $sms_bind_num;
		}
		
		$this->DatabaseHandler->SetTable(TABLE_PREFIX.'members');
		$this->DatabaseHandler->Update($datas);
		
		
				if($this->_sms_register())
		{
			$_sms_info = _sms_client_user($sms_bind_num);
			
			$username = $username ? $username : $this->Post['username'];
			$_sms_sets = array(
				'uid' => $uid,
				'username' => $username,
				'bind_key' => 0,
	            'bind_key_time' => 0,
	            'try_bind_times' => '+1',
	            'last_try_bind_time' => $timestamp,
			);
			
			sms_client_user_update($_sms_sets, $_sms_info);
		}
		
		
				
		
				$followgroup_ary = ConfigHandler::get('follow');
		if (empty($followgroup_ary)) {
			$followgroup_ary = get_def_follow_group();
		}
		if (!empty($followgroup_ary)) {
			foreach ($followgroup_ary as $value) {
				$insert_ary = array(
					'uid' => $uid,
					'group_name' => $value,
					'group_count' => 0,
				);
				DB::insert("group", $insert_ary);
			}
		}


				if($inviter_member)
		{
			$u = $inviter_member['uid'];
			$c = $check_result['code'];
			
			buddy_add($u, $uid);
			buddy_add($uid, $u);
			
			if(0 < ($invite_id = $check_result['invite_id'])) 
			{
				$row = DB::fetch_first("select * from `".TABLE_PREFIX."invite` where `id`='{$invite_id}'");
				if ($row) 
				{
					DB::query("update `".TABLE_PREFIX."invite` set `fuid`='{$uid}',`fusername`='{$username}' where `id`='{$row['id']}'");
				}
			}
			else 
			{
				DB::query("insert into `".TABLE_PREFIX."invite` (`uid`,`code`,`dateline`,`fuid`,`fusername`,`femail`) values ('{$u}','{$c}','{$timestamp}','{$uid}','{$username}','{$email}')");
			}


						$sql = "update `".TABLE_PREFIX."members` set `invite_count`=`invite_count`+1 where `uid`='{$u}'";
			$this->DatabaseHandler->Query($sql);
			
						$sql = "update `".TABLE_PREFIX."members` set `invite_uid`='{$inviter_member['uid']}' where `uid`='$uid'";
			$this->DatabaseHandler->Query($sql);


						if ($c && $this->Config['invite_limit'] > 0) 
			{
				$code_invite_count = DB::result_first("select count(*) as code_invite_count from `".TABLE_PREFIX."invite` where `uid`='{$u}' and `code`='{$c}'");

				if ($code_invite_count > $this->Config['invite_limit']) 
				{
					jsg_member_register_check_invite($u,1);
				}
			}
			

			if($this->Config['extcredits_enable'] && $u > 0)
			{
				
				update_credits_by_action('register',$u);
			}
		}


		
		$rets = jsg_member_login_set_status($uid);				
		

		
		$this->Messager(null,'index.php');
	}
	
	function _sms_register()
	{
		if(!isset($this->sms_register))
		{
			$this->sms_register = ($this->Config['sms_enable'] && $this->Config['sms_register_verify_enable'] && sms_init($this->Config));		
		}
		
		return $this->sms_register;
	}

}

?>
