<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename other.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-08-17 19:12:46 2011071811 1194900299 17191 $
 *******************************************************************/



if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class OtherLogic
{

	var $Config;
	var $DatabaseHandler;

	
	function OtherLogic()
	{
		$this->DatabaseHandler = &Obj::registry("DatabaseHandler");
		$this->Config = &Obj::registry("config");
	}


	

	function TopicFavorite($uid=0,$tid=0,$act='')
	{
		$timestamp = time();
		
		$uid = (is_numeric($uid) ? $uid : 0);
		if($uid < 1) {
			return  "请登录";
		} 	
		
		$tid = (is_numeric($tid) ? $tid : 0);
		if ($tid < 1) {
			return  "请指定一个微博";
		}

		$topic_info = DB::fetch_first("select * from ".DB::table('topic')." where `tid`='$tid' ");
		if(!$topic_info) {
			return "指定的微博已经不存在了";
		}
		 
		 
		$sql = "select * from `".TABLE_PREFIX."topic_favorite` where `uid`='{$uid}' and `tid`='{$tid}'";
		$query = $this->DatabaseHandler->Query($sql);
		$is_favorite = ($query->GetNumRows()>0);
		if('check' == $act) {
			return ($is_favorite ? 1 : 0);
		}
		$topic_favorite = $query->GetRow();
		if('info' == $act) {
			return $topic_favorite;
		}

		$sql = "select count(*) as `topic_favorite_count` from `".TABLE_PREFIX."topic_favorite` where `uid`='{$uid}'";
		$query = $this->DatabaseHandler->Query($sql);
		$row = $query->GetRow();
		if($row) {
			$sql = "update `".TABLE_PREFIX."members` set `topic_favorite_count`='{$row['topic_favorite_count']}' where `uid`='{$uid}'";
			$this->DatabaseHandler->Query($sql);
		}

		if('add' == $act)
		{
			if(!$is_favorite) {
				$sql = "insert into `".TABLE_PREFIX."topic_favorite` (`uid`,`tid`,`tuid`,`dateline`) values ('{$uid}','{$tid}','{$topic_info['uid']}','{$timestamp}')";
				$this->DatabaseHandler->Query($sql);

				$sql = "update `".TABLE_PREFIX."members` set `topic_favorite_count`=`topic_favorite_count`+1 where `uid`='{$uid}'";
				$this->DatabaseHandler->Query($sql);

				$sql = "update `".TABLE_PREFIX."members` set `favoritemy_new`=`favoritemy_new`+1 where `uid`='{$topic_info['uid']}'";
				if($uid!=$topic_info['uid']) {
					$this->DatabaseHandler->Query($sql);
				}
			}
			return "已收藏";
		}

		if('delete' == $act)
		{
			if ($is_favorite) {
				$id = $topic_favorite['id'];

				$sql = "delete from `".TABLE_PREFIX."topic_favorite` where `id`='{$id}'";
				$this->DatabaseHandler->Query($sql);
					
				jsg_member_update_count($uid, 'topic_favorite_count', '-1');
			}
			return "已取消";
		}


	}


	

	function Hot_User($hot_type='',$limit=0)
	{
		;
	}


	

	function AddFavoriteTag($uid=0,$tagids=0)
	{

		if($tagids)
		{
			$where_list = "where `id` in ('".implode("','",$tagids)."')";

						$sql = "select `id`,`name` from `".TABLE_PREFIX."tag` {$where_list}";
			$query = $this->DatabaseHandler->Query($sql);
			$tagname = array();
			while ($row=$query->GetRow()) {
				$tagname[] = $row;
			}
		}
			
		$timestamp = time();
			
		if ($tagname) {

			foreach ($tagname as $tag) {
					
								$sql = "select `id`,`tag` from `".TABLE_PREFIX."tag_favorite` where `tag` = '{$tag['name']}' and `uid` = '".MEMBER_ID."' ";
				$query = $this->DatabaseHandler->Query($sql);
				$row=$query->GetRow();
					
								if(empty($row))
				{
					$sql = "insert into `".TABLE_PREFIX."tag_favorite` (`uid`,`tag`,`dateline`) values ('{$uid}','{$tag['name']}','{$timestamp}')";
					$this->DatabaseHandler->Query($sql);

					$sql = "update `".TABLE_PREFIX."members` set `tag_favorite_count`=`tag_favorite_count`+1 where `uid`='{$uid}'";
					$this->DatabaseHandler->Query($sql);

										$sql = "update `".TABLE_PREFIX."tag` set `tag_count`=`tag_count`+1 where `id`='{$tag['id']}'";
					$this->DatabaseHandler->Query($sql);
				}
			}
			return 1;
		}
		else{

			return -1;
		}
			
		
	}


	
	function qmd_list($uid=0,$pic_path='')
	{
		$uid = is_numeric($uid) ? $uid : 0;

		if ($this->Config['is_qmd']) {
			
			$TopicLogic = Load::logic('topic', 1);

						$condition = " where `type` = 'first' and `uid`='{$uid}' order by `tid` desc limit 1 ";
			$topic_list = $TopicLogic->Get($condition);

			if($topic_list) {
				foreach ($topic_list as $v) {
					$topic = $v;
				}
			} else {
				return false;
			}
			
			$temp_face = ((true === UCENTER_FACE && true === UCENTER) ? $topic['face_small'] : $topic['face_original']);
			if(false === strpos($temp_face, ':/'.'/')) {
				$member_face = $temp_face;
			} else {
				$field = 'temp_face';
				$image_path = RELATIVE_ROOT_PATH . './data/cache/' . $field . '/' . face_path($topic['uid']);
				$image_file_small = $image_path.$topic['uid'] . "_s.jpg";

				if(!file_exists($image_file_small)) {
					if (!is_dir($image_path)) {
						Load::lib('io', 1)->MakeDir($image_path);
					}

					$temp_image = dfopen($temp_face, 999999, '', '', true, 5, $_SERVER['HTTP_USER_AGENT']);
					if($temp_image) {
						Load::lib('io', 1)->WriteFile($image_file_small, $temp_image);
					}
					
					if(!is_image($image_file_small)) {
						@copy(ROOT_PATH . 'images/noavatar.gif', $image_file_small);
					}
				}
					
				$member_face =  $image_file_small;
			}

			$content = strip_tags($topic['content']);
			$content = str_replace(array("\r\n", "\n", "\r", "\t", "  "), ' ', $content);
			$content = cut_str($content,74);

			$qmd_data = array(
    			'uid' => $topic['uid'],
    			'nickname' => $topic['nickname'],		    			
    			'validate' => $topic['validate'],
    			'face' => $member_face,
    			'content' => $content,
    			'dateline' => date('m月d日 H:i', $topic['addtime']),		    
			);

			$qmd_list = $this->qmd_img_list($pic_path, $qmd_data);

			return $qmd_list;
		}

		return false;

	}

	
	function qmd_img_list($pic_path='',$qmd_data)
	{
		if(!$qmd_data) return '';

				$topic_content1 = cutstr($qmd_data['content'],43);
		$topic_content2 = str_replace($topic_content1,'',$qmd_data['content']);

		$content1 = array_iconv($this->Config['charset'],'utf-8',$topic_content1);
		$content2 = array_iconv($this->Config['charset'],'utf-8',$topic_content2);

			
				$topic_url = $this->Config['site_url'];

				$topic_date = array_iconv($this->Config['charset'],'utf-8',$qmd_data['dateline']);

				$nickname = array_iconv($this->Config['charset'],'utf-8',$qmd_data['nickname']);

		
				$bg = imagecreatefromjpeg($pic_path);

				$content_white = imagecolorallocate($bg, 80, 80, 80);

		$font_white = imagecolorallocate($bg, 125, 125, 125);

				$url_white = imagecolorallocate($bg, 75, 167, 213);

			
				imagettftext($bg, 10,0, 108, 23, $url_white, $this->Config['qmd_fonts_url'], $nickname);

		imagettftext($bg, 9, 0, 108, 43, $content_white, $this->Config['qmd_fonts_url'], strip_tags($content1));
		imagettftext($bg, 9, 0, 108, 64, $content_white, $this->Config['qmd_fonts_url'], strip_tags($content2));
			
		imagettftext($bg, 9, 0, 288, 80, $font_white, $this->Config['qmd_fonts_url'], $topic_date);
		imagettftext($bg, 9, 0, 108, 94, $url_white, $this->Config['qmd_fonts_url'], $topic_url);
		


				$user_src = $qmd_data['face'];
		if(!is_image($user_src)) return '';

			
		list($width,$height, $image_type) = getimagesize($user_src);
			
				$user_src_im = null;
		if(1 == $image_type) {
			$user_src_im = imagecreatefromgif($user_src);
		} elseif (3 == $image_type) {
			$user_src_im = imagecreatefrompng($user_src);
		} else {
			$user_src_im = imagecreatefromjpeg($user_src);
		}
			
				if (true === UCENTER_FACE && true === UCENTER) {
						if($height <= 60) {
				$new_face_width = 75 ;
				$new_face_height = 75;
			} else {
				$new_face_width = round($width / 1.5);
				$new_face_height = round($height / 1.5);
			}
		} else {
			$new_face_width = 75;
			$new_face_height = 75;
		}


		
		$thumb = imagecreatetruecolor($new_face_width,$new_face_height);
		imagecopyresampled($thumb,$user_src_im,0,0,0,0,$new_face_width,$new_face_height,$width,$height);

				$box = imagecreatefrompng('./images/kuang.png');
		imagecopyresampled($thumb,$box,0,0,0,0,$new_face_width,$new_face_height,80,80);

				$dst_x = 20;
		$dst_y  = 12;


		$src_x = 0;
		$src_y = 0;

		$alpha = 100;

				imagecopymerge($bg,$thumb,$dst_x,$dst_y,$src_x,$src_y,$new_face_width,$new_face_height,$alpha);


				$qmd_file_url = '';
		if(!$this->Config['ftp_on']) {
			$qmd_file_url = $this->Config['qmd_file_url'];
		}
			
		$image_path = $qmd_file_url. face_path($qmd_data['uid']);
		$image_file =  $image_path . $qmd_data['uid'] . '_o.gif';
		if(!is_dir($image_path)) {
			Load::lib('io', 1)->MakeDir($image_path);
		}
		
				if(function_exists('imagegif')) {
			imagegif($bg,$image_file);
		} elseif(function_exists('imagepng')) {
			imagepng($bg, $image_file);
		} elseif (function_exists('imagejpeg')) {
			imagejpeg($bg, $image_file);
		} else {
			return '';
		}

		imagedestroy($bg);
		imagedestroy($thumb);
		imagedestroy($box);
		imagedestroy($v);
		imagedestroy($user_src_im);

		Load::lib('io', 1)->DeleteFile($user_src);
			
		$site_url = '';
		if($this->Config['ftp_on']) {
			$site_url = ConfigHandler::get('ftp','attachurl');

			$ftp_result = ftpcmd('upload',$image_file);
			if($ftp_result > 0) {
				Load::lib('io', 1)->DeleteFile($image_file);
					
				$image_file = $site_url . '/' . $image_file;
			}
		}


		$sql = "update `".TABLE_PREFIX."members` set `qmd_url`='{$image_file}' where `uid`='".$qmd_data['uid']."' ";
		$this->DatabaseHandler->Query($sql);


		return $image_file;

	}

	
	function autoCheckMedal($medalid,$uid=0){
		$dateline = time();

		$uid = $uid ? $uid : MEMBER_ID;
				$sql = " select * from `".TABLE_PREFIX."members` Where `uid`='$uid'";
		$query = $this->DatabaseHandler->Query($sql);
		$members=$query->GetRow();

				$sql = " select * from `".TABLE_PREFIX."medal` Where `id` = '{$medalid}'";
		$query = $this->DatabaseHandler->Query($sql);
		$medal_list=$query->GetRow();
		$medal_value = @unserialize($medal_list['conditions']);

				if($medal_value['type'] == 'topic')
		{
						$sql ="select * from `".TABLE_PREFIX."topic` where `uid` = '".$uid."' and `type` = 'first' order by `tid` desc limit 1";
			$query = $this->DatabaseHandler->Query($sql);
			$topic_list=$query->GetRow();

			if($topic_list){
				$return = $this->_chackmdealday($topic_list['dateline'],$medal_value['day'],'first',$uid);
			} else{
				$return = '2';
			}

		}

				if($medal_value['type'] == 'reply')
		{
			$sql = " select * from `".TABLE_PREFIX."topic` Where `type` = 'reply' and `uid` = '".$uid."' order by 'dateline' desc limit 0,1";
			$query = $this->DatabaseHandler->Query($sql);
			$reply_list=$query->GetRow();

			if($reply_list){
				$return = $this->_chackmdealday($reply_list['dateline'],$medal_value['day'],'reply',$uid);
			} else{
				$return = '2';
			}
		}

				if ($medal_value['type'] == 'invite') {
			if ($medal_value['invite'] > $members['invite_count']) {
				$return = 2;
			} else{
				$return =  1;
			}
		}

				if ($medal_value['type'] == 'fans') {
			if($medal_value['fans'] > $members['fans_count']) {
				$return = $medal_value['fans'] .'>'. $members['fans_count'];
			} else{
				$return =  1;
			}
		}

				if ($medal_value['type'] == 'sign') {
			$credits = $this->Config['credits_filed'];
			if($medal_value['sign'] > $members[$credits]) {
				$return = 2;
			} else{
				$return =  1;
			}
		}

				if($medal_value['type'] == 'tag')
		{
			$tag = trim($medal_value['tagname']);

			$sql = " select `id`,`name` from `".TABLE_PREFIX."tag` Where `name` = '{$tag}' ";
			$query = $this->DatabaseHandler->Query($sql);
			$tags=$query->GetRow();

			if($tags)
			{
				$sql = " select `item_id`,`tag_id` from `".TABLE_PREFIX."topic_tag` Where `tag_id` = '{$tags['id']}' ";
				$query = $this->DatabaseHandler->Query($sql);
				$topicids = array();
				while($row=$query->GetRow())
				{
					$topicids[$row['item_id']] = $row['item_id'];
				}
			}
			if($topicids)
			{
				$sql = " select `tid`,`uid`,`content` from `".TABLE_PREFIX."topic` where `tid` in ('".implode("','",$topicids)."') and `uid` = '".$uid."' limit 0,1";
				$query = $this->DatabaseHandler->Query($sql);
				$topiclist=$query->GetRow();
			}
			if($topiclist){
				$return = 1;
			}
			else{
				$return = '2';
			}

		}
			
				if($return == 1)
		{
			$return = $this->giveUserMedal($medalid,$members);
		}

		return $return;
	}

	
	function giveUserMedal($medalid,$members){
		$sql = " select * from `".TABLE_PREFIX."user_medal` where `medalid` = '{$medalid}' and `uid` = '".$members['uid']."' limit 0,1";
		$query = $this->DatabaseHandler->Query($sql);
		$user_medal=$query->GetRow();
		if($user_medal)
		{
			return 3;
		}
				$sql = "insert into `".TABLE_PREFIX."user_medal` (`uid`,`nickname`,`medalid`,`dateline`) values ('{$members['uid']}','{$members['nickname']}','{$medalid}','".time()."')";
		$query = $this->DatabaseHandler->Query($sql);

		if(!empty($members['medal_id']))
		{
						$sql = "select * from `".TABLE_PREFIX."user_medal` where `uid` = '{$members['uid']}'";
			$query = $this->DatabaseHandler->Query($sql);
			$user_medal_id = array();
			while (false != ($row = $query->GetRow())) {
				$user_medal_id[] = $row['medalid'];
			}

			$user_medal_id = implode(",",$user_medal_id);
		}

		$user_medal = $user_medal_id ? $user_medal_id : $medalid;

				$sql = "update `".TABLE_PREFIX."members` set  `medal_id`='{$user_medal}'  where `uid` = '".$members['uid']."'";
		$update = $this->DatabaseHandler->Query($sql);

				$sql = "update `".TABLE_PREFIX."medal` set  `medal_count`=`medal_count`+1  where `id` = '{$medalid}'";
		$this->DatabaseHandler->Query($sql);
		return 1;
	}

		function _chackmdealday($date_time=0,$chackday=0,$check_type='',$uid=0){
		$uid = $uid ? $uid : MEMBER_ID;
		$endtime = $date_time ? $date_time : time();
		$topic_start_time = $endtime - (86400 * $chackday);
			
		$sql = " select `dateline`,`tid` from `".TABLE_PREFIX."topic` Where `dateline` >= '{$topic_start_time}' and `dateline` <= '{$endtime}' and `type` = '{$check_type}' and `uid` = '".$uid."' order by 'dateline' desc ";
		$query = $this->DatabaseHandler->Query($sql);
		$topic_date =array();
		while (false != ($row = $query->GetRow())){
			$topic_date[] = date("Ymd",$row['dateline']);
		}
			
		for ($j = 0; $j < count($topic_date); $j++){
			if($topic_date[$j] == $topic_date[$j+1]){
				unset($topic_date[$j+1]);
			}
		}

		$user_topic_date = array_unique($topic_date);
		$user_topic_date = implode(',',$user_topic_date);
		$user_topic_date = explode(',',$user_topic_date);
		sort($user_topic_date);

		if(count($user_topic_date) < $chackday){
			return 2;
		}

		if($chackday > 1){
			for($i=0; $i < count($user_topic_date) - 1  ; $i++){
				if($user_topic_date[$i] + 1 != $user_topic_date[$i+1]){
					return 2;
				}
			}
			return true;
		}

				elseif($user_topic_date){
			return true;
		}else{
			return 2;
		}
	}

	
	function getSignTag(){
		$tag_arr = array();
		$tag = $this->DatabaseHandler->ResultFirst("select tag from ".TABLE_PREFIX."sign_tag limit 1");
		if($tag){
			$tag_arr = explode("\r\n",$tag);
		}else{
			return array();
		}

		return $tag_arr;
	}

}
?>