<?php
/**
 * 文件名：plugin.mod.php
 * 版本号：1.0
 * 最后修改时间：2011-08-30
 * 作者：狐狸<foxis@qq.com>
 * 功能描述：插件管理
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
		switch($this->Code) 
		{
			case 'add':
				$this->Add();
				break;
			case 'config':
				$this->Config();
				break;
			case 'configsave':
				$this->Configsave();
				break;
			case 'del':
				$this->Del();
				break;
			case 'manage':
				$this->Manage();
				break;
			case 'design':
				$this->Design();
				break;
			case 'adddesign':
				$this->Adddesign();
				break;
			case 'action':
				$this->Action($_GET['tyle']);
				break;
			case 'install':
				$this->Install();
				break;
			case 'uninstall':
				$this->Uninstall();
				break;
			case 'upgrade':
				$this->Upgrade();
				break;
			default:
				$this->Main();
		}
		$body = ob_get_clean();
		
		$this->ShowBody($body);
		
	}
	function Manage()
	{
		$pluginid = is_numeric($_GET['id']) ? $_GET['id'] : 0;
		$pluginconfig = "插件管理中心";
		$identifier = $_GET['identifier'];
		$pmod = $_GET['pmod'];
		$sql = "SELECT * FROM `" . TABLE_PREFIX . "plugin` WHERE `pluginid`='$pluginid'";
		$query = $this->DatabaseHandler->Query($sql);
		$row = $query->GetRow();
		if($identifier != $row['identifier']){
			$this->Messager("插件未找到", -1);
		}
		$pluginname = $row['name'];
		$mod_ary = unserialize($row['modules']);
		if(is_array($mod_ary))
		{
			foreach($mod_ary as $module)
			{
				if($module['modtype'] == '4' && $module['role_id'] == '7')
				{
					$pluginmenu[] = array(
						'identifier'  => $row['identifier'],
						'pmod'  => $module['mod_file'],
						'name' => $module['mod_name'],
					);
				}
			}
		}
		$sqls = "select * FROM ".TABLE_PREFIX."pluginvar WHERE `pluginid` = '$pluginid'";
		$querys = $this->DatabaseHandler->Query($sqls);
		$config = $querys->GetRow();
		$template = 'plugin/'.$identifier.'/'.$pmod;
		if(@!file_exists($pluginfile = PLUGIN_DIR .'/'.$identifier.'/'.$pmod.'.inc.php')){
			$this->Messager("插件模块文件(".$template.'.inc.php'.")不存在或者插件文件不完整", -1);
		}else{
			include_once($pluginfile);
		}

		include($this->TemplateHandler->Template('admin/plugin_manage'));
	}
	function Config()
	{
		$pluginid = is_numeric($_GET['id']) ? $_GET['id'] : 0;
		$pluginconfig = "插件设置";
		$sql = "SELECT * FROM `" . TABLE_PREFIX . "plugin` WHERE `pluginid`='$pluginid'";
		$query = $this->DatabaseHandler->Query($sql);
		$row = $query->GetRow();
		$pluginname = $row['name'];
		$mod_ary = unserialize($row['modules']);
		if(is_array($mod_ary))
		{
			foreach($mod_ary as $module)
			{
				if($module['modtype'] == '4' && $module['role_id'] == '7')
				{
					$pluginmenu[] = array(
						'identifier'  => $row['identifier'],
						'pmod'  => $module['mod_file'],
						'name' => $module['mod_name'],
					);
				}
			}
		}
		$sqls = "SELECT * FROM `" . TABLE_PREFIX . "pluginvar` WHERE `pluginid`='$pluginid'";
		$querys = $this->DatabaseHandler->Query($sqls);
		while($rows = $querys->GetRow())
		{
			$pluginvar[] = $rows;
		}
		if($pluginvar)
		{
			$id = jget('id','int','G');
			foreach($pluginvar as $temp => $var)
			{
				$var['variable'] = 'varsnew['.$var['variable'].']';
				if($var['type'] == 'text')
				{
					$pluginvar[$temp]['pluginvar'] = "<input type=\"text\" name=\"".$var['variable']."\" value=\"".$var['value']."\" size=\"40\">";
				}
				elseif($var['type'] == 'radio')
				{
					$str = "<input name=\"".$var['variable']."\" class=\"radio\" type=\"radio\" value=\"1\" ".($var['value'] == '1' ? 'CHECKED' : '').">是&nbsp;&nbsp;<input name=\"".$var['variable']."\" class=\"radio\" type=\"radio\" value=\"0\" ".($var['value'] == '0' ? 'CHECKED' : '').">否";
					$pluginvar[$temp]['pluginvar'] = $str;
				}
				elseif($var['type'] == 'textarea')
				{
					$pluginvar[$temp]['pluginvar'] = "<textarea name=\"".$var['variable']."\" rows=\"5\" cols=\"50\">".$var['value']."</textarea>";
				}
				elseif($var['type'] == 'select')
				{
					$str = "<select name=\"".$var['variable']."\"><option value=\"\">请选择...</option>";
					foreach(explode("\n", $var['extra']) as $key => $option)
					{
						$option = trim($option);
						if(strpos($option, '=') === FALSE) {
							$key = $option;
						} else {
							$item = explode('=', $option);
							$key = trim($item[0]);
							$option = trim($item[1]);
						}
						$str .= "<option value=\"".$key."\" ".($var['value'] == $key ? 'selected' : '').">".$option."</option>";
					}
					$str .= "</select>";
					$pluginvar[$temp]['pluginvar'] = $str;
				}
				elseif($var['type'] == 'checkbox')
				{
					$var['value'] = unserialize($var['value']);
					$var['value'] = is_array($var['value']) ? $var['value'] : array($var['value']);
					$str = '';
					foreach(explode("\n", $var['extra']) as $key => $option)
					{
						$option = trim($option);
						if(strpos($option, '=') === FALSE) {
							$key = $option;
						} else {
							$item = explode('=', $option);
							$key = trim($item[0]);
							$option = trim($item[1]);
						}
						$str .= "<input type=\"checkbox\" name=\"".$var['variable']."[]\" class=\"radio\" value=\"".$key."\" ".(in_array($key, $var['value']) ? 'CHECKED' : '').">".$option."&nbsp;&nbsp;";
					}
					$pluginvar[$temp]['pluginvar'] = $str;
				}
				else
				{
					$sql = " SELECT id,name FROM " . TABLE_PREFIX.'role' . " WHERE id>1";
					$query = $this->DatabaseHandler->Query($sql);
					while($role_row = $query->GetRow())
					{
						$role_list[] = array('name' => $role_row['name'], 'value' => $role_row['id']);
					}
					$str = "<select name=\"".$var['variable']."\"><option value=\"\">请选择...</option>";
					foreach($role_list as $key => $role)
					{
						$str .= "<option value=\"".$role['value']."\" ".($var['value'] == $role['value'] ? 'selected' : '').">".$role['name']."</option>";
					}
					$str .= "</select>";
					$pluginvar[$temp]['pluginvar'] = $str;
				}
			}
		}
		include($this->TemplateHandler->Template('admin/plugin_config'));
	}
	function Configsave()
	{
		$id = is_numeric($_GET['id']) ? $_GET['id'] : 0;
		$sql = "SELECT variable FROM `" . TABLE_PREFIX . "pluginvar` WHERE `pluginid`='$id'";
		$query = $this->DatabaseHandler->Query($sql);
		while(false != ($row = $query->GetRow()))
		{
			$pluginvars[] = $row['variable'];
		}
		$data = array();
		if(is_array($_POST['varsnew'])) {
			foreach($_POST['varsnew'] as $variable => $value) {
				if(in_array($variable,$pluginvars)) {
					$data['value'] = $value;
					if(is_array($value)) {
						$data['value'] = addslashes(serialize($value));
					}
					$this->DatabaseHandler->SetTable(TABLE_PREFIX.'pluginvar');
					$this->DatabaseHandler->Update($data,"`pluginid`='$id' AND `variable`='$variable'");
				}
			}
		}
		$this->Messager("插件设置成功", 'admin.php?mod=plugin&code=config&id='.$id);
	}
	function Main()
	{
				$sql = " select count(*) as `total` from `".TABLE_PREFIX."plugin`";
		$total = DB::result_first($sql);
		
		$page_num=10;
		$p=max($_GET['p'],1);
		$offset=($p-1)*$page_num;
		$pages=page($total, $page_num, '', array('var'=>'p'));
		
		$sql = "select *  FROM ".TABLE_PREFIX."plugin LIMIT $offset,$page_num";
		$query = $this->DatabaseHandler->Query($sql);
		$i = 0;
		while(false != ($row = $query->GetRow()))
		{
			$plugin_list[$i] = $row;
			$plugin_list[$i]['uninstall'] = "admin.php?mod=plugin&code=uninstall&plugindir=".str_replace('/', '',$row['directory'])."&id=".$row['pluginid'];
			$plugin_list[$i]['upgrade'] = "admin.php?mod=plugin&code=upgrade&id=".$row['pluginid'];
			$sqls = "select * FROM ".TABLE_PREFIX."pluginvar WHERE pluginid = '{$row['pluginid']}'";
			$querys = $this->DatabaseHandler->Query($sqls);
			$vars = $querys->GetRow();
			$plugin_list[$i]['pluginvar'] = $vars;
			$mod_ary = unserialize($row['modules']);
			if(is_array($mod_ary))
			{
				foreach($mod_ary as $module)
				{
					if($module['modtype'] == '4' && $module['role_id'] == '7')
					{
						$plugin_list[$i]['pluginmenu'][] = array(
							'identifier'  => $row['identifier'],
							'pmod'  => $module['mod_file'],
							'name' => $module['mod_name'],
						);
					}
				}
			}
			$plugin_list[$i]['logo'] = file_exists(PLUGIN_DIR.'/'.$row['directory'].'logo.png') ? str_replace(ROOT_PATH,'',PLUGIN_DIR).$row['directory'].'logo.png' : 'templates/default/images/plugin_logo.png';
			$plugin_ary[$temp]['dir']  = $val['plugin']['directory'];
			$i++;
		}
		
		include($this->TemplateHandler->Template('admin/plugin_list'));
	}
	function Add()
	{
		$sql = "SELECT directory FROM " .TABLE_PREFIX."plugin ORDER BY pluginid DESC ";
		$query = $this->DatabaseHandler->Query($sql);
		while(false != ($row = $query->GetRow()))
		{
			$installsdir[] = $row['directory'];
		}
		if(!is_array($installsdir)){$installsdir = array();}
		$pluginsdir = dir(PLUGIN_DIR);
		while(($file = $pluginsdir->read()) !== false){
			if(!in_array($file, array('.', '..')) && is_dir(PLUGIN_DIR.'/'.$file) && !in_array($file.'/', $installsdir)){
				$filedir = PLUGIN_DIR . '/'.$file;
				$d = dir($filedir);
				while($f = $d->read()){
					if(preg_match('/^jishigou\_plugin\_'.$file.'.xml$/', $f)){
						$xml_url = $filedir.'/jishigou_plugin_'.$file.'.xml';
						$fp = fopen($xml_url, 'r');
						$xmldata = fread($fp, filesize($xml_url));
						fclose($fp);
						$plugin_ary = array();
						$plugindata = xml_unserialize($xmldata);
						$plugin_all[] = $plugindata['Data'];						
					}
				}
			}
		}

		if(is_array($plugin_all)){
		foreach($plugin_all as $temp => $val)
		{
			$plugin_ary[$temp]['name']   = $val['plugin']['name'];
			$plugin_ary[$temp]['logo'] = file_exists(PLUGIN_DIR.'/'.$val['plugin']['directory'].'logo.png') ? str_replace(ROOT_PATH,'',PLUGIN_DIR).'/'.$val['plugin']['directory'].'logo.png' : 'templates/default/images/plugin_logo.png';
			$plugin_ary[$temp]['dir']  = $val['plugin']['directory'];
			$plugin_ary[$temp]['install_url'] = "admin.php?mod=plugin&code=install&plugindir=".str_replace('/', '',$val['plugin']['directory']);
		}}
		
		$pluginsdir->close();
		include($this->TemplateHandler->Template('admin/plugin_add'));
	}
	
	function Design()
	{
		if(PLUGINDEVELOPER > 0){
			include($this->TemplateHandler->Template('admin/plugin_design'));
		}
	}
	
	function Adddesign()
	{		
		if(PLUGINDEVELOPER > 0)
		{
		$data = array();
		$data['name'] = cutstr(trim($this->Post['plugin_name']),40);
		$data['identifier'] = cutstr(trim($this->Post['identifier']),40);
		$data['version'] = cutstr(trim($this->Post['version']),10);
		$data['copyright'] = cutstr(trim($this->Post['copyright']),80);
		$data['description'] = cutstr($this->Post['description'],100);
		$data['available'] = 2;
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
		$data['directory'] = $data['identifier'].'/';
		$result_id = $this->DatabaseHandler->Insert($data);
				$filedir = PLUGIN_DIR . '/'.$data['directory'];
		$tempdir = ROOT_PATH .'templates/default/plugin/'.$data['directory'];
		
		
		if (!is_dir($filedir))
		{
			Load::lib('io', 1)->MakeDir($filedir);
		}
		if (!is_dir($tempdir))
		{
			Load::lib('io', 1)->MakeDir($tempdir);
		}
		if($result_id != false)
		{
			$this->Messager("插件设计成功,请完善你的设计", 'admin.php?mod=plugindesign&code=design&id='.$result_id);
		}else{
			$this->Messager("添加失败");
		}
		}
	}

	
	function Action($tyle)
	{
		$_data = array(
			'available' => ($tyle == 'stop') ? 0:1
			);
		
		$id = is_numeric($_GET['id']) ? $_GET['id'] : 0;
		$sql = "select *  FROM ".TABLE_PREFIX."plugin WHERE `pluginid` = '$id'";
		$query = $this->DatabaseHandler->Query($sql);
		$plugin_info = $query->GetRow();
	
				$mod_ary = unserialize($plugin_info['modules']);
		unset($this->navigation_config['pluginmenu'][$plugin_info['identifier']]);
		foreach($mod_ary as $temp => $val)
		{
			if($val['modtype'] == 5)
			{
				if($tyle == 'start' && empty($this->hookall_config[$plugin_info['identifier']])){
					$this->hookall_config[$plugin_info['identifier']] = $plugin_info['directory'].$val['mod_file'];
				}else{
					unset($this->hookall_config[$plugin_info['identifier']]);
				}
			}
			elseif($val['modtype'] <= 3)
			{
				if($tyle == 'start')
				{
					$this->navigation_config['pluginmenu'][$plugin_info['identifier']][] = array(
					'type' => $val['modtype'],
					'name' => $val['mod_name'],
					'code' => $val['mod_file'],
					'url' => 'index.php?mod=plugin&plugin='.$plugin_info['identifier'].':'.$val['mod_file'],
					'target' => '_parent',
					);
				}
			}
		}
		ConfigHandler::set('hookall',$this->hookall_config);
		ConfigHandler::set('navigation',$this->navigation_config);
		$ok_messager = ($tyle == 'stop') ? "插件已成功关闭":"插件已成功开启";
		$this->DatabaseHandler->SetTable(TABLE_PREFIX . 'plugin');	
		$result = $this->DatabaseHandler->Update($_data,"`pluginid`='$id'");
		if($result != false)
		{
			$this->Messager($ok_messager, 'admin.php?mod=plugin');
		}
		else
		{
			$this->Messager("操作失败");
		}
	}
	
	
	function Install()
	{	
		global $_J;
		$plugindir = $this->Get['plugindir'];
		$filedir = PLUGIN_DIR . '/'.$plugindir;
		$xml_url = $filedir.'/jishigou_plugin_'.$plugindir.'.xml';
		$fp = fopen($xml_url, 'r');
		$xmldata = fread($fp, 4096);
		$plugindata_all = xml_unserialize($xmldata);
		if($this->Get['submit'] != 'yes'){
			$pname = $plugindata_all['Data']['plugin']['name'];
			if($plugindata_all['license']){
				$plugindata_all['license'] = strip_tags($plugindata_all['license'],'<b>,<i>,<u>,<p>,<font>,<center>');
				$license = nl2br($plugindata_all['license']);
			}else{
				if($plugindata_all['Version'] != $_J['config']['sys_version']){
					$license = '<br><br><center>本插件适用版本为'.$plugindata_all['Version'].'，您当前正在使用的系统版本是'.$_J['config']['sys_version'].'<br>您确定要安装吗？</center><br><br>';
				}else{
					$license = '<br><br><center>您确定要安装该插件吗？<br>如果此插件是从记事狗官方下载的，则为绿色插件，您可放心安装</center><br><br>';
				}
			}
			include($this->TemplateHandler->Template('admin/plugin_install'));
			exit;
		}
		
		$plugindata = $plugindata_all['Data']['plugin'];
		$vardata = empty($plugindata_all['Data']['var']) ? array() : $plugindata_all['Data']['var'];
		$installfile = $plugindata_all['installfile'];

		if($installfile)
		{
						if(file_exists($filedir.'/'.$installfile))
			{
				$sql = '';
				include($filedir.'/'.$installfile);
				
				if($sql)
				{
					$sqls = str_replace("\r","\n",str_replace("{jishigou}",TABLE_PREFIX,$sql));
					foreach(explode(";\n", trim($sqls)) as $sql)
					{
						$query = trim($sql);
						if(!empty($query))
						{
							if(strtoupper(substr($query, 0, 12)) == 'CREATE TABLE') 
							{
								$query = $this->_sql_createtable($query, $this->Config['charset']);
							}
							
							$this->DatabaseHandler->Query($query);
						}
					}
				}					
			}else{
				$this->Messager("安装失败（找不到安装文件".$installfile."，无法安装）", 'admin.php?mod=plugin');
			}
		}
		
				$data['name'] = $plugindata['name'];
		$data['available'] = $plugindata['available'];
		$data['directory'] = $plugindata['directory'];
		$data['identifier'] = $plugindata['identifier'];
		$data['version'] = $plugindata['version'];
		$data['copyright'] = $plugindata['copyright'];
		$data['modules'] = addslashes(serialize($plugindata['__modules']));
		$data['description'] = $plugindata['description'];
		
		$this->DatabaseHandler->SetTable(TABLE_PREFIX.'plugin');
		$result_id = $this->DatabaseHandler->Insert($data);
		
				$this->DatabaseHandler->SetTable(TABLE_PREFIX.'pluginvar');
		foreach($vardata as $temp => $value)
		{
			$var_data['pluginid'] = $result_id;
			$var_data['title'] = $value['title'];
			$var_data['description'] = $value['description'];
			$var_data['variable'] = $value['variable'];
			$var_data['type'] = $value['type'];
			$var_data['extra'] = $value['extra'];
			$var_data['value'] = '';
			$var_data['displayorder'] = $value['displayorder'];
			$this->DatabaseHandler->Insert($var_data);
		}

				unset($this->navigation_config['pluginmenu'][$plugindata['identifier']]);
		if(is_array($plugindata['__modules']) && $plugindata['available'])
		{
			foreach($plugindata['__modules'] as $key => $val)
			{
				if($val['modtype'] <= 3)
				{
					$this->navigation_config['pluginmenu'][$plugindata['identifier']][] = array(
					'type' => $val['modtype'],
					'name' => $val['mod_name'],
					'code' => $val['mod_file'],
					'url' => 'index.php?mod=plugin&plugin='.$plugindata['identifier'].':'.$val['mod_file'],
					'target' => '_parent',
					);
				}
				elseif($val['modtype'] == 5)
				{
					$this->hookall_config[$plugindata['identifier']] = $plugindata['directory'].$val['mod_file'];
				}
			}
		}
		ConfigHandler::set('hookall',$this->hookall_config);
		ConfigHandler::set('navigation',$this->navigation_config);
		$this->Messager("已成功安装 (".$plugindata['name'].") 插件", 'admin.php?mod=plugin');
	}
	
	
	function Uninstall()
	{
		$id = is_numeric($_GET['id']) ? $_GET['id'] : 0;
		$sql = "select available, identifier FROM ".TABLE_PREFIX."plugin WHERE `pluginid` = '$id'";
		$query = $this->DatabaseHandler->Query($sql);
		$plugin_info = $query->GetRow();
		if($plugin_info['available'] == 1)
		{
			$this->Messager("卸载失败（此插件启动中，如卸载请先关闭本插件）", 'admin.php?mod=plugin');
		}

		$plugindir = $this->Get['plugindir'];
		$filedir = PLUGIN_DIR . '/'.$plugindir;
		$xml_url = $filedir.'/jishigou_plugin_'.$plugindir.'.xml';
		$fp = fopen($xml_url, 'r');
		$xmldata = fread($fp, 4096);
		$plugindata_all = xml_unserialize($xmldata);
		$uninstallfile = $plugindata_all['uninstallfile'];
		if($uninstallfile)
		{
						if(file_exists($filedir.'/'.$uninstallfile)) {
				include($filedir.'/'.$uninstallfile);
				$sqls = str_replace("\r","\n",str_replace("{jishigou}",TABLE_PREFIX,$sql));
				foreach(explode(";\n", trim($sqls)) as $sql)
				{
					$query = trim($sql);
					if(!empty($query))
					{
						if(strtoupper(substr($query, 0, 12)) == 'CREATE TABLE') 
						{
							$query = $this->_sql_createtable($query, $this->Config['charset']);
						}
						
						$this->DatabaseHandler->Query($query);
					}
				}
			}else{
				$this->Messager("卸载失败（卸载文件".$uninstallfile."丢失，无法卸载）", 'admin.php?mod=plugin');
			}
		}

		$sql = "DELETE FROM `" . TABLE_PREFIX . "plugin` WHERE `pluginid` = '$id'";
		$result = $this->DatabaseHandler->Query($sql);		
				$sql = "DELETE FROM `" . TABLE_PREFIX . "pluginvar` WHERE `pluginid` = '$id'";
		$result = $this->DatabaseHandler->Query($sql);
				unset($this->navigation_config['pluginmenu'][$plugin_info['identifier']]);
		unset($this->hookall_config[$plugin_info['identifier']]);
		ConfigHandler::set('hookall',$this->hookall_config);
		ConfigHandler::set('navigation',$this->navigation_config);
		
		if($result != false)
		{
			$this->Messager("插件已经成功卸载", 'admin.php?mod=plugin');
		}
		else
		{
			$this->Messager("操作失败");
		}
	}

	
	function Upgrade()
	{
		$id = is_numeric($_GET['id']) ? $_GET['id'] : 0;
		$sql = "select available, identifier, version FROM ".TABLE_PREFIX."plugin WHERE `pluginid` = '$id'";
		$query = $this->DatabaseHandler->Query($sql);
		$plugin_info = $query->GetRow();
		if($plugin_info['available'] == 1)
		{
			$this->Messager("升级失败（此插件启动中，如升级请先关闭本插件）", 'admin.php?mod=plugin');
		}

		$plugindir = $plugin_info['identifier'];
		$nowver = !empty($plugin_info['version']) ? $plugin_info['version'] : 0;
		$filedir = PLUGIN_DIR . '/'.$plugindir;
		$xml_url = $filedir.'/jishigou_plugin_'.$plugindir.'.xml';
		$fp = fopen($xml_url, 'r');
		$xmldata = fread($fp, 4096);
		$plugindata_all = xml_unserialize($xmldata);
		$upgradefile = $plugindata_all['upgradefile'];
		$newver = $plugindata_all['Data']['plugin']['version'];
		$upgrade = ($newver > $nowver) ? true : false;
		$data = array();
		$data['version'] = $newver;
		if($upgrade)
		{
			if($upgradefile){ 
								if(file_exists($filedir.'/'.$upgradefile)) {
					include($filedir.'/'.$upgradefile);
					$sqls = str_replace("\r","\n",str_replace("{jishigou}",TABLE_PREFIX,$sql));
					foreach(explode(";\n", trim($sqls)) as $sql)
					{
						$query = trim($sql);
						if(!empty($query))
						{
							$this->DatabaseHandler->Query($query);
						}
					}
				}else{
					$this->Messager("升级失败（升级文件".$upgradefile."丢失，无法升级）", 'admin.php?mod=plugin');
				}
			}
			$this->DatabaseHandler->SetTable(TABLE_PREFIX . 'plugin');
			$this->DatabaseHandler->Update($data,"`pluginid` = '$id'");
			$this->Messager("插件已经从".$nowver."成功升级到".$newver, 'admin.php?mod=plugin');
		}
		else
		{
			$this->Messager("此插件无需升级，请上传新版本后再执行本操作", 'admin.php?mod=plugin');
		}
	}

	function Del()
	{
		$id = is_numeric($_GET['id']) ? $_GET['id'] : 0;
		$sql = "select directory, identifier FROM ".TABLE_PREFIX."plugin WHERE `pluginid` = '$id'";
		$query = $this->DatabaseHandler->Query($sql);
		$plugin_info = $query->GetRow();
		$directory = $plugin_info['directory'];
		$sql = "DELETE FROM `" . TABLE_PREFIX . "plugin` WHERE `pluginid` =  '$id'";
		$result = $this->DatabaseHandler->Query($sql);		
				$sql = "DELETE FROM `" . TABLE_PREFIX . "pluginvar` WHERE `pluginid` =  '$id'";
		$result = $this->DatabaseHandler->Query($sql);
				unset($this->navigation_config['pluginmenu'][$plugin_info['identifier']]);
		ConfigHandler::set('navigation',$this->navigation_config);

		$filedir = PLUGIN_DIR . '/'.$directory;
		$tempdir = ROOT_PATH .'templates/default/plugin/'.$directory;
		
		
		Load::lib('io', 1)->RemoveDir($filedir);
		Load::lib('io', 1)->RemoveDir($tempdir);
		
		if($result != false)
		{
			$this->Messager("插件已经成功删除", 'admin.php?mod=plugin');
		}
		else
		{
			$this->Messager("操作失败");
		}
	}
	
	
	function _sql_createtable($sql, $dbcharset) 
	{
		$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
		$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
		$dbcharset = strtolower($dbcharset);
		if('utf-8' == $dbcharset)
		{
			$dbcharset = 'utf8';
		}
	    
	    $search = ' character set gbk collate gbk_bin ';
	    if(false!==strpos($sql,$search))
	    {
	        if(mysql_get_server_info() <= '4.1')
	        {
	            $sql = str_replace($search, ' binary ', $sql);
	        }
	        else
	        {
	            if('gbk'!=$dbcharset)
	            {
	                $sql = str_replace($search, " character set {$dbcharset} collate {$dbcharset}_bin ", $sql);
	            }
	        }
	    }
	    
		return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
			(mysql_get_server_info() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=$dbcharset" : " TYPE=$type");
	}
}
?>