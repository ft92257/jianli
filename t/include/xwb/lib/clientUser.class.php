<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename clientUser.class.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 2037824442 598053458 3597 $
 *******************************************************************/





class clientUser 
{
	var $uidField;
	var $dk = '';
	
	
	function clientUser($uidField='uid'){
		$this->uidField = $uidField;
		$this->dk = XWB_CLIENT_SESSION;
	}
		function setOAuthKey($keys,$is_confirm = false){
		$k = $is_confirm ? 'XWB_OAUTH_KEYS2' : 'XWB_OAUTH_KEYS1' ;
		$this->setInfo(array("$k"=>$keys));
	}
		function getOAuthKey($is_confirm = false){
		$k = $is_confirm ? 'XWB_OAUTH_KEYS2' : 'XWB_OAUTH_KEYS1' ;
		return $this->getInfo($k);
	}
		function getToken(){
		$key2 = $this->getOAuthKey(true);
		return empty($key2) ? $this->getOAuthKey(false) : $key2;
	}
		function clearToken(){
		$this->setOAuthKey(array(),true);
		$this->setOAuthKey(array(),false);
	}
		function clearInfo(){
		$_SESSION[$this->dk] = array();
	}
	
	function setInfo($k,$v=false){
		if( is_array($k) ){
			$_SESSION[$this->dk] = array_merge($_SESSION[$this->dk],$k);
		}else{
			$_SESSION[$this->dk][$k] = $v;
		}
	}
		function getInfo($key=false){
		if($key){
			return isset($_SESSION[$this->dk][$key]) ? $_SESSION[$this->dk][$key] : null;
		}else{
			return $_SESSION[$this->dk];
		}
	}
		function delInfo($k){
		if ( !isset($_SESSION[$this->dk]) || empty($_SESSION[$this->dk]) ){
			return true;
		}
		if(!is_array($k)) {$k = array($k);}
		foreach($k as $kv ){
			if (isset($_SESSION[$this->dk][$kv])) unset($_SESSION[$this->dk][$kv]);
		}
		return true;
	}
		function isLogin(){
		$r = $this->getInfo($this->uidField);
		return !empty($r);
	}
		
	
	function appendStat( $type, $args = array() ){
		$this->_checkStat();
		$args['xt'] = $type;
		return true;
	}
	
	
	function getStat(){
		return $this->_checkStat();
	}
	
	
	function clearStat(){
		$this->setInfo( 'STAT', array() );
		return array();
	}
	
	
	function _checkStat(){
		$statInfo = $this->getInfo('STAT');
		if( empty( $statInfo ) || !is_array($statInfo) ){
			$statInfo = array();
			$this->setInfo( 'STAT', $statInfo );
		}
		
		return $statInfo;
		
	}
	
	
}
?>