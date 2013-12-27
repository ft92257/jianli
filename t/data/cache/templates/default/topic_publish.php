<?php /* 2012-08-31 in jishigou invalid request template */ if(!defined("IN_JISHIGOU")) exit("invalid request"); ?><style type="text/css">ul.mycon li{ width:65px;}</style> <script type="text/javascript" src="templates/default/js/publishbox.js?build+20120829"></script> <div id="zz_main_publish"> <div id="tigBox_2" class="tigBox_2">点<a href="javascript:" onClick="thread_insert()" style="cursor:pointer">#插入自定义话题#</a>给微博添加话题，方便关注</div> <div class="issueBox"> <div class="fbqCount"> <div class="fLeft"> <?php if($this->Get['mod'] == 'member') { ?> <?php $content = '#新人报到# 我是'.$this->Config['site_name'].'新加入的成员@'.MEMBER_NICKNAME.' ，欢迎大家来关注我！'; ?>
所有关注我的人将看到我分享的信息<?php } elseif($defaust_value) { ?><?php echo $defaust_value; ?> <?php } else { ?><span> <?php $__member_fans_count=(int)($GLOBALS['_J']['member']['fans_count']?$GLOBALS['_J']['member']['fans_count']:$member['fans_count']); ?> <?php if($__member_fans_count>0) { ?> <?php echo $__member_fans_count; ?> <?php } else { ?>0
<?php } ?> </span>人在关注我，
<A href="index.php?mod=profile&code=invite" style="cursor:pointer">邀请</A>更多人
<?php } ?> </div> <ul class="mycon"> <?php if($this->Config['topic_input_length']>0) { ?> <li>还可以输入</li><li style="width:auto"><span id="wordCheck<?php echo $h_key; ?>" style='font-family:Georgia;font-size:24px;'><?php echo $this->Config['topic_input_length']; ?></span></li><li style="width:14px;">字</li> <?php } ?> </ul> </div> <div class="box_1" style="display:block"> <?php $i_already_value = $content ? $content : $this->Config['today_topic'];$this->totid = $this->totid ? $this->totid : 0; ?> <script type="text/javascript">
var __I_ALREADY_VALUE__ = '<?php echo $i_already_value; ?>';
var __ALERT__='<?php echo $this->Config['verify_alert']; ?>';
</script> <textarea name="content" id="i_already<?php echo $h_key; ?>"  onkeyup="javascript:checkWord('<?php echo $this->Config['topic_input_length']; ?>',event,'wordCheck<?php echo $h_key; ?>')" onkeydown="ctrlEnter(event, 'publishSubmit<?php echo $h_key; ?>')"><?php echo $i_already_value; ?></textarea> <?php $_get_type=str_safe($this->Get['type']); ?> <input name="topic_type" type="hidden" id="topic_type<?php echo $h_key; ?>" value="<?php echo $_get_type; ?>" /> <input name="totid" type="hidden" id="totid<?php echo $h_key; ?>" value="<?php echo $this->totid; ?>" /> <input name="touid" type="hidden" id="touid<?php echo $h_key; ?>" value="<?php echo $this->touid; ?>" /> <input name="item" type="hidden" id="mapp_item<?php echo $h_key; ?>" value="<?php echo $this->item; ?>" /> <input name="item_id" type="hidden" id="mapp_item_id<?php echo $h_key; ?>" value="<?php echo $this->item_id; ?>" /> <input name="xiami_id" type="hidden" id="xiami_id" value="" /> </div> <?php if(!($type == 'design' || $type == 'btn_wyfx')) { ?> <div class="box_3"> <script type="text/javascript">
$(document).ready(function() {	 	
//表情
$(".menu_bqb_c").click(function(){
$("#showface<?php echo $h_key; ?>").show();
$(".menu_tqb").hide();
$(".menu_fjb").hide();
$(".menu_spb").hide();
$('.menu_htb').hide();
$('.menu_vsb').hide();
$(".menu_wqb").hide();
$(".menu_hdb").hide();
$(".menu_cwb").hide();
$(".menu_hts").hide();
$(".menu_music").hide();
});
$('#showface<?php echo $h_key; ?>').click(function(){return false;});
//图片 
$(".menu_tqb_c").click(function(){
$(".menu_tqb").show();
$(".menu_fjb").hide();
$(".menu_spb").hide();
$('#showface<?php echo $h_key; ?>').hide();
$('.menu_htb').hide();
$('.menu_vsb').hide();
$(".menu_wqb").hide();
$(".menu_hdb").hide();
$(".menu_cwb").hide();
$(".menu_hts").hide();
$(".menu_music").hide();
});
$('#pubImg').click(function(){
$("#publisher_file").style.posLeft=event.x-offsetWidth/2;$("#publisher_file").style.posTop=event.y-offsetHeight/2;});
$(".menu_tqb_c1").click(function(){$(".menu_tqb").hide();});
$("#publishSubmit").click(function(){$(".menu_tqb").hide();});
//附件 
$(".menu_fjb_c").click(function(){
$(".menu_fjb").show();
$(".menu_tqb").hide();
$(".menu_spb").hide();
$('#showface<?php echo $h_key; ?>').hide();
$('.menu_htb').hide();
$('.menu_vsb').hide();
$(".menu_wqb").hide();
$(".menu_hdb").hide();
$(".menu_cwb").hide();
$(".menu_hts").hide();
$(".menu_music").hide();
});
$('#pubAttach').click(function(){
$("#publisher_file_attach").style.posLeft=event.x-offsetWidth/2;$("#publisher_file_attach").style.posTop=event.y-offsetHeight/2;});
$(".menu_fjb_c1").click(function(){$(".menu_fjb").hide();});
$("#publishSubmit").click(function(){$(".menu_fjb").hide();});
//视频
$(".menu_spb_c").click(function(){
$(".menu_spb").show();
$(".menu_tqb").hide();
$(".menu_fjb").hide();
$('#showface<?php echo $h_key; ?>').hide();
$('.menu_htb').hide();
$('.menu_vsb').hide();
$(".menu_wqb").hide();
$(".menu_hdb").hide();
$(".menu_cwb").hide();
$(".menu_hts").hide();
$(".menu_music").hide();
});
$(".menu_spb_c1").click(function(){$(".menu_spb").hide();});
//音乐
$(".menu_m_c").click(function(){
$(".menu_music").show();
$(".menu_wqb").hide();
$(".menu_tqb").hide();
$(".menu_spb").hide();
$('#showface').hide();
$('.menu_htb').hide();
$(".menu_vsb").hide();
$(".menu_hdb").hide();
$(".menu_cwb").hide();
$(".menu_hts").hide();
$(".menu_fjb").hide();
});
$(".menu_music_c1").click(function(){$(".menu_music").hide();});
//话题
$(".menu_htb_c").click(function(){
$(".menu_htb").show();
$(".menu_spb").hide();
$(".menu_tqb").hide();
$(".menu_fjb").hide();
$('#showface<?php echo $h_key; ?>').hide();
$('.menu_vsb').hide();
$(".menu_wqb").hide();
$(".menu_hdb").hide();
$(".menu_cwb").hide();
$(".menu_hts").hide();
$(".menu_music").hide();
});
$('.menu_htb').click(function(){return false;});
//签到
$(".menu_hts_c").click(function(){
$(".menu_hts").show();
$(".menu_htb").hide();
$(".menu_spb").hide();
$(".menu_tqb").hide();
$(".menu_fjb").hide();
$('#showface<?php echo $h_key; ?>').hide();
$('.menu_vsb').hide();
$(".menu_wqb").hide();
$(".menu_hdb").hide();
$(".menu_cwb").hide();
$(".menu_music").hide();
});
$(".menu_hts_c1").click(function(){$(".menu_hts").hide();});
//投票
$(".menu_vsb_c").click(function(){
getVoteAvtivityFromIndex('vote_publish', 'con_vote_content_1');
$(".menu_vsb").show();
$(".menu_tqb").hide();
$(".menu_fjb").hide();
$(".menu_spb").hide();
$('#showface<?php echo $h_key; ?>').hide();
$('.menu_htb').hide();
$(".menu_wqb").hide();
$(".menu_hdb").hide();
$(".menu_cwb").hide();
$(".menu_hts").hide();
$(".menu_music").hide();
});
$(".menu_vsb_c1").click(function(){$(".menu_vsb").hide();});
//活动
$(".menu_hdb_c").click(function(){
getEventPost();
$(".menu_hdb").show();
$(".menu_vsb").hide();
$(".menu_tqb").hide();
$(".menu_fjb").hide();
$(".menu_spb").hide();
$('#showface<?php echo $h_key; ?>').hide();
$('.menu_htb').hide();
$(".menu_wqb").hide();
$(".menu_cwb").hide();
$(".menu_hts").hide();
$(".menu_music").hide();
});
$(".menu_hdb_c1").click(function(){$(".menu_hdb").hide();});
//微群
$(".menu_wqb_c").click(function(){
getMyQun();
$(".menu_wqb").show();
$(".menu_tqb").hide();
$(".menu_fjb").hide();
$(".menu_spb").hide();
$('#showface<?php echo $h_key; ?>').hide();
$('.menu_htb').hide();
$(".menu_vsb").hide();
$(".menu_hdb").hide();
$(".menu_cwb").hide();
$(".menu_hts").hide();
$(".menu_music").hide();
});
$(".menu_wqb_c1").click(function(){$(".menu_wqb").hide();});
//长文本
$(".menu_cwb_c").click(function(){
initKindEditor();
/*
get_longtext_info();
$(".menu_cwb").show();
$(".menu_tqb").hide();
$(".menu_spb").hide();
$('#showface').hide();
$('.menu_htb').hide();
$(".menu_vsb").hide();
$(".menu_hdb").hide();
$(".menu_wqb").hide();
$(".menu_hts").hide();
$(".menu_music").hide();
//*/
});
$(".menu_cwb_c1").click(function(){$(".menu_cwb").hide();});
$(".menu_xb_c").click(function(){
getClassPost();
$(".menu_xb").show();
$(".menu_wqb").hide();
$(".menu_tqb").hide();
$(".menu_spb").hide();
$('#showface').hide();
$('.menu_htb').hide();
$(".menu_vsb").hide();
$(".menu_hts").hide();
$(".menu_music").hide();
});
$(".menu_xb_c1").click(function(){$(".menu_xb").hide();});
//同步
$(".box_4_open_span").click(function(){
$(".box_4_open_Box").show();
$(".menu_wqb").hide();
$(".menu_tqb").hide();
$(".menu_spb").hide();
$('#showface').hide();
$('.menu_htb').hide();
$(".menu_vsb").hide();
$(".menu_hdb").hide();
$(".menu_cwb").hide();
$(".menu_hts").hide();
$(".menu_fjb").hide();
});
$(".box_4_open_span_c1").click(function(){$(".box_4_open_Box").hide();});
//$(".box_4_open_Box").mouseout(function(){$(".box_4_open_Box").hide();});
});
//-----------------------------------
function setTab(name,cursel,n){
for(i=1;i<=n;i++){
var menu=document.getElementById(name+i);
var con=document.getElementById("con_"+name+"_"+i);
menu.className=i==cursel?"vhover":"";
con.style.display=i==cursel?"block":"none";
}
}
function setTab1(name,cursel,n){
for(i=1;i<=n;i++){
var menu=document.getElementById(name+i);
var con=document.getElementById("con_"+name+"_"+i);
menu.className=i==cursel?"vhover":"";
con.style.display=i==cursel?"block":"none";
}
}
function setTab2(name,cursel,n){
for(i=1;i<=n;i++){
var menu2=document.getElementById(name+i);
var con2=document.getElementById("con2_"+name+"_"+i);
menu2.className=i==cursel?"vhover2":"";
con2.style.display=i==cursel?"block":"none";
}
}
</script> <?php if($this->Config['sign']['sign_enable']) { ?> <div class="menu" > <div class="menu_ht menu_qd" id="sign_<?php echo MEMBER_ID; ?>"><span onclick="getSignTag(<?php echo MEMBER_ID; ?>);return false;" class="menu_hts_c">签到</span> <div class="menu_hts"> <div id="sign_tag_<?php echo MEMBER_ID; ?>"></div> </div> </div> </div> <?php } ?> <div class="menu"> <div class="menu_bq" id="editface" ><b class="menu_bqb_c"><a href="javascript:void(0);" onclick="topic_face('showface<?php echo $h_key; ?>','i_already<?php echo $h_key; ?>','topic_face');return false;">表情</a></b> <div id="showface<?php echo $h_key; ?>" class="showface"></div> </div></div> <?php $image_uploadify_topic = array(); ?> <?php $image_uploadify_from = 'topic_publish'; ?> <?php $image_uploadify_only_js = 1; ?> <?php $image_uploadify_id=$content_textarea_id.$image_uploadify_type.($image_uploadify_topic['tid']>0?"_".$image_uploadify_topic['tid']:""); ?> <?php $image_uploadify_image_small_size=$image_uploadify_image_small_size?$image_uploadify_image_small_size:45; ?> <?php $content_textarea_id=$content_textarea_id?$content_textarea_id:'i_already'.$h_key; ?> <?php $content_textarea_empty_val=isset($content_textarea_empty_val)?$content_textarea_empty_val:'分享图片'; ?> <?php $image_uploadify_queue_size_limit=max(0, (int) $this->Config['image_uploadify_queue_size_limit']);if($image_uploadify_queue_size_limit<1)$image_uploadify_queue_size_limit=3; ?> <?php $img_item=isset($this->item)?$this->item:''; ?> <?php $img_itemid=isset($this->item_id)?$this->item_id:0; ?> <success></success> <script type="text/javascript">
var __IMAGE_IDS__ = {};
$(document).ready(function(){
$('#publisher_file<?php echo $image_uploadify_id; ?>').uploadify({
'uploader'  : '<?php echo $this->Config['site_url']; ?>/images/uploadify/uploadify.swf?id=<?php echo $image_uploadify_id; ?>&random=' + Math.random(),
'script'    : '<?php echo urlencode($this->Config['site_url'] . "/ajax.php?mod=uploadify&code=image&iitem=$img_item&iitemid=$img_itemid"); ?>',
'cancelImg' : '<?php echo $this->Config['site_url']; ?>/images/uploadify/cancel.png',
'buttonImg'	: '<?php echo $this->Config['site_url']; ?>/images/uploadify/addatt.gif',
'width'		: 111,
'height'	: 17,
'multi'		: true,
'auto'      : true,
'sizeLimit' : '2097152',
'fileExt'	: '*.jpg;*.png;*.gif;*.jpeg',
'fileDesc'	: '请选择图片文件(*.jpg;*.png;*.gif;*.jpeg)',
'queueID'	: 'uploadifyQueueDiv<?php echo $image_uploadify_id; ?>',
'wmode'		: 'transparent',
'fileDataName'	 : 'topic',
'queueSizeLimit' : <?php echo $image_uploadify_queue_size_limit; ?>,
'simUploadLimit' : 1,
'scriptData'	 : {
<?php if($image_uploadify_topic_uid) { ?>
'topic_uid'  : '<?php echo $image_uploadify_topic_uid; ?>',
<?php } ?>
'cookie_auth': '<?php echo urlencode(jsg_getcookie("auth")); ?>'
},
'onSelect'		 : function(event, ID, fileObj) {
},
'onSelectOnce'	 : function (event, data) {
imageUploadifySelectOnce<?php echo $image_uploadify_id; ?>();			    	
},
'onProgress'     : function(event, ID, fileObj, data) {
return false;
},
'onComplete'	 : function(event, ID, fileObj, response, data) {
eval('var r = ' + response + ';');
if (r.done) {					
var rv = r.retval;
if ( rv.id > 0 && rv.src.length > 0 ) {
imageUploadifyComplete<?php echo $image_uploadify_id; ?>(rv.id, rv.src, fileObj.name);
}
}
},
'onError'        : function (event, ID, fileObj, errorObj) {
alert(errorObj.type + ' Error: ' + errorObj.info);
},
'onAllComplete'	 : function(event, data) {
imageUploadifyAllComplete<?php echo $image_uploadify_id; ?>();
}
});
$("#viewImgDiv<?php echo $image_uploadify_id; ?> img").each(function() {
if($(this).width() > $(this).parent().width()) {
$(this).width("100%");
}
});
});
//删除一张图片
function imageUploadifyDelete<?php echo $image_uploadify_id; ?>(idval)
{
var idval = ('undefined'==typeof(idval) ? 0 : idval);
if(idval > 0) 
{
$.post
(
'ajax.php?mod=uploadify&code=delete_image',
{
'id' : idval
},
function (r) 
{				
if(r.done)
{
$('#uploadImgSpan_' + idval).remove();
if( ($.trim(($('#viewImgDiv<?php echo $image_uploadify_id; ?>').html()))).length < 1 )
{
$('#viewImgDiv<?php echo $image_uploadify_id; ?>').css('display', 'none');
$('#insertImgDiv<?php echo $image_uploadify_id; ?>').css('display', 'block');
}
if( 'undefined' != typeof(__IMAGE_IDS__[idval]) )
{
__IMAGE_IDS__[idval] = 0;
}
}
else
{
if(r.msg)
{
MessageBox('warning', r.msg);
}
}
},
'json'
);
}
}
function imageUploadifySelectOnce<?php echo $image_uploadify_id; ?>()
{
$('#uploading<?php echo $image_uploadify_id; ?>').html("<img src='images/loading.gif'/>&nbsp;上传中，请稍候……");
}
function imageUploadifyComplete<?php echo $image_uploadify_id; ?>(idval, srcval, nameval)
{
var imageIdsCount = 0;
$.each( __IMAGE_IDS__, function( k, v ) { if( v > 0 ) { imageIdsCount += 1; } } );
if( imageIdsCount >= <?php echo $image_uploadify_queue_size_limit; ?> )
{
MessageBox('warning', '本次图片数量超过限制了');
return false;
}
var idval = ('undefined' == typeof(idval) ? 0 : idval);
var srcval = ('undefined' == typeof(srcval) ? 0 : srcval);
var nameval = ('undefined' == typeof(nameval) ? '' : nameval);
<?php if('topic_publish'==$image_uploadify_from) { ?>
$('#viewImgDiv<?php echo $image_uploadify_id; ?>').prepend('<li id="uploadImgSpan_' + idval + '" class="menu_ps vv_2"><img sr' + 'c' + '=' + '"' + srcval + '"/> <p><i>' + nameval + ' <a title="删除图片" onclick="imageUploadifyDelete<?php echo $image_uploadify_id; ?>(' + idval + ');return false;" href="javascript:;">删除</a></i></p></li>');<?php } elseif('topic_longtext_info_ajax'==$image_uploadify_from) { ?>$('#viewImgDiv<?php echo $image_uploadify_id; ?>').append('<span id="uploadImgSpan_' + idval + '"><img s' + 'rc' + '="' + srcval + '" width="<?php echo $image_uploadify_image_small_size; ?>" alt="点击图片插入到文中" onclick="longtext_info_img_insert(\'' + srcval + '\');" /><a href="javascript:void(0);" onclick="imageUploadifyDelete<?php echo $image_uploadify_id; ?>(' + idval + '); return false;" title="删除图片">×</a></span>');
<?php } else { ?>$('#viewImgDiv<?php echo $image_uploadify_id; ?>').append('<span id="uploadImgSpan_' + idval + '"><img sr' + 'c' + '="' + srcval + '" width="<?php echo $image_uploadify_image_small_size; ?>" /><a href="javascript:void(0);" onclick="imageUploadifyDelete<?php echo $image_uploadify_id; ?>(' + idval + '); return false;" title="删除图片">×</a></span>');
<?php } ?>
$('#normalUploadFile<?php echo $image_uploadify_id; ?>').val('');
__IMAGE_IDS__[idval] = idval;
}
function imageUploadifyAllComplete<?php echo $image_uploadify_id; ?>()
{
$('#uploading<?php echo $image_uploadify_id; ?>').html('');			    	
$('#viewImgDiv<?php echo $image_uploadify_id; ?>').css('display', 'block');
//$('#insertImgDiv<?php echo $image_uploadify_id; ?>').css('display', 'none');
if( $.trim(($('#<?php echo $content_textarea_id; ?>').val())).length < 1 ) {
$('#<?php echo $content_textarea_id; ?>').val('<?php echo $content_textarea_empty_val; ?>');	
}
$('#<?php echo $content_textarea_id; ?>').focus();
}
function normalUploadFormSubmit<?php echo $image_uploadify_id; ?>()
{
var fileVal = $('#normalUploadFile<?php echo $image_uploadify_id; ?>').val();
if(($.trim(fileVal)).length < 1)
{
MessageBox('warning', '请选择一个正确的图片文件');
return false;
}
else
{
if(!(/\.(jpg|png|gif|jpeg)$/i.test(fileVal)))
{
MessageBox('warning', '请选择一个正确的图片文件');
return false;
}
else
{
imageUploadifySelectOnce<?php echo $image_uploadify_id; ?>();
return true;
}
}
}
function imageUploadifyModuleSwitch<?php echo $image_uploadify_id; ?>()
{
if('none' == $('#normalUploadDiv<?php echo $image_uploadify_id; ?>').css('display'))
{
$('#uploadDescModuleSpan<?php echo $image_uploadify_id; ?>').html('快速');
$('#swfUploadDiv<?php echo $image_uploadify_id; ?>').css('display', 'none');
$('#normalUploadDiv<?php echo $image_uploadify_id; ?>').css('display', 'block');
}
else
{
$('#uploadDescModuleSpan<?php echo $image_uploadify_id; ?>').html('普通');
$('#normalUploadDiv<?php echo $image_uploadify_id; ?>').css('display', 'none');
$('#swfUploadDiv<?php echo $image_uploadify_id; ?>').css('display', 'block');
}
}
</script> <?php if(!$image_uploadify_only_js) { ?> <div id="insertImgDiv<?php echo $image_uploadify_id; ?>" class="insertImgDiv" > <i class="insertImgDiv_up_<?php echo $image_uploadify_id; ?> insertImgDiv_up" onclick="$(this).parent().hide()"><img src="templates/default/images/imgdel.gif" title="关闭" /></i> <div id="swfUploadDiv<?php echo $image_uploadify_id; ?>"><input type="file" id="publisher_file<?php echo $image_uploadify_id; ?>" name="publisher_file<?php echo $image_uploadify_id; ?>" style="display:none;" />（按ctrl键可一次选多图上传）</div> <iframe id="imageUploadifyIframe<?php echo $image_uploadify_id; ?>" name="imageUploadifyIframe<?php echo $image_uploadify_id; ?>" width="0" height="0" marginwidth="0" frameborder="0" src="about:blank" style="display:none;"></iframe> <div id="normalUploadDiv<?php echo $image_uploadify_id; ?>" style="display:none;"> <form id="normalUploadForm<?php echo $image_uploadify_id; ?>" method="post"  action="ajax.php?mod=uploadify&code=image&type=normal&iitem=<?php echo $img_item; ?>&iitemid=<?php echo $img_itemid; ?>" enctype="multipart/form-data" target="imageUploadifyIframe<?php echo $image_uploadify_id; ?>" onsubmit="return normalUploadFormSubmit<?php echo $image_uploadify_id; ?>()"> <input type="hidden" name="FORMHASH" value='<?php echo FORMHASH; ?>'/> <input type="hidden" name="image_uploadify_id" value="<?php echo $image_uploadify_id; ?>" /> <input type="file" id="normalUploadFile<?php echo $image_uploadify_id; ?>" name="topic" /> <input type="submit" value="上传" class="tul" /> </form> </div> <span id="uploading<?php echo $image_uploadify_id; ?>"></span> <div id="uploadDescDiv<?php echo $image_uploadify_id; ?>"> <span class="fontRed">*</span> 如果您不能上传图片，可以<a href="javascript:;" onclick="imageUploadifyModuleSwitch<?php echo $image_uploadify_id; ?>();">点击这里</a>尝试<span id="uploadDescModuleSpan<?php echo $image_uploadify_id; ?>">普通</span>模式上传
<?php if('topic_longtext_info_ajax'==$image_uploadify_from) { ?> <br /><span class="fontRed">*</span> 图片上传成功后，可以点击图片将图片插入到您想要的位置
<?php } ?> </div> <div id="uploadifyQueueDiv<?php echo $image_uploadify_id; ?>" style="display:none;"></div> <div id="viewImgDiv<?php echo $image_uploadify_id; ?>" class="viewImgDiv"> <?php if((!$image_uploadify_new || $image_uploadify_modify) && $image_uploadify_topic['imageid']) { ?> <?php if(is_array($image_uploadify_topic['image_list'])) { foreach($image_uploadify_topic['image_list'] as $ik => $iv) { ?> <script type="text/javascript"> __IMAGE_IDS__[<?php echo $ik; ?>] = <?php echo $ik; ?>; </script> <span id="uploadImgSpan_<?php echo $ik; ?>"> <img src="<?php echo $iv['image_small']; ?>" width="<?php echo $image_uploadify_image_small_size; ?>" /> <a href="javascript:void(0);" onclick="imageUploadifyDelete<?php echo $image_uploadify_id; ?>('<?php echo $ik; ?>'); return false;" title="删除图片">×</a> </span> <?php } } ?> <?php } ?> </div> </div> <?php } ?> <div class="menu"> <div class="menu_tq" ><b class="menu_tqb_c">图片</b> <div class="menu_tqb"> <div class="menu_pi insertImgDiv" id="insertImgDiv"> <div id="swfUploadDiv"><input type="file" id="publisher_file" name="publisher_file" style="display:none;" />（按ctrl键可一次选多图上传）</div> <iframe id="imageUploadifyIframe" name="imageUploadifyIframe" width="0" height="0" marginwidth="0" frameborder="0" src="about:blank" style="display:none;"></iframe> <div id="normalUploadDiv" style="display:none;"> <form id="normalUploadForm" method="post"  action="ajax.php?mod=uploadify&code=image&type=normal&iitem=<?php echo $img_item; ?>&iitemid=<?php echo $img_itemid; ?>" enctype="multipart/form-data" target="imageUploadifyIframe" onsubmit="return normalUploadFormSubmit()"> <input type="hidden" name="FORMHASH" value='<?php echo FORMHASH; ?>'/> <input type="file" id="normalUploadFile" name="topic" /> <input type="submit" value="上传" class="tul" /> </form> </div> <i class="menu_tqb_c1"><img src="templates/default/images/imgdel.gif" title="关闭" /></i> <em>
1、如您不能上传图片，请<a href="javascript:;" onclick="imageUploadifyModuleSwitch();">点击这里</a>用<span id="uploadDescModuleSpan">普通</span>模式上传 ；<br />
2、网上图片URL地址可直接复制到上面发布框来发布。
</em> <span id="uploading"></span> <div class="viewImgDiv" id="viewImgDiv"></div> </div> <div id="uploadifyQueueDiv" style="display:none;"></div> </div> </div> </div> <?php if(($this->Config['attach_enable'] && $this->Module!='qun') || ($this->Config['qun_attach_enable'] && $this->Module=='qun')) $allow_attach = 1; else $allow_attach = 0  ?> <?php $attach_uploadify_topic = array(); ?> <?php $attach_uploadify_from = 'topic_publish'; ?> <?php $attach_uploadify_only_js = 1; ?> <?php $attach_num = min(max(1,(int)$this->Config['attach_files_limit']),5); ?> <?php $attach_size = min(max(1,(int)$this->Config['attach_size_limit']),5120); ?> <?php $attach_size = $attach_size >= 1024 ? round(($attach_size/1024),1).'M' : $attach_size.'KB'; ?> <?php if($allow_attach) { ?> <?php $attach_uploadify_id=$topic_textarea_id.$attach_uploadify_type.($attach_uploadify_topic['tid']>0?"_".$attach_uploadify_topic['tid']:""); ?> <?php $attach_img_siz=$attach_img_siz?$attach_img_siz:32; ?> <?php $attach_fz_siz=min(max(1,(int)$this->Config['attach_size_limit']),5120)*1024; ?> <?php $topic_textarea_id=$topic_textarea_id?$topic_textarea_id:'i_already'.$h_key; ?> <?php $topic_textarea_empty_val=isset($topic_textarea_empty_val)?$topic_textarea_empty_val:'分享文件'; ?> <?php $attach_uploadify_queue_size_limit=min(max(1,(int)$this->Config['attach_files_limit']),5); ?> <?php $attach_item=isset($this->item)?$this->item:''; ?> <?php $attach_itemid=isset($this->item_id)?$this->item_id:0; ?> <success></success> <script type="text/javascript">
var __ATTACH_IDS__ = {};
$(document).ready(function(){			
var upfilename = '';
$('#publisher_file_attach<?php echo $attach_uploadify_id; ?>').uploadify({
'uploader'  : '<?php echo $this->Config['site_url']; ?>/images/uploadify/uploadify.swf?id=<?php echo $attach_uploadify_id; ?>&random=' + Math.random(),
'script'    : '<?php echo urlencode($this->Config['site_url'] . "/ajax.php?mod=uploadattach&code=attach&aitem=$attach_item&aitemid=$attach_itemid"); ?>',
'cancelImg' : '<?php echo $this->Config['site_url']; ?>/images/uploadify/cancel.png',
'buttonImg'	: '<?php echo $this->Config['site_url']; ?>/images/uploadify/addatta.gif',
'width'		: 111,
'height'	: 17,
'multi'		: true,
'auto'      : true,
'sizeLimit' : <?php echo $attach_fz_siz; ?>,
'fileExt'	: '*.rar;*.zip;*.txt;*.doc;*.xls;*.pdf;*.ppt;*.docx;*.xlsx;*.pptx',
'fileDesc'	: '*.rar;*.zip;*.txt;*.doc;*.xls;*.pdf;*.ppt;*.docx;*.xlsx;*.pptx',
'queueID'	: 'uploadifyQueueDivAttach<?php echo $attach_uploadify_id; ?>',
'wmode'		: 'transparent',
'fileDataName'	 : 'topic',
'queueSizeLimit' : <?php echo $attach_uploadify_queue_size_limit; ?>,
'simUploadLimit' : 1,
'scriptData'	 : {
<?php if($attach_uploadify_topic_uid) { ?>
'topic_uid'  : '<?php echo $attach_uploadify_topic_uid; ?>',
<?php } ?>
'cookie_auth': '<?php echo urlencode(jsg_getcookie("auth")); ?>'
},
'onSelect'		 : function(event, ID, fileObj) {
},
'onSelectOnce'	 : function (event, data) {
attachUploadifySelectOnce<?php echo $attach_uploadify_id; ?>();			    	
},
'onProgress'     : function(event, ID, fileObj, data) {
return false;
},
'onComplete'	 : function(event, ID, fileObj, response, data) {
eval('var r = ' + response + ';');
if (r.done) {					
var rv = r.retval;
if ( rv.id > 0 && rv.src.length > 0 ) {
attachUploadifyComplete<?php echo $attach_uploadify_id; ?>(rv.id, rv.src, fileObj.name);
upfilename = fileObj.name;
}
}
else
{
if(r.msg)
{
if(r.msg=='forbidden'){
MessageBox('warning','您没有上传文件的权限，无法继续操作！');
}else{
MessageBox('warning', '上传失败，文件过大或过多或格式错误！');
}
}
}
},
'onError'        : function (event, ID, fileObj, errorObj) {
alert(errorObj.type + ' Error: ' + errorObj.info);
},
'onAllComplete'	 : function(event, data) {
attachUploadifyAllComplete<?php echo $attach_uploadify_id; ?>(upfilename);
}
});
$("#viewAttachDiv<?php echo $attach_uploadify_id; ?> img").each(function() {
if($(this).width() > $(this).parent().width()) {
$(this).width("100%");
}
});
});
//删除一个文件
function attachUploadifyDelete<?php echo $attach_uploadify_id; ?>(idval)
{
var idval = ('undefined'==typeof(idval) ? 0 : idval);
if(idval > 0) 
{
$.post
(
'ajax.php?mod=uploadattach&code=delete_attach',
{
'id' : idval
},
function (r) 
{				
if(r.done)
{
$('#uploadAttachSpan_' + idval).remove();
if( ($.trim(($('#viewAttachDiv<?php echo $attach_uploadify_id; ?>').html()))).length < 1 )
{
$('#viewAttachDiv<?php echo $attach_uploadify_id; ?>').css('display', 'none');
$('#insertAttachDiv<?php echo $attach_uploadify_id; ?>').css('display', 'block');
}
if( 'undefined' != typeof(__ATTACH_IDS__[idval]) )
{
__ATTACH_IDS__[idval] = 0;
}
}
else
{
if(r.msg)
{
MessageBox('warning', r.msg);
}
}
},
'json'
);
}
}
function attachUploadifySelectOnce<?php echo $attach_uploadify_id; ?>()
{
$('#uploadingAttach<?php echo $attach_uploadify_id; ?>').html("<img src='images/loading.gif'/>&nbsp;上传中，请稍候……");
}
function attachUploadifyComplete<?php echo $attach_uploadify_id; ?>(idval, srcval, nameval)
{
var attachIdsCount = 0;
$.each( __ATTACH_IDS__, function( k, v ) { if( v > 0 ) { attachIdsCount += 1; } } );
if( attachIdsCount >= <?php echo $attach_uploadify_queue_size_limit; ?> )
{
MessageBox('warning', '本次文件数量超过限制了');
return false;
}
var idval = ('undefined' == typeof(idval) ? 0 : idval);
var srcval = ('undefined' == typeof(srcval) ? 0 : srcval);
var nameval = ('undefined' == typeof(nameval) ? '' : nameval);
<?php if('topic_publish'==$attach_uploadify_from) { ?>
$('#viewAttachDiv<?php echo $attach_uploadify_id; ?>').prepend('<li id="uploadAttachSpan_' + idval + '" class="menu_ps vv_2"><img src="' + srcval + '" class="uploadAttachSpan_img_type"/> <p class="uploadAttachSpan_doc_type"><i>' + nameval + '</i></p><p>（<a title="删除文件" onclick="attachUploadifyDelete<?php echo $attach_uploadify_id; ?>(' + idval + ');return false;" href="javascript:;">删</a>）需<input title="填写用户下载该附件所需贡献给你的积分" size="1" type="text" onblur="set_attach_score(this.value,' + idval + ');return false;">积分 </p></li>');<?php } elseif('topic_longtext_info_ajax'==$attach_uploadify_from) { ?>$('#viewAttachDiv<?php echo $attach_uploadify_id; ?>').append('<span id="uploadAttachSpan_' + idval + '"><img src="' + srcval + '" width="<?php echo $attach_img_siz; ?>" alt="点击文件插入到文中" onclick="longtext_info_img_insert(\'' + srcval + '\');" />（<a href="javascript:void(0);" onclick="attachUploadifyDelete<?php echo $attach_uploadify_id; ?>(' + idval + '); return false;" title="删除文件">删</a>）需<input title="填写用户下载该附件所需贡献给你的积分" size="1" type="text" onblur="set_attach_score(this.value,' + idval + ');return false;">积分</span>');
<?php } else { ?>$('#viewAttachDiv<?php echo $attach_uploadify_id; ?>').append('<span id="uploadAttachSpan_' + idval + '"><img src="' + srcval + '" width="<?php echo $attach_img_siz; ?>" />（<a href="javascript:void(0);" onclick="attachUploadifyDelete<?php echo $attach_uploadify_id; ?>(' + idval + '); return false;" title="删除文件">删</a>）需<input title="填写用户下载该附件所需贡献给你的积分" size="1" type="text" onblur="set_attach_score(this.value,' + idval + ');return false;">积分</span>');
<?php } ?>
$('#normalAttachUploadFile<?php echo $attach_uploadify_id; ?>').val('');
__ATTACH_IDS__[idval] = idval;
}
function attachUploadifyAllComplete<?php echo $attach_uploadify_id; ?>(nameval)
{
var nameval = ('undefined' == typeof(nameval) ? '' : nameval);
$('#uploadingAttach<?php echo $attach_uploadify_id; ?>').html('');			    	
$('#viewAttachDiv<?php echo $attach_uploadify_id; ?>').css('display', 'block');
//$('#insertAttachDiv<?php echo $attach_uploadify_id; ?>').css('display', 'none');
if( $.trim(($('#<?php echo $topic_textarea_id; ?>').val())).length < 1 ) {
$('#<?php echo $topic_textarea_id; ?>').val('<?php echo $topic_textarea_empty_val; ?>' + ':' + nameval);	
}
$('#<?php echo $topic_textarea_id; ?>').focus();
}
function normalAttachUploadFormSubmit<?php echo $attach_uploadify_id; ?>()
{
var fileVal = $('#normalAttachUploadFile<?php echo $attach_uploadify_id; ?>').val();
if(($.trim(fileVal)).length < 1)
{
MessageBox('warning', '请上传正确格式的附件文件');
return false;
}
else
{
if(!(/\.(zip|rar|txt|doc|xls|pdf)$/i.test(fileVal)))
{
MessageBox('warning', '请选择一个正确格式的附件文件');
return false;
}
else
{
attachUploadifySelectOnce<?php echo $attach_uploadify_id; ?>();
return true;
}
}
}
function attachUploadifyModuleSwitch<?php echo $attach_uploadify_id; ?>()
{
if('none' == $('#normalAttachUploadDiv<?php echo $attach_uploadify_id; ?>').css('display'))
{
$('#uploadDescModuleSpanAttach<?php echo $attach_uploadify_id; ?>').html('快速');
$('#swfUploadDivAttach<?php echo $attach_uploadify_id; ?>').css('display', 'none');
$('#normalAttachUploadDiv<?php echo $attach_uploadify_id; ?>').css('display', 'block');
}
else
{
$('#uploadDescModuleSpanAttach<?php echo $attach_uploadify_id; ?>').html('普通');
$('#normalAttachUploadDiv<?php echo $attach_uploadify_id; ?>').css('display', 'none');
$('#swfUploadDivAttach<?php echo $attach_uploadify_id; ?>').css('display', 'block');
}
}
</script> <?php if(!$attach_uploadify_only_js) { ?> <div id="insertAttachDiv<?php echo $attach_uploadify_id; ?>" class="insertAttachDiv" style="border-bottom:1px solid #ddd;"> <i class="insertAttachDiv_up" onclick="$(this).parent().hide()"><img src="templates/default/images/imgdel.gif" title="关闭" /></i> <div id="swfUploadDivAttach<?php echo $attach_uploadify_id; ?>"><input type="file" id="publisher_file_attach<?php echo $attach_uploadify_id; ?>" name="publisher_file<?php echo $attach_uploadify_id; ?>" style="display:none;" />（按ctrl键可一次选多个文件）</div> <iframe id="attachUploadifyIframe<?php echo $attach_uploadify_id; ?>" name="attachUploadifyIframe<?php echo $attach_uploadify_id; ?>" width="0" height="0" marginwidth="0" frameborder="0" src="about:blank" style="display:none;"></iframe> <div id="normalAttachUploadDiv<?php echo $attach_uploadify_id; ?>" style="display:none;"> <form id="normalAttachUploadForm<?php echo $attach_uploadify_id; ?>" method="post"  action="ajax.php?mod=uploadattach&code=attach&type=normal&aitem=<?php echo $attach_item; ?>&aitemid=<?php echo $attach_itemid; ?>" enctype="multipart/form-data" target="attachUploadifyIframe<?php echo $attach_uploadify_id; ?>" onsubmit="return normalAttachUploadFormSubmit<?php echo $attach_uploadify_id; ?>()"> <input type="hidden" name="FORMHASH" value='<?php echo FORMHASH; ?>'/> <input type="hidden" name="attach_uploadify_id" value="<?php echo $attach_uploadify_id; ?>" /> <input type="file" id="normalAttachUploadFile<?php echo $attach_uploadify_id; ?>" name="topic" /> <input type="submit" value="上传" class="tul" /> </form> </div> <span id="uploadingAttach<?php echo $attach_uploadify_id; ?>"></span> <div id="uploadDescDivAttach<?php echo $attach_uploadify_id; ?>"> <span class="fontRed">*</span> 如果您不能上传文件，可以<a href="javascript:;" onclick="attachUploadifyModuleSwitch<?php echo $attach_uploadify_id; ?>();">点击这里</a>尝试<span id="uploadDescModuleSpanAttach<?php echo $attach_uploadify_id; ?>">普通</span>模式上传
<?php if('topic_longtext_info_ajax'==$attach_uploadify_from) { ?> <br /><span class="fontRed">*</span> 文件上传成功后，可以点击文件将文件插入到您想要的位置
<?php } ?> </div> <div id="uploadifyQueueDivAttach<?php echo $attach_uploadify_id; ?>" style="display:none;"></div> <div id="viewAttachDiv<?php echo $attach_uploadify_id; ?>" class="viewAttachDiv"> <?php if((!$attach_uploadify_new || $attach_uploadify_modify) && $attach_uploadify_topic['attachid']) { ?> <?php if(is_array($attach_uploadify_topic['attach_list'])) { foreach($attach_uploadify_topic['attach_list'] as $ik => $iv) { ?> <script type="text/javascript"> __ATTACH_IDS__[<?php echo $ik; ?>] = <?php echo $ik; ?>; </script> <span id="uploadAttachSpan_<?php echo $ik; ?>"> <img src="<?php echo $iv['attach_img']; ?>" width="<?php echo $attach_img_siz; ?>" />（<a href="javascript:void(0);" onclick="attachUploadifyDelete<?php echo $attach_uploadify_id; ?>('<?php echo $ik; ?>'); return false;" title="删除文件">删</a>）下载附件需消耗<input title="填写用户下载该附件所需贡献给你的积分" size="1" type="text" value="<?php echo $iv['attach_score']; ?>" onblur="set_attach_score(this.value,<?php echo $iv['id']; ?>);return false;">积分
</span> <?php } } ?> <?php } ?> </div> </div> <?php } ?> <?php } ?> <div class="menu"> <div class="menu_sp"><b class="menu_spb_c">视频</b> <div class="menu_spb" id="upload_ajax_video"> <div class="menu_tb"><span style="float:left; padding-left:5px;">支持如下视频的站内播放</span><div class="menu_spb_c1"></div></div> <p class="menu_p"><a href="http://video.sina.com.cn/" rel="nofollow" target="_blank">新浪</a>、<a href="http://www.youku.com/" rel="nofollow" target="_blank">优酷</a>、<a href="http://v.blog.sohu.com/" rel="nofollow" target="_blank">搜狐</a>、<a href="http://www.ku6.com/" rel="nofollow" target="_blank">酷6</a>、<a href="http://www.tudou.com/" rel="nofollow" target="_blank">土豆</a><br>请复制视频播放页网站地址即可</p> <div id="upload_video_list" class="menu_p" style="display:none;"> <span id="return_ajax_video_title"></span> <span><img id="video_img" width="130" /></span> <p> <input id="videoid" type="hidden" name="video_id" /> <span><a href="" onclick="DelVideo('videoid','video_ajax'); return false;" title="删除视频">删除视频</a></span> </p> </div> <div id="add_video" class="menu_p" style=" margin-bottom:6px; padding-top:0"> <iframe id="upload_video_frame" name="upload_video_frame" width="0" height="0" marginwidth="0" frameborder="0" src="about:blank"></iframe> <form action="ajax.php?mod=topic&code=dovideo" method="post"  enctype="multipart/form-data" name="upload_video" id="upload_video" target="upload_video_frame"> <input type="hidden" name="FORMHASH" value='<?php echo FORMHASH; ?>'/> <input name="url" type="text" id="url" class="sc_r_t_a" style=" width:220px; display:inline;"/> <input type="submit" name="Submit" value="提交" class="c_b1" /> </form> </div> </div></div> </div> <div class="menu"> <div class="menu_m"> <b class="menu_m_c">音乐</b> <div class="menu_music"> <div class="menu_tb"> <span style="float:left; padding-left:10px;">请在下面输入歌曲名或歌手名字搜索</span> <sub class="menu_music_c1"></sub> </div> <div id="add_music" class="menu_m_s" style=" margin-bottom:6px; padding:15px 10px 0; float:left;"> <form action="javascript:void(0);" method="post"  enctype="multipart/form-data" name="upload_music" id="upload_music"> <input type="hidden" name="FORMHASH" value='<?php echo FORMHASH; ?>'/> <input name="url" type="text" id="music_name" class="sc_r_t_a" style=" width:220px;"> <input type="submit" name="Submit" value="搜索" class="c_b1" onclick="music_serach();"> </form> </div> <p class="menu_p" style="padding:0 10px;">音乐后缀的url请直接粘贴到上面的发布框中</p> <div id="music_list" class="menu_m_l"></div> </div> </div> </div> <?php if($allow_attach) { ?> <div class="menu"> <div class="menu_fj" ><b class="menu_fjb_c">附件</b> <div class="menu_fjb"> <div class="menu_pi insertImgDiv" id="insertAttachDiv"> <div id="swfUploadDivAttach"><input type="file" id="publisher_file_attach" name="publisher_file" style="display:none;" />（按ctrl键可一次选多个文件）</div> <iframe id="attachUploadifyIframe" name="attachUploadifyIframe" width="0" height="0" marginwidth="0" frameborder="0" src="about:blank" style="display:none;"></iframe> <div id="normalAttachUploadDiv" style="display:none;"> <form id="normalAttachUploadForm" method="post"  action="ajax.php?mod=uploadattach&code=attach&type=normal&aitem=<?php echo $attach_item; ?>&aitemid=<?php echo $attach_itemid; ?>" enctype="multipart/form-data" target="attachUploadifyIframe" onsubmit="return normalAttachUploadFormSubmit()"> <input type="hidden" name="FORMHASH" value='<?php echo FORMHASH; ?>'/> <input type="file" id="normalAttachUploadFile" name="topic" /> <input type="submit" value="上传" class="tul" /> </form> </div> <i class="menu_fjb_c1"><img src="templates/default/images/imgdel.gif" title="关闭" /></i> <em>
1、如您不能上传文件，请<a href="javascript:;" onclick="attachUploadifyModuleSwitch();">点击这里</a>用<span id="uploadDescModuleSpanAttach">普通</span>模式上传.<br />
2、一次最多可上传<?php echo $attach_num; ?>个文件，单个文件大小不超过<?php echo $attach_size; ?>。
</em> <span id="uploadingAttach"></span> <div class="viewImgDiv" id="viewAttachDiv"></div> </div> <div id="uploadifyQueueDivAttach" style="display:none;"></div> </div> </div> </div> <?php } ?> <div class="menu" > <div class="menu_ht" id="button_<?php echo MEMBER_ID; ?>"><span onclick="get_tag_choose(<?php echo MEMBER_ID; ?>,'my_tag','<?php echo $h_key; ?>');return false;" class="menu_htb_c">话题</span> <div class="menu_htb"><div id="<?php echo $h_key; ?>tag_<?php echo MEMBER_ID; ?>"></div></div> </div> </div> <?php if($this->Config['vote_open'] && !$set_vote_closed && !($this->Get['mod'] == 'talk' || $this->Get['mod'] == 'live' || $type == 'answer' || $type == 'ask')) { ?> <div class="menu"> <div class="menu_vs"><b class="menu_vsb_c">投票</b> <div class="menu_vsb"> <div class="menu_vsbox"> <p class="stitle"> <b id="vote_content1" class="vhover" onclick="setTab('vote_content',1,3)">创建新的投票</b> <b id="vote_content2" onclick="setTab('vote_content',2,3);getMyVoteList(1);">我发起的</b> <b id="vote_content3" onclick="setTab('vote_content',3,3);getMyJoinList(1);">我参与的</b> <sub class="menu_vsb_c1"></sub> </p> <div class="vcontent" id="con_vote_content_1"> <p>正在加载...</p> </div> <div class="vcontent vote_conLi" id="con_vote_content_2" style="display:none;"> <p>正在加载...</p> </div> <div class="vcontent vote_conLi" id="con_vote_content_3" style="display:none;"> <p>正在加载...</p> </div> </div> </div> </div> </div> <?php } ?> <?php if($this->Config['event_open'] == 1 && !$set_event_closed && !($this->Get['mod'] == 'talk' || $this->Get['mod'] == 'live' || $type == 'answer' || $type == 'ask')) { ?> <div class="menu"> <div class="menu_hd"><b class="menu_hdb_c">活动</b> <div class="menu_hdb"> <div class="menu_hdbox"> <p class="stitle"> <b id="event_content1" class="vhover" onclick="setTab1('event_content',1,3)">发起新的活动</b> <b id="event_content2" onclick="setTab1('event_content',2,3);getMyEventList(1);">我发起的</b> <b id="event_content3" onclick="setTab1('event_content',3,3);getJoinEventList(1);">我参与的</b> <sub class="menu_hdb_c1"></sub> </p> <div class="vcontent" id="con_event_content_1"> <p>正在加载...</p> </div> <div class="vcontent vote_conLi" id="con_event_content_2" style="display:none;"> <p>正在加载...</p> </div> <div class="vcontent vote_conLi" id="con_event_content_3" style="display:none;"> <p>正在加载...</p> </div> </div> </div> </div> </div> <?php } ?> <?php if($this->Config['qun_setting']['qun_open'] && !$set_qun_closed && !($this->Get['mod'] == 'talk' || $this->Get['mod'] == 'live' || $type == 'answer' || $type == 'ask')) { ?> <div class="menu"> <div class="menu_wq"> <b class="menu_wqb_c">微群</b> <div class="menu_wqb"> <div class="menu_wqbox" style="width:210px;"> <div class="menu_tb" style="width:210px;"> <span style="float:left; padding-left:5px;">选择你要发布到的群</span> <sub class="menu_wqb_c1"></sub> </div> <div class="wcontent" id="wcontent_wp"></div> </div> </div> </div> </div> <?php } ?> <?php if($this->Config['fenlei_open'] == 1 && !$set_fenlei_closed) { ?> <div class="menu"> <div class="menu_x"> <b class="menu_xb_c">分类</b> <div class="menu_xb"> <div class="menu_xbox"> <p class="stitle"> <b id="vote2_content1" class="vhover2" onclick="setTab2('vote2_content',1,2)">新的分类</b> <b id="vote2_content2" onclick="setTab2('vote2_content',2,2);getMyFenleiList(1);">我发布的分类</b> <sub class="menu_xb_c1"></sub> </p> <div class="vcontent" id="con2_vote2_content_1"> <p>正在加载...</p> </div> <div class="vcontent vote_conLi" id="con2_vote2_content_2" style="display:none;"> <p>正在加载...</p> </div> </div> </div> </div> </div> <?php } ?> <?php echo $this->hookall_temp['global_publish_extra1']; ?> <?php } else { ?><div class="box_3ajax"> <?php } ?> </div> <div class="box_4"> <?php if ($this->Get['mod'] == 'tag') $type = 'tagview' ;elseif ($this->Get['mod'] == 'member') $type = 'tohome';elseif ($this->Get['mod'] == 'vote') $type='vote';elseif ($this->Get['mod'] == 'live') $type='live';elseif ($this->Get['mod'] == 'talk') $type='talk';elseif ($this->Get['mod'] == 'fenlei') $type='fenlei';elseif ($this->Get['mod'] == 'event') $type='event';elseif ($this->Get['mod'] == 'reward') $type='reward'; else $type = $params['code']; ?> <?php $type = $type ? $type : $this->Code; ?> <input type="button" class="indexBtn" id="publishSubmit<?php echo $h_key; ?>" title="按Ctrl+Enter快捷发布"/> <?php if(in_array($this->Get['mod'], array('qun','live','talk','event','vote','fenlei','reward')) || $this->Get['type'] == 'ask') { ?> <?php $topic_type_value = $this->Get['type'] == 'ask' ? $this->Get['item'] : $this->Get['mod']; ?> <div class="box_4_open"> <b class="box_4_open_span" style="padding:0;"> <label><input id="chk_toweibo<?php echo $h_key; ?>" type="checkbox" checked="checked" onclick="selectAppTopicType(this.id, {toid:'topic_type<?php echo $h_key; ?>', defTopicType:'<?php echo $topic_type_value; ?>'})">同步发作微博</label></b> </div> <?php } else { ?><div class="box_4_open" id="weibo_syn_wp"> <b class="box_4_open_span">同步发到</b> <div class="box_4_open_Box"> <sub class="box_4_open_span_c1"></sub> <?php if($this->Config['sina_enable'] && sina_weibo_init()) { ?> <p> <?php echo sina_weibo_syn(); ?> </p> <?php } ?> <?php if($this->Config['qqwb_enable'] && qqwb_init()) { ?> <p> <?php echo qqwb_syn(); ?> </p> <?php } ?> <?php if($this->Config['kaixin_enable'] && kaixin_init()) { ?> <p> <?php echo kaixin_syn_html(); ?> </p> <?php } ?> <?php if($this->Config['renren_enable'] && renren_init()) { ?> <p> <?php echo renren_syn_html(); ?> </p> <?php } ?> </div> </div> <?php if($this->Channel_enable) { ?> <script>
$(document).ready(function(){
$("#p_channel,#t_pb").bind('mouseover', function(){$('#p_channel').show();$('#t_pb').addClass('hover');});
$("#p_channel,#t_pb").bind('mouseout', function(){$('#p_channel').hide();$('#t_pb').removeClass('hover');});
});
function c_hide(){$('#p_channel').hide();$('#t_pb').removeClass('hover');}
function c_cut(){$('#t_channel').html('');$('#mapp_item<?php echo $h_key; ?>').val('<?php echo $this->item; ?>');$('#mapp_item_id<?php echo $h_key; ?>').val('<?php echo $this->item_id; ?>');}
function c_int(n,s){$('#p_channel').hide();$('#t_pb').removeClass('hover');$('#t_channel').html(s+'<em onclick="c_cut();">×</em>');$('#mapp_item<?php echo $h_key; ?>').val('channel');$('#mapp_item_id<?php echo $h_key; ?>').val(n);}
</script> <div class="box_4_channel"> <span class="select" id="t_pb">发布到频道</span><span class="channel" id="t_channel"></span> <div class="channels" id="p_channel"> <span class="close" onclick="c_hide();">×</span> <?php if($this->Channels) { ?> <?php if(is_array($this->Channels)) { foreach($this->Channels as $val) { ?> <dl> <dt><a onclick="c_int(<?php echo $val['ch_id']; ?>,'<?php echo $val['ch_name']; ?>');"><?php echo $val['ch_name']; ?></a></dt> <dd> <?php if($val['child']) { ?> <?php if(is_array($val['child'])) { foreach($val['child'] as $v) { ?> <a onclick="c_int(<?php echo $v['ch_id']; ?>,'<?php echo $v['ch_name']; ?>');"><?php echo $v['ch_name']; ?></a> <?php } } ?> <?php } else { ?><a href="javascript:void(0);">&nbsp;</a> <?php } ?> </dd> </dl> <?php } } ?> <?php } else { ?><p>没有频道可供发布</p> <?php } ?> </div> </div> <?php } ?> <?php } ?> </div> </div> </div> <script type="text/javascript">		
$("#i_already<?php echo $h_key; ?>").bind('focus', function(){
$('#tigBox_2').css('visibility', 'visible');
var i=0;
setTimeout(function(){i+=1; $('#tigBox_2').css('visibility', 'hidden'); },5000);
});
$("#publishSubmit<?php echo $h_key; ?>").bind('click',function() {
publishSubmit('i_already<?php echo $h_key; ?>','totid<?php echo $h_key; ?>','<?php echo $type; ?>','topic_type<?php echo $h_key; ?>','','',$('#mapp_item<?php echo $h_key; ?>').val(),$('#mapp_item_id<?php echo $h_key; ?>').val(),$('#xiami_id').val(),$('#touid<?php echo $h_key; ?>').val());
return false;
});
$(document).ready(function(){
initAiInput('i_already<?php echo $h_key; ?>');
checkWord('<?php echo $this->Config['topic_input_length']; ?>','i_already<?php echo $h_key; ?>','wordCheck<?php echo $h_key; ?>');
});
/*
$("#i_already").bind('keydown',function(event) {
event = event || window.event;
if (event.keyCode == 13 && event.ctrlKey) {
$("#publishSubmit").click();
};
});*/
</script>