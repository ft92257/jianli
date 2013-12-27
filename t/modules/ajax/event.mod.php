<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename event.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-08-17 19:12:46 355546937 363684665 21101 $
 *******************************************************************/




if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $Member;

	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->initMemberHandler();

		
		$this->TopicLogic = Load::logic('topic', 1);

		if (MEMBER_ROLE_TYPE != 'admin') {
			if(!$this->Config['event_open']){
				exit("管理员已关闭活动功能");
			}
		}
		
		if(MEMBER_ID < 1){
			exit("你需要先登录才能继续本操作");
		}

		$this->Member = $this->_member();

		$this->Execute();
	}

	
	function Execute()
	{
        ob_start();
        switch($this->Code){
        	case 'onloadPic':
        		$this->onloadPic();
        		break;
        	case 'create':
        		$this->create();
        		break;
        	case 'publish_share':
        		$this->publishShare();
        		break;
        	case 'simple_talk':
        		$this->simpleTalk();
        		break;
        	case 'store':
        		$this->store();
        		break;
        	case 'app':
        		$this->app();
        		break;
        	case 'need_info':
        		$this->need_info();
        		break;
        	case 'manage':
        		$this->manage();
        		break;
        	case 'choose':
        		$this->choose();
        		break;
        	case 'eventpost':
        		$this->eventPost();
        		break;
        	case 'eventlist':
        		$this->eventList();
        		break;
        	default:
        		break;
		}
        response_text(ob_get_clean());
	}

	
	function eventList(){
		$page = empty($this->Get['page']) ? 0 : intval($this->Get['page']);
		$perpage = 8;
		if ($page == 0) {
			$page = 1;
		}
		$start = ($page - 1) * $perpage;

		$uid = MEMBER_ID;
		$type = $this->Get['type'];
		$sys_config = $this->Config;
		if($type == 'event'){
			$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('event')." WHERE postman = '$uid' ");
			if ($count) {
				$list = array();
				$query = DB::query("SELECT id,title
									FROM ".DB::table('event')."
									WHERE postman = '$uid'
									ORDER BY lasttime DESC
									LIMIT $start,$perpage ");
				while ($value = DB::fetch($query)) {
					$value['event_url'] = get_full_url($sys_config['site_url'],'index.php?mod=event&code=detail&id='.$value['id']);
					$value['radio_value'] = str_replace(array('"', '\''), '', $value['title']).' - '.$value['event_url'];
					$list[] = $value;
				}
				$multi = ajax_page($count, $perpage, $page, 'getMyEventList');
			}
		}else if($type == 'join'){
			$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('event_member')." WHERE fid = '$uid' ");
			if ($count) {
				$list = array();
				$query = DB::query("SELECT id,title
									FROM ".DB::table('event_member')."
									WHERE fid = '$uid'
									ORDER BY play_time DESC
									LIMIT $start,$perpage ");
				while ($value = DB::fetch($query)) {
					$value['event_url'] = get_full_url($sys_config['site_url'],'index.php?mod=event&code=detail&id='.$value['id']);
					$value['radio_value'] = str_replace(array('"', '\''), '', $value['title']).' - '.$value['event_url'];
					$list[] = $value;
				}
				$multi = ajax_page($count, $perpage, $page, 'getJoinEventList');
			}
		}

		include(template('event_list'));

	}

	
	function eventPost(){
		$is_allowed = '';
		if(MEMBER_ID < 1){
			$is_allowed = "请先登录或者注册一个帐号";	
		}		
		
				if (MEMBER_ROLE_TYPE != 'admin' && !$is_allowed) {
			load::logic('event');
			$EventLogic = new EventLogic();
			$is_allowed = $EventLogic->allowedCreate(MEMBER_ID,$this->Member);
		}
		if($is_allowed){
			exit($is_allowed);
		}

		$item = $this->Get['item'];
		$item_id = $this->Get['item_id'];
		$top = 'top';
				$free = 'checked';
		$all = 'checked';
		$info = array();

				$need_info = $this->DatabaseHandler->ResultFirst("select need_info from ".TABLE_PREFIX."event_info");
		$info = unserialize($need_info);

				Load::lib('form');
		$FormHandler = new FormHandler();
		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."event_sort order by id");
		while ($rsdb = $query->GetRow()){
			$rs[$rsdb[id]]['value'] = $rsdb['id'];
			$rs[$rsdb[id]]['name'] = $rsdb['type'];
		}
		$event_type = $FormHandler->Select("type",$rs);

				if($this->Member['province']){
			$province_id = $this->DatabaseHandler->ResultFirst("select id from ".TABLE_PREFIX."common_district where name = '".$this->Member['province']."' and `upid` = '0'");
		}else{
			$province_id = $this->DatabaseHandler->ResultFirst("select id from ".TABLE_PREFIX."common_district where `upid` = '0' order by list");
		}
		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."common_district where `upid` = '0' order by list");
		while ($rsdb = $query->GetRow()){
			$province[$rsdb['id']]['value']  = $rsdb['id'];
			$province[$rsdb['id']]['name']  = $rsdb['name'];
		}
		$hid_province = $province_id;
		$province_list = $FormHandler->Select("province",$province,$province_id,"onchange=\"changeProvince();\"");

				if($this->Member['city']){
			$city_id = $this->DatabaseHandler->ResultFirst("select id from ".TABLE_PREFIX."common_district where name = '".$this->Member['city']."' and upid = '$province_id'");
			$where_city = " and upid = '$city_id' ";
		}
		$hid_city = $city_id;

				if($this->Member['area']){
			$area_id = $this->DatabaseHandler->ResultFirst("select id from ".TABLE_PREFIX."common_district where name = '".$this->Member['area']."' $where_city");
		}
		$hid_area = $area_id;

				$fromt = my_date_format(TIMESTAMP, 'Y-m-d');
		$hour_select_from = mk_time_select('hour',false,'hour_select_from');
		$min_select_from =  mk_time_select('min',false,'min_select_from');

		$tot = my_date_format(TIMESTAMP+7*24*3600, 'Y-m-d');
		$hour_select_to = mk_time_select('hour',false,'hour_select_to');
		$min_select_to =  mk_time_select('min',false,'min_select_to');
	
		include($this->TemplateHandler->Template('event_publish'));

	}


	
	function create(){
                if(!($this->MemberHandler->HasPermission($this->Module,$this->Code)))
        {
            json_error($this->MemberHandler->GetError());
        }
		$post = $this->Post;
	    if(!$post['name']){
	    	json_error("请输入活动标题");
	    }
	    $f_rets = filter($post['name']);
		if($f_rets && $f_rets['error']){
			json_error("活动标题".$f_rets['msg']);
		}
		if(!$post['content1']){
	    	json_error("请输入活动描述");
	    }
	    $f_rets = filter($post['content1']);
	    if($f_rets && $f_rets['error']){
			json_error("活动描述".$f_rets['msg']);
		}
		if(!$post['address']){
	    	json_error("请输入活动地址");
	    }
	    $f_rets = filter($post['address']);
	    if($f_rets && $f_rets['error']){
			json_error("活动地址".$f_rets['msg']);
		}
		if($post['money_r'] == 'money' && !$post['money']){
	    	json_error("请输入活动人均费用");
	    }
	    if($post['money_r'] == 'money' && !is_numeric($post['money'])){
	    	json_error("活动人均费用应为数字");
	    }
		if($post['qua']=='qua' && $post['fans'] && !is_numeric($post['fans_num'])){
	    	json_error("粉丝数应为数字");
	    }
		if(!$post['fromt']){
	    	json_error("请输入活动开始时间");
	    }
		if(!$post['tot']){
	    	json_error("请输入活动结束时间");
	    }
	    if(!$post['hid_pic']){
	    	json_error("请上传活动海报");
	    }
	    	    $fromt = strtotime($post['fromt']." ".$post['hour_select_from'].":".$post['min_select_from']);
	    $tot = strtotime($post['tot']." ".$post['hour_select_to'].":".$post['min_select_to']);
		if($fromt >= $tot){
	    	json_error("活动结束时间不能早于开始时间");
	    }
	    
	    $verify = $this->Config['event_verify'] ? 0 : 1;

	    load::logic('event');
	    $eventLogic = new EventLogic();
	    
				if (MEMBER_ROLE_TYPE != 'admin') {
			$is_allowed = $eventLogic->allowedCreate(MEMBER_ID,$this->Member);
		}
		if($is_allowed){
			json_error($is_allowed);
		}
	    
	    $item = get_param('item');
	    $item_id = (int) get_param('item_id');
	    $return = $eventLogic->createEvent($post,$item,$item_id,$verify);
	    
	    if(is_array($return)){
	    	json_result("修改成功",$return);
	    }else{
	    	$id = $return;
	    }
	    
	    if(0 == $verify){
	    	json_error('发布成功，等待管理员审核');
	    }
	    
	    $value = '我发布了一个活动【'.$post[name].'】，地址：' . get_full_url($this->Config['site_url'],"index.php?mod=event&code=detail&id=$id");
	    if($post['top'] == 'top'){
			$values = array(
					'id' => $id,
					'content' => $value,
					'from' => '',
				);
			json_result('发布成功', $values);
		}
		$item_id = $id;
		$msg = '发布成功';
		include($this->TemplateHandler->Template('vote_toweibo'));
		exit;
	}

	
	function publishShare(){
		$id= (int)$this->Get['id'];
		$name = $this->DatabaseHandler->ResultFirst("select title from ".TABLE_PREFIX."event where id = '$id'");
		$value = '我觉得活动【'.$name.'】不错，推荐给大家，地址：' . get_full_url($this->Config['site_url'],"index.php?mod=event&code=detail&id=$id");
		$item_id = $id;
		include($this->TemplateHandler->Template('vote_toweibo'));
		exit;
	}

	
	function simpleTalk(){
		$id= (int)$this->Get['id'];
		$item_id = $id;
		$msg = '报名成功';
		$chk_topic_type = 'talk';
		include($this->TemplateHandler->Template('vote_toweibo'));
		exit;
	}

	
	function store(){
	    $id = (int)$this->Get['id'];
	    $type = $this->Get['type'];
	    load::logic('event');
	    $eventLogic = new EventLogic();
	    $eventLogic->doStroe($id,$type);
	}

	
	function app(){
		load::logic('event');
		$EventLogic = new EventLogic();
		
	    $id = (int)$this->Get['id'];
	    $type = $this->Get['type'];
	    $time = TIMESTAMP;
	    	    $rs = $EventLogic->get_event_info($id);

		if($rs['tot'] < TIMESTAMP){
	    	json_error("活动已截止");
	    }else{
			$user_info = $EventLogic->getMemberInfo($id);
			if($type == 'cancle'){
				if($user_info['play']){
					json_error("你已参加该活动，请先联系活动发起者取消你的参加资格");
				}elseif($user_info['app'] == 0){
					json_error("你未报名该活动");
				}else{
					$EventLogic->doCancle($id);
			    	json_result("取消报名成功");
				}
		    }else{
		    			    	if($user_info['app'] == 1){
		    		json_error("你已报名该活动");
		    	}
		    	$qua = unserialize($rs['qualification']);
		    	if($rs['postman'] != MEMBER_ID){
					if($qua['fans_num']){
				    	$fans_num = $this->Member['fans_count'];
				    	if($fans_num < $qua['fans_num']){
				    		json_error("报名活动者的粉丝数不能少于".$qua['fans_num']."人");
				    	}
				    }
					if($qua['same_city']){
						$city_name = $this->DatabaseHandler->ResultFirst("select name from ".TABLE_PREFIX."common_district where id = '".(int) trim($rs['city_id'])."'");
					    if($city_name != $this->Member[city]){
					    	json_error("该活动仅限".$city_name."的朋友参与，您当前不符合要求");
					    }
				    }
				    if($qua['inqun']){
				    	$inqun = $this->DatabaseHandler->ResultFirst("select count(*) from ".TABLE_PREFIX."qun_user where qid = '{$qua[inqun]}' and uid = '".MEMBER_ID."'");
				    	if(!$inqun){
				    		$qun_name = $this->DatabaseHandler->ResultFirst("select name from ".TABLE_PREFIX."qun where qid = '$qua[inqun]'");
				    		json_error("该活动仅限<a href='index.php?mod=qun&qid=$qua[inqun]' target='_blank'>".$qun_name."</a>的朋友参与，您当前不符合要求");
				    	}
				    }
		    	}

		    	$need_info = unserialize($rs['need_app_info']);
		    			    			    			    	if(count($need_info)){
		    		$html = $this->_makeSimpleHtml($need_info);
		    		include($this->TemplateHandler->Template('need_info'));
					exit;
		    	}else{
				    $EventLogic->doApp($id);
				    json_result("1");
		    	}
		    }

	    }
	}

	
	function need_info(){
		load::logic('event');
		$EventLogic = new EventLogic();
		$id = (int)$this->Get['id'];
		$post = $this->Post;
				
		$event_info = $EventLogic->get_event_info($id);
		$qua = $event_info['need_app_info'];
		
		$qua_info = unserialize($qua);
		$qua_arr = array();
		$time = TIMESTAMP;
				foreach ($qua_info as $key=>$val) {
			if(!$post[$val['ename']]){
				json_error("请输入$val[name]");
			}
			$qua_arr[$val['name']] = $post[$val['ename']];
		}
		$qua_arr['留言'] = $post['content'];

		$qua = serialize($qua_arr);
				$EventLogic->doApp($id,$qua);
		json_result("1");
	}

	
	function manage(){
		$id = (int)$this->Get['id'];

		$page = empty($this->Get['page']) ? 0 : intval($this->Get['page']);
		$perpage = 8;
		if ($page == 0) {
			$page = 1;
		}
		$start = ($page - 1) * $perpage;

		$rs = array();
		$count = $this->DatabaseHandler->ResultFirst("select count(*) from ".TABLE_PREFIX."event_member where id = '$id' and app = 1");
		$sql = "select am.fid,m.nickname,am.app_info,am.app_time,am.play from ".TABLE_PREFIX."event_member am
				left join ".TABLE_PREFIX."members m on m.uid = am.fid
				where am.id = '$id'
				and am.app = 1
				order by am.app_time desc
				limit $start,$perpage";
		$multi = ajax_page($count, $perpage, $page, 'manage');
		$query = $this->DatabaseHandler->Query($sql);
		while ($rsdb = $query->GetRow()){
						if($rsdb['play']){
				$rsdb['type'] = "已参加";
				$rsdb['check'] = 'checked';
			}else{
				$rsdb['type'] = "尚未审核";
			}
						$rsdb['app_time'] = date("m-d h:i",$rsdb['app_time']);
						$app_info = unserialize($rsdb['app_info']);
			foreach ($app_info as $key=>$value) {
				$rsdb['app'][$key] = $value;
			}
			$rs[$rsdb['fid']] = $rsdb;
		}
    	include($this->TemplateHandler->Template('event_manage'));
		exit;
	}

	
	function choose(){
		$post = $this->Post;
		$post['id'] = (int) $post['id'];
		$type = $this->Get['type'];
		$time = TIMESTAMP;
		$i = 0;
		if($type == 'agree'){
			foreach ($post['che'] as $key=>$val) {
				$key = (int) $key;
				if($val){
					$this->DatabaseHandler->Query("update ".TABLE_PREFIX."event_member set play = 1,play_time = '$time' where id = '{$post[id]}' and fid = '$key'");
					$i++;
				}
			}
		}else{
			foreach ($post['che'] as $key=>$val) {
				$key = (int) $key;
				if($val){
					$this->DatabaseHandler->Query("update ".TABLE_PREFIX."event_member set play = 0,play_time = 0 where id = '{$post[id]}' and fid = '$key'");
					$i--;
				}
			}
		}
				$play_num = $this->DatabaseHandler->ResultFirst("select count(*) from ".TABLE_PREFIX."event_member where id = '{$post[id]}' and play = 1 ");
		$this->DatabaseHandler->Query("update ".TABLE_PREFIX."event set play_num = '$play_num' where id = '{$post[id]}' ");
		json_result("成功");
	}

	function onloadPic(){		
		if(!($this->MemberHandler->HasPermission($this->Module, 'create'))) {
            js_alert_showmsg($this->MemberHandler->GetError());
        }
        if('admin' != MEMBER_ROLE_TYPE) {
	        $is_allowed = Load::logic('event', 1)->allowedCreate(MEMBER_ID, $this->Member);
	        if($is_allowed) {
	        	js_alert_showmsg($is_allowed);
	        }
		}
        
                
        Load::lib('image');
        
        $image = new image();
		Load::lib('upload');

        if($_FILES['pic']['name']){
						$name = time().MEMBER_ID;
			$image_name = $name."_b.jpg";
			$image_path = RELATIVE_ROOT_PATH . './images/event/';
			$image_file = $image_path . $image_name;

						$image_name_show = $name."_s.jpg";
			$image_file_min = $image_path . $image_name_show;

			if (!is_dir($image_path))
			{
				Load::lib('io', 1)->MakeDir($image_path);
			}

			$UploadHandler = new UploadHandler($_FILES,$image_path,'pic',true);
			$UploadHandler->setMaxSize(2048);
			$UploadHandler->setNewName($image_name);
			$result=$UploadHandler->doUpload();

			if($result)
	        {
				$result = is_image($image_file);
			}
			if(!$result)
	        {
				unlink($image_file);
				unlink($image_file_min);
				echo "<script language='Javascript'>";
				echo "parent.document.getElementById('message').style.display='block';";
				echo "parent.document.getElementById('uploading').style.display='none';";
	        	if($this->Post['top'] == 'top'){
					echo "parent.document.getElementById('back1').style.display='block';";
					echo "parent.document.getElementById('next3').style.display='block';";
				}
												echo "parent.document.getElementById('message').innerHTML='图片上载失败'";
				echo "</script>";
				exit;
			}
			makethumb($image_file,$image_file_min,60,60,0,0,0,0,0,0);
			$image->param['ignored_animation'] = 0;
			$image->Thumb($image_file,$image_file,100,128);

        				if($this->Config['watermark_enable']) {
				Load::logic('image', 1)->watermark($image_file);
			}
			
						$hid_pic = $this->Post['hid_pic'];
			$eid = (int) $this->Post['id'];
			$this->doUnlink($hid_pic,$eid);
		
		
			
			echo "<script language='Javascript'>";
			echo "parent.document.getElementById('uploading').style.display='none';";
			if($this->Post['top'] == 'top'){
				echo "parent.document.getElementById('back1').style.display='block';";
				echo "parent.document.getElementById('next3').style.display='block';";
			}
			echo "parent.document.getElementById('message').style.display='none';";
			echo "parent.document.getElementById('img').style.display='block';";
			echo "parent.document.getElementById('showimg').src='{$image_file}';";
			echo "parent.document.getElementById('hid_pic').value='{$image_file}';";
			echo "</script>";
			exit;
        }
	}
	
		function doUnlink($pic) {
				$pic = dir_safe($pic);
		if(!$pic) return false;
		$pic = str_replace(array('\\'), array('/'), $pic);
		$pic = str_replace(array('/'.'/', '/./', '././'), array('/', '/', './'), $pic);
		if(false !== strpos($pic, '../')) return false;
		$exp = '~^(\./){0,2}images/event/\d{10}'.MEMBER_ID.'_b\.(jpg|jpeg|gif|png|bmp)$~';		
		if(preg_match($exp, $pic)) {
			unlink($pic);
			unlink(strtr($pic,'_b.','_s.'));
			return true;
		} else {
			return false;
		}
	}

	
	function _member()
	{
		if (MEMBER_ID < 1) {
			$this->Messager(null,$this->Config['site_url'] . "/index.php?mod=login");
		}

		$member = jsg_member_info(MEMBER_ID);

		return $member;
	}

	
	function _makeSimpleHtml($arr){
		foreach ($arr as $key=>$val) {
			$html .="<tr>";
			if($val['form_type'] == 'text'){
				$html .= "<td>$val[name]:<span style=\"color:red\">*</span></td><td><input type=\"text\" id=\"$val[ename]\" name=\"$val[ename]\"></td>";
			}
			if($val['form_type'] == 'select'){
				$detail=explode("\r\n",$val[form_set]);
				foreach ($detail as $val1){
					$_html .= "<option value=\"$val1\">$val1</option>";
				}
				$html .= "<td>$val[name]:<span style=\"color:red\">*</span></td>
						  <td>
						    <select id=\"$val[ename]\" name=\"$val[ename]\">
						     $_html
						    </select>
						  </td>";
				$_html = "";
			}
			$html .="</tr>";
		}
		$html .="<tr>
				  <td>留言：</td>
				  <td><input type=\"text\" id=\"content\" name=\"content\"></td>
				 </tr>";
		return $html;
	}
}