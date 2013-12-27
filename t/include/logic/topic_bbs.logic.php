<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename topic_bbs.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-04 18:49:37 224708704 1191875050 21141 $
 *******************************************************************/

 


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class TopicBbsLogic
{
	
    var $MemberHandler;
	var $user_lists = array();

    
    var $Config;
    
    var $TopicLogic;

	var $BbsDatabase;

	var $BbsType;
    
	
	function TopicBbsLogic()
	{
		$this->MemberHandler = &Obj::registry("MemberHandler");
		$this->Config = &Obj::registry("config");
		
		$this->TopicLogic = Load::logic('topic', 1);

				if(@is_file(ROOT_PATH . 'setting/dzbbs.php') && $this->Config['dzbbs_enable']){
			include ROOT_PATH . 'setting/dzbbs.php';
			$this->BbsDatabase = new MySqlHandler($config['dzbbs']['db_host'],$config['dzbbs']['db_port']);
			$this->BbsDatabase->Charset($config['dzbbs']['charset']);
			$this->BbsDatabase->doConnect($config['dzbbs']['db_user'],$config['dzbbs']['db_pass'],$config['dzbbs']['db_name'],$this->Config['db_persist']);
			Obj::register('BbsDatabase',$this->BbsDatabase);
			define('BBS_TB_PRE', $config['dzbbs']['db_pre']);
			define('BBS_API_URL', $config['dzbbs']['db_url']);
			define('BBS_ENABLE', $config['dzbbs']['enable']);
			define('DZ_BBS_VER', $config['dzbbs']['dz_ver']);
			$this->BbsType = 'discuz';
		}
				elseif(@is_file(ROOT_PATH . 'setting/phpwind.php') && $this->Config['phpwind_enable'] && $this->Config['pwbbs_enable']){
			include ROOT_PATH . 'setting/phpwind.php';
			$this->BbsDatabase = new MySqlHandler($config['phpwind']['pw_db_host']);
			$this->BbsDatabase->Charset($config['phpwind']['pw_db_charset']);
			$this->BbsDatabase->doConnect($config['phpwind']['pw_db_user'],$config['phpwind']['pw_db_password'],$config['phpwind']['pw_db_name'],$this->Config['db_persist']);
			Obj::register('BbsDatabase',$this->BbsDatabase);
			define('BBS_TB_PRE', $config['phpwind']['pw_db_table_prefix']);
			define('BBS_API_URL', $config['phpwind']['pw_api']);
			define('BBS_ENABLE', $config['phpwind']['enable']);
			$this->BbsType = 'phpwind';
		}
	}

	
	function _get_member_fid()
	{
		if($this->BbsType == 'discuz'){
			$table_m = (DZ_BBS_VER == 'dzx') ? BBSDB::table('common_member') : BBSDB::table('members');
			$table_ff = (DZ_BBS_VER == 'dzx') ? BBSDB::table('forum_forumfield') : BBSDB::table('forumfields');
			$get_member = BBSDB::fetch_first("SELECT username,groupid FROM {$table_m} WHERE uid = " . MEMBER_UCUID);
			$query = BBSDB::query("SELECT fid,formulaperm,viewperm FROM {$table_ff}");
			$ids = array();
			while ($value = BBSDB::fetch($query)) {
				if($value['viewperm']) {
					$viewpermids = explode("\t", $value['viewperm']);
					if(!in_array($get_member['groupid'], $viewpermids)) {
						array_push($ids,$value['fid']);
						continue;
					}
				}
				$formpermstr = unserialize($value['formulaperm']);
				$formperm = $formpermstr['users'];
				if($formperm) {
					$formperm = str_replace(array("\r\n", "\r"), array("\n", "\n"), $formperm);
					$formperm = explode("\n", trim($formperm));
					if(!in_array($get_member['username'], $formperm)) {
						array_push($ids,$value['fid']);
					}
				}
			}
			$fids = implode(",",$ids);
		}
		return $fids;
	}

	
    function get_pw_uicon($pwuid)
    {
		$pwuid = (int) $pwuid;
		$icon = '';
		$geticon = BBSDB::result_first("SELECT icon FROM ".BBSDB::table('members')." WHERE uid = '$pwuid'");
		if($geticon){
			$icons = explode("|",$geticon);
			if ($icons[4]) {
				$icon = '/images/pig.gif';
			}elseif ($icons[1] == 3) {
				$icon = '/attachment/upload/middle/'.$icons[0];
			}elseif ($icons[1] == 2 && strncmp($icons[0],'http',4) == 0) {
				$icon = $icons[0];
			}else{
				if(!$icons[0]){$icons[0] = 'none.gif';}
				$icon = '/images/face/'.$icons[0];
			}
		}
		return $icon;
	}

	
    function get_reply($tid)
    {
		if($this->BbsType == 'discuz'){
			$table = (DZ_BBS_VER == 'dzx') ? BBSDB::table('forum_post') : BBSDB::table('posts');
			$turl = (DZ_BBS_VER == 'dzx') ? '/forum.php?mod=viewthread&tid='.$tid : '/viewthread.php?tid='.$tid;
			$total_record = BBSDB::result_first("SELECT COUNT(*) FROM {$table} WHERE tid = '$tid' AND first = '0'");
		}elseif($this->BbsType == 'phpwind'){
			$table = BBSDB::table('posts');
			$turl = '/read.php?tid='.$tid;
			$total_record = BBSDB::result_first("SELECT COUNT(*) FROM {$table} WHERE tid = '$tid'");
		}
		if ($total_record > 0) {
			if($this->BbsType == 'discuz'){
				$query = BBSDB::query("SELECT pid,authorid,author,message,dateline FROM {$table} WHERE tid = '$tid' AND first = '0' ORDER BY pid DESC LIMIT 10");
			}elseif($this->BbsType == 'phpwind'){
				$query = BBSDB::query("SELECT pid,authorid,author,content AS message,postdate AS dateline FROM {$table} WHERE tid = '$tid' ORDER BY pid DESC LIMIT 10");
			}
			$reply_list = array();
			while ($value = BBSDB::fetch($query)) {
				if($user_lists[$value['authorid']]){
					$m_user = $user_lists[$value['authorid']];
				}else{
					$user = $this->TopicLogic->GetMember($this->_getuid($value['authorid']), "`uid`,`ucuid`,`nickname`,`face`,`level`,`signature`,`validate`,`validate_category`");
					$m_user = empty($user) ? array() : $this->TopicLogic->MakeMember($user);
					$user_lists[$value['authorid']] = $m_user;
				}
				$reply_list[$value['pid']]['tid'] = $value['pid'];
				$reply_list[$value['pid']]['dateline'] = my_date_format2($value['dateline']);
				$reply_list[$value['pid']]['content'] = $this->_format($value['message']);
				$reply_list[$value['pid']]['content_full'] = $this->_format($value['message'],'long');
				$reply_list[$value['pid']]['face'] = $m_user['face'];
				$reply_list[$value['pid']]['nickname'] = empty($m_user['nickname']) ? $value['author'] : $m_user['nickname'];
				$reply_list[$value['pid']]['username'] = $value['author'];
				$reply_list[$value['pid']]['longtext'] = $this->_format($value['message'],'islong');
				$reply_list[$value['pid']]['uid'] = $m_user['uid'];
				$reply_list[$value['pid']]['validate_html'] = $m_user['validate_html'];
			}
			$info = array(
				'list' => $reply_list,
				'count' => $total_record,
				'url' => BBS_API_URL.$turl,
			);
			return $info;
		}
	}

	 
    function get_bbs($param)
    {
		if (BBS_ENABLE){
			if($this->BbsType == 'discuz'){
				$table_p = (DZ_BBS_VER == 'dzx') ? BBSDB::table('forum_post') : BBSDB::table('posts');
				$table_t = (DZ_BBS_VER == 'dzx') ? BBSDB::table('forum_thread') : BBSDB::table('threads');
				$table_f = (DZ_BBS_VER == 'dzx') ? BBSDB::table('home_favorite') : BBSDB::table('favorites');
				$table_ff = (DZ_BBS_VER == 'dzx') ? BBSDB::table('forum_forum') : BBSDB::table('forums');
				$furl = (DZ_BBS_VER == 'dzx') ? '/forum.php?mod=forumdisplay&fid=' : '/forumdisplay.php?fid=';
				$turl = (DZ_BBS_VER == 'dzx') ? '/forum.php?mod=viewthread&tid=' : '/viewthread.php?tid=';
				$lurl = (DZ_BBS_VER == 'dzx') ? '/forum.php?mod=redirect&tid=' : '/redirect.php?tid=';
				$where_sql = " p.invisible >=0 ";
				$order_by = " t.lastpost DESC ";
				if (!empty($param['where'])) {
					
					if($param['where'] == 'favorites') {
						if(DZ_BBS_VER == 'dzx'){
							$where_sql .= " AND p.first = 1 AND p.fid IN (SELECT id FROM {$table_f} WHERE idtype = 'fid' AND uid = " . MEMBER_UCUID . ") ";
						}else{
							$where_sql .= " AND p.first = 1 AND p.fid IN (SELECT fid FROM {$table_f} WHERE tid = '0' AND uid = " . MEMBER_UCUID . ") ";
						}
						$order_by = " p.pid DESC ";
					}elseif($param['where'] == 'favorite') {
						if(DZ_BBS_VER == 'dzx'){
							$where_sql .= " AND p.first = 1 AND p.tid IN (SELECT id FROM {$table_f} WHERE idtype = 'tid' AND uid = " . MEMBER_UCUID . ") ";
						}else{
							$where_sql .= " AND p.first = 1 AND p.tid IN (SELECT tid FROM {$table_f} WHERE fid = '0' AND uid = " . MEMBER_UCUID . ") ";
						}
					}elseif($param['where'] == 'all') {
						$where_sql .= " AND p.first = 1 ";
						if($fids = $this->_get_member_fid()){
							$where_sql .= " AND p.fid NOT IN (" . $fids . ") ";
						}
						$order_by = " p.pid DESC ";
					}elseif($param['where'] == 'thread') {
						$where_sql .= " AND p.first = 1 AND p.authorid = " . MEMBER_UCUID;
					}elseif($param['where'] == 'reply') {
						$where_sql .= " AND p.first = 0 AND p.authorid = " . MEMBER_UCUID;
						$order_by = " p.pid DESC ";
					}
				}
			}elseif($this->BbsType == 'phpwind'){
				$table_ff = BBSDB::table('forums');
				$where_sql = ' p.fid !=0 ';
				$furl = '/thread.php?fid=';
				$turl = '/read.php?tid=';
				$lurl = '/read.php?tid=';
				
				if (!empty($param['where'])) {
					if($param['where'] == 'favorite') {
						$table_p = BBSDB::table('collection');
						$where_sql = " p.type ='postfavor' AND p.uid = " . MEMBER_UCUID;
					}elseif($param['where'] == 'all') {
						$table_p = BBSDB::table('threads');
					}elseif($param['where'] == 'thread') {
						$table_p = BBSDB::table('threads');
						$where_sql .= " AND p.authorid = " . MEMBER_UCUID;
					}elseif($param['where'] == 'reply') {
						$table_p = BBSDB::table('posts');
						$where_sql .= " AND p.authorid = " . MEMBER_UCUID;
					}else{
						return false;
					}
				}
			}
			$total_record = BBSDB::result_first("SELECT COUNT(*) FROM {$table_p} AS p WHERE {$where_sql} LIMIT 1000");
			if ($total_record > 0) {
				if ($param['perpage']) {
					$page_arr = page($total_record, $param['perpage'], $param['page_url'], array('return'=>'array'));
					$limit_sql = $page_arr['limit'];
				} else {
					if ($param['limit']) {
						$limit_sql = ' LIMIT '.$param['limit'];
					}
				}
				if($this->BbsType == 'discuz'){
					$query = BBSDB::query("SELECT p.pid,p.tid,p.fid,p.authorid,p.author,p.subject,p.message,p.dateline,p.first,t.author AS t_username,t.subject AS t_title,t.dateline AS t_dateline,t.authorid AS t_authorid,t.replies,t.lastpost,ff.name FROM {$table_p} p LEFT JOIN {$table_t} t ON p.tid = t.tid LEFT JOIN {$table_ff} ff ON p.fid = ff.fid WHERE {$where_sql} ORDER BY {$order_by} {$limit_sql} ");
				}elseif($this->BbsType == 'phpwind'){
					
					if($param['where'] == 'favorite') {
						$table_fc = BBSDB::table('collection');
						$table_t = BBSDB::table('tmsgs');
						$table_p = BBSDB::table('threads');
						$where_sql = " p.tid IN(SELECT typeid FROM {$table_fc} WHERE type ='postfavor' AND uid = " . MEMBER_UCUID .")";
						$order_by = ' p.lastpost DESC ';
						$query = BBSDB::query("SELECT p.tid,p.tid AS pid,p.lastpost,p.tid AS first,p.fid,p.authorid,p.authorid AS t_authorid,p.author,p.subject,t.content AS message,p.postdate AS dateline,p.replies,ff.name FROM {$table_p} p LEFT JOIN {$table_t} t ON p.tid = t.tid LEFT JOIN {$table_ff} ff ON p.fid = ff.fid WHERE {$where_sql} ORDER BY {$order_by} {$limit_sql} ");
					}elseif($param['where'] == 'all') {
						$table_t = BBSDB::table('tmsgs');
						$table_p = BBSDB::table('threads');
						$order_by = ' p.tid DESC ';
						$query = BBSDB::query("SELECT p.tid,p.tid AS pid,p.lastpost,p.tid AS first,p.fid,p.authorid,p.authorid AS t_authorid,p.author,p.subject,t.content AS message,p.postdate AS dateline,p.replies,ff.name FROM {$table_p} p LEFT JOIN {$table_t} t ON p.tid = t.tid LEFT JOIN {$table_ff} ff ON p.fid = ff.fid WHERE {$where_sql} ORDER BY {$order_by} {$limit_sql} ");
					}elseif($param['where'] == 'thread') {
						$table_t = BBSDB::table('tmsgs');
						$table_p = BBSDB::table('threads');
						$where_sql .= " AND p.authorid = " . MEMBER_UCUID;
						$order_by = ' p.lastpost DESC ';
						$query = BBSDB::query("SELECT p.tid AS pid,p.lastpost,p.tid AS first,p.tid,p.fid,p.authorid,p.authorid AS t_authorid,p.author,p.subject,t.content AS message,p.postdate AS dateline,p.replies,ff.name FROM {$table_p} p LEFT JOIN {$table_t} t ON p.tid = t.tid LEFT JOIN {$table_ff} ff ON p.fid = ff.fid WHERE {$where_sql} ORDER BY {$order_by} {$limit_sql} ");
					}elseif($param['where'] == 'reply') {
						$table_p = BBSDB::table('posts');
						$table_t = BBSDB::table('threads');
						$where_sql .= " AND p.authorid = " . MEMBER_UCUID;
						$order_by = ' p.pid DESC ';
						$query = BBSDB::query("SELECT p.pid,t.lastpost,p.tid,p.fid,p.authorid,p.author,p.subject,p.content AS message,p.postdate AS dateline,t.author AS t_username,t.subject AS t_title,t.postdate AS t_dateline,t.authorid AS t_authorid,t.replies,ff.name FROM {$table_p} p LEFT JOIN {$table_t} t ON p.tid = t.tid LEFT JOIN {$table_ff} ff ON p.fid = ff.fid WHERE {$where_sql} ORDER BY {$order_by} {$limit_sql} ");
					}
				}
				$topic_list = array();
				while ($value = BBSDB::fetch($query)) {
					if($user_lists[$value['authorid']]){
						$p_m_user = $user_lists[$value['authorid']];
					}else{
						$p_user = $this->TopicLogic->GetMember($this->_getuid($value['authorid']), "`uid`,`ucuid`,`nickname`,`face`,`level`,`signature`,`validate`,`validate_category`");
						$p_m_user = empty($p_user) ? array() : $this->TopicLogic->MakeMember($p_user);
						$user_lists[$value['authorid']] = $p_m_user;
					}
					if($user_lists[$value['t_authorid']]){
						$t_m_user = $user_lists[$value['t_authorid']];
					}else{
						$t_user = $this->TopicLogic->GetMember($this->_getuid($value['t_authorid']), "`uid`,`ucuid`,`nickname`,`face`,`level`,`signature`,`validate`,`validate_category`");
						$t_m_user = empty($t_user) ? array() : $this->TopicLogic->MakeMember($t_user);
						$user_lists[$value['t_authorid']] = $t_m_user;
					}
					$topic_list[$value['pid']]['pid'] = $value['pid'];
					$topic_list[$value['pid']]['dateline'] = my_date_format2($value['dateline']);
					$topic_list[$value['pid']]['t_dateline'] = my_date_format2($value['t_dateline']);
					$topic_list[$value['pid']]['lastpost'] = my_date_format2($value['lastpost']);
					$topic_list[$value['pid']]['content'] = $this->_format($value['message']);
					$topic_list[$value['pid']]['content_full'] = $this->_format($value['message'],'long');
					$topic_list[$value['pid']]['t_title'] = $this->_format($value['t_title']);
					$topic_list[$value['pid']]['title'] = $this->_format($value['subject']);
					$topic_list[$value['pid']]['face'] = $p_m_user['face'];
					$topic_list[$value['pid']]['level'] = $p_m_user['level'];
					$topic_list[$value['pid']]['t_nickname'] = empty($t_m_user['nickname']) ? $value['t_username'] : $t_m_user['nickname'];
					$topic_list[$value['pid']]['nickname'] = empty($p_m_user['nickname']) ? $value['author'] : $p_m_user['nickname'];
					$topic_list[$value['pid']]['bbsurl'] = BBS_API_URL.$turl.$value['tid'];
					$topic_list[$value['pid']]['lasturl'] = BBS_API_URL.$lurl.$value['tid'].'&goto=lastpost#lastpost';
					$topic_list[$value['pid']]['replys'] = $value['replies'];
					$topic_list[$value['pid']]['username'] = $value['author'];
					$topic_list[$value['pid']]['first'] = $value['first'];
					$topic_list[$value['pid']]['t_username'] = $value['t_username'];
					$topic_list[$value['pid']]['longtext'] = $this->_format($value['message'],'islong');
					$topic_list[$value['pid']]['forumtitle'] = $value['name'];
					$topic_list[$value['pid']]['forumurl'] = BBS_API_URL.$furl.$value['fid'];
					$topic_list[$value['pid']]['uid'] = $p_m_user['uid'];
					$topic_list[$value['pid']]['tid'] = $value['pid'];
					$topic_list[$value['pid']]['rid'] = $value['tid'];
					$topic_list[$value['pid']]['tuid'] = $t_m_user['uid'];
					$topic_list[$value['pid']]['signature'] = $p_m_user['signature'];
					$topic_list[$value['pid']]['validate_html'] = $p_m_user['validate_html'];
					$topic_list[$value['pid']]['t_validate_html'] = $t_m_user['validate_html'];
				}
				$info = array(
					'list' => $topic_list,
					'count' => $total_record,
					'page' => $page_arr,
				);
				return $info;
			}
		}
		return false;
	}
	
	
	function _convert($msg) {
		$in = ($this->BbsType == 'discuz') ? $config['dzbbs']['charset'] : $config['phpwind']['pw_db_charset'];
		$out = $config['charset'];
		return $this->_convertEncoding($msg, $in, $out);
	}

	
	function _getuid($ucuid) {
		$ucuid = (int) $ucuid;
		return DB::result_first("SELECT `uid` FROM ". TABLE_PREFIX ."members WHERE ucuid = '$ucuid'");
	}

	
	function _convertEncoding($source, $in, $out){
		$in	= strtoupper($in);
		$out = strtoupper($out);
		if ($in == "UTF8"){
			$in = "UTF-8";
		}
		if ($out == "UTF8"){
			$out = "UTF-8";
		}
		if( $in==$out ){
			return $source;
		}
	
		if(function_exists('mb_convert_encoding')) {
			return mb_convert_encoding($source, $out, $in );
		}elseif (function_exists('iconv'))  {
			return iconv($in,$out."/"."/IGNORE", $source);
		}
		return $source;
	}

	
	function _filter($content) {
				$content = preg_replace('!\[(attachimg|attach)\]([^\[]+)\[/(attachimg|attach)\]!', '', $content);
				$content = preg_replace('!\[attachment=(.*?)\]!', '', $content);

        
        $content = preg_replace('#\[size=2\]\[color=gray\](.*?)\[/url\]\[/color\]\[/size\]#', '', $content);

						$content = str_replace('<','&lt;',str_replace('>', '&gt;', $content));
				$content = preg_replace('#\[quote\]([\w\W]*)\[/quote\]#i','',$content);

		
        $content = preg_replace('|\[img(?:=[^\]]*)?\](.*?)\[/img\]|', '\\1 ', $content);
        
				$re ="#\[([a-z]+)(?:=[^\]]*)?\](.*?)\[/\\1\]#sim";
		while(preg_match($re, $content)) {
			$content = preg_replace($re, '\2', $content);
		}

				$smiles = array(':)',':(',':D',':\'(',':@',':o',':P',':$',';P',':L',':Q',':lol',':loveliness:',':funk:',':curse:',':dizzy:',':shutup:',':sleepy:',':hug:',':victory:',':time:',':kiss:',':handshake',':call:','{:2_25:}','{:2_26:}','{:2_27:}','{:2_28:}','{:2_29:}','{:2_30:}','{:2_31:}','{:2_32:}','{:2_33:}','{:2_34:}','{:2_35:}','{:2_36:}','{:2_37:}','{:2_38:}','{:2_39:}','{:2_40:}','{:3_41:}','{:3_42:}','{:3_43:}','{:3_44:}','{:3_45:}','{:3_46:}','{:3_47:}','{:3_48:}','{:3_49:}','{:3_50:}','{:3_51:}','{:3_52:}','{:3_53:}','{:3_54:}','{:3_55:}','{:3_56:}','{:3_57:}','{:3_58:}','{:3_59:}','{:3_60:}','{:3_61:}','{:3_62:}','{:3_63:}','{:3_64:}');
		$content = str_replace($smiles, '', $content);

				$content = trim($content);
		
		return $content;
	}

	function _format2($str) {
				$message = $this->_convert($str);
		
				$message = $this->_filter($message);
		
				$message = preg_replace("|\s*http:/"."/[a-z0-9-\.\?\=&_@/%#]*\$|sim", "", $message);

				$short = trim(preg_replace("#\s+#", ' ', $message));
		if(strlen($short)>140){
			$islong = true;
			$short = cut_str($short, 140);
		}
		$long = nl2br($message);

		return array('short'=>$short,'long'=>$long,'islong'=>$islong);
	}

	function _format($str,$val='short')
	{
		$strs = $this->_format2($str);
		return $strs[$val];
	}
}

class BBSDB
{
	function table($table)
	{
		$table_name = BBS_TB_PRE.$table;
		return $table_name;
	}
	function _execute($cmd , $arg1 = '', $arg2 = '') {
		static $bbsdb;
		if(empty($bbsdb)) $bbsdb = & BBSDB::object();
		if ($cmd == 'GetRow') {
			$res = $arg1->GetRow($arg2);
		} else if ($cmd == 'result') {
			$res = $arg1->result($arg2);
		} else if ($cmd == 'GetNumRows') {
			$res = $arg1->GetNumRows();
		} else if ($cmd == 'FreeResult') {
			$res = $arg1->FreeResult();
		} else {
			$res = $bbsdb->$cmd($arg1, $arg2);
		}
		return $res;
	}
	function fetch($resourceid, $type = 'assoc')
	{
		return BBSDB::_execute('GetRow', $resourceid, $type);
	}
	function &object() {
		static $bbsdb;
		if(empty($bbsdb)) {
			$bbsdb = & Obj::registry('BbsDatabase');
			if (empty($bbsdb)) {
				exit("Database init fail!");
			}
		}
		return $bbsdb;
	}
	function query($sql, $type = '')
	{
		DB::checkquery($sql);
		return BBSDB::_execute('Query', $sql, $type);
	}
	function result_first($sql)
	{
		DB::checkquery($sql);
		$query = BBSDB::query($sql);
		return BBSDB::result($query);
	}
	function result($resourceid, $row = 0)
	{
		return BBSDB::_execute('result', $resourceid, $row);
	}
	function fetch_first($sql)
	{
		DB::checkquery($sql);
		return BBSDB::_execute('fetch_first', $sql);
	}
}
?>