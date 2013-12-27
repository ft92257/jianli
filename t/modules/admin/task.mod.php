<?php
/**
 *
 * 计划任务操作模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: task.mod.php 1353 2012-08-09 08:23:26Z wuliyong $
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{

	
	var $ID = 0;

	
	var $IDS;

	
	var $ModuleList;

	
	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->ID = jget('id', 'int');

		$this->IDS = jget('ids');
        
        Load::lib('form');

		$this->Execute();
	}

	
	function Execute()
	{
		switch($this->Code)
		{
			case 'list':
				$this->Main();
				break;
			case 'log_list':
				$this->LogList();
				break;
			case 'log_delete':
				$this->LogDelete();
				break;
			case 'add':
				$this->Add();
				break;
			case 'doadd':
				$this->DoAdd();
				break;
			case 'run':
				$this->run();
				break;

			case 'modify':
				$this->Modify();
				break;
			case 'domodify':
				$this->DoModify();
				break;
			case 'dobatchmodify':
				$this->doBatchModify();
				break;

			default:
				$this->Main();
				break;
		}
	}

	
	function Main()
	{
		$sql="SELECT * FROM ".TABLE_PREFIX.'task';
		$query = $this->DatabaseHandler->Query($sql);
		$task_list=array();
		$available_count=0;
		while ($row=$query->GetRow())
		{
			$disabled = $row['weekday'] == -1 && $row['day'] == -1 && $row['hour'] == -1 && $row['minute'] == '' ? 'disabled' : '';
			$row['disabled']=$disabled;
			$row['type_name']=$row['type']=='system'?"内置":"自定义";
			if($row['available']==1)$available_count++;
			foreach(array('weekday', 'day', 'hour', 'minute') as $key) {
				if(in_array($row[$key], array(-1, ''))) {
					$row[$key] = '<b>*</b>';
				} elseif($key == 'weekday') {
					$row[$key] = $lang['rows_week_day_'.$row[$key]];
				} elseif($key == 'minute') {
					foreach($row[$key] = explode("\t", $row[$key]) as $k => $v) {
						$row[$key][$k] = sprintf('%02d', $v);
					}
					$row[$key] = implode(',', $row[$key]);
				}
			}
			$row['lastrun']=$row['lastrun']?my_date_format($row['lastrun'],"Y-m-d<\b\\r>H:i:s"): '<b>N/A</b>';
			$row['nextrun']=$row['nextrun']?my_date_format($row['nextrun'],"Y-m-d<\b\\r>H:i:s"): '<b>N/A</b>';
			$task_list[]=$row;
		}
				
		if ($available_count > 0) {
			$task_disable = 1;
		} else {
			$task_disable = 0;
		}
		if($task_disable!=$this->Config['task_disable']) {
			$config = array();
			$config['task_disable'] = $task_disable;

			ConfigHandler::update($config);

		}
		
		include $this->TemplateHandler->Template('admin/task_list');

	}
	
	
	
	function LogList()
	{
		$task_id=(int)$this->Get['task_id']?(int)$this->Get['task_id']:(int)$this->Post['task_id'];
		$limit=(int)$this->Get['limit']?(int)$this->Get['limit']:(int)$this->Post['limit'];
		if($limit==0)$limit=5;
		$where_list=array();
		$where="";
		if($task_id)
		{
			$where_list['task_id']="task_id='$task_id'";
		}
		if ($where_list!=false) 
		{
			$where=' where '.implode(" AND ",$where_list);
		}
		
				$error_type=array(
			0=>"成功",
			E_USER_ERROR=>"<span style='color:red'>错误</span>",
			E_USER_WARNING=>"<span style='color:#EF6000'>警告</span>",
			E_USER_NOTICE=>"<span style='color:#FF9710'>注意</span>",
		);
				
				$sql="SELECT id,name from ".TABLE_PREFIX.'task';
		$query = $this->DatabaseHandler->Query($sql);
		$task_list=array();
		$task_list[]=array('name'=>"所有任务",'id'=>0);
		while ($row=$query->GetRow()) 
		{
			$task_list[$row['id']]=$row;
		}
		
		$task_select=FormHandler::Select('task_id',$task_list,$task_id);
		
		$sql="SELECT * from ".TABLE_PREFIX.'task_log'.$where." order by id desc limit {$limit}";
		$query = $this->DatabaseHandler->Query($sql);
		$log_list=array();
		while ($row=$query->GetRow()) 
		{
			$row['error_string']=$error_type[$row['error']];
			$row['task_name']=$task_list[$row['task_id']]['name'];
			$row['dateline']=my_date_format($row['dateline']);
			$log_list[]=$row;
		}
		

		include $this->TemplateHandler->Template('admin/task_log_list');

	}
	
	function LogDelete()
	{
		$log_id_list=(array)$this->Post['delete'];
		$day=(int)$this->Post['day'];
		$task_id=(int)$this->Post['task_id'];
				if(count($log_id_list)>0)
		{
			$sql="DELETE FROM ".TABLE_PREFIX.'task_log'." where ".$this->DatabaseHandler->BuildIn($log_id_list,'id');
			$query = $this->DatabaseHandler->Query($sql);
		}
				elseif ($day>0) 
		{
			$day_before=time()-($day*86400);
			$task_add=$task_id>0?" and task_id={$task_id}":"";
			$sql="DELETE FROM ".TABLE_PREFIX.'task_log'." WHERE dateline<{$day_before}".$task_add;
			$query = $this->DatabaseHandler->Query($sql);
		}
		else
		{
			$this->Messager("未指定删除条件",-1);
		}
		$delete_count=$this->DatabaseHandler->AffectedRows();
		$this->Messager("删除成功，共删除{$delete_count}条记录");
	}





	
	function Add()
	{

		
		$action="admin.php?mod=role&code=doadd";
		$title="添加";
		$sql="SELECT * FROM ".TABLE_PREFIX.'role_action';
		$query = $this->DatabaseHandler->Query($sql);
		$privilege_list=$query->GetAll();


		$options=array(
		array('name'=>'普通用户','value'=>'normal')
		);
		$type_select=FormHandler::Select('type',$options);

		$privileges=explode(',',$role_info['privilege']);
		foreach($privilege_list as $key=>$privilege)
		{
			if($privilege['allow_all']==1)
			{
				$privilege['disabled']=" disabled";
			}

			$module_name=isset($this->ModuleList[$privilege['module']])
			?$this->ModuleList[$privilege['module']]
			:"z.<b>[其它]</b>模块权限";

			if(in_array($privilege['id'],$privileges) or
			$privileges[0]=="*" or
			$privilege['allow_all']==1)
			{
				$privilege['checked']=" checked";
			}

			$privilege['link']="admin.php?mod=role_action&code=modify&id=".$privilege['id'];

			$privilege['name']=strpos($privilege['action'],"_other")!==false?"<font color='#660099'>{$privilege['name']}</font>":$privilege['name'];
			$module_list[$module_name][]=$privilege;
		}
						include $this->TemplateHandler->Template('admin/admin/role_info');
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
			$this->Messager("添加成功",'admin.php?mod=role');
		}
		else
		{
			$this->Messager("添加失败");
		}

	}

	function run($id=0,$messager=true)
	{
		
		$id = (int) ($this->ID ? $this->ID : $id);
		
		if ($id < 1) {
			$messager && $this->Messager("请先指定一个ID",null);
			return false;
		}

		Load::logic('task');
		$TaskLogic = new TaskLogic();
		
		$TaskLogic->run($id);
		$messager && $this->Messager("已成功执行",'',5);

		return true;
	}


	function Modify()
	{
		$sql="SELECT * FROM ".TABLE_PREFIX.'task'." where id='{$this->ID}'";
		$query = $this->DatabaseHandler->Query($sql);
		$task=$query->getRow();
		if ($task==false)
		{
			$this->Messager("任务已经不存在");
		}
		$task['filename'] = str_replace(array('..', '/', '\\'), array('', '', ''), $task['filename']);
		$task['minute'] = explode("\t", $task['minute']);

		$weekdaylist=array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");
		$weekdayselect = $dayselect = $hourselect = $minuteselect = '';

		for($i = 0; $i <= 6; $i++) {
			$weekdayselect .= "<option value=\"$i\" ".($task['weekday'] == $i ? 'selected' : '').">".$weekdaylist[$i]."</option>";
		}

		for($i = 1; $i <= 31; $i++) {
			$dayselect .= "<option value=\"$i\" ".($task['day'] == $i ? 'selected' : '').">$i</option>";
		}

		for($i = 0; $i <= 23; $i++) {
			$hourselect .= "<option value=\"$i\" ".($task['hour'] == $i ? 'selected' : '').">$i</option>";
		}

		for($i = 0; $i < 12; $i++) {
			$minuteselect .= '<select name="minutenew[]"><option value="-1">*</option>';
			for($j = 0; $j <= 59; $j++) {
				$minuteselect .= "<option value=\"$j\" ".($task['minute'][$i] != '' && $task['minute'][$i] == $j ? 'selected' : '').">".sprintf("%02d", $j)."</option>";
			}
			$minuteselect .= '</select>'.($i == 5 ? '<br>' : ' ');
		}
		include $this->TemplateHandler->Template('admin/task_info');
	}


	
	function DoModify()
	{
		$sql="SELECT * FROM ".TABLE_PREFIX.'task'." where id='{$this->ID}'";
		$query = $this->DatabaseHandler->Query($sql);
		$task=$query->getRow();
		if ($task==false)
		{
			$this->Messager("任务已经不存在");
		}
		$task['filename'] = str_replace(array('..', '/', '\\'), array('', '', ''), $task['filename']);
		$task['minute'] = explode("\t", $task['minute']);

		$daynew = get_param('daynew');
		$weekdaynew = get_param('weekdaynew');
		$daynew = $weekdaynew != -1 ? -1 : $daynew;

		$minutenew = get_param('minutenew');
		if(is_array($minutenew)) {
			sort($minutenew = array_unique($minutenew));
			foreach($minutenew as $key => $val) {
				if($val < 0 || $val > 59) {
					unset($minutenew[$key]);
				}
			}
			$minutenew = implode("\t", $minutenew);
		} else {
			$minutenew = '';
		}

		$filenamenew = get_param('filenamenew');
		$hournew = get_param('hournew');
		if(preg_match("/[\\\\\/\:\*\?\"\<\>\|]+/", $filenamenew)) {
			$this->Messager("计划任务文件名不正确",-1);
		} 							elseif($weekdaynew == -1 && $daynew == -1 && $hournew == -1 && $minutenew == '') {
			$this->Messager("时间设置不正确",-1);
		}
		$sql="UPDATE ".TABLE_PREFIX.'task'." SET weekday='$weekdaynew', day='$daynew', hour='$hournew', minute='$minutenew', filename='".trim($filenamenew)."' WHERE id='{$this->ID}'";
		$this->DatabaseHandler->Query($sql);

		Load::logic('task');
		$TaskLogic=new TaskLogic();
		$TaskLogic->nextRun($task);
		$this->Messager("编辑成功","admin.php?mod=task&code=list");
	}
	
	function doBatchModify()
	{
		$timestamp=time();

		$id = get_param('id');
		$delete = get_param('delete');
		if(false != ($ids = $this->DatabaseHandler->BuildIn($delete,""))) {
			$this->DatabaseHandler->Query("DELETE FROM ".TABLE_PREFIX.'task'." WHERE id IN ($ids) AND `type`='user'");
			$this->DatabaseHandler->Query("DELETE FROM ".TABLE_PREFIX.'task_log'." WHERE task_id IN ($ids)");
		}

		$namenew = get_param('namenew');
		$availablenew = get_param('availablenew');
		if(is_array($namenew)) {
			foreach($namenew as $id => $name) {
				$this->DatabaseHandler->Query("UPDATE ".TABLE_PREFIX.'task'." SET name='".$namenew[$id]."', available='".$availablenew[$id]."' ".($availablenew[$id] ? '' : ', nextrun=\'0\'')." WHERE id='$id'");
			}
		}

		$newname = get_param('newname');
		if($newname) {
			$this->DatabaseHandler->Query("INSERT INTO ".TABLE_PREFIX.'task'."(name, type, available, weekday, day, hour, minute, nextrun)
				VALUES ('".addslashes($newname)."', 'user', '0', '-1', '-1', '-1', '', '$timestamp')");
		}
		$this->Messager("计划任务成功更新","admin.php?mod=task&code=list");
	}

}

?>