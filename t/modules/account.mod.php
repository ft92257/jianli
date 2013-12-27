<?php
/**
 * 文件名：account.mod.php
 * 版本号：1.0
 * 作     者：狐狸<foxis@qq.com>
 * 创建时间：2011年9月14日
 * 功能描述: 帐号登录前台模块
 * @todo 增加人人、开心接口 2011年9月22日
 */


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{	
	var $ID = '';

	function ModuleObject($config)
	{
		$this->MasterObject($config);
		
		$this->ID = (int) ($this->Post['id'] ? $this->Post['id'] : $this->Get['id']);
		
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();		
		switch ($this->Code) 
		{
			;
				
			default:
				$this->Main();
		}
		$body=ob_get_clean();
		
		$this->ShowBody($body);
	}

	function Main()
	{
		$member = jsg_member_info(MEMBER_ID);
		$act_list = array();  
		$act_list['qqwb'] = '腾讯微博';        
		$act_list['sina'] = '新浪微博';
		$act_list['yy'] = 'YY帐号';
		$act_list['renren'] = '人人帐号';
		$act_list['kaixin'] = '开心帐号';
		if($this->Config['fjau_enable']) {
			$act_list['fjau'] = 'FJAU帐号';
		}
		$act = isset($act_list[$this->Code]) ? $this->Code : 'qqwb';
		$this->Code = $act;

        
        if('qqwb' == $act)
        {
            $qqwb_init = qqwb_init($this->Config);
            
            if($qqwb_init)
            {
	            Load::lib('form');
	            $FormHandler = new FormHandler();
	            
	            $qqwb = ConfigHandler::get('qqwb');
	            
	            $qqwb_bind_info = qqwb_bind_info(MEMBER_ID);
	            
	            if($qqwb_bind_info)
	            {
	                if($qqwb['is_synctopic_toweibo'])
	                {
	                    $synctoqq_radio = $FormHandler->YesNoRadio('synctoqq',(int) $qqwb_bind_info['synctoqq']);
	                }                
	            }
            }
                        
            ;
        }

		
		elseif ('sina' == $act)
		{
			$sina_init = sina_init($this->Config);
			
			if($sina_init) {
				$sina = ConfigHandler::get('sina');				
				if($sina['oauth2_enable']) {
					$sina_bind_info = sina_weibo_bind_info(MEMBER_ID);
					if($sina_bind_info) {
						$profiles = $sina_bind_info['profiles'];
						if($sina['is_synctopic_toweibo']) {
							$synctoweibo_radio = Load::lib('form', 1)->YesNoRadio('bind_setting', (int) sina_weibo_bind_setting(MEMBER_ID));
						}
						if($sina['is_synctopic_tojishigou']) {
							$synctopic_tojishigou_radio = Load::lib('form', 1)->YesNoRadio('synctopic_tojishigou', (int) $profiles['synctopic_tojishigou']);
						}
						if($sina['is_syncreply_tojishigou']) {
							$syncreply_tojishigou_radio = Load::lib('form', 1)->YesNoRadio('syncreply_tojishigou', (int) $profiles['syncreply_tojishigou']);
						}
					}					
				} else {
					$profile_bind_message = '';
		
					$xwb_start_file = ROOT_PATH . 'include/xwb/sina.php';
		
					if (!is_file($xwb_start_file))
					{
						$profile_bind_message = '&#25554;&#20214;&#25991;&#20214;&#20002;&#22833;&#65292;&#26080;&#27861;&#21551;&#21160;&#65281;';
					}
					else
					{
						require($xwb_start_file);
		
						$profile_bind_message = '<a href="javascript:XWBcontrol.bind()">&#22914;&#26524;&#30475;&#19981;&#21040;&#26032;&#28010;&#24494;&#21338;&#32465;&#23450;&#35774;&#32622;&#31383;&#21475;&#65292;&#35831;&#28857;&#20987;&#36825;&#37324;&#21551;&#21160;&#12290;</a>';
		
						$GLOBALS['xwb_tips_type'] = 'bind';
		
						$profile_bind_message .= jsg_sina_footer();
					}
				}
			}

			;
		}
		
		
		elseif ('yy' == $act)
		{
            $yy_init = yy_init($this->Config);
            
            if($yy_init)
            {
            	$yy_bind_info = yy_bind_info(MEMBER_ID);
            }
            
            ;
		}
		
		
		elseif ('renren' == $act)
		{
            $renren_init = renren_init($this->Config);
            
            if($renren_init)
            {
            	$renren_bind_info = renren_bind_info(MEMBER_ID);
            }
            
            ;
		}
		
		
		
		elseif ('kaixin' == $act)
		{
			$kaixin_init = kaixin_init($this->Config);
			
			if($kaixin_init)
			{
				$kaixin_bind_info = kaixin_bind_info(MEMBER_ID);
			}
			
			;
		}
		
		
		
		elseif ('fjau' == $act)
		{
			$fjau_init = fjau_init($this->Config);
			
			if($fjau_init)
			{
				$fjau_bind_info = fjau_bind_info(MEMBER_ID);
			}
			
			;
		}
		
		
		else 
		{
			;
		}
		

		$this->Title = $act_list[$act];
		include($this->TemplateHandler->Template('account_main'));
	}
	
}


?>
