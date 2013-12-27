<?php
/**
 * 文件名：topic.mod.php
 * 版本号：1.0
 * 最后修改时间：2009年9月28日 14时10分42秒
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 微博话题模块
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $ShowConfig;

	var $CacheConfig;

	var $TopicLogic;

	var $ID = '';


	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->ID = (int) ($this->Post['id'] ? $this->Post['id'] : $this->Get['id']);

		$this->TopicLogic = Load::logic('topic', 1);

		$this->CacheConfig = ConfigHandler::get('cache');

		$this->ShowConfig = ConfigHandler::get('show');



		$this->Execute();

	}

	
	function Execute()
	{
		ob_start();
		if('fans' == $this->Code) {
			$this->Fans();
		}elseif ('follow' == $this->Code) {
			$this->Follow();
		} elseif ('top' == $this->Code) {
			$this->Top();
		} elseif ('group' == $this->Code) {
			$this->ViewGroup();
		} elseif (in_array($this->Code,array('new','hotforward','hotreply','newreply','tc'))) {
			$this->Hot();
		} elseif ('home' == $this->Code) {
			$this->_guestIndex();
		} elseif ('view' == $this->Code) {
			$this->View();
		} elseif ('photo' == $this->Code) {
			$this->Photo();
		} elseif (is_numeric($this->Code)) {
			$this->ID = (int) $this->Code;
			$this->View();
		} else {
			$this->Main();
		}
		$body=ob_get_clean();

		$this->ShowBody($body);
	}

	function Main()
	{
		$FormHandler = Load::lib('form', 1);
		$channel_enable = ConfigHandler::get('channel') && ConfigHandler::get('channels') ? true : false;

				if ('topic'==trim($this->Get['mod']) && empty($this->Code) && empty($this->Get['mod_original'])) {
						if (MEMBER_ID > 0) {
				$this->Code = 'myhome';
			} else {
				$this->_guestIndex();
				return ;
			}
		}

				$member = $this->_member();
		if(!$member) {
			$this->_guestIndex();
			return false;
		}

		$title = '';
				$per_page_num = 20;
		$topic_uids = $topic_ids = $order_list = $where_list = $params = array();
		$where = $order = $limit = "";
		$cache_time = 0;
		$cache_key = '';

				$options = array();
		$gets = array(
			'mod' => ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module),
			'code' => $this->Code,
			'type' => $this->Get['type'],
			'gid' => $this->Get['gid'],
			'qid' => $this->Get['qid'],
			'chid' => $this->Get['chid'],
			'view' => $this->Get['view'],
			'filter' => $this->Get['filter'],
		);
		$options['page_url'] = "index.php?".url_implode($gets);
		unset($gets['type']);
		$type_url = "index.php?".url_implode($gets);


		$params['uid'] = $uid = $member['uid'];

				$is_personal = ($uid == MEMBER_ID);
		$params['is_personal'] = $is_personal;

		$params['code'] = $this->Code;

				$code_ary = array (
			'myblog',
			'mycomment',
			'tocomment',
			'myhome',
			'myat',
			'myfavorite',
			'favoritemy',
			'tag',
					'qun',
			'recd',
			'other',
			'bbs',
			'cms',
			'department',
			'channel',
		);

		if (!in_array($params['code'], $code_ary)) {
						$params['code'] = 'myblog';
		}

				$page_str = $params['code'];
		if($params['code'] == 'bbs' || $params['code'] == 'cms') {
			$page_str = 'myhome'; 		}
		if (($show_topic_num = (int) $this->ShowConfig['topic'][$page_str]) > 0) {
			$per_page_num = $show_topic_num;
		}

		$options['perpage'] = $per_page_num;

				$groupname = '';
		$groupid = 0;


		$TopicListLogic = Load::logic('topic_list', 1);
		
		#if NEDU
		if (defined('NEDU_MOYO'))
		{
			nui('jsg')->cindex($this, $params, $topic_list_get);
		}
		#endif

				$tpl = 'topic_index';
		if ('myhome'==$params['code']) {
			$tpl = 'topic_myhome';
			
			$topic_selected = 'myhome';

						$type = get_param('type');
			if($type && !in_array($type, array('pic', 'video', 'music', 'vote', 'event', ))) {
				$type = '';
			}
			if($type) {
				$params['type'] = $type;
			}

						$gid = max(0, (int) get_param('gid'));
			if($gid) {
				$params['gid'] = $gid;
			}

						$topic_myhome_time_limit = 0;
			if($this->Config['topic_myhome_time_limit'] > 0) {
				$topic_myhome_time_limit = (TIMESTAMP - ($this->Config['topic_myhome_time_limit'] * 86400));
				if ($topic_myhome_time_limit > 0) {
					$options['dateline'] = $topic_myhome_time_limit;
				}
			}

						$options['uid'] = array($member['uid']);
			if ($member['uid'] == MEMBER_ID) {
				$cache_time = 600;
				$cache_key = "{$member['uid']}-topic-myhome-{$type}-{$gid}";
				$title = '我的首页';
				
								$refresh_time = max(30, (int) $this->Config['ajax_topic_time']);
				if(get_param('page') < 2 && ($member['lastactivity'] + $refresh_time < TIMESTAMP)) {
					$new_topic = Load::model('buddy')->check_new_topic($uid, 1);
					if($new_topic > 0) {
						cache_db('rm', "{$uid}-topic-%", 1);
					}
				}

								if ($gid) {
					$group_info = DB::fetch_first("SELECT *
												   FROM ".DB::table('group')."
												   WHERE uid=".MEMBER_ID." AND id='{$gid}' ");
					if (empty($group_info)) {
						$this->Messager("当前分组不存在", 'index.php?mod=myhome');
					}
					$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&code={$this->Code}&type={$this->Get['type']}&gid={$this->Get['gid']}" : "");
					$sql = "select * from `".TABLE_PREFIX."groupfields` where `gid`='{$gid}' and `uid`='".MEMBER_ID."' ";
					$query = $this->DatabaseHandler->Query($sql);
					$g_view_uids = array();
					$list = array();
					while (false != ($row = $query->GetRow())) {
						$g_view_uids[$row['touid']] = $row['touid'];
						$groupname = $row['g_name'];
						$groupid = $row['gid'];
					}

										if($g_view_uids) {
						$options['uid'] = $g_view_uids;
					} else {
						$this->Messager("没有设置用户，无法查看这个组的微博",'index.php?mod=topic&code=group&gid='.$gid);
					}
					$active[$gid] = "current";
				} else {
					if($type || false === (cache_db('get', $cache_key))) {
						$buddyids = get_buddyids($params['uid'], $this->Config['topic_myhome_time_limit']);
						if($buddyids) {
							$options['uid'] = array_merge($options['uid'], $buddyids);
						}
					}

					$active['all'] = "current";
				}
			} else {
				$title = "{$member['nickname']}的微博";
				$this->_initTheme($member);
			}

						if($type) {
				$getTypeTidReturn = $TopicListLogic->GetTypeTid($type,$options['uid'],$options);
				$options['tid'] = $getTypeTidReturn['tid'];
				$options['count'] = $getTypeTidReturn['count'];
				$options['limit'] = $per_page_num;
			}
		
		} else if ('channel' == $params['code'] && $channel_enable) {
			$title = '我的频道微博';
			if ($member['uid'] != MEMBER_ID) {
				$this->Messager("您无权查看该页面",null);
			}
			$ChannelLogic = Load::logic('channel',1);
			$my_buddy_channel = $ChannelLogic->mychannel();
			$cachefiles = ConfigHandler::get('channels');
			$chid = max(0,(int)$this->Get['chid']);
			if(empty($my_buddy_channel)){
				$channel_ids = array(0);
			}else{
				$my_chs = array_keys($my_buddy_channel);
				if(in_array($chid,$my_chs)){
					$active[$chid] = 'current';
					$channel_ids = $cachefiles[$chid];
				}else{
					if(strlen($this->Get['chid']) == 0){
						$active['all'] = 'current';
						$channel_ids = array();
						foreach($my_chs as $val){
							$channel_ids = array_merge($channel_ids,$cachefiles[$val]);
						}
						$channel_ids = array_unique($channel_ids);
					}else{
						$channel_ids = array(0);
					}
				}
			}
			$options['item'] = 'channel';
			$options['item_id'] = $channel_ids;
		}  else if('department' == $params['code']) {
			$tpl = 'topic_department';
			$title = ($this->Config['default_department'] ? $this->Config['default_department'] : '部门').'微博';
			if ($member['uid'] != MEMBER_ID) {
				$this->Messager("您无权查看该页面",null);
			}
			if (!$this->Config['department_enable']){
				$this->Messager("网站没有开启该功能",null);
			}
			$views = array('all', 'my', 'other');
			$view = trim($this->Get['view']);
			if (!in_array($view, $views)) {
				$view = 'other';
			}
			$active[$view] = 'current';
			$dids = array();
			if($view == 'my'){
				$dids[] = $member['departmentid'];
			}else{
				$sql = "select did	from `".TABLE_PREFIX."buddy_department` where `uid`='".MEMBER_ID."'";
				$query = $this->DatabaseHandler->Query($sql);
				while (false != ($row = $query->GetRow())) {
					$dids[] = $row['did'];
				}
				if($view == 'all'){
					$dids[] = $member['departmentid'];
				}
			}
			if($dids){
								$sql = "select `uid` from `".TABLE_PREFIX."members` where  `departmentid` in(".jimplode($dids).")";
				$query = $this->DatabaseHandler->Query($sql);
				$options['uid'] = array();
				while (false != ($row = $query->GetRow())) {
					$options['uid'][] = $row['uid'];
				}
								if(false != ($type = $this->Get['type']) && 'all' != $type){
					$options['tid'] = $TopicListLogic->GetTypeTid($type,$options['uid']);
				}
			}else{
				$options['tid'] = array();
			}
			if($member['departmentid']){
				$department = DB::fetch_first("SELECT * FROM ".DB::table('department')." WHERE id='".$member['departmentid']."'");
			}
			$user_lp = $this->TopicLogic->GetMember(array($department['leaderid'],$department['managerid']), "`uid`,`ucuid`,`username`,`nickname`,`face`,`face_url`,`validate`,`validate_category`,`aboutme`");
			$mybuddys = is_array(get_buddyids(MEMBER_ID)) ? get_buddyids(MEMBER_ID) : array(get_buddyids(MEMBER_ID));
						$user_l = $user_lp[$department['leaderid']];
			if($user_l){
				$user_l['here_name'] = $department['leadername'];
				$user_l['follow_html'] = follow_html2($department['leaderid'],in_array($department['leaderid'],$mybuddys));
				$leader_list[] = $user_l;
			}
						$user_m = $user_lp[$department['managerid']];
			if($user_m){
				$user_m['here_name'] = $department['managername'];
				$user_m['follow_html'] = follow_html2($department['managerid'],in_array($department['managerid'],$mybuddys));
				$manager_list[] = $user_m;
			}
						$CPLogic = Load::logic('cp',1);
			$department_list = $CPLogic->Getdepartment($member['companyid'],$member['departmentid']);

		}  else if('tag' == $params['code']) {

			$tpl = 'topic_tag';
			
			$title = '我关注的话题';
			if ($member['uid'] != MEMBER_ID) {
				$this->Messager("您无权查看该页面",null);
			}

			$views = array('new', 'new_reply', 'my_reply', 'recd');
			$view = trim($this->Get['view']);
			if (!in_array($view, $views)) {
				$view = 'new';
			}
			$active[$view] = 'current';

			$sql = "select  TF.uid , TF.tag ,T.name , T.*
					from `".TABLE_PREFIX."tag` T
					left join `".TABLE_PREFIX."tag_favorite` TF
					on T.name=TF.tag
					where `uid`='".MEMBER_ID."'";
			$query = $this->DatabaseHandler->Query($sql);
			$tag_info = array();
			while (false != ($row = $query->GetRow())) {
				$tag_info[$row['id']] = $row['id'];
			}

			if($tag_info)
			{
								$sql = "select `item_id` from `".TABLE_PREFIX."topic_tag` where  `tag_id` in('".implode("','",$tag_info)."') ORDER BY `item_id` DESC LIMIT 2000 ";
				$query = $this->DatabaseHandler->Query($sql);
				$topic_ids = array();
				while (false != ($row = $query->GetRow())) {
					$topic_ids[$row['item_id']] = $row['item_id'];
				}

				$options['tid'] = $topic_ids;
				unset($topic_ids);

								if ($this->Get['type']) {
					$options['filter'] = $this->Get['type'];
				}

				if ($view == 'new_reply') {
					$options['where'] = " replys>0 ";
					$options['order'] = " lastupdate DESC ";
				} else if ($view == 'recd') {

					$p = array(
						'where' => " tr.recd >= 1 AND tr.item='tag' AND tr.item_id IN(".jimplode($tag_info).") ",
						'perpage' => 10,
						'filter' => $this->Get['type'],
					);
					$info = $TopicListLogic->get_recd_list($p);
					if (!empty($info)) {
						$total_record = $info['count'];
						$topic_list = $info['list'];
						$page_arr = $info['page'];
					}
					$topic_list_get = true;
				}
			}else{
				$topic_list_get = true;
			}

						if($this->MemberHandler->MemberFields['topic_new']) {
				DB::query("UPDATE ".DB::table('members')." SET topic_new=0 WHERE uid='".MEMBER_ID."' ");
				$this->MemberHandler->MemberFields['topic_new'] = 0;
			}

		} else if ('mycomment' == $params['code']) {
			$tpl = 'topic_mycomment';
			

			$title = '评论我的';
			if ($member['uid']!=MEMBER_ID) {
				$this->Messager("您无权查看该页面",null);
			}

						if ($member['comment_new']) {
				$sql = "update `".TABLE_PREFIX."members` set `comment_new`=0 where `uid`='{$member['uid']}'";
				$this->DatabaseHandler->Query($sql);
				$this->MemberHandler->MemberFields['comment_new'] = 0;
			}

			$topic_selected = 'mycomment';
			$options['where'] = "`touid`='{$member['uid']}' and `type` in ('both','reply')";

		} else if ('tocomment' == $params['code']) {
						

			$title = '我评论的';

			if($member['uid']!=MEMBER_ID) {
				$this->Messager("您无权查看该页面",null);
			}

			$topic_selected = 'mycomment';
			$options['where'] = "`uid` = '{$member['uid']}' and `type` in ('both','reply')";

		} else if ('myblog' == $params['code']) {
			$tpl = 'topic_myblog';

			
			$where = " 1 ";
						$options['uid'] = array($member['uid']);
						if ($this->Get['type']) {
								if('vote' == $this->Get['type']){
					$type = 'vote';
					$tpl = 'topic_vote';
					$perpage = $this->ShowConfig['vote']['list'];
					$perpage = empty($perpage) ? 20 : $perpage;
					$vote_where = ' 1 ';
										$filter = get_param('filter');
					if ($filter == 'joined') {
												$vids = Load::logic('vote',1)->get_joined($member['uid']);
						if (!empty($vids)) {
							$vote_where .= " AND `v`.`vid` IN(".jimplode($vids).") ";
						} else {
							$vote_where = ' 0 ';
						}
					} else if ($filter == 'new_update') {
												DB::query("UPDATE ".DB::table('members')." SET vote_new=0 WHERE uid='{$uid}'");
						$this->MemberHandler->MemberFields['vote_new'] = 0;
						
						$vids = Load::logic('vote',1)->get_joined($uid);
						if (!empty($vids)) {
							$vote_where .= " AND `v`.`vid` IN(".jimplode($vids).") ";
						}
						$vote_where .= " OR `v`.`uid`='{$uid}' ";
					}  else {
						$vote_where .= " AND `v`.`uid`='{$uid}' ";
						$filter = 'created';
					}
					$vote_order_sql = ' ORDER BY lastvote DESC ';
					$vote_where .=" AND v.verify = 1";
					$param = array(
						'where' => $vote_where,
						'order' => $vote_order_sql,
						'page' => true,
						'perpage' => $perpage,
						'page_url' => $options['page_url'],
					);
					$vote_info = Load::logic('vote',1)->find($param);
					$count = 0;
					$vote_list = array();
					$page_arr['html'] = '';
					$uid_ary = array();
					if (!empty($vote_info)) {
						$count = $vote_info['count'];
						$vote_list = $vote_info['vote_list'];
						$page_arr['html'] = $vote_info['page']['html'];
						$uid_ary = $vote_info['uids'];
					}
										if (!empty($uid_ary)) {
						$members = $this->TopicLogic->GetMember($uid_ary);
					}
					$topic_list_get = true;
				}
								if('event' == $this->Get['type']){
					$type = 'event';
					$tpl = 'topic_event';
					$filter = get_param('filter');
					$param = array('perpage'=>"10",'page'=>true,);
					$return = array();
					if($filter == 'joined'){
						$this->Title = $member['nickname']."参与的活动";
						$param['where'] = " m.play = 1 and m.fid = '$uid' ";
						$param['order'] = " order by a.lasttime desc,a.app_num desc,a.posttime desc ";
						$param['page_url'] = $options['page_url'];
						$return = Load::logic('event',1)->getEvents($param);			
										} else if ($filter == 'new_update'){
												$this->DatabaseHandler->Query("update ".TABLE_PREFIX."members set event_new = 0 where uid = '$uid'");
						$this->MemberHandler->MemberFields['event_new'] = 0;
						
						$this->Title = "最近更新的活动";
						$param['uid'] = $uid;
						$param['page_url'] = $options['page_url'];
						$return = Load::logic('event',1)->getNewEvent($param);	
										}else{
						$filter = 'created';
						$this->Title = $member['nickname']."的活动";
						$param['where'] = " a.postman = '$uid' and a.verify = 1 ";
						$param['order'] = " order by a.lasttime desc,a.app_num desc,a.posttime desc ";
						$param['page_url'] = $options['page_url'];
						$return = Load::logic('event',1)->getEventInfo($param);	
					}
					$rs = $return['event_list'];
					$count = $return['count'];
					$page_arr = $return['page'];
					$topic_list_get = true;
				}
				
				if ('my_reply' == $this->Get['type']) {
					$type = $this->Get['type'];
					$options['type'] = array('reply', 'both');
				}
								if(in_array($this->Get['type'],array('pic','video','music','attach'))){
					if($this->Get['follow']){
						$buddyids = get_buddyids($params['uid'], $this->Config['topic_myhome_time_limit']);

						if($buddyids) {
							$options['uid'] = $buddyids;
						}
					}
					$type = $this->Get['type'];

					$getTypeTidReturn = $TopicListLogic->GetTypeTid($type,$options['uid'],$options);
					$options['tid'] = $getTypeTidReturn['tid'];
					$options['count'] = $getTypeTidReturn['count'];
					$options['limit'] = $per_page_num;

				}
				
								$dateline = TIMESTAMP - 30*24*3600;
				if('hot_reply' == $this->Get['type']){
					$options['where'] = " replys > 0 and dateline > '$dateline' ";
					$options['type'] = 'first';
					$options['order'] = ' replys DESC ';
				}
				if('hot_forward' == $this->Get['type']){
					$options['where'] = " forwards > 0 and dateline > '$dateline' ";
					$options['type'] = 'first';
					$options['order'] = ' forwards DESC ';
				}
				if('my_verify' == $this->Get['type']){
					
					$title = '审核中的微博';
					if('admin' != MEMBER_ROLE_TYPE){
						if ($member['uid'] != MEMBER_ID) {
							$this->Messager("您无权查看该页面",-1);
						}
					}

										$sql = "select count(*) as `total_record` from `".TABLE_PREFIX."topic_verify` where managetype = 0";
					$total_record = DB::result_first($sql);

										$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>"Array"));

										$sql = "select v.*
							from `".TABLE_PREFIX."topic_verify` v

							where v.uid='{$uid}'
							and v.managetype = 0 
							order by v.lastupdate desc {$page_arr['limit']}";
					$query = $this->DatabaseHandler->Query($sql);
					while (false != ($row = $query->GetRow())) {
						if ($row['id']<1) {
							continue;
						}
						$row['tid'] = $row['id'];
						$topic_list[$row['id']]= $this->TopicLogic->Make($row);
					}
					$topic_list_get = true;
				}
			} else {
								$img_arr = Load::logic('image', 1)->get_my_image($member['uid'], 6);
			}

			if ($member['uid'] != MEMBER_ID) {
				$title = "{$member['nickname']}的微博";

								$list_blacklist = is_blacklist($member['uid'], MEMBER_ID);


								$fg_code = 'hisblog';
				$this->_initTheme($member);
			} else {
				$title = '我的微博';
				$this->MetaKeywords ="{$member['nickname']}的微博";
			}
			$buddys = array();

						if (MEMBER_ID > 0 && $member['uid'] != MEMBER_ID) {
				$sql = "select `buddyid` as `id`,`remark`
						from `".TABLE_PREFIX."buddys`
						where `uid`='".MEMBER_ID."' and `buddyid` = '".$member['uid']."'";
				$query = $this->DatabaseHandler->Query($sql);
				$buddys = $query->GetRow();
			}
		} elseif ('myat' == $params['code']) {
			$tpl = 'topic_myat';
			

			$title = '@提到我的';

						$topic_selected = 'myat';
			if($member['uid'] != MEMBER_ID) {
				$this->Messager("您无权查看该页面",null);
			}

						if ($member['at_new']) {
								$sql = "update `".TABLE_PREFIX."members` set `at_new`=0 where `uid`='{$member['uid']}'";
				$this->DatabaseHandler->Query($sql);
				$this->MemberHandler->MemberFields['at_new'] = 0;
			}

			$sql = "select * from `".TABLE_PREFIX."topic_mention` where `uid`='{$uid}'";
			$query = $this->DatabaseHandler->Query($sql);
			$topic_ids[0] = 0;
			while (false != ($row = $query->GetRow()))
			{
				$topic_ids[$row['tid']] = $row['tid'];
			}
			$options['tid'] = $topic_ids;

		} else if ('myfavorite' == $params['code']) {
			$tpl = 'topic_myfavorite';
			

			$topic_selected = 'myfavorite';
			$title = '我的收藏';
			if ($member['uid'] != MEMBER_ID) {
				$this->Messager("您无权查看该页面",null);
			}

						$sql = "select count(*) as `total_record` from `".TABLE_PREFIX."topic_favorite` TF where TF.uid='{$uid}'";
			$total_record = DB::result_first($sql);

						$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>"Array"));

						$sql = "select TF.dateline as favorite_time , T.*
					from `".TABLE_PREFIX."topic_favorite` TF
					left join `".TABLE_PREFIX."topic` T
					on T.tid=TF.tid
					where TF.uid='{$uid}'
					order by TF.id desc {$page_arr['limit']}";
			$query = $this->DatabaseHandler->Query($sql);
			while (false != ($row = $query->GetRow())) {
				if ($row['tid']<1) {
					continue;
				}
				$row['favorite_time'] = my_date_format2($row['favorite_time']);
				$row = $this->TopicLogic->Make($row);
				$topic_list[$row['tid']] = $row;
			}
			$topic_list_get = true;

		} else if ('favoritemy' == $params['code']) {
						

			$topic_selected = 'favoritemy';
			$title = '收藏我的';
			if ($member['uid'] != MEMBER_ID) {
				$this->Messager("您无权查看该页面",null);
			}

						if ($member['favoritemy_new'] > 0) {
				$sql = "update `".TABLE_PREFIX."members` set `favoritemy_new`=0 where `uid`='{$member['uid']}'";
				$this->DatabaseHandler->Query($sql);
				$this->MemberHandler->MemberFields['favoritemy_new'] = 0;
			}

						$sql = "select count(*) as `total_record` from `".TABLE_PREFIX."topic_favorite` TF where TF.tuid='{$uid}'";
			$total_record = DB::result_first($sql);

						$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>"Array"));

						$sql = "select TF.dateline as favorite_time , TF.uid as fuid , T.*
					from `".TABLE_PREFIX."topic_favorite` TF
					left join `".TABLE_PREFIX."topic` T
					on T.tid=TF.tid
					where TF.tuid='{$uid}'
					order by TF.id desc {$page_arr['limit']}";
			$query = $this->DatabaseHandler->Query($sql);
			$fuids = array();
			while (false != ($row = $query->GetRow())) {
				if ($row['tid']<1) {
					continue;
				}
				$row['favorite_time'] = my_date_format2($row['favorite_time']);
				$row = $this->TopicLogic->Make($row);
				$topic_list[$row['tid']] = $row;
				$fuids[$row['fuid']] = $row['fuid'];
			}
			$favorite_members = $this->TopicLogic->GetMember($fuids,"`uid`,`ucuid`,`username`,`nickname`,`face_url`,`face`,`validate`,`validate_category`");

						$topic_list_get = true;

		} else if ('qun' == $params['code']) {
			$tpl = 'topic_qun';
			$title = "我的微群";

			$qun_setting = $this->Config['qun_setting'];
			if (!$qun_setting['qun_open']) {
				$this->Messager("当前站点没有开放微群功能", null);
			}
						DB::query("UPDATE ".DB::table('members')." SET qun_new=0 WHERE uid='".MEMBER_ID."' ");
			$this->MemberHandler->MemberFields['qun_new'] = 0;

			$views = array('new', 'new_reply', 'my_reply', 'recd');
			$view = trim($this->Get['view']);
			if (!in_array($view, $views)) {
				$view = 'new';
			}
			$active[$view] = "current";

			$u = MEMBER_ID;
			$join_qun_count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('qun_user')." WHERE uid='{$u}' ");
			$qun_name = '';
			if ($join_qun_count > 0) {
								$query = DB::query("SELECT qid FROM ".DB::table('qun_user')." WHERE uid='{$u}'");
				while ($value = DB::fetch($query)) {
					$qid_ary[] = $value['qid'];
				}
				if (!empty($qid_ary)) {

					$where_sql = " 1 ";
					$order_sql = " t.dateline DESC ";
					if ($this->Get['type']) {
						if ('pic' == $this->Get['type']){
							$where_sql .= " AND t.`imageid` > 0 ";
						} else if('video' == $this->Get['type']) {
							$where_sql .= " AND t.`videoid` > 0 ";
						} else if('music' == $this->Get['type']) {
							$where_sql .= " AND t.`musicid` > 0 ";
						} else if ('vote' == $this->Get['type']) {
							$where_sql .= " AND t.item='vote' ";
						} else if('longtext' == $this->Get['type']) {
							$where_sql .= " AND t.`longtextid` > 0 ";
						}
					}

					$topic_get_flg = false;
					if ($view == 'new') {
						$where_sql .= " AND tq.item_id IN(".jimplode($qid_ary).") ";
					} else if ($view == 'new_reply') {
						$where_sql .= " AND tq.item_id IN(".jimplode($qid_ary).") AND t.replys>0 ";
						$order_sql = " t.lastupdate DESC ";
					} else if ($view == 'recd') {
						$p = array(
							'where' => " tr.recd >= 1 AND tr.item='qun' AND tr.item_id IN(".jimplode($qid_ary).") ",
							'perpage' => $options['perpage'],
							'filter' => $this->Get['type'],
						);
						$info = $TopicListLogic->get_recd_list($p);
						if (!empty($info)) {
							$total_record = $info['count'];
							$topic_list = $info['list'];
							$page_arr = $info['page'];
						}
						$topic_get_flg = true;
					}

					if (!$topic_get_flg) {
						$total_record = DB::result_first("SELECT COUNT(*)
												   FROM ".DB::table('topic')." AS t
												   LEFT JOIN ".DB::table('topic_qun')." AS tq
												   USING(tid)
												   WHERE {$where_sql}");
						if ($total_record > 0) {
														$page_arr = page($total_record, $options['perpage'], $options['page_url'], array('return'=>'array'));
							$query = DB::query("SELECT t.*
												FROM ".DB::table('topic')." AS t
												LEFT JOIN ".DB::table('topic_qun')." AS tq
												USING(tid)
												WHERE {$where_sql}
												ORDER BY {$order_sql}
												{$page_arr['limit']} ");
												$topic_list = array();
												while ($value = DB::fetch($query)) {
													$topic_list[$value['tid']] = $this->TopicLogic->Make($value);
												}
						}
					}

										DB::query("UPDATE ".DB::table('members')." SET qun_new=0 WHERE uid='".MEMBER_ID."' ");
					$this->MemberHandler->MemberFields['qun_new'] = 0;
				}
			}

						$showConfig = $this->ShowConfig;
			$recd_qun_limit = (int) $showConfig['page_r']['recd_qun'];
						if($recd_qun_limit){
				$sql = "select * from `".TABLE_PREFIX."qun`  where `recd` = 1 order by `member_num` desc limit $recd_qun_limit  ";
				
				$query = $this->DatabaseHandler->Query($sql);
				$hot_qun = array();
				$qunLogic = Load::logic('qun',1);
				while (false != ($row = $query->GetRow()))
				{
					$row['icon'] = $qunLogic->qun_avatar($row['qid'], 's');
					$hot_qun[] = $row;
				}
			}
			$topic_list_get = true;

		} else if ('recd' == $params['code']) {
			
			$title = "官方推荐";
			$view = trim($this->Get['view']);
			$where_sql = '';
			if ($view == 'new_reply') {
				$where_sql = ' AND t.replys>0 ';
			} else {
				$view = 'all';
			}
			$active[$view] = 'current';

			$p = array(
				'where' => ' tr.recd > 2 '.$where_sql,
				'perpage' => $options['perpage'],
				'filter' => $this->Get['type'],
			);
			$info = $TopicListLogic->get_recd_list($p);
			if (!empty($info)) {
				$total_record = $info['count'];
				$topic_list = $info['list'];
				$page_arr = $info['page'];
			}
			$topic_list_get = true;

		} else if ('cms' == $params['code']) {
			
			$title = "网站资讯";
			$param = array(
				'perpage' => $options['perpage'],
				'page_url' => $options['page_url'],
			);
			$view = 'all';
			$active[$view] = 'current';
			$info = array();
						if($this->Config['dedecms_enable'] && @is_file(ROOT_PATH . 'setting/dedecms.php')){
				Load::logic("topic_cms");
				$TopicCmsLogic = new TopicCmsLogic();
				$info = $TopicCmsLogic->get_cms($param);
				$cms_url = CMS_API_URL;
			}
			if (!empty($info)) {
				$total_record = $info['count'];
				$topic_list = $info['list'];
				$page_arr = $info['page'];
			}
			$topic_list_get = true;

		} else if ('bbs' == $params['code']) {
			
			$title = "我的论坛";
			$view = trim($this->Get['view']);
			$where_sql = '';
			if ($view == 'favorites') {
				$where_sql = 'favorites';
			}else if($view == 'favorite'){
				$where_sql = 'favorite';
			}else if($view == 'thread'){
				$where_sql = 'thread';
			}else if($view == 'reply'){
				$where_sql = 'reply';
			}else if($view == 'all'){
				$where_sql = 'all';
			} else {
				if($this->Config['dzbbs_enable']){
					$view = 'favorites';
					$where_sql = 'favorites';
				}else{
					$view = 'all';
					$where_sql = 'all';
				}
			}
			$active[$view] = 'current';
			$info = array();
			$param = array(
				'where' => $where_sql,
				'perpage' => $options['perpage'],
				'page_url' => $options['page_url'],
			);
						if(($this->Config['dzbbs_enable'] && @is_file(ROOT_PATH . 'setting/dzbbs.php')) || ($this->Config['phpwind_enable'] && $this->Config['pwbbs_enable'] && @is_file(ROOT_PATH . 'setting/phpwind.php'))){
				Load::logic("topic_bbs");
				$TopicBbsLogic = new TopicBbsLogic();
				$info = $TopicBbsLogic->get_bbs($param);
				$bbs_url = BBS_API_URL;
			}

			if (!empty($info)) {
				$total_record = $info['count'];
				$topic_list = $info['list'];
				$page_arr = $info['page'];
			}
			$topic_list_get = true;
		}

		if (!$topic_list_get) {
						if($cache_time > 0 && !$options['tid']) { 				$cache_key = ($cache_key ? $cache_key : "{$member['uid']}-topic-{$params['code']}-{$params['type']}-{$params['gid']}-{$params['qid']}-{$params['view']}");
				
				$options = $TopicListLogic->get_options($options, $cache_time, $cache_key);
			}
						
			$info = $TopicListLogic->get_data($options);
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
		}

		$topic_list_count = 0;
		if ($topic_list) {
			if($GLOBALS['_J']['config']['is_topic_user_follow'] && !$GLOBALS['_J']['disable_user_follow']) {
				$topic_list = Load::model('buddy')->follow_html2($topic_list);
			}
			$topic_list_count = count($topic_list);
			if (!$topic_parent_disable && ('bbs' != $this->Code || 'cms' != $this->Code)) {
								$parent_list = $this->TopicLogic->GetParentTopic($topic_list, ('mycomment' == $this->Code));
							}
		}

				if (!in_array($params['code'],array('bbs','cms'))){			$ajaxkey = array();			$ajaxnum = 10;			if(count($topic_list)>$ajaxnum){				$topic_keys = array_keys($topic_list);				$topic_list = array_slice($topic_list,0,$ajaxnum);				array_splice($topic_keys,0,$ajaxnum);				$num = ceil(count($topic_keys)/$ajaxnum);				for($i=0;$i<$num;$i++){
					if(count($topic_keys)>$ajaxnum){
						$topic_key = array_splice($topic_keys,0,$ajaxnum);					}else{
						$topic_key = $topic_keys;
					}
					$ajaxkey[] = base64_encode(serialize($topic_key));				}
				$isloading = true;			}
		}

				$group_list = $grouplist2 = array();
		$group_list = $this->_myGroup($member['uid']);
		$cut_num = 5;
		if ($group_list) {
			$group_count = count($group_list);
			if ($group_count > $cut_num) {
				$grouplist2 = array_slice($group_list,0,$cut_num);
				$grouplist_more = array_slice($group_list, $cut_num);
				foreach ($grouplist_more as $key => $value) {
					if ($value['id'] == $gid) {
						$tmp = $grouplist2[$cut_num-1];
						$grouplist2[$cut_num-1] = $value;
						$grouplist_more[] = $tmp;
						unset($grouplist_more[$key]);
						break;
					}
				}
				$group_list = $grouplist_more;
			} else {
				$grouplist2 = $group_list;
				$group_list = array();
			}
		}

				$member_medal = $my_member ? $my_member : $member;
		if ($member_medal['medal_id']) {
			$medal_list = $this->_Medal($member_medal['medal_id'],$member_medal['uid']);
		}
		
		$exp_return = user_exp($member_medal['level'],$member_medal['credits']);
		if($exp_return['exp_width'] >= 1){
			$exp_width = $exp_return['exp_width'];
		} else {
			$exp_width = 0;
		}
				$nex_exp_credit  = $exp_return['nex_exp_credit'];
				$nex_level  = $exp_return['nex_exp_level'];

		$this->Title = $title;
		$tpl = $tpl ? $tpl : 'topic_index';
		include($this->TemplateHandler->Template($tpl));
	}


		function View()
	{
		if ($this->ID < 1) {
			$this->Messager("请指定一个ID",null);
		}
		$per_page_num = 20;
		$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&amp;code={$this->Code}" : "");

		$topic_info = $this->TopicLogic->Get($this->ID);

		if (!$topic_info) {
			$this->Messager("您要查看的话题已经不存在了",null);
		}

				$allow_op = 1;

		
		if ($topic_info['item'] == 'qun' && !empty($topic_info['item_id'])) {
			Load::logic('qun');
			$QunLogic = new QunLogic();
			$qun_info = $QunLogic->get_qun_info($topic_info['item_id']);
			if (!empty($qun_info)) {
				$qun_info['icon'] = $QunLogic->qun_avatar($qun_info['qid'], 's');
				$allow_op = $is_qun_member = $QunLogic->is_qun_member($topic_info['item_id'], MEMBER_ID);
				if ($qun_info['gview_perm'] == 1) {
					if (!$is_qun_member) {
						$this->Messager("你不是当前微群的成员，无法查看", 'index.php?mod=qun&qid='.$topic_info['item_id']);
					}
				}
			}
		} else {
									if ($topic_info['type'] == 'reply') {
				$roottid = $topic_info['roottid'];

								if (empty($roottid)) {
					$root_type = 'reply';
				} else {
					$root_type = DB::result_first("SELECT type FROM ".DB::table('topic')." WHERE tid='{$roottid}'");
				}
			} else {
				$root_type = $topic_info['type'];
			}
			if (!$this->TopicLogic->check_view_perm($topic_info['uid'], $root_type)) {
				$this->Messager("你没有权限查看");
			}
		}


		$parent_list = $t_parent_list = $r_parent_list = array();
		if($topic_info['parent_id'])
		{
			$parent_id_list = array
			(
			$topic_info['parent_id'],
			$topic_info['top_parent_id'],
			);

			if($parent_id_list)
			{
				$t_parent_list = $this->TopicLogic->Get($parent_id_list);
			}
		}

		if ($topic_info['replys'] > 0) {

			$_config = array(
				'return' => 'array',
			);

			$tids = $this->TopicLogic->GetReplyIds($topic_info['tid']);

						
			$total_record = $topic_info['replys'];
			$page_arr = page($total_record,$per_page_num,$query_link,$_config);

			if($tids)
			{
								$condition = "where `tid` in ('".implode("','",$tids)."') order by `dateline` asc {$page_arr['limit']}";

				$reply_list = $this->TopicLogic->Get($condition);
				
				$ajaxkey = array();
				$ajaxnum = 10;
				if(count($reply_list)>$ajaxnum){
					$topic_keys = array_keys($reply_list);
					$reply_list = array_slice($reply_list,0,$ajaxnum);
					array_splice($topic_keys,0,$ajaxnum);
					$num = ceil(count($topic_keys)/$ajaxnum);
					for($i=0;$i<$num;$i++){
						if(count($topic_keys)>$ajaxnum){
							$topic_key = array_splice($topic_keys,0,$ajaxnum);
						}else{
							$topic_key = $topic_keys;
						}
						$ajaxkey[] = base64_encode(serialize($topic_key));
					}
					$isloading = true;
				}

								$parent_list = $r_parent_list = $this->TopicLogic->GetParentTopic($reply_list, 1);
			}
		}
		if($t_parent_list)
		{
			foreach($t_parent_list as $k=>$v)
			{
				if(!isset($parent_list[$k]))
				{
					$parent_list[$k] = $v;
				}
			}
		}
		

		if (MEMBER_ID > 0) {
			$sql = "select * from `".TABLE_PREFIX."topic_favorite` where `uid`='".MEMBER_ID."' and `tid`='{$topic_info['tid']}'";
			$query = $this->DatabaseHandler->Query($sql);
			$is_favorite = $query->GetRow();
		}

		$member = $this->_member($topic_info['uid']);
				$member_medal = $member;
		if($member_medal['medal_id'])
		{
			$medal_list = $this->_Medal($member_medal['medal_id'],$member_medal['uid']);
		}
	

				if($topic_info['longtextid']) {
			$longtext_info = Load::logic('longtext', 1)->get_info($topic_info['longtextid'], 1);
			$longtext_info[longtext] = nl2br($longtext_info[longtext]);
			$topic_info['content'] = $longtext_info['longtext'];
		}

		
		$this->Title = cut_str(strip_tags($topic_info['content']),50)." - {$member['nickname']}的微博";

				$Keywords = array();
		if(strpos($topic_info['content'],'#'))
		{
			preg_match_all('~\#([^\#\s\'\"\/\<\>\?\\\\]+?)\#~',strip_tags($topic_info['content']),$Keywords);
		}
		if(is_array($Keywords[1]) && count($Keywords[1]))
		{
			$this->MetaKeywords = implode(',',$Keywords[1]);
		}

		$this->MetaDescription = strip_tags($topic_info['content']);
		


		if(MEMBER_ID != $member['uid'])
		{
			$this->_initTheme($member);
		}


		$topic_view = 1;
		$this->item = $topic_info['item'];
		$this->item_id = $topic_info['item_id'];

		include($this->TemplateHandler->Template('topic_view'));
	}

	function Follow() {
		$member =  $this->_member();
		if (!$member) {
			$this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",'index.php?mod=login');
		}

		$my_member = $this->_member((int) $this->Get['mod_original']);

				$gid = intval(trim($this->Get['gid']));
		$keyword = $this->Post['nickname'] ? $this->Post['nickname'] : $this->Get['nickname'];

				$per_page_num = empty($this->ShowConfig['topic']['follow']) ? 10 : $this->ShowConfig['topic']['follow'];
		$gets = array(
			'mod' => $_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module,
			'code' => $this->Code,
			'gid' => $this->Get['gid'],
			'nickname' => $this->Get['nickname'],
			'type' => $this->Get['type'],
		);
		$page_url = "index.php?".url_implode($gets);


				$orderBy = ' ORDER BY ';
		if("fans_count" == $this->Get['type']){
			$orderBy .= " m.`fans_count` DESC ";
		} else if ("lastpost" == $this->Get['type']) {
			$orderBy .= " m.`lastpost` DESC ";
		} else {
			$orderBy .= " b.`id` DESC ";
		}

		$member_list = $uids = array();

		
		if (!empty($keyword)) {
			if (strlen($keyword) < 2) {
				$this->Messager("请输入至少3个字节的关键词", -1);
			}

			$search_sql = build_like_query("m.`nickname`", $keyword);
			$where_sql = " b.uid=".MEMBER_ID." AND {$search_sql} ";
			$count = DB::result_first("SELECT COUNT(*)
									   FROM ".DB::table('buddys')." AS b
									   LEFT JOIN ".DB::table('members')." AS m
									   ON b.buddyid=m.uid
									   WHERE {$where_sql}");
			if ($count > 0) {
				$page_arr = page($count, $per_page_num, $page_url, array('return' => 'array'));
				$query = DB::query("SELECT b.remark,m.*
									FROM ".DB::table('buddys')." AS b
									LEFT JOIN ".DB::table('members')." AS m
									ON b.buddyid=m.uid
									WHERE {$where_sql}
									{$orderBy}
									{$page_arr['limit']} ");
									while ($row = DB::fetch($query)) {
										if($row['uid'] > 0) {
											$member_list[$row['uid']] = $this->TopicLogic->MakeMember($row);
										}
										$uids[] = $row['uid'];
									}
			}
		} else if($gid) {

						$group_view = DB::fetch_first("SELECT *
										   FROM ".DB::table("group")."
										   WHERE `id`='{$gid}' AND uid='".MEMBER_ID."'");
			if (empty($group_view)) {
				$this->Messager("这个不是你的分组，你不能查看", -1);
			}

						$count = DB::result_first("SELECT COUNT(*)
									   FROM ".DB::table('groupfields')."
									   WHERE gid='{$gid}' AND `uid` = '".MEMBER_ID."' ");
			if ($count > 0) {
				$page_arr = page($count, $per_page_num, $page_url, array('return' => 'array'));
								$sql = "SELECT m.*,b.remark
						FROM ".DB::table('groupfields')." AS g
						LEFT JOIN ".DB::table('members')." AS m
						ON m.`uid` = g.`touid`
						LEFT JOIN  ".DB::table('buddys')." AS b
						ON b.`buddyid` = m.`uid`
						WHERE g.`gid`='{$gid}' AND  b.uid=".MEMBER_ID."
						{$orderBy}
						{$page_arr['limit']}";
						$query = DB::query($sql);
						while ($row = DB::fetch($query)) {
							if($row['uid'] > 0) {
								$member_list[$row['uid']] = $this->TopicLogic->MakeMember($row);
							}
							$uids[] = $row['uid'];
						}
			}
		} else {

						$count = $member['follow_count'];
			if ($count > 0) {
								$page_arr = page($count, $per_page_num, $page_url, array('return' => 'array'));

								$sql = "SELECT b.remark, m.*
						FROM ".DB::table('buddys')." AS b
						LEFT JOIN ".DB::table('members')." AS m
						ON m.`uid` = b.`buddyid`
						WHERE b.`uid`='{$member['uid']}'
						{$orderBy}
						{$page_arr['limit']}";
						$query = DB::query($sql);
						while (false != ($row = $query->GetRow())) {
							if($row['uid'] > 0) {
								$member_list[$row['uid']] = $this->TopicLogic->MakeMember($row);
							}
							$uids[] = $row['uid'];
						}
			}
		}


				$member_list = Load::model('buddy')->follow_html($member_list);


				$sql = "SELECT  GF.touid , GF.gid, GF.g_name , GF.display ,G.group_name ,G.id , GF.*
				FROM ".DB::table('group')." AS G
				LEFT JOIN ".DB::table('groupfields')." AS GF
				ON G.id=GF.gid
				WHERE G.uid='".MEMBER_ID." ' ";
		$query = DB::query($sql);
		$user_group = array();
		while ($row = DB::fetch($query)) {
			$user_group[$row['id']] = $row;
		}

				$group_list = $grouplist2 = array();
		$group_list = $this->_myGroup($member['uid']);
		if($group_list) $grouplist2 = array_slice($group_list,0,min(4,count($group_list)));

				$member_medal = $my_member ? $my_member : $member;
		if ($member_medal['medal_id']) {
			$medal_list = $this->_Medal($member_medal['medal_id'],$member_medal['uid']);
		}

		$this->Title = "{$member['nickname']}关注的微博";

		include($this->TemplateHandler->Template('topic_follow'));

	}

	function ViewGroup()
	{
		$member = $this->_member();
		if (!$member) {
			$this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",'index.php?mod=login');
		}
				$gid = (int) $this->Get['gid'];

				$sql = "select * from `".TABLE_PREFIX."group` where `id`='{$gid}' limit 0,1";
		$query = $this->DatabaseHandler->Query($sql);
		$group_view = $query->GetRow();


				$_config = array(
			'return' => 'array',
		);
		$per_page_num = 12;
		$page_arr = page($member['follow_count'],$per_page_num,"index.php?mod=topic&code=group&gid={$gid}",$_config);


		$p = array(
			'fields' => 'buddyid',
			'count' => $member['follow_count'],	
			'order' => ' `dateline` DESC ',		
			'limit' => " {$page_arr['limit']} ",
			'uid' => $member['uid'],		
		);
		$uids = Load::model('buddy')->get_ids($p);


		$buddysList = $this->TopicLogic->GetMember($uids,"`uid`,`ucuid`,`username`,`face_url`,`face`,`province`,`city`,`fans_count`,`topic_count`,`validate`,`validate_category`,`nickname`");


				$group_list = $grouplist2 = array();
		$group_list = $this->_myGroup($member['uid']);
		if($group_list) $grouplist2 = array_slice($group_list,0,min(4,count($group_list)));

		
		include($this->TemplateHandler->Template('topic_group'));
	}

	function Fans() {
				$member = $this->_member();
		if (!$member) {
			$this->Messager("链接错误，请检查",null);
		}

		$my_member = $this->_member($this->Get['mod_original']);

				$per_page_num = $this->ShowConfig['topic']['fans'];

				if ($member['uid']==MEMBER_ID && $member['fans_new']>0) {
			$sql = "update `".TABLE_PREFIX."members` set `fans_new`=0 where `uid`='{$member['uid']}'";
			$this->DatabaseHandler->Query($sql);

			$this->MemberHandler->MemberFields['fans_new'] = 0;
		}

				$orderBy = $orderBy2 = '';
		if("fans_count" == $this->Get['type']){
			$orderBy = " order by m.`fans_count` DESC ,id ";
		} else if ("lastpost" == $this->Get['type']){
			$orderBy = " order by m.`lastpost` DESC ,id ";
		} else {
			$order = 'id';
			$orderBy = " order by b.id DESC ";
		}

				$gid = intval(trim($this->Get['gid']));
		$keyword = trim($this->Post['nickname'] ? $this->Post['nickname'] : $this->Get['nickname']);

				if($keyword)
		{
			if (strlen($keyword) < 2) {
				$this->Messager("请输入至少3个字符的关键词",-1);
			}

						$query = DB::query("SELECT * FROM ".DB::table('buddys')." WHERE buddyid='".MEMBER_ID."' ");
			$fans_uid = array();
			while ($row = DB::fetch($query)) {
				$fans_uid[$row['uid']] = $row['uid'];
			}
			if(empty($fans_uid)){
				$this->Messager("暂时还没有人关注你",-1);
			}

						$where_list['keyword'] =  build_like_query("`nickname`",$keyword);
			$where = (empty($where_list)) ? null : " WHERE ".implode(' AND ',$where_list)." and `uid` in ('" . implode("','", $fans_uid) . "')";

						$page_url = "index.php?mod={$member['username']}&code=fans&nickname=".$keyword;

			$_config = array(
				'return' => 'array',
			);

						$count = DB::result_first("SELECT count(*) FROM ".DB::table('members')." {$where} ");
			$page_arr = page($count, $per_page_num, $page_url, $_config);

			$query = DB::query("SELECT * FROM ".DB::table('members')." {$where} {$page_arr['limit']}");
			$uids = array();
			while ($row = DB::fetch($query)) {
				$uids[$row['uid']] = $row['uid'];
			}
		}
				elseif($gid)
		{
						$username = $member['username'];
			$fans_group = DB::fetch_first("SELECT * FROM ".DB::table('fans_group')." WHERE gid='{$gid}'");
			if (empty($fans_group)) {
				$this->Messager('当前分组不存在或或者已经被删除', "index.php?mod={$username}&code=fans");
			}
			if ($fans_group['uid'] != MEMBER_ID) {
				$this->Messager('你没有权限此操作', "index.php?mod={$username}&code=fans");
			}

						$uid = MEMBER_ID;

						$orderType = $this->Get['type'] ? '&type='.$this->Get['type'] : "";

			$page_url = "index.php?mod={$member['username']}&code=fans&gid={$gid}".$orderType;
			$_config = array(
				'return' => 'array',
			);

			$where_sql = " f.gid='{$gid}' AND f.uid='{$uid}' ";

						$count = DB::result_first("SELECT count(*) FROM ".DB::table('fans_group_fields')." f where {$where_sql} ");
						$page_arr = page($count, $per_page_num, $page_url, $_config);

			$query = DB::query("SELECT f.`touid` FROM ".DB::table('fans_group_fields')." f LEFT JOIN ".DB::table('members')." m ON m.`uid` = f.`touid` WHERE {$where_sql}" . $orderBy ."{$page_arr['limit']}");
			$uids = array();
			while ($value = DB::fetch($query))
			{
				$uids[$value['touid']] = $value['touid'];
			}

		}
		else
		{
			
			$page_url = "index.php?mod={$member['username']}&code=fans";

			$_config = array(
				'return' => 'array',
			);

						$count = $member['fans_count'];

						$page_arr = page($count, $per_page_num, $page_url, $_config);

			$sql = "select b.`uid` from `".TABLE_PREFIX."buddys` b left join `".TABLE_PREFIX."members` m on m.`uid` = b.`uid` where b.`buddyid`='{$member['uid']}' ". $orderBy ."{$page_arr['limit']}";
			$query = $this->DatabaseHandler->Query($sql);
			$uids = array();
			while (false != ($row = $query->GetRow())) {
				$uids[$row['uid']] = $row['uid'];
			}
		}

		$member_list = array();
		if($uids) {
			$sql_in_uids = "'".implode("','", $uids)."'";
			if('id'==$order) {
				$orderBy = " ORDER BY FIELD(m.uid, $sql_in_uids) ";
			}
			$member_list = $this->TopicLogic->GetMember(" m LEFT JOIN `".TABLE_PREFIX."buddys` b ON (b.buddyid=m.uid AND b.uid='{$member['uid']}') WHERE m.uid IN($sql_in_uids) $orderBy LIMIT ".count($uids)." ", " m.*, b.remark ");

			$member_list = Load::model('buddy')->follow_html($member_list);
		}

				$member_medal = $my_member ? $my_member : $member;
		if ($member_medal['medal_id']) {
			$medal_list = $this->_Medal($member_medal['medal_id'],$member_medal['uid']);
		}

		$this->Title = "关注{$member['nickname']}的人";

		include($this->TemplateHandler->Template('topic_fans'));
	}



	function Top()
	{


		$limit = $this->ShowConfig['topic_top']['guanzhu'];
		$cache_id = "misc/top_users-{$limit}";
		if($limit>0 && false === ($r_users = cache_file('get', $cache_id))) {
			$r_users = $this->TopicLogic->GetMember("where face!='' order by `fans_count` desc limit {$limit}","`uid`,`ucuid`,`username`,`fans_count`,`topic_count`,`validate`,`validate_category`,`province`,`city`,`face`,`nickname`");

			cache_file('set', $cache_id, $r_users, $this->CacheConfig['topic_top']['guanzhu']);
		}
		$r_users = Load::model('buddy')->follow_html($r_users, 'uid', 'follow_html2');

				$limit = $this->ShowConfig['topic_top']['renqi'];
		$cache_id = "misc/top_day7_r_buddys";
		if ($limit>0 && false == ($day7_r_buddys = cache_file('get', $cache_id))) {
			$day7_r_buddys = array();

						$sql = "select DISTINCT(B.buddyid) AS buddyid , COUNT(B.uid) AS count  from `".TABLE_PREFIX."buddys` B left join `".TABLE_PREFIX."members` M on B.buddyid=M.uid WHERE B.dateline>='".(TIMESTAMP - 86400 * 7)."' and M.face!='' GROUP BY buddyid ORDER BY count DESC LIMIT {$limit}";

			$query = $this->DatabaseHandler->Query($sql);
			$uids = $_ids = array();
			while (false != ($row = $query->GetRow()))
			{
				$uids[$row['buddyid']] = $row['buddyid'];
				$_ids[$row['buddyid']] = $row;
			}


			if($_ids) {
				$_list = $this->TopicLogic->GetMember($uids, "`uid`,`ucuid`,`username`,`fans_count`,`topic_count`,`validate`,`validate_category`,`province`,`city`,`face`,`nickname`");

				foreach ($_ids as $id=>$row) {
					$row['uid'] = $_list[$id]['uid'];
					$row['username'] = $_list[$id]['username'];
					$row['nickname'] = $_list[$id]['nickname'];
					$row['face'] = $_list[$id]['face'];
					$row['from_area'] = $_list[$id]['from_area'];
					$row['validate_html'] = $_list[$id]['validate_html'];
					$row['fans_count'] = $_list[$id]['fans_count'];
					$row['topic_count'] = $_list[$id]['topic_count'];
					if(isset($_list[$id])) {
						$day7_r_buddys[$id] = $row;
					}
				}
			}
			cache_file('set', $cache_id, $day7_r_buddys, $this->CacheConfig['topic_top']['renqi']);
		}
		$day7_r_buddys = Load::model('buddy')->follow_html($day7_r_buddys, 'uid', 'follow_html2');

				$limit = $this->ShowConfig['topic_top']['huoyue'];
		$cache_id = "misc/top_day7_r_users";
		if ($limit>0 && false == ($day7_r_users = cache_file('get', $cache_id))) {
						$sql = "select DISTINCT(T.uid) AS uid , COUNT(T.tid) AS `count` from `".TABLE_PREFIX."topic` T left join `".TABLE_PREFIX."members` M on T.uid=M.uid WHERE T.dateline>='".(TIMESTAMP - 86400 * 7)."' and M.face!='' GROUP BY uid ORDER BY `count` DESC LIMIT {$limit}";

			$query = $this->DatabaseHandler->Query($sql);
			$uids = $day7_r_users = array();
			while (false != ($row = $query->GetRow()))
			{
				$day7_r_users[$row['uid']] = $row;
				$uids[$row['uid']] = $row['uid'];
			}

			if ($day7_r_users) {
				$members = $this->TopicLogic->GetMember("where `uid` in ('".implode("','",$uids)."') limit {$limit}", "`uid`,`ucuid`,`username`,`fans_count`,`topic_count`,`validate`,`validate_category`,`province`,`city`,`face`,`nickname`");

				foreach ($members as $_m) {
					$day7_r_users[$_m['uid']]['validate_html'] = $_m['validate_html'];
					$day7_r_users[$_m['uid']]['face'] = $_m['face'];
					$day7_r_users[$_m['uid']]['uid'] = $_m['uid'];
					$day7_r_users[$_m['uid']]['from_area'] = $_m['from_area'];
					$day7_r_users[$_m['uid']]['username'] = $_m['username'];
					$day7_r_users[$_m['uid']]['nickname'] = $_m['nickname'];
					$day7_r_users[$_m['uid']]['fans_count'] = $_m['fans_count'];
					$day7_r_users[$_m['uid']]['topic_count'] = $_m['topic_count'];
				}
			}
			cache_file('set', $cache_id, $day7_r_users, $this->CacheConfig['topic_top']['huoyue']);
		}
		$day7_r_users = Load::model('buddy')->follow_html($day7_r_users, 'uid', 'follow_html2');

				$limit = $this->ShowConfig['topic_top']['yingxiang'];
		$cache_id = "misc/top_day7_r_topics";
		if ($limit>0 && false == ($day7_r_topics = cache_file('get', $cache_id))) {
						$sql = "select DISTINCT(T.touid) AS uid ,  COUNT(T.tid) AS `count` from `".TABLE_PREFIX."topic` T left join `".TABLE_PREFIX."members` M on T.touid=M.uid WHERE M.face !='' and  T.dateline>='".(TIMESTAMP - 86400 * 7)."' and T.touid > 0  GROUP BY `uid` ORDER BY `count` DESC LIMIT {$limit}";

			$query = $this->DatabaseHandler->Query($sql);
			$uids = $day7_r_topics = array();
			while (false != ($row = $query->GetRow()))
			{
				$day7_r_topics[$row['uid']] = $row;
				$uids[$row['uid']] = $row['uid'];

			}
			if ($day7_r_topics) {
				$members = $this->TopicLogic->GetMember("where `face` !='' and `uid` in ('".implode("','",$uids)."') limit {$limit}","`uid`,`ucuid`,`username`,`fans_count`,`topic_count`,`validate`,`validate_category`,`province`,`city`,`face`,`nickname`");

				foreach ($members as $_m) {
					$day7_r_topics[$_m['uid']]['validate_html'] = $_m['validate_html'];
					$day7_r_topics[$_m['uid']]['face'] = $_m['face'];
					$day7_r_topics[$_m['uid']]['uid'] = $_m['uid'];
					$day7_r_topics[$_m['uid']]['from_area'] = $_m['from_area'];
					$day7_r_topics[$_m['uid']]['username'] = $_m['username'];
					$day7_r_topics[$_m['uid']]['nickname'] = $_m['nickname'];
					$day7_r_topics[$_m['uid']]['fans_count'] = $_m['fans_count'];
					$day7_r_topics[$_m['uid']]['topic_count'] = $_m['topic_count'];
				}

			}

			cache_file('set', $cache_id, $day7_r_topics, $this->CacheConfig['topic_top']['yingxiang']);
		}
		$day7_r_topics = Load::model('buddy')->follow_html($day7_r_topics, 'uid', 'follow_html2');

				$limit = isset($this->ShowConfig['topic_top']['credits']) ? $this->ShowConfig['topic_top']['credits'] : 20;
		$credits = $this->Config['credits_filed'];
		$credits_name = $this->Config['credits']['ext'][$credits]['name'];
		$cache_id = "misc/top_day7_r_sign";
		if ($limit>0 && false == ($top_day7_r_sign = cache_file('get', $cache_id))) {
									if($credits){
				$members = $this->TopicLogic->GetMember(" where `$credits` > 0 order by `$credits` desc limit {$limit}","`uid`,`ucuid`,`username`,`fans_count`,`topic_count`,`validate`,`validate_category`,`province`,`city`,`face`,`nickname`,`$credits`");

				$top_day7_r_sign = array();
				if($members) {
					foreach ($members as $_m) {
						$top_day7_r_sign[$_m['uid']]['validate_html'] = $_m['validate_html'];
						$top_day7_r_sign[$_m['uid']]['face'] = $_m['face'];
						$top_day7_r_sign[$_m['uid']]['uid'] = $_m['uid'];
						$top_day7_r_sign[$_m['uid']]['from_area'] = $_m['from_area'];
						$top_day7_r_sign[$_m['uid']]['nickname'] = $_m['nickname'];
						$top_day7_r_sign[$_m['uid']]['username'] = $_m['username'];
						$top_day7_r_sign[$_m['uid']]['fans_count'] = $_m['fans_count'];
						$top_day7_r_sign[$_m['uid']]['topic_count'] = $_m['topic_count'];
						$top_day7_r_sign[$_m['uid']]['credit'] = $_m[$credits];
					}
				}

				cache_file('set', $cache_id, $top_day7_r_sign, $this->CacheConfig['topic_top']['credits']);
			}
		}
		$top_day7_r_sign = Load::model('buddy')->follow_html($top_day7_r_sign, 'uid', 'follow_html2');
		

		$this->Title = "人气用户推荐";
		include($this->TemplateHandler->Template('topic_top'));
	}

	function Hot()
	{
		if(MEMBER_ID > 0) {
			$member = jsg_member_info(MEMBER_ID);
		}

				$vip_uids_in = '';
		$vip_uids = array();
		if($this->Config['only_show_vip_topic']) {
			$vip_uids = jsg_get_vip_uids();

			if($vip_uids) {
				$vip_uids_in = " `uid` in ('".implode("','", $vip_uids)."') and ";
			}
		}


		
		$TopicListLogic = Load::logic('topic_list', 1);

		if('hotforward' == $this->Code) {
			$title = '热门转发';

			$d_list = array(1=>'近一天',7=>'近一周',14=>'近两周',30=>'近一月',);
			$d = isset($d_list[$this->Get['d']]) ? $this->Get['d'] : 7;
			$time = $d * 86400;
			$dateline = TIMESTAMP - $time;

						$per_page_num = (int) $this->ShowConfig['topic_hot']["day{$d}"];
			if($per_page_num < 1) {
				$per_page_num = 20;
			}
			$cache_time = max(300, (int) ($this->CacheConfig['topic_hot']["day{$d}"] ? $this->CacheConfig['topic_hot']["day{$d}"] : $time / 90));
			$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&amp;code={$this->Code}&d={$d}" : "");

			$options = array(
				'cache_time' => $cache_time,
				'cache_key' => "topic-hotforward-{$d}",
				'perpage' => $per_page_num,
				'page_url' => $query_link,
				'type' => 'first',
				'where' => " $vip_uids_in `forwards`>'0' AND `dateline`>='$dateline' ",
				'order' => " `forwards` DESC , `dateline` DESC ",
			);
			$info = $TopicListLogic->get_data($options);
			if($info) {
				$page_arr = $info['page'];
				$topics = $info['list'];
				$total_record = $info['count'];
			}
			$params = array('d'=>$d,'pp_time'=>$per_page_num,'c_time'=>$cache_time,'uid_sql'=>$vip_uids_in);
		} elseif('hotreply' == $this->Code) {
			$title = '热门评论';

			$d_list = array(1=>'近一天',7=>'近一周',14=>'近两周',30=>'近一月',);
			$d = isset($d_list[$this->Get['d']]) ? $this->Get['d'] : 7;
			$time = $d * 86400;
			$dateline = TIMESTAMP - $time;

						$per_page_num = $this->ShowConfig['reply_hot']["day{$d}"];
			if($per_page_num < 1) {
				$per_page_num = 20;
			}
			$cache_time = max(300, (int) ($this->CacheConfig['reply_hot']["day{$d}"] ? $this->CacheConfig['reply_hot']["day{$d}"] : $time / 90));
			$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&amp;code={$this->Code}&d={$d}" : "");

			$options = array(
				'cache_time' => $cache_time,
				'cache_key' => "topic-hotreply-{$d}",
				'perpage' => $per_page_num,
				'page_url' => $query_link,
				'type' => 'first',
				'where' => " $vip_uids_in `replys`>'0' AND `dateline`>='{$dateline}' ",
				'order' => " `replys` DESC , `dateline` DESC ",
			);
			$info = $TopicListLogic->get_data($options);
			if (!empty($info)) {
				$page_arr = $info['page'];
				$topics = $info['list'];
				$total_record = $info['count'];
			}
		} elseif('newreply' == $this->Code) {
			$title = '最新评论';

						$per_page_num = $this->ShowConfig['new_reply']['reply'];
			if($per_page_num < 1) {
				$per_page_num = 20;
			}
			$cache_time = max(180, (int) $this->CacheConfig['new_reply']["reply"]);
			$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&amp;code={$this->Code}" : "");

			$options = array(
				'cache_time' => $cache_time,
				'cache_key' => "topic-newreply",
				'perpage' => $per_page_num,
				'page_url' => $query_link,
				'where' => " $vip_uids_in `type` IN ('reply','both') AND `totid`>'0' ",
				'order' => " `dateline` DESC ",
			);
			$info = $TopicListLogic->get_data($options);
			if (!empty($info)) {
				$page_arr = $info['page'];
				$topics = $info['list'];
				$total_record = $info['count'];
			}
		} elseif ($this->Code =='new') {
			$title = '最新微博';
						$per_page_num = $this->ShowConfig['topic_new']['topic'];
			if($per_page_num < 1) {
				$per_page_num = 20;
			}
			$cache_time = max(0, (int) $this->CacheConfig['topic_new']["topic"]);
			$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&amp;code={$this->Code}" : "");


			
			if($this->ShowConfig['topic_new']['topic'] > 0) {
				$options = array(
					'cache_time' => $cache_time,
					'cache_key' => 'topic-newtopic',
					'type' => get_topic_type(),
					'page_url' => $query_link,
					'perpage' => $per_page_num,
					'order' => ' dateline DESC ',
					'filter' => $this->Get['type'],
				);
				if($this->Get['type'] && in_array($this->Get['type'],array('pic','music','video'))){
					$getTypeTidReturn = $TopicListLogic->GetTypeTid($type,$options['uid'],$this->Get['page'],$per_page_num);
					$options['tid'] = $getTypeTidReturn['tid'];
					$options['count'] = $getTypeTidReturn['count'];
				}else{
					$options['filter'] = $this->Get['type'];
				}

				if($this->Config['only_show_vip_topic']) {
					$options['uid'] = $vip_uids;
					$title = '最新V博';
					$NewVipUid9 = $TopicListLogic->GetVipUid();
					$new_vip_user_list = $this->TopicLogic->GetMember($NewVipUid9);
					if($options['uid'] && $NewVipUid9){
						$info = $TopicListLogic->get_data($options);
					}else{
						$info = array();
					}
				}else{
					$info = $TopicListLogic->get_data($options);
				}

				$topics = array();
				$total_record = 0;
				$page_arr = array();
				if (!empty($info)) {
					$topics = $info['list'];
					$total_record = $info['count'];
					$page_arr = $info['page'];
				}
			}
			$params = array('pp_time'=>$per_page_num,'c_time'=>$cache_time,'uid'=>base64_encode(serialize($vip_uids)));
		} elseif('tc' == $this->Code) {
			$title = '同城微博';

			$province_id = (int) $this->Get['province'];
			$city_id = (int) $this->Get['city'];
			$area_id = (int) $this->Get['area'];

			if($province_id < 1){
				if($member['province']){
					$province_name = $member['province'];
					$province_id = $this->DatabaseHandler->ResultFirst("select id from ".TABLE_PREFIX."common_district where name = '".$member['province']."' and `upid` = '0'");
					if($member['city']){
						$city_name = $member['city'];
						$city_id = $this->DatabaseHandler->ResultFirst("select id from ".TABLE_PREFIX."common_district where name = '".$member['city']."' ");
						if($member['area']){
							$area_name = $member['area'];
						}
					}
				}
			}else{
				if($city_id){
					$city_name = $this->DatabaseHandler->ResultFirst("select name from ".TABLE_PREFIX."common_district where id = '$city_id' ");
					if($area_id){
						$area_name = $this->DatabaseHandler->ResultFirst("select name from ".TABLE_PREFIX."common_district where id = '$area_id' ");
					}
				}
			}

			$query = $this->DatabaseHandler->Query("select id,name from ".TABLE_PREFIX."common_district where `upid` = '0' order by list");
			while ($rsdb = $query->GetRow()){
				$province[$rsdb['id']]['value']  = $rsdb['id'];
				$province[$rsdb['id']]['name']  = $rsdb['name'];
				if($province_id == $rsdb['id']){
					$province_name = $rsdb['name'];
				}
			}
			$province_list = Load::lib('form', 1)->Select("tc_province",$province,$province_id," onchange=\"changeProvince();\" style=\"width:150px\" ");

						$per_page_num = $this->ShowConfig['topic_new']['topic'];
			if($per_page_num < 1) {
				$per_page_num = 20;
			}
			$cache_time = max(0, (int) $this->CacheConfig['topic_new']["topic"]);
			$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&amp;code={$this->Code}" : "")."&province=$province_id&city=$city_id&area=$area_id";

			$info = array();
			if($province_name || $city_name) {
				if($this->ShowConfig['topic_new']['topic'] > 0) {
					$options = array(
						'cache_time' => $cache_time,
						'cache_key' => "topic-tctopic-{$province_name}-{$city_name}-{$area_name}",
						'type' => get_topic_type(),
						'vip' => $this->Config['only_show_vip_topic'],
						'page_url' => $query_link,
						'perpage' => $per_page_num,
					);
					if($area_name){
						$options['area'] = $area_name;
					}elseif($city_name){
						$options['city'] = $city_name;
					}elseif($province_name){
						$options['province'] = $province_name;
					}

					$info = $TopicListLogic->get_tc_data($options);
					$topics = array();
					$total_record = 0;
					$page_arr = array();
					if (!empty($info)) {
						$topics = $info['list'];
						$total_record = $info['count'];
						$page_arr = $info['page'];
					}
				}
			}
			$params = array('province'=>$province_name,'city'=>$city_name,'area'=>$area_name,'pp_time'=>$per_page_num,'c_time'=>$cache_time,'vip'=> $options['vip']);

						$events = array();
			$event_limit = (int) $this->ShowConfig['page_r']['tc_event'];
			if($city_id){
				$event_where = " city_id = '$city_id' ";
			}elseif($province_id){
				$event_where = " province_id = '$province_id' ";
			}
			if($event_limit && $event_where){
				$sql = "select id,title,app_num from ".TABLE_PREFIX."event where $event_where order by lasttime desc limit $event_limit ";
				$query = $this->DatabaseHandler->Query($sql);
				$events = $query->GetAll();
			}
		}


				if ('forward' != $this->Code) {
			$parent_id_list = array();
			if ($topics) {
				foreach ($topics as $row) {
					if(0 < ($p = (int) $row['parent_id'])) {
						$parent_id_list[$row['tid']] = $p;
					}
					if (0 < ($p = (int) $row['top_parent_id'])) {
						$parent_id_list[$row['tid']] = $p;
					}
				}
			}

			if($parent_id_list) {
								$pcount = count($parent_id_list);
				$options = array(
					'tid' => $parent_id_list,
					'type' => get_topic_type(),
					'count' => $pcount,
				);

				$p_info = $TopicListLogic->get_data($options);

				$parent_list = array();
				if (!empty($p_info)) {
					$parent_list = $p_info['list'];
					$keys = array_keys($parent_list);
				} else {
					$keys = array();
				}

								foreach ($parent_id_list as $key => $val) {
					if (in_array($val, $keys)) {
						continue;
					}
					unset($topics[$key]);
				}
			}
		}
		
		

		$this->Title = $title;
		if($_GET['filter_type']=='pic' && in_array($this->Code,array('new','hotforward','tc'))){
			if($page_arr['html']){
				$ajax_num = ceil($total_record/$per_page_num);
			}
			foreach ($topics as $key => $row) {				if($row['parent_id'] || $row['top_parent_id']) {
					unset($topics[$key]);
				}
			}
			$topic_pic_keys = array('ji','shi','gou');
			include($this->TemplateHandler->Template('topic_new_pic'));
		}else{
		$ajaxkey = array();
		$ajaxnum = 10;
		if(count($topics)>$ajaxnum){
			$topic_keys = array_keys($topics);
			$topics = array_slice($topics,0,$ajaxnum);
			array_splice($topic_keys,0,$ajaxnum);
			$num = ceil(count($topic_keys)/$ajaxnum);
			for($i=0;$i<$num;$i++){
				if(count($topic_keys)>$ajaxnum){
					$topic_key = array_splice($topic_keys,0,$ajaxnum);
				}else{
					$topic_key = $topic_keys;
				}
				$ajaxkey[] = base64_encode(serialize($topic_key));
			}
			$isloading = true;
		}

				
		$Gz_limit = $this->ShowConfig['topic_index']['guanzhu'];
		if($Gz_limit > 0) {
			$cache_time  = max(0, $this->CacheConfig['topic_index']['guanzhu']);
			$cache_id = 'misc/topic-index-guanzhu-'.$Gz_limit;
			if(!$cache_time || (false === ($concern_users = cache_file('get', $cache_id)))) {
				$concern_users = $this->TopicLogic->GetMember("order by `fans_count` desc limit {$Gz_limit}","`uid`,`ucuid`,`username`,`face_url`,`face`,`fans_count`,`validate`,`validate_category`,`nickname`");

				if($cache_time) {
					cache_file('set', $cache_id, $concern_users, $cache_time);
				}
			}
		}

		

				$Tg_limit = $this->ShowConfig['topic_new']['tag'];
		if($Tg_limit > 0) {
						$Tg_date  = max(43200, (int) $this->CacheConfig['topic_new']['day_tag']);
			$dateline = TIMESTAMP - $Tg_date;
			$cache_time  = max(0, (int) $this->CacheConfig['topic_new']['tag']);
			$cache_id = "misc/topic-new-tag-{$Tg_limit}-{$Tg_date}";

			if(!$cache_time || false === ($tags = cache_file('get', $cache_id))) {
				$sql = "select `id`,`name`,`topic_count`,`last_post` from `".TABLE_PREFIX."tag` where `last_post` > '{$dateline}' order by `topic_count` desc limit {$Tg_limit}";
				$query = $this->DatabaseHandler->Query($sql);
				$tags = $query->GetAll();

				if($cache_time) {
					cache_file('set', $cache_id, $tags, $cache_time);
				}
			}
		}
		
		include($this->TemplateHandler->Template('topic_new'));
		}
	}

	
	function _member($uid=0)
	{
		$member = array();
		if($uid < 1)
		{
			$member = jsg_member_info_by_mod();
		}

		$uid = (int) ($uid ? $uid : MEMBER_ID);
		if($uid > 0 && !$member)
		{
			$member = $this->TopicLogic->GetMember($uid);
		}
		if(!$member)
		{
			return false;
		}
		$uid = $member['uid'];

		if (!$member['follow_html'] && $uid!=MEMBER_ID && MEMBER_ID>0)
		{
			$member['follow_html'] = Load::model('buddy')->follow_html($member, 'uid', 'follow_html', 1);
		}

				if(true === UCENTER_FACE && MEMBER_ID == $uid && MEMBER_UCUID > 0 && !($member['__face__']))
		{
			include_once(ROOT_PATH . './api/uc_client/client.php');

			$uc_check_result = uc_check_avatar(MEMBER_UCUID);

			if($uc_check_result)
			{
				$this->DatabaseHandler->Query("update ".TABLE_PREFIX."members set `face`='./images/noavatar.gif' where `uid`='{$uid}'");
			}
		}

		return $member;
	}

	function _recommendTag($day=1,$limit=12,$cache_time=0)
	{
		if($limit < 1) return false;

		$time = $day * 86400;
		$cache_time = ($cache_time ? $cache_time : $time / 90);
		$cache_id = "misc/recommendTopicTag-{$day}-{$limit}";

		if (false === ($list = cache_file('get', $cache_id))) {
			$dateline = TIMESTAMP - $time;
			$sql = "SELECT DISTINCT(tag_id) AS tag_id, COUNT(item_id) AS item_id_count FROM `".TABLE_PREFIX."topic_tag` WHERE dateline>=$dateline GROUP BY tag_id ORDER BY item_id_count DESC LIMIT {$limit}";
			$query = $this->DatabaseHandler->Query($sql);
			$ids = array();
			while (false != ($row = $query->GetRow()))
			{
				$ids[$row['tag_id']] = $row['tag_id'];
			}

			$list = array();
			if($ids) {
				$sql = "select `id`,`name`,`topic_count` from `".TABLE_PREFIX."tag` where `id` in('".implode("','",$ids)."')";
				$query = $this->DatabaseHandler->Query($sql);
				$list = $query->GetAll();
			}

			cache_file('set', $cache_id, $list, $cache_time);
		}

		return $list;

	}

	function _recommendUser($day=1,$limit=12,$cache_time=0) {
		return Load::model('data_block')->recommend_topic_user($day, $limit, $cache_time);
	}

	function _guestIndex() {
		if(MEMBER_ID > 0) {
			$member = $this->_member(MEMBER_ID);
		}


				$limit = $this->ShowConfig['topic_index']['guanzhu'];
		if ($limit > 0) {
			$cache_id = "index/r_users";
			if(false === ($r_users = cache_file('get', $cache_id))) {
				$r_users = $this->TopicLogic->GetMember("where face !='' order by `fans_count` desc limit {$limit}","`uid`,`ucuid`,`username`,`face_url`,`face`,`fans_count`,`validate`,`validate_category`,`nickname`");

				cache_file('set', $cache_id, $r_users, $this->CacheConfig['topic_index']['guanzhu']);
			}
		}


				$day2_r_users = $this->_recommendUser(7,$this->ShowConfig['topic_index']['new_user'],$this->CacheConfig['topic_index']['new_user']);


				$r_tags = $this->_recommendTag(2,$this->ShowConfig['topic_index']['hot_tag'],$this->CacheConfig['topic_index']['hot_tag']);


		$recommend_count = 0;
		if ($this->ShowConfig['topic_index']['recommend_topic']) {
			$cache_id = "index/recommend_topics";
			if (false === ($cache_recommend_topics = cache_file('get', $cache_id))) {
				
				$TopicListLogic = Load::logic('topic_list', 1);
				$type_sql = jimplode(get_topic_type());
				$fields = " a.* ";
				$vip_t = $vip_w = '';
				if($this->Config['only_show_vip_topic']) {
					$vip_t = ', '.DB::table('members').' m ';
					$vip_w = ' and m.uid=a.uid and m.validate="1" ';
				}
				$table = " ".DB::table("topic")." a,(SELECT uid, max(dateline) max_dateline FROM ".DB::table("topic")." WHERE type IN(".$type_sql.") GROUP BY uid) b $vip_t ";
				$where = "  WHERE a.uid = b.uid AND a.dateline = b.max_dateline AND a.type IN({$type_sql}) $vip_w ORDER BY a.dateline DESC LIMIT {$this->ShowConfig['topic_index']['recommend_topic']}";
				$recommend_topics = $this->TopicLogic->Get($where, $fields, 'Make', $table);

								$parent_list = $this->TopicLogic->GetParentTopic($recommend_topics);
				
				$cache_recommend_topics = array(
					'recommend_topics' => $recommend_topics,
					'parent_list' => $parent_list,
				);
				cache_file('set', $cache_id, $cache_recommend_topics, $this->CacheConfig['topic_index']['recommend_topic']);
			} else {
				$recommend_topics = $cache_recommend_topics['recommend_topics'];
				$parent_list = $cache_recommend_topics['parent_list'];
			}
			$recommend_count = count($recommend_topics);
		}


				$cache_id = 'notice/list-topic_index_guest';
		if (false===($list_notice = cache_file('get', $cache_id))) {
			$sql="select `id`,`title` from ".TABLE_PREFIX.'notice'." order by `id` desc limit 5 ";
			$query = $this->DatabaseHandler->Query($sql);
			$list_notice = array();
			while (false != ($row = $query->GetRow())) {
				$row['titles']	= $row['title'];
				$row['title'] 	= cutstr($row['title'],30);
				$list_notice[] 	= $row;
			}
			cache_file('set', $cache_id, $list_notice, 86400);
		}


		$this->MetaKeywords = $this->Config['index_meta_keywords'];
		$this->MetaDescription = $this->Config['index_meta_description'];
		include($this->TemplateHandler->Template('topic_index_guest'));
	}

		function _myGroup($uid=0,$limit='')
	{

		$order = 'order by `group_count` desc';

		$sql="Select `id`,`group_name`,`group_count` From ".TABLE_PREFIX.'group'." where `uid` = '{$uid}' {$order} {$limit}";
		$query = $this->DatabaseHandler->Query($sql);
		$list = $query->GetAll();

		return $list;
	}

		function _GroupFields($uid=0)
	{
		$list = array();

		if(MEMBER_ID > 0)
		{
			$sql="Select * From ".TABLE_PREFIX.'groupfields'." where `touid` = '{$uid}' and `uid` = '".MEMBER_ID."' order by `id` desc";

			$query = $this->DatabaseHandler->Query($sql);
			$list = $query->GetAll();
		}

		return $list;
	}

	function _Medal($medalid=0,$uid=0)
	{
		
		
		

		$uid = (is_numeric($uid) ? $uid : 0);

		$medal_list = array();

		if($uid > 0)
		{
			$sql = "select  U_MEDAL.dateline ,  MEDAL.medal_img , MEDAL.conditions , MEDAL.medal_name ,MEDAL.medal_depict ,MEDAL.id , U_MEDAL.*
					from `".TABLE_PREFIX."medal` MEDAL
					left join `".TABLE_PREFIX."user_medal` U_MEDAL on MEDAL.id=U_MEDAL.medalid
					where U_MEDAL.uid='{$uid}'
					and U_MEDAL.is_index = 1
					and MEDAL.is_open = 1 ";

			$query = $this->DatabaseHandler->Query($sql);
			while (false != ($row = $query->GetRow()))
			{
				$row['dateline'] = date('m-d日 H:s ',$row['dateline']);
				$medal_list[$row['id']] = $row;
			}
		}

		return $medal_list;
	}

		function Photo()
	{
		$this->Title = '图片墙';
		$nickname = '我';
		$uid = 0;
		if(isset($this->Get['uid'])){
			if((int)$this->Get['uid']>0){
				$uid = (int)$this->Get['uid'];
				$type = 1;
				if($uid != MEMBER_ID){
					$nickname = DB::result_first("select `nickname` from ".TABLE_PREFIX."members where uid = '$uid'");
					$this->Title = $nickname."关注的人的图片";
				}else{
					$this->Title = '我关注人的图片';
				}
			}
		}

		$TopicListLogic = Load::logic('topic_list', 1);
		$photo_num = 20; 		$p = array(
			'count' => $photo_num,
			'vip' => $this->Config['only_show_vip_topic'],
			'limit' => $photo_num,
			'uid' => $uid,
		);
		$info = $TopicListLogic->get_photo_list($p);
		if (!empty($info)) {
			$topic_list = $info['list'];
			$isloading = ($info['count'] >= $p['limit'] ? true : false);
		}else{
			$isloading = false;
		}
		if($this->Config['attach_enable']){$allow_attach = 1;}else{$allow_attach = 0;}

		$t_col_foot = 't_col_foot'; 		$t_col_backTop = 't_col_backTop';
		$url_uid = ($uid ? $uid : MEMBER_ID); 		include($this->TemplateHandler->Template('topic_photo'));
	}
}

?>