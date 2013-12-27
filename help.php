<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once('function.php');
$f='help.php';
if($udb['uid']>0){
	if(($udb['iswc']!=1 && ($udb['qx']==5 || $udb['qx']==6)) || $udb['isnc']==0){
		echo '<script type="text/javascript">location.href=\'./\';</script>';
		exit();
	}
	if($udb['qx']>0){
		echo '<script type="text/javascript">location.href=\'./\';</script>';
		exit();
	}
}else{
	echo '<script type="text/javascript">location.href=\'login.php?u='.urlencode($f).'\';</script>';
	exit();
}
$page_title='请监理师到现场';
if($_SERVER['REQUEST_METHOD']=='POST'){
	if(isset($_POST['name']) && trim($_POST['name'])!='' && isset($_POST['mobile']) && trim($_POST['mobile'])!='' && isset($_POST['address']) && trim($_POST['address'])!='' && isset($_POST['content']) && trim($_POST['content'])!=''){
		$name=htmlspecialchars(trim($_POST['name']),ENT_QUOTES);
		$mobile=htmlspecialchars(trim($_POST['mobile']),ENT_QUOTES);
		$address=htmlspecialchars(trim($_POST['address']),ENT_QUOTES);
		$content=htmlspecialchars(trim($_POST['content']),ENT_QUOTES);
		$iSQL=sprintf('insert into %s (uid, name, mobile, address, content, datetime) values (%s, %s, %s, %s, %s, %s)', $yjl_dbprefix.'jlsm',
			$user_id,
			yjl_SQLString($name, 'text'),
			yjl_SQLString($mobile, 'text'),
			yjl_SQLString($address, 'text'),
			yjl_SQLString($content, 'text'),
			time());
		$result=mysql_query($iSQL) or die('');
	}
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">alert(\'信息已保存，我们会尽快和您联系！\');location.href=\''.$f.'\';</script>';
	exit();
}
$c='<h2>请监理师到现场</h2>
		<div class="main clearfix">
			<form method="post" class="main_form" action="">
				<table>
				<tbody>
					<tr>
						<th></th><td><span class="form_tip" style="margin:0;">请留下您的信息，我们会尽快和您联系</span></td>
					</tr>
					<tr>
						<th width="100">姓名<b>*</b></th>
						<td><input type="text" class="text" name="name" value="'.$udb['nc'].'" /></td>
					</tr>
					<tr>
						<th>联系方式<b>*</b></th>
						<td><input type="text" class="text" name="mobile" /></td>
					</tr>
					<tr>
						<th>施工地址<b>*</b></th>
						<td><input type="text" class="text" name="address" /></td>
					</tr>
					<tr>
						<th valign="top">简介<b>*</b></th>
						<td><textarea name="content"></textarea></td>
					</tr>
					<tr>
						<td></td>
						<td><input type="submit" class="submit sub_reg" id="submit_bt" value="提 交"/><input type="button" style="margin-left: 20px;" class="submit sub_reg" onclick="location.href=\'faq-new.html\';" value="向监理咨询"/></td>
					</tr>
				</tbody>
				</table>
			</form>
		</div>';
echo yjl_html($c, 'regist', 'regist');
?>