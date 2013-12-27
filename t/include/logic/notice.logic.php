<?php
/**
 * 邮件通知
 *
 * @author 狐狸<foxis@qq.com>
 * @version 1.0
 * @since  2010-9-9
 * @final  2010-9-9
 */
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class NoticeLogic
{

	var $Config;
	var $DatabaseHandler;

		
	function NoticeLogic()
	{
		$this->DatabaseHandler = &Obj::registry("DatabaseHandler");
		$this->Config = &Obj::registry("config");
	}


	
	function Insert_Cron($touid=0)
	{
        $touid = max(0, (int) $touid);
		$timestamp = time();

				$sql = "select * from `".TABLE_PREFIX."cron` where `touid` = '{$touid}'";
		$query = $this->DatabaseHandler->Query($sql);
		$touser = $query->GetRow();


		$sql = "select `last_notice_time`,`user_notice_time` from `".TABLE_PREFIX."members` where `uid` = '{$touid}'";
		$query = $this->DatabaseHandler->Query($sql);
		$members = $query->GetRow();

		$new_sendtime = $members['last_notice_time'] + $members['user_notice_time'];

		if($touser)
		{
			$sql = "update `".TABLE_PREFIX."cron` set `sendtime`= '{$new_sendtime}' where `touid` = '{$touid}'";
		  $this->DatabaseHandler->Query($sql);
		}
		else
		{
			$sql = "insert into `".TABLE_PREFIX."cron` (`touid`,`sendtime`) values ('{$touid}','{$new_sendtime}')";
			$this->DatabaseHandler->Query($sql);
		}


		$this->Notice_email($touid);

	}

	
	function Notice_email($touid=0)
	{
			$touid = max(0, (int) $touid);
			$timestamp = time();

						$sql = "select `uid`,`email`,`notice_at`,`notice_pm`,`notice_reply`,`user_notice_time`,`last_notice_time` from `".TABLE_PREFIX."members` where `uid` = '{$touid}'";
			$query = $this->DatabaseHandler->Query($sql);
			$members = $query->GetRow();

						$sql = "select * from `".TABLE_PREFIX."cron` where `touid` = '{$touid}'";
			$query = $this->DatabaseHandler->Query($sql);
			$crons = $query->GetRow();

						if($members['user_notice_time'] == 0)   			{
												Load::logic('task');
				$TaskLogic = new TaskLogic();
				$TaskLogic->run(($id=1));
			}

												
			if($send_return)
			{
								$sql = "update `".TABLE_PREFIX."members` set `last_notice_time`= '{$timestamp}' where `uid` = '{$touid}'";
				$this->DatabaseHandler->Query($sql);

								$sql = "delete from `".TABLE_PREFIX."cron` where `id`= '{$crons['id']}' ";
				$this->DatabaseHandler->Query($sql);
			}

	}


	
 function Send_notice($mail_to,$at_content='',$pm_content='',$reply_content='')
 {
		Load::lib('mail');

		$mail_subject = "{$this->Config[site_name]}邮件提醒";
		$mail_content = $at_content.'<br />'.$pm_content.'<br />'.$reply_content;

		$send_result = send_mail($mail_to,$mail_subject,$mail_content,array(),3,false);



		return $send_result;
 }


}
?>