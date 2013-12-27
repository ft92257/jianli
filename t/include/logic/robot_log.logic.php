<?php
/**
 *[JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 对搜索引擎的操作
 *
 * @author 狐狸<foxis@qq.com>
 * @package www.jishigou.net
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

include_once(ROOT_PATH . 'include/logic/mysql.logic.php');
Class RobotLogLogic extends MysqlLogic
{
	var $robotName="";
	var $tableName="";
	var $dateFormat="Y-m-d";
	var $date="";
	var $fieldList=array();
	function RobotLogLogic($robot_name)
	{
		$this->setRobotName($robot_name);
		$this->tableName=TABLE_PREFIX."robot_log";
		$this->date=date($this->dateFormat);
		$this->MysqlLogic();
	}
	
	function setRobotName($name)
	{
		$this->robotName=$name;
	}
	
	function statistic()
	{
		if(empty($this->tableName))return false;
		$timestamp=time();
		$sql="UPDATE ".$this->tableName." 
			set 
			`times`=`times`+1,
			`last_visit`=$timestamp
			where `name`='$this->robotName' and `date`='$this->date'";
		$query=$this->DatabaseHandler->Query($sql,"SKIP_ERROR");
		if($query==false && $this->DatabaseHandler->getlastErrorNo()==ERROR_TABLE_NOT_EXIST)
		{
			$this->createTable($this->tableName,$this->getFieldList(),$sql);
		}
		$result= $this->DatabaseHandler->AffectedRows();
		if($result>0)return true;
				$sql="insert into $this->tableName(`name`,`times`,`date`,`first_visit`,`last_visit`) 
		values('{$this->robotName}','1','$this->date','$timestamp','$timestamp')";
		$query = $this->DatabaseHandler->Query($sql);
		return (boolean)$this->DatabaseHandler->AffectedRows();
	}
	
	
	function getRobotName()
	{
		return $this->robotName;
	}
	function getFieldList()
	{                  
		$this->fieldList=array
		(
		     "name"=>"`name` char(50) NOT NULL", 
		     "date"=>"`date` date NOT NULL default '0000-00-00',UNIQUE KEY `name` (`name`,`date`)",  
		     "times"=>"`times` int(10) unsigned NOT NULL default '0'",
		     'first_visit'=>"`first_visit` int(10) unsigned NOT NULL default '0'",
		     'last_visit'=>"`last_visit` int(10) unsigned NOT NULL default '0'",
		 );
		return $this->fieldList;
	}
}
?>