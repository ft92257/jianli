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
> <?php if($this->pluginid) { ?> <a href="<?php echo $value['link']; ?>&id=<?php echo $this->pluginid; ?>"> <?php } else { ?><a href="<?php echo $value['link']; ?>"> <?php } ?> <?php echo $value['name']; ?></a> </li> <?php } } ?> </ul> </div> <?php } ?> <br /> <?php if('modify'==$this->Code) { ?> <form method="post"  action="admin.php?mod=api&code=do_modify"> <input type="hidden" name="FORMHASH" value='<?php echo FORMHASH; ?>'/><input
type="hidden" name="id" value="<?php echo $app['id']; ?>" /> <table cellspacing="1" cellpadding="4" width="100%" align="center"
class="tableborder"> <tr class="header"> <td colspan="2">APP编辑</td> </tr> <tr class="altbg1"> <td width="40%"><b>APP KEY:</b> <span class="smalltxt"></span><br /> </td> <td><?php echo $app['app_key']; ?></td> </tr> <tr class="altbg2"> <td width="40%"><b>APP SECRET:</b> <span class="smalltxt"></span><br /> </td> <td><?php echo $app['app_secret']; ?></td> </tr> <tr class="altbg1"> <td width="40%"><b>应用名称:</b> <span class="smalltxt"></span><br /> </td> <td><input type="text" name="app_name" value="<?php echo $app['app_name']; ?>"
style="width: 300px;" /></td> </tr> <tr class="altbg2"> <td width="40%"><b>应用网址:</b> <span class="smalltxt"></span><br /> </td> <td><input type="text" name="source_url"
value="<?php echo $app['source_url']; ?>" style="width: 300px;" /></td> </tr> <tr class="altbg1"> <td width="40%"><b>应用介绍:</b> <span class="smalltxt"></span><br /> </td> <td><textarea name="app_desc" style="width: 300px;"><?php echo $app['app_desc']; ?></textarea></td> </tr> <tr class="altbg2"> <td width="40%"><b>显示来自详情？</b> <span class="smalltxt">选择“是”，微博页面会显示来自具体的API应用名称和相应的网址
</span><br /> </td> <td><?php echo $app_show_from_radio; ?></td> </tr> <tr class="altbg1"> <td width="40%"><b>开启本API应用？</b> <span class="smalltxt"></span><br /> </td> <td><?php echo $app_status_radio; ?></td> </tr> </table> <br /> <center><input class="button" type="submit" name="cronssubmit"
value="提 交" /> &nbsp; <input class="button" type="button" value="返 回"
onclick="window.location.href='admin.php?mod=api'" /></center> <br /> </form> <?php } else { ?><form method="post"  action="admin.php?mod=api&code=do_modify_setting"> <input type="hidden" name="FORMHASH" value='<?php echo FORMHASH; ?>'/> <table cellspacing="1" cellpadding="4" width="100%" align="center"
class="tableborder"> <tr class="header"> <td colspan="2">参数设置</td> </tr> <tr class="altbg1"> <td width="40%"><b>开启网站API应用:</b><br> <span class="smalltxt">选择是开启网站API应用，则其他应用可以调用本站的api接口来查询和发微博</span><br /> </td> <td><?php echo $app_enable_radio; ?> <a target="_blank"
href="http://cenwor.com/go.php?w=jsg.api.admin"><font color="red">点击查看API开发说明</font></a> </td> </tr> <tr class="altbg2"> <td width="40%"><b>开启来自API来源名称:</b><br> <span class="smalltxt">选择是开启，则每条微博下面会显示来自具体的API应用名称和并显示应用的网址</span><br /> </td> <td><?php echo $app_from_enable_radio; ?></td> </tr> <tr class="altbg1"> <td width="40%"><b>API每天请求次数限制:</b><br> <span class="smalltxt">填写具体的数字比如1000来限制api的请求次数，0为不限制</span><br /> </td> <td><input type="text" name="api[request_times_day_limit]"
value="<?php echo $api['request_times_day_limit']; ?>" /></td> </tr> </table> <br /> <center><input class="button" type="submit" name="cronssubmit"
value="提 交" /></center> <br /> <?php if($api_config['enable']) { ?> <table width="100%" border="0" cellpadding="0" cellspacing="0"
class="tableborder"> <tr class="header"> <td colspan="6">API应用列表</td> </tr> <?php if(!$app_list) { ?> <tr align="center" class="altbg1"> <td colspan="6" align="center">暂无，可通过在下面填写第一个应用名称来新增api应用。</td> </tr> <?php } else { ?><tr class="altbg1"> <td>应用名称</td> <td>AppKey<br />
AppSecret</td> <td>最后请求时间</td> <td>(日/周/月/年/总)请求数<br />
上(日/周/月/年/)请求数</td> <td>状态</td> <td>其他操作</td> </tr> <?php } ?> <?php if(is_array($app_list)) { foreach($app_list as $app) { ?> <tr align="center" class="altbg2"> <td> <?php if($app['source_url']) { ?> <a target="_blank"
href="<?php echo $app['source_url']; ?>"><b><?php echo $app['app_name']; ?></b></a> <?php } else { ?><?php echo $app['app_name']; ?> <?php } ?> </td> <td><?php echo $app['app_key']; ?><br /> <?php echo $app['app_secret']; ?></td> <td><?php echo $app['last_request_time_html']; ?></td> <td> <?php echo $app['request_times_day']; ?>/<?php echo $app['request_times_week']; ?>/<?php echo $app['request_times_month']; ?>/<?php echo $app['request_times_year']; ?>/<?php echo $app['request_times']; ?><br /> <?php echo $app['request_times_last_day']; ?>/<?php echo $app['request_times_last_week']; ?>/<?php echo $app['request_times_last_month']; ?>/<?php echo $app['request_times_last_year']; ?> </td> <td><?php echo $app['status_html']; ?></td> <td> <?php if($app['status']) { ?> <a
href="admin.php?mod=api&code=status0&id=<?php echo $app['id']; ?>">暂停</a> &nbsp; 
<?php } else { ?><a href="admin.php?mod=api&code=status1&id=<?php echo $app['id']; ?>">开启</a> &nbsp; 
<?php } ?> <a onclick="return confirm('删除后的内容不可恢复，确认删除？');"
href="admin.php?mod=api&code=delete&id=<?php echo $app['id']; ?>">删除</a> &nbsp; <a
href="admin.php?mod=api&code=modify&id=<?php echo $app['id']; ?>">编辑</a> &nbsp;</td> </tr> <?php } } ?> <tr class="altbg1"> <td colspan="6" align="center">填写应用名称： <input type="text"
name="app_name_new" value="" style="width: 200px;" /> <input
type="submit" name="app_submit" value="新 增" class="button" /></td> </tr> </table> <br /> <?php } ?> </form> <?php } ?>