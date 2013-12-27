<?php
/**
 * 文件名：kaixin.mod.php
 * 版本号：1.0
 * 最后修改时间：2011年9月13日
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 开心接口模块
 */

/**
 * ModuleObject
 *
 * @package www.jishigou.com
 * @author 狐狸<foxis@qq.com>
 * @copyright 2011
 * @version $Id: kaixin.mod.php 1451 2012-08-29 08:56:40Z wuliyong $
 * @access public
 */
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $callback_url = '';
    
    var $kaixinConfig = array();    
    
    var $kaixinBindInfo = array();
    
    var $kaixinOauth = '';
    

	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->_init_kaixin();

		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch ($this->Code)
        {                
            case 'login':
                $this->Login();
                break;
            
            case 'login_check':
                $this->LoginCheck();
                break;
            
            case 'do_login':
                $this->DoLogin();
                break;
                    
            case 'reg_check':
                $this->RegCheck();
                break;
                
            case 'do_reg':
                $this->DoReg();
                break;  
                
            case 'unbind':
                $this->UnBind();
                break;
                
            case 'do_modify_bind_info':
                $this->DoModifyBindInfo();
                break;

			default:
                $this->AuthCallback();
		}
		$body=ob_get_clean();

		$this->ShowBody($body);
	}
    
    function Login()
    {
    	$keys = array(
    		'scope' => 'basic create_records create_album',
    		'state' => 'state_value',
    		'display' => 'page',
    	);
    	
        $aurl = $this->kaixinOauth->getAuthorizeURL($this->callback_url, 'code', $keys);
        
                
        $this->Messager(null,$aurl);
    }
    
    function AuthCallback()
    {
    	if(!$this->Code)
    	{
    		$this->Messager("未定义的操作", null);
    	}
    	
    	
    	$last_keys = $this->_get_last_keys();
		if(!$last_keys) {
			$this->Messager("返回内容为空，您的服务器是否支持OpenSSL？请检查……");
		}
    	if($last_keys['error_code'])
    	{
    		$last_keys = array_iconv('UTF-8', $this->Config['charset'], $last_keys);
    		$this->Messager("[{$last_keys['error_code']}]{$last_keys['error']}", null);
    	}    
    	if(!$last_keys['access_token'])
    	{
    		$this->Messager("请求错误", null);
    	}
        
                $kaixin_user_info = $this->_get_kaixin_loggend_user_info($last_keys['access_token']);
        $kaixin_user_info = array_iconv('UTF-8', $this->Config['charset'], $kaixin_user_info);
    	if($kaixin_user_info['error_code'])
    	{
    		$this->Messager("[{$kaixin_user_info['error_code']}]{$kaixin_user_info['error']}", null);
    	}
        if(!$kaixin_user_info['uid'])
        {
        	$this->Messager('获取用户信息失败', null);
        }
        
        
        $kaixin_bind_info = $this->DatabaseHandler->FetchFirst("select * from ".TABLE_PREFIX."kaixin_bind_info where `kaixin_uid`='{$kaixin_user_info['uid']}'");        
        if($kaixin_bind_info)
        {
        	$this->_update($kaixin_bind_info, $kaixin_user_info, $last_keys);
        	
                        if(false != ($user_info = $this->_user_login($kaixin_bind_info['uid'])))
            {
                if(true === UCENTER && ($ucuid = (int) $user_info['ucuid']) > 0)
                {
                    include_once(ROOT_PATH . './api/uc_client/client.php');
                    
                    $uc_syn_html = uc_user_synlogin($ucuid);
                    
                    $this->Messager("登录成功，正在为您跳转到首页。{$uc_syn_html}", $this->Config['site_url'], 5);
                }
                
                $this->Messager(null, $this->Config['site_url']);
            }
            else
            {
                $this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."kaixin_bind_info where `kaixin_uid`='{$kaixin_user_info['uid']}'");
                
                $this->Messager("绑定的用户已经不存在了", $this->Config['site_url']);
            }    
        }
        else
        {
        	$bind_info = array_merge($kaixin_user_info, $last_keys);
        	
            if(MEMBER_ID > 0)
            {
            	$this->_bind(MEMBER_ID, $bind_info);
            	
                $this->Messager(null, $this->Config['site_url']);
            }
            else
            {
                                $hash = authcode(md5($bind_info['uid'] . $bind_info['access_token']), 'ENCODE');
                
                $reg = array();
                $kaixin_uid = (string) $kaixin_user_info['uid'];
                $kaixin_uid{0} = $kaixin_uid{1} = 'x';
                $reg['username'] = 'kx_' . $kaixin_uid;
                $reg['email'] = $kaixin_user_info['email'];                
                $reg['nickname'] = $kaixin_user_info['name'];
                
                
                $this->Title = '开心帐号绑定';
                include($this->TemplateHandler->Template('kaixin_bind_info'));
            }
        }
    }
    
    function RegCheck()
    {
        exit($this->_reg_check());
    }
    function _reg_check()
    {
		$regstatus = jsg_member_register_check_status();
		if($regstatus['error'])
		{
			Return $regstatus['error'];
		}	
		if(true!==JISHIGOU_FORCED_REGISTER && $regstatus['invite_enable'])
		{
			if(!$regstatus['normal_enable'])
			{
				Return '非常抱歉，本站目前需要有邀请链接才能注册。' . jsg_member_third_party_reg_msg();
			}
		}
		
        $in_ajax = get_param('in_ajax');
        if($in_ajax)
        {
            $this->Post = array_iconv('utf-8',$this->Config['charset'],$this->Post);
        }
        
        $nickname = trim($this->Post['nickname']);
        $email = trim($this->Post['email']);
        
        
        
        $rets = array(
        	'0' => '[未知错误] 有可能是站点关闭了注册功能',
        	'-1' => '不合法',
        	'-2' => '不允许注册',
        	'-3' => '已经存在了',
        	'-4' => '不合法',
        	'-5' => '不允许注册',
        	'-6' => '已经存在了',
        );
        
        $ret = jsg_member_checkname($nickname, 1);
        if($ret < 1)
        {
        	return "帐户/昵称 " . $rets[$ret];
        }
        
        $ret = jsg_member_checkemail($email);
        if($ret < 1)
        {
        	return "Email " . $rets[$ret];
        }
        
        $password = trim($this->Post['password']);
        if(strlen($password) < 6) {
        	return "密码至少5位以上";
        }
        
        return '';
    }    
    function DoReg()
    {
    	$this->_hash_check();
    	
        if(false != ($check_result = $this->_reg_check()))
        {
            $this->Messager($check_result,null);
        }
        
        $username = trim($this->Post['username']);
        $nickname = trim($this->Post['nickname']);
        $password = trim($this->Post['password']);
        $email = trim($this->Post['email']);
        
        
        
        $uid = $ret = jsg_member_register($nickname, $password, $email);
        if($ret < 1)
        {
        	$this->Messager("注册失败{$ret}",null);
        }        
        
        $rets = jsg_member_login($uid, $password, 'uid');
        
        
        $bind_info = $this->Post['bind_info'];
        if($bind_info)
        {
            $this->_bind($rets['uid'], $bind_info);
        }
        
        
        if($this->kaixinConfig['reg_pwd_display'])
        {
            $this->Messager("您的帐户 <strong>{$rets['nickname']}</strong> 已经注册成功，请牢记您的密码 <strong>{$password}</strong>，现在为您转入到首页{$rets['uc_syn_html']}", $this->Config['site_url'],10);
        }
        else
        {
            if($rets['uc_syn_html'])
            {
                $this->Messager("注册成功，现在为您转入到首页{$rets['uc_syn_html']}", $this->Config['site_url']);
            }
            else
            {
                $this->Messager(null, $this->Config['site_url']);
            }
        }
    }
    
    function LoginCheck()
    {
        exit($this->_login_check());
    }
    function _login_check()
    {   
        $in_ajax = get_param('in_ajax');
        if($in_ajax)
        {
            $this->Post = array_iconv('utf-8',$this->Config['charset'],$this->Post);
        }
        
        $username = trim($this->Post['username']);
        $password = trim($this->Post['password']);
        
        
        $rets = jsg_member_login_check($username, $password);
        $ret = $rets['uid'];
        if($ret < 1)
        {
        	$rets = array(
        		'0' => '未知错误 ',
        		'-1' => '用户名或者密码错误',
        		'-2' => '用户名或者密码错误',
        		'-3' => '累计 5 次错误尝试，15 分钟内您将不能登录',
        	);
        	
        	return $rets[$ret];
        }        
        
        return '';
    }
    function DoLogin()
    {
        $this->_hash_check();
    	
        if(false != ($check_result = $this->_login_check()))
        {
            $this->Messager($check_result,null);
        }
        
        $timestamp = time();
        $username = trim($this->Post['username']);
        $password = trim($this->Post['password']);
        
        
        $rets = jsg_member_login($username, $password);             
        
        
        $bind_info = $this->Post['bind_info'];
        if($bind_info)
        {
            $this->_bind($rets['uid'], $bind_info);
        }
        
        
    	if($rets['uc_syn_html'])
        {            
            $this->Messager("登录成功，现在为您转入到首页{$rets['uc_syn_html']}", $this->Config['site_url'], 5);
        }
        else
        {
        	$this->Messager(null, $this->Config['site_url']);
        }
    }
    
    
    function _hash_check()
    {
    	$hash = '';
    	if($this->Post['hash']) 
    	{
    		$hash = authcode($this->Post['hash'], 'DECODE');
    	}
    	
    	$md5 = md5($this->Post['bind_info']['uid'] . $this->Post['bind_info']['access_token']);
    	
    	if($hash != $md5)
    	{
    		$this->Messager("非法请求", null);
    	}
    }
    
    function UnBind()
    {
        $uid = max(0, (int) MEMBER_ID);
        if($uid < 1)
        {
            $this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",null);
        }
        
        $this->_unbind($uid);
        
        
        $this->Messager("已经成功解除绑定");
    }
    function _unbind($uid)
    {
    	$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."kaixin_bind_info where `uid`='$uid'");
    }
    
    function DoModifyBindInfo()
    {
        $uid = max(0, (int) MEMBER_ID);
        if($uid < 1)
        {
            $this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",null);
        }
        
        $synctoqq = (get_param('synctoqq') ? 1 : 0);
        
        $this->DatabaseHandler->Query("update ".TABLE_PREFIX."kaixin_bind_info set `synctoqq`='$synctoqq' where `uid`='$uid'");
        
        
        $this->Messager("设置成功");
    }
    
    function _bind($uid, $bind_info, $last_keys = array())
    {    	
    	$ret = false;
    	
    	$uid = is_numeric($uid) ? $uid : 0;
    	if($uid > 0)
    	{
    		if($last_keys)
	    	{
	    		foreach($last_keys as $k=>$v)
	    		{
	    			if(!isset($bind_info[$k])) 
	    			{
	    				$bind_info[$k] = $v;
	    			}
	    		}
	    	}
	    	
    		$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."kaixin_bind_info where `uid`='$uid'");
	    	$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."kaixin_bind_info where `kaixin_uid`='{$bind_info['uid']}'");	    			
    		$user_info = $this->DatabaseHandler->FetchFirst("select * from ".TABLE_PREFIX."members where `uid`='$uid'");
	    	if(!$user_info)
	    	{
	    		return false;
	    	}
	    	
    		if($bind_info['uid'] && $bind_info['access_token'])
    		{
    			$timestamp = time();
	    		
	    		$ret = $this->DatabaseHandler->Query("insert into ".TABLE_PREFIX."kaixin_bind_info 
					(`uid`,`kaixin_uid`,`kaixin_name`,`kaixin_gender`,`kaixin_logo50`,`token`,`token_time`,`token_expire`,`dateline`) values 
				('$uid','{$bind_info['uid']}','{$bind_info['name']}','{$bind_info['gender']}','{$bind_info['logo50']}','{$bind_info['access_token']}','$timestamp','{$bind_info['expires_in']}','$timestamp')");
    		}	    		
    	}
		
		return $ret;
    }
    
    function _update($kaixin_bind_info, $kaixin_user_info, $last_keys = array())
    {
    	$ret = 0;
    	
    	if(!$kaixin_bind_info['kaixin_uid'] || $kaixin_bind_info['kaixin_uid']!=$kaixin_user_info['uid'])
    	{
    		return $ret;
    	}
    	
    	$updates = array('name'=>'kaixin_name', 'gender'=>'kaixin_gender', 'logo50'=>'kaixin_logo50', 'access_token'=>'token', );
    	
    	if($last_keys)
    	{
    		foreach($last_keys as $k=>$v)
    		{
    			if(!isset($kaixin_user_info[$k]))
    			{
    				$kaixin_user_info[$k] = $v;
    			}
    		}
    	}
    	
    	$sets = array();
    	foreach($kaixin_user_info as $k=>$v)
    	{
    		$k = $updates[$k];
    		if($k && isset($kaixin_bind_info[$k]) && $kaixin_bind_info[$k] != $v)
    		{
    			$sets[$k] = "`$k`='$v'";
    		}
    	}
    	
    	if($sets)
    	{
    		$ret = $this->DatabaseHandler->Query("update ".TABLE_PREFIX."kaixin_bind_info set ".implode(" , ", $sets)." where `kaixin_uid`='{$kaixin_bind_info['kaixin_uid']}'");
    	}
    	
    	return $ret;
    }
    
    function _init_kaixin()
    {
        if ($this->Config['kaixin_enable'] && kaixin_init($this->Config))
		{ 
			$this->callback_url = $this->Config['site_url'] . "/index.php?mod=kaixin";
			
            $this->kaixinConfig = ConfigHandler::get('kaixin');
                           		  
			if(MEMBER_ID > 0)
            {
                $this->kaixinBindInfo = $this->DatabaseHandler->FetchFirst("select * from ".TABLE_PREFIX."kaixin_bind_info where `uid`='".MEMBER_ID."'");
            }
            
            
            $this->_init_kaixin_oauth();
            
			if($this->Config['third_party_regstatus'] && in_array('kaixin', $this->Config['third_party_regstatus'])) {
	    		define('JISHIGOU_FORCED_REGISTER', true);
	    	}
		}
		else
		{
			$this->Messager("整合开心的功能未开启",null);
		}
    }
    
    function _init_kaixin_oauth($access_token = null)
    {
    	$this->kaixinOauth = kaixin_oauth($access_token);
    }
    
    function _user_login($uid)
    {
    	
    	
    	return jsg_member_login_set_status($uid);
    }
    
    function _get_last_keys()
    {
    	$p = array(
    		'code' => $this->Code,
    		'redirect_uri' => $this->callback_url,
    	);
    	
    	return $this->kaixinOauth->getAccessToken('code', $p);
    }
    
    function _get_kaixin_loggend_user_info($access_token = null)
    {
    	$p = array(
    		'access_token' => $access_token,
    	);
    	
    	$ret = $this->_api_call('users/me', $p);
    	
    	return $ret;
    }
    
    function _get_kaixin_user_info($uids, $access_token = null)
    {
    	$p = array(
    		'uids' => $uids,
    	);
    	if($access_token)
    	{
    		$p['access_token'] = $access_token;
    	}
    	
    	$ret = $this->_api_call('users/show', $p);
    	
    	return $ret;
    }    
    
    function _api_call($method, $p)
    {    	
    	$ret = kaixin_api($method, $p, 'GET', $this->kaixinOauth);
    	
    	return $ret;
    }
}


?>
