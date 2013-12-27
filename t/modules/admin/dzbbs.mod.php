<?php
/**
 * 文件名： dzbbs.mod.php
 * 版本号： 1.0.0
 * 作  者：　狐狸 <foxis@qq.com>
 * 修改时间： 2011年10月14日
 * 功能描述： dzbbs for JishiGou
 * 版权所有： Powered by JishiGou dzbbs 1.0.0 (a) 2005 - 2099 Cenwor Inc.
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
			case 'dzbbs_save':
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
        $dzbbs = $dzbbs_config = ConfigHandler::get('dzbbs');
        if(!$dzbbs_config)
        {
            $dzbbs = $dzbbs_config = array
            (
                'enable' => 0,
            	'db_host' => 'localhost',
				'db_name' => 'discuz',
				'db_user' => 'root',
				'db_pass' => 'root',
				'db_port' => '3306',
				'db_pre'  => 'pre_',
				'charset' => 'gbk',
				'db_url'  => 'http:/'.'/',
				'dz_ver'  => 'dzx',
            );
            
            ConfigHandler::set('dzbbs',$dzbbs_config);
        }	   
	   
        Load::lib('form');
        $FormHandler = new FormHandler();	   
       
		$dzbbs_enable = $FormHandler->YesNoRadio('dzbbs[enable]',(int) ($dzbbs_config['enable']));
		$dzbbs_charset = $FormHandler->Radio('dzbbs[charset]',array(array("name"=>"GBK","value"=>"gbk"),array("name"=>"UTF-8","value"=>"utf8")),$dzbbs_config['charset']);
		$dzbbs_dzver = $FormHandler->Select('dzbbs[dz_ver]',array(array("name"=>"Discuz! 系列","value"=>"dz"),array("name"=>"Discuz! X系列","value"=>"dzx")),$dzbbs_config['dz_ver']);
        
		include($this->TemplateHandler->Template('admin/dzbbs'));
	}
    
    function DoSave()
    {
       
        $dzbbs = $this->Post['dzbbs'];
        
        $dzbbs_config_default = $dzbbs_config = ConfigHandler::get('dzbbs');
        $dzbbs_config['enable']  = ($dzbbs['enable'] ? 1 : 0);
		$dzbbs_config['db_host'] = $dzbbs['db_host'];
		$dzbbs_config['db_name'] = $dzbbs['db_name'];
		$dzbbs_config['db_user'] = $dzbbs['db_user'];
		$dzbbs_config['db_pass'] = $dzbbs['db_pass'];
		$dzbbs_config['db_port'] = $dzbbs['db_port'];
		$dzbbs_config['db_pre']  = $dzbbs['db_pre'];
		$dzbbs_config['charset'] = $dzbbs['charset'];
		$dzbbs_config['db_url']  = $dzbbs['db_url'];
		$dzbbs_config['dz_ver']  = $dzbbs['dz_ver'];

		if($dzbbs_config['enable']){
			$table_m = ($dzbbs['dz_ver'] == 'dzx') ? $dzbbs['db_pre'].'common_member' : $dzbbs['db_pre'].'members';
			include_once(ROOT_PATH.'./api/uc_api_db.php');
			$dz_db = new JSG_UC_API_DB();
			@$dz_db->connect($dzbbs['db_host'],$dzbbs['db_user'],$dzbbs['db_pass'],$dzbbs['db_name'],$dzbbs['charset'],1,$dzbbs['db_pre']);
			if(!($dz_db->link) || !($dz_db->query("SHOW COLUMNS FROM {$table_m}",'SILENT'))){
				$this->Messager("无法连接Discuz数据库，请检查您填写的Discuz数据库配置信息是否正确.");exit;
			}
		}
        
        if($dzbbs_config_default != $dzbbs_config)
        {
        	ConfigHandler::set('dzbbs',$dzbbs_config);
        }

		if($dzbbs_config['enable']!=$this->Config['dzbbs_enable'])
        {
            $config = array();
			$config['dzbbs_enable'] = $dzbbs_config['enable'];
			if($dzbbs_config['enable'] == 1){
				$config['ucenter_enable'] = 1;
				$config['pwbbs_enable'] = 0;
				$config['phpwind_enable'] = 0;
			}
            
            ConfigHandler::update($config);
        }

        $this->Messager("修改成功");
    }
}

?>
