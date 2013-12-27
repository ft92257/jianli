<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename channel.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-08-17 19:12:46 234729041 258276987 3132 $
 *******************************************************************/




if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $TopicLogic;
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		Load::logic('channel');
		$this->ChannelLogic = new ChannelLogic($this);		
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{	
			case 'docategory':
				$this->docategory();
				break;
			case 'delcat':
				$this->delcat();
				break;
			default:
				$this->category();
				break;
		}
		$body = ob_get_clean();
		
		$this->ShowBody($body);
		
	}
	
	
	function category()
	{
		if(SYS_VERSION < 4){$this->Messager("网站暂未开启该功能，到4.0以上版本才开启！",null);}		$tree = $this->ChannelLogic->get_category_tree();
		include template('admin/channel');
	}

	function docategory()
	{
				$cat_ary = &$this->Post['cat'];
		if (!empty($cat_ary)) {
			$cat_order_ary = &$this->Post['cat_order'];
			foreach ($cat_ary as $key => $cat) {
				$ch_name = getstr($cat, 30, 1, 1);
								$display_order = intval($cat_order_ary[$key]);
				$this->ChannelLogic->update_category($key, $ch_name, $display_order);
			}
		}
		
				$tcat_ary = &$this->Post['new_tcat'];
		if (!empty($tcat_ary)) {
			$tcat_order_ary = &$this->Post['new_tcat_order'];
			$this->_batch_add_category($tcat_ary, $tcat_order_ary);
		}
		
				$scat_ary = &$this->Post['new_scat'];
		if (!empty($scat_ary)) {
			$scat_order = &$this->Post['new_scat_order'];
			foreach ($scat_ary as $p => $cats) {
				$this->_batch_add_category($cats, $scat_order[$p], $p);
			}
		}
		
				$this->ChannelLogic->update_category_cache();
		$this->Messager('操作成功了');
	}
	
	
	function _batch_add_category($cat_ary, $order_ary, $parent_id = 0)
	{
		foreach ($cat_ary as $key => $cat) {
						$ch_name = getstr($cat, 30, 1, 1);
			if (empty($ch_name) || $this->ChannelLogic->category_exists($ch_name, $parent_id)) {
				continue;
			}
			$display_order = intval($order_ary[$key]);
			$this->ChannelLogic->add_category($ch_name, $display_order, $parent_id);
		}
	}
	
	
	function delcat()
	{
		$ch_id = empty($this->Get['ch_id']) ? 0 : intval($this->Get['ch_id']);
		if (empty($ch_id)) {
			$this->Messager('没有指定频道ID');
		}
		
		$ret = $this->ChannelLogic->delete_category($ch_id);
		
				$this->ChannelLogic->update_category_cache();
		
		if ($ret == 1) {
			$this->Messager('删除频道成功');
		} else if ($ret == -1) {
			$this->Messager('当前频道不存在');
		} else if ($ret == -2) {
			$this->Messager('当前频道下面存在微博，不能被删除');
		} else if ($ret == -3) {
			$this->Messager('下级频道不为空，请先返回删除本频道或频道的所属微博');
		}
	}
}
?>
