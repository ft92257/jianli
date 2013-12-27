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
 * @Date 2012-07-06 20:53:40 2109293481 231519663 24311 $
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

		
		$this->TopicLogic = Load::logic('topic', 1);
		if (MEMBER_ROLE_TYPE != 'admin') {
			if(!$this->Config['event_open']){
				$this->Messager("管理员已关闭活动功能","index.php");
			}
		}
		$this->Member = $this->_member();
		
		$this->Execute();

	}

	
	function Execute()
	{
		ob_start();
		switch ($this->Code){
			case 'pevent':
				$this->pevent();
				break;
			case 'editevent':
				$this->editEvent();
				break;
			case 'myevent':
				$this->myevent();
				break;
			case 'followevent':
				$this->followevent();
				break;
			case 'detail':
				$this->eventDetail();
				break;
			case 'del':
				$this->delevent();
				break;
			case 'alluser':
				$this->getAllUser();
				break;
			case 'export_to_excel':
            	$this->ExportToExcel();
            	break;   
			default:
				$this->main();
				break;			
		}
		$body=ob_get_clean();

		$this->ShowBody($body);
	}
	
	
	function main(){
				Load::lib('form');
		$FormHandler = new FormHandler();
		
		load::logic('event');
		$EventLogic = new EventLogic();
		
		$hot_event = $EventLogic->getHotEvent();
		
				$uids = array();
		if($uids = $EventLogic->getDaRen()){
			$hd_daren = $this->TopicLogic->GetMember($uids);
		}
		
				$event_type = array();
		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."event_sort order by id");
		while ($rsdb = $query->GetRow()){
			$acount = $this->DatabaseHandler->ResultFirst("select count(*) from ".TABLE_PREFIX."event where type_id = '$rsdb[id]' and verify = 1");
			$event_type[$rsdb['id']]['count'] = $acount;
			$event_type[$rsdb['id']]['type'] = $rsdb['type'];
		}

		$where = " 1 ";
		$type = intval($this->Get['type']);
		if($type){
			$where .= " and a.type_id = '$type' ";
			$tclass[$type] = " class='fred'";
			$hid_type = $type;
		}else{
			$tclass[0] = " class='fred'";
		}

						$choose_province = intval($this->Get['province']);
		$choose_city = intval($this->Get['city']);
		$choose_area = intval($this->Get['area']);
		$nocity = $this->Get['nocity'];
		
		if($choose_province){
			$hid_province = $choose_province;
		}
		if($this->Member['province']){
			$province_id = $this->DatabaseHandler->ResultFirst("select id from ".TABLE_PREFIX."common_district where name = '".$this->Member['province']."' and `upid` = '0'");
		}
		else{
			$province_id = $this->DatabaseHandler->ResultFirst("select id from ".TABLE_PREFIX."common_district where `upid` = '0' order by list");
		}
		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."common_district where `upid` = '0' order by list");
		while ($rsdb = $query->GetRow()){
			$province[$rsdb['id']]['value']  = $rsdb['id'];
			$province[$rsdb['id']]['name']  = $rsdb['name'];
		}
		$province_id = $choose_province ? $choose_province : $province_id;
		$province_list = $FormHandler->Select("province",$province,$province_id," style=\"display:none\" onchange=\"changeProvince();\"");

		if($province_id){
			$hid_province = $province_id;
			if($this->Member['city']){
				$city_id = $this->DatabaseHandler->ResultFirst("select id from `".TABLE_PREFIX."common_district` where `name` = '".$this->Member['city']."'");
			}else{
				$city_id = $this->DatabaseHandler->ResultFirst("select id from `".TABLE_PREFIX."common_district` where `upid` = '$province_id' order by `list`");
			}
			$query = $this->DatabaseHandler->Query("select * from `".TABLE_PREFIX."common_district` where `upid` = '$province_id' order by `list`");
			while ($rsdb = $query->GetRow()){
				$city[$rsdb['id']]['value'] = $rsdb['id'];
				$city[$rsdb['id']]['name'] = $rsdb['name'];
			}
			$city_id = $choose_city ? $choose_city : $city_id;
			$city_name = $this->DatabaseHandler->ResultFirst("select `name` from `".TABLE_PREFIX."common_district` where `id` = '$city_id' ");
			$city_list = $FormHandler->Select("city",$city,$city_id," style=\"display:none\" onchange=\"changeCity();\"");
			
			if($city_id){
				$query = $this->DatabaseHandler->Query("select * from `".TABLE_PREFIX."common_district` where `upid` = '$city_id' order by `list`");
				while ($rsdb = $query->GetRow()){
					$area[$rsdb['id']] = $rsdb['name'];
				}
			}
		}
		
		if($nocity){
			$cclass['no'] = " class='fred'";
			$hid_city = $city_id;
			$hid_no_city = $nocity;
		}else{
			if($city_id && !$choose_area){
				$where .= " and a.city_id = '$city_id' ";
				$cclass[$city_id] = " class='fred'";
				$hid_city = $city_id;
			}else{
				if($choose_area){
					$where .= " and a.area_id = '$choose_area' ";
					$cclass[$choose_area] = " class='fred'";
					$hid_city = $city_id;
					$hid_area = $choose_area;
				}
			}
		}
		
				$where .= " and a.verify = 1 ";
		$param = array(
			'where'=>$where,
			'order'=>'order by a.posttime DESC , a.app_num DESC ',
			'limit'=>'',
			'page'=>true,
			'perpage'=>10,
			'page_url'=>"index.php?mod=event&province=$choose_province&city=$choose_city&area=$choose_area&type=$type&nocity=$nocity",
		);
		$return = array();
		$return = $EventLogic->getEventInfo($param);
		$count = $return['count'] ? $return['count'] : 0;
		$event = $return['event_list'];
		$page_arr = $return['page'];
		$app = $return['app'];

			$member = $this->Member;
			if ($member['medal_id']) {
				$medal_list = $this->TopicLogic->GetMedal($member['medal_id'],$member['uid']);
			}


		$this->Title = "活动";
		include($this->TemplateHandler->Template('event'));
	}
	
	
	function getAllUser(){
		load::logic('event');
		$EventLogic = new EventLogic();
		
		$id = (int) $this->Get['id'];
		$type = $this->Get['type'];
		$title = $this->DatabaseHandler->ResultFirst("select `title` from `".TABLE_PREFIX."event` where `id` = '$id'");
		
		$param = array(
			'limit'=>'',
			'page'=>true,
			'perpage'=>20,
			'page_url'=>"index.php?mod=event&code=alluser&id=$id&type=$type",
		);
				if($type == 'app'){
			$param['where'] = " a.id = '$id' and a.app = 1 and a.play = 0 ";
			$param['order'] = " order by a.app_time ";
			$name = "报名";
				}else{
			$param['where'] = " a.id = '$id' and a.play = 1 ";
			$param['order'] = " order by a.play_time ";
		    $name = "参与";
		}
		
		$return = array();
		if($return = $EventLogic->getAllUser($param,$type)){
			extract($return);
		}

			$my_member = $this->Member;
			if ($my_member['medal_id']) {
				$medal_list = $this->TopicLogic->GetMedal($my_member['medal_id'],$my_member['uid']);
			}

		$this->Title = $title."的所有".$name."者";
		include($this->TemplateHandler->Template('event_member'));
	}
	
	
	function delevent(){
		$id = (int) $this->Get['id'];
		if($id < 1){
			$this->Messager("请选择要删除的活动",-1);
		}
		
		load::logic('event');
		$eventLogic = new EventLogic();
		$return = $eventLogic->delEvent($id);
		if($return == 1){
			$this->Messager("该活动已有报名者，您不能删除该活动，如要删除，请联系管理员",-1);
		}elseif($return == 2){
			$this->Messager("活动不存在或已删除",-1);
		}else{
			$this->Messager("删除活动成功","index.php?mod=event&code=myevent");
		}
	}
	
	
	function ExportToExcel(){
		$id = intval($this->Get['id']);
		$list = array();

		load::logic('event');
		$EventLogic = new EventLogic();
		$param = array(
			'where'=>" a.id = '$id' ",
		);
		$return = $EventLogic->getEventInfo($param);
		$event = $return['event_list'][$id];
		
				$list[0][0] = '标题:';
		$list[0][1] = $event['title'];
				$list[1][0] = '时间:';
		$list[1][1] = $event['fromt']."-".$event['tot'];
				$list[2][0] = '地点:';
		$list[2][1] = $event['province'].$event['city'].$event['area'].$event['address'];
				$list[3][0] = '发起人:';
		$list[3][1] = $event['nickname'];
				$list[4][0] = '参与人数:';
		$list[4][1] = $event['play_num']."人";
		
		$list[5] = array();
		
				$list[6][0] = '申请者';
		$j = 1;
		foreach (unserialize($event['need_app_info']) as $value) {
			$list[6][$j] = $value['name'];
			$j++;
		}
		$list[6][$j] = '留言';

		$i = 7;
		$param = array(
			'where' => " id = '$id' and play = 1 ",
			'order' => " order by a.play_time ",
		);
		$member = $EventLogic->getAllUser($param);
		$member_list = (array) $member['member'];
		foreach($member_list as $member){
			$app_info = (array) unserialize($member['app_info']);
			$list[$i][0] = $member['nickname'];
			$k = 1;
			foreach ($app_info as $value) {
				$list[$i][$k] = $value;
				$k++;
			}
			$i++;
			
		}
		Load::lib('php-excel');
		$xls = new Excel_XML($this->Config['charset']);		
		$xls->addArray($list);
		$xls->generateXML('event-'.date("YmdHis"));
		exit;
	}
	
	
	function eventDetail(){
	    $id = intval($this->Get['id']);
	    
		load::logic('event');
		$EventLogic = new EventLogic();
		$param = array(
			'where'=>" a.id = '$id' ",
		);

	    		$return = $EventLogic->getEventInfo($param);
		$rs = $return['event_list'][$id];

	    if(!$rs){
	    	$this->Messager("活动不存在或已删除",-1);
	    }

		if(!$rs['verify'] || $rs['verify'] == 0){
			if($rs['postman'] != MEMBER_ID){
	    		$this->Messager("活动还在审核中",-1);
			}
	    }
		
	    $from = array();
	    if($rs['item'] == 'qun' && $rs['item_id'] > 0){
	    	$rs['qunname'] = DB::Result_first(" select `name` from `".TABLE_PREFIX."qun` where `qid` = '$rs[item_id]' ");
	    	$from['name'] = '群--'.$rs['qunname'];
	    	$from['url'] = get_full_url('','index.php?mod=qun&qid='.$rs['item_id']);
	    }
	    else
	    {
	        #if NEDU
		    if (defined('NEDU_MOYO'))
    	    {
    	        if ($rs['item'] && $rs['item_id'])
    	        {
    	            $app = nlogic('com.object')->get_info($rs['item'], $rs['item_id']);
    	            if ($app)
    	            {
    	                $from = array(
    	                    'name' => $app['object_name'],
    	                    'url' => $app['object_url']
    	                );
    	            }
    	        }
    	    }
    	    #endif
	    }
	    
	    	    $app_member_arr = $EventLogic->getAllUser(array('where'=>" a.id = '$id' and a.app = 1 and a.play = 0 ",'order'=>" order by a.app_time ",'limit'=>" limit 6 "),'app');
	    $app_count = $app_member_arr['count'];
	    $app_member = $app_member_arr['member'];
	    
	    	    $play_member_arr = $EventLogic->getAllUser(array('where'=>" a.id = '$id' and a.play = 1  ",'order'=>" order by a.play_time ",'limit'=>" limit 6 "),'play');
	    $play_count = $play_member_arr['count'];
	    $play_member = $play_member_arr['member'];
	  
    	$member = $this->Member;
	    if ($member['medal_id']) {
			$medal_list = $this->TopicLogic->GetMedal($member['medal_id'],$member['uid']);
		}
	    
	    		Load::functions('app');
		$gets = array(
			'mod' => 'event',
			'code' => "detail",
			'id' => $id,
		);
		$page_url = 'index.php?'.url_implode($gets);

		$options = array(
			'page' => true,
			'perpage' => 5,				'page_url' => $page_url,
		);
		$topic_info = app_get_topic_list('event', $id, $options);
		$topic_list = array();
		if (!empty($topic_info)) {
			$topic_list = $topic_info['list'];
			$page_arr['html'] = $topic_info['page']['html'];
			$no_from = true;
		}
	    
				$this->item = 'event';
		$this->item_id = $id;

				$set_qun_closed = 1;
		$set_event_closed = 1;
		$set_fenlei_closed = 1;
	    $this->Title = $rs['title'];
		include($this->TemplateHandler->Template('event_dateil'));
	}
	
	
	function myevent(){
		load::logic('event');
		$EventLogic = new EventLogic();
		
		$hot_event = $EventLogic->getHotEvent();
		
				$uids = array();
		if($uids = $EventLogic->getDaRen()){
			$hd_daren = $this->TopicLogic->GetMember($uids);
		}

	    $type = $this->Get['type'];
	    $uid = intval($this->Get['uid']);
	    	    if($uid && $uid != MEMBER_ID){
	    	$user = $this->DatabaseHandler->ResultFirst("select `nickname` from `".TABLE_PREFIX."members` where `uid` = '$uid'");
	    }else{
	    	$user = "我";
	    }
	    $uid = ($uid && $uid != MEMBER_ID) ? $uid : MEMBER_ID;
	    
	    $param = array('perpage'=>"10",'page'=>true,);
	    $return = array();
		if($type == 'part'){
			$this->Title = $user."参与的活动";
			$param['where'] = " m.play = 1 and m.fid = '$uid' ";
			$param['order'] = " order by a.lasttime desc,a.app_num desc,a.posttime desc ";
			$param['page_url'] = "index.php?mod=event&code=myevent&type=part&uid=$uid";
			$return = $EventLogic->getEvents($param);			
				}else if($type == 'app'){
			$this->Title = $user."报名的活动";
			$param['where'] = " m.app = 1 and m.fid = '$uid' ";
			$param['order'] = " order by a.lasttime desc,a.app_num desc,a.posttime desc ";
			$param['page_url'] = "index.php?mod=event&code=myevent&type=app&uid=$uid";
			$return = $EventLogic->getEvents($param);	
				}else if($type == 'store'){
			$this->Title = $user."收藏的活动";
			$param['where'] = " m.store = 1 and m.fid = '$uid' ";
			$param['order'] = " order by a.lasttime desc,a.app_num desc,a.posttime desc ";
			$param['page_url'] = "index.php?mod=event&code=myevent&type=store&uid=$uid";
			$return = $EventLogic->getEvents($param);	
				} else if ($type == 'new'){
						$this->DatabaseHandler->Query("update ".TABLE_PREFIX."members set event_new = 0 where uid = '$uid'");
			$this->MemberHandler->MemberFields['event_new'] = 0;
			
			$this->Title = "最近更新的活动";
			$param['uid'] = $uid;
			$param['page_url'] = "index.php?mod=event&code=myevent&type=new";
			$return = $EventLogic->getNewEvent($param);	
			
				}else{
			$this->Title = $user."的活动";
			$param['where'] = " a.postman = '$uid' and a.verify = 1 ";
			$param['order'] = " order by a.lasttime desc,a.app_num desc,a.posttime desc ";
			$param['page_url'] = "index.php?mod=event&code=myevent&uid=$uid";
			$return = $EventLogic->getEventInfo($param);	
		}
		$rs = $return['event_list'];
		$count = $return['count'];
		$page_arr = $return['page'];

			$member = $this->Member;
			if ($member['medal_id']) {
				$medal_list = $this->TopicLogic->GetMedal($member['medal_id'],$member['uid']);
			}
		
		include($this->TemplateHandler->Template('my_event'));
	}
	
	
	function followevent(){
		load::logic('event');
		$EventLogic = new EventLogic();
		
		$hot_event = $EventLogic->getHotEvent();
		
				$uids = array();
		if($uids = $EventLogic->getDaRen()){
			$hd_daren = $this->TopicLogic->GetMember($uids);
		}
		
				$buddyids = get_buddyids(MEMBER_ID);
		if($buddyids){
			$uid_list = implode(',', $buddyids);
		}
		
		$type = $this->Get['type'];
		
		$param = array(
			'perpage'=>"10",
			'page'=>true,
		);
		$return = array();
		if($uid_list){
						if($type == 'part'){
				$param['where'] = " m.play = 1 and m.fid in ($uid_list) ";
				$param['order'] = " order by a.lasttime desc,a.app_num desc,a.posttime desc ";		
				$param['page_url'] = "index.php?mod=event&code=followevent&type=part";
				$this->Title = "他们参与的活动";
				$return = $EventLogic->getEvents($param);
						}else{
				$param['where'] = " a.postman in ($uid_list) and a.verify = 1 ";
				$param['order'] = " order by a.lasttime desc,a.app_num desc,a.posttime desc ";		
				$param['page_url'] = "index.php?mod=event&code=followevent";
				$page_url = "index.php?mod=event&code=followevent";
				$this->Title = "他们管理的活动";
				$return = $EventLogic->getEventinfo($param);
			}
			if($return){
				extract($return);
				$rs = $event_list;
			}
	    }else{
	    	$this->Messager("你没有关注的人哦、可以通过找人来关注你感兴趣的人","index.php?mod=profile&code=search");
	    }

			$member = $this->Member;
			if ($member['medal_id']) {
				$medal_list = $this->TopicLogic->GetMedal($member['medal_id'],$member['uid']);
			}
		
		include($this->TemplateHandler->Template('follow_event'));
	}
	
	
	function pevent(){
		if(MEMBER_ID < 1){
			$this->Messager("你需要先登录才能继续本操作", 'index.php?mod=login');	
		}		
		
				if (MEMBER_ROLE_TYPE != 'admin') {
			load::logic('event');
			$EventLogic = new EventLogic();
			$is_allowed = $EventLogic->allowedCreate(MEMBER_ID,$this->Member);
		}
		if($is_allowed){
			$this->Messager($is_allowed);
		}
		
				$free = 'checked';
		$all = 'checked';
		$info = array();
	
				$need_info = $this->DatabaseHandler->ResultFirst("select `need_info` from `".TABLE_PREFIX."event_info`");
		$info = unserialize($need_info);

				Load::lib('form');
		$FormHandler = new FormHandler();
		$query = $this->DatabaseHandler->Query("select * from `".TABLE_PREFIX."event_sort` order by `id` ");
		while ($rsdb = $query->GetRow()){
			$rs[$rsdb['id']]['value'] = $rsdb['id'];
			$rs[$rsdb['id']]['name'] = $rsdb['type'];
		}
		$event_type = $FormHandler->Select("type",$rs,$val['type_id']);

				if($this->Member['province']){
			$province_id = $this->DatabaseHandler->ResultFirst("select `id` from ".TABLE_PREFIX."common_district where name = '".$this->Member['province']."' and `upid` = '0'");
		}else{
			$province_id = $this->DatabaseHandler->ResultFirst("select `id` from `".TABLE_PREFIX."common_district` where `upid` = 0 order by `list`");
		}
		$query = $this->DatabaseHandler->Query("select * from `".TABLE_PREFIX."common_district` where `upid` = 0 order by `list`");
		while ($rsdb = $query->GetRow()){
			$province[$rsdb['id']]['value']  = $rsdb['id'];
			$province[$rsdb['id']]['name']  = $rsdb['name'];
		}
		$hid_province = $province_id;
		$province_list = $FormHandler->Select("province",$province,$province_id,"onchange=\"changeProvince();\"");

				if($this->Member['city']){
			$city_id = $this->DatabaseHandler->ResultFirst("select id from ".TABLE_PREFIX."common_district where name = '".$this->Member['city']."' and upid= '$province_id'");
			$where_city = " and upid = '$city_id'";
		}
		$hid_city = $city_id;
				if($this->Member['area']){
			$area_id = $this->DatabaseHandler->ResultFirst("select id from ".TABLE_PREFIX."common_district where name = '".$this->Member['area']."' $where_city");
		}
		$hid_area = $area_id;
		
				$fromt = $edit_fromt ? $edit_fromt : my_date_format(TIMESTAMP, 'Y-m-d');
		$edit_fromt_h = $edit_fromt_h ? $edit_fromt_h : false;
		$edit_fromt_i = $edit_fromt_i ? $edit_fromt_i : false;
		$hour_select_from = mk_time_select('hour',$edit_fromt_h,'hour_select_from');
		$min_select_from =  mk_time_select('min',$edit_fromt_i,'min_select_from');

		$tot = $edit_tot ? $edit_tot : my_date_format(TIMESTAMP+7*24*3600, 'Y-m-d');
		$edit_tot_h = $edit_tot_h ? $edit_tot_h : false;
		$edit_tot_i = $edit_tot_i ? $edit_tot_i : false;
		$hour_select_to = mk_time_select('hour',$edit_tot_h,'hour_select_to');
		$min_select_to =  mk_time_select('min',$edit_tot_i,'min_select_to');
		
			$member = $this->Member;
			if ($member['medal_id']) {
				$medal_list = $this->TopicLogic->GetMedal($member['medal_id'],$member['uid']);
			}
		
		$this->Title = "发起活动";
		include($this->TemplateHandler->Template('event_create'));
	}
	
	
	function editEvent(){
		$id =  (int) $this->Get['id'];
		$postman = $this->DatabaseHandler->ResultFirst("select postman from ".TABLE_PREFIX."event where id = '$id'");
		if($postman != MEMBER_ID){
			$this->Messager("你无权修改该活动");
		}
				$free = 'checked';
		$all = 'checked';
		$info = array();
	
				$need_info = $this->DatabaseHandler->ResultFirst("select `need_info` from ".TABLE_PREFIX."event_info");
		$info = unserialize($need_info);

		$val =array();
		if($id){
			$act = "edit";
			$query = $this->DatabaseHandler->Query("select * from `".TABLE_PREFIX."event` where `id` = '$id'");
			$val = $query->GetRow();
			$item_id = $val['item_id'];
			$title = $val['title'];
			$moneys = $val['money'];

			$content = $val['content'];
			$address = $val['address'];
			if($val['image']){
				$image = $val['image'];
			}
						$edit_fromt = date("Y-m-d",$val['fromt']);
						$edit_fromt_h = date("H",$val['fromt']);
						$edit_fromt_i = date("i",$val['fromt']);
						$edit_tot = date("Y-m-d",$val['tot']);
						$edit_tot_h = date("H",$val['tot']);
						$edit_tot_i = date("i",$val['tot']);
						if($val['money']){
				$money = 'checked';
				$free = '';
			}else{
				$money = '';
			    $free = 'checked';
			}
						$qualification  = unserialize($val['qualification']);
			if(count($qualification)){
				$qua = "checked";
				$all = "";
				if($qualification['fans_num']){
					$fans = "checked";
					$fans_num = $qualification['fans_num'];
				}
				if($qualification['same_city']){
					$same_city = "checked";
				}
				if($qualification['inqun']){
					$inqun = " checked ";
				}
			}
						$need_app_info = unserialize($val['need_app_info']);
			if(count($need_app_info)){
				foreach ($need_app_info as $value) {
					$info[$value['id']][$value['ename']] = "checked";
				}
			}
		}

				Load::lib('form');
		$FormHandler = new FormHandler();
		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."event_sort order by `id`");
		while ($rsdb = $query->GetRow()){
			$rs[$rsdb[id]]['value'] = $rsdb['id'];
			$rs[$rsdb[id]]['name'] = $rsdb['type'];
		}
		$event_type = $FormHandler->Select("type",$rs,$val['type_id']);

				$query = $this->DatabaseHandler->Query("select * from `".TABLE_PREFIX."common_district` where `upid` = 0 order by `list`");
		while ($rsdb = $query->GetRow()){
			$province[$rsdb['id']]['value']  = $rsdb['id'];
			$province[$rsdb['id']]['name']  = $rsdb['name'];
		}
		$province_id = $val['province_id'];
		$hid_province = $province_id;
		$province_list = $FormHandler->Select("province",$province,$province_id,"onchange=\"changeProvince();\"");

				$city_id = $val['city_id'];
		$hid_city = $city_id;
				$area_id = $val['area_id'];
		$hid_area = $area_id;
		
				$fromt = $edit_fromt ? $edit_fromt : my_date_format(TIMESTAMP, 'Y-m-d');
		$edit_fromt_h = $edit_fromt_h ? $edit_fromt_h : false;
		$edit_fromt_i = $edit_fromt_i ? $edit_fromt_i : false;
		$hour_select_from = mk_time_select('hour',$edit_fromt_h,'hour_select_from');
		$min_select_from =  mk_time_select('min',$edit_fromt_i,'min_select_from');

		$tot = $edit_tot ? $edit_tot : my_date_format(TIMESTAMP+7*24*3600, 'Y-m-d');
		$edit_tot_h = $edit_tot_h ? $edit_tot_h : false;
		$edit_tot_i = $edit_tot_i ? $edit_tot_i : false;
		$hour_select_to = mk_time_select('hour',$edit_tot_h,'hour_select_to');
		$min_select_to =  mk_time_select('min',$edit_tot_i,'min_select_to');

			$member = $this->Member;
		
		$this->Title = "修改活动";
		include($this->TemplateHandler->Template('event_create'));
	}
	
	
	function _member()
	{		
		$member = jsg_member_info(MEMBER_ID);
		
		return $member;
	}
}