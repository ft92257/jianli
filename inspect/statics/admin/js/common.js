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
	if (obj.val() == '') {
		return false;
	}
	
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

/*
 * 审核
 */
function audit(url, obj) {
	if (confirm('确定要进行该操作吗？')) {
		$.get(url + '/status/' + obj.value, {}, function(text){
			if (text != '1') {
				alert(text);
				obj.value = $(obj).attr('oldval');
			} else {
				$(obj).attr('oldval', obj.value);
				//alert('修改成功！');
			}
		});
	} else {
		obj.value = $(obj).attr('oldval');
	}
}

/*
 * checkbox 全部选中
 */
function checkAll(obj) {
	if (obj.checked) {
		$(obj).nextAll("input[type='checkbox']").each(function(){
			this.checked = "checked";
		});
	} else {
		$(obj).nextAll("input[type='checkbox']").attr("checked", false);
	}
}

/*
 * checkbox 不全部选中
 */
function checkNotAll(obj) {
	if (!obj.checked) {
		$(obj).prevAll("input[type='checkbox']").filter("[value='ALL']").attr("checked", false);
	}
}

/*
 * 自动更新列表字段
 */
function autoUpdate(obj, url) {
	if ($(obj).attr('oldval') == obj.value) {
		return false;
	}
	
	$.post(url, {field:$(obj).attr('field'),value:obj.value}, function(text){
		if (text.status != 1) {
			alert(text.info);
		} else {
			$(obj).attr('oldval', obj.value);
		}
	});
}

/*
 * 选择小区
 */
function selectXq(obj){
	obj = $(obj);
	var sdiv = obj.next();
	var c = $.trim(obj.val());
	if(c != ''){
		sdiv.html('<i>正在查找…</i>');
		var p = obj.offset();
		sdiv.show();
		$.post('../j/xqsearch.php?home=1&l1id=9', {q:c}, function(text){
			if (text.substr(0,3) == '<i>') {
				sdiv.next().val(0);
			} else {
				text = text.replace(/ onclick="[^"]+"/g ," onclick=\"selectedXq(this);\"");
			}
			sdiv.html(text);
		});
	}else{
		sdiv.html('');
		sdiv.hide();
	}
}

/*
 * 选中小区
 */
function selectedXq(obj) {
	obj = $(obj);
	var sdiv = obj.parent().parent();
	sdiv.next().val(obj.attr('data-xqid'));
	sdiv.prev().val(obj.html());
	sdiv.hide();
	sdiv.html('');
}