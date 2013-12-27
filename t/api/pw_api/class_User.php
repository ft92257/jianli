<?php

!defined('P_W') && exit('Forbidden');

define('API_USER_USERNAME_NOT_UNIQUE', 100);

class User {

	var $base;
	var $db;

	function User($base) {
		$this->base = $base;
		$this->db = $base->db;
	}

	function synlogin($user) {
		list($winduid, $windid, $windpwd) = explode("\t", $this->base->strcode($user, false));
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');

		include ROOT_PATH.'include/function/global.func.php';

        DB::query("SELECT `uid`, `password` FROM `".DB::table('members')."` WHERE `ucuid`='$winduid'");
		$UserFields = DB::fetch_array($query);
		if($UserFields)
		{
			$auth = authcode("{$UserFields['password']}\t{$UserFields['uid']}","ENCODE",'',2592000);
			jsg_setcookie('sid', '', -86400000);
			jsg_setcookie('auth',$auth,86400000);
		}
	}

	function synlogout() {
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		include ROOT_PATH.'include/function/global.func.php';
        jsg_setcookie('sid', '', -86400000);
		jsg_setcookie('auth', '', -86400000);
	}
    function getusergroup() {
        $usergroup = array();
        $query = $this->db->query("SELECT gid,gptype,grouptitle FROM pw_usergroups ");
        while($rt= $this->db->fetch_array($query)) {
            $usergroup[$rt['gid']] = $rt;
        }
        return new ApiResponse($usergroup);
    }
}
?>