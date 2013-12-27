<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename cookie.han.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 1497558501 1370596913 1625 $
 *******************************************************************/


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class CookieHandler
{

   
	var $_config;

   
	var $_cookie;

   
	var $_prefix;

   
	var $_path;

   
	var $_domain;

	function CookieHandler(& $config, & $cookie)
	{
		$this->_config =& $config;
		$this->_cookie =& $cookie;

		$this->_prefix = $this->_config['cookie_prefix'];
		$this->_path   = $this->_config['cookie_path']   ? $this->_config['cookie_path']   : '/';
		$this->_domain = $this->_config['cookie_domain'] ? $this->_config['cookie_domain'] : '';
	}

	function SetVar($name, $value, $time = false)
	{
		$expire = 0;

        if($time)
        {
            $expire = time() + $time;
        }

		@setcookie($this->_prefix . $name, $value, $expire, $this->_path, $this->_domain);
		return true;
	}

	function GetVar($key)
	{
		if(isset($this->_cookie[$this->_prefix . $key]))
        {
			return rawurldecode($this->_cookie[$this->_prefix . $key]);
        }
        else {
			return false;
        }
	}
	
	function DeleteVar($name)
	{
		$name_list=func_get_args();
		foreach ($name_list as $name)
		{
			$this->SetVar($name,'',-86400000);
		}
	}
	
	function ClearAll()
	{
		$prefix_len=strlen($this->_prefix);
		foreach ((array)$this->_cookie as $name=>$value)
		{
			$name=substr($name,$prefix_len);
			$this->SetVar($name,'',-86400000);
		}
	}

}

?>