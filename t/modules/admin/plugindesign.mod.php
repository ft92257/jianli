<?php
/**
 * 文件名：plugindesign.mod.php
 * 版本号：1.0
 * 最后修改时间：2011-08-30
 * 作者：狐狸<foxis@qq.com>
 * 功能描述：插件设计
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

include_once(ROOT_PATH . 'include/function/api.func.php');

class ModuleObject extends MasterObject
{
	
	var $Config = array(); 	
	function ModuleObject(& $config)
	{
		$this->hookall_config = ConfigHandler::get('hookall');
		$this->navigation_config = ConfigHandler::get('navigation');
		$this->MasterObject($config);		
		$this->Execute();		
	}
	
	function Execute()
	{
		ob_start();
		$id = jget('id','int');
		$sql = "SELECT pluginid, name FROM `".TABLE_PREFIX."plugin` WHERE `pluginid` = '$id'";
		$query = $this->DatabaseHandler->Query($sql);
		$plugin = $query->GetRow();
		if(PLUGINDEVELOPER > 0){
		if(empty($plugin['pluginid']))
		{
			$this->Messager("操作失败（找不到您要设计的插件）");
		}
		$this->pluginid = $id;
		$this->pluginconfig = "插件设计";
		$this->pluginname = $plugin['name'];
		switch($this->Code) 
		{
			case 'design':
				$this->Design();
				break;
			case 'adddesign':
				$this->Adddesign();
				break;
			case 'modules':
				$this->Modules();
				break;
			case 'addmodules':
				$this->Addmodules();
				break;
			case 'vars':
				$this->Vars();
				break;
			case 'addvar':
				$this->Addvar();
				break;
			case 'export':
				$this->Export();
				break;
			case 'config':
				$this->config();
				break;
			case 'addconfig':
				$this->Addconfig();
				break;
			default:
				$this->Main();
		}
		}
		$body = ob_get_clean();
		
		$this->ShowBody($body);
		
	}

	function Main()
	{
		include($this->TemplateHandler->Template('admin/plugin_design'));
	}
	
	function Design()
	{
		$id = is_numeric($_GET['id']) ? $_GET['id'] : 0;
		$sql = "SELECT name, identifier, version, copyright, description, directory FROM ".TABLE_PREFIX."plugin WHERE `pluginid` = '$id'";
		$query = $this->DatabaseHandler->Query($sql);
		$plugin_info = $query->GetRow();
		$infos = true;
				include($this->TemplateHandler->Template('admin/plugin_design_editor'));
	}
	
	function Adddesign()
	{		
		$id = is_numeric(trim($this->Post['id'])) ? trim($this->Post['id'])  : 0;
		$data = array();
		$data['name'] = cutstr(trim($this->Post['plugin_name']),40);
		$data['identifier'] = cutstr(trim($this->Post['identifier']),40);
		$data['version'] = cutstr(trim($this->Post['version']),10);
		$data['copyright'] = cutstr(trim($this->Post['copyright']),80);
		$data['description'] = cutstr($this->Post['description'],100);
		$data['directory'] = $data['identifier'].'/';
				if(empty($data['name'])){
			$this->Messager("插件名不能为空");
		}
		$this->DatabaseHandler->SetTable(TABLE_PREFIX.'plugin');
		$is_exists = $this->DatabaseHandler->Select('', "identifier='{$data['identifier']}'");
	
		if($is_exists != false || empty($data['identifier']) || !ctype_alpha($data['identifier'])){
			$this->Messager("插件唯一识别符不合法，或不能为空");	
		}
		if(empty($data['version'])){
			$this->Messager("插件版本号不能为空");
		}
		if(empty($data['copyright'])){
			$this->Messager("版权信息不能为空");
		}
		$this->DatabaseHandler->SetTable(TABLE_PREFIX.'plugin');
		$result = $this->DatabaseHandler->Update($data,"`pluginid`='$id'");
		$this->Messager("插件设计完善成功", 'admin.php?mod=plugindesign&code=design&id='.$id);
	}

	function Config()
	{
		$id = is_numeric($_GET['id']) ? $_GET['id'] : 0;
		$vid = is_numeric($_GET['vid']) ? $_GET['vid'] : 0;
		$sql = "SELECT * FROM ".TABLE_PREFIX."pluginvar WHERE `pluginvarid` = '$vid'";
		$query = $this->DatabaseHandler->Query($sql);
		$pluginvar = $query->GetRow();
		$modvars = true;
		include($this->TemplateHandler->Template('admin/plugin_design_editor'));
	}

	function Addconfig()
	{		
		$id = is_numeric(trim($this->Post['id'])) ? trim($this->Post['id']) : 0;
		$vid = is_numeric(trim($this->Post['vid'])) ? trim($this->Post['vid']) : 0;
		$data = array();
		$data['variable'] = trim($this->Post['variable']);
		$data['title'] = trim($this->Post['title']);
		$data['description'] = trim($this->Post['description']);
		$data['type'] = trim($this->Post['type']);
		if($data['type'] == 'select' || $data['type'] == 'checkbox')
		{
			$data['extra'] = $this->Post['extra'];
		}
		else
		{
			$data['extra'] = '';
		}
		$this->DatabaseHandler->SetTable(TABLE_PREFIX.'pluginvar');
		$result = $this->DatabaseHandler->Update($data,"`pluginvarid`='$vid'");
		$this->Messager("插件设计完善成功", 'admin.php?mod=plugindesign&code=vars&id='.$id);
	}

	function Modules()
	{
		$id = is_numeric($_GET['id']) ? $_GET['id'] : 0;
		$sql = "SELECT modules FROM ".TABLE_PREFIX."plugin WHERE `pluginid` = '$id'";
		$query = $this->DatabaseHandler->Query($sql);
		$row = $query->GetRow();
		$mod_ary = unserialize($row['modules']);
		if($mod_ary){
		foreach($mod_ary as $key => $var)
		{
			if($var['modtype'] == '5')
			{
				$mod_ary[$key]['phpstr'] = ".class.php";
			}
			else
			{
				$mod_ary[$key]['phpstr'] = ".inc.php";
			}
		}}
		$sql = " SELECT id,name FROM " . TABLE_PREFIX.'role' . " WHERE id>1";
		$query = $this->DatabaseHandler->Query($sql);
		while($role_row = $query->GetRow())
		{
			$role_list[] = array('name' => $role_row['name'], 'value' => $role_row['id']);
		}
		$modules = true;
		$plugin = ConfigHandler::get('plugin');			
		include($this->TemplateHandler->Template('admin/plugin_design_editor'));
	}
	
	function Addmodules()
	{		
		$id = is_numeric(trim($this->Post['id'])) ? trim($this->Post['id']) : 0;
		$modulesnew = array();
		if(is_array($this->Post['mod_filenew']))
		{
			foreach($this->Post['mod_filenew'] as $moduleid => $module){
				if(!isset($this->Post['delete'][$moduleid]) && !empty($this->Post['mod_filenew'][$moduleid]) && ($this->Post['modtypenew'][$moduleid] == '5' ? true : !empty($this->Post['mod_namenew'][$moduleid])))
				{
					$modulesnew[] = array(
						'modtype'		=> trim($this->Post['modtypenew'][$moduleid]),
						'mod_file'		=> trim($this->Post['mod_filenew'][$moduleid]),
						'mod_name'		=> trim($this->Post['mod_namenew'][$moduleid]),
						'role_id'		=> trim($this->Post['role_idnew'][$moduleid]),
					);
				}
			}
		}

		if(!empty($this->Post['newmod_file']) && ($this->Post['newmodtype'] == '5' ? true : !empty($this->Post['newmod_name']))){
			$modulesnew[] = array(
				'modtype'		=> trim($this->Post['newmodtype']),
				'mod_file'		=> trim($this->Post['newmod_file']),
				'mod_name'		=> trim($this->Post['newmod_name']),
				'role_id'		=> trim($this->Post['newrole_id']),
			);
		}

		$sqls = "select identifier, directory FROM ".TABLE_PREFIX."plugin WHERE pluginid = '$id'";
		$querys = $this->DatabaseHandler->Query($sqls);
		$configs = $querys->GetRow();
		unset($this->navigation_config['pluginmenu'][$configs['identifier']]);

		foreach($modulesnew as $temp => $val)
		{
			if($val['modtype'] <= 3)
			{
				$this->navigation_config['pluginmenu'][$configs['identifier']][] = array(
					'type' => $val['modtype'],
					'name' => $val['mod_name'],
					'code' => $val['mod_file'],
					'url' => 'index.php?mod=plugin&plugin='.$configs['identifier'].':'.$val['mod_file'],
					'target' => '_parent',
					);
			}
			elseif($val['modtype'] == 5)
			{
				$this->hookall_config[$configs['identifier']] = $configs['directory'].$val['mod_file'];
			}
		}
		ConfigHandler::set('hookall',$this->hookall_config);
		ConfigHandler::set('navigation',$this->navigation_config);

		$data = array();
		$data['modules'] = addslashes(serialize($modulesnew));
			
		$this->DatabaseHandler->SetTable(TABLE_PREFIX.'plugin');
		$result = $this->DatabaseHandler->Update($data,"`pluginid`='$id'");
		$this->Messager("插件设计完善成功", 'admin.php?mod=plugindesign&code=modules&id='.$id);
	}

	function Vars()
	{
		$id = is_numeric($_GET['id']) ? $_GET['id'] : 0;
		$sql = "select * FROM ".TABLE_PREFIX."pluginvar WHERE pluginid = '$id'";
		$query = $this->DatabaseHandler->Query($sql);
		while(false != ($row = $query->GetRow())){
			$plugin_var[] = $row;
		}
		$vars = true;
		$plugin = ConfigHandler::get('plugin');	
		include($this->TemplateHandler->Template('admin/plugin_design_editor'));
	}
	
	function Addvar()
	{		
		$id = is_numeric(trim($this->Post['id'])) ? trim($this->Post['id']) : 0;
		$var_data = array();
		$var_data['pluginid'] = $id;
		$var_data['displayorder'] = trim($this->Post['newdisplayorder']);
		$var_data['title'] = trim($this->Post['newtitle']);
		$var_data['variable'] = trim($this->Post['newvariable']);
		$var_data['type'] = trim($this->Post['newtype']);

		$this->DatabaseHandler->SetTable(TABLE_PREFIX.'pluginvar');
		if(!empty($var_data['title']) || !empty($var_data['variable ']))
		{				
			$this->DatabaseHandler->Insert($var_data);
		}

		if($ids = jimplode($this->Post['delete']))
		{
			$sql = "DELETE FROM `" . TABLE_PREFIX . "pluginvar` WHERE `pluginvarid` IN ($ids)";
			$this->DatabaseHandler->Query($sql);
		}

		if(is_array($this->Post['displayordernew'])) {
			foreach($this->Post['displayordernew'] as $vid => $displayorder) {
				$data['displayorder'] = $displayorder;
				$this->DatabaseHandler->Update($data,"`pluginvarid`='$vid'");
			}
		}
			
		$this->Messager("插件设计完善成功", 'admin.php?mod=plugindesign&code=vars&id='.$id);
	}
	
	function Export()
	{
		$id = is_numeric($_GET['id']) ? $_GET['id'] : 0;;
		$sql = "select *  FROM ".TABLE_PREFIX."plugin WHERE pluginid = '$id'";
		$query = $this->DatabaseHandler->Query($sql);
		$plugin_info = $query->GetRow();
		if($plugin_info){
			$export_ary = array();
			$export_ary['Title'] = 'JishiGou! Plugin';
			$export_ary['Version'] = SYS_VERSION;
			$export_ary['Data']['plugin']['available'] = 0;
			$export_ary['Data']['plugin']['name'] = $plugin_info['name'];
			$export_ary['Data']['plugin']['identifier'] = $plugin_info['identifier'];
			$export_ary['Data']['plugin']['description'] = $plugin_info['description'];
			$export_ary['Data']['plugin']['directory'] = $plugin_info['directory'];
			$export_ary['Data']['plugin']['copyright'] = $plugin_info['copyright'];
			$export_ary['Data']['plugin']['version'] = $plugin_info['version'];
			$export_ary['Data']['plugin']['__modules'] = unserialize($plugin_info['modules']);
			
						$sql = "select *  FROM ".TABLE_PREFIX."pluginvar WHERE pluginid = '$id' ORDER BY displayorder DESC ";
			$query = $this->DatabaseHandler->Query($sql);
			$plugin_var = $query->GetAll();
			foreach($plugin_var as $temp => $val)
			{
				$export_ary['Data']['var'][$temp]['displayorder'] = $val['displayorder'];
				$export_ary['Data']['var'][$temp]['title'] = $val['title'];
				$export_ary['Data']['var'][$temp]['description'] = $val['description'];
				$export_ary['Data']['var'][$temp]['variable'] = $val['variable'];
				$export_ary['Data']['var'][$temp]['type'] = $val['type'];
				$export_ary['Data']['var'][$temp]['extra'] = $val['extra'];
			}
			
						$plugindir = PLUGIN_DIR . '/'.$plugin_info['directory'];
			if(file_exists($plugindir.'/install.php')) {
				$export_ary['installfile'] = 'install.php';
			}
						if(file_exists($plugindir.'/upgrade.php')) {
				$export_ary['upgradefile'] = 'upgrade.php';
			}
						if(file_exists($plugindir.'/uninstall.php')){
				$export_ary['uninstallfile'] = 'uninstall.php';
			}
			
			$xml = xml_serialize($export_ary, true);
			$filename = strtolower(str_replace(array('!', ' '), array('', '_'), 'JishiGou! Plugin')).'_'.$plugin_info['identifier'].'.xml';
			ob_end_clean();
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
			header('Cache-Control: no-cache, must-revalidate');
			header('Pragma: no-cache');
			header('Content-Encoding: none');
			header('Content-Length: '.strlen($xml));
			header('Content-Disposition: attachment; filename='.$filename);
			header('Content-Type: text/xml');
			echo $xml;
			exit;
		}else{
			$this->Messager("未找到该插件", 'admin.php?mod=plugin');
		}		
	}
}
?>