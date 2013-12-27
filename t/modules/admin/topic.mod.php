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
						case 'aboutme':
			case 'signature':
				$this->ASManage();
				break;
			case 'domanage':
				$this->doManage();
				break;
				
			case 'manage':
				$this->ManageDetail();
				break;
			case 'delmanage':
				$this->delManageDetail();
				break;
			
			case 'doverify':
				$this->doVerify();
				break;
		    case 'domodify':
				$this->DoModify();
				break;
			case 'modifylist':
				$this->ModifyList();
				break;
			case 'del_img':
				$this->DeleteImg();
				break;
			case 'del_attach':
				$this->DeleteAttach();
				break;
			case 'del_video':
				$this->DeleteVideo();
				break;
			case 'del_music':
				$this->DeleteMusic();
				break;
			case 'delrecycling':
				$this->delRecycling();
				break;
			default:
				$this->Main();
				break;
		}
		$body = ob_get_clean();
		
		$this->ShowBody($body);
		
	}

	function Main()
	{
		$per_page_num = min(500,max((int) $_GET['per_page_num'],(int) $_GET['pn'],20));
		if($_GET['pn']) $pn = '&pn='.$_GET['pn'];
		$where_list = array();
		$query_link = 'admin.php?mod=topic'.$pn.'&code='.$this->Code;
		
				$type = $this->Get['type'];
		if($type == 'first'){
			$type_arr['first'] = " selected ";
			$where_list['type'] = "`type` = '$type'";
			$query_link .= "&type=$type";
		}elseif($type == 'forward'){
			$type_arr['forward'] = " selected ";
			$where_list['type'] = "`type` in ('forward','both')";
			$query_link .= "&type=$type";
		}elseif($type == 'reply'){
			$type_arr['reply'] = " selected ";
			$where_list['type'] = "`type` in ('reply','both')";
			$query_link .= "&type=$type";
		}
		
				$postip = $this->Get['postip'];
		if($postip){
			$where_list['postip'] = " `postip` = '$postip' ";
						$where_list['lastupdatef'] = " `lastupdate` > '" . strtotime(date('Y-m-d',time()))."' ";
			$where_list['lastupdatet'] = " `lastupdate` < '".strtotime(date('Y-m-d',strtotime('+1 day')))."' ";
			$where_list['managetype'] = " managetype = 0";
			$query_link .= "&postip=$postip";
		}
		
				$tid = trim($this->Get['tid']);
		if($tid){
			$tids = str_replace(" ","','",$tid);
			$where_list['tid'] = "`tid` in ('$tids')";
			$query_link .= "&tid=$tid";
		}
				$username = trim($this->Get['username']);
		if($username){
			$where_list['username'] = "`username`='$username'";
			$query_link .= "&tid=$username";
		}
		
				$keyword = trim($this->Get['keyword']);
		if ($keyword) {
			$_GET['highlight'] = $keyword;

			$where_list['keyword'] = build_like_query('content,content2',$keyword);
			$query_link .= "&keyword=".urlencode($keyword);
		
		}
				$nickname = trim($this->Get['nickname']);
		if ($nickname) {
			
			$sql = "select `uid`,`nickname` from `".TABLE_PREFIX."members` where `nickname`='{$nickname}' limit 0,1";
			$query = $this->DatabaseHandler->Query($sql);
			$members=$query->GetRow();
		
			$where_list['uid'] = "`uid`='{$members['uid']}'";
			$query_link .= "&nickname=".urlencode($members['nickname']);
		}
				$timefrom = $this->Get['timefrom'];
		if($timefrom){
			$str_time_from = strtotime($timefrom);
			$where_list['timefrom'] = "`lastupdate`>'$str_time_from'";
			$query_link .= "&timefrom=".$timefrom;
		}
				$timeto = $this->Get['timeto'];
		if($timeto){
			$str_time_to = strtotime($timeto);
			$where_list['timeto'] = "`lastupdate`<'$str_time_to'";
			$query_link .= "&timeto=".$timeto;
		}
				
		$mtype = $this->Get['mtype'];
		if($mtype != ''){
			if($mtype == 1){
				$where_list['managetype'] = " managetype != 0";
			}else{
				$where_list['managetype'] = " managetype = '$mtype'";
			}
			$mtype_arr[$mtype] = " selected ";
			$query_link .= "&mtype={$mtype}";
		}
		
		$where = (empty($where_list)) ? null : ' WHERE '.implode(' AND ',$where_list).' ';
		
		if($this->Code == 'verify' || $this->Code == 'del'){
			$template = 'topic_verify';
			$verify = 1;
			$del = (int)$this->Get['del'];
			if($del){
				$where = $where ? $where. " and managetype = 1 " : " where managetype = 1 ";
				$query_link .= "&del=1";
			}else{
				$where = $where ? $where. " and managetype = 0 " : " where managetype = 0 ";
			}
			$sql = " select count(*) as `total_record` from `".TABLE_PREFIX."topic_verify` {$where} ";
		}else{
			$template = 'topic';
			$this->Code = 'topic_manage';
			$sql = " select count(*) as `total_record` from `".TABLE_PREFIX."topic` {$where} ";
		}
		
		$total_record = DB::result_first($sql);
		
		$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'array',),'20 50 100 200 500');
		$topic_list = array();
		if($this->Code == 'verify' || $this->Code == 'del'){
			$topic_list = $this->TopicLogic->Get(" {$where} order by `tid` desc {$page_arr['limit']} ",'*','Make',TABLE_PREFIX.'topic_verify','id');
			$action = "admin.php?mod=topic&code=doverify";
			if($topic_list){
				foreach ($topic_list as $key=>$val) {
					if($val['type'] == 'forward' && $val['roottid'] > 0){
						$topic_list[$key]['root_topic'] = $this->TopicLogic->Get($val['roottid']);
					}
				}
			}
		}else{
			$topic_list = $this->TopicLogic->Get(" {$where} order by `tid` desc {$page_arr['limit']} ");
			$action = "admin.php?mod=topic&code=domanage";
			if($topic_list){
				foreach ($topic_list as $key=>$val) {
					if($val['managetype']==0 ||$val['managetype']==1){
						$topic_list[$key]['manage_type'][1] = " checked ";
					}else{
						$topic_list[$key]['manage_type'][$val['managetype']] = " checked ";
					}
					if($val['type'] == 'forward' && $val['roottid']){
						$topic_list[$key]['root_topic'] = $this->TopicLogic->Get($val['roottid']);
					}
				}
			}
		}
		
		
		include($this->TemplateHandler->Template('admin/'.$template));
	}
	
	
	function ManageDetail(){
		$where = "";
		$query_link = "admin.php?mod=topic&code=manage";
		$tid = $this->Post['tid'] ? $this->Post['tid'] : $this->Get['tid'];
		if($tid > 0){
			$where .= " and d.tid = '$tid' ";
			$query_link .= "&tid=$tid";
		}
		$nickname = $this->Post['nickname'] ? $this->Post['nickname'] : $this->Get['nickname'];
		if($nickname){
			$where .= " and m.nickname = '$nickname' ";
			$query_link .= "&nickname=$nickname";
		}
				$timefrom = $this->Get['timefrom'];
		if($timefrom){
			$str_time_from = strtotime($timefrom);
			$where .= " and d.`dateline`>'$str_time_from'";
			$query_link .= "&timefrom=".$timefrom;
		}
				$timeto = $this->Get['timeto'];
		if($timeto){
			$str_time_to = strtotime($timeto);
			$where .= " and d.`dateline`<'$str_time_to'";
			$query_link .= "&timeto=".$timeto;
		}
		
		$sql = "select count(*) from ".TABLE_PREFIX."manage_detail d 
				left join ".TABLE_PREFIX."members tm on tm.uid = d.tuid 
				left join ".TABLE_PREFIX."members m on m.uid = d.uid 
				where 1 $where ";
		$total_record = $this->DatabaseHandler->ResultFirst($sql);
		$per_page_num = min(500,max((int) $_GET['per_page_num'],(int) $_GET['pn'],20));
		
		$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'array',),'20 50 100 200 500');
		
		$sql = "select d.*,tm.nickname as tnickname,m.nickname as nickname  
				from ".TABLE_PREFIX."manage_detail d 
				left join ".TABLE_PREFIX."members tm on tm.uid = d.tuid 
				left join ".TABLE_PREFIX."members m on m.uid = d.uid 
				where 1  $where 
				order by d.dateline desc ,d.tid desc 
				$page_arr[limit] ";
		$query = $this->DatabaseHandler->Query($sql);
		$manageList = array();
		while (false != ($rs = $query->GetRow())){
			$manageList[$rs['id']] = $rs;
		}
		$action = "admin.php?mod=topic&code=delmanage";
		include($this->TemplateHandler->Template('admin/topic_manage_detail'));
	}
	
		function delManageDetail(){
		if(MEMBER_ID != 1){
			$this->Messager("请用初始帐号执行此操作");
		}
		$ids = (array) $this->Post['ids'];
		foreach ($ids as $val) {
			$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."manage_detail where id = '$val'");
		}
		$this->Messager("操作成功");
	}
	
	
	function doManage(){
		$managetype = get_param('managetype');
		
		load::logic('topic_manage');
		$TopicManage = new TopicManageLogic();
		foreach ($managetype as $key=>$val) {
			$TopicManage->doManage($key,$val);
		}
		
		$this->Messager("操作成功");
	}
	
	
	function ASManage(){
		
		$code = $this->Code;
		if($code == 'signature'){
			$time = 'signtime';
		}else{
			$time = 'aboutmetime';
		}
		$action = "admin.php?mod=topic&code=$code";
		
		$managetype = (array) $this->Post['managetype']; 
		
		if($managetype){
			foreach ($managetype as $val=>$act) {
				if($act==1){
										$sql = "update ".TABLE_PREFIX."members set $time = '".time()."' where uid = '$val'";
				}else{
										$sql = "update ".TABLE_PREFIX."members set $code = '',$time = 0 where uid = '$val'";
				}
				$this->DatabaseHandler->Query($sql);
			}
			$this->Messager("操作成功");
		}
		
		$per_page_num = min(500,max((int) $_GET['per_page_num'],(int) $_GET['pn'],20));
		$query_link = 'admin.php?mod=topic&code='.$code;
		$where = '';
		
				$mtype = (int)$this->Get['mtype'];
		if($mtype){
			$where .= " and $time != 0 ";
			$query_link .="&mtype=$mtype";
			$mtype_arr[1] = ' selected ';
		}else{
			$where .= " and $time = 0 ";
			$query_link .="&mtype=$mtype";
		}
		
				$nickname = $this->Get['nickname'] ? $this->Get['nickname'] : $this->Post['nickname'];
		if($nickname){
			$where .= " and nickname = '$nickname' ";
			$query_link .="&nickname=$nickname";
		}
		
				$keyword = $this->Get['keyword'] ? $this->Get['keyword'] : $this->Post['keyword'];
		if($keyword){
			$where .= " and $code like '%$keyword%' ";
			$query_link .="&keyword=$keyword";
		}
		
				$timefrom = $this->Get['timefrom'];
		if($timefrom){
			$str_time_from = strtotime($timefrom);
			$where .= " and `lastactivity`>'$str_time_from'";
			$query_link .= "&timefrom=".$timefrom;
		}
				$timeto = $this->Get['timeto'];
		if($timeto){
			$str_time_to = strtotime($timeto);
			$where .= " and `lastactivity`<'$str_time_to'";
			$query_link .= "&timeto=".$timeto;
		}

		$total_record = $this->DatabaseHandler->ResultFirst("select count(*) from ".TABLE_PREFIX."members where $code != '' $where ");
		$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'array',),'20 50 100 200 500');
		$sql = "select uid,username,nickname,lastip,lastactivity,$this->Code 
				from ".TABLE_PREFIX."members 
				where $code != ''  $where 
				order by lastactivity desc 
				$page_arr[limit]";
		$query = $this->DatabaseHandler->Query($sql);
		$member = array();
		while (false != ($rs = $query->GetRow())){
			$rs['face'] = face_get($rs['uid']);
			$rs['content'] = $rs[$code];
			$member[$rs['uid']] = $rs;
		}
		
		include($this->TemplateHandler->Template('admin/as_manage'));
	}
	
		function doVerify(){
		
		$ids = get_param('ids');
		$tid = (int) get_param('tid');
		
		if($tid){
			$ids[$tid] = $tid;
		}
		
				
		load::logic('topic_manage');
		$topicManage = new TopicManageLogic();

		$manage = get_param('manage');
		$_POST['syn_to_sina'] = 1;
		$_POST['syn_to_qqwb'] = 1;
		$_POST['syn_to_kaixin'] = 1;
		$_POST['syn_to_renren'] = 1;
		foreach ($manage as $key=>$value) {
			if($value == 'keep'){
				continue;
			} elseif ($value == 'yes' || $value == 2){
				$sql = "select * from ".TABLE_PREFIX."topic_verify where id = '$key'";
				$query = $this->DatabaseHandler->Query($sql);
				$date = $query->GetRow();
				$date['content'] = addslashes($date['content']);
				if($date['longtextid']){
					$date['content'] = addslashes(DB::result_first("select `longtext` from `".TABLE_PREFIX."topic_longtext` where id = '$date[longtextid]'"));
				}
				
				$date['verify'] = "verify";
				$managetype = $date['managetype'];
				$date['managetype'] = ($value == 2) ? 2 : 1;
				$date['checkfilter'] = 1;
												$return = $this->TopicLogic->Add($date,0,$date['imageid']);

												if(1 == $managetype){
					$managetype = 4;
				}else{
					$managetype = $date['tid'] ? 3 : 0;
				}
				if($date['tid']){
					$topicManage->manageDetail($date['tid'],$managetype,$date['managetype'],$del=1);
				}
				
		        		        $this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."topic_verify where id = '{$key}'");
		        		        if($date['longtextid']){
		        	DB::query("delete from `".TABLE_PREFIX."topic_longtext` where id = '$date[longtextid]'");
		        }
			}elseif($value == 'no'){
				$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."topic_verify where id = '$key'");
				$topic = $query->GetRow();
				
				if($topic){
																				$managetype = $topic['tid'] ? 3 : 0;
					if($topic['tid']){
						$topicManage->manageDetail($topic['tid'],$managetype,4,$del=1);
					}		
					$this->DatabaseHandler->Query("update ".TABLE_PREFIX."topic_verify set managetype = 1 where id = '$key'");
				}
			}elseif($value == 'dodel'){
				$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."topic_verify where id = '$key'");
				$topic = $query->GetRow();
				if($topic){
					if($topic['tid']){
						$topicManage->manageDetail($topic['tid'],4,5);
					}
					$return = $this->TopicLogic->Delete($topic['tid']);
				}
			}
		}
		
		unset($this->_cache);
		$this->Messager("操作成功");
	}
	
	
	function delRecycling(){
		$query = $this->DatabaseHandler->Query("select `id`,`tid` from `".TABLE_PREFIX."topic_verify` where managetype = 1 limit 100");
		while ($rs = $query->GetRow()){
			if($rs['tid'] > 0){
				$tids[$rs['id']] = $rs['tid'];
			}
		}
		if($tids){
			foreach ($tids as $tid) {
				if($tid > 0){
					$this->TopicLogic->Delete($tid);
				}
			}
			$this->delRecycling();
		}else{
			$this->Messager('清空回收站成功','admin.php?mod=topic&code=del&del=1');
		}
	}

	function ModifyList()
	{	 
		 $title = "编辑微博";
		 $tid = (int) $this->Get['tid'];
		 $verify = $this->Get['verify'];
		 
		 if(!empty($tid)) {
		 	if($verify){
				$action = "admin.php?mod=topic&code=domodify&verify=verify";
				$sql = "select  T.tid , T.imageid, T.videoid , T.musicid ,T.content,T.content2 ,M.nickname,M.username , T.* 
						from `".TABLE_PREFIX."members` M 
						left join `".TABLE_PREFIX."topic_verify` T on M.uid=T.uid 
						where T.tid='{$tid}' limit 0,1";
			}else{
				$action = "admin.php?mod=topic&code=domodify";
				$sql = "select  T.tid , T.imageid, T.videoid , T.musicid ,T.content,T.content2 ,M.nickname,M.username , T.* 
						from `".TABLE_PREFIX."members` M 
						left join `".TABLE_PREFIX."topic` T on M.uid=T.uid 
						where T.tid='{$tid}' limit 0,1";
			}
			$query = $this->DatabaseHandler->Query($sql);
			$topiclist=$query->GetRow();
			
			
			if($topiclist['longtextid'] > 0) {
				$topiclist['content'] = DB::result_first("select `longtext` from ".DB::table('topic_longtext')." where `id`='{$topiclist['longtextid']}'");
			} else {
				$topiclist['content'] = $topiclist['content'].$topiclist['content2'];
			}
			
		 } else {
		 		$this->Messager(NULL,'admin.php?mod=topic',0);
		 }
			
		 if($topiclist==false) 
		 {
		 		$this->Messager("您要编辑的微博信息已经不存在!");
		 }
		
		
        		$topiclist['content'] = preg_replace('~<U ([0-9a-zA-Z]+)>(.+?)</U>~','',$topiclist['content']);

	    		$topiclist['content'] = strip_tags($topiclist['content']);	
		
				if('both'==$topiclist['type'] || 'forward'==$topiclist['type'])
		{
			$topiclist['content'] = $this->TopicLogic->GetForwardContent($topiclist['content']);
		}
		
		
		$image_list = array();
		if($topiclist['imageid']){
			$this->DatabaseHandler->SetTable(TABLE_PREFIX.'topic_image');
			$image_id_arr = explode(",",$topiclist['imageid']);
			foreach ($image_id_arr as $value) {
				$img = $this-> DatabaseHandler->Select($value);
	 		 	$image_list[$img['id']]['id'] = $img['id'];
				$image_list[$img['id']]['img_path'] = topic_image($img['id']);
			 }
		 }

		$attach_list = array();
		if($topiclist['attachid']){
			$this->DatabaseHandler->SetTable(TABLE_PREFIX.'topic_attach');
			$attach_id_arr = explode(",",$topiclist['attachid']);
			foreach ($attach_id_arr as $value) {
				$attach = $this-> DatabaseHandler->Select($value);
	 		 	$attach_list[$attach['id']]['id'] = $attach['id'];
				$attach_list[$attach['id']]['attach_name'] = topic_attach($attach['id'],'name');
				$attach_list[$attach['id']]['attach_score'] = topic_attach($attach['id'],'score');
				$attach_list[$attach['id']]['attach_img'] = 'images/filetype/'.topic_attach($attach['id'],'filetype').'.gif';
			 }
		 }
		 
		 if($topiclist['videoid'])
		 {
		 		$this->DatabaseHandler->SetTable(TABLE_PREFIX.'topic_video');
			  $video = $this-> DatabaseHandler->Select($topiclist['videoid']);
			  
			  $videoid 	 = $video['id'];
			  $videohost = $video['video_hosts'];
			  $videolink = $video['video_link'];
			  $videoimg  = $video['video_img'];
		 }
		 
		 if($topiclist['musicid']){
		 		$this->DatabaseHandler->SetTable(TABLE_PREFIX.'topic_music');
			  $topic_music = $this-> DatabaseHandler->Select($topiclist['musicid']);
			  
			  $musicid_id = $topic_music['id'];
			  $ContentMusicid =  $topic_music['music_url'];
		 }
		
		include $this->TemplateHandler->Template('admin/topic_info');
	}
	
	function DoModify()
	{	 
		$verify = (int) get_param('verify');
		$tid = (int) get_param('tid');
		
		if($verify){			$sql = "select * from `".TABLE_PREFIX."topic_verify` where `tid` = '{$tid}' limit 0,1";
			$table = TABLE_PREFIX."topic_verify";
		}else{
			$sql = "select * from `".TABLE_PREFIX."topic` where `tid` = '{$tid}' limit 0,1";
			$table = TABLE_PREFIX."topic";
		}
		$query = $this->DatabaseHandler->Query($sql);
		$topiclist=$query->GetRow();
		
		if($topiclist['content2'])
		{
			$sql = "update `" . TABLE_PREFIX . "topic` set `content2`='' where `tid`='{$tid}'";
        	$this->DatabaseHandler->Query($sql);
		}
		
		preg_match_all('~(?:https?\:\/\/)(?:[A-Za-z0-9_\-]+\.)+[A-Za-z0-9]{2,4}(?:\/[\w\d\/=\?%\-\&_\~`@\[\]\:\+\#]*(?:[^<>\'\"\n\r\t\s])*)?~',$topiclist['content'],$match);
		$is_post_url = implode(glue,$match[0]);  
		
		        preg_match_all('~(.+?)</U>~', $topiclist['content'], $URL);
        $url_tag = implode($URL[0]);
        
		$content		=  strip_tags($this->Post['content']);
		$totid 			=  $topiclist['totid'];
		$imageid 		=  $topiclist['imageid'];
		$attachid 		=  $topiclist['attachid'];
		$type 			=  $topiclist['type'];
		$uid  			=  $topiclist['uid'];
		$username 		=  $topiclist['username'];
		$tid  			=  $topiclist['tid'];

		$return = $this->TopicLogic->Modify($tid,$content,$imageid,$attachid,$table);
		$newcontent = $url_tag.$content;
		
															        
				if(isset($this->Post['attach_score']) && $this->Post['attach_id'] && is_array($this->Post['attach_id'])){
			foreach($this->Post['attach_score'] as $key => $value){
				if($this->Post['old_attach_score'][$key] != $value){
					DB::update('topic_attach', array('score' => $value), array('id' => $this->Post['attach_id'][$key]));
				}
			}
		}
    
		if(!is_array($return)) {
			$this->Messager("【编辑失败】{$return}");	
		}
		else {
			$this->Messager("编辑成功",'admin.php?mod=topic&code='.$verify);
	 	}			 	 
	}
	
	function DeleteImg()
	{			
		$tid = (int) $this->Get['tid'];
		$ids =  ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);
		
		$sql = "delete from `".TABLE_PREFIX."topic_image` where `id`='{$ids}'";
		$this->DatabaseHandler->Query($sql);
		
		
		Load::lib('io', 1)->DeleteFile(topic_image($ids,'small'));
		Load::lib('io', 1)->DeleteFile(topic_image($ids,'original'));
		
		$verify = $this->Get['verify'];
		if($verify){
			$table = TABLE_PREFIX."topic_verify";
		}else{
			$table = TABLE_PREFIX."topic";
		}
		
		$imageid = $this->DatabaseHandler->ResultFirst("select imageid from $table where tid = '$tid'");

		if(!$imageid) {
		    $this->Messager("请指定要删除的对象");
		}
		$image_id_arr = explode(",",$imageid);
		foreach ($image_id_arr as $key=>$value) {
			if($value == $ids){
				unset($image_id_arr[$key]);
			}
		}
		$new_imageid = implode(",",$image_id_arr);
		$updata = "update $table set `imageid`='$new_imageid' where `tid`= '$tid'";
		$result = $this->DatabaseHandler->Query($updata);

		$this->Messager("操作成功");
	}
	
function DeleteAttach()
	{			
		$tid = (int) $this->Get['tid'];
		$ids =  ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);
		
		$sql = "delete from `".TABLE_PREFIX."topic_attach` where `id`='{$ids}'";
		$this->DatabaseHandler->Query($sql);
		
		
		Load::lib('io', 1)->DeleteFile(topic_attach($ids,'file'));
		
		$verify = $this->Get['verify'];
		if($verify){
			$table = TABLE_PREFIX."topic_verify";
		}else{
			$table = TABLE_PREFIX."topic";
		}
		
		$attachid = $this->DatabaseHandler->ResultFirst("select attachid from $table where tid = '$tid'");

		if(!$attachid) {
		    $this->Messager("请指定要删除的对象");
		}
		$attach_id_arr = explode(",",$attachid);
		foreach ($attach_id_arr as $key=>$value) {
			if($value == $ids){
				unset($attach_id_arr[$key]);
			}
		}
		$new_attachid = implode(",",$attach_id_arr);
		$updata = "update $table set `attachid`='$new_attachid' where `tid`= '$tid'";
		$result = $this->DatabaseHandler->Query($updata);

		$this->Messager($return ? $return : "操作成功");
	}

	function DeleteVideo()
	{
        	$ids =  ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);
    		if(!$ids) {
    			$this->Messager("请指定要删除的对象");
    		}
		
			$sql = "select `id`,`tid`,`video_img` from `".TABLE_PREFIX."topic_video` where `id`='".$ids."' ";
			$query = $this->DatabaseHandler->Query($sql);
			$topic_video=$query->GetRow();
			
			
			$sql = "delete from `".TABLE_PREFIX."topic_video` where `id`='{$topic_video['id']}'";
			$this->DatabaseHandler->Query($sql);		
	
			
			Load::lib('io', 1)->DeleteFile($topic_video['video_img']);
			
			$verify = $this->Get['verify'];
			if($verify){
				$table = TABLE_PREFIX."topic_verify";
			}else{
				$table = TABLE_PREFIX."topic";
			}
			
			$updata = "update `$table` set `videoid`='0' where `tid`='{$topic_video['tid']}'";
		    $result = $this->DatabaseHandler->Query($updata);
		
			$this->Messager("操作成功");
	}
	
	function DeleteMusic()
	{		
		$ids =  (int) ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);
		$sql = "delete from `".TABLE_PREFIX."topic_music` where `tid`='{$ids}'";
		$this->DatabaseHandler->Query($sql);	
		
		$verify = $this->Get['verify'];
		if($verify){
			$table = TABLE_PREFIX."topic_verify";
		}else{
			$table = TABLE_PREFIX."topic";
		}
		
		$updata = "update `$table` set `musicid`='0' where `musicid`='$ids'";
		$result = $this->DatabaseHandler->Query($updata);
	
		$this->Messager("操作成功");
	}
		
}

?>
