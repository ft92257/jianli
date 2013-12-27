<?php
session_start();
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once($yjl_tpath.'setting/face.php');
if(isset($_GET['rg']) && trim($_GET['rg'])!=''){
	$a_rg=explode('-', trim($_GET['rg']));
	if(isset($a_rg[0]))$_GET['xqid']=$a_rg[0];
	if(isset($a_rg[1])){
		if(substr($a_rg[1], 0, 1)=='p'){
			$_GET['p']=substr($a_rg[1], 1);
		}else{
			$_GET['cid']=$a_rg[1];
			if(isset($a_rg[2]) && substr($a_rg[2], 0, 1)=='p')$_GET['p']=substr($a_rg[2], 1);
		}
	}
}
require_once('function.php');
$f='square.php';
if($udb['uid']>0){
	if(($udb['iswc']!=1 && ($udb['qx']==5 || $udb['qx']==6)) || $udb['isnc']==0){
		echo '<script type="text/javascript">location.href=\'./\';</script>';
		exit();
	}
}
if($xqid>0){
	if($user_id>0)yjl_vlog($xqid);
	$uSQL=sprintf('update %s set c_fw=c_fw+1 where xqid=%s', $yjl_dbprefix.'xq', $xqid);
	$result=mysql_query($uSQL) or die('');
	$c_l1id=$xqdb['l1id'];
	$js_c='';
	$c='';
	$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
	$page_title=$xqdb['name'].' 小区广场';
	$c='<div class="main_left">';
	$xqtcid=(isset($_GET['cid']) && isset($a_xqgc[$_GET['cid']]))?$_GET['cid']:0;
	if($user_id>0){
		$isupimg=1;
		$js_a='upimg_a_0(response);';
		$js_ac='upimg_ac_0();';
		$js_c.=yjl_uploadjs($js_a, $js_ac);
		$c.='<div class="broadcast">
				<table>
					<tr>
						<td><textarea style="padding: 5px;" id="content"></textarea><input type="hidden" id="xqcid" value="'.$xqtcid.'"/></td>
					</tr>
					<tr>
						<td><input type="submit" value="广 播" class="submit sub_smbe" id="submit_fb" onclick="postxqwb(\''.$xqid.'\');" /></td>
					</tr>
				</table>
				<div class="spdin"><a href="#" onclick="if($(\'#imgu_div\').is(\':hidden\'))$(\'#imgu_div\').show();return false;"><span class="mn_ico ico22"></span>图片</a><a href="#" onclick="if($(\'#wb_v_div\').is(\':hidden\'))$(\'#wb_v_div\').show();return false;"><span class="mn_ico ico23"></span>视频</a></div><div class="wb_imgv">'.yjl_uploadv_3().'</div><div id="wb_v_div" style="display: none;padding-top: 10px;">请复制视频播放页网站地址即可 <input class="text" id="vurl"/></div>
			</div>';
	}
	$c.='<div class="box1 clearfix"><div class="plaza_nva">';
	$js_c.='
	$(\'.xqrc_s\').click(function(){
		$(\'.xqrc_s\').css(\'font-weight\', \'\');
		var id=$(this).attr(\'jq_id\');
		$(\'#xqrc_\'+id).css(\'font-weight\', \'bold\');
		'.($user_id>0?'$(\'#xqcid\').val(id);':'').'
		$(\'#xqtopic_'.$xqid.'\').load(\'j/xqtopic.php?xqid='.$xqid.'&cid=\'+id);
		return false;
	});';
	$c.='<a id="xqrc_0" class="xqrc_s" jq_id="0" href="square-'.$xqid.'.html"'.($xqtcid==0?' style="font-weight: bold;"':'').'>全部</a>';
	foreach($a_xqgc as $k=>$v)$c.='|<a id="xqrc_'.$k.'" class="xqrc_s" jq_id="'.$k.'" href="square-'.$xqid.'-'.$k.'.html"'.($xqtcid==$k?' style="font-weight: bold;"':'').'>'.$v.'</a>';
	$c.='</div>';
	$c.='<div id="xqtopic_'.$xqid.'">';
	$cdb=$xqtcid>0?' and a.cid='.$xqtcid:'';
	$q_res=sprintf('select b.* from %s as a, %s as b where a.tid=b.tid and a.xqid=%s%s order by a.datetime desc', $yjl_dbprefix.'xq_topic', $dbprefix.'topic', $xqid, $cdb);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		do{
			if(!isset($uadb[$r_res['uid']]))$uadb[$r_res['uid']]=yjl_udb($r_res['uid']);
			$a_res[$r_res['tid']]=yjl_newwb($r_res);
		}while($r_res=mysql_fetch_assoc($res));
	}
	mysql_free_result($res);
	$q_res=sprintf('select b.* from %s as a, %s as b where a.tid=b.tid and a.xqid<>%s%s order by a.datetime desc', $yjl_dbprefix.'xq_topic', $dbprefix.'topic', $xqid, $cdb);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		do{
			if(!isset($uadb[$r_res['uid']]))$uadb[$r_res['uid']]=yjl_udb($r_res['uid']);
			$a_res[$r_res['tid']]=yjl_newwb($r_res);
		}while($r_res=mysql_fetch_assoc($res));
	}
	mysql_free_result($res);
	if(isset($a_res)){
		$c.='<ul class="list_comment">';
		$tr_res=count($a_res);
		$p_size=10;
		$tp_res=ceil($tr_res/$p_size);
		if($page>$tp_res)$page=$tp_res;
		$i=0;
		foreach($a_res as $v){
			if($i>=($page-1)*$p_size && $i<$page*$p_size)$c.=$v;
			$i++;
		}
		$c.='</ul>';
		if($tp_res>1)$c.=yjl_newhmpage('square-'.$xqid.($xqtcid>0?'-'.$xqtcid:'').'-p[p]'.'.html', $page, $tp_res, 'xqtopic_'.$xqid);
	}
	$c.='</div><br /><br /></div></div><div class="main_right">'.yjl_newr_xq().yjl_newr_jlzx().yjl_newr_tjhd().yjl_newr_yh().yjl_newr_visitor().'</div>';
	echo yjl_html($c, 'plaza');
}else{
	echo '<script type="text/javascript">location.href=\'estate-1.html\';</script>';
}
?>