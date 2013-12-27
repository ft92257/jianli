/**
 * 微群js库
 * 
 * @category   Qun
 * @author     ~ZZ~
 * @version	   v1.0 $Date 2011-03-14
 */

//二级分类
function get_catselect(obj)
{
	cat_id = obj.options[obj.selectedIndex].value;
	$.get(
		'ajax.php?mod=qun&code=second_cat&cat_id='+cat_id,
		{},
		function(r) {
			$("#sub_cat").empty();
			$(r).appendTo("#sub_cat");
		}
	);	
}

/**
 * 加入群
 */
function joinQun(qid)
{
	$.post(
		'ajax.php?mod=qun&code=join',
		{'qid':qid},
		function(r) {
			if (!is_json(r)) {
				var handle_key = 'join_qun_success_dialog';
				showDialog(handle_key, 'local', '加入成功', {html:r}, 500);
				
				//设置标题栏关闭按钮侦听
				setDialogOnCloseListener(handle_key, function(){
					location.reload();
				});
				
				getUserFriends(1, {'qid':qid});
				$(document).ready(function(){
					$('#topic_simple_close_btn').click(
						function() {
							closeDialog(handle_key);
							location.reload();
						}
					);
					$('#topic_simple_share_btn').click(
						function() {
							var response = function() {
								MessageBox('notice', '成功推荐到微博');
								location.reload();
							}
							if (publishSimpleTopic($('#topic_simple_content').val(), '', 0, {response:response})) {
								closeDialog(handle_key);
							}
						}
					);
				});
			} else {
				var json = eval('('+r.toString()+')');
				MessageBox('warning', json.msg);
			}
		}
	);		
}

/**
 * 申请加入群
 */
function applyQun(qid, msg)
{
	$.post(
		'ajax.php?mod=qun&code=join',
		{'qid':qid, 'message':msg},
		function(r) {
			if (r.done) {
				closeDialog('apply_qun_dialog');
				MessageBox('notice', r.msg);
				location.reload();
			} else {
				MessageBox('warning', r.msg);
			}
		},
		'json'
	);		
}

/**
 * 退出群
 */
function quitQun(qid)
{
	var param = {
		'onClickYes':function(){
			$.post(
				'ajax.php?mod=qun&code=quit',
				{'qid':qid},
				function(r) {
					if (r.done) {
						var options = {
							onclick:function() {
								location.reload();
							},
							close_first:true
						};
						MessageBox('notice', r.msg, '提示', options);
						
						//设置标题栏关闭按钮侦听
						setDialogOnCloseListener('notice_dialog', function(){
							location.reload();
						});
					} else {
						MessageBox('warning', r.msg);
					}
				},
				'json'
			);
		}
	}
	MessageBox('confirm', "你确定退出微群吗？", '提示', param);
}

//按分类id获取推荐的类
function recommendQun(cat_id)
{
	var lis = $("#cat_nav div");
	var len = lis.length;
	var cur_id = 'nav_inner_'+cat_id;
	for (i=0;i<len;i++) {
		if (lis[i].id == cur_id) {
			lis[i].className = 'tago';
		} else {
			lis[i].className = 'tagn';
		}
	}
	
	$.get(
		'ajax.php?mod=qun&code=recdqun&random='+Math.random(),
		{'cat_id':cat_id},
		function(r) {
			if(is_json(r)){
				r = eval('('+r.toString()+')');
				$('#recdqun_wp').html(r.msg);
			} else {
				$('#recdqun_wp').html(r);
			}
		}
	);
}

//按照省份ID获取同城群
function tcQun(province){
	getCityList(province);
	tcQunSearch(province);
}

function tcQunSearch(province){
	$.get(
			'ajax.php?mod=qun&code=tcqun',
			{'province':province},
			function(r) {
				if(is_json(r)){
					r = eval('('+r.toString()+')');
					$('#tc_wq').html(r.msg);
				} else {
					$('#tc_wq').html(r);
				}
			}
		);
}

function getCityList(province){
	$.get(
			'ajax.php?mod=member&code=sel',
			{'province':province},
			function(r) {
				if(is_json(r)){
					r = eval('('+r.toString()+')');
					$('#tc_city').html(r.msg);
				} else {
					$('#tc_city').html(r);
				}
			}
		);
}

//按照城市ID获取同城群
function tcityQun(city){
	if(!city){
		var province = $('#tc_province').val();
		tcQunSearch(province);
	}else{
	$.get(
			'ajax.php?mod=qun&code=tcqun',
			{'city':city},
			function(r) {
				if(is_json(r)){
					r = eval('('+r.toString()+')');
					$('#tc_wq').html(r.msg);
				} else {
					$('#tc_wq').html(r);
				}
			}
		);
	}
}

//获取最热微群
/*
function get_hot_qun_list()
{
	$.get(
		'ajax.php?mod=qun&code=block&type=24hot&random='+Math.random(),
		{},
		function(r) {
			$('#hot_24_wp').html(r);
		}
	);
}*/

//隐私设置单选强制
function privacy_radio_force(type)
{
	if (type == 1) {
		$('#join_ratify').attr({ checked: "checked"});
	} else if (type == 2) {
		$('#privacy_open').attr({ checked: "checked"});
	}
}

/**
 * 获取我的好友列表
 */
function getUserFriends(page, values)
{
	if (!page) {
		page = 1;
	}
	var url = "ajax.php?mod=qun&code=userfriends&qid="+values['qid']+"&page="+page;
	$.get(
		url,
		{},
		function (r) {
			$('#recd_wp').html(r);
			if (CHECKED != null || CHECKED.length > 0) {
				for (var i in CHECKED) {
					var obj = $('#'+i);
					if (obj) {
						obj.attr({ checked: "checked"});
					}
				}
			}
		}
	)
}

function getUserFans(page, values)
{
	if (!page) {
		page = 1;
	}
	var url = "ajax.php?mod=qun&code=userfans&qid="+values['qid']+"&page="+page;
	$.get(
		url,
		{},
		function (r) {
			$('#userfans_wp').html(r);
			if (__USERS__ != null || __USERS__.length > 0) {
				for (var i in __USERS__) {
					var obj = $('#checked_'+i);
					if (obj) {
						obj.attr({ checked: "checked"});
					}
				}
			}
		}
	);
}

//选择要推荐的关注者
var __QUN_RECD_CONTENT__ = ''
function checkedFollower(obj)
{
	if (__QUN_RECD_CONTENT__.length == '') {
		__QUN_RECD_CONTENT__= $("#topic_simple_content").val();
	}
	var content = __QUN_RECD_CONTENT__;
	var at = '';
	if (obj.checked) {
		if (isUndefined(CHECKED[obj.id])) {
			CHECKED[obj.id] = $('#nickname_wp_'+obj.value).html();
			for (i in CHECKED) {
				at = at+'@'+CHECKED[i]+' ';
			}
			content = content+at;
			$("#topic_simple_content").val(content);
		}
	} else {
		var n = $('#nickname_wp_'+obj.value).html();
		CHECKED = remove_ele(CHECKED, n);
		for (i in CHECKED) {
			at = at+'@'+CHECKED[i]+' ';
		}
		$("#topic_simple_content").val(content+at);
	}
}

var __USERS__ = Array();
function checkedFans(obj)
{
	if (obj.checked) {
		if (isUndefined(__USERS__[obj.value])) {
			
			__USERS__[obj.value] = $('#userfans_nickname_'+obj.value).val();
		}
	} else {
		__USERS__ = remove_ele(__USERS__, $('#userfans_nickname_'+obj.value).val());
	}
}

/**
 * 发送群邀请
 */
function sendQunInvite(qid){
	if(publishSubmit('topic_simple_content')){
		setTimeout(function(){location.href = 'index.php?mod=qun&qid='+qid;},1000);
	}
}

/*
function sendQunInvite()
{
	if (__USERS__.length < 1) {
		MessageBox('warning', '你还没有选择要发送邀请的粉丝呢');
		return false;
	}
	
	var message = $('#invite_message').val();
	if ('' == message) {
		MessageBox('warning', '你总要说点什么吧');
		return false;
	}
	
	var to_user = __USERS__.toString();
	
	var qid = $('#hid_qid').val();

	var post_data = {
		'message':message,
		'to_user':to_user
	}
	$.post(
		"ajax.php?mod=pm&code=do_add",
		post_data,
		function (d) {
			if ('' != d) {
				MessageBox('warning', d);
				return false;
			} else {
				show_message('发送邀请成功');
				goQun(qid);
			}
		}
	);
}
*/

function goQun(qid){
	if(qid < 1){
		show_message('请确定你要进入的群');
		return false;
	}
	location.href = thisSiteURL + "index.php?mod=qun&qid="+qid;
}

//创建群组
function create_qun()
{
}

/**
 * 显示推荐群对话框
 */
function showRecommendQunDialog(qid)
{
	var handle_key = 'recommend_qun_dialog';
	showDialog(handle_key, 'ajax', '推荐到微博', {url:'ajax.php?mod=qun&code=recd2w&qid='+qid}, 500);
}

/**
 * 显示申请加入群对话框
 */
function showApplyQunDialog(qid)
{
	var handle_key = 'apply_qun_dialog';
	showDialog(handle_key, 'local', '加入群', {id:'apply_qun_wp'}, 500);
	
	//取消按钮
	$('#cancel_btn').click(
		function(){
			closeDialog(handle_key);
		}
	);
	
	//申请按钮
	$('#apply_qun_btn').click(
		function(){
			var message = $('#apply_msg').val();
			if (message.length < 1) {
				MessageBox('warning', '至少应该写点什么吧');
				return false;
			}
			applyQun(qid, message);
		}
	);
}

/**
 * 解散群
 */
function dismissQun(qid)
{
	var param = {
		'onClickYes':function(){
			location.href="index.php?mod=qun&code=domanage&op=dismiss&qid="+qid;
		}
	}
	MessageBox('confirm', "你确定要解散当前微群吗？", '提示', param);
}

function quntheme(obj) {
	var ele= obj.split(",");

    $("#color-background").val(ele[0]);
    $("#color-background").css("background-color",ele[0]);
    $("#color-text").val(ele[1]);
    $("#color-text").css("background-color",ele[1]);
    $("#color-links").val(ele[2]);
    $("#color-links").css("background-color",ele[2]);

    $("body").css("backgroundColor",ele[0]);
    $("body").css("color",ele[1]);
    $("a").css("color",ele[2]);

    $("#setbgyes").css("backgroundImage","url("+thisSiteURL+"templates/default/images/quntheme/"+ele[3]+"/themebg_preview.jpg)");
    $("#user-background-"+ele[4]).attr("checked",true);
    if (ele[4]=="repeat") {
        $("body").css("background",ele[0]+" url("+thisSiteURL+"templates/default/images/quntheme/"+ele[3]+"/themebg.jpg) repeat scroll left top");
    } else if (ele[4]=="center"){
        $("body").css("background",ele[0]+" url("+thisSiteURL+"templates/default/images/quntheme/"+ele[3]+"/themebg.jpg) no-repeat scroll center top");
    } else if (ele[4]=="left"){
        $("body").css("background",ele[0]+" url("+thisSiteURL+"templates/default/images/quntheme/"+ele[3]+"/themebg.jpg) no-repeat scroll left top");
    }
	
    $("#qun_theme_id").val(ele[3]);
    document.getElementById(ele[3]).checked = "checked";
}
