<?php
/**
 *
 * 微博图片的数据库逻辑操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: image.logic.php 1347 2012-08-09 06:50:25Z wuliyong $
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

/**
 *
 * 微博图片的数据库逻辑操作类
 *
 * @author 狐狸<foxis@qq.com>
 *
 */
class ImageLogic
{
	
	var $table = 'topic_image';

	function ImageLogic() {
		;
	}

	
	function get($p)
	{
		$wheres = array();

		if($p['where']){
			$wheres[] = $p['where'];
		}
		if(isset($p['id']))
		{
			$p['id'] = max(0, (int) $p['id']);
			if($p['id'] > 0) $wheres[] = " `id`='{$p['id']}' ";
		}
		if(isset($p['ids']))
		{
			$p['ids'] = $this->get_ids($p['ids'], 0);
			if($p['ids']) $wheres[] = " `id` in ({$p['ids']}) ";
		}
		if(isset($p['tid']))
		{
			$p['tid'] = max(0, (int) $p['tid']);
			$wheres[] = " `tid`='{$p['tid']}' ";
		}
		if(isset($p['tids']))
		{
			$p['tids'] = $this->get_ids($p['tids'], 0);
			if($p['tids']) $wheres[] = " `tid` in ({$p['tids']}) ";
		}
		if(isset($p['dateline_min']))
		{
			$p['dateline_min'] = max(0, (int) $p['dateline_min']);
			$wheres[] = " `dateline`>='{$p['dateline_min']}' ";
		}
		if(isset($p['dateline_max']))
		{
			$p['dateline_max'] = max(0, (int) $p['dateline_max']);
			$wheres[] = " `dateline`<='{$p['dateline_max']}' ";
		}
		if(isset($p['uid']))
		{
			$p['uid'] = max(0, (int) $p['uid']);
			$wheres[] = " `uid`='{$p['uid']}' ";
		}
		if(isset($p['uids']))
		{
			$p['uids'] = $this->get_ids($p['uids'], 0);
			if($p['uids']) $wheres[] = " `uid` in ({$p['uids']}) ";
		}

		$sql_where = ($wheres ? " where " . implode(" and ", $wheres) : "");

		$count = max(0, (int) $p['count']);
		if($count < 1)
		{
			$count = DB::result_first("select count(*) as `count` from ".DB::table($this->table)." $sql_where ");
		}
		$list = array();
		$page = array();
		if($count > 0)
		{
			$sql_limit = '';
			if($p['per_page_num'])
			{
				$page = page($count, $p['per_page_num'], $p['page_url'], array('return' => 'Array'));

				$sql_limit = " {$page['limit']} ";
			}
			elseif($p['limit'])
			{
				if(false !== strpos(strtolower($p['limit']), 'limit '))
				{
					$sql_limit = " {$p['limit']} ";
				}
				else
				{
					$sql_limit = " limit {$p['limit']} ";
				}
			} elseif ($p['count']) {
				$sql_limit = " LIMIT {$p['count']} ";
			}

			$sql_order = '';
			if($p['order'])
			{
				if(false !== strpos(strtolower($p['order']), 'order by '))
				{
					$sql_order = " {$p['order']} ";
				}
				else
				{
					$sql_order = " order by {$p['order']} ";
				}
			}

			$sql_fields = ($p['fields'] ? $p['fields'] : "*");

			$query = DB::query("select $sql_fields from ".DB::table($this->table)." $sql_where $sql_order $sql_limit ");
			while(false != ($r = DB::fetch($query)))
			{
				$r['image'] = $r['image_small'] = topic_image($r['id'], 'small', 0);
    			$r['image_original'] = $r['image_big'] = topic_image($r['id'], 'original', 0);
				
				$list[] = $r;
			}

			if($list)
			{
				return array('count'=>$count, 'list'=>$list, 'page'=>$page);
			}
		}

		return array();
	}
	
	
	function get_my_image($uid=MEMBER_ID, $limit=6) {
		$uid = (is_numeric($uid) ? $uid : 0);
		if($uid < 1) {
			return false;
		}
		$limit = max(1, (int) $limit);
		
		$cache_id = "{$uid}-my_image-{$limit}";
		if(false !== ($ret = cache_db('get', $cache_id))) {
			return $ret;
		}
		
		$ret = array();
		
		$p = array(
			'where' => ' `tid` > 0 ',
			'count' => $limit,
			'uid' => $uid,
			'order' => ' `id` DESC ',
		);
		$rets = $this->get($p);
		if($rets) {
			$ret = $rets['list'];
		}
		
		cache_db('set', $cache_id, $ret, 36000);
		
		return $ret;
	}

	
	function get_info($id)
	{
		$id = max(0, (int) $id);
		if($id < 1) return array();

		$p = array(
			'id' => $id,
			'count' => 1,
		);
		$rets = $this->get($p);

		$ret = $rets['list'][0];

		return $ret;
	}

	
	function add($uid, $username = '', $item = '', $itemid = 0)
	{
		$uid = is_numeric($uid) ? $uid : 0;
		$itemid = is_numeric($itemid) ? $itemid : 0;
		if($uid < 1) {
			$uid = MEMBER_ID;
		}
		if($uid < 1) return 0;

		if(!$username) {
			$username = DB::result_first("select `username` from ".DB::table('members')." where `uid`='$uid'");
		}
		if(!$username) return 0;

		$arr = array(
			'uid' => $uid,
			'username' => $username,
			'item' => $item,
			'itemid' => $itemid,
			'dateline' => time(),
		);
		$ret = DB::insert($this->table, $arr, 1);
		

		return $ret;
	}

	
	function modify($p)
	{
		$id = (is_numeric($p['id']) ? $p['id'] : 0);
		if($id < 1) return 0;

		$info = $this->get_info($id);
		if(!$info) return 0;
		$vtid = $p['vtid'];		$sets = array();

		$_int_fields = array('tid', 'filesize', 'width', 'height', 'uid', 'dateline', 'views');
		foreach($_int_fields as $_field) {
			if(isset($p[$_field])) {
				$sets[$_field] = (int) $p[$_field];
			}
		}

		$_str_fields = array('site_url', 'photo', 'name', 'description', 'username', 'image_url');
		foreach($_str_fields as $_field) {
			if(isset($p[$_field])) {
				$sets[$_field] = trim(strip_tags($p[$_field]));
			}
		}

		$ret = 0;
		if($sets) {
			$ret = DB::update($this->table, $sets, array('id' => $id));

						if(isset($sets['tid']) && $sets['tid'] > 0) {
				$tid = $sets['tid'] ? $sets['tid'] : $info['tid'];

				$this->set_topic_imageid($tid);
			} elseif ($vtid > 0) {
				$this->set_topic_verify_imageid($vtid,$id);
			}
		}
		
		if($ret && $info['uid']) {
			cache_db('rm', "{$info['uid']}-my_image-%", 1);
			cache_db('rm', "{$info['uid']}-get_photo_list-%", 1);
		}

		return $ret;
	}

	
	function delete($ids) {
		$p = array('ids' => $ids);
		$rets = $this->get($p);
		if(!$rets) return 0;

		$ret = 1;
		$tids = array();
		$uids = array();
		foreach($rets['list'] as $r) {
			$id = $r['id'];

			Load::lib('io', 1)->DeleteFile(topic_image($id, 'small'));
			Load::lib('io', 1)->DeleteFile(topic_image($id, 'photo'));
			Load::lib('io', 1)->DeleteFile(topic_image($id, 'original'));

			DB::query("delete from ".DB::table($this->table)." where `id`='$id'");

			if($r['tid'] > 0) {
				$tids[$r['tid']] = $r['tid'];
			}
			if($r['uid'] > 0) {
				$uids[$r['uid']] = $r['uid'];
			}
		}
		
		if($tids) {
			foreach($tids as $tid) {
				$this->set_topic_imageid($tid);
			}
		}
		if($uids) {
			foreach($uids as $uid) {
				cache_db('rm', "{$uid}-my_image-%", 1);
				cache_db('rm', "{$uid}-get_photo_list-%", 1);
			}
		}

		return $ret;
	}

	
	function set_tid($ids, $tid, $set_topic_imageid = 0)
	{
		$ids = $this->get_ids($ids);
		if(!$ids) return 0;

		$tid = max(0, (int) $tid);

		$ret = DB::query("update ".DB::table($this->table)." set `tid`='$tid' where `id` in ($ids)");

		if($tid > 0 && $set_topic_imageid) {
			$this->set_topic_imageid($tid);
		}

		return $ret;
	}

	
	function set_views($ids, $views)
	{
		$ids = $this->get_ids($ids);
		if(!$ids) return 0;

		$views = is_numeric($views) ? $views : 0;
		$sign = substr((string) $views, 0, 1);
		$views_set = " `views`=" . (('-' == $sign || '+' == $sign) ? "`views`+{$views}" : "$views") . " ";

		$ret = DB::query("update ".DB::table($this->table)." set $views_set where `id` in ($ids)");

		return $ret;
	}

	
	function get_ids($ids, $checks = array('uid' => -1, 'tid' => null), $ret_arr = 0)
    {
    	$_ids = array();
    	if(is_numeric($ids))
    	{
    		$_ids[$ids] = $ids;
    	}
    	elseif(is_string($ids))
    	{
    		$_rs = explode(',', $ids);
            foreach($_rs as $_r)
            {
                $_ids[$_r] = $_r;
            }
    	}
        else
        {
            if($ids)
            {
                $_ids = (array) $ids;
            }
        }

        $ids = array();
        if($_ids)
        {
            foreach($_ids as $_r)
            {
            	$_r = trim($_r , ' ,"\'');
                $_r = is_numeric($_r) ? $_r : 0;
                if($_r > 0)
                {
                    $ids[$_r] = $_r;
                }
            }
        }

        if($ids && $checks)
        {
        	        	$_checks = array('uid' => 1, 'tid' => 0);

        	if(is_numeric($checks))
        	{
        		$checks = array('uid' => $checks);
        		if($checks['uid'] >= $_checks['uid'])
        		{
        			$checks['tid'] = $_checks['tid'];
        		}
        	}

        	$check_sql = '';
        	foreach($_checks as $k => $_v)
        	{
        		if(isset($checks[$k]))
        		{
        			$v = $checks[$k];

        			if(is_numeric($v) && $v >= $_v)
        			{
        				$check_sql .= " and `$k`='$v' ";
        			}
        			elseif(is_string($v) && false !== strpos(" and ", strtolower($v)))
        			{
        				$check_sql .= " $v ";
        			}
        		}
        	}

            $query = DB::query("select `id` from ".DB::table($this->table)." where `id` in ('".implode("','", $ids)."') $check_sql ");
            $rets = array();
            while(false != ($rs = DB::fetch($query)))
            {
                $rets[$rs['id']] = $rs['id'];
            }

            $ids = $rets;
        }

    	if($ret_arr)
        {
        	return $ids;
        }
        else
        {
            return implode(",", $ids);
        }
    }

    
    function clear_invalid($time = 300)
    {
    	$p = array(
    		'tid' => 0,
    	);
    	if($time) {
    		$p['dateline_max'] = time() - $time;
    	}

    	$rets = $this->get($p);
    	if(!$rets) return 0;

    	$ids = array();
    	foreach($rets['list'] as $r) {
    		$ids[] = $r['id'];
    	}

    	return $this->delete($ids);
    }

    
    function set_topic_imageid($tid, $imageid = null)
    {
    	$tid = is_numeric($tid) ? $tid : 0;
    	if($tid < 1) return 0;

    	if(!isset($imageid))
    	{
    		$imageids = array();
    		$p = array(
    			'tid' => $tid,
    		);
    		$rets = $this->get($p);
    		if($rets)
    		{
    			foreach($rets['list'] as $r)
    			{
    				$imageids[$r['id']] = $r['id'];
    			}
    		}

    		$imageid = implode(",", $imageids);
    	}
    	else
    	{
    		$imageid = $this->get_ids($imageid);
    	}

    	return DB::query("update ".DB::table('topic')." set `imageid`='$imageid' where `tid`='$tid'");
    }
    
    
    function set_topic_verify_imageid($tid, $imageid = null){
    	$tid = is_numeric($tid) ? $tid : 0;
    	if($tid < 1) return 0;

    	return DB::query("update ".DB::table('topic_verify')." set `imageid`='$imageid' where `id`='$tid'");
    }

    
    function image_list($ids)
    {
    	$ids = $this->get_ids($ids, 0, 1);

    	$list = array();
    	if($ids)
    	{
    		foreach($ids as $id)
    		{
    			$image_small = topic_image($id, 'small', 0);
				$image_middle = topic_image($id, 'photo', 0);
    			$image_big = topic_image($id, 'original', 0);
    			list($iw, $ih) = @getimagesize(topic_image($id, 'original', 1));

    			$list[$id] = array(
    				'id' => $id,
    				'image' => $image_small,
    				'image_small' => $image_small,
					'image_middle' => $image_middle,
    				'image_big' => $image_big,
    				'image_original' => $image_big,
    				'image_width' => $iw,
    				'image_height' => $ih,
    			);
    		}
    	}

    	return $list;
    }

    
    function loadImage($_FILES,$name,$pname){
		
                
        
		$image_name = $pname.".jpg";
		$image_path = ROOT_PATH.'images/index/';
		$image_file = $image_path . $image_name;

		if (!is_dir($image_path))
		{
			Load::lib('io', 1)->MakeDir($image_path);
		}
		Load::lib('upload');
		$UploadHandler = new UploadHandler($_FILES,$image_path,$name,true);
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
			return false;
		}
		return 'images/index/'.$image_name;
    }
    
    function upload($p = array()) {
    	$sys_config = ConfigHandler::get();
    	
    	$pic_url = (($p['pic_url'] && false!==strpos($p['pic_url'], ':/'.'/')) ? $p['pic_url'] : '');
    	$p['pic_field'] = ($p['pic_field'] ? $p['pic_field'] : 'topic');
    	$pic_field = (($p['pic_field'] && $_FILES[$p['pic_field']]) ? $p['pic_field'] : '');
    	if(!$pic_url && !$pic_field) {
    		return array('error'=>'pic is empty', 'code'=>-1);
    	}
    	    	
    	$itemid = (is_numeric($p['itemid']) ? $p['itemid'] : 0);
    	$item = '';
    	if($itemid > 0) {
    		$item = $p['item'];
    	}
    	$uid = (int) ($p['uid'] ? $p['uid'] : MEMBER_ID);
    	$username = ($p['username'] ? $p['username'] : MEMBER_NICKNAME);
    	if($uid < 1) {
    		return array('error'=>'uid is invalid', 'code'=>-2);
    	}
    		
    			$image_id = $this->add($uid, $username, $item, $itemid);
		if($image_id < 1) {
			return array('error'=>'write database is invalid', 'code'=>-3);
		}
		
    			$image_path = RELATIVE_ROOT_PATH . 'images/topic/' . face_path($image_id);
		$image_name = $image_id . "_o.jpg";
		$image_file = $image_path . $image_name;
		$image_file_small = $image_path.$image_id . "_s.jpg";
		$image_file_photo = $image_path.$image_id . "_p.jpg";
		$image_file_temp = $image_path.$image_id . "_t.jpg"; 		if (!is_dir($image_path)) {
			Load::lib('io', 1)->MakeDir($image_path);
		}		

    	if($pic_field) {
    		if(empty($_FILES) || !$_FILES[$pic_field]['name']) {
    			return array('error'=>'FILES is empty', 'code'=>-4);
    		}
		
						Load::lib('upload');
			$UploadHandler = new UploadHandler($_FILES,$image_path,$pic_field,true,false);		
			$UploadHandler->setMaxSize(2048);
			$UploadHandler->setNewName($image_name);
			$ret = $UploadHandler->doUpload();
			
						if(!$ret) {
				$this->delete($image_id);
				
				$rets = $UploadHandler->getError();
				$ret = ($rets ? implode(" ", (array) $rets) : 'image upload is invalid');
				
				return array('error'=>$ret, 'code'=>-5);
			}
    	} elseif($pic_url) {
    		$temp_image = dfopen($pic_url);
    		if($temp_image) {
    			Load::lib('io', 1)->WriteFile($image_file,$temp_image);
    		} else {
    			return array('error'=>'image download is invalid','code'=>-6);
    		}
    	}
    	
    	    	if(!is_image($image_file)) {
    		return array('error'=>'image file is invalid', 'code'=>-7);
    	}
    	
    	@copy($image_file, $image_file_temp);
    	
    			list($image_width,$image_height,$image_type,$image_attr) = getimagesize($image_file);
		$thumbwidth = min($sys_config['thumbwidth'],$image_width);
		$thumbheight = min($sys_config['thumbheight'],$image_width);			
	
				$maxw = $sys_config['maxthumbwidth'];
		$maxh = $sys_config['maxthumbheight'];
		$result = makethumb($image_file, $image_file_small, $thumbwidth, $thumbheight, $maxw, $maxh, 0, 0, 0, 0, $sys_config['thumb_cut_type']);
		clearstatcache();
		if(!is_file($image_file)) {
			@copy($image_file_temp, $image_file);
		}
		
		$iw = $image_width;
		$ih = $image_height;
		if(!$sys_config['thumb_cut_type']) {
	    				if($image_width != $image_height) {		
				if($maxw > 300 && $maxh > 300 && ($iw > $maxw || $ih > $maxh)) {
										list($iw, $ih) = getimagesize($image_file);
				}
				
				$src_x = $src_y = 0;
				$src_w = $src_h = min($iw, $ih);
				if($iw > $ih) {
					$src_x = round(($iw - $ih) / 2);
				} else {
					$src_y = round(($ih - $iw) / 2);
				}
				$result = makethumb($image_file, $image_file_small, $thumbwidth, $thumbheight, 0, 0, $src_x, $src_y, $src_w, $src_h);
			}		
			clearstatcache();
			if (!$result && !is_file($image_file_small)) {
				@copy($image_file_temp, $image_file_small);
			}
		}
		
    			if($iw > 200) {
			$p_width = 200;
			$p_height = round(($ih*200)/$iw);
			$result = makethumb($image_file, $image_file_photo, $p_width, $p_height);
		}
		clearstatcache();
		if($iw <= 200 || (!$result && !is_file($image_file_photo))) {
			@copy($image_file_temp, $image_file_photo);
		}
				if($sys_config['watermark_enable']) {
			$this->watermark($image_file);
			clearstatcache();
			if(!is_file($image_file)) {
				@copy($image_file_temp, $image_file);
			}
		}

				$site_url = '';
		if($sys_config['ftp_on']) {
			$site_url = ConfigHandler::get('ftp','attachurl');
			
			$ftp_result = ftpcmd('upload',$image_file);
			if($ftp_result > 0) {
				ftpcmd('upload', $image_file_small);
				ftpcmd('upload', $image_file_photo);
				
				Load::lib('io', 1)->DeleteFile($image_file);
				Load::lib('io', 1)->DeleteFile($image_file_small);
				Load::lib('io', 1)->DeleteFile($image_file_photo);
				
				$image_file_small = $site_url . '/' . $image_file_small; 
			}
		}
		
				$image_size = filesize($image_file);
		$name = addslashes(basename($_FILES[$pic_field]['name']));		
		$p = array(
			'id' => $image_id,		
			'site_url' => $site_url,
			'photo' => $image_file,
			'name' => $name,
			'filesize' => $image_size,
			'width' => $image_width,
			'height' => $image_height,
		
        	'tid' => max(0, (int) $p['tid']),
        	'image_url' => $pic_url,
		);
		$this->modify($p);
		
				Load::lib('io', 1)->DeleteFile($image_file_temp);
		
		$p['src'] = $image_file_small;
		return $p;
    }
	
	
	function watermark($pic_path,$watermark='',$new_pic_path='') {	
		$sys_config = ConfigHandler::get();
		if (!$sys_config['watermark_enable']) {
			return false;
		}
		if(!is_image($pic_path)) {
			return false;
		}
		$ims = @getimagesize($pic_path);
		if(in_array($ims['mime'], array('image/gif'))) {
			return false;
		}
		if('' == trim($watermark)) {
			if($sys_config['watermark_contents']) {
				$watermark = '@'.MEMBER_NICKNAME;
			} else {
				$watermark = $sys_config['site_url'] . "/" . MEMBER_NAME;
			}
		}
		if('' == $new_pic_path) {
			$new_pic_path = $pic_path;
		}

		require_once(ROOT_PATH . 'include/lib/thumb.class.php');
		$_thumb = new ThumbHandler();
		$_thumb->setSrcImg($pic_path);
		$_thumb->setDstImg($new_pic_path);
		$_thumb->setImgCreateQuality(100);
	
				$_thumb->setMaskPosition($sys_config['watermark_position']);
				$_thumb->setMaskFontColor($sys_config['watermark_contents_color']);
				$_thumb->setMaskFontSize(max((int) $sys_config['watermark_contents_size'],12));
	
		if(is_file($watermark)) {
			$_thumb->setMaskImgPct(100);			
			$_thumb->setMaskImg($watermark);			
		} else {
						$mask_word = (string) $watermark;			
			if($sys_config['watermark_contents'] && is_file(RELATIVE_ROOT_PATH . 'images/jsg.ttf')) {
				$_thumb->setMaskFont(RELATIVE_ROOT_PATH . 'images/jsg.ttf');
				$mask_word = array_iconv($sys_config['charset'], 'utf-8', $mask_word);
			} elseif(preg_match('~[\x7f-\xff][\x7f-\xff]~', $mask_word)) {
				$mask_word = $sys_config['site_url'];
			}
			$_thumb->setMaskWord($mask_word);
		}
		
		return $_thumb->createImg(100);
	}
	
}

?>