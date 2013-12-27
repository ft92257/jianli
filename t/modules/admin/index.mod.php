<?php
/**
 *[JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 文件名：index.mod.php
 * 版本号：1.0
 * 最后修改时间：2006年7月13日 20:42:26
 * 作者：狐狸<foxis@qq.com>
 * 功能描述：首页模块
 */
if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class ModuleObject extends MasterObject
{

	
	var $Config = array(); 

	function ModuleObject(& $config)
	{
		$this->MasterObject($config);

		$this->Execute();

	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'menu':
				$this->Menu();
				break;
			case 'home':
				$this->Home();
				break;
			case 'help':
				$this->Help();
				break;
			case 'theme':
				$this->Theme();
				break;
			case 'affiche':
				$this->Affiche();
				break;
			case 'recommend':
				$this->recommend();
				break;
			case 'upgrade_check':
				$this->upgrade_check();
				break;
			case 'lrcmd_nt':
				$this->lrcmd_nt();
				break;
			case 'ccdsp':
				upsCtrl()->dspControlDone();
				break;
			default:
				$this->Main();
		}
		$body = ob_get_clean();

		$this->ShowBody($body);

	}

	
	function main()
	{

		if(MEMBER_ID<1) {
			$this->Messager("游客无权限进入后台,请先<a href='index.php?mod=login'>点此登录</a>。",null);
		}
			
		$has_p=$this->MemberHandler->HasPermission('index','',1);
		if($has_p)
		{
			$menuList = $this->Menu();
			include($this->TemplateHandler->Template('admin/index'));
			exit;
		}
		else
		{
			$this->Messager("您无权进入后台。",null);
		}		
	}

	function Affiche()
	{

		include($this->TemplateHandler->Template('admin/affiche'));

		exit;
	}

	function _recommendList() {
		
	}

	
	function Menu()
	{
		global $rewriteHandler,$config;
		$default_open=true;		$open_onlyone=false;
				$open_list=explode('_',$this->Get['open']);
		require(ROOT_PATH.'setting/admin_left_menu.php');
		#if NEDU
		if (defined('NEDU_MOYO'))
		{
			nlogic('admin.menu')->hooks_nav(&$menu_list);
		}
		#endif
				foreach ($menu_list as $_key=>$_menu)
		{
			if($_menu['sub_menu_list'])
			{
				foreach ($_menu['sub_menu_list'] as $_sub_key=>$_sub_menu)
				{
					if(strpos($_sub_menu['link'],":\/\/")!==false)continue;
					preg_match("~mod=([^&\x23]+)&?(code=([^&\x23]*))?~",$_sub_menu['link'],$match);
					list(,$_mod,,$_code)=$match;
					if(!empty($_mod) && $this->MemberHandler->HasPermission($_mod,$_code,1)==false)
					{
						unset($menu_list[$_key]['sub_menu_list'][$_sub_key]);
					}
				}
			}
		}

		$all_open_list=array_keys($menu_list);
		if($default_open && isset($this->Get['open'])==false)
		{
			$open_list=$all_open_list;
		}

		foreach($menu_list as $key=>$menu)
		{
			if ($key == 1)
			{
								foreach ($menu_list as $_menu_list_s)
				{
					foreach((array)$_menu_list_s['sub_menu_list'] as $menu_s)
					{
						if($menu_s['shortcut'])
						{
							$menu['sub_menu_list'][] = $menu_s;
						}
					}
				}
			}
			if(empty($menu['sub_menu_list']))continue;
			$menu_tmp_list[$key]=$menu;
			if(in_array($key,$open_list)!=false)
			{
				$menu_tmp_list[$key]['img']='minus';
				$open_list_tmp=$open_list;
				unset($open_list_tmp[array_search($key, $open_list_tmp)]);
							}
			else
			{
				$menu_tmp_list[$key]['img']='plus';
								$menu_tmp_list[$key]['sub_menu_list']=array();
			}
			if(isset($menu['sub_menu_list']))
			{

				$menu_tmp_list[$key]['link']="?mod=index&code=menu"; 				$menu_tmp_list[$key]['target']="";

			}
			else
			{
				$menu_tmp_list[$key]['target']='target="main"';
			}
		}
		$menu_list=$menu_tmp_list;

		return $menu_list;
	}
	
	function home()
	{
		$program_name = "记事狗";

				include(ROOT_PATH . 'setting/admin_left_menu.php');
		$shorcut_list=array();
		foreach ($menu_list as $_menu_list)
		{
			foreach((array)$_menu_list['sub_menu_list'] as $menu)
			{
				if($menu['shortcut'])
				{
					$shortcut_list[$_menu_list['title']][]=$menu;
				}
			}
		}

				$item_list=array(
			"<b>微博数</b>"=>"topic",
			"<b>标签数</b>"=>"tag",
			"<b>会员数</b>"=>"member",
			"<b>在线人数</b>"=>"sessions",
		);

		$sys_env = array();
		$cache_id = "misc/admin_statistic";
		if(false === ($statistic = cache_file('get', $cache_id))) {
			$statistic=array();
			foreach ($item_list as  $item_name=>$item)
			{
				$table=$item=="member"?"members":$item;
				$sql="SELECT count(1) total FROM `" . TABLE_PREFIX . $table . "`";
				$query = $this->DatabaseHandler->Query($sql);
				$row=$query->GetRow();

				$sys_env["sys_{$item}s"] = $statistic[$item]=$row['total'];
			}

			cache_file('set', $cache_id, $statistic, 36000);
		} elseif (isset($statistic['sessions'])) {
			$sql="SELECT count(1) total FROM `" . TABLE_PREFIX . "sessions`";
			$query = $this->DatabaseHandler->Query($sql);
			$row=$query->GetRow();
				
			$statistic['sessions'] = $row['total'];
		}

				$cache_id = "misc/data_length";		
		if (false === ($data_length = cache_file('get', $cache_id))) {
			$sql="show table status from `{$this->Config['db_name']}` like '".TABLE_PREFIX."%'";
			$query=$this->DatabaseHandler->query($sql,"SKIP_ERROR");
			$data_length=0;
			while ($row=$query->GetRow())
			{
				$data_length+=$row['Data_length']+$row['Index_length'];
			}
			if($data_length>0)
			{
				
				$data_length=Load::lib('io', 1)->SizeConvert($data_length);
			}
			$sys_env['sys_data_length'] = $data_length;
				
			cache_file('set', $cache_id, $data_length, 36000);
		}
		
		


				

								$topic = DB::result_first("select count(*) as tcount from ".TABLE_PREFIX."topic_verify where managetype = 0 ");
			$data_verify['name'][0] = '微博';
			$data_verify['count'][0] = "<a href='admin.php?mod=topic&code=verify'>$topic</a>";
						$event = DB::result_first("select count(*) as tcount from ".TABLE_PREFIX."event where verify = 0 ");
			$data_verify['name'][1] = '活动';
			$data_verify['count'][1] = "<a href='admin.php?mod=event&code=verify'>$event</a>";
						$vote = DB::result_first("select count(*) as tcount from ".TABLE_PREFIX."vote where verify = 0 ");
			$data_verify['name'][2] = '投票';
			$data_verify['count'][2] = "<a href='admin.php?mod=vote&code=verify'>$vote</a>";
						$member = DB::result_first("select count(*) as tcount from ".TABLE_PREFIX."members_verify ");
			$data_verify['name'][3] = '头像签名';
			$data_verify['count'][3] = "<a href='admin.php?mod=verify'>$member</a>";
						$report = DB::result_first("select count(*) as tcount from ".TABLE_PREFIX."report ");
			$data_verify['name'][4] = '举报';
			$data_verify['count'][4] = "<a href='admin.php?mod=report'>$report</a>";
						$vip = DB::result_first("select count(*) as tcount from ".TABLE_PREFIX."validate_category_fields where is_audit = 0");
			$data_verify['name'][5] = 'V认证';
			$data_verify['count'][5] = "<a href='admin.php?mod=vipintro'>$vip</a>";
						$member5 = DB::result_first("select count(*) as tcount from ".TABLE_PREFIX."members where role_id = 5 ");
			$data_verify['name'][6] = '待审会员';
			$data_verify['count'][6] = "<a href='admin.php?mod=member&code=waitvalidate'>$member5</a>";
						include($this->TemplateHandler->Template('admin/home'));
	}

	function recommend()
	{
		$cache_id = "misc/recommend_list";
		if(false == ($recommend_list = cache_file('get', $cache_id)))
		{
			@$recommend_list=request('recommend',array('f'=>'text'),$error);

			if(!$error && is_array($recommend_list) && count($recommend_list)) {
				cache_file('set', $cache_id, (array) $recommend_list, 864000);
			}
		}
		if (!$recommend_list || count($recommend_list) < 1 || is_string($recommend_list))
		{
			$recommend_list = $this->_recommendList();
		}
		if (time() < $recommend_list['overtime'])
		{
			echo $recommend_list['string'];
		}
		exit;
	}

	function upgrade_check()
	{
		$ckey = 'fcache/home.console.upgrade.check';
		$ctim = 86400*3;
		$last = cache_file('get', $ckey);
		$last && exit($last);
		$response = request('upgrade', array(), $error);
		upsCtrl()->RPSFailed($response) && exit('~');
		$version = is_array($response) ? $response['version'] : SYS_VERSION;
		$build = is_array($response) ? 'build '.$response['build'] : SYS_BUILD;
		if ($version == SYS_VERSION)
		{
			$alert = 'noups';
			cache_file('set', $ckey, $alert, $ctim);
			exit($alert);
		}
		$version == '' && exit('noups');
		$aver = '发现新版本：'.$version.' '.$build;
		cache_file('set', $ckey, $aver, $ctim);
		exit($aver);
	}

	function lrcmd_nt()
	{
		$lv = $this->Get['lv'];
		$ckey = 'fcache/home.console.lrcmd.nt';
		$ctim = 86400;
		$last = cache_file('get', $ckey);
		$last && exit($last);
		$response = request('lrcmd', array('lv'=>$lv), $error);
		$error && exit('false');
		$nt = $response['transfer'] ? $response['recommend'] : 'false';
		cache_file('set', $ckey, $nt, $ctim);
		exit($nt);
	}

	function Help()
	{
		$new=(int)$this->Get['new'];
		include($this->TemplateHandler->Template('admin/help'));
		exit;
	}

	function Theme()
	{
		include($this->TemplateHandler->Template('admin/theme'));
		exit;
	}

}

?>