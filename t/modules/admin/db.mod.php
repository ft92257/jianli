<?php

/**
 *
 * 后台数据库备份还原模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: db.mod.php 1303 2012-08-01 07:31:57Z wuliyong $
 */

include_once(ROOT_PATH . 'include/function/misc.func.php');
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{

	function ModuleObject($config)
	{
		$this->MasterObject($config);
		
		ini_set("memory_limit","128M");
		
		$this->Execute();
	}

	function Execute()
	{

		ob_start();
		switch($this->Code)
		{
			case 'export':
				$this->Export();
				break;
			case 'doexport':
				$this->DoExport();
				break;
			case 'import':
				$this->Import();
				break;
			case 'importzip':
				$this->DoImportZip();
				break;
			case 'doimport':
				$this->DoImport();
				break;
			case 'dodelete':
				$this->DoDelete();
				break;
			case 'optimize':
				$this->optimize();
				break;
			case 'dooptimize':
				$this->DoOptimize();
				break;
			default:
				$this->Main();
				break;
		}
		$body = ob_get_clean();
		
		$this->ShowBody($body);
		
	}
	function Main()
	{
		;
	}

	function Import()
	{	
		$backupdir = $this->Get['backupdir'];
		$backupdir = dir_safe($backupdir);
		if (!$backupdir) {
			if(true===JISHIGOU_FOUNDER) {
				$_f_list = (array) Load::lib('io', 1)->ReadDir(RELATIVE_ROOT_PATH.'data/backup/db/'.$backupdir,1);
				$f_list = array();
				$key = 0;
				foreach ($_f_list as $_k=>$_f) {
					$ext = strtolower(trim(substr(strrchr($_f, '.'), 1, 10)));
					if(!in_array($ext,array('sql','zip',)) || 'jishigou.sql'==basename($_f)) {
						unset($_f_list[$_k]);
						
						continue;
					}
					
					if(is_file($_f)) {
						$f_list[dirname($_f)] = 1;
					}			
				}		
				$_tmp_arr = (array_keys($f_list));
				$dateline_list = $dir_list = array();
				foreach ($_tmp_arr as $key=>$dir) {
					$timestamp = @filemtime($dir . './index.htm');
					$arr = array(
						'timestamp' => $timestamp,
						'dateline' => my_date_format($timestamp,'Y-m-d H:i:s'),
						'dir' => $dir,
						'backupdir' => ($backupdir = (substr($dir,13))) ? $backupdir : './',
					);
					$arr['backupdir_urlencode'] = urlencode($arr['backupdir']);
					if ($timestamp) {
						$dateline_list[$key] = (int) $timestamp;
					}
					
					$dir_list[$key] = $arr;
				}
				@array_multisort($dateline_list,SORT_DESC,SORT_NUMERIC,$dir_list);
			}
		} else {		
			$exportlog = array();		
			if(is_dir(RELATIVE_ROOT_PATH.'data/backup/'.$backupdir)) {
				$dateline_list = array();
				$key = 0;
				$dir = dir(RELATIVE_ROOT_PATH.'data/backup/'.$backupdir);
				while($entry = $dir->read()) {
					$entry = RELATIVE_ROOT_PATH.'data/backup/'.$backupdir.'/'.$entry;
					if(is_file($entry)) {
						if(preg_match("/\/[\w\d\-\_]+\.sql$/i", $entry)) {
							$filesize = filesize($entry);
							$fp = @fopen($entry, 'rb');
							$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
							fclose ($fp);
							$exportlog[$key] = array(
							'version' => $identify[1],
							'type' => $identify[2],
							'method' => $identify[3],
							'volume' => $identify[4],
							'filename' => $entry,
							'dateline' => @filemtime($entry),
							'size' => $filesize
							);
						} elseif(preg_match("/\/[\w\d\-\_]+\.zip$/i", $entry)) {
							$filesize = filesize($entry);
							$exportlog[$key] = array(
							'type' => 'zip',
							'filename' => $entry,
							'size' => filesize($entry),
							'dateline' => @filemtime($entry)
							);
						}
						
						if($exportlog[$key]['dateline'])
						{
							$dateline_list[$key] = (int) $exportlog[$key]['dateline']; 				
						}
						$key++;
					}
				}
				$dir->close();
			} else {
				$this->Messager('database_export_dest_invalid');
			}
			@array_multisort($dateline_list,SORT_ASC,SORT_NUMERIC,$exportlog);
			
			$exportinfo = '';
			$exportinfo .= "<input type=hidden name=backupdir value='{$backupdir}' />";
			$type_list=array("all_tables"=>"全部数据","custom"=>"自定义备份",'zip'=>"压缩备份");
			$dateline_list = array();
			foreach($exportlog as $info) {		
				$info['dateline'] = is_int($info['dateline']) ? my_date_format($info['dateline']) : "未知";
				$info['size'] = sizecount($info['size']);
				$info['volume'] = $info['method'] == 'multivol' ? $info['volume'] : '';
				$info['method'] = $info['type'] != 'zip' ? ($info['method'] == 'multivol' ? "多卷" : "Shell") : '';
				$exportinfo .= "<tr align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"".basename($info['filename'])."\"></td>\n".
				"<td class=\"altbg2\"><a href=\"$info[filename]\">".substr(strrchr($info['filename'], "/"), 1)."</a></td>\n".
				"<td class=\"altbg1\">$info[version]</td>\n".
				"<td class=\"altbg2\">$info[dateline]</td>\n".
				"<td class=\"altbg1\">".$type_list[$info['type']]."</td>\n".
				"<td class=\"altbg2\">$info[size]</td>\n".
				"<td class=\"altbg1\">$info[method]</td>\n".
				"<td class=\"altbg2\">$info[volume]</td>\n".
				($info['type'] == 'zip' ? "<td class=\"altbg1\"><a href=\"admin.php?mod=db&code=importzip&datafile_server=".urlencode($info[filename])."&importsubmit=yes\">[解压缩]</a></td>\n" :
				"<td class=\"altbg1\"><a href=\"admin.php?mod=db&code=doimport&from=server&datafile_server=".urlencode($info[filename])."&importsubmit=yes\"".
				($info['version'] != SYS_VERSION ? " onclick=\"return confirm('导入和当前 JishiGou 版本不一致的数据极有可能产生无法解决的故障，您确定继续吗？');\"" : '').">[导入]</a></td>\n");
			}
		}
			
		include $this->TemplateHandler->Template('admin/db_import');
	}

	function DoImport()
	{		
		if(true!==JISHIGOU_FOUNDER) {
			$this->Messager("为安全起见，只有网站创始人才能执行数据恢复操作。", null);
		}
		
		$readerror = 0;
		$datafile = '';
		
		$from = get_param('from');
		$datafile_server = get_param('datafile_server');
		$datafile_server = dir_safe($datafile_server);
		if(false == preg_match('~^\.\/data\/backup\/db\/([\w\d\-\_]+)\/\\1(\-\d+)?\.sql$~i', $datafile_server)) {
			$this->Messager("文件名参数传递有误，请返回重试", null);
		}
		$autoimport = get_param('autoimport');
		$supe = get_param('supe');
		$delunzip = get_param('delunzip');
		
		if($from == 'server') {
			$datafile = RELATIVE_ROOT_PATH.'./'.$datafile_server;
		}
		
		$dbcharset = $this->DatabaseHandler->Charset;

		
		if($datafile && false != ($fp = @fopen($datafile, 'rb'))) {
			$sqldump = fgets($fp, 256);
			$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", $sqldump)));
			$dumpinfo = array('method' => $identify[3], 'volume' => intval($identify[4]));
			if($dumpinfo['method'] == 'multivol') {
				$sqldump .= @fread($fp, filesize($datafile));
			}
			fclose($fp);
		} else {
			if($autoimport) {
				cache_clear();
				$this->Messager('分卷数据成功导入数据库。',null);
			} else {
				$this->Messager('数据文件不存在: 可能服务器不允许上传文件或尺寸超过限制。',null);
			}
		}

		if($dumpinfo['method'] == 'multivol') {
			$sqlquery = splitsql($sqldump);
			unset($sqldump);
			$supetablepredot = strpos($supe['tablepre'], '.');
			$supe['dbname'] =  $supetablepredot !== FALSE ? substr($supe['tablepre'], 0, $supetablepredot) : '';

			foreach($sqlquery as $sql) {

				$sql = syntablestruct(trim($sql), $this->DatabaseHandler->GetVersion() > '4.1', $dbcharset);

				if(substr($sql, 0, 11) == 'INSERT INTO') {
					$sqldbname = substr($sql, 12, 20);
					$dotpos = strpos($sqldbname, '.');
					if($dotpos !== FALSE) {
						if(empty($supe['dbmode'])) {
							$sql = 'INSERT INTO `'.$supe['dbname'].'`.'.substr($sql, 13 + $dotpos);
						} else {
													}
					}
				}

				if($sql != '') {
					$this->DatabaseHandler->Query($sql, 'SKIP_ERROR');
					if(($sqlerror = $this->DatabaseHandler->GetLastErrorString()) && $this->DatabaseHandler->GetLastErrorNo() != 1062) {
						die('MySQL Query Error'.$sql);
					}
				}
			}

			if($delunzip) {
				@unlink($datafile_server);
			}

			$datafile_next = preg_replace("/\-($dumpinfo[volume])(\.sql)$/i", "-".($dumpinfo['volume'] + 1)."\\2", $datafile_server);

			if($dumpinfo['volume'] == 1) {
				$to="admin.php?mod=db&code=doimport&from=server&datafile_server=".urlencode($datafile_next)."&autoimport=yes&importsubmit=yes".(!empty($delunzip) ? '&delunzip=yes' : '');
				$msg='            <form method="post" action="'.$to.'">
                    <br /><br /><br />分卷数据成功导入数据库，您需要自动导入本次其它的备份吗？<br /><br /><br /><br />
                    <input type="hidden" name="FORMHASH" value="'.FORMHASH.'"> &nbsp; 
                    <input class="button" type="submit" name="confirmed" value=" 确 定 "> &nbsp; 
                    <input class="button" type="button" value=" 取 消 " onClick="history.go(-1);">
                  </form><br />';
				$this->Messager($msg,null);
			} elseif($autoimport) {
				$this->Messager("数据文件 #{$dumpinfo['volume']} 成功导入，程序将自动继续。", "admin.php?mod=db&code=doimport&from=server&datafile_server=".urlencode($datafile_next)."&autoimport=yes&importsubmit=yes".(!empty($delunzip) ? '&delunzip=yes' : ''));
			} else {
				cache_clear();
				$this->Messager('数据成功导入数据库。',null);
			}
		} 
		
		
		 
		else {
			$this->Messager('数据文件非 JishiGou 格式，无法导入。');
		}

	}

	function DoImportZip()
	{
		if(true!==JISHIGOU_FOUNDER) {
			$this->Messager("为安全起见，只有网站创始人才能执行数据恢复操作。", null);
		}
		
		$datafile_server = get_param('datafile_server');		
		$datafile_server = dir_safe($datafile_server);
		if(false == preg_match('~^\.\/data\/backup\/db\/([\w\d\-\_]+)\/\\1(\-\d+)?\.zip$~i', $datafile_server)) {
			$this->Messager("文件名参数传递有误，请返回重试", null);
		}
				$backupdir = 'db/' . basename(dirname($datafile_server));
		$backupdir = dir_safe($backupdir);
		$multivol = (int) get_param('multivol');
		$datafile_vol1 = get_param('datafile_vol1');
		$datafile_vol1 = dir_safe($datafile_vol1);
		if($datafile_vol1 && false == preg_match('~^\.\/data\/backup\/db\/([\w\d\-\_]+)\/\\1(\-\d+)?\.sql$~i', $datafile_vol1)) {
			$this->Messager("文件名参数传递有误，请返回重试", null);
		}
		
		require_once ROOT_PATH . 'include/function/zip.func.php';
		$unzip = new SimpleUnzip();
		$unzip->ReadFile($datafile_server);

		if($unzip->Count() == 0 || $unzip->GetError(0) != 0 || !preg_match("/^[\w\d\-\_]+\.sql$/i", $importfile = $unzip->GetName(0))) {
			$this->Messager('数据文件不存在: 可能服务器不允许上传文件或尺寸超过限制。',null);
		}

		$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", substr($unzip->GetData(0), 0, 256))));
		$confirm = (('yes'==get_param('confirm', 'P')) ? 1 : 0);
		if(!$confirm && $identify[1] != SYS_VERSION) {
			$to="admin.php?mod=db&code=importzip&datafile_server=".urlencode($datafile_server)."&importsubmit=yes&confirm=yes";
			$msg=' <form method="post" action="'.$to.'">
                    <br /><br /><br />导入和当前程序版本不一致的数据极有可能产生无法解决的故障，您确定继续吗？<br /><br /><br /><br />
                    <input type="hidden" name="FORMHASH" value="'.FORMHASH.'"> &nbsp; 
                    <input class="button" type="submit" name="confirmed" value=" 确 定 "> &nbsp; 
                    <input class="button" type="button" value=" 取 消 " onClick="history.go(-1);">
                  </form><br />';
			$this->Messager($msg,null);
		}

		$sqlfilecount = 0;
		foreach($unzip->Entries as $entry) {
			if(preg_match("/^[\w\d\-\_]+\.sql$/i", $entry->Name)) {
				$len = Load::lib('io', 1)->WriteFile('./data/backup/'.$backupdir.'/'.$entry->Name, $entry->Data);
				$sqlfilecount++;
			}
		}

		if(!$sqlfilecount) {
			$this->Messager('database_import_file_illegal');
		}
		$type_list=array("all_tables"=>"全部数据","custom"=>"自定义备份",'zip'=>"压缩备份");
		$info = basename($datafile_server).'<br />'.'版本'.': '.$identify[1].'<br />'.'类型'.': '.$type_list[$identify[2]].'<br />'.'方式'.': '.($identify[3] == 'multivol' ? "多卷" : "SHELL").'<br />';

		if($multivol) {
			$multivol++;
			$df = $datafile_server;
			$datafile_server = preg_replace("/\-(\d+)(\.zip)$/i", "-$multivol\\2", $datafile_server);
			if(is_file($datafile_server)) {
				$this->Messager("数据文件 #$multivol 成功解压缩，程序将自动继续。", 'admin.php?mod=db&code=importzip&multivol='.$multivol.'&datafile_vol1='.$datafile_vol1.'&datafile_server='.urlencode($datafile_server).'&importsubmit=yes&confirm=yes');
			} else {
				$to='admin.php?mod=db&code=doimport&from=server&datafile_server='.urlencode($datafile_vol1).'&importsubmit=yes&delunzip=yes';
				$msg=' <form method="post" action="'.$to.'">
		                    <br /><br /><br />所有分卷文件解压缩完毕，您需要自动导入备份吗？导入后解压缩的文件将会被删除。<br /><br /><br /><br />
		                    <input type="hidden" name="FORMHASH" value="'.FORMHASH.'"> &nbsp; 
		                    <input class="button" type="submit" name="confirmed" value=" 确 定 "> &nbsp; 
		                    <input class="button" type="button" value=" 取 消 " onClick="location.href=\'admin.php?mod=db&code=import\';">
		                  </form><br />';

				$this->Messager($msg,null);
			}
		}

		if($identify[3] == 'multivol' && $identify[4] == 1 && preg_match("/\-1(\.zip+)$/i", $datafile_server)) {
			$datafile_vol1 = $datafile_server;
			$datafile_server = preg_replace("/\-1(\.zip+)$/i", "-2\\1", $datafile_server);
			if(is_file($datafile_server) && $datafile_vol1 != $datafile_server) {
				$to='admin.php?mod=db&code=importzip&multivol=1&datafile_vol1=./data/backup/'.$backupdir.'/'.$importfile.'&datafile_server='.urlencode($datafile_server).'&importsubmit=yes&confirm=yes';
				$msg=' <form method="post" action="'.$to.'">
		                    '.$info.'<br />备份文件解压缩完毕，您需要自动解压缩其它的分卷文件吗？<br /><br /><br /><br />
		                    <input type="hidden" name="FORMHASH" value="'.FORMHASH.'"> &nbsp; 
		                    <input class="button" type="submit" name="confirmed" value=" 确 定 "> &nbsp; 
		                    <input class="button" type="button" value=" 取 消 " onClick="history.go(-1);">
		                  </form><br />';
				$this->Messager($msg, null);
			}
		}
		
		$to='admin.php?mod=db&code=doimport&from=server&datafile_server=./data/backup/'.$backupdir.'/'.$importfile.'&importsubmit=yes&delunzip=yes';
		$msg=' <form method="post" action="'.$to.'">
                    <br /><br /><br />所有分卷文件解压缩完毕，您需要自动导入备份吗？导入后解压缩的文件将会被删除。<br /><br /><br /><br />
                    <input type="hidden" name="FORMHASH" value="'.FORMHASH.'"> &nbsp; 
                    <input class="button" type="submit" name="confirmed" value=" 确 定 "> &nbsp; 
                    <input class="button" type="button" value=" 取 消 " onClick="location.href=\"admin.php?mod=db&code=import\";">
                  </form><br />';
		$this->Messager($msg,null);
	}

	function DoDelete()
	{
		$backupdir = $this->Post['backupdir'];
		$backupdir = dir_safe($backupdir);
		$delete=$this->Post['delete'];
		if(is_array($delete)) {
			$dir = RELATIVE_ROOT_PATH.'data/backup/'.$backupdir.'/';
			foreach($delete as $filename) {
				@unlink($dir.str_replace(array('/', '\\'), '', $filename));
			}
			
			
			if ($backupdir && false!==strpos($dir,'/db/')) {
				
				
				$f_list = Load::lib('io', 1)->ReadDir($dir,1);
				if(count($f_list) < 3) {
					
					$d = true;
					foreach ($f_list as $f) {
						if ((filesize($f) > 0) || ((basename($f) != 'index.htm') && (basename($f) != 'index.html'))) {
							$d = false;		
							break;
						}
					}
					
					if ($d) {
						Load::lib('io', 1)->RemoveDir($dir);
					}
				}
			}
			
			$this->Messager('指定备份文件成功删除',null);
		} else {
			$this->Messager('您没有选择要删除的备份文件，请返回');
		}
	}

	function optimize()
	{
		$tabletype = $this->DatabaseHandler->GetVersion() > '4.1' ? 'Engine' : 'Type';
		$optimizetable = '';
		$totalsize = 0;
		$tablearray = array( 0 =>TABLE_PREFIX) ;
		$table_string="";
		foreach($tablearray as $tp) {
			$query = $this->DatabaseHandler->Query("SHOW TABLE STATUS LIKE '$tp%'", 'SKIP_ERROR');
			while($table = $query->GetRow()) {
				if($table['Data_free'] && $table[$tabletype] == 'MyISAM') {
					$checked = $table[$tabletype] == 'MyISAM' ? 'checked' : 'disabled';
					$table_string.= "<tr><td class=\"altbg1\" align=\"center\"><input class=\"checkbox\" type=\"checkbox\" name=\"optimizetables[]\" value=\"$table[Name]\" $checked></td>\n".
					"<td class=\"altbg2\" align=\"center\">$table[Name]</td>\n".
					"<td class=\"altbg1\" align=\"center\">".$table[$tabletype]."</td>\n".
					"<td class=\"altbg2\" align=\"center\">$table[Rows]</td>\n".
					"<td class=\"altbg1\" align=\"center\">$table[Data_length]</td>\n".
					"<td class=\"altbg2\" align=\"center\">$table[Index_length]</td>\n".
					"<td class=\"altbg1\" align=\"center\">$table[Data_free]</td></tr>\n";
					$totalsize += $table['Data_length'] + $table['Index_length'];
				}
			}
		}
		if(empty($totalsize)) {
			$table_string.= "<tr><td colspan=\"7\" align=\"right\">数据表没有碎片，不需要再优化。</td></tr></table></div>";
		} else {
			$table_string.="<tr><td colspan=\"7\" align=\"right\">尺寸 ".sizecount($totalsize)."</td></tr></table></div><br /><center><input class=\"button\" type=\"submit\" name=\"optimizesubmit\" value=\"提交\"></center>";
		}

		include $this->TemplateHandler->Template('admin/db_optimize');
	}
	function DoOptimize()
	{
		$optimizetables = get_param('optimizetables');
		
		$optimizetable = '';
		$totalsize = 0;
		$tablearray = array( 0 =>TABLE_PREFIX) ;
		$table_string="";
		foreach($tablearray as $tp) {
			$query = $this->DatabaseHandler->Query("SHOW TABLE STATUS LIKE '$tp%'", 'SKIP_ERROR');
			while($table = $query->GetRow()) {
				if(is_array($optimizetables) && in_array($table['Name'], $optimizetables)) {
					$this->DatabaseHandler->Query("OPTIMIZE TABLE $table[Name]");
					$this->DatabaseHandler->Query("REPAIR TABLE $table[Name]");
				}

				$table_string.= "<tr>\n".
				"<td class=\"altbg1\" align=\"center\">是</td>\n".
				"<td class=\"altbg2\" align=\"center\">$table[Name]</td>\n".
				"<td class=\"altbg1\" align=\"center\">".($this->DatabaseHandler->GetVersion() > '4.1' ?  $table['Engine'] : $table['Type'])."</td>\n".
				"<td class=\"altbg2\" align=\"center\">$table[Rows]</td>\n".
				"<td class=\"altbg1\" align=\"center\">$table[Data_length]</td>\n".
				"<td class=\"altbg2\" align=\"center\">$table[Index_length]</td>\n".
				"<td class=\"altbg1\" align=\"center\">0</td>\n".
				"</tr>\n";
				$totalsize += $table['Data_length'] + $table['Index_length'];
			}
		}
		$table_string.= "<tr><td colspan=\"7\" align=\"right\">尺寸  ".sizecount($totalsize)."</td></tr></table>";

		include $this->TemplateHandler->Template('admin/db_optimize');
	}

	function Export()
	{
		$filename=my_date_format(time(),'YmdHi').'_'.random(8);
		$shelldisabled = function_exists('shell_exec') ? '' : 'disabled';
		$table_list = $this->_fetch_table_list(TABLE_PREFIX);
		$table_list_group=array_chunk($table_list,4);

		include $this->TemplateHandler->Template('admin/db_export');
	}
	function DoExport()
	{
 		global $sizelimit, $startrow, $extendins, $sqlcompat, $sqlcharset, $dumpcharset, $usehex, $complete, $excepttables;
		
		$excepttables=array(TABLE_PREFIX."sessions", TABLE_PREFIX."cache", );

		$time=$timestamp=time();
		$tablepre=TABLE_PREFIX;

		$this->DatabaseHandler->Query('SET SQL_QUOTE_SHOW_CREATE=1', 'SKIP_ERROR');
		$filename = get_param('filename');
		if(!$filename || preg_match("/(\.)(exe|php|jsp|asp|aspx|cgi|fcgi|pl)(\.|$)/i", $filename) || !preg_match('~^[\w\d\-\_]+$~', $filename)) {
			$this->Messager("备份文件名无效");
		}
		
		$type = get_param('type');
		$setup = get_param('setup');
		$customtables = get_param('customtables');
		$startrow = get_param('startrow');
		$extendins = get_param('extendins');
		$usehex = get_param('usehex');
		$usezip = get_param('usezip');
		$sizelimit = get_param('sizelimit');
		$volume = (int) get_param('volume');
				$method = 'multivol';
		$sqlcharset = get_param('sqlcharset');
		$sqlcompat = get_param('sqlcompat');
		
		
				if($type == 'all_tables') {
			$tables = $this->_array_keys2($this->_fetch_table_list($tablepre), 'Name');
		}
		elseif($type == 'custom')
		{
			$tables = array();
			$cache_id = "tables";
			if(empty($setup))
			{
				$tables = cache_file('get', $cache_id);
			}
			else
			{
				cache_file('set', $cache_id, $customtables);
				$tables = & $customtables;
			}
			if( !is_array($tables) || empty($tables))
			{
				$this->Messager("没有要导出的数据表");
			}
		}

		
		$volume = intval($volume) + 1;
		$idstring = '# Identify: '.base64_encode("$timestamp,".SYS_VERSION.",$type,$method,$volume")."\n";

		
		$dumpcharset = $sqlcharset ? $sqlcharset : str_replace('-', '', $this->Config['charset']);
		$setnames = ($sqlcharset && $this->DatabaseHandler->GetVersion() > '4.1' && (!$sqlcompat || $sqlcompat == 'MYSQL41')) ? "SET NAMES '$dumpcharset';\n\n" : '';
		if($this->DatabaseHandler->GetVersion() > '4.1') {
			if($sqlcharset) {
				$this->DatabaseHandler->Query("SET NAMES '".$sqlcharset."';\n\n");
			}
			if($sqlcompat == 'MYSQL40') {
				$this->DatabaseHandler->Query("SET SQL_MODE='MYSQL40'");
			} elseif($sqlcompat == 'MYSQL41') {
				$this->DatabaseHandler->Query("SET SQL_MODE=''");
			}
		}

		
		$f = str_replace(array('/', '\\', '.'), '', $filename);
		$f = dir_safe($f);
		$backupdir = 'db/' . $f;
		$backupfilename = './data/backup/'.$backupdir.'/'.$f;
		if (!is_dir(($d = dirname($backupfilename)))) {
			Load::lib('io', 1)->MakeDir($d);
		}
		

		
		if($usezip) {
			require_once ROOT_PATH . 'include/function/zip.func.php';
		}

		
		if($method == 'multivol') {
			$sqldump = '';
			$tableid = intval(get_param('tableid'));
			$startfrom = intval(get_param('startfrom'));

			$complete = TRUE;

			for(; $complete && $tableid < count($tables) && strlen($sqldump) + 500 < $sizelimit * 1000; $tableid++) {
				$sqldump .= $this->_sql_dump_table($tables[$tableid], $startfrom, strlen($sqldump));
				if($complete) {
					$startfrom = 0;
				}
			}

			$dumpfile = $backupfilename."-%s".'.sql';
			!$complete && $tableid--;
			if(trim($sqldump)) {
				$sqldump = "$idstring".
				"# <?php exit(); ?>\n".
				"# JishiGou Multi-Volume Data Dump Vol.$volume\n".
				"# Version: JishiGou ".SYS_VERSION."\n".
				"# Time: $time\n".
				"# Type: $type\n".
				"# Table Prefix: $tablepre\n".
				"#\n".
				"# JishiGou Home: http:\/\/www.jishigou.net\n".
				"# Please visit our website for newest infomation about JishiGou\n".
				"# --------------------------------------------------------\n\n\n".
				"$setnames".
				$sqldump;
				$dumpfilename = sprintf($dumpfile, $volume);
				$fp = @fopen($dumpfilename, 'wb');
				flock($fp, 2);
				if(@!fwrite($fp, $sqldump)) {
					fclose($fp);
					$this->Messager("备份文件名有问题");
				} else {
					fclose($fp);
					if($usezip == 2) {
						$fp = @fopen($dumpfilename, "r");
						$content = @fread($fp, filesize($dumpfilename));
						fclose($fp);
						$zip = new zipfile();
						$zip->addFile($content, basename($dumpfilename));
						$fp = @fopen(sprintf($backupfilename."-%s".'.zip', $volume), 'w');
						if(fwrite($fp, $zip->file()) !== FALSE) {
							@unlink($dumpfilename);
						}
						fclose($fp);
					}
					unset($sqldump, $zip, $content);
					$this->Messager("分卷备份: 数据文件 #{$volume} 成功创建，程序将自动继续。
", "admin.php?mod=db&code=doexport&type=".rawurlencode($type)."&saveto=server&filename=".rawurlencode($filename)."&method=multivol&sizelimit=".rawurlencode($sizelimit)."&volume=".rawurlencode($volume)."&tableid=".rawurlencode($tableid)."&startfrom=".rawurlencode($startrow)."&extendins=".rawurlencode($extendins)."&sqlcharset=".rawurlencode($sqlcharset)."&sqlcompat=".rawurlencode($sqlcompat)."&exportsubmit=yes&usehex=$usehex&usezip=$usezip");

				}
			} else {
				$volume--;
				$filelist = '<ul>';

				if($usezip == 1) {
					$zip = new zipfile();
					$zipfilename = $backupfilename.'.zip';
					$unlinks = array();
					for($i = 1; $i <= $volume; $i++) {
						$filename = sprintf($dumpfile, $i);
						$fp = @fopen($filename, "r");
						$content = @fread($fp, filesize($filename));
						fclose($fp);
						$zip->addFile($content, basename($filename));
						$unlinks[] = $filename;
						$filelist .= "<li><a href=\"$filename\">$filename</a></li>\n";
					}
					$fp = @fopen($zipfilename, 'w');
					if(fwrite($fp, $zip->file()) !== FALSE) {
						foreach($unlinks as $f) {
							Load::lib('io', 1)->DeleteFile($f);
						}
					} else {
						$this->Messager('database_export_multivol_succeed');
					}
					unset($sqldump, $zip, $content);
					fclose($fp);
					touch('./data/backup/'.$backupdir.'/index.htm');
					$filename = $zipfilename;
					$this->Messager("数据成功备份并压缩至服务器  data/backup/db/ 目录下。<br />".(true === JISHIGOU_FOUNDER ? $filelist : ""), null);
				} else {
					touch('./data/backup/'.$backupdir.'/index.htm');
					for($i = 1; $i <= $volume; $i++) {
						$filename = sprintf($usezip == 2 ? $backupfilename."-%s".'.zip' : $dumpfile, $i);
						$filelist .= "<li><a href=\"$filename\">$filename</a></li>\n";
					}
					$this->Messager("恭喜您，全部 $volume 个备份文件成功创建，备份完成。
".(true===JISHIGOU_FOUNDER ? $filelist : "<br />文件备份在  data/backup/db/ 目录下") ,null);
				}
			}

		} else {

			$tablesstr = '';
			foreach($tables as $table) {
				$tablesstr .= '"'.$table.'" ';
			}

			$query = $this->DatabaseHandler->Query("SHOW VARIABLES LIKE 'basedir'");
			list(, $mysql_base) = $query->GetRow('row');

			$dumpfile = addslashes(dirname(dirname(__FILE__))).'/'.$backupfilename.'.sql';
			@unlink($dumpfile);

			$mysqlbin = $mysql_base == '/' ? '' : addslashes($mysql_base).'bin/';
			@shell_exec($mysqlbin.'mysqldump --force --quick '.($this->DatabaseHandler->GetVersion() > '4.1' ? '--skip-opt --create-options' : '-all').' --add-drop-table'.($extendins == 1 ? ' --extended-insert' : '').''.($this->DatabaseHandler->GetVersion() > '4.1' && $sqlcompat == 'MYSQL40' ? ' --compatible=mysql40' : '').' --host="'.$this->Config['db_host'].($this->Config['db_port'] ? (is_numeric($this->Config['db_port']) ? ' --port='.$this->Config['db_port'] : ' --socket="'.$this->Config['db_port'].'"') : '').'" --user="'.$this->Config['db_user'].'" --password="'.$this->Config['db_pass'].'" "'.$this->Config['db_name'].'" '.$tablesstr.' > '.$dumpfile);

			if(is_file($dumpfile)) {

				if($usezip) {
					require_once ROOT_PATH . 'include/function/zip.func.php';
					$zip = new zipfile();
					$zipfilename = $backupfilename.'.zip';
					$fp = @fopen($dumpfile, "r");
					$content = @fread($fp, filesize($dumpfile));
					fclose($fp);
					$zip->addFile($idstring."# <?exit();?>\n ".$setnames."\n #".$content, basename($dumpfile));
					$fp = @fopen($zipfilename, 'w');
					fwrite($fp, $zip->file());
					fclose($fp);
					@unlink($dumpfile);
					touch('./data/backup/'.$backupdir.'/index.htm');
					$filename = $backupfilename.'.zip';
					unset($sqldump, $zip, $content);
					$this->Messager('database_export_zip_succeed');
				} else {
					if(is_writeable($dumpfile)) {
						$fp = @fopen($dumpfile, 'rb+');
						fwrite($fp, $idstring."# <?exit();?>\n ".$setnames."\n #");
						fclose($fp);
					}
					touch('./data/backup/'.$backupdir.'/index.htm');
					$filename = $backupfilename.'.sql';
					$this->Messager('database_export_succeed');
				}

			} else {

				$this->Messager('database_shell_fail');

			}

		}
	}


	function _fetch_table_list($tablepre = '')
	{
		$arr = explode('.', $tablepre);
		$dbname = $arr[1] ? $arr[0] : '';
		$sqladd = $dbname ? " FROM $dbname LIKE '$arr[1]%'" : "LIKE '$tablepre%'";
		!$tablepre && $tablepre = '*';
		$tables = $table = array();
		$query = $this->DatabaseHandler->query("SHOW TABLE STATUS $sqladd");
		while($table = $query->GetRow()) {
			$table['Name'] = ($dbname ? "$dbname." : '').$table['Name'];
			$tables[] = $table;
		}
		return $tables;
	}
	function _array_keys2($array, $key2) {
		$return = array();
		foreach($array as $val) {
			$return[] = $val[$key2];
		}
		return $return;
	}
	function _sql_dump_table($table, $startfrom = 0, $currsize = 0) {
		global $sizelimit, $startrow, $extendins, $sqlcompat, $sqlcharset, $dumpcharset, $usehex, $complete, $excepttables;

		$offset = 300;
		$tabledump = '';
		$tablefields = array();

		$query = $this->DatabaseHandler->Query("SHOW FULL COLUMNS FROM $table", 'SKIP_ERROR');
		if(strexists($table, 'adminsessions')) {
			return ;
		} elseif(!$query && $this->DatabaseHandler->GetLastErrorNo() == 1146) {
			return;
		} elseif(!$query) {
			$usehex = FALSE;
		} else {
			while($fieldrow = $query->GetRow()) {
				$tablefields[] = $fieldrow;
			}
		}
		if(!$startfrom) {

			$createtable = $this->DatabaseHandler->Query("SHOW CREATE TABLE $table", 'SKIP_ERROR');

			if(!$this->DatabaseHandler->GetLastErrorString()) {
				$tabledump = "DROP TABLE IF EXISTS $table;\n";
			} else {
				return '';
			}

			$create = $createtable->GetRow('row');

			if(strpos($table, '.') !== FALSE) {
				$tablename = substr($table, strpos($table, '.') + 1);
				$create[1] = str_replace("CREATE TABLE $tablename", 'CREATE TABLE '.$table, $create[1]);
			}
			$tabledump .= $create[1];

			if($sqlcompat == 'MYSQL41' && $this->DatabaseHandler->GetVersion() < '4.1') {
				$tabledump = preg_replace("/TYPE\=(.+)/", "ENGINE=\\1 DEFAULT CHARSET=".$dumpcharset, $tabledump);
			}
			if($this->DatabaseHandler->GetVersion() > '4.1' && $sqlcharset) {
				$tabledump = preg_replace("/(DEFAULT)*\s*CHARSET=.+/", "DEFAULT CHARSET=".$sqlcharset, $tabledump);
			}

			$query = $this->DatabaseHandler->Query("SHOW TABLE STATUS LIKE '$table'");
			$tablestatus = $query->GetRow();
			$tabledump .= ($tablestatus['Auto_increment'] ? " AUTO_INCREMENT=$tablestatus[Auto_increment]" : '').";\n\n";
			if($sqlcompat == 'MYSQL40' && $this->DatabaseHandler->GetVersion() >= '4.1' && $this->DatabaseHandler->GetVersion() < '5.1') {
				if($tablestatus['Auto_increment'] <> '') {
					$temppos = strpos($tabledump, ',');
					$tabledump = substr($tabledump, 0, $temppos).' auto_increment'.substr($tabledump, $temppos);
				}
				if($tablestatus['Engine'] == 'MEMORY') {
					$tabledump = str_replace('TYPE=MEMORY', 'TYPE=HEAP', $tabledump);
				}
			}
		}

		$cache_table = TABLE_PREFIX."cache_";
		if(!in_array($table, $excepttables) && $cache_table!=substr($table, 0, strlen($cache_table))) {
			$tabledumped = 0;
			$numrows = $offset;
			$firstfield = $tablefields[0];
			if($extendins == '0') {
				while($currsize + strlen($tabledump) + 500 < $sizelimit * 1000 && $numrows == $offset) {
					if($firstfield['Extra'] == 'auto_increment') {
						$selectsql = "SELECT * FROM $table WHERE $firstfield[Field] > $startfrom LIMIT $offset";
					} else {
						$selectsql = "SELECT * FROM $table LIMIT $startfrom, $offset";
					}
					$tabledumped = 1;
					$rows = $this->DatabaseHandler->Query($selectsql);
					$numfields = $rows->GetNumFields();

					$numrows = $rows->GetNumRows();

					while($row = $rows->GetRow('row')) {
						$comma = $t = '';
						for($i = 0; $i < $numfields; $i++) {
							$t .= $comma.($usehex && !empty($row[$i]) && (strexists($tablefields[$i]['Type'], 'char') || strexists($tablefields[$i]['Type'], 'text')) ? '0x'.bin2hex($row[$i]) : '\''.mysql_escape_string($row[$i]).'\'');
							$comma = ',';
						}
						if(strlen($t) + $currsize + strlen($tabledump) + 500 < $sizelimit * 1000) {
							if($firstfield['Extra'] == 'auto_increment') {
								$startfrom = $row[0];
							} else {
								$startfrom++;
							}
							$tabledump .= "INSERT INTO $table VALUES ($t);\n";
						} else {
							$complete = FALSE;
							break 2;
						}
					}
				}
			} else {
				while($currsize + strlen($tabledump) + 500 < $sizelimit * 1000 && $numrows == $offset) {
					if($firstfield['Extra'] == 'auto_increment') {
						$selectsql = "SELECT * FROM $table WHERE $firstfield[Field] > $startfrom LIMIT $offset";
					} else {
						$selectsql = "SELECT * FROM $table LIMIT $startfrom, $offset";
					}
					$tabledumped = 1;
					$rows = $this->DatabaseHandler->Query($selectsql);
					$numfields = $rows->GetNumFields();

					if($numrows = $rows->GetNumRows()) {
						$t1 = $comma1 = '';
						while($row = $rows->GetRow('row')) {
							$t2 = $comma2 = '';
							for($i = 0; $i < $numfields; $i++) {
								$t2 .= $comma2.($usehex && !empty($row[$i]) && (strexists($tablefields[$i]['Type'], 'char') || strexists($tablefields[$i]['Type'], 'text'))? '0x'.bin2hex($row[$i]) : '\''.mysql_escape_string($row[$i]).'\'');
								$comma2 = ',';
							}
							if(strlen($t1) + $currsize + strlen($tabledump) + 500 < $sizelimit * 1000) {
								if($firstfield['Extra'] == 'auto_increment') {
									$startfrom = $row[0];
								} else {
									$startfrom++;
								}
								$t1 .= "$comma1 ($t2)";
								$comma1 = ',';
							} else {
								$tabledump .= "INSERT INTO $table VALUES $t1;\n";
								$complete = FALSE;
								break 2;
							}
						}
						$tabledump .= "INSERT INTO $table VALUES $t1;\n";
					}
				}
			}

			$startrow = $startfrom;
			$tabledump .= "\n";
		}

		return $tabledump;
	}
}
?>