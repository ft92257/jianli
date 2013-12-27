<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename company.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-06-20 15:46:10 314667264 1240968801 8521 $
 *******************************************************************/




if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		if (!$this->Config['department_enable']){
			$this->Messager("网站没有开启该功能",null);
		}
		$this->CPLogic = Load::logic('cp',1);		
		$this->TopicLogic = Load::logic('topic',1);
		$this->Execute();		
	}

	
	function Execute()
	{
		ob_start();
		switch ($this->Code){
			case 'add':
				$this->Add();
			case 'del':
				$this->Del();
			default:
				$this->main();
				break;			
		}
		$body=ob_get_clean();
		$this->ShowBody($body);
	}
	
	function Add()
	{
		$message = empty($this->Post['message']) ? '' : trim($this->Post['message']);
		if (empty($message)) {
			$this->Messager('至少要写点什么吧');
		}
				$message = getstr($message, 800, 1, 1);
		$data = array(
			'uid' => (int)$this->Post['uid'],
			'cpid' => (int)$this->Post['cpid'],
			'username' => $this->Post['nickname'],
			'type' => 0,
			'message' => $this->Post['message'],
			'dateline' => TIMESTAMP,
		);
		$return = DB::insert('bulletin', $data, true);
		if($return){$this->Messager("添加成功",'',1);}
	}

	function Del()
	{
		$id = $this->Get['id'];
		$return = DB::query("DELETE FROM ".DB::table('bulletin')." WHERE id = '$id'");
		if($return){$this->Messager("删除成功",'',1);}
	}
	
	
	function main()
	{
		if(MEMBER_ID < 1) {
			$this->Messager("您无权查看该页面",null);
		}
		$dotable = 'company';
		global $_J;
				if(MEMBER_STYLE_THREE_TOL){
			$my_member = $this->TopicLogic->GetMember(MEMBER_ID);
		}
		$member = $_J['member'];
		$d_c_name = $this->Config['default_company'] ? $this->Config['default_company'] : '单位';
		if($member['companyid']<1){
			$this->Messager("您还不属于任何".$d_c_name."，请先加入某个".$d_c_name."！",'index.php?mod=settings&code=base');
		}
		$id = isset($this->Get['id']) ? max(0,(int)$this->Get['id']) : ($member['companyid'] ? $member['companyid'] : 0);
		$com_info = ($id > 0) ? $this->CPLogic->Getrow($id) : array('image' =>'images/qun_def_b.jpg','name' => $this->Config['site_name']);
		if(empty($com_info['id'])){$this->Messager("您要查找的".$d_c_name."不存在！");}
				$com_my_info = $com_info;
		$this->Title = ($id == $member['companyid'] ? '我的'.$d_c_name : $d_c_name.'微博').' - '.$com_my_info['name'];
						$c_department = $this->CPLogic->get_c_department('cid',$id);
						$is_ml = $this->CPLogic->isML($member['uid'],$id,'company');
		if($this->Code == 'bulletin' && $is_ml){
			$bulletin_list = array();
						$query = DB::query("SELECT * FROM ".DB::table('bulletin')." WHERE type = 0 AND cpid = '".$id."' ORDER BY id DESC");
			while($val=DB::fetch($query))
			{
				$val['dateline'] = my_date_format2($val['dateline']);
				$bulletin_list[] = $val;
			}
		}else{
		$cp_list = array();
				if(trim($this->Get['view']) == 'all'){
			$notall = false;
			$query = DB::query("SELECT * FROM ".DB::table('company')." ORDER BY topiccount DESC");
		}else{
			$notall = true;
			$query = DB::query("SELECT * FROM ".DB::table('company')." WHERE parentid = '$id' ORDER BY topiccount DESC");
		}
		while($val=DB::fetch($query))
		{
		   $val['image'] = empty($val['image']) ? 'images/qun_def_b.jpg' : $val['image'];
		   $cp_list[] = $val;
		}
				$user_list = array();
		
		$user_list = Load::logic('topic', 1)->GetMember(" where companyid = '$id' and companyid > 0 order by topic_count desc,uid asc limit 500 ", "`uid`,`ucuid`,`username`,`face_url`,`face`,`aboutme`,`validate`,`validate_category`,`nickname`,`company`,`department`,`topic_count`");
		$cp_name = $d_c_name;
				if($this->Code == 'top'){$murl = '&code=top'; }else{$murl = '';}
		$cpnavurl = ($id > 0) ? '<a href="index.php?mod='.$dotable.$murl.'&id=0">'.$d_c_name.'首页</a>>>' : $d_c_name.'首页';
		if($com_info['upid']){
			$query = DB::query("SELECT id,name FROM ".DB::table('company')." WHERE id IN(".$com_info['upid'].") ORDER BY id ASC");
			while($v=DB::fetch($query))
			{
				$cpnavurl .= "<a href='index.php?mod=".$dotable.$murl."&id=".$v['id']."'>".$v['name']."</a>".">>";
			}
		}
		$cpnavurl .= ($id > 0) ? $com_info['name'] : '';
		$options = array();
		$options['perpage'] = 20;
		$TopicListLogic = Load::logic('topic_list', 1);
				$sql = "select `uid` from `".TABLE_PREFIX."members` where companyid = '".$id."'";
		$query = $this->DatabaseHandler->Query($sql);
		$options['uid'] = array();
		while (false != ($row = $query->GetRow())) {
			$options['uid'][] = $row['uid'];
		}
		if($options['uid']){
			$info = $TopicListLogic->get_data($options);
		}
		$topic_list = array();
		$total_record = 0;
		if (!empty($info)) {
			$topic_list = $info['list'];
			$total_record = $info['count'];
			if($info['page']){
				$page_arr = $info['page'];
			}else{
				$page_arr = $getTypeTidReturn['page'];
			}
		}
		$topic_list_count = 0;
		if ($topic_list) {
			$topic_list_count = count($topic_list);
			if (!$topic_parent_disable) {
								$parent_list = $this->TopicLogic->GetParentTopic($topic_list, ('mycomment' == $this->Code));
							}
		}
				$announcement = DB::result_first("SELECT message FROM ".DB::table('bulletin')." WHERE type = 0 AND cpid = '".$id."' ORDER BY id DESC LIMIT 1");
		}
						$topic_user = $this->get_c_user($id,'topic_count');
		$fans_user = $this->get_c_user($id,'fans_count');
		if($this->Code == 'top'){
			include template('cp_top');
		}else{
			include template('company');
		}
	}
	function get_c_user($cid=0,$str='topic_count', $day=1, $limit=9, $cache_time=0) {
		if($cid>0){
			$day = (is_numeric($day) ? $day : 0);
			$limit = (is_numeric($limit) ? $limit : 0);
			$cache_time = (is_numeric($cache_time) ? $cache_time : 0);
			if($day < 1 || $limit < 1) {
				return false;
			}
			$time = $day * 86400;
			$cache_time = max(300, ($cache_time ? $cache_time : ($time / 24)));
			$cache_id = "data_block/get_c_user-{$cid}-{$str}-{$day}-{$limit}";
			if (false === ($list = Load::model('cache/file')->get($cache_id))) {
				$dateline = TIMESTAMP - $time;
				$list = array();
				
				$list = Load::logic('topic', 1)->GetMember(" where companyid = '{$cid}' order by {$str} DESC LIMIT {$limit} ", "`uid`,`ucuid`,`username`,`face_url`,`face`,`aboutme`,`validate`,`validate_category`,`nickname`");
				Load::model('cache/file')->set($cache_id, $list, $cache_time);
			}
		}else{
			$list = array();
		}
		return $list;
	}
}
?>
