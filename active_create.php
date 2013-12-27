<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once($yjl_tpath.'setting/face.php');
require_once('function.php');
$f='active_create.php';
if($udb['uid']>0){
	if(($udb['iswc']!=1 && ($udb['qx']==5 || $udb['qx']==6)) || $udb['isnc']==0){
		echo '<script type="text/javascript">location.href=\'./\';</script>';
		exit();
	}
}else{
	echo '<script type="text/javascript">location.href=\'login.php?u='.urlencode($f.'?xqid='.$xqid).'\';</script>';
	exit();
}
if($udb['qx']==0){
	if($udb['xqid']==0 && $udb['isxg']==0){
		echo '<script type="text/javascript">location.href=\'./\';</script>';
		exit();
	}elseif($udb['xqid']>0 && $udb['xqid']!=$xqid){
		echo '<script type="text/javascript">location.href=\''.$f.'?xqid='.$udb['xqid'].'\';</script>';
		exit();
	}
}
if($xqid==0 && $udb['isxg']==0 && $udb['qx']<10){
	echo '<script type="text/javascript">location.href=\'estate-4.html\';</script>';
	exit();
}
$page_title=($xqid>0?$xqdb['name'].' ':'').'创建活动';
$js_c='';
$c='<div class="main_left">
			<div class="vilr_nav clearfix">
				<h2 class="h2"><span>创建活动</span></h2>
			</div>
			<div class="box1">';
if(isset($_GET['id']) && intval($_GET['id'])>0){
	$hdid=intval($_GET['id']);
	$q_res=sprintf('select * from %s where (xqid=%s or xqid=0) and hdid=%s and uid=%s', $yjl_dbprefix.'hd', $xqid, $hdid, $user_id);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		if(isset($_GET['t']) && $_GET['t']=='invite'){
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
				if($c_fri>0){
					foreach($a_fri as $k=>$v){
						if(isset($_POST['u_'.$k]) && $_POST['u_'.$k]==1){
							$a_toid[$k]=$k;
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
							if(mysql_num_rows($rep)>0)$a_toid[$r_rep['uid']]=$r_rep['uid'];
							mysql_free_result($rep);
						}
					}
				}
				if(isset($a_toid) && count($a_toid)>0){
					foreach($a_toid as $v){
						yjl_addlog('[uid]邀请[luid]参加活动：<a href="active-'.$xqid.'-'.$hdid.'.html">'.$r_res['name'].'</a>', md5('hdyq|'.$v.'|'.$user_id.'|'.$hdid), 0, $v, $user_id);
					}
					echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">alert(\'邀请已发送。\');</script>';
				}
				echo '<script type="text/javascript">location.href=\'active-'.$xqid.'-'.$hdid.'.html\';</script>';
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
			$c.='<ul class="step01">
					<li><span class="mn_ico ico43"></span><br />1 填写活动信息</li>
					<li><span class="mn_ico ico44"></span><br />2 上传活动海报</li>
					<li class="bg_no current"><span class="mn_ico ico45"></span><br />3 完成创建，邀请好友</li>
				</ul>

				<div class="end">
					<p>'.$r_res['name'].' 创建成功<br />你可以发送邀请让好友们来参加你的活动</p>
				</div>
				<br />
				<div class="wt_mail clearfix">
				<form method="post" class="main_form" id="login_form" action="">
					<table>
						<tr>
							<th width="70">好友<b></b></th>
							<td><input type="text" class="text" name="touser" id="touser" jq_ise="1" value="'.$ic.'" /></td>
						</tr>';
			if($c_fri>0){
				$c.='<tr><th></th><td><div class="friend clearfix"><ul>';
				foreach($a_fri as $k=>$v)$c.='<li><a href="#" jq_flink="'.$k.'" title="'.$v['nc'].'"><img id="fimg_'.$k.'" src="'.yjl_face($k, $v['face']).'" style="border: 2px solid #fff;" /><br />'.yjl_substrs($v['nc'], 4).'</a><input type="hidden" name="u_'.$k.'" id="u_'.$k.'" value="0"/></li>';
				$c.='</ul></div></td></tr>';
			}
			$c.='<tr>
							<td valign="top"></td>
							<td><a href="active-'.$xqid.'-'.$hdid.'.html">跳过</a><input type="submit" value="发送邀请" class="submit sub_reg" id="submit_bt" /></td>
						</tr>
					</table>
				</form>
				<br class="clear" /><br /><br />
			</div>';
		}else{
			$isupimg=1;
			$js_a='upimg_a_7(response, \'tt_iv\');';
			$js_s=', \'m\':\'hd\', \'hdid\':\''.$r_res['hdid'].'\'';
			$js_c.=yjl_uploadjs($js_a, '', $js_s, 'uploadxzhdimg.php', 'hdtt_upload');
			$c.='<ul class="step01">
					<li><span class="mn_ico ico43"></span><br />1 填写活动信息</li>
					<li class="current"><span class="mn_ico ico44"></span><br />2 上传活动海报</li>
					<li class="bg_no"><span class="mn_ico ico45"></span><br />3 完成创建，邀请好友</li>
				</ul>
				<div class="cut_pic clearfix"><div id="tt_uv">'.yjl_uploadv_0(1, 'hdtt_upload', 'j/uploadxzhdimg.php', '<input type="hidden" name="is_nu" value="1"/><input type="hidden" name="hdid" value="'.$r_res['hdid'].'"/><input type="hidden" name="m" value="hd"/>').'</div><div id="tt_iv" style="margin-top: 20px;">'.($r_res['url']!=''?'<img src="'.$r_res['url'].'" alt="" width="'.$a_wh_xzhd[0].'" height="'.$a_wh_xzhd[1].'"/>':'').'</div>
				</div>
				<div class="bt_crte">
					<a href="active-'.$xqid.'-'.$hdid.'.html">跳过</a><a href="?xqid='.$xqid.'&amp;id='.$hdid.'&amp;t=invite" class="btn bt_nomgray">下一步</a>
				</div>';
		}
	}else{
		echo '<script type="text/javascript">location.href=\'./\';</script>';
		exit();
	}
	mysql_free_result($res);
}else{
	if($_SERVER['REQUEST_METHOD']=='POST'){
		if(isset($_POST['name']) && trim($_POST['name'])!=''){
			$name=htmlspecialchars(trim($_POST['name']),ENT_QUOTES);
			$content=trim($_POST['content']);
			$jluid=$udb['qx']==5?$user_id:0;
			$isgf=0;
			if(($udb['isxg']>0 || $udb['qx']==10) && isset($_POST['isgf']) && $_POST['isgf']==1)$isgf=1;
			$hxqid=$xqid;
			if(($udb['isxg']>0 || $udb['qx']==10) && isset($_POST['isnoxq']) && $_POST['isnoxq']==1)$hxqid=0;
			$isxzrs=$_POST['isxzrs'];
			$xzrs=intval($_POST['xzrs'])>0?intval($_POST['xzrs']):0;
			if($xzrs==0)$isxzrs=0;
			$sjtid=$_POST['sjtid'];
			$datetime=mktime(0,0,0,$_POST['datetime_m'],$_POST['datetime_d'],$_POST['datetime_y']);
			$metime=mktime(0,0,0,$_POST['datetime1_m'],$_POST['datetime1_d'],$_POST['datetime1_y']);
			if($metime<=$datetime && $sjtid==1)$sjtid=0;
			$cs=intval($_POST['cs'])>0?intval($_POST['cs']):1;
			if($cs<=1 && $sjtid==2)$sjtid=0;
			switch($sjtid){
				case 2:
					$etime=$datetime+86400-1;
					$etime+=($cs-1)*86400*7;
					break;
				case 1:
					$etime=$metime+86400-1;
					break;
				default:
					$etime=$datetime+86400-1;
					break;
			}
			$address=htmlspecialchars(trim($_POST['address']),ENT_QUOTES);
			$iSQL=sprintf('insert into %s (uid, jluid, xqid, name, content, c_cy, isgf, isxzrs, xzrs, datetime, sjtid, cs, etime, address, lasttime) values (%s, %s, %s, %s, %s, 1, %s, %s, %s, %s, %s, %s, %s, %s, %s)', $yjl_dbprefix.'hd',
				$user_id,
				$jluid,
				$hxqid,
				yjl_SQLString($name, 'text'),
				yjl_SQLString($content, 'text'),
				$isgf,
				$isxzrs,
				$xzrs,
				$datetime,
				$sjtid,
				$cs,
				$etime,
				yjl_SQLString($address, 'text'),
				time());
			$result=mysql_query($iSQL) or die('');
			$hdid=mysql_insert_id();
			yjl_addlog('[uid]创建活动：<a href="active-'.$hxqid.'-'.$hdid.'.html">'.$name.'</a>', md5('cjhd|'.$user_id.'|'.$user_id.'|'.$hdid));
			if($hxqid>0){
				$uSQL=sprintf('update %s set c_hd=c_hd+1 where xqid=%s', $yjl_dbprefix.'xq', $hxqid);
				$result=mysql_query($uSQL) or die('');
			}
			$iSQL=sprintf('insert into %s (uid, hdid, datetime) values (%s, %s, %s)', $yjl_dbprefix.'hd_user',
				$user_id,
				$hdid,
				time());
			$result=mysql_query($iSQL) or die('');
			$app_a=yjl_app('活动 '.$name, $hdid, $yjl_url.'active-'.$hxqid.'-'.$hdid.'.html', 'hd');
			$uSQL=sprintf('update %s set app_id=%s where hdid=%s', $yjl_dbprefix.'hd', $app_a[0], $hdid);
			$result=mysql_query($uSQL) or die('');
			require_once('lib/jishigouapi.class.php');
			$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $app_a[1], $app_a[2], $udb['nickname'], md5($udb['nickname'].$udb['password']));
			$content='添加活动：'.$_POST['name'].' '.$yjl_url.'active-'.$hxqid.'-'.$hdid.'.html';
			$jsg_result=$JishiGouAPI->AddTopic($content);
			if(!isset($jsg_result['error']) && isset($jsg_result['result']['tid'])){
				$tid=$jsg_result['result']['tid'];
				$uSQL=sprintf('update %s set tid=%s, uname=%s where hdid=%s', $yjl_dbprefix.'hd', $tid, yjl_SQLString($udb['username'], 'text'), $hdid);
				$result=mysql_query($uSQL) or die('');
				yjl_uwb($user_id, $content.' '.$yjl_url.'active-'.$hxqid.'-'.$hdid.'.html', $tid);
			}
			echo '<script type="text/javascript">location.href=\''.$f.'?xqid='.$hxqid.'&id='.$hdid.'\';</script>';
		}else{
			echo '<script type="text/javascript">location.href=\''.$f.'?xqid='.$xqid.'\';</script>';
		}
		exit();
	}
	$is_mce=1;
	$js_c.='
	$(\'#form_text\').tinymce({
		script_url : \'lib/tiny_mce/tiny_mce.js\',
		theme : "advanced",
		plugins : "inlinepopups,paste,xhtmlxtras",
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,forecolor,backcolor",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "none",
		theme_advanced_resizing : false,
		language : "cn",
	});';
	$c.='<ul class="step01">
					<li class="current"><span class="mn_ico ico43"></span><br />1 填写活动信息</li>
					<li><span class="mn_ico ico44"></span><br />2 上传活动海报</li>
					<li class="bg_no"><span class="mn_ico ico45"></span><br />3 完成创建，邀请好友</li>
				</ul>
				<form class="main_form cre_act" method="post" action="">
					<table>
						<tr>
							<th width="100">标题<b></b></th>
							<td><input type="text" class="text" name="name"></td>
						</tr>'.(($udb['isxg']>0 || $udb['qx']==10)?'
						<tr>
							<th></th>
							<td><input type="checkbox" class="radio" name="isgf" value="1" checked="checked"/>易监理官方活动</td>
						</tr>':'').'
						<tr>
							<th>人数<b></b></th>
							<td><input type="radio" name="isxzrs" value="0" onclick="$(\'#xzrs_i\').hide();" checked="checked" class="radio" id="style01"><label for="style01">不限制</label>
								<input type="radio" name="isxzrs" value="1" onclick="$(\'#xzrs_i\').show();" class="radio" id="style02"><label for="style02">限制人数</label>
								<input type="text" class="codetext" id="xzrs_i" name="xzrs" value="10" style="display: none;"/>
							</td>
						</tr>
						<tr>
							<th>时间<b></b></th>
							<td><input type="radio" name="sjtid" value="0" onclick="$(\'#lxqs\').hide();$(\'#lxspan_1\').hide();$(\'#lxspan_2\').hide();" checked="checked" class="radio"><label>单日</label>
								<input type="radio" name="sjtid" value="1" onclick="$(\'#lxqs\').hide();$(\'#lxspan_1\').show();$(\'#lxspan_2\').hide();" class="radio"><label>连续多日</label>
								<input type="radio" name="sjtid" value="2" onclick="$(\'#lxqs\').show();$(\'#lxspan_1\').hide();$(\'#lxspan_2\').show();" class="radio"><label>每周举行</label></td>
						</tr>
						<tr>
							<th></th>
							<td class="chk">
								<span id="lxqs" class="yjl_ft" style="display: none;">起始日期</span><select name="datetime_y">';
	for($i=date('Y');$i<=2014;$i++)$c.='<option value="'.$i.'"'.($i==date('Y')?' selected="selected"':'').'>'.$i.'</option>';
	$c.='</select><span class="yjl_ft">年</span><select name="datetime_m">';
	for($i=1;$i<=12;$i++)$c.='<option value="'.$i.'"'.($i==date('n')?' selected="selected"':'').'>'.$i.'</option>';
	$c.='</select><span class="yjl_ft">月</span><select name="datetime_d">';
	for($i=1;$i<=31;$i++)$c.='<option value="'.$i.'"'.($i==date('j')?' selected="selected"':'').'>'.$i.'</option>';
	$c.='</select><span class="yjl_ft">日</span><span id="lxspan_1" style="display: none;"><span class="yjl_ft">&nbsp;到&nbsp;</span><select name="datetime1_y">';
	for($i=date('Y');$i<=2014;$i++)$c.='<option value="'.$i.'"'.($i==date('Y')?' selected="selected"':'').'>'.$i.'</option>';
	$c.='</select><span class="yjl_ft">年</span><select name="datetime1_m">';
	for($i=1;$i<=12;$i++)$c.='<option value="'.$i.'"'.($i==date('n')?' selected="selected"':'').'>'.$i.'</option>';
	$c.='</select><span class="yjl_ft">月</span><select name="datetime1_d">';
	for($i=1;$i<=31;$i++)$c.='<option value="'.$i.'"'.($i==date('j')?' selected="selected"':'').'>'.$i.'</option>';
	$c.='</select><span class="yjl_ft">日</span></span><span id="lxspan_2" style="display: none;"><span class="yjl_ft">&nbsp; &nbsp;连续</span><input type="text" class="codetext" name="cs" value="2"/><span class="yjl_ft">周</span></span>
							</td>
						</tr>
						<tr>
							<th>地点<b></b></th>
							<td>'.($xqid>0?(($udb['isxg']>0 || $udb['qx']==10)?'<input type="radio" class="radio" name="isnoxq" value="0" checked="checked"/><span class="yjl_ft">':'').$xqdb['name'].(($udb['isxg']>0 || $udb['qx']==10)?'&nbsp;&nbsp;</span><input type="radio" class="radio" name="isnoxq" value="1"/>全部小区':''):'全部小区').'</td>
						</tr>
						<tr>
							<th></th>
							<td><input type="text" class="text" name="address"></td>
						</tr>
						<tr>
							<th valign="top">介绍<b></b></th>
							<td><textarea name="content" id="form_text" style="height: 200px;"></textarea></td>
						</tr>
						<tr>
						<td></td>
						<td><input type="submit" class="submit sub_reg" value="下一步"/></td>
					</tr>
					</table>
				</form>';
}
$c.='</div><br /><br />
		</div>
		<div class="main_right">
			<div class="map">
				<br /><br />
				<h3>什么是合适的活动？</h3>
				<p>1.有能最终确定的活动起止日期<br />
					2.具备现实中能集体参与的活动地点<br />
					3.是多人在现实中能碰面的活动</p>
				 <br />
				<h3>如何才能让你的活动更吸引人？</h3>
				<p>1.标题简单明了<br />
					2.活动内容和特点介绍详细<br />
					3.活动海报吸引人眼球</p>
				<br />
				<h3>你需要活动赞助？</h3>
				<p>我们为优秀的活动争取商家赞助<br />
					<a href="#">我要申请赞助 &#187;</a>
				</p>
			</div>
		</div>';
echo yjl_html($c, 'active', '', 3);
?>