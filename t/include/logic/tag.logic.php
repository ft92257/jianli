<?php
/*************************************************************************************************
* 文件名：tag.logic.php
* 版本号：
* 创建时间： 2010年6月12日
* 最后修改时间： 2010年6月12日
* 作者：狐狸<foxis@qq.com>
* 功能描述：tag相关操作 包括添加、编辑等操作
**************************************************************************************************/
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class TagLogic
{
	
	var $TagTableName;

	
	var $TagCountField;

	
	var $UserTableName ;

	
	var $UserTablePri = 'uid';


	
	var $MyTagTableName;

	
	var $Item;

	
	var $ItemCountField;

	
	var $ItemTableName;

	
	var $ItemTablePri;

	
	var $ItemTagTableName;


	
	var $MyItemTagTableName;

	
	var $SplitChar = ',';

	
	var $Separate = array(',',);

	
	var $Config;

	
	var $ItemConfig;

	
	var $DatabaseHandler;

	
	var $_error;

	
	function TagLogic($item)
	{
		$this->Config			= ConfigHandler::get('tag');
		if (false == $this->Config) exit("tag的配制文件不存在，请检查SETTING目录下的tag.php文件是否存在。");

		$this->Item				= trim($item);
		$this->ItemConfig 		= $this->Config['item_list'][$this->Item];
		if(false == $this->ItemConfig) exit("{$item}的配制文件不存在，请检查SETTING目录下的tag.php文件。");

		$this->DatabaseHandler	= &Obj::registry("DatabaseHandler");

		$this->_setItem();
	}

	
	function GetOne($tag)
	{
		if('' == trim($tag)) return false;

		if(is_numeric($tag))
		{
			$id = (int) $tag;
			$where = "`id`='{$id}'";
		}
		else
		{
			$tag = (string) addslashes($tag);
			$where = "`name`='{$tag}'";
		}

		$sql = "select * from `{$this->TagTableName}` where {$where}";
		$query = $this->DatabaseHandler->Query($sql);

		return $query->GetRow();
	}

	
	function GetTag($post,$field = '*',$simple_process = false)
	{
		
		if(is_array($post))
		{
			if (preg_match("/^(?:\s*\d+\s*\,\s*)*\s*\d+\s*$/i",implode(',',$post)))
			{
				$form_value['ids'] = $post;
			}
			else
			{
				$form_value = $post;
			}
		}
		elseif (is_numeric($post) or preg_match("/^(?:\s*\d+\s*\,\s*)*\s*\d+\s*$/i",$post))
		{
			$form_value['ids'] = $post;
		}
		elseif (is_string($post))
		{
			$form_value['tags'] = $post;
		}
		else
		{
			return false;
		}

		
		$field_count = 0;
		$field = ('*' != $field and '' != $field) ? $field : ($form_value['fields'] ? $form_value['fields'] : $form_value['field']);
		$field = $this->_filterField($field,'array');
		$field_count = count($field);
		if($field_count > 0)
		{
			$_field = implode('`,`',$field);
			$field = "`{$_field}`";
		}
		else
		{
			$field = '*';
		}

		
		$limit = $form_value['limit'];
		if('' == $limit)
		{
			$start = $form_value['start'] ? (int) $form_value['start'] : ($form_value['offset'] ? (int) $form_value['offset'] : (int) $form_value['begin']);
			$per_page_num = $form_value['per_page_num'] ? (int) $form_value['per_page_num'] : ($form_value['row'] ? (int) $form_value['row'] : ($form_value['end'] ? (int) $form_value['end'] : (int) $form_value['rows']));

			if($start <= 0) $start = 0;
			if($per_page_num <= 0 or $per_page_num > 300) $per_page_num = 300;
			$limit = "limit {$start},{$per_page_num}";
		}

		
		$order = $form_value['order'];
		if('' == $order) $order = "order by `{$this->ItemCountField}` desc";


		
		$where = $form_value['where'];
		if('' == $where)
		{
			
			$ids = $form_value['ids'] ? $form_value['ids'] : ($form_value['tag_ids'] ? $form_value['tag_ids'] : ($form_value['id'] ? $form_value['id'] : $form_value['tag_id']));
			$tags = $form_value['tags'] ? $form_value['tags'] : ($form_value['tag'] ? $form_value['tag'] : ($form_value['tag_name'] ? $form_value['tag_name'] : ($form_value['name'] ? $form_value['name'] : ($form_value['keyword'] ? $form_value['keyword'] : $form_value['keywords']))));

			$uid = $form_value['user_id'] ? $form_value['user_id'] : $form_value['uid'];
			$username = $form_value['username'] ? $form_value['username'] : ($form_value['member_name'] ? $form_value['member_name'] : $form_value['user']);

			$total_count = (int) $form_value['total_count'];
			$total_count_min = (int) $form_value['total_count_min'];
			$total_count_max = (int) $form_value['total_count_max'];

			$user_count = (int) $form_value['user_count'];
			$user_count_min = (int) $form_value['user_count_min'];
			$user_count_max = (int) $form_value['user_count_max'];

			$item_count = isset($form_value['item_count']) ? (int) $form_value['item_count'] : (int) $form_value[$this->ItemCountField];
			$item_count_min = isset($form_value['item_count_min']) ? (int) $form_value['item_count_min'] : (int) $form_value[$this->ItemCountField.'_min'];
			$item_count_max = isset($form_value['item_count_max']) ? (int) $form_value['item_count_max'] : (int) $form_value[$this->ItemCountField.'_max'];

			$dateline = (int) $form_value['dateline'];
			$dateline_min = (int) $form_value['dateline_min'];
			$dateline_max = (int) $form_value['dateline_max'];

			$last_post = (int) $form_value['last_post'];
			$last_post_min = (int) $form_value['last_post_min'];
			$last_post_max = (int) $form_value['last_post_max'];

			

			
			$_ids_count = $_tags_count = $where_list = null;
			if($ids)
			{
				$ids = $this->_filterId($ids);
				$_ids_count = count($ids);
				if($_ids_count > 0) $where_list['ids'] = "`id` in('".implode("','",$ids)."')";
			}
			elseif($tags)
			{
				$tags = $this->Filtertag($tags);
				$_tags_count = count($tags);
				if($_tags_count > 0) $where_list['tags'] = "`name` in('".implode("','",$tags)."')";
			}

			if($uid)
			{
				$uid = $this->_filterId($uid);
				$_uid_count = count($uid);
				if($_uid_count > 0) $where_list['uid'] = "`user_id` in('".implode("','",$uid)."')";
			}

			if($username)
			{
				$username = $this->_filterField($username);
				$_username_count = count($username);
				if($_username_count > 0) $where_list['username'] = "`username` in('".implode("','",$username)."')";
			}

			if($total_count > 0) $where_list['total_count'] = "`total_count`='{$total_count}'";
			if($total_count_max > 0) $where_list['total_count_max'] = "`total_count`<'{$total_count_max}'";
			if($total_count_min > 0) $where_list['total_count_min'] = "`total_count`>'{$total_count_min}'";

			if($user_count > 0) $where_list['user_count'] = "`user_count`='{$user_count}'";
			if($user_count_max > 0) $where_list['user_count_max'] = "`user_count`<'{$user_count_max}'";
			if($user_count_min > 0) $where_list['user_count_min'] = "`user_count`>'{$user_count_min}'";

			if($item_count > 0) $where_list['item_count'] = "`{$this->ItemCountField}`='{$item_count}'";
			if($item_count_max > 0) $where_list['item_count_max'] = "`{$this->ItemCountField}`<'{$item_count_max}'";
			if($item_count_min > 0) $where_list['item_count_min'] = "`{$this->ItemCountField}`>'{$item_count_min}'";

			if($dateline > 0) $where_list['dateline'] = "`dateline`='{$dateline}'";
			if($dateline_max > 0) $where_list['dateline_max'] = "`dateline`<'{$dateline_max}'";
			if($dateline_min > 0) $where_list['dateline_min'] = "`dateline`>'{$dateline_min}'";

			if($last_post > 0) $where_list['last_post'] = "`last_post`='{$last_post}'";
			if($last_post_max > 0) $where_list['last_post_max'] = "`last_post`<'{$last_post_max}'";
			if($last_post_min > 0) $where_list['last_post_min'] = "`last_post`>'{$last_post_min}'";

			$method = ('or' == strtolower(trim($form_value['method']))) ? ' or ' : ' and ';
			if(is_array($where_list)) $where = implode($method,$where_list);
		}

		$where = ('' == $where) ? "`{$this->ItemCountField}`>'0'" : $where." and `{$this->ItemCountField}`>'0'";
		if('' != $where) $where = " where ".preg_replace("/^\s*where\s+/i",'',$where);
		if('' != $limit) $limit = " limit ".preg_replace("/^\s*limit\s+/i",'',$limit);
		if('' != $order) $order = " order by ".preg_replace("/^\s*order\s+by\s+/",'',$order);

		$sql = "select {$field} from `{$this->TagTableName}` {$where} {$order} {$limit}";
		$query = $this->DatabaseHandler->Query($sql,'SKIP_ERROR');
		if(false == $query)
		{
			$errno=$this->DatabaseHandler->GetLastErrorNo();
			if($errno==1146) $this->_createTagTable();
			if($errno==1054) $this->_alterTagTable();

			$query = $this->DatabaseHandler->Query($sql);
		}
		if($query -> GetNumRows() < 1) return false;

		if(false == $simple_process) return $query->GetAll();

		
		if('1' == max($_ids_count,$_tags_count))
		{
			$rs = $query->GetRow();
			return (('1' == $field_count) ? $rs[$_field] : $rs);
		}
		else
		{
			$_tag_list = array();
			while ($rs = $query->GetRow())
			{
				$_tag_list[] = ('1' == $field_count) ? $rs[$_field] : $rs;
			}
			return $_tag_list;
		}
	}

	
	function GetTagByItemId($item_id,$limit=0,$min_count='0',$url_pattern='')
	{
		$where_list = array();
		$item_id = $this->_filterId($item_id);
		if(!empty($item_id)) $where_list['item_id'] = "i_t.`item_id` in ('".implode("','",$item_id)."')";
		if($min_count > 1) $where_list['count'] = "i_t.`count` >= '{$min_count}'";
		if(count($where_list) < 1) return false;

		if('' == $limit) $limit = 50;
		$limit = ' limit '.preg_replace("/^\s*limit\s+/i",'',$limit);

		$sql = "select * from `{$this->ItemTagTableName}` i_t left join `{$this->TagTableName}` t on i_t.tag_id=t.id where ".implode(' and ',$where_list)." order by i_t.`count` desc {$limit}";
		$query = $this->DatabaseHandler->Query($sql,"SKIP_ERROR");
		if(false == $query)
		{
			$errno=$this->DatabaseHandler->GetLastErrorNo();
			if($errno==1146) $this->_createItemTagTable();
			if($errno==1054) $this->_alterItemTagTable();

			$query = $this->DatabaseHandler->Query($sql);
		}
		if($query->GetNumRows() <= 0) return false;
		$result = $query->GetAll();
		if('' == $url_pattern) return $result;

		return $this->BuildTag($result,$url_pattern);
	}

	
	function GetMyTag($user_id,$limit=0,$min_count=1)
	{
		$where_list = array();
		$user_id = $this->_filterId($user_id);
		if(!empty($user_id))
		{
			$where_list['user_id'] = "m_t.`user_id` in ('".implode("','",$user_id)."')";
		}

		if(0 < ($min_count = (int) $min_count))
		{
			$where_list['count'] = "m_t.`{$this->ItemCountField}` >= '{$min_count}'";
		}
		if(count($where_list) < 1) return false;

		if('' == $limit)
		{
			$limit = " limit 50 ";
		}
		else
		{
			$limit = ' limit '.preg_replace("/^\s*limit\s+/i",'',$limit);
		}

				$sql = "select t.id,t.name,m_t.* from `{$this->MyTagTableName}` m_t left join `{$this->TagTableName}` t on m_t.tag_id=t.id where ".implode(' and ',$where_list)." order by m_t.`{$this->ItemCountField}` desc {$limit}";
		$query = $this->DatabaseHandler->Query($sql);
		if($query->GetNumRows() <= 0) return false;
		return $query->GetAll();
	}

	
	function GetItemIdByTag($tag,$limit='',$match_times='80%',$return='array')
	{
		$tag_id_list = (is_numeric($tag)) ? (array) $tag : (array) $this->GetTag($tag,'id',true);
		$tag_id_count = count($tag_id_list);
		if($tag_id_count < 1) return false;

		if($tag_id_count > 1 and false !== strpos($match_times,'%')) $match_times = round($tag_id_count * (int) $match_times / 100);
		if($match_times > 1 and $match_times <= $tag_id_count) $having = "having `match_tag_count`>='{$match_times}'";

		if('' == $limit) $limit = 50;
		$limit = ' limit '.preg_replace("/^\s*limit\s+/i",'',$limit);

		$sql = "
			select
				`item_id`,
				count(`tag_id`) as `match_tag_count`,
				sum(`count`) as `tag_use_count`
			from
				`{$this->ItemTagTableName}`
			where
				`tag_id` in('".implode("','",$tag_id_list)."')
			group by
				`item_id`
			{$having}
			order by
				`match_tag_count` desc,
				`tag_use_count` desc
			{$limit}
			";
		$query = $this->DatabaseHandler->Query($sql);
		if($query -> GetNumRows() <= 0) return false;
		$result = array();
		while ($rs = $query->GetRow())
		{
			$result[$rs['item_id']] = $rs['item_id'];
		}
		if('array' == $return) return $result;

		return @implode($return,$result);
	}

	
	function GetItemByTag($tag,$limit='',$match_times='80%')
	{
		$tag_id_list = (is_numeric($tag)) ? (array) $tag : (array) $this->GetTag($tag,'id',true);
		$tag_id_count = count($tag_id_list);
		if($tag_id_count < 1) return false;

		if($tag_id_count > 1 and false !== strpos($match_times,'%')) $match_times = round($tag_id_count * (int) $match_times / 100);
		if($match_times > 1 and $match_times <= $tag_id_count) $having = "having `match_tag_count`>='{$match_times}'";

		if('' == $limit) $limit = 50;
		$limit = ' limit '.preg_replace("/^\s*limit\s+/i",'',$limit);

		$sql = "
			select
				i.*,
				i_t.`item_id`,
				count(i_t.`tag_id`) as `match_tag_count`,
				sum(i_t.`count`) as `tag_use_count`
			from
				`{$this->ItemTagTableName}` i_t
			left join
				`{$this->ItemTableName}` i
			on
				i_t.`item_id`=i.`{$this->ItemTablePri}`
			where
				i_t.`tag_id` in('".implode("','",$tag_id_list)."')
			group by
				i_t.`item_id`
			{$having}
			order by
				`match_tag_count` desc,
				`tag_use_count` desc,
				i_t.`item_id` desc
			{$limit}
			";

		$query = $this->DatabaseHandler->Query($sql);
		if($query -> GetNumRows() < 1) return false;

		return $query->GetAll();
	}

	
	function GetUserByTag($tag,$limit='',$match_times='80%')
	{
		$tag_id_list = (is_numeric($tag)) ? (array) $tag : (array) $this->GetTag($tag,'id',true);
		$tag_id_count = count($tag_id_list);
		if($tag_id_count < 1) return false;

		if($tag_id_count > 1 and false !== strpos($match_times,'%')) $match_times = round($tag_id_count * (int) $match_times / 100);
		if($match_times > 1 and $match_times <= $tag_id_count) $having = "having `match_tag_count`>='{$match_times}'";

		if('' == $limit) $limit = 50;
		$limit = ' limit '.preg_replace("/^\s*limit\s+/i",'',$limit);

		$sql = "
			select
				u.*,
				m_t.`user_id`,
				count(m_t.`tag_id`) as `match_tag_count`,
				sum(m_t.`total_count`) as `tag_use_count`
			from
				`{$this->MyTagTableName}` m_t
			left join
				`{$this->UserTableName}` u
			on
				m_t.`user_id`=u.`{$this->UserTablePri}`
			where
				m_t.`tag_id` in('".implode("','",$tag_id_list)."')
			group by
				m_t.`user_id`
			{$having}
			order by
				`match_tag_count` desc,
				`tag_use_count` desc
			{$limit}
			";

		$query = $this->DatabaseHandler->Query($sql);
		if($query -> GetNumRows() < 1) return false;

		return $query->GetAll();
	}

	function GetItemByMyTag($user_id,$tag,$limit,$match_times='80%')
	{
		$user_id = (int) $user_id;
		if($user_id < 1) return false;

		$tag_id_list = (is_numeric($tag)) ? (array) $tag : (array) $this->GetTag($tag,'id',true);
		$tag_id_count = count($tag_id_list);
		if($tag_id_count < 1) return false;

		if($tag_id_count > 1 and false !== strpos($match_times,'%')) $match_times = round($tag_id_count * (int) $match_times / 100);
		if($match_times > 1 and $match_times <= $tag_id_count) $having = "having `match_tag_count`>='{$match_times}'";

		if('' == $limit) $limit = 50;
		$limit = ' limit '.preg_replace("/^\s*limit\s+/i",'',$limit);

		$sql = "
			select
				i.*,
				m_i_t.`item_id`,
				count(m_i_t.`tag_id`) as `match_tag_count`,
				sum(m_i_t.`count`) as `tag_use_count`
			from
				`{$this->MyItemTagTableName}` m_i_t
			left join
				`{$this->ItemTableName}` i
			on
				m_i_t.`item_id`=i.`{$this->ItemTablePri}`
			where
				m_i_t.`user_id` in('{$user_id}')
			and
				m_i_t.`tag_id` in('".implode("','",$tag_id_list)."')
			group by
				m_i_t.`item_id`
			{$having}
			order by
				`match_tag_count` desc,
				`tag_use_count` desc
			{$limit}
			";

		$query = $this->DatabaseHandler->Query($sql);
		if($query -> GetNumRows() < 1) return false;

		return $query->GetAll();
	}

	
	function Modify($data,$old_tag,$update_to_item=false)
	{
		$item_id = (isset($data[$this->ItemTablePri])) ? (int) $data[$this->ItemTablePri] : (int) $data['item_id'];
		if($item_id <= 0)
		{
			$this->_setError('tag编辑失败：item_id不能小于零');
			return false;
		}
		$tag = (isset($data['tag'])) ? $data['tag'] : $data['tags'];

		$user_id = (isset($data['user_id'])) ? (int) $data['user_id'] : (int) $data['uid'];

		$_tag_list = $this->FilterTag($tag);
		$_old_tag_list = $this->FilterTag($old_tag);
		if($_tag_list == $_old_tag_list) return $tag;


		

		$new_tag_list = array_diff($_tag_list,$_old_tag_list);
		if(count($new_tag_list) > 0)
		{
			$new_data = $data;
			$new_data['tag'] = $new_tag_list;
			$this->Add($new_data,false);
		}

		$need_delete_tag_list = array_diff($_old_tag_list,$_tag_list);
		if(count($need_delete_tag_list) > 0)
		{
			$need_delete_data = $data;
			$need_delete_data['tag'] = $need_delete_tag_list;
			$this->Del($need_delete_data);
		}

		$tag_string = implode($this->SplitChar,$_tag_list);

		
		if($update_to_item) $this->_updateItemTable($item_id,null,$tag_string);

		return $tag_string;
	}

	function Delete($data)
	{
		return $this->Del($data);
	}
	function Del($data)
	{
		$item_id = (isset($data[$this->ItemTablePri])) ? (int) $data[$this->ItemTablePri] : (int) $data['item_id'];
		if($item_id <= 0)
		{
			$this->_setError('tag删除失败：item_id不能小于零');
			return false;
		}
		$tag = (isset($data['tag'])) ? $data['tag'] : $data['tags'];

		$user_id = (isset($data['user_id'])) ? (int) $data['user_id'] : (int) $data['uid'];

		$_tag_list = $this->FilterTag($tag);
		if(count($_tag_list) < 1)
		{
			$this->_setError('tag删除失败：tag不能为空');
			return false;
		}

		$sql = "select * from `{$this->TagTableName}` where `name` in('".implode("','",$_tag_list)."')";
		$query = $this->DatabaseHandler->Query($sql);
		$tag_id_list = array();
		while ($rs = $query->GetRow())
		{
			$tag_id_list[$rs['id']] = $rs['id'];
		}

		if($user_id > 0)
		{
			$this->_delMyItemTag($user_id,$item_id,$tag_id_list);

			$_del_use_tag_count = $this->_delMyTag($user_id,$tag_id_list);
			if($_del_use_tag_count > 0) $this->_updateUserTable($user_id, - $_del_use_tag_count);
		}

		$_del_item_tag_count = $this->_delItemtag($item_id,$tag_id_list);
		if($_del_item_tag_count > 0) $this->_updateItemTable($item_id, - $_del_item_tag_count);
	}

	
	function Add($data,$insert_into_item=false)
	{
		
		$item_id = (isset($data[$this->ItemTablePri])) ? (int) $data[$this->ItemTablePri] : (int) $data['item_id'];
		if($item_id <= 0)
		{
			$this->_setError('tag添加失败：item_id不能小于零');
			return false;
		}
		$tag = (isset($data['tag'])) ? $data['tag'] : $data['tags'];

		$user_id = MEMBER_ID;
		$username = MEMBER_NAME;
		$time = time();

		$_tag_list = $this->FilterTag($tag);
		if(count($_tag_list) < 1)
		{
			$this->_setError('tag添加失败：tag不能为空');
			return false;
		}

		
		$sql = "select `id`,`name`,`user_id`,`username`,`dateline`,`last_post`,`total_count`,`user_count`,`{$this->ItemCountField}` from `{$this->TagTableName}` where `name` in ('".implode("','",$_tag_list)."') group by `name`";
		$query = $this->DatabaseHandler->Query($sql,"SKIP_ERROR");
		if(false == $query)
		{
			$errno=$this->DatabaseHandler->GetLastErrorNo();
			if($errno==1146) $this->_createTagTable();
			if($errno==1054) $this->_alterTagTable();

			$query = $this->DatabaseHandler->Query($sql);
		}
		$tag_id_list = $tag_list = $_exist_tag_list = array();
		while($rs = $query->GetRow())
		{
			$tag_id_list[$rs['id']] = $rs['id'];
			$tag_list[$rs['id']] = $_exist_tag_list[$rs['id']] = $this->_strtolower($rs['name']);
		}

		
		$_tmp_new_tag_list = array_diff($_tag_list,$_exist_tag_list);

		if(is_array($_tmp_new_tag_list) and count($_tmp_new_tag_list))
		{
			$time = time();
			$this->DatabaseHandler->SetTable($this->TagTableName);
			$new_tag_count = 0;
			foreach ($_tmp_new_tag_list as $_tag)
			{
				$_form_value = array(
					'name' => $_tag,
					'user_id' => $user_id,
					'username' => $username,
				);
				$_form_value['dateline'] = $_form_value['last_post'] = $time;
				$_tag_id = $this->DatabaseHandler->Insert($_form_value);
				if($_tag_id <= 0) continue;

				$tag_id_list[$_tag_id] = $_tag_id;
				$tag_list[$_tag_id] = $_tag;
				$new_tag_count++;
			}
		}
		$tag_string = implode($this->SplitChar,$tag_list);

				if($user_id > 0)
		{
			$this->_addMyItemTag($user_id,$item_id,$tag_id_list);

			$_new_user_tag_count = $this->_addMyTag($user_id,$tag_id_list);
			if($_new_user_tag_count > 0) $this->_updateUserTable($user_id,$_new_user_tag_count,$new_tag_count);
		}

		
		$_new_item_tag_count = $this->_addItemTag($item_id,$tag_id_list);
		if($insert_into_item && $_new_item_tag_count > 0) {
			$this->_updateItemTable($item_id,$_new_item_tag_count,$insert_into_item ? $tag_string : null);
		}

		return $tag_string;
	}

	function _addItemTag($item_id,$tag_id_list)
	{
		$time = time();

		$sql = "select `item_id`,`tag_id`,`count` from `{$this->ItemTagTableName}` where `item_id`='{$item_id}' and `tag_id` in ('".implode("','",$tag_id_list)."')";
		$query = $this->DatabaseHandler->Query($sql,"SKIP_ERROR");
		if(false == $query)
		{
			$errno=$this->DatabaseHandler->GetLastErrorNo();
			if($errno==1146) $this->_createItemTagTable();
			if($errno==1054) $this->_alterItemTagTable();

			$query = $this->DatabaseHandler->Query($sql);
		}
		$_exist_tag_list = array();
		$_need_update_list = array();
		while($rs = $query->GetRow())
		{
			$_exist_tag_list[$rs['tag_id']] = $rs['tag_id'];

			$_need_update_list[$rs['tag_id']] = "(`item_id`='{$item_id}' and `tag_id`='{$rs['tag_id']}')";
		}

		$need_update_tag_list = array();
		$_insert_value_list = array();
		foreach ($tag_id_list as $id)
		{
			if(false == isset($_exist_tag_list[$id]))
			{
				$_insert_value_list[$id] = "('{$item_id}','{$id}','".time()."','1')";

				$need_update_tag_list[$id] = $id;
			}
		}

		if(count($_need_update_list) > 0)
		{
			$sql = "update `{$this->ItemTagTableName}` set `dateline`='".time()."', `count`=`count`+1 where ".implode(' or ',$_need_update_list);
			$this->DatabaseHandler->Query($sql);
		}

		$new_tag_count = count($_insert_value_list);
		if($new_tag_count > 0)
		{
			$sql = "replace into `{$this->ItemTagTableName}` (`item_id`,`tag_id`,`dateline`,`count`) values ".implode(' , ',$_insert_value_list);
			$this->DatabaseHandler->Query($sql);
		}

		$this->_updateTagTableItemCount($tag_id_list);

		return $new_tag_count;
	}

	function _updateTagTableItemCount($tag_id_list)
	{
		$this->DatabaseHandler->Query("delete from `{$this->ItemTagTableName}` where tag_id in('".implode("','",$tag_id_list)."') and `count`<1");

		$sql = "select tag_id,count(item_id) as item_count from `{$this->ItemTagTableName}` where tag_id in('".implode("','",$tag_id_list)."') group by tag_id";

		$list = array();
		foreach ($tag_id_list as $tag_id)
		{
			$list[$tag_id] = 0;
		}

		$query = $this->DatabaseHandler->Query($sql);
		while ($rs = $query->GetRow())
		{
			$list[$rs['tag_id']] = $rs['item_count'];
		}

		$time = time();
		foreach ($list as $tag_id=>$item_count)
		{
			$this->DatabaseHandler->Query("update `{$this->TagTableName}` set `{$this->ItemCountField}`='{$item_count}' , `last_post`='{$time}' where `id`='{$tag_id}'");
		}

		$this->_updateTagTableTotalCount($tag_id_list);
	}
	function _updateTagTableTotalCount($tag_id_list)
	{
		$set_value = "`total_count`=`user_count`+";
		foreach ($this->Config['item_list'] as $item=>$item_config)
		{
			$set_value .= "`".$this->GetItemCountField($item)."`+";
		}
		$set_value = rtrim($set_value,'+');

		$sql = "update `{$this->TagTableName}` set {$set_value} where `id` in('".implode("','",$tag_id_list)."')";

		$this->DatabaseHandler->Query($sql);
	}

	function _delItemTag($item_id,$tag_id_list)
	{
		$sql = "select `item_id`,`tag_id`,`count` from `{$this->ItemTagTableName}` where `item_id`='{$item_id}' and `tag_id` in ('".implode("','",$tag_id_list)."')";
		$query = $this->DatabaseHandler->Query($sql);
		$_need_update_list = array();
		$_need_del_list = array();
		$need_update_tag_list = array();
		while ($rs = $query->GetRow())
		{
			if($rs['count'] > 1)
			{
				$_need_update_list[$rs['tag_id']] = "(`item_id`='{$item_id}' and `tag_id`='{$rs['tag_id']}')";
			}
			else
			{
				$_need_del_list[$rs['tag_id']] = "(`item_id`='{$item_id}' and `tag_id`='{$rs['tag_id']}')";
				$need_update_tag_list[$rs['tag_id']] = $rs['tag_id'];
			}
		}

		if(count($_need_update_list) > 0)
		{
			
			$sql = "update `{$this->ItemTagTableName}` set `count`=if(`count`>1,`count`-1,0) where ".implode(' or ',$_need_update_list);
			$this->DatabaseHandler->Query($sql);
		}

		$del_tag_count = count($_need_del_list);
		if($del_tag_count > 0)
		{
			$sql = "delete from `{$this->ItemTagTableName}` where ".implode(' or ',$_need_del_list);
			$this->DatabaseHandler->Query($sql);
		}

		$this->_updateTagTableItemCount($tag_id_list);

		return $del_tag_count;
	}

	function _addMyTag($user_id,$tag_id_list)
	{
		$time = time();

		$sql = "select `user_id`,`tag_id`,`total_count`,`{$this->ItemCountField}` from `{$this->MyTagTableName}` where `user_id`='{$user_id}' and `tag_id` in ('".implode("','",$tag_id_list)."')";
		$query = $this->DatabaseHandler->Query($sql,"SKIP_ERROR");
		if(false == $query)
		{
			$errno=$this->DatabaseHandler->GetLastErrorNo();
			if($errno==1146) $this->_createMyTagTable();
			if($errno==1054) $this->_alterMyTagTable();

			$query = $this->DatabaseHandler->Query($sql);
		}
		$_exist_tag_list = array();
		$_need_update_list = array();
		while ($rs = $query->GetRow())
		{
			$_exist_tag_list[$rs['tag_id']] = $rs['tag_id'];
			$_need_update_list[$rs['tag_id']] = "(`user_id`='{$user_id}' and `tag_id`='{$rs['tag_id']}')";
		}

		$need_update_tag_list = array();
		$_insert_value_list = array();
		foreach ($tag_id_list as $id)
		{
			if(false == isset($_exist_tag_list[$id]))
			{
				$_insert_value_list[$id] = "('{$user_id}','{$id}','1','1')";

				$need_update_tag_list[$id] = $id;
			}
		}

		if(count($_need_update_list) > 0)
		{
			$sql = "update `{$this->MyTagTableName}` set `total_count`=`total_count`+1 , `{$this->ItemCountField}`=`{$this->ItemCountField}`+1 where ".implode(' or ',$_need_update_list);
			$this->DatabaseHandler->Query($sql);
		}

		$new_tag_count = count($_insert_value_list);
		if($new_tag_count > 0)
		{
			$sql = "insert into `{$this->MyTagTableName}` (`user_id`,`tag_id`,`total_count`,`{$this->ItemCountField}`) values ".implode(' , ',$_insert_value_list);
			$this->DatabaseHandler->Query($sql);
		}

		$this->_updateTagTableUserCount($tag_id_list);

		return $new_tag_count;
	}

	function _updateTagTableUserCount($tag_id_list)
	{
		$sql = "select tag_id,count(user_id) as item_count from `{$this->MyTagTableName}` where tag_id in('".implode("','",$tag_id_list)."') group by tag_id";

		$list = array();
		foreach ($tag_id_list as $tag_id)
		{
			$list[$tag_id] = 0;
		}

		$query = $this->DatabaseHandler->Query($sql);
		while ($rs = $query->GetRow())
		{
			$list[$rs['tag_id']] = $rs['item_count'];
		}

		$time = time();
		foreach ($list as $tag_id=>$item_count)
		{
			$this->DatabaseHandler->Query("update `{$this->TagTableName}` set `user_count`='{$item_count}' , `last_post`='{$time}' where `id`='{$tag_id}'");
		}

		$this->_updateTagTableTotalCount($tag_id_list);
	}

	function _delMyTag($user_id,$tag_id_list)
	{
		$sql = "select `user_id`,`tag_id`,`total_count`,`{$this->ItemCountField}` from `{$this->MyTagTableName}` where `user_id`='{$user_id}' and `tag_id` in ('".implode("','",$tag_id_list)."')";
		$query = $this->DatabaseHandler->Query($sql,"SKIP_ERROR");
		IF(!$query)return false;
		$_need_update_list = array();
		$_need_del_list = array();
		$need_update_tag_list = array();
		while ($rs = $query->GetRow())
		{
			if($rs[$this->ItemCountField] > 1)
			{
				$_need_update_list[$rs['tag_id']] = "(`user_id`='{$user_id}' and `tag_id`='{$rs['tag_id']}')";
			}

			if($rs['total_count'] <= 1)
			{
				$_need_del_list[$rs['tag_id']] = "(`user_id`='{$user_id}' and `tag_id`='{$rs['tag_id']}')";
				$need_update_tag_list[$rs['tag_id']] = $rs['tag_id'];
			}
		}

		if(count($_need_update_list) > 0)
		{
			
			$sql = "update `{$this->MyTagTableName}` set `total_count`=if(`total_count`>1,`total_count`-1,0) , `{$this->ItemCountField}`=`{$this->ItemCountField}`-1 where ".implode(' or ',$_need_update_list);
			$this->DatabaseHandler->Query($sql);
		}

		$del_tag_count = count($_need_del_list);
		if($del_tag_count > 0)
		{
			$sql = "delete from `{$this->MyTagTableName}` where ".implode(' or ',$_need_del_list);
			$this->DatabaseHandler->Query($sql);
		}

		$this->_updateTagTableUserCount($tag_id_list);

		return $del_tag_count;
	}

	
	function _addMyItemTag($user_id,$item_id,$tag_id_list)
	{
		$sql = "select `user_id`,`item_id`,`tag_id`,`count` from `{$this->MyItemTagTableName}` where `user_id`='{$user_id}' and `item_id`='{$item_id}' and `tag_id` in('".implode("','",$tag_id_list)."')";
		$query = $this->DatabaseHandler->Query($sql,"SKIP_ERROR");
		if(false == $query)
		{
			$errno=$this->DatabaseHandler->GetLastErrorNo();
			if($errno==1146) $this->_createMyItemTagTable();
			if($errno==1054) $this->_alterMyItemTagTable();

			$query = $this->DatabaseHandler->Query($sql);
		}
		$_exist_tag_list = array();
		$_need_update_list = array();
		while ($rs = $query->GetRow()) 		{
			$_exist_tag_list[$rs['tag_id']] = $rs['tag_id'];
			$_need_update_list[$rs['tag_id']] = "(`user_id`='{$user_id}' and `item_id`='{$item_id}' and `tag_id`='{$rs['tag_id']}')";
		}

		$_insert_value_list = array();
		foreach ($tag_id_list as $id)
		{
			if(false == isset($_exist_tag_list[$id])) 			{
				$_insert_value_list[$id] = "('{$user_id}','{$item_id}','{$id}','1')";
			}
		}

				if(count($_need_update_list) > 0)
		{
			$sql = "update `{$this->MyItemTagTableName}` set `count`=`count`+1 where ".implode(' or ',$_need_update_list);
			$this->DatabaseHandler->Query($sql);
		}

				if(count($_insert_value_list) > 0)
		{
			$sql = "replace into `{$this->MyItemTagTableName}` (`user_id`,`item_id`,`tag_id`,`count`) values ".implode(' , ',$_insert_value_list);
			$this->DatabaseHandler->Query($sql);
		}
	}
	function _delMyItemTag($user_id,$item_id,$tag_id_list)
	{
		$sql = "select `user_id`,`item_id`,`tag_id`,`count` from `{$this->MyItemTagTableName}` where `user_id`='{$user_id}' and `item_id`='{$item_id}' and `tag_id` in('".implode("','",$tag_id_list)."')";
		$query = $this->DatabaseHandler->Query($sql,"SKIP_ERROR");
		if(!$query)return false;
		$_need_update_list = array();
		$_need_del_list = array();
		while ($rs = $query->GetRow())
		{
			if($rs['count'] > 1)
			{
				$_need_update_list[$rs['tag_id']] = "(`user_id`='{$user_id}' and `item_id`='{$item_id}' and `tag_id`='{$rs['tag_id']}')";
			}
			else
			{
				$_need_del_list[$rs['tag_id']] = "(`user_id`='{$user_id}' and `item_id`='{$item_id}' and `tag_id`='{$rs['tag_id']}')";
			}
		}

		if(count($_need_update_list) > 0)
		{
			
			$sql = "update `{$this->MyItemTagTableName}` set `count`=if(`count`>1,`count`-1,0) where ".implode(' or ',$_need_update_list);
			$this->DatabaseHandler->Query($sql);
		}

		if(count($_need_del_list) > 0)
		{
			$sql = "delete from `{$this->MyItemTagTableName}` where ".implode(' or ',$_need_del_list);
			$this->DatabaseHandler->Query($sql);
		}
	}

	function _updateUserTable($id,$use_tag_count=0,$create_tag_count=0)
	{
		if(0 != $use_tag_count) $set_list['use_tag_count'] = "`use_tag_count`=`use_tag_count`+{$use_tag_count}";
		if(0 != $create_tag_count) $set_list['create_tag_count'] = "`create_tag_count`=`create_tag_count`+{$create_tag_count}";
		if(count($set_list) < 1) return false;

		$sql = "update `{$this->UserTableName}` set ".implode(' , ',$set_list)." where `{$this->UserTablePri}` in('".implode("','", (array) $id)."')";
		$query = $this->DatabaseHandler->Query($sql,'SKIP_ERROR');
		if(false == $query)
		{
			$errno=$this->DatabaseHandler->GetLastErrorNo();
			if($errno==1054) $this->_alterUserTable();

			$query = $this->DatabaseHandler->Query($sql);
		}

		return $query;
	}

	function _updateItemTable($id,$tag_count=0,$tag=null)
	{
		if(0 != $tag_count) $set_list['tag_count'] = "`tag_count`=`tag_count`+{$tag_count}";
		if(isset($tag)) $set_list['tag'] = "`tag`='{$tag}'";
		if(count($set_list) < 1) return false;

		$sql = "update `{$this->ItemTableName}` set ".implode(' , ',$set_list)." where `{$this->ItemTablePri}` in('".implode("','", (array) $id)."')";

		$query = $this->DatabaseHandler->Query($sql,'SKIP_ERROR');
		if(false == $query)
		{
			$errno=$this->DatabaseHandler->GetLastErrorNo();
			if($errno==1054) $this->_alterItemTable();

			$query = $this->DatabaseHandler->Query($sql);
		}

		return $query;
	}

	
	function BuildTag($tag,$url_pattern="index.php?mod=tag&code=list_item&item=item&tag=%s",$extra='',$limit='',$show_count=true)
	{
		global $rewriteHandler;
		$tag = (array) $tag;
		$_tag = array();
		foreach ($tag as $s)
		{
			$filter_tag = false;
		 	if(is_array($s))
		 	{
			 	$_id = isset($s['id']) ? (int) $s['id'] : (int) $s['tag_id'];
			 	$_name = trim($s['name']);
			 	if('' != $_name)
			 	{
			 		$_tag[$_name] = $s;
			 	}
			 	else
			 	{
			 		$filter_tag = true;
			 	}
		 	}
		 	else
		 	{
		 		$filter_tag = true;
		 	}

		 	if(true == $filter_tag)
		 	{
		 		foreach ($this->FilterTag($s,'array',$limit) as $_s)
		 		{
		 			$_tag[$_s]['name'] = $_s;
		 		}
		 	}
		 }
		 if(count($_tag) < 1) return false;

		 $is_href = (false !== strpos($url_pattern,'%s')) ? true : false;
		 if($is_href && $rewriteHandler)$url_pattern=$rewriteHandler->formatURL($url_pattern);

		 $limit = (int) $limit;
		 $result_list = array();
		 foreach ($_tag as $_k => $_s)
		 {
			$name = $_s['name'];
			if('' == $name) continue;

			if($is_href)
			{
			 	$href = @sprintf($url_pattern,urlencode($_k));
			 	if ($show_count)
			 	{
			 		$count = (isset($_s['count'])) ? (int) $_s['count'] : (int) $_s[$this->ItemCountField];
			 		$count = $count > 0 ? "({$count})" : null;
			 	}

			 	$result_list[] = "<a title='{$name}' href='{$href}' {$extra}>{$name}</a>{$count}";
			}
			else
			{
				$result_list[$name] = "{$name}";
			}
			if($limit > 0 and ++$ii >= $limit) break;
		 }
		 if('array' == strtolower($url_pattern)) return $result_list;

		 return implode(($is_href) ? ' &nbsp;' : $url_pattern,$result_list);
	}

	
	function FilterTag($tag,$return='array',$limit=null)
	{
		if(!isset($limit) && ($limit = (int) ConfigHandler::get('tag_num',$this->Item)) < 1)
		{
			$this->_setError("未设置{$this->Item}的tag_num值");
			return array();
		}

		$min_word_len = 2;
		$max_word_len = 50;

		$tag = (array) $tag;
		$_word = array();
		foreach ($tag as $word)
		{
			$word_len = strlen((string) $word);
			if($word_len < $min_word_len) continue;

			for($i=0;$i<$word_len;$i++)
			{
				if(ord($word{$i}) <= 128)
				{
					$_word[] = $word{$i};
				}
				else
				{
					$_word[] = $word{$i}.$word{++$i};
				}
			}
			$_word[] = ',';
		}

		$result = array();
		if(count($_word) > 1)
		{
			$w = null;
			foreach ($_word as $_w)
			{
				if(true === $this->_isSeparate($_w))
				{
					$w_len = strlen($w);
					if($w_len < $min_word_len or $w_len > $max_word_len)
					{
						$w = null;
						continue;
					}

										$w = addslashes($this->_strtolower(trim($w)));
					if($w) $result[$w] = $w;
					if($limit>0 and ++$k >= $limit) break;

					$w = null;
				}
				else
				{
					$w = $w.$_w;
				}
			}
		}

		if('array' == $return) return $result;

		return implode($return,$result);
	}
	function _isSeparate($chr)
	{
		for ($i = 0 ; $i<count($this->Separate) ; $i++)
		{
			if($this->Separate[$i] == $chr) return true;
		}
		return false;
	}

	
	function _setItem()
	{
				$this->ItemTableName = $this->ItemConfig['table_name'] ? $this->ItemConfig['table_name'] : exit("请配置 <b>{$this->Item}</b> 的table_name值");
		$this->ItemTablePri = $this->ItemConfig['table_pri'] ? $this->ItemConfig['table_pri'] : $this->_getTablePri($this->ItemTableName);

				$this->TagTableName = $this->Config['table_name'] ? $this->Config['table_name'] : TABLE_PREFIX.'tag';

				$this->UserTableName = $this->Config['user_table_name'] ? $this->Config['user_table_name'] : TABLE_PREFIX.'members';
		$this->UserTablePri = $this->Config['user_table_pri'] ? $this->Config['user_table_pri'] : $this->_getTablePri($this->UserTableName);

				$this->ItemCountField = $this->GetItemCountField();

				$this->MyTagTableName = $this->Config['my_tag_table_name'] ? $this->Config['my_tag_table_name'] : TABLE_PREFIX.'my_tag';

				$this->ItemTagTableName = $this->ItemConfig['item_tag_table_name'] ? $this->ItemConfig['item_tag_table_name'] : TABLE_PREFIX.$this->Item.'_tag';

				$this->MyItemTagTableName = $this->ItemConfig['my_item_tag_table_name'] ? $this->ItemConfig['my_item_tag_table_name'] : TABLE_PREFIX.'my_'.$this->Item.'_tag';
	}

	
	function GetItemCountField($item='')
	{
		$item = '' == $item ? $this->Item : $item;

		return $this->Config['item_list'][$item]['count_field'] ? $this->Config['item_list'][$item]['count_field'] : $item.'_count';
	}

	function _setTagFieldList()
	{
				return array
		(
			'id' => "`id` mediumint(8) unsigned NOT NULL auto_increment , PRIMARY KEY (`id`)",
			'name' => "`name` char(15) NOT NULL default '' , KEY `name` (`name`)",
			'user_id' => "`user_id` mediumint(8) unsigned NOT NULL default '0' , KEY `user_id` (`user_id`)",
			'username' => "`username` char(15) NOT NULL default ''",
			'dateline' => "`dateline` int(10) unsigned NOT NULL default '0'",
			'last_post' => "`last_post` int(10) unsigned NOT NULL default '0'",
			'total_count' => "`total_count` int(10) unsigned NOT NULL default '0'",
			'user_count' => "`user_count` mediumint(8) unsigned NOT NULL default '0'",
			$this->ItemCountField => "`{$this->ItemCountField}` mediumint(8) unsigned NOT NULL default '0'",
		);
	}
	
	function _createTagTable()
	{
		return $this->_createTable($this->TagTableName,$this->_setTagFieldList());
	}
	
	function _alterTagTable()
	{
		return $this->_alterTable($this->TagTableName,$this->_setTagFieldList());
	}

	function _setItemTagFieldList()
	{
		return array
		(
			'item_id' => "`item_id` mediumint(8) unsigned NOT NULL default '0'",
			'tag_id' => "`tag_id` mediumint(8) unsigned NOT NULL , PRIMARY KEY (`item_id`,`tag_id`) , KEY (`tag_id`)",
			'dateline' => "`dateline` int(10) unsigned NOT NULL",
			'count' => "`count` mediumint(6) NOT NULL default '0'",
		);
	}
	function _createItemTagTable()
	{
		return $this->_createTable($this->ItemTagTableName,$this->_setItemTagFieldList());
	}
	function _alterItemTagTable()
	{
		return $this->_alterTable($this->ItemTagTableName,$this->_setItemTagFieldList());
	}

	function _setMyTagFieldList()
	{
		return array
		(
			'user_id' => "`user_id` mediumint(8) unsigned NOT NULL default '0'",
			'tag_id' => "`tag_id` mediumint(8) unsigned NOT NULL default '0' , PRIMARY KEY (`user_id`,`tag_id`)",
			'total_count' => "`total_count` mediumint(8) unsigned NOT NULL default '0'",
			$this->ItemCountField => "`{$this->ItemCountField}` smallint(6) unsigned NOT NULL default '0'",
		);
	}
	function _createMyTagTable()
	{
		return $this->_createTable($this->MyTagTableName,$this->_setMyTagFieldList());
	}
	function _alterMyTagTable()
	{
		return $this->_alterTable($this->MyTagTableName,$this->_setMyTagFieldList());
	}

	function _setMyItemTagFieldList()
	{
		return array
		(
			'user_id' => "`user_id` mediumint(8) unsigned NOT NULL default '0'",
			'item_id' => "`item_id` mediumint(8) unsigned NOT NULL default '0'",
			'tag_id' => "`tag_id` mediumint(8) unsigned NOT NULL default '0', PRIMARY KEY (`user_id`,`item_id`,`tag_id`)",
			'count' => "`count` smallint(4) unsigned NOT NULL default '1'",
		);
	}
	function _createMyItemTagTable()
	{
		return $this->_createTable($this->MyItemTagTableName,$this->_setMyItemTagFieldList());
	}
	function _alterMyItemTagTable()
	{
		return $this->_alterTable($this->MyItemTagTableName,$this->_setMyItemTagFieldList());
	}

	function _setItemFieldList()
	{
		return array
		(
			'tag' => "`tag` char(255) NOT NULL default ''",
			'tag_count' => "`tag_count` smallint(4) unsigned NOT NULL default '0'",
		);
	}
	function _alterItemTable()
	{
		return $this->_alterTable($this->ItemTableName,$this->_setItemFieldList());
	}

	function _setUserFieldList()
	{
		return array
		(
			'use_tag_count' => "`use_tag_count` mediumint(8) unsigned NOT NULL default '0'",
			'create_tag_count' => "`create_tag_count` smallint(4) unsigned NOT NULL default '0'"
		);
	}
	function _alterUserTable()
	{
		return $this->_alterTable($this->UserTableName,$this->_setUserFieldList());
	}

	
	function _createTable($table_name,$field_list)
	{
		$sql="CREATE TABLE `{$table_name}` (".implode(',',$field_list).") TYPE=MyISAM";
		return $this->DatabaseHandler->Query($sql);
	}
	function _alterTable($table_name,$field_list)
	{
		$table_field_list = $this->DatabaseHandler->SetTable($table_name);
		$sql = "alter table `{$table_name}` ";
		foreach ($field_list as $field=>$info)
		{
			if(false === ($key = array_search($field,$table_field_list)))
			{
				$sql_l[]="ADD ".preg_replace("/,\s*([a-z])/i",",ADD \\1",$info);
			}
		}

		if(count($sql_l)<1) return false;
		$sql.=implode(",\r\n\t",$sql_l);
		return $this->DatabaseHandler->Query($sql);
	}

	
	function _getTablePri($table_name)
	{
		$table_field_list = $this->DatabaseHandler->SetTable($table_name);

		return $table_field_list['PRI'];
	}

	
	function _filterField($fields,$_return = 'array',$field_list = false,$limit='')
	{
		$fields = (array) $fields;
		$_fields = array();
		foreach ($fields as $f)
		{
			if(false !== strpos($f,','))
			{
				$_f_list = explode(',',$f);
				foreach ($_f_list as $_f)
				{
					$_f = trim((string) $_f);
					if('' == $_f or ($field_list and false == in_array($_f,$field_list))) continue;

					$_fields[$_f] = $_f;
				}
			}
			else
			{
				$f = trim((string) $f);
				if('' == $f or ($field_list and false == in_array($f,$field_list))) continue;

				$_fields[$f] = $f;
			}
		}

		if('array' == $_return) return $_fields;

		return implode($_return,$_fields);
	}

	
	function _filterId($ids,$_return = 'array',$limit='')
	{
		$ids = (array) $ids;
		$_ids = array();
		foreach ($ids as $i)
		{
			if(false !== strpos($i,','))
			{
				$_i_list = explode(',',$i);
				foreach ($_i_list as $_i)
				{
					$_i = (int) $_i;
					if($_i > 0) $_ids[$_i] = $_i;
				}
			}
			else
			{
				$i = (int) $i;
				if($i > 0) $_ids[$i] = $i;
			}
		}

		if('array' == $_return) return $_ids;

		return implode($_return,$_ids);
	}
	function _strtolower($str)
	{
		return urldecode(strtolower(urlencode($str)));
	}
	function _setError($msg)
	{
		$this->_error[] = $msg;
	}
	function GetError()
	{
		return $this->_error;
	}
	
	function getCommonInterestUser($uid=MEMBER_ID,$limit=10){
		$uid = max(0, (int) $uid);
		$limit = max(0, (int) $limit);
		if($uid < 1 || $limit < 1) {
			return false;
		}
		$user = array();
		$sql = "SELECT f2.uid,COUNT(*) AS common_count ,m.username,m.nickname 
				FROM ".TABLE_PREFIX."tag_favorite f 
				LEFT JOIN ".TABLE_PREFIX."tag_favorite  f2 ON f2.tag = f.tag AND f2.uid != '$uid' 
				LEFT JOIN ".TABLE_PREFIX."members m ON m.uid = f2.uid 
				WHERE f.uid = '$uid' and m.topic_count > 0  
				GROUP BY f2.uid  
				ORDER BY common_count DESC 
				limit $limit "; 
		$query = DB::query($sql);
		while ($rs = DB::fetch($query)){
			$rs['face'] = face_get($rs['uid']);
			$user[$rs['uid']] = $rs;
		}
		$user = Load::model('buddy')->follow_html($user);
		return $user;
	}
};


?>
