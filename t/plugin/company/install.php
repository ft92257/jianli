<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename install.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-06-20 16:52:55 1161901517 1227518391 808 $
 *******************************************************************/


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}
$sql = <<<EOF
DROP TABLE IF EXISTS {jishigou}plugin_company;
CREATE TABLE {jishigou}plugin_company(
  `cid` int(10) unsigned NOT NULL auto_increment,
  `uid` int(10) unsigned NOT NULL default '0',
  `ucuid` int(10) unsigned NOT NULL default '0',
  `username` varchar(50) NOT NULL default '',
  `companyname` varchar(200) NOT NULL default '',
  `companyid` varchar(80) NOT NULL default '',
  `ceoname` varchar(20) NOT NULL default '',
  `userid` varchar(30) NOT NULL default '',
  `address` varchar(250) NOT NULL default '',
  `tel` varchar(30) NOT NULL default '',
  `ptime` int(10) NOT NULL default '0',
  `ison` tinyint(1) NOT NULL default '0',
  `descripction` text,
  PRIMARY KEY  (`cid`)
) ENGINE=MyISAM;
EOF;
?>