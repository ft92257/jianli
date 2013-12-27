<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename mysql.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 1349123278 1470375727 2922 $
 *******************************************************************/


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

if(!defined("ERROR_TABLE_NOT_EXIST"))	define("ERROR_TABLE_NOT_EXIST",	1146);
if(!defined("ERROR_KEY_DUPLICATE"))		define("ERROR_KEY_DUPLICATE",	1062);
if(!defined("ERROR_UNKNOWN_COLUMN"))	define("ERROR_UNKNOWN_COLUMN",	1054);
class MysqlLogic
{
	var $DatabaseHandler=null;
	var $tableName="";
	var $fieldList=array();
	
	function MysqlLogic()
	{
		
		$this->RegistryObj("DatabaseHandler");
	}
	
	
	function alterTable($table_name,$field_list,$sql="")
	{
		$table_field_list=$this->DatabaseHandler->SetTable($table_name);
		$sqls="ALTER TABLE {$table_name} ";
		foreach ($field_list as $field=>$info)
		{
			if(($key=array_search($field,$table_field_list))===false)
			{
				$sql_l[]="ADD ".preg_replace("/,\s*([a-z])/i",",ADD \\1",$info);
			}
			else
			{
				unset($table_field_list[$key]);
			}
		}
		if(!empty($table_field_list))
		{
			foreach ($table_field_list as $drop_field)
			{
							}
		}
		if(count($sql_l)<1)return false;
		$sqls.=implode(",\r\n\t",$sql_l);
		$query=$this->DatabaseHandler->Query($sqls);
		if($sql!="" && $query)$query = $this->DatabaseHandler->Query($sql);
		return $query;
	}
	function createTable($table_name,$field_list,$sql="")
	{
		$sqls="CREATE TABLE `$table_name` (".implode(',',$field_list).")";
		$sqls.=($this->DatabaseHandler->GetVersion() > '4.1' 
		? " ENGINE=MyISAM DEFAULT CHARSET=".$this->DatabaseHandler->Charset : 
		" TYPE=MyISAM");
		$query=$this->DatabaseHandler->Query($sqls);
		if($sql!="" && $query)$query = $this->DatabaseHandler->Query($sql);
		return $query;
	}
	function RegistryObj($objName)
	{		
		$this->DatabaseHandler=&Obj::registry($objName);
	}
	
	function createOrAlterTable($table_name,$field_list,$sql='')
	{
		$errno=$this->DatabaseHandler->GetLastErrorNo();
		switch ($errno)
		{
			case ERROR_TABLE_NOT_EXIST:
				$query=$this->createTable($table_name,$field_list);
				break;
			case ERROR_UNKNOWN_COLUMN:
				$query=$this->alterTable($table_name,$field_list);
				break;
			default:
				$query= null;
				break;
		}
		if($sql!="" && $query)$query = $this->DatabaseHandler->Query($sql);
		return $query;
	}
	
	function query($sql,$table_name='',$field_list=array())
	{
		if(empty($field_list))$field_list=$this->fieldList;
		if(empty($table_name))$table_name=$this->tableName;
		$query=$this->DatabaseHandler->Query($sql,"SKIP_ERROR");
		if(!$query && !empty($table_name) && !empty($field_list))
		{
			$query=$this->createOrAlterTable($table_name,$field_list,$sql);
		}
		return $query;
	}
}
?>