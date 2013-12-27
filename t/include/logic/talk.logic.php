<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename talk.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-04 18:49:37 1916894231 1826403843 20305 $
 *******************************************************************/




if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class TalkLogic
{
	var $mybuddys;
	function TalkLogic()
	{
		$this->mybuddys = is_array(get_buddyids(MEMBER_ID)) ? get_buddyids(MEMBER_ID) : array(get_buddyids(MEMBER_ID));
		$this->TopicLogic = Load::logic('topic', 1);
	}
	
	
	function get_list($param)
	{
		$talk_list = array();
		$lids = array();		$uids = array();		$guests = array();		$time = time();
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
		$total_record = DB::result_first("SELECT COUNT(*) FROM ".DB::table('talk')." {$where_sql}");
		if ($total_record > 0) {
			if ($param['perpage']) {
				$page_arr = page($total_record, $param['perpage'], $param['page_url'], array('return'=>'array'));
				$limit_sql = $page_arr['limit'];
			} else {
				if ($param['limit']) {
					$limit_sql = ' LIMIT '.$param['limit'];
				}
			}
			$query = DB::query("SELECT * FROM ".DB::table('talk')." {$where_sql} {$order_sql} {$limit_sql}");
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
				$value['shortname'] = cut_str($value['talkname'], 18);
				$talk_list[$value['lid']] = $value;
				$lids[]=$value['lid'];
			}
			$guestall = $this->Getguest($lids);
			foreach($talk_list as $key => $val){
				$talk_list[$key] = array_merge($talk_list[$key],$guestall[$key]);
			}
			$info = array(
				'list' => $talk_list,
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

	
	function get_talkinfo($lid,$list = array())
	{
		$talk = DB::fetch_first("SELECT * FROM ".DB::table('talk')." WHERE lid='{$lid}'");
		$list = empty($list) ? $this->Getguest($lid) : $list;
		foreach($list[$lid] as $key => $val){
			if($key != 'host_guest' && !empty($val) && $key != 'all'){
				foreach($val as $k => $v){
					$list[$lid][$key][$k]['followed'] = $this->is_followed($v['uid']);
				}
			}
			$talk[$key] = $list[$lid][$key];
		}
		$talk['starttime'] = date('Y-m-d H:i',$talk['starttime']);
		$talk['endtime'] = date('Y-m-d H:i',$talk['endtime']);
		return $talk;
	}
	function id2talkinfo($lid,$list = array())
	{
		$time = time();
		$talk = DB::fetch_first("SELECT * FROM ".DB::table('talk')." WHERE lid='{$lid}'");
		if(date('Y-m-d',$talk['starttime']) != date('Y-m-d',$talk['endtime'])){
			$talk['time'] = "<br>".date('m月d日H:i',$talk['starttime'])."—".date('m月d日H:i',$talk['endtime']);
		}else{
			$talk['time'] = date('m月d日',$talk['starttime']).'&nbsp;'.date('H:i',$talk['starttime']).'-'.date('H:i',$talk['endtime']);
		}
		if($talk['starttime'] > $time){
			$talk['status_css'] = 'ico_notyet';
			$talk['img_css'] = 'info_img1';
			if($this->is_design(MEMBER_ID,$talk['lid'])){
				$talk['design'] = '已经定制';
			}else{
				$talk['design'] = '定制访谈提醒';
			}
			$talk['btn_css'] = 'forange';
			$talk['status'] = '未开始';
		}elseif($talk['endtime'] < $time){
			$talk['status_css'] = 'ico_complete';
			$talk['img_css'] = 'info_img3';
			$talk['btn_css'] = 'gray';
			$talk['status'] = '已完成';
		}else{
			$talk['status_css'] = 'ico_ongoing';
			$talk['img_css'] = 'info_img2';
			$talk['btn_css'] = 'forange';
			$talk['status'] = '进行中';
		}
				
		$list = empty($list) ? $this->Getguest($lid) : $list;
		foreach($list[$lid] as $key => $val){
			if($key != 'host_guest' && !empty($val) && $key != 'all'){
				foreach($val as $k => $v){
					$list[$lid][$key][$k]['followed'] = $this->is_followed($v['uid']);
				}
			}
			$talk[$key] = $list[$lid][$key];
			
		}
		$talk['starttime'] = date('Y-m-d H:i',$talk['starttime']);
		$talk['endtime'] = date('Y-m-d H:i',$talk['endtime']);
		$talk['guests_num'] = count($talk['guest']);
		return $talk;
	}
	
	
	function id2subject($lid)
	{
		static $talkname;
		if($talkname[$lid]){
			$subject = $talkname[$lid];
		}else{
			$subject = DB::result_first("SELECT talkname FROM ".DB::table('talk')." WHERE lid='{$lid}' ");
			$talkname[$lid] = $subject;
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
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('talk')." WHERE lid='{$lid}'");
		return $count;
	}

	
	function is_atme($tid)
	{
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('topic_mention')." WHERE tid='{$tid}' AND uid='".MEMBER_ID."'");
		return $count;
	}

	
	function is_design($uid=0,$itemid=0)
	{
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('item_sms')." WHERE uid='{$uid}' AND item='talk' AND itemid='$itemid'");
		return $count;
	}

	
	function is_followed($buid=0)
	{
		return in_array($buid,$this->mybuddys);
	}
	
	
	function create($post)
	{	
		$setarr = array(
			'cat_id' => $post['sub_cat'],
			'talkname' => $post['talkname'],
			'description' => $post['description'],
			'image' => $post['image'],
			'starttime' => strtotime($post['starttime']),
			'endtime' => strtotime($post['endtime']),
		);		
		$lid = DB::insert('talk', $setarr, true);
		if($lid && !empty($_FILES['image']['name'])){
						$this->upload_pic($_FILES,$lid);
		}
		return ($lid ? $lid : 0);
	}

	
	function adduser($item='talk',$type='guest',$itemid=0,$uid=0,$userabout='')
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
			'cat_id' => $post['sub_cat'],
			'talkname' => $post['talkname'],
			'description' => $post['description'],
			'image' => $post['image'],
			'starttime' => strtotime($post['starttime']),
			'endtime' => strtotime($post['endtime']),
		);
		$return = DB::update('talk', $setarr, array('lid' => jget('lid','int','P')));
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
				DB::query("DELETE FROM ".DB::table('item_user')." WHERE item = 'talk' AND itemid IN (".jimplode($ids).")");
				DB::query("DELETE FROM ".DB::table('item_sms')." WHERE item = 'talk' AND itemid IN (".jimplode($ids).")");
				DB::update('topic', array('item'=>'','item_id'=>''), "`tid` IN (SELECT tid FROM ".DB::table('topic_talk')." WHERE item_id IN (".jimplode($ids)."))");
		DB::update('topic', array('type'=>'first'), "`tid` IN (SELECT tid FROM ".DB::table('topic_talk')." WHERE item_id IN (".jimplode($ids).")) AND `type` = 'talk'");
				DB::query("DELETE FROM ".DB::table('topic_talk')." WHERE item_id IN (".jimplode($ids).")");
				DB::query("UPDATE ".DB::table('talk_category')." SET talk_num = talk_num - 1 WHERE `cat_id` IN (SELECT cat_id FROM ".DB::table('talk')." WHERE lid IN (".jimplode($ids)."))");
				$polls = DB::query("DELETE FROM ".DB::table('talk')." WHERE lid IN (".jimplode($ids).")");
		return $polls;
	}

	
	function dopost($post,$type='')
	{
		if(empty($post['talkname'])){
			$return = "访谈主题不能为空";
		}elseif(empty($post['description'])){
			$return = "访谈说明不能为空";
		}elseif(empty($post['starttime'])){
			$return = "访谈开始时间不能为空";
		}elseif(empty($post['endtime'])){
			$return = "访谈结束时间不能为空";
		}elseif(strtotime($post['starttime']) >= strtotime($post['endtime'])){
			$return = "访谈结束时间不能早于开始时间";
		}elseif(empty($post['old_uid_host']) && empty($post['uid_host'])){
			$return = "访谈主持人不能为空";
		}elseif(empty($post['old_uid_guest']) && empty($post['uid_guest'])){
			$return = "访谈嘉宾不能为空";
		}elseif(empty($post['sub_cat']) || empty($post['top_cat'])){
			$return = "访谈分类不能为空，如果没有请先添加";
		}else{
			if($type == 'edit'){
				$this->modify($post);
				$lid = jget('lid','int','P');
			}else{
				$lid = $this->create($post);
				DB::query("UPDATE ".DB::table('talk_category')." SET talk_num = talk_num + 1 WHERE `cat_id`='{$post['sub_cat']}'");
			}
			if(isset($post['userabout_host']) && $post['uid_host'] && is_array($post['uid_host'])){
				foreach($post['userabout_host'] as $key => $value){
					$this->adduser('talk','host',$lid,$post['uid_host'][$key],$value);
				}
			}
			if(isset($post['userabout_guest']) && $post['uid_guest'] && is_array($post['uid_guest'])){
				foreach($post['userabout_guest'] as $key => $value){
					$this->adduser('talk','guest',$lid,$post['uid_guest'][$key],$value);
				}
			}
			if(isset($post['userabout_media']) && $post['uid_media'] && is_array($post['uid_media'])){
				foreach($post['userabout_media'] as $key => $value){
					$this->adduser('talk','media',$lid,$post['uid_media'][$key],$value);
				}
			}
			if($type == 'edit'){
				$return = "访谈修改成功";
			}else{
				$return = "访谈添加成功";
			}
		}
		return $return;
	}

	
	function category_exists($cat_name, $pid = 0)
	{
		$count = DB::result_first("SELECT COUNT(*) 
								   FROM ".DB::table('talk_category')." 
								   WHERE cat_name='{$cat_name}' AND parent_id='{$pid}'");
		return $count > 0 ? true : false;
	}
	
	
	function id2category($cat_id)
	{
		$category = array();
		$category = DB::fetch_first("SELECT * FROM ".DB::table('talk_category')." WHERE cat_id='{$cat_id}'");
		return $category;
	}

	
	function id2cateid($cat_id)
	{
		$cateids = array();
		$pcatid = DB::result_first("SELECT parent_id FROM ".DB::table('talk_category')." WHERE cat_id='{$cat_id}'");
		if($pcatid == 0){
			$query = DB::query("SELECT cat_id FROM ".DB::table('talk_category')." WHERE parent_id = '$cat_id' ORDER BY display_order DESC");
			while($value = DB::fetch($query)) {
				$cateids[] = $value['cat_id'];
			}
			return $cateids;
		}elseif($pcatid > 0){
			return $cat_id;
		}else{
			return false;
		}
	}
	
	
	function &get_category_tree()
	{
		$tree = $cat_ary = array();
		$query = DB::query("SELECT *  
							FROM ".DB::table('talk_category')." 
							ORDER BY display_order ASC");
		while ($value = DB::fetch($query)) {
			$cat_ary[] = $value;
		}
		
		if (!empty($cat_ary)) {
			$tree = $this->category_tree($cat_ary);
		}
		return $tree;
	}
	function category_list($parent_id = 0)
	{
		$categorys = array();
		$query = DB::query("SELECT * FROM ".DB::table('talk_category')." WHERE parent_id = '$parent_id' ORDER BY display_order ASC");
		while ($value = DB::fetch($query)) {
			$categorys[] = $value;
		}
		return $categorys;
	}
	
	
	function category_tree($data, $parent_id = 0)
	{
		$tree = array();
		foreach ($data as $value) {
			if ($value['parent_id'] == $parent_id) {
				$tmp = array();
				$tmp = $value;
				$tmp['child'] = $this->category_tree($data, $value['cat_id']);
				$tree[$value['cat_id']] = $tmp;
			}
		}
		return $tree;
	}
	
	
	function add_category($cat_name, $display_order = 0, $parent_id = 0)
	{	
		$set_ary = array(
			'cat_name' => $cat_name,
			'parent_id' => $parent_id,
			'display_order' => $display_order,
		);
		$qid = DB::insert('talk_category', $set_ary, true);
		return $qid;
	}

	
	function update_category($cat_id, $cat_name, $display_order)
	{
		$set_ary = array(
			'cat_name' => $cat_name,
			'display_order' => $display_order,
		);
		DB::update('talk_category', $set_ary, array('cat_id' => $cat_id));
	}
	
	
	function delete_category($cat_id)
	{
				$category = $this->id2category($cat_id);
		if (empty($category)) {
			return -1;
		}
		
		if ($category['talk_num'] > 0) {
			return -2;
		}
		
				$sub_count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('talk_category')." WHERE parent_id='{$cat_id}'");
		if ($sub_count) {
			return -3;
		}
		
		DB::query("DELETE FROM ".DB::table('talk_category')." WHERE cat_id='{$cat_id}'");
		return 1;
	}
	
	
	function update_category_cache()
	{
		$cat_ary = array();
		$query = DB::query("SELECT * FROM ".DB::table('talk_category')." ORDER BY display_order ASC");
		while ($value = DB::fetch($query)) {
			if ($value['parent_id'] == 0) {
				$cat_ary['first'][$value['cat_id']] = $value;	
			} else {
				$cat_ary['second'][$value['cat_id']] = $value;	
			}
		}
				if (!empty($cat_ary['first'])) {
			foreach ($cat_ary['first'] as $key => $v) {
				$tmp_cat_id = $v['cat_id'];
				$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('talk_category')." WHERE parent_id='{$tmp_cat_id}'");
				if ($count < 1) {
					unset($cat_ary['first'][$key]);
				}
			}
			ConfigHandler::set('talk_category', $cat_ary);
			return $cat_ary;	
		}
		return array();
	}
	
	
	function get_category($id=0,$step='')
	{
		$cat_ary = array();
		$cat_ary = ConfigHandler::get('talk_category');
		if (empty($cat_ary)) {
			$cat_ary = $this->update_category_cache();
		}
		if($id){
			return $cat_ary[$step][$id];
		}else{
			return $cat_ary;
		}
	}
	
	
	function get_catselect($parent_id = 0, $sub_id = 0, $in_ajax = false)
	{
		$cat_ary = array();
		$cat_ary = $this->get_category();
		
		if (empty($cat_ary)) {
			return false;
		}
		
		$first_cat = $cat_ary['first'];
		$second_cat = $cat_ary['second'];
		$r = array();
		if ($parent_id == 0 && $sub_id == 0) {
			$tmp = current($first_cat);
			$parent_id = $tmp['cat_id'];
			unset($tmp);
		} else if ($parent_id == 0 && $sub_id != 0) {
			$parent_id = $second_cat[$sub_id]['parent_id'];
		}
		
		if ($in_ajax == false) {
						foreach ($first_cat as $value) {
				if ($value['parent_id'] == 0) {
					$ps = '';
					if ($value['cat_id'] == $parent_id) {
						$ps = 'selected="selected"';
					}
					$r['first'] .= "<option value='{$value['cat_id']}' {$ps} >{$value['cat_name']}</option>";
				}
			}
		}
		
				$r['second'] = "<option value=''>请选择...</option>";
		foreach ($second_cat as $value) {
			if ($parent_id == $value['parent_id']) {
				$ss = '';
				if ($value['cat_id'] == $sub_id) {
					$ss = 'selected="selected"';
				}
				$r['second'] .= "<option value='{$value['cat_id']}' {$ss} >{$value['cat_name']}</option>";
			}
		}
		return $r;
	}

	function upload_pic($_FILES,$id){
		$image_name = $id.".png";
		$image_path = RELATIVE_ROOT_PATH . 'images/talk/'.face_path($id);
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
			DB::update('talk', array('image' => $image_file), array('lid' => $id));
		}
		return true;
	}

		function Getguest($ids)
	{
		$list = array();$uids = array();$guests = array();
		$ids = is_array($ids) ? $ids : array((int)$ids);
		$query = DB::query("SELECT iid,itemid,uid,description,type FROM ".DB::table('item_user')." WHERE item = 'talk' AND itemid IN(".jimplode($ids).")");
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