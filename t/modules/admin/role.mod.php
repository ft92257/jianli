<?php
/**
 *
 * 后台角色操作模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: role.mod.php 1290 2012-07-30 09:29:11Z wuliyong $
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class ModuleObject extends MasterObject
{
	
	var $ID = 0;

	
	var $ModuleList;

	
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		$this->ID = (int)$this->Get['id']?(int)$this->Get['id']:(int)$this->Post['id'];

		$sql="SELECT name,module from ".TABLE_PREFIX.'role_module';
		$query = $this->DatabaseHandler->Query($sql);
		while ($row=$query->GetRow()) {
			$this->ModuleList[$row['module']]=$row['name'];
		}

				$this->smods = array('role', 'role_action', 'role_module', 'db');
		

		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'copy':
				$this->Copy();
				break;
			
			case 'add':
				$this->Add();
				break;
			case 'doadd':
				$this->DoAdd();
				break;

			case 'admin':				
			case 'modify':
				$this->Modify();
				break;
			case 'domodify':
				$this->DoModify();
				break;
				
			case 'do_modify_by_admin':
				$this->DoModifyByAdmin();
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
		$role_type=in_array($this->Get['type'],array('admin','normal'))
		?$this->Get['type']
		:'normal';
		$sql="
			SELECT
				*
			FROM
				".TABLE_PREFIX.'role'."
			WHERE
				`type`='{$role_type}'
			ORDER BY
				`creditshigher` ASC, `creditslower` ASC, `rank` ASC, `id` ASC";
		$query = $this->DatabaseHandler->Query($sql);
		$role_list = array();
		$role_ids = array();
		while(false != ($row = $query->GetRow())) {
			$role_list[] = $row;
			$role_ids[] = $row['id'];
		}

		$this->_experience();
		
		
				if('admin' == $role_type) {
			$p = array(
				'role_id' => $role_ids,
				'count' => 9999, 
			);
			$rets = jsg_member_get($p, 0);
			$admin_users = $rets['list'];
		}
		
		if($this->Config['jishigou_founder']) {
			$p = array(
				'uid' => explode(',', $this->Config['jishigou_founder']),
				'count' => 999,
			);
			$rets = jsg_member_get($p);
			$founder_users = $rets['list'];
		}
		

		include $this->TemplateHandler->Template('admin/role_list');

	}


	function Copy() {
		$id = (int) get_param('id');
		if($id < 1) {
			$this->Messager("请指定一个要Copy的对象");
		}		
		
		$role_info = DB::fetch_first("select * from ".DB::table('role')." where `id`='$id'");
		if(!$role_info) {
			$this->Messager("请指定一个正确的ID");
		}
		
		$datas = $role_info;
		unset($datas['id']);
		$new_id = DB::insert('role', $datas, 1);
		
		if($new_id > 0) {
			$this->Messager("复制成功，现在为您跳转到编辑页面", "admin.php?mod=role&code=modify&id=$new_id");
		} else {
			$this->Messager("复制失败");
		}
	}


	
	function Add()
	{

		$action="admin.php?mod=role&code=doadd";
		$title="添加";
		$sql="SELECT * FROM ".TABLE_PREFIX.'role_action';
		$query = $this->DatabaseHandler->Query($sql);
		$privilege_list=$query->GetAll();

		$options=array(
		array('name'=>'管理员组','value'=>'admin'),
		array('name'=>'普通用户组','value'=>'normal')
		);

		Load::lib('form');
		$type_select=FormHandler::Select('type',$options);

		$privileges=explode(',',$role_info['privilege']);
		foreach($privilege_list as $key=>$privilege)
		{
			if($privilege['allow_all']==1 && false === JISHIGOU_FOUNDER)
			{
				$privilege['disabled']=" disabled";
			}

			$module_name=isset($this->ModuleList[$privilege['module']])
			?$this->ModuleList[$privilege['module']]
			:"[其它]权限";

			if(in_array($privilege['id'],$privileges) or
			$privileges[0]=="*" or
			$privilege['allow_all']==1)
			{
				$privilege['checked']=" checked";
			}

			$privilege['link']="admin.php?mod=role_action&code=modify&id=".$privilege['id'];

			$privilege['name']=strpos($privilege['action'],"_other")!==false?"<font color='#660099'>{$privilege['name']}</font>":$privilege['name'];
			$module_list[($privilege['is_admin'] ? "后台权限" : "前台权限")][$module_name][]=$privilege;
		}
		krsort($module_list);

		include $this->TemplateHandler->Template('admin/role_info');
	}

	
	function DoAdd()
	{
		$data=array(
				'name'=>$this->Post['name'],
				'type'=>$this->Post['type'],
				'creditshigher'=>$this->Post['creditshigher'],
				'creditslower'=>$this->Post['creditslower'],
				'privilege'=>implode(',',(array)$this->Post['privilege']));

						$this->DatabaseHandler->SetTable(TABLE_PREFIX.'role');
		$result=$this->DatabaseHandler->Insert($data);
		if($result!=false)
		{
			$this->_experience();

			$this->Messager("添加成功",'admin.php?mod=role');
		}
		else
		{
			$this->Messager("添加失败");
		}

	}


	
	function Modify()
	{
				$role_info = DB::fetch_first("SELECT * FROM ".DB::table('role')." WHERE `id`='{$this->ID}'");
				if(!$role_info) {
			$this->Messager("您要编辑的角色信息已经不存在!");
		}

		$action="admin.php?mod=role&code=domodify";
		$title="编辑用户组权限";
		$wheres = array();
		if(true !== JISHIGOU_FOUNDER) {
			$wheres[] = " `module` NOT IN ('".implode("','", $this->smods)."') ";
		}
		if('normal'==$role_info['type']) {
			$wheres[] = " `is_admin`='0' ";
		}
		$where = ($wheres ? (" WHERE " . implode(" AND ", $wheres)) : "");
		$sql="SELECT * FROM ".TABLE_PREFIX.'role_action'.$where;
		$query = $this->DatabaseHandler->Query($sql);
		$privilege_list=$query->GetAll();

		$privileges=explode(',',$role_info['privilege']);
		foreach($privilege_list as $privilege) {
			if($privilege['allow_all']==1 && false === JISHIGOU_FOUNDER) {
				$privilege['disabled']=" disabled ";
			}

			$module_name=isset($this->ModuleList[$privilege['module']])
			?$this->ModuleList[$privilege['module']]
			:"[其它]权限";

			if(in_array($privilege['id'],$privileges) or
			$privileges[0]=="*" or
			$privilege['allow_all']==1) {
				$privilege['checked']=" checked ";
			}

			$privilege['link']="admin.php?mod=role_action&code=modify&id=".$privilege['id'];

			$privilege['name']=strpos($privilege['action'],"_other")!==false?"<font color='#660099'>{$privilege['name']}</font>":$privilege['name'];
			$module_list[($privilege['is_admin'] ? "后台权限" : "前台权限")][$module_name][]=$privilege;
		}
		krsort($module_list);
		
		
		if($this->ID > 1) {
			$role_list_default = array();
			$role_list_default[0] = array('value'=>0, 'name'=>'<b>0、不限制，允许所有</b>',);
			$role_list_default[-1] = array('value'=>-1, 'name'=>'-1、限制，只允许自身',);
			$role_list_default[-2] = array('value'=>-2, 'name'=>'-2、不允许，限制所有',);
			$role_list_default[-3] = array('value'=>-3, 'name'=>'-3、自定义设置（请在下面所列的用户组选项中进行选择）<br /><br />',);
			
			$role_list = array();
			$query = DB::query("select `name`, `id` as `value` from ".DB::table('role')." where `id`!='1' order by `type` desc, `id` asc");
			$v = 0;
			while (false != ($row = DB::fetch($query))) {
				$v = $row['value'];
	
				$role_list[$v] = $row;
			}
			$role_list[$v]['name'] .= '<br /><br />';
				
			foreach($role_info as $k=>$v) {
				if($v && 'allow_' == substr($k, 0, 6)) {
					$v = explode(',', $v);				
					$role_info[$k] = $v;
				}
			}
			
			Load::lib('form');
			$FormHandler = new FormHandler();
		}
			

		$tpl = 'admin/role_info';
		if(true===DEBUG && true===JISHIGOU_FOUNDER && 2==$this->ID && 'admin'==$this->Code) {
			$tpl = 'admin/role_info_admin';
		}
		include $this->TemplateHandler->Template($tpl);
	}


	
	function DoModify()
	{
		$this->DatabaseHandler->SetTable(TABLE_PREFIX.'role');
		$role=$this->DatabaseHandler->Select($this->ID);
		if ($role==false) {
			$this->Messager("该角色已经不存在了", null);
		}		

		$query = DB::query("select * from ".DB::table('role_action'));
		$role_action_list = array();
		$sids = array();
		while(false != ($row = DB::fetch($query))) {
			$role_action_list[$row['id']] = $row;
			if(in_array($row['module'], $this->smods)) {
				$sids[$row['id']] = $row['id'];
			}
		}
		
		$iiddss = array();
		if($this->Post['privilege']) {
			foreach((array) $this->Post['privilege'] as $iid) {
				$iid = (int) $iid;
				if($iid > 0 && isset($role_action_list[$iid])) {
					$iiddss[$iid] = $iid;
				}
			}
			if(true !== JISHIGOU_FOUNDER) {
								$role_pids = array();
				foreach(explode(',', $role['privilege']) as $oid) {
					$role_pids[$oid] = $oid;
				}
				foreach($sids as $sid) {
					if(isset($role_pids[$sid])) {
						$iiddss[$sid] = $sid;
					} else {
						unset($iiddss[$sid]);
					}
				}
			}
			sort($iiddss);
		}
		

		$data=array(
			'id'=>$this->ID,
			'name'=>trim(strip_tags($this->Post['name'])),
			'creditshigher'=>(int) $this->Post['creditshigher'],
			'creditslower'=>(int) $this->Post['creditslower'],
			'privilege'=>implode(',',$iiddss),
		);
		$data = $this->_process_allows($role, $data);

		$result=$this->DatabaseHandler->Update($data);
		
		if($result===false)
		{
			$this->Messager("编辑失败");
		}
		else
		{
			$cache_id = 'role/role_'.$role['id'];
			cache_file('rm', $cache_id);
			
			$cache_id = 'role/role_id_'.$role['id'];
			cache_file('rm', $cache_id);
			
			$this->_experience();

			$this->Messager("编辑成功");
		}

	}


	
	function _experience()
	{
		$sql="
			SELECT
				*
			FROM
				".TABLE_PREFIX.'role'."
			ORDER BY
				`creditshigher` ASC, `creditslower` ASC, `rank` ASC, `id` ASC";
			
		$query = $this->DatabaseHandler->Query($sql);
		$experience_list = array();
		$rank = 0;
		while(false != ($row = $query->GetRow()))
		{
			if(('normal' == $row['type']) && ($row['creditshigher'] > 0 || $row['creditslower'] > 0))
			{
				if($rank != $row['rank'])
				{
					$this->DatabaseHandler->Query("update ".TABLE_PREFIX."role set `rank`='$rank' where `id`='{$row['id']}'");

					$row['rank'] = $rank;
				}

				$rank +=1;

				if($row['rank'] > 0)
				{
					$experience_list[$row['rank']] = array(
						'level' => $row['rank'],
						'start_credits' => $row['creditshigher'],
						'order' => $row['rank'],
						'enable' => 1,		
					);
				}
			}
			else
			{
				if($row['rank'])
				{
					$this->DatabaseHandler->Query("update ".TABLE_PREFIX."role set `rank`='0' where `id`='{$row['id']}'");

					$row['rank'] = 0;
				}
			}
		}

				if($experience_list) {
			$experience = ConfigHandler::get('experience');
			if($experience_list != $experience['list']) {
				$experience['list'] = $experience_list;

				ConfigHandler::set('experience', $experience);
			}
		}
	}
	
	function _process_allows($role, $data = array(), $posts = array()) {
		$posts = ($posts ? $posts : $this->Post);
		
		foreach($posts as $k=>$v) {
			if('allow_' == substr($k, 0, 6)) {
				$vv = implode(',', $v);
				$vs = array();
				if(jsg_find($vv, 0)) {
					$vs[] = 0;
				} elseif (jsg_find($vv, -1)) {
					$vs[] = -1;
					$vs[] = $role['id'];
				} elseif (jsg_find($vv, -2)) {
					$vs[] = -2;
				} else {
					foreach($v as $i) {
						$i = (int) $i;
						if($i > 0) {
							$vs[] = $i;
						}
					}
					if($vs) {
						$vs[] = -3;
					}
				}
				
				$vss = 0;
				if($vs) {
					array_unique($vs);
					sort($vs);
					
					$vss = implode(',', $vs);
				}
				$data[$k] = $vss;
			}
		}
		
		return $data;
	}
	
	function DoModifyByAdmin() {
		if(true!==DEBUG || true!==JISHIGOU_FOUNDER || 2!=$this->ID) {
			exit;
		}
		
		$modns = get_param("modns");
		foreach($modns as $m1=>$m2) {
			if($m1 != $m2) {
				$m2 = trim(strip_tags($m2));
				
				DB::query("update ".DB::table("role_module")." set `name`=\"$m2\" where `name`=\"$m1\"");
			}
		}
		
		$codens = get_param("codens");
		$_codens = get_param("_codens");
		foreach($codens as $k=>$n2) {
			if($n2 != $_codens[$k]) {
				$n2 = trim(strip_tags($n2));
				
				DB::query("update ".DB::table("role_action")." set `name`=\"$n2\" where `id`=\"$k\"");
			}
		}
		DB::query("delete from ".DB::table("role_action")." where `name`=\"\"");
		

		$this->Messager("修改成功");
	}

}

?>