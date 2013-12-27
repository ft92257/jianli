<?php
/**
 *
 * 记事狗REWRITE相关
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: rewrite.php 950 2012-05-23 01:39:01Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

$_rewrite = ConfigHandler::get('rewrite');
if($_rewrite['mode']) {
	global $rewriteHandler;	
	if(is_null($rewriteHandler)) {
		include_once(ROOT_PATH . 'include/lib/rewrite.han.php');	
		$rewriteHandler = new rewriteHandler();
		if($_rewrite['abs_path']) $rewriteHandler->absPath = $_rewrite['abs_path'];
		if($_rewrite['gateway']) $rewriteHandler->gateway = $_rewrite['gateway'];
		if($_rewrite['extention']) $rewriteHandler->extention = $_rewrite['extention'];
		if($_rewrite['arg_separator']) $rewriteHandler->argSeparator = $_rewrite['arg_separator'];
		if($_rewrite['var_separator']) $rewriteHandler->varSeparator = $_rewrite['var_separator'];
		if($_rewrite['prepend_var_list']) $rewriteHandler->prependVarList = $_rewrite['prepend_var_list'];
		if($_rewrite['var_replace_list']) $rewriteHandler->varReplaceList = (array)$_rewrite['var_replace_list'];
		if($_rewrite['value_replace_list']) $rewriteHandler->valueReplaceList = (array)$_rewrite['value_replace_list'];
	}
	if(true === IN_JISHIGOU_INDEX || true === IN_JISHIGOU_AJAX) {
		$rewriteHandler->parseRequest($_rewrite['request']);
	}
}
?>