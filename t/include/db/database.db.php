<?php
/**
 * 文件名：database.db.php
 * 版本号：JishiGou 1.0.0 beta
 * 最后修改时间：2005年8月19日 9:45:13
 * 作者：狐狸<foxis@qq.com>
 * 功能描述：各种数据库的标准接口类
 */
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class DatabaseHandler
{
	
	var $ServerHost;
	
	var $ServerPort;

	
	var $Connection_ID; 
	
	
	var $SQL_Store;

	
	var $Cache;

	
	function DatabaseHandler($server_host, $server_port)
	{
		$this->ServerHost = $server_host;
		$this->ServerPort = $server_port;
		$this->Connection_ID = null;
		$this->SQL_Store = array();
		$this->_query_count = 0;
	}

	
	function SetConnectionId($connection_id)
	{
		$this->Connection_ID = $connection_id;
	}


	

	function GetConnectionId()
	{
		return $this->Connection_ID;
	}

	
	function GetQueryCount()
	{
		return sizeof($this->SQL_Store);
	}
	
	function SetSqlStore($sql)
	{
		$this->SQL_Store[] = $sql;
	}

	
	function GetSqlStore()
	{
		return $this->SQL_Store;
	}

	
	function GetHost()
	{
		return $this->ServerHost;
	}

	
	function IsConnected()
	{
		return $this->GetConnectionId() ? true : false;
	}

	
	function Debug() 
	{
		if(is_array($this->SQL_Store) && count($this->SQL_Store)) 
		{
			$sum=0;
			echo "		<table align='center' id=debugtable width=98% border=0 cellspacing=1 style='background:#828284;word-break: break-all'>
		<tr style='background:Darkred;height:30;Color:White'>
							<th width=30>NO</th>
							<th>Query</th>
							<th width=100>In File<br>From Line<br>ProcessTime</th>
							<th width=200>Explain SQL</th>
		</tr>";
			foreach($this->SQL_Store as $key=>$val) 
			{
				$key=$key+1;
				$sum+=$val['TIME'];
				$val['SQL']=str_replace("\s","&nbsp;",nl2br($val['SQL']));
				$val['SQL']=str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;",$val['SQL']);
				if($_GET['debug'])
				{
					$val['SQL'] = preg_replace('~([^\s])([\,\=])([^\s])~', '$1 $2 $3', $val['SQL']);
				}
				$val['FILE']=basename($val['FILE']);
				echo "		<tr style='background:#EEEEEE;Height:25;Text-Align:center'>
								<td>[$key]</td>
								<td align=left>{$val['SQL']}</td>
								<td>{$val['FILE']}<BR />
								Line : {$val['LINE']}<BR />
								Time : {$val['TIME']}</td>
								<td  align=left>{$val['explain']}</td>
		</tr>";
			}
			echo "		<tr style='background:#EEEEEE;Height:25;Text-Align:center'>
								<td colspan='6'><span style='font-size: 12px;'>本页面共有<FONT COLOR='#FF0000'>{$key}</FONT>个查询,查询总时间为:<FONT COLOR='#FF0000'>{$sum}</FONT></span></td>

		</tr>";
			echo "</table>";
			
			echo "<hr>";
		}
	}
}

?>