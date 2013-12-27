<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename topic_cms.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-28 13:48:47 1192656547 1126907149 9618 $
 *******************************************************************/

 


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class TopicCmsLogic
{
	
    var $MemberHandler;
	var $user_lists = array();

    
    var $Config;
    
    var $TopicLogic;

	var $CmsDatabase;
    
	
	function TopicCmsLogic()
	{
		$this->MemberHandler = &Obj::registry("MemberHandler");
		$this->Config = &Obj::registry("config");
		
		$this->TopicLogic = Load::logic('topic', 1);

				if(@is_file(ROOT_PATH . 'setting/dedecms.php') && $this->Config['dedecms_enable']){
			include ROOT_PATH . 'setting/dedecms.php';
			$this->CmsDatabase = new MySqlHandler($config['dedecms']['db_host'],$config['dedecms']['db_port']);
			$this->CmsDatabase->Charset($config['dedecms']['charset']);
			$this->CmsDatabase->doConnect($config['dedecms']['db_user'],$config['dedecms']['db_pass'],$config['dedecms']['db_name'],$this->Config['db_persist']);
			Obj::register('CmsDatabase',$this->CmsDatabase);
			define('CMS_TB_PRE', $config['dedecms']['db_pre']);
			define('CMS_API_URL', $config['dedecms']['db_url']);
			define('CMS_ENABLE', $config['dedecms']['enable']);
		}
	}

	
    function get_reply($tid)
    {
		$table = CMSDB::table('feedback');
		$turl = '/plus/view.php?aid='.$tid;
		$total_record = CMSDB::result_first("SELECT COUNT(*) FROM {$table} WHERE aid = '$tid'");
		if ($total_record > 0) {
			$query = CMSDB::query("SELECT id,username,msg,dtime FROM {$table} WHERE aid = '$tid' ORDER BY id DESC LIMIT 10");
			$reply_list = array();
			while ($value = CMSDB::fetch($query)) {
				if($user_lists[$value['username']]){
					$m_user = $user_lists[$value['username']];
				}else{
					$user = $this->TopicLogic->GetMember($this->_getuid($value['username']), "`uid`,`ucuid`,`nickname`,`face`,`level`,`signature`,`validate`,`validate_category`");
					$m_user = empty($user) ? array() : $this->TopicLogic->MakeMember($user);
					$user_lists[$value['username']] = $m_user;
				}
				$reply_list[$value['id']]['tid'] = $value['id'];
				$reply_list[$value['id']]['dateline'] = my_date_format2($value['dtime']);
				$reply_list[$value['id']]['content'] = $this->_format($value['msg']);
				$reply_list[$value['id']]['content_full'] = $this->_format($value['msg'],'long');
				$reply_list[$value['id']]['face'] = $m_user['face'];
				$reply_list[$value['id']]['nickname'] = empty($m_user['nickname']) ? $value['username'] : $m_user['nickname'];
				$reply_list[$value['id']]['username'] = $value['username'];
				$reply_list[$value['id']]['longtext'] = $this->_format($value['msg'],'islong');
				$reply_list[$value['id']]['uid'] = $m_user['uid'];
				$reply_list[$value['id']]['validate_html'] = $m_user['validate_html'];
			}
			$info = array(
				'list' => $reply_list,
				'count' => $total_record,
				'url' => $turl,
			);
			return $info;
		}
	}

	 
    function get_cms($param)
    {
		if (CMS_ENABLE){
			$table_p = CMSDB::table('archives');
			$table_t = CMSDB::table('arctype');
			$table_c = CMSDB::table('addonarticle');
			$table = CMSDB::table('feedback');
			$turl = "/plus/list.php?tid=";
			$furl = "/plus/view.php?aid=";
			$curl = "/plus/feedback.php?aid=";
			$where_sql = " p.arcrank =0 ";
			$order_by = " p.id DESC ";
			$total_record = CMSDB::result_first("SELECT COUNT(*) FROM {$table_p} AS p WHERE {$where_sql} LIMIT 1000");
			if ($total_record > 0) {
				if ($param['perpage']) {
					$page_arr = page($total_record, $param['perpage'], $param['page_url'], array('return'=>'array'));
					$limit_sql = $page_arr['limit'];
				} else {
					if ($param['limit']) {
						$limit_sql = ' LIMIT '.$param['limit'];
					}
				}

				$query = CMSDB::query("SELECT p.id,p.typeid,p.title,p.writer,p.description,p.pubdate,t.typename,c.body FROM {$table_p} p LEFT JOIN {$table_t} t ON p.typeid = t.id  LEFT JOIN {$table_c} c ON p.id = c.aid WHERE {$where_sql} ORDER BY {$order_by} {$limit_sql} ");

				$topic_list = array();
				while ($value = CMSDB::fetch($query)) {
					if($user_lists[$value['writer']]){
						$p_m_user = $user_lists[$value['writer']];
					}else{
						$p_user = $this->TopicLogic->GetMember($this->_getuid($value['writer']), "`uid`,`ucuid`,`nickname`,`face`,`level`,`signature`,`validate`,`validate_category`");
						$p_m_user = empty($p_user) ? array() : $this->TopicLogic->MakeMember($p_user);
						$user_lists[$value['writer']] = $p_m_user;
					}
					$replytime = CMSDB::result_first("SELECT dtime FROM {$table} WHERE aid = '".$value['id']."' order by id DESC LIMIT 1");
					$topic_list[$value['id']]['pid'] = $value['id'];
										$topic_list[$value['id']]['replys'] = CMSDB::result_first("SELECT COUNT(*) FROM {$table} WHERE aid = '".$value['id']."'");					
					$topic_list[$value['id']]['replytime'] = $replytime ? my_date_format2($replytime) : my_date_format2($value['pubdate']);
					$topic_list[$value['id']]['replyurl'] = CMS_API_URL.$curl.$value['id'];
					$topic_list[$value['id']]['dateline'] = my_date_format2($value['pubdate']);
					$topic_list[$value['id']]['content'] = $this->_format($value['description']);
					$topic_list[$value['id']]['content_full'] = $this->_format($value['body'],'long');
					$topic_list[$value['id']]['title'] = $this->_format($value['title']);
					$topic_list[$value['id']]['face'] = $p_m_user['face'];
					$topic_list[$value['id']]['level'] = $p_m_user['level'];
					$topic_list[$value['id']]['nickname'] = empty($p_m_user['nickname']) ? $value['writer'] : $p_m_user['nickname'];
					$topic_list[$value['id']]['cmsurl'] = CMS_API_URL.$furl.$value['id'];
					$topic_list[$value['id']]['username'] = $value['writer'];
					$topic_list[$value['id']]['longtext'] = $this->_format($value['body'],'islong');
					$topic_list[$value['id']]['typetitle'] = $value['typename'];
					$topic_list[$value['id']]['typeurl'] = CMS_API_URL.$turl.$value['typeid'];
					$topic_list[$value['id']]['uid'] = $p_m_user['uid'];
					$topic_list[$value['id']]['tid'] = $value['id'];
					$topic_list[$value['id']]['signature'] = $p_m_user['signature'];
					$topic_list[$value['id']]['validate_html'] = $p_m_user['validate_html'];
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
		$in = $config['dedecms']['charset'];
		$out = $config['charset'];
		return $this->_convertEncoding($msg, $in, $out);
	}

	
	function _getuid($uname) {
		return DB::result_first("SELECT `uid` FROM ". TABLE_PREFIX ."members WHERE nickname ='".$uname."'");
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

	function _format2($str) {
				$message = $this->_convert($str);
		
				$message = preg_replace("|\s*http:/"."/[a-z0-9-\.\?\=&_@/%#]*\$|sim", "", $message);

				$short = preg_replace("#\s+#", ' ', $message);

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

class CMSDB
{
	function table($table)
	{
		$table_name = CMS_TB_PRE.$table;
		return $table_name;
	}
	function _execute($cmd , $arg1 = '', $arg2 = '') {
		static $cmsdb;
		if(empty($cmsdb)) $cmsdb = & CMSDB::object();
		if ($cmd == 'GetRow') {
			$res = $arg1->GetRow($arg2);
		} else if ($cmd == 'result') {
			$res = $arg1->result($arg2);
		} else if ($cmd == 'GetNumRows') {
			$res = $arg1->GetNumRows();
		} else if ($cmd == 'FreeResult') {
			$res = $arg1->FreeResult();
		} else {
			$res = $cmsdb->$cmd($arg1, $arg2);
		}
		return $res;
	}
	function fetch($resourceid, $type = 'assoc')
	{
		return CMSDB::_execute('GetRow', $resourceid, $type);
	}
	function &object() {
		static $cmsdb;
		if(empty($cmsdb)) {
			$cmsdb = & Obj::registry('CmsDatabase');
			if (empty($cmsdb)) {
				exit("Database init fail!");
			}
		}
		return $cmsdb;
	}
	function query($sql, $type = '')
	{
		DB::checkquery($sql);
		return CMSDB::_execute('Query', $sql, $type);
	}
	function result_first($sql)
	{
		DB::checkquery($sql);
		$query = CMSDB::query($sql);
		return CMSDB::result($query);
	}
	function result($resourceid, $row = 0)
	{
		return CMSDB::_execute('result', $resourceid, $row);
	}
	function fetch_first($sql)
	{
		DB::checkquery($sql);
		return CMSDB::_execute('fetch_first', $sql);
	}
}
?>