<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename validate_category.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-04 18:49:37 154516344 1729700426 7280 $
 *******************************************************************/



if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ValidateLogic
{

	var $Config;
	var $DatabaseHandler;

		
	function ValidateLogic()
	{
		$this->DatabaseHandler = &Obj::registry("DatabaseHandler");
		
		$this->Config = &Obj::registry("config");
	}
	
	
	
	
	
	function CategoryList($cid='') 
	{   
		$where_list = "";
		if($cid || $cid == 0){
			$where_list = " `category_id` = '{$cid}' ";
		}
	    
		$where_list = $where_list ? " where ".$where_list : "";
		
		$query = DB::query("SELECT *  
							FROM ".DB::table('validate_category')."  
							{$where_list}  ORDER BY id ASC");		
		$cat_ary = array();
		while ($value = DB::fetch($query)) {
			$cat_ary[] = $value;
		}

		return $cat_ary;

		
	}
	

	
	
		function CategoryView($ids=0) 
	{	
		
		$error_info = array(
							
							0 => '未找到指定分类信息',
		);
		
		if($ids < 1)
		{
			return $error_info[0];
		}
	
		$cat_view_ary = DB::fetch_first("SELECT *  
							FROM ".DB::table('validate_category')." 
							where `id` = '{$ids}' ");		

		return $cat_view_ary ;
		
	}
	
	
	function CategoryUserList($where='',$limit='',$query_link='',$orderby='uid') 
	{	
		
		$per_page_num = $limit ? $limit : 20;
		
		
				$query = DB::query("SELECT *  
							FROM ".DB::table('validate_category_fields')." 
							{$where}  order by `id` desc");		
		$uds = array();
		while ($v = DB::fetch($query)) 
		{
			$uds[$v['uid']] = $v['uid'];
		}

				$total_record = count($uds);
		
				$page_arr = page ($total_record,$per_page_num,$query_link,array('return'=>'array',));
		
		$wherelist = "where `uid` in (".jimplode($uds).") and `city` !='' order by `{$orderby}` desc  {$page_arr['limit']} ";

		
		$TopicLogic = Load::logic('topic', 1);
		
		$members = $TopicLogic->GetMember($wherelist,"`uid`,`ucuid`,`media_id`,`aboutme`,`username`,`nickname`,`province`,`city`,`face_url`,`face`,`validate`");
		$members = Load::model('buddy')->follow_html($members, 'uid', 'follow_html2');

		$user_ary = array('member'=>$members,'uids' =>$uds,'pagearr'=>$page_arr);
		
		return $user_ary;
	}
	
	
	function getValidatedUid(){
				$query = DB::query("SELECT *  
							FROM ".DB::table('validate_category_fields')."  
							WHERE is_audit = 1 order by `id` desc");		
		$uds = array();
		while ($v = DB::fetch($query)) 
		{
			$uds[$v['uid']] = $v['uid'];
		}
		return $uds;
	}
	
	
		
	function CategoryProvinceList($pid=0,$cid=0) 
	{
		if($pid)
		{
			$province_where = "where `province` = '{$pid}'  ";
		}

		if($cid)
		{
			$city_where = " and `city` = '{$cid}'  ";
		}

		$where_list = $province_where . $city_where;
		
		if(empty($where_list))
		{
			return false;
		}
		
		$query = DB::query("SELECT *  
							FROM ".DB::table('validate_category_fields')." 
							{$where_list}  order by `dateline` desc limit 0,20");		
		$cat_Province_ary = array();
		while ($value = DB::fetch($query)) 
		{
			$cat_Province_ary[$value['uid']] = $value['uid'];
		}
		
		return $cat_Province_ary;
		
	}
	
	
		
	function CategoryCityList($where='',$is_check_user=0)
	{

		$query = DB::query("SELECT *  
							FROM ".DB::table('common_district')." 
							{$where}  order by list ");		
		$ary_list = array();
		while ($value = DB::fetch($query)) 
		{	
			if($is_check_user)
			{
			 	$where = "where `city` = '".$value['name']."' limit 0,1";
			 	$count = DB::result_first("SELECT count(*) FROM ".DB::table('members')." {$where} ");
			 	$value['user_count'] =  $count; 
			}		  
			
			$ary_list[$value['id']] = $value ;
			
		}
	
		return $ary_list;
	}
	
	
	
	
	
	function Member_Validate_Add($data='') 
	{   
		
		$error_info = array(
							
							0=>'申请失败，未知错误',
							1=>'申请成功，等待审核',
							2=>'已经提交过认证，等待审核',
							3=>'请填写完整的认证信息'
		);
		
		$msg = $error_info[1]; 

		if(empty($data)){
			$msg = $error_info[3];
			$ary_info = array('msg_info'=>$msg);
			
			return $ary_info;
			
		}
		
			    $validate_info = DB::fetch_first("select * from ".DB::table('validate_category_fields')." where `uid`='".MEMBER_ID."' ");
	    if($validate_info && $validate_info['is_audit'] != -1) 
	    {
			$msg = $error_info[2];
			$ary_info = array('msg_info'=>$msg);
			
			return $ary_info;
			
	    }
	
	    $table_name = 'validate_category_fields';
	    if($validate_info['is_audit']== -1){
			$ids = $validate_info['id'];
			DB::update($table_name, $data," id = '$ids' ");
	    }else{
	    	DB::insert($table_name, $data);
			$ids = $this->DatabaseHandler->Insert_ID();
	    }
        if ($ids < 1)
        {
            $msg = $error_info[0];
            $ary_info = array('msg_info'=>$msg);
			
			return $ary_info;
			
        }
        
                $province_info = DB::fetch_first("select * from ".DB::table('common_district')." where `id`='". (int) $data['province']."' ");
	    $city_info = DB::fetch_first("select * from ".DB::table('common_district')." where `id`='". (int) $data['city']."' ");

        $sql = "update `".TABLE_PREFIX."members` set `province`='{$province_info['name']}',`city`='{$city_info['name']}' where `uid`='".MEMBER_ID."'";	
        $this->DatabaseHandler->Query($sql);
		
  		$ary_info = array('msg_info'=>$msg,'ids'=>$ids);
		
        return $ary_info;

	}

	
		function Small_CategoryList($category_fid=0) 
	{

				if($category_fid > 0)
		{
			$sql = "select * from `".TABLE_PREFIX."validate_category` where `category_id` = '{$category_fid}' ";
			$query = $this->DatabaseHandler->Query($sql);
			$subclass_list = array();
			while (false != ($row = $query->GetRow()))
			{
				$subclass_list[] = $row;
			}

		   		   if($subclass_list)
		   {
			   for ($i = 0; $i < count($subclass_list); $i++)
			   {
			 	 echo '<option value="'.$subclass_list[$i]['id'].'">'.$subclass_list[$i]['category_name'].'</option>';
			   }
		   }
		   else 
		   {
		   		echo '<option value="0" selected="selected">没有分类</option>';
		   }
		   		   exit;
		}
		else
		{
					   		echo '<option value="none" selected="selected">没有分类</option>';
		   			   	exit;
		}
		
	
		
	}


}
?>