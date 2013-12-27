<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename admin_left_menu.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-08-17 19:12:47 1494907256 1394363538 13807 $
 *******************************************************************/

 $menu_list = array (
  1 => 
  array (
    'title' => '常用操作',
    'link' => 'admin.php?mod=index&code=home',
  ),
  2 => 
  array (
    'title' => '全局设置',
    'link' => 'admin.php?mod=index&code=home',
    'sub_menu_list' => 
    array (
      0 => 
      array (
        'title' => '<font color=blue>网站核心设置</font>',
        'link' => 'admin.php?mod=setting&code=modify_normal',
        'shortcut' => true,
      ),
      1 => 
      array (
        'title' => '注册访问控制',
        'link' => 'admin.php?mod=setting&code=modify_register',
        'shortcut' => true,
      ),
      2 => 
      array (
        'title' => '自动关注和推荐',
        'link' => 'admin.php?mod=setting&code=regfollow',
        'shortcut' => false,
      ),
      3 => 
      array (
        'title' => 'UCenter整合',
        'link' => 'admin.php?mod=ucenter&code=ucenter',
        'shortcut' => false,
      ),
      4 => 
      array (
        'title' => '<font color=red>调用Discuz论坛</font>',
        'link' => 'admin.php?mod=dzbbs&code=discuz_setting',
        'shortcut' => false,
      ),
      5 => 
      array (
        'title' => '伪静态和SEO',
        'link' => 'admin.php?mod=setting&code=modify_rewrite',
        'shortcut' => false,
      ),
      6 => 
      array (
        'title' => '积分规则和等级',
        'link' => 'admin.php?mod=setting&code=modify_credits',
        'shortcut' => false,
      ),
      7 => 
      array (
        'title' => '顶部导航菜单',
        'link' => 'admin.php?mod=setting&code=navigation',
        'shortcut' => false,
      ),
      8 => 
      array (
        'title' => '<font color=blue>皮肤风格设置</font>',
        'link' => 'admin.php?mod=show&code=modify_theme',
        'shortcut' => true,
      ),
      9 => 
      array (
        'title' => '省市区域设置',
        'link' => 'admin.php?mod=city',
        'shortcut' => false,
      ),
      10 => 
      array (
        'title' => '手机短信',
        'link' => 'admin.php?mod=sms',
        'shortcut' => false,
      ),
      11 => 
      array (
        'title' => '帐户绑定设置',
        'link' => 'admin.php?mod=setting&code=modify_sina',
        'shortcut' => false,
      ),
      12 => 
      array (
        'title' => '名人堂设置',
        'link' => 'admin.php?mod=vipintro&code=people_setting',
        'shortcut' => true,
      ),
      13 => 
      array (
        'title' => '<font color=blue>微博评论模块</font>',
        'link' => 'admin.php?mod=output&code=output_setting',
        'shortcut' => true,
      ),
      14 => 
      array (
        'title' => '内容设置',
        'link' => 'hr',
        'shortcut' => false,
      ),
      15 => 
      array (
        'title' => '我的首页幻灯',
        'link' => 'admin.php?mod=setting&code=modify_slide',
        'shortcut' => true,
      ),
      16 => 
      array (
        'title' => '广告管理',
        'link' => 'admin.php?mod=income',
        'shortcut' => false,
      ),
      17 => 
      array (
        'title' => '关于我们等',
        'link' => 'admin.php?mod=web_info',
        'shortcut' => false,
      ),
      18 => 
      array (
        'title' => '首页公告',
        'link' => 'admin.php?mod=notice',
        'shortcut' => false,
      ),
      19 => 
      array (
        'title' => '友情链接',
        'link' => 'admin.php?mod=link',
        'shortcut' => true,
      ),
    ),
  ),
  3 => 
  array (
    'title' => '内容管理',
    'link' => '',
    'sub_menu_list' => 
    array (
      0 => 
      array (
        'title' => '微博管理',
        'link' => 'admin.php?mod=topic&code=topic_manage',
        'shortcut' => false,
      ),
      1 => 
      array (
        'title' => '<font color=blue>待审核微博</font>',
        'link' => 'admin.php?mod=topic&code=verify',
        'shortcut' => true,
      ),
      2 => 
      array (
        'title' => '微博回收站',
        'link' => 'admin.php?mod=topic&code=del&del=1',
        'shortcut' => false,
      ),
      3 => 
      array (
        'title' => '内容过滤设置',
        'link' => 'admin.php?mod=setting&code=modify_filter',
        'shortcut' => true,
      ),
      4 => 
      array (
        'title' => '微博举报管理',
        'link' => 'admin.php?mod=report',
        'shortcut' => true,
      ),
      5 => 
      array (
        'title' => '官方推荐',
        'link' => 'admin.php?mod=recdtopic',
        'shortcut' => false,
      ),
      6 => 
      array (
        'title' => '话题和专题管理',
        'link' => 'admin.php?mod=tag',
        'shortcut' => false,
      ),
      7 => 
      array (
        'title' => '管理记录',
        'link' => 'admin.php?mod=topic&code=manage',
        'shortcut' => false,
      ),
      8 => 
      array (
        'title' => '个人信息管理',
        'link' => 'hr',
        'shortcut' => false,
      ),
      9 => 
      array (
        'title' => '签名管理',
        'link' => 'admin.php?mod=topic&code=signature',
        'shortcut' => false,
      ),
      10 => 
      array (
        'title' => '头像签名审核',
        'link' => 'admin.php?mod=verify',
        'shortcut' => false,
      ),
      11 => 
      array (
        'title' => '自我介绍管理',
        'link' => 'admin.php?mod=topic&code=aboutme',
        'shortcut' => false,
      ),
      12 => 
      array (
        'title' => '个人标签管理',
        'link' => 'admin.php?mod=user_tag',
        'shortcut' => false,
      ),
      13 => 
      array (
        'title' => '私信管理',
        'link' => 'admin.php?mod=pm',
        'shortcut' => false,
      ),
    ),
  ),
  4 => 
  array (
    'title' => '用户管理',
    'link' => '',
    'sub_menu_list' => 
    array (
      5 => 
      array (
        'title' => '+添加新用户',
        'link' => 'admin.php?mod=member&code=add',
        'shortcut' => false,
      ),
      2 => 
      array (
        'title' => '编辑用户',
        'link' => 'admin.php?mod=member&code=search',
        'shortcut' => true,
      ),
      8 => 
      array (
        'title' => '<font color=blue>用户V认证</font>',
        'link' => 'admin.php?mod=vipintro',
        'shortcut' => true,
      ),
      3 => 
      array (
        'title' => '修改我的资料',
        'link' => 'admin.php?mod=member&code=modify',
        'shortcut' => false,
      ),
      0 => 
      array (
        'title' => '用户列表',
        'link' => 'admin.php?mod=member&code=newm',
        'shortcut' => true,
      ),
      13 => 
      array (
        'title' => '封杀用户列表',
        'link' => 'admin.php?mod=member&code=force_out',
        'shortcut' => false,
      ),
      14 => 
      array (
        'title' => '上报领导列表',
        'link' => 'admin.php?mod=member&code=leaderlist',
        'shortcut' => false,
      ),
      16 => 
      array(
        'title' => '用户访问记录',
        'link' => 'admin.php?mod=member&code=login',
        'shortcut' => false,
      ),
      4 => 
      array (
        'title' => '当前在线用户',
        'link' => 'admin.php?mod=sessions',
        'shortcut' => false,
      ),
      15 => 
      array (
        'title' => '等待验证用户',
        'link' => 'admin.php?mod=member&code=waitvalidate',
        'shortcut' => false,
      ),
      6 => 
      array (
        'title' => '用户勋章',
        'link' => 'admin.php?mod=medal',
        'shortcut' => false,
      ),
      7 => 
      array (
        'title' => '媒体汇',
        'link' => 'admin.php?mod=media',
        'shortcut' => false,
      ),
      9 => 
      array (
        'title' => '导出用户到Excel',
        'link' => 'admin.php?mod=member&code=export_all_user',
        'shortcut' => false,
      ),
      1001 => 
      array (
        'title' => '角色权限设置',
        'link' => 'hr',
        'shortcut' => false,
      ),
      10 => 
      array (
        'title' => '管理员角色',
        'link' => 'admin.php?mod=role&code=list&type=admin',
        'shortcut' => false,
      ),
      11 => 
      array (
        'title' => '普通用户角色',
        'link' => 'admin.php?mod=role&code=list&type=normal',
        'shortcut' => false,
      ),
      12 => 
      array (
        'title' => '+添加用户角色',
        'link' => 'admin.php?mod=role&code=add',
        'shortcut' => false,
      ),
    ),
  ),
  5 => 
  array (
    'title' => '应用和插件',
    'link' => '',
    'sub_menu_list' => 
    array (
      1 => 
      array (
        'title' => '微群',
        'link' => 'admin.php?mod=qun',
        'shortcut' => false,
      ),
      2 => 
      array (
        'title' => '投票',
        'link' => 'admin.php?mod=vote&code=index',
        'shortcut' => false,
      ),
      3 => 
      array (
        'title' => '签到',
        'link' => 'admin.php?mod=sign',
        'shortcut' => false,
      ),
      4 => 
      array (
        'title' => '活动',
        'link' => 'admin.php?mod=event&code=manage',
        'shortcut' => false,
      ),
      /*
      9 => array(
        'title' => '版块',
        'link' => 'admin.php?mod=block',
        'shortcut' => false,
      ),
      10 => array(
      	'title' => '分类',
      	'link' => 'admin.php?mod=fenlei',
      	'shortcut' => false,
      ),
      11 => array(
      	'title' => '有奖转发',
      	'link' => 'admin.php?mod=reward',
      	'shortcut' => false,
      ),
      */
      5 => 
      array (
        'title' => '微直播',
        'link' => 'admin.php?mod=live&code=index',
        'shortcut' => false,
      ),
      6 => 
      array (
        'title' => '微访谈',
        'link' => 'admin.php?mod=talk',
        'shortcut' => false,
      ),
      7 => 
      array (
        'title' => '附件文档',
        'link' => 'admin.php?mod=attach',
        'shortcut' => false,
      ),
      8 => 
      array (
        'title' => 'API应用授权',
        'link' => 'admin.php?mod=api',
        'shortcut' => false,
      ),
	  /*
	  12 => 
      array (
        'title' => '频道管理',
        'link' => 'admin.php?mod=channel',
        'shortcut' => false,
      ),
	  */
	  13 => 
      array (
        'title' => '<font color=blue>单位和部门</font>',
        'link' => 'admin.php?mod=company',
        'shortcut' => false,
      ),
      20 => 
      array (
        'title' => '插件',
        'link' => 'hr',
        'shortcut' => false,
      ),
      21 => 
      array (
        'title' => '已安装插件',
        'link' => 'admin.php?mod=plugin',
        'shortcut' => false,
      ),
      22 => 
      array (
        'title' => '安装新插件',
        'link' => 'admin.php?mod=plugin&code=add',
        'shortcut' => false,
      ),
      23 => 
      array (
        'title' => '插件设计',
        'link' => 'admin.php?mod=plugin&code=design',
        'shortcut' => false,
        'type' => '1',
      ),
    ),
  ),
  6 => 
  array (
    'title' => '系统工具',
    'link' => '',
    'sub_menu_list' => 
    array (
      3 => 
      array (
        'title' => '在线系统升级',
        'link' => 'admin.php?mod=upgrade',
        'shortcut' => false,
      ),
      2 => 
      array (
        'title' => '<font color=red>后台操作记录</font>',
        'link' => 'admin.php?mod=logs',
        'shortcut' => true,
      ),
      1 => 
      array (
        'title' => '清空系统缓存',
        'link' => 'admin.php?mod=cache',
        'shortcut' => false,
      ),
      5 => 
      array (
        'title' => '蜘蛛爬行统计',
        'link' => 'admin.php?mod=robot',
        'shortcut' => false,
      ),
      6 => 
      array (
        'title' => '关键词排名',
        'link' => 'http://keyword.biniu.com',
        'shortcut' => false,
      ),
      7 => 
      array (
        'title' => 'alexa排名',
        'link' => 'http://alexa.biniu.com',
        'shortcut' => false,
      ),
      8 => 
      array (
        'title' => '友情链接检测',
        'link' => 'http://checklink.biniu.com',
        'shortcut' => false,
      ),
      9 => 
      array (
        'title' => '收录查询',
        'link' => 'http://shoulu.biniu.com',
        'shortcut' => false,
      ),
      10 => 
      array (
        'title' => '同IP网站监测',
        'link' => 'http://cnrdn.com/G8f4',
        'shortcut' => false,
      ),
      11 => 
      array (
        'title' => '反向链接分析',
        'link' => 'http://backlink.biniu.com',
        'shortcut' => false,
      ),
      30 => 
      array (
        'title' => '数据库管理',
        'link' => 'hr',
        'shortcut' => false,
      ),
      31 => 
      array (
        'title' => '数据备份',
        'link' => 'admin.php?mod=db&code=export',
        'shortcut' => false,
      ),
      32 => 
      array (
        'title' => '数据恢复',
        'link' => 'admin.php?mod=db&code=import',
        'shortcut' => false,
      ),
      33 => 
      array (
        'title' => '数据表优化',
        'link' => 'admin.php?mod=db&code=optimize',
        'shortcut' => false,
      ),
    ),
  ),
  7 => 
  array (
    'title' => '使用帮助',
    'link' => '',
    'sub_menu_list' => 
    array (
      11 => 
      array (
        'title' => '商业授权和客户端',
        'link' => 'http://cnrdn.com/15f4',
        'shortcut' => false,
      ),
      12 => 
      array (
        'title' => '推广赚金币',
        'link' => 'http://cnrdn.com/05f4',
        'shortcut' => false,
      ),
      13 => 
      array (
        'title' => 'Bug反馈',
        'link' => 'http://cnrdn.com/z4f4',
        'shortcut' => false,
      ),
      14 => 
      array (
        'title' => '模板风格分享',
        'link' => 'http://cnrdn.com/x4f4',
        'shortcut' => false,
      ),
      15 => 
      array (
        'title' => '插件和应用开发',
        'link' => 'http://cnrdn.com/25f4',
        'shortcut' => false,
      ),
      16 => 
      array (
        'title' => '最新版微博体验',
        'link' => 'http://cnrdn.com/35f4',
        'shortcut' => false,
      ),
    ),
  ),
); ?>