<?php
/**
 * other.mod.php
 * 版本号：1.0
 * 最后修改时间：2009年9月28日 14时10分42秒
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 网站杂项，其他模块
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $ShowConfig;

	var $CacheConfig;

	var $TopicLogic;

	var $ID = '';


	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->ID = (int) ($this->Post['id'] ? $this->Post['id'] : $this->Get['id']);

		
		$this->TopicLogic = Load::logic('topic', 1);

		$this->CacheConfig = ConfigHandler::get('cache');

		$this->ShowConfig = ConfigHandler::get('show');

		$this->InfoConfig = ConfigHandler::get('web_info');

		$this->Execute();

	}

	
	function Execute()
	{
		ob_start();
		if (in_array($this->Code,array('wap','sms','mobile','iphone','android','pad'))) {
			$this->Wap();
		} elseif ('test' == $this->Code) {
			$this->Test();
		}  elseif ('about' == $this->Code) {
			$this->About();
		} elseif ('contact' == $this->Code) {
			$this->Contact();
		} elseif ('joins' == $this->Code) {
			$this->Joins();
		} elseif ('media' == $this->Code) {
			$this->Media();
		} elseif ('groupdelete' == $this->Code) {
			$this->GroupDelete();
		} elseif ('vip_intro'==$this->Code) {
			$this->VipIntro();
		} elseif ('medal'==$this->Code) {
			$this->Medal();
		} elseif ('notice'==$this->Code) {
			$this->Notice();
		} elseif ('checkmedal'==$this->Code) {
			$this->CheckMedal();
		} elseif ('media_more'==$this->Code) {
			$this->Media_More();
		} elseif ('add_favor_tag'==$this->Code) {
			$this->addFavoriteTag();
		} elseif ('regagreement' == $this->Code) {
						$this->regagreement();
		} elseif ('seccode' == $this->Code) {
			$this->Seccode();
		} elseif ('navigation' == $this->Code) {
			$this->Navigation();
		} elseif ('usergroup' == $this->Code) {
			$this->UserGroupList();
		} elseif ('qmd' == $this->Code) {
			$this->Qmd();
		} else {
			$this->Main();
		}
		$body=ob_get_clean();

		$this->ShowBody($body);
	}

    function Main()
    {
        $this->Messager("页面不存在",null);
    }

	function Test()
	{
		exit;
	}


		function Media()
	{
				$sql = "select `id`,`media_name`,`media_count` from `".TABLE_PREFIX."media`  order by `order` asc";
				$query = $this->DatabaseHandler->Query($sql);
		$media_list = array();
		$media_ids = array();
		while (false != ($row = $query->GetRow()))
		{
			$media_ids[$row['id']] = $row['id'];
			$media_list[] = $row;
		}

				$limit = $this->ShowConfig['media']['user'];

				$media_user = array();
		foreach ($media_list as $row) {
			$user_media_id = $row['id'];

			$where_list['media_id'] = " `media_id` = '$user_media_id'";
			$where = ' where '.implode(' AND ',$where_list).'order by `fans_count` desc limit 0,'.$limit;

			$_list = $this->TopicLogic->GetMember($where,"`uid`,`ucuid`,`media_id`,`username`,`aboutme`,`nickname`,`face_url`,`face`,`validate`");
			if($_list){
				foreach ($_list as $row) {
					$row['validate_html'] = $row['validate_html'];
					$media_user[] = $row;
				}
			}
		}

		$this->Title = "媒体汇 ";
		include($this->TemplateHandler->Template('media'));
	}

		function Media_More()
	{
		$ids = (int) $this->Get['ids'];

		$media_info = DB::fetch_first("SELECT `id`,`media_name` FROM ".DB::table('media')." WHERE id='{$ids}'");


				$sql = "select `id`,`media_name`,`media_count` from `".TABLE_PREFIX."media`  order by `id` desc";
		$query = $this->DatabaseHandler->Query($sql);
		$media_list = array();
		$media_ids = array();
		while (false != ($row = $query->GetRow()))
		{
			$media_ids[$row['id']] = $row['id'];
			$media_list[] = $row;
		}

		$per_page_num = $this->ShowConfig['media_view']['user'] ? $this->ShowConfig['media_view']['user'] :40;
		$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&amp;code={$this->Code}&ids={$this->Get['ids']}" : "");

				$sql = " select count(*) as `total_record` from `".TABLE_PREFIX."members` where `media_id` = '{$ids}'";
		$total_record = DB::result_first($sql);

				$page_arr = page ($total_record,$per_page_num,$query_link,array('return'=>'array',));

		$where = " where `media_id` = '{$ids}' order by `topic_count` desc {$page_arr['limit']} ";

		$member_list = $this->TopicLogic->GetMember($where,"`uid`,`ucuid`,`media_id`,`aboutme`,`username`,`nickname`,`face_url`,`face`,`validate`");

		
		$this->Title = "媒体汇 用户";
		include($this->TemplateHandler->Template('media_more'));


	}

    function Wap()
  {
  	$member = jsg_member_info(MEMBER_ID);
  	  	if('wap' == $this->Code){
		if (!$this->Config['wap']) {
	  		$this->Messager("{$this->Config['site_name']}的手机访问功能还未开启",null);
	  	}
	
		$this->Title = "手机访问 {$this->Config['site_name']}";
		$this->MetaKeywords = "手机访问,wap,{$this->Config['site_name']}";
		$this->MetaDescription = $this->Title."，可登录、查看、发微博、评论转发等";
		
	  	} elseif ('mobile' == $this->Code){
  		$this->Title = "3G手机访问 {$this->Config['site_name']}";
  		$this->MetaKeywords = "手机访问,wap,{$this->Config['site_name']}";
		$this->MetaDescription = $this->Title."，可登录、查看、发微博、评论转发等";
	
	  	} elseif ('sms' == $this->Code){
  		$this->Title = "短·彩信版";
        define('IN_SMS_MOD',      true);
        $sms_msg_return = 1;
        include(ROOT_PATH . 'modules/sms.mod.php');
  	} elseif ('iphone' == $this->Code){
  		$this->Title = "iPone客户端";
  	} elseif ('android' == $this->Code){
  		$this->Title = "Android客户端";
  	} elseif ('pad' == $this->Code){
  		$this->Title = "Android平板客户端";
  	}
	
	include($this->TemplateHandler->Template('topic_wap'));

  }

    function About()
  {
  	$this->Title = "关于我们";

	$member = jsg_member_info(MEMBER_ID);

  	include($this->TemplateHandler->Template('topic_about'));

  }



		function Medal()
	{
		Load::logic('other');
		$otherLogic = new OtherLogic();
		$act_list = array('share'=>'分享到微博','qmd'=>'签名档',);

		$act_list['show'] = array('name'=>'微博秀','link_mod'=>'show','link_code'=>'show',);

        $act_list['imjiqiren'] = 'QQ机器人';
        if('qqrobot'==$this->Code && !isset($act_list['qqrobot']) && isset($act_list['imjiqiren']))
        {
            $this->Code = 'imjiqiren';
        }
        $act_list['medal'] = array('name'=>'勋章','link_mod'=>'other','link_code'=>'medal',);
        $act_list['sms'] = '短信';
		$act = isset($act_list[$this->Code]) ? $this->Code : 'share';

		$uid = MEMBER_ID;

				$member = $this->TopicLogic->GetMember(MEMBER_ID);
		if ($member['medal_id']) {
			$medal_list = $this->TopicLogic->GetMedal($member['medal_id'],$member['uid']);
		}
		$view = $this->Get['view'];
		$all_medal = array();
		if($view == 'my'){
			$sql = "select u.medalid as medal_id,u.is_index,u.dateline,
						   m.medal_img,m.medal_name,m.medal_depict,m.conditions
				    from `".TABLE_PREFIX."user_medal` u
					left join `".TABLE_PREFIX."medal` m on m.id = u.medalid
					where u.uid = '$uid'
					and m.is_open  = 1
					order by u.dateline desc";
						if($this->Config[sina_enable] && sina_weibo_init($this->Config)){
    			$sina = sina_weibo_has_bind(MEMBER_ID);
    		}
    		    		if($this->Config[imjiqiren_enable] && imjiqiren_init($this->Config)){
    			$imjiqiren = imjiqiren_has_bind(MEMBER_ID);
    		}
    		    		if($this->Config[sms_enable] && sms_init($this->Config)){
    			$sms = sms_has_bind(MEMBER_ID);
    		}
    					if($this->Config[qqwb_enable] && qqwb_init($this->Config)){
				$qqwb = qqwb_bind_icon(MEMBER_ID);
			}
		}else{
			$sql = "SELECT m.id as medal_id,m.medal_img,m.medal_name,m.medal_depict,m.conditions,u.dateline,y.apply_id
					FROM ".TABLE_PREFIX."medal m
					LEFT JOIN ".TABLE_PREFIX."user_medal u ON (u.medalid = m.id AND u.uid = '$uid')
					LEFT JOIN ".TABLE_PREFIX."medal_apply y ON (y.medal_id = m.id AND y.uid = '$uid')
					WHERE m.is_open = 1
					ORDER BY u.dateline DESC,m.id";

			$query = $this->DatabaseHandler->Query($sql);
			while ($rs = $query->GetRow()){
				$rs['conditions'] = unserialize($rs['conditions']);
				if(in_array($rs['conditions']['type'],array('topic','reply','tag','invite','fans')) && !$rs['dateline']){
					$result = $otherLogic->autoCheckMedal($rs['medal_id']);
				}
			}
		}

		$query = $this->DatabaseHandler->Query($sql);
		while ($rsdb = $query->GetRow()){
			$rsdb['conditions'] = unserialize($rsdb['conditions']);
			if($rsdb['is_index']){
				$rsdb['show'] = 'checked';
			}
			$all_medal[$rsdb['medal_id']] = $rsdb;
		}

		$count = count($all_medal);

		$this->Title = "{$this->Config['site_name']}勋章";
		include($this->TemplateHandler->Template('topic_medal'));
	}


		function CheckMedal()
	{
		$medalid = (int)$this->Get['medal_id'];
		Load::logic('other');
		$otherLogic = new OtherLogic();
		$result = $otherLogic->autoCheckMedal($medalid);
		if($result == '1'){
			$this->Messager("成功点亮",'index.php?mod=other&code=medal');
		}else if($return == '3'){
			$this->Messager("你已获得此勋章了哦",-1);
		}else{
			$this->Messager("未达成获取勋章的条件",-1);
		}
	}


	 	function GroupDelete()
	{
		$gid = (int) $this->Get['gid'];

		$sql = "select `id`,`uid` from `".TABLE_PREFIX."group` where `id` ='{$gid}'";
		$query = $this->DatabaseHandler->Query($sql);
		$user_group = $query->GetRow();

		if($user_group['uid'] != MEMBER_ID)
		{
		  $this->Messager('分组不存在','index.php',0);
		}

		$sql = "delete from `".TABLE_PREFIX."group` where `id`='{$gid}' and `uid` =".MEMBER_ID;
	    $this->DatabaseHandler->Query($sql);

	  	$sql = "delete from `".TABLE_PREFIX."groupfields` where `gid`='{$gid}'";
	  	$this->DatabaseHandler->Query($sql);

		$this->Messager(NULL,'index.php?mod='.MEMBER_NAME.'&code=follow',0);
	}


    function Notice()
  {
  	$ids = (int) $this->Get['ids'];

  	  	if($ids)
  	{
	  	$sql="Select * From ".TABLE_PREFIX.'notice'." Where id = '{$ids}' ";
		$query = $this->DatabaseHandler->Query($sql);
		$view_notice=$query->GetRow();

		$title		 =  $view_notice['title'];
		$content  =  $view_notice['content'];
		$dateline =  my_date_format2($view_notice['dateline']);

		  		$sql="select `id`,`title` from ".TABLE_PREFIX.'notice'." order by `dateline` desc  ";
    	$query = $this->DatabaseHandler->Query($sql);
    	$list_notice = array();
    	while (false != ($row = $query->GetRow()))
    	{

    		$row['titles'] 	= cutstr($row['title'],26);
    		$list_notice[] 	= $row;
    	}

		$this->Title = "网站公告 - {$view_notice['title']}";
	}
	else{

    	    	$this->Title = '网站公告';

    	$per_page_num = $this->ShowConfig['notice']['list'] ? $this->ShowConfig['notice']['list'] : 10;
		$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&amp;code={$this->Code}" : "");

		    	$sql = " select count(*) as `total_record` from `".TABLE_PREFIX."notice`";
		$total_record = DB::result_first($sql);

				$page_arr = page ($total_record,$per_page_num,$query_link,array('return'=>'array',));

    	$sql="select `id`,`title` from ".TABLE_PREFIX.'notice'." order by `dateline` desc {$page_arr['limit']} ";
    	$query = $this->DatabaseHandler->Query($sql);
    	$list_notice = array();
    	while (false != ($row = $query->GetRow()))
    	{
    		$row['titles'] 	= cutstr($row['title'],26);
    		$list_notice[] 	= $row;
    	}
	}

	include($this->TemplateHandler->Template('view_notice'));

  }

 	function Contact()
    {
    	$this->Title = "联系我们";

		$member = jsg_member_info(MEMBER_ID);

    	include($this->TemplateHandler->Template('topic_about'));
    }

    function Joins()
    {
      	$this->Title = "加入我们";

		$member = jsg_member_info(MEMBER_ID);

      	include($this->TemplateHandler->Template('topic_about'));
    }


        function VipIntro()
    {	   	
    	if(MEMBER_ID < 1)
    	{
    		$this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",'index.php?mod=member&code-login',3);
    	}
    	
    	
    	$member = jsg_member_info(MEMBER_ID);
    	if(!$member['__face__']){
    		$this->Messager("已上传头像用户才能进行认证","index.php?mod=settings&code=face");
    	}
    	
        Load::lib('form');
		$FormHandler = new FormHandler();
		
    	Load::logic('validate_category');
		$this->ValidateLogic = new ValidateLogic($this);
		$is_card_pic = $this->Config['card_pic_enable']['is_card_pic'];
    	    	if($this->Post['postFlag'])
    	{	
    		    		$validate_info = $this->Post['validate_remark'];
    		    		$validate_info = trim(strip_tags((string) $validate_info));
    	    if(empty($validate_info)){
	    		$this->Messager('认证说明不能为空',-1);
	    	}
	    		    	$f_rets = filter($validate_info);
	    	if($f_rets && $f_rets['error'])
	    	{
	    		$this->Messager($f_rets['msg'],-1);
	    	}
	    	
	    	$category_fid = $this->Post['category_fid'];
	    	$category_id = $this->Post['category_id'];
    		if(empty($category_fid) || empty($category_id)){
    			$this->Messager('认证类别不能为空',-1);
    		}
    		
    		$city  = (int) $this->Post['city'];
    		if($city < 1){
    			$this->Messager('请填写所在区域',-1);
    		}
    		
    		$validate_true_name = $this->Post['validate_true_name'];
    	    if(empty($validate_true_name)){
    			$this->Messager('真实姓名不能为空',-1);
    		}
    		
    	    $validate_card_type = $this->Post['validate_card_type'];
    	    if(empty($validate_card_type)){
    			$this->Messager('证件类型不能为空',-1);
    		}

    	    $validate_card_id = $this->Post['validate_card_id'];
    	    if(empty($validate_card_id)){
    			$this->Messager('证件号码不能为空',-1);
    		}
    		if($is_card_pic){
    			$field = 'card_pic';
    			if(empty($_FILES) || !$_FILES[$field]['name'])
				{
					$this->Messager("请上传证件图片",-1);
				}
    		}
	    	
	    				$data = array(
				'uid' 			=> MEMBER_ID,
				'category_fid'  => (int) $this->Post['category_fid'],
				'category_id'   => (int) $this->Post['category_id'],
				'province' 		=> $this->Post['province'],
				'city'			=> $this->Post['city'],
				'is_audit'		=> 0,
				'dateline'	    => Time(),

			);

			$return_info = $this->ValidateLogic->Member_Validate_Add($data);
	
			if($return_info['ids'])
			{
							    if($is_card_pic)
		    	{
		    		$image_id = $return_info['ids'];
		    		
		    		
					if(empty($_FILES) || !$_FILES[$field]['name'])
					{
						$this->Messager("请上传证件图片",-1);
					}

					$image_path = RELATIVE_ROOT_PATH . 'images/' . $field . '/'.$image_id.'/';
					$image_name = $image_id . "_o.jpg";
					$image_file = $image_path . $image_name;
					$image_file_small = $image_path.$image_id . "_s.jpg";
					
					if (!is_dir($image_path)) {
						Load::lib('io', 1)->MakeDir($image_path);
					}
		
					Load::lib('upload');
					$UploadHandler = new UploadHandler($_FILES,$image_path,$field,true);
					$UploadHandler->setMaxSize(2048);
					$UploadHandler->setNewName($image_name);
					$result=$UploadHandler->doUpload();
					
					if($result) {
						$result = is_image($image_file);
					}
		
					if (!$result) {
						$this->Messager("上传图片失败",-1);
					}
				
					
			    	list($w,$h) = getimagesize($image_file);
			        if($w > 601)
			        {
			            $tow = 599;
			            $toh = round($tow * ($h / $w));
			
			            $result = makethumb($image_file,$image_file,$tow,$toh);
			
			            if(!$result)
			            {
			                Load::lib('io', 1)->DeleteFile($image_file);
			                js_alert_output('大图片缩略失败');
			            }
			        }
		        
		        	$image_file = addslashes($image_file);
		
		        	$validate_card_pic = " `validate_card_pic` = '{$image_file}' ,"; 
	
		    	}
		    	
												$sql = "update ".TABLE_PREFIX."memberfields 
						set {$validate_card_pic} 
							`validate_remark` = '{$this->Post['validate_remark']}' ,
							`validate_true_name`='{$this->Post['validate_true_name']}' ,
							`validate_card_id` = '{$this->Post['validate_card_id']}' ,
							`validate_card_type` = '{$this->Post['validate_card_type']}' 
						where `uid`='".MEMBER_ID."'";
				$this->DatabaseHandler->Query($sql);
				
				if($notice_to_admin = $this->Config['notice_to_admin']){
					$message = "用户".MEMBER_NICKNAME."申请了身份认证，<a href='admin.php?mod=vipintro&code=vipintro_manage' target='_blank'>点击</a>进入审核。";
					$pm_post = array(
						'message' => $message,
						'to_user' => str_replace('|',',',$notice_to_admin),
					);
										$admin_info = DB::fetch_first('select `uid`,`username`,`nickname` from `'.TABLE_PREFIX.'members` where `uid` = 1');
					load::logic('pm');
					$PmLogic = new PmLogic();
					$PmLogic->pmSend($pm_post,$admin_info['uid'],$admin_info['username'],$admin_info['nickname']);
				}
			}

			if($return_info['msg_info'])
			{
				$this->Messager($return_info['msg_info']);
			}

    	}

    	    	$sql = "select * from `".TABLE_PREFIX."validate_category_fields` where `uid`='".MEMBER_ID."' ";
		$query = $this->DatabaseHandler->Query($sql);
		$validate_info = $query->GetRow();
		
		   		$sql = "select * from `".TABLE_PREFIX."memberfields` where `uid`='".MEMBER_ID."'";
		$query = $this->DatabaseHandler->Query($sql);
		$memberfields = $query->GetRow();
		
		$dateline = date('Y-m-d',$validate_info['dateline']);

				if(empty($validate_info['uid']) || $validate_info['is_audit'] == -1)
		{
	    				if(!$memberfields) {
				$memberfields = array();
				$memberfields['uid'] = $member['uid'];
	
				$sql = "insert into `".TABLE_PREFIX."memberfields` (`uid`) values ('{$member['uid']}')";
				$this->DatabaseHandler->Query($sql);
			}
	
			$_options = array(
					'0' => array(
						'name' => '请选择',
						'value' => '0',
					),
					'身份证' => array(
						'name' => '身份证',
						'value' => '身份证',
					),
					'学生证' => array(
						'name' => '学生证',
						'value' => '学生证',
					),
					'军官证' => array(
						'name' => '军官证',
						'value' => '军官证',
					),
					'护照' => array(
						'name' => '护照',
						'value' => '护照',
					),
					'营业执照' => array(
						'name' => '营业执照',
						'value' => '营业执照',
					),
					'官方公函' => array(
						'name' => '官方公函',
						'value' => '官方公函',
					),
					'其他' => array(
						'name' => '其他',
						'value' => '其他',
					),
				);

			$select_value = $memberfields['validate_card_type'] ? $memberfields['validate_card_type'] : "身份证";
			$validate_card_type_select = $FormHandler->Select('validate_card_type',$_options,$select_value);
			
						$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."common_district where `upid` = '0' order by list");
			while ($rsdb = $query->GetRow()){
				$province[$rsdb['id']]['value']  = $rsdb['id'];
				$province[$rsdb['id']]['name']  = $rsdb['name'];
				if($member['province'] == $rsdb['name']){
						$province_id = $rsdb['id'];
					}
			}
			$province_list = $FormHandler->Select("province",$province,$province_id,"onchange=\"changeProvince();\"");   		
			$member_city = DB::fetch_first("SELECT * FROM ".DB::table('common_district')." WHERE `name`='{$member['city']}'");
		}

    	    	$where_list = " `category_id` = '' ";
    	$query = DB::query("SELECT *  
							FROM ".DB::table('validate_category')." 
							where {$where_list}  ORDER BY id ASC");		
		$category_list = array();
		while ($value = DB::fetch($query)) {
			$category_list[] = $value;
		}

    	    	if($this->Post['category_fid'])
    	{
    	  $sub_category_list = $this->ValidateLogic->Small_CategoryList($this->Post['category_fid']);
    	}
    	
    	$this->Title = "{$this->Config['site_name']}身份验证";
    	include($this->TemplateHandler->Template('topic_vip'));
    }

	function Navigation()
    {

    	$slide_config = ConfigHandler::get('navigation');
        $slide_list = $slide_config['list'];


    	include($this->TemplateHandler->Template('test_navigation'));
    }



    
	function regagreement()
  	{
  		$this->Title = '用户使用协议';
		include(template('regagreement'));
	}

	
	function Seccode()
	{
		Load::lib('seccode');
		$seccode = mkseccode();
		jsg_setcookie('seccode', authcode($seccode, 'ENCODE'));
		$s = new Seccode();
		$s->code = $seccode;
		$s->datapath = ROOT_PATH."images/seccode/";
		$s->display();
	}



	
	function UserGroupList()
	{
		if(MEMBER_ID < 0)
		{
			$this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",'index.php',0);
		}
		$member = $this->TopicLogic->GetMember(MEMBER_ID);


		$per_page_num = 15;
		$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&amp;code={$this->Code}&ids={$this->Get['ids']}" : "");

				$sql = " select count(*) as `total_record` from `".TABLE_PREFIX."group` where `uid` = '".MEMBER_ID."'";
		$total_record = DB::result_first($sql);

				$page_arr = page ($total_record,$per_page_num,$query_link,array('return'=>'array',));

		$where = " where `uid` = '".MEMBER_ID."' order by `id` desc {$page_arr['limit']} ";

		$sql = " select * from `".TABLE_PREFIX."group` {$where} ";
		$query = $this->DatabaseHandler->Query($sql);
		$grouplist = array();
		while (false != ($row = $query->GetRow()))
		{
			$grouplist[] = $row;
		}

		$this->Title = '管理分组';
		include($this->TemplateHandler->Template('group'));

	}

	function Qmd() 
	{
		$image_file = $this->Config['site_url'] .'/images/qmd_error.gif';
		
		$uid = (int) $this->Get['ids'];
    		
		/**
		 * @author 狐狸<foxis@qq.com>
		 * @todo 优化签名档生成机制、不再频繁生成
		 */
        if($this->Config['is_qmd'] && $uid > 0 && (false != ($member = jsg_member_info($uid)))) {
			if(!$member['qmd_url'] || ($this->Config['ftp_on'] && (time() > $member['lastpost'] + 1800)) || (!$this->Config['ftp_on'] && $member['lastpost'] > @filemtime($member['qmd_url']))) {				
        		        		$member_qmd = ($member['qmd_img'] ? $member['qmd_img'] : 'images/qmd.jpg');
		    	
	    		$member['qmd_url'] = Load::logic('other', 1)->qmd_list($uid, $member_qmd);
			}
			
			if($member['qmd_url']) {
				$image_file = ($this->Config['ftp_on'] ? $member['qmd_url'] : $this->Config['site_url'] . '/' . $member['qmd_url']);
			}
        }
 
        header("Location: $image_file");
	}
}

?>
