/*
 * 自动选中导航条 
 * 默认class current
 */
function selectNav(id, curClass) {
	var links = $("#"+id).find('a');
	links.removeClass(curClass);
	
	links.each(function(){
		if (location.href.indexOf($(this).attr('data-match')) != -1) {
			links.filter('.' + curClass).removeClass(curClass);
			$(this).addClass(curClass);
		}
	});
}

/*
 * 自动验证字段 示例：
 * <input type="text" name="account" onblur="ajaxValidate(this)" />
 */
function ajaxValidate(tobj) {
	var obj = $(tobj);
	var url = URL + '/ajaxValidate';
	var data = {FIELD:obj.attr('name'),VALUE:obj.val()};
	
	$.post(url, data, function(msg){
		obj.nextAll('span').remove();
		
		if (msg != '') {
			obj.focus();
			obj.after('<span style="color:red"> '+msg+'</span>');
		} else {
			obj.after('<span style="color:green"> √</span>');
		}
	});
}

/*
 * 添加标签
 */
function pasteTag(tobj) {
	obj = $(tobj);
	var type = obj.attr('tagType');
	var selectedTags = obj.parent().prev('.selectedTags');
	var prev = obj.prev("input[type='text']");
	var content;
	if (tobj.tagName == 'SPAN') {
		content = obj.html();
	} else {
		content = prev.val();
	}
	
	$.post(GROUP + '/Tag/add', {type:type,content:content},
		function(text){
			if (text == '1') {
				selectedTags.append('<span onclick="deleteTag(this)">'+content+'</span>');
				prev.val('');
				var field = selectedTags.find("input[type='hidden']");
				field.val(field.val() + content + '|');
				if (tobj.tagName == 'SPAN') {
					obj.removeAttr('onClick');
					obj.unbind('click');
					obj.addClass('pasted');
				}
			} else {
				alert(text);
			}
	});
}

/*
 * 显示标签删除按钮
 */
function deleteTag(tobj) {
	obj = $(tobj);
	var cont = obj.html();
	var field = obj.siblings("input[type='hidden']");
	field.val(field.val().replace('|'+cont+'|', '|'));
	
	obj.parent().next('.hotTags').find('.pasted').each(function(){
		if (this.innerHTML == cont) {
			$(this).removeClass('pasted');
			$(this).bind('click', function(){pasteTag(this);});
		}
	});
	obj.remove();
}

/*
 * 获取区域下一级选项
 */
function getAreaChildren(tobj, level) {
	var upid = tobj.value;
	var obj = $(tobj);

	$.post(GROUP + '/District/getChildren', {upid:upid,level:level}, function(text){
		obj.nextAll('select:gt(0)').val('');
		obj.nextAll('select:gt(0)').hide();
		obj.nextAll('select:eq(0)').show();
		obj.nextAll('select:eq(0)').html(text);
	});
}

/*
 * 控制下一级是否显示
 */
function radioTarget(field, index) {
	var s = "[name='"+field+"']";
	var obj = $(s).parent().parent().next();
	$(s + ":eq("+index+")").click(function(){
		obj.show();
	});
	$(s + ":lt("+index+"),"+ s + ":gt("+index+")").click(function(){
		obj.hide();
	});
	if ($(s + ":eq("+index+")").attr('checked') == 'checked') {
		obj.show();
	} else {
		obj.hide();
	}
}
