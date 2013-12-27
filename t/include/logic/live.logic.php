<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename live.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-04 18:49:37 1222698574 2137154504 13264 $
 *******************************************************************/




if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class LiveLogic
{
	var $mybuddys;
	function LiveLogic()
	{
		$this->mybuddys = is_array(get_buddyids(MEMBER_ID)) ? get_buddyids(MEMBER_ID) : array(get_buddyids(MEMBER_ID));
		$this->TopicLogic = Load::logic('topic', 1);
	}
	
	
	function get_list($param)
	{
		$live_list = array();
		$lids = array();		$guestall = array();		$time = time();
		extract($param);
		$limit_sql = '';
		$order_sql = '';
		$where_sql = ' WHERE 1=1 ';
		if ($where) {
			$where_sql .= ' AND '.$where;
		}
		if ($order) {
			$order_sql .= $order;
		}
		if ($limit) {
			$limit_sql = ' LIMIT '.$limit;
		}
		$total_record = DB::result_first("SELECT COUNT(*) FROM ".DB::table('live')." {$where_sql}");
		if ($total_record > 0) {
			if ($param['perpage']) {
				$page_arr = page($total_record, $param['perpage'], $param['page_url'], array('return'=>'array'));
				$limit_sql = $page_arr['limit'];
			} else {
				if ($param['limit']) {
					$limit_sql = ' LIMIT '.$param['limit'];
				}
			}
			$query = DB::query("SELECT * FROM ".DB::table('live')." {$where_sql} {$order_sql} {$limit_sql}");
			while($value = DB::fetch($query)) {
				if($value['starttime'] > $time){
					$value['status_css'] = 'ico_notyet';
					$value['status'] = '未开始';
				}elseif($value['endtime'] < $time){
					$value['status_css'] = 'ico_complete';
					$value['status'] = '已完成';
				}else{
					$value['status_css'] = 'ico_ongoing';
					$value['status'] = '进行中';
				}
				$value['datetime'] = date('Y-m-d H:i',$value['starttime']).' - '.date('Y-m-d H:i',$value['endtime']);
				if(date('Y-m-d',$value['starttime']) != date('Y-m-d',$value['endtime'])){
					$value['ldate'] = date('m-d H:i',$value['starttime']).'—'.date('m-d H:i',$value['endtime']);
				}else{
					$value['ldate'] = date('Y-m-d H:i',$value['starttime']).'-'.date('H:i',$value['endtime']);
				}
				$value['shortname'] = cut_str($value['livename'], 18);
				$live_list[$value['lid']] = $value;
				$lids[]=$value['lid'];
			}
			$guestall = $this->Getguest($lids);
			foreach($live_list as $key => $val){
				$live_list[$key] = array_merge($live_list[$key],$guestall[$key]);
			}
			$info = array(
				'list' => $live_list,
				'count' => $total_record,
				'page' => $page_arr,
			);
			return $info;
		}
	}

	
	function get_user($itemid=0,$type='guest')
	{
		$list = $this->Getguest($itemid);
		return $list[$itemid][$type];
	}

	
	function get_users($type='',$itemid=0,$limit=3)
	{
		$list = $this->Getguest($itemid);
		return $list[$itemid][$type];
	}

	
	function get_liveinfo($lid,$list = array())
	{
		$live = DB::fetch_first("SELECT * FROM ".DB::table('live')." WHERE lid='{$lid}'");
		$list = empty($list) ? $this->Getguest($lid) : $list;
		foreach($list[$lid] as $key => $val){
			if($key != 'host_guest' && !empty($val) && $key != 'all'){
				foreach($val as $k => $v){
					$list[$lid][$key][$k]['followed'] = $this->is_followed($v['uid']);
				}
			}
			$live[$key] = $list[$lid][$key];
		}
		$live['starttime'] = date('Y-m-d H:i',$live['starttime']);
		$live['endtime'] = date('Y-m-d H:i',$live['endtime']);
		return $live;
	}
	function id2liveinfo($lid,$list = array())
	{
		$time = time();
		$live = DB::fetch_first("SELECT * FROM ".DB::table('live')." WHERE lid='{$lid}'");
		if(date('Y-m-d',$live['starttime']) != date('Y-m-d',$live['endtime'])){
			$live['date'] = date('m月d',$live['starttime']).'—'.date('m月d',$live['endtime']);
		}else{
			$live['date'] = date('Y年m月d日',$live['starttime']);
		}
		$live['time'] = date('H:i',$live['starttime']).'-'.date('H:i',$live['endtime']);
		if($live['starttime'] > $time){
			$live['status_css'] = 'ico_notyet';
			$live['clock_css'] = 'ico_clock_normal';
			if($this->is_design(MEMBER_ID,$live['lid'])){
				$live['btn_css'] = 'btn_dzwc';
			}else{
				$live['btn_css'] = 'btn_wydz';
			}
			$live['status'] = '未开始';
			$live['str'] = '定制';
			$live['banner'] = '预告';
		}elseif($live['endtime'] < $time){
			$live['status_css'] = 'ico_complete';
			$live['clock_css'] = 'ico_clock_gray';
			$live['btn_css'] = 'btn_wyfx';
			$live['status'] = '已完成';
			$live['str'] = '分享';
			$live['banner'] = '回顾';
		}else{
			$live['status_css'] = 'ico_ongoing';
			$live['clock_css'] = 'ico_clock_on';
			$live['btn_css'] = 'btn_wycy';
			$live['status'] = '进行中';
			$live['str'] = '参与';
			$live['banner'] = '进行时';
		}
		$list = empty($list) ? $this->Getguest($lid) : $list;
		foreach($list[$lid] as $key => $val){
			if($key != 'host_guest' && !empty($val) && $key != 'all'){
				foreach($val as $k => $v){
					$list[$lid][$key][$k]['followed'] = $this->is_followed($v['uid']);
				}
			}
			$live[$key] = $list[$lid][$key];
		}
		$live['starttime'] = date('Y-m-d H:i',$live['starttime']);
		$live['endtime'] = date('Y-m-d H:i',$live['endtime']);
		return $live;
	}
	
	
	function id2subject($lid)
	{
		static $livename;
		if($livename[$lid]){
			$subject = $livename[$lid];
		}else{
			$subject = DB::result_first("SELECT livename FROM ".DB::table('live')." WHERE lid='{$lid}' ");
			$livename[$lid] = $subject;
		}
		return $subject;
	}

	
	function id2usertype($lid=0,$uid=0,$list = array())
	{
		$list = empty($list) ? $this->Getguest($lid) : $list;
		foreach($list[$lid]['all'] as $k => $v){
			if($k == $uid){
				$return = $v;
				break;
			}
		}
		return $return;
	}
	
	
	function is_exists($lid)
	{
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('live')." WHERE lid='{$lid}'");
		return $count > 0 ? true : false;
	}

	
	function is_design($uid=0,$itemid=0)
	{
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('item_sms')." WHERE uid='{$uid}' AND item='live' AND itemid='$itemid'");
		return $count;
	}

	
	function is_followed($buid=0)
	{
				return in_array($buid,$this->mybuddys);
	}
	
	
	function create($post)
	{	
		$setarr = array(
			'livename' => $post['livename'],
			'description' => $post['description'],
			'image' => $post['image'],
			'starttime' => strtotime($post['starttime']),
			'endtime' => strtotime($post['endtime']),
		);		
		$lid = DB::insert('live', $setarr, true);
		if($lid && !empty($_FILES['image']['name'])){
						$this->upload_pic($_FILES,$lid);
		}
		return ($lid ? $lid : 0);
	}

	
	function adduser($item='live',$type='guest',$itemid=0,$uid=0,$userabout='')
	{	
		$setuser = array(
			'item' => $item,
			'type' => $type,
			'itemid' => $itemid,
			'uid' => $uid,
			'description' => $userabout,
		);		
		DB::insert('item_user', $setuser, true);
	}
	
	
	function modify($post)
	{
		$setarr = array(
			'livename' => $post['livename'],
			'description' => $post['description'],
			'image' => $post['image'],
			'starttime' => strtotime($post['starttime']),
			'endtime' => strtotime($post['endtime']),
		);
		$return = DB::update('live', $setarr, array('lid' => jget('lid','int','P')));
		if($return && !empty($_FILES['image']['name'])){
						$this->upload_pic($_FILES,jget('lid','int','P'));
		}
		return ($return ? $return : 0);
	}
	
	
	function delete($ids)
	{
		if (!is_array($ids)) {
			$ids = (array)$ids;
		}
				DB::query("DELETE FROM ".DB::table('item_user')." WHERE item = 'live' AND itemid IN (".jimplode($ids).")");
				DB::query("DELETE FROM ".DB::table('item_sms')." WHERE item = 'live' AND itemid IN (".jimplode($ids).")");
				DB::update('topic', array('item'=>'','item_id'=>0), "`tid` IN (SELECT tid FROM ".DB::table('topic_live')." WHERE item_id IN (".jimplode($ids)."))");
		DB::update('topic', array('type'=>'first'), "`tid` IN (SELECT tid FROM ".DB::table('topic_live')." WHERE item_id IN (".jimplode($ids).")) AND `type` = 'live'");
				DB::query("DELETE FROM ".DB::table('topic_live')." WHERE item_id IN (".jimplode($ids).")");
				$polls = DB::query("DELETE FROM ".DB::table('live')." WHERE lid IN (".jimplode($ids).")");
		return $polls;
	}

	
	function dopost($post,$type='')
	{
		if(empty($post['livename'])){
			$return = "直播主题不能为空";
		}elseif(empty($post['description'])){
			$return = "直播说明不能为空";
		}elseif(empty($post['starttime'])){
			$return = "直播开始时间不能为空";
		}elseif(empty($post['endtime'])){
			$return = "直播结束时间不能为空";
		}elseif(strtotime($post['starttime']) >= strtotime($post['endtime'])){
			$return = "直播结束时间不能早于开始时间";
		}elseif(empty($post['old_uid_host']) && empty($post['uid_host'])){
			$return = "直播主持人不能为空";
		}elseif(empty($post['old_uid_guest']) && empty($post['uid_guest'])){
			$return = "直播嘉宾不能为空";
		}else{
			if($type == 'edit'){
				$this->modify($post);
				$lid = jget('lid','int','P');
			}else{
				$lid = $this->create($post);
			}
			if(isset($post['userabout_host']) && $post['uid_host'] && is_array($post['uid_host'])){
				foreach($post['userabout_host'] as $key => $value){
					$this->adduser('live','host',$lid,$post['uid_host'][$key],$value);
				}
			}
			if(isset($post['userabout_guest']) && $post['uid_guest'] && is_array($post['uid_guest'])){
				foreach($post['userabout_guest'] as $key => $value){
					$this->adduser('live','guest',$lid,$post['uid_guest'][$key],$value);
				}
			}
			if(isset($post['userabout_media']) && $post['uid_media'] && is_array($post['uid_media'])){
				foreach($post['userabout_media'] as $key => $value){
					$this->adduser('live','media',$lid,$post['uid_media'][$key],$value);
				}
			}
			if($type == 'edit'){
				$return = "直播修改成功";
			}else{
				$return = "直播添加成功";
			}			
		}
		return $return;
	}

	function upload_pic($_FILES,$id){
		
		$image_name = $id.".png";
		$image_path = RELATIVE_ROOT_PATH . 'images/live/'.face_path($id);
		$image_file = $image_path . $image_name;

		if (!is_dir($image_path))
		{
			Load::lib('io', 1)->MakeDir($image_path);
		}
		Load::lib('upload');
		$UploadHandler = new UploadHandler($_FILES,$image_path,'image',true);
		$UploadHandler->setMaxSize(1000);
		$UploadHandler->setNewName($image_name);
		$result=$UploadHandler->doUpload();

		if($result)
        {
			$result = is_image($image_file);
		}
		if(!$result)
        {
			unlink($image_file);
			return false;
		}else{
			DB::update('live', array('image' => $image_file), array('lid' => $id));
		}
		return true;
	}

		function Getguest($ids)
	{
		$list = array();$uids = array();$guests = array();
		$ids = is_array($ids) ? $ids : array((int)$ids);
		$query = DB::query("SELECT iid,itemid,uid,description,type FROM ".DB::table('item_user')." WHERE item = 'live' AND itemid IN(".jimplode($ids).")");
		while($row = DB::fetch($query)) {
			$uids[] = $row['uid'];
			$guests[$row['itemid']][$row['uid']]['type'] = $row['type'];
			$guests[$row['itemid']][$row['uid']]['iid'] = $row['iid'];
			$guests[$row['itemid']][$row['uid']]['description'] = $row['description'];
		}
		$uids = array_unique($uids);
		$users = $this->TopicLogic->GetMember($uids, "`uid`,`ucuid`,`username`,`nickname`,`face`,`fans_count`,`validate`,`validate_category`");
		foreach($guests as $key => $val ){
			$guests_g = array();			$guests_h = array();			$guests_m = array();			$guests_hg = array();			$guests_a = array();			foreach($val as $k => $v ){
				$guests_a[$key][$k] = $guests[$key][$k]['type'];
				if($guests[$key][$k]['type'] == 'guest'){
					unset($guests[$key][$k]['type']);
					$guests_g[$key][$k] = array_merge($users[$k],$guests[$key][$k]);
					$guests_hg[$key][$k] = $users[$k]['nickname'];
				}elseif($guests[$key][$k]['type'] == 'host'){
					unset($guests[$key][$k]['type']);
					$guests_h[$key][$k] = array_merge($users[$k],$guests[$key][$k]);
					$guests_hg[$key][$k] = $users[$k]['nickname'];
				}elseif($guests[$key][$k]['type'] == 'media'){
					unset($guests[$key][$k]['type']);
					$guests_m[$key][$k] = array_merge($users[$k],$guests[$key][$k]);
				}
			}
			$list[$key]['guest'] = $guests_g[$key];
			$list[$key]['host'] = $guests_h[$key];
			$list[$key]['media'] = $guests_m[$key];
			$list[$key]['host_guest'] = $guests_hg[$key];
			$list[$key]['all'] = $guests_a[$key];
		}
		return $list;
	}
}
?>