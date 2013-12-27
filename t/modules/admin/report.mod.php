<?php
/**
 * 文件名：report.mod.php
 * 版本号：1.0
 * 最后修改时间：2009年9月28日 14时10分42秒
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 举报模块
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
			case 'batch_process':
				$this->BatchProcess();
				break;			
			
			default:
				$this->Code = 'report_manage';
				$this->Main();
				break;
		}
		$body = ob_get_clean();
		
		$this->ShowBody($body);
		
	}

	function Main()
	{
		$report_config = ConfigHandler::get('report');
		
		$per_page_num = min(500,max((int) $_GET['per_page_num'],(int) $_GET['pn'],20));
		$where_list = array();
		$query_link = 'admin.php?mod=report';
		
				$keyword = trim($this->Get['keyword']);
		if ($keyword) {
			$_GET['highlight'] = $keyword;

			$where_list['keyword'] = build_like_query('content',$keyword);
			$query_link .= "&keyword=".urlencode($keyword);
		}
				$username = trim($this->Get['username']);
		if ($username) {
			$where_list['username'] = "`username`='{$username}'";
			$query_link .= "&username=".urlencode($username);
		}
				$reason = (int) $this->Get['reason'];
		$reason_arr[$reason] = " selected ";
		if($reason){
			$where_list['reason'] = "`reason`='{$reason}'";
			$query_link .= "&reason=$reason";
		}
				$result = isset($this->Get['result']) ? $this->Get['result'] : '';
		$result_arr[$result] = " selected ";
		if($result != ''){
			$where_list['result'] = "`process_result`='{$result}'";
			$query_link .= "&result=$result";
		}
				$timefrom = $this->Get['timefrom'];
		if($timefrom){
			$str_time_from = strtotime($timefrom);
			$where_list['timefrom'] = "`dateline`>'$str_time_from'";
			$query_link .= "&timefrom=".$timefrom;
		}
				$timeto = $this->Get['timeto'];
		if($timeto){
			$str_time_to = strtotime($timeto);
			$where_list['timeto'] = "`dateline`<'$str_time_to'";
			$query_link .= "&timeto=".$timeto;
		}
		
		$where = (empty($where_list)) ? null : ' WHERE '.implode(' AND ',$where_list).' ';
		
		$sql = " select count(*) as `total_record` from `".TABLE_PREFIX."report` {$where} ";
		$total_record = DB::result_first($sql);
		
		$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'array',),'20 50 100 200 500');		

		$sql = " select * from `".TABLE_PREFIX."report` {$where} order by `id` desc {$page_arr['limit']} ";
		$query = $this->DatabaseHandler->Query($sql);
		$report_list = array();

		$TopicLogic = Load::logic('topic', 1);
		$deleted_tid = array();
		while (false != ($row = $query->GetRow())) 
		{	
			$row['topic_list'] = $TopicLogic->Get($row['tid']);
			
			if(!$row['topic_list']){
				$deleted_tid[$row['id']] = $row['id'];
				continue;
			}
			
			if($row['topic_list']['type'] == 'forward' && $row['topic_list']['roottid'] > 0){
				$row['topic_list']['root_topic'] =$TopicLogic->Get($row['topic_list']['roottid']);
			}
			
			$row['type_show'] = $report_config['type_list'][$row['type']];
			$row['reason_show'] = $report_config['reason_list'][$row['reason']];
			
			$row['process_result_show'] = $report_config['process_result_list'][$row['process_result']];
			if($row['process_time']) {
				$row['process_time'] = my_date_format($row['process_time']);
				$row['process_result_show'] = "[{$row['process_time']}]" . $row['process_result_show'];
			}
			$report_list[] = $row;
		}
		
		if($deleted_tid){
			DB::query("delete from `".TABLE_PREFIX."report` where id in (".jimplode($deleted_tid).")");
		}

		include($this->TemplateHandler->Template('admin/report'));
	}
	
	function BatchProcess()
	{
		load::logic('topic_manage');
		$TopicManage = new TopicManageLogic();
		
		$PmLogic = load::logic('pm',1);
		$managetype = get_param('managetype');
		foreach ($managetype as $key=>$val) {
			if(!$key || !$val){
				continue;
			}
			$sql = "select r.*,m.nickname,t.tid as ttid,t.content as tcontent from `".TABLE_PREFIX."report` r  
					left join `".TABLE_PREFIX."members` m on m.uid = r.uid 
					left join `".TABLE_PREFIX."topic` t on t.tid = r.tid 
					where r.id = '$key'";
			$query = $this->DatabaseHandler->Query($sql);
			$report = $query->GetRow();
			
						$this->DatabaseHandler->Query("delete from `".TABLE_PREFIX."report` where id = '$key' ");
			
			if(!$report['ttid']){
				continue;
			}
			$TopicManage->doManage($report['ttid'],$val);
			$do = $val == 1 ? '正常显示' : '删除微博';
			$pm_post = array(
				'message' => '管理员<a href="index.php?mod='.MEMBER_ID.'" href="_blank">'.MEMBER_NICKNAME.'</a>已对您举报的微博【'.cut_str($report['tcontent'],20).'】做了'.$do.'的处理，感谢你对本站维护做出的贡献。',
				'to_user' => $report['nickname'],
			);
						$PmLogic->pmSend($pm_post);
		}
		$this->Messager("操作成功");
	}
		
}

?>
