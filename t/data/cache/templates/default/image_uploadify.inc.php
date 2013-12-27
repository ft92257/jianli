<?php /* 2012-08-31 in jishigou invalid request template */ if(!defined("IN_JISHIGOU")) exit("invalid request"); ?>
<?php $image_uploadify_id=$content_textarea_id.$image_uploadify_type.($image_uploadify_topic['tid']>0?"_".$image_uploadify_topic['tid']:""); ?> <?php $image_uploadify_image_small_size=$image_uploadify_image_small_size?$image_uploadify_image_small_size:45; ?> <?php $content_textarea_id=$content_textarea_id?$content_textarea_id:'i_already'.$h_key; ?> <?php $content_textarea_empty_val=isset($content_textarea_empty_val)?$content_textarea_empty_val:'分享图片'; ?> <?php $image_uploadify_queue_size_limit=max(0, (int) $this->Config['image_uploadify_queue_size_limit']);if($image_uploadify_queue_size_limit<1)$image_uploadify_queue_size_limit=3; ?> <?php $img_item=isset($this->item)?$this->item:''; ?> <?php $img_itemid=isset($this->item_id)?$this->item_id:0; ?> <success></success> <script type="text/javascript">
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
<?php } ?> </div> <div id="uploadifyQueueDiv<?php echo $image_uploadify_id; ?>" style="display:none;"></div> <div id="viewImgDiv<?php echo $image_uploadify_id; ?>" class="viewImgDiv"> <?php if((!$image_uploadify_new || $image_uploadify_modify) && $image_uploadify_topic['imageid']) { ?> <?php if(is_array($image_uploadify_topic['image_list'])) { foreach($image_uploadify_topic['image_list'] as $ik => $iv) { ?> <script type="text/javascript"> __IMAGE_IDS__[<?php echo $ik; ?>] = <?php echo $ik; ?>; </script> <span id="uploadImgSpan_<?php echo $ik; ?>"> <img src="<?php echo $iv['image_small']; ?>" width="<?php echo $image_uploadify_image_small_size; ?>" /> <a href="javascript:void(0);" onclick="imageUploadifyDelete<?php echo $image_uploadify_id; ?>('<?php echo $ik; ?>'); return false;" title="删除图片">×</a> </span> <?php } } ?> <?php } ?> </div> </div> <?php } ?>