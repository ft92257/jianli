<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename recdtopic.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:35 1284525498 1515660989 4712 $
 *******************************************************************/

 


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $TopicLogic;
	var $TopicRecommendLogic;	

	function ModuleObject($config)
	{
		$this->MasterObject($config);
		Load::logic('topic_recommend');
		$this->TopicRecommendLogic = new  TopicRecommendLogic();
		
		$this->TopicLogic = Load::logic('topic', 1);
		
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{	
			case 'delete':
				$this->delete();
				break;			
			case 'edit':
				$this->edit();
				break;
		  	case 'doedit':
				$this->doedit();
				break;
		  	case 'onekey':
		  		$this->onekey();
		  		break;
			default:
				$this->Code = 'recdtopic_manage';
				$this->index();
				break;
		}
		$body = ob_get_clean();
		
		$this->ShowBody($body);
		
	}
	
	function index()
	{
		$per_page_num = min(500,max((int) $_GET['per_page_num'],(int) $_GET['pn'],20));
		$gets = array(
			'mod' => 'recdtopic',
			'pn' => $this->Get['pn'],
			'per_page_num' => $this->Get['per_page_num'],
			'keyword' => $this->Get['keyword'],
			'nickname' => $this->Get['nickname'],
		);
		$page_url = 'admin.php?'.url_implode($gets);
		$where_sql = ' 1 AND tr.tid>0 ';
		
				$keyword = trim($this->Get['keyword']);
		if ($keyword) {
			$_GET['highlight'] = $keyword;
			$where_sql .= " AND ".build_like_query('t.content,t.content2',$keyword)." ";
		}
		
				$nickname = trim($this->Get['nickname']);
		if ($nickname) {
			
			$sql = "select `username`,`nickname` from `".TABLE_PREFIX."members` where `nickname`='{$nickname}' limit 0,1";
			$query = $this->DatabaseHandler->Query($sql);
			$members=$query->GetRow();
			$where_sql .= " AND `username`='{$members['username']}' ";
		}
		
		$count = DB::result_first("SELECT COUNT(*) 
								   FROM ".DB::table('topic')." AS t  
								   LEFT JOIN ".DB::table('topic_recommend')." AS tr 
								   ON t.tid=tr.tid 
								   WHERE {$where_sql}");
		
		$topic_list = array();
		if ($count) {
			$page_arr = page($count,$per_page_num,$page_url,array('return'=>'array'));
			$query = DB::query("SELECT t.*,tr.dateline AS recd_time,tr.expiration   
								FROM  ".DB::table('topic')." AS t  
								LEFT JOIN ".DB::table('topic_recommend')." AS tr
								ON t.tid=tr.tid  
								WHERE {$where_sql}
								ORDER BY tr.dateline DESC 
								{$page_arr['limit']} ");
			while ($value = DB::fetch($query)) {
				$value['recd_time'] = my_date_format2($value['recd_time']);
				$topic_list[] = $this->TopicLogic->Make($value); 
			}
		}
		
		
		include template('admin/recdtopic');
	}
	
	function delete()
	{
		$ids = (array) ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);
		if(!$ids) {
			$this->Messager("请指定要删除的对象");
		}
		$this->TopicRecommendLogic->delete($ids);
		$this->Messager("操作成功了");
	}
	
		function edit()
	{
		$tid = intval($this->Get['tid']);
		$recd_levels = array(); 
		$topic_recd = $this->TopicRecommendLogic->get_info($tid);
		if (!empty($topic_recd)) {
			$topic_recd['expiration'] = empty($topic_recd['expiration']) ? '' : my_date_format($topic_recd['expiration'], 'Y-m-d ');
		}
		if ($topic_recd['item_id'] > 0) {
			if ($topic_recd['item'] == 'qun') {
				$recd_levels = $this->TopicRecommendLogic->recd_levels('admin_qun');
			} else if ($topic_recd['item'] == 'tag') {
				$recd_levels = $this->TopicRecommendLogic->recd_levels('tag');
			}
		} else {
			$recd_levels = $this->TopicRecommendLogic->recd_levels('topic');
		}
		include template('admin/recdtopic_edit');
	}
	
		function doedit()
	{
		$tid = intval($this->Post['tid']);
		$recd = intval($this->Post['recd']);
		
				if ($recd>4 || $recd < 0) {
			$this->Messager("推荐等级错误");
		}
		
		$expiration = jstrtotime(trim($this->Post['expiration']));
		$display_order = intval($this->Post['display_order']);
		
		$data = array(
			'recd' => $recd,
			'tid' => $tid,
			'expiration' => $expiration,
			'display_order' => $display_order,
		);
		$this->TopicRecommendLogic->modify($data, array('tid'=>$tid));
		$this->Messager("操作成功了", 'admin.php?mod=recdtopic');
	}
	
	function onekey()
	{
		$time = time();
		DB::query("DELETE FROM ".DB::table('topic_recommend')." WHERE expiration>0 AND expiration<=$time");
		$this->Messager("操作成功了", 'admin.php?mod=recdtopic');
	}
}



?>