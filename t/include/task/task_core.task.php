<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename task_core.task.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 674129551 1283308957 1269 $
 *******************************************************************/


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}


/**
 * TASK对象核心
 *
 * @author 狐狸<foxis@qq.com>
 * @package www.tttuangou.net
 */
class TaskCore
{
	var $DatabaseHandler=null;
	
	var $log=array('message'=>'已成功执行','error'=>0);
	
	function TaskCore()
	{
		$this->DatabaseHandler=&Obj::registry("DatabaseHandler");
	}
	
	function SqlError($sql,$file='',$line='')
	{
		$this->log['message']="<b>SQL查询语句错误</b>".
				"\r\n<br><br>错误语句:<br>[{$line}]{$file}<code>$sql</code>".
				"\r\n<br><br>错误编号:".$this->DatabaseHandler->GetLastErrorNo().
				"\r\n<br><br>错误信息:<br>".$this->DatabaseHandler->GetLastErrorString()."<br>";

		$this->log['error']=E_USER_ERROR;
	}
	
	function log($message,$error=0)
	{
		$this->log['message']=$message;
		$this->log['error']=$error;
	}
	
	function request($url)
	{
		if(strpos($url,':/'.'/')===false) {
			$url=$GLOBALS['_J']['site_url'].'/'.$url;
		}
		
		if ((!$_SERVER['HTTP_USER_AGENT']) || (!$_COOKIE) || ('remote_script' == get_param('request_from'))) {
			dfopen($url,-1,$post,$cookie,true,3);
			@usleep(rand(10000,100000)); 		
		} else {
			$GLOBALS['iframe'] .="<iframe src='{$url}' border=0 width=0 height=0></iframe>";
		}
	}
}
?>