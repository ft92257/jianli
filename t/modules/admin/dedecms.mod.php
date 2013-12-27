<?php
/**
 * 文件名： dedecms.mod.php
 * 版本号： 1.0.0
 * 作  者：　狐狸 <foxis@qq.com>
 * 修改时间： 2011年10月14日
 * 功能描述： dedecms for JishiGou
 * 版权所有： Powered by JishiGou dedecms 1.0.0 (a) 2005 - 2099 Cenwor Inc.
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
			case 'dedecms_save':
                {
                    $this->DoSave();
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
        $dedecms = $dedecms_config = ConfigHandler::get('dedecms');
        if(!$dedecms_config)
        {
            $dedecms = $dedecms_config = array
            (
                'enable' => 0,
            	'db_host' => 'localhost',
				'db_name' => 'dedecms',
				'db_user' => 'root',
				'db_pass' => '',
				'db_port' => '3306',
				'db_pre'  => 'dede_',
				'charset' => 'gbk',
				'db_url'  => 'http:/'.'/',
            );
            
            ConfigHandler::set('dedecms',$dedecms_config);
        }	   
	   
        Load::lib('form');
        $FormHandler = new FormHandler();	   
       
		$dedecms_enable = $FormHandler->YesNoRadio('dedecms[enable]',(int) ($dedecms_config['enable']));
		$dedecms_charset = $FormHandler->Radio('dedecms[charset]',array(array("name"=>"GBK","value"=>"gbk"),array("name"=>"UTF-8","value"=>"utf8")),$dedecms_config['charset']);
		include($this->TemplateHandler->Template('admin/dedecms'));
	}
    
    function DoSave()
    {
       
        $dedecms = $this->Post['dedecms'];        
        $dedecms_config_default = $dedecms_config = ConfigHandler::get('dedecms');
        $dedecms_config['enable']  = ($dedecms['enable'] ? 1 : 0);
		$dedecms_config['db_host'] = $dedecms['db_host'];
		$dedecms_config['db_name'] = $dedecms['db_name'];
		$dedecms_config['db_user'] = $dedecms['db_user'];
		$dedecms_config['db_pass'] = $dedecms['db_pass'];
		$dedecms_config['db_port'] = $dedecms['db_port'];
		$dedecms_config['db_pre']  = $dedecms['db_pre'];
		$dedecms_config['charset'] = $dedecms['charset'];
		$dedecms_config['db_url']  = $dedecms['db_url'];

		if($dedecms_config['enable']){
			include_once(ROOT_PATH.'./api/uc_api_db.php');
			$dede_db = new JSG_UC_API_DB();
			@$dede_db->connect($dedecms['db_host'],$dedecms['db_user'],$dedecms['db_pass'],$dedecms['db_name'],$dedecms['charset'],1,$dedecms['db_pre']);
			if(!($dede_db->link) || !($dede_db->query("SHOW COLUMNS FROM {$dedecms['db_pre']}member",'SILENT'))){
				$this->Messager("无法连接DedeCMS数据库，请检查您填写的DedeCMS数据库配置信息是否正确.");exit;
			}
		}
        
        if($dedecms_config_default != $dedecms_config)
        {
        	ConfigHandler::set('dedecms',$dedecms_config);
        }

		if($dedecms_config['enable']!=$this->Config['dedecms_enable'])
        {
            $config = array();
			$config['dedecms_enable'] = $dedecms_config['enable']; 
			
            ConfigHandler::update($config);
        }

        $this->Messager("修改成功");
    }
}

?>
