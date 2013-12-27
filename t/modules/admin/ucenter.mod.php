<?php
/**
 * 文件名： ucenter.mod.php
 * 版本号： 1.0
 * 创建时间： 2008年9月19日 11时32分07秒
 * 修改时间：2009年3月21日 11时45分46秒
 * 作者： 狐狸<foxis@qq.com>
 * 功能描述： 整合Ucenter
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{

	
	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'do_setting':
				$this->DoSetting();
				break;

			case 'merge':
				$this->DoMerge();
				break;

            case 'face2uc':
                $this->Face2UC();
                break;
                
            case 'ucenter':
            	$this->Ucenter();
            	break;

			default:
				$this->Main();
				break;
		}
		$body = ob_get_clean();

		$this->ShowBody($body);
	}
	
	function Main() {
		$this->Messager('正在建设中……', null);
	}

	

	function Ucenter()
	{
		if(!is_file(ROOT_PATH . './api/uc_client/client.php')){
			$this->Messager('Ucenter的客户端文件 <b>' . ROOT_PATH . './api/uc_client/client.php' . "</b> 不存在，请检查",null);
		}
		if(!is_file(ROOT_PATH . './api/uc.php')){
			$this->Messager('Ucenter的api文件 <b>' . ROOT_PATH . './api/uc_client/client.php' . "</b> 不存在，请检查",null);
		}

		$ucenter = ConfigHandler::get('ucenter');

		ConfigHandler::update('ucenter_enable', ($ucenter['enable'] ? 1 : 0));

		Load::lib('form');
		$uc_enable_radio = FormHandler::YesNoRadio('ucenter[enable]',(bool) $ucenter['enable']);

		$uc_face_radio = FormHandler::YesNoRadio('ucenter[face]',(bool) $ucenter['face']);
		${"uc_connect_{$ucenter['uc_connect']}_checked"} = " checked ";

		$uc_modify_nickname_radio = FormHandler::YesNoRadio('ucenter[modify_nickname]', (bool) $ucenter['modify_nickname']);
		
		
		include $this->TemplateHandler->Template('admin/ucenter');
	}

	function DoSetting()
	{
		if(!is_file(ROOT_PATH . './api/uc_client/client.php')) {
			$this->Messager('Ucenter的客户端文件 <b>' . ROOT_PATH . './api/uc_client/client.php' . "</b> 不存在，请检查");
		}
		if(!is_file(ROOT_PATH . 'api/uc.php')) {
			$this->Messager('Ucenter的api文件 <b>' . ROOT_PATH . 'api/uc.php' . "</b> 不存在，请检查");
		}

		if (trim($_POST['uc_config_string'])) {
			$_uc_config = $this->_get_uc_config($_POST['uc_config_string']);
			if (!$_uc_config) {
				$this->Messager("通过字符串更新UC配置失败，请手工填写具体的配置信息",null);
			}
			
			$_POST['ucenter'] = array_merge($_POST['ucenter'], $_uc_config);
		}

		$_POST['ucenter']['uc_charset'] = $_POST['ucenter']['uc_db_charset'] = $this->Config['charset'];

		if(!$_POST['ucenter']['uc_key']) {
			$this->Messager("请填写Ucenter通信密钥，建议查看"."帮助文"."档</b>"."</a>");
		}

		if(!$_POST['ucenter']['uc_api']) {
			$this->Messager("请填写Ucenter地址，建议查看"."帮助文"."档</b>"."</a>");
		}

		if(!$_POST['ucenter']['uc_app_id']) {
			$this->Messager("请填写当前应用ID，建议查看"."帮助文"."档</b>"."</a>");
		}

		if('请输入Ucenter的数据库密码' == $_POST['ucenter']['uc_db_password']) {
			$_POST['ucenter']['uc_db_password'] = ConfigHandler::get('ucenter', 'uc_db_password');
		}

		if('mysql' == $_POST['ucenter']['uc_connect']) {
			$_POST['ucenter']['uc_db_name'] = "`".trim($_POST['ucenter']['uc_db_name'],'`')."`";
			$_POST['ucenter']['uc_db_table_prefix'] = $_POST['ucenter']['uc_db_name'] . '.' . (false !== ($_tmp_pos = strpos($_POST['ucenter']['uc_db_table_prefix'],'.')) ? substr($_POST['ucenter']['uc_db_table_prefix'],$_tmp_pos + 1) : $_POST['ucenter']['uc_db_table_prefix']);

			if((@$dl = mysql_connect($_POST['ucenter']['uc_db_host'],$_POST['ucenter']['uc_db_user'],$_POST['ucenter']['uc_db_password'])) && mysql_query("SHOW COLUMNS FROM {$_POST['ucenter']['uc_db_table_prefix']}members",$dl)) {
				;
			} else {
				$this->Messager("无法连接Ucenter数据库，请检查您填写的Ucenter数据库配置信息");
			}
		}
		$ucenter = $_POST['ucenter'];
		ConfigHandler::set('ucenter', $ucenter);

		if($ucenter['enable']) {
			if('mysql' == $ucenter['uc_connect']) {
				include_once(ROOT_PATH.'./api/uc_api_db.php');

				$uc_db = new JSG_UC_API_DB();
				@$uc_db->connect($ucenter['uc_db_host'],$ucenter['uc_db_user'],$ucenter['uc_db_password'],$ucenter['uc_db_name'],$ucenter['uc_db_charset'],0,$ucenter['uc_db_table_prefix']);

				if(!($uc_db->link) || !($uc_db->query("SHOW COLUMNS FROM {$ucenter['uc_db_table_prefix']}members",'SILENT'))) {
					$ucenter['enable'] = 0;
					ConfigHandler::set('ucenter', $ucenter);
					
					ConfigHandler::update('ucenter_enable', ($ucenter['enable'] ? 1 : 0));

					$this->Messager("无法连接Ucenter数据库，请检查您填写的Ucenter数据库配置信息是否正确.");
				} else {
					ConfigHandler::update('ucenter_enable', ($ucenter['enable'] ? 1 : 0));
					if($ucenter['enable']) {
						$this->Messager("Ucenter配置保存成功,如果您已经对数据库进行过备份了,<a href='admin.php?mod=ucenter&code=merge&confirm=1'><b>请点此进行用户数据整合</b></a>",null);
					}
				}
			}
		}
		ConfigHandler::update('ucenter_enable', ($ucenter['enable'] ? 1 : 0));
		

		$this->Messager("配置成功",'admin.php?mod=ucenter&code=ucenter');
	}

	function DoMerge()
	{
		$start = max(0,(int) $this->Get['start']);
		$limit = 500;

		$ucenter = ConfigHandler::get('ucenter');

		if(!$ucenter['enable'] || !$this->Get['confirm'] || 'mysql' != $ucenter['uc_connect']) {
			$this->Messager("你的配置不正确，或者已经进行过用户数据整合了", 'admin.php?mod=ucenter&code=ucenter');
		}

		include_once(ROOT_PATH.'./api/uc_api_db.php');

		$db = new JSG_UC_API_DB();
		$db->connect($this->Config['db_host'],$this->Config['db_user'],$this->Config['db_pass'],$this->Config['db_name'],$this->Config['charset'],$this->Config['db_persist'],$this->Config['db_table_prefix']);
        if(!$start) {
        	        	if(get_param('new_merge')) {
        		$db->query("update ".TABLE_PREFIX."members set `ucuid`=0 where `ucuid`!=0");
        	} else {
        		$db->query("update ".TABLE_PREFIX."members set `ucuid`=0 where `ucuid`<0");
        	}
        }
		$query = $db->query("select * from ".TABLE_PREFIX."members where ucuid=0 limit {$limit}");
		if($db->num_rows($query) < 1) {
			$this->Messager("用户数据合并成功", 'admin.php?mod=ucenter&code=ucenter');
		}

		$uc_db = new JSG_UC_API_DB();
		$uc_db->connect($ucenter['uc_db_host'],$ucenter['uc_db_user'],$ucenter['uc_db_password'],$ucenter['uc_db_name'],$ucenter['uc_db_charset'],0,$ucenter['uc_db_table_prefix']);
		while (false != ($data = $db->fetch_array($query))) {
			$ucuid = -1;
			$db->query("update `".TABLE_PREFIX."members` set `ucuid`='{$ucuid}' where `uid`='{$data['uid']}'"); 			
			$salt = rand(100000, 999999);
			$password = md5($data['password'].$salt);
			$data['username'] = addslashes($data['username']);
			$data['nickname'] = addslashes($data['nickname']);
		
						$uc_user = $uc_db->fetch_first("SELECT * FROM {$ucenter['uc_db_table_prefix']}members WHERE username='{$data['nickname']}'"); 
			
			
													
				
				
			
			
						if(!$uc_user) {
				$uc_db->query("INSERT LOW_PRIORITY INTO {$ucenter['uc_db_table_prefix']}members SET username='{$data['nickname']}', `password`='$password',email='$data[email]', regip='$data[regip]', regdate='$data[regdate]', salt='$salt'", 'SILENT');
				$ucuid = $uc_db->insert_id();
				$uc_db->query("INSERT LOW_PRIORITY INTO {$ucenter['uc_db_table_prefix']}memberfields SET uid='$ucuid'",'SILENT');
			} else {
				$ucuid = $uc_user['uid'];
				
				
			}

			$db->query("update `".TABLE_PREFIX."members` set `ucuid`='{$ucuid}' where `uid`='{$data['uid']}'");
		}
		
		$db->close();
		$uc_db->close();

		$next = ($start + $limit);
		$this->Messager("[{$start}-{$next}]正在进行用户数据的合并中，请稍候……",'admin.php?mod=ucenter&code=merge&confirm=1&start='.$next);
	}

	function _get_uc_config($str)
	{
		$str = trim(stripslashes($str));

		$ms = false;
		$uc_config = array();

		preg_match_all('~define\s*\(\s*[\'\"](UC\_\w+?)[\'\"]\s*\,\s*[\'\"]([^\'\"]+?)[\'\"]\s*\)\;~i',$str,$ms,2);
		if (is_array($ms) && count($ms))
		{
			foreach ($ms as $k=>$v)
			{
				$uc_config[strtolower($v[1])] = $v[2];
			}

			$uc_config['uc_db_host'] = $uc_config['uc_dbhost'];
			$uc_config['uc_db_user'] = $uc_config['uc_dbuser'];
			$uc_config['uc_db_password'] = $uc_config['uc_dbpw'];
			$uc_config['uc_db_name'] = $uc_config['uc_dbname'];
			$uc_config['uc_db_charset'] = $uc_config['uc_dbcharset'];
			$uc_config['uc_db_table_prefix'] = $uc_config['uc_dbtablepre'];
			$uc_config['uc_app_id'] = $uc_config['uc_appid'];
		}
		return $uc_config;
	}


    function Face2UC()
    {
        if('admin'!=MEMBER_ROLE_TYPE)
        {
            exit;
        }

        if(true!==UCENTER)
        {
            $this->Messager("请先开启UC功能",null);
        }

        if(true===UCENTER_FACE)
        {
            $this->Messager("请先关闭UC头像功能",null);
        }

        $uc_root = $this->Get['uc_root'];
        if(false===strpos($uc_root,'/data/avatar/'))
        {
            $uc_root = $uc_root . '/data/avatar/';
        }
        if(!is_dir($uc_root))
        {
            $this->Messager("请先设置正确的UC服务端绝对路径地址",null);
        }


        $uid_min = max(0,(int) $this->Get['uid_min']);
        $uid_max = $uid_min + 200;

        $sql = "select `uid`,`username`,`face_url`,`face`,`ucuid` from ".TABLE_PREFIX."members where `ucuid`>'0' and `uid`>='$uid_min' and `uid`<'$uid_max' order by `uid` asc";
        $query = $this->DatabaseHandler->Query($sql);
        if($query->GetNumRows() < 1)
        {
            $this->Messager("已经同步完成",null);
        }

        while(false != ($row = $query->GetRow()))
        {
            $row['ucuid'] = (int) $row['ucuid'];
            if($row['ucuid'] < 1)
            {
                continue ;
            }

            $face_s = face_get($row);
            $face_b = face_get($row, 'big');

            $uc_face_s = $uc_root . $this->uc_get_avatar($row['ucuid'],'small');
            $uc_face_m = $uc_root . $this->uc_get_avatar($row['ucuid'],'middle');
            $uc_face_b = $uc_root . $this->uc_get_avatar($row['ucuid'],'big');

            if($face_s!=$face_b)
            {
                if(!is_dir(dirname($uc_face_s)))
                {
                    Load::lib('io', 1)->MakeDir(dirname($uc_face_s));
                }

                if(is_image($face_s) && !is_file($uc_face_s))
                {
                    copy($face_s,$uc_face_s);
                }
                if(is_image($face_b))
                {
                    if(!is_file($uc_face_m))
                    {
                        copy($face_b,$uc_face_m);
                    }
                    if(!is_file($uc_face_b))
                    {
                        copy($face_b,$uc_face_b);
                    }
                }
            }
        }


        $this->Messager("正在更新UC头像，请稍候……","admin.php?mod=ucenter&code=face2uc&uc_root=".urlencode($uc_root)."&uid_min=$uid_max");
    }
    function uc_get_avatar($uid, $size = 'middle', $type = '')
    {
    	$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';
    	$uid = abs(intval($uid));
    	$uid = sprintf("%09d", $uid);
    	$dir1 = substr($uid, 0, 3);
    	$dir2 = substr($uid, 3, 2);
    	$dir3 = substr($uid, 5, 2);
    	$typeadd = ($type == 'real' ? '_real' : '');
    	return $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_$size.jpg";
    }
}

?>