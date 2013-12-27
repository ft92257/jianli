<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename tools.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-06 20:53:39 251517955 593825177 7622 $
 *******************************************************************/




if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $Member;
	
	var $ID = '';
	
	var $TopicLogic;


	function ModuleObject($config)
	{
		$this->MasterObject($config);

		
		$this->TopicLogic = Load::logic('topic', 1);
		
		$this->ID = (int) ($this->Post['id'] ? $this->Post['id'] : $this->Get['id']);
		
		$this->Member = $this->_member();
		
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();		
		switch ($this->Code) {
			case 'do_notice':
				$this->DoNotice();
				break;	
			case 'user_share':
				$this->DoUserShare();
				break;	
			case 'invite_by_email':
				$this->InviteByEmail();
				break;
            case 'qqrobot':
                $this->Messager(null,"index.php?mod=tools&code=imjiqiren");
                break;
				
			default:
				$this->Main();
		}				
		$body=ob_get_clean();
		
		$this->ShowBody($body);
	}

	function Main()
	{
		$act_list = array('share'=>'分享到微博','qmd'=>'签名档',);
		
		$act_list['show'] = array('name'=>'微博秀','link_mod'=>'show','link_code'=>'show',);    
        
        if($this->Config['imjiqiren_enable']) {
        	$act_list['imjiqiren'] = 'QQ机器人';        
        }
        if('qqrobot'==$this->Code && !isset($act_list['qqrobot']) && isset($act_list['imjiqiren'])) {
            $this->Code = 'imjiqiren';
        }
        $act_list['medal'] = array('name'=>'勋章','link_mod'=>'other','link_code'=>'medal',);  
        $act_list['sms'] = '短信';       
		$act = isset($act_list[$this->Code]) ? $this->Code : 'share';


		$member = $this->Member;
		if (!$member) {
				$this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",'index.php?mod=login');
		}
		
				if ($member['medal_id']) {
			$medal_list = $this->TopicLogic->GetMedal($member['medal_id'],$member['uid']);
		}
		$member_nickname = $member['nickname'];

		Load::lib('form');
		$FormHandler = new FormHandler();


		
		if ('qqrobot' == $act)
		{
			if( empty($member['qq'])) {
				$qq_code = $member['uid']."j".md5($member['password'].$member['username']);
			}

		}
        
	   elseif ('qmd' == $act) 
	   {
	    	$uid = MEMBER_ID;
			$image_path = $this->Config['site_url'];
			
	        if($this->Config['is_qmd'])
	        {

	    		if($member['qmd_url'])
	    		{
	    			$image_file =  $image_path .'/'. $member['qmd_url'];  
	    			
	    		} else {
	    		
	    			$image_file = $image_path.'/images/qmd_error.gif';
	    		}

	        } 

		}
		    
		
		elseif ('extcredits'==$act)
		{
			if (!$this->Config['extcredits_enable'])
			{
				$this->Messager("积分功能未启用",null);
			}

			$credits_config = $this->Config['credits'];

			$_default_credits = array();
			$_enable = false;
			if(is_array($credits_config) && count($credits_config))
			{
				foreach ($credits_config['ext'] as $_k=>$_v)
				{
					if ($_v['enable'])
					{
						$_enable = true;

						if ($_v['default'])
						{
							$_default_credits[$_k]=$_v['default'];
						}
					}
				}
			}
			if (!$_enable)
			{
				$this->Messager("积分未设置",null);
			}

			$op = $this->Get['op'];
			$op_lists = array(
				'base'=>'我的积分',
				'log'=>'积分记录',
				'rule'=>'积分规则',
			);
			$op = (isset($op_lists[$op]) ? $op : 'base');


			if ('base'==$op)
			{
				$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."members where `uid`='".MEMBER_ID."'");

				$member = $query->GetRow();

				$_search = $_replace = array();
				for ($i=1;$i<=8;$i++)
				{
					$k = 'extcredits'.$i;
					$_search[$k] = '$member['.$k.']';
					$_replace[$k] = ' 0 ';
					if (isset($credits_config['ext'][$k]) && $credits_config['ext'][$k]['enable'])
					{
						$_replace[$k] = $credits_config['ext'][$k]['name'];
					}
				}
				$_search['topic_count'] = '$member[topic_count]';
				$_replace['topic_count'] = '发微博数量';

				$credits_config_formula = str_replace($_search,$_replace,$credits_config['formula']);

				;
			}
			elseif('log'==$op)
			{
				$query = $this->DatabaseHandler->Query("select R.rulename,RL.* from ".TABLE_PREFIX."credits_rule_log RL left join ".TABLE_PREFIX."credits_rule R on R.rid=RL.rid where RL.`uid`='".MEMBER_ID."'");
				$log_list = array();
				while ($row=$query->GetRow())
				{
					$log_list[] = $row;
				}

				if ($_default_credits)
				{
					$log_list['default_credits'] = $_default_credits;
					$log_list['default_credits']['rulename'] = '注册时的初始积分';
					$log_list['default_credits']['total'] = $log_list['default_credits']['cyclenum'] = 1;
				}

				$_counts = array();
				foreach ($log_list as $k=>$row)
				{
					$row['dateline'] = ($row['dateline'] ? my_date_format($row['dateline'],'m-d H:i') : ' - ');

					foreach ($credits_config['ext'] as $_k=>$_v)
					{
						$row[$_k] = $row[$_k] * $row['total'];

						$_counts[$_k] += $row[$_k];
					}

					$log_list[$k] = $row;
				}
			}
			elseif ('rule'==$op)
			{
				if(!($credits_rule = ConfigHandler::get('credits_rule')))
				{
					$sql = "select * from ".TABLE_PREFIX."credits_rule order by rid";
					$query = $this->DatabaseHandler->Query($sql);
					$credits_rule = array();
					while (false != ($row = $query->GetRow()))
					{
						$v = false;
						foreach ($credits_config['ext'] as $_k=>$_v)
						{
							if ($row[$_k])
							{
								$v = true;
								break;
							}
						}

						if($v)
						{
							foreach ($row as $k=>$v)
							{
								if (!$v)
								{
									unset($row[$k]);
								}
							}

							$credits_rule[$row['action']] = $row;
						}
					}
				}

				$_cycletypes = array
				(
					0 => '一次性',
					1 => '每天',
					2 => '整点',
					3 => '间隔分钟',
					4 => '不限周期',
				);
				if ($_default_credits)
				{
					$credits_rule['default_credits'] = $_default_credits;
					$credits_rule['default_credits']['rulename'] = '注册时的初始积分';
					$credits_rule['default_credits']['cycletype'] = 0;
					$credits_rule['default_credits']['rewardnum'] = 1;
				}
				foreach ($credits_rule as $k=>$v)
				{
					$v['cycletype'] = $_cycletypes[(int) $v['cycletype']];
					if (!$v['rewardnum'])
					{
						$v['rewardnum'] = '不限次数';
					}

					$credits_rule[$k] = $v;
				}

				;
			}
		   	
			else
			{
				$this->Messager("未定义的操作");
			}
		}


		
		elseif ('imjiqiren' == $act)
		{
            define('IN_IMJIQIREN_MOD',      true);

            include(ROOT_PATH . 'modules/imjiqiren.mod.php');
		}
        
        elseif('sms' == $act)
        {
            define('IN_SMS_MOD',      true);

            include(ROOT_PATH . 'modules/sms.mod.php');
        }
		elseif ('email' == $act)
		{
            ;
		}
		elseif('share' == $act)
		{  
			$PostShareName = $this->Post['share_name'];
		    $shareName =  $PostShareName ? $PostShareName : $this->Config['site_name'];
		}

		$this->Title = $act_list[$act];
		include($this->TemplateHandler->Template('tools_main'));
	}
	
	
	function _member()
	{
		if (MEMBER_ID < 1) {
			$this->Messager(null,$this->Config['site_url'] . "/index.php?mod=login");
		}		
		
		$member = jsg_member_info(MEMBER_ID);
		
		return $member;
	}

	
}


?>
