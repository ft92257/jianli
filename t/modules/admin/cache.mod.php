<?php

/**
 * 缓存管理
 *
 * @author 狐狸<foxis@qq.com>
 * @package JishiGou
 * @version $Id: cache.mod.php 1374 2012-08-15 07:07:59Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
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
		switch($this->Code) {
			case 'do_clean':
				$this->DoClean();
					
			default:
				$this->Code = '';
				$this->Main();
				break;
		}
		$body = ob_get_clean();

		$this->ShowBody($body);
	}

	function Main() {
		
		$this->_free_login_ip();
		$this->_fix_members();
		

		include template('admin/cache_index');
	}

	function DoClean() {
		$type = get_param('type');
		if(!$type) {
			$this->Messager("请先选择要清理的缓存对象");
		}		
		
				$this->_removeTopicImage();
		$this->_removeTopicAttach();
		$this->_removeTopicLongtext();
		
		if(in_array('data', $type)) {
						cache_db('clear');
		}

		if(in_array('tpl', $type)) {
						cache_clear();
			
						ConfigHandler::set('validate_category', array());
		}


		$this->Messager("已清空所有缓存，同时清理了用户上传但未使用的图片及附件");
	}

	function _removeTopicImage() {
				Load::logic('image', 1)->clear_invalid(300);
	}
	function _removeTopicAttach() {
				Load::logic('attach', 1)->clear_invalid(300);
	}
	function _removeTopicLongtext() {
		Load::logic('longtext', 1)->clear_invalid(300);
	}

	function _free_login_ip() {
		global $_J;

		$failedlogins = DB::fetch_first("SELECT count, lastupdate FROM ".TABLE_PREFIX.'failedlogins'." WHERE ip='{$_J['client_ip']}'");
		if($failedlogins) {
			DB::query("UPDATE ".TABLE_PREFIX.'failedlogins'." SET count='1', lastupdate='{$_J['timestamp']}' WHERE ip='{$_J['client_ip']}'");
		}
		DB::query("DELETE FROM ".TABLE_PREFIX.'failedlogins'." WHERE lastupdate<'".($_J['timestamp']-901)."'");
	}
	function _fix_members() {
		DB::query("update ".DB::table("members")." set `username`=`uid` where `username`=''");
		DB::query("update ".DB::table("members")." set `nickname`=`username` where `nickname`=''");
		DB::query("update ".DB::table("memberfields")." set `account_bind_info`=''");
		DB::query("REPLACE INTO ".TABLE_PREFIX."memberfields
            (`uid`)
SELECT
  M.uid
FROM ".TABLE_PREFIX."members M
  LEFT JOIN ".TABLE_PREFIX."memberfields MF
    ON MF.uid = M.uid
WHERE MF.uid IS NULL");
	}
	function _fix_settings() {
				;
	}

}
?>