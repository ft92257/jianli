<?php /* 2012-08-31 in jishigou invalid request template */ if(!defined("IN_JISHIGOU")) exit("invalid request"); ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> <html xmlns="http://www.w3.org/1999/xhtml"> <head> <?php $__my=$this->MemberHandler->MemberFields; ?> <?php $conf_charset=$this->Config['charset']; ?> <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $conf_charset; ?>" /> <link href="./templates/default/styles/admincp.css?build+20120829" rel="stylesheet" type="text/css" /> <script type="text/javascript">
var thisSiteURL = '<?php echo $this->Config['site_url']; ?>/';
var thisTopicLength = '<?php echo $this->Config['topic_input_length']; ?>';
var thisMod = '<?php echo $this->Module; ?>';
var thisCode = '<?php echo $this->Code; ?>';
var thisFace = '<?php echo $__my['face_small']; ?>';
<?php $qun_setting = ConfigHandler::get('qun_setting'); ?> <?php if($qun_setting['qun_open']) { ?>
var isQunClosed = false;
<?php } else { ?>var isQunClosed = true;
<?php } ?>
function faceError(imgObj)
{
var errorSrc = '<?php echo $this->Config['site_url']; ?>/images/noavatar.gif';
imgObj.src = errorSrc;
}
</script> <script type="text/javascript" type="text/javascript" src="./templates/default/js/cookies.js?build+20120829"></script> <script type="text/javascript" src="templates/default/js/min.js?build+20120829"></script> <script type="text/javascript" src="templates/default/js/common.js?build+20120829"></script> <script type="text/javascript" src="templates/default/js/admin_script_common.js?build+20120829"></script> <script language="JavaScript">
function checkalloption(form, value) {
for(var i = 0; i < form.elements.length; i++) {
var e = form.elements[i];
if(e.value == value && e.type == 'radio' && e.disabled != true) {
e.checked = true;
}
}
}
function checkallvalue(form, value, checkall) {
var checkall = checkall ? checkall : 'chkall';
for(var i = 0; i < form.elements.length; i++) {
var e = form.elements[i];
if(e.type == 'checkbox' && e.value == value) {
e.checked = form.elements[checkall].checked;
}
}
}
function zoomtextarea(objname, zoom) {
zoomsize = zoom ? 10 : -10;
obj = $(objname);
if(obj.rows + zoomsize > 0 && obj.cols + zoomsize * 3 > 0) {
obj.rows += zoomsize;
obj.cols += zoomsize * 3;
}
}
function redirect(url) {
window.location.replace(url);
}
function checkall(form, prefix, checkall) {
var checkall = checkall ? checkall : 'chkall';
for(var i = 0; i < form.elements.length; i++) {
var e = form.elements[i];
if(e.name != checkall && (!prefix || (prefix && e.name.match(prefix)))) {
e.checked = form.elements[checkall].checked;
}
}
}
var collapsed = Cookies.getCookie('guanzhu_collapse');
function collapse_change(menucount) {
if(document.getElementById('menu_' + menucount).style.display == 'none') {
document.getElementById('menu_' + menucount).style.display = '';collapsed = collapsed.replace('[' + menucount + ']' , '');
$('menuimg_' + menucount).src = './templates/default/images/admincp/menu_reduce.gif';
} else {
document.getElementById('menu_' + menucount).style.display = 'none';collapsed += '[' + menucount + ']';
$('menuimg_' + menucount).src = './templates/default/images/admincp/menu_add.gif';
}
Cookies.setCookie('guanzhu_collapse', collapsed, 2592000);
}
function advance_search(o)
{
o.innerHTML=$('advance_search').visible()?"高级搜索":"简单搜索";
$('advance_search').toggle();
return false;
}
</script> </head> <body> <div id="show_message_area"></div> <table width="100%" border="0" cellpadding="2" cellspacing="6" style="_margin-left:-10px; "> <tr> <td><table width="100%" border="0" cellpadding="2" cellspacing="6"> <tr> <td> <?php if($__is_messager!=true) { ?> <div style="width:100%; height:15px;color:#000;margin:0px 0px 10px;"> <div style="float:left;"><a href="admin.php?mod=index&code=home">控制面板首页</a>&nbsp;&raquo;&nbsp;
<?php if($pluginconfig && $pluginname) { ?> <?php echo $pluginconfig; ?>&nbsp;&raquo;&nbsp;<?php echo $pluginname; ?> <?php } elseif($this->pluginconfig && $this->pluginname) { ?> <?php echo $this->pluginconfig; ?>&nbsp;&raquo;&nbsp;<?php echo $this->pluginname; ?> <?php } else { ?> <?php echo $this->actionName(); ?> <?php } ?> </div> <?php if($this->RoleActionId) { ?> <div style="float: right;"><a title="查看谁操作过这个页面" href="admin.php?mod=logs&role_action_id=<?php echo $this->RoleActionId; ?>"><b style="color:red">查看当前页操作记录</b></a></div> <?php } ?> </div> <?php } ?> <?php if($this->Config['company_enable']) { ?> <?php $d_c_name = $this->Config['default_company'] ? $this->Config['default_company'] : '单位'; $d_d_name = $this->Config['default_department'] ? $this->Config['default_department'] : '部门';  ?> <?php } ?> <?php $sub_menu_list = $_sub_menu_list?$_sub_menu_list:get_sub_menu(); ?> <?php if($sub_menu_list) { ?> <div class="nav3"> <ul class="cc"> <?php if(is_array($sub_menu_list)) { foreach($sub_menu_list as $value) { ?> <?php if($value['type'] == '1' && PLUGINDEVELOPER < 1)continue; ?> <li 
<?php if($value['current']) { ?>
class="current"
<?php } ?>
> <?php if($this->pluginid) { ?> <a href="<?php echo $value['link']; ?>&id=<?php echo $this->pluginid; ?>"> <?php } else { ?><a href="<?php echo $value['link']; ?>"> <?php } ?> <?php echo $value['name']; ?></a> </li> <?php } } ?> </ul> </div> <?php } ?> <br /> <form method="post"  name="config[settings]" action="admin.php?mod=setting&code=domodify_normal" enctype="multipart/form-data"> <input type="hidden" name="FORMHASH" value='<?php echo FORMHASH; ?>'/> <a name="图片选项"></a> <table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder"> <tr class="header"> <td colspan="2">图片上传相关设置</td> </tr> <tr class="altbg2"> <td width="45%"><b>一次上传多图数量限制:</b><br> <span class="smalltxt">多图上传时，一次性最多只能上传多少张？默认为3张</span></td> <td><input type="text" name="config[image_uploadify_queue_size_limit]" value="<?php echo $this->Config['image_uploadify_queue_size_limit']; ?>" size="4" /></td> </tr> <tr class="altbg1"> <td width="45%"><b>指定图片缩略图像素大小:</b><br> <span class="smalltxt">微博中上传图片按比例生成缩略图；<BR>
比如设置为宽：120px，高：120px，但都不能小于60px</span></td> <td>
宽<input type="text" name="config[thumbwidth]" value="<?php echo $this->Config['thumbwidth']; ?>" size="4" />px，高<input type="text" name="config[thumbheight]" value="<?php echo $this->Config['thumbheight']; ?>" size="4" />px			</td> </tr> <tr class="altbg2"> <td width="45%"><b>指定缩略图截取方法:</b><br> <span class="smalltxt">微博中上传图片按比例生成缩略图，宽高不会超过本设定；<BR> </span></td> <td><?php echo $thumb_cut_type_radio; ?></td> </tr> <tr class="altbg1"> <td width="45%"><b>保存图片的最大像素尺寸:</b><br> <span class="smalltxt">原始数码图片像素和文件都很大，则系统会按照本设置进行缩小后再保存到服务器；<br>
比如可以设置为 宽：600px，高：1600px，但都不能小于300px。</span></td> <td>
宽<input type="text" name="config[maxthumbwidth]" value="<?php echo $this->Config['maxthumbwidth']; ?>" size="4" />px，高<input type="text" name="config[maxthumbheight]" value="<?php echo $this->Config['maxthumbheight']; ?>" size="4" />px			</td> </tr> <tr class="altbg2"> <td width="45%"><b>对上传图片打水印:</b><br> <span class="smalltxt">选择“是”将开启图片水印功能，如需支持中文水印，<a target="_blank" href="http://www.jishigou.net/images/jsg.ttf">请点此下载中文字体</a><br>
或从C:/windows/fonts拷贝一种字体文件改名为jsg.ttf，再通过FTP上传为 ./images/jsg.ttf </span></td> <td> <?php echo $watermark_enable_radio; ?> </td> </tr> <tr class="altbg1"> <td width="45%"><b>图片水印内容:</b><br> <span class="smalltxt">选择用户昵称，必须上传字体到./images/jsg.ttf</span></td> <td><?php echo $watermark_contents_radio; ?></td> </tr> <tr class="altbg2"> <td width="45%"><b>水印字体大小:</b><br> <span class="smalltxt">字体大小仅对truetype字体有效<br>默认为12</span></td> <td><input type="text" name="config[watermark_contents_size]" value="<?php echo $this->Config['watermark_contents_size']; ?>" size="2" /></td> </tr> <tr class="altbg1"> <td width="45%"><b>水印字体颜色:</b><br> <span class="smalltxt">默认为白色(#ffffff)</span></td> <td><input type="text" name="config[watermark_contents_color]" value="<?php echo $this->Config['watermark_contents_color']; ?>" size="8" /></td> </tr> <tr class="altbg2"> <td width="45%"><b>图片水印位置:</b><br> <span class="smalltxt">即水印文字显示在图片上的方位</span></td> <td> <?php echo $watermark_position_radio; ?> </td> </tr> </table> <br> <center><input type="submit" class="button" name="settingsubmit" value="提 交"></center><br> </form> <br>