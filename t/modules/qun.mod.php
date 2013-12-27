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
 * @Date 2012-07-04 18:49:37 968828165 603267873 42815 $
 *******************************************************************/



 

if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
		var $item = 'qun';
	var $item_id = 0;
	
	var $TopicLogic = null;
	
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		
		$qun_setting = $this->Config['qun_setting'];
		if (MEMBER_ROLE_TYPE != 'admin') {
			if (!$qun_setting['qun_open']) {
				$this->Messager('站点暂时不开放微群功能', 'index.php');
			}
		}
		
		
		$this->TopicLogic = Load::logic('topic', 1);
		
				$this->my = array();
		if (MEMBER_ID < 1 && $this->Code) {
			$this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",'index.php?mod=login');	
		}
		
		$this->my = jsg_member_info(MEMBER_ID);
		
		$this->ShowConfig = ConfigHandler::get('show');
		
		Load::logic('qun');
		$this->QunLogic = new QunLogic();
		
		ob_start();
		
				$code = $this->Code;
		if (!empty($this->Get['qid']) && empty($code)) {
			$code = 'view';
			$this->Code = $code;
		}
		
		if (method_exists('ModuleObject', $code)) {
			$this->$code();
		} else {
			$this->Code = 'index';
			$this->index();
		}
		$body = ob_get_clean();
		$this->ShowBody($body);
	}
	
	
	function index()
	{
		$this->category();
		
	}
	
	
	function create()
	{
		$catselect = $this->QunLogic->get_catselect();
		$this->Title = '创建新微群';
		$detail = array();
		$is_allowed = true;
				if (MEMBER_ROLE_TYPE != 'admin') {
			$is_allowed = $this->QunLogic->allowed_create(MEMBER_ID, $detail);
		}
		
		$member = &$this->my;
		if($is_allowed){
			$themelist = $this->QunLogic->getQunThemeList();
			
			$checked = array();
			$checked['gview_perm'][0] = 'checked="checked"';
			$checked['join_type'][0] = 'checked="checked"';
			$u_tips = $this->QunLogic->upload_tips();	
			
			Load::lib('form');
			$FormHandler = new FormHandler();
						$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."common_district where `upid` = '0' order by list");
			while ($rsdb = $query->GetRow()){
				$province[$rsdb['id']]['value']  = $rsdb['id'];
				$province[$rsdb['id']]['name']  = $rsdb['name'];
				if($member['province'] == $rsdb['name']){
					$province_id = $rsdb['id'];
				}
			}
			$province_list = $FormHandler->Select("province",$province,$province_id,"onchange=\"changeProvince();\"");
			if($province_id){
				if($member['city']){
					$hid_city = $this->DatabaseHandler->ResultFirst("select id from ".TABLE_PREFIX."common_district where name = '$member[city]' and upid = '$province_id'");				}
			}
		}
		include template('qun/create');
	}
	
	function docreate()
	{
		$post = $this->Post;
		
		if (MEMBER_ROLE_TYPE != 'admin') {
			$detail = array();
			$is_allowed = $this->QunLogic->allowed_create(MEMBER_ID, $detail);
			if ($is_allowed == false) {
				$this->Messager("不允许创建微群");
			}
		}
		
		$member = $this->MemberHandler->MemberFields;
		$post['username'] = jaddslashes($member['username']);
		$post['uid'] = $member['uid'];
		
		$province = trim($this->DatabaseHandler->ResultFirst("select name from ".TABLE_PREFIX."common_district where id = '".$this->Post['province']."'")); 		$post['province'] = $province;
		$city = trim($this->DatabaseHandler->ResultFirst("select name from ".TABLE_PREFIX."common_district where id = '".$this->Post['city']."'"));		$post['city'] = $city;
		
		$ret = $this->QunLogic->create($post);
		
		if ($ret == 1) {
			$this->Messager("创建微群成功了", 'index.php?mod=qun&code=invite&qid='.$post['qid']);
		} else if ($ret == -1) {
			$this->Messager("微群名称为空或者微群名称长度小于3", -1);
		} else if ($ret == -2) {
			$this->Messager("微群分类为空或者不存在", -1);
		} else if ($ret == -3) {
			$this->Messager("群组所在省份或者城市为空", -1);
		}
		exit;
	}
	
	
	function profile()
	{
		$view = empty($this->Get['view']) ? 'index' : trim($this->Get['view']);
		$view_list = array(
				'managed' => array(
					'title' => '我管理的微群',
					'code' => 'managed',
				),
				'joined' => array(
					'title' => '我加入的微群',
					'code' => 'joined',
				),
				'followed' => array(
					'title' => '我关注的人的微群',
					'code' => 'followed',
				)
		);
		
		if (in_array($view, array('managed', 'joined', 'followed'))) {
			$title = $this->Title = $view_list[$view]['title'];
			$gets = array(
				'mod' => 'qun',
				'code' => 'profile',
				'view' => $view,
			);
			$page_url = 'index.php?'.url_implode($gets);
			$page = empty($this->Get['page']) ? 0 : intval(trim($this->Get['page']));
			if ($page < 1) {
				$page = 1;
			}
			$perpage = 42;
			$start = ($page - 1) * $perpage;
			$limit = "{$start},{$perpage}";
			$qun_list = $this->QunLogic->get_qun_list(array('type' => $view, 'limit' => $limit));
			$multi = page($qun_list['row_nums'], $perpage, $page_url);
		} else {
			$limit = 18;
			$view = 'index';
			$all_list = $view_list;
			unset($all_list['followed']);
			foreach ($all_list as $key => $value) {
				$tmp = array();
				$tmp = $this->QunLogic->get_qun_list(array('type' => $key, 'limit' => $limit));
				$all_list[$key]['list'] = $tmp['list'];
				$all_list[$key]['row_nums'] = $tmp['row_nums'];
			}
			unset($tmp);
			$this->Title = '我的微群';
		}
		$member = &$this->my;

						$ploys = $this->_base_ploy();
		
		include template('qun/profile');
	}
	
	
	function view()
	{
		$uid = MEMBER_ID;
		$qid = intval(trim($this->Get['qid']));
		$tag = getSafeCode($this->Get['tag']);
		$view = trim($this->Get['view']);
		$qun_info = $this->QunLogic->get_qun_info($qid);
		
				if($qun_info['qun_theme_id']){
			$this->Config['qun_theme_id'] = $qun_info['qun_theme_id'];
			$this->Config['theme_id'] = '';
			$this->Config['theme_bg_image'] = '';
			$this->Config['theme_bg_color'] = '';
			$this->Config['theme_text_color'] = '';
			$this->Config['theme_link_color'] = '';
			$this->Config['theme_bg_image_type'] = '';
			$this->Config['theme_bg_repeat'] = '';
			$this->Config['theme_bg_fixed'] = '';
		}
		
		if (empty($qun_info)) {
			$this->Messager("当前群不存在或者已经被删除了", 'index.php?mod=qun');
		}
		if (!empty($tag)) {
			$qun_info['icon'] = $this->QunLogic->qun_avatar($qun_info['qid'], 's');
		} else {
			$qun_info['icon'] = $this->QunLogic->qun_avatar($qun_info['qid'], 'b');
		}
		$active = array();
		$topic_list = array();
		$get_topic_flg = true;
		
				$this->item_id = $qid;
		$params['code'] = $this->item;
		
				$cat_ary = $this->QunLogic->get_category();		$top_cat =array();
		$sub_cat = array();
		if($cat_ary['second'][$qun_info['cat_id']]){
			$sub_cat = array(
				'cat_name' => $cat_ary['second'][$qun_info['cat_id']]['cat_name'],
				'cat_id' => $qun_info['cat_id'],
			);
			$parent_id = $cat_ary['second'][$qun_info['cat_id']]['parent_id'];
			$top_cat = array(
				'cat_name' => $cat_ary['first'][$parent_id]['cat_name'],
				'cat_id' => $parent_id,
			);
		}else{
			$top_cat = array(
				'cat_name' => $cat_ary['first'][$qun_info['cat_id']]['cat_name'],
				'cat_id' => $qun_info['cat_id'],
			);
		}
		
				$founder_info = $this->TopicLogic->GetMember($qun_info['founderuid']);
		
				$qun_admin_list = $this->QunLogic->get_admin_list($qid);
		
				$tag_ary = $this->QunLogic->get_qun_tag($qid);
		
				$recd_event_list = $this->QunLogic->getRecdEventList($qid);
				$recd_vote_list = $this->QunLogic->getRecdVoteList($qid);
		
				$buddyids = get_buddyids(MEMBER_ID);
		$where_sql = jimplode($buddyids);
		$followme_nums = DB::result_first("SELECT COUNT(*) 
										   FROM ".DB::table('qun_user')." 
										   WHERE qid='{$qid}' AND uid IN({$where_sql})");
		$perm = $this->QunLogic->chk_perm($qid, MEMBER_ID);
		$status = '';
		
				$allow_list_manage = false;
		if (in_array($perm, array(1, 2, 4)) || MEMBER_ROLE_TYPE == 'admin') {
			$status = 'isgroupuser';
			$allow_list_manage = true;
		}
		
				Load::functions('app');
		$gets = array(
			'mod' => 'qun',
			'type' => $this->Get['type'],
			'qid' => $qid,
			'tag' => $this->Get['tag'],
			'code' => $this->Code,
		);
		$page_url = 'index.php?'.url_implode($gets);
		
		$where = " type!='reply' ";
		if ($this->Get['type']) {
			if ('pic' == $this->Get['type']){
				$where = " `imageid` > 0 ";
			} else if('video' == $this->Get['type']) {
				$where = " `videoid` > 0 ";
			} else if('music' == $this->Get['type']) {
				$where = " `musicid` > 0 ";
			}
		}

				if (!empty($tag)) {
			$sql = "SELECT * FROM  ".DB::table('tag')." WHERE name='".addslashes($tag)."'";
			$tag_info = DB::fetch_first($sql);
			$tag_id = $tag_info['id'];
			$sql = "SELECT item_id FROM ".DB::table('topic_tag')." WHERE tag_id='{$tag_id}' ";
			$query = DB::query($sql);
			$topic_ids = array();
			while ($row = DB::fetch($query)) {
				$topic_ids[$row['item_id']] = $row['item_id'];
			}
			if (!empty($topic_ids)) {
				$where .= " AND tid IN(".jimplode($topic_ids).") ";
			}
			$content = "#{$tag}#";
			$view = 'tag';
		} else {
						if ($view == "newreply") {
				$type_where = ' AND '.$where;
								
								$per_page_num = $this->ShowConfig['qun']['topic_reply'] ? $this->ShowConfig['qun']['topic_reply'] : 10;
				
								$count = DB::result_first("SELECT COUNT(*) 
											FROM ".DB::table('topic')." 
											Where `replys` > 0 AND item='qun' AND item_id='{$qid}' {$type_where}");
				if ($count > 0) {
					$page_arr = page ($count, $per_page_num, $page_url, array('return'=>'array'));
					$condition = " WHERE  `replys` > 0 AND item='qun' AND item_id='{$qid}' {$type_where} ORDER BY `lastupdate` DESC {$page_arr['limit']}";	
					$topic_list = $this->TopicLogic->Get($condition);	
				}
				$get_topic_flg = false;
			} else if ($view == 'recd') {
				Load::logic('topic_list');
				$TopicListLogic = new TopicListLogic();
				$p = array(
					'where' => " tr.recd <= 2 AND tr.item='qun' AND tr.item_id='{$qid}' ",
					'perpage' => $per_page_num,
					'filter' => $this->Get['type'],
				);
				$info = $TopicListLogic->get_recd_list($p);
				if (!empty($info)) {
					$total_record = $info['count'];
					$topic_list = $info['list'];
					$page_arr = $info['page'];
				}
				$get_topic_flg = false;
			}elseif($view == 'event'){
				$param = array(
					'qid' => $qid,
					'where' => " a.item = 'qun' and a.item_id = '$qid' ",
					'page' => true,
					'perpage' => 10,
				    'page_url' => 'index.php?mod=qun&view=event&qid='.$qid,
				);
				load::logic('event');
				$EventLogic = new EventLogic();
				$return = $EventLogic->getEventInfo($param);
				$count = $return['count'] ? $return['count'] : 0;
				$event = $return['event_list'];
				$page_arr = $return['page'];
				$get_topic_flg = false;
			}elseif($view == 'vote'){
				load::logic('vote');
				$VoteLogic = new VoteLogic();
				$param = array(
					'where' => " v.item = 'qun' and v.item_id = '$qid' ",
					'order' => " order by v.dateline ",
					'page' => true,
					'perpage' => 10,
					'page_url' => 'index.php?mod=qun&view=vote&qid='.$qid,
				);
				$return = $VoteLogic->find($param);
				$count = $return['count'] ? $return['count'] : 0;
				if(!empty($return)){
					$vote_list = $return['vote_list'];
					$page_arr['html'] = $return['page']['html'];
					$uid_ary = $return['uids'];
				}
								if (!empty($uid_ary)) {
					$members = $this->TopicLogic->GetMember($uid_ary);
				}
				$get_topic_flg = false;
			}elseif($view == 'image'){
				
				
				$param = array(
					'item' => 'qun',
					'itemid' => $qid,
					'page' => true,
					'per_page_num' => 20,
					'page_url' => 'index.php?mod=qun&view=image&qid='.$qid,
				);
				$return = Load::logic('image', 1)->get($param);
				$count = $return['count'] ? $return['count'] : 0;
				$image_list = $return['list'];
				$page_arr['html'] = $return['page']['html'];
				$get_topic_flg = false;
			}elseif($view == 'attach' && $this->Config['qun_attach_enable']){
				$type = $this->Get['type'];
				load::logic('attach');
				$AttachLogic = new AttachLogic();
				$param = array(
					'item' => 'qun',
					'itemid' => $qid,
					'page' => true,
					'per_page_num' => 20,
					'page_url' => 'index.php?mod=qun&view=attach&qid='.$qid.'&type='.$type,
				);
				if ($type) {
					if ('hot' == $type){
						$param['order'] = " order by download DESC ";
					} else if('new' == $type) {
						$param['order'] = " order by id DESC ";
					}
				}
				$return = $AttachLogic->get($param);
				$count = $return['count'] ? $return['count'] : 0;
				$attach_list = $return['list'];
				$page_arr['html'] = $return['page']['html'];
				$get_topic_flg = false;
			} else {
				$view = 'newtopic';
				$active['newtopic'] = "class='current'";
			}
			$active[$view] = "class='current'";
		}

		if ($get_topic_flg) {
			$options = array(
				'where' => $where,
				'page' => true,
				'perpage' => $this->ShowConfig['qun']['topic_new'] ? $this->ShowConfig['qun']['topic_new'] : 10,
				'page_url' => $page_url,
			);
			$topic_info = app_get_topic_list($this->item, $qid, $options);
			if (!empty($topic_info)) {
				$topic_list = $topic_info['list'];
				$page_arr['html'] = $topic_info['page']['html'];
			}
		}
		$topic_list_count = count($topic_list);
		$parent_list = $this->_get_parent_topic($topic_list);
		
		$gets = array(
			'item' => $this->item,
			'item_id' => $qid,
					);

		$member = &$this->my;
	
		if ($member['medal_id']) {
			$medal_list = $this->TopicLogic->GetMedal($member['medal_id'],$member['uid']);
		}
		
		$set_qun_closed = 1;
		if (empty($tag)) {
						$new_members = $this->QunLogic->get_new_member_list($qid);
			
						$announcement = $this->QunLogic->new_announcement($qid);
	
			$this->Title = '微群 - '.$qun_info['name'];
			include template('qun/view');
		} else {
			$this->Title = '微群 - '.$qun_info['name'].' - '.$tag;
			include template('qun/tag_view');
		}
	}
	
	
	function manage()
	{
		$manage_info = $this->_chk_manage();
		$qun_info = $manage_info['data'];
		$op = $manage_info['op'];
		$perm = $manage_info['perm'];
		$active[$op] = true;
		$qid = $qun_info['qid'];

		$this->Title = '微群管理 - '.$qun_info['name'];
		if ($op == 'icon') {
			$qun_info['icon'] = $this->QunLogic->qun_avatar($qun_info['qid'], 'b');
			$u_tips = $this->QunLogic->upload_tips();
		} else if ($op == 'privacy') {
			$checked = array();
			$checked['join_type'][$qun_info['join_type']] = 'checked="checked"';
			$checked['gview_perm'][$qun_info['gview_perm']] = 'checked="checked"';
		} else if ($op == 'members') {
			$view = trim($this->Get['view']);
			
			$perpage = 20;
			$gets = array(
				'mod' => 'qun',
				'code' => 'manage',
				'op' => 'members',
				'view' => $view,
				'qid' => $qun_info['qid'],
			);
			$page_url = 'index.php?'.url_implode($gets);
			
			$order_sql = '';
			$where_sql = " qu.qid='{$qun_info['qid']}' ";
			if ($view == 'followed') {
								$followed_ids = get_buddyids(MEMBER_ID);
				if (empty($followed_ids)) {
					$where_sql = ' 0 ';
				} else {
					$where_sql .= " AND uid IN(".jimplode($followed_ids).") ";
				}
				$order_sql = " qu.join_time DESC ";
			} else {
								$view = 'all';
				$order_sql .= " qu.level,qu.join_time DESC ";
				
				$level = $this->QunLogic->qun_level($qun_info['qid']);
				$admin_nums = $this->QunLogic->admin_nums($qun_info['qid']);
			}
			$active[$view] = 'class="on"';
			
			$members = array();
			$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('qun_user')." AS qu  WHERE {$where_sql} ");
			if ($count) {
								$_config = array(
					'return' => 'array',
				);
				$page_arr = page($count, $perpage, $page_url, $_config);
				$query = DB::query("SELECT qu.*,m.username,m.nickname  
									FROM ".DB::table('qun_user')." AS qu 
									LEFT JOIN ".DB::table('members')." AS m
									USING (uid) 
									WHERE {$where_sql} 
									ORDER BY {$order_sql} 
									{$page_arr['limit']} ");
				while ($value = DB::fetch($query)) {
					$value['face'] = face_get($value['uid']);
					$members[] = $value;
				}
			}
		} else if ($op == 'check_member') {
						$perpage = 20;
			$gets = array(
				'mod' => 'qun',
				'code' => 'manage',
				'op' => 'check_member',
				'qid' => $qun_info['qid'],
			);
			$page_url = 'index.php?'.url_implode($gets);
			$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('qun_apply')." WHERE qid='{$qid}'");
			$members = array();
			if ($count) {
								$_config = array(
					'return' => 'array',
				);
				$page_arr = page($count, $perpage, $page_url, $_config);
				$query = DB::query("SELECT qa.*,m.username,m.nickname  
									FROM ".DB::table('qun_apply')." AS qa
									LEFT JOIN ".DB::table('members')." AS m
									USING(uid)  
									WHERE qa.qid='{$qid}' 
									ORDER BY qa.apply_time DESC 
									{$page_arr['limit']} ");
				while ($value = DB::fetch($query)) {
					$value['face'] = face_get($value['uid']);
					$members[] = $value;
				}
			}
		} else if ($op == 'announcement') {
			$gets = array(
				'mod' => 'qun',
				'code' => 'manage',
				'op' => 'announcement',
				'qid' => $qun_info['qid'],
			);
			$page_url = 'index.php?'.url_implode($gets);
			$page = empty($this->Get['page']) ? 0 : intval(trim($this->Get['page']));
			if ($page < 1) {
				$page = 1;
			}
			$perpage = 20;
			$start = ($page - 1) * $perpage;
			
			$param = array(
				'where' => " qid='{$qun_info['qid']}' ", 
				'order' => ' qa.dateline DESC ',
				'limit' => " {$start}, {$perpage} ",
			);
			$info = $this->QunLogic->get_announcement_list($param);
			$count = 0;
			$announcements = array();
			if (!empty($info)) {
				$announcements = $info['list'];
				$count = $info['row_nums'];
				$multi = page($count, $perpage, $page_url);
			}
		}elseif($op == 'event'){
			$param = array(
				'qid' => $qid,
			);
			extract($this->QunLogic->getEvent($param));
		}elseif($op == 'vote'){
			$param = array(
				'qid' => $qid,
			);
			extract($this->QunLogic->getVote($param));
		}elseif($op == 'attach' && $this->Config['qun_attach_enable']){
			$param = array(
				'qid' => $qid,
			);
			extract($this->QunLogic->getAttach($param));
		} else {
			$op = 'setting';
						$tag = $this->QunLogic->get_qun_strtag($qid);
			
			$cat_ary = ConfigHandler::get('qun_category');
			if(empty($cat_ary['second'][$qun_info['cat_id']])){
				$catselect = $this->QunLogic->get_catselect($qun_info['cat_id'], 0);
			}else{
				$catselect = $this->QunLogic->get_catselect(0, $qun_info['cat_id']);
			}
			$themelist = $this->QunLogic->getQunThemeList();
			
			Load::lib('form');
			$FormHandler = new FormHandler();
						$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."common_district where `upid` = '0' order by list");
			while ($rsdb = $query->GetRow()){
				$province[$rsdb['id']]['value']  = $rsdb['id'];
				$province[$rsdb['id']]['name']  = $rsdb['name'];
				if($qun_info['province'] == $rsdb['name']){
					$province_id = $rsdb['id'];
				}
			}
			$province_list = $FormHandler->Select("province",$province,$province_id,"onchange=\"changeProvince();\"");
			if($province_id){
				if($qun_info['city']){
					$hid_city = $this->DatabaseHandler->ResultFirst("select id from ".TABLE_PREFIX."common_district where name = '$qun_info[city]' and upid = '$province_id'");				}
			}
		}

			$member = &$this->my;		
			if ($member['medal_id']) {
				$medal_list = $this->TopicLogic->GetMedal($member['medal_id'],$member['uid']);
			}
		include_once template('qun/manage');
	}
	
	function domanage()
	{
		$manage_info = $this->_chk_manage();
		$qun_info = $manage_info['data'];
		$op = $manage_info['op'];
		
		$post = &$this->Post;
		$goto_url = '';
		
		if ($op == 'setting') {
			$post['qid'] = $qun_info['qid'];

			$province = trim($this->DatabaseHandler->ResultFirst("select name from ".TABLE_PREFIX."common_district where id = ".(int) $this->Post['province'])); 			$post['province'] = $province;
			$city = trim($this->DatabaseHandler->ResultFirst("select name from ".TABLE_PREFIX."common_district where id = ".(int) $this->Post['city']));			$post['city'] = $city;
			
			$ret = $this->QunLogic->modify_setting($this->Post);
			if ($ret == -1) {
				$this->Messager("微群名称不能小于3个字节");
			} else if ($ret == -2) {
				$this->Messager("您还没有选择微群分类呢");
			} else if ($ret == -3) {
				$this->Messager("您还没有选择微群所在地呢");
			}
		} else if ($op == 'icon') {
			if (empty($_FILES['icon']['name'])) {
				$this->Messager("请选择一张jpg，gif格式的图片");
			}
			$upload_data = array(
				'field' => 'icon',
				'qid' => $qun_info['qid'],
			);
			$ret = $this->QunLogic->upload_icon($upload_data);
			if ($ret == -1) {
				$this->Messager("图片格式错误，系统只支持jpg,gif格式");
			} else if ($ret == -2) {
				$this->Messager("图片上传失败，请重新上传");
			}
		} else if ($op == 'privacy') {
						$gview_perm = $this->Post['gview_perm'] > 0 ? 1 : 0; 
			$join_type = $this->Post['join_type'] > 0 ? 1 : 0; 
			$set_ary = array(
				'gview_perm' => $gview_perm,
				'join_type' => $join_type,
			);
			DB::update('qun', $set_ary, array('qid' => $qun_info['qid']));
		} else if (in_array($op, array('del_member', 'upgrade2admin', 'degrade', 'audit'))) {
						$uid = intval(trim($this->Get['uid']));
			if (empty($uid)) {
				$this->Messager("错误的操作");
			}
			$qun_user = $this->QunLogic->get_qun_user($qun_info['qid'], $uid);
			if (empty($qun_user)) {
				$this->Messager("当前用户不是群内成员，无法对他进行操作");
			}
			
			if ($qun_user['level'] == 1) {
				$this->Messager("不能对创始人进行此操作");
			}
			
			if ($qun_user['level'] == 2 && $manage_info['perm'] != 1) {
				$this->Messager("没有权限");
			}
			if ($op == 'del_member') {
								$this->QunLogic->quit_qun($qun_info['qid'], $uid);
			} else if ($op == 'upgrade2admin') {	
								$level = $this->QunLogic->qun_level($qun_info['qid']);
				$admin_nums = $this->QunLogic->admin_nums($qun_info['qid']);
				if ($level['admin_num'] <= $admin_nums) {
					$this->Messager("管理员人数到达上限无法再加入");
				}
				$ret = $this->QunLogic->upgrade2admin($qun_info['qid'], $uid);
			} else if ($op == 'degrade') {
								$this->QunLogic->degrade($qun_info['qid'], $uid);
			}
			$this->Messager("您的操作成功了");
		} else if ($op == 'members') {
						$uids = $this->Post['ids'];
			if (empty($uids)) {
				$this->Messager('没有选择要操作的成员');
			}
			
			$qun_user_list = array();
			$where_sql = " qid={$qun_info['qid']} AND uid IN(".jimplode($uids).") ";
			$uids = array();
			$query = DB::query("SELECT * FROM ".DB::table('qun_user')." WHERE {$where_sql} ");
			while ($value = DB::fetch($query)) {
				
								if (($value['level'] == 1) || ($value['level'] == 2 && $manage_info['perm'] != 1)) {
					continue;
				}
				$uids[] = $value['uid'];
			}
			foreach ($uids as $uid) {
				$this->QunLogic->quit_qun($qun_info['qid'], $uid);
			}
			$this->Messager("您的操作成功了");
		} else if ($op == 'check_member') {
						$ids = array();
						if (!empty($this->Post['ids'])) {
				$ids = $this->Post['ids'];
				$check_type = intval(trim($this->Post['check_type']));
			} else {
				$ids = array($this->Get['ids']);
				$check_type = intval(trim($this->Get['check_type']));
			}
		
			if ($check_type == 1) {
				$check_type = 'yes';
			} else if ($check_type == 2) {
				$check_type = 'no';
			} else {
				$this->Messager("错误的操作");
			}
			
			if (!empty($ids)) {
				$where_sql = " qid='{$qun_info['qid']}' AND uid IN(".jimplode($ids).") ";
				$m_ary = array();
				$query = DB::query("SELECT * FROM ".DB::table('qun_apply')." WHERE {$where_sql} ");
				while ($value = DB::fetch($query)) {
					$m_ary[] = $value;
				}
				
				$level = $this->QunLogic->qun_level($qun_info['qid']);
				$count = count($m_ary);
				$p = $count+$qun_info['member_num'] - $level['member_num'];
				if ($p > 0) {
					$count = $count - $p;
				}
				
				for ($i=0;$i<$count;++$i) {
					$info = array(
						'uid' => $m_ary[$i]['uid'],
						'username' => $m_ary[$i]['username'],
					);
					$this->QunLogic->audit_qun_apply($qun_info['qid'], $info, $check_type);
				}
			}
			$this->Messager("您的操作成功了");
		} else if ($op == 'announcement') {
						$message = empty($this->Post['message']) ? '' : trim($this->Post['message']);
			if (empty($message)) {
				$this->Messager('至少要写点什么吧');
			}
						$message = getstr($message, 800, 1, 1);
			$data = array(
				'qid' => $qun_info['qid'],
				'message' => $message,
				'author' => $this->my['username'],
				'author_id' => MEMBER_ID,
			);
			$this->QunLogic->add_announcement($data);
		} else if ($op == 'del_announcement') {
			$id = empty($this->Get['id']) ? 0 : intval($this->Get['id']);
			$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('qun_announcement')." WHERE id='{$id}'");
			if ($count == 0) {
				$this->Messager("当前公告不存在");
			}
			$this->QunLogic->del_announcement($id);	
		} else if ($op == 'dismiss') {
						$this->QunLogic->delete_qun($qun_info['qid'], $qun_info['cat_id']);
			$goto_url = 'index.php?mod=qun&code=profile';
		}
		$this->Messager("您的操作成功了", $goto_url);
	}
	
	
	function category()
	{
		$cat_id = empty($this->Get['cat_id']) ? 0 : intval(trim($this->Get['cat_id']));
		
				$cat_ary = $this->QunLogic->get_category();		
				if (!empty($cat_ary)) {
			$top_cat_ary = $cat_ary['first'];
			$sub_cat_ary = $cat_ary['second'];
		}
		
				$member = &$this->my;
		if ($member['medal_id']) {
			$medal_list = $this->TopicLogic->GetMedal($member['medal_id'],$member['uid']);
		}
		$joined_nums = $this->QunLogic->joined_nums(MEMBER_ID);
		
				$hot_tag_ary = $this->QunLogic->get_hot_tag_list();
		
		if ($cat_id) {
			
			$sort = intval(trim($this->Get['sort']));
			if (empty($sort)) {
				$sort = 1;
			}
			
						$perpage = 20;
			$gets = array(
				'mod' => 'qun',
				'code' => 'category',
				'cat_id' => $cat_id, 
				'sort' => $this->Get['sort'],
			);
			$page_url = 'index.php?'.url_implode($gets);
			
			$parent_id = 0;
			$sub_cat_id = 0;
			if (isset($top_cat_ary[$cat_id])) {
				$parent_id = $cat_id;
			} else if (isset($sub_cat_ary[$cat_id])) {
				$parent_id = $sub_cat_ary[$cat_id]['parent_id'];
				$sub_cat_id = $cat_id;
			}
			
						if ($parent_id > 0) {
				$tcat_name = $top_cat_ary[$parent_id]['cat_name'];
				$this->Title = $top_cat_ary[$parent_id]['cat_name'].' - 微群列表';
				
								$sub_cat_id_ary = array();
				$cur_sub_cat_ary = array();
				foreach ($sub_cat_ary as $value) {
					if ($value['parent_id'] == $parent_id) {
						$cur_sub_cat_ary[] = $value;
						$sub_cat_id_ary[] = $value['cat_id'];
					}
				}
				
								$where_sql = ' gview_perm=0 ';
				if ($sub_cat_id > 0) {
					$where_sql .= " AND cat_id='{$sub_cat_id}' ";
				} else {
					$sub_cat_id_ary[] = $cat_id;
					$where_sql .= " AND cat_id IN(".jimplode($sub_cat_id_ary).") ";
				}
				
								$order_sql = ' ';
				if ($sort == 2) {
					$order_sql = ' thread_num DESC ';
				} else if ($sort == 3) {
					$order_sql = ' dateline DESC ';
				} else {
					$sort = 1;
					$order_sql = ' member_num DESC ';
				}
				$active_sort[$sort] = 'class="on"';
				
				
				$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('qun')." WHERE {$where_sql}");
				$qun_list = array();
				if ($count) {
					
										$_config = array(
						'return' => 'array',
					);
					$page_arr = page($count, $perpage, $page_url, $_config);
					
					$query = DB::query("SELECT * 
										FROM ".DB::table('qun')." 
										WHERE {$where_sql} 
										ORDER BY {$order_sql} 
										{$page_arr['limit']}");
					while ($value = DB::fetch($query)) {
						$value['icon'] = $this->QunLogic->qun_avatar($value['qid'], 's');
						$value['dateline'] = my_date_format($value['dateline'], 'Y-m-d H:i');
						$qun_list[] = $value;
					}
					
					$this->_get_ql_tag($qun_list);
				}
			} else {
				$this->Messager('当前分类不存在', 'index.php?mod=qun&code=category');
			}
			
			include_once template('qun/category_list');
		} else {
						$nav_cat_ary = array();
			$recdCid = $this->QunLogic->getRecdQunCid();
			if($recdCid){
				$i = 0;
				foreach ($recdCid as $val) {
					$key = $val;
					if(isset($sub_cat_ary[$val])){
						$key = $sub_cat_ary[$val]['parent_id'];
						$nav_cat_ary[$key] = $top_cat_ary[$key];
					}
					if(isset($top_cat_ary[$val])){
						$nav_cat_ary[$key] = $top_cat_ary[$key];
					}
					if($i == 0){
						$first_cat_id = $nav_cat_ary[$key]['cat_id'];
					}
					++$i;
				}
			}
			
						if($this->Config['qun_setting']['tc_qun']){
				Load::lib('form');
				$FormHandler = new FormHandler();
				$query = $this->DatabaseHandler->Query("select id,name from ".TABLE_PREFIX."common_district where `upid` = '0' order by list");
				while ($rsdb = $query->GetRow()){
					$province[$rsdb['id']]['value']  = $rsdb['id'];
					$province[$rsdb['id']]['name']  = $rsdb['name'];
					if($member['province'] == $rsdb['name']){
						$province_id = $rsdb['id'];
					}
				}
				$province_list = $FormHandler->Select("tc_province",$province,$province_id," onchange=\"tcQun($('#tc_province').val());\" style=\"width:150px\" ");
			}
						$activity_qun = $this->QunLogic->GetActivityQun($top_cat_ary,$sub_cat_ary);
			
						$new_qun_list = $this->QunLogic->getNewQun();
			
						extract($this->QunLogic->GetNum());
			
			$this->Title = '微群 - 发现微群';
			include_once template('qun/category_index');
		}
		
	}
	
	
	function members()
	{
		$qid = intval(trim($this->Get['qid']));
		if (empty($qid)) {
			$this->Messager('错误的操作');
		}
		
				$qun_info = $this->QunLogic->get_qun_info($qid);
		if (empty($qun_info)) {
			$this->Messager('当前微群不存在或者已经被删除了');
		}
		
		$type = trim($this->Get['type']);
		$page = intval(trim($this->Get['page']));
		$page = $page == 0 ? 1 : $page;
		$perpage = 60;
		
		$gets = array(
			'mod' => 'qun',
			'code' => 'members',
			'type' => $type,
			'qid' => $qid,
		);
		$page_url = 'index.php?'.url_implode($gets);
		
		if ($type == 'followed') {
						
						$followed_ids = get_buddyids(MEMBER_ID);
			if (empty($followed_ids)) {
				$where_sql = ' 0 ';
			} else {
				$where_sql = " qid='{$qid}' AND uid IN(".jimplode($followed_ids).") ";
			}
		} else {
			if ($page == 1) {
								$founder_info = array();
				$founderuid = DB::result_first("SELECT uid  
												FROM ".DB::table('qun_user')." 
												WHERE qid='{$qid}' AND level=1");
				$founder_info = $this->TopicLogic->GetMember($founderuid, 'uid,username,nickname,face');
				
								$admin_ary = array();
				$admin_nums = 0;
				$admin_ids = array();
				$query = DB::query("SELECT uid FROM ".DB::table('qun_user')." WHERE qid='{$qid}' AND level=2");
				while ($value = DB::fetch($query)) {
					$admin_ids[] = $value['uid'];
				}
				if (!empty($admin_ids)) {
					$admin_ary = $this->TopicLogic->GetMember($admin_ids, 'uid,username,nickname,face');
					$admin_nums = count($admin_ary);
				}
			}
			
			$where_sql = " qid='{$qid}' AND level=4 ";
		}
		
				$members = $member_ids = array();
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('qun_user')." WHERE {$where_sql} ");
		if ($count) {
						$_config = array(
				'return' => 'array',
			);
			$page_arr = page($count, $perpage, $page_url, $_config);
			$query = DB::query("SELECT uid 
								FROM ".DB::table('qun_user')." 
								WHERE {$where_sql} 
								{$page_arr['limit']} ");
			while ($value = DB::fetch($query)) {
				$member_ids[] = $value['uid'];
			}
			
			$members = $this->TopicLogic->GetMember($member_ids, 'uid,username,nickname,face');
		}
		
				$uid = MEMBER_ID;
		$buddyids = get_buddyids($uid);
		
		$this->Title = "微群 - ".$qun_info['name']."的成员";
		include_once template('qun/members');
	}
	
	
	function member_search()
	{
		$q = trim($this->Get['q']);
		$qid = intval(trim($this->Get['qid']));
		
				if (empty($qid)) {
			$this->Messager('错误的操作');
		}
		
				$qun_info = $this->QunLogic->get_qun_info($qid);
		if (empty($qun_info)) {
			$this->Messager('当前微群不存在或者已经被删除了');
		}
		
				if (empty($q)) {
			$this->Messager('搜索关键字不能为空');
		}
		
		$perpage = 60;
		$gets = array(
			'mod' => 'qun',
			'code' => 'member_search',
			'qid' => $qid,
			'q' => $this->Get['q'],
		);
		$page_url = 'index.php?'.url_implode($gets);
		
		$key = jstripslashes($q);
		$s_sql = addcslashes($q, '_%');
		$where_sql = " m.nickname LIKE('%{$s_sql}%') ";
		$count = DB::result_first("SELECT COUNT(*)  
								   FROM ".DB::table('members')." AS m 
								   LEFT JOIN ".DB::table('qun_user')." AS qu 
								   ON m.uid=qu.uid 
								   WHERE qu.qid='{$qid}' AND {$where_sql} ");
		$members = array();
		if ($count) {
						$_config = array(
				'return' => 'array',
			);
			$page_arr = page($count, $perpage, $page_url, $_config);
			$query = DB::query("SELECT m.nickname, m.username, m.uid, m.face 
								FROM ".DB::table('members')." AS m 
								LEFT JOIN ".DB::table('qun_user')." AS qu 
								ON m.uid=qu.uid 
								WHERE qu.qid='{$qid}' AND {$where_sql} 
								{$page_arr['limit']} ");
			while ($value = DB::fetch($query)) {
								$value['face'] = face_get($value['uid']);
				$members[] = $value;
			}
		}
		
		$this->Title = "微群 - ".$qun_info['name']."的成员";
		include_once template('qun/members');
	}
	
	
	function tag()
	{
		$sort = intval(trim($this->Get['sort']));
		$tag_id = intval(trim($this->Get['tag_id']));
		if (empty($tag_id)) {
			$this->Messager("当前标签不存在或者已经被删除了");
		}
		
		$tag_info = $this->QunLogic->get_tag_info($tag_id);
		if (empty($tag_info)) {
			$this->Messager("当前标签不存在或者已经被删除了");
		}
		
				$perpage = 10;
		$gets = array(
			'mod' => 'qun',
			'code' => 'tag',
			'tag_id' => $tag_id,
			'sort' => $this->Get['sort']
		);
		$page_url = 'index.php?'.url_implode($gets);
		
		$where_sql = " tag_id='{$tag_id}' AND gview_perm=0 ";
		
				$order_sql = ' ';
		if ($sort == 2) {
			$order_sql = ' q.thread_num DESC ';
		} else if ($sort == 3) {
			$order_sql = ' q.dateline DESC ';
		} else {
			$sort = 1;
			$order_sql = ' q.member_num DESC ';
		}
		$active_sort[$sort] = 'class="on"';
		
				$count = DB::result_first("SELECT COUNT(*) 
								   FROM ".DB::table('qun')." AS q 
								   LEFT JOIN ".DB::table('qun_tag_fields')." AS qtf 
								   USING(qid) 
								   WHERE {$where_sql} ");
		$qun_list = array();
		if ($count) {
						$_config = array(
				'return' => 'array',
			);
			$page_arr = page($count, $perpage, $page_url, $_config);
			$query = DB::query("SELECT q.*  
								FROM ".DB::table('qun')." AS q 
								LEFT JOIN ".DB::table('qun_tag_fields')." AS qtf 
								USING(qid) 
								WHERE {$where_sql} ");
			while ($value = DB::fetch($query)) {
				$value['icon'] = $this->QunLogic->qun_avatar($value['qid'], 's');
				$value['dateline'] = my_date_format($value['dateline'], 'Y-m-d H:i');
				$qun_list[] = $value;
			}
			
			$this->_get_ql_tag($qun_list);
		}
		
				$recd_qun_list = $this->QunLogic->get_recd_list();
		
				$member = &$this->my;
		$joined_nums = $this->QunLogic->joined_nums(MEMBER_ID);
		$title = "所有使用“{$tag_info['tag_name']}”标签的微群列表";
		$this->Title = $title;
		include_once template('qun/tag_list');
	}
	
		function invite()
	{
		$qid = intval(trim($this->Get['qid']));
		
				if (empty($qid)) {
			$this->Messager('错误的操作');
		}
		
				$qun_info = $this->QunLogic->get_qun_info($qid);	
		if (empty($qun_info)) {
			$this->Messager('当前微群不存在或者已经被删除了');
		}
		
		$config = ConfigHandler::get();
				$invite_url = get_full_url($config['site_url'], "/index.php?mod=qun&qid=".$qid);
		$this->Title = "群邀请 - ".$qun_info['name'];
		include_once template('qun/invite');
	}
	
		function ploy()
	{
		$ploys = $this->QunLogic->ploy_config();
		$this->Title = "微群策略";
		
		$member = &$this->my;
		if ($member['medal_id']) {
			$medal_list = $this->TopicLogic->GetMedal($member['medal_id'],$member['uid']);
		}
		
		include_once template('qun/ploy');
	}
	
	
	function _get_ql_tag(&$qun_list)
	{
				foreach ($qun_list as $key => $value) {
			$qid = $value['qid'];
			$query = DB::query("SELECT * 
								FROM ".DB::table('qun_tag_fields')." 
								WHERE qid='{$qid}'");
			while ($value = DB::fetch($query)) {
				$qun_list[$key]['tags'][] = $value;
			}
		}
	}
	
	function _chk_manage()
	{
		$op = empty($this->Get['op']) ? '' : trim($this->Get['op']);
		$op_ary = array(
			'setting', 
			'icon', 
			'privacy', 
			'members', 
			'del_member', 
			'upgrade2admin',
			'degrade',
			'check_member',
			'announcement', 
			'del_announcement',
			'dismiss',
			'event',
			'vote',
			'attach',
		);
		if (!in_array($op, $op_ary)) {
			$op = 'setting';
		}
		
		$qid = intval(trim($this->Get['qid']));
		$qun_info = $this->QunLogic->get_qun_info($qid);
		if (empty($qun_info)) {
			$this->Messager("当前群不存在或者已经被删除了", 'index.php?mod=qun');
		}
		
				$perm = $this->QunLogic->chk_perm($qid, MEMBER_ID);
		if (!in_array($perm, array(1, 2)) && MEMBER_ROLE_TYPE != 'admin') {
			$this->Messager("你没有权限进行当前操作", 'index.php?mod=qun');
		}
		return array('op' => $op, 'data' => $qun_info, 'perm' => $perm);
	}
	
	
	function _get_parent_topic($topic_list)
	{
		return $this->TopicLogic->GetParentTopic($topic_list, 1);;	
	}
	
	
	function _base_ploy()
	{
		$base_ploy = $this->QunLogic->ploy_config(true);
		$ploys = array();
		$config = $this->Config['qun_setting'];
		if ($config['qun_ploy']['avatar']) {
			$ploys[] = "上传头像 ";
		}
		$ploys[] = "粉丝达到{$base_ploy['fans_num_min']}人  ";
		$ploys[] = "微博超过{$base_ploy['topics_lower']}条 ";
		return $ploys;
	}
}
?>
