<?php /* 2012-08-31 in jishigou invalid request template */ if(!defined("IN_JISHIGOU")) exit("invalid request"); ?> 
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN"><head> <?php $conf_charset=$this->Config['charset']; ?> <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $conf_charset; ?>" /> <meta http-equiv="x-ua-compatible" content="ie=7" /> <title>记事狗微博系统后台管理</title> <link href="./templates/default/admin/admin_m.css?build+20120829" rel="stylesheet" type="text/css"> </head> <body scroll="yes" style="height:100%"> <script type="text/javascript">
function setTab(name,cursel,n){
for(i=1;i<=n;i++){
var menu=document.getElementById(name+i);
var con=document.getElementById("con_"+name+"_"+i);
try {
menu.className=i==cursel?"navon":"";
con.style.display=i==cursel?"block":"none";
}catch(e){}
}
return false;
}
</script> <table width="100%" height="100%"  border="0" cellpadding="0" cellspacing="0"> <tr> <td colspan="2" height="80" valign="top"><div id="header"> <div class="logo fl"> <div class="png"><a href="<?php echo $this->Config['site_url']; ?>/admin.php"><img width="160" height="43" src="./templates/default/admin/images/logo.gif" alt=" 记事狗微博系统 " /></a></div> <div class="lun"><span style="color:#FA891B">V<?php echo $this->Config['sys_version']; ?></span> <?php echo SYS_PUBLISHED; ?> <?php echo SYS_BUILD; ?> </div> </div> <ul class="nav"> <?php if(is_array($menuList)) { foreach($menuList as $i => $menuOne) { ?> <li id="nav<?php echo $i; ?>" onClick="return setTab('nav',<?php echo $i; ?>,12)"
<?php if($i==1) { ?>
class="navon"
<?php } ?>
><em><a href="#"><?php echo $menuOne['title']; ?></a></em></li> <?php } } ?> </ul> <div class="wei fl">当前用户：<a title="查看或修改我的相关信息" href="admin.php?mod=member&code=modify" target="main"><?php echo MEMBER_NICKNAME; ?></a>（
<?php if(true===JISHIGOU_FOUNDER) { ?>
网站创始人&
<?php } ?> <?php echo $GLOBALS['_J']['member']['role_name']; ?> | <a href="admin.php?mod=login&code=logout">退出后台</a>） &nbsp;|&nbsp; <a href="admin.php?mod=cache" target="main">清空缓存</a> &nbsp;|&nbsp; <a title="在新窗口中打开访问首页" href="index.php" style="cursor: pointer;" class="s0" target="_blank">网站首页</a> &nbsp;|&nbsp; <a href="admin.php?mod=upgrade" target="main" title="在线升级到记事狗最新版本">在线升级</a> &nbsp;</div> <div class="wei2 fr"> <TABLE> <TR> <TD valign="top"> <div class="wei2_t1"> <form method="get" name="settings" action="admin.php" target="main"> <input type="hidden" name="mod" value="search" /> <input type="hidden" name="code" value="menu" /> <input type="text" name="keyword" value="" class="wei2_t11" style="width:100px;"/> <input type="submit" class="button" name="settingsubmit" value="查找功能" /> </form> </div> </TD> <TD valign="top"> <div class="wei2_t2"> <img title="商业用户可QQ在线咨询记事狗客服" style="cursor: pointer" onClick="javascript:window.open('http://b.qq.com/webc.htm?new=0&sid=800058566&eid=2188z8p8p8p8R8z8R8x8x&o=cenwor.com&q=7', '_blank', 'height=544, width=644,toolbar=no,scrollbars=no,menubar=no,status=no');"  border="0" src="./templates/default/images/admincp/qq.gif"> </div> </TD> </TR> </TABLE> </div> </div></td> </tr> <tr> <td valign="top" id="main-fl"><div id="left"> <?php if(is_array($menuList)) { foreach($menuList as $i => $menuOne) { ?> <div id="con_nav_<?php echo $i; ?>"
<?php if($i>1) { ?>
style="display:none;"
<?php } ?>
> <h1> <?php if($i>1) { ?> <?php echo $menuOne['title']; ?> <?php } else { ?>常用操作 [<a style="background:none;padding:0;margin:0;display:inline;" href="admin.php?mod=setting&code=modify_shortcut" target="main">设置</a>]
<?php } ?> </h1> <div class="cc"/> </div> <ul> <?php if(is_array($menuOne['sub_menu_list'])) { foreach($menuOne['sub_menu_list'] as $j => $menu) { ?> <?php if($menu['type'] == '1' && PLUGINDEVELOPER < 1)continue; ?> <?php if($menu['link']!='hr') { ?> <li><a href="<?php echo $menu['link']; ?>" target="main"><?php echo $menu['title']; ?></a></li> <?php } else { ?> </ul> <h1><?php echo $menu['title']; ?></h1> <div class="cc"/> </div> <ul> <?php } ?> <?php } } ?> </ul> </div> <?php } } ?> </td> <td valign="top" id="mainright" style="height:94%; "> <iframe name="main" frameborder="0" width="100%" height="100%" frameborder="0" scrolling="yes" style="overflow: visible;" src="admin.php?mod=index&code=home"> </iframe> </td> </tr> </table> </body> </html>