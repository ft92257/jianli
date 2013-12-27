<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename core.class.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 1667964289 825925400 22356 $
 *******************************************************************/



class XWB_plugin {
	
	
	function XWB_plugin () {
		trigger_error('THIS CLASS CAN ONLY CALL STATIC!', 256);
	}
	
	function init(){
	}
	
	
	function getIP() {
		if (getenv ( "HTTP_CLIENT_IP" ) && strcasecmp ( getenv ( "HTTP_CLIENT_IP" ), "unknown" )) {
			$ip = getenv ( "HTTP_CLIENT_IP" );
		} else if (getenv ( "HTTP_X_FORWARDED_FOR" ) && strcasecmp ( getenv ( "HTTP_X_FORWARDED_FOR" ), "unknown" )) {
			$ip = getenv ( "HTTP_X_FORWARDED_FOR" );
		} else if (getenv ( "REMOTE_ADDR" ) && strcasecmp ( getenv ( "REMOTE_ADDR" ), "unknown" )) {
			$ip = getenv ( "REMOTE_ADDR" );
		} else if (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] && strcasecmp ( $_SERVER ['REMOTE_ADDR'], "unknown" )) {
			$ip = $_SERVER ['REMOTE_ADDR'];
		} else {
			$ip = "unknown";
		}
		
		return ( $ip == 'unknown' || ip2long ( $ip ) === false || ip2long ( $ip ) == -1 ) ? '0.0.0.0' : $ip;
	}
	
	
	function convertEncoding($source, $in, $out){
		$in	= strtoupper($in);
		$out = strtoupper($out);
		if ($in == "UTF8"){
			$in = "UTF-8";
		}
		if ($out == "UTF8"){
			$out = "UTF-8";
		}
		if( $in==$out ){
			return $source;
		}
	
		if(function_exists('mb_convert_encoding')) {
			return mb_convert_encoding($source, $out, $in );
		}elseif (function_exists('iconv'))  {
			return iconv($in,$out."/"."/IGNORE", $source);
		}
		return $source;
	}
	
	
	function pCfg($key=false){
		static $static_xwb_config = array();
		if( empty($static_xwb_config) ){
			require XWB_P_ROOT.'/set.data.php';
			$static_xwb_config = (array)$__XWB_SET;
		}
		
		if( $key ){
			return isset($static_xwb_config[$key]) ? $static_xwb_config[$key] : null;
		}else{
			return $static_xwb_config;
		}
	}
	
	
	function setPCfg($k, $v=false){
		static $static_xwb_config = array();
		$dFile = XWB_P_ROOT.'/set.data.php';
		if( empty($static_xwb_config) ){
			require $dFile;
			$static_xwb_config = (array)$__XWB_SET;
		}
		
		$set = $k;
		if (!is_array($k)) {
			$set = array(''.$k=>$v);
		}
		foreach ($set as $kk=>$vv){
			$static_xwb_config[$kk] = $vv;
		}
		
		$cFormat = "<?php\n%s=%s;\n?>";
		return file_put_contents($dFile, sprintf($cFormat, '$__XWB_SET',var_export($static_xwb_config, 1)) ) ? true : false;
	}
	
	
	
	function L($k){
		static $L = array();
		if (empty($L)){
			require XWB_P_ROOT. '/lang/'. strtolower(XWB_API_CHARSET).'.php';
			$L = $_LANG;
		}
		$s = isset($L[$k]) ? $L[$k] : $s;
		
		if ( func_num_args() > 1 ){
			$p = func_get_args();
			$p[0] = $s;
			$s = call_user_func_array('sprintf',$p);
		}
		return $s;
	}
	
	
	
	function V($vRoute,$def_v=NULL,$setVar=false){
		static $v = array();
		static $vKeyMap = array('C' => '_COOKIE',
								  'G' => '_GET',
								  'P' => '_POST',
								  'R' => '_REQUEST',
								  'F' => '_FILES',
								  'S' => '_SERVER',
								  'E' => '_ENV',
							);
		$vRoute = trim($vRoute);
		
				if ($setVar){
			$v[$vRoute] = $def_v;
			return true;
		}
		
		if (!isset($v[$vRoute])){
			
			if (empty($_REQUEST)){
				$_REQUEST = array_merge ( $_GET, $_POST, $_COOKIE );
			}
			
			if ( !preg_match("#^([cgprfse])(?::(.+))?\$#sim",$vRoute,$m) || !isset($vKeyMap[strtoupper($m[1])]) ){
				trigger_error("Can't parse var from vRoute: $vRoute ", E_USER_WARNING);
				return NULL;
			}
			
			$m[1] = strtoupper($m[1]);
			$tv = $GLOBALS[ $vKeyMap[$m[1]] ];
			
			if ( empty($tv) ) {
				$v[$vRoute] = $def_v;
			}elseif ( empty($m[2]) ) {
				$v[$vRoute] =  ( ($m[1]=='F' || $m[1]=='S') && version_compare(PHP_VERSION, '5.0.0', '>=') ) ? $tv :  XWB_plugin::_magic_var($tv);
			}else{
				$vr = explode('/',$m[2]);
				foreach( $vr as $vk ){
					if (!isset($tv[$vk])){
						$tv = $def_v;
						break;
					}
					$tv = $tv[$vk];
				}
				$v[$vRoute] = ( ($m[1]=='F'  || $m[1]=='S')  && version_compare(PHP_VERSION, '5.0.0', '>=')  )  ? $tv :  XWB_plugin::_magic_var($tv);
			}
		}
		
		return $v[$vRoute];
	}
	
	
	function &getDB(){
		return $GLOBALS[XWB_SITE_GLOBAL_V_NAME]['site_db'];
	}
	
	
	function &getWB(){
		
				
				$wb = XWB_plugin::N('weibo');
		$wb->setConfig();
		return $wb;
	}
	
	
	
	function &getUser(){
		return XWB_plugin::O('clientUser');
	}
	
	
	function getIsSynPost(){
		$p = XWB_plugin::O('xwbUserProfile');
		return (int)($p->get('bind_setting',1));
	}
	
	
	
	function _magic_var($mixed) {
		if( ini_get('magic_quotes_gpc') || ini_get('magic_quotes_sybase') ) {
			if(is_array($mixed)){
				return array_map(array('XWB_plugin','_magic_var'), $mixed);
			}
			return stripslashes($mixed);
		}else{
			return $mixed;
		}	
	}
	
	
	
	function URL($mRoute, $qData=false, $entry=false){
		
		if( is_string($entry) ){
			$baseUrl = "/" . ltrim($entry,"/ ");
		}else{
			static $urlTmp = '-1';
			if( '-1' == $urlTmp ){
				$urlTmp = parse_url( XWB_plugin::siteUrl() );
			}
			$baseUrl = isset($urlTmp['path']) ? $urlTmp['path'] : '';
		}
		
				$baseUrl .= 'index.php?mod=xwb';
		
		if($qData){
			if(is_array($qData)){
				$qData = http_build_query( $qData );
			}else{
				$qData = trim($qData, "&");
			}
		}else{
			$qData = '';
		}
				$rStr	= XWB_R_GET_VAR_NAME . '=' . $mRoute;
		$qData	= empty($qData) ?  $rStr  : $rStr . "&" . $qData;
		return  $baseUrl ."&" . $qData;
	}
	
	
	function redirect($mRoute,$type=1){
		switch ($type){
			case 1:
				XWB_plugin::M($mRoute);
				break;
			case 2:
								$url = XWB_plugin::baseUrl(). XWB_plugin::URL($mRoute);
				@header("Location: ".$url);
				break;
			case 3:
				@header("Location: ".$mRoute);
				break;	
			default:
				trigger_error("Error redirect type: [ $mRoute ] ", E_USER_ERROR);
				break;
		}
		exit;
	}
	
	
	function hackFile($hRoute){
		return XWB_plugin::_getIncFile($hRoute, 'hack');
		
	}
	
	
	function deny($info=''){
		@header("HTTP/1.1 403 Forbidden");
		exit('Access deny: '.$info);
	}
	
	
	function showError( $info = '', $deny = false, $extra = array() ){
		if( true == $deny ){
			XWB_plugin::deny($info);
		}else{
			include XWB_P_ROOT.'/tpl/xwb_show_error.tpl.php';
		}
		exit();
	}
	
	
	
	function getRequestRoute( $is_acc = false ){
		$m = XWB_plugin::V("g:".XWB_R_GET_VAR_NAME);
		$m = !empty($m) ? $m : XWB_R_DEF_MOD;
		
		if (!$is_acc) {
			return $m;
		}else{
			$r = XWB_plugin::_parseRoute($m);
			return array('path'=>$r[1], 'class'=>$r[2], 'function'=>$r[3]);
		}
	}
	
	
	function request($halt=false){
		XWB_plugin::M(XWB_plugin::getRequestRoute());
		if ($halt) exit;
	}
	
	
	function M($mRoute){
		$r = XWB_plugin::_parseRoute($mRoute);
		if (substr($r[3],0,1)=='_'){
			trigger_error("Module method: [ ".$r[3]." ]  start with '_' is private !  ", E_USER_ERROR);
		}
		
		$p = func_get_args();
		array_splice($p, 1, 0, array('mod',true));
		$m = call_user_func_array(array('XWB_plugin','_cls'),$p);
		
		if (!is_object($m)){
			trigger_error("Can't instance mRoute  [ $mRoute ] ", E_USER_ERROR);
		}
		
		if (!method_exists($m,$r[3])){
			trigger_error("Can't find method  [ ".$r[3]." ]  in  [ ".$r[2]." ] ", E_USER_ERROR);
		}
		
				if ($r[3]!=$r[2]) { $m->$r[3]();}
	}
	
	
	
	function &O($oRoute){
		$p = func_get_args();
		array_splice($p, 1, 0, array('cls',true));
		$o = call_user_func_array(array('XWB_plugin','_cls'),$p);
		return $o;
	}
	
	
	
	function &N($oRoute){
		$p = func_get_args();
		array_splice($p, 1, 0, array('cls',false));
		return call_user_func_array(array('XWB_plugin','_cls'),$p);
	}
	
	
	function &_cls($iRoute,$type,$is_single){
		static $clsArr = array();
		$iRoute = trim($iRoute);
		$type 	= trim($type);
		
		if ( $is_single && isset($clsArr[$iRoute]) &&  is_object($clsArr[$iRoute]) ){
			return $clsArr[$iRoute];
		}else{
			
			$cFile = XWB_plugin::_getIncFile($iRoute,$type);
			require_once($cFile);
			$r = XWB_plugin::_parseRoute($iRoute);
			$class	= $r[2];
			$func	= $r[3];
			
			if(!class_exists ($class)){
				trigger_error("class [ $class ]  is not exists in file [ $cFile ] ", E_USER_ERROR);
			}
			$p = func_get_args();
			array_splice($p, 0, 3);
			if(!empty($p)){
				$prm = array();
				foreach($p as $i=>$v){
					$prm[] = "\$p[".$i."]";
				}
				eval("\$retClass = new ".$class." (".implode(",",$prm).");");
				if ( $is_single ) { $clsArr[$iRoute] = $retClass; }
				return $retClass;
			}else{				
				if ( $is_single ) {
					$clsArr[$iRoute] = new $class;
					return $clsArr[$iRoute];
				}else{
					$retClass = new $class;
					return $retClass;
				}
			}
		}
	}
	
	
	
	function F($fRoute){
		$p = func_get_args();
		array_shift($p);
		
		$cFile = XWB_plugin::_getIncFile($fRoute,'func');
		require_once($cFile);
		
		$pp = preg_match("#^([a-z_][a-z0-9_\./]*/|)([a-z0-9_]+)(?:\.([a-z_][a-z0-9_]*))?\$#sim",$fRoute,$m);
		if (!$pp) { trigger_error("fRoute : [ $fRoute  ] is  invalid ", E_USER_ERROR);  return false;}
		$func = empty($m[3])?$m[2]:$m[3];
		if ( !function_exists($func) ) {
			trigger_error("Can't find function [ $func ] in file [ $cFile ]", E_USER_ERROR); 
		}
		return call_user_func_array($func,$p);
	}
	
	
	function getPluginUrl($path='',$deep=0){
		return XWB_plugin::siteUrl($deep).XWB_P_DIR_NAME."/".$path;
	}
	
	
	function _getIncFile($fRoute, $type='cls'){
		
		static $fileMap = array();
		$fileId = (string)$fRoute. (string)$type;
		if( isset($fileMap[$fileId]) ){
			return $fileMap[$fileId];
		}
		
		if ( !XWB_plugin::_chkPath($fRoute) ){
			trigger_error("file route: [ $fRoute  - $type  ] is  invalid ", E_USER_ERROR);
		}
		
		$m = XWB_plugin::_parseRoute($fRoute);
		$fp = $m[1].$m[2];
		
		$type = strtolower($type);
		$f = array(
				   'cls'=>	XWB_P_ROOT . DIRECTORY_SEPARATOR. "lib" . DIRECTORY_SEPARATOR . $fp . '.class.php',
				   'mod'=>	XWB_P_ROOT . DIRECTORY_SEPARATOR. "lib" . DIRECTORY_SEPARATOR . $fp . '.mod.php',
				   'func'=>	XWB_P_ROOT . DIRECTORY_SEPARATOR. "lib" . DIRECTORY_SEPARATOR . $fp . '.function.php',
				   'hack'=>	XWB_P_ROOT . DIRECTORY_SEPARATOR. "hack" . DIRECTORY_SEPARATOR . $fp . '.hack.php'
		);
		if ( !isset($f[$type]) ){
			trigger_error("file type: [ $type  ] is  invalid ", E_USER_ERROR);
		}
		if ( !file_exists($f[$type]) ){
			trigger_error("file:[ ".$f[$type]." ] not exists  ", E_USER_ERROR);
			
		}
		$fileMap[$fileId] = $f[$type];
		return $f[$type];
		
	}
	
	
	function _chkPath($v){
		return count(explode("..",$v))== 1 && preg_match("#^[a-z_][a-z0-9_/\.]*\$#sim",$v);
	}
	
	
	function _parseRoute($route){
		static $routeMap = array();
		
		$route = trim($route);
		if( isset($routeMap[$route]) ){
			return $routeMap[$route];
		}
		
		$p = preg_match("#^([a-z_][a-z0-9_\./]*/|)([a-z0-9_]+)(?:\.([a-z_][a-z0-9_]*))?\$#sim",$route,$m);
		if (!$p) { trigger_error("route : [ $route  ] is  invalid ", E_USER_ERROR);  return false;}
		if (empty($m[3])) $m[3] = XWB_R_DEF_MOD_FUNC;
		$routeMap[$route] = $m;
		return $m;
	}
	
	
	function isUserBinded(){
		$bInfo = XWB_plugin::getBindInfo();
		return (empty($bInfo) || !is_array($bInfo)) ? false : true;
	}
	
	
	function getBindInfo($key=false, $def=null){
		static $rst = '-1';   		if (!XWB_S_UID) {return false;}
		if( $rst === '-1' ){
			$db = XWB_plugin::getDB();
			$rst = $db->fetch_first("SELECT * FROM ".XWB_S_TBPRE."xwb_bind_info  WHERE  uid=".XWB_S_UID." ");
		}
		if ($key===false){
			return empty($rst) ? array() :  $rst;
		}else{
			return isset($rst[$key]) ? $rst[$key] : $def;
		}
	}

	
	
	function siteUrl($deep=0){
		
				if( 0 === $deep && defined('XWB_S_SITEURL') ){
			return XWB_S_SITEURL;
		}
		
				$v1 = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']) : '';
		$v2 = str_replace('\\','/',$_SERVER['SCRIPT_FILENAME']);
		$deep+=1;
				$url  = XWB_plugin::baseUrl();
		$pUrl = str_replace($v1,'',$v2);
		
		if ($pUrl==$v2){
			$pUrl = $_SERVER['SCRIPT_NAME'];
		}
		
		$pUrl = '/' . ltrim($pUrl, '/');
		$url  = $url . preg_replace("#(/[^/]+){".$deep."}$#",'/',$pUrl);
		
		return $url;
	}
	
	
	
	function baseUrl(){
		static $url = '';
		
		if( empty($url) ){
						if( defined('XWB_S_BASEURL') ){
				$url = XWB_S_BASEURL;
			}else{
				$url  = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http' ). 
					':/'.'/'. ( isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : 
								(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') 
					);
			}
		}

		return $url;
	}
	
	
	
	function LOG($msg, $logName='log', $halt = false){
		
		
		return 0;
	}
	
	
	
	function statUrl($type, $args = array(), $html = false, $random = true ){
		if( defined('XWB_P_STAT_DISABLE') ){
			return '';
		}
		
		$statUrl = 'http:/'.'/beacon.x.weibo.com/a.gif';
		
				$args['pjt'] = XWB_P_PROJECT;
		$args['dsz'] = XWB_S_VERSION;
		$args['ver'] = XWB_P_VERSION;
		$args['xt'] = $type;
		$args['akey'] = isset($args['akey']) ? $args['akey'] : XWB_APP_KEY;
		$args['ip'] = XWB_plugin::getIP();
				if( !isset($args['uid']) ){
			$args['uid'] = (int)(XWB_plugin::getBindInfo("sina_uid"));
		}
		$args['uid'] = ( 1 > (int)$args['uid'] ) ? '' : (int)$args['uid'];
		if( true === $random ){
			$args['random'] = rand(1,999999);
		}
		
		$statUrl .= '?'. http_build_query($args);
		
		if ( defined('XWB_P_DEBUG') && true == XWB_P_DEBUG ){
			$logmsg = "涓婃姤鐨刄RL涓猴細". $statUrl;
			XWB_plugin::LOG( $logmsg, 'statRecord', false );
		}
		
		if( false == $html ){
			return $statUrl;
		}else{
			return '<img src="'. $statUrl. '" style="display:none" />';
		}
		
	}
	
	
	
	function statReport( $type, $args = array() ){
		if( defined('XWB_P_STAT_DISABLE') ){
			return false;
		}
		
		$statUrl = XWB_plugin::statUrl( $type, $args );
		if( '' == $statUrl ){
			return false;
		}
		
		if( !class_exists('fsockopenHttp') ){
			require_once "fsockopenHttp.class.php";
		}
		
		$httpObj = new fsockopenHttp();
		$httpObj->setUrl( $statUrl );
		$httpObj->max_retries = 0;
		$httpObj->request();
		return true;
		
	}
	
}

