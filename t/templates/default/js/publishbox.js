/**
 * 智能发布框相关的函数库
 *
 * @author     由~ZZ~<505171269@qq.com>整理
 * @version	   v1.0 $Date 2011-07-25
 */

var ctrlEnter;

/**
 * 获取粉丝分组选择框
 */
function getFansGroupSelect(id)
{
	$.get(
		'ajax.php?mod=misc&code=fansgroup_select',
		{},
		function(r) {
			$("#"+id).html(r);
			if ($('#fansgroup_select').val() == 0) {
				if ($('#weibo_syn_wp')) {
					$('#weibo_syn_wp').show();
					__ALLOW_WEIBO_SYN__ = true;
				}
			}
			$("#fansgroup_select").change(function(){
				if ($('#fansgroup_select').val() == 0) {
					if ($('#weibo_syn_wp')) {
						$('#weibo_syn_wp').show();
						__ALLOW_WEIBO_SYN__ = true;
					}
				} else if ($('#fansgroup_select').val() == 'create') {
					$("#fansgroup_select").get(0).selectedIndex = 0; 
					showFansGroupAddDialog();
				} else {
					if ($('#weibo_syn_wp')) {
						$('#weibo_syn_wp').hide();
						__ALLOW_WEIBO_SYN__ = false;
					}
					$('#topic_type').val($('#fansgroup_select').val());
				}
			});
		}
	);	
}

/**
 * 获取我的投票列表
 */
function getMyVoteList(page, options)
{
	var cache = Cache.get('con_vote_content_2');
	if (!isUndefined(cache) && cache.length > 0) {
		$('#con_vote_content_2').html(cache);
		return ;
	}
	
	if (isUndefined(options)) {
		options = {};
	}
	var dataUrl = 'ajax.php?mod=vote&code=my_vote&page='+page;
	$.get(
		dataUrl,
		{},
		function(r) {
			if (options.response) {
				options.response.call();
			} else{
				if (is_json(r)) {
					var json = eval('('+r+')');
					$('#con_vote_content_2').html(json.msg);
				} else {
					r = evalscript(r);
					$('#con_vote_content_2').html(r);
					Cache.save('con_vote_content_2', r);
				}
			}
		}
	);
}

/**
 * 获取我的参与的投票列表
 */
function getMyJoinList(page, options)
{
	var cache = Cache.get('con_vote_content_3');
	if (!isUndefined(cache) && cache.length > 0) {
		$('#con_vote_content_3').html(cache);
		return ;
	}
	if (isUndefined(options)) {
		options = {};
	}
	var dataUrl = 'ajax.php?mod=vote&code=my_join&page='+page;
	$.get(
		dataUrl,
		{},
		function(r) {
			if (options.response) {
				options.response.call();
			} else{
				if (is_json(r)) {
					var json = eval('('+r+')');
					$('#con_vote_content_3').html(json.msg);
				} else {
					r = evalscript(r);
					$('#con_vote_content_3').html(r);
					Cache.save('con_vote_content_3', r);
				}
			}
		}
	);
}

/**
 * 从首页获取投票应用
 */
function getVoteAvtivityFromIndex(appCode, appWpId)
{
	var options = {
		arf:'index'
	};
	getAppActivity('vote', appCode, appWpId, options);
}

//获取我的群
function getMyQun()
{	
	var html = $('#wcontent_wp').html();
	if (html == '') {
		$('#wcontent_wp').html(lang('loading'));	
	} else {
		return false;
	}

	$.get(
		'ajax.php?mod=qun&code=widgets&op=my_qun&random='+Math.random(),
		{},
		function (d) {
			d = evalscript(d);
			$("#wcontent_wp").html(d);
			var cb = ComboBoxManager.create('my_qun_select');
			cb.setComboBoxWidth(175);
			cb.change = function() {
				if (cb.val() == 0) {
					$("#mapp_item").val('');
					$("#mapp_item_id").val('');
					$("#toweibo_wp").hide();
					$("#topic_type").val('first');
				} else {
					$("#mapp_item").val('qun');
					$("#mapp_item_id").val(cb.val());
					$("#topic_type").val('first');
					$("#toweibo_wp").show();
					$("#goto_qun").attr('href', 'index.php?mod=qun&qid='+cb.val());
				}
			};
			
			$("#checkbox_toweibo").click(function(){
				if ($("#checkbox_toweibo").attr("checked")) {
					$("#topic_type").val('first');
				} else {
					$("#topic_type").val('qun');
				}
			});	
			/*
			var appitem_id = $("#mapp_item_id").val();
			if (appitem_id > 0) {
				$("#qun_select").attr('value', appitem_id); 
			}
			$("#qun_select").change(function(){
				if ($('#qun_select').val() == 0) {
					$("#mapp_item").val('');
					$("#mapp_item_id").val('');
				} else {
					$("#mapp_item").val('qun');
					$("#mapp_item_id").val($('#qun_select').val());
				}
			});	*/		
		}
	);
}

//获取签到话题
function getSignTag(uid)
{	
	$.get(
		'ajax.php?mod=class&code=getsigntag',
		{},
		function (d) {
			if(is_json(d)){
				var json = eval('('+d.toString()+')');
				if(json.done == false){
					$('#sign_tag_'+uid).html(json.msg);
				}
			}else{
				$('#sign_tag_'+uid).html(d);
			}
		}
	);
}

//话题填充到发布框
function setSignTag(){
	var tag = $('#sign_tag').val();
	if(tag){
		$('#i_already').val('#'+tag+'#');
		$('.menu_hts').hide();
	}
}

//获取长文发布框
function get_longtext_info(idval, content_id, button_id)
{
	var idval = 'undefined' == typeof(idval) ? '' : idval;
	var content_id = 'undefined' == typeof(content_id) ? 'i_already' : content_id;
	var button_id = 'undefined' == typeof(button_id) ? 'publishSubmit' : button_id;
	var longtextval = idval ? idval : $('#' + content_id).val();
	
	var cache = Cache.get('wcontent_cw');
	if (!isUndefined(cache) && cache.length > 0) {
		$('#wcontent_cw').html(cache);
		return ;
	}
	//开启ajax缓存
	$.ajaxSetup({ cache : true });
	$.post
	(
		'ajax.php?mod=longtext&code=add',
		{
			'longtext' : longtextval,
			'content_id' : content_id,
			'button_id' : button_id,
			'from_cls' : 'menu_cwb_c1'
		},
		function (d) 
		{
			//d = evalscript(d);
			$('#wcontent_cw').html(d);
			Cache.save('wcontent_cw', d);
		}
	);
}

/**
 * 在首页发布活动
 */
function getEventPost()
{
	var cache = Cache.get('con_event_content_1');
	if (!isUndefined(cache) && cache.length > 0) {
		$('#con_event_content_1').html(cache);
		return ;
	}
	$.ajaxSetup({ cache : true });
	$.get(
		'ajax.php?mod=event&code=eventpost',
		{'item':__APPITEM__,
		 'item_id':__APPITEM_ID__},
		function(r){
			$('#con_event_content_1').html(r);
			Cache.save('con_event_content_1', r);
		}
	);	
}


/**
 * 获取我的活动列表
 */
function getMyEventList(page, options)
{
	var cache = Cache.get('con_event_content_2');
	if (!isUndefined(cache) && cache.length > 0) {
		$('#con_event_content_2').html(cache);
		return ;
	}
	
	if (isUndefined(options)) {
		options = {};
	}
	var dataUrl = 'ajax.php?mod=event&code=eventlist&type=event&page='+page;
	$.get(
		dataUrl,
		{},
		function(r) {
			if (options.response) {
				options.response.call();
			} else{
				if (is_json(r)) {
					var json = eval('('+r+')');
					$('#con_event_content_2').html(json.msg);
				} else {
					r = evalscript(r);
					$('#con_event_content_2').html(r);
					Cache.save('con_event_content_2', r);
				}
			}
		}
	);
}

/**
 * 获取我的参与的活动列表
 */
function getJoinEventList(page, options)
{
	var cache = Cache.get('con_event_content_3');
	if (!isUndefined(cache) && cache.length > 0) {
		$('#con_event_content_3').html(cache);
		return ;
	}
	if (isUndefined(options)) {
		options = {};
	}
	var dataUrl = 'ajax.php?mod=event&code=eventlist&type=join&page='+page;
	$.get(
		dataUrl,
		{},
		function(r) {
			if (options.response) {
				options.response.call();
			} else{
				if (is_json(r)) {
					var json = eval('('+r+')');
					$('#con_event_content_3').html(json.msg);
				} else {
					r = evalscript(r);
					$('#con_event_content_3').html(r);
					Cache.save('con_event_content_3', r);
				}
			}
		}
	);
}

/**
 * 获取我的分类列表
 */
function getMyFenleiList(page, options)
{
	if (isUndefined(options)) {
		options = {};
	}
	var dataUrl = 'ajax.php?mod=fenlei&code=fenleilist&page='+page;
	$.get(
		dataUrl,
		{},
		function(r) {
			if (options.response) {
				options.response.call();
			} else{
				if (is_json(r)) {
					var json = eval('('+r+')');
					$('#con2_vote2_content_2').html(json.msg);
				} else {
					$('#con2_vote2_content_2').html(r);
				}
			}
		}
	);
}

/**
 * 分类
 */
function getClassPost(){
	$.post(
	  "ajax.php?mod=fenlei&code=fenleipost",
	  function(d){
			$('#' + "con2_vote2_content_1").html(d);
		  },'json'
	)
}
