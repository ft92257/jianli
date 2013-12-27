<?php
/**
 *
 * 通行证类（包含注册、登录、退出等操作）
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id$
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class passport {

	var $table = 'members';

	function passport() {
		if(true === UCENTER) {
			include_once ROOT_PATH . 'api/uc_client/client.php';
		}
	}

	function register($nickname, $password, $email, $username = '', $ucuid = 0, $role_id = 0) {
		
		$rets = jsg_member_register_check_status();
		if($rets['error']) {
			return 0;
		}

		
		$nickname = trim(strip_tags($nickname));
		$jsg_result = $this->checkname($nickname, 1, $ucuid);
		if($jsg_result < 1) {
			return $jsg_result;
		}

		
		$username = trim(strip_tags($username));
		if($username) {
			$jsg_result = $this->checkname($username, 0, $ucuid);
			if($jsg_result < 1) {
				return $jsg_result;
			}
		}

		
		$jsg_result = $this->checkemail($email, $ucuid);
		if($jsg_result < 1) {
			return $jsg_result;
		}

		
		if(true === UCENTER && $ucuid < 1) {
			$uc_result = uc_user_register($nickname, $password, $email);
			if($uc_result < 1) {
				return $uc_result;
			}
			$ucuid = $uc_result;
		}

		
		elseif(true === PWUCENTER && $ucuid < 1)
		{
			define('P_W','admincp');
			include_once(ROOT_PATH.'api/pw_api/security.php');
			include_once(ROOT_PATH.'api/pw_api/pw_common.php');
			
			include_once(ROOT_PATH.'./api/pw_client/uc_client.php');

			$uc_result = uc_user_register($nickname, md5($password), $email);
			if($uc_result < 1)
			{
				return $uc_result;
			}
			$ucuid = $uc_result;
		}


		
		$timestamp = time();
		$sql_datas = array();
		$sql_datas['ucuid'] 	= $ucuid;
		$sql_datas['password']	= md5($password);
		$sql_datas['nickname']	= mysql_escape_string($nickname);
		$sql_datas['username']  = ($username ? mysql_escape_string($username) : '');
		$sql_datas['email'] 	= mysql_escape_string($email);
		$sql_datas['role_type']	= 'normal';
		$sql_datas['role_id'] 	= (int) ($GLOBALS['_J']['config']['reg_email_verify'] ? $GLOBALS['_J']['config']['no_verify_email_role_id'] : $GLOBALS['_J']['config']['normal_default_role_id']);
		$sql_datas['invitecode']= substr(md5(random(32)),-16);
		$sql_datas['regdate']	= $sql_datas['lastactivity'] = $timestamp;
		$sql_datas['regip']		= $sql_datas['lastip'] = client_ip();

				if ($GLOBALS['_J']['config']['extcredits_enable'])
		{
			$credits = ConfigHandler::get('credits');
			foreach ($credits['ext'] as $_k=>$_v)
			{
				if ($_v['enable'] && $_v['default'])
				{
					$sql_datas[$_k] = (int) $_v['default'];
				}
			}
		}

		
		DB::query("insert into `" . TABLE_PREFIX . "members` (`" . implode("`,`", array_keys($sql_datas)) . "`) values ('".implode("','",$sql_datas)."')");
		$uid = (int) DB::insert_id();
		if($uid < 1) {
			return 0;
		}
				if(!$username) {
						DB::query("UPDATE `".TABLE_PREFIX."members` SET `username`=`uid` WHERE `username`=''");
		}
				DB::query("insert into `".TABLE_PREFIX."memberfields` (`uid`) values ('$uid')");

				if($GLOBALS['_J']['config']['reg_email_verify']) {
			Load::functions('my');
			my_member_validate($uid,$sql_datas['email'],(int) $GLOBALS['_J']['config']['normal_default_role_id']);
		}
		
				$ruids = ConfigHandler::get('default_regfollow');
		if($ruids) {
			$ruids = (array) $ruids;
			foreach($ruids as $v) {
				$v = (int) $v;
				if($v > 0) {
					buddy_add($v, $uid);
				}
			}
		}
		
		$pmLogic = Load::logic('pm',1);
				if(($sendmsgname = $GLOBALS['_J']['config']['notice_to_new_user']) && $GLOBALS['_J']['config']['notice_to_new_user_news']) {			
			$pm_post = array(
				'message' => $GLOBALS['_J']['config']['notice_to_new_user_news'],
				'to_user' => $nickname,
			);
						$admin_info = DB::fetch_first("select `uid`,`username`,`nickname` from `".TABLE_PREFIX."members` where `nickname` = '$sendmsgname'");
			if($admin_info){
				$pmLogic->pmSend($pm_post,$admin_info['uid'],$admin_info['username'],$admin_info['nickname']);
			}
		}

				if(trim($sql_datas['role_id']) == 5){
						$first_admin = DB::fetch_first("select `uid`,`username`,`nickname` from `".TABLE_PREFIX."members` where `uid` = 1");
			$pm_post_touser = array(
				'message' => ($GLOBALS['_J']['config']['notice_to_waitvalidate_user'] ? $GLOBALS['_J']['config']['notice_to_waitvalidate_user'] : "新注册帐号的角色为'待验证会员'，您只能浏览该站点，不能进行活动"),
				'to_user' => $sql_datas['nickname'],
			);
			$return  = $pmLogic->pmSend($pm_post_touser,$first_admin['uid'],$first_admin['username'],$first_admin['nickname']);
			
			if($sendmsgname = $GLOBALS['_J']['config']['notice_to_admin']){
								$pm_post_toadmin = array(
					'message' => "有新注册用户进入待验证会员组，<a href='admin.php?mod=member&code=waitvalidate' target='_blank'>点击进入审核</a>。",
					'to_user' =>  str_replace('|',',',$sendmsgname),
				);
				$pmLogic->pmSend($pm_post_toadmin,$first_admin['uid'],$first_admin['username'],$first_admin['nickname']);
			}
		}
		return $uid;
	}

	function login($nickname, $password, $is = '') {
		$reg_rets = array(
			'0' => '【注册失败】有可能是站点关闭了注册功能',
			'-1' => '帐户/昵称 不合法，含有不允许注册的字符，请尝试更换一个。',
			'-2' => '帐户/昵称 不允许注册，含有被保留的字符，请尝试更换一个。',
			'-3' => '帐户/昵称 已经存在了，请尝试更换一个。',
			'-4' => 'Email 不合法，请输入正确的Email地址。',
			'-5' => 'Email 不允许注册，请尝试更换一个。',
			'-6' => 'Email 已经存在了，请尝试更换一个。',
		);

		$login_rets = array(
			'0' => '登录失败，请联系站点管理员。',
	        '-1' => '帐户/昵称不存在，您可以有至多 5 次尝试。',
	        '-2' => '帐户/昵称或密码错误，您可以有至多 5 次尝试。<br />如果您遗忘了登录密码，请<a href="index.php?mod=get_password">点此找回密码</a>，或者<a href="javascript:history.go(-1)">点此返回重新登录</a>',
	        '-3' => '累计 5 次错误尝试，15 分钟内您将不能登录，请稍后再尝试。'
	        );

	        
	        $uc_syn_html = '';
	        if(true === UCENTER)
	        {
	        	
	        	$member = jsg_get_member($nickname, $is, 0);
	        	$_uid = 0;
	        	if($member)
	        	{
	        		$_member = $this->login_check($nickname, $password, $is);
	        		$_uid = $_member['uid'];
	        		if(-3==$_uid) {
	        			return array('uid' => -3, 'error' => $login_rets[$_uid]);
	        		}
	        		$nickname = $member['nickname'];
	        		$is = 'nickname';
	        	}

	        	
	        	if($member['ucuid'] < 1) {
	        		list($uc_uid, $uc_nickname, $uc_password, $uc_email) = uc_user_login($nickname, $password);
	        	} else {
	        		list($uc_uid, $uc_nickname, $uc_password, $uc_email) = uc_user_login($member['ucuid'], $password, 1);
	        	}

	        	if($uc_uid > 0 && $_uid < 1) 	        	{
	        		if(!$member) 	        		{
	        			$_new_uid = $this->register($uc_nickname, $password, $uc_email, '', $uc_uid);
	        			if($_new_uid < 1) 	        			{
	        					        				if($_new_uid < -3) {
	        					$_new_uid = $this->register($uc_nickname, $password, abs(crc32($uc_nickname)) . '@' . abs(crc32($password)) . '.com', '', $uc_uid);
	        				}

	        				$error = "UC用户注册到本地失败： " . $reg_rets[$_new_uid];
	        				return array('uid' => ($_new_uid - 10), 'error' => $error);
	        			}
	        		}
	        		else 	        		{
	        			$this->edit($member['nickname'], '', '', $password, '', '', 1);
	        		}
	        	}
	        	elseif($uc_uid < 1 && $_uid > 0) 	        	{
	        		if(-1 == $uc_uid) 	        		{
	        			$uc_uid = uc_user_register($member['nickname'], $password, $member['email']);
	        			if($uc_uid < 1) 	        			{
	        					        				if($uc_uid < -3) {
	        					$uc_uid = uc_user_register($member['nickname'], $password, abs(crc32($member['nickname'])) . '@' . abs(crc32($password)) . '.com');
	        				}

	        				$error = "本地用户注册到UC失败： " . $reg_rets[$uc_uid];
	        				return array('uid' => ($uc_uid - 100), 'error' => $error);
	        			}
	        		}
	        	}

	        	if($uc_uid < 1) 	        	{
	        		$error = "在UC中登录失败： " . $login_rets[$uc_uid];
	        		return array('uid' => $uc_uid, 'error' => $error);
	        	}

	        	if($member['uid'] > 0 && $uc_uid != $member['ucuid']) 	        	{
	        		DB::query("update `".TABLE_PREFIX."members` set `ucuid`='$uc_uid' where `uid`='{$member['uid']}'");
	        	}

	        	$uc_syn_html = uc_user_synlogin($uc_uid); 	        }

	        
	        elseif(true === PWUCENTER)
	        {
	        	
	        	define('P_W','admincp');
	        	include_once(ROOT_PATH.'api/pw_api/security.php');
	        	include_once(ROOT_PATH.'api/pw_api/pw_common.php');
	        	
	        	include_once(ROOT_PATH.'./api/pw_client/uc_client.php');

	        	
	        	$member = jsg_get_member($nickname, $is, 0);
	        	$_uid = 0;
	        	if($member)
	        	{
	        		$_member = $this->login_check($nickname, $password, $is);
	        		$_uid = $_member['uid'];
	        		if(-3==$_uid) {
	        			return array('uid' => -3, 'error' => $login_rets[$_uid]);
	        		}
	        		$nickname = $member['nickname'];
	        		$is = 'nickname';
	        	}

	        	
	        	$user_login = uc_user_login($nickname, md5($password));
	        	$uc_uid = $user_login['uid'];

	        	if($uc_uid > 0 && $_uid < 1 && $user_login['status'] == 1) 	        	{
	        		if(!$member) 	        		{
	        			$_new_uid = $this->register($user_login['username'], $password, $user_login['email'], '', $uc_uid);
	        			$is = 'nickname';
	        		}
	        		else 	        		{
	        			DB::query("update `".TABLE_PREFIX."members` set `password`='".md5($password)."' where `uid`='{$member['uid']}'");
	        		}
	        	}
	        	elseif($user_login['status'] < 1 && $_uid > 0) 	        	{
	        		if(-1 == $user_login['status']) 	        		{
	        			$uc_uid = uc_user_register($member['nickname'], md5($password), $member['email']);
	        		}
	        	}
	        	if($member['uid'] > 0 && $uc_uid != $member['ucuid']) 	        	{
	        		DB::query("update `".TABLE_PREFIX."members` set `ucuid`='$uc_uid' where `uid`='{$member['uid']}'");
	        	}

	        	$uc_syn_html =  $user_login['synlogin']; 	        }

	        
	        $member = $this->login_check($nickname, $password, $is);

	        $_uid = $member['uid'];
	        if($_uid < 1)
	        {
	        	$error = '登录失败： ' . $login_rets[$_uid];
	        	return array('uid' => $_uid, 'error'=>$error);
	        }
	        else
	        {
	        	$member['uc_syn_html'] = $uc_syn_html;

	        	
	        	$timestamp=time();
	        	$last_ip=client_ip();
	        	$sql="
		UPDATE
			".TABLE_PREFIX.'members'."
		SET
			`lastactivity`='{$timestamp}',
			`lastip`='{$last_ip}'
		WHERE
			uid={$_uid}";
	        	DB::query($sql);
	        }

	        
	        $member = $this->login_set_status($member);


	        return $member;
	}

	function logout() {
		$rets = array();

		$prefix_length = strlen($GLOBALS['_J']['config']['cookie_prefix']);
		foreach($_COOKIE as $k=>$v) {
			$k = substr($k, $prefix_length);
			jsg_setcookie($k, '', -86400000);
			$_COOKIE[$k] = null;
			unset($_COOKIE[$k]);
		}

		$MemberHandler = & Obj::registry('MemberHandler');
		if($MemberHandler) {
			$MemberHandler->SessionExists = false;
			$MemberHandler->MemberFields = array();
		}

		$uc_syn_html = '';
		if (true === UCENTER) {
			$uc_syn_html .= uc_user_synlogout();
					}
		if (true === PWUCENTER) {
						define('P_W','admincp');
			include_once(ROOT_PATH.'api/pw_api/security.php');
			include_once(ROOT_PATH.'api/pw_api/pw_common.php');
			
			include_once(ROOT_PATH.'./api/pw_client/uc_client.php');

			$uc_syn_html .= uc_user_synlogout();

					}
		if($uc_syn_html) {
			$rets['uc_syn_html'] = $uc_syn_html;
		}

		return $rets;
	}

	function login_check($nickname, $password, $is = '', $checkip = 1) {
		
		if($checkip)
		{
			$ip = client_ip();
			$timestamp = time();

			$failed = DB::fetch_first("SELECT * FROM ".TABLE_PREFIX.'failedlogins'." WHERE ip='{$ip}'");
			if($failed)
			{
				if($failed['lastupdate'] + 900 > $timestamp)
				{
					if($failed['count'] > 5)
					{
						return array('uid'=>-3);
					}
				}
				else
				{
					DB::query("UPDATE ".TABLE_PREFIX.'failedlogins'." SET count='1', lastupdate='{$timestamp}' WHERE ip='{$ip}'");
					DB::query("DELETE FROM ".TABLE_PREFIX.'failedlogins'." WHERE lastupdate<{$timestamp}-901", 'UNBUFFERED');
				}
			}
		}

		
		$member = jsg_get_member($nickname, $is, 0);

		$rets = array();

		
		if(!$member || $member['uid'] < 1)
		{
			$rets = array('uid'=>-1);
		}
		else
		{
			
			if(md5($password) != $member['password'])
			{
				$rets = array('uid'=>-2);
			}
		}

		if($rets)
		{
			if($checkip && $failed)
			{
				DB::query("UPDATE ".TABLE_PREFIX.'failedlogins'." SET count=count+1, lastupdate='$timestamp' WHERE ip='$ip'");
			}
			else
			{
				DB::query("REPLACE INTO ".TABLE_PREFIX.'failedlogins'." (ip, count, lastupdate) VALUES ('$ip', '1', '$timestamp')");
			}

			return $rets;
		}


		return $member;
	}

	function login_set_status($member) {
		if(is_numeric($member)) {
			$member = DB::fetch_first("select * from ".DB::table('members')." where `uid`='$member'");
		}

		if(!$member) {
			return array();
		}

		

		jsg_setcookie('sid', '', -86400000);
		jsg_setcookie('referer', '', -86400000);
		jsg_setcookie('auth', authcode("{$member['password']}\t{$member['uid']}", 'ENCODE', '', 2592000), 86400000);
		
		return $member;
	}

	function login_extract() {
		$rets = array();

		if($GLOBALS['_J']['config']['jsg_member_login_extract']) {
			$conf = $GLOBALS['_J']['config']['jsg_member_login_extract'];

			$rets = ConfigHandler::get($conf);
			if($rets['load_functions']) {
				Load::functions($rets['load_functions']);
			}
		}

		return $rets;
	}

	function checkname($username, $is_nickname = 0, $ucuid = 0, $check_exists = 1) {
		$username = trim(strip_tags($username));

		
		$username_len = jstrlen($username);
		if($username_len < 3 || $username_len > 15)
		{
			return -1;
		}

				if($ucuid < 1)
		{
			
			if($is_nickname)
			{
												if(false != preg_match('~[\<\>\?\@\$\#\[\]\{\}]+~',$username))
				{
					return -1;
				}
							}
			else
			{
								if(is_numeric($username) || (false == preg_match('~^[\w\d\_]+$~',$username)))
				{
					return -1;
				}
			}
		}

		
		$f_rets = filter($username);
		if($f_rets && $f_rets['error'])
		{
			return -2;
		}

		
		$censoruser = ConfigHandler::get('user','forbid');
		$censoruser .= "|guest|topic|index|admin|ajax|mobile|login|member|profile|tag|get_password|report|weather|master|setting|wap|include|cache|data|api|error_log|iis_rewrite|images|install|modules|templates|uc_client|backup|imjiqiren|sms|qqwb|url|wall|qun|vote|account|yy|renren|douban|kaixin|baidu|output|event|fenlei|class|verify";
		$censorexp = '/^('.trim(str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(trim($censoruser), '/')),'| ').')$/i';
		if($censoruser && preg_match($censorexp, $username))
		{
			return -2;
		}

		
		if(true === UCENTER && $ucuid < 1)
		{
				

			$uc_result = uc_user_checkname($username);
			if($uc_result < 1)
			{
				return $uc_result;
			}
		}

		
		if(true === PWUCENTER && $ucuid < 1)
		{
			
			define('P_W','admincp');
			include_once(ROOT_PATH.'api/pw_api/security.php');
			include_once(ROOT_PATH.'api/pw_api/pw_common.php');
			
			include_once(ROOT_PATH.'./api/pw_client/uc_client.php');

			$uc_result = uc_check_username($username);
			if($uc_result < 1)
			{
				return $uc_result;
			}
		}

		
		if($check_exists) {
			$username = addslashes($username);
			$row = DB::fetch_first("select `uid` from `" . TABLE_PREFIX . "members` where `username`='$username' or `nickname`='$username' limit 1");
			if($row) {
				return -3;
			}
		}

		
		return 1;
	}

	function checkemail($email, $ucuid = 0) {
		$email = trim(strip_tags($email));

		
		$email_len = strlen($email);
		if($email_len < 6 || $email_len > 50)
		{
			return -4;
		}
		if(false == $this->_is_email($email))
		{
			return -4;
		}



		
		if($GLOBALS['_J']['config']['reg_email_forbid'])
		{
			$email_host = strstr($email,'@');
			if (false !== stristr($GLOBALS['_J']['config']['reg_email_forbid'],$email_host))
			{
				return -5;
			}
		}

		
				if(1)
		{
			$email = addslashes($email);
			$row = DB::fetch_first("select `uid` from `" . TABLE_PREFIX . "members` where `email`='$email' limit 1");
			if($row)
			{
				return -6;
			}
		}

		
		if(true === UCENTER && $ucuid < 1)
		{
				

			$uc_result = uc_user_checkemail($email);
			if($uc_result < 1)
			{
				return $uc_result;
			}
		}

		
		if(true === PWUCENTER && $ucuid < 1)
		{
			
			define('P_W','admincp');
			include_once(ROOT_PATH.'api/pw_api/security.php');
			include_once(ROOT_PATH.'api/pw_api/pw_common.php');
			
			include_once(ROOT_PATH.'./api/pw_client/uc_client.php');

			$uc_result = uc_check_email($email);
			if($uc_result < 1)
			{
				return $uc_result;
			}
		}

		
		return 1;
	}

	function delete($ids) {
		$ids = (array) $ids;

		$admin_list = array();
		$member_ids = array();

		$query = DB::query("select * from ".DB::table('members')." where `uid` in ('".implode("','", $ids)."')");
		while(false != ($row = DB::fetch($query))) {
			$uid = $row['uid'];

			if(jsg_member_is_founder($uid) || 'admin' == $row['role_type']) {
				$admin_list[$uid] = $row['nickname'];
			} else {
				$member_ids[$uid] = $uid;

								if(true === UCENTER && $row['ucuid'] > 0) {
					uc_user_delete($row['ucuid']);
				}
			}
						if ($GLOBALS['_J']['config']['company_enable']){
				$CPLogic = Load::logic('cp',1);
				if($row['companyid']>0){
					$CPLogic->update('company',$row['companyid'],-1,0);
				}
				if($GLOBALS['_J']['config']['department_enable'] && $row['departmentid']>0){
					$CPLogic->update('department',$row['departmentid'],-1,0);
				}
			}
		}

		$member_ids_count = count($member_ids);
		if($member_ids_count > 0)
		{
			$member_ids_in = "'".implode("','", $member_ids)."'";

						Load::model('buddy')->del(array('uid'=>$member_ids));
			Load::model('buddy')->del(array('buddyid'=>$member_ids));


			
			$TopicLogic = Load::logic('topic', 1);

			$TopicLogic->DeleteToBox("where `uid` in({$member_ids_in}) limit 999999999 ");
			$TopicLogic->Delete("where `uid` in({$member_ids_in}) limit 999999999 ");

			$tbs = array(
				'blacklist' => array('uid', 'touid'),
				'credits_log' => 'uid',
				'credits_rule_log' => 'uid',
				'cron' => 'touid',
				'event' => 'postman',
				'event_favorite' => 'uid',
				'event_member' => 'fid',
												'group' => 'uid',
				'groupfields' => 'uid',
				'imjiqiren_client_user' => 'uid',
				'invite' => array('uid', 'fuid'),
				'kaixin_bind_info' => 'uid',
				'log' => 'uid',
				'medal_apply' => 'uid',
				'member_validate' => 'uid',
				'my_tag' => 'user_id',
				'my_topic_tag' => 'user_id',
				'plugin_company' => 'uid',
				'pms' => array('msgfromid', 'msgtoid'),
				'qqwb_bind_info' => 'uid',
				'qun' => 'founderuid',
								'qun_apply' => 'uid',
				'qun_user' => 'uid',
				'renren_bind_info' => 'uid',
				'report' => 'uid',
				'schedule' => 'uid',
				'sessions' => 'uid',
				'sms_client_user' => 'uid',
				'sms_receive_log' => 'uid',
				'sms_send_log' => 'uid',
				'tag_favorite' => 'uid',
				'task_log' => 'uid',
				'topic' => 'uid',
				'topic_favorite' => 'uid',
				'topic_image' => 'uid',
				'topic_attach' => 'uid',
				'topic_longtext' => 'uid',
				'topic_mention' => 'uid',
				'topic_music' => 'uid',
				'topic_show' => 'uid',
				'topic_video' => 'uid',
				'user_medal' => 'uid',
				'user_tag_fields' => 'uid',
				'vote' => 'uid',
				'vote_user' => 'uid',
				'wall' => 'uid',
				'xwb_bind_info' => 'uid',
				'yy_bind_info' => 'uid',
				'memberfields' => 'uid',
				'members' => 'uid', 			);
			foreach($tbs as $k=>$vs) {
				$vs = (array) $vs;

				foreach($vs as $v) {
					DB::query("delete from `".TABLE_PREFIX."{$k}` where `{$v}` in ({$member_ids_in})", "SKIP_ERROR");
				}
			}
		}


		$rets = array(
			'admin_list' => $admin_list,
			'member_ids' => $member_ids,
			'member_ids_count' => $member_ids_count,
		);

		return $rets;
	}

	function edit($oldnickname, $oldpw='', $nickname='', $password='', $email='', $username='', $ignoreoldpw=0) {
		$oldmember = array();

		if(!$ignoreoldpw)
		{
			$rets = $this->login_check($oldnickname, $oldpw);
			if($rets['uid'] < 1)
			{
				return ($rets['uid'] - 10);
			}
			else
			{
				$oldmember = $rets;
			}
		}
		else
		{
			$oldmember = jsg_get_member($oldnickname, 'nickname', 0);
		}

		$uc_password = $uc_email = '';

		$newmember = array();

				if($nickname && $nickname!=$oldmember['nickname'] && (true!==UCENTER || true===UCENTER_MODIFY_NICKNAME || $ignoreoldpw))
		{
			$ret = $this->checkname($nickname, 1);
			if($ret < 1)
			{
				return $ret;
			}

			$newmember['nickname'] = $nickname;
		}
		if($password)
		{
			$password_hash = md5($password);
			if($password_hash!=$oldmember['password'])
			{
				$newmember['password'] = $password_hash;
				
				$uc_password = $password;
			}
		}
				if($username && $username!=$oldmember['username'] && (!$oldmember['username'] || is_numeric($oldmember['username']) || $ignoreoldpw))
		{
			$ret = $this->checkname($username);
			if($ret < 1)
			{
				return $ret;
			}

			$newmember['username'] = $username;
		}
		if($email && $email!=$oldmember['email'])
		{
			$ret = $this->checkemail($email);
			if($ret < 1)
			{
				return $ret;
			}

			


			if($GLOBALS['_J']['config']['reg_email_verify'])
			{
				$newmember['role_id'] = $GLOBALS['_J']['config']['no_verify_email_role_id'];

								Load::functions('my');
				my_member_validate($oldmember['uid'],$email,(int) ($oldmember['role_id']!=$GLOBALS['_J']['config']['no_verify_email_role_id'] ? $oldmember['role_id'] : $GLOBALS['_J']['config']['normal_default_role_id']));
			}

			$newmember['email'] = $email;
			
			$uc_email = $email;
		}

		if(!$newmember)
		{
			return -7;
		}

		if(true===UCENTER && $oldmember['ucuid'] > 0)
		{
			$ret = uc_user_edit($oldnickname, $oldpw, $uc_password, $uc_email, $ignoreoldpw);
			if($ret < 0 && -7 != $ret)
			{
				return $ret;
			}
		}

		$ret = DB::update('members', $newmember, array('uid' => $oldmember['uid']));

		return $ret;
	}

	function register_check_invite($invite_code='', $reset=0) {
		$invite_code = $invite_code ? $invite_code : ($_POST['invite_code'] ? $_POST['invite_code'] : $_GET['invite_code']);

		$regstatus = jsg_member_register_check_status();

		$result = ($regstatus['invite_enable'] ? false : true);

		if($invite_code)
		{

			$invite_max = (int) $GLOBALS['_J']['config']['invite_count_max'];

			if(is_numeric($invite_code))
			{
				$u = $invite_code;
			}
			else
			{
				$invite_code = str_replace(array('@','#',),'|',(string) $invite_code);
				list($u,$c) = explode('|',$invite_code);
			}

			if(($u = (int) $u) > 0)
			{
				$c_l = strlen(($c = trim($c)));

				if(32 == $c_l)
				{
					$row = DB::fetch_first("select * from `".TABLE_PREFIX."invite` where `id`='{$u}'");
					if ($row)
					{
						if(!$result)
						{
							$result = ($c==md5($row['id'].$row['code'].$row['dateline'].$row['femail']));
						}
						$invite_id = $u;
						$u = $row['uid'];
						$c = $row['code'];
					}
					else
					{
						$result = false;
					}
				}

				$row = jsg_member_info($u);
				if($row)
				{
					if($c && !$result && ('admin' == $row['role_type'] || $invite_max < 1 || $invite_max >= $row['invite_count']))
					{
						$result = ($row['invitecode'] == $c);
					}
				}
				else
				{
					$result = false;
				}

				if ($reset && $row['uid']>0)
				{
					$result = DB::query("update `".TABLE_PREFIX."members` set `invitecode`='".(substr(md5($row['uid'] . $row['invitecode'] . random(16) . time()),0,16))."' where `uid`='{$row['uid']}'");
				}
			}
		}
		$result = ($result ? array('uid'=>$u,'code'=>$c,'invite_id'=>$invite_id) : false);

		return $result;
	}

	function register_by_invite($invite_uid, $uid=MEMBER_ID, $check_result=array()) {
		$u = (int) $invite_uid;
		if($u < 1) return 0;
		$uid = (int) $uid;
		if($uid < 1) return 0;
		if($uid == $u) return 0;
		
		$invite_member = jsg_member_info($u);
		if(!$invite_member) {
			return 0;
		}
		$member = jsg_member_info($uid);
		if(!$member) {
			return 0;
		}

		$timestamp = time();
		$username = $member['nickname'];
		$email = $member['email'];

		$c = $check_result['code'];



		buddy_add($u, $uid);
		if($check_result) {
			buddy_add($uid, $u);
		}

		if(0 < ($invite_id = $check_result['invite_id']))  {
			$row = DB::fetch_first("select * from `".TABLE_PREFIX."invite` where `id`='{$invite_id}'");
			if ($row) {
				DB::query("update `".TABLE_PREFIX."invite` set `fuid`='{$uid}',`fusername`='{$username}' where `id`='{$row['id']}'");
			}
		} else {
			DB::query("insert into `".TABLE_PREFIX."invite` (`uid`,`code`,`dateline`,`fuid`,`fusername`,`femail`) values ('{$u}','{$c}','{$timestamp}','{$uid}','{$username}','{$email}')");
		}

				DB::query("update `".TABLE_PREFIX."members` set `invite_count`=`invite_count`+1 where `uid`='{$u}'");

				DB::query("update `".TABLE_PREFIX."members` set `invite_uid`='{$u}' where `uid`='$uid'");

				if ($c && $GLOBALS['_J']['config']['invite_limit'] > 0) {
			$code_invite_count = DB::result_first("select count(*) as code_invite_count from `".TABLE_PREFIX."invite` where `uid`='{$u}' and `code`='{$c}'");

			if ($code_invite_count > $GLOBALS['_J']['config']['invite_limit']) {
				$this->register_check_invite($u,1);
			}
		}

		if($GLOBALS['_J']['config']['extcredits_enable'] && $u > 0) {
			
			update_credits_by_action('register',$u);
		}

		return 1;
	}
	
	function _is_email($email) {
		$ret = false;
		if($email && false !== strpos($email,'@')) {
			$ret = preg_match('~^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+([a-z]{2,4})|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$~i', $email);
		}	
		return $ret;
	}

}

?>