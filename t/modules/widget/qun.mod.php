<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename qun.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:35 762309524 1519528472 2388 $
 *******************************************************************/



 

if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	
	var $TopicLogic = null;
	
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		
		$qun_setting = ConfigHandler::get('qun_setting');
		if (MEMBER_ROLE_TYPE != 'admin') {
			if (!$qun_setting['qun_open']) {
				$this->Messager('站点暂时不开放微群功能', 'index.php');
			}
		}
		
		
		$this->TopicLogic = Load::logic('topic', 1);
		
		Load::logic('qun');
		$this->QunLogic = new QunLogic();
		$this->Execute();
	}
	
		
	function Execute()
	{
		switch($this->Code)
		{
			case 'list':
				$this->DoList();
				break;
			default:
				$this->Main();
				break;
		}
		exit;
	}
	
	function Main()
	{
		
	}
	
	
	function DoList()
	{
		$qid = intval($this->Get['qid']);
		if (!$qid) {
			widget_error('Id is empty', 102);
		}
		$page = intval($this->Get['page']);
		$page_size = intval($this->Get['page_size']);
		$page_size = $page_size == 0 ? 10 : $page_size;

				if (!$this->QunLogic->is_exists($qid)) {
			widget_error('Id is invalid', 103);
		}
		
		$where_sql = " tq.item_id='{$qid}' ";
		$order_sql = " t.dateline DESC ";
				$total_record = DB::result_first("SELECT COUNT(*)  
								   		  FROM ".DB::table('topic')." AS t 
								   		  LEFT JOIN ".DB::table('topic_qun')." AS tq 
								   		  USING(tid)  
								   		  WHERE {$where_sql}");
		if ($total_record > 0) {
						$page_arr = $this->_page($total_record, $page_size);
			$query = DB::query("SELECT t.* 
								FROM ".DB::table('topic')." AS t 
								LEFT JOIN ".DB::table('topic_qun')." AS tq 
								USING(tid)  
								WHERE {$where_sql} 
								ORDER BY {$order_sql}
								LIMIT {$page_arr['offset']},{$page_arr['limit']}");
			$topic_list = array();
			while ($value = DB::fetch($query)) {
				$topic_list[] = $this->TopicLogic->Make($value);
			}
			$data = array('total_record' => $total_record, 'topic_list' => $topic_list,'page_arr'=>$page_arr);
			widget_output($data);
		} else {
						widget_error('List is empty', 104);
		}
	}
}
?>
