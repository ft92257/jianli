/**
 * 移动应用js库
 *
 * @author 		~ZZ~
 * @version		v1.0 $Date:2011-09-30
 */
 
var UserAgent = navigator.userAgent.toLowerCase();  
var is_Ipad = UserAgent.match(/ipad/i) == "ipad";    
var is_Iphoneos = UserAgent.match(/iphone os/i) == "iphone os";  
var is_Midp = UserAgent.match(/midp/i) == "midp";  
var is_Uc7 = UserAgent.match(/rv:1.2.3.4/i) == "rv:1.2.3.4";  
var is_Uc = UserAgent.match(/ucweb/i) == "ucweb";  
var is_Android = UserAgent.match(/android/i) == "android";  
var is_CE = UserAgent.match(/windows ce/i) == "windows ce";  
var is_WM = UserAgent.match(/windows mobile/i) == "windows mobile";  

//列表页每页显示
var PERPAGE = 20;
var IS_TAPHOLD = true;
var IS_TAP = true;
var TAP_START_TIME = 0;
var TIMEOUT_OP = Array();

//tab类型
var TAB_HOME = 101;
var TAB_MESSAGE = 102;
var TAB_PROFILE = 103;
var TAB_SQUARE = 104;
var TAB_MORE = 105;

function isUndefined(variable)
{
	return typeof variable=='undefined'?true:false;
}

//获取更多微博列表
function getMoreMBlogList(options)
{
	if (isUndefined(options)) {
		return false;
	}
	
	var max_tid = parseInt(options.max_tid);
	
	var next_page = parseInt(options.next_page);
	
	var mod = "topic";
	if (options.mod) {
		mod = options.mod;
	}
	
	var q = "";
	if (options.q) {
		q = encodeURI(options.q);
	}
	
	var tid = "";
	if (options.tid) {
		tid = options.tid;
	}
	
	var uid = 0;
	if (options.uid) {
		uid = parseInt(options.uid);
	}
	
	var code = options.code;
	if (code == '' || code == null) {
		return false;
	}
	
	var list_type = "";
	if (options.list_type) {
		list_type = options.list_type;
	}
	
	var tag_key = "";
	if (options.tag_key) {
		tag_key = options.tag_key;
	}
	
	//设置点击按钮显示正在加载
	$('#btn_more').html("<span class='more_loading'>加载中...</span>");
	var ajax_url = "ajax.php?mod=" + mod + "&code=" + code;
	
	if (q!="") {
		ajax_url += "&q=" + q; 
	}
	
	if (tid!="") {
		ajax_url += "&tid=" + tid; 
	}
	
	if (uid!="") {
		ajax_url += "&uid=" + uid; 
	}
	
	if (tag_key != "") {
		ajax_url += "&tag_key="+tag_key;
	}
	
	if (next_page > 0) {
		ajax_url += "&page=" + next_page;
	} else {
		ajax_url += "&max_tid=" + max_tid;
	}
	$.get(
		ajax_url,
		{},
		function(r){
			if (r.code == 200) {
				var html = '';
				var list_count = r.result.list_count;
				var max_tid = r.result.max_tid;
				var next_page = r.result.next_page;
				if (list_type == "comment") {
					html = makeCommentList(r.result);
				} else {
					html = makeMBlogList(r.result);
				}
				$('#weibo_list_wp').append(html);
				//bindWeiboTapHold();
				setMBlogListEvent();
				if (list_count >= PerPage_MBlog && max_tid > 1) {
					$('#btn_more').html("更多...");
					$('#btn_more').attr('onclick', '');
					$('#btn_more').unbind("click");
					$('#btn_more').click(function(){
						getMoreMBlogList({"max_tid":max_tid, "next_page":next_page, "mod":mod, "code":code, "q":q, "tid":tid, "uid":uid, "tag_key":tag_key});
					});
				} else {
					$('#btn_more').hide();
				}
			} else {
				if (r.code == 400) {
					showTips("已经是最后一页了", 1);
					$('#btn_more').hide();
				}
			}
		},
		'json'
	);
	
}


//客户端组装微博样式
function makeMBlogList(data)
{
	var topic_list = data.topic_list;
	var parent_list = data.parent_list;
	var max_tid = data.max_tid;
	var list_count = data.list_count;
	var html = '';
	for (var i=0;i<topic_list.length;++i) {
		var topic =	topic_list[i];
		//头部
		html += '<div class="" id="weibo_itmes_'+topic['tid']+'"><div class="weibo" data-tid="'+topic['tid']+'" data-login="1" data-uid="'+topic['uid']+'">';
		
		//左侧头像
		html += '<div class="wb_l"><div><img class="author" src="'+topic['face']+'"  onclick="goToUserInfo('+ topic['uid'] +');" /></div></div>';

		//昵称和发布时间
		html += '<div class="wb_r"><div class="user_info"><span class="fl p_u">'+topic['nickname']+'</span><span class="fr p_t">';
		
		if (!isUndefined(topic['image_list'])) {
			html += '<img src="./images/pic.png"/>';
		}
		html += '<span>'+topic['dateline']+'</span></span></div>';

		//微博正文
		html += '<div class="wb_c_wp"><div class="wb_c">'+topic['content']+'</div>';
		
		//是否含有图片
		if ( !isUndefined(topic['image_list'])) {
			html += '<div class="share">';
			for(var j in topic['image_list']) {
				var image = topic['image_list'][j];
				html += '<img class="author" src="' + image['image_small'] + '" style="width:100px; height:100px;" /><br/>';
			}
			html += '</div>';
		}
		
		//父级模块处理
		if (topic['totid'] > 0) {
			html += '<div class="tips_ico"></div><div class="wbf">';
			if (!isUndefined(parent_list[topic['totid']])) {
				var parent = parent_list[topic['totid']];
				html += '<div><a href="javascript:;">' + parent['nickname'] + '</a> : ' + parent['content'] + '</div>';
				if ( !isUndefined(parent['image_list'])) {
					html += '<div class="share">';
					for(var j in parent['image_list']) {
						var image = parent['image_list'][j];
						html += '<img class="author" src="' + image['image_small'] + '" style="width:100px; height:100px;" /><br/>';
					}
					html += '</div>';
				}
			} else {
				html += "原始微博已删除";
			}
			html += '</div>';
		}
		
		//from处理
		html += '<div class="from"><span class="fl">'+topic['from_string']+'</span><span class="fr num">';
		if (topic['forwards'] > 0) {
			html += '<span class="forward_num"><img src="./images/redirect_icon.png" /><span>'+topic['forwards']+'</span></span>';
		}
		if (topic['replys'] > 0) {
			html += '<span class="comment_num"><img src="./images/comment_icon.png" /><span>'+topic['replys']+'</span></span>';
		}
		html += '</span></div></div></div></div><div class="wb_line"></div></div>';
	}
	return html;
}

//评论列表处理
function makeCommentList(data)
{
	var topic_list = data.topic_list;
	var parent_list = data.parent_list;
	var max_tid = data.max_tid;
	var list_count = data.list_count;
	var html = '';
	for (var i=0;i<topic_list.length;++i) {
		var topic =	topic_list[i];
		//头部
		html += '<div class="" id="weibo_itmes_'+topic['tid']+'"><div class="weibo" data-tid="'+topic['tid']+'" data-login="1" data-uid="'+topic['uid']+'" data-huifu="is_huifu" >';
		
		//左侧头像
		html += '<div class="wb_l"><div><img class="author" src="'+topic['face']+'"/></div></div>';

		//昵称和发布时间
		html += '<div class="wb_r"><div class="user_info"><span class="fl p_u">'+topic['nickname']+'</span><span class="fr p_t">';
		
		if (!isUndefined(topic['image_list'])) {
			html += '<img src="./images/pic.png"/>';
		}
		html += '<span>'+topic['dateline']+'</span></span></div>';

		//微博正文
		html += '<div class="wb_c_wp"><div class="wb_c">'//topic['content']+'</div>';
		if (topic['totid'] > 0 && topic['totid'] != topic['roottid']) {
			if (!isUndefined(parent_list[topic['totid']])) {
				var parent = parent_list[topic['totid']];
				html += '回复<a href="">@'+parent['nickname']+'</a>:';
			}
		}
		
		html += topic['content']+'</div>';
		
		//是否含有图片
		if ( !isUndefined(topic['image_list'])) {
			html += '<div class="share">';
			for(var j in topic['image_list']) {
				var image = topic['image_list'][j];
				html += '<img class="author" src="' + image['image_small'] + '" style="width:100px; height:100px;" /><br/>';
			}
			html += '</div>';
		}
		
		//from处理
		html += '<div class="from"><span class="fl">'+topic['from_string']+'</span><span class="fr num">';
		if (topic['forwards'] > 0) {
			html += '<span class="forward_num"><img src="./images/redirect_icon.png" /><span>'+topic['forwards']+'</span></span>';
		}
		if (topic['replys'] > 0) {
			html += '<span class="comment_num"><img src="./images/comment_icon.png" /><span>'+topic['replys']+'</span></span>';
		}
		html += '</span></div></div></div></div><div class="wb_line"></div></div>';
	}
	return html;
}

//微博列表页面事件绑定
function setMBlogListEvent()
{
	bindWeiboTap();
}

function bindWeiboTap()
{
	//简单的触摸
	$(".weibo").unbind("touchstart");
	$(".weibo").bind("touchstart", function(event){
		//判断是否是移动客户端
		TAP_START_TIME = Date.parse(new Date());
		var c = this;
		var t = setTimeout(function(){tapHoldWeibo(c);}, 1000);
		TIMEOUT_OP.push(t);
	});
	
	$(".weibo").unbind("touchmove");
	$(".weibo").bind("touchmove", function(event){
		IS_TAP = false;
	});
	
	$(".weibo").unbind("touchend");
	$(".weibo").bind("touchend", function(event){
		if (!IS_TAP) {
			for(i=0;i<TIMEOUT_OP.length;i++) {
				clearTimeout(TIMEOUT_OP[i]);
			}
			TIMEOUT_OP = Array();
			IS_TAP = true;
			return ;
		}
		var timestamp = Date.parse(new Date());
		var tid = $(this).attr("data-tid");
		var islogin = $(this).attr("data-login");
		var uid = $(this).attr("data-uid");
		var is_huifu = $(this).attr("data-huifu");
		
		if (TAP_START_TIME > 0) {
			if (timestamp - TAP_START_TIME > 1) {
				TAP_START_TIME = 0;
				tapHoldWeibo(this);
			} else {
				TAP_START_TIME = 0;
				IS_TAPHOLD = false;
				if (!isUndefined(islogin) && islogin==0) {
					showTips("你还没有登录", 0);
				} else {
					if (isUndefined(tid)) {
						showTips("参数错误", 0);
					} else {
						goToMBlogDetail(tid);
					}
				}
			}
		}
	});
}

function tapHoldWeibo(obj)
{
	TAP_START_TIME = 0;
	if (IS_TAP && IS_TAPHOLD) {
		var tid = $(obj).attr("data-tid");
		var islogin = $(obj).attr("data-login");
		var uid = $(obj).attr("data-uid");
		var is_huifu = $(obj).attr("data-huifu");
		if (!isUndefined(islogin) && islogin==0) {
			showTips("你还没有登录", 0);
		} else {
			if (isUndefined(tid)) {
				showTips("参数错误", 0);
			} else {
				DesireJs.showBlogOperationDailog('{"tid":'+tid+', "roottid":'+tid+', "totid":'+tid+', "is_huifu":"'+is_huifu+'", "uid":'+uid+'}');
			}
		}
	} else {
		IS_TAPHOLD = true;
	}
}

//取消关注
function delFollow(obj, uid)
{
	var ajax_url = "ajax.php?mod=friend&code=del_follow"
	$.get(
		ajax_url,
		{"uid":uid},
		function(r){
			switch(r.code) {
				case 200:
					$(obj).removeClass("btn_y").addClass("btn_g");
					$(obj).attr("onclick", "");
					$(obj).unbind("click");
					$(obj).html("关注")
					$(obj).bind("click", function(){
						addFollow(obj, uid);
					});
					break;
				case 300:
					showTips("当前用户不存在或已经被删除了");
					break;
				default:
					showTips("取消关注失败");
			}
		}, 
		"json"
	);
	event.stopPropagation();
}

//关注
function addFollow(obj, uid)
{
	var ajax_url = "ajax.php?mod=friend&code=add_follow"
	$.get(
		ajax_url,
		{"uid":uid},
		function(r){
			switch(r.code) {
				case 200:
					$(obj).removeClass("btn_g").addClass("btn_y");
					$(obj).attr("onclick", "");
					$(obj).unbind("click");
					$(obj).html("取消关注")
					$(obj).bind("click", function(){
						delFollow(obj, uid);
					});
					break;
				case 300:
					showTips("当前用户不存在或已经被删除了");
					break;
				default:
					showTips("关注失败");
			}
		}, 
		"json"
	);
	event.stopPropagation();

}

//添加到黑名单
function addToBlackList(obj, uid, type)
{
	var ajax_url = "ajax.php?mod=friend&code=add_blacklist";
	$.get(
		ajax_url,
		{"uid":uid},
		function(r){
			if (isUndefined(type)) {
				switch(r.code) {
					case 200:
						$(obj).removeClass("btn_g").addClass("btn_y");
						$(obj).attr("onclick", "");
						$(obj).unbind("click");
						$(obj).html("解除黑名单")
						$(obj).bind("click", function(){
							delFromBlackList(obj, uid);
						});
						break;
					case 300:
						showTips("当前用户不存在或已经被删除了");
						break;
					default:
						showTips("拉入黑名单失败");
				}
			} else if (type == 1) {
				switch(r.code) {
					case 200:
						$(obj).attr("onclick", "");
						$(obj).unbind('click');
						$(obj).bind("click", function(){
							delFromBlackList(obj, uid, type);
						});
						showTipsExp("拉入黑名单成功", 1);
						break;
					case 300:
						showTipsExp("当前用户不存在或已经被删除了", 1);
						break;
					default:
						showTipsExp("拉入黑名单失败", 1);
						break;
				}
			}
		}, 
		"json"
	);
	event.stopPropagation();

}

////解除黑名单
function delFromBlackList(obj, uid, type)
{
	var ajax_url = "ajax.php?mod=friend&code=del_blacklist";
	$.get(
		ajax_url,
		{"uid":uid},
		function(r){
			if (isUndefined(type)) {
				switch(r.code) {
					case 200:
						$(obj).removeClass("btn_y").addClass("btn_g");
						$(obj).attr("onclick", "");
						$(obj).unbind("click");
						$(obj).html("加入黑名单")
						$(obj).bind("click", function(){
							addToBlackList(obj, uid);
						});
						break;
					case 300:
						showTips("当前用户不存在或已经被删除了");
						break;
					default:
						showTips("解除黑名单失败");
				}
			} else if (type == 1) {
				switch(r.code) {
					case 200:
						$(obj).attr("onclick", "");
						$(obj).unbind('click');
						$(obj).bind("click", function(){
							addToBlackList(obj, uid, type);
						});
						showTipsExp("解除黑名单成功", 1);
						break;
					case 300:
						showTipsExp("当前用户不存在或已经被删除了", 1);
						break;
					default:
						showTipsExp("解除黑名单失败", 1);
						break;
				}
			}
		}, 
		"json"
	);
	event.stopPropagation();
}

function getMoreMemberList(options)
{
	if (isUndefined(options)) {
		return false;
	}
	
	var max_id = parseInt(options.max_id);
	
	var uid = 0;
	if (options.uid) {
		uid = parseInt(options.uid);
	}
	
	if (uid < 1) {
		showTips("参数错误", 0);
		return false;
	}
	
	var code = options.code;
	if (code == '' || code == null) {
		showTips("参数错误", 0);
		return false;
	}
	
	var mod = "friend";
	
	//设置点击按钮显示正在加载
	$('#btn_more').html("<span class='more_loading'>加载中...</span>");
	var ajax_url = "ajax.php?mod=" + mod + "&code=" + code;
	ajax_url += "&uid=" + uid; 
	ajax_url += "&max_id=" + max_id;
	$.get(
		ajax_url,
		{},
		function(r){
			if (r.code == 200) {
				var html = '';
				var list_count = r.result.list_count;
				var max_id = r.result.max_id;
				html = makeMemberList(r.result);
			 	$('#member_ul').append(html);
				if (list_count >= PerPage_Member && max_id > 1) {
					$('#btn_more').html("更多...");
					$('#btn_more').attr('onclick', '');
					$('#btn_more').unbind("click");
					$('#btn_more').click(function(){
						getMoreMemberList({"max_id":max_id, "code":code, "uid":uid});
					});
				} else {
					$('#btn_more').hide();
				}
			} else {
				if (r.code == 400) {
					showTips("已经是最后一页了", 0);
					$('#btn_more').hide();
				}
			}
		},
		'json'
	);
}

function getSerachMoreMemberList(options)
{
	if (isUndefined(options)) {
		return false;
	}
	
	var max_id = parseInt(options.max_id);
	var q = options.keyword;
	
	var mod = "search";
	var code = "user"
	
	//设置点击按钮显示正在加载
	$('#btn_more').html("<span class='more_loading'>加载中...</span>");
	var ajax_url = "ajax.php?mod=" + mod + "&code=" + code;
	ajax_url += "&q=" + q; 
	ajax_url += "&max_id=" + max_id;
	$.get(
		ajax_url,
		{},
		function(r){
			if (r.code == 200) {
				var html = '';
				var list_count = r.result.list_count;
				var max_id = r.result.max_id;
				html = makeMemberList(r.result);
			 	$('#member_ul').append(html);
				if (list_count >= PerPage_Member && max_id > 1) {
					$('#btn_more').html("更多...");
					$('#btn_more').attr('onclick', '');
					$('#btn_more').unbind("click");
					$('#btn_more').click(function(){
						getMoreMemberList({"max_id":max_id, "q":q});
					});
				} else {
					$('#btn_more').hide();
				}
			} else {
				if (r.code == 400) {
					showTips("已经是最后一页了", 0);
					$('#btn_more').hide();
				}
			}
		},
		'json'
	);
}

function makeMemberList(data)
{
	var member_list = data.member_list;
	var html = "";
	for (i=0;i<member_list.length;i++) {
		var member = member_list[i];
		html += '<li onclick="goToUserInfo(\'' + member['uid'] + '\')"><div class="info_wp">';
		html += '<div class="bc_l"><img src="' + member['face'] + '"/></div>';
		html += '<div class="bc_r"><div class="uname">' + member['nickname'] + '</div><div class="other">' + member['from_area'] + '<br>粉丝' + member['fans_count'] + '人 | '+ member['topic_count'] + '条微博</div></div>';
		html += '</div>';
		html += '<div class="do_wp">';
		if (member['friendship'] == -1) {
			html += '<a href="javascript:;" onclick="delFromBlackLis(this, ' + member['uid'] + ');return true;" class="btn_y">解除黑名单</a>';
		} else if (member['friendship'] == 0) {
			html += '<a href="javascript:;" onclick="addFollow(this, ' + member['uid'] + ');return true;" class="btn_g">关注</a>';
		} else if (member['friendship'] == 1) {
			html += '自己';
		} else if (member['friendship'] == 2) {
			html += '<a href="javascript:;" onclick="delFollow(this, ' + member['uid'] + ');return true;" class="btn_y">取消关注</a>';
		} else if (member['friendship'] == 4) {
			html += '<a href="javascript:;" onclick="delFollow(this, ' + member['uid'] + ');return true;" class="btn_y">取消关注</a>';
		}
		html += '</div></li>';
	}
	return html;
}

function getMoreTagList(options)
{
	if (isUndefined(options)) {
		return false;
	}
	
	var max_id = parseInt(options.max_id);
	
	var uid = 0;
	if (options.uid) {
		uid = parseInt(options.uid);
	}
	
	if (uid < 1) {
		showTips("参数错误", 0);
		return false;
	}
	
	var code = options.code;
	if (code == '' || code == null) {
		code = "list";
	}
	
	var mod = "tag";
	
	//设置点击按钮显示正在加载
	$('#btn_more').html("<span class='more_loading'>加载中...</span>");
	var ajax_url = "ajax.php?mod=" + mod + "&code=" + code;
	ajax_url += "&uid=" + uid; 
	ajax_url += "&max_id=" + max_id;
	$.get(
		ajax_url,
		{},
		function(r){
			if (r.code == 200) {
				var html = '';
				var list_count = r.result.list_count;
				var max_id = r.result.max_id;
				html = makeMoreTagList(r.result);
			 	$('#tag_ul').append(html);
				if (list_count >= PerPage_Def && max_id > 1) {
					$('#btn_more').html("更多...");
					$('#btn_more').attr('onclick', '');
					$('#btn_more').unbind("click");
					$('#btn_more').click(function(){
						getMoreTagList({"max_id":max_id, "code":code, "uid":uid});
					});
				} else {
					$('#btn_more').hide();
				}
			} else {
				if (r.code == 400) {
					showTips("已经是最后一页了", 0);
					$('#btn_more').hide();
				}
			}
		},
		'json'
	);
}

function makeMoreTagList(data)
{
	var tag_list = data.tag_list;
	var html = "";
	for (i=0;i<tag_list.length;i++) {
		var tag = tag_list[i];
		html += '<li onclick="goToTopicList(\''+tag['tag']+'\')"><a href="javascript:;" >'+tag['tag']+'</a></li>';
	}
	return html;
}

//获取更多私信列表
function getMorePmList(options)
{
	if (isUndefined(options)) {
		return false;
	}
	
	var page = parseInt(options.page);
	
	var mod = Module;
	var code = Code;
	if (mod == null || mod == "" || code == null || code == "") {
		showTips("服务器系统遭到破坏，请联系管理员修复", 0);
		return false;
	}
	
	if (code == "history") {
		var uid = 0;
		if (options.uid) {
			uid = parseInt(options.uid);
		}
		if (uid == 0) {
			showTips("参数错误", 0);
		}
	}
	
	//设置点击按钮显示正在加载
	$('#btn_more').html("<span class='more_loading'>加载中...</span>");
	var ajax_url = "ajax.php?mod=" + mod + "&code=" + code;
	if (uid > 0) {
		ajax_url += "&uid=" + uid;
	}
	ajax_url += "&page=" + page;
	$.get(
		ajax_url,
		{},
		function(r){
			if (r.code == 200) {
				var html = '';
				var list_count = r.result.list_count;
				var page = r.result.current_page + 1;
				html = makeMorePmList(r.result);
			 	$('#pm_list_wp').append(html);
				setPmListEvent();
				if (page <= r.result.total_page) {
					$('#btn_more').html("更多...");
					$('#btn_more').attr('onclick', '');
					$('#btn_more').unbind("click");
					$('#btn_more').click(function(){
						if (code == "history") {
							getMorePmList({"page":page, "uid":uid});
						} else {
							getMorePmList({"page":page});
						}
					});
				} else {
					$('#btn_more').hide();
				}
			} else {
				if (r.code == 400) {
					showTips("已经是最后一页了", 0);
					$('#btn_more').hide();
				}
			}
		},
		'json'
	);
}

function makeMorePmList(data)
{
	var pm_list = data.pm_list;
	var html = "";
	for (i=0;i<pm_list.length;i++) {
		var pm = pm_list[i];
		html += '<div class="" id="pm_itmes_' + pm['plid'] + '"><div class="pm" data-uid="'+pm['uid']+'"><div class="wb_l"><div><img class="author" src="'+pm['face']+'"/></div></div>';
		html += '<div class="wb_r"><div class="user_info">';
		html += '<span class="fl p_u">';
		if (Code == "list") {
			if (pm['msgfromid'] == Uid) {
				html += '<span style="margin-right:5px;">发给:</span>' + pm['tonickname'];
			} else {
				html += pm['msgnickname'];
			}
			html += '<span style="margin-left:8px;">['+pm['num']+']</span>';
		} else if (Code == "history") {
			html += pm['msgnickname'];
		}
		html += '</span><span class="fr p_t"><span>'+pm['date']+'</span></span></div>';
		html += '<div class="wb_c_wp"><div class="wb_c">'+pm['message']+'</div></div></div></div><div class="wb_line"></div></div>';
	}
	return html;
}


//微博列表页面事件绑定
function setPmListEvent()
{
	bindPmTap();
}

function bindPmTap()
{
	//简单的触摸
	$(".pm").unbind("click");
	$(".pm").bind("click", function(event){
		if (Code == 'list') {
			var uid = $(this).attr("data-uid");
			if (isUndefined(uid)) {
				showTips("参数错误", 0);
			} else {
				showPmOperationDailog(uid)
			}
		} else if (Code == 'history') {
			var pmid = $(this).attr("data-pmid");
			if (isUndefined(pmid)) {
				showTips("参数错误", 0);
			} else {
				showOnePmOperationDailog(pmid)
			}
		}
	});
}


//转到详情页面
function goToMBlogDetail(options)
{
	if (MobileClient) {
		DesireJs.goToBlogDetail(options);
	} else {
		location.href = "index.php?mod=topic&code=detail&tid=" + options;
	}
}

//转到转发编辑框
function goToForwardEditView(tid)
{
	if (MobileClient) {
		DesireJs.goToForwardEditView(tid);
	} else {
		openPublishBox(PUBLISH_FORWARD, {totid:tid});
	}
}

//转到用户资料页面
function goToUserInfo(uid)
{
	if (MobileClient) {
		DesireJs.goToUserInfo(uid);
	} else {
		location.href = "index.php?mod=member&code=userinfo&uid="+uid;
	}
}

function goToMyMbList(uid)
{
	if (MobileClient) {
		DesireJs.goToMyMbList(uid);
	} else {
		location.href = "index.php?mod=topic&code=my_blog&uid="+uid;
	}
	//goToUserInfo(uid);
}

function goToFwList(uid)
{
	if (MobileClient) {
		DesireJs.goToFollowList(uid);
	} else {
		location.href = "index.php?mod=friend&code=follow&uid="+uid;
	}
}

function goToFansList(uid)
{
	if (MobileClient) {
		DesireJs.goToFansList(uid);
	} else {
		location.href = "index.php?mod=friend&code=fans&uid="+uid;
	}
}

function goToBlackList(uid)
{
	if (MobileClient) {
		DesireJs.goToBlackList(uid);
	} else {
		location.href = "index.php?mod=friend&code=blacklist&uid="+uid;
	}
}

//我的收藏
function goToMyFavList()
{
	if (MobileClient) {
		DesireJs.goToMyFavList();
	} else {
		location.href = "index.php?mod=topic&code=my_favorite";
	}
}

//评论列表
function goToCommentList(tid)
{
	if (MobileClient) {
		DesireJs.goToCommentList(tid);
	} else {
		location.href = "index.php?mod=topic&code=comment&tid="+tid;
	}
}

//显示提示
function showTips(msg, type)
{
	if (MobileClient) {
		DesireJs.showTips(msg, type);
	} else {
		showTipsExp(msg);
	}
}

//设置nick临时变量
function setTmpNick(nick)
{
	//showTips("debug in zz", 0);
	DesireJs.setTmpNick(nick);
}

function goToTagList(uid)
{
	if (MobileClient) {
		DesireJs.goToTagList(uid);
	} else {
		location.href = "index.php?mod=tag&code=list&uid="+uid;
	}
}

function goToTopicList(tag)
{
	if (MobileClient) {
		DesireJs.goToTopicList(tag);
		event.stopPropagation();
	} else {
		location.href = "index.php?mod=topic&code=tag&tag_key="+encodeURI(tag);
	}
}

function showPmOperationDailog(uid)
{
	DesireJs.showPmOperationDailog(uid);
}

function showOnePmOperationDailog(pmid)
{
	DesireJs.showOnePmOperationDailog(pmid);
}

function goToEditProfile(uid)
{
	DesireJs.goToEditProfile(uid);
}

function goToLookAround()
{
	if (MobileClient) {
		if (is_Android) {
			DesireJs.goToLookAround();
		} else if (is_Iphoneos || is_Ipad) {
			location.href = "square://app=lookaround";
		}
	} else {
		location.href = "index.php?mod=topic&code=new";
	}
}

function goToHotComment()
{
	if (MobileClient) {
		if (is_Android) {
			DesireJs.goToHotComment();
		} else if (is_Iphoneos || is_Ipad) {
			location.href = "square://app=hotcomment";
		}
	} else {
		location.href = "index.php?mod=topic&code=hot_comments";
	}
}

function goToHotForward()
{
	if (MobileClient) {
		if (is_Android) {
			DesireJs.goToHotForward();
		} else if (is_Iphoneos || is_Ipad) {
			location.href = "square://app=hotforward";
		}
	} else {
		location.href = "index.php?mod=topic&code=hot_forwards";
	}
}

function accessError(code)
{
	DesireJs.accessError(code);
}

function goToLogin()
{
	DesireJs.goToLogin();
}

/*
var TAB_HOME = 101;
var TAB_MESSAGE = 102;
var TAB_PROFILE = 103;
var TAB_SQUARE = 104;
var TAB_MORE = 105;
*/
function changeTab(type)
{
	var url = "";
	switch (type) {
		case TAB_HOME:
			url = "index.php?mod=topic&code=home";
			break;
		case TAB_MESSAGE:
			url = "index.php?mod=topic&code=at_my";
			break;
		case TAB_PROFILE:
			url = "index.php?mod=member&code=userinfo&uid=" + Uid;
			break;
		case TAB_SQUARE:
			url = "index.php?mod=square&code=3g";
			break;
		case TAB_MORE:
			url = "index.php?mod=more";
			break;
	}
	//$.mobile.changePage(url);
	location.href = url;
}

var TAB_MESSAGE_AT = 101;
var TAB_MESSAGE_COMMENT = 102;
function changeMessageTab(type)
{
	var url = "";
	switch (type) {
		case TAB_MESSAGE_AT:
			url = "index.php?mod=topic&code=at_my";
			break;
		case TAB_MESSAGE_COMMENT:
			url = "index.php?mod=topic&code=comment_my";
			break;
	}
	location.href = url;
}

var PUBLISH_NEW = 101;
var PUBLISH_COMMENT = 102;
var PUBLISH_FORWARD = 103;
function openPublishBox(type, data)
{
	var pt = "new";
	switch (type) {
		case PUBLISH_NEW:
			pt = "new";
			break;
		case PUBLISH_COMMENT:
			pt = "reply";
			break;
		case PUBLISH_FORWARD:
			pt = "forward"
			break;
	}
	url = "index.php?mod=topic&code=publish&pt="+pt;
	if (isUndefined(data) || !data) {
		data = {};
	}
	
	if (data.totid) {
		url += "&totid="+data.totid;
	}
	
	if (data.atuid) {
		url += "&atuid="+data.atuid;
	}
	
	if (data.tagid) {
		url += "&tagid="+data.tagid;
	}
	
	location.href = url;
}

var TAB_SEARCH_WEIBO = 101;
var TAB_SEARCH_USER = 102;
function changeSearchTab(type, q)
{
	var code = "topic";
	switch (type) {
		case TAB_SEARCH_WEIBO:
			code = "topic";
			break;
		case TAB_SEARCH_USER:
			code = "user";
			break;
	}
	url = "index.php?mod=search&code=" + code + "&q="+encodeURI(q);
	location.href = url;
}

//发表微博
function publishMBlog()
{
	var content = $("#g_content").val();
	if (content.length < 3) {
		showTipsExp("微博内容不能小于3个字节", 1);
		return ;
	}
	var type = $("#g_type").val();
	if ($("#p_both").attr("checked")) {
		type = "both";	
	}
	
	var totid = $("#g_totid").val();
	
	var postData = {
		"content":content,
		"topictype":type,
		"totid":totid,
	};
	showTipsExp("正在发布...");
	$("#publish_btn").attr("disabled", "disabled");
	var myAjax = $.post (
		"ajax.php?mod=topic&code=add",
		postData,
		function(r){
			if (r.code == 200) {
				showTipsExp("发布成功，正在跳转...");
				setTimeout("history.go(-1)", 1000);
			} else if (r.code == 430) {
				showTipsExp(r.result);
			}
			$("#publish_btn").removeAttr("disabled");
		},
		"json"
	);
}

function showTipsExp(msg, type) {
	var t = $("#g_tips");
	t.html(msg);
	t.show();
	if (!isUndefined(type) && type != null) {
		if (type == 1) {
			setTimeout("closeTipsExp()", 3000);
		}
	}
}

function closeTipsExp() {
	$("#g_tips").hide();
}

//收藏微博
function favMBlog(op, tid) {
	showTipsExp("正在处理...");
	var postData = {
		"tid":tid,
		"op":op
	}
	var ajax = $.post (
		"ajax.php?mod=topic&code=favorite",
		postData,
		function(r) { 
			if (r.code == 200) {
				if (op == "add") {
					showTipsExp("收藏成功", 1);
					$("#btn_fav_mblog").attr("href", "javascript:favMBlog('delete', "+tid+");");
					$("#btn_fav_mblog").removeClass("btn_fav").addClass("btn_fav_on");
				} else if (op == "delete") {
					showTipsExp("取消收藏成功", 1);
					$("#btn_fav_mblog").attr("href", "javascript:favMBlog('add', "+tid+");");
					$("#btn_fav_mblog").removeClass("btn_fav_on").addClass("btn_fav");
				}
			} else {
				showTipsExp("收藏失败", 1);
			}
		},
		"json"
	);
}

//关注话题
function favTopic(op, tag) {
	showTipsExp("正在处理...");
	var postData = {
		"tag":tag
	};
	var ajax = $.post (
		"ajax.php?mod=tag&code=" + op,
		postData,
		function(r) { 
			if (r.code == 200) {
				if (op == "add_favorite") {
					showTipsExp("关注成功", 1);
					$("#btn_fav_topic").attr("href", "javascript:favTopic('del_favorite', '"+tag+"');");
					$("#btn_fav_topic").removeClass("btn_fav").addClass("btn_fav_on");
				} else if (op == "del_favorite") {
					showTipsExp("取消关注成功", 1);
					$("#btn_fav_topic").attr("href", "javascript:favTopic('add_favorite', '"+tag+"');");
					$("#btn_fav_topic").removeClass("btn_fav_on").addClass("btn_fav");
				}
			} else {
				showTipsExp("关注失败", 1);
			}
		},
		"json"
	);
}

//检查话题
function checkTopic(tag) {
	var postData = {
		'tag':tag
	};
	var ajax = $.post (
		"ajax.php?mod=tag&code=check",
		postData,
		function(r) { 
			if (r.code == 200) {
				$("#btn_fav_topic").attr("href", "javascript:favTopic('del_favorite', '"+tag+"');");
				$("#btn_fav_topic").removeClass("btn_fav").addClass("btn_fav_on");			
			} else {
				$("#btn_fav_topic").attr("href", "javascript:favTopic('add_favorite', '"+tag+"');");
				$("#btn_fav_topic").removeClass("btn_fav_on").addClass("btn_fav");
			}
		},
		"json"
	);
}

function login(u, p) {
	showTipsExp("正在登录...");
	var postData = {
		'user_name':$('#'+u).val(),
		'password':$('#'+p).val()
	};
	var ajax = $.post (
		"ajax.php?mod=member&code=login",
		postData,
		function(r) { 
			switch (r.code) {
				case 200:
					showTipsExp("登录成功，正在跳转...", 1);
					setTimeout("location.href='index.php?mod=topic&code=home'", 1000);
					break;
				case 320:
					showTipsExp("用户名与密码不能为空", 1);
					break;
				case 403:
					showTipsExp("未知错误", 1);
					break;
				case 321:
					showTipsExp("用户名错误", 1);
					break;
				case 322:
					showTipsExp("密码错误", 1);
					break;
				case 323:
					showTipsExp("输入错误超过5次", 1);
					break;
			}
		},
		"json"
	);
}

