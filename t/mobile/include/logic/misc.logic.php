<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename misc.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 734864297 422047363 449 $
 *******************************************************************/



 
class MiscLogic
{
	var $Config;
	var $DatabaseHandler;
	var $OtherLogic;
	
	function MiscLogic()
	{
		$this->Config = ConfigHandler::get();
		$this->DatabaseHandler = &Obj::registry('DatabaseHandler');
		Load::logic('other');
		$this->OtherLogic = new OtherLogic();
	}
	
	function getSignTag()
	{
		$tags = $this->OtherLogic->getSignTag();
		return $tags;
	}
}
?>
