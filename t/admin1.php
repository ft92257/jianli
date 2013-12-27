<?php
/**
 *
 * 后台入口
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: admin.php 622 2012-04-10 07:48:43Z wuliyong $
 */



require './include/jishigou.php';
$jishigou = new jishigou();

$jishigou->run('admin');

?>