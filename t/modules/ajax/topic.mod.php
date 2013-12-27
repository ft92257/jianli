<?php
/**
 * 文件名：topic.mod.php
 * 版本号：1.0
 * 最后修改时间：2009年9月28日 14时10分42秒
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 微博话题AJAX模块
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $TopicLogic;

	var $ID;


	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->initMemberHandler();


		$this->TopicLogic = Load::logic('topic', 1);

		$this->ID = (int) ($this->Post['id'] ? $this->Post['id'] : $this->Get['id']);

		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();

		switch($this->Code)
		{
			case 'favor_fenlei':
				$this->favoriteFenlei();
				break;
			case 'favor_event':
				$this->favoriteEvent();
				break;
			case 'favorite_tag':
				$this->FavoriteTag();
				break;
			case 'group_list':
				$this->GroupList();
				break;
			case 'group_menu':
				$this->Group_Menu();
				break;
			case 'forward_menu':
				$this->Forward_Menu();
				break;
			case 'usermenu':
				$this->UserMenu();
				break;
			case 'tag_menu':
				$this->Tag_Menu();
				break;
			case 'delete_image':
				$this->DeleteImage();
				break;
			case 'delete_video':
				$this->DeleteVideo();
				break;
			case 'delete_music':
				$this->DeleteMusic();
				break;
			case 'follow':
				$this->Follow();
				break;
			case 'followAdd':
				$this->followAdd();
				break;
			case 'favor':
				$this->Favorite();
				break;
			case 'favor_tag':
				$this->FavoriteTag();
				break;
			case 'upload':
				$this->Upload();
				break;
			case 'dovideo':
				$this->DoVideo();
				break;
			case 'do_add':
				$this->DoAdd();
				break;
			case 'delete':
				$this->Delete();
				break;
			case 'delverify':
				$this->delVerify();
				break;
			case 'list_reply':
				$this->ListReply();
				break;
			case 'forward':
				$this->Do_forward();
				break;
			case 'view_comment':
				$this->ViewComment();
				break;
			case 'view_bbs':
				$this->ViewBbs();
				break;
			case 'view_cms':
				$this->ViewCms();
				break;
			case 'create_group':
				$this->Create_Group();
				break;
			case 'do_group':
				$this->Do_Group();
				break;
			case 'group_fields':
				$this->Group_fields();
				break;
			case 'del_group':
				$this->Del_Group();
				break;
			case 'do_fansgroup':
				$this->Do_FansGroup();
				break;

			case 'del_fansgroup':
				$this->Del_FansGroup();
				break;
			case 'set_fansgroup':
				$this->Set_FansGroup();
				break;
			case 'do_setfansgroup':
				$this->Do_SetFansGroup();
				break;
			case 'create_fansgroup':
				$this->Create_FansGroup();
				break;
			case 'fansgrouplist':
				$this->FansGroupList();
				break;
			case 'doreport':
				$this->DoReport();
				break;
			case 'remark':
				$this->Remark();
				break;
			case 'add_remark':
				$this->Add_Remark();
				break;
			case 'add_user_follow':
				$this->Add_User_Follow();
				break;
			case 'follower_choose':
				$this->Follower_choose();
				break;
			case 'doblacklist':
				$this->DoAddMyBlackList();
				break;
			case 'do_delmyblacklist':
				$this->DoDelMyBlackList();
				break;
			case 'modifytopic':
				$this->ModifyTopic();
				break;
			case 'do_modifytopic':
				$this->Do_ModifyTopic();
				break;
			case 'uploadface':
				$this->UploadFace();
				break;
			case 'topicshow':
				$this->TopicShow();
				break;
			case 'user_tag':
				$this->User_Tag();
				break;
			case 'del_tag':
				$this->Del_Tag();
				break;
				break;
			case 'pmfriends':
				$this->PmFriends();
				break;
			case 'open_mdeal':
				$this->Open_Mdeal_Index();
				break;

			case 'do_delmyfans':
				$this->DoDelMyFans();
				break;

							case 'qmd':
				$this->Qmd();
				break;
			case 'insert_qmd':
				$this->Insert_Qmd();
				break;

							case 'recd':
				$this->recd();
				break;
			case 'do_recd':
				$this->do_recd();
				break;
							case 'editarea':
				$this->editErea();
				break;

							case 'publishsuccess':
				$this->publishSuccess();
				break;

			case 'list':
			case 'tag':
			case 'myhome':
			case 'mycomment':
			case 'mylastpublish':
			case 'updatecurrent':
			case 'myat':
			case 'myblog':
			case 'tocomment':
			case 'myfavorite':
			case 'favoritemy':
			case 'groupview':
				$this->DoList();
				break;
							case 'reg_follow_user':
				$this->Do_Reg_Follow_User();
				break;
							case 'showlogin':
				$this->ShowLogin();
				break;
							case 'add_favor_tag':
				$this->addFavoriteTag();
				break;
							case 'modify_user_signature':
				$this->Modify_User_Signature();
				break;

							case 'check_medal_list':
				$this->Check_Medal_List();
				break;
							case 'photo':
				$this->Photo();
				break;
							case 'ajax':
				$this->Ajax();
				break;
							case 'new':
			case 'tc':
			case 'hotforward':
			case 'channel':
				$this->Pic_ajax();
				break;
			default:
				$this->Main();
				break;
		}

		response_text(ob_get_clean());
	}

	function Main()
	{
		response_text("正在建设中……");
	}

	
	function Ajax()
	{
		$TopicListLogic = Load::logic('topic_list', 1);
		$tids = unserialize(base64_decode($this->Post['key']));
		if($this->Post['order']=='asc'){$order = 'dateline ASC';}else{$order = '';}
		if(!is_array($tids)){$tids=array($tids);}
		$options = array('tid'=>$tids,'count'=>'20','order'=>$order);
		$info = $TopicListLogic->get_data($options);
		$topic_list = $info['list'];
		if($topic_list){
			if(!empty($order)){$topic_view = 1;}
			$parent_list = $this->TopicLogic->GetParentTopic($topic_list,1);
			include($this->TemplateHandler->Template('topic_list_js_ajax'));
		}
	}

		function Pic_ajax()
	{
		$options = array();
		$TopicListLogic = Load::logic('topic_list', 1);
		$per_page_num = $this->Post['pp_num'] ? (int)$this->Post['pp_num'] : 20;
		$cache_time = $this->Post['c_time'] ? (int)$this->Post['c_time'] : 10;
		$uid = $this->Post['uid'] ? $this->Post['uid'] : '';
		if($this->Code =='channel'){
			$id = $this->Post['id'] ? $this->Post['id'] : '';
			$options = array(
				'item'=>'channel',
				'item_id' => unserialize(base64_decode($id)),
				'perpage' => $per_page_num,
			);
			$info = $TopicListLogic->get_data($options);
		}elseif ($this->Code =='new'){
			$options = array(
				'cache_time' => $cache_time,
				'cache_key' => 'topic-newtopic',
				'perpage' => $per_page_num,
				'order' => ' dateline DESC ',
				'uid' => unserialize(base64_decode($uid)),
			);
			$info = $TopicListLogic->get_data($options);
		}elseif('hotforward' == $this->Code){
			$d = in_array($this->Post['d'],array(1,7,14,30)) ? (int)$this->Post['d'] : 7;
			$uid_sql = $this->Post['uid_sql'] ? $this->Post['uid_sql'] : '';
			$time = $d * 86400;
			$dateline = TIMESTAMP - $time;
			$options = array(
				'cache_time' => $cache_time,
				'cache_key' => "topic-hotforward-{$d}",
				'perpage' => $per_page_num,
				'type' => 'first',
				'where' => " $uid_sql `forwards`>'0' AND `dateline`>='$dateline' ",
				'order' => " `forwards` DESC , `dateline` DESC ",
			);
			$info = $TopicListLogic->get_data($options);
		}elseif('tc' == $this->Code){
			$province = $this->Post['province'] ? $this->Post['province'] : '';
			$city = $this->Post['city'] ? $this->Post['city'] : '';
			$area = $this->Post['area'] ? $this->Post['area'] : '';
			$vip = $this->Post['vip'] ? $this->Post['vip'] : '';
			$options = array(
				'cache_time' => $cache_time,
				'cache_key' => "topic-tctopic-{$province}-{$city}-{$area}",
				'perpage' => $per_page_num,
				'province' => $province,
				'city' => $city,
				'area' => $area,
				'vip' => $vip,
			);
			$info = $TopicListLogic->get_tc_data($options);
		}
		$topic_list = array();
		if (!empty($info)) {
			$topic_list = $info['list'];
			foreach ($topic_list as $key => $row) {				if($row['top_parent_id'] || $row['parent_id']) {
					unset($topic_list[$key]);
				}
			}
		}
		if($topic_list){
			include($this->TemplateHandler->Template('topic_new_pic_ajax'));
		}
	}

		function editErea(){
		$province = (int) $this->Get['province'];
		if($province){
			$province_name = $this->DatabaseHandler->ResultFirst("select name from ".TABLE_PREFIX."common_district where id = '$province'");
		}

		$city = (int) $this->Get['city'];
		if($city){
			$city_name = $this->DatabaseHandler->ResultFirst("select name from ".TABLE_PREFIX."common_district where id = '$city'");
		}

		$area = (int) $this->Get['area'];
		if($area){
			$area_name = $this->DatabaseHandler->ResultFirst("select name from ".TABLE_PREFIX."common_district where id = '$area'");
		}

		$street = (int) $this->Get['street'];
		if($street){
			$street_name = $this->DatabaseHandler->ResultFirst("select name from ".TABLE_PREFIX."common_district where id = '$street'");
		}

		$this->DatabaseHandler->Query("update ".TABLE_PREFIX."members set province = '$province_name' , city = '$city_name' , area = '$area_name' , street = '$street_name' where uid = ".MEMBER_ID);
		echo $province_name." ".$city_name;
	}

	
	function DoList()
	{
		$options = array();
		if(($per_page_num = (int) ConfigHandler::get('show','topic',$this->Code)) < 1) {
			$per_page_num = 20;
		}

		$uid = (int) (get_param('uid'));
		$is_personal = (int) (get_param('is_personal'));
		$tag_id = (int) (get_param('tag_id'));

		$topic_parent_disable = false;

		$start = max(0, (int) $start);
		$limit = "limit {$start},{$per_page_num}";
		$next = $start + $per_page_num;

		if ($tag_id > 0) {
			$sql = "select `item_id` from `".TABLE_PREFIX."topic_tag` where `tag_id`='{$tag_id}' order by `item_id` desc {$limit}";
			$query = $this->DatabaseHandler->Query($sql);
			$topic_ids[0] = 0;
			while (false != ($row = $query->GetRow())) {
				$topic_ids[$row['item_id']] = $row['item_id'];
			}
			$options['tid'] = $topic_ids;
		}

		$options['perpage'] = $per_page_num;
				$tpl = 'topic_list_ajax';
		if ('myhome' == $this->Code) {
			$uid = MEMBER_ID;
			$cache_time = 600;
			$cache_key = "{$uid}-topic-myhome--0";
			
			$topic_myhome_time_limit = 0;
			if($this->Config['topic_myhome_time_limit'] > 0) {
				$topic_myhome_time_limit = (time() - ($this->Config['topic_myhome_time_limit'] * 86400));

				if($topic_myhome_time_limit > 0) {
					$options['dateline'] = $topic_myhome_time_limit;
				}
			}

			$topic_uids[$uid] = $uid;
			if($is_personal) {
				if(false === (cache_db('get', $cache_key))) {
					$buddyids = get_buddyids(MEMBER_ID, $this->Config['topic_myhome_time_limit']);
					if($buddyids) {
						$topic_uids = array_merge($topic_uids, $buddyids);
					}
				}
			}

			$options['uid'] = $topic_uids;
		} else if('myat' == $this->Code) {
			$uid = MEMBER_ID;
			$sql = "select * from `".TABLE_PREFIX."topic_mention` where `uid`='".MEMBER_ID."'";
			$query = $this->DatabaseHandler->Query($sql);
			$topic_ids = array();
			while (false != ($row = $query->GetRow()))
			{
				$topic_ids[$row['tid']] = $row['tid'];
			}
			$options['tid'] = $topic_ids;
		} else if ('groupview' == $this->Code) {

			$gid = (int) ($this->Post['gid'] ? $this->Post['gid'] : $this->Get['gid']);

						$sql = "select * from `".TABLE_PREFIX."groupfields` where `gid`='{$gid}' and uid = ".MEMBER_ID." ";
			$query = $this->DatabaseHandler->Query($sql);
			$g_view_uids = array();
			$list = array();
			while (false != ($row = $query->GetRow())) {
				$g_view_uids[$row['touid']] = $row['touid'];
				$groupname = $row['g_name'];
				$groupid = $row['gid'];
			}

						if ($g_view_uids) {
				$options['uid'] = $g_view_uids;
			} else {
				exit();
			}
		} else if ('mylastpublish' == $this->Code) {

			$topic_list = $this->TopicLogic->Get(" where `uid`='".MEMBER_ID."' order by `tid` desc limit 0,1 ");

						$no_from = false;
									$ref_mod = $this->Post['ref_mod'];
			$talk_r = $this->Post['r'];
			if($talk_r == 'answer' || $talk_r == 'talk'){
				$tpl = 'talk_item_ajax';
			}
			$ref_code = $this->Post['ref_code'];
			$no_from = $this->_no_from($ref_mod, $ref_code);
			$topic_list_get = true;
			if($ref_mod == 'live' || $ref_mod == 'talk'){
				foreach($topic_list as $key => $val){
					$item = $topic_list[$key]['item'];
					$itemid = $topic_list[$key]['item_id'];
					$uid = $topic_list[$key]['uid'];
					$user_type = DB::result_first("SELECT type FROM ".DB::table('item_user')." WHERE item = '$item' AND itemid='{$itemid}' AND uid = '$uid'");
					$topic_list[$key]['user_css'] = $item.$user_type;
					if($ref_mod == 'talk' && $user_type == 'guest'){
						$topic_list[$key]['user_str'] = '本期嘉宾';
					}else{
						$topic_list[$key]['user_str'] = '&nbsp;';
					}
				}
				$no_mBlog_linedot2 = false;
			}else{
				$no_mBlog_linedot2 = true;
			}
		} else if ('updatecurrent' == $this->Code) {
						$tid = intval($this->Post['tid']);
			if (empty($tid)) {
				exit;
			}

			$refcode = trim($this->Post['refcode']);
			$refmod = trim($this->Post['refmod']);
			$tpl = 'topic_item_ajax';
			if ('topic' == $refmod && 'myfavorite' == $refcode) {
				$sql = "SELECT TF.dateline as favorite_time , T.*
						FROM ".DB::table("topic_favorite")." AS TF
						LEFT JOIN ".DB::table("topic")." AS T
						ON T.tid=TF.tid where T.tid='{$tid}'";
				$this->Code = $refcode;
				$topic_parent_disable = true;
			} else {
				$sql = "SELECT * FROM ".DB::table('topic')." WHERE tid='{$tid}'";
			}

			$data = DB::fetch_first($sql);
			if (empty($data)) {
				exit;
			}
			if (isset($data['favorite_time'])) {
				$data['favorite_time'] = my_date_format2($data['favorite_time']);
			}
			$val = $this->TopicLogic->Make($data);
			$topic_list[] = $val;

			$no_from = false;
						if ('vote' == $refmod && 'view' == $refcode) {
				$no_from = true;
			}

						if ($refmod == 'qun') {
				$this->Module = 'qun';
			}

						if ($refcode == 'reply_list_ajax') {
				$tpl = 'topic_comment_item';
				$topic_parent_disable = true;
				$v = $val;
			}

			$topic_list_get = true;
		} else if ('mycomment' == $this->Code) {

			$options['where'] = " `touid`='".MEMBER_ID."' ";

		} elseif ('tocomment' == $this->Code) {
			$title = '我评论的';
			$topic_selected = 'tocomment';
			$options['where'] = " `uid` = '".MEMBER_ID."' and `type` in ('both','reply') ";

		} elseif ('myblog' == $this->Code) {
			$options['uid'] = $uid;

		} else if ('myfavorite' == $this->Code) {
			$uid = MEMBER_ID;

			$sql = "select TF.dateline as favorite_time , T.* from `".TABLE_PREFIX."topic_favorite` TF left join `".TABLE_PREFIX."topic` T on T.tid=TF.tid where TF.uid='{$uid}' order by TF.id desc {$limit}";
			$query = $this->DatabaseHandler->Query($sql);
			while (false != ($row = $query->GetRow()))
			{
				if($row['tid']<1) continue;

				$row['favorite_time'] = my_date_format2($row['favorite_time']);

				$row = $this->TopicLogic->Make($row);

				$topic_list[$row['tid']] = $row;
			}
			$topic_list_get = true;

		} else if ('favoritemy' == $this->Code) {
			$uid = MEMBER_ID;

			$sql = "select TF.dateline as favorite_time , TF.tuid , T.* from `".TABLE_PREFIX."topic_favorite` TF left join `".TABLE_PREFIX."topic` T on T.tid=TF.tid where TF.tuid='{$uid}' order by TF.id desc {$limit}";
			$query = $this->DatabaseHandler->Query($sql);
			$tuids = array();
			while (false != ($row = $query->GetRow()))
			{
				if($row['tid']<1) continue;

				$row['favorite_time'] = my_date_format2($row['favorite_time']);
				$row = $this->TopicLogic->Make($row);
				$topic_list[$row['tid']] = $row;
				$tuids[$row['tuid']] = $row['tuid'];
			}
			$topic_members = $this->TopicLogic->GetMember($tuids,"`uid`,`ucuid`,`username`,`nickname`,`face_url`,`face`,`validate`");

			$topic_parent_disable = true;
			$topic_list_get = true;

		}

		#if NEDU
		defined('NEDU_MOYO') && nlogic('feeds.app.jsg')->on_ajax_topic_request($options);
		#endif

		if (!$topic_list_get) {
						if($cache_time > 0 && $cache_key && !$options['tid']) { 				$options = Load::logic('topic_list', 1)->get_options($options, $cache_time, $cache_key);
			}
							
			$info = Load::logic('topic_list', 1)->get_data($options);
			$topic_list = array();
			$total_record = 0;
			if (!empty($info)) {
				$topic_list = $info['list'];
				$total_record = $info['count'];
				$page_arr = $info['page'];
			}
		}

		$topic_list_count = 0;
		if($topic_list) {
			$topic_list_count = count($topic_list);

			if(!$topic_parent_disable) {
								$parent_list = $this->TopicLogic->GetParentTopic($topic_list);
							}
		}
		if($tpl == 'talk_item_ajax'){
			$answer_list = array();
			if($parent_list){
				$answer_list = $topic_list;
				$topic_list = $parent_list;
			}
			foreach($topic_list as $key => $val){
				if(empty($topic_list[$key]['touid'])){
					$topic_list[$key]['biank_css'] = 'talk_view_ping';
					$topic_list[$key]['tubiao_css'] = 'talk_view_pin';
				}else{
					$topic_list[$key]['biank_css'] = 'talk_view_wenda';
					$topic_list[$key]['tubiao_css'] = 'talk_view_wen';
					$topic_list[$key]['ask_list'] = $answer_list;
					foreach($topic_list[$key]['ask_list'] as $k => $v){
						$topic_list[$key]['ask_list'][$k]['tubiao_css'] = 'talk_view_da';
						$topic_list[$key]['ask_list'][$k]['user_css'] = 'talkguest';
					}
				}
			}
		}

		#if NEDU
		defined('NEDU_MOYO') && nlogic('feeds.app.jsg')->on_ajax_topic_response($topic_list, $page_arr);
		#endif
		include($this->TemplateHandler->Template($tpl));
	}

		function _no_from($ref_mod, $ref_code = '')
	{
		$no_from = true;
		if ($ref_mod == 'topic' || $ref_mod == 'qun' || $ref_mod == 'live' || $ref_mod == 'talk') {
			$no_from = false;
		}
		return $no_from;
	}

	function DoAdd()
	{
		if (MEMBER_ID < 1) {
			response_text("请先登录或者注册一个帐号");
		}

		$content = trim(strip_tags($this->Post['content']));

		if (!$content) {
			response_text("请输入内容");
		}

				$topic_type = $this->Post['topictype'];

		
		if('both' == $topic_type){
			$type = 'both';
		} elseif('reply' == $topic_type){
			$type = 'reply';
		} elseif('qun' == $topic_type){
			$type = 'qun';
		} elseif('live' == $topic_type){
			$type = 'live';
		} elseif('talk' == $topic_type){
			$type = 'talk';
		} elseif ('personal' == $topic_type) {
			$type = 'personal';
		} elseif(in_array($topic_type,array('answer','event','vote','fenlei','reward'))){
			$type = 'reply';
		} elseif (is_numeric($topic_type)) {
			$type = 'first';
		} else{
			$type = 'first';
		}
		#if NEDU
		defined('NEDU_MOYO') && nlogic('feeds.app.jsg')->topic_detect_type($type, $topic_type);
		#endif
		
				if(!in_array($type, array('both', 'reply', 'forward'))) { 			if(!($this->MemberHandler->HasPermission('topic','add'))) {
				response_text("您的角色没有发布的权限");
			}
		} else {
			if(('reply'==$type || 'both'==$type) && !($this->MemberHandler->HasPermission('topic','reply'))) {
				response_text("您的角色没有评论的权限");
			} elseif(('forward'==$type || 'both'==$type) && !($this->MemberHandler->HasPermission('topic','forward'))) {
				response_text("您的角色没有转发的权限");
			}
		}

		$roottid = max(0, (int) $this->Post['roottid']);
		$totid = max(0, (int) $this->Post['totid']);
		$touid = max(0, (int) $this->Post['touid']);
		$imageid = trim($this->Post['imageid']);
		$attachid = trim($this->Post['attachid']);

		$videoid = max(0, (int) $this->Post['videoid']);

		$longtextid = max(0, (int) $this->Post['longtextid']);
		$design = trim($this->Post['r']);
		$xiami_id = trim($this->Post['xiami_id']) ? trim($this->Post['xiami_id']) : 0;
				$from = trim($this->Post['from']);


		

		$item = trim($this->Post['item']);
		$item_id  = intval(trim($this->Post['item_id']));
		if (!empty($item_id)) {
						Load::functions('app');
			$ret = app_check($item, $item_id);
			if (!$ret) {
				$item = '';
				$item_id = 0;
			}
		} else {
			$item = '';
			$item_id = 0;
		}
		$data = array(
			'content' => $content,
			'totid'=>$totid,
			'imageid'=>$imageid,
			'attachid'=>$attachid,
			'videoid'=>$videoid,
			'from'=>empty($from) ? 'web' : $from,
			'type'=>$type,
					'design'=>$design,

					'item' => $item,
			'item_id' => $item_id,
			'touid' => $touid,
					'longtextid' => $longtextid,
					'xiami_id' => $xiami_id,
		);

		$return = $this->TopicLogic->Add($data);

		if (is_array($return) && $return['tid'] > 0) {

			$r = $this->Post['r'];

			$is_huifu = $this->Post['is_huifu'];

			$return_reply = $this->Post['return_reply'];

			if($totid > 0 && $r) {
				if('vc' == $r) {
					if($is_huifu == 'is_huifu') {
						$return_reply = 'is_huifu';
					}
					$this->ViewComment($return['totid'], $return['tid'], $return_reply, $roottid);
				} elseif ('rl' == substr($r,0,2)) {
					$_GET['page'] = 999999999;
					$this->ListReply(((is_numeric(($tti=substr($r,3))) && $tti > 0) ? $tti : $return['totid']),$return['tid']);
				} elseif (in_array($r,array('tohome','lt','myblog','myhome','tagview','view'))) {
					exit;
				}
			}
		} else {
			if(is_string($return)){
				$return  = '[发布失败]'.$return;
			}elseif(is_array($return)){
				$return = '[发布成功]'.implode(",",$return);
			}else{
				$return = '未知错误';
			}

			response_text("{$return}");
		}
	}

	function Delete()
	{
		$tid = (int) ($this->Post['tid'] ? $this->Post['tid'] : $this->Get['tid']);

		if ($tid < 1) {
			js_alert_output("请指定一个您要删除的话题");
		}
		$topic = $this->TopicLogic->Get($tid);
		if (!$topic) {
			js_alert_output("话题已经不存在了");
		}
		if ($topic['uid']!=MEMBER_ID && 'admin'!=MEMBER_ROLE_TYPE) {
			js_alert_output("您无权删除该话题");
		}

		$return = $this->TopicLogic->DeleteToBox($tid);

		response_text($return . $this->js_show_msg());
	}

		function delVerify(){
		$tid = (int) ($this->Post['tid'] ? $this->Post['tid'] : $this->Get['tid']);

		if ($tid < 1) {
			js_alert_output("请指定一个您要删除的话题");
		}
		$query = $this->DatabaseHandler->Query("select *  from ".TABLE_PREFIX."topic_verify where id = '{$tid}'");
		$topic = $query->GetRow();
		if (!$topic) {
			js_alert_output("话题已经不存在了");
		}
		if ($topic['uid']!=MEMBER_ID && 'admin'!=MEMBER_ROLE_TYPE) {
			js_alert_output("您无权删除该话题");
		}

				if ($topic['imageid']){
			Load::logic('image', 1)->delete($topic['imageid']);
		}

		if ($topic['videoid']) {
						$sql = "select `id`,`video_img` from `" . TABLE_PREFIX .
					"topic_video` where `id`='" . $topic['videoid'] . "' ";
			$query = $this->DatabaseHandler->Query($sql);
			$topic_video = $query->GetRow();


			Load::lib('io', 1)->DeleteFile($topic_video['video_img']);
		}
				if($topic['longtextid']){
			$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."topic_longtext where id = '{$topic[longtextid]}'");
		}
				$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."topic_verify where id = '$tid'");

		$return = ($this->Post['return'] ? $this->Post['return'] : $this->Get['return']);
		response_text($return . $this->js_show_msg());
	}

	function ViewComment($tid=0, $highlight=0, $return_reply='', $roottid=0)
	{
		$limit = max(0, (int) ConfigHandler::get('show', 'topic_one_comment', 'list'));
		if($limit < 1) {
			$limit = 6;
		}
		
		$highlight = ($highlight ? $highlight : get_param('highlight'));
		$_GET['highlight'] = $highlight;

		$tid = max(0,(float) ($tid ? $tid : get_param('tid')));

		if($tid > 0) {
			$topic_info = $this->TopicLogic->Get($tid);

			$reply_list = array();
			if($topic_info) {
								$tids = array();
				if($return_reply == 'is_huifu') {
					$roottid = (int) ($roottid ? $roottid : $topic_info['roottid']);
					if($roottid > 0) {
						$topic_info = $this->TopicLogic->Get($roottid);
					}
				}
				if ($topic_info['replys'] > 0) {
					$tids = $this->TopicLogic->GetReplyIds($topic_info['tid']);
				}

				$tids_count = count($tids);
				if($tids && $tids_count) {
					rsort($tids);

					$condition = "where `tid` in('".implode("','", array_slice((array) $tids, 0, min($limit, $tids_count)))."')  order by `tid` desc limit {$limit}";
					$reply_list = $this->TopicLogic->Get($condition);

					$r_parent_list = $this->TopicLogic->GetParentTopic($reply_list, 1);
				}
			}
		}

		include($this->TemplateHandler->Template('topic_view_comment_ajax'));
	}

	function ViewBbs($tid=0)
	{
		$m_tl = '回复';
		$limit = max(0, (int) ConfigHandler::get('show', 'topic_one_comment', 'list'));
		if($limit < 1)
		{
			$limit = 6;
		}
		$tid = max(0,(float) ($tid ? $tid : get_param('tid')));
		$info = array();
		if($tid > 0)
		{
						if($this->Config['dzbbs_enable']){
				if(@file_exists(ROOT_PATH . 'setting/dzbbs.php')){
					Load::logic("topic_bbs");
					$TopicBbsLogic = new TopicBbsLogic();
					$info = $TopicBbsLogic->get_reply($tid);
				}
			}elseif($this->Config['phpwind_enable']){
				if(@file_exists(ROOT_PATH . 'setting/phpwind.php')){
					$config = array();
					include ROOT_PATH . 'setting/phpwind.php';
					if($config['phpwind']['bbs']){
						Load::logic("topic_bbs");
						$TopicBbsLogic = new TopicBbsLogic();
						$info = $TopicBbsLogic->get_reply($tid);
					}
				}
			}
		}
		if (!empty($info)) {
			$replys = $info['count'];
			$reply_list = $info['list'];
			$replyurl = $info['url'];
		}
		include($this->TemplateHandler->Template('topic_view_cmsbbs_ajax'));
	}

	function ViewCms($tid=0)
	{
		$m_tl = '评论';
		$limit = max(0, (int) ConfigHandler::get('show', 'topic_one_comment', 'list'));
		if($limit < 1)
		{
			$limit = 6;
		}
		$tid = max(0,(float) ($tid ? $tid : get_param('tid')));
		$info = array();
		if($tid > 0)
		{
						if($this->Config['dedecms_enable']){
				if(@file_exists(ROOT_PATH . 'setting/dedecms.php')){
					Load::logic("topic_cms");
					$TopicCmsLogic = new TopicCmsLogic();
					$info = $TopicCmsLogic->get_reply($tid);
				}
			}
		}
		if (!empty($info)) {
			$replys = $info['count'];
			$reply_list = $info['list'];
			$replyurl = $info['url'];
		}
		include($this->TemplateHandler->Template('topic_view_cmsbbs_ajax'));
	}

		function ModifyTopic()
	{
		$tid = $modify_tid = max(0, (int) $this->Post['tid']);
		if($tid < 1)
		{
			js_alert_output('微博ID 错误');
		}
		$types = ($this->Post['types'] ? $this->Post['types'] : $this->Get['types']);
		$handle_key = ($this->Post['handle_key'] ? $this->Post['handle_key'] : $this->Get['handle_key']);
		$allow_attach = ($this->Post['attach'] ? $this->Post['attach'] : $this->Get['attach']);


				$types = $this->Post['types'];

		$topiclist = $this->TopicLogic->Get($modify_tid);

		if(!$topiclist)
		{
			response_text('您要编辑的微博已经不存在了');
		}


				if(MEMBER_ROLE_TYPE != 'admin')
		{
			if(MEMBER_ID != $topiclist['uid'])
			{
				response_text("您没有权限编辑该微博");
			}

			if($topiclist['replys'] >= 1 || $topiclist['forwards'] >= 1 )
			{
				response_text("微博已被评论或者转发,不能编辑");
			}

						if($this->Config['topic_modify_time'] && (($topiclist['addtime'] ? $topiclist['addtime'] : $topiclist['dateline']) + ($this->Config['topic_modify_time'] * 60) < time()))
			{
				response_text("微博已超出可编辑时间了");
			}
		}



				if($topiclist['longtextid'] > 0) {
			$topiclist['content'] = DB::result_first("select `longtext` from ".DB::table('topic_longtext')." where `id`='{$topiclist['longtextid']}'");
		} else {
			$row = $this->DatabaseHandler->FetchFirst("select * from ".TABLE_PREFIX."topic where `tid`='$tid'");
			$topiclist['content'] = ($row['content'] . $row['content2']);
		}

				$topiclist['content'] = strip_tags($topiclist['content']);
				if('both'==$topiclist['type'] || 'forward'==$topiclist['type'])
		{
			$topiclist['content'] = $this->TopicLogic->GetForwardContent($topiclist['content']);
		}
		$this->item = $topiclist['item'];
		$this->item_id = $topiclist['item_id'];

		include($this->TemplateHandler->Template('modify_topic_ajax'));

	}

		function Do_ModifyTopic()
	{
		if(MEMBER_ID < 1)
		{
			js_alert_output("请先登录或者注册一个帐号");
		}

		$tid = max(0, (int) $this->Post['tid']);

		if($tid < 1)
		{
			js_alert_output("微博ID不能为空");
		}


		$topiclist = $this->DatabaseHandler->FetchFirst("select * from `".TABLE_PREFIX."topic` where `tid`='{$tid}'");

		if(!$topiclist)
		{
			js_alert_output("您要编辑的内容已经不存在了");
		}


				if(MEMBER_ROLE_TYPE != 'admin')
		{
			if(MEMBER_ID != $topiclist['uid'])
			{
				js_alert_output("您没有权限编辑该微博");
			}

			if($topiclist['replys'] >= 1 || $topiclist['forwards'] >= 1 )
			{
				js_alert_output("微博已被评论或者转发,不能编辑");
			}

						if($this->Config['topic_modify_time'] && (($topiclist['addtime'] ? $topiclist['addtime'] : $topiclist['dateline']) + ($this->Config['topic_modify_time'] * 60) < time()))
			{
				js_alert_output("微博已超出可编辑时间了");
			}
		}


		$content = strip_tags($this->Post['content']);

				
				if(empty($content))
		{
			js_alert_output("微博内容不能为空");
		}


				if('both'==$topiclist['type'] || 'forward'==$topiclist['type'])
		{
			$content = $this->TopicLogic->GetForwardContent($content);
		}


		$imageid = $this->Post['imageid'];
		$attachid = $this->Post['attachid'];

		$return = $this->TopicLogic->Modify($tid,$content,$imageid,$attachid);


		if(is_array($return))
		{
			$topic_list = $this->TopicLogic->Get($tid);
		}
		else
		{
			js_alert_output("【编辑失败】{$return}");
		}
	}


	function ListReply($tid=0,$highlight=0)
	{
		$per_page_num = 10;
		$tid = max(0,(float) ($tid? $tid : $this->Post['tid']));

		if ($tid < 1)
		{
			response_text("[链接参数错误]不存在的地址");
		}
		$highlight = ($highlight ? $highlight : get_param('highlight'));
		$_GET['highlight'] = $highlight;

		$topic_info = $this->TopicLogic->Get($tid);

				if ($topic_info['type'] == 'reply') {
			$roottid = $topic_info['roottid'];
			$root_type = DB::result_first("SELECT type FROM ".DB::table('topic')." WHERE tid='{$roottid}'");
		} else {
			$root_type = $topic_info['type'];
		}

		if (!$topic_info)
		{
			response_text("您要查看的话题已经不存在了");
		}

		$reply_list = array();
		if ($topic_info['replys'] > 0)
		{
			$total_record = $topic_info['replys'];
			$_config = array
			(
				'return' => 'array',
				'extra' => 'onclick="replyList(this.title);return false;"',
				'var' => 'p',
			);
			$page_arr = page($total_record,$per_page_num,"index.php?mod=topic&code={$topic_info['tid']}",$_config);

			$tids = array();
			$tids = $this->TopicLogic->GetReplyIds($topic_info['tid']);
			if($tids)
			{
				krsort($tids);
				$condition = "where `tid` in ('".implode("','",array_slice((array) $tids,$page_arr['offset'],$per_page_num))."') order by `dateline` asc limit {$per_page_num}";

				$reply_list = $this->TopicLogic->Get($condition);

								$r_parent_list = $this->TopicLogic->GetParentTopic($reply_list, 1);
			}

		}

		include($this->TemplateHandler->Template('topic_reply_list_ajax'));
	}

	function Upload()
	{

		$error_msg = '';
		if (MEMBER_ID < 1) {
			$error_msg = "请先登录或者注册一个帐号";
		} else {
			$field = 'topic';
			if (empty($_FILES) || !$_FILES[$field]['name']) {
				$error_msg = "请设置图片";
			} else {
				$timestamp = time();

				$uid = $this->Post['touid'] ? $this->Post['touid'] : MEMBER_ID;
				$username = $this->Post['tousername'] ? $this->Post['tousername'] : MEMBER_NAME;
				$sql = "insert into `".TABLE_PREFIX."topic_image`(`uid`,`username`,`dateline`) values ('{$uid}','{$username}','{$timestamp}')";
				$query = $this->DatabaseHandler->Query($sql);
				$image_id = $this->DatabaseHandler->Insert_ID();

				if ($image_id < 1)
				{
					js_alert_output('图片上传失败');
				}




				$image_path = RELATIVE_ROOT_PATH . 'images/' . $field . '/' . face_path($image_id);
				$image_name = $image_id . "_o.jpg";
				$image_file = $image_path . $image_name;
				$image_file_small = $image_path.$image_id . "_s.jpg";
				$image_file_photo = $image_path.$image_id . "_p.jpg";
				if (!is_dir($image_path))
				{
					Load::lib('io', 1)->MakeDir($image_path);
				}

				Load::lib('upload');
				$UploadHandler = new UploadHandler($_FILES,$image_path,$field,true);
				$UploadHandler->setMaxSize(2048);
				$UploadHandler->setNewName($image_name);
				$result=$UploadHandler->doUpload();

				if($result)
				{
					$result = is_image($image_file);
				}

				if(false == $result) {
					Load::lib('io', 1)->DeleteFile($image_file);
					$sql = "delete from `".TABLE_PREFIX."topic_image` where `id`='{$image_id}'";
					$this->DatabaseHandler->Query($sql);

					$error_msg = implode(" ",(array) $UploadHandler->getError());
				} else {
					
					$this->_removeTopicImage($image_id);

					list($image_width,$image_height,$image_type,$image_attr) = getimagesize($image_file);

										if($image_width > 200)
					{
						$p_width = 200;
						$p_height = round(($image_height*200)/$image_width);
						$result = makethumb($image_file, $image_file_photo, $p_width, $p_height);
					}
					if($image_width <= 200 || (!$result && !is_file($image_file_photo)))
					{
						@copy($image_file,$image_file_photo);
					}

					$result = makethumb(
					$image_file,
					$image_file_small,
					min($this->Config['thumbwidth'],$image_width),
					min($this->Config['thumbwidth'],$image_height),
					$this->Config['maxthumbwidth'],
					$this->Config['maxthumbheight']
					);
					if (!$result && !is_file($image_file_small))
					{
						@copy($image_file,$image_file_small);
					}

										if($this->Config['watermark_enable']) {
						Load::logic('image', 1)->watermark($image_file);
					}

					$image_size = filesize($image_file);
					$name = addslashes($_FILES[$field]['name']);


										$site_url = '';
					if($this->Config['ftp_on'])
					{
						$site_url = ConfigHandler::get('ftp','attachurl');

						$ftp_result = ftpcmd('upload',$image_file);
						if($ftp_result > 0)
						{
							ftpcmd('upload',$image_file_small);

							Load::lib('io', 1)->DeleteFile($image_file);
							Load::lib('io', 1)->DeleteFile($image_file_small);

							$image_file_small = $site_url . '/' . $image_file_small;
						}
					}


					$sql = "update `".TABLE_PREFIX."topic_image` set `site_url`='{$site_url}', `photo`='{$image_file}' , `name`='{$name}' , `filesize`='{$image_size}' , `width`='{$image_width}' , `height`='{$image_height}' where `id`='{$image_id}'";
					$this->DatabaseHandler->Query($sql);


					if($this->Get['type'] == 'modify')
					{

						echo "<script language='Javascript'>";
						echo "parent.modifyimgId={$image_id};";
						echo "parent.document.getElementById('imageids').value={$image_id};";
						echo "parent.document.getElementById('modify_img_upload').style.display='block';";
						echo "parent.document.getElementById('modify_viewImg').innerHTML='".cut_str($name,14)."';";
						echo "parent.document.getElementById('modify_add_img').style.display='none';";
						echo "</script>";

					}
					else
					{
						echo "<script language='Javascript'>";
						echo "parent.imgId={$image_id};";
						echo "parent.document.getElementById('uploading').innerHTML='';";
						echo "parent.document.getElementById('publisher_image_form').style.display='none';";
						echo "parent.document.getElementById('insertImgDiv').style.display='none';";
						echo "parent.document.getElementById('uploading').style.display='none';";
						echo "parent.document.getElementById('viewImgDiv').style.display='block';";
						echo "parent.document.getElementById('viewImg').innerHTML='".cut_str($name,14)."';";
						echo "parent.document.getElementById('img_pre').src='{$image_file_small}';";
						echo "if(''==parent.document.getElementById('i_already').value){parent.document.getElementById('i_already').value='分享图片';}";
						echo "parent.document.getElementById('publishSubmit').disabled=false;";
						echo "parent.document.getElementById('i_already').focus();";
						echo "</script>";

					}

				}
			}
		}

		if ($error_msg) {
			echo "<script language='Javascript'>";
			echo "alert('{$error_msg}');";
			echo "</script>";
		}

	}

	function _removeTopicImage($id=0)
	{



		$sql = "select * from ".TABLE_PREFIX."topic_image where `tid`<1" . ($id>0?" and `id`<'".($id - 10)."'":"");
		$query = $this->DatabaseHandler->Query($sql);
		while (false != ($row = $query->GetRow()))
		{
			Load::lib('io', 1)->DeleteFile(topic_image($row['id'],'small'));
			Load::lib('io', 1)->DeleteFile(topic_image($row['id'],'original'));
		}
	}

		function Create_Group()
	{
		if (MEMBER_ID < 1) {
			js_alert_output("请先登录或者注册一个帐号");
		}
		include(template('topic_group_create_ajax'));
	}


		function Do_Group()
	{

		if (MEMBER_ID < 1) {
			js_alert_output("请先登录或者注册一个帐号");
		}

		$uid = MEMBER_ID;
		$group_name = $this->Post['group_name'];
		$gid = (int) $this->Post['gid'];
		$touid = (int) $this->Post['touid'];

		if(empty($group_name)){
			js_alert_output('分组不能为空');
		}
		if (preg_match('~[\~\`\!\@\#\$\%\^\&\*\(\)\=\+\[\{\]\}\;\:\'\"\,\<\.\>\/\?]~',$group_name)) {
			js_alert_output('分组不能包含特殊字符');
		}
				$f_rets = filter($group_name);
		if($f_rets && $f_rets['error'])
		{
			js_alert_output($f_rets['msg']);
		}

		$sql="SELECT * FROM ".TABLE_PREFIX.'group'." WHERE `group_name`='{$group_name}' and `uid` ='{$uid}' limit 0,1";
		$query = $this->DatabaseHandler->Query($sql);
		$row = $query->GetRow();
		if($row)
		{
			js_alert_output($group_name.' 分组已经存在');
		}
		if($this->Post['act'] == 'modify' )
		{
			$sql = "update `".TABLE_PREFIX."group` set `group_name`='{$group_name}'  where `id`='{$gid}'";
			$this->DatabaseHandler->Query($sql);

			$sql = "update `".TABLE_PREFIX."groupfields` set `g_name`='{$group_name}'  where `uid` ='{$uid}' and `gid`='{$gid}'";
			$this->DatabaseHandler->Query($sql);

			$sql="SELECT * FROM ".TABLE_PREFIX.'group'." WHERE id='{$gid}' ";
			$query = $this->DatabaseHandler->Query($sql);
			$group_view=$query->GetRow();


			include($this->TemplateHandler->Template('modify_group_ajax'));


		} else {

			$sql = "insert into `".TABLE_PREFIX."group`(`uid`,`group_name`) values ('".MEMBER_ID."','{$group_name}')";
			$query = $this->DatabaseHandler->Query($sql);
			$group_id = $this->DatabaseHandler->Insert_ID();

			if($this->Post['act'] == 'add')
			{
				echo "<script language='Javascript'>";
				echo "window.location.href='index.php?mod=topic&code=group&gid={$group_id}';";
				echo "</script>";
				exit;
			}

			$sql="SELECT * FROM ".TABLE_PREFIX.'group'." WHERE id='{$group_id}' and `uid` = '{$uid}' ";
			$query = $this->DatabaseHandler->Query($sql);
			$group_list[] = $query->GetRow();

			if ($this->Post['act'] == 'menu_add') {
				include($this->TemplateHandler->Template('topic_group_add_item'));
			} else {
				include($this->TemplateHandler->Template('topic_group_ajax'));
			}

		}

	}



		function Group_fields()
	{
		$uid = MEMBER_ID;
		if($uid < 1) exit;
		
		$g_id = jget('gid', 'int', 'P');
		$touid = jget('touid', 'int', 'P');

		$sql="SELECT * FROM ".TABLE_PREFIX.'group'." WHERE uid='$uid' and id='$g_id'";
		$query = $this->DatabaseHandler->Query($sql);
		$group_info=$query->GetRow();

				$sql="SELECT `uid` FROM ".TABLE_PREFIX.'members'." WHERE uid='$touid'";
		$query = $this->DatabaseHandler->Query($sql);
		$member_info=$query->GetRow();

				$sql="SELECT `touid`,`display` FROM ".TABLE_PREFIX.'groupfields'." WHERE touid ='{$touid}' and gid='$g_id'";
		$query = $this->DatabaseHandler->Query($sql);
		$fields_info=$query->GetRow();


		if(empty($fields_info['display']))
		{
						$sql = "insert into `".TABLE_PREFIX."groupfields`(`uid`, `touid`,`gid`,`g_name`,`display`) values ('$uid','{$member_info['uid']}','{$group_info['id']}','{$group_info['group_name']}','1')";
			$query = $this->DatabaseHandler->Query($sql);
		}
		else
		{
						$sql = "delete from `".TABLE_PREFIX."groupfields` where `touid`='{$touid}' and gid = '{$g_id}'";
			$this->DatabaseHandler->Query($sql);
		}

				$sql = "select count(*) as group_count from `".TABLE_PREFIX."groupfields` where `uid`='{$uid}' and `gid`='{$g_id}'";
		$group_count = DB::result_first($sql);

		$sql = "update `".TABLE_PREFIX."group` set `group_count`='{$group_count}'  where `uid`='{$uid}' and `id`='{$g_id}'";
		$this->DatabaseHandler->Query($sql);

	}


	
		function Group_Menu()
	{
		if (MEMBER_ID < 1) {
			js_alert_output("请先登录或者注册一个帐号");
		}
		$uid = MEMBER_ID;
		$timestamp = time();

		$userid = (int) get_param('to_user');

				$member = jsg_member_info($userid);
		if(!$member) {
			js_alert_output("您要操作的用户已经不存在了");
		}

				$buddy_info = Load::model('buddy')->info($userid, $uid);


				$sql = "select  GF.touid , GF.g_name , GF.display , G.* from `".TABLE_PREFIX."group` G left join `".TABLE_PREFIX."groupfields` GF on G.id=GF.gid where G.uid='".MEMBER_ID." ' ";
		$query = $this->DatabaseHandler->Query($sql);
		$group_list = array();

		while (false != ($row = $query->GetRow()))
		{
			$group_list[$row['id']] = $row;
		}

				$sql = "select  `uid`,`gid`,`touid` from  `".TABLE_PREFIX."groupfields` where uid='".MEMBER_ID."' and touid= '{$userid}' ";
		$query = $this->DatabaseHandler->Query($sql);
		$group_set = array();

		while (false != ($row = $query->GetRow()))
		{
			$group_set[] = $row['gid'];
		}


		$val["uid"]=$userid;
		$handle_key = get_param('handle_key');
		include($this->TemplateHandler->Template('topic_group_menu'));
	}

		function GroupList()
	{
		$userid = trim($this->Post['touid']);

		$sql = "select GF.gid,GF.g_name  from  `".TABLE_PREFIX."groupfields` GF  where GF.uid='".MEMBER_ID."'  and GF.touid='$userid' ";
		$query = $this->DatabaseHandler->Query($sql);
		$user_group = array();
		while (false != ($row = $query->GetRow()))
		{
			echo '<a href="index.php?mod=topic&code=follow&gid='.$row['gid'].'">[ '.$row['g_name']." ]".'</a> ';
		}

	}

		function Remark() {
		$uid = (int) get_param('uid');

		$buddy_info = Load::model('buddy')->info($uid, MEMBER_ID);

		include($this->TemplateHandler->Template('topic_remark_ajax'));
	}

		function Add_Remark()
	{
				$remark = trim(strip_tags($this->Post['remark']));

				$buddyid =  (is_numeric($this->Post['buddyid']) ? $this->Post['buddyid'] : 0);
		if($buddyid < 1) {
			response_text('请指定一个好友ID');
		}

		$buddy_info = Load::model('buddy')->info($buddyid, MEMBER_ID);
		if(!$buddy_info) {
			response_text('你的好友已经不存在了');
		}

		$f_rets = filter($remark);
		if ($f_rets && $f_rets['error']) {
			response_text($f_rets['msg']);
		}

				if ($remark && preg_match('~[\<\>\'\"]~',$remark)) {
			response_text('不能包含特殊字符');
		}
		
		if($remark != $buddy_info['remark']) {
			$p = array(
				'id' => $buddy_info['id'],
			);
			$ret = Load::model('buddy')->set_remark($p, $remark);
		}

			}

		function DoDelMyFans() {
		$buddyid = MEMBER_ID;
				$touid = (int) $this->Post['touid'];
		if($buddyid > 0 && $touid > 0) {
						$is_black = $this->Post['is_black'];
			if($is_black) {
								$this->_AddBlackList($buddyid,$touid,'add');
			}
			
			Load::model('buddy')->del_info($buddyid, $touid);
		}

		include(template('topic_fans'));
	}

		function Follow()
	{
		$GLOBALS['disable_show_msg'] = 1;		$response = '';

				$follow_button = $this->Post['follow_button'];

		if(MEMBER_ID < 1) {
			js_show_login('登录后才能执行此操作');
		}

				$uid = (int) $this->ID;
		if($follow_button == 'channel'){			$isbuddy = DB::result_first("SELECT count(*) FROM ".DB::table('buddy_channel')." WHERE uid = '".MEMBER_ID."' AND ch_id = '$uid'");
			if($isbuddy){
				DB::query("DELETE FROM ".DB::table('buddy_channel')." WHERE uid = '".MEMBER_ID."' AND ch_id = '$uid'");
				$response = follow_channel($uid,0);
			}else{
				DB::query("INSERT INTO ".DB::table('buddy_channel')." (`uid`,`ch_id`) values ('".MEMBER_ID."','{$uid}')");
				$response = follow_channel($uid,1);
			}
		}elseif($this->Config['department_enable'] && $follow_button == 'department'){			$isbuddy = DB::result_first("SELECT count(*) FROM ".DB::table('buddy_department')." WHERE uid = '".MEMBER_ID."' AND did = '$uid'");
			if($isbuddy){
				DB::query("DELETE FROM ".DB::table('buddy_department')." WHERE uid = '".MEMBER_ID."' AND did = '$uid'");
				$response = follow_department($uid,0);
			}else{
				DB::query("INSERT INTO ".DB::table('buddy_department')." (`uid`,`did`) values ('".MEMBER_ID."','{$uid}')");
				$response = follow_department($uid,1);
			}
		}else{
			$rets = buddy_add($this->ID, MEMBER_ID, 1);
			if($rets) {
				if($rets['error']) {
					js_alert_output($rets['error']);
				} elseif ($rets['id'] > 0) {
										if($follow_button == 'xiao'){
						$response = follow_html2($uid, 0, 0, 0);
					} else {
						$response = follow_html($uid, 0, 0, 0);
					}
				}
			} else {
								if($follow_button == 'xiao'){
					$response = follow_html2($uid, 1, 0, 0);
				} else {
					$response = follow_html($uid, 1, 0, 0);
				}
			}
						$response .= '<success></success>';
		}
		response_text($response);
	}

	function followAdd(){return "@@";
		$GLOBALS['disable_show_msg'] = 1;
		if(MEMBER_ID < 1) {
			js_show_login('登录后才能执行此操作');
		}
		
		$rets = buddy_add($this->ID, MEMBER_ID, 0);
		if($rets['error']) {
			return $rets['error'];
		}
		
		return '关注成功';
	}


		function UserMenu()
	{
		if($this->Post['nickname']) {
			$member = jsg_member_info($this->Post['nickname'], 'nickname');
		}
		if($this->Post['arrow']=='yes') {
			$arrow = true;
		}

		$uid = (int) ($this->Post['uid'] ? $this->Post['uid'] : $member['uid']);
		if($uid < 1) {
			exit;
		}

				$buddy_info = Load::model('buddy')->info($uid, MEMBER_ID);
				$blacklist_info = Load::model('buddy')->blacklist($uid, MEMBER_ID);

		$list_members = $this->TopicLogic->GetMember($uid,"`uid`,`ucuid`,`medal_id`,`username`,`nickname`,`face`,`fans_count`,`topic_count`,`validate`,`validate_category`,`aboutme`,`province`,`city`,`level`,`company`,`department`");
		$list_members['aboutme'] = cut_str($list_members['aboutme'],54);
		$list_members = Load::model('buddy')->follow_html($list_members, 'uid', 'follow_html', 1);
		$follow_html = $list_members['follow_html'];

				$sql = "select * from `".TABLE_PREFIX."user_tag_fields` where `uid` = '{$uid}'";
		$query = $this->DatabaseHandler->Query($sql);
		$usertag = $query->GetAll();


		include($this->TemplateHandler->Template('topic_user_menu'));
	}

		function Follower_choose()
	{
		$nickname = get_param('nickname');
		$template = get_param('template');
		$types = get_param('types');
		$uid = (int) get_param('uid');

		$touid = $uid;

		if($touid)
		{
			$sql = "select `uid`,`ucuid`,`nickname`,`username`,`signature` from `".TABLE_PREFIX."members` where `uid`='{$touid}' ";
			$query = $this->DatabaseHandler->Query($sql);
			$members = $query->GetRow();
		}

				Load::lib('form');
		$FormHandler = new FormHandler();
		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."common_district where `upid` = '0' order by list");
		while (false != ($rsdb = $query->GetRow())){
			$province[$rsdb['id']]['value']  = $rsdb['id'];
			$province[$rsdb['id']]['name']  = $rsdb['name'];
		}
		$province_list = $FormHandler->Select("province",$province,''," onchange=\"changeProvince();\"");




		include($this->TemplateHandler->Template('user_follower_menu'));
	}

		function DoAddMyBlackList()
	{
				$uid  = MEMBER_ID;
		if ($uid < 1) {
			json_error("请先登录或者注册一个帐号");
		}

				$touid  = (int) $this->Post['touid'];
		if($touid < 1) {
			json_error("请指定要拉黑的用户");
		}

				$member = $this->TopicLogic->GetMember($touid);
		if(!$member) {
			json_error("请指定一个正确的用户ID");
		}

				$types	= $this->Post['types'];

				$follow_html = $this->_AddBlackList($uid,$touid,$types);

				$template	= $this->Post['template'];
		if($template) {
			$template = dir_safe($template);
			
			include($this->TemplateHandler->Template($template));
		}
	}

		function DoDelMyBlackList()
	{
				$uid  = MEMBER_ID;

		if ($uid < 1) {
			json_error("请先登录或者注册一个帐号");
		}

				$touid  = (int) $this->Post['touid'];

				$this->_AddBlackList($uid,$touid,'del');

		include($this->TemplateHandler->Template('blacklist'));

	}


		function Tag_Menu()
	{
		$uid  = (int) $this->Post['uid'];
		$type = $this->Post['type'];
				if('my_tag' == $type)
		{
			$sql = "select `id`,`tag` as tag_name,`uid` from `".TABLE_PREFIX."tag_favorite` where `uid`='{$uid}' order by `id` desc limit 0,12 ";
			$query = $this->DatabaseHandler->Query($sql);
			$list = $query->GetAll();
			$my_tag_class = 'here';
					} elseif('day_tag' == $type){

			$sql = "select `id`,`name` as tag_name,`topic_count` from `".TABLE_PREFIX."tag`  WHERE dateline>='".(time() - 86400 * 7)."' GROUP BY `tag_count` DESC limit 0,12";
			$query = $this->DatabaseHandler->Query($sql);
			$list = $query->GetAll();
			$day_tag_class = 'here';
					} elseif('day_hot' == $type){

			$sql = "select `id`,`name` as tag_name,`topic_count`,`tag_count` from `".TABLE_PREFIX."tag`  WHERE dateline>='".(time() - 86400 * 7)."' GROUP BY `topic_count` DESC limit 0,12";
			$query = $this->DatabaseHandler->Query($sql);
			$list = $query->GetAll();
			$day_hot_class = 'here';
					} elseif('tui_tag' == $type){

						
			$hot_tag_recommend = ConfigHandler::get('hot_tag_recommend');
			$list = $hot_tag_recommend['list'];
			$tui_tag_class = 'here';
		}


		include($this->TemplateHandler->Template('tag_menu'));
	}


	
	function Add_User_Follow() {
		$success = 0;
		$uid = MEMBER_ID;
		if ($uid < 1) {
			$msg = "请先登录或者注册一个帐号";
		} else {
						if($this->Post['uids']) {
				$uids = $this->Post['uids'];
			}
						if($this->Post['ids']) {
				$uids = $this->Post['ids'];
			}
						if($this->Post['media_uids_'.$this->Post['media_id']]) {
				$uids =  $this->Post['media_uids_'.$this->Post['media_id']] ;
			}
			if(empty($uids)) {
				$msg = "请选择你要关注的用户";
			} else {	
				$uids = (array) $uids;
				$buddyids = array();
				foreach($uids as $v) {
					$v = (int) $v;
					if($v > 0) {
						$buddyids[$v] = $v;
					}
				}
				$GLOBALS['disable_show_msg'] = 1; 				if('add' == $this->Post['type']) {
					foreach($buddyids as $bid) {
						buddy_add($bid, $uid);
					}
		
					$success = 1;
					$msg = "关注成功";
				} elseif('del' == $this->Post['type']) {
					foreach($buddyids as $bid) {
						buddy_del($bid, $uid);
					}
		
					$msg = "取消成功";
				}
			}
		}

		
		$__to = get_param('__to');
		if('iframe' == $__to) {
			js_alert_output($msg, 'alert');
		} elseif('json' == $__to) {
			if(!$success) {
				json_error($msg);
			} else {
				json_result($msg);
			}
		} else {
			response_text($msg);
		}
	}


		function addFavoriteTag()
	{
		$uid = MEMBER_ID;

		if ($uid < 1) {

			js_alert_output("请先登录或者注册一个帐号",'alert');
		}

				$tagid = (int) $this->Post['tag'] ? $this->Post['tag'] : $this->Get['tag'];

		if(!$tagid){
			js_alert_output('请选择关注对象','alert');
		}

		
		Load::logic('other');
		$OtherLogic = new OtherLogic();
		$jsg_result = $OtherLogic->AddFavoriteTag($uid,$tagid);

		if($jsg_result <= 1)
		{
			$rets = array(
				'1'  => '话题关注成功',
				'-1' => '关注话题失败,请选择关注对象',
			);
			js_alert_output($rets[$jsg_result],'alert');
		}

	}

	

		function Favorite()
	{
		if (MEMBER_ID < 1) {
			response_text("请登录");
		}

		$uid = MEMBER_ID;
		$tid = (int) ($this->Post['tid']);

		if ($tid < 1) {
			return  "请指定一个微博";
		}

		$act = $this->Post['act'];

		
		Load::logic('other');
		$OtherLogic = new OtherLogic();
		$TopicFavorite = $OtherLogic->TopicFavorite($uid,$tid,$act);

		response_text($TopicFavorite);

	}

		function favoriteFenlei(){
		$id = (int) $this->Post['id'];
		$act = $this->Post['act'];
		$time = time();
		if($act == 'add'){
			$this->DatabaseHandler->Query("insert into ".TABLE_PREFIX."fenlei_favorite (fid,uid,dateline) values ('$id','".MEMBER_ID."','$time')");
		}elseif($act == 'delete'){
			$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."fenlei_favorite where fid = '$id'");
		}
	}

		function favoriteEvent(){
		$id = (int) $this->Post['id'];
		$act = $this->Post['act'];
		$time = time();
		if($act == 'add'){
			$this->DatabaseHandler->Query("insert into ".TABLE_PREFIX."event_favorite (type_id,uid,dateline) values ('$id','".MEMBER_ID."','$time')");
		}elseif($act == 'delete'){
			$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."event_favorite where type_id = '$id'");
		}
	}

		function FavoriteTag()
	{
		if (MEMBER_ID < 1) {

			js_show_login("请登录");
		}
		$uid = MEMBER_ID;
		$timestamp = time();

		$tag = trim($this->Post['tag'] ? $this->Post['tag'] : $this->Get['tag']);
		if (!$tag) {
			js_alert_showmsg("请指定一个标签");
		}

		$sql = "select * from `".TABLE_PREFIX."tag` where `name`='{$tag}'";
		$query = $this->DatabaseHandler->Query($sql);
		$tag_info = $query->GetRow();

		if(!$tag_info) {
			js_alert_showmsg("指定的话题已经不存在了");
		}

				$sql = "select * from `".TABLE_PREFIX."tag_favorite` where `uid`='{$uid}' and `tag`='{$tag}'";
		$query = $this->DatabaseHandler->Query($sql);
		$is_favorite = ($query->GetNumRows()>0);
		$tag_favorite = $query->GetRow();

				if ('delete' != $this->Post['act']) {
			if($tag_favorite) {
				js_alert_showmsg("指定的话题已经关注过了");
			}
		}

		$sql = "select count(*) as `tag_favorite_count` from `".TABLE_PREFIX."tag_favorite` where `uid`='{$uid}'";
		$query = $this->DatabaseHandler->Query($sql);
		$row = $query->GetRow();
		if($row) {
			$sql = "update `".TABLE_PREFIX."members` set `tag_favorite_count`='{$row['tag_favorite_count']}' where `uid`='{$uid}'";
			$this->DatabaseHandler->Query($sql);
		}
				if ('delete' == $this->Post['act']) {

			if ($is_favorite) {
				$id = $tag_favorite['id'];

				$sql = "delete from `".TABLE_PREFIX."tag_favorite` where `id`='{$id}'";
				$this->DatabaseHandler->Query($sql);


				$sql = "update `".TABLE_PREFIX."members` set `tag_favorite_count`=if(`tag_favorite_count`>1,`tag_favorite_count`-1,0) where `uid`='{$uid}'";
				$this->DatabaseHandler->Query($sql);


								$sql = "update `".TABLE_PREFIX."tag` set `tag_count`=`tag_count`-1 where `id`='{$tag_info['id']}'";
				$this->DatabaseHandler->Query($sql);

			}

			js_alert_showmsg("已取消话题关注");
		}

				else
		{

			if(!$is_favorite) {

				$sql = "insert into `".TABLE_PREFIX."tag_favorite` (`uid`,`tag`,`dateline`) values ('{$uid}','{$tag}','{$timestamp}')";
				$this->DatabaseHandler->Query($sql);
				$favorite_tag_id = $this->DatabaseHandler->Insert_ID();

				$sql = "update `".TABLE_PREFIX."members` set `tag_favorite_count`=`tag_favorite_count`+1 where `uid`='{$uid}'";
				$this->DatabaseHandler->Query($sql);

								$sql = "update `".TABLE_PREFIX."tag` set `tag_count`=`tag_count`+1 where `id`='{$tag_info['id']}'";
				$this->DatabaseHandler->Query($sql);

								$sql = "select * from `".TABLE_PREFIX."tag_favorite` where `uid`='{$uid}' order by `id` desc  limit 0,12";
				$query = $this->DatabaseHandler->Query($sql);
				$list = $my_favorite_tags = array();
				while (false != ($row = $query->GetRow())) {
					$my_favorite_tags[] = $row;
					$list[] = $row;
				}

			}
			if('input_add' != $this->Post['act'])
			{
				js_alert_showmsg("话题关注成功");
			}
			else
			{
				include($this->TemplateHandler->Template('tag_favorite_ajax'));
			}


		}

	}



		function Forward_Menu()
	{
		$tid = jget('tid','int','P');
		$forward_topic = $this->TopicLogic->Get($tid);

				$returncode = $this->Post['r'];

		if($forward_topic['roottid'])
		{
			$forward_topic = $this->TopicLogic->Get($forward_topic['roottid']);
		} 

		$forward_tid		 = $forward_topic['tid'];
		if(!$forward_tid){
			json_error("抱歉，此微博已经被删除，无法进行转发哦，请试试其他内容吧。");
		}
				include($this->TemplateHandler->Template('topic_forward_menu'));
	}


		function Do_forward()
	{
		if (MEMBER_ID < 1) {
			response_text("请登录");
		}

				if($this->MemberHandler->HasPermission('topic','forward')==false) {
			response_text("您的角色没有转发的权限");
		}

		$content = strip_tags($this->Post['content']);

		$totid  		= (int) $this->Post['tid'];
		$imageid = trim($this->Post['imageid']);
		$attachid = trim($this->Post['attachid']);

		$type = $this->Post['topictype'];
		$from = 'web';

		$is_reward = $this->Post['is_reward'];
		
		$item = trim($this->Post['item']);
		$item_id  = intval(trim($this->Post['item_id']));
		
		#是有奖转发的时候除去item
		if($is_reward){
			unset($item);
			unset($item_id);
		}
		if (!empty($item_id)) {
						Load::functions('app');
			$ret = app_check($item, $item_id);
			if (!$ret) {
				$item = '';
				$item_id = 0;
			} else {
				$from = $item;
			}
		} else {
			$item = '';
			$item_id = 0;
		}

		$data = array(
			'content' => $content,
			'totid'=>$totid,
			'imageid'=>$imageid,
			'attachid'=>$attachid,
			'from'=>$from,
			'type'=>$type,

					'item' => $item,
			'item_id' => $item_id,
			#有奖转发标记
			'is_reward' => $is_reward,
		);

		$return = $this->TopicLogic->Add($data);

		if (is_array($return) && $return['tid'] > 0)
		{
			response_text('<success></success>');
		}
		else
		{
			$return = (is_string($return) ? "[转发失败]".$return : (is_array($return) ? "[转发成功]但".implode("",$return) : "未知错误"));
			response_text("{$return}");

								}
	}

		function DoReport()
	{
		if(MEMBER_ID < 1 && !$this->Config['is_report'])
		{
			response_text('您是游客，没有权限举报');
		}

		$tid =  jget('totid','int','P');
		$report_reason = $this->Post['report_reason'];
		$report_content = $this->Post['report_content'];

		
		$data = array(
				'uid' => MEMBER_ID,
				'username' => MEMBER_NICKNAME,
				'ip' => client_ip(),
				'reason' => (int) $report_reason,
				'content' => strip_tags($report_content),
				'tid' => (int) $tid,
				'dateline' => time(),
		);

		$this->DatabaseHandler->SetTable(TABLE_PREFIX . 'report');
		$result = $this->DatabaseHandler->Insert($data);

		if($notice_to_admin = $this->Config['notice_to_admin']){
			$message = "用户".MEMBER_NICKNAME."举报了微博ID：$tid(".$data['content'].")，<a href='admin.php?mod=report&code=report_manage' target='_blank'>点击</a>进入管理。";
			$pm_post = array(
				'message' => $message,
				'to_user' => str_replace('|',',',$notice_to_admin),
			);
						$admin_info = DB::fetch_first('select `uid`,`username`,`nickname` from `'.TABLE_PREFIX.'members` where `uid` = 1');
			load::logic('pm');
			$PmLogic = new PmLogic();
			$PmLogic->pmSend($pm_post,$admin_info['uid'],$admin_info['username'],$admin_info['nickname']);
		}

		response_text('举报成功');

	}

		function TopicShow()
	{
		$uid = MEMBER_ID;

		$sql = "select `uid` from `".TABLE_PREFIX."topic_show` where `uid` =  '{$uid}' ";
		$query = $this->DatabaseHandler->Query($sql);
		$showlist = array();
		while (false != ($row = $query->GetRow())) {
			$showlist[] = $row;
		}


		$styleData = array(
			'titleColor' 	=> ($this->Post['titleColor'] ? $this->Post['titleColor'] : $this->Get['titleColor']),
			'width' 		=> ($this->Post['width'] ? $this->Post['width'] : $this->Get['width']),
			'height' 		=> ($this->Post['height'] ? $this->Post['height'] : $this->Get['height']),
			'bgColor' 		=> ($this->Post['bgColor'] ? $this->Post['bgColor'] : $this->Get['bgColor']),
			'textColor' 	=> ($this->Post['textColor'] ? $this->Post['textColor'] : $this->Get['textColor']),
			'linkColor' 	=> ($this->Post['linkColor'] ? $this->Post['linkColor'] : $this->Get['linkColor']),
			'borderColor'	=> ($this->Post['borderColor'] ? $this->Post['borderColor'] : $this->Get['borderColor']),
			'showFans' 		=> ($this->Post['showFans'] ? $this->Post['showFans'] : $this->Get['showFans']),
			'isFans' 		=> (int) ($this->Post['isFans'] ? $this->Post['isFans'] : $this->Get['isFans']),
			'isTopic' 		=> (int) ($this->Post['isTopic'] ? $this->Post['isTopic'] : $this->Get['isTopic']),
			'isTitle' 		=> (int) ($this->Post['isTitle'] ? $this->Post['isTitle'] : $this->Get['isTitle']),
			'isBorder'		=> (int) ($this->Post['isBorder'] ? $this->Post['isBorder'] : $this->Get['isBorder']),
		);

				if($showlist){

			$sql = "update `".TABLE_PREFIX."topic_show` set `stylevalue`='".serialize($styleData)."'  where `uid`='{$uid}'";
			$this->DatabaseHandler->Query($sql);

		} else{

			$sql = "insert into `".TABLE_PREFIX."topic_show` (`uid`,`stylevalue`) values ('{$uid}','".serialize($styleData)."')";
			$this->DatabaseHandler->Query($sql);
		}

		echo "<script language='Javascript'>";
		echo "location.replace('index.php?mod=show&code=show');";
		echo "</script>";
		exit;
	}


		function User_Tag()
	{
		$uid 	 	= (int) MEMBER_ID;
		$tagid 		= (int) $this->Post['tagid'];
		$tag_name 	= strip_tags($this->Post['tag_name']);
		$addtime 	= time();

		if($uid < 1)
		{
			js_alert_output("请先登录或者注册一个帐号");
		}

				$f_rets = filter($tag_name);
		if($f_rets && $f_rets['error'])
		{
			js_alert_output($f_rets['msg']);
		}

				$sql = "select count(*) as `total_record` from `".TABLE_PREFIX."user_tag_fields` where `uid` = '".MEMBER_ID."'";
		$total_record = DB::result_first($sql);


		if($total_record >= 10)
		{
			js_alert_output('最多只能设置10个标签');
		}

				if($this->Post['types'] == 'add')
		{
			if(empty($tag_name))
			{
				js_alert_output('请输入标签');
			}

			$sql = "select * from `".TABLE_PREFIX."user_tag_fields` where `tag_name` = '{$tag_name}' and `uid` = '".MEMBER_ID."'";
			$query = $this->DatabaseHandler->Query($sql);
			$row = $query->GetRow();

			if(!empty($row))
			{
				js_alert_output($tag_name.' 标签已经打上');
			}

						$sql = "select * from `".TABLE_PREFIX."user_tag` where `name` = '{$tag_name}'";
			$query = $this->DatabaseHandler->Query($sql);
			$usertag = $query->GetRow();
			if(empty($usertag))
			{
								$sql = "insert into `".TABLE_PREFIX."user_tag`(`name`,`dateline`) values ('{$tag_name}','{$addtime}')";
				$this->DatabaseHandler->Query($sql);
				$tag_insertid = $this->DatabaseHandler->Insert_ID();
			}

			$tag_insertid = $tag_insertid ? $tag_insertid :$usertag['id'];

						$sql = "insert into `".TABLE_PREFIX."user_tag_fields`(`tag_id`,`uid`,`tag_name`) values ('{$tag_insertid}','{$uid}','{$tag_name}')";
			$this->DatabaseHandler->Query($sql);
			$tag_fields_id = $this->DatabaseHandler->Insert_ID();

		}


				if($this->Post['types'] == 'useradd')
		{
			$sql = "select `tag_name` from `".TABLE_PREFIX."user_tag_fields` where `tag_name`='{$tag_name}' and `uid` = '".MEMBER_ID."'";
			$query = $this->DatabaseHandler->Query($sql);
			$row = $query->GetRow();

			if(!empty($row))
			{
				js_alert_output('标签 '.$tag_name.' 已经打上');
			}


			$sql = "insert into `".TABLE_PREFIX."user_tag_fields`(`tag_id`,`uid`,`tag_name`) values ('{$tagid}','{$uid}','{$tag_name}')";
			$this->DatabaseHandler->Query($sql);
			$tag_fields_id = $this->DatabaseHandler->Insert_ID();

		}

		$sql = "select * from `".TABLE_PREFIX."user_tag_fields` where `id` = '{$tag_fields_id}' and `uid` = '".MEMBER_ID."' limit 0,1";
		$query = $this->DatabaseHandler->Query($sql);
		$user_tag_fields[]=$query->GetRow();

				$sql = "update `".TABLE_PREFIX."user_tag` set `count`=`count`+1 where `id`='{$tagid}'";
		$this->DatabaseHandler->Query($sql);

		include($this->TemplateHandler->Template('user_tag_ajax'));
	}

		function Del_Tag()
	{
		$uid 		= (int) MEMBER_ID;
		$tag_id 	= (int) get_param('tag_id');

		

		if($uid > 0 && $tag_id > 0) {
			$info = DB::fetch_first("select * from `".TABLE_PREFIX."user_tag_fields` where `tag_id`='{$tag_id}' and `uid` = '{$uid}'");
			if($info) {
				$sql = "delete from `".TABLE_PREFIX."user_tag_fields` where `tag_id`='{$tag_id}' and `uid` = '{$uid}'";
				$this->DatabaseHandler->Query($sql);
		
				$sql = "update `".TABLE_PREFIX."user_tag` set `count`=if(`count`>1,`count`-1,0) where `id`='{$tag_id}'";
				$this->DatabaseHandler->Query($sql);
			}
		}

		include($this->TemplateHandler->Template('user_tag_ajax'));
	}

		function Del_Group()
	{
		$uid 		= (int) MEMBER_ID;
		$group_id 	= (int) get_param('group_id');

		

		if($uid > 0 && $group_id > 0) {
			$info = DB::fetch_first("select * from `".TABLE_PREFIX."group` where `id`='{$group_id}' and `uid` ='{$uid}'");
			if($info) {
				$sql = "delete from `".TABLE_PREFIX."group` where `id`='{$group_id}' and `uid` ='{$uid}'";
				$this->DatabaseHandler->Query($sql);
		
				$sql = "delete from `".TABLE_PREFIX."groupfields` where `gid`='{$group_id}'";
				$this->DatabaseHandler->Query($sql);
			}
		}
		
		
		include($this->TemplateHandler->Template('topic_follow'));
	}

		function UploadFace()
	{
		if (MEMBER_ID < 1)
		{
			js_alert_output("请先登录或者注册一个帐号");
		}

		$field = 'face';

				$temp_img_size = intval($_FILES[$field]['size']/1024);
		if($temp_img_size >= 2048)
		{
			js_alert_output('图片文件过大,2MB以内');
		}


		$type = trim(strtolower(end(explode(".",$_FILES[$field]['name']))));
		if($type != 'gif' && $type != 'jpg' && $type != 'png')
		{
			js_alert_output('图片格式不对');
		}

		$image_name = substr(md5($_FILES[$field]['name']),-10).".{$type}";
		$image_path = RELATIVE_ROOT_PATH . './images/temp/face_images/'.$image_name{0}.'/';
		$image_file = $image_path . $image_name;

		if (!is_dir($image_path))
		{
			Load::lib('io', 1)->MakeDir($image_path);
		}

		Load::lib('upload');
		$UploadHandler = new UploadHandler($_FILES,$image_path,$field,true,false);
		$UploadHandler->setMaxSize(2048);
		$UploadHandler->setNewName($image_name);
		$result=$UploadHandler->doUpload();
		if($result)
		{
			$result = is_image($image_file);
		}


		if(!$result)
		{
			js_alert_output('图片上载失败');
		}


		
		list($w,$h) = getimagesize($image_file);
		if($w > 601)
		{
			$tow = 599;
			$toh = round($tow * ($h / $w));

			$result = makethumb($image_file,$image_file,$tow,$toh);

			if(!$result)
			{
				Load::lib('io', 1)->DeleteFile($image_file);
				js_alert_output('大图片缩略失败');
			}
		}


		$up_image_path = addslashes($image_file);

		echo "<script language='Javascript'>";
		if($this->Post['temp_face'])
		{
			echo "window.parent.location.href='{$this->Config[site_url]}/index.php?mod=settings&code=face&temp_face={$up_image_path}'";
		}
		else
		{
			echo "parent.document.getElementById('cropbox').src='{$up_image_path}';";
			echo "parent.document.getElementById('img_path').value='{$up_image_path}';";
			echo "parent.document.getElementById('temp_face').value='{$up_image_path}';";
			echo "parent.document.getElementById('jcrop_init_id').onclick();";
		}
		echo "</script>";
	}

		function DoVideo()
	{
		$url = $this->Post['url'];

		preg_match_all('~(?:https?\:\/\/)(?:[A-Za-z0-9_\-]+\.)+[A-Za-z0-9]{2,4}(?:\/[\w\d\/=\?%\-\&_\~`@\[\]\:\+\#]*(?:[^<>\'\"\n\r\t\s])*)?~',$url,$match);

		if (empty($match))
		{
			js_alert_output('输入正确的视频地址');
		}

		$ext = trim(strtolower(substr($url,strrpos($url,'.'))));

				$return = array();
		if('.swf'==$ext)
		{
						$return = array
			(
				'id' => $url,
				'host' => 'flash',
				'url' => $url,
				'title' => $url,
			);
		}
		else
		{
			$return = $this->TopicLogic->_parse_video($url);
		}

				$return_content = (
		$return['title'] ?
		$return['title'] . (
		$this->Config['video_status'] ?
				"" :
				" $url"
		)
		: '分享链接  '.$url
		);

		$return_content = str_replace(array("\r\n", "\n\r", "\n", "\r"), " ", $return_content);

		if ($return)
		{
			$video_link 	= $return['id'];
			$video_hosts 	= $return['host'];
			$video_url		= $return['url'];
			$video_img = '';
			if($return['image_src'])
			{
				$return['image_local'] = $this->TopicLogic->_parse_video_image($return['image_src']);
			}
			$video_img = $return['image_local'];
			$video_img_url = '';
			if($video_img)
			{
				$video_img_url = ($this->Config['ftp_on'] ? ConfigHandler::get('ftp','attachurl') : "");
			}
			$timestamp 		= time();

			$sql = "insert into `".TABLE_PREFIX."topic_video`
			(`uid`,`tid`,`username`,`video_hosts`,`video_link`,`video_url`,`video_img`,`video_img_url`,`dateline`)
			values
			('".MEMBER_ID."','".''."','".MEMBER_NAME."','".$video_hosts."','".$video_link."','".$video_url."','".$video_img."','$video_img_url','".$timestamp."')";
			$this->DatabaseHandler->Query($sql);

			$videoid = $this->DatabaseHandler->Insert_ID();

			if($video_img) $video_img_src = $video_img_url . $video_img;


			if(empty($video_img_src))
			{
				$video_img_src = 'images/vd.gif';
			}
						echo "<script language='Javascript'>";
			echo "parent.videoid={$videoid};";
			echo "parent.document.getElementById('upload_video_list').style.display='block';";
			echo "parent.document.getElementById('add_video').style.display='none';";
			echo "parent.document.getElementById('videoid').value='".$videoid."';";
			echo "parent.document.getElementById('video_img').src='".$video_img_src."';";
			echo "parent.document.getElementById('url').value='';";
			echo "parent.document.getElementById('i_already').value=parent.document.getElementById('i_already').value + ' ".$return_content." ';";
			echo "parent.document.getElementById('return_ajax_video_title').innerHTML='[".cut_str($return_content,36)."]';";
			echo "parent.document.getElementById('publishSubmit').disabled=false;";
			echo "parent.document.getElementById('i_already').focus();";
			echo "</script>";
		}
		else
		{

			echo "<script language='Javascript'>";
			echo "parent.document.getElementById('add_video').style.display='none';";
			echo "parent.document.getElementById('url').value='';";
			echo "if(''==parent.document.getElementById('i_already').value){parent.document.getElementById('i_already').value='".$return_content."';}";
			echo "parent.document.getElementById('publishSubmit').disabled=false;";
			echo "parent.document.getElementById('i_already').focus();";
			echo "</script>";
		}

	}
		function DeleteImage() {
		$topic_image = Load::logic('image', 1)->get_info($this->ID);
		if (!$topic_image) {
			response_text("图片已经不存在了");
		}

		if($topic_image['uid'] == MEMBER_ID || 'admin' == MEMBER_ROLE_TYPE) {
			Load::logic('image', 1)->delete($this->ID);
		} else {
			response_text("您没有删除这张图片的权限");
		}
	}

		function DeleteVideo()
	{
		if($this->ID > 0) {
			$sql = "select `id`,`tid`,`uid`,`video_img` from `".TABLE_PREFIX."topic_video` where `id`='".$this->ID."' ";
			$query = $this->DatabaseHandler->Query($sql);
			$topic_video=$query->GetRow();
		}

		if (!$topic_video) {
			response_text("视频已经不存在了");
		}

		if($topic_video['uid'] == MEMBER_ID || 'admin' == MEMBER_ROLE_TYPE)
		{
			$sql = "delete from `".TABLE_PREFIX."topic_video` where `id`='{$this->ID}'";
			$this->DatabaseHandler->Query($sql);


			Load::lib('io', 1)->DeleteFile($topic_video['video_img']);

			$updata = "update `".TABLE_PREFIX."topic` set `videoid`='0' where `tid`='{$topic_video['tid']}'";
			$result = $this->DatabaseHandler->Query($updata);
		}
		else
		{
			response_text("您没有删除这个视频的权限");
		}

	}

		function DeleteMusic()
	{
		if($this->ID > 0) {
			$sql = "select * from `".TABLE_PREFIX."topic_music` where `id`='".$this->ID."' ";
			$query = $this->DatabaseHandler->Query($sql);
			$topic_music=$query->GetRow();
		}

		if (!$topic_music) {
			response_text("视频已经不存在了");
		}

		if($topic_music['uid'] == MEMBER_ID || 'admin' == MEMBER_ROLE_TYPE)
		{
			$sql = "delete from `".TABLE_PREFIX."topic_music` where `id`='{$this->ID}'";
			$this->DatabaseHandler->Query($sql);

			$updata = "update `".TABLE_PREFIX."topic` set `musicid`='0' where `tid`='{$topic_music['tid']}'";
			$result = $this->DatabaseHandler->Query($updata);
		}
		else
		{
			response_text("您没有删除这个视频的权限");
		}

	}


		function Open_Mdeal_Index()
	{
		$medalid = (int) $this->Post['medalid'];

				$sql = "select is_index from `".TABLE_PREFIX."user_medal` where `medalid` = '{$medalid}' and uid = '".MEMBER_ID."'";
		$show = $this->DatabaseHandler->ResultFirst($sql);

		if($show){
			$sql = "update `".TABLE_PREFIX."user_medal` set  `is_index`='0' where `medalid` = '{$medalid}' and uid = '".MEMBER_ID."'";
		}else{
			$sql = "update `".TABLE_PREFIX."user_medal` set  `is_index`='1' where `medalid` = '{$medalid}' and uid = '".MEMBER_ID."'";
		}
		$this->DatabaseHandler->Query($sql);
		json_result("1");
	}

		function Qmd()
	{

		$uid = MEMBER_ID;

		$sql = "select `uid`,`qmd_img` from `".TABLE_PREFIX."members` where `uid`='{$uid}'";
		$query = $this->DatabaseHandler->Query($sql);
		$row = $query->GetRow();

				$qmd_bg_path = $this->Post['qmd_bg_path'] ? $this->Post['qmd_bg_path'] : $row['qmd_img'];

		Load::logic('other');
		$OtherLogic = new OtherLogic();
		$qmd_return = $OtherLogic->qmd_list($uid,$qmd_bg_path);

		$sql = "update `".TABLE_PREFIX."members` set  `qmd_img`='{$qmd_bg_path}'  where `uid` = '{$uid}' ";
		$this->DatabaseHandler->Query($sql);

			}

		function Insert_Qmd()
	{

		if($this->Config['is_qmd'])
		{
			$uid = MEMBER_ID;

			$sql = "select `uid`,`qmd_img` from `".TABLE_PREFIX."members` where `uid`='{$uid}'";
			$query = $this->DatabaseHandler->Query($sql);
			$row = $query->GetRow();

						$qmd_bg_path = $row['qmd_img']? $row['qmd_img'] : $this->Post['qmd_bg_path'];

			Load::logic('other');
			$OtherLogic = new OtherLogic();
			$qmd_return = $OtherLogic->qmd_list($uid,$qmd_bg_path);

					}
	}


		function ShowLogin()
	{

		include($this->TemplateHandler->Template('show_login_ajax'));
	}

		function Do_Reg_Follow_User()
	{

				$follow_type = $this->Post['followType'] ;

				$_limit = $this->Post['list_limit'] ? $this->Post['list_limit'] + 15 : '15';

		$list = array();

				if ($follow_type == 'recommend') {

			$day = 7;
			$time = $day * 86400;
			$limit = (int) $this->ShowConfig['reg_follow']['user'];
			if($limit < 1) $limit = 20;

			$regfollow = ConfigHandler::get('regfollow');
						for ($i = 0; $i < count($regfollow); $i++)
			{
				if($regfollow[$i] == '')
				{
					unset($regfollow[$i]);
				}
			}
			if (!empty($regfollow)) {
				$count = count($regfollow);
				if ($count > $limit) {
					$keys = array_rand($regfollow, $limit);
					foreach ($keys as $k) {
						$uids[] = $regfollow[$k];
					}
				} else {
					$uids = $regfollow;
				}
			} else {

								$cache_id = "misc/RTU-{$day}-{$limit}";
				if (false === ($uids = cache_file('get', $cache_id))) {
					$dateline = time() - $time;
					$sql = "SELECT DISTINCT(uid) AS uid, COUNT(tid) AS topics FROM `".TABLE_PREFIX."topic` WHERE dateline>=$dateline GROUP BY uid ORDER BY topics DESC LIMIT {$limit}";

															$query = $this->DatabaseHandler->Query($sql);
					$uids = array();
					while (false != ($row = $query->GetRow()))
					{
						$uids[$row['uid']] = $row['uid'];
					}

					cache_file('set', $cache_id, $uids, 900);
				}

			}

		}
				elseif ($follow_type == 'huoyue') {

			$sql = "select DISTINCT(T.username) AS username , T.uid AS uid , COUNT(T.tid) AS count from `".TABLE_PREFIX."topic` T left join `".TABLE_PREFIX."members` M on T.uid=M.uid WHERE T.dateline>='".(time() - 86400 * 7)."' and M.face!='' GROUP BY username ORDER BY count DESC LIMIT 0,{$_limit}";
			$query = $this->DatabaseHandler->Query($sql);
			$uids =  array();
			while (false != ($row = $query->GetRow()))
			{
				$uids[$row['uid']] = $row['uid'];
			}

		}
				elseif ($follow_type == 'renqi') {

			$sql = "select DISTINCT(B.buddyid) AS buddyid , COUNT(B.uid) AS count  from `".TABLE_PREFIX."buddys` B left join `".TABLE_PREFIX."members` M on B.buddyid=M.uid WHERE B.dateline>='".(time() - 86400 * 7)."' and M.face!='' GROUP BY buddyid ORDER BY count DESC LIMIT 0,{$_limit}";
			$query = $this->DatabaseHandler->Query($sql);
			$uids = array();
			while (false != ($row = $query->GetRow()))
			{
				$uids[$row['buddyid']] = $row['buddyid'];
			}
					}
				elseif ($follow_type == 'yingxiang') {

			$sql = "select DISTINCT(T.tousername) AS username ,  COUNT(T.tid) AS count, M.face ,M.username,M.uid from `".TABLE_PREFIX."topic` T left join `".TABLE_PREFIX."members` M on T.tousername=M.username WHERE M.face !='' and  T.dateline>='".(time() - 86400 * 7)."' and T.touid > 0  GROUP BY tousername ORDER BY count DESC LIMIT 0,{$_limit}";
			$query = $this->DatabaseHandler->Query($sql);
			$uids = array();
			while (false != ($row = $query->GetRow()))
			{
				$uids[$row['uid']] = $row['uid'];
			}
			
		}

		if($uids)
		{
			$_list = $this->TopicLogic->GetMember($uids,"`uid`,`ucuid`,`username`,`face_url`,`face`,`validate`,`nickname`,`aboutme`");
			foreach ($uids as $uid) {
				if ($uid > 0 && isset($_list[$uid]) && $uid!=MEMBER_ID) {
					$list[$uid] = $_list[$uid];
				}
			}
						$user_count = $list ? count($list) : '0';

		} else {
			;
		}


				if ($follow_type == 'tag')
		{

			
			

			$sql = "select * from `".TABLE_PREFIX."tag_recommend` order by `id` desc limit  0,{$_limit}";
			$query = $this->DatabaseHandler->Query($sql);
			$tag_name = array();
			while (false != ($row = $query->GetRow()))
			{
				$tag_name[$row['name']] = $row['name'];
			}

						if($tag_name)
			{
				$query = DB::query("SELECT `id`,`name` FROM ".DB::table('tag')." where `name` in ('".implode("','", $tag_name)."') order by `id` desc limit 0,{$_limit} ");
				$tag_list = array();
				while (false != ($row = DB::fetch($query)))
				{
					$tag_list[] = $row;
				}

			}
			$tag_count = count($tag_list);

		}

		include($this->TemplateHandler->Template('reg_follow_user_ajax'));
	}


	
	function Modify_User_Signature()
	{
		$uid = (int) $this->Post['uid'];
		if($uid < 1) {
			showjsmessage("请先登录或者注册一个帐号");
		}
				if($uid != MEMBER_ID && 'admin' != MEMBER_ROLE_TYPE) {
			json_error("您无权修改此用户签名");
		}
		
				$rets = Load::model('misc')->sign_modify($uid, $this->Post['signature']);
		if(is_array($rets) && $rets['error']) {
			json_error($rets['msg']);
		} else {
			json_result($rets);
		}
	}

	
	function _AddBlackList($uid=0,$touid=0,$types='')
	{
		$uid = (is_numeric($uid) ? $uid : 0);
		if ($uid < 1) {
			json_error("请先登录或者注册一个帐号");
		}
		$touid = (is_numeric($touid) ? $touid : 0);
		if($touid < 0) {
			json_error("请指定一个用户ID");
		}

				if('add' == $types) {
			if($touid == $uid) {
				json_error('不能拉黑自己');
			}

			Load::model('buddy')->add_blacklist($touid, $uid);
		}

				if('del' == $types) {
			Load::model('buddy')->del_blacklist($touid, $uid);
		}

		$follow_html = follow_html($touid);
		return $follow_html;
	}

		function _recd_levels($type = 'all')
	{
		Load::logic('topic_recommend');
		$TopicRecommendLogic = new  TopicRecommendLogic();
		$recd_levels = $TopicRecommendLogic->recd_levels($type);
		return $recd_levels;
	}


		function recd()
	{
		Load::logic('topic_recommend');
		$TopicRecommendLogic = new  TopicRecommendLogic();
		$tid = intval($this->Get['tid']);
		$tag_id = intval($this->Get['tag_id']);

				$topic = DB::fetch_first("SELECT * FROM ".DB::table("topic")." WHERE tid='{$tid}'");
		if (empty($topic)) {
			json_error("当前微博不存在或者已经被删除了");
		}

				$topic_recd = $TopicRecommendLogic->get_info($tid);
		if (!empty($topic_recd)) {
			$topic_recd['expiration'] = empty($topic_recd['expiration']) ? '' : my_date_format($topic_recd['expiration'], 'Y-m-d ');
		}
		#默认为4全局置顶
		$topic_recd['recd'] = $topic_recd['recd'] ? $topic_recd['recd'] : 4;

		
		if ('admin' != MEMBER_ROLE_TYPE) {
			if ($topic['item'] == 'qun' && $topic['item_id'] > 0) {
								Load::logic('qun');
				$QunLogic = new QunLogic();
				$tmp_perm = $QunLogic->chk_perm($topic['item_id'], MEMBER_ID);
				if (!in_array($tmp_perm, array(1,2))) {
					json_error("你没有权限进行当前操作");
				} else {
					$recd_levels = $this->_recd_levels('qun');
				}
			} else {
				json_error("你没有权限进行当前操作");
			}
		} else {
						$recd_levels = $this->_recd_levels('topic');
			if (!empty($tag_id)) {
				$count = DB::result_first("SELECT COUNT(*)
										   FROM ".DB::table('topic_tag')."
										   WHERE item_id='{$tid}' AND tag_id='{$tag_id}' ");
				if (empty($count)) {
					json_error("当前微博不再该话题下");
				}
				$recd_levels = $this->_recd_levels('tag');
			} else {
				if ($topic_recd['item'] == 'tag' && $topic_recd['item_id'] > 0) {
					$recd_levels = $this->_recd_levels('tag');
				} else {
					if ($topic['item'] == 'qun' && $topic['item_id'] > 0) {
						$recd_levels = $this->_recd_levels('admin_qun');
					}
				}
			}
		}

		include(template("topic_recd"));
	}

		function do_recd()
	{
		Load::logic('topic_recommend');
		$TopicRecommendLogic = new  TopicRecommendLogic();
		$tid = intval($this->Post['tid']);
				$topic = DB::fetch_first("SELECT * FROM ".DB::table("topic")." WHERE tid='{$tid}'");
		if (empty($topic)) {
			json_error("当前微博不存在或者已经被删除了");
		}

		$recd = intval($this->Post['recd']);

				if ($recd>4 || $recd < 0) {
			json_error("推荐等级错误");
		}

		
		if ('admin' != MEMBER_ROLE_TYPE) {
			if ($topic['item'] == 'qun' && $topic['item_id'] > 0) {
								Load::logic('qun');
				$QunLogic = new QunLogic();
				$perm = $QunLogic->chk_perm($topic['item_id'], MEMBER_ID);
				if (!in_array($perm, array(1,2))) {
					json_error("你没有权限进行当前操作");
				} else {
					$perm = 2;
					if (in_array($recd, array(2,3,4))) {
						json_error("你没有权限进行当前操作");
					}
				}
			} else {
				json_error("你没有权限进行当前操作");
			}
		} else {
			$perm = 1;
		}

		if ($recd == 0) {
			$topic_recd = $TopicRecommendLogic->delete(array($tid));
		} else {
			$expiration = jstrtotime(trim($this->Post['expiration']));
			$display_order = intval($this->Post['display_order']);
			$tag_id = intval($this->Post['tag_id']);
			if (!empty($tag_id)) {
								$count = DB::result_first("SELECT COUNT(*)
										   FROM ".DB::table('topic_tag')."
										   WHERE item_id='{$tid}' AND tag_id='{$tag_id}' ");
				if (empty($count)) {
					json_error("当前微博不再该话题下");
				}
				$topic['item'] = 'tag';
				$topic['item_id'] = $tag_id;
			}
			$data = array(
				'expiration' => $expiration,
				'display_order' => $display_order,
				'item' => $topic['item'],
				'item_id' => $topic['item_id'],
				'tid' => $tid,
				'recd' => $recd,
				'dateline' => TIMESTAMP,
			);
			$TopicRecommendLogic->add($data);
		}
		json_result("推荐成功了");
	}

		function publishSuccess()
	{
		echo $this->js_show_msg();
		#if NEDU
		defined('NEDU_MOYO') && nlogic('feeds.app.jsg')->on_ajax_topic_published();
		#endif
	}


		function Check_Medal_List()
	{

		$types = 'user_type_medal';

		$uid = (int) $this->Post['uid'];
		$medal_id = (int) $this->Post['medal_id'];

		$medal_type = $this->Post['medal_type'];

		$medalinfo = $this->TopicLogic->GetMedal($medal_id,$uid);
		foreach ($medalinfo as $v)
		{
			$medalinfo = $v;
		}

				include($this->TemplateHandler->Template('user_follower_menu'));

	}

		function Photo() {
		$page = (int)$_POST['page'];
		$uid = max(0,(int)$_POST['uid']);
		$photo_num = 20; 		$p_ajax_num = 12; 		if($page < 0){return false;}
				$total_page = ($this->Config['total_page_default'] ? $this->Config['total_page_default'] : 100);
		if($page > $total_page) {
			return false;
		}
		$i = $page*$p_ajax_num + $photo_num;
		$p = array(
			'count' => $p_ajax_num,
			'vip' => $this->Config['only_show_vip_topic'],
			'limit' => $i.','.$p_ajax_num,
			'uid' => $uid,
		);
		$info = Load::logic('topic_list', 1)->get_photo_list($p);
		$topic_list = array();
		if (!empty($info)) {
			$total_photo = $info['count'];
			$topic_list = $info['list'];
		}
		if($topic_list){
			if($this->Config['attach_enable']){$allow_attach = 1;}else{$allow_attach = 0;}
			include($this->TemplateHandler->Template('topic_photo_ajax'));
		}
	}

}

?>
