<?php
session_start();
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once($yjl_tpath.'setting/face.php');
require_once('function.php');
$f='msg.php';
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
$c='';
$page_title='信箱';
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
if(isset($_GET['id']) && intval($_GET['id'])>0){
	$id=intval($_GET['id']);
	if($id==$udb['uid']){
		echo '<script type="text/javascript">location.href=\''.$f.'\';</script>';
		exit();
	}else{
		$cudb=yjl_udb($id);
		if($cudb['uid']==0){
			echo '<script type="text/javascript">location.href=\''.$f.'\';</script>';
			exit();
		}
		$uadb[$id]=$cudb;
	}
	if($_SERVER['REQUEST_METHOD']=='POST'){
		if(isset($_POST['content']) && trim($_POST['content'])!=''){
			$q_rep=sprintf('select app_key, app_secret from %s where id=%s limit 1', $dbprefix.'app', $r_main['app_id']);
			$rep=mysql_query($q_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				$app_k=$r_rep['app_key'];
				$app_s=$r_rep['app_secret'];
			}else{
				$app_a=yjl_app($r_main['site_name'], 0, $yjl_url, 'yjl', '个人微博');
				$uSQL=sprintf('update %s set app_id=%s', $yjl_dbprefix.'main', $app_a[0]);
				$result=mysql_query($uSQL) or die('');
				$app_k=$app_a[1];
				$app_s=$app_a[2];
			}
			mysql_free_result($rep);
			$content=htmlspecialchars(trim($_POST['content']),ENT_QUOTES);
			require_once('lib/jishigouapi.class.php');
			$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $app_k, $app_s, $udb['nickname'], md5($udb['nickname'].$udb['password']));
			$jsg_result=$JishiGouAPI->SendPm($uadb[$id]['nickname'], str_replace('\'', '&#039;', $_POST['content']));
			yjl_addlog('[uid]发送私信给[luid]', md5('msg|'.$id.'|'.$user_id), 1, $id, $user_id);
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">alert(\'私信已发送。\');</script>';
		}
		echo '<script type="text/javascript">location.href=\''.$f.'?id='.$id.'\';</script>';
		exit();
	}
	$q_res=sprintf('select * from %s where ((msgfromid=%s and msgtoid=%s) or (msgtoid=%s and msgfromid=%s)) and folder=%s order by dateline desc', $dbprefix.'pms', $id, $user_id, $id, $user_id, yjl_SQLString('inbox', 'text'));
	$a_res=mysql_query($q_res) or die('');
	$tr_res=mysql_num_rows($a_res);
	$c.='<div class="pr_cont mail_tit">
				<p>收件人：<a href="user-'.$id.'.html">'.($uadb[$id]['isnc']>0?$uadb[$id]['nc']:$uadb[$id]['nickname']).'</a></p>
			</div>
			<div class="act_cont clearfix">
				<div class="mail_cont">
					<form method="post" action="" class="main_form">
					<table>
					<tr>
						<td><textarea name="content"></textarea></td>
					</tr>
					<tr>
						<td align="right"><input type="submit" class="submit sub_reg" value="'.($tr_res>0?'回 复':'发 送').'" /></td>
					</tr>
					</table>
					</form>';
	if($tr_res>0){
		$c.='<ul class="list_reply">';
		$tp_res=ceil($tr_res/$p_size);
		if($page>$tp_res)$page=$tp_res;
		$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
		$res=mysql_query($q_l_res) or die('');
		$r_res=mysql_fetch_assoc($res);
		do{
			if($r_res['msgtoid']==$user_id && $r_res['new']>0){
				$uSQL=sprintf('update %s set newpm=newpm-1 where uid=%s and newpm>0', $dbprefix.'members', $user_id);
				$result=mysql_query($uSQL) or die(mysql_error());
				$uSQL=sprintf('update %s set new=0 where pmid=%s', $dbprefix.'pms', $r_res['pmid']);
				$result=mysql_query($uSQL) or die(mysql_error());
				$uSQL=sprintf('update %s set is_new=0 where plid=%s and uid=%s', $dbprefix.'pms_list', $r_res['plid'], $user_id);
				$result=mysql_query($uSQL) or die(mysql_error());
			}
			if(isset($_GET['did']) && $_GET['did']==$r_res['pmid'] && $r_res['msgtoid']==$user_id){
				$dSQL=sprintf('delete from %s where pmid=%s', $yjl_dbprefix.'pms', $r_res['pmid']);
				$result=mysql_query($dSQL) or die('');
				echo '<script type="text/javascript">location.href=\''.$f.'?id='.$id.'&p='.$page.'\';</script>';
				exit();
			}
			$c.='<li'.($r_res['new']>0?'':' class="already"').'>
							<div class="pic_text clearfix">
								<a href="user-'.$r_res['msgfromid'].'.html"><img src="'.yjl_face($r_res['msgfromid'], $uadb[$r_res['msgfromid']]['face']).'" /></a>
								<p class="memb"><a href="user-'.$r_res['msgfromid'].'.html">'.($r_res['msgfromid']==$user_id?'我':$uadb[$r_res['msgfromid']]['nc']).'</a><span>'.yjl_wbdate($r_res['dateline']).($r_res['msgfromid']==$user_id?'':' | <a href="?id='.$id.'&amp;did='.$r_res['pmid'].'&amp;p='.$page.'" style="color: #f00;" onclick="if(!confirm(\'确认删除？\'))return false;">删除</a>').'</span></p>
								<p>'.yjl_wbdecode($r_res['message']).'</p>
							</div>
						</li>';
		}while($r_res=mysql_fetch_assoc($res));
		mysql_free_result($res);
		$c.='</ul>';
		if($tp_res>1)$c.=yjl_newpage($page, $tp_res);
	}
	mysql_free_result($a_res);
	$c.='</div><br class="clear" /><br /><br /></div>';
}elseif(isset($_GET['write']) && $_GET['write']==1){
	$q_rep=sprintf('select b.uid, b.face, b.nickname, c.nc from %s as a, %s as b, %s as c where a.uid=%s and a.buddyid=b.uid and b.uid=c.uid order by a.dateline desc', $dbprefix.'buddys', $dbprefix.'members', $yjl_dbprefix.'members', $udb['uid']);
	$rep=mysql_query($q_rep) or die('');
	$r_rep=mysql_fetch_assoc($rep);
	$c_fri=mysql_num_rows($rep);
	if($c_fri>0){
		do{
			$a_fri[$r_rep['uid']]=$r_rep;
		}while($r_rep=mysql_fetch_assoc($rep));
	}
	mysql_free_result($rep);
	if($_SERVER['REQUEST_METHOD']=='POST'){
		if(isset($_POST['content']) && trim($_POST['content'])!=''){
			if($c_fri>0){
				foreach($a_fri as $k=>$v){
					if(isset($_POST['u_'.$k]) && $_POST['u_'.$k]==1){
						$a_toid[$k]=$v['nickname'];
						$a_ton[$v['nc']]=1;
					}
				}
			}
			if(isset($_POST['touser']) && trim($_POST['touser'])!=''){
				$touser=htmlspecialchars(trim($_POST['touser']),ENT_QUOTES);
				$touser=str_replace('；', ';', $touser);
				$a_to0=explode(';', $touser);
				foreach($a_to0 as $v){
					if(trim($v)!='' && !isset($a_ton[trim($v)]) && trim($v)!=$udb['nc'])$a_to[trim($v)]=trim($v);
				}
				if(isset($a_to) && count($a_to)>0){
					foreach($a_to as $v){
						$q_rep=sprintf('select a.uid, a.nickname from %s as a, %s as b where a.uid=b.uid and b.nc=%s limit 1', $dbprefix.'members', $yjl_dbprefix.'members', yjl_SQLString($v, 'text'));
						$rep=mysql_query($q_rep) or die('');
						$r_rep=mysql_fetch_assoc($rep);
						if(mysql_num_rows($rep)>0)$a_toid[$r_rep['uid']]=$r_rep['nickname'];
						mysql_free_result($rep);
					}
				}
			}
			if(isset($a_toid) && count($a_toid)>0){
				$q_rep=sprintf('select app_key, app_secret from %s where id=%s limit 1', $dbprefix.'app', $r_main['app_id']);
				$rep=mysql_query($q_rep) or die('');
				$r_rep=mysql_fetch_assoc($rep);
				if(mysql_num_rows($rep)>0){
					$app_k=$r_rep['app_key'];
					$app_s=$r_rep['app_secret'];
				}else{
					$app_a=yjl_app($r_main['site_name'], 0, $yjl_url, 'yjl', '个人微博');
					$uSQL=sprintf('update %s set app_id=%s', $yjl_dbprefix.'main', $app_a[0]);
					$result=mysql_query($uSQL) or die('');
					$app_k=$app_a[1];
					$app_s=$app_a[2];
				}
				mysql_free_result($rep);
				require_once('lib/jishigouapi.class.php');
				$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $app_k, $app_s, $udb['nickname'], md5($udb['nickname'].$udb['password']));
				foreach($a_toid as $k=>$v){
					yjl_addlog('[uid]发送私信给[luid]', md5('msg|'.$k.'|'.$user_id), 1, $k, $user_id);
					$jsg_result=$JishiGouAPI->SendPm($v, str_replace('\'', '&#039;', $_POST['content']));
				}
				echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">alert(\'私信已发送。\');</script>';
			}
		}
		echo '<script type="text/javascript">location.href=\''.$f.'?write=1\';</script>';
		exit();
	}
	$ic='你可以直接输入好友名（;分隔多个好友）'.($c_fri>0?'或者选择好友':'');
	$js_c='
	$(\'#touser\').focus(function(){
		if($(this).attr(\'jq_ise\')==\'1\')$(this).val(\'\');
	}).blur(function(){
		if($(this).val()!=\'\'){
			$(this).attr(\'jq_ise\', \'0\');
		}else{
			$(this).attr(\'jq_ise\', \'1\');
			$(this).val(\''.$ic.'\');
		}
	})
	$("a[jq_flink]").click(function(){
		var id=$(this).attr(\'jq_flink\');
		if($(\'#u_\'+id).val()==\'0\'){
			$(\'#u_\'+id).val(\'1\');
			$(\'#fimg_\'+id).css(\'border-color\', \'#2097dd\');
		}else{
			$(\'#u_\'+id).val(\'0\');
			$(\'#fimg_\'+id).css(\'border-color\', \'#fff\');
		}
		return false;
	});
	$(\'#submit_bt\').click(function(){
		if($(\'#touser\').attr(\'jq_ise\')==\'1\')$(\'#touser\').val(\'\');
		$(\'#login_form\').submit();
	});';
	$c.='<div class="title">
				<h3>写私信</h3>
			</div>
			<div class="act_cont wt_mail clearfix">
				<form method="post" class="main_form" id="login_form" action="">
					<table>
						<tr>
							<th width="70">收件人<b></b></th>
							<td><input type="text" class="text" name="touser" id="touser" jq_ise="1" value="'.$ic.'" /></td>
						</tr>';
	if($c_fri>0){
		$c.='<tr><th></th><td><div class="friend clearfix"><ul>';
		foreach($a_fri as $k=>$v)$c.='<li><a href="#" jq_flink="'.$k.'" title="'.$v['nc'].'"><img id="fimg_'.$k.'" src="'.yjl_face($k, $v['face']).'" style="border: 2px solid #fff;" /><br />'.yjl_substrs($v['nc'], 4).'</a><input type="hidden" name="u_'.$k.'" id="u_'.$k.'" value="0"/></li>';
		$c.='</ul></div></td></tr>';
	}
	$c.='<tr>
							<th valign="top">内容<b></b></th>
							<td><textarea name="content"></textarea></td>
						</tr>
						<tr>
							<td valign="top"></td>
							<td><input type="submit" value="发送" class="submit sub_reg" id="submit_bt" /></td>
						</tr>
					</table>
				</form>
				<br class="clear" /><br /><br />
			</div>';
}elseif(isset($_GET['t']) && $_GET['t']=='send'){
	$c.='<div class="title">
				<h3>信箱</h3>
				<div class="sub_title">
					<a href="'.$f.'">收件箱</a>|<span>已发送</span>
				</div>
				<div class="flt_rt"><a href="?write=1" class="btn bt_smgray">写 信</a></div>
			</div>
			<div class="act_cont clearfix"><ul class="list_friend">';
	$q_res=sprintf('select a.*, b.face, c.nc, c.qx from %s as a, %s as b, %s as c where a.msgtoid=b.uid and a.msgfromid=%s and a.folder=%s and b.uid=c.uid order by a.dateline desc', $dbprefix.'pms', $dbprefix.'members', $yjl_dbprefix.'members', $user_id, yjl_SQLString('inbox', 'text'));
	$a_res=mysql_query($q_res) or die('');
	$tr_res=mysql_num_rows($a_res);
	if($tr_res>0){
		$tp_res=ceil($tr_res/$p_size);
		if($page>$tp_res)$page=$tp_res;
		$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
		$res=mysql_query($q_l_res) or die('');
		$r_res=mysql_fetch_assoc($res);
		do{
			$c.='<li'.($r_res['new']>0?'':' class="already"').'><div class="flt_lt">
							<div class="pic_text clearfix">
								<a href="user-'.$r_res['msgfromid'].'.html"><img src="'.yjl_face($r_res['msgtoid'], $r_res['face']).'" /></a>
								<p class="memb">我发送给 <a href="?id='.$r_res['msgfromid'].'">'.$r_res['nc'].'</a>：</p>
								<p>'.yjl_wbdecode($r_res['message']).'</p>
							</div>
						</div>
						<div class="flt_rt">
							<p><span>'.yjl_wbdate($r_res['dateline']).'</span></p>
							<p><a href="?id='.$r_res['msgfromid'].'">查看</a></p>
						</div>
					</li>';
		}while($r_res=mysql_fetch_assoc($res));
		mysql_free_result($res);
		$c.='</ul>';
		if($tp_res>1)$c.=yjl_newpage($page, $tp_res);
	}else{
		$c.='<li>没有已发送私信</li></ul>';
	}
	mysql_free_result($a_res);
	$c.='<br class="clear" /><br /><br /></div>';
}else{
	$c.='<div class="title">
				<h3>信箱</h3>
				<div class="sub_title">
					<span>收件箱</span>|<a href="?t=send">已发送</a>
				</div>
				<div class="flt_rt"><a href="?write=1" class="btn bt_smgray">写 信</a></div>
			</div>
			<div class="act_cont clearfix"><ul class="list_friend">';
	$q_res=sprintf('select a.*, b.face, c.nc, c.qx from %s as a, %s as b, %s as c where a.msgfromid=b.uid and a.msgtoid=%s and a.folder=%s and b.uid=c.uid order by a.dateline desc', $dbprefix.'pms', $dbprefix.'members', $yjl_dbprefix.'members', $user_id, yjl_SQLString('inbox', 'text'));
	$a_res=mysql_query($q_res) or die('');
	$tr_res=mysql_num_rows($a_res);
	if($tr_res>0){
		$tp_res=ceil($tr_res/$p_size);
		if($page>$tp_res)$page=$tp_res;
		$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
		$res=mysql_query($q_l_res) or die('');
		$r_res=mysql_fetch_assoc($res);
		do{
			if($r_res['new']>0){
				$uSQL=sprintf('update %s set newpm=newpm-1 where uid=%s and newpm>0', $dbprefix.'members', $user_id);
				$result=mysql_query($uSQL) or die(mysql_error());
				$uSQL=sprintf('update %s set new=0 where pmid=%s', $dbprefix.'pms', $r_res['pmid']);
				$result=mysql_query($uSQL) or die(mysql_error());
				$uSQL=sprintf('update %s set is_new=0 where plid=%s and uid=%s', $dbprefix.'pms_list', $r_res['plid'], $user_id);
				$result=mysql_query($uSQL) or die(mysql_error());
			}
			if(isset($_GET['did']) && $_GET['did']==$r_res['pmid']){
				$dSQL=sprintf('delete from %s where pmid=%s', $yjl_dbprefix.'pms', $r_res['pmid']);
				$result=mysql_query($dSQL) or die('');
				echo '<script type="text/javascript">location.href=\''.$f.'?p='.$page.'\';</script>';
				exit();
			}
			$c.='<li'.($r_res['new']>0?'':' class="already"').'><div class="flt_lt">
							<div class="pic_text clearfix">
								<a href="user-'.$r_res['msgfromid'].'.html"><img src="'.yjl_face($r_res['msgfromid'], $r_res['face']).'" /></a>
								<p class="memb"><a href="?id='.$r_res['msgfromid'].'">'.$r_res['nc'].'</a><span>发送给我：</span></p>
								<p>'.yjl_wbdecode($r_res['message']).'</p>
							</div>
						</div>
						<div class="flt_rt">
							<p><span>'.yjl_wbdate($r_res['dateline']).'</span></p>
							<p><a href="?id='.$r_res['msgfromid'].'">回复</a>|<a href="?did='.$r_res['pmid'].'&amp;p='.$page.'" style="color: #f00;" onclick="if(!confirm(\'确认删除？\'))return false;">删除</a></p>
						</div>
					</li>';
		}while($r_res=mysql_fetch_assoc($res));
		mysql_free_result($res);
		$c.='</ul>';
		if($tp_res>1)$c.=yjl_newpage($page, $tp_res);
	}else{
		$c.='<li>没有私信</li></ul>';
	}
	mysql_free_result($a_res);
	$c.='<br class="clear" /><br /><br /></div>';
}
echo yjl_gehtml(yjl_userl($cuid, 'yx'), $c, '信箱');
?>