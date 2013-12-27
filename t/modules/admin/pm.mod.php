<?php
/**
 * 文件名：topic.mod.php
 * 版本号：1.0
 * 最后修改时间：2009年9月28日 14时10分42秒
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 微博模块
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $TopicLogic;	

	function ModuleObject($config)
	{
		$this->MasterObject($config);

		
		$this->TopicLogic = Load::logic('topic', 1);
		
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{	
			case 'dopmsend':
				$this->DoPmSend();
				break;
			case 'pmsend':
				$this->PmSend();
				break;
			case 'delmsg':
				$this->delMsg();
				break;
			case 'delete':
				$this->Delete();
				break;
			default:
				$this->Code = 'pm_manage';
				$this->Main();
				break;
		}
		$body = ob_get_clean();
		
		$this->ShowBody($body);
		
	}

	function Main()
	{
		
		$per_page_num = min(500,max((int) $_GET['per_page_num'],(int) $_GET['pn'],20));
		$where_list = array();
		$query_link = 'admin.php?mod=pm';
		
		$username = trim($this->Get['username']);
		$keyword  = trim($this->Get['keyword']);
		$tousername = trim($this->Get['tousername']);
		$where_list['inbox'] = " `folder` = 'inbox' ";
		if($username)
		{
			$where_list['msgnickname'] = "`msgnickname`='{$username}'";
			$query_link .= "&username=".urlencode($username);	
		}
		if($tousername)
		{
			$where_list['tonickname'] = "`tonickname`='{$tousername}'";
			$query_link .= "&tousername=".urlencode($tousername);	
		}
		if($keyword)
		{
			$where_list['keyword'] = build_like_query('subject,message',$keyword);
			$query_link .= "&keyword=".urlencode($keyword);
		}
		
		$where = (empty($where_list)) ? null : ' WHERE '.implode(' AND ',$where_list).' ';

		$sql = " select count(*) as `total_record` from `".TABLE_PREFIX."pms` {$where}";
		$result = mysql_query($sql); 
		$total_records = mysql_fetch_array($result);
		$total_record = $total_records[0];

		$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'array',),'20 50 100 200,500');		

		$sql = " select *,m1.nickname as msgnickname,m2.nickname as tonickname from `".TABLE_PREFIX."pms` p 
				 left join `".TABLE_PREFIX."members` m1 on m1.uid = p.msgfromid 
				 left join `".TABLE_PREFIX."members` m2 on m2.uid = p.msgtoid 
				 {$where} order by `pmid` desc {$page_arr['limit']} ";
		$query = $this->DatabaseHandler->Query($sql);	

		$pm_list=array();
		while($row=$query->GetRow())
		{	
			$pm_list[]=$row;
			
		}
		
		include($this->TemplateHandler->Template('admin/pm'));
		
	}
	
	
	function PmSend(){
				$per_page_num = min(500,max((int) $_GET['per_page_num'],(int) $_GET['pn'],20));
		$query_link = 'admin.php?mod=pm&code=pmsend';
		$param = array(
		    'per_page_num' => $per_page_num,
		    'query_link' => $query_link,
		);
		$return = Load::logic('pm',1)->getNotice($param);
		extract($return);
		
		
		include($this->TemplateHandler->Template('admin/admin_pmsend'));
	}
	
	
	function DoPmSend(){
		load::logic("pm");
		$PmLogic = new PmLogic();
		
		$return = $PmLogic->doPmSend($this->Post);
		$return = $return ? $return : '发送成功';
		
		$this->Messager($return,"admin.php?mod=pm&code=pmsend");
	}
	
	
	function delMsg(){
		$ids = (array) ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);
		if(!$ids) {
			$this->Messager("请指定要删除的对象");
		}
		
		load::logic('pm');
		$PmLogic = new PmLogic();
		
		foreach ($ids as $key=>$value) {
			if($value==''){continue;}
			$PmLogic->delNotice($value);
		}
		
		$this->Messager('操作成功',"admin.php?mod=pm&code=pmsend");
	}

	function Delete()
	{
		$ids = (array) ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);
		
		if(!$ids) {
			$this->Messager("请指定要删除的对象");
		}
		
		$pmid_list = jimplode($ids);

		load::logic('pm');
		$PmLogic = new PmLogic();
		
		$query  = $this->DatabaseHandler->Query("select distinct msgfromid,msgtoid,plid from ".TABLE_PREFIX."pms where pmid in ($pmid_list)");
		
		$sql = "delete from `".TABLE_PREFIX."pms` where `pmid` in ($pmid_list)";
		$this->DatabaseHandler->Query($sql);
		
		while ($rsdb = $query->GetRow()){
			$PmLogic->setNewList($rsdb['msgfromid'],$rsdb['msgtoid'],$rsdb['plid']);
			if($rsdb['msgfromid'] != $rsdb['msgtoid']){
				$PmLogic->setNewList($rsdb['msgtoid'],$rsdb['msgfromid'],$rsdb['plid']);
			}
		}

		$this->Messager($return ? $return : "操作成功");
	}
		
}

?>
