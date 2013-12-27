<?php
/**
 *
 * 当前在线的用户列表
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: sessions.mod.php 356 2012-03-19 03:14:21Z wuliyong $
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

			default:
				$this->Main();
				break;
		}
		$body = ob_get_clean();
		
		$this->ShowBody($body);
		

	}


	function Main()
	{
		$where_list=array();
		$where="";
		$query_link="admin.php?mod=sessions";
		
		
		
				$p=max((int)$this->Get['page'],1);
		$query_link.="&page=".$p;
		$pn=(int)$this->Get['pn']?(int)$this->Get['pn']:20;
		if($pn<1)$pn=20;
		$query_link.='&pn='.$pn;
		
		
		$uid = 0;
		$nickname = get_param('nickname');
		if($nickname) {
			$info = jsg_member_info($nickname, '', '`uid`');
			
			$uid = $info['uid'];
		}		
		
				$uid = $uid ? $uid : (int) get_param('uid');
		if ($uid > 0)
		{
			$where_list[]="uid = '$uid'";
			$query_link.="&uid=$uid";
		}
		
		
				Load::lib('form');
		$is_member_radio=FormHandler::YesNoRadio('is_member',$this->Get['is_member']);
		if(isset($this->Get['is_member']))
		{
			$is_member=(int)$this->Get['is_member'];
			$where_list[]=$is_member>0?"uid>0":"uid=0";
		}
		
				$ip=trim($this->Get['ip']);
		if (!empty($ip)) 
		{
			$ip_l=explode('.',$ip);
			$ips='';
			$i=0;
			$and = '';
			foreach ($ip_l as $_ip)
			{
				$i++;
				if($_ip=="*" || empty($_ip)) continue;
				$ips.= ($and."ip{$i}='$_ip'");
				$and=" and ";
			}
			$where_list[]=$ips;
		}

		if($this->Get['order_by'])$query_link.="&order_by=".$this->Get['order_by'];
		if($this->Get['order_type'])$query_link.="&order_type=".$this->Get['order_type'];

		$where = (empty($where_list)) ? null : ' WHERE '.implode(' AND ',$where_list).' ';
		

				$order_by_list = array
		(
			'order_by_default' => 'slastactivity',
			
			'slastactivity' => array
			(
				'name' => '最后访问时间',
				'order_by' => 'slastactivity',
			),
			'ip' => array
			(
				'name' => 'IP地址',
				'order_by' =>"concat_ws('.',ip1,ip2,ip3,ip4)",
			),		
			'uid' => array
			(
				'name' => '用户ID',
				'order_by' => 'uid',
			),		
		);
		$order_array = order($order_by_list,$query_link,array('display_un_href'=>true));
		$order = $order_array['order'];
		$order_html = $order_array['html'];

				$limit="";
		$offset=($p-1)*$pn;
		
				$sql="SELECT count(1) total from ".TABLE_PREFIX."sessions".$where;
		$query = $this->DatabaseHandler->Query($sql);
		$row=$query->GetRow();
		$total=$row['total'];

		
				$sql="SELECT * from ".TABLE_PREFIX."sessions".$where." ".$order." limit $offset,$pn";
		$query = $this->DatabaseHandler->Query($sql);
		$session_list=array();
		$uids = array();
		$ips = array();
		while (false != ($row=$query->GetRow())) 
		{
			$row['dateline']=my_date_format($row['slastactivity']);
			$row['ip']=sprintf("%s.%s.%s.%s",$row['ip1'],$row['ip2'],$row['ip3'],$row['ip4']);
			if($row['uid'] > 0) {
				$uids[$row['uid']] = $row['uid'];
			}
			$ips[$row['ip']] = $row['ip'];
			$session_list[]=$row;
		}
		
		$robot = array();
		if($ips && $this->Config['robot_enable']) {
						$sql="SELECT `ip`, `name` from ".TABLE_PREFIX."robot_ip where `ip` in ('".implode("','", $ips)."')";
			$query=$this->DatabaseHandler->Query($sql, "SKIP_ERROR");
			if($query) {
				while (false != ($row=$query->GetRow())) {
					$robot[$row['ip']] = $row['name'];
				}
			}
		}
		
		$users = array();
		if ($uids) {
			$query = DB::query("select `uid`, `username`, `nickname` from ".DB::table('members')." where `uid` in ('".implode("','", $uids)."')");
			while (false != ($row = DB::fetch($query))) {
				$users[$row['uid']] = $row;
			}
		}
		

		$pages=page($total,$pn,$query_link,array(),	"10 20 50 100 200 500");
		
		$action = "admin.php?mod=session&code=delete";
		include $this->TemplateHandler->Template('admin/sessions');

	}

}

?>