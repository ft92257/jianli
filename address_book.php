<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once('function.php');
$f='address_book.php';
if($udb['uid']>0){
	if(($udb['iswc']!=1 && ($udb['qx']==5 || $udb['qx']==6)) || $udb['isnc']==0){
		echo '<script type="text/javascript">location.href=\'./\';</script>';
		exit();
	}
}else{
	echo '<script type="text/javascript">location.href=\'login.php\';</script>';
	exit();
}
$cuid=$user_id;
if($xqid>0){
	$q_res=sprintf('select * from %s where xqid=%s and iskf=1 limit 1', $yjl_dbprefix.'xq', $xqid);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$xqdb=$r_res;
		$c_l1id=$xqdb['l1id'];
	}
	mysql_free_result($res);
}
$js_c='';
$page_title=$uadb[$cuid]['nc'].' 装修通讯录';
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
$c='<div class="title">
				<h3>装修通讯录</h3>
				<div class="flt_rt"><a href="#" class="btn bt_gray" rel="#overlay_newct" id="open_ldiv">新建联系人</a></div>
			</div>
			<br />
			<div class="act_cont main_table clearfix">';
$q_res=sprintf('select * from %s where uid=%s order by datetime desc', $yjl_dbprefix.'txl', $cuid);
$a_res=mysql_query($q_res) or die('');
$tr_res=mysql_num_rows($a_res);
if($tr_res>0){
	$c.='<table class="tdhover">
					<thead>
					<tr>
						<th align="center" width="80">姓名</th>
						<th align="left" width="150">电话</th>
						<th align="center" width="80">类别</th>
						<th align="left">地址</th>
						<th align="center" width="80">备注</th>
						<th align="left"	width="150"></th>
					</tr>
					</thead>
					<tbody>';
	$tp_res=ceil($tr_res/$p_size);
	if($page>$tp_res)$page=$tp_res;
	$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
	$res=mysql_query($q_l_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	do{
		if(isset($_GET['eid']) && $_GET['eid']==$r_res['txlid']){
			$edb=$r_res;
		}elseif(isset($_GET['did']) && $_GET['did']==$r_res['txlid']){
			$dSQL=sprintf('delete from %s where txlid=%s', $yjl_dbprefix.'txl', $r_res['txlid']);
			$result=mysql_query($dSQL) or die('');
			echo '<script type="text/javascript">location.href=\''.$f.'?p='.$page.'\';</script>';
			exit();
		}
		$c.='<tr>
						<td align="center">'.$r_res['name'].'</td>
						<td>'.($r_res['mobile']!=''?$r_res['mobile']:'-').'</td>
						<td align="center">'.$a_txlc[$r_res['cid']].'</td>
						<td>'.$r_res['address'].'</td>
						<td>'.$r_res['bz'].'</td>
						<td align="right"><div class="edit"><a href="?eid='.$r_res['txlid'].'&amp;p='.$page.'">编辑</a>|<a href="?did='.$r_res['txlid'].'&amp;p='.$page.'" onclick="if(!confirm(\'确认删除？\'))return false;" style="color: #f00;">删除</a></div></td>
					</tr>';
	}while($r_res=mysql_fetch_assoc($res));
	mysql_free_result($res);
	$c.='</tbody></table>';
	if($tp_res>1)$c.=yjl_newpage($page, $tp_res);
}
mysql_free_result($a_res);
$c.='<br class="clear" /><br /><br /></div>';
if($_SERVER['REQUEST_METHOD']=='POST'){
	if(isset($_POST['name']) && trim($_POST['name'])!=''){
		$name=htmlspecialchars(trim($_POST['name']),ENT_QUOTES);
		$mobile=htmlspecialchars(trim($_POST['mobile']),ENT_QUOTES);
		$address=htmlspecialchars(trim($_POST['address']),ENT_QUOTES);
		$bz=htmlspecialchars(trim($_POST['bz']),ENT_QUOTES);
		if(isset($edb)){
			$uSQL=sprintf('update %s set cid=%s, name=%s, mobile=%s, address=%s, bz=%s where txlid=%s', $yjl_dbprefix.'txl',
				$_POST['cid'],
				yjl_SQLString($name, 'text'),
				yjl_SQLString($mobile, 'text'),
				yjl_SQLString($address, 'text'),
				yjl_SQLString($bz, 'text'),
				$edb['txlid']);
			$result=mysql_query($uSQL) or die('');
		}else{
			$iSQL=sprintf('insert into %s (uid, cid, name, mobile, address, bz, datetime) values (%s, %s, %s, %s, %s, %s, %s)', $yjl_dbprefix.'txl',
				$user_id,
				$_POST['cid'],
				yjl_SQLString($name, 'text'),
				yjl_SQLString($mobile, 'text'),
				yjl_SQLString($address, 'text'),
				yjl_SQLString($bz, 'text'),
				time());
			$result=mysql_query($iSQL) or die('');
		}
		echo '<script type="text/javascript">location.href=\''.$f.'?p='.$page.'\';</script>';
	}else{
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">alert(\'请输入姓名。\');location.href=\''.$f.'?p='.$page.(isset($edb)?'&eid='.$edb['txlid']:'').'\';</script>';
	}
	exit();
}
$c.='<div class="overlay" id="overlay_newct">';
if(isset($edb)){
	$c.='<h3>修改联系人：'.$edb['name'].'</h3>
		<div class="overlay_cont">
			<form method="post" class="main_form" name="form1" action="" onsubmit="if(document.form1.name.value==\'\'){alert(\'请输入姓名。\');return false;}">
				<table>
				<tr>
					<th width="50">类别</th>
					<td><select name="cid">';
	foreach($a_txlc as $k=>$v)$c.='<option value="'.$k.'"'.($edb['cid']==$k?' selected="selected"':'').'>'.$v.'</option>';
	$c.='</select></td>
				</tr>
				<tr>
					<th>姓名</th>
					<td><input type="text" class="text" name="name" value="'.$edb['name'].'" /><span class="form_tip"><b>*</b></span></td>
				</tr>
				<tr>
					<th>电话</th>
					<td><input type="text" class="text" name="mobile" value="'.$edb['mobile'].'" /></td>
				</tr>
				<tr>
					<th>地址</th>
					<td><input type="text" class="text" name="address" value="'.$edb['address'].'" /></td>
				</tr>
				<tr>
					<th>备注</th>
					<td><input type="text" class="text" name="bz" value="'.$edb['bz'].'" /></td>
				</tr>
				<tr>
					<th></th>
					<td><input type="button" value="取 消" class="submit sub_reg" onclick="location.href=\'?p='.$page.'\';" /><input type="submit" value="修 改" class="submit sub_reg" /></td>
				</tr>
			</table>
			</form>
		</div>';
	$js_c.='
	$(\'#open_ldiv\').click();
	$(\'.close\').click(function(){
		location.href=\'?p='.$page.'\';
	});';
}else{
	$c.='<h3>新建联系人</h3>
		<div class="overlay_cont">
			<form method="post" class="main_form" name="form1" action="" onsubmit="if(document.form1.name.value==\'\'){alert(\'请输入姓名。\');return false;}">
				<table>
				<tr>
					<th width="50">类别</th>
					<td><select name="cid">';
	foreach($a_txlc as $k=>$v)$c.='<option value="'.$k.'">'.$v.'</option>';
	$c.='</select></td>
				</tr>
				<tr>
					<th>姓名</th>
					<td><input type="text" class="text" name="name" /><span class="form_tip"><b>*</b></span></td>
				</tr>
				<tr>
					<th>电话</th>
					<td><input type="text" class="text" name="mobile" /></td>
				</tr>
				<tr>
					<th>地址</th>
					<td><input type="text" class="text" name="address" /></td>
				</tr>
				<tr>
					<th>备注</th>
					<td><input type="text" class="text" name="bz" /></td>
				</tr>
				<tr>
					<th></th>
					<td><input type="submit" value="增 加" class="submit sub_reg" /></td>
				</tr>
			</table>
			</form>
		</div>';
}
$c.='</div>';
echo yjl_gehtml(yjl_userl($cuid, 'zx'), $c, '装修通讯录');
?>