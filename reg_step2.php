<?php
session_start();
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
$no_getxq=1;
require_once('function.php');
$f='reg_step2.php';
$js_c='';
if($udb['uid']>0){
	if(($udb['iswc']!=1 && ($udb['qx']==5 || $udb['qx']==6)) || $udb['isnc']==0){
		echo '<script type="text/javascript">location.href=\'./\';</script>';
		exit();
	}
}else{
	echo '<script type="text/javascript">location.href=\'./\';</script>';
	exit();
}
$page_title='装修意向调查';
if($_SERVER['REQUEST_METHOD']=='POST'){
	$ys=$_POST['ys'];
	$mj=$udb['mj'];
	$fxid=$_POST['fxid'];
	$fg=isset($_POST['fg'])?$_POST['fg']:0;
	$uSQL=sprintf('update %s set fxid=%s, ys=%s, fg=%s, mj=%s where uid=%s', $yjl_dbprefix.'members',
		$fxid,
		$ys,
		$fg,
		$mj,
		$udb['uid']);
	$result=mysql_query($uSQL) or die($uSQL);
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">location.href=\'user-'.$udb['uid'].'.html\';</script>';
	exit();
}
$fxc=yjl_fxop($udb['xqid'], $udb['fxid']);
$c='<div class="regist_step">
			<div class="step current"><span>1</span>个人资料完善</div>
			<div class="step current"><span>2</span>装修意向调查</div>
			<div class="right">轻松两步<br />让我们更好的为你服务</div>
		</div>
		<div class="main clearfix">
			<form method="post" class="main_form">
				<table>
				<tbody>
					<tr>
						<th width="100">预算<b></b></th>
						<td><select name="ys"><option value="0">选择预算</option>';
foreach($a_ys as $k=>$v)$c.='<option value="'.$k.'"'.($udb['ys']==$k?' selected="selected"':'').'>'.$v.'</option>';
$c.='</select>';
if($fxc[0]>0)$c.='</td>
					</tr>
					<tr>
						<th>户型<b></b></th>
						<td>';
$c.=$fxc[1].'</td>
					</tr>
					<tr>
						<th valign="top">风格<b></b></th>
						<td><ul class="list_style">';
foreach($a_fg as $k=>$v)$c.='<li><input type="radio" name="fg" class="radio" id="style'.$k.'" value="'.$k.'"'.($udb['fg']==$k?' checked="checked"':'').'><label for="style'.$k.'">'.$v.'</label></li>';
$c.='</ul>
						</td>
					</tr>
					<tr>
						<td></td>
						<td><input type="submit" class="submit sub_reg" value="下一步"/></td>
					</tr>
				</tbody>
				</table>
			</form>
			<div class="jump_step"><a href="user-'.$udb['uid'].'.html">跳过</a>以后填写，直接进入我的首页</div>
		</div>';
echo yjl_html($c, 'regist', 'regist');
?>