<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename more.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 17110058 1259568605 789 $
 *******************************************************************/




if (!defined('IN_JISHIGOU')) {
    exit('Access Denied');
}

class ModuleObject extends MasterObject
{
	var $CacheConfig;

	function ModuleObject($config)
	{
		$this->MasterObject($config);
		$this->CacheConfig = ConfigHandler::get('cache');
				Mobile::is_login();
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch ($this->Code) {
			case "about":
				$this->about();
				break;
			default:
				$this->main();
		}
		$body=ob_get_clean();
		echo $body;
	}
	
	function main()
	{
		include(template('more'));
	}
	
	function about()
	{
		include(template('about'));
	}
}
?>