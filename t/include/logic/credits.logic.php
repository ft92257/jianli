<?php
/**
 *
 * 用户积分操作逻辑类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: credits.logic.php 1343 2012-08-09 01:41:43Z wuliyong $
 */

 if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class CreditsLogic
{
	var $Config;
	var $DatabaseHandler;
	var $Action;

	function CreditsLogic()
	{
		$this->DatabaseHandler = &Obj::registry('DatabaseHandler');
		$this->Config = ConfigHandler::get();

		if($this->Config['extcredits_enable'])
		{
			if (!$this->Config['credits'])
			{
				$this->Config['credits'] = ConfigHandler::get('credits');
			}
			if (!$this->Config['credits_rule'])
			{
				$this->Config['credits_rule'] = ConfigHandler::get('credits_rule');
			}
		}
	}

	function ExecuteRule($action,$uid=0,$coef=1)
	{
		if (!$this->Config['extcredits_enable'])
		{
			return false;
		}

        $uid = max(0,(int) ($uid ? $uid : MEMBER_ID));
        if($uid < 1)
        {
            return false;
        }

        $this->Action = $action;
		$coef = (int) $coef;
		$update = 1;
		$update_credits = false;
		$timestamp = time();

		$rule = $this->GetRule($action);
		if (!$rule)
		{
			return false;
		}

		$enable = false;
		for ($i=1;$i<=8;$i++)
		{
			if ($rule['extcredits'.$i])
			{
				$enable = true;
				break;
			}
		}
		if (!$enable)
		{
			return false;
		}

		$rule_log = $this->GetRuleLog($rule['rid'],$uid);

		if($rule['rewardnum'] && $rule['rewardnum'] < $coef)
		{
			$coef = $rule['rewardnum'];
		}

		if (!$rule_log)
		{
			$log_arr = array
			(
				'uid'=>$uid,
				'rid'=>$rule['rid'],
				'total'=>$coef,
				'cyclenum'=>$coef,
				'dateline'=>$timestamp,
			);
			if (2==$rule['cycletype'] || 3==$rule['cycletype'])
			{
				$log_arr['starttime'] = $timestamp;
			}
			if ('_'==$rule['action']{0})
			{
                $_relatedid = substr($rule['action'],2);
                if($_relatedid && is_numeric($_relatedid))
                {
                    $log_arr['relatedid'] = $_relatedid;
                }
			}
			$log_arr = $this->AddLogArr($log_arr,$rule,$coef);
			if ($update)
			{
				$sql = "replace into ".TABLE_PREFIX."credits_rule_log (`".implode("`,`",array_keys($log_arr))."`) values ('".implode("','",$log_arr)."')";
				$this->DatabaseHandler->Query($sql);
				$clid = $this->DatabaseHandler->Insert_ID();
			}

			$update_credits = true;
		}
		else
		{
			$new_cycle = false;
			$log_arr = array();

			$log_arr['dateline'] = "dateline=$timestamp";
			if ($rule['related'] && $rule_log['relatedid'] && ('_'==$rule['action']{0}) && ($_relatedid = substr($rule['action'],2)) && is_numeric($_relatedid) && $_relatedid!=$rule_log['relatedid'])
			{
				$rule_log['cyclenum'] = 0;
				$log_arr['relatedid'] = "`relatedid`='".$_relatedid."'";
			}

			switch ($rule['cycletype'])
			{
				case 0:
					{
						if (!$rule_log['cyclenum'])
						{
							$log_arr['cyclenum'] = "cyclenum=$coef";
							$log_arr['total'] = "total=$coef";

							$update_credits = true;
						}
					}
					break;
				case 1:
				case 4:
					{
						if (1==$rule['cycletype'])
						{
							$today = strtotime(date('Y-m-d',$timestamp));
							if ($rule_log['dateline']<$today && $rule['rewardnum'])
							{
								$rule_log['cyclenum'] = 0;
								$new_cycle = true;
							}
						}
						if (!$rule['rewardnum'] || $rule_log['cyclenum']<$rule['rewardnum'])
						{
							if($rule['rewardnum'])
							{
								$remain = $rule['rewardnum'] - $rule_log['cyclenum'];
								if($remain < $coef)
								{
									$coef = $remain;
								}
							}
							$cyclen_num = ($new_cycle ? $coef : 'cyclenum+'.$coef);
							$log_arr['cyclenum'] = "cyclenum=$cyclen_num";
							$log_arr['total'] = 'total=total+'.$coef;
							$update_credits = true;
						}
					}
					break;
				case 2:
				case 3:
					{
						$next_cycle = 0;
						if($rule_log['starttime'])
						{
							if($rule['cycletype'] == 2)
							{
								$start = strtotime(date('Y-m-d H:00:00',$rule_log['starttime']));
								$next_cycle = $start+$rule['cycletime']*3600;
							}
							else
							{
								$next_cycle = $rule_log['starttime']+$rule['cycletime']*60;
							}
						}
						if($timestamp <= $next_cycle && $rule_log['cyclenum'] < $rule['rewardnum'])
						{
							if($rule['rewardnum'])
							{
								$remain = $rule['rewardnum'] - $rule_log['cyclenum'];
								if($remain < $coef)
								{
									$coef = $remain;
								}
							}
							$cycle_num = 'cyclenum+'.$coef;
							$log_arr['cyclenum'] = "cyclenum=cyclenum+$cycle_num";
							$log_arr['total'] = 'total=total+'.$coef;
							$update_credits = true;
						}
						elseif($timestamp >= $next_cycle)
						{
							$new_cycle = true;
							$log_arr['cyclenum'] = "cyclenum=$coef";
							$log_arr['total'] = 'total=total+'.$coef;
							$log_arr['starttime'] = "starttime='$timestamp'";
							$update_credits = true;
						}
					}
					break;
				default:
					break;
			}

			if($update && $log_arr)
			{
				$log_arr = $this->AddLogArr($log_arr, $rule, $coef, true);
				$this->DatabaseHandler->Query("UPDATE ".TABLE_PREFIX."credits_rule_log SET ".implode(',', $log_arr)." WHERE clid='{$rule_log['clid']}'");
			}
		}


        $_js_show_msg = false;
		if($update && $update_credits)
		{
			$_js_show_msg = $this->UpdateCreditsByRule($rule, $uid, $coef);
		}

                if($uid==MEMBER_ID && !$_js_show_msg && 'login'!=$action)
        {
            $this->_jsg_show_msg();
        }

		$rule['updatecredit'] = $update_credits;

		return $rule;
	}

	function GetRule($action)
	{
		$rule = false;
		if (is_array($this->Config['credits_rule']))
		{
			$rule = $this->Config['credits_rule'][$action];

			if($rule)
			{
				for ($i=1;$i<=8;$i++)
				{
					$k = 'extcredits'.$i;
                    if(isset($rule[$k]))
                    {
                        $rule[$k] = (int) $rule[$k];
    					if (!($rule[$k]) || !($this->Config['credits']['ext'][$k]['enable']))
    					{
    						unset($rule[$k]);
    					}
                    }
				}
			}
		}

		return $rule;
	}

	function GetRuleLog($rid,$uid=0)
	{
		$rid = max(0,(int) $rid);
		$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));

		$rule_log = array();
		if($rid && $uid)
		{
			$sql = "select * from ".TABLE_PREFIX."credits_rule_log where `rid`='$rid' and `uid`='$uid'";
			$query = $this->DatabaseHandler->Query($sql);
			$rule_log = $query->GetRow();
		}
		return $rule_log;
	}

	function AddLogArr($log_arr,$rule,$coef=1,$is_sql=0)
	{
		foreach ($this->Config['credits']['ext'] as $k=>$v)
		{
			$_v = (int) $rule[$k] * $coef;
			if ($v['enable'] && $_v)
			{
				if ($is_sql)
				{
					$log_arr[$k] = "`$k`='$_v'";
				}
				else
				{
					$log_arr[$k] = $_v;
				}
			}
		}

		return $log_arr;
	}

	function UpdateCreditsByRule($rule,$uids=0,$coef=1)
	{
        $return = false;

		$coef = (int) $coef;
		$uids = $uids ? $uids : intval(MEMBER_ID);
		$rule = is_array($rule) ? $rule : $this->GetRule($rule);
		$credit_arr = array();
		$update_credits = false;
		for($i = 1; $i <= 8; $i++)
		{
			$k = 'extcredits'.$i;
			$rule[$k] = intval($rule[$k]);
			if($this->Config['credits']['ext'][$k]['enable'] && $rule[$k])
			{
				$credit_arr[$k] = $rule[$k] * $coef;
				$update_credits = true;
			}
		}

		if($update_credits)
		{
			$return = $this->UpdateMemberCount($credit_arr, $uids);
		}

        return $return;
	}

	function UpdateMemberCount($credit_arr, $uids = 0)
	{
        $return = false;

		if(!$uids) $uids = intval(MEMBER_ID);
		$uids = is_array($uids) ? $uids : array($uids);
		if($uids && $credit_arr)
		{
            $js_show_msgs = array();
            $js_show_msg = (defined('MEMBER_ID') && MEMBER_ID>0 && MEMBER_ID==$uids[0]);

			$sql = array();
			$allow_keys = array('extcredits1'=>1, 'extcredits2'=>1, 'extcredits3'=>1, 'extcredits4'=>1, 'extcredits5'=>1, 'extcredits6'=>1, 'extcredits7'=>1, 'extcredits8'=>1, 'friends'=>1, 'views'=>1,);
			foreach($credit_arr as $key => $value)
			{
				if($key && isset($allow_keys[$key]))
				{
					$value = intval($value);
					$sql[] = " $key=if($key+'$value'>0, $key+'$value', 0) ";

                    if($js_show_msg && isset($this->Config['credits']['ext'][$key]))
                    {
                        $js_show_msgs[] = "{$this->Config['credits']['ext'][$key]['name']} ".($value>0?"+":"")." {$value}";
                    }
				}
			}

			if($sql)
			{
				$this->DatabaseHandler->Query("UPDATE ".TABLE_PREFIX."members SET ".implode(',', $sql)." WHERE uid IN ('".implode("','",$uids)."')");

				if(1==count($uids))
				{
					$this->CountCredits($uids[0],true);
				}

                if($js_show_msg && $js_show_msgs)
                {
					$return = $this->_jsg_show_msg($js_show_msgs);
                }
			}
		}

        return $return;
	}

	function CountCredits($uid,$update=true)
	{
		$credits = 0;
		if($uid && $this->Config['credits']['formula']) {
			$member = jsg_member_info($uid, 'uid', '*', 0);
			if ($member) {
				eval("\$credits = round(".$this->Config['credits']['formula'].");");
				if($update && $member['credits'] != $credits) {
					$credits = max(0, (int) $credits);
					if($credits >= 2147480000) {
						$credits = 0;
					}
					$this->DatabaseHandler->Query("update ".TABLE_PREFIX."members set `credits`='{$credits}' where `uid`='{$uid}'");

					$this->UpdateRole($uid);
				}
			}
		}
		return $credits;
	}

	
	function UpdateRole($uid)
	{
		$ret = 0;

		$member_info = jsg_member_info($uid);
		if(!$member_info) return 0;

		$credits = (int) $member_info['credits'];

		$role_id = max(0, (int) $member_info['role_id']);

		if('normal' == $member_info['role_type'])
		{
			$role_c = DB::fetch_first("select * from ".DB::table('role')." where `id`='$role_id'");
		}
		else
		{
			$role_c = DB::fetch_first("select * from ".DB::table('role')." where `rank`='{$member_info['level']}' and `type`='normal' order by `creditshigher` desc, `creditslower` desc, `rank` desc limit 1");
		}

		$role_c_max = $role_c['creditshigher'];
		$role_c_min = $role_c['creditslower'];
		$role_level = $role_c['rank'];

		$role_n = array();

				if($role_c_max && $credits < $role_c_max)
		{
			$role_n = DB::fetch_first("select * from ".DB::table('role')." where `creditshigher`<'$role_c_max' and `type`='normal' order by `creditshigher` desc, `creditslower` desc, `rank` desc limit 1");
		}
				elseif($role_c_min && $credits > $role_c_min)
		{
			$role_n = DB::fetch_first("select * from ".DB::table('role')." where `creditshigher`>='$role_c_min' and `type`='normal' order by `creditshigher` asc, `creditslower` asc, `rank` asc limit 1");
		}

		$role_n_id = $role_n['id'];
		if($role_n && $role_n_id>0)
		{
			$role_level = $role_n['rank'];

						if('normal'==$member_info['role_type'] && $role_n_id!=$member_info['role_id'])
			{
				$ret = DB::query("update ".DB::table('members')." set `role_id`='$role_n_id' where `uid`='$uid'");
			}
		}

				if($role_level != $member_info['level'])
		{
			$ret = DB::query("update ".DB::table('members')." set `level`='$role_level' where `uid`='$uid'");
		}

		return $ret;
	}

    function _jsg_show_msg($js_show_msgs=array())
    {
        $return = false;

        if(!$js_show_msgs)
        {
            $js_show_msgs = array('成功');
        }

        if($this->Config['credits_rule'][$this->Action]['rulename']) {
            $GLOBALS['js_show_msg'] = $GLOBALS['js_show_msg'] . ' ' . (('topic'==$this->Action || 'reply'==$this->Action ? ('发布' . (array('成功')==$js_show_msgs ? '' : '成功')) : $this->Config['credits_rule'][$this->Action]['rulename']) . " <strong>".($this->Config['credits_rule'][$this->Action]['related'])."</strong> " . implode(" \t ",$js_show_msgs));

            $un_setcookie_mods = array('output'=>1, 'xwb'=>1, 'qqwb'=>1, 'share'=>1, );
            if(!isset($un_setcookie_mods[get_param('mod')]) && !$GLOBALS['disable_show_msg']) {
            	jsg_setcookie('js_show_msg', $GLOBALS['js_show_msg'], 3600);
            }

            $return = true;
        }

        return $return;
    }
}

?>