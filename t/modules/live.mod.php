<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename live.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-18 15:49:15 876852901 1627273493 5127 $
 *******************************************************************/




if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	function ModuleObject($config)
	{
		$this->MasterObject($config);		
		Load::logic('live');
		$this->LiveLogic = new LiveLogic();		
		
		$this->TopicLogic = Load::logic('topic', 1);
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch ($this->Code){
			case 'view':
				$this->view();
				break;
			default:
				$this->main();
				break;			
		}
		$body=ob_get_clean();
		$this->ShowBody($body);
	}
	
	
	function main()
	{
				$view = empty($this->Get['view']) ? 'all' : trim($this->Get['view']);
		$time = time();		
		$gets = array(
			'mod' => 'live',
			'view' => $this->Get['view']
		);
		$page_url = 'index.php?'.url_implode($gets);
		$order_sql = ' ORDER BY lid DESC';
				$perpage = 10;
		switch ($view) {
			case 'go':
				$this->Title = '未开始';
				$where_sql = " starttime > '$time'";
				break;
			case 'on':
				$this->Title = '进行中';
				$where_sql = " starttime <= '$time' AND endtime >= '$time'";
				break;
			case 'no':
				$this->Title = '已完成';
				$where_sql = " endtime < '$time'";
				break;
			default:
				$this->Title = '全部';
				$where_sql = "";
				break;
		}
		
		$param = array(
			'where' => $where_sql,
			'order' => $order_sql,
			'perpage' => $perpage,
			'page_url' => $page_url
		);
		$live_info = $this->LiveLogic->get_list($param);
		$count = 0;
		$live_list = array();
		$page_arr['html'] = '';
		$uid_ary = array();
		if (!empty($live_info)) {
			$count = $live_info['count'];
			$live_list = $live_info['list'];
			$page_arr['html'] = $live_info['page']['html'];
		}
		$live_config = ConfigHandler::get('live');
		$live_config['des'] = nl2br($live_config['des']);
		$live_config['ads'] = nl2br($live_config['ads']);
		$user = $this->TopicLogic->GetMember($live_config['uid'], "`uid`,`username`,`face`,`fans_count`,`validate`,`validate_category`");
		if(empty($user_host)){
			$user_host = array();
			$user_host['face'] = $user['face'];
			$user_host['username'] = $user['username'];
			$user_host['fans_count'] = $user['fans_count'];
			$user_host['validate_html'] = $user['validate_html'];
			$user_host['followed'] = $this->LiveLogic->is_followed($live_config['uid']);
		}
		$member = jsg_member_info(MEMBER_ID);
		include template('live_index');
	}
	
	
	function view()
	{
		$lid = jget('id','int','G');
		if(!$this->LiveLogic->is_exists($lid)){return false;}
		$list = $this->LiveLogic->Getguest($lid);
		$this->item = 'live';
		$this->item_id = $item_id = $lid;
		$ltype = $this->Get['list'] ? $this->Get['list'] : $this->Get['type'];
		$live = $this->LiveLogic->id2liveinfo($lid,$list);
		if(!in_array($ltype,array('g','h'))){
			if($live['status_css'] == 'ico_notyet'){$ltype = 'g';}else{$ltype = 'h';}
		}
		$params = array(
			'item'    => 'live',
			'item_id' => $lid
		);
		$uids = array();
		$gets = array(
			'mod' => 'live',
			'code' => 'view',
			'type' => $ltype,
			'id' => $lid,
		);
		$page_url = 'index.php?'.url_implode($gets);
		$options = array(
			'page' => true,
			'perpage' => 20,
			'page_url' => $page_url,
		);
		if(empty($live)) {
			$this->Messager('当前直播不存在!');
		}elseif($live['all']){
			$defaust_value = '&nbsp;一起来说说#'.$live['livename'].'#吧';
			foreach($live['all'] as $key => $val){
				$uids[$key] = $key;
			}
			if($ltype == 'h'){
				$options['where'] = " uid IN(".jimplode($uids).") ";
			}else{
				$options['where'] = " uid NOT IN(".jimplode($uids).") ";
			}
			$content = '#'.$live['livename'].'#';
		}
		$is_live_hosts = (in_array(MEMBER_ID,$uids)) ? true : false;
		
		$live_config = ConfigHandler::get('live');

				if(MEMBER_STYLE_THREE_TOL){
			$member = $this->TopicLogic->GetMember(MEMBER_ID);
		}

		$param = array(
			'limit' => '5'
		);
		$live_info = $this->LiveLogic->get_list($param);		if (!empty($live_info)) {
			$live_count = $live_info['count'];
			$live_list = $live_info['list'];
		}
		Load::functions('app');
		$topic_info = app_get_topic_list('live', $lid, $options);
		$topic_list = array();
		if (!empty($topic_info)) {
			$topic_list = $topic_info['list'];
			$page_arr['html'] = $topic_info['page']['html'];
			$no_from = false;			if($ltype == 'h' && !empty($topic_list)){
				foreach($topic_list as $key => $val){
					$topic_list[$key]['user_css'] = 'live'.$this->LiveLogic->id2usertype($lid,$val['uid'],$list);
					$topic_list[$key]['user_str'] = '&nbsp;';
				}
			}
			$topic_count = $topic_info['count'];
		}
		$topic_count = $topic_count ? $topic_count : 0;
		$this->Title = '直播 - '.$live['livename'];
		include template("live");
	}
}
?>
