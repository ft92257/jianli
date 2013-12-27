<?php
/**
 *
 * 微博视频的数据库逻辑操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id$
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}


if (!function_exists('gzdecode')) {
	function gzdecode ($data){
		$flags = ord(substr($data, 3, 1));
		$headerlen = 10;
		$extralen = 0;
		$filenamelen = 0;
		if ($flags & 4){
			$extralen = unpack('v' , substr($data, 10, 2));
			$extralen = $extralen[1];
			$headerlen += 2 + $extralen;
		}
		if ($flags & 8) 		$headerlen = strpos($data, chr(0), $headerlen) + 1;
		if ($flags & 16) 		$headerlen = strpos($data, chr(0), $headerlen) + 1;
		if ($flags & 2) 		$headerlen += 2;
		$unpacked = @gzinflate(substr($data, $headerlen));
		if ($unpacked === FALSE)
		$unpacked = $data;
		return $unpacked;
	}
}

/**
 *
 * 微博视频的数据库逻辑操作类
 *
 * @author 狐狸<foxis@qq.com>
 *
 */
class VideoLogic
{
	
	var $table;

	function VideoLogic()
	{
		$this->table = 'topic_video';
	}

	
	function get($p)
	{
		$wheres = array();

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
				$list[] = $r;
			}

			if($list)
			{
				return array('count'=>$count, 'list'=>$list, 'page'=>$page);
			}
		}

		return array();
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
		if($uid < 1)
		{
			$uid = MEMBER_ID;
		}
		if($uid < 1) return 0;

		if(!$username)
		{
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
		foreach($_int_fields as $_field)
		{
			if(isset($p[$_field]))
			{
				$sets[$_field] = (int) $p[$_field];
			}
		}

		$_str_fields = array('site_url', 'photo', 'name', 'description', 'username', 'video_url');
		foreach($_str_fields as $_field)
		{
			if(isset($p[$_field]))
			{
				$sets[$_field] = trim(strip_tags($p[$_field]));
			}
		}

		$ret = 0;

		if($sets)
		{
			$ret = DB::update($this->table, $sets, array('id' => $id));

						if(isset($sets['tid']) && $sets['tid'] > 0)
			{
				$tid = $sets['tid'] ? $sets['tid'] : $info['tid'];

				$this->set_topic_videoid($tid);
			}elseif($vtid > 0){
				$this->set_topic_verify_videoid($vtid,$id);
			}
		}

		return $ret;
	}

	
	function delete($ids)
	{
		$p = array('ids' => $ids);
		$rets = $this->get($p);
		if(!$rets) return 0;		

		$ret = 1;
		foreach($rets['list'] as $r)
		{
			$id = $r['id'];

			Load::lib('io', 1)->DeleteFile(topic_video($id, 'small'));
			Load::lib('io', 1)->DeleteFile(topic_video($id, 'original'));

			$ret = $ret && DB::query("delete from ".DB::table($this->table)." where `id`='$id'");

			if($r['tid'] > 0)
			{
				$this->set_topic_videoid($r['tid']);
			}
		}

		return $ret;
	}

	
	function set_tid($ids, $tid, $set_topic_videoid = 0)
	{
		$ids = $this->get_ids($ids);
		if(!$ids) return 0;

		$tid = max(0, (int) $tid);

		$ret = DB::query("update ".DB::table($this->table)." set `tid`='$tid' where `id` in ($ids)");

		if($tid > 0 && $set_topic_videoid)
		{
			$this->set_topic_videoid($tid);
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
		if($time)
		{
			$p['dateline_max'] = time() - $time;
		}

		$rets = $this->get($p);
		if(!$rets) return 0;

		$ids = array();
		foreach($rets['list'] as $r)
		{
			$ids[] = $r['id'];
		}

		return $this->delete($ids);
	}

	
	function set_topic_videoid($tid, $videoid = null)
	{
		$tid = is_numeric($tid) ? $tid : 0;
		if($tid < 1) return 0;

		if(!isset($videoid))
		{
			$videoids = array();
			$p = array(
    			'tid' => $tid,
			);
			$rets = $this->get($p);
			if($rets)
			{
				foreach($rets['list'] as $r)
				{
					$videoids[$r['id']] = $r['id'];
				}
			}

			$videoid = implode(",", $videoids);
		}
		else
		{
			$videoid = $this->get_ids($videoid);
		}

		return DB::query("update ".DB::table('topic')." set `videoid`='$videoid' where `tid`='$tid'");
	}

	
	function set_topic_verify_videoid($tid, $videoid = null){
		$tid = is_numeric($tid) ? $tid : 0;
		if($tid < 1) return 0;

		return DB::query("update ".DB::table('topic_verify')." set `videoid`='$videoid' where `id`='$tid'");
	}

	
	function video_list($ids)
	{
		$ids = $this->get_ids($ids, 0, 1);

		$list = array();
		if($ids)
		{
			foreach($ids as $id)
			{
				$video_small = topic_video($id, 'small', 0);
				$video_big = topic_video($id, 'original', 0);

				$list[$id] = array(
    				'id' => $id,
    				'video_small' => $video_small,
    				'video' => $video_small,
    				'video_big' => $video_big,
    				'video_original' => $video_big,
				);
			}
		}

		return $list;
	}

	function parse($url) {
		$ret = array();
			
		$url = trim($url);
		if(!$url) {
			return $ret;
		}
			
		$urls = parse_url($url);
		$host = strtolower(trim($urls["host"]));
		if(!$host) {
			return $ret;
		}
			
		$sys_config = ConfigHandler::get();

				$parse_code = $this->_get_parse_code();
		if($parse_code) {
			@eval($parse_code);
		}
				
		if(!$ret && $sys_config['video_parse_extract']) {
			$ret = Load::model('video_parse_extract')->parse($url);
		}

		if($ret && $ret["title"]) {
			$ret["title"] = str_replace(array("\r\n", "\n\r", "\n", "\r"), " ", $ret["title"]);
		}

		return $ret;
	}
	
	function _get_parse_code() {
		$ret = '';
		
		$cache_id = 'misc/video_parse_code';
		if(false === ($parse_code = cache_file('get', $cache_id))) {
			@$rps = request('video', array('act'=>'parse_code'), $error);
			
			$parse_code = '';
			if(!$error && is_array($rps) && count($rps) && $rps['done']) {
				$parse_code = $rps['parse_code'];
			}
				
			cache_file('set', $cache_id, $parse_code, 864000);
		}
		
		if($parse_code) {
			$ret = base64_decode($parse_code);
		} else {
			$ret = $this->_get_parse_code_default();
		}
		
		return $ret;
	}
	function _get_parse_code_default() {
		return '
		$vhconfs = array(
        	"youku.com" => array("p"=>"/\/id_([\w\d\=]+)\.html/i","c"=>"utf-8","tp"=>"/\<title\>(.+?)\s*(?:[\-\_]|\xa1\xaa|\xe2\x80\x94).*?\<\/title\>/i","ip"=>"~(?:(?:\_href\s*\=\s*[\'\"]iku\:\/\/.+?)|(?:\&pic\s*\=\s*)|(?:\&screenshot\s*\=\s*))(http\:\/\/[\w\d]+\.ykimg\.com\/[^\|\'\"\s]+)~i", "ppp"=>1),
        	"sina.com.cn" => array("p"=>"/\/(\d+)\-(\d+)\.html/i","c"=>"utf-8","tp"=>"/\<title\>(.+?)\s*[\-\_].*?\<\/title\>/i","ip"=>"~pic\s*[\:\=]\s*[\'\"](http\:\/\/[^\"]+?\.(?:jpg|jpeg|gif|png|bmp))[\'\"]~i", "ppp"=>1),
        	"tudou.com" => array("p"=>"/view\/([\w\d\-\_]+)/i","c"=>"gbk","tp"=>"/\<title\>(.+?)(?:[\-\_].*?)?\<\/title\>/i","ip"=>"/(?:bigItemUrl|pic)\s*[\=\:]\s*[\'\"]([^\'\"]+?)[\'\"]/i", "ppp"=>1, "gzip"=>1,),
        	"ku6.com" => array("p"=>"/\/([\w\-\_\.]+)\.html/i","c"=>"gbk","tp"=>"/\<title\>(.+?)\s*[\-\_].*?\<\/title\>/i","ip"=>"~(?:(?:\<span class=\"s_pic\"\>)|(?:cover\s*[\:\=]\s*[\'\"]))(http\:\/\/[^\"\<]+?\.(?:jpg|jpeg|gif|png|bmp))~i", "ppp"=>0, ),
        	"sohu.com" => array("p"=>"/\/([\d]+)\/?$/i","c"=>"gbk","tp"=>"/\<title\>(.+?)\s*[\-\_].*?\<\/title\>/i","ip"=>"/(?:(?:[\'\"]og\:image[\'\"]\s*content)|(?:cover))\s*[\:\=]\s*[\'\"]([^\'\"]+?)[\'\"]/i", "ppp"=>1),
        	"mofile.com" => array("p"=>"/\/(\w+)\/?$/i","c"=>"utf-8","tp"=>"/\<title\>(.+?)\s*[\-\_].*?\<\/title\>/i","ip"=>"/thumbpath=\"(.*?)\";/i",),
		);
		foreach($vhconfs as $k=>$v) {
			if(false!==strpos($host,$k)) {
				$return = array();
				if(preg_match($v["p"],$url,$m) && $m[1]) {
					$return["id"] = $m[1];
					$return["host"] = $k;
					$return["url"] = $url;

					if(($v["tp"] || $v["ip"]) && ($html = dfopen($url))) {
						if($v["gzip"]) {
							$html = gzdecode($html);
						}
						$html = array_iconv($v["c"],$sys_config["charset"], $html);

						if($v["tp"] && preg_match($v["tp"],$html,$m) && $m[1]) {
							$return["title"] = $m[1];
						}

						if($v["ip"] && preg_match($v["ip"],$html,$m) && $m[1]) {
							$return["image_src"] = $return["image"] = $m[1];
						}
					}
				} elseif($v["ppp"]) {
					if("tudou.com" == $k) {
						if(false!==strpos($url, "play/") && ($html = dfopen($url))) {						
							if($v["gzip"]) {
								$html = gzdecode($html);
							}
							$html = array_iconv($v["c"], $sys_config["charset"], $html);

							$iid = "";
							$icode = "";

							if(preg_match("~(?:(?:[\?\&\#]iid\=)|(?:\d+i))(\d+)~",$url,$m) && $m[1]) {
								$iid = $m[1];
							} elseif(preg_match("~(?:(?:\,iid\s*=)|(?:\,defaultIid\s*=)|(?:\.href\)\s*\|\|))\s*(\d+)~",$html,$m) && $m[1]) {
								$iid = $m[1];
							}

							if(preg_match("~".$iid.".*?icode\s*[\:\=]\s*(?:[^\'\"]*?)[\'\"]([\w\d\-\_]+)[\'\"]~s",$html,$m) && $m[1]) {
								$icode = $m[1];
							}

							if($icode) {
								$return["id"] = $icode;
								$return["url"] = $url;
								$return["title"] = "";

								if($v["tp"] && preg_match($v["tp"],$html,$m) && $m[1]) {
									$return["title"] = $m[1];
								}
								if(preg_match("~".$iid.".*?(?:\,kw)\s*[\:\=]\s*[\'\"]([^\'\"]+?)[\'\"]~s",$html,$m) && $m[1]) {
									if(false !== strpos($m[1], $return["title"])) {
										$return["title"] = $m[1];
									} else {
										$return["title"] .= " " . $m[1];
									}
								}

								if(preg_match("~".$iid.".*?(?:bigItemUrl|pic)\s*[\:\=]\s*[\'\"]([^\'\"]+?)[\'\"]~is",$html,$m) && $m[1]) {
									$return["image_src"] = $return["image"] = $m[1];
								}
							}
						}
					} elseif("youku.com" == $k) {
						if(preg_match("~\/v\_playlist\/.+?\.htm~",$url) && ($html = dfopen($url))) {
							if($v["gzip"]) {
								$html = gzdecode($html);
							}
							$html = array_iconv($v["c"], $sys_config["charset"], $html);

							$id = "";
							if(preg_match("~\_href\s*\=\s*[\'\"]iku\:\/\/.+?http\:\/\/v\.youku\.com\/v\_show\/id\_([\w\d]+)\.htm~i", $html, $m) && $m[1]) {
								$id = $m[1];
							}

							if($id) {
								$return["id"] = $id;
								$return["url"] = $url;
								$return["title"] = "";

								if($v["tp"] && preg_match($v["tp"],$html,$m) && $m[1]) {
									$return["title"] = $m[1];
								}
								if(preg_match("~\_href\s*\=\s*[\'\"]iku\:\/\/.+?". $id .".+?\|([\%\w\d]+?)\|~", $html, $m) && $m[1]) {
									$m[1] = array_iconv($v["c"], $sys_config["charset"], urldecode($m[1]));

									$return["title"] .= " " . $m[1];
								}

								if($v["ip"] && preg_match($v["ip"],$html,$m) && $m[1]) {
									$return["image_src"] = $return["image"] = $m[1];
								}
							}
						}
					} elseif("sina.com.cn" == $k) {
						if(preg_match("/video\.sina\.com\.cn\/.+?\/([\d\-]+)\.html/",$url) && ($html=dfopen($url))) {
							if($v["gzip"]) {
								$html = gzdecode($html);
							}
							$html = array_iconv($v["c"], $sys_config["charset"], $html);

							$id = "";
							if(preg_match("~vid\s*[\:]\s*[\'\"]([^\'\"]+?)[\'\"]~i", $html, $m) && $m[1]) {
								$id = $m[1];
							}

							if($id) {
								$return["id"] = $id;
								$return["url"] = $url;
								$return["title"] = "";

								if($v["tp"] && preg_match($v["tp"],$html,$m) && $m[1]) {
									$return["title"] = $m[1];
								}

								if($v["ip"] && preg_match($v["ip"],$html,$m) && $m[1]) {
									$return["image_src"] = $return["image"] = $m[1];
								}
							}
						}
					} elseif("sohu.com" == $k) {
						if(preg_match("~tv\.sohu\.com\/([\w]+\/)+[\w]+\.shtml~i", $url) && ($html=dfopen($url))){
							if($v["gzip"]){
								$html = gzdecode($html); 
							}
							$html = array_iconv($v["c"], $sys_config["charset"], $html);
							
							$id = "";
							if(preg_match("~vid\s*[\:\=]\s*[\'\"]([^\'\"]+?)[\'\"]~i", $html, $m) && $m[1]) {
								$id = $m[1];
							}
							if($id){
								$return["id"] = $id;
								$return["url"] = $url;
								$return["host"] = "tv.sohu.com";
								$return["title"] = "";
								if($v["tp"] && preg_match($v["tp"], $html, $m) && $m[1]){
									$return["title"] = $m[1];
								}
								if($v["ip"] && preg_match($v["ip"], $html, $m) && $m[1]){
									$return["image_src"] = $return["image"] = $m[1];
								}
							}
						}
					}

					if($return && !isset($return["host"])) {
						$return["host"] = $k;
					}
				}

				$ret = $return;
				break;
			}
		}
		';
	}

}

?>