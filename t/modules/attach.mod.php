<?php
/**
 *
 * 附件模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id$
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class ModuleObject extends MasterObject {

	function ModuleObject($config) {
		$this->MasterObject($config);

		$this->Execute();
	}

	
	function Execute() {
		ob_start();
		switch ($this->Code) {
			case 'download':
				$this->Download();
				break;

			default:
				$this->Code = '';
				$this->Main();
		}
		$body=ob_get_clean();

		$this->ShowBody($body);
	}

	function Main() {
		$this->Messager('正在建设中。。。', null);
	}

	function Download() {
		$uid = MEMBER_ID;
		if($uid < 1) {
			die('System error: You are not logged in or Your download URL is wrong or is expired.');
		}
						$readmod = 2;
		$downfile = get_param('downfile');
		if($downfile) {
			@list($dasize, $daid, $datime, $dadown) = explode('|', base64_decode($downfile));
			$daid = (int) $daid;
			if($daid > 0) {
				$down_attach_file = DB::fetch_first("SELECT * FROM ".DB::table('topic_attach')." WHERE id = '{$daid}' AND tid > 0");
								if(!empty($down_attach_file)){
					$MIMETypes = array(
						'doc'  => 'application/msword',
						'ppt'  => 'application/vnd.ms-powerpoint',
						'pdf'  => 'application/pdf', 
						'xls'  => 'application/vnd.ms-excel',
						'txt'  => 'text/plain',
						'rar'  => 'application/octet-stream',
						'zip'  => 'application/zip',
						'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
						'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
						'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
					);
					if(isset($MIMETypes[$down_attach_file['filetype']]))
					{
						if($down_attach_file['site_url'] && $down_attach_file['file'])
						{
							$fileurl = $down_attach_file['site_url'].'/'.str_replace('./','',$down_attach_file['file']);
							@header("Location: {$fileurl}");
						}						
						elseif(file_exists($down_attach_file['file']) && is_readable($down_attach_file['file']))
						{
							$auid = $down_attach_file['uid'];
							$score = $down_attach_file['score'];
							DB::query("update `".DB::table('topic_attach')."` set `download` = download + 1  where `id`='{$daid}'");
														DB::query("update `".DB::table('members')."` set `credits` = credits - {$score}  where `uid`='{$uid}'");
														if($auid != $uid){
								DB::query("update `".DB::table('members')."` set `credits` = credits + {$score}  where `uid`='{$auid}'");
							}
							$fileType = $MIMETypes[$down_attach_file['filetype']];
							$down_attach_file['name'] = '"'.(strtolower(str_replace('-','',$this->Config['charset'])) == 'utf8' && strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') ? urlencode($down_attach_file['name']) : $down_attach_file['name']).'"';							ob_end_clean();
							ob_start();							header('Cache-control: max-age=31536000');
							header('Expires: ' . gmdate('D, d M Y H:i:s', time()+31536000) . ' GMT');
							header('Content-Encoding: none');
							header('Content-type: '.$fileType);
							header('Content-Disposition: attachment; filename=' . $down_attach_file['name']);
							header('Content-Length: ' . filesize($down_attach_file['file']));
							if($readmod == 1 || $readmod == 3){
								if($fp = @fopen($down_attach_file['file'], 'rb')){
									@fseek($fp, 0);
									if(function_exists('fpassthru') && $readmod == 3){
										@fpassthru($fp);
									}else{
										echo @fread($fp, filesize($down_attach_file['file']));
									}
								}
								@fclose($fp);
							}else{
								@readfile($down_attach_file['file']);
							}
							@flush();
							@ob_flush();
						}
						else
						{
							echo 'System error: Sorry, The file that you want to download does not exist,Please contact the website administrator.';
						}
					}
					else
					{
						echo 'System error: Sorry, The file that you want to download is Not allowed data types.You can only download the DOC,TXT,PDF,XLS,PPT,RAR,ZIP file.';
					}
				}
				else
				{
					echo 'System error: You are not logged in or Your download URL is wrong or is expired.';
									}
			}
		}
		exit;
	}
}


?>
