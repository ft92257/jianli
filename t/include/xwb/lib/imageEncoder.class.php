<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename imageEncoder.class.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 2048815433 1688287145 2110 $
 *******************************************************************/




class imageEncoder
{
	
	function flashdata_decode($s) {
		$r = '';
		$l = strlen($s);
		for($i=0; $i<$l; $i=$i+2) {
			$k1 = ord($s[$i]) - 48;			$k1 -= $k1 > 9 ? 7 : 0;			$k2 = ord($s[$i+1]) - 48;
			$k2 -= $k2 > 9 ? 7 : 0;
			$r .= chr($k1 << 4 | $k2);		}
		return $r;
	}
	
	function flashdata_encode($s){
		if( version_compare($this->_getUCVersion(), '1.0.0', '>') ){
						$_loc_2 = "";
			for($i = 0; $i < strlen($s); $i++){
				$_loc_3 = strtoupper($this -> toHexNum(ord($s[$i])));				$_loc_2 .= $_loc_3;			}
			return $_loc_2;
		}else{
						return base64_encode( $s );
		}
		
	}
	
	function toHexNum($param1)
	{
	     return ($param1 <= 15 ? ("0" . strval(dechex($param1))) :strval(dechex($param1)));
	}
	
	
	function _getUCVersion()
	{
		loaducenter();
		return defined('UC_CLIENT_VERSION') ? UC_CLIENT_VERSION : UC_VERSION;
	}

}