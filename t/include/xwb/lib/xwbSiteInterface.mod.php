<?php
/*******************************************************************
 *[JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename xwbSiteInterface.mod.php $
 *
 * @Author 狐狸<foxis@qq.com> $
 *
 * @Date 2010-12-06 04:58:24 $
 *******************************************************************/ 

class xwbSiteInterface {
	function xwbSiteInterface(){}
	
	function t(){
		return ;
		session_start();
		echo '<pre>';
		$wb = XWB_plugin::getWB();
		$ret = $wb->getUserShow(11014);
		print_r($ret);
		
		exit;
		$ret = $wb->update('TEST-PUBLIST-MBLOG.'.rand(1111111,99999999), false);
		print_r($ret);
		var_dump($ret['id']);
		
		$line = "\r\n".str_repeat('-', 80)."\r\n";
		XWB_plugin::LOG($line.print_r($ret, 1).$line);
	}
	
	function signer(){
		if( !XWB_plugin::pCfg('is_signature_display') ){
			XWB_plugin::showError('新浪微博签名功能已经关闭！');
		}
		
		$myid = XWB_plugin::getBindInfo('sina_uid');
		$myKeyStr = '';
		if (!empty($myid)){
			$wbApi = XWB_plugin::getWB();
			$wbApi->is_exit_error = false;
			$ret = $wbApi->getUserShow($myid);
			if ( isset($ret['created_at']) ){
				$t = @strtotime($ret['created_at']);
				$t = ( $t === false || $t == '-1' ) ? time() : $t;
				$myKeyStr = substr(md5(date('Ymd', $t)),0,8);
			}
		}
		include XWB_P_ROOT.'/tpl/signer.tpl.php';
	}
	
	function getTips(){
		if( !XWB_plugin::pCfg('is_tips_display') ){
			$this->ajaxOut('99999', '新浪微博资料页功能已经关闭！');
		}
		
		$view_id = XWB_plugin::V('g:view_id');
		$view_id*=1;
		if (empty($view_id)) {$this->ajaxOut('10001', '查看目标ID不能为空.');}
        
		$wbApi  = XWB_plugin::getWB();
        $keys	= $this->_getTockenFromDB($view_id);
        if (empty($keys))  {$this->ajaxOut('10002', '无法从数据库中获取对方绑定信息，可能是未绑定用户，不能查看其信息.');}
        $wbApi->setTempToken($keys['oauth_token'], $keys['oauth_token_secret']);
        $wbApi->is_exit_error = false;

        
        $data = $this->_getUserInfoFromDB($view_id);
        

        if ( ! $data || ! isset($data['timestamp']) || intval(XWB_plugin::pCfg('wbx_medal_update_time')) < time() - $data['timestamp'])         {
            $rst = $wbApi->getUserShow($view_id);
            
            if (!is_array($rst) || isset($rst['error']) || empty($rst['id'])){
                    $this->ajaxOut('10003', '无法从接口中用户信息,['.$rst['error_code'].":".$rst['error'].']');
            }

            $data = array(
            'sina_uid'=>$rst['id'].'','sina_name'=>$rst['screen_name'],'location'=>$rst['location'],
            'gender'=>$rst['gender'],'profile_image_url'=>$rst['profile_image_url'],
            'followers_count'=>$rst['followers_count'],'friends_count'=>$rst['friends_count'],'statuses_count'=>$rst['statuses_count'],
            'description'=>$rst['description'],'last_blog'=>$rst['status']['text'],'last_blog_id'=>$rst['status']['id'].'',
            'timestamp'=>time()
            );

            
            $this->_setUserInfoFromDB($view_id, $data);
            
        }

        $isFriend	= 0;
        $mySinaUid	= XWB_plugin::getBindInfo('sina_uid','');
        if ( !empty($mySinaUid) ){
            if( $mySinaUid == $view_id ){
                $isFriend	= 1;
            }else{
                $isFriend	= $wbApi->existsFriendship($mySinaUid,$view_id);
                $isFriend	= $isFriend['friends']==true ? 1 : 0;
            }
            $data['isFriend'] = $isFriend;
        }
		
		$this->ajaxOut(0,$data);		
	}
	
	function ajaxOut($code, $rst=''){
		echo json_encode(array($code, $rst));exit;
	}
	
		function _getTockenFromDB($sina_uid){
		$db = XWB_plugin::getDB();
		$sql = "SELECT * FROM ".XWB_S_TBPRE."xwb_bind_info  WHERE  sina_uid='".$sina_uid."' ";
		$r	= $db->fetch_first($sql);
		if (empty($r)) {return false;}
		return array('oauth_token'=>$r['token'],
					 'oauth_token_secret'=>$r['tsecret'],
					 'uid'=>$r['uid'],
					 'sina_uid'=>$r['sina_uid']);
	}

    
    function _setUserInfoFromDB($sina_uid, $dataset)
    {
        $profile = XWB_plugin::O('xwbUserProfile');
        return $profile->set4Tip($sina_uid, 'tipUserInfo', $dataset);
    }

    
    function _getUserInfoFromDB($sina_uid)
    {
        $profile = XWB_plugin::O('xwbUserProfile');
        return $profile->get4Tip($sina_uid, 'tipUserInfo', FALSE);
    }

	function attention(){
		if( !XWB_plugin::pCfg('is_tips_display') ){
			XWB_plugin::deny('新浪微博资料页功能已经关闭！');
		}
		
		$att_id = XWB_plugin::V('g:att_id');
		$wbApi  = XWB_plugin::getWB();
		$wbApi->is_exit_error = false;
		$mySinaUid	= XWB_plugin::getBindInfo('sina_uid','');
		if (empty($mySinaUid)){
			XWB_plugin::deny('你不是绑定用户，不能关注其它用户');
		}
		
		$rst = $wbApi->createFriendship($att_id);
		$url = 'http:/'.'/t.sina.com.cn/'.$att_id;
		XWB_plugin::redirect($url, 3);
	}
	
	function reg(){
		if( !XWB_plugin::pCfg('is_account_binding') ){
			XWB_plugin::showError('新浪微博绑定功能已经关闭！');
		}
		
		$this->_chkIsWaitingForReg();
		$xwb_user = XWB_plugin::getUser();
		$sina_id = $xwb_user->getInfo('sina_uid');
		$wb = XWB_plugin::getWB();
		
		$sina_user_info = $wb->getUserShow($sina_id);
		
		include XWB_P_ROOT.'/tpl/register.tpl.php';
	}
	
	function doReg(){
		if( !XWB_plugin::pCfg('is_account_binding') ){
			XWB_plugin::showError('新浪微博绑定功能已经关闭！');
		}
		
		$this->_chkIsWaitingForReg();
		$usernameS	= trim( (string)(XWB_plugin::V('p:siteRegName')) );
        $nicknameS = trim( (string) XWB_plugin::V('p:siteRegNickname') );
		$email		= trim( (string)(XWB_plugin::V('p:siteRegEmail')) );
		$regPwd	= trim( (string)(XWB_plugin::V('p:regPwd')) );
		
		        $nickname   = XWB_plugin::convertEncoding($nicknameS, "UTF8", XWB_S_CHARSET);
		$email		= XWB_plugin::convertEncoding($email, "UTF8", XWB_S_CHARSET);
		
		$uid = 0;
		if (empty($email))	{$uid = -101;}
		if (empty($regPwd))	{$uid = -103;}
        if (empty($nickname)) {$uid = -105;}
		
		$password = $regPwd;
		
		$db	 = XWB_plugin::getDB();
		
		if (empty($uid)){
			$wbApi	= XWB_plugin::getWB();
			$uInfo	= $wbApi->verifyCredentials();
			
						$bInfo = $db->fetch_first("SELECT * FROM ".XWB_S_TBPRE."xwb_bind_info  WHERE  sina_uid='".$uInfo['id']."'");
			if ( !empty($bInfo) && is_array($bInfo) ){
				$uid = -201;
			}else{
				require_once XWB_P_ROOT. '/lib/xwbSite.inc.php';
				$uidReg = xwb_setSiteRegister($nickname, $email, $password);
				$uid	= $uidReg['uid'];
				$password  = $uidReg['password'];
			}
			unset($bInfo);
		
		}
		
		$msg = '';
		
		if ($uid<1){
			$msg = $this->_getRegTip($uid);
		}else{
			$sess = XWB_plugin::getUser();
			$sess->setInfo('sina_uid', $uInfo['id']);
			$last_key = $sess->getOAuthKey(true);
			
			$inData = array();
			$inData['uid'] 		= $uid;
			$inData['sina_uid'] = $uInfo['id'];
			$inData['token']	= $last_key['oauth_token'];
			$inData['tsecret']	= $last_key['oauth_token_secret'];
			$inData['profile']	= '[]';
			$sqlF = array();
			$sqlV = array();
			foreach ($inData as $k=>$v){
				$sqlF[] = "`".$k."`";
				$sqlV[] = "'".mysql_escape_string($v)."'";
			}
			
			$sql = "INSERT INTO ".XWB_S_TBPRE."xwb_bind_info  (".implode(",",$sqlF).") VALUES (".implode(",",$sqlV).") ;";
			$rst = $db->query($sql, 'UNBUFFERED');
			
			require_once XWB_P_ROOT. '/lib/xwbSite.inc.php';
			xwb_setSiteUserLogin($uid);
			
			if( XWB_plugin::pCfg('is_sync_face') )
			{
								$faceSync = XWB_plugin::N('sinaFaceSync');
				$faceSyncResult = $faceSync->sync($uid);				

				$faceSyncResultLog = $this->_logFaceSyncResult( $faceSyncResult );
											}
			
			setcookie('xwb_tips_type','',0);
			$sess->setInfo('waiting_site_reg', '0');
			
			$msg = "已为你创建了&nbsp;" . XWB_S_TITLE .  "&nbsp;的帐号，并与你的新浪微博帐号进行绑定。下次你可以继续使用新浪微博帐号登录使用&nbsp;" . XWB_S_TITLE . "&nbsp。";
			$msg.= "<br>帐号:  <em>".htmlspecialchars($nicknameS)."</em>  ";
			if ( XWB_plugin::pCfg('reg_pwd_display') )
			{
				$msg.="<br>密码:  <em>".htmlspecialchars($password)."</em>  ";
			}
			
						$sess->appendStat('bind', array( 'uid' => $uInfo['id'], 'type' => 2 ));
			
		}
		$this->_oScript('xwbSetTips',array($uid,$msg, XWB_plugin::pCfg('reg_pwd_display')));
	}
	
	
	function doBindAtNotLog(){
		if( !XWB_plugin::pCfg('is_account_binding') ){
			XWB_plugin::showError('新浪微博绑定功能已经关闭！');
		}
		
		$this->_chkIsWaitingForReg();
		$usernameS	= trim( (string)(XWB_plugin::V('p:siteBindName')) );
		$password	= trim( (string)(XWB_plugin::V('p:bindPwd')) );

		
		$username	= XWB_plugin::convertEncoding($usernameS, "UTF8", XWB_S_CHARSET);
		if( !empty($questionanswerS) ){
			$questionanswer		= XWB_plugin::convertEncoding($questionanswerS, "UTF8", XWB_S_CHARSET);
		}else{
			$questionanswer = '';
		}
		
		$uid = 0;
				if (empty($username))				{$uid = -102;}
		if (empty($password))				{$uid = -103;}
		
		$msg = '';
				if( $uid == 0  ){
			$verify = XWB_plugin::O('siteUserVerifier');
			$verifyresult = $verify->verify ( $username, $password, $questionid, $questionanswer );
			$uid = $verifyresult[0];
		}
		
		if( $uid > 0 ){
			$wbApi	= XWB_plugin::getWB();
			$uInfo	= $wbApi->verifyCredentials();
			
			$db	 = XWB_plugin::getDB();
						$bInfo = $db->fetch_first("SELECT * FROM ".XWB_S_TBPRE."xwb_bind_info  WHERE sina_uid='".$uInfo['id']."' or uid='".$uid ."'");
			if ( !empty($bInfo) && is_array($bInfo) ){
				$uid = -201;
			}else{
				$sess = XWB_plugin::getUser();
				$sess->setInfo('sina_uid', $uInfo['id']);
				$last_key = $sess->getOAuthKey(true);
			
				$inData = array();
				$inData['uid'] 		= $uid;
				$inData['sina_uid'] = $uInfo['id'];
				$inData['token']	= $last_key['oauth_token'];
				$inData['tsecret']	= $last_key['oauth_token_secret'];
				$inData['profile']	= '[]';
				$sqlF = array();
				$sqlV = array();
				foreach ($inData as $k=>$v){
					$sqlF[] = "`".$k."`";
					$sqlV[] = "'".mysql_escape_string($v)."'";
				}
			
				$sql = "INSERT INTO ".XWB_S_TBPRE."xwb_bind_info  (".implode(",",$sqlF).") VALUES (".implode(",",$sqlV).") ;";
				$rst = $db->query($sql, 'UNBUFFERED');
			
				require_once XWB_P_ROOT. '/lib/xwbSite.inc.php';
				xwb_setSiteUserLogin($uid);
				
				setcookie('xwb_tips_type','',0);
				$sess->setInfo('waiting_site_reg', '0');

				Load::model('misc')->update_account_bind_info($uid, '', '', 1);
			
				$msg = "绑定&nbsp;" . XWB_S_TITLE .  "&nbsp;帐号成功。下次你可以继续使用新浪微博帐号登录使用&nbsp;" . XWB_S_TITLE .  "&nbsp;。";
				$msg.="<br>绑定帐号:  <em>".htmlspecialchars($usernameS)."</em>  ";
				
								$sess->appendStat('bind', array( 'uid' => $uInfo['id'], 'type' => 1 ));
				
			}
		}
		
		if( $uid <= 0 ){
			$msg = $this->_getBindTip($uid);
		}
		
		$this->_oScript('xwbSetTips',array($uid, $msg, 1));
		
	}
	
	
	function _getRegTip( $code ){
		$tips = array(
			'-1' => '用户名不合法，允许输入3~15位的英文或者数字',
			'-2' => '用户名中包含了敏感字符',
			'-3' => '此帐户已被使用，请重新输入一个',
			'-4' => '邮箱格式错误',
			'-5' => '邮箱不允许注册',
			'-6' => '邮箱已经被注册',
            '-7' => '昵称不允许使用',
            '-8' => '昵称已经被使用',
            

			'-101' => '用户邮箱不能为空',
			'-102' => '用户帐号不能为空',
			'-103' => '密码不能为空',
			'-104' => '密码不一致',
            '-105' => '昵称不能为空',
		);
		
		$code = (string)$code;
		return isset($tips[$code]) ? $tips[$code] : '未知错误';
	}
	
	
	function _getBindTip( $code ){
		$tips = array(
			'-1' => '用户不存在',
			'-2' => '密码错误',
			'-3' => '安全提问错误',
			'-4' => '用户在&nbsp;' . XWB_S_TITLE .  '&nbsp;未激活',
			'-5' => '禁止登录',
		
			'-102' => '用户帐号不能为空',
			'-103' => '密码不能为空',
			
			'-201' => '上述帐号昵称已绑定其他新浪微博，更更换其他账户，或先登录后解除绑定',
		);
		
		$code = (string)$code;
		return isset($tips[$code]) ? $tips[$code] : '未知错误';
	}
	
	
	
	
	function _logFaceSyncResult( $code ){
			
		$tips = array(
			'0' => '头像成功同步',
			'-1' => '初始化失败（无法获取新浪用户信息）',
			'-2' => '传uid参数错误（小于1）',
			'-3' => '无法获取服务器上的头像',
			'-4' => '服务器返回错误数据（非头像数据或者给出来的头像太小）；或者临时目录权限问题导致无大头像文件',
			'-5' => '服务器没有加载GD库，无法进行头像同步操作',
		
			'-10' => '本地编码失败（一般是无法生成3种头像文件所致）',
			'-11' => '与UC进行HTTP通讯出错',
			'-12' => 'UC返回头像编码解码失败代码',
			'-13' => 'UC返回头像上传失败代码',
			'-14' => 'UC返回找寻传参uid失败代码',
			'-15' => 'UC返回未知错误代码',
		
			'-20' => '要复制的中等头像不存在',
			'-21' => XWB_S_TITLE . '&nbsp;设置不允许该用户所在用户组上传头像',
			'-22' => '复制头像到&nbsp;' . XWB_S_TITLE . '&nbsp;头像目录失败',
		);
		
		$faceSyncResultLog = isset($tips[$code]) ? $tips[$code] : '未知错误代码';
			
		if( defined('XWB_DEV_LOG_ALL_RESPOND') && XWB_DEV_LOG_ALL_RESPOND == true ){
			XWB_plugin::LOG("[FACE SYNC RESULT]\t{$code}\t{$faceSyncResultLog}");
		}
			
		return $faceSyncResultLog;
			
	}
	
	function _oScript($func,$ret){
		echo '<script>';
		echo "parent.".$func."(".json_encode($ret).");";
		echo '</script>';
		exit;
	}
	
		function _chkIsWaitingForReg(){
		$sess = XWB_plugin::getUser ();
		$isReg = $sess->getInfo ( 'waiting_site_reg' );
		if ( XWB_S_UID || empty ( $isReg )) {
			$sess->clearToken();
			$this->_oScript ( 'parent.XWBcontrol.close', 'reg' );
		}
	}
	
		function bind(){
		if( !XWB_plugin::pCfg('is_account_binding') ){
			XWB_plugin::showError('新浪微博绑定功能已经关闭！');
		}
		
		if ( XWB_S_UID > 0 && XWB_plugin::isUserBinded() ) {
			$xwb_user = XWB_plugin::getUser();
			$sina_id = $xwb_user->getInfo('sina_uid');
			$wb = XWB_plugin::getWB();
			$wb->is_exit_error = false;
			$sina_user_info = $wb->getUserShow($sina_id);
			
			if( isset($sina_user_info['error_code']) || isset($sina_user_info['error']) ){
				include XWB_P_ROOT.'/tpl/xwb_cenbind_error.tpl.php';
				
			}else{
			 
			    $db = XWB_plugin::getDB();                
                $share = XWB_plugin::V("R:share");
                $share_msg = '';
                   
                if ($share)
                {
                    $bind_info = $db->fetch_first("select * from ".XWB_S_TBPRE."xwb_bind_info where `uid`='".XWB_S_UID."'");
                    $share_time = $bind_info['share_time'];
                    if(!$share_time)
                    {
                        $share_time = time();                    
                        $db->query("update ".XWB_S_TBPRE."xwb_bind_info set `share_time`='".$share_time."' where `uid`='".XWB_S_UID."'");
                        
						Load::model('misc')->update_account_bind_info(XWB_S_UID, '', '', 1);

                        $share_msg = "<img src='".(XWB_plugin::baseUrl(). XWB_plugin::URL('&code=enter&share_time='.$share_time))."' width='0' height='0' />";                    
                    }                                            
                }
                else
                {
                     
    				$skip_share = XWB_plugin::V("R:skip_share");
    				if(!$skip_share)
    				{					
    					$bind_info = $db->fetch_first("select * from ".XWB_S_TBPRE."xwb_bind_info where `uid`='".XWB_S_UID."'");
    
    					if(!$bind_info['share_time'])
    					{						
    						include XWB_P_ROOT.'/tpl/xwb_cenbind_share.tpl.php';
    
    						exit;
    					}
    				}
                }           
                    

				$screen_name = $sina_user_info['screen_name'];
				$profile = XWB_plugin::O('xwbUserProfile');
				$setting = $profile->get('bind_setting', 1);
                $tojishigou = $profile->get('synctopic_tojishigou', 0);
                $reply_tojishigou = $profile->get('syncreply_tojishigou', 0);
				include XWB_P_ROOT.'/tpl/xwb_cenbind_on.tpl.php';
			}			
		} else {
			include XWB_P_ROOT.'/tpl/xwb_cenbind_off.tpl.php';
		}
	}

	
	function unbind() {
		if( XWB_S_UID < 1 || !XWB_plugin::pCfg('is_account_binding') ){
			XWB_plugin::showError('新浪微博绑定功能已经关闭！');
		}
		
		$db = XWB_plugin::getDb();
		$sql = 'DELETE FROM ' . XWB_S_TBPRE . 'xwb_bind_info WHERE `uid`=' . XWB_S_UID;
		$db->query($sql);

		Load::model('misc')->update_account_bind_info(XWB_S_UID, '', '', 1);

		echo '';
	}

	
	function bindTopic() {
		if(  XWB_S_UID < 1 || !XWB_plugin::pCfg('is_account_binding') ){
			XWB_plugin::showError('新浪微博绑定功能已经关闭！');
		}
		
		$setting = XWB_plugin::V('p:setting');
        $tojishigou = XWB_plugin::V('p:tojishigou');
        $reply_tojishigou = XWB_plugin::V('p:reply_tojishigou');        
		$profile = XWB_plugin::O('xwbUserProfile');
		$profile->set(array('bind_setting'=>(int)$setting,'synctopic_tojishigou'=>(int)$tojishigou,'syncreply_tojishigou'=>(int)$reply_tojishigou,));
				
		Load::model('misc')->update_account_bind_info(XWB_S_UID, '', '', 1);
		
		echo '<script>parent.tips();</script>';
	}

    
    function share()
    {
        if( ! XWB_plugin::pCfg('is_rebutton_display')) XWB_plugin::showError('新浪微博分享功能已经关闭！');

        
        $rst = $this->_getUserInfo();
        if (isset($rst['error_no']) && 0 < $rst['error_no']) $this->_showTip($rst['error']);

        
		$tid = intval(XWB_plugin::V('g:tid'));
		if (empty($tid)) $this->_showTip('错误:微博ID不能为空.', $rst);

        
        $xp_publish = XWB_plugin::N('xwb_plugins_publish');
        $shareData = $xp_publish->forShare($tid);
        if (empty($shareData)) $this->_showTip('错误:无法获取微博内容.', $rst);

        
        if (isset($_SESSION['forshare'])) unset($_SESSION['forshare']);

        
        $_SESSION['forshare'] = TRUE;

        include XWB_P_ROOT.'/tpl/share.tpl.php';
    }

    
    function doShare()
    {
        if( ! XWB_plugin::pCfg('is_rebutton_display')) XWB_plugin::showError('新浪微博资料页功能已经关闭！');

        
        if ( ! isset($_SESSION['forshare']) || TRUE !== $_SESSION['forshare']) XWB_plugin::showError('禁止外部转发');

        
        unset($_SESSION['forshare']);
        
        
        $shareTime = intval(XWB_plugin::pCfg('wbx_share_time'));
        if ($shareTime >= time() - intval(@$_SESSION['sharetime'])) XWB_plugin::showError("转发过快，转发间隔为 {$shareTime} 秒");
        
        
        $rst = $this->_getUserInfo();
        if (isset($rst['error_no']) && 0 < $rst['error_no']) $this->_showTip($rst['error']);

        
        $message = trim(strval(XWB_plugin::V('p:message')));
        $pic = trim(strval(XWB_plugin::V('p:share_pic')));
		if (empty($message)) $this->_showTip('错误:转发信息不能为空.', $rst);

        
        $xp_publish = XWB_plugin::N('xwb_plugins_publish');
        $ret = $xp_publish->sendShare($message, $pic);

        
        $_SESSION['sharetime'] = time();

        
        if ($ret === false || $ret === null) $this->_showTip('错误:系统错误!', $rst);
        if (isset($ret['error_code']) && isset($ret['error']))
        {
            $error_code_se = substr($ret['error'], 0, 5);
            if ('400' == $ret['error_code'] && '40025' == $error_code_se)
                $ret['error'] = '错误:不能发布相同的微博!';
            else
                $ret['error'] = '错误:系统错误!';
            $this->_showTip($ret['error'], $rst);
        }

        $this->_showTip('转发成功！', $rst);
    }

    
    function _getUserInfo()
    {
        
		$sina_id = XWB_plugin::getBindInfo('sina_uid');
        if ( ! $sina_id)
            return array('error_no' => '10001', 'error' => '错误:用户未绑定.');

        
        $wbApi = XWB_plugin::getWB();
        $keys = $this->_getTockenFromDB($sina_id);
        if (empty($keys))
            return array('error_no' => '10002', 'error' => '错误:无法获取绑定信息.');

        $wbApi->setTempToken($keys['oauth_token'], $keys['oauth_token_secret']);
        $wbApi->is_exit_error = false;
        $rst = $wbApi->getUserShow($sina_id);

        if ( ! is_array($rst) || isset($rst['error']) || empty($rst['id']))
            return array('error_no' => '10003', 'error' => "错误:无法从接口中获取用户信息.");

        return $rst;
    }

    
    function _showTip($tipMsg, $rst = array())
    {
        include XWB_P_ROOT.'/tpl/share_msg.tpl.php';
        exit;
    }

    
    function _showErr($errMsg)
    {
		@header('Content-Type: text/html;charset=utf-8');
        exit($errMsg);
    }
}


?>
