<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename qun.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-06-20 16:52:55 293070398 1958069393 44016 $
 *******************************************************************/




if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class QunLogic
{
	
	function QunLogic()
	{
	}
	
	
	
	
	function joined_nums($uid)
	{
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('qun_user')." WHERE uid='{$uid}'");
		return $count;
	}
	
	
	function is_exists($qid)
	{
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('qun')." WHERE qid='{$qid}'");
		return $count > 0 ? true : false;
	}
	
	
	function admin_nums($qid)
	{
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('qun_user')." WHERE qid='{$qid}' AND level=2");
		return $count;
	}
	
	
	function join_qun($qid, $member_info, $join_type = 0)
	{
		if ($join_type == 0) {
						$data = array(
				'qid' => $qid,
				'uid' => $member_info['uid'],
				'username' => $member_info['username'],
				'level' => 4,
				'join_time' => TIMESTAMP,
			);
			DB::insert('qun_user', $data);
			
						DB::query("UPDATE ".DB::table('qun')." SET member_num=member_num+1,lastactivity='".time()."' WHERE qid='{$qid}'");

		} else if($join_type == 1) {
						$data = array(
				'qid' => $qid,
				'uid' => $member_info['uid'],
				'username' => $member_info['username'],
				'message' => $member_info['message'],
				'apply_time' => TIMESTAMP,
			);
			DB::insert('qun_apply', $data);
		}
		return 1;
	}
	
	
	function quit_qun($qid, $uid)
	{
				DB::query("DELETE FROM ".DB::table('qun_user')." WHERE qid='{$qid}' AND uid='{$uid}'");
		
				DB::query("UPDATE ".DB::table('qun')." SET member_num=member_num-1 WHERE qid='{$qid}'");
	}
	
	
	function upgrade2admin($qid, $uid)
	{
				DB::query("UPDATE ".DB::table('qun_user')." SET level=2 WHERE qid='{$qid}' AND uid='{$uid}'");
		return 1;
	}
	
	
	function degrade($qid, $uid)
	{
		DB::query("UPDATE ".DB::table('qun_user')." SET level=4 WHERE qid='{$qid}' AND uid='{$uid}'");
	}
	
	
	function audit_qun_apply($qid, $member_info, $type = 'yes')
	{
		if ($type == 'yes') {
			$this->join_qun($qid, $member_info);
		}
		$uid = $member_info['uid'];
		DB::query("DELETE FROM ".DB::table('qun_apply')." WHERE qid='{$qid}' AND uid='{$uid}'");
	}
	
	
	function is_qun_member($qid, $uid)
	{
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('qun_user')." WHERE qid='{$qid}' AND uid='{$uid}'");
		return $count;
	}
	
	
	function get_qun_user($qid, $uid)
	{
		$qun_user = DB::fetch_first("SELECT * FROM ".DB::table('qun_user')." WHERE qid='{$qid}' AND uid='{$uid}'");
		return $qun_user;
	}
	
	
	function get_admin_list($qid)
	{
		$uids = array();
		$admin_list = array();
		$query = DB::query("SELECT uid FROM ".DB::table('qun_user')." WHERE level=2 AND qid='{$qid}'");
		while ($value = DB::fetch($query)) {
			$uids[] = $value['uid'];
		}
		if (!empty($uids)) {
			
			$TopicLogic = Load::logic('topic', 1);
			$admin_list = $TopicLogic->GetMember($uids);
		}
		return $admin_list;	
	}
	
	
	function get_new_member_list($qid, $num = 12)
	{
		$time = TIMESTAMP - 7*24*3600;
		$where_sql = " qid='{$qid}' AND join_time > {$time} ";
		$query = DB::query("SELECT uid FROM ".DB::table('qun_user')." WHERE {$where_sql} ORDER BY join_time DESC LIMIT {$num}");
		$uids = array();
		while ($value = DB::fetch($query)) {
			$uids[] = $value['uid'];	
		}
		$qun_members = array();
		if (!empty($uids)) {
			
			$TopicLogic = Load::logic('topic', 1);
			$qun_members = $TopicLogic->GetMember($uids);
		}
		return $qun_members;
	}
	
	
	function get_qun_info($qid)
	{
		$qun_info = DB::fetch_first("SELECT * FROM ".DB::table('qun')." WHERE qid='{$qid}'");
		return $qun_info;
	}
	
	
	function get_qun_list($parma)
	{
		$where_sql = " 1 ";
		$type = $parma['type'];
		if ($type == 'managed' || $type == 'joined') {
			if ($type == 'managed') {
				$level = "('1', '2')";
			} else {
				$level = "('4')";
			}
			$query = DB::query("SELECT qid 
								FROM ".DB::table('qun_user')." 
								WHERE uid=".MEMBER_ID." AND level IN {$level}");
			$ids = array();
			while ($value = DB::fetch($query)) {
				$ids[] = $value['qid'];
			}
			$count = count($ids);
			if ($count == 0) {
				return array('row_nums' => 0);
			} else {
				$where_sql .= " AND qid IN(".jimplode($ids).") ";
			}
		} else if ($type == 'followed') {
						$buddyids = get_buddyids(MEMBER_ID);
			if (empty($buddyids)) {
				return array('row_nums' => 0);
			}
			$ids = array();
			$query = DB::query("SELECT qid 
					   			FROM ".DB::table('qun_user')." 
					   			WHERE uid IN(".jimplode($buddyids).")");
			while ($value = DB::fetch($query)) {
				$ids[] = $value['qid'];
			}
			$count = count($ids);
			if ($count == 0) {
				return array('row_nums' => 0);
			} else {
				$where_sql .= " AND qid IN(".jimplode($ids).") ";
			}
		}
		
		$limit_sql = '';
		if ($parma['limit']) {
			$limit_sql = $parma['limit'];
		}
		$order_sql = ' member_num DESC';
		$list = array();
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('qun')." WHERE {$where_sql} ");
		if ($count) {
			$query = DB::query("SELECT * 
								FROM ".DB::table('qun')." 
								WHERE {$where_sql} 
								ORDER BY {$order_sql} 
								LIMIT $limit_sql");
			while ($value = DB::fetch($query)) {
				if (empty($value['icon'])) {
					$value['icon'] = $this->qun_avatar($value['qid'], 's'); 
				}
				$list[] = $value;
			}
		}
		return array('row_nums' => $count, 'list' => $list);
	}
	
	
	
	
	function get_recd_list($num = 5)
	{
				$list = array();
		$query = DB::query("SELECT * 
							FROM ".DB::table('qun')." 
							WHERE recd=1 
							ORDER BY member_num DESC
							LIMIT {$num} ");	
		while ($value = DB::fetch($query)) {
						if (empty($value['icon'])) {
				$value['icon'] = $this->qun_avatar($value['qid'], 's'); 
			}
			$list[] = $value;
		}
		return $list;
	}
	
	
	function create(&$struct_qun)
	{
		$ret = $this->chk_qun_post($struct_qun);
		if ($ret != 1) {
			return $ret;
		}
		
				$tag_ary = array();
		if (!empty($struct_qun['tag'])) {
			$tag_ary = $this->tag($struct_qun['tag']);
		}
		
		$recd = empty($struct_qun['recd']) ? 0 : 1;
		$data = array(
			'name' => $struct_qun['qun_name'],
			'cat_id' => $struct_qun['sub_cat'],
			'province' => $struct_qun['province'],
			'city' => $struct_qun['city'],
			'desc' => $struct_qun['desc'],
			'gview_perm' => $struct_qun['gview_perm'],
			'join_type' => $struct_qun['join_type'],
			'founderuid' => $struct_qun['uid'],
			'foundername' => $struct_qun['username'],
			'dateline' => TIMESTAMP,
			'member_num' => 1,
			'recd' => $recd,
			'qun_theme_id' => $struct_qun['qun_theme_id'],
			'postip' => client_ip(),
		);
		if($struct_qun['sub_cat']){
			$data['cat_id'] = $struct_qun['sub_cat'];
		}else{
			$data['cat_id'] = $struct_qun['top_cat'];
		}
		
		$qid = DB::insert('qun', $data, true);
		$struct_qun['qid'] = $qid;
		
		$qun_user_data = array(
			'qid' => $qid,
			'uid' => $struct_qun['uid'],
			'username' => $struct_qun['username'],
			'level' => 1,
			'join_time' => TIMESTAMP,
		);
		DB::insert('qun_user', $qun_user_data);
		
				$cat_ary = $this->get_category();		$sub_cat_id = $struct_qun['sub_cat'];
		if(empty($sub_cat_id)){
			$sub_cat_id = $struct_qun['top_cat'];
			DB::query("UPDATE ".DB::table('qun_category')." 
		   			   SET qun_num = qun_num+1 
		   			   WHERE cat_id = '{$sub_cat_id}'");
		}else{
			$top_cat_id = $cat_ary['second'][$sub_cat_id]['parent_id'];
		
			DB::query("UPDATE ".DB::table('qun_category')." 
					   SET qun_num = qun_num+1 
					   WHERE cat_id IN('{$sub_cat_id}','$top_cat_id') ");
		}
		$this->update_category_cache();
		
				if (!empty($tag_ary)) {
			$this->add_tag_relation($qid, $tag_ary);
		}
		
				if (!empty($_FILES['icon']['name'])) {
			$u_data = array(
				'field' => 'icon', 
				'qid' => $qid,
			);
			$this->upload_icon($u_data);
		}
		return 1;
	}
	
	
	function edit($struct_qun)
	{
		$ret = $this->modify_setting($struct_qun, 'all');
		if ($ret != 1) {
			return -1;
		}
		
				if (!empty($_FILES['icon']['name'])) {
			$u_data = array(
				'field' => 'icon', 
				'qid' => $struct_qun['qid'],
			);
			$this->upload_icon($u_data);
		}
		return 1;
	}
	
	
	function modify_setting($struct_qun, $type = '')
	{
		$ret = $this->chk_qun_post($struct_qun);
		if ($ret != 1) {
			return $ret;
		}
		
				$qun_info = $this->get_qun_info($struct_qun['qid']);
		
				$tag_ary = array();
		if (!empty($struct_qun['tag'])) {
			$tag_ary = $this->tag($struct_qun['tag']);
		}
		
		$data = array(
			'name' => $struct_qun['qun_name'],
			'cat_id' => $struct_qun['sub_cat'],
			'province' => $struct_qun['province'],
			'city' => $struct_qun['city'],
			'desc' => $struct_qun['desc'],
			'qun_theme_id' => $struct_qun['qun_theme_id'],
		);
		
		if($struct_qun['sub_cat']){
			$data['cat_id'] = $struct_qun['sub_cat'];
		}else{
			$data['cat_id'] = $struct_qun['top_cat'];
		}
		
		if ($type == 'all') {
			$data['gview_perm'] = $struct_qun['gview_perm'];
			$data['join_type'] = $struct_qun['join_type'];
			$data['recd'] = $struct_qun['recd'];
		}
		
		DB::update('qun', $data, array('qid' => $struct_qun['qid']));
		
				if (!empty($tag_ary)) {
			$this->add_tag_relation($struct_qun['qid'], $tag_ary);
		} else {
						$this->delete_tag_relation($struct_qun['qid']);
		}
		
				$cat_ary = $this->get_category();		$sub_cat_id = $struct_qun['sub_cat'];
		if(empty($sub_cat_id)){
			$sub_cat_id = $struct_qun['top_cat'];
		}
		if ($sub_cat_id != $qun_info['cat_id']) {
			
						$top_cat_id = $cat_ary['second'][$sub_cat_id]['parent_id'];
			DB::query("UPDATE ".DB::table('qun_category')." 
					   SET qun_num = qun_num+1 
					   WHERE cat_id IN('{$sub_cat_id}','$top_cat_id') ");
			
						DB::query("UPDATE ".DB::table('qun_category')." 
					   SET qun_num = if(qun_num>0,qun_num-1,0)  
					   WHERE cat_id IN('{$qun_info['cat_id']}','{$cat_ary['second'][$qun_info['cat_id']]['parent_id']}') ");
			
			$this->update_category_cache();
		}
		
		return 1;
	}
	
	
	function delete_qun($qid, $cat_id)
	{
		$where_sql = " qid='{$qid}' ";
		
				DB::query("DELETE FROM ".DB::table('qun')." WHERE {$where_sql}");
		
				DB::query("DELETE FROM ".DB::table('qun_user')." WHERE {$where_sql}");
		
				$this->delete_tag_relation($qid);
		
						$info = DB::fetch_first("SELECT icon FROM ".DB::table('qun')." WHERE qid='{$qid}'");
		if (!empty($info['icon'])) {
			unlink($this->qun_avatar($qid, 's'));
			unlink($this->qun_avatar($qid, 'b'));
		}
		
				DB::query("DELETE FROM ".DB::table('qun_announcement')." WHERE qid='{$qid}'");
		
				$cat_ary = $this->get_category();		$top_cat_id = $cat_ary['second'][$cat_id]['parent_id'];
		DB::query("UPDATE ".DB::table('qun_category')." 
				   SET qun_num = if(qun_num>0,qun_num-1,0) 
				   WHERE cat_id IN('{$cat_id}','$top_cat_id') ");
		$this->update_category_cache();
		
		
				load::logic('event');
		$EventLogic = new EventLogic();
		$eid_arr = array();
		$query = DB::query("SELECT eid FROM ".DB::table('qun_event')." WHERE qid='{$qid}'");
		while ($val = DB::fetch($query)) {
			$eid_arr[$val['eid']] = $val['eid'];
		}
		foreach($eid_arr as $key=>$eid){
			$EventLogic->delEvent($eid);
		}
		
				$tids = array();
		$query = DB::query("SELECT tid FROM ".DB::table('topic_qun')." WHERE item_id='{$qid}'");
		while ($value = DB::fetch($query)) {
			$tids[] = $value['tid'];
		}
		
		if (!empty($tids)) {
			DB::query("DELETE FROM ".DB::table('topic_qun')." WHERE item_id='{$qid}'");
			$where_sql = " tid IN(".jimplode($tids).") ";
			DB::query("DELETE FROM ".DB::table('topic')." WHERE {$where_sql} ");
			
		}
	}
	
	
	function upload_setting()
	{
				$allow_exts = array('jpg', 'gif', 'png'); 
		$config = ConfigHandler::get('qun_setting');
				$img_size = 512;
		if (!empty($config['img_size'])) {
			$img_size = $config['img_size'];
		}
		return array('allow_exts' => $allow_exts, 'img_size' => $img_size);
	}
	
	
	function upload_icon($upload_data)
	{
		$u_setting = $this->upload_setting();
		$allow_exts = $u_setting['allow_exts']; 
		$max_size = $u_setting['img_size']; 
		extract($upload_data);
		
		
		
		$type = trim(strtolower(end(explode(".",$_FILES[$field]['name']))));
		if(!in_array($type, $allow_exts)) {
			return -1;
		}
		
		$image_name = substr(md5($_FILES[$field]['name']),-10).".{$type}";
		$image_path = $this->qun_avatar_path($upload_data['qid']);
		if (empty($image_path)) {
			return;
		}
		$image_file = $image_path . $image_name;
		if (!is_dir($image_path)) {
			Load::lib('io', 1)->MakeDir($image_path);
		}
		Load::lib('upload');
		$UploadHandler = new UploadHandler($_FILES,$image_path,$field,true);
		$UploadHandler->setMaxSize($max_size);
		$UploadHandler->setNewName($image_name);
		$result = $UploadHandler->doUpload();
		if ($result) {
			$result = is_image($image_file);
		}
		
		if(!$result) {
			Load::lib('io', 1)->DeleteFile($image_file);
			return -2;
		}
		
		
        list($w,$h) = getimagesize($image_file);
        
                $dst_file = $image_path . $upload_data['qid'] . '_b.jpg';
        if (file_exists($dst_file)) {
        	unlink($dst_file);
        }
        
        $make_result = autothumbnail($image_file, $dst_file, 80, 80);
		
                $dst_file = $image_path . $upload_data['qid'] . '_s.jpg';
	    if (file_exists($dst_file)) {
        	unlink($dst_file);
        }
       	$make_result = autothumbnail($image_file, $dst_file, 50, 50);
        
        unlink($image_file);
		
        if (!empty($upload_data['qid'])) {
        	DB::query("UPDATE ".DB::table('qun')." SET icon='{$dst_file}' WHERE qid='{$upload_data['qid']}'");
        }
		return 1;
	}
	
	
	function qun_avatar_path($qid)
	{
		$qid = sprintf("%09d", $qid);
		$dir1 = substr($qid, 0, 3);
		$dir2 = substr($qid, 3, 2);
		$dir3 = substr($qid, 5, 2);
		
				$qun_dir = RELATIVE_ROOT_PATH . "images/qun_img/";
		if(!is_dir($qun_dir)) {
			@mkdir($qun_dir, 0777);
		}
		return "{$qun_dir}{$dir1}/{$dir2}/{$dir3}/";
	}
	
	
	function qun_avatar($qid, $type = 's')
	{
		$path = $this->qun_avatar_path($qid);
		$avatar_file = $path."{$qid}_{$type}.jpg";
		if (!file_exists($avatar_file)) {
			$avatar_file = RELATIVE_ROOT_PATH . "images/qun_def_{$type}.jpg";
		}
		return $avatar_file;
	}
	
	
	function tag($tag)
	{
		if (empty($tag)) {
			return false;
		}
		$tag = strip_tags($tag);
		$tag = getstr($tag, 300, 1, 1);
		
				$tags = preg_split("/[\\s,]+/", $tag);
		$tags = array_unique($tags);
		
		$new_tag_ary = array();
		$len = count($tags);
		if ($len > 5) {
			for ($i=0;$i<5;++$i) {
				$new_tag_ary[] = $tags[$i];
			}
		} else {
			$new_tag_ary = $tags;
		}
		unset($tags);
		
		$vtags = array();
		$query = DB::query("SELECT *  
							FROM ".DB::table('qun_tag')." 
							WHERE tag_name IN (".jimplode($new_tag_ary).")");
		while ($value = DB::fetch($query)) {
			$value['tag_name'] = addslashes($value['tag_name']);
			$vkey = md5($value['tag_name']);
			$vtags[$vkey] = $value;
		}

		$tag_ary = $updatetagids = array();
		foreach ($new_tag_ary as $tagname) {
			if(!preg_match('/^([\x7f-\xff_-]|\w){3,20}$/', $tagname)) {
				continue;
			}
			$vkey = md5($tagname);
			if(empty($vtags[$vkey])) {
				$setarr = array(
					'tag_name' => $tagname,
					'dateline' => TIMESTAMP,
				);
				$tagid = DB::insert('qun_tag', $setarr, true);
				$tag_ary[$tagid] = $tagname;
			} else {
				$tagid = $vtags[$vkey]['tag_id'];
				$updatetagids[] = $tagid;
				$tag_ary[$tagid] = $tagname;
			}
		}
		return $tag_ary;
	}
	
	
	function add_tag_relation($qid, $tag_ary)
	{
				$old_tagids = $old_tag_ary = array();
		$query = DB::query("SELECT * FROM ".DB::table('qun_tag_fields')." WHERE qid='{$qid}'");
		while ($value = DB::fetch($query)) {
			$old_tagids[] = $value['tag_id'];
		}
		
				if (!empty($old_tagids)) {
			$tag_ids = array_keys($tag_ary);
			$diffs_1 = array_diff($old_tagids, $tag_ids);
			$diffs_2 = array_diff($tag_ids, $old_tagids);
			$diffs = array_merge($diffs_1, $diffs_2);
			
						if (!empty($diffs)) {
				foreach ($diffs as $id) {
					if (in_array($id, $old_tagids)) {
						$new_old_tag_ary[] = $id;
					} else if (in_array($id, $tag_ids)) {
						$new_tag_ary[$id] = $tag_ary[$id];
					}
				}
			} else {
				return ;
			}
		} else {
			$new_tag_ary = &$tag_ary;
		}
		if (!empty($new_tag_ary)) {
			$data_sql_ary = array();
			$tag_ids = array();
			foreach ($new_tag_ary as $key => $value) {
				$data_sql_ary[] = " ('{$key}', '{$qid}', '{$value}') ";
				$tag_ids[] = $key;
			}
			$data_sql = implode(',', $data_sql_ary);
			DB::query("INSERT INTO ".DB::table('qun_tag_fields')." (tag_id,qid,tag_name) VALUES ".$data_sql);
			
						$where_sql = jimplode($tag_ids);
			DB::query("UPDATE ".DB::table('qun_tag')." SET count=count+1 WHERE tag_id IN({$where_sql})");
		}
		
		if (!empty($new_old_tag_ary)) {
			$where_sql = jimplode($new_old_tag_ary);
			DB::query("DELETE FROM ".DB::table('qun_tag_fields')." WHERE qid='{$qid}' AND tag_id IN(".$where_sql.")");
			DB::query("UPDATE ".DB::table('qun_tag')." SET count=count-1 WHERE tag_id IN({$where_sql})");
		}
		
	}
	
	
	function delete_tag_relation($qid)
	{
		$tag_ids = array();
		$query = DB::query("SELECT tag_id FROM ".DB::table('qun_tag_fields')." WHERE qid='{$qid}'");
		while ($value = DB::fetch($query)) {
			$tag_ids[] = $value['tag_id'];
		}
		if (!empty($tag_ids)) {
			$where_sql = jimplode($tag_ids);
			DB::query("DELETE FROM ".DB::table('qun_tag_fields')." WHERE qid='{$qid}' AND tag_id IN(".$where_sql.")");
			DB::query("UPDATE ".DB::table('qun_tag')." SET count=count-1 WHERE tag_id IN({$where_sql})");
		}
	}
	
	
	function get_qun_tag($qid)
	{
		$tag_arys = array();
		$query = DB::query("SELECT * FROM ".DB::table('qun_tag_fields')." WHERE qid='{$qid}'");
		while ($value = DB::fetch($query)) {
			$tag_ary[] = $value;		
		}
		return $tag_ary;
	}
	
	
	function getRecdEventList($qid){
		$recd_event_list = array();
		$query = DB::query("SELECT qe.eid,qe.qid,e.title FROM ".DB::table('qun_event')." qe left join ".DB::table('event')." e on e.item='qun' and e.id = qe.eid and e.verify = 1 WHERE qe.qid='{$qid}' and qe.recd = 1");
		while ($value = DB::fetch($query)) {
			$recd_event_list[] = $value;		
		}
		return $recd_event_list;
	}

	
	function getRecdVoteList($qid){
		$recd_vote_list = array();
		$sql = "SELECT qv.vid,qv.qid,v.subject 
				FROM ".DB::table('qun_vote')." qv 
				left join ".DB::table('vote')." v on v.item='qun' and v.vid = qv.vid 
				WHERE qv.qid='{$qid}' and qv.recd = 1";
		$query = DB::query($sql);
		while ($value = DB::fetch($query)) {
			$recd_vote_list[] = $value;		
		}
		return $recd_vote_list;
	}
	
	
	function getEvent($param){
		$qid = $param['qid'];
		$event_list = array();
		$sql = " SELECT qe.qid,qe.eid,e.*,m.nickname,qe.recd  
				 FROM ".DB::table('qun_event')." qe 
				 LEFT JOIN ".DB::table('event')." e ON e.item='qun' AND e.id = qe.eid and e.verify = 1 
				 LEFT JOIN ".DB::table('members')." m ON m.uid = e.postman 
				 WHERE qe.qid='{$qid}' 
				 ORDER BY e.lasttime ";
		$query = DB::query($sql);
		$count = 0;
		while ($rsdb = DB::fetch($query)) {
			$count++;
			$rsdb['che'] = $rsdb['recd'] ? " checked " : "";
			$event_list[$rsdb['eid']] = $rsdb;		
		}
		$return['event_list'] = $event_list;
		$return['count'] = $count;
		return $return;
	}
	
	
	function getVote($param){
		$qid = $param['qid'];
		$vote_list = array();
		$recd = array();
		$sql = " SELECT qv.qid,qv.vid,v.uid,v.subject,v.voter_num,v.expiration,m.nickname,qv.recd  
				 FROM ".DB::table('qun_vote')." qv 
				 LEFT JOIN ".DB::table('vote')." v ON v.item='qun' AND v.vid = qv.vid 
				 LEFT JOIN ".DB::table('members')." m ON m.uid = v.uid 
				 WHERE qv.qid='{$qid}' 
				 ORDER BY v.dateline ";
		$query = DB::query($sql);
		$count = 0;
		while ($rsdb = DB::fetch($query)) {
			$count++;
			$rsdb['che'] = $rsdb['recd'] ? " checked " : "";
			$vote_list[] = $rsdb;		
		}
		$return['vote_list'] = $vote_list;
		$return['count'] = $count;
		return $return;
	}

	
	function getAttach($param){
		$qid = $param['qid'];
		$attach_list = array();
		$recd = array();
		$sql = " SELECT ta.*,m.nickname FROM ".DB::table('topic_attach')." ta 
				 LEFT JOIN ".DB::table('members')." m ON m.uid = ta.uid 
				 WHERE ta.itemid='{$qid}' AND ta.item='qun' 
				 ORDER BY ta.id DESC ";
		$query = DB::query($sql);
		$count = 0;
		while ($rsdb = DB::fetch($query)) {
			$count++;
			$rsdb['filetype'] = 'images/filetype/'.$rsdb['filetype'].'.gif';
			$rsdb['dateline'] = my_date_format($rsdb['dateline']);
			$rsdb['size'] = ($rsdb['filesize'] > 1024*1024) ? round($rsdb['filesize']/(1024*1024),2).'MB' : round($rsdb['filesize']/1024,1).'KB';
			$attach_list[] = $rsdb;		
		}
		$return['attach_list'] = $attach_list;
		$return['count'] = $count;
		return $return;
	}
	
	
	function get_hot_tag_list($num = 12)
	{
		$list = array();
		$query = DB::query("SELECT * FROM ".DB::table('qun_tag')." ORDER BY count DESC LIMIT {$num}");
		while ($value = DB::fetch($query)) {
			$list[] = $value;
		}
		return $list;
	}
	
	function get_qun_strtag($qid, $sp = ',')
	{
		$tag_arys = array();
		$query = DB::query("SELECT tag_name FROM ".DB::table('qun_tag_fields')." WHERE qid='{$qid}'");
		while ($value = DB::fetch($query)) {
			$tag_ary[] = $value['tag_name'];		
		}
		$tag = '';
		if (!empty($tag_ary)) {
			$tag = implode($sp, $tag_ary);
		}
		return $tag;
	}
	
	
	function get_tag_info($tag_id)
	{
		$tag_info = DB::fetch_first("SELECT * FROM ".DB::table('qun_tag')." WHERE tag_id='{$tag_id}'");
		return $tag_info;
	}
	
	
	function chk_qun_post(&$struct_qun)
	{
		$struct_name = &$struct_qun['qun_name'];
		if (empty($struct_name) || strlen($struct_name) < 3) {
			return -1;
		}
				$struct_name = getstr($struct_name, 100, 1, 1);
		
		
				if (empty($struct_qun['province'])) {
			return -3;
		}
		
				$struct_qun['desc'] = getstr($struct_qun['desc'], 400, 1, 1);
		
				if ($struct_qun['gview_perm'] == 1) {
			$struct_qun['join_type'] == 1;
		}
		
		return 1;
	}
	
	
	function category_exists($cat_name, $pid = 0)
	{
		$count = DB::result_first("SELECT COUNT(*) 
								   FROM ".DB::table('qun_category')." 
								   WHERE cat_name='{$cat_name}' AND parent_id='{$pid}'");
		return $count > 0 ? true : false;
	}
	
	
	function id2category($cat_id)
	{
		$category = array();
		$category = DB::fetch_first("SELECT * FROM ".DB::table('qun_category')." WHERE cat_id='{$cat_id}'");
		return $category;
	}
	
	
	function &get_category_tree()
	{
		$tree = $cat_ary = array();
		$query = DB::query("SELECT *  
							FROM ".DB::table('qun_category')." 
							ORDER BY display_order ASC");
		while ($value = DB::fetch($query)) {
			$cat_ary[] = $value;
		}
		
		if (!empty($cat_ary)) {
			$tree = $this->category_tree($cat_ary);
		}
		return $tree;
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
		$qid = DB::insert('qun_category', $set_ary, true);
		return $qid;
	}
	
	
	function update_category($cat_id, $cat_name, $display_order)
	{
		$set_ary = array(
			'cat_name' => $cat_name,
			'display_order' => $display_order,
		);
		DB::update('qun_category', $set_ary, array('cat_id' => $cat_id));
	}
	
	
	function delete_category($cat_id)
	{
				$category = $this->id2category($cat_id);
		if (empty($category)) {
			return -1;
		}
		
		if ($category['qun_num'] > 0) {
			return -2;
		}
		
				$sub_count = DB::result_first("SELECT COUNT(*) 
									   FROM ".DB::table('qun_category')." 
									   WHERE parent_id='{$cat_id}'");
		if ($sub_count) {
			return -3;
		}
		
		DB::query("DELETE FROM ".DB::table('qun_category')." WHERE cat_id='{$cat_id}'");
		return 1;
	}
	
	
	function update_category_cache()
	{
		$cat_ary = array();
		$query = DB::query("SELECT * FROM ".DB::table('qun_category')." ORDER BY display_order ASC");
		while ($value = DB::fetch($query)) {
			if ($value['parent_id'] == 0) {
				$cat_ary['first'][$value['cat_id']] = $value;	
			} else {
				$cat_ary['second'][$value['cat_id']] = $value;	
			}
		}
				if (!empty($cat_ary['first'])) {
			ConfigHandler::set('qun_category', $cat_ary);
			return $cat_ary;	
		}
		return array();
	}
	
	
	function get_category()
	{
		$cat_ary = array();
		$cat_ary = ConfigHandler::get('qun_category');
		if (empty($cat_ary)) {
			$cat_ary = $this->update_category_cache();
		}
		return $cat_ary;
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
	
	
	
	
	function add_level($struct_level)
	{
		$level_id = DB::insert('qun_level', $struct_level, true);
		return $level_id;
	}
	
	
	function update_level($struct_level)
	{
		$data = array(
			'level_name' => $struct_level['level_name'],
			'credits_higher' => $struct_level['credits_higher'],
			'credits_lower' => $struct_level['credits_lower'],
			'member_num' => $struct_level['member_num'],
			'admin_num' => $struct_level['admin_num'],
		);
		DB::update('qun_level', $data, array('level_id' => $struct_level['level_id']));
	}
	
	
	function &get_level_list()
	{
		$level_list = array();
		$query = DB::query("SELECT * FROM ".DB::table('qun_level'));
		while ($value = DB::fetch($query)) {
			$level_list[] = $value;
		}
		return $level_list;	
	}
	
	
	function delete_level($level_id)
	{
				$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('qun')." WHERE level='{$level_id}'");
		if ($count > 0) {
			return -1;
		}
		DB::query("DELETE FROM ".DB::table('qun_level')." WHERE level_id='{$level_id}'");
		return 1;
	}
	
	
	function batch_delete_level($ids)
	{
		$new_ids = array();
		foreach ($ids as $id) {
			$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('qun')." WHERE level='{$id}'");
			if ($count < 1) {
				$new_ids[] = $id;
			}
		}
		
		if (!empty($new_ids)) {
			$where_sql = jimplode($new_ids);
			DB::query("DELETE FROM ".DB::table('qun_level')." WHERE level_id IN({$where_sql})");
		}
	}
	
	
	function level_nums()
	{
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('qun_level'));
		return $count;
	}
	
	
	function update_level_cache()
	{
		$cat_ary = array();
		$query = DB::query("SELECT * FROM ".DB::table('qun_level'));
		while ($value = DB::fetch($query)) {
			$cat_ary[$value['level_id']] = $value;
		}
		ConfigHandler::set('qun_level', $cat_ary);
	}
	
	
	function qun_level($qid, $credits = '')
	{
				
		$max_member_num = 999999999;
		$max_admin_num = 999999999;
		$config = ConfigHandler::get('qun_setting');
		if (empty($config)) {
			$info = array(
				'member_num' => $max_member_num,
				'admin_num' => $max_member_num,
			);	
		} else {
			$info = array(
				'member_num' => empty($config['member_num']) ? $max_member_num : $config['member_num'],
				'admin_num' => empty($config['admin_num']) ? $max_admin_num : $config['admin_num'],
			);
		}
		
		
		return $info;
	}
	
	
	
	
	function add_ploy($struct_ploy)
	{
		$id = DB::insert('qun_ploy', $struct_ploy, true);
		return $id;
	}
	
	
	function update_ploy($struct_ploy)
	{
		$data = array(
			'fans_num_min' => $struct_ploy['fans_num_min'],
			'fans_num_max' => $struct_ploy['fans_num_max'],
			'topics_lower' => $struct_ploy['topics_lower'],
			'topics_higher' => $struct_ploy['topics_higher'],
			'qun_num' => $struct_ploy['qun_num'],
		);
		DB::update('qun_ploy', $data, array('id' => $struct_ploy['id']));
	}
	
	
	function &get_ploy_list()
	{
		$ploy_list = array();
		$query = DB::query("SELECT * FROM ".DB::table('qun_ploy')." ORDER BY qun_num ASC");
		while ($value = DB::fetch($query)) {
			$ploy_list[] = $value;
		}
		return $ploy_list;
	}
	
	
	function ploy_nums()
	{
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('qun_ploy'));
		return $count;
	}
	
	
	function delete_ploy($ids)
	{
		$ids = (array) $ids;
		$where_sql = jimplode($ids);
		DB::query("DELETE FROM ".DB::table('qun_ploy')." WHERE id IN({$where_sql})");
	}
	
	
	function update_ploy_cache()
	{
		$cat_ary = array();
		$query = DB::query("SELECT * FROM ".DB::table('qun_ploy')." ORDER BY qun_num ASC");
		while ($value = DB::fetch($query)) {
			$cat_ary[$value['id']] = $value;
		}
		
		ConfigHandler::set('qun_ploy', $cat_ary);
		return $cat_ary;
	}
	
	
	function ploy_config($f = false)
	{
		$qun_ploy = ConfigHandler::get('qun_ploy');
		if (empty($qun_ploy)) {
			$qun_ploy = $this->update_ploy_cache();
		}
		if ($f) {
			return array_shift($qun_ploy);
		}
		return $qun_ploy;
	}
	
	
	function allowed_create($uid, &$ret)
	{
		$config = ConfigHandler::get();
		
				if (!$config['qun_setting']['new_qun']) {
			$ret = array(
				'sys_not_allow' => true,
			);
			return false;
		}
		
		$qun_ploy = $this->ploy_config();		if (empty($qun_ploy)) {
			exit("微群策略配置错误,请管理员到后台配置！");
		}
		
		$user_info = DB::fetch_first("SELECT * FROM ".DB::table('members')." WHERE uid='{$uid}'");
		
				if($config['qun_setting']['qun_ploy']['vip']){
			if(!$user_info['validate']){
				$ret = array('not_vip' => true);
				return false;
			}
		}
		
				if ($config['qun_ploy']['avatar']) {
			if (empty($user_info['face'])) {
				$ret = array(
					'no_avatar' => true,
				);
				return false;
			}
		}
		
		$num = 0;
		$pid = 0;
		$primary_ploy = array();
				foreach ($qun_ploy as $value) {
			if ($user_info['fans_count'] >= $value['fans_num_min'] && $user_info['topic_count'] >= $value['topics_lower']) {
				$num = $value['qun_num'];
			} else {
				if ($user_info['fans_count'] < $value['fans_num_min']) {
					$primary_ploy['fans_count'] = $value['fans_num_min'];
				} else if ($user_info['topic_count'] < $value['topics_lower']) {
					$primary_ploy['topic_count'] = $value['topics_lower'];
				}
				break;
			}
		}
		
		if ($num > 0) {
						$e_count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('qun')."  WHERE founderuid='{$uid}'");
			$allow_create_num = $num-$e_count;
			$ret = array(
				'ploy_qnum' => $num,
				'my_qnum' => $e_count,
				'allow_create_num' => $allow_create_num,
			);
			if ($allow_create_num == 0) {
				return false;
			}
			return true;
		}
		
				if (isset($primary_ploy['fans_count'])) {
			$ret = array(
				'little_fans' => true,
				'ploy_fans_count' => $primary_ploy['fans_count'],
				'my_fans_count' => $user_info['fans_count'],
				'allow_create_num' => 0,
			);
			
		} else if (isset($primary_ploy['topic_count'])) {
			$ret = array(
				'little_topic' => true,
				'ploy_topic_count' => $primary_ploy['topic_count'],
				'my_topic_count' => $user_info['topic_count'],
				'allow_create_num' => 0,
			);	
		}
		
		return false;
	}
	
	
	function chk_perm($qid, $uid, $perm = '')
	{
		
		$info = DB::fetch_first("SELECT level FROM ".DB::table('qun_user')." WHERE qid='{$qid}' AND uid='{$uid}'");
		if (!empty($info)) {
			return intval($info['level']);
		}
		
				$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('qun_apply')." WHERE qid='{$qid}' AND uid='{$uid}'");
		if ($count) {
			return 0;
		}
		
				return -1;
	}
	
	
	function add_announcement($param)
	{
		$param['dateline'] = TIMESTAMP;
		$id = DB::insert('qun_announcement', $param, true);
		return $id;
	}
	
	
	function new_announcement($qid)
	{
		$info = DB::fetch_first("SELECT message FROM ".DB::table('qun_announcement')." WHERE qid='{$qid}' ORDER BY dateline DESC");
		return $info['message'];
	}
	
	
	function del_announcement($id)
	{
		DB::query("DELETE FROM ".DB::table('qun_announcement')." WHERE id='{$id}'");	
	}
	
	
	function get_announcement_list($param)
	{
		$where_sql = ' WHERE 1 ';
		$order_sql = '';
		$limit_sql = '';
		if ($param['where']) {
			$where_sql .= " AND {$param['where']} " ;
		}
		
		if ($param['order']) {
			$order_sql = " ORDER BY {$param['order']} ";
		}
		
		if ($param['limit']) {
			$limit_sql = " LIMIT {$param['limit']}";
		}
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('qun_announcement')." {$where_sql}");
		if ($count) {
			$list = array();
			$query = DB::query("SELECT qa.*,m.username,m.nickname 
								FROM ".DB::table('qun_announcement')." AS qa 
								LEFT JOIN ".DB::table('members')." AS m 
								ON qa.author_id=m.uid 
								$where_sql 
								$order_sql 
								$limit_sql ");
			while ($value = DB::fetch($query)) {
				$list[] = $value;
			}
			return array('row_nums' => $count, 'list' => $list);
		}
		return false;
	}
	
	
	function upload_tips()
	{
		$tips = '';
		$u_setting = $this->upload_setting();
		if (!empty($u_setting['allow_exts'])) {
			$tips = "请选择".implode(' , ', $u_setting['allow_exts'])."格式";
		}
		if (!empty($u_setting['img_size'])) {
			$tips .= "，且文件大小不超过".$u_setting['img_size']."K的图片";
		}
		return $tips;
	}
	
	
	function checkThemeId($post=array()){
		$themeid = array();
		foreach ($post as $value) {
			if(in_array($value['theme_id'],$themeid)){
				return 1;
			}
			$themeid[$value['theme_id']] = $value['theme_id'];
		}
	}
	
		function getQunThemeList() {
		$themelist = ConfigHandler::get('qun_module');
		foreach($themelist as $k=>$v)
		{
			$v['element'] = "{$v[theme_bg_color]},{$v[theme_text_color]},{$v[theme_link_color]},{$v[theme_id]},{$v[theme_bg_image_type]}";

			$themelist[$k] = $v;
		}
		
		return $themelist;
	}
	
		function GetActivityQun($top_cat_ary,$sub_cat_ary){
		$activity_qun = array();
		
		$CacheConfig = ConfigHandler::get('cache');
		$ShowConfig = ConfigHandler::get('show');
		
		$limit = $ShowConfig['qun']['activity'];
		$cache_id = "misc/activity_qun";
		if ($limit>0 && false == ($activity_qun = cache_file('get', $cache_id))) {
			
			$sql = "SELECT * 
					FROM ".DB::table('qun')." q 
					WHERE $limit > (SELECT  
					              COUNT(*) 
					            FROM ".DB::table('qun')." subq 
					            WHERE subq.cat_id = q.cat_id
					                AND subq.lastactivity > q.lastactivity)
					ORDER BY q.lastactivity DESC ";
			$query = DB::query($sql);
			$num = array();
		    while ($val = DB::fetch($query)) {
				if(isset($top_cat_ary[$val['cat_id']])){
					$act_id = $val['cat_id'];
				}elseif(isset($sub_cat_ary[$val['cat_id']])){
					$act_id = $sub_cat_ary[$val['cat_id']]['parent_id'];
				}else{
					continue;
				}
				
				if(($num[$act_id] = $num[$act_id] ? $num[$act_id] : 1)  > $limit){
					continue;
				}
				
								$val['icon'] = $this->qun_avatar($val['qid'], 's');
				$val['desc'] = getstr($val['desc'], 80);
				
				$activity_qun[$act_id][$val['qid']] = $val;
				$num[$act_id]++;
			}
			cache_file('set', $cache_id, $activity_qun, $CacheConfig['qun']['activity']);
		}
		return $activity_qun;
	}
	
	
	function GetNum(){
		$sql = " SELECT 
				     COUNT(*) AS qun_num, 
				     SUM(member_num) AS member_num, 
				     SUM(thread_num) AS thread_num 
				 FROM ".DB::table('qun');
		return(DB::fetch_first($sql));
		
	}
	
	
	function getRecdQunCid(){
		$recdCid = array();
		$sql = "SELECT DISTINCT(cat_id) as id  
				FROM ".DB::table('qun')." 
				WHERE recd = 1 ";
		$query = DB::query($sql);
		while ($rs = DB::fetch($query)){
			$recdCid[$rs['id']] = $rs['id'];
		}
		return $recdCid;
	}
	
		function getNewQun($limit=6){
		$qun_list = array();
		$sql = "SELECT `qid`,`name`,`icon`,`member_num`,`topic_num`,`thread_num`  
				FROM `".DB::table('qun')."`  
				WHERE `thread_num` > 0 
				ORDER BY `dateline` DESC  
				LIMIT $limit ";
		$query = DB::query($sql);
		while ($rs = DB::fetch($query)){
						$rs['icon'] = $this->qun_avatar($rs['qid'], 's');
						$qun_list[$rs['qid']] = $rs;
		}
		return $qun_list;
	}
}

?>