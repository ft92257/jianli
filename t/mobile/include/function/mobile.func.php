<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename mobile.func.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-06-20 16:52:55 76012816 1298187945 3063 $
 *******************************************************************/




class Mobile
{
		function show_message($code, $data = array())
	{
		if (IN_JISHIGOU_MOBILE_AJAX === true) {
			if ($code == 410) {
				Mobile::error("No Login", 410);
			}
		} else {
			include(template('message'));
		}
		exit;
	}
	
		function is_login()
	{
		global $_J;
		
		if (MEMBER_ID > 0) {
			return true;
		} else {
			if (defined("IS_MOBILE_CLIENT") && IS_MOBILE_CLIENT == true) {
				Mobile::show_message(410);
			} else {
				echo "正在转到登录页面...";
				echo "<script>location.href='{$_J['mobile_url']}/index.php?mod=member&code=login'</script>";
				exit;
			}
		}
	}
	
		function convert($string)
	{
		global $_J;
		$charset = $_J['charset'];
		if (preg_match("/gbk/is", $charset)) {
			if (is_array($string)) {
				$string = array_iconv($charset, OUT_CHARSET, $string);
			} else {
				$string = iconv($charset, OUT_CHARSET, $string);
			}
		}
		return $string;
	}
	
	
	function output($result, $status = '', $code = 200, $convert = true)
	{
		$outputs = array();
		if($status) {
			$outputs['status'] = $status;
		}
		
	   	$outputs['code'] = $code;
	    
	    $outputs['result'] = $convert == true ? Mobile::convert($result) : $result;
		ob_clean();
		echo json_encode($outputs);
		exit;
	}
	
	
	function error($msg, $code=400, $halt=true)
	{
				Mobile::output($msg, 'Error', $code, false);
		$halt && exit;
	}
	
		function success($msg, $code=200, $halt=true)
	{
		Mobile::output($msg, 'Success', $code, false);
		$halt && exit;
	}
	
		function page($options)
	{
		$total_record =	$options['total_record'];
		$per_page_num = $options['per_page_num'];
		$url = isset($options['url']) ? $options['url'] : ''; 
		$_config = isset($options['_config']) ? $options['_config'] : array();
		$per_page_nums = isset($options['per_page_nums']) ? $options['per_page_nums'] : ''; 
		$info = page($total_record, $per_page_num, $url, $_config, $per_page_nums);
		return $info;
	}
	
	function functions($name)
	{
		return @include_once(ROOT_PATH . 'mobile/include/function/' .$name.'.func.php');
	}
	
	function logic($name)
	{
		return @include_once(ROOT_PATH . 'mobile/include/logic/' .$name.'.logic.php');
	}
	
	function lib($name)
	{
		return @include_once(ROOT_PATH . 'mobile/include/lib/' .$name.'.han.php');
	}
	
	function config($key = "", $type = "")
	{
		$config = array(
			'perpage_def' => 20,
			'perpage_mblog' => 20,
			'perpage_pm' => 20,
			'perpage_member' => 20,
		);
		if (empty($key)) {
			return $config;
		} else {
			if (isset($config[$key])) {
				return $config[$key];
			}
		}
		return "";
	}
}

?>