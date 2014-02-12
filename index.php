<?php
session_start();
require_once('config.php');

require_once($yjl_tpath.'setting/settings.php');
$no_getxq=1;

require_once('function.php');

setcookie('isgz', 0, 0, '/');
$_COOKIE['isgz'] = 0;
$dtype = ' dtype = 1 AND';
$js_c='';
if($udb['uid']>0 && ($udb['iswc']!=1 && ($udb['qx']==5 || $udb['qx']==6 || ($udb['qx']==0 && isset($_GET['ws']) && $_GET['ws']==1)) || $udb['isnc']==0)){
	$c_l1id=(isset($_GET['s']) && intval($_GET['s'])>0)?intval($_GET['s']):$d_l1id;
	$js_c='';
	if($udb['qx']>0){
		require_once('index_inc_0.php');
	}else{
		require_once('index_inc_1.php');
	}
	echo yjl_html($c, 'regist', 'regist');
}else{
	$is_home=1;
	if($udb['uid']>0){
		//echo '<script type="text/javascript">location.href=\'user-'.$udb['uid'].'.html\';</script>';
		//exit();
	}
	$js_c.='
	$(\'#jl_search_q\').focus(function(){
		if($(this).attr(\'jq_ise\')==\'1\')$(this).val(\'\');
	}).blur(function(){
		if($(this).val()!=\'\'){
			$(this).attr(\'jq_ise\', \'0\');
		}else{
			$(this).attr(\'jq_ise\', \'1\');
			$(this).val(\'监理项目\');
		}
	});
	$(\'#jl_search_bt\').click(function(){
		if($(\'#jl_search_q\').attr(\'jq_ise\')==\'1\')return false;
	});
	$(\'#login_u\').focus(function(){
		if($(this).attr(\'jq_ise\')==\'1\')$(this).val(\'\');
	}).blur(function(){
		if($(this).val()!=\'\'){
			$(this).attr(\'jq_ise\', \'0\');
		}else{
			$(this).attr(\'jq_ise\', \'1\');
			$(this).val(\'用户名\');
		}
	})
	$(\'#login_p\').focus(function(){
		$(this).css(\'background-image\', \'\');
	}).blur(function(){
		if($(this).val()==\'\')$(this).css(\'background-image\', \'url(images/pwbg.gif)\');
	})
	$(\'#submit_bt\').click(function(){
		if($(\'#login_u\').attr(\'jq_ise\')==\'1\')$(\'#login_u\').val(\'\');
		$(\'#login_form\').submit();
	});';
	$hdc='';
	$hdu='';
	if($r_main['d_hdid']>0)$hdu='active-'.$r_main['d_hdxqid'].'-'.$r_main['d_hdid'].'.html';
	$q_rep=sprintf('select * from %s where etime>%s order by isgf desc, c_zan desc, lasttime desc limit 3', $yjl_dbprefix.'hd', time());

	$rep=mysql_query($q_rep) or die(mysql_error());
	$r_rep=mysql_fetch_assoc($rep);
	if(mysql_num_rows($rep)>0){
		$hdc.='<ul class="list_upact">';
		do{
			if($hdu=='')$hdu='active-'.$r_rep['xqid'].'-'.$r_rep['hdid'].'.html';
			$hdc.='<li><p>'.$r_rep['name'].'<span><img src="images/hot.gif" /></span></p><a href="active-'.$r_rep['xqid'].'-'.$r_rep['hdid'].'.html" class="btn">参加</a></li>';
		}while($r_rep=mysql_fetch_assoc($rep));
		$hdc.='</ul>';
	}
	mysql_free_result($rep);
	$jlc='';
	$jlu='';
	if($r_main['d_jlid']>0)$jlu='photo-'.$r_main['d_jlxqid'].'-'.$r_main['d_jlid'].'.html';
	$q_res=sprintf('select a.*, b.name as b_name from %s as a, %s as b where a.hzqr=1 and '.$dtype.' a.lid>0 and a.c_zp>4 and a.xqid=b.xqid order by a.lasttime desc limit 6', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq');
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$jlc.='<ul class="list_visit">';
		do{
			if($jlu=='')$jlu='photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html';
			$pu='images/jl_d.jpg';
			$q_reu=sprintf('select * from %s where jlid=%s and is_del=0 order by datetime desc, jpid desc limit 1', $yjl_dbprefix.'jl_photo', $r_res['jlid']);
			$reu=mysql_query($q_reu) or die('');
			$r_reu=mysql_fetch_assoc($reu);
			if(mysql_num_rows($reu)>0)$pu=$r_reu['t_url'];
			mysql_free_result($reu);
			$jlc.='<li><a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html"><img src="images/blank.gif" width="'.$a_wh_jltpt[0].'" height="'.$a_wh_jltpt[1].'" style="background: url('.$pu.') no-repeat center;" /></a>
						<em class="percent'.$r_res['lid'].'"></em>
						<p><a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html" title="'.$r_res['name'].'">'.yjl_substrs($r_res['name'], 7).'</a></p>
						<p title="'.$r_res['b_name'].'">'.yjl_substrs($r_res['b_name'], 7).'</p>
						<div class="bt_ltblue"><a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html">参观</a></div>
					</li>';
		}while($r_res=mysql_fetch_assoc($res));
		$jlc.='</ul>';
	}
	mysql_free_result($res);
	$c='<div class="top_row clearfix" style="padding:0px;">
			<img src="images/banimg_ban0.png" width="" />
		</div>
		<div class="row_bg clearfix">
			<div class="left">
				<h2 class="hot_line">咨询监理<sub>咨询热线400-990-2013、QQ：5678107</sub></h2>
				<ul class="list_spr hover clearfix">';
	$q_res=sprintf('select a.nc, b.uid, b.face, b.aboutme from %s as a, %s as b where a.uid=b.uid and a.qx=5 and a.iswc=1 and a.iszxjl=1 limit 2', $yjl_dbprefix.'members', $dbprefix.'members');
	$res=mysql_query($q_res) or die($q_res);
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		do{
			$c.='<li><a href="user-'.$r_res['uid'].'.html"><img src="'.yjl_face($r_res['uid'], $r_res['face'], 1).'" style="width: '.$a_wh_utxb[0].'px;height: '.$a_wh_utxb[1].'px;" /></a>
						<p class="sprne">高级监理师<a href="user-'.$r_res['uid'].'.html">'.$r_res['nc'].'</a></p>
						<p>'.yjl_substrs($r_res['aboutme'], 20).'</p>
						<div class="spr_consul">
							<a '.(!$udb['uid'] ? 'href="login.php" rel="#overlay_login"' : 'href="faq-new-'.$r_res['uid'].'.html"').' class="btn bt_orangesml"><span class="mn_ico ico25"></span>向监理咨询</a>
							<a href="login.php?u='.urlencode('help.php').'" rel="#overlay_login" class="btn bt_bgbluesml"><span class="mn_ico ico25"></span>请监理到现场</a>
						</div>
					</li>';
			}while($r_res=mysql_fetch_assoc($res));
		}
	mysql_free_result($res);
	$c.='</ul><div class="box clearfix" id="faq">';
	$p_size=5;
	$q_res=sprintf('select a.qzid, a.c_zan, a.jluid, a.cid, b.* from %s as a, %s as b where a.tid=b.tid and a.c_hd>0 order by a.datetime desc, a.qzid desc limit %s', $yjl_dbprefix.'qz', $dbprefix.'topic', $p_size);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$js_scrc='
		if($(\'#qz_iso\').length==0 || $(\'#qz_iso\').val()!=\'1\'){
			var clienth=document.documentElement.clientHeight;
			var scrollh=document.documentElement.scrollHeight;
			var scrollt=document.documentElement.scrollTop+document.body.scrollTop;
			if(clienth+scrollt+50>scrollh){
				if($(\'#isloading\').val()==\'0\'){
					$(\'#qz_ldiv\').show();
					$(\'#isloading\').val(\'1\');
					var p=parseInt($(\'#cp\').val());
					p++;
					$.get(\'j/homeqz.php?s='.$p_size.'&p=\'+p, function(data){
						$(\'#home_qzlist\').append(data);
						$(\'#isloading\').val(\'0\');
						$(\'#qz_ldiv\').hide();
						$(\'#cp\').val(p);
					})
				}
			}
		}';
		$c.='<input type="hidden" id="isloading" value="0"><input type="hidden" id="cp" value="1"><ul id="home_qzlist" class="list_comment">';
		do{
			if(!isset($uadb[$r_res['uid']]))$uadb[$r_res['uid']]=yjl_udb($r_res['uid']);
			$c.=yjl_homeqz($r_res);
		}while($r_res=mysql_fetch_assoc($res));
		$c.='</ul><center style="padding-top: 20px;" id="qz_ldiv" style="display: none;">正在加载中，请稍候…</center>';
	}
	mysql_free_result($res);
	$c.='<div class="box2 clearfix">
						<div class="flt_lt">
						<h2 class="h2">欢迎来到易监理</h2>	
						<div class="login">
						<a href="reg.php" class="btn bt_nomblue">注 册</a><a href="login.php" class="btn bt_orangenom">登 录</a><a href="login.php?t=sina"><span class="mn_ico ico11"></span>使用微博账号登录</a><a href="login.php?t=tqq"><span class="mn_ico ico12"></span>QQ登录</a>
						</div>	
						</div>
						<div class="flt_rt">
							<p>关注我们：</p>
							<br />
							<iframe width="136" height="24" frameborder="0" allowtransparency="true" marginwidth="0" marginheight="0" scrolling="no" border="0" src="http://widget.weibo.com/relationship/followbutton.php?language=zh_cn&width=136&height=24&uid=2562337987&style=2&btn=light&dpc=1"></iframe>
							<br />
							<iframe src="http://follow.v.t.qq.com/index.php?c=follow&a=quick&name=zhangyixin-guo&style=5&t=1346826131273&f=1" frameborder="0" scrolling="auto" width="148" height="24" marginwidth="0" marginheight="0" allowtransparency="true"></iframe>
						</div>
					</div>
				</div>
			</div>
			<div class="rright">
				<h2 class="h2">最新活动</h2>
				<a href="active-0.html" class="more">查看更多</a>'.$hdc.'<h2 class="h2">邻家装修参观</h2><a href="photo-0.html" class="more">查看更多</a>'.$jlc.'<div class="search_index">
				<form method="get" class="main_form" action="photo_search.php">
					<input type="text" class="search_text" id="jl_search_q" name="q" jq_ise="1" value="监理项目" />
					<input type="submit" value="" id="jl_search_bt" class="search_submit" />
				</form>
                </div></div></div>';
	echo yjl_html($c, 'index');
}
?>