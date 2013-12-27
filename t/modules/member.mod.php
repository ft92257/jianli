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

	var $CPLogic;

	
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

				if ($this->Config['company_enable']){
			$this->CPLogic = Load::logic('cp',1);
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
			case 'delete':
				$this->Delete();
				break;

			case 'step1':
				$this->Step1();
				break;
			case 'do_step1':
				$this->DoStep1();
				break;
			case 'step2':
				$this->Step2();
				break;
			case 'do_step2':
				$this->DoStep2();
				break;
			case 'verify':
				$this->Verify();
				break;
			case 'doregister':
				$this->DoRegister();
				break;

		 				case 'setverify':
				$this->DoSetVerify();
				break;
						case 'check_modify_emali':
				$this->DoModifyEmali();
				break;


			default:
				$this->Register();
				break;
		}
		$Contents=ob_get_clean();
		$this->ShowBody($Contents);
	}

	function verify()
	{
		$key=(string) trim($this->Get['key']);
		$uid=(int) $this->Get['uid'];
		if (empty($key)) $this->Messager("验证字符串不能为空",null);
		if(strlen($key)!=16)$this->Messager("验证字符长度不符合标准，请检查。",null);
		$this->DatabaseHandler->SetTable(TABLE_PREFIX.'member_validate');
		$row=$this->DatabaseHandler->Select('',"`key`='{$key}' and `uid`='{$uid}'");
		if ($row==false)$this->messager("验证已过期或者验证信息不符合要求",null);
		if ($row['uid']!=$uid)$this->messager("验证用户ID和你的用户ID不符合，验证失败。",null);
		if($row['status']=='1')$this->Messager('您已经验证过了，不需要重复验证。',null);
		$data=array();
		$data['verify_time']=time();
		$data['status']=1;
		$this->DatabaseHandler->Update($data,"`key`='{$key}' and `uid`='{$uid}'");

				$sql = "delete from `".TABLE_PREFIX."member_validate` where `uid`='{$uid}' and `status`=0";
		$this->DatabaseHandler->Query($sql);


		$data=array();
		$data['role_id']=$row['role_id'];

		$this->DatabaseHandler->SetTable(TABLE_PREFIX.'members');
		$this->DatabaseHandler->Update($data,"uid={$row['uid']}");
		
		$this->Messager("验证成功，您现在可以正常体验本网站了",'index.php?mod=member&code=step1');
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
			$noemail = ConfigHandler::get('sms', 'register_verify', 'noemail');
		}

		
		Load::lib('form');
		$FormHandler = new FormHandler();
				$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."common_district where `upid` = '0' order by list");
		while (false != ($rsdb = $query->GetRow())){
			$province[$rsdb['id']]['value']  = $rsdb['id'];
			$province[$rsdb['id']]['name']  = $rsdb['name'];
		}
		$province = $FormHandler->Select("province",$province,null,"onchange=\"changeProvince();\"");

		if (@is_file(ROOT_PATH . 'include/logic/cp.logic.php') && $this->Config['company_enable']){
			$companyselect = $this->CPLogic->GetOption('companyid','company','—',0,0,0);
			if($this->Config['department_enable']){
				$departmentselect = $this->CPLogic->GetOption('departmentid','department','—',0,0,0);
			}
		}
		
		$email = '';
		$_email = get_param('email');
		if(false != (Load::model('passport')->_is_email($_email))) {
			$email = $_email;
		}
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


		
		if ($this->Config['seccode_register']) {
			$seccode = $this->Post['seccode'];
			if (!ckseccode($seccode)) {
				$this->Messager("验证码输入错误",-1);
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


		
		$password = $this->Post['password'];
		$email = $this->Post['email'];
		$username = $nickname = $this->Post['nickname'];

		
		if(strlen($password) < 5) {
			$this->Messager("密码过短，请设置至少5位",-1);
		}
		if($password != $this->Post['password2']) {
			$this->Messager("两次输入的密码不相同",-1);
		}

		

		$uid = jsg_member_register($nickname, $password, $email);
		if($uid < 1) {
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
		if($this->Post['province']){
			$datas['province'] = $this->DatabaseHandler->ResultFirst("select name from ".TABLE_PREFIX."common_district where id = '".(int) $this->Post['province']."'"); 		}
		if($this->Post['city']){
			$datas['city'] = $this->DatabaseHandler->ResultFirst("select name from ".TABLE_PREFIX."common_district where id = '".(int) $this->Post['city']."'");		}
		if($this->Post['area']){
			$datas['area'] = $this->DatabaseHandler->ResultFirst("select name from ".TABLE_PREFIX."common_district where id = '".(int) $this->Post['area']."'");		}
		if($this->Post['street']){
			$datas['street'] = $this->DatabaseHandler->ResultFirst("select name from ".TABLE_PREFIX."common_district where id = '".(int) $this->Post['street']."'");		}

		if($this->_sms_register()) {
			$datas['phone'] = $sms_bind_num;
		}
				if ($this->Config['company_enable']){
			if($this->Post['companyid']){
				$datas['companyid'] = (int)$this->Post['companyid'];				$datas['company'] = DB::result_first("SELECT name FROM ".DB::table('company')." WHERE id = '".$datas['companyid']."'");
				if($datas['companyid']>0){
					$this->CPLogic->update('company',$datas['companyid'],1,0);
					$this->CPLogic->SetCache('company');
				}
			}
			if($this->Config['department_enable'] && $this->Post['departmentid']){
				$datas['departmentid'] = (int)$this->Post['departmentid'];				if($datas['departmentid']>0){
					$this->CPLogic->update('department',$datas['departmentid'],1,0);
					$this->CPLogic->SetCache('department');
				}
				$datas['department'] = DB::result_first("SELECT name FROM ".DB::table('department')." WHERE id = '".$datas['departmentid']."'");
			}
		}

		$this->DatabaseHandler->SetTable(TABLE_PREFIX.'members');
		$this->DatabaseHandler->Update($datas);


				if($this->_sms_register()) {
			$_sms_info = _sms_client_user($sms_bind_num);

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
		
				if($inviter_member) {
			jsg_member_register_by_invite($inviter_member['uid'], $uid, $check_result);
		}		

		
		$rets = jsg_member_login($uid, $password, 'uid');
		if($rets['uc_syn_html']) {
			$message[] = $rets['uc_syn_html'];
		}

		
		if($this->Config['reg_email_verify']!='1') {
			$redirect_to = 'index.php?mod=member&code=step1';
		} else {
			$redirect_to = 'index.php?mod=member&code=setverify&ids='.$uid;
		}
		
		if($message) {
			$message[] = "您已经注册成功";
		} else {
			$message = null;
		}
		$this->Messager($message, $redirect_to, 0);
	}


		function DoSetVerify()
	{
		$uid = (int) $this->Get['ids'];

		$action = "index.php?mod=member&code=check_modify_emali";


		$this->DatabaseHandler->SetTable(TABLE_PREFIX.'role');
		$role=$this->DatabaseHandler->Select($this->Config['no_verify_email_role_id']);

		$sql = "SELECT `uid`,`ucuid`,`nickname`,`username`,`email` from `".TABLE_PREFIX."members` where `uid` = '{$uid}'  LIMIT 0,1";
		$query = $this->DatabaseHandler->Query($sql);
		$members = $query->GetRow();

				$emali_url = $this->_email_url($members['email']);


		include($this->TemplateHandler->Template('member_verify'));
	}

		function DoModifyEmali()
	{

			$uid = (int) $this->Post['uid'];

						$email = $this->Post['email'];

						$checktype = $this->Post['checktype'];

			if($email)
			{
				if($checktype == 'modify')
				{
					$sql = "SELECT `uid`,`ucuid`,`nickname`,`username`,`email` from `".TABLE_PREFIX."members` where `uid` = '{$uid}'  LIMIT 0,1";
					$query = $this->DatabaseHandler->Query($sql);
					$members = $query->GetRow();

					

					$jsg_result = jsg_member_checkemail($email, $members['ucuid']);

					if($jsg_result < 1)
					{
						$rets = array(
				        	'0' => '【注册失败】有可能是站点关闭了注册功能',
				        	'-4' => 'Email 不合法，请输入正确的Email地址。',
				        	'-5' => 'Email 不允许注册，请尝试更换一个。',
				        	'-6' => 'Email 已经存在了，请尝试更换一个。',
				        );

				        				        echo $rets[$jsg_result];
				        die;
					}
										$sql = "update `".TABLE_PREFIX."members` set  `email`='{$email}' where `uid`='{$uid}'";
					$this->DatabaseHandler->Query($sql);
				}

				
				$sys_config = ConfigHandler::get();

				if($sys_config['reg_email_verify'])
				{
					Load::functions('my');

					my_member_validate($uid,$email,(int) $sys_config['normal_default_role_id']);

					echo "邮件已重新发送成功";
					echo "<script language='Javascript'>";
					echo "parent.document.getElementById('user_email').innerHTML='{$email}';";
					echo "</script>";
				    die;
									}

			}
	}

	function Step1()
	{
		if (MEMBER_ID < 1) {
			$this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",'index.php?mod=login');
		}

				$follow_type = 'recommend';
		$this->ShowConfig = ConfigHandler::get('show');

		$day = 7;
		$time = $day * 86400;
		$limit = (int) $this->ShowConfig['reg_follow']['user'];
		if($limit < 1) $limit = 20;

				
		$TopicLogic = Load::logic('topic', 1);
		$regfollow = ConfigHandler::get('regfollow');
		
				for ($i = 0; $i < count($regfollow); $i++) 
		{
			if($regfollow[$i] == '')
			{
				unset($regfollow[$i]);
			}
		}

		if (!empty($regfollow)) {
			$count = count($regfollow);
			if ($count > $limit) {
				$keys = array_rand($regfollow, $limit);
				foreach ($keys as $k) {
					$uids[] = $regfollow[$k];
				}
			} else {
				$uids = $regfollow;
			}
		} else {
						$cache_id = "misc/RTU-{$day}-{$limit}";
			if (false === ($uids = cache_file('get', $cache_id))) {
				$dateline = time() - $time;
				$sql = "SELECT DISTINCT(uid) AS uid, COUNT(tid) AS topics FROM `".TABLE_PREFIX."topic` WHERE dateline>=$dateline GROUP BY uid ORDER BY topics DESC LIMIT {$limit}";
				$query = $this->DatabaseHandler->Query($sql);
				$uids = array();
				while (false != ($row = $query->GetRow()))
				{
					$uids[$row['uid']] = $row['uid'];
				}

				cache_file('set', $cache_id, $uids, 900);
			}
		}

		if(!$uids) {
			$uids[] = 1;
		}

		$list = array();
		if($uids) {
			$_list = $TopicLogic->GetMember($uids,"`uid`,`ucuid`,`username`,`face_url`,`face`,`province`,`city`,`fans_count`,`topic_count`,`validate`,`validate_category`,`nickname`,`aboutme`");
			foreach ($uids as $uid) {
				if ($uid > 0 && isset($_list[$uid]) && $uid!=MEMBER_ID) {
					$list[$uid] = $_list[$uid];
				}
			}
		}

				$user_count = count($uids);

		$this->Title = "关注热门人物";
		include($this->TemplateHandler->Template('member_step1'));
	}
	function DoStep1()
	{
		if (MEMBER_ID < 1) {
			$this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",'index.php?mod=login');
		}		
		$uid = MEMBER_ID;

				$uids = $this->Post['ids'];
		if(MEMBER_ID > 0 && $uids) {
			$uids = (array) $uids;
			foreach ($uids as $id) {
				$id = (int) $id;
				if($id > 0) {
					buddy_add($id, $uid);
				}
			}
		}

				$tagid = (int) get_param('tag');
		if($tagid)
		{
	    	
		    Load::logic('other');
	    	$OtherLogic = new OtherLogic();
	    	$jsg_result = $OtherLogic->AddFavoriteTag($uid,$tagid);

		}

		$this->Messager(null,$this->Config['site_url'].'/index.php?mod=topic&code=myhome',0);
	}

	function Step2()
	{
		$this->Title = '发布第一条微博';
		include($this->TemplateHandler->Template('member_step2'));
	}
	function DoStep2()
	{
		if (($content = $this->Post['content'])) {
			
			$TopicLogic = Load::logic('topic', 1);
			$return = $TopicLogic->Add($content);

			if ($return['tid'] < 1) {
				$this->Messager(is_string($return) ? $return : "未知错误",'?');
			}
		}

		$this->Messager("注册成功",'index.php?mod=topic&code=myhome',0);
	}

	function _sms_register()
	{
		if(!isset($this->sms_register))
		{
			$this->sms_register = ($this->Config['sms_enable'] && $this->Config['sms_register_verify_enable'] && sms_init($this->Config));
		}

		return $this->sms_register;
	}

	function Delete()
	{
		if(MEMBER_ID < 1 || 'admin' != MEMBER_ROLE_TYPE)
		{
			$this->Messager('您没有权限访问该页面', null);
		}

		$ids = get_param('ids');

		$ids = $ids ? $ids : $this->ID;
		if(!$ids)
		{
			$this->Messager("请指定要删除的用户ID");
		}

		$rets = jsg_member_delete($ids);

		$member_ids_count = $rets['member_ids_count'];
		$admin_list = $rets['admin_list'];


		$msg = '';
		$msg .= "成功删除<b>{$member_ids_count}</b>位会员";
		if($admin_list)
		{
			$msg .= "，其中<b>".implode(' , ',$admin_list)."</b>是管理员，不能直接删除";
		}

		$this->Messager($msg, "?");
	}
	
		function _email_url($email='')
	{
		$url = "";
	
		$email_array = explode("@",$email);
	
		$email_value = $email_array[1];
	
		switch($email_value)
		{
			case "163.com":
				$url = "mail.163.com";
				break;
			case "vip.163.com":
				$url = "vip.163.com/?b08abh1";
				break;
			case "sina.com":
				$url = "mail.sina.com.cn";
				break;
			case "sina.cn":
				$url = "mail.sina.com.cn/cnmail/index.html";
				break;
			case "vip.sina.com":
				$url = "vip.sina.com.cn";
				break;
			case "2008.sina.com":
				$url = "mail.2008.sina.com.cn";
				break;
			case "sohu.com":
				$url = "mail.sohu.com";
				break;
			case "vip.sohu.com":
				$url = "vip.sohu.com";
				break;
			case "tom.com":
				$url = "mail.tom.com";
				break;
			case "vip.sina.com":
				$url = "vip.tom.com";
				break;
			case "sogou.com":
				$url = "mail.sogou.com";
				break;
			case "126.com":
				$url = "www.126.com";
				break;
			case "vip.126.com":
				$url = "vip.126.com/?b09abh1";
				break;
			case "139.com":
				$url = "mail.10086.cn";
				break;
			case "gmail.com":
				$url = "www.google.com/accounts/ServiceLogin?service=mail";
				break;
			case "hotmail.com":
				$url = "www.hotmail.com";
				break;
			case "189.cn":
				$url = "webmail2.189.cn/webmail/";
				break;
			case "qq.com":
				$url = "mail.qq.com/cgi-bin/loginpage";
				break;
			case "yahoo.com":
				$url = "mail.cn.yahoo.com";
				break;
			case "yahoo.cn":
				$url = "mail.cn.yahoo.com";
				break;
			case "yahoo.com.cn":
				$url = "mail.cn.yahoo.com";
				break;
			case "21cn.com":
				$url = "mail.21cn.com";
				break;
			case "eyou.com":
				$url = "www.eyou.com";
				break;
			case "188.com":
				$url = "www.188.com";
				break;
			case "yeah.net":
				$url = "www.yeah.net";
				break;
			case "foxmail.com":
				$url = "mail.qq.com/cgi-bin/loginpage?t=fox_loginpage";
				break;
			case "wo.com.cn":
				$url = "mail.wo.com.cn/smsmail/login.html";
				break;
			case "263.net":
				$url = "www.263.net";
				break;
			case "x263.net":
				$url = "www.263.net";
				break;
			case "263.net.cn":
				$url = "www.263.net";
				break;
			default:
				$url = "";
		}
		if($url)
		{
			return $url;
		}
		else
		{
			return false;
		}
	}
}

?>
