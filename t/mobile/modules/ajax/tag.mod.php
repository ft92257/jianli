<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename tag.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 280953056 536218813 1944 $
 *******************************************************************/




if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $TopicLogic;
	var $MTagLogic;
	
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		Load::logic('topic');
		$this->TopicLogic = new TopicLogic($this);
		Mobile::logic('tag');
		$this->MTagLogic = new MTagLogic(); 
		
				Mobile::is_login();
		
		$this->Execute();
	}

	
	function Execute()
	{
        ob_start();

		switch($this->Code)
		{
			case 'list':
				$this->getTagList();
				break;
			case 'add_favorite':
				$this->favorite('add');
				break;
			case 'del_favorite':
				$this->favorite('delete');
				break;
			case 'check':
				$this->check();
				break;
		}

        response_text(ob_get_clean());
	}
	
		function getTagList()
	{
		$uid = intval($this->Get['uid']);
		$max_id = intval($this->Get['max_id']);
		$param = array(
			'limit' => Mobile::config("perpage_def"),
			'uid' => $uid,
			'max_id' => $max_id,
		);
		$ret = $this->MTagLogic->getTagList($param);
		if (is_array($ret)) {
			Mobile::output($ret);
		} else {
			Mobile::error("No Error Tips", $ret);
		}
	}
	
		function favorite($op = 'add')
	{
		$param = array(
			'op' => $op,
			'tag' => $this->Post['tag'],
		);
		$ret = $this->MTagLogic->favorite($param);
		if ($ret == 200) {
			Mobile::success("Success");
		} else {
			Mobile::error("Has a Error", $ret);
		}
	}
	
		function check()
	{
		$uid = MEMBER_ID;
		$tag = $this->Post['tag'];
		$ret = $this->MTagLogic->checkFavorite($uid, $tag);
		if ($ret) {
			Mobile::success("Success");
		} else {
			Mobile::error("Has a Error", 400);
		}
	}
	
}

?>
