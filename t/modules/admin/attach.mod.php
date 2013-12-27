<?php

/**
 * 文件名： attach.mod.php
 * 版本号： 1.0.0
 * 作  者：　狐狸 <foxis@qq.com>
 * 修改时间： 2012年2月14日
 * 功能描述： attach for JishiGou
 * 版权所有： Powered by JishiGou attach 1.0.0 (a) 2005 - 2099 Cenwor Inc.
 * 公司网站： http://cenwor.com
 * 产品网站： http://jishigou.net
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}


class ModuleObject extends MasterObject
{


    
	function ModuleObject($config)
	{
		$this->MasterObject($config);
        $this->Execute();
	}

	function Execute()
	{
		switch($this->Code)
		{
			case 'do_modify_setting':
                {
                    $this->DoModifySetting();
                    break;
                }
			default :
    			{
    				$this->Main();
    				break;
    			}
		}
	}

	function Main()
	{
        $attach = $attach_config = ConfigHandler::get('attach');
        if(!$attach_config)
        {
            $attach_config = array
            (
                'enable' => 1,
				'qun_enable' => 1,
            	'request_size_limit' => 2000,
				'request_files_limit' => 3,
            );
            
            ConfigHandler::set('attach',$attach_config);
        }	   
	   
        Load::lib('form');
        $FormHandler = new FormHandler();	   
       
		$attach_enable_radio = $FormHandler->YesNoRadio('attach[enable]',(int) ($attach_config['enable'] && $this->Config['attach_enable']));
		$qun_attach_enable_radio = $FormHandler->YesNoRadio('attach[qun_enable]',(int) ($attach_config['qun_enable'] && $this->Config['qun_attach_enable']));
 
        include($this->TemplateHandler->Template('admin/attach'));
	}
    
    function DoModifySetting()
    {        
        $attach = $this->Post['attach'];        
        $attach_config_default = $attach_config = ConfigHandler::get('attach');
        $attach_config['enable'] = ($attach['enable'] ? 1 : 0);
		$attach_config['qun_enable'] = ($attach['qun_enable'] ? 1 : 0);
        $attach_config['request_size_limit'] = min(max(1,(int)$attach['request_size_limit']),5120);		
        $attach_config['request_files_limit'] = min(max(1,(int)$attach['request_files_limit']),5);
        
        if($attach_config_default != $attach_config)
        {
        	ConfigHandler::set('attach',$attach_config);
        }
        
        if($attach_config['enable']!=$this->Config['attach_enable'])
        {
            ConfigHandler::update('attach_enable', $attach_config['enable']);
        }
		
		if($attach_config['qun_enable']!=$this->Config['qun_attach_enable'])
        {
            ConfigHandler::update('qun_attach_enable', $attach_config['qun_enable']);
        }

		if($attach_config['request_size_limit']!=$this->Config['attach_size_limit'])
        {
            ConfigHandler::update('attach_size_limit', $attach_config['request_size_limit']);
        }

		if($attach_config['request_files_limit']!=$this->Config['attach_files_limit'])
        {
            ConfigHandler::update('attach_files_limit', $attach_config['request_files_limit']);
        }
        
        $this->Messager("修改成功");
    }
}

?>
