<?php
/*********************************************************
 *文件名： attach.logic.php
 *作  者： 狐狸<foxis@qq.com>
 *创建时间： 2012年01月08日
 *修改时间：
 *功能描述：微博上传附件逻辑操作
 *使用方法：

 ******************************************************/
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

/**
 *
 * 微博上传附件的数据库逻辑操作类
 *
 * @author 狐狸<foxis@qq.com>
 *
 */
class AttachLogic
{
	
	var $table;

	function AttachLogic()
	{
		$this->table = 'topic_attach';
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
		if(isset($p['item']))
		{
			$wheres[] = " `item`='{$p['item']}' ";
		}
		if(isset($p['itemid']))
		{
			$p['itemid'] = max(0, (int) $p['itemid']);
			if($p['itemid'] > 0) $wheres[] = " `itemid`='{$p['itemid']}' ";
		}
		if(isset($p['itemids']))
		{
			$p['itemids'] = $this->get_ids($p['itemids'], 0);
			if($p['itemids']) $wheres[] = " `itemid` in ({$p['itemids']}) ";
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
				$r['type'] = 'images/filetype/'.$r['filetype'].'.gif';
				$r['time'] = my_date_format($r['dateline']);
				$r['size'] = ($r['filesize'] > 1024*1024) ? round($r['filesize']/(1024*1024),2).'MB' : round($r['filesize']/1024,1).'KB';
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

		$sets = array();

		$_int_fields = array('tid', 'filesize', 'itemid', 'uid', 'dateline', 'download', 'score');
		foreach($_int_fields as $_field)
		{
			if(isset($p[$_field]))
			{
				$sets[$_field] = (int) $p[$_field];
			}
		}

		$_str_fields = array('filetype', 'description', 'name', 'file', 'site_url', 'username', 'item');
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

						if(isset($sets['tid']))
			{
				$tid = $sets['tid'] ? $sets['tid'] : $info['tid'];

				$this->set_topic_attachid($tid);
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
			Load::lib('io', 1)->DeleteFile(topic_attach($id));
			$ret = $ret && DB::query("delete from ".DB::table($this->table)." where `id`='$id'");
			if($r['tid'] > 0)
			{
				$this->set_topic_attachid($r['tid']);
			}
		}
		return $ret;
	}

	
	function set_tid($ids, $tid, $set_topic_attachid = 0)
	{
		$ids = $this->get_ids($ids);
		if(!$ids) return 0;

		$tid = max(0, (int) $tid);

		$ret = DB::query("update ".DB::table($this->table)." set `tid`='$tid' where `id` in ($ids)");

		if($tid > 0 && $set_topic_attachid)
		{
			$this->set_topic_attachid($tid);
		}

		return $ret;
	}

	
	function set_downloads($ids, $downloads)
	{
		$ids = $this->get_ids($ids);
		if(!$ids) return 0;

		$downloads = is_numeric($downloads) ? $downloads : 0;
		$sign = substr((string) $downloads, 0, 1);
		$downloads_set = " `download`=" . (('-' == $sign || '+' == $sign) ? "`download`+{$downloads}" : "$downloads") . " ";

		$ret = DB::query("update ".DB::table($this->table)." set $downloads_set where `id` in ($ids)");

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

    
    function set_topic_attachid($tid, $attachid = null)
    {
    	$tid = is_numeric($tid) ? $tid : 0;
    	if($tid < 1) return 0;

    	if(!isset($attachid))
    	{
    		$attachids = array();
    		$p = array(
    			'tid' => $tid,
    		);
    		$rets = $this->get($p);
    		if($rets)
    		{
    			foreach($rets['list'] as $r)
    			{
    				$attachids[$r['id']] = $r['id'];
    			}
    		}

    		$attachid = implode(",", $attachids);
    	}
    	else
    	{
    		$attachid = $this->get_ids($attachid);
    	}

    	return DB::query("update ".DB::table('topic')." set `attachid`='$attachid' where `tid`='$tid'");
    }

    
    function attach_list($ids)
    {
    	$ids = $this->get_ids($ids, 0, 1);

    	$list = array();
    	if($ids)
    	{
    		$query = DB::query("SELECT * FROM ".DB::table('topic_attach')." WHERE id IN(".jimplode($ids).")");
			while($attach = DB::fetch($query))
			    		{
    							$attach_img = $attach['filetype'];
				$attach_name = $attach['name'];
				$attach_size = $attach['filesize'];
				$attach_down = $attach['download'];
				$attach_size = ($attach_size > 1024*1024) ? round($attach_size/(1024*1024),2).'MB' : ($attach_size == 0 ? '未知' : round($attach_size/1024,1).'KB');
				$attach_score = $attach['score'];
				$attach_file = RELATIVE_ROOT_PATH . $attach['file'];
																																    				    			$list[$attach['id']] = array(
    				'id' => $attach['id'],
    				'attach_img' => 'images/filetype/'.$attach_img.'.gif',
    				'attach_file' => $attach_file,
					'attach_name' => $attach_name,
					'attach_score' => $attach_score,
					'attach_down' => $attach_down,
					'attach_size' => '大小:'.$attach_size,
    			);
    		}
    	}

    	return $list;
    }

}

?>