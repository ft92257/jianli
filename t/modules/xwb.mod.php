<?php
/**
 *
 * 新浪微博接口模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: xwb.mod.php 1451 2012-08-29 08:56:40Z wuliyong $
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{

	var $callback_url = '';
	var $module_config = array();
	var $oauth = null;
	

	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->_init();

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
        	
                
			case 'enter':
				$this->Enter();
				break;

            case 'synctopic':
                $this->SyncTopic();
                break;

            case 'syncreply':
                $this->SyncReply();
                break;

			default:
				$this->Main();
		}

		exit;
	}


	function Main() {  
		$this->third_party_regstatus();
		  	
    	if(MEMBER_ID > 0 && 'POST' == $_SERVER['REQUEST_METHOD']) {
    		$this->_update();
    	}
    	
		if($this->module_config['oauth2_enable']) {
			$this->AuthCallback();
		} else {
			$this->xwb();
		}
	}
	
	function xwb() {		
				require_once ROOT_PATH . 'include/xwb/sina.php';

						XWB_plugin::init();
		XWB_plugin::request();
	}

	function Login() {
		$this->_check_oauth2();
		
		$aurl = $this->oauth->getAuthorizeURL($this->callback_url);
		
		$this->Messager(null, $aurl);
	}
	
	function AuthCallback() {
		$this->_check_oauth2();
		
		if(!$this->Code) {
			$this->Messager('未定义的操作', null);
		}
		
		$last_keys = $this->_get_last_keys();
		if(!$last_keys) {
			$this->Messager("新浪微博返回内容为空，启用OAuth2.0接口，需要您的服务器支持OpenSSL，请检查……");
		}
    	if($last_keys['error_code']) {
    		$last_keys = array_iconv('UTF-8', $this->Config['charset'], $last_keys);
    		$this->Messager("[{$last_keys['error_code']}]{$last_keys['error']}", null);
    	}
    	if(!$last_keys['access_token']) {
    		$this->Messager('请求错误', null);
    	}
    	
    	$rets = $this->_get_uid($last_keys['access_token']);
    	if($rets['error_code']) {
    		$this->Messager("[{$rets['error_code']}]{$rets['error']}", null);
    	}
    	if(!$rets['uid']) {
    		$this->Messager('获取用户信息失败', null);
    	}
    	$last_uid = $rets['uid'];
    	
    	$xwb_bind_info = DB::fetch_first("select * from ".DB::table('xwb_bind_info')." where `sina_uid`='$last_uid'");
    	if($xwb_bind_info) {
                        if(false != ($user_info = $this->_user_login($xwb_bind_info['uid']))) {
            	if($xwb_bind_info['access_token'] != $last_keys['access_token']) {
            		DB::query("update ".DB::table('xwb_bind_info')." set `access_token`='{$last_keys['access_token']}', `expires_in`='{$last_keys['expires_in']}' where `sina_uid`='$last_uid'");
            	}
            	
                if(true === UCENTER && ($ucuid = (int) $user_info['ucuid']) > 0) {
                    include_once(ROOT_PATH . './api/uc_client/client.php');
                    
                    $uc_syn_html = uc_user_synlogin($ucuid);
                    
                    $this->Messager("登录成功，正在为您跳转到首页。{$uc_syn_html}", $this->Config['site_url'], 5);
                }
                
                $this->Messager(null, $this->Config['site_url']);
            } else {
                DB::query("delete from ".DB::table('xwb_bind_info')." where `sina_uid`='{$last_uid}'");
                
                $this->Messager("绑定的用户已经不存在了", $this->Config['site_url']);
            }
    	} else {
    		$bind_info = $this->_get_info($last_uid, $last_keys['access_token']);
    		$bind_info = array_iconv('utf-8', $this->Config['charset'], $bind_info);    		
    		$bind_info = array_merge($bind_info, $last_keys);
    		
    		if(MEMBER_ID > 0) {
    			$this->_bind(MEMBER_ID, $bind_info, $last_keys);
    			
    			$this->Messager(null, $this->Config['site_url']);
    		} else {
    			                $hash = authcode(md5($bind_info['id'] . $bind_info['access_token']), 'ENCODE');
                
                $reg = array();
                $reg['username'] = $bind_info['domain'];
                $reg['email'] = $bind_info['email'];                
                $reg['nickname'] = $bind_info['screen_name'];
                if($this->module_config['is_sync_face']) {
                	$reg['face'] = $bind_info['avatar_large'];
                }
                
                
                $this->Title = '新浪微博帐号绑定';
                include($this->TemplateHandler->Template('xwb_bind_info'));
    		}
    	}
	}
    
    function RegCheck()
    {
		$this->_check_oauth2();
		
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
		$this->_check_oauth2();
		
    	$this->_hash_check();
    	
        if(false != ($check_result = $this->_reg_check()))
        {
            $this->Messager($check_result,null);
        }
        
        $username = trim($this->Post['username']);
        $nickname = trim($this->Post['nickname']);
        $password = trim($this->Post['password']);
        $email = trim($this->Post['email']);
        $face = trim($this->Post['face']);
        $synface = ($this->Post['synface'] ? 1 : 0);        
        
        
        $uid = $ret = jsg_member_register($nickname, $password, $email);
        if($ret < 1)
        {
        	$this->Messager("注册失败{$ret}",null);
        }        
        
        $rets = jsg_member_login($uid, $password, 'uid');
        if($this->module_config['is_sync_face'] && $synface && $face) {
        	        	jsg_schedule(array('uid'=>$uid, 'face'=>$face), 'syn_sina_face', $uid);
        }
        
        $bind_info = $this->Post['bind_info'];
        if($bind_info)
        {
            $this->_bind($uid, $bind_info);
        }
        
        
        if($this->module_config['reg_pwd_display'])
        {
            $this->Messager("您的帐户 <strong>{$rets['nickname']}</strong> 已经注册成功，请牢记您的密码 <strong>{$password}</strong>，现在为您转入到首页{$rets['uc_syn_html']}", $this->Config['site_url'], 15);
        }
        else
        {
            $this->Messager("注册成功，现在为您转入到首页{$rets['uc_syn_html']}", $this->Config['site_url'], 10);
        }
    }
    
    function LoginCheck()
    {
		$this->_check_oauth2();
		
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
		$this->_check_oauth2();
		
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
    
    function _user_login($uid)
    {
    	return jsg_member_login_set_status($uid);
    }    
    
    function third_party_regstatus() {
    	if($this->Config['third_party_regstatus'] && in_array('sina', $this->Config['third_party_regstatus'])) {
    		define('JISHIGOU_FORCED_REGISTER', true);
    	}
    }
    
    function _check_oauth2() {
    	$this->_update();
    	if(!$this->module_config['oauth2_enable']) {
    		$this->Messager('请先在后台开启OAuth2认证', null);
    	}
    }
    
    function _syn_face($uid, $face='') {
    	return sina_weibo_sync_face($uid, $face);    	
    }
    
    
    function _hash_check()
    {
    	$hash = '';
    	if($this->Post['hash']) 
    	{
    		$hash = authcode($this->Post['hash'], 'DECODE');
    	}
    	
    	$md5 = md5($this->Post['bind_info']['id'] . $this->Post['bind_info']['access_token']);
    	
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
    	DB::query("delete from ".TABLE_PREFIX."xwb_bind_info where `uid`='$uid'");
    	
    	$this->_update();
    }

    function DoModifyBindInfo() {
    	if(MEMBER_ID < 1) {
            $this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",null);
        }
        
    	$xwb_bind_info = "select * from ".DB::table('xwb_bind_info')." where `uid`='".MEMBER_ID."'";
    	if(!$xwb_bind_info) {
    		$this->Messager('用户记录已经不存在了',null);
    	}

    	$p = (array) json_decode($xwb_bind_info['profile'], true);
    	$keys = array('bind_setting', 'synctopic_tojishigou', 'syncreply_tojishigou');
    	foreach($keys as $k) {
    		if(isset($_POST[$k])) {
    			$v = ($_POST[$k] ? 1 : 0);
    			$p[$k] = $v;
    		}
    	}
		$np = array();
		foreach($keys as $k) {
			if(isset($p[$k])) {
				$np[$k] = $p[$k];
			}
		}
    	
    	DB::query("update ".DB::table('xwb_bind_info')." set `profile`='".json_encode($np)."' where `uid`='".MEMBER_ID."'");

    	$this->_update();
    	
        $this->Messager("设置成功");
    }

    
	function Enter()
	{
	    $share_time = get_param('share_time');
		if (MEMBER_ID > 0 && $share_time>0 && ($share_time + 300 > time()))
		{
			$bind_info = sina_weibo_bind_info(MEMBER_ID);

            if($share_time==$bind_info['share_time'])
            {
                $_site_url = substr($this->Config['site_url'],strpos($this->Config['site_url'],':/'.'/') + 3);

                $share_msg = "我刚绑定了新浪微博帐户，可以使用新浪微博帐户登录{$this->Config['site_name']}(".$_site_url.")、不再担心忘记密码；还可以在{$this->Config['site_name']}发微博同步发到新浪上，吸引更多人关注；你也来试试吧 ".get_full_url($this->Config['site_url'],"index.php?mod=account&code=sina")." ";

                
                $TopicLogic = Load::logic('topic', 1);

                
                $_POST['syn_to_sina'] = (sina_weibo_bind_setting($bind_info) ? 1 : 0);
                $add_result = $TopicLogic->Add($share_msg);

                
                DB::query("update ".TABLE_PREFIX."xwb_bind_info set `share_time`='".mt_rand(1,1111111111)."' where `uid`='".MEMBER_ID."'");
                
                $this->_update();
            }
		}

        exit;
	}

    function SyncTopic()
    {
        $sina = ConfigHandler::get('sina');
        if(!$sina['is_synctopic_tojishigou'])
        {
            return ;
        }

        $info = array();

        $uid = max(0, (int) ($this->Post['uid'] ? $this->Post['uid'] : $this->Get['uid']));
        if(!$uid)
        {
            $uid = MEMBER_ID;
        }

        if(!$uid)
        {
            return ;
        }

        $info = sina_weibo_bind_info($uid);

        if(!$info)
        {
            return ;
        }

        $uid = max(0, (int) $info['uid']);
        if(!$uid)
        {
            return ;
        }

        $sina_uid = $info['sina_uid'];
        if(!$sina_uid)
        {
            return ;
        }

        if(!(sina_weibo_synctopic_tojishigou($uid)))
        {
            return ;
        }

        if($sina['syncweibo_tojishigou_time'] > 0 && ($info['last_read_time'] + $sina['syncweibo_tojishigou_time'] > time()))
        {
            return ;
        }

        $member = DB::fetch_first("select * from ".TABLE_PREFIX."members where `uid`='{$uid}'");
        if(!$member)
        {
            return ;
        }

        if(!($this->MemberHandler->HasPermission('xwb','__synctopic',0,$uid)))
        {
            return ;
        }


        if($this->module_config['oauth2_enable']) {
        	$p = array(
        		'uid' => $sina_uid,
        		'access_token' => $info['access_token'],
        	);
        	$rets = sina_weibo_api('2/statuses/user_timeline', $p, 'GET');
        	$datas = $rets['statuses'];
        } else {
	        require_once ROOT_PATH . 'include/xwb/sina.php';
	        $wb = XWB_plugin::getWB();
	        $datas = $wb->getUserTimeline(null,$sina_uid);
        }


        if($datas)
        {
            krsort($datas);
            
            $TopicLogic = Load::logic('topic', 1);

            foreach($datas as $data)
            {
                $mid = $data['id'];

                if($mid && !(DB::fetch_first("select * from ".TABLE_PREFIX."xwb_bind_topic where `mid`='{$mid}'")) && 
                	($content = trim(strip_tags(array_iconv('utf-8',$this->Config['charset'],$data['text'] . (isset($data['retweeted_status']) ? 
                		" /"."/@{$data['retweeted_status']['user']['name']}: {$data['retweeted_status']['text']}" : "")))))
                ) {
                	DB::query("insert into ".TABLE_PREFIX."xwb_bind_topic (`mid`) values ('{$mid}')");

                    $_t = time();
                    if($data['created_at'])
                    {
                        $_t = strtotime($data['created_at']);
                    }
                    $_t = (is_numeric($_t) ? $_t : 0);
                    $add_datas = array(
                        'content' => $content,
                        'from' => 'sina',
                        'type' => 'first',
                        'uid' => $uid,
                        'timestamp' => $_t,
                    );
                    $add_result = $TopicLogic->Add($add_datas);

                    if(is_array($add_result) && count($add_result))
                    {
                        $tid = max(0, (int) $add_result['tid']);

                        if($tid)
                        {
                            if($sina['is_syncimage_tojishigou'] && $data['original_pic'])
                            {
                                $TopicLogic->_parse_url_image($add_result,$data['original_pic']);
                            }
                            if($sina['is_syncimage_tojishigou'] && $data['retweeted_status']['original_pic'])
                            {
                                $TopicLogic->_parse_url_image($add_result,$data['retweeted_status']['original_pic']);
                            }

                            DB::query("replace into ".DB::table('xwb_bind_topic')." (`tid`, `mid`) values ('$tid', '$mid')");
                        }
                    }
                }
            }
        }

        DB::query("update ".TABLE_PREFIX."xwb_bind_info set `last_read_time`='".time()."',`last_read_id`='{$mid}' where `sina_uid`='{$sina_uid}'");
    }

    function SyncReply()
    {
        $sina = ConfigHandler::get('sina');
        if(!$sina['is_syncreply_tojishigou'])
        {
            return ;
        }

        $tid = max(0, (int) ($this->Post['tid'] ? $this->Post['tid'] : $this->Get['tid']));
        if(!$tid)
        {
            return ;
        }

        $info = DB::fetch_first("select * from ".TABLE_PREFIX."xwb_bind_topic where `tid`='$tid'");
        if(!$info)
        {
            return ;
        }

        $mid = $info['mid'];
        if(!$mid)
        {
            return ;
        }

        if($sina['syncweibo_tojishigou_time'] > 0 && ($info['last_read_time'] + $sina['syncweibo_tojishigou_time'] > time()))
        {
            return ;
        }

        if(!($topic_info = DB::fetch_first("select * from ".TABLE_PREFIX."topic where `tid`='$tid'")))
        {
            return ;
        }
        
        $xwb_bind_info = sina_weibo_bind_info($topic_info['uid']);
        if(!$xwb_bind_info) return ;

        if(!(sina_weibo_syncreply_tojishigou($topic_info['uid'])))
        {
            return ;
        }

        if(!($this->MemberHandler->HasPermission('xwb','__syncreply',0,$topic_info['uid'])))
        {
            return ;
        }


        if($this->module_config['oauth2_enable']) {
        	$p = array(
        		'id' => $mid,
        		'access_token' => $xwb_bind_info['access_token']
        	);
        	$rets = sina_weibo_api('2/comments/show', $p, 'GET');
        	$datas = $rets['comments'];
        } else {
	        require_once ROOT_PATH . 'include/xwb/sina.php';
	        $wb = XWB_plugin::getWB();
	        $datas = $wb->getComments($mid);
        }


        if($datas)
        {
            krsort($datas);
            
            $TopicLogic = Load::logic('topic', 1);

            foreach($datas as $data)
            {
                $mid = $data['id'];

                $sina_uid = $data['user']['id'];

                if($mid && ($bind_info = DB::fetch_first("select * from ".TABLE_PREFIX."xwb_bind_info where `sina_uid`='$sina_uid'")) && 
                	!(DB::fetch_first("select * from ".TABLE_PREFIX."xwb_bind_topic where `mid`='{$mid}'")) && 
                	($content = trim(strip_tags(array_iconv('utf-8',$this->Config['charset'],$data['text'] . 
                		(isset($data['retweeted_status']) ? 
                		" /"."/@{$data['retweeted_status']['user']['name']}: {$data['retweeted_status']['text']}" : "")))))
                ) {
                	DB::query("insert into ".TABLE_PREFIX."xwb_bind_topic (`mid`) values ('{$mid}')");

                    $_t = time();
                    if($data['created_at'])
                    {
                        $_t = strtotime($data['created_at']);
                    }
                    $_t = (is_numeric($_t) ? $_t : 0);
                    $add_datas = array(
                        'content' => $content,
                        'from' => 'sina',
                        'type' => 'reply',
                        'uid' => $bind_info['uid'],
                        'timestamp' => $_t,
                    );
                    $add_result = $TopicLogic->Add($add_datas);

                    if(is_array($add_result) && count($add_result))
                    {
                        $_tid = max(0, (int) $add_result['tid']);

                        if($_tid)
                        {
                            if($sina['is_syncimage_tojishigou'] && $data['original_pic'])
                            {
                                $TopicLogic->_parse_url_image($add_result,$data['original_pic']);
                            }

							DB::query("replace into ".DB::table('xwb_bind_topic')." (`tid`, `mid`) values ('$_tid', '$mid')");
                        }
                    }
                }
            }
        }

        DB::query("update `".TABLE_PREFIX."xwb_bind_topic` set `last_read_time`='".time()."' where `tid`='{$tid}'");
    }
    
    function _update($uid=0) {
    	$uid = ($uid > 0 ? $uid : MEMBER_ID);
    	Load::model('misc')->update_account_bind_info($uid, '', '', 1);
    }
 
    function _bind($uid, $bind_info, $last_keys = array()) {    	
    	$ret = false;
    	
    	$uid = is_numeric($uid) ? $uid : 0;
    	if($uid > 0) {
    		if(is_numeric($bind_info)) {
    			$bind_info = array(
    				'uid' => (int) $bind_info
    			);
    		}
    		$bind_info['uid'] = ($bind_info['uid'] ? $bind_info['uid'] : $bind_info['id']);
    		if($last_keys) {
	    		foreach($last_keys as $k=>$v) {
	    			if(!isset($bind_info[$k])) {
	    				$bind_info[$k] = $v;
	    			}
	    		}
	    	}
	    	
    		DB::query("delete from ".TABLE_PREFIX."xwb_bind_info where `uid`='$uid'");
	    	DB::query("delete from ".TABLE_PREFIX."xwb_bind_info where `sina_uid`='{$bind_info['uid']}'");	    			
    		$user_info = DB::fetch_first("select * from ".TABLE_PREFIX."members where `uid`='$uid'");
	    	if(!$user_info) {
	    		return false;
	    	}
	    	
    		if($bind_info['uid'] && $bind_info['access_token']) {
    			$timestamp = time();
	    		
	    		$ret = DB::query("replace into ".TABLE_PREFIX."xwb_bind_info 
					(`uid`,`sina_uid`,`access_token`,`expires_in`,`name`,`screen_name`,`domain`,`avatar_large`,`dateline`) values 
				('$uid','{$bind_info['uid']}','{$bind_info['access_token']}','{$bind_info['expires_in']}','{$bind_info['name']}','{$bind_info['screen_name']}','{$bind_info['domain']}','{$bind_info['avatar_large']}','$timestamp')");
    		}	    		
    	}
    	
    	$this->_update($uid);
		
		return $ret;
    }
    
    function _init() {    
		if($this->Config['sina_enable'] && sina_weibo_init($this->Config)) {
			
			$this->module_config = ConfigHandler::get('sina');
			
			if($this->module_config['oauth2_enable']) {
				$this->callback_url = $this->Config['site_url'] . '/index.php?mod=xwb';
								
				$this->_init_oauth();
			}
		} else {
			$this->Messager("整合新浪微博的功能未开启",null);
		}
    }
    function _init_oauth($access_token = null) {
    	$this->oauth = sina_weibo_oauth($access_token);
    }
    
	function _get_last_keys() {
    	$p = array(
    		'code' => $this->Code,
    		'redirect_uri' => $this->callback_url,
    	);
    	
    	return $this->oauth->getAccessToken('code', $p);
    }
    
    function _get_uid($access_token=null) {
    	$p = array(
    		'access_token' => $access_token,
    	);
    	
    	return sina_weibo_api('2/account/get_uid', $p, 'GET', $this->oauth);
    }
    
    function _get_info($uid, $access_token=null) {
    	$p = array(
    		'uid' => $uid,
    	);
    	if($access_token) {
    		$p['access_token'] = $access_token;
    	}
    	
    	return sina_weibo_api('2/users/show', $p, 'GET', $this->oauth);
    }    

}


?>
