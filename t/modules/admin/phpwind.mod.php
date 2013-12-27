<?php
/**
 * 文件名： phpwind.mod.php
 * 版本号： 1.0
 * 创建时间： 2011年9月19日 11时32分07秒
 * 修改时间：2011年11月11日 11时11分11秒
 * 作者： 狐狸<foxis@qq.com>
 * 功能描述： 整合phpwind
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
		ob_start();
		switch($this->Code)
		{
			case 'phpwind_save':
				$this->DoSave();
				break;

			default:
				$this->Main();
				break;
		}
		$body = ob_get_clean();

		$this->ShowBody($body);


	}

	function Main()
	{
		if(!is_file(ROOT_PATH . './api/pw_client/uc_client.php')){
			$this->Messager('Phpwind的客户端文件 <b>' . ROOT_PATH . './api/pw_client/uc_client.php' . "</b> 不存在，请检查",null);
		}
		if(!is_file(ROOT_PATH . 'pw_api.php')){
			$this->Messager('Phpwind的api文件 <b>' . ROOT_PATH . 'pw_api.php' . "</b> 不存在，请检查",null);
		}

		$phpwind = $phpwind_config = ConfigHandler::get('phpwind');

		if(!$phpwind_config)
        {
            $phpwind = $phpwind_config = array
            (
                'enable' => 0,
				'face' => 0,
				'bbs' => 0,
            	'pw_db_host' => 'localhost',
				'pw_db_name' => 'phpwind',
				'pw_db_user' => 'root',
				'pw_db_password' => '',
				'pw_db_table_prefix'  => 'pw_',
				'pw_db_charset' => 'gbk',
				'pw_pptkey' => '',
				'pw_key' => '',
				'pw_api'  => 'http:/'.'/',
				'pw_charset'  => 'gbk',
				'pw_ip'  => '',
				'pw_app_id'  => '',
            );
            
            ConfigHandler::set('phpwind',$phpwind_config);
        }	   

		Load::lib('form');
		$pw_enable_radio = FormHandler::YesNoRadio('phpwind[enable]',(bool) $phpwind['enable']);
		$pw_db_charset = FormHandler::Radio('phpwind[pw_db_charset]',array(array("name"=>"GBK","value"=>"gbk"),array("name"=>"UTF-8","value"=>"utf8")),$phpwind_config['pw_db_charset']);
		$pw_charset = FormHandler::Radio('phpwind[pw_charset]',array(array("name"=>"GBK","value"=>"gbk"),array("name"=>"UTF-8","value"=>"utf8")),$phpwind_config['pw_charset']);

		$pw_face_radio = FormHandler::YesNoRadio('phpwind[face]',(bool) $phpwind['face']);
		$pw_bbs_radio = FormHandler::YesNoRadio('phpwind[bbs]',(bool) $phpwind['bbs']);
		include $this->TemplateHandler->Template('admin/phpwind');
	}

	function DoSave()
	{
		if(!is_file(ROOT_PATH . './api/pw_client/uc_client.php')) {
			$this->Messager('Phpwind的客户端文件 <b>' . ROOT_PATH . './api/pw_client/uc_client.php' . "</b> 不存在，请检查");
		}
		if(!is_file(ROOT_PATH . 'pw_api.php')) {
			$this->Messager('Phpwind的api文件 <b>' . ROOT_PATH . 'pw_api.php' . "</b> 不存在，请检查");
		}

		$phpwind = $this->Post['phpwind'];
        
        $phpwind_config_default = $phpwind_config = ConfigHandler::get('phpwind');
        $phpwind_config['enable']  = ($phpwind['enable'] ? 1 : 0);
		$phpwind_config['face']  = ($phpwind['face'] ? 1 : 0);
		$phpwind_config['bbs']  = ($phpwind['bbs'] ? 1 : 0);
		$phpwind_config['pw_db_host'] = $phpwind['pw_db_host'];
		$phpwind_config['pw_db_name'] = $phpwind['pw_db_name'];
		$phpwind_config['pw_db_user'] = $phpwind['pw_db_user'];
		$phpwind_config['pw_db_password'] = $phpwind['pw_db_password'];
		$phpwind_config['pw_ip'] = $phpwind['pw_ip'];
		$phpwind_config['pw_db_table_prefix']  = $phpwind['pw_db_table_prefix'];
		$phpwind_config['pw_db_charset'] = $phpwind['pw_db_charset'];
		$phpwind_config['pw_charset'] = $phpwind['pw_charset'];
		$phpwind_config['pw_pptkey'] = $phpwind['pw_pptkey'];
		$phpwind_config['pw_key'] = ($phpwind['pw_key'] ? $phpwind['pw_key'] : '');
		$phpwind_config['pw_api']  = $phpwind['pw_api'];
		$phpwind_config['pw_app_id']  = ($phpwind['pw_app_id'] ? $phpwind['pw_app_id'] : 0);

		if($phpwind_config['enable']){
			include_once(ROOT_PATH.'./api/uc_api_db.php');
			$pw_db = new JSG_UC_API_DB();
			@$pw_db->connect($phpwind['pw_db_host'],$phpwind['pw_db_user'],$phpwind['pw_db_password'],$phpwind['pw_db_name'],$phpwind['pw_db_charset'],1,$phpwind['pw_db_table_prefix']);
			if(!($pw_db->link) || !($pw_db->query("SHOW COLUMNS FROM {$phpwind['pw_db_table_prefix']}members",'SILENT'))){
				$this->Messager("无法连接PhpWind数据库，请检查您填写的PhpWind数据库配置信息是否正确.");exit;
			}
		}
        
        if($phpwind_config_default != $phpwind_config)
        {
        	ConfigHandler::set('phpwind',$phpwind_config);
        }

		if($phpwind_config['enable']!=$this->Config['phpwind_enable'] || $phpwind_config['bbs']!=$this->Config['pwbbs_enable'])
        {
            $config = array();
			$config['phpwind_enable'] = $phpwind_config['enable'];
			$config['pwbbs_enable'] = $phpwind_config['bbs'];
			if($phpwind_config['enable'] == 1){
				$config['ucenter_enable'] = 0;
				$config['dzbbs_enable'] = 0;
			}
            ConfigHandler::update($config);
        }

		$this->Messager("配置成功");
	}
}

?>