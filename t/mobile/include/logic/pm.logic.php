<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename pm.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 48370252 911955595 1637 $
 *******************************************************************/




Load::logic('pm');

class MyPmLogic extends PmLogic
{
	function MyPmLogic(){parent::PmLogic();}
	function getPmList($folder='inbox',$page=array(),$uid=0)
	{
		$info = parent::getPmList($folder, $page, $uid);
		if (!empty($info)) {
			$list = array();
			foreach ($info['pm_list'] as $key => $val) {
				$val['lastmessage'] = unserialize($val['lastmessage']);
				$val['message'] = $val['lastmessage']['message'];
				$val['date'] = my_date_format2($val['dateline']);
				$list[] = $val;
			}
			if (!empty($list)) {
				$ret = array(
					'pm_list' => $list,
					'current_page' => $info['page_arr']['current_page'],
					'total_page' => $info['page_arr']['total_page'],
					'list_count' => count($list),
				);
				return $ret;
			}
		}
		return false;
	}
	
	function getHistoryList($uid = MEMBER_ID,$touid = MEMBER_ID,$page=array(),$limit='')
	{
		
		$info = $this->getHistory($uid, $touid, $page, $limit);
		if (!empty($info)) {
			$list = array();
			foreach ($info['pm_list'] as $key => $val) {
				$val['date'] = my_date_format2($val['dateline']);
				$list[] = $val;
			}
			if (!empty($list)) {
				usort($list, create_function('$a,$b','if($a[dateline]==$b[dateline])return 0;return $a[dateline]<$b[dateline]?-1:1;'));
				$ret = array(
					'pm_list' => $list,
					'current_page' => $info['page_arr']['current_page'],
					'total_page' => $info['page_arr']['total_page'],
					'list_count' => count($list),
				);
				return $ret;
			}
		}
		return false;
	}
	
}


?>