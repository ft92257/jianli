<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename misc.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-08-17 19:12:46 878818403 94903839 2324 $
 *******************************************************************/



 
 if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $MiscLogic;
	var $Config;
	
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		$this->Config = $config;
		
				
		Mobile::is_login();
		
		Mobile::logic('misc');
		$this->MiscLogic = new MiscLogic();
		
		$this->Execute();
	}

	
	function Execute()
	{
        ob_start();

		switch($this->Code)
		{
			case 'sign':
				$this->sign();
				break;
			case 'reminded':
				$this->reminded();
				break;
			case 'clear_reminded':
				$this->clearReminded();
				break;
			default:
				exit();
						}

        response_text(ob_get_clean());
	}
	
	function sign()
	{
				$sign_config = $this->Config['sign'];
		if ($sign_config['sign_enable'] != 1) {
			Mobile::error('Not Turned', 407);
		}
		
		$tags = $this->MiscLogic->getSignTag();
		if (!empty($tags)) {
			Mobile::output($tags);
		}
		Mobile::error('No Data', 400);
	}
	
	function reminded()
	{
		$my = jsg_member_info(MEMBER_ID);
        if(!$my) {
        	Mobile::error("No User", 300);
        }
        
        $ret = array(
        	'at_count' => $my['at_new'],
        	'comment_count' => $my['comment_new'],
        	'pm_count' => $my['newpm'],
        	'total' => (string)($my['at_new'] + $my['comment_new'] + $my['newpm']),
        );
        Mobile::output($ret);
	}
	
	function clearReminded() 
	{
		$ops = array('at', 'comment', 'pm');
		$op = $this->Get['op'];
		if (!in_array($op, $ops)) {
			Mobile::error("Error op", 402);
		}
		$f = "";
		switch ($op) {
			case 'at':
				$f = 'at_new';
				break;
			case 'comment':
				$f = 'comment_new';
				break;
			case 'pm':
				$f = 'newpm';
				break;
		}
		if (!empty($f)) {
			$uid = MEMBER_ID;
			DB::query("update `".TABLE_PREFIX."members` set `{$f}`='0' where `uid`='$uid'");
			Mobile::success();
		}
		Mobile::error("Error op", 402);
	}
}

?>
