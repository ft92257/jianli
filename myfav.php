<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once('function.php');
$f='myfav.php';
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
$page_title=$uadb[$cuid]['nc'].' 我喜欢的';
$c='<style type="text/css">
.pbl {
	width: '.$pbl_w.'px;
}
</style><script src="lib/setwaterfall.js" type="text/javascript"></script><input type="hidden" id="cp" value="'.$pbl_p.'"><input type="hidden" id="isloading" value="0"><input type="hidden" id="sp" value="'.$pbl_s.'"><div class="title">
				<h3>收藏夹</h3>
				<div class="sub_title">
					<span>我喜欢的</span>|<a href="myshare.php">好友的分享</a>
				</div>
			</div>
			<div class="act_cont clearfix">
				<div style="height: 20px;"></div>';
$q_rep=sprintf('select a.*, b.xqid, b.hzid from %s as a, %s as b, %s as c where a.is_del=0 and a.jlid=b.jlid and a.jpid=c.jpid and c.uid=%s and c.tlid=0 order by c.datetime desc limit %s', $yjl_dbprefix.'jl_photo', $yjl_dbprefix.'jl', $yjl_dbprefix.'jl_plike', $udb['uid'], ($pbl_p*$pbl_s));
$rep=mysql_query($q_rep) or die(mysql_error());
$r_rep=mysql_fetch_assoc($rep);
if(mysql_num_rows($rep)>0){
	$c.='<div id="pbl_pv">';
	$js_c='
	$("div[jq_newh=\'1\']").fadeIn(500);
	$("div[jq_newh=\'1\']").attr(\'jq_newh\', \'0\');
	$(\'.pbl\').setwaterfall();';
	$js_scrc='
		var clienth=document.documentElement.clientHeight;
		var scrollh=document.documentElement.scrollHeight;
		var scrollt=document.documentElement.scrollTop+document.body.scrollTop;
		if(clienth+scrollt+200>scrollh){
			if($(\'#isloading\').val()==\'0\'){
				$(\'#isloading\').val(\'1\');
				var p=parseInt($(\'#cp\').val());
				p++;
				$.get(\'j/myfav.php?p=\'+p+\'&s=\'+$(\'#sp\').val(), function(data){
					if(data!=\'\'){
						$(\'#pbl_pv\').append(data);
						$("div[jq_newh=\'1\']").fadeIn(500);
						$("div[jq_newh=\'1\']").attr(\'jq_newh\', \'0\');
						$(\'.pbl\').setwaterfall();
						$(\'#cp\').val(p);
					}
					$(\'#isloading\').val(\'0\');
				})
			}
		}';
	do{
		if(!isset($uadb[$r_rep['hzid']]))$uadb[$r_rep['hzid']]=yjl_udb($r_rep['hzid']);
		$c.=yjl_pbl($r_rep);
	}while($r_rep=mysql_fetch_assoc($rep));
	$c.='</div>';
}
mysql_free_result($rep);
$c.='<br class="clear" /><br /><br /></div>';
echo yjl_gehtml(yjl_userl($cuid, 'sc'), $c, '收藏夹');
?>