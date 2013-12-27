<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename task.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-04 18:49:37 1977414954 1063995350 6768 $
 *******************************************************************/


 
 
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class TaskLogic
{
	var $DatabaseHandler=null;

	var $ID=0;

	var $timestamp=0;

	var $nextRun=0;

	var $cacheFile='task_next_run';
	
	var $task=array();

	function TaskLogic()
	{
		$this->timestamp=time();
		$this->DatabaseHandler=&Obj::registry("DatabaseHandler");
	}

	function setID($id)
	{
		$this->ID=(int)$id;
	}

	function run($id=0)
	{
		
		$this->setID($id);
		$sql="SELECT * FROM ".TABLE_PREFIX.'task'." WHERE ".($this->ID ? "id='{$this->ID}'" : "available>'0' AND nextrun<='{$this->timestamp}'")." order by nextrun LIMIT 1";
		$query = $this->DatabaseHandler->Query($sql);
		if($task = $query->GetRow()) 
		{
			$this->task=$task;
			$lock=ConfigHandler::get('task','lock');

			$task['filename'] = str_replace(array('..', '/', '\\'), '', $task['filename']);
			$taskfile = './include/task/'.$task['filename'];
			

			if($lock > ($this->timestamp - 600)) {
				return null;
			} else {
				ConfigHandler::set('task','lock',time());
			}
			@ignore_user_abort(true);

			$task['filename'] = str_replace(array('..', '/', '\\'), array('', '', ''), $task['filename']);
			$task['minute'] = explode("\t", $task['minute']);
			$this->nextRun($task);
			
			if(!include_once $taskfile) {
				$this->log("计划任务 {$task['name']} 文件　{$taskfile}　已经不存在",E_USER_ERROR,0);
			} else {
			
				$log_id=$this->log("执行超时，请检查任务脚本。",E_USER_ERROR,0);
								list($usec, $sec) = explode(" ", microtime());
				$start=((float)$usec + (float)$sec);
				
				$TaskItem=new TaskItem();
				$TaskItem->run();
				
				list($usec, $sec) = explode(" ", microtime());
				$exec_time=((float)$usec + (float)$sec)-$start;
				
				$this->logUpdate($log_id,$TaskItem->log['message'],$TaskItem->log['error'],$exec_time);
			}
			
			@ConfigHandler::set('task','lock',0);
		}

		$this->writeNextRunTime();
	}

		function writeNextRunTime() {
		$query = $this->DatabaseHandler->Query("SELECT nextrun FROM ".TABLE_PREFIX.'task'." WHERE available>'0' ORDER BY nextrun LIMIT 1");
		$row = $query->GetRow();
		$nextruntime = $row['nextrun'] ? $row['nextrun'] : (time() + 1800);

		ConfigHandler::set('task','nextrun',$nextruntime);
	}

	function nextRun($task) {

		if(empty($task)) return false;

		list($yearnow, $monthnow, $daynow, $weekdaynow, $hournow, $minutenow) = explode('-', date('Y-m-d-w-H-i', $this->timestamp));

		if($task['weekday'] == -1)
		{
			if($task['day'] == -1)
			{
				$firstday = $daynow;
				$secondday = $daynow + 1;
			}
			else
			{
				$firstday = $task['day'];
				$secondday = $task['day'] + date('t', $this->timestamp);
			}
		}
		else
		{
			$firstday = $daynow + ($task['weekday'] - $weekdaynow);
			$secondday = $firstday + 7;
		}

		if($firstday < $daynow)
		{
			$firstday = $secondday;
		}

		if($firstday == $daynow)
		{
			$todaytime = $this->todayNextRun($task);
			if($todaytime['hour'] == -1 && $todaytime['minute'] == -1)
			{
				$task['day'] = $secondday;
				$nexttime = $this->todayNextRun($task, 0, -1);
				$task['hour'] = $nexttime['hour'];
				$task['minute'] = $nexttime['minute'];
			}
			else
			{
				$task['day'] = $firstday;
				$task['hour'] = $todaytime['hour'];
				$task['minute'] = $todaytime['minute'];
			}
		}
		else
		{
			$task['day'] = $firstday;
			$nexttime = $this->todayNextRun($task, 0, -1);
			$task['hour'] = $nexttime['hour'];
			$task['minute'] = $nexttime['minute'];
		}

		$nextrun = mktime($task['hour'], $task['minute'], 0, $monthnow, $task['day'], $yearnow);
		$availableadd = $nextrun > $this->timestamp ? '' : ', available=\'0\'';
		$this->DatabaseHandler->Query("UPDATE ".TABLE_PREFIX.'task'." SET lastrun='$this->timestamp', nextrun='$nextrun' $availableadd WHERE id='{$task['id']}'");
		return true;
	}

	function todayNextRun($task, $hour = -2, $minute = -2)
	{
		$hour = $hour == -2 ? date('H', $this->timestamp) : $hour;
		$minute = $minute == -2 ? date('i', $this->timestamp) : $minute;

		$nexttime = array();

		if($task['hour'] == -1 && !$task['minute'])
		{
			$nexttime['hour'] = $hour;
			$nexttime['minute'] = $minute + 1;
		}
		elseif($task['hour'] == -1 && $task['minute'] != '')
		{
			$nexttime['hour'] = $hour;
			if(($nextminute = $this->nextMinute($task['minute'], $minute)) === false)
			{
				++$nexttime['hour'];
				$nextminute = $task['minute'][0];
			}
			$nexttime['minute'] = $nextminute;
		}
		elseif($task['hour'] != -1 && $task['minute'] == '')
		{
			if($task['hour'] < $hour)
			{
				$nexttime['hour'] = $nexttime['minute'] = -1;
			}
			elseif($task['hour'] == $hour)
			{
				$nexttime['hour'] = $task['hour'];
				$nexttime['minute'] = $minute + 1;
			}
			else
			{
				$nexttime['hour'] = $task['hour'];
				$nexttime['minute'] = 0;
			}
		}
		elseif($task['hour'] != -1 && $task['minute'] != '')
		{
			$nextminute = $this->nextMinute($task['minute'], $minute);
			if($task['hour'] < $hour || ($task['hour'] == $hour && $nextminute === false))
			{
				$nexttime['hour'] = -1;
				$nexttime['minute'] = -1;
			}
			else
			{
				$nexttime['hour'] = $task['hour'];
				$nexttime['minute'] = $nextminute;
			}
		}

		return $nexttime;
	}

	function nextMinute($nextminutes, $minutenow)
	{
		foreach($nextminutes as $nextminute)
		{
			if($nextminute > $minutenow)
			{
				return $nextminute;
			}
		}
		return false;
	}
	
	function log($message,$error,$exec_time)
	{
		$ip=client_ip();
		$message=str_replace("'","\'",$message);
		$agent=str_replace("'","\'",$_SERVER['HTTP_USER_AGENT']);
		$sql="
		insert into ".TABLE_PREFIX.'task_log'."
		(message, error, task_id,exec_time,dateline, 
		ip, username, uid,`agent`)
		values
		('$message', '$error', '{$this->task['id']}',$exec_time,$this->timestamp, 
		'$ip', '".MEMBER_NAME."', '".MEMBER_ID."','$agent')";
		$query = $this->DatabaseHandler->Query($sql);
		return $this->DatabaseHandler->Insert_ID();
	}
	
	function logUpdate($log_id,$message,$error,$exec_time)
	{
		$ip=client_ip();
		$message=str_replace("'","\'",$message);
		$sql="
		update ".TABLE_PREFIX.'task_log'."
		set 
			message='$message',
		 	error='$error', 
		 	task_id='{$this->task['id']}',
		 	exec_time=$exec_time,
		 	dateline=$this->timestamp, 
			ip='$ip', 
			username='".MEMBER_NAME."', 
			uid='".MEMBER_ID."'
		where id='{$log_id}'";
		$query = $this->DatabaseHandler->Query($sql);
		return $this->DatabaseHandler->AffectedRows();
	}



	function add()
	{

	}

	function modify()
	{

	}
}

?>
