/*
 * 自动选中导航条 (适用<li><a>结构)
 * 默认class current
 */
function selectNav(id, curClass) {
	var links = $("#"+id+" li a");
	if (!arguments[1]) {
		curClass = "current";
	}

	for (var i=0;i<links.length;i++) {
		if (links[i].href == location.href) {
			links[i].parentNode.className = curClass;
			break;
		}
	}
}

/*
 * 自动验证字段 示例：
 * <input type="text" name="account" id="account" onblur="ajaxValidate(this.id)" /> <span id="_prompt_account"></span>
 */
function ajaxValidate(id) {
	var url = URL_MODEL + '/ajaxValidate';
	var data = '{_NAME:"' + id + '", ' + id + ':"' + $("#" + id).val() + '"}';
	eval("data = (" + data + ")");
	
	$.post(url, data, function(msg){
		if (msg != '') {
			$("#" + id).focus();
			$("#_prompt_" + id).html(msg);
			$("#_prompt_" + id).css("color", "red");
		} else {
			$("#_prompt_" + id).html("√");
			$("#_prompt_" + id).css("color", "green");
		}
	});
}

