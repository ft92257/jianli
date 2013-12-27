<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename company.class.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 756873843 163042330 1456 $
 *******************************************************************/


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}
class plugin_company
{
	var $value = array();
	function plugin_company()
	{
		global $DB;
		$query = DB::query("SELECT C.* FROM ".DB::table('plugin_company')." AS C, ".DB::table('members')." AS M WHERE C.uid = M.uid AND M.username =  '".$_GET['mod_original']."'");
		$cominfo = DB::fetch($query);
		if($cominfo){
		$cominfo['ptime'] = my_date_format($cominfo['ptime']);
		$this->value['global_usernav_extra1'] = "<p>企业名称：".$cominfo['companyname']."</p>";
		$this->value['global_usernav_extra2'] = "<script type='text/javascript'>\$(document).ready(function(){".
		"\$('.SC_company').click(function(){\n\$(this).parent().toggleClass('fold_guanyu');\$('.SC_company_box').toggle();});".
		"});</script><div class='SC'><h3>".$cominfo['companyname'].
		"<a class='btn SC_company' href='javascript:void(0)'></a></h3><ul class='FTL SC_company_box'>".
		"<li><u>公司名称</u>：".$cominfo['companyname']."</li>".
		"<li><u>法人代表</u>：".$cominfo['ceoname']."</li>".
		"<li><u>联系电话</u>：".$cominfo['tel']."</li>".
		"<li><u>公司地址</u>：".$cominfo['address']."</li>".
		"<li><u>企业介绍</u>：".$cominfo['descripction']."</li>".
		"</ul></div>";
		}
		return '';
	}

	function global_usernav_extra1()
	{
		return $this->value['global_usernav_extra1'];
	}
	
	function global_usernav_extra2()
	{
		return $this->value['global_usernav_extra2'];
	}
}
?>