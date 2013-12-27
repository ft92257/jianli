/*******************************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 * 
 * This is NOT a freeware, use is subject to license terms
 * 
 * @Filename common.js $
 * 
 * @Author http://www.jishigou.net $
 * 
 * @Date 2011年9月28日 $
 * 
 * @version $Id$
 ******************************************************************************/


var userAgent=navigator.userAgent.toLowerCase();
var is_opera=userAgent.indexOf('opera')!=-1&&opera.version();
var is_moz=(navigator.product=='Gecko')&&userAgent.substr(userAgent.indexOf('firefox')+8,3);
var is_ie=(userAgent.indexOf('msie')!=-1&&!is_opera)&&userAgent.substr(userAgent.indexOf('msie')+5,3);
var is_safari=(userAgent.indexOf('webkit')!=-1||userAgent.indexOf('safari')!=-1);
// if(top.location!=location){top.location.href=location.href;}
var maxl=thisTopicLength;

var JSLOADED = [];
var evalscripts = [];

// 用于ajax返回带有js调用等情况
function evalscript(s)
{
	if(s.indexOf('<script') == -1) return s;
	var p = /<script[^\>]*?>([^\x00]*?)<\/script>/ig;
	var arr = [];
	while(arr = p.exec(s)) {
		var p1 = /<script[^\>]*?src=\"([^\>]*?)\"[^\>]*?(reload=\"1\")?(?:charset=\"([\w\-]+?)\")?><\/script>/i;
		var arr1 = [];
		arr1 = p1.exec(arr[0]);
		if(arr1) {
			appendscript(arr1[1], '', arr1[2], arr1[3]);
			// 防止jquery去加载js文件
			s = s.replace(arr1[0], '');
		} else {
			p1 = /<script(.*?)>([^\x00]+?)<\/script>/i;
			arr1 = p1.exec(arr[0]);
			appendscript('', arr1[2], arr1[1].indexOf('reload=') != -1);
		}
	}
	return s;
}

function appendscript(src, text, reload, charset) {
	var id = hash(src + text);
	if(!reload && in_array(id, evalscripts)) return;
	if(reload && document.getElementById(id)) {
		document.getElementById(id).parentNode.removeChild($(id));
	}

	evalscripts.push(id);
	var scriptNode = document.createElement("script");
	scriptNode.type = "text/javascript";
	scriptNode.id = id;
	scriptNode.charset = charset ? charset : (is_moz ? document.characterSet : document.charset);
	try {
		if(src) {
			scriptNode.src = src;
			scriptNode.onloadDone = false;
			scriptNode.onload = function () {
				scriptNode.onloadDone = true;
				JSLOADED[src] = 1;
			};
			scriptNode.onreadystatechange = function () {
				if((scriptNode.readyState == 'loaded' || scriptNode.readyState == 'complete') && !scriptNode.onloadDone) {
					scriptNode.onloadDone = true;
					JSLOADED[src] = 1;
				}
			};
		} else if(text){
			scriptNode.text = text;
		}
		document.getElementsByTagName('head')[0].appendChild(scriptNode);
	} catch(e) {}
}

function hash(string, length) {
	var length = length ? length : 32;
	var start = 0;
	var i = 0;
	var result = '';
	filllen = length - string.length % length;
	for(i = 0; i < filllen; i++){
		string += "0";
	}
	while(start < string.length) {
		result = stringxor(result, string.substr(start, length));
		start += length;
	}
	return result;
}

function stringxor(s1, s2) {
	var s = '';
	var hash = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	var max = Math.max(s1.length, s2.length);
	for(var i=0; i<max; i++) {
		var k = s1.charCodeAt(i) ^ s2.charCodeAt(i);
		s += hash.charAt(k % 52);
	}
	return s;
}

function checkAll(e,itemName)
{
	var aa=document.getElementsByName(itemName);
	for(var i=0;i<aa.length;i++) {
		aa[i].checked=e.checked;
	}
}

function checkItem(e,allName)
{	var all=document.getElementsByName(allName)[0];
	if(!e.checked) {
		all.checked=false;
	} else {
		var aa=document.getElementsByName(e.name);
		for(var i=0;i<aa.length;i++) {
			if(!aa[i].checked)
				return;
		}
		all.checked=true;
	}
}

function addEvents(eventHandler)
{
	var tags=document.getElementsByTagName('input');
	for(var i=0;i<tags.length;i++){
		if(tags[i].getAttribute('url')=='true') {
			if(tags[i].addEventListener) {
				tags[i].addEventListener('keyup',eventHandler,true);
			} else {
				tags[i].attachEvent('onkeyup',eventHandler);
			}
		}
	}
}

function addInput(e)
{
	var obj=e.target?e.target:e.srcElement;
	var tags=document.getElementsByTagName('input');
	for(var i=0;i<tags.length;i++) {
		if(tags[i].getAttribute('url')=='true'&&tags[i]!=obj) {
			tags[i].value=obj.value;
		}
	}
}

window.onload=function(){
	addEvents(addInput);
};

function drop_mouseover(pos)
{
	try{
		window.clearTimeout(timer);
	}catch(e){}
}

function drop_mouseout(pos)
{
	var posSel=document.getElementById(pos+"Sel").style.display;
	if(posSel=="block"){
		timer=setTimeout("drop_hide('"+pos+"')",1000);
	}
}

function drop_hide(pos){
	document.getElementById(pos+"Sel").style.display="none";
};

function search_show(pos,searchType,href)
{
	document.getElementById(pos+"SearchType").value=searchType;
	document.getElementById(pos+"Sel").style.display="none";
	document.getElementById(pos+"Slected").innerHTML=href.innerHTML;
	document.getElementById(pos+'q').focus();
	try{
		window.clearTimeout(timer);
	}catch(e){}
	return false;
}

function pmTopic(i,eid,act)
{
	var i = 'undefined' == typeof(i) ? 0 : i;
	var eid = 'undefined' == typeof(eid) ? 'Pmreply_area_'+i : eid;
	var act = 'undefined' == typeof(act) ? '' : act;
	var handle_key = eid;
	var p = {
		url:"ajax.php?mod=pm&code=view_comment",
		post:{pmid:i,cod:act}
	};
	
	// 修改私信读取状态
	$("#pm_type_img_"+i).attr("src","./templates/default/images/read.gif");
	$("#pm_type_txt_"+i).removeClass('pm_type_txt');
	
	showDialog(handle_key, 'ajax', '正在载入', p, 430);
	/*
	 * if('' == eidVal){ var
	 * myAjax=$.post("ajax.php?mod=pm&code=view_comment",{pmid:i,cod:act},function(d){if(''!=d){$("#"+eid).html(d);}}); }
	 * else { $("#"+eid).html(''); }
	 */
}

// 收藏
function favoriteTopic(i,act)
{
	var i = 'undefined'==typeof(i) ? 0 : i;
	var act = 'undefined' == typeof(act) ? '' : act; 
	var eid = 'favorite_'+i;
	var myAjax = $.post("ajax.php?mod=topic&code=favor",
						{tid:i,act:act},
						function(d){
							if(''!=d){
								$("#"+eid).html(d);
							}
						}
				);
}

// 这个函数不知道用在哪里的
function setMenuPosition(showid,offset)
{
	var showobj=$jishigou_Obj(showid);
	var menuobj=$jishigou_Obj(showid+'_menu');
	if(isUndefined(offset)) {
		offset=0;
	}
	if(showobj){
		showobj.pos=fetchOffset(showobj);
		showobj.X=showobj.pos['left'];
		showobj.Y=showobj.pos['top'];
		showobj.w=showobj.offsetWidth;
		showobj.h=showobj.offsetHeight;
		menuobj.w=menuobj.offsetWidth;
		menuobj.h=menuobj.offsetHeight;
		if(offset!=-1){
			menuobj.style.left=(showobj.X+menuobj.w>document.body.clientWidth)&&(showobj.X+showobj.w-menuobj.w>=0)?showobj.X+showobj.w-menuobj.w+'px':showobj.X+'px';
			menuobj.style.top=offset==1?showobj.Y+'px':(offset==2||((showobj.Y+showobj.h+menuobj.h>document.documentElement.scrollTop+document.documentElement.clientHeight)&&(showobj.Y-menuobj.h>=0))?(showobj.Y-menuobj.h)+'px':showobj.Y+showobj.h+'px');
		} else if(offset==-1){ 
			menuobj.style.left=(document.body.clientWidth-menuobj.w)/2+'px';
			var divtop=document.documentElement.scrollTop+(document.documentElement.clientHeight-menuobj.h)/2;
			if(divtop>100) {
				divtop=divtop-100;
			}
			menuobj.style.top=divtop+'px';
		}
		if(menuobj.style.clip&&!is_opera){
			menuobj.style.clip='rect(auto, auto, auto, auto)';
		}
	}
}

function fetchOffset(obj)
{
	var left_offset=obj.offsetLeft;
	var top_offset=obj.offsetTop;
	while((obj=obj.offsetParent)!=null){
		left_offset+=obj.offsetLeft;
		top_offset+=obj.offsetTop;
	}
	return{'left':left_offset,'top':top_offset};
}

function $jishigou_Obj(id)
{
	return document.getElementById(id);
}

function isUndefined(variable)
{
	return typeof variable=='undefined'?true:false;
}

function strlen(str)
{
	var ie;
	return(ie&&str.indexOf('\n')!=-1)?str.replace(/\r?\n/g,'_').length:str.length;
}

function insertContent(target,text)
{
	var obj=$jishigou_Obj(target);
	selection=document.selection;
	if(!obj.hasfocus){ 
		obj.focus();
	}
	if(!isUndefined(obj.selectionStart)){
		var opn=obj.selectionStart+0;
		obj.value=obj.value.substr(0,obj.selectionStart)+text+obj.value.substr(obj.selectionEnd);
	}else if(selection&&selection.createRange){
		var sel=selection.createRange();
		sel.text=text;
		sel.moveStart('character',-strlen(text));
	}else{
		obj.value+=text;
	}
}

var note_step=0;
var note_oldtitle=document.title;
var note_timer;

function $jishigou(id){return document.getElementById(id);}

// 复制代码
function copyText(_sTxt)
{
	if(is_ie)
	{
		clipboardData.setData('Text',_sTxt);
		alert("网址“"+_sTxt+"”\n已经复制到您的剪贴板中\n您可以使用Ctrl+V快捷键粘贴到需要的地方");
	} else{
		alert("你的浏览器不支持脚本复制或你拒绝了浏览器安全确认，请尝试按[Ctrl+C]手动复制");
		document.getElementById('invite_url').select();
	}
}

function setCopy(_sTxt)
{
	if(is_ie){
		clipboardData.setData('Text',_sTxt);
		alert("网址“"+_sTxt+"”\n已经复制到您的剪贴板中\n您可以使用Ctrl+V快捷键粘贴到需要的地方");
	}else{
		prompt("请复制网站地址:",_sTxt);
	}
}

function trim(str)
{
	var re=/\s*(\S[^\0]*\S)\s*/;
	re.exec(str);
	return RegExp.$1;
}

function stopMusic(preID,playerID)
{
	var musicFlash=preID.toString()+'_'+playerID.toString();
	if($jishigou(musicFlash)){
		$jishigou(musicFlash).SetVariable('closePlayer',1);
	}
}

function showFlash(host,flashvar,obj,shareid,url,pageright)
{	
	var pageright = ('undefined'==typeof(pageright) ? 0 : pageright);

	var flashAddr={
		'youku.com':'http://player.youku.com/player.php/sid/FLASHVAR=/v.swf',
		'ku6.com':'http://player.ku6.com/refer/FLASHVAR/v.swf',
		'youtube.com':'http://www.youtube.com/v/FLASHVAR',
		'5show.com':'http://www.5show.com/swf/5show_player.swf?flv_id=FLASHVAR',
		'sina.com.cn':'http://vhead.blog.sina.com.cn/player/outer_player.swf?vid=FLASHVAR',
		'sohu.com':'http://v.blog.sohu.com/fo/v4/FLASHVAR',
		'tv.sohu.com':'http://share.vrs.sohu.com/FLASHVAR/v.swf',
		'mofile.com':'http://tv.mofile.com/cn/xplayer.swf?v=FLASHVAR',
		'tudou.com':'http://www.tudou.com/v/FLASHVAR',
		'v.aiao.cn':'http://tv.aiao.cn/PlayerS.swf?FlvID=FLASHVAR',
		'music':'FLASHVAR',
		'flash':'FLASHVAR'
	};
	
	var flash='<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="480" height="400">'
+'<param name="movie" value="FLASHADDR" />'
+'<param name="quality" value="high" />'
+'<param name="bgcolor" value="#FFFFFF" />'
+'<embed width="440" height="360" menu="false" quality="high" src="FLASHADDR" type="application/x-shockwave-flash" />'
+'<span>flashAddr</span>'
+'</object>';

	var vH1 = 400; var vH2 = 450;
	if( 'v.aiao.cn' == host ) { vH1 = 278; vH2 = 303; }
	var videoFlash='<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="440" height="' + vH1 + '">'
+'<param value="transparent" name="wmode"/>'
+'<param value="FLASHADDR" name="movie" />'
+'<embed src="FLASHADDR" wmode="transparent" allowfullscreen="true" type="application/x-shockwave-flash" width="480" height="' + vH2 + '"></embed>'
+'</object>';
	if(pageright)
	{
		var videoFlash='<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="180" height="200">'
+'<param value="transparent" name="wmode"/>'
+'<param value="FLASHADDR" name="movie" />'
+'<embed src="FLASHADDR" wmode="transparent" allowfullscreen="true" type="application/x-shockwave-flash" width="180" height="200"></embed>'
+'</object>';
	}

	var musicFlash='<object id="audioplayer_SHAREID" height="24" width="290" data="images/player.swf" type="application/x-shockwave-flash">'
+'<param value="images/player.swf" name="movie"/>'
+'<param value="autostart=yes&bg=0xCDDFF3&leftbg=0x357DCE&lefticon=0xF2F2F2&rightbg=0xF06A51&rightbghover=0xAF2910&righticon=0xF2F2F2&righticonhover=0xFFFFFF&text=0x357DCE&slider=0x357DCE&track=0xFFFFFF&border=0xFFFFFF&loader=0xAF2910&soundFile=FLASHADDR" name="FlashVars"/>'
+'<param value="high" name="quality"/>'
+'<param value="false" name="menu"/>'
+'<param value="#FFFFFF" name="bgcolor"/>'
+'</object>';

	var musicMedia='<object height="64" width="290" data="FLASHADDR" type="audio/x-ms-wma">'
+'<param value="FLASHADDR" name="src"/>'
+'<param value="1" name="autostart"/>'
+'<param value="true" name="controller"/>'
+'</object>';

	var flashHtml=videoFlash;
	var videoMp3=true;
	if(''==flashvar){
		alert('链接地址错误，不能为空');
		return false;
	}
	
	if('music'==host){
		var mp3Reg=new RegExp('.mp3$','ig');
		var flashReg=new RegExp('.swf$','ig');
		flashHtml=musicMedia;
		videoMp3=false;
		if(mp3Reg.test(flashvar)){
			videoMp3=true;
			flashHtml=musicFlash;
		}else if(flashReg.test(flashvar)){
			videoMp3=true;
			flashHtml=flash;
		}
	}
	flashvar=encodeURI(flashvar);
	
	if(flashAddr[host]){
		var flash=flashAddr[host].replace('FLASHVAR',flashvar);
		flashHtml=flashHtml.replace(/FLASHADDR/g,flash);
		flashHtml=flashHtml.replace(/SHAREID/g,shareid);
	}
	
	if(!obj){
		$jishigou('flash_div_'+shareid).innerHTML=flashHtml;
		return true;
	}
	
	if($jishigou('flash_div_'+shareid)){
		$jishigou('flash_div_'+shareid).style.display='';
		$jishigou('flash_hide_'+shareid).style.display='';
		obj.style.display='none';
		return true;
	}
	if(flashAddr[host]){
		var flashObj=document.createElement('div');
		flashObj.id='flash_div_'+shareid;
		obj.parentNode.insertBefore(flashObj,obj);
		flashObj.innerHTML=flashHtml;
		obj.style.display='none';
		$jishigou('play_'+shareid).style.display='none';
		var hideObj=document.createElement('div');
		hideObj.id='flash_hide_'+shareid;

		if(pageright)
		{
			;
		}
		else
		{
			var nodetxt=document.createTextNode("收起");
			hideObj.appendChild(nodetxt);
		}

		obj.parentNode.insertBefore(hideObj,obj);
		hideObj.style.cursor='pointer';
		hideObj.onclick=function(){
			if(true==videoMp3){
				stopMusic('audioplayer',shareid);
				flashObj.parentNode.removeChild(flashObj);
				hideObj.parentNode.removeChild(hideObj);
			}else{
				flashObj.style.display='none';
				hideObj.style.display='none';
			}
			obj.style.display='';
			$jishigou('play_'+shareid).style.display='block';
		};
	}
}


// -------------------------------------------------------------------------------------

var lastPublishSubmitContent = '';
var videoid = 0;
var __IMAGE_IDS__ = {};
var __ATTACH_IDS__ = {};
var __LONGTEXT_ID__ = 0;
var __APPITEM__ = '';
var __APPITEM_ID__ = 0;

// 是否允许微博同步到sina,qq等
var __ALLOW_WEIBO_SYN__ = true;
function publishSubmit(ci, i, r, topictype, roottid, is_huifu, item, item_id, xiami_id, touid) {
	// 验证登录状态 ， check_PublishBox_uid 页面中的隐藏域 存放登录后的uid
	var check_uid = $('#check_PublishBox_uid').val();
	var uid ='undefined'==typeof(check_uid)?'0':check_uid;
	var touid ='undefined'==typeof(touid)?'0':touid;
	if(uid < 1){
		ShowLoginDialog();
		return false;
	}
	
	
	var c = $('#'+ci).val();
	var c = ('undefined' == typeof(c) ? '' : c);
	var civaldefault = ('undefined' == typeof(__I_ALREADY_VALUE__) ? '' : __I_ALREADY_VALUE__);
	
	if( '' == c || '#插入自定义话题#' == c)
	{
		show_message('请输入微博内容',1);
		return false;
	}
	if(c.length < 2)
	{
		show_message('微博内容至少2个字',1);
		return false;
	}
	if(lastPublishSubmitContent==c)
	{
		 show_message('不要贪心哦，同样的内容发一次就够啦。');
		 return false;
	}
	else
	{
		//lastPublishSubmitContent=c;
	}
// 存放发布内容
// if(is_ie){
// clipboardData.setData('Text',c);
// }
// else{
// show_message('仅支持IE，请手动保存发布内容！');
// }
	
	// 只有开启同步的时候才允许同步 By ~ZZ~
	if (__ALLOW_WEIBO_SYN__) {
		var ss = $('#syn_to_sina').attr('checked');
		var ss = (('undefined' == typeof(ss) || false==ss) ? 0 : 1);
	
		var qs = $('#syn_to_qqwb').attr('checked');
		var qs = (('undefined' == typeof(qs) || false==qs) ? 0 : 1);
	
		var ks = $('#syn_to_kaixin').attr('checked');
		var ks = (('undefined' == typeof(ks) || false==ks) ? 0 : 1);
	
		var rs = $('#syn_to_renren').attr('checked');
		var rs = (('undefined' == typeof(rs) || false==rs) ? 0 : 1);
	} else {
		var ss = 0;
		var qs = 0;
		var ks = 0;
		var rs = 0;
	}

	// 转发 类别
	var topictypev = $('#'+topictype).val();
	var topictypev = ('undefined' == typeof(topictypev) ? '' : topictypev);

	var totidv = $('#'+i).val();
	var totidv = ('undefined' == typeof(totidv) ? '' : totidv);

	var is_huifuv = '';
	if ('undefined' != typeof(is_huifu) && is_huifu) {
		var is_huifuv = $('#'+is_huifu).val();
		var is_huifuv = ('undefined' == typeof(is_huifuv) ? '' : is_huifuv);
	}

	var r = ('undefined' == typeof(r) ? '' : r);
	var roottid = ('undefined' == typeof(roottid) ? 0 : roottid);
	if('design' == r || 'btn_wyfx' == r){topictype = 'first';}// 处理直播或访谈里的推荐与分享
	// 发布前的提示信息
	var loadingEid = '';
	var loadingTips = "<div><center><span class='loading'>内容正在发布中，请稍候……</span></center></div>";
	if( 'vc'==r )
	{
		loadingEid = "topic_view_comment_msg_area_" + roottid;
	} 
	else if( -1!=r.indexOf('rl') ) 
	{
		loadingEid = "replyListMsgArea";
	}
	else if( 'myblog'==r || 'myhome'==r || 'tagview'==r || 'new'==r  || 'qun'==r || 'event'==r || 'reward'==r || 'fenlei' ==r || 'vote'==r || 'live'==r || 'talk'==r || 'ask'==r || 'answer'==r)		// 加入了对微群发微博的处理
	{
		loadingEid = "listTopicMsgArea";
	}    
	if(loadingEid)
	{
		$("#" + loadingEid).html(loadingTips);
	}
	
	var appitem = __APPITEM__;
	// 应用from
	var from = appitem;
	var appitem_id = __APPITEM_ID__;
	
	// 微群应用相关
	if ((('undefined' != typeof(item))) && (('undefined' != typeof(item_id)))) {
		appitem = item;
		appitem_id = item_id;
		from = appitem;
	}
	// alert('r='+r+'&type='+topictype+'&item='+appitem+'&itemid='+appitem_id);return
	// false;
	
	// 多图
	var imageids = '';
	$.each(__IMAGE_IDS__, function(k, v) {
		if(v > 0) {
			imageids = imageids + ( imageids ? ',' + v : v );
		}
	});
	// 多附件
	var attachids = '';
	$.each(__ATTACH_IDS__, function(k, v){
		if(v > 0)
		{
			attachids = attachids + ( attachids ? ',' + v : v );
		}
	});
	
	var publish_success = 0;
	var longtextid = __LONGTEXT_ID__;
	var verify = $('#verify').val();
	// 开始发布
	var myAjax = $.post(
		"ajax.php?mod=topic&code=do_add&act=reply",
		{
			totid:totidv,
			syn_to_sina:ss,
			syn_to_qqwb:qs,
			syn_to_kaixin:ks,
			syn_to_renren:rs,
			imageid:imageids,
			attachid:attachids,
			videoid:videoid,
			longtextid:longtextid,
			content:c,
			topictype:topictypev,
			r:r,
			roottid:roottid,
			is_huifu:is_huifuv,
			item:appitem,
			item_id:appitem_id,
			from:from,
			xiami_id:xiami_id,
			touid:touid
		},
		function (d) {
			$('#xiami_id').val('');
			if(verify == 1){
				if(loadingEid) { $("#" + loadingEid).html(''); }
				if(''==d || 'vc' == r){
					publish_success = 1;
					show_message_2("发布成功、等待管理员审核");
				}else{
					show_message_2(d);
					//alert(d);
				}
			}else if( ''!=d && ''==r ) {
				if(loadingEid) { $("#" + loadingEid).html(''); }
				
				$('#'+ci).val(c);
				show_message_2(d);
				//alert(d);
				$('#'+ci).focus();

				return false;
				
			} else if('vc'==r) {				
				$("#topic_view_comment_area_"+roottid).html(d);				
				if( -1 == d.indexOf('<success></success>')) {
					if(loadingEid) { $("#" + loadingEid).html(''); }
					
					$('#'+ci).val(c);
					$('#'+ci).focus();
					
					return false;
				} else {
					publish_success = 1;
				}
			} else if(-1!=r.indexOf('rl')) {
				$("#replyListArea").html(d);				
				if( -1 == d.indexOf('<success></success>')) {
					if(loadingEid) { $("#" + loadingEid).html(''); }
					
					$('#'+ci).val(c);
					$('#'+ci).focus();
					
					return false;
				} else {
					publish_success = 1;
				}
			} else if(-1!=r.indexOf('tohome')) {
				window.location.href= thisSiteURL + 'index.php?mod=topic&code=myhome';
			} else {
				if(''!=d) {
					if(d.indexOf("发布成功") > 0){
						if(loadingEid) { $("#" + loadingEid).html(''); } 
						$('#'+ci).val('');
						if(__ALERT__){
							show_message_2(d);
						}
						return false;
					}
					if(loadingEid) { $("#" + loadingEid).html(''); } 
					
					$('#'+ci).val(c);
					show_message_2(d);
					$('#'+ci).focus();
					return false;
				} else {
					publish_success = 1;
					// 话题发布成功后，清除图片、附件、视频、音乐的ID值
					videoid = 0;
					if( 'myblog'==r || 'myhome'==r || 'tagview'==r || 'new'==r || 'qun'==r || 'event'==r || 'fenlei'==r  || 'vote'==r || 'live'==r || 'talk'==r || 'ask'==r || 'answer'==r || 'reward'==r) {                        
						listAreaPrependTopic(r);
						if('answer'==r){$("#answer_" + totidv).remove();}
						// /listTopic(0,0);
					} else if( 'mycomment'==r ) {
						window.location.href= thisSiteURL + 'index.php?mod=topic&code=myhome';
					} else {
						publishSuccess();						
					}
				}
			}
			
			if( publish_success && c != lastPublishSubmitContent ) {
				lastPublishSubmitContent = c;
			}
		}
	);	
	
	
	// select_qmd('images/qmd2.jpg');
	$('#'+ci).val(civaldefault);
	// show_message('提示消息内容','显示几秒后消失','提示标题','样式--默认');
	// show_message('发布成功',1);
	
	if('' != imageids)
	{
		__IMAGE_IDS__ = {};
		
		$('.insertImgDiv').css('display', 'block');
		
		// 清空图层的内容再隐藏
		$('.viewImgDiv').empty();
		$('.viewImgDiv').css('display', 'none');
	}
	if('' != attachids)
	{
		__ATTACH_IDS__ = {};
		
		$('.insertImgDiv').css('display', 'block');
		
		// 清空图层的内容再隐藏
		$('.viewImgDiv').empty();
		$('.viewImgDiv').css('display', 'none');
	}
	
	if(longtextid > 0)
	{
		__LONGTEXT_ID__ = 0;
	}
	
	if('' != videoid)
	{	
		videoid = 0;
		
		$('#add_video').css('display', 'block');
		$('#upload_video_list').css('display', 'none');
		$('#upload_ajax_video').css('display', 'none');
	}
	
	return true;
}

// 发布提示
function publishTips()
{
	 $("#listTopicMsgArea").html("<div><center><span class='loading'>内容正在加载中，请稍候……</span></center></div>");
	
}

// 获取当前发布的微博
function listAreaPrependTopic(r)
{   
	var r = ('undefined' == typeof(r) ? '' : r);
	publishTips();
	var myAjax = $.post
	(
		"ajax.php?mod=topic",
		{
			code:'mylastpublish',
			ref_mod:thisMod,
			ref_code:thisCode,
			r:r
		},
		function (d) 
		{
			$("#listTopicMsgArea").html('');
			// document.write(d);
			$('#listTopicArea').prepend(d);
			// $('#listTopicArea').html('debug');
			
			if ($('#empty_list_tips').length > 0) {
				$('#empty_list_tips').hide();
			}
		}
	);    
}

// 发布成功的处理
function publishSuccess()
{
	var myAjax = $.get(
		"ajax.php?mod=topic",
		{
			code:'publishsuccess'
		},
		function (d) {
			$('#show_message_area').html(d);
		}
	);  
}

function deleteTopic(i,eid,view)
{
	var i = 'undefined' == typeof(i) ? 0 : i;
	var view = 'undefined' == typeof(view) ? '' : view;

	options = {
		'onClickYes':function(){
			var myAjax = $.post (
				"ajax.php?mod=topic&code=delete",
				{tid:i},
				function (d){
					$("#"+eid).remove();
					$("#ajax_output_area").html(d);
					if('' != view) {
						window.location.href=thisSiteURL+"index.php?mod=topic&code=myhome";
					}
				}
			);
		}
	};
	
	MessageBox('confirm', "确认删除？", '提示', options);
}

function deleteVerify(i,eid,view)
{
	var i = 'undefined' == typeof(i) ? 0 : i;
	var view = 'undefined' == typeof(view) ? '' : view;

	options = {
		'onClickYes':function(){
			var myAjax = $.post (
				"ajax.php?mod=topic&code=delverify",
				{tid:i},
				function (d){
					$("#"+eid).remove();
					$("#ajax_output_area").html(d);
					if('' != view) {
						window.location.href=thisSiteURL+"index.php?mod=topic&code=myhome";
					}
				}
			);
		}
	};
	
	MessageBox('confirm', "确认删除？", '提示', options);
}

// 关注 和 取消关注
function follow(fid,eid,events,follow_button)
{
	var events = ('undefined' == typeof(events) ? '' : events);
	
	// follow_button = 关注和取消用户按钮（图标1 和图标2）
	var follow_button = ('undefined' == typeof(follow_button) ? '' : follow_button);
	// alert(follow_button);return false;
	
	$('.'+eid).html('');
	var myAjax=$.post(
		"ajax.php?mod=topic&code=follow",
		{
			id:fid,
			follow_button:follow_button
		},
		function(d)
		{
			if(''!=d)
			{
				$('.'+eid).html(d);
				if( -1 != d.indexOf('<success></success>'))
				{
					if('add' == events)
					{
						// 触发选择分组层
						parent.document.getElementById('button_'+fid).onclick();
					}
					else
					{
						$("#user_grouplist_"+fid).remove();
					}
				}
				if('channel' == follow_button)
				{
					var mhtml = '<li class="mychannel" id="ch_'+fid+'"><a href="index.php?mod=channel&id='+fid+'">'+$('#channel_name').html()+'</a></li>';
					if('add' == events)
					{
						$('#my_channels').append(mhtml);
					}else{
						$('#ch_'+fid).remove();
					}
				}
			}
		}
	 );
}

// 复制代码
function talkvisit(_sTxt)
{
	if(is_ie)
	{
		clipboardData.setData('Text',_sTxt);
		MessageBox('notice', "将链接通过MSN、QQ或E-mail发给好友：<br><textarea style='width:200px;height:50px;overflow:hidden;'>"+_sTxt+"</textarea><br>", '复制链接，邀请好友参加访谈');
	} else{
		MessageBox('notice',"你的浏览器不支持此操作！", '提示');
	}
}

// 举报
function ReportSub(tid,report_content,report_reason,eid)
{
	var totid = $('#'+tid).val();
	var content = $('#'+report_content).val();
	var reasons = $('#'+report_reason).val();

	if(trim(content) == ''){
		show_message("请说明举报理由。",1);
		return false;
	}
	if('0'==reasons) {
		show_message("请选择举报类型");
		return false;
	}

	var myAjax=$.post(
	"ajax.php?mod=topic&code=doreport",
	{
		totid:totid,
		report_content:content,
		report_reason:reasons
	},
		function (d) {
			closeDialog("hk_report_dialog");
			show_message(d,1);
		}
 );

}


// 编辑微博
function modifyTopic(i,eid,types,attach)
{
	var i = 'undefined' == typeof(i) ? 0 : i;
	var eid = 'undefined' == typeof(eid) ? 'modify_topic_list_'+i : eid;
	var attach = 'undefined' == typeof(attach) ? 0 : attach;
	var handle_key = eid;
	var ajax_url = "ajax.php?mod=topic&code=modifytopic";
	var post = {
		tid:i,
		types:types,
		handle_key:handle_key,
		attach:attach
	};
	
	showDialog(handle_key, 'ajax', "编辑", {"url":ajax_url, "post":post}, 420);
	/*
	 * var eidVal=$("#"+eid).html(); if(''==eidVal){ var myAjax=$.post(
	 * "ajax.php?mod=topic&code=modifytopic", { tid:i, types:types },
	 * function(d) { if(''!=d){ $("#"+eid).html(d); } }); } else{
	 * $("#"+eid).html(''); }
	 */
}

// 修改微博成功后的操作
function modifyTopicSuccess(tid, type)
{
	if (isUndefined(type)) {
		var type = '';
	}
	
	var refmod = thisMod;
	var refcode = thisCode;
	if (type == 'reply_list_ajax') {
		refcode = 'reply_list_ajax';
	} else {
		refmod = thisMod;
		refcode = thisCode;
	}
	
	var myAjax=$.post(
		"ajax.php?mod=topic&code=updatecurrent",
		{
			tid:tid,
			refmod:refmod,
			refcode:refcode
		},
		function(r) {
			if (type == 'reply_list_ajax') {
				$('#view_comment_'+tid).html(r);
			} else {
				$('#topic_list_'+tid).html(r);
			}
		}
	);
}

function do_modifyTopic(i,imageids,attachids,content,types, handle_key)
{
	// 多图
	var imageids = $('#' + imageids).val();
	$.each(__IMAGE_IDS__, function(k, v){
		if(v > 0)
		{
			imageids = imageids + ( imageids ? ',' + v : v );
		}
	});
	// 多附件
	var attachids = $('#' + attachids).val();
	$.each(__ATTACH_IDS__, function(k, v){
		if(v > 0)
		{
			attachids = attachids + ( attachids ? ',' + v : v );
		}
	});
	
	var i='undefined'==typeof(i)?0:i;

	var content = $('#'+content).val();
	var content = 'undefined' == typeof(content) ? '': content;
	
	var myAjax=$.post(
	"ajax.php?mod=topic&code=do_modifytopic",
	{
		tid:i,
		imageid:imageids,
		attachid:attachids,
		content:content
	},
	function (d) 
	{
		closeDialog(handle_key);


		if('topic_list' == types || 'reply_list_ajax' == types)
		{
			modifyTopicSuccess(i, types);
			closeDialog('modify_topic_list_'+i);
		}
		else
		{
			replyList();
		}

		show_message('微博编辑成功',1);
		// parent.document.getElementById("modify_topic_"+tid).style.display="none";
	}
 );

}

// 弹出框定位
function _cumulpos(a)
{
	var b=0,c=0,a=0;
	do{
			b+=a.offsetTop||0;
			c+=a.offsetLeft||0;
			a=a.offsetParent;
	}while(a);
	return [c,b];
}

// 添加分组
function GroupSubmit(ci,i,act,touid) {

	var c = $('#'+ci).val();
	var c = 'undefined' == typeof(c) ? '' : c;

	if(''==c) {
		show_message("请输入分组名称");
		return false;
	}

	var i = 'undefined' == typeof(i) ? 0 : i;
	var r = 'undefined' == typeof(r) ? '' : r;
	var act = 'undefined' == typeof(act) ? '' : act;
	var touid = 'undefined' == typeof(touid) ? 0 : touid;

	// alert(i); return false;
	var myAjax = $.post(
		"ajax.php?mod=topic&code=do_group",
		{
			group_name:c,
			gid:i,
			act:act,
			touid:touid
		},
		function (d) {
			if( ''!=d) {
				  if('modify' != act) {
						if ( act == 'menu_add') {
							// add_group_menu_27
							$("#add_group_menu_"+touid).html( $("#add_group_menu_"+touid).html()+d);
						} else {
							$("#show_message_area").html($("#show_message_area").html()+d);
							$("#add_group_fllow").html( $("#add_group_fllow").html()+d);
							$("#add_group_view").html( $("#add_group_view").html()+d);
							$("#add_group_menu_"+touid).html( $("#add_group_menu_"+touid).html()+d);
						}
					} else{
						$("#up_grouplist_"+i).html(d);
						$("#up_grouplist_view_"+i).html(d);
						parent.document.getElementById('modify_group_'+i).value=c;
					}
			}
		}
	);

$('#'+ci).val('');

	return true;
}

// 删除分组
function del_group(group_id,touid)
{
	var group_id = 'undefined' == typeof(group_id) ? 0 : group_id;
	var myAjax = $.post(
		"ajax.php?mod=topic&code=del_group",
		{
			group_id:group_id
		},

		function (d) {
			if(''!=d) {
				$("#del_group_ajax_"+group_id).remove();
				$("#del_group_follow_top_"+group_id).remove();
			}
		}
	);
}


// 分组弹出框
function get_group_choose(userid){
	
	
	var handle_key = "global_select_"+userid;
	var title = "正在载入...";
	var ajax_url = "ajax.php?mod=topic&code=group_menu&to_user="+userid;
	showDialog(handle_key, 'ajax', title, {"url":ajax_url});
/*
 * var eidVal = $("#"+"global_select_"+userid).html();
 * 
 * if(''==eidVal){ var myAjax=$.post( "ajax.php?mod=topic&code=group_menu", {
 * to_user:userid }, function(d) { if(''!=d) {
 * document.getElementById("global_select_"+userid).style.display="block";
 * $("#"+"global_select_"+userid).html(d); } }); } else{
 * $("#"+"global_select_"+userid).html(''); }
 */
}

function groupState(gid,touid,eid) {
	
	var myAjax = $.post(
		"ajax.php?mod=topic&code=group_fields",
		{
			gid:gid,
			touid:touid
		},
		function (d) {
			if(''!=d) {
				$('#'+eid).html(d);
			}
			/*
			 * if(eid.checked){ eid.checked=false; //show_message('分组取消成功',1);
			 * }else{ eid.checked=true; //show_message('分组设置成功',1); }
			 */
			
			var myAjax = $.post(
					"ajax.php?mod=topic&code=group_list",
					{
						touid:touid
					},
					function (d) {
						$('#'+"group_list2_"+touid).html(d);
						parent.document.getElementById('group_list2_'+touid).style.display='block';

					}
				);
		}
	);
}

// 添加备注 、 操作框
function get_remark(uid)
{
   // var eidVal=$("#"+"get_remark_"+uid).html();
   var handle_key = "get_remark_"+uid;
   var ajax_url = "ajax.php?mod=topic&code=remark&uid="+uid;
   showDialog(handle_key, 'ajax', '设置备注名', {"url":ajax_url}, 300);
   /*
	 * if(''==eidVal){ //设置备注名; var myAjax=$.post(
	 * "ajax.php?mod=topic&code=remark", { uid:uid }, function(d) { if(''!=d) {
	 * document.getElementById("get_remark_"+uid).style.display="block";
	 * $("#"+"get_remark_"+uid).html(d); } }); } else{
	 * $("#"+"get_remark_"+uid).html(''); }
	 */
}



// 添加备注
function publishSubmit_remark(ci,buddyid)
{
	var c = $('#'+ci).val();
	var c = 'undefined' == typeof(c) ? '' : c;
	
	var buddyid = 'undefined' == typeof(buddyid) ? 0 : buddyid;

	var myAjax = $.post
	(
		"ajax.php?mod=topic&code=add_remark",
		{
			remark:c,
			buddyid:buddyid
		},
		function (d) 
		{	
			if(d) 
			{	
				// 返回页面中 输出的错误提示信息
				show_message(d,2); return false;
				$('#'+"remarklist_"+buddyid).html(d);     
			}
			else
			{
				if(c)
				{
					$('#'+"remarklist_"+buddyid).html('(' + c + ')');
				}
				else
				{
					$('#'+"remarklist_"+buddyid).html('');
				}
				
				show_message('备注设置成功',2);
			}
			
			var handle_key = "get_remark_"+buddyid;
			closeDialog(handle_key);
   
		}
	);
	
	return true;
}

// 添加关注话题
function favoriteTag(i,act)
{
	var i='undefined'==typeof(i) ? '' : i;
	// alert(act); return false;
	if(act == 'input_add'){
	 var i = $('#'+i).val();
	}
	
	var act='undefined'==typeof(act)?'':act;
	
	var eid='favorite_tag_id';

	var myAjax=$.post
	(
			"ajax.php?mod=topic&code=favor_tag",
			{
				tag:i,
				act:act
			},
			function(d)
			{
				if(''!=d)
				{	// alert(d);
					if(act != 'input_add')
					{
						$("#"+eid).html(d);
					}
					else
					{
					$("#add_ajax_favorite_tags").html(d);
					parent.document.getElementById('tag_name').value="";
					}
					
				}
				
				if('delete' == act){
					 // alert(i);
					 $("#favorite_"+i).remove();
					 
				}
				$("#favorite_tag_id").fadeOut(1000);//取消关注后隐藏按钮
				show_message(d);
			}
	);
}

// 转发提交js
function Forward(content,forward_tid,tid,check,options)
{
	if (isUndefined(options)) {
		options = {};
	}
	
	var cid = content;
	
	var content = trim($('#'+cid).val());
	var check = $('#'+check).val();
	
	if(''==content || content.length < 2) {
		show_message("微博内容至少2个字。");
		$('#'+cid).focus();
		return false;
	}
	
	// $("#topic_forward_content_area_" + tid).html('');
	$("#topic_forward_content_area_" + tid).append('<br />内容正在发布中，请稍候……');
	
	// 应用
	var appitem = '';
	var appitem_id = 0;
	if (options.appitem && options.appitem_id) {
		appitem = options.appitem;
		appitem_id = options.appitem_id;
	}
	
	var is_reward = 0;
	if(options.is_reward){
		is_reward = options.is_reward;
	}

	// 图片
	var imageids = '';
	$.each(__IMAGE_IDS__, function(k, v){
		if(v > 0)
		{
			imageids = imageids + ( imageids ? ',' + v : v );
		}
	});
	// 附件
	var attachids = '';
	$.each(__ATTACH_IDS__, function(k, v){
		if(v > 0)
		{
			attachids = attachids + ( attachids ? ',' + v : v );
		}
	});
	var verify = $('#verify').val();
	var myAjax=$.post
	(
		"ajax.php?mod=topic&code=forward",
		{
			forward_tid:forward_tid,
			tid:tid,
			content:content,
			topictype:check,
			item:appitem,
			item_id:appitem_id,
			imageid:imageids,
			attachid:attachids,
			is_reward:is_reward
		},
		function (d) 
		{
			if(d)
			{
				if( -1 != d.indexOf('<success></success>') )
				{
					if(verify == 1){
						show_message("发布成功、等待管理员审核",2);
					}else{
						listAreaPrependTopic();
						// listTopic(0,0);
						show_message('微博转发成功',2); 
					}
					
					// parent.document.getElementById('forward_list').style.display='none';
					// parent.document.getElementById("forward_menu_"+tid).style.display="none";
					closeDialog("forward_menu_"+tid);
					return true;
				}
				else if( -1 != d.indexOf('[转发成功]') ){
					// show_message(d,2);
					if(__ALERT__){
						show_message(d,2);
					}
					closeDialog("forward_menu_"+tid);
					return true;
				}
				else
				{
					alert(d);
				}
			}	 
			else
			{
				alert('微博转发失败');
			}
			
			$('#' + cid).val(content);
			$('#' + cid).focus();
			
			return false;   
	   }
	);
	
	$('#' + cid).val('');
	

	if('' != imageids)
	{
		$('.insertImgDiv').css('display', 'block');
		
		// 清空该层的内容再隐藏
		$('.viewImgDiv').empty();
		$('.viewImgDiv').css('display', 'none');
	}
	
	return true;
}

// 话题弹出框
function get_tag_choose(uid,tc_type,ajax){
	var uid = 'undefined' == typeof(uid) ? 0 : uid;
	var tc_type = 'undefined' == typeof(tc_type) ? '' : tc_type;
	var ajax = 'undefined' == typeof(ajax) ? '' : ajax;
	var _cache_id = tc_type + '_' + uid;
	
	if(undefined==Cache.get(_cache_id))
	{
		var _jsgcid = 'JSGCACHE_get_tag_choose_'+_cache_id;
		if(undefined!=Cache.get(_jsgcid))
		{
			return false;
		}
		Cache.save(_jsgcid,_jsgcid);

		var myAjax = $.post(
			"ajax.php?mod=topic&code=tag_menu",
			{
				uid:uid,
				type:tc_type
			},
			function (d) {
				if(''!=d) {
					Cache.save(_cache_id,d);
					document.getElementById(ajax+"tag_"+uid).style.display="block";
					$("#"+ajax+"tag_"+uid).html(Cache.get(_cache_id));
				}
			}
		);
	}
	else
	{
		document.getElementById(ajax+"tag_"+uid).style.display="block";
		$("#"+ajax+"tag_"+uid).html(Cache.get(_cache_id));
	}
}

// 评论下拉框
function replyTopic(i,eid,tReplys,not_allow_forward,allow_attach,options)
{
	var i = ( 'undefined'==typeof(i) ? 0 : i );
	var tReplys = ( 'undefined'==typeof(tReplys) ? 0 : tReplys );
	var eid = ( 'undefined'==typeof(eid) ? 'reply_area_'+i : eid );
	var allow_attach = ( 'undefined'==typeof(allow_attach) ? 0 : allow_attach );
	
	// 增加了item,item_id参数 Modify by ~ZZ~ 2011-06-30
	if (isUndefined(options)) {
		options = {};
	}
	
	var eidVal=$("#"+eid).html();
	if (isUndefined(not_allow_forward)) {
		not_allow_forward = false;
	}

	if(''==eidVal)
	{
		// Modify by ~ZZ~ 2011-06-30
		var tcHTML = topicCommentHTML(i,tReplys,not_allow_forward,allow_attach,options);
		
		$("#"+eid).html(tcHTML);
	}
	else
	{
		$("#"+eid).html('');
	}
}
// 评论弹出框(目前仅在微博相册中使用)
function DreplyTopic(tid,allow_attach)
{
	var allow_attachHTML = '';
	var tid = 'undefined' == typeof(tid) ? 0 : tid;
	var allow_attach = 'undefined' == typeof(allow_attach) ? 0 : allow_attach;
	var type_html = '<input name="topicReplyType_' + tid + '" type="checkbox" id="topicReplyType_' + tid + '" value="reply" onclick="select_checked(\'topicReplyType_' + tid + '\');"/> <label for="topicReplyType_' + tid + '" style="cursor:pointer;">同时转发微博</label>';
	if(allow_attach){
		allow_attachHTML = 'attachUploadifyHTML(' + tid + ', "reply_content_' + tid + '", "reply_attach_uploadify_' + tid + '");';
	}
	var handle_key = "reply_menu_"+tid;
	var fcHTML = '<div id="reply_list" class="dialog_inner" style="width:400px;_overflow:hidden;"><div class="mWarp"><table width="380"><tr> <td colspan="2"><div class="alt_3"> <div class="fbqCount" style=" position:absolute; margin:-10px 0 0 285px;*margin:-22px 0 0 -60px;"><style>ul.mycon li{ width:65px;}</style> <ul class="mycon fontGreen" style="width:130px"> <li>还可以输入</li> <li id="wordc"><span id="wordcNump_' + tid + '" style="color:#ff0000;">' + thisTopicLength + '</span></li> <li style="width:14px;">字</li> </ul> </div> <div class="menuf"> <div class="menuf_bq" ><b class="menu_bqb_c"><a href="javascript:viod(0);" onclick="topic_face(\'reply_' + tid + '\',\'reply_content_' + tid + '\');return false;">表情</a></b> <div class="forward_f" id="reply_' + tid + '"></div> </div></div> </div> <div class="alt_3"> <textarea id="reply_content_' + tid + '" name="content" onkeyup="javascr' + 'ipt:checkWord(' + thisTopicLength + ',event,\'wordcNump_' + tid + '\')" class="textarea" style="width:380px;"></textarea><input type="hidden" id="replytid_' + tid + '" name="replytid_' + tid + '" value="' + tid + '"/> <input type="hidden" id="is_huifu_' + tid + '" name="is_huifu_' + tid + '" value=""/> </div></td> </tr> <tr> <td width="150"><div class="alt_5">'+type_html+'</div> </td> <td align="right"><div class="rb_a1"> <input id="rcbtna_' + tid + '" onclick="publishSubmit(\'reply_content_' + tid + '\',\'replytid_' + tid + '\',\'vc\',\'topicReplyType_' + tid + '\',' + tid + ',\'is_huifu_' + tid + '\',\'\',\'\');closeDialog(\'' + handle_key + '\');" type="button" value="评 论" title="按Ctrl+Enter直接发布" class="rb_c9"/> <input type="button" name="del" value="取 消" onclick="closeDialog(\'' + handle_key + '\');" class="rb_a2"/> </div> </td> </tr>  <tr> <td colspan="2"> <div id="reply_image_uploadify_' + tid + '"> </div> <div id="reply_attach_uploadify_' + tid + '"> </div></td> </tr> </table></div></div> <sc' + 'ript type="text/jav' + 'ascri' + 'pt"> $(document).ready(function(){ imageUploadifyHTML(' + tid + ', "reply_content_' + tid + '", "reply_image_uploadify_'  + tid + '");' + allow_attachHTML + '$(".menu_bqb_c").click(function(){$("#reply_' + tid + '").show();}); $(".menu_bqb_c1").click(function(){$("#reply_' + tid + '").hide();}); initAiInput("reply_content_' + tid + '");}); $("#reply_content_' + tid + '").bind("keydown",function(event){ event = event || window.event; if(event.keyCode == 13 && event.ctrlKey) { $("#rcbtna_' + tid + '").click(); } }); </scri' + 'pt>';

	var fcFrameHtml = '<div class="zfbox"><div class="zfTitle" id="zfTitle_'+tid+'"><ul class="zfti"><li id="zf_m1" class="zfhover">微博评论</li><sub class="menu_zf_c1" onclick="closeDialog(\''+handle_key+'\');"></sub></ul></div><div id="zfcon_zf_m_1" class="zfcon">'+fcHTML+'</div></div>';
	var h = showDialog(handle_key, 'local', '对微博发表评论', {"html":fcFrameHtml, "noTitleBar":true}, 440);
	// 标题栏拖动绑定
	draggable(h.dom.wrapper, $('#zfTitle_'+tid));
}

// foxis 2011.08.25
function imageUploadifyHTML(tid, content_textarea_id, toeid, iuConfirm)
{
	var tid = ( 'undefined' == typeof(tid) ? 0 : tid );
	var content_textarea_id = ( 'undefined' == typeof(content_textarea_id) ? '' : content_textarea_id );
	var toeid = ( 'undefined' == typeof(toeid) ? '' : toeid );
	var iuConfirm = ( 'undefined' == typeof(iuConfirm) ? '' : iuConfirm );
		
	if(iuConfirm)
	{

		
		if(toeid)
		{
			$('#' + toeid).html('<span class="loading" title="Loading..">Loading..</span>');
		}
		
		var confirmHTML2 = '<div class="menu_tq" onclick="imageUploadifyHTML(\'' + tid + '\', \'' + content_textarea_id + '\', \'' + toeid + '\', \'' + iuConfirm + '\');"><b class="menu_tqb_c">图片</b></div>';


		$.post
		(
			'ajax.php?mod=uploadify&code=html',
			{
				'tid' : tid,
				'new' : 1,
				'only_js' : 0,
				'content_textarea_id' : content_textarea_id
			},
			function (iuHTML) 
			{
				if( -1 != iuHTML.indexOf('<success></success>'))
				{
					if(toeid)
					{
						$('#' + toeid).html(confirmHTML2+iuHTML);
					}
					else
					{
						return iuHTML;
					}
				}
			}
		);
	}
	else
	{
		iuConfirm = 1;
		
		var confirmHTML = '<div class="menu_tq" onclick="imageUploadifyHTML(\'' + tid + '\', \'' + content_textarea_id + '\', \'' + toeid + '\', \'' + iuConfirm + '\');"><b class="menu_tqb_c">图片</b></div>';
		
		if(toeid)
		{
			$('#' + toeid).html(confirmHTML);
		}
		else
		{
			return confirmHTML;
		}
	}
}
function attachUploadifyHTML(tid, topic_textarea_id, toeid, iuConfirm, item, itemid)
{
	var tid = ( 'undefined' == typeof(tid) ? 0 : tid );
	var topic_textarea_id = ( 'undefined' == typeof(topic_textarea_id) ? '' : topic_textarea_id );
	var toeid = ( 'undefined' == typeof(toeid) ? '' : toeid );
	var iuConfirm = ( 'undefined' == typeof(iuConfirm) ? '' : iuConfirm );
	var item = ( 'undefined' == typeof(item) ? '' : item );
	var itemid = ( 'undefined' == typeof(itemid) ? 0 : itemid );
		
	if(iuConfirm)
	{
		if(toeid)
		{
			$('#' + toeid).html('<span class="loading" title="Loading..">Loading..</span>');
		}

		var confirmHTML3 = '<div class="menu_fj menu_fj_reply_style" onclick="attachUploadifyHTML(\'' + tid + '\', \'' + topic_textarea_id + '\', \'' + toeid + '\', \'' + iuConfirm + '\', \'' + item + '\', \'' + itemid + '\');"><b class="menu_fjb_c">附件</b></div>';

		$.post
		(
			'ajax.php?mod=uploadattach&code=html',
			{
				'tid' : tid,
				'new' : 1,
				'only_js' : 0,
				'topic_textarea_id' : topic_textarea_id,
				'item' : item,
				'itemid' : itemid
			},
			function (iuHTML) 
			{
				if( -1 != iuHTML.indexOf('<success></success>'))
				{
					if(toeid)
					{
						$('#' + toeid).html(confirmHTML3+iuHTML);
					}
					else
					{
						return iuHTML;
					}
				}
			}
		);
	}
	else
	{
		iuConfirm = 1;
		
		var confirmHTML = '<div class="menu_fj menu_fj_reply_style s" onclick="attachUploadifyHTML(\'' + tid + '\', \'' + topic_textarea_id + '\', \'' + toeid + '\', \'' + iuConfirm + '\', \'' + item + '\', \'' + itemid + '\');"><b class="menu_fjb_c">附件</b></div>';
		
		if(toeid)
		{
			$('#' + toeid).html(confirmHTML);
		}
		else
		{
			return confirmHTML;
		}
	}
}
function downattach(aid)
{
	var check_uid = $('#check_PublishBox_uid').val();
	var uid ='undefined'==typeof(check_uid)?'0':check_uid;	
	if(uid < 1){
		ShowLoginDialog();
		return false;
	}
	var aid = ( 'undefined' == typeof(aid) ? 0 : aid );
	if (aid > 0)
	{
		var durl = 'ajax.php?mod=uploadattach&code=down';
		var myAjax = $.post (
			durl,{aid:aid},
			function (d){
				if('' != d) {
					var s= d.split(',');var l = 'yes';var scstr = '';
					if(s[0] > 0){
						// options =
						// {'onClickYes':function(){$.post(durl,{aid:aid,dos:l},function(){});window.location.href
						// = thisSiteURL + s[2];}}
						options = {'onClickYes':function(){window.location.href = thisSiteURL + s[2];}};
						if(s[1] > 0){scstr = '确定下载将扣除' + s[1] + '积分，并奖给附件发布者！';}
						MessageBox('confirm', scstr, '您确定要下载该文件吗？', options);
					}else if(s[0] == 0){
						MessageBox('notice', "您的权限不够，无法进行下载操作！", '提示');
					}else{
						MessageBox('notice', "您的积分不够，无法下载该文件！", '提示');
					}
				}
			}
		);
	}
	else
	{
		return false;
	}
}

function set_attach_score(i,id)
{
	var id = ('undefined' == typeof(id) ? 0 : id);
	var score = ('undefined' == typeof(i) ? 0 : i);
	if(id > 0 && score.length > 0)
	{
		var myAjax = $.post('ajax.php?mod=uploadattach&code=score',{id:id,score:score},function(){});
	}
}

// Modify by ~ZZ~ 2011-06-30
function topicCommentHTML(tid,tReplys, not_allow_forward,allow_attach,options)
{
	var type_html = '';
	var allow_attach = ( 'undefined'==typeof(allow_attach) ? 0 : allow_attach );
	// 增加了item,item_id参数 Modify by ~ZZ~ 2011-06-30
	if (isUndefined(options)) {
		options = {};
	}
	
	if (!isUndefined(not_allow_forward) && not_allow_forward) {
		type_html = '<input name="topicReplyType_' + tid + '" type="hidden" id="topicReplyType_' + tid + '" value="reply" onclick="select_checked(\'topicReplyType_' + tid + '\');"/> ';
	} else {
		type_html = '<input name="topicReplyType_' + tid + '" type="checkbox" id="topicReplyType_' + tid + '" value="reply" onclick="select_checked(\'topicReplyType_' + tid + '\');"/> <label for="topicReplyType_' + tid + '" style="cursor:pointer;">同时转发微博</label>';
	}
	
	var appitem = '';
	var appitem_id = 0;
	if (options.appitem && options.appitem_id) {
		appitem = options.appitem;
		appitem_id = options.appitem_id;
	}
	var item = '';
	if (options.item) {
		item = options.item;
	}
	var publishHtml = '';
	var tcHTML = '';
	var allow_attachHTML = '';
	if(allow_attach){
		allow_attachHTML = 'attachUploadifyHTML(' + tid + ', "reply_content_' + tid + '", "reply_attach_uploadify_' + tid + '",0,"' + appitem + '",' + appitem_id + ');';
	}
	if(item){
		publishHtml = '<scr' + 'ipt language="Javasc' + 'ript">var tReplys = ' + tReplys + ';if(tReplys > 0){topic_view_comment_list(' + tid + ',\'' + item + '\');}</scr' + 'ipt>';
		tcHTML = '<div class="blogTxt b15"> <div class="top_2"></div> <div class="mid">'+publishHtml+'<div id="topic_view_comment_area_' + tid + '"></div></div><div class="bottom"></div></div>';
	}else{
		publishHtml = '<div class="comment_p_t"> <div class="menuf"><img class="comment_p_img" src="' + thisFace + '" onerror="javascript:faceError(this);"/></div> <textarea id="reply_content_' + tid + '" name="textarea" class="replybb" onkeyup="javas' + 'cript:checkWord(' + thisTopicLength + ',event,\'wordCheckNum_' + tid + '\')" ></textarea> <input type="hidden" id="replytid_' + tid + '" name="replytid_' + tid + '" value="' + tid + '"/> <input type="hidden" id="is_huifu_' + tid + '" name="is_huifu_' + tid + '" value=""/> <input id="rcbtn_' + tid + '" onclick="publishSubmit(\'reply_content_' + tid + '\',\'replytid_' + tid + '\',\'vc\',\'topicReplyType_' + tid + '\',' + tid + ',\'is_huifu_' + tid + '\',\'' + appitem + '\',\'' + appitem_id + '\');$(\'#wordCheckNum_' + tid + '\').html('+thisTopicLength+');" type="button" class="sBtn_2 sBtn_2t" value="评 论" title="按Ctrl+Enter直接发布"/> </div> <div class="comment_p_b"> <div class="comment_p_t2"><div class="menuf_bq"><b id="reply_ajax_menu_bqb_c_' + tid + '" class="reply_ajax_menu_bqb_c"><a href="javascript:viod(0);" onclick="topic_face(\'reply_face_' + tid + '\',\'reply_content_' + tid + '\');return false;">表情</a></b> <div class="forward_f2" style="margin-top:0px;" id="reply_face_' + tid + '" style="border:1px solid #BFBFBF;display:none;"></div> </div><div id="reply_image_uploadify_'  + tid + '" class="comment_p_b2 comment_p_b22"></div><div id="reply_attach_uploadify_'  + tid + '" class="comment_p_b2 comment_p_b22"></div></div> <div class="comment_p_t3"> '+type_html+'</div> </div>  <scr' + 'ipt language="Javasc' + 'ript"> $(document).ready(function(){ imageUploadifyHTML(' + tid + ', "reply_content_' + tid + '", "reply_image_uploadify_' + tid + '"); ' + allow_attachHTML + ' $("#reply_ajax_menu_bqb_c_' + tid + '").click(function(){$("#reply_face_' + tid + '").show();}); $(".menu_bqb_c1").click(function(){$("#reply_face_' + tid + '").hide();}); $("#reply_content_' + tid + '").bind("keyup keypress",function(e){ if(e.which==13){return false;} var width=$(this).width(); if (!$.browser.msie )$(this).css("letter-spacing","0.05em"); var len=$(this).val().replace(/[^\\x00-\\xff]/g,"aa").length-1; $(this).height(17*(parseInt(len*6.5/width)+1)); }) }); $("#reply_content_' + tid + '").bind("keydown",function(event){ event = event || window.event; if(event.keyCode == 13 && event.ctrlKey) { $("#rcbtn_' + tid + '").click(); } }); var tReplys = ' + tReplys + ';if(tReplys > 0){topic_view_comment_list(' + tid + ',\'comment\');}initAiInput("reply_content_' + tid + '");</scr' + 'ipt>';
		tcHTML = '<div class="blogTxt b15"> <div class="top_2"></div> <div class="mid"><div id="to_reply_user_' + tid + '"></div>'+publishHtml+'<p class="comment_p h10px"><div id="topic_view_comment_area_' + tid + '"><div id="topic_view_comment_msg_area_' + tid + '"></div></div></p></div><div class="bottom"></div> </div>';
	}
		
	return tcHTML;
}
function topic_view_comment_list(tid,item)
{
	var eid = "topic_view_comment_area_" + tid;
	
	$("#" + eid).html("<div><center><span class='loading'>内容正在加载中，请稍候……</span></center></div>");
	
	var myAjax=$.post("ajax.php?mod=topic&code=view_"+item,{tid:tid},function(d){$("#" + eid).html(d);});
}

// 绑定转发点击事件
function onForwardSelectedListener(tid, options)
{
	if (isUndefined(options)) {
		options = {};
	}
	
	if($('#topicForwardType_'+tid).attr("checked")) {
		$('#rcbtna_'+tid).attr("onclick", '');
		$("#rcbtna_" + tid).click(function(){
			Forward('forward_content_' + tid, tid , tid , 'topicForwardType_' + tid);
		});
	} else {
		$('#rcbtna_'+tid).attr("onclick", '');
		$("#rcbtna_" + tid).click(function(){
			Forward('forward_content_' + tid, tid , tid , 'topicForwardType_' + tid, options);
		});
	}
}

// 转发Tab js
function zfTab(name,cursel,n,key)
{
	var tid = key;
	for(i=1;i<=n;i++){
		var zfmenu=document.getElementById(name+i);
		var zfcon=document.getElementById("zfcon_"+name+"_"+i);
		zfmenu.className=i==cursel?"zfhover":"";
		// $("#rcbtna_"+key).attr('disabled', false);
		if (cursel == 2 && i==cursel) {
			// 重新绑定按钮事件
			$('#toqunwp_'+key).show();
			zfQun('qun_select_wp_'+key, key);
			$('#rcbtna_'+tid).attr("onclick", '');
			$('#rcbtna_' + tid).unbind("click");
			$("#rcbtna_" + tid).click(function(){
				Forward('forward_content_' + tid, tid , tid , 'topicForwardType_' + tid, {appitem:'qun', appitem_id:ComboBoxManager.get('qun_select_'+key).val()});
			});
		} else if (cursel == 1 && i==cursel){
			//$('#rcbtna_'+tid).attr("onclick", '');
			$('#rcbtna_' + tid).unbind("click");
			$("#rcbtna_" + tid).click(function(){
				Forward('forward_content_' + tid, tid , tid , 'topicForwardType_' + tid);
			});
			$('#toqunwp_'+key).hide();
		}
	}
}

// 转发获取我的群
function zfQun(wp ,key)
{
	var html = $("#"+wp).html();
	if (html == '') {
		$("#"+wp).html('微群正在加载...');	
	} else {
		return false;
	}
	$.get(
		'ajax.php?mod=qun&code=widgets&op=my_qun&type=1&key='+key+'&random='+Math.random(),
		{},
		function (d) {
			$("#"+wp).html(d);
			var cb = ComboBoxManager.create('qun_select_'+key);
			cb.setComboBoxWidth(200);
		}
	);
}

// 转发弹出框
function get_forward_choose(tid, allow_attach, options)
{
	var check_uid = $('#check_PublishBox_uid').val();
	var uid ='undefined'==typeof(check_uid)?'0':check_uid;	
	if(uid < 1){
		ShowLoginDialog();
		return false;
	}
	
	if (isUndefined(options)) {
		options = {};	
	}
	var tid = 'undefined' == typeof(tid) ? 0 : tid;
	if(tid < 1){
		show_message("抱歉，此微博已经被删除，无法进行转发哦，请试试其他内容吧。",3);
		return false;
	}
	var allow_attach = 'undefined' == typeof(allow_attach) ? 0 : allow_attach;
	
	var strOptions = "{}";
	var item = '';
	var itemid = 0;
	if ((options.appitem && options.appitem_id) || options.is_reward) {
		if('undefined' == typeof(options.is_reward)){
			options.is_reward = 0;
		}
		strOptions = "{appitem:'"+options.appitem+"',appitem_id:'"+options.appitem_id+"',is_reward:'"+options.is_reward+"'}";
		item = options.appitem;
		itemid = options.appitem_id;
	}

	// 可以隐藏转发并评论复选框 Modify by ~ZZ~ 2011-06-30
	var strForwarAndReply = '';
	if (!options.noReply) {
		strForwarAndReply = '<input name="topicForwardType_' + tid + '" type="checkbox" id="topicForwardType_' + tid + '" value="forward" onclick="select_checked(\'topicForwardType_' + tid + '\',\'forward\');"/> <label for="topicForwardType_' + tid + '" style="cursor:pointer;">同时作为评论发布</label>';
	} else {
		strForwarAndReply = '<input name="topicForwardType_' + tid + '" type="checkbox" id="topicForwardType_' + tid + '" value="forward" onclick="onForwardSelectedListener(' + tid + ',' + strOptions + ');"/> <label for="topicForwardType_' + tid + '" style="cursor:pointer;">转发到微博</label>';
	}
	
	var i_content = '转发微博';
	if(options.i_content){
		i_content = options.i_content;
	}
	
	var handle_key = "forward_menu_"+tid;
	var allow_attachHTML = '';
	if(allow_attach){
		allow_attachHTML = 'attachUploadifyHTML(' + tid + ', "forward_content_' + tid + '", "forward_attach_uploadify_' + tid + '",0,"' + item + '",' + itemid + ');';
	}
	var fcHTML = '<div id="forward_list" class="dialog_inner" style="width:400px;_overflow:hidden;"><div class="mWarp"> <form action="ajax.php?mod=topic&code=forward" method="POST" target="Forwardframe_' + tid + '"><table width="380"><tr><td colspan="2"><div id="toqunwp_' + tid + '" style="display:none;"><div style="float:left">选择微群：</div><div style="float:left" id="qun_select_wp_' + tid + '"></div></div></td></tr><tr> <td colspan="2"><span id="user_new"></span></td> </tr> <tr> <td colspan="2"><span id="topic_forward_content_area_' + tid + '">原文正在加载中……</span></td> </tr> <tr> <td colspan="2"><div class="alt_3"> <div class="fbqCount" style="margin:0 -40px 0 310px"><style>ul.mycon li{ width:65px;}</style> <ul class="mycon fontGreen" style="width:130px;margin-right:7px;color:#999;"> <li>还可以输入</li> <li id="wordc"><span id="wordcNum_' + tid + '" style="color:#ff0000;">' + thisTopicLength + '</span></li> <li style="width:14px;">字</li> </ul> </div>  </div> <div class="alt_3"> <textarea id="forward_content_' + tid + '" name="content" onkeyup="javascr' + 'ipt:checkWord(' + thisTopicLength + ',event,\'wordcNum_' + tid + '\')" class="textarea" onblur="if(this.value == \'\'){this.value = \'转发微博\'; }" onfocus="if(this.value == \'转发微博\'){this.value =\'\'; }" style="width:378px;margin-bottom:2px;">'+i_content+'</textarea> </div></td> </tr> <tr> <td class="modify_tool_left"><div class="menuf_bq" style="position:static;"><b class="menu_bqb_c"><a href="javascript:viod(0);" onclick="topic_face(\'forward_' + tid + '\',\'forward_content_' + tid + '\');return false;">表情</a></b><div class="forward_f" id="forward_' + tid + '" style="left:23px;top:282px;"></div></div><div id="forward_image_uploadify_'  + tid + '" class="comment_p_b2 comment_p_b22"></div><div id="forward_attach_uploadify_'  + tid + '" class="comment_p_b2 comment_p_b22"></div></td> <td class="modify_tool_right"><div class="rb_a1">'+strForwarAndReply+'<input id="rcbtna_' + tid + '" onclick="Forward(\'forward_content_' + tid + '\',' + tid + ',' + tid + ',\'topicForwardType_' + tid + '\','+strOptions+');return false;" type="button" value="转 发" class="sBtn_2 sBtn_2t" style="margin:0 0 0 5px"/> <input name="forward_tid" type="hidden" id="forward_tid" value="' + tid + '" /></div> </td> </tr> </table> </form> </div></div> <sc' + 'ript type="text/jav' + 'ascri' + 'pt"> $(document).ready(function(){ imageUploadifyHTML(' + tid + ', "forward_content_' + tid + '", "forward_image_uploadify_' + tid + '");' + allow_attachHTML + '$(".menu_bqb_c").click(function(){$("#forward_' + tid + '").show();}); $(".menu_bqb_c1").click(function(){$("#forward_' + tid + '").hide();}); initAiInput("forward_content_' + tid + '");}); $("#forward_content_' + tid + '").bind("keydown",function(event){ event = event || window.event; if(event.keyCode == 13 && event.ctrlKey) { $("#rcbtna_' + tid + '").click(); } }); $("#topic_forward_content_area_' + tid + '").html("<span class=\'loading\'>内容正在加载中，请稍候……</span>");var myAjax=$.post("ajax.php?mod=topic&code=forward_menu",{tid:' + tid + '},function(d){if(is_json(d)){var d_json=eval("("+d.toString()+")");show_message(d_json.msg,3);closeDialog("forward_menu_'+tid+'");}else{$("#topic_forward_content_area_' + tid + '").html(d);}});</scri' + 'pt>';
	
	var fcFrameHtml = '';
	if (!isQunClosed) {
		fcFrameHtml = '<div class="zfbox"><div class="zfTitle" id="zfTitle_'+tid+'"><ul class="zfti"><li id="zf_m1" class="zfhover" onclick="zfTab(\'zf_m\',1,2,' + tid + ')">转发到微博</li><li id="zf_m2" onclick="zfTab(\'zf_m\',2,2,' + tid + ')">转发到微群</li><sub class="menu_zf_c1" onclick="closeDialog(\''+handle_key+'\');"></sub></ul></div><div id="zfcon_zf_m_1" class="zfcon">'+fcHTML+'</div><div id="zfcon_zf_m_2" class="zfcon" style="display:none">转发到微群</div></div>';
	} else {
		fcFrameHtml = '<div class="zfbox"><div class="zfTitle" id="zfTitle_'+tid+'"><ul class="zfti"><li id="zf_m1" class="zfhover">转发到微博</li><sub class="menu_zf_c1" onclick="closeDialog(\''+handle_key+'\');"></sub></ul></div><div id="zfcon_zf_m_1" class="zfcon">'+fcHTML+'</div></div>';
	}

	// $("#"+"forward_menu_"+tid).css('display','block');
	// $("#"+"forward_menu_"+tid).html(fcHTML);
	var h = showDialog(handle_key, 'local', '转发给我的粉丝', {"html":fcFrameHtml, "noTitleBar":true}, 440);
	// 标题栏拖动绑定
	draggable(h.dom.wrapper, $('#zfTitle_'+tid));
}

var authort;
// 悬浮头像显示用户
function get_user_choose(uid,types,tid)
{// alert(uid);return false;
	clear_user_choose();
	var div_id = tid ? tid : uid;
	var _cache_id = uid;

		authort = setTimeout(function () {
		var myAjax = $.post(
			"ajax.php?mod=topic&code=usermenu",{
				uid:uid
			},
			function (d) {
				if(''!=d) {
					Cache.save(_cache_id,d);
					if(types == "media"){
						$("#"+"media_"+div_id).html(Cache.get(_cache_id));
					}
					else{
						$("#"+"user_"+div_id+types).html(Cache.get(_cache_id));
					}
				}
			}
		);
		if(types == "media"){
			$("#"+"media_"+div_id).html(Cache.get(_cache_id));
		}
		else{
			$("#"+"user_"+div_id+types).html(Cache.get(_cache_id));
		}
	}, 500);
}

// 鼠标定位 获取坐标
function mousePosition(ev){ 
	
	if(ev.pageX || ev.pageY){ 
	return {x:ev.pageX, y:ev.pageY}; 
	} 
	return { 
	x:ev.clientX + document.body.scrollLeft - document.body.clientLeft, 
	y:ev.clientY + document.body.scrollTop - document.body.clientTop 
	}; 
} 

// user card loading flag
var topic_user_face_is_loading = null;
/*
 * 微博内容 中悬浮用户昵称显示名片框
 * nickname 用户昵称
 */
//modify 2012-07-09 16:58
//modify 2012-07-31 11:32 （1.添加查询字段类型[uid|nickname] 2.添加loading效果）
function get_at_user_choose(nickname,obj,queryType){
	var id = "topicuserface";
	if($('#'+id).length==0){
		$(document.body).append("<div id='"+id+"' />");
	}
	else
	{
		$('#'+id).html('');
	}
	var _cache_id = nickname;
	var position = $(obj).offset();
	$('#'+id).css({position:"absolute",'padding-top':'10px','z-index':99,left:position.left-10,top:position.top+25});
	if ($.browser.msie && $.browser.version<8){
		$('#'+id).css({left:position.left+40});
	}
	// timer to load
	authort = setTimeout(function () {
		// loading status pre display
		$('#'+id).html("<div class='arrow'></div><div id='topic_user_menu_x' class='media_user_list'><ul class='tipsBox'><li style='padding-left:21px;'><img src='"+thisSiteURL+"images/loading.gif' width='16' height='16' /></li></ul></div>");
		topic_user_face_is_loading = true;
		// card data dsp
		if(Cache.get(_cache_id)!=null){
			$('#'+id).html(Cache.get(_cache_id));
			topic_user_face_is_loading = false;
		}else{
			// nedu-query-type-redeclare
			var myPost = {};
			myPost['arrow'] = 'yes';
			if (queryType)
			{
				myPost[queryType] = nickname;
			}
			else
			{
				myPost['nickname'] = nickname;
			}
			// over
			var myAjax = $.post(
			"ajax.php?mod=topic&code=usermenu",myPost,
			function (d) {
				if(''!=d) {
					Cache.save(_cache_id,d);										 
					$('#'+id).html(Cache.get(_cache_id));
					topic_user_face_is_loading = false;
				}
			}
			);
		}
	},200);
	obj.onmouseout=function(){ 
		clear_user_choose();
	};
} 

/*
 * 微博内容 中悬浮用户昵称显示名片框
 * 
 * nickname 用户昵称 uid 用户uid tid 微博tid ev 鼠标悬浮用户昵称 坐标， 定位名片框显示
 * 
 * types 名片框显示div 标识
 */
/*以前的，因为有BUG（在转发的微博中显示原微博里的@对象时定位不正确）
function get_at_user_choose(nickname,types,tid,ev)
{   
	// 鼠标定位 获取坐标
	var mousePos = mousePosition(ev); 
	var mouseX = mousePos.x; 
	var mouseY = mousePos.y; 
	
	
	clear_user_choose();
	var div_id =  tid;
	var _cache_id = tid;
		authort = setTimeout(function () {
		var myAjax = $.post(
			"ajax.php?mod=topic&code=usermenu",{
				nickname:nickname
			},
			function (d) {
				if(''!=d) {
					Cache.save(_cache_id,d);
					
					 document.getElementById("at_"+div_id+types).style.scrollTop 	=  mouseY+'px';
					 document.getElementById("at_"+div_id+types).style.left =  mouseX+'px';
					 
					 document.getElementById("at_"+div_id+types).style.display = "block";
					 
					 $("#"+"at_"+div_id+types).html(Cache.get(_cache_id));
			}
			}
		);
			$("#"+"at_"+div_id+types).html(Cache.get(_cache_id));
		
	}, 500);
}
*/

// 删除已上传的视频
function DelVideo(videoid,types)
{   
	
	var videoid = $('#'+videoid).val();
	var videoid = 'undefined' == typeof(videoid) ? 0 : videoid;
	// alert(videoid);return false;
	if(videoid > 0) {
		$.post(
			'ajax.php?mod=topic&code=delete_video',
			{
				id:videoid
			},
			function (d) {				
				if(''!=d) {
					;		
				} else {
					// 通过ajax上传视频后 删除视频清除输入框内的内容和缩略图
					if(types == 'video_ajax')
					{	
						parent.document.getElementById('upload_video_list').style.display='none';
						parent.document.getElementById('add_video').style.display='block';
						parent.document.getElementById('i_already').value='';
					}
				}
			}
		);
		videoid = 0;
	}
	listTopic(0,0);
}

function clear_user_choose()
{
	clearTimeout(authort);
	// clear loading status
	if (topic_user_face_is_loading)
	{
		var id = "topicuserface";
		$('#'+id).html('');
	}
}

/**
 * 通过JS消息提示，在显示设定时间后隐藏
 * 
 * @param show_message
 *            提示的消息内容 如 “发布成功”；默认为空
 * @param show_time
 *            消息显示的时间，单位为秒；默认显示 “3” 秒
 * @param show_title
 *            提示的消息标题，默认为“提示”
 * @param tigBoxClass
 *            消息提示层所使用的样式，默认为“tigBox_6”
 */
function show_message(show_message,show_time,show_title,tigBoxClass)
{
	var show_message = (undefined==show_message ? '' : show_message);

	if(show_message)
	{
		var show_time = (undefined==show_time ? 1 : show_time);
		var show_title = (undefined==show_title ? '提示' : show_title);
		var tigBoxClass = (undefined==tigBoxClass ? 'tigBox_6' : tigBoxClass);

		var smaHTML = '<div id="tigBox" class="' + tigBoxClass + '"><ul class="warnBox"><li><div class="tt1">' + show_title + '</div><div class="wWarp"><div class="wwsp">' + show_message + '</div></div></li></ul></div>';
		$('#show_message_area').html(smaHTML);

		var tigBoxObj = document.getElementById("tigBox");

		tigBoxObj.style.visibility = "visible";
		
		var i=0;

		setTimeout(function() {
			i += 1;
			tigBoxObj.style.visibility= "hidden";
		},(show_time * 1000));
	}
}

//包含确定框
function show_message_2(show_message,show_title,tigBoxClass)
{
	var show_message = (undefined==show_message ? '' : show_message);

	if(show_message)
	{
		var show_title = (undefined==show_title ? '提示' : show_title);
		var tigBoxClass = (undefined==tigBoxClass ? 'tigBox_6' : tigBoxClass);

		var smaHTML = '<div id="tigBox" class="' + tigBoxClass + '">'+
						'<ul class="warnBox"><li><div class="tt1">' + show_title + '</div><div class="wWarp"><div class="wwsp">' + show_message + '</div></div>'+
						'<div id="qr" class="bt_qr">确认</div></li></ul></div>';
		$('#show_message_area').html(smaHTML);

		var tigBoxObj = document.getElementById("tigBox");

		tigBoxObj.style.visibility = "visible";
		$('#qr').click(function(){tigBoxObj.style.visibility= "hidden";});
		//var i=0;

	   // setTimeout(function() {
		//    i += 1;
	   //     tigBoxObj.style.visibility= "hidden";
		//},(show_time * 1000));
	}
}

// 对话、拉黑弹出操作框
function follower_choose(uid,nickname,types,template)
{
	var uid = 'undefined' == typeof(uid) ? 0 : uid;
	var template = 'undefined' == typeof(template) ? '' : template;
	var handle_key = "alert_follower_menu_"+uid;
	var ajax_url = 'ajax.php?mod=topic&code=follower_choose';
	var post = {
					uid:uid,
					nickname:nickname,
					types:types,
					template:template
					
				};
	var title = "正在载入..."; 
	if (types == 'lahei') {
		showDialog(handle_key, 'ajax', title, {"url":ajax_url, "post":post}, 400);
	} else if (types == 'del') {
		title = "取消拉黑？";
		showDialog(handle_key, 'ajax', title, {"url":ajax_url, "post":post}, 400);
	} else if (types == 'at') {
		showDialog(handle_key, 'ajax', title, {"url":ajax_url, "post":post}, 400);
	} else if (types == 'buddys') {
		showDialog(handle_key, 'ajax', title, {"url":ajax_url, "post":post}, 400);
	} else if (types == 'topic_signature') {
		
		// 微博页面修改个人签名
		showDialog(handle_key, 'ajax', title, {"url":ajax_url, "post":post}, 400);	
	} else if (types == 'editarea') {
		title = "编辑注册地址";
		showDialog(handle_key, 'ajax', title, {"url":ajax_url, "post":post}, 400);
	} else if (types == 'del_fans') {
		title = "移除粉丝";
		showDialog(handle_key, 'ajax', title, {"url":ajax_url, "post":post}, 400);
	} else {

		var myAjax=$.post(
		"ajax.php?mod=topic&code=follower_choose",
			{
				uid:uid,
				nickname:nickname,
				types:types,
				template:template,
				return_type:return_type
			},
			function(d)
			{
				if(''!=d){
					document.getElementById("alert_follower_menu_"+uid).style.display="block";
					$("#"+"alert_follower_menu_"+uid).html(d);
					if('user_face' == template){
						document.getElementById("topic_user_menu_"+uid).style.display="none";
					}
				}
				
			}
			
		);
	}
		
}



/*
 * uid = 当前操作者UID touid = 被操作者UID tyoes = 取消拉黑 黑是 加入黑名单
 * 
 * template = ajax 值 返回模板
 */
function do_blacklist(uid,touid,types,template)
{ 
	var uid = 'undefined' == typeof(uid) ? 0 : uid;
	var touid = 'undefined' == typeof(touid) ? 0 : touid;
	var types = 'undefined' == typeof(types) ? '' : types;

	
	// alert(template); return false;
	
	var myAjax = $.post(
		"ajax.php?mod=topic&code=doblacklist",
		{
			uid:uid,
			touid:touid,
			types:types,
			template:template
		},
		function (d) {
			if(''!=d) {				
				closeDialog("alert_follower_menu_"+touid);
				
				if('add'==types){
					$('#topic_index_blacklist_'+touid).html(d);
				}
				else {
					$('#topic_index_blacklist_'+touid).html(d);
				}

				$("#follow_user_"+touid).remove();
				// listTopic(0,0);
				// document.getElementById("alert_follower_menu_"+touid).style.display="none";
			}
		}
	);
}

// 设置个性标签
function user_tag(tagid,tag_name,types)
{

	var tagid = 'undefined' == typeof(tagid) ? 0 : tagid;

  if('add' == types)
  {
		var tag_name = $('#'+tag_name).val();
		var tag_name = 'undefined' == typeof(tag_name) ? '' : tag_name;
	}

	var myAjax = $.post(
		"ajax.php?mod=topic&code=user_tag",
		{
			tagid:tagid,
			tag_name:tag_name,
			types:types
		},
		function (d) {
			if(''!=d) {
				// show_message('标签设置成功',1);
				document.getElementById("tags_name").value="";
				$("#user_tag_list").html( $("#user_tag_list").html()+d);
			}
			$("#del_tag_"+tagid).remove();
		}
	);
}
// 删除个性标签
function del_tag(tag_id)
{
	var tag_id = 'undefined' == typeof(tag_id) ? 0 : tag_id;

	var myAjax = $.post(
		"ajax.php?mod=topic&code=del_tag",
		{
			tag_id:tag_id
		},
		function (d) {
			if(''!=d) {
				$("#del_id_"+tag_id).remove();
			}
		}
	);
}

function get_tag_insert(tag_name){
	var tag_value;
	tag_value=document.getElementById('i_already').value;
	tag_value=tag_value+'#'+tag_name+'#';
	document.getElementById('i_already').focus();
	document.getElementById('i_already').value=tag_value;
}
function tag_insert(tagName){
	var tag_value;
	tag_value=document.getElementById('i_already').value;
	tag_value=tag_value+'#'+tagName+'#';
	document.getElementById('i_already').focus();
	document.getElementById('i_already').value=tag_value;
}
function nickname_insert(at_nickname){
	var tag_value;
	tag_value=document.getElementById('i_already').value;
	tag_value=tag_value+'@'+at_nickname+' ';
	document.getElementById('i_already').focus();
	document.getElementById('i_already').value=tag_value;
}
// 插入自定义话题
function thread_insert(tagname, symbol){
	
	var document_id = document.getElementById('i_alreadyajax') ? document.getElementById('i_alreadyajax') : document.getElementById('i_already');
	
	var con = tagname ? tagname : "插入自定义话题";
	
	if (isUndefined(symbol)) {
		symbol = '#';
	}
	
	// 转载文字
	document_id.value += symbol+con+symbol;
	var l = document_id.value.length;
	// 创建选择区域
	if(document_id.createTextRange){// IE浏览器
		var range = document_id.createTextRange();
		range.moveEnd("character",-l);         
		// range.moveStart("character",-l)
		range.moveEnd("character",l-1);
		range.moveStart("character", l-1-con.length);
		range.select();
	}else{
		document_id.setSelectionRange(l-1-con.length,l-1);
		document_id.focus();
	}

}


// 判断同时微博 同时评论 选择状态
function select_checked(topicTypeId,topicTypeVal) {
	var topicTypeId = ('undefined'==typeof(topicTypeId) ? 'topictype' : topicTypeId);
	if($("#" + topicTypeId).attr("checked")) {
		$("#" + topicTypeId).val('both');
	} else {
		var topicTypeVal = ('undefined'==typeof(topicTypeVal) ? 'reply' : topicTypeVal);
		if('reply' != topicTypeVal) {
			topicTypeVal = 'forward';
		}
		$("#" + topicTypeId).val(topicTypeVal);
	}
}

// 关闭用户弹出框
function close_media_menu(uid){
	document.getElementById("media_user_list_"+uid).style.display="none";
	document.getElementById("media_"+uid).style.display="none";
}

// 关闭弹出框
function close_menu(div_id,close_div){
	$("#"+close_div+"_"+div_id).html("");
	document.getElementById(close_div+"_"+div_id).style.display="none";
}

// 定时刷新
function ajax_reminded(r_uid,is_uptime)
{
	var ajax_remindedHTML = $("#ajax_reminded").html();
	ajax_remindedHTML = ('undefined' == typeof(ajax_remindedHTML) ? '' : ajax_remindedHTML);

	/*
	 * 已经存在了提醒内容时就不再请求？可以提高性能，防止空刷新；
	 */
	if(''!=ajax_remindedHTML)
	{
// return false;
	}

	var r_uid = 'undefined' == typeof(r_uid) ? 0 : r_uid;
	var is_uptime = 'undefined' == typeof(is_uptime) ? 0 : is_uptime;

	var myAjax = $.post(
		"ajax.php?mod=reminded&code=show",
		{
			uid:r_uid,
			is_uptime:is_uptime
		},
		function (d) 
		{
			if( '' != d && -1 != d.indexOf('<success></success>') ) 
			{
				d = d.trim();
				if (d != '<success></success>') {
					$("#ajax_reminded").html(d);
				}
			}
		}
	);
}

// 发送站内消息
function PmSend(touid,to_user,eid,pmid)
{
	if ($('#message').length > 0) {
		$("html,body").animate({scrollTop: $("#message").offset().top-40}, 1000, 'swing', function(){$('#message').focus();});
		return false;
	}
	var eid='undefined'==typeof(eid)?'Pmsend_to_user_area':eid;
	var to_user='undefined'==typeof(to_user)?'':to_user;
	var handle_key = eid;
	var ajax_url = "ajax.php?mod=pm&code=send";
	var title = "给‘"+to_user+"’发私信";
	var post = {"to_user":to_user,
				 touid : touid,
				 pmid : pmid};
	showDialog(handle_key, 'ajax', title, {"url":ajax_url, "post":post}, 400);
}

function PmSubmit(i,eid,to_user,topmid, options)
{
	var i='undefined'==typeof(i)?0:i;
	if (isUndefined(options)) {
		options = {};
	}
	
	var sysTip = false;
	if (options.sysTip) {
		sysTip = true;
	}
	
	var message=document.getElementById("message").value;
	var pmid = $('#pmid').val();

	if(''==message) {
		if (sysTip) {
			alert('请输入私信内容');
		} else {
			show_message('请输入私信内容',1);
		}
		return false;
	}

	// $("#"+eid).remove();
	if (!closeDialog(eid)) {
		$("#"+eid).remove();
	}
	var myAjax=$.post("ajax.php?mod=pm&code=do_add",
	{
		pmid:i,
		message:message,
		to_user:to_user,
		topmid:topmid,
		pmid:pmid
	},

		function(d){
			if(''!=d)
			{
				show_message(d);return false;
			}else{
				if(pmid){
					show_message('私信发送成功',1);
					closeDialog('sendagain');
					$('#outbox_'+pmid).remove();
				}else if (sysTip) {
					alert('私信发送成功');
				} else {
					show_message('私信发送成功',1);
				}
				if (options.success) {
					options.success.call();
				}
			}
		}
	);
}

// 返回一个UNIX时间戳，和PHP一样的
function time()
{
	var _dateObj=new Date();
	var _time=_dateObj.getTime().toString();
	return parseInt(_time.substring(0,_time.length-3));
}
// JS缓存类，用于缓存AJAX返回结果非常有用
function CacheHandler()
{
	this.data={};
	this.lifeTime=3600;// 默认缓存一小时
	this.setLifeTime=function(lifeTime)
	{
		this.lifeTime=lifeTime;
	};
	this.save=function (name,value,lifeTime)
	{
		this.data[name]={expire:time()+(parseInt(lifeTime) || this.lifeTime),value:value};
		return this.data[name]['value'];
	};
	this.get=function (name)
	{
		if(this.data[name]==undefined || this.data[name]['expire']<time())return undefined;
		return this.data[name]['value'];
	};
	this.getOrSave=function(name,value,lifeTime)
	{
		return this.get(name)==undefined && this.save(name,value,lifeTime);
	};
	this.clear=function (name)
	{
		name?delete this.data[name]:this.data={};
	};
}
var Cache=new CacheHandler();// 实例化个全局缓存对象

//
function headDoSearch()
{
	var searchValue=$('#headq').val();
	var searchType=$('#headSearchType').val();
	var redirectURL='';
	if(''==searchValue||'undefined'==searchValue||'请输入关键字'==searchValue)
	{
		alert("请输入关键字");
	}
	else
	{
		searchValue = encodeURIComponent(searchValue);
		
		if('userSearch'==searchType){
			redirectURL='index.php?mod=search&code=user&nickname='+searchValue;
		}else if('tagSearch'==searchType){
			redirectURL='index.php?mod=search&code=tag&tag='+searchValue;
		}else if('topicSearch'==searchType){
			redirectURL='index.php?mod=search&code=topic&topic='+searchValue;
		}else if('voteSearch'==searchType){
			// 加入对投票的search By ~ZZ~ 2010-04-22
			redirectURL='index.php?mod=search&code=vote&q='+searchValue;
		}else if('qunSearch'==searchType){
			// 加入对微群的search By ~ZZ~ 2010-08-23
			redirectURL='index.php?mod=search&code=qun&q='+searchValue;
		}else{
			alert("未定义的操作");
		}

		if(''!=redirectURL)
		{
			window.location.href=thisSiteURL+redirectURL;
		}
	}
	return false;
}

function ProfileSearch()
{
	var searchValue=$('#keywarod').val();
	var searchType=$('#ProfileSearchType').val();
	var searchGetType=$('#type').val();
	
	/* 我关注的 我的粉丝 搜索时用到 */
	var userNameValue=$('#userName').val();

	var redirectURL='';
	if(''==searchValue||'undefined'==searchValue||'请输入关键字'==searchValue)
	{
		alert("请输入关键字");
	}
	else
	{
		if('user'==searchType){
			redirectURL='index.php?mod=search&code=user&nickname='+searchValue;
		}else if('usertag'==searchType){
			redirectURL='index.php?mod=search&code=usertag&usertag='+searchValue;
		}else if('topic'==searchType){
			redirectURL='index.php?mod=search&code=topic&topic='+searchValue;
		}else if('tag'==searchType){
			redirectURL='index.php?mod=search&code=tag&tag='+searchValue;
		}else if('vote'==searchType){
			// 加入对投票的search By ~ZZ~ 2010-04-22
			redirectURL='index.php?mod=search&code=vote&q='+searchValue;
		}else if('fansSearch' == searchType){
			/* 粉丝搜索 zx */
			redirectURL='index.php?mod='+userNameValue+'&code=fans&nickname='+searchValue;
		}else if('followSearch' == searchType){
			/* 关注搜索 zx */
			redirectURL='index.php?mod='+userNameValue+'&code=follow&nickname='+searchValue;
		}else if('qun'==searchType){
			redirectURL='index.php?mod=search&code=qun&q='+searchValue;
		}else{
			alert("未定义的操作");
		}

		if(''!=redirectURL)
		{
			window.location.href=thisSiteURL+redirectURL;
		}
	}
	return false;
}

// 表情
function face_insert(facename,insert){
	// alert(facename);return false;
	var values;
	values=document.getElementById(insert).value;
	values=values + '['+facename+']';
	document.getElementById(insert).value=values;
}
function topic_face(eid,insert,getname)
{  
	// insert = 将表情插入的发布框ID getname = 引用的表情文件

	var getname = 'undefined' == typeof(getname) ? 'topic_face' : getname;
		
	if(getname == 'tusiji_face')
	{ 	$("#"+eid).addClass("tusiji_face");
		if($("#"+eid).hasClass("topic_face")){
			$("#"+eid).removeClass("topic_face");
		  }else{
			  $("#"+eid).addClass("tusiji_face");
		  }			
	}else if(getname == 'topic_face'){
		
		if($("#"+eid).hasClass("tusiji_face")){
			$("#"+eid).removeClass("tusiji_face");
		  }else{
			  $("#"+eid).addClass("topic_face");
		  }
	}

	var tfHTML = topicFaceHTML(eid, insert, getname);
	
	$("#" + eid).html(tfHTML);		
}
// 组建表情的HTML代码（不再从AJAX页面读取） 2011年6月21日 by foxis
function topicFaceHTML(eid, insert, getname)
{
	var __TOPIC_FACE_CONFIG__ = 
	{
		'topic_face' : ['微笑','撇嘴','色','发呆','得意','流泪','害羞','闭嘴','睡','大哭','尴尬','发怒','调皮','呲牙','惊讶','难过','酷','冷汗','抓狂','吐','偷笑','可爱','白眼','傲慢','饥饿','困','惊恐','流汗','憨笑','大兵','奋斗','咒骂','疑问','嘘','晕','折磨','衰','骷髅','敲打','再见','擦汗','抠鼻','鼓掌','糗大了','坏笑','左哼哼','右哼哼','哈欠','鄙视','委屈','快哭了','阴险','亲亲','吓','可怜','菜刀','西瓜','啤酒','篮球','乒乓','咖啡','饭','猪头','玫瑰','凋谢','示爱','爱心','心碎','蛋糕','闪电','炸弹','刀','足球','瓢虫','便便','月亮','太阳','礼物','拥抱','强','弱','握手','胜利','抱拳','勾引','拳头','差劲','给力','NO','OK','干杯','飞吻','跳跳','发抖','怄火','转圈','磕头','回头','跳绳','挥手','激动','街舞','献吻','左太极','右太极'],
		'tusiji_face' : ['醒醒','昏迷','耶','怒吼','扭背','顶','抖胸','拜拜','挥汗','无聊','鲁拉','拍砖','揉脸','生日','摊手','洗洗睡','瘫坐','哼','闪闪放','旋转','不行','郁闷','音乐','抓墙','撞墙','歪头','戳眼','飘过','互拍','扎','暗爽','少林寺','我得意','砖头','奶瓶','我踢','摇晃','晕厥','笼子','震荡']
	};
	if($(__TOPIC_FACE_CONFIG__[getname]).length < 1)
	{
		getname = 'topic_face';
	}
	var tfs = __TOPIC_FACE_CONFIG__[getname];
	var tfHTML = '<sc' + 'ript type="text/javascri' + 'pt"> $(document).ready(function(){ $(".showfaceBb").click(function(){$(this).parents("#' + eid + '").hide();}); $(".showfaceBb").click(function(){$(this).parents(".forward_f2").hide();}); $(".showfaceBb").click(function(){$(this).parents(".forward_f").hide();}); $(".menu_bqb_c1").click(function(){$("#' + eid + '").hide();}); $(".menu_bqb_c1").click(function(){$(this).parents("#' + eid + '").hide();}); $(".menu_bqb_c1").click(function(){$(this).parents(".forward_f2").hide();}); $(".menu_bqb_c1").click(function(){$(this).parents(".forward_f").hide();}); }); </s' + 'cript> <div class="menu_bqb_cb"> <div style="float:left; width:200px;"> <a href="javascript:void(0);" class="bq_select_1" onclick="topic_face(\'' + eid + '\',\'' + insert + '\',\'topic_face\');return false;">QQ表情</a> <a href="javascript:void(0);" class="bq_select_2" onclick="topic_face(\'' + eid + '\',\'' + insert + '\',\'tusiji_face\');return false;">兔斯基</a> </div> <div class="menu_bqb_c1"></div> </div> <div class="faceBG">';	
		
	$(tfs).each(function (i) {
		r = tfs[i];
		tfHTML = tfHTML + '<span class="spanFs"><img src="' + thisSiteURL + '/templates/default/images/face_bb.gif" class="showfaceBb" title="' + r + '" onclick="face_insert(\'' + r + '\',\'' + insert + '\'); return false;" /></span>';		
	});
	
	tfHTML = tfHTML + '</div>';
	
	return tfHTML;
}


// 短信 聊天记录
function pmListChat(msgfromid)
{
	var i='undefined'==typeof(msgfromid)?0:msgfromid;
	var eid='undefined'==typeof(eid)?'Pmreply_area_'+i:eid;

	var eidVal=$("#"+eid).html();

	if(''==eidVal)
	{
		var myAjax=$.post
		(
			"ajax.php?mod=pm&code=listchat",
			{
				msgfromid:i
			},
			function(d)
			{
				if(''!=d)
				{
					$("#chat_list_"+i).html(d);
				}
			}
		);
	}
	else
	{
		$("#chat_list_"+i).html('');
	}
}

// 发布框等字数统计
function checkWord(len,evt,wordCheckNumID)
{
	var len = ('undefined'==typeof(len) ? 0 : len);
	if(len < 1) {
		return true;
	}
	
   if(evt==null) 
   evt = window.event; 
   if(typeof evt == 'string'){
	   var src = $('#'+evt).val();
	   var src = ('undefined' == typeof(src) ? '' : src);
	   var str = src.trim();
   }else{
	   var src = evt.srcElement? evt.srcElement : evt.target;
	   var str = src.value.trim();
   }

   myLen =0;
   i=0;  
   for(;(i<str.length)&&(myLen<=len*2);i++)
   {
	   if(str.charCodeAt(i)>0&&str.charCodeAt(i)<128)
		   myLen++;
	   else
		   myLen+=2;
   }   

   if(myLen>len*2)
   {
	   src.value=str.substring(0,i-1);
	   /*
		 * if(confirm('字数已经超过了限定的长度，点击确定开启发布长文功能（长文没有字数长度的限制）')) {
		 * initKindEditor(src.id); } else { src.value=str.substring(0,i-1); }
		 */
   }
   else{
	   $('#'+wordCheckNumID).html(Math.floor((len*2-myLen)/2));
	   // document.getElementById(wordCheckNumID).innerHTML =
		// Math.floor((len*2-myLen)/2);
   }
}

String.prototype.trim = function() 
{ 
	return this.replace(/(^\s*)|(\s*$)/g, ""); 
};

/**
 * 检查发布框字符串
 */
function checkPublishText(len, txt_id, tips_id)
{ 
   var src = null;
   src = $('#'+txt_id);
   var str = src.val().trim();
   var myLen = 0;
   var i = 0;
   for(;(i<str.length)&&(myLen<=len*2);i++){
	   if(str.charCodeAt(i)>0&&str.charCodeAt(i)<128) {
			myLen++;
	   } else {
			myLen+=2;
	   }
   }
   
   if(myLen>len*2){
		src.val(str.substring(0,i-1));
   } else if (!isUndefined(tips_id)){ 
		$('#'+tips_id).html(Math.floor((len*2-myLen)/2));
   }
}

/**
 * 检查字符串是否是json代码
 */
function is_json(data)
{
	var reg = new RegExp(/^{.*}$/igm);
	if (data.search(reg) != -1) {
		return true;
	}
	return false;
}

/**
 * 对话框
 * 
 * @param handle_key
 *            对话框的唯一标识，确保它的唯一性
 * @param module
 *            对话框的模式。 module='ajax'
 *            :需要设定options={url:'xxxx'},如果加入了post参数则使用post方式请求 module='local'
 *            :需要设定options={html:'xxxx'},对话框内直接显示options.html的值或者使用option.id对话框内会直接显示id中html内容
 *            module='message' :需要设定options中的type， 告警对话框type='warning'
 *            :需要设定options={type:'warning',button_name:'确定',text:'你没有权限进行当前操作',onclick:''}
 *            确认对话框type='confirm'
 *            :需要设定options={type:'warning',yes_button_name:'确定',no_button_name:'取消',
 *            text:'你确定要进行这项操作吗?'，onclick:''} module='loading'
 *            :需要设定options={text:'正在加载'}
 * @param width
 *            对话框宽。
 */
var __DialogHtml__ = new Array();
function showDialog(handle_key, module, title, options, width)
{
	if (!width) {
		width = 400;
	}
	Dialog.prototype.noTitleBar = !options.noTitleBar ? false : true;
	var handle = DialogManager.create(handle_key);
	if (!options.noTitleBar) {
		handle.setTitle(title);
	}
	if (module == 'local') {
		var html = '';
		if (isUndefined(__DialogHtml__[handle_key])) {
			if (options.html) {
				html = options.html;
			} else if (options.id) {
				html = $('#'+ options.id).html();
				$('#'+ options.id).html('');
			} else {
				html = '';
			}
			__DialogHtml__[handle_key] = html;
		} else {
			html = __DialogHtml__[handle_key];
		}
		handle.setContents(html);
	} else if (module == 'ajax') {
		// 暂时这样写，可以以后会else里面不一样的
		// 加上默认的错误处理
		if (!options.checkerror) {
			options.checkerror = function (data) {
				if (is_json(data)) {
					var json = eval('('+data.toString()+')');
					closeDialog(handle_key);
					MessageBox('warning', json.msg);
					return false;
				}
				return true;
			};
		}
		handle.setContents(module, options);
	} else {
		handle.setContents(module, options);
	}    	
	handle.setWidth(width);
	handle.show('center');
	return handle;
}

/**
 * 动态设置对话框的标题
 */
function setDialogTitle(handle_key, title)
{
	DialogManager.setTitle(handle_key, title);
}

/**
 * 关闭对话框
 */
function closeDialog(handle_key)
{
	return DialogManager.close(handle_key);
}

/**
 * 设置对话框关闭事件的侦听
 */
function setDialogOnCloseListener(handle_key, func, options)
{
	__DIALOG_WRAPPER__[handle_key].onClose = function() {
		if (options) {
			func(options);
		} else {
			func();
		}
		Dialog.prototype.onClose = function() {return true;};
		return true;
	};
}

/**
 * 消息提示框
 */
function MessageBox(type, msg, title, options)
{
	if (isUndefined(options)) {
		options = {};
	}
	
	if (type == 'notice') {
		handle_key = 'notice_dialog';
		clickEvent = null;
		close_first = false;
		if (options.onclick) {
			clickEvent = options.onclick;
		}
		if (options.close_first) {
			close_first = options.close_first;
		}
		param = {type:'notice',button_name:'确定',text:msg, onclick:clickEvent, close_first:close_first};
	} else if (type == 'warning') {
		handle_key = 'warning_dialog';
		clickEvent = null;
		close_first = false;
		if (options.onclick) {
			clickEvent = options.onclick;
		}
		if (options.close_first) {
			close_first = options.close_first;
		}
		param = {type:'warning',button_name:'确定',text:msg, onclick:clickEvent, close_first:close_first};
	} else if (type == 'confirm') {
		handle_key = 'confirm_dialog';
		var onClickYes = null;
		var onClickNo = null;
		if (options.onClickYes) {
			onClickYes = options.onClickYes;
		}
		if (options.onClickNo) {
			onClickNo = options.onClickNo;
		}		
		param = {type:'confirm',yes_button_name:'确定',no_button_name:'取消',text:msg,onClickYes:onClickYes,onClickNo:onClickNo};
	}
	if (!title || title == '') {
		title = '提示';
	}
	showDialog(handle_key, 'message', title, param, 300);
}

/**
 * 显示主发布对话框
 */
function showMainPublishBox(type,item,itemid,totid,touid)
{	
	// check_PublishBox_uid = 验证用户ID的隐藏域
	var check_uid = $('#check_PublishBox_uid').val();
	var uid ='undefined'==typeof(check_uid)?'0':check_uid;	
	if(uid < 1){
		ShowLoginDialog();
		return false;
	}
	if (isUndefined(type)) {
		var type = '';
	}
	if (isUndefined(item)) {
		var item = '';
	}
	if (isUndefined(itemid)) {
		var itemid = 0;
	}
	if (isUndefined(totid)) {
		var totid = 0;
	}
	if (isUndefined(touid)) {
		var touid = 0;
	}
	if(type == 'btn_dzwc'){
		MessageBox('notice','您已经定制了，请不要重复操作！', '提示');
	}else if(type == 'btn_wydz'){
		$.post("ajax.php?mod=item&code=sms",{uid:uid,item:item,itemid:itemid}, function(d){
			if(d == 1){
				if(item == 'live'){
					MessageBox('notice', "定制成功，直播前5分钟会通知您！", '提示');
					$("#btn_css").removeClass("btn_wydz");$("#btn_css").addClass("btn_dzwc");
				}else if(item == 'talk'){
					MessageBox('notice', "定制成功，访谈前5分钟会通知您！", '提示');
					$("#makeNotice").html("定制成功");
				}
			}else if(d == -1){
				MessageBox('notice','您已经定制了，请不要重复操作！', '提示');
			}else{
				MessageBox('notice','定制失败，未知错误！', '提示');
			}
		});
	}else if (type == 'btn_wyfx' || type == 'design' || type =='ask' || type == 'answer'){
		var handle_key = "item_main_pb_dialog";
		if(type == 'ask'){
			var htitle = '提问';
		}else if(type == 'answer'){
			var htitle = '答复';
		}else{
			var htitle = '推荐';
		}
		showDialog(handle_key, 'ajax', htitle, {url:"ajax.php?mod=item&code=publishbox&type="+type+"&item="+item+"&itemid="+itemid+"&totid="+totid+"&touid="+touid+"&random="+Math.random()}, 590);
	}else{
		// 如果页面存在了微博发布框则不显示弹出框
		if ($('#i_already').length > 0 && $('#wordCheck').length>0) {
			$("html,body").animate({scrollTop: $("#zz_main_publish").offset().top-40}, 1000, 'swing', function(){$('#i_already').focus();});
		} else {
			var handle_key = "hk_main_pb_dialog";
			showDialog(handle_key, 'ajax', '随时随处发微博', {url:"ajax.php?mod=misc&code=publishbox&type="+type+"&random="+Math.random()}, 590);
		}
	}
}

/**
 * 显示举报对话框
 */
function ReportBox(tid)
{
	var handle_key = "hk_report_dialog";
	showDialog(handle_key, 'ajax', '举报不良信息', {url:"ajax.php?mod=misc&code=report&tid="+tid}, 400);
}

/**
 * 应用中的微博发布
 */
function publishSimpleTopic(content, appitem, appitem_id, options)
{
	if (!options) {
		options = {};
	}
	
	if (isUndefined(content) || content == '') {
		MessageBox('warning', '请输入微博');
		return false;
	}
	
	if (lastPublishSubmitContent == content) {
		// MessageBox('warning', '不要贪心哦，发一次就够啦。');
		// return false;
	} else {
		lastPublishSubmitContent = content;
	}
	
	var topictype = '';
	if (options.topic_type) {
		topictype = options.topic_type;
	} else {
		topictype = 'first';
	}
	
	var from = "web";
	if (options.from) {
		from = options.from;
	} else {
		from = appitem;
	}

	// 多图
	var imageids = '';
	$.each(__IMAGE_IDS__, function(k, v){
		if(v > 0)
		{
			imageids = imageids + ( imageids ? ',' + v : v );
		}
	});
	// 多附件
	var attachids = '';
	$.each(__ATTACH_IDS__, function(k, v){
		if(v > 0)
		{
			attachids = attachids + ( attachids ? ',' + v : v );
		}
	});
	
	// 开始发布(当前之支持发布文字，其他的以后用到的时候加入)
	var myAjax = $.post(
		"ajax.php?mod=topic&code=do_add&act=reply",
		{
			topictype:topictype,
			imageid:imageids,
			attachid:attachids,
			from:from,
			content:content,
			item:appitem,
			item_id:appitem_id
		},
		function (d) {
			if (options.response) {
				options.response.call();
			} else {
				location.reload();
			}
		}
	);
	return true;
}

/**
 * 微博类型选择(应用)
 */
function selectAppTopicType(id, options)
{
	id = (isUndefined(id) ? 'topictype' : id);
	if (!options) {
		options = {};
	}
	
	var input = $("#" + id);
	if (options.toid) {
		out = $('#'+options.toid);
	} else {
		out = input;
	}
	
	// 默认的微博客类型
	var defTopicType = 'reply';
	if (options.defTopicType) {
		defTopicType = options.defTopicType;
	}
	
	if (input.attr("checked")) {
		out.val('first');
	} else {
		out.val(defTopicType);
	}
}

/**
 * 获取应用活动
 */
var __AppActivityHandleKey__ = new Array();
function getAppActivity(appMod, appCode, appWpId, options)
{
	var cache = Cache.get(appWpId);
	if (!isUndefined(cache) && cache.length > 0) {
		$('#'+appWpId).html(cache);
		return ;
	}
	
	if (isUndefined(options)) {
		options = {};
	}
	if (!isUndefined(__AppActivityHandleKey__[appCode])) {
		return ;
	}
	__AppActivityHandleKey__[appCode] = true;
	var arf = '';	// apprequestfrom
	if (options.arf) {
		arf = '&arf='+options.arf;
	}
	appUrl = 'ajax.php?mod='+appMod+'&code='+appCode+arf;
	var retType = 'html';
	if (options.retType) {
		reType = options.retType;
	}
	$.get(
		appUrl,
		{},
		function(r) {
			if (options.response) {
				options.response.call();
			} else if (appWpId) {
				if (is_json(r)) {
					var json = eval('('+r+')');
					$('#'+appWpId).html(json.msg);
				} else {
					r = evalscript(r);
					$('#'+appWpId).html(r);
					Cache.save(appWpId, r);
				}
			}
		},
		retType
	);
}

/**
 * 从数组中删除一个指定值元素(一维数组)
 */
function remove_ele(ary, val)
{
	var ary2 = Array();
	for (var i in ary) {
		if (ary[i] != val) {
			ary2[i] = ary[i];
		}
	}
	return ary2;
}

/**
 * 指定值是否存在数组中
 */
function in_array(needle, haystack)
{
	if(typeof needle == 'string' || typeof needle == 'number') {
		for(var i in haystack) {
			if(haystack[i] == needle) {
					return true;
			}
		}
	}
	return false;
}

/**
 * 移出黑名单对话框
 */
function DelBlackListDialog(uid)
{	
	var handle_key = 'del_my_blacklist';
	showDialog('del_my_blacklist', 'ajax', '移出黑名单', {"url":"ajax.php?mod=topic&code=follower_choose&uid="+uid}, 300);	
}

/**
 * 黑名单页面 (移出黑名单操作）
 * 
 * 移出我的黑名单
 */
function DoDelMyBlackList()
{	
	// 是否关注此用户
	var is_follow = 0;
	
	// 移出黑名单 用户 ID
	var touid = $("#touid").val();
	
	// 我的 ID
	var uid = $("#uid").val();
	
	// 勾选 关注此用户 增加关注
	if($("#is_follow").attr("checked")){	
		// 触发关注js
		follow(touid,'','add');
	}
	var myAjax=$.post(
		"ajax.php?mod=topic&code=do_delmyblacklist",
		{
			touid:touid
		},
		function(d){
			if(''!=d){
				$("#follow_user_"+touid).remove();
				// 关闭 移出我的黑名单对话框
				closeDialog('del_my_blacklist');    
			}
		}
	 );
}



/**
 * 修改邮箱, 重新发送邮箱验证对话框
 */
function CheckEmailModifyDialog(uid)
{	
	var handle_key = 'del_my_fans';
	showDialog('del_my_fans', 'ajax', '移除粉丝', {"url":"ajax.php?mod=topic&code=del_myfans&uid="+uid}, 300);	
}

// 长微博内容 foxis 2011年6月28日
function show_longtext_info_dialog(idval, is_modify, content_id, button_id)
{
	var content_id = 'undefined' == typeof(content_id) ? 'i_already' : content_id;
	var button_id = 'undefined' == typeof(button_id) ? 'publishSubmit' : button_id;
	var titleval = '发布微博长内容';
	var urlval = 'ajax.php?mod=longtext';
	if(content_id)
	{
		urlval = urlval + '&content_id=' + content_id;
	}
	if(button_id)
	{
		urlval = urlval + '&button_id=' + button_id;
	}
		
	var idval = 'undefined' == typeof(idval) ? 0 : idval;
	var is_modify = 'undefined' == typeof(idval) ? 0 : is_modify;
	
	if(is_modify)
	{
		if(idval < 1)
		{
			MessageBox('warning', '请指定一个正确的ID');
			
			return false;
		}
		
		titleval = '编辑微博长内容';
		urlval = urlval + '&code=modify&id=' + idval;
	}
	else
	{
		urlval = urlval + '&code=add';
		if(idval)
		{
			urlval = urlval + '&longtext=' + encodeURIComponent(idval);
		}
	}
	
	showDialog('longtext_info_dialog', 'ajax', titleval, {url:urlval}, 600);
}
function close_longtext_info_dialog()
{
	closeDialog('longtext_info_dialog');
}
function view_longtext(idval, tidval, aobj, TPT_id, ptidv)
{
	var TPT_id = ('undefined' == typeof(TPT_id) ? '' : TPT_id);
	var ptidv = ('undefined' == typeof(ptidv) ? 0 : ptidv);
	
	var full_id = 'topic_content_' + TPT_id + tidval + '_full';
	var short_id = 'topic_content_' + TPT_id + tidval + '_short';
	if('' != TPT_id) {
		var hash_id = 'topic_list_' + ptidv;
	} else {
		var hash_id = 'topic_list_' + tidval;
	}
	
	var full_html = $.trim(($('#' + full_id).html()));
	if('' != full_html)
	{
		$('#' + full_id).empty();
		$('#' + full_id).css('display', 'none');

		//$('#' + hash_id + ' ul.imgList').show();
		$('#' + short_id).css('display', 'block');
		if('undefined' != typeof(aobj))
		{
			$(aobj).html('查看全文');
		}
		window.location.hash = '#' + hash_id;
	}
	else
	{
		$.post
		(
			'ajax.php?mod=longtext&code=view',
			{
				'id' : idval			
			},
			function(d)
			{
				if( -1 != d.indexOf('<success></success>') )
				{
					//$('#' + hash_id + ' ul.imgList').hide();
					$('#' + short_id).css('display', 'none');
					
					$('#' + full_id).html(d);
					$('#' + full_id).css('display', 'block');
					if('undefined' != typeof(aobj))
					{
						$(aobj).html('收起全文');
					}
				}
			}
		);
	}
}

/**
 * 
 * @param idval
 *            tid
 * @param sidv
 *            source tid
 */
function view_topic_content(idval, sidv, TPT_id)
{
	var idval = ('undefined'==typeof(idval) ? 0 : idval);
	var sidv = ('undefined'==typeof(sidv) ? 0 : sidv);
	var TPT_id = ('undefined' == typeof(TPT_id) ? '' : TPT_id);

	var sidval = 0;
	if(sidv < 1)
	{
		sidv = idval;
	}
	else
	{
		if( TPT_id.length < 1 )
		{
			TPT_id = 'TPT_';
		}
		
		if(sidv != idval)
		{
			sidval = sidv;
		}
	}
	var topic_view = 'undefined'==typeof(__TOPIC_VIEW__) ? 0 : __TOPIC_VIEW__;
	var short_id = 'topic_content_area_' + TPT_id + idval + '_short';
	var full_id = 'topic_content_area_' + TPT_id + idval + '_full';
	var reply_aid = 'topic_list_reply_' + sidv + '_aid';
	var reply_area_id = 'reply_area_' + sidv;
	var hash_id = 'topic_list_' + sidv;
	
	var full_html = $.trim(($('#' + full_id).html()));
	if('' != full_html)
	{
		hash_id = short_id;
		
		$('#' + full_id).empty();
		$('#' + full_id).css('display', 'none');

		$('#' + short_id).css('display', 'block');
		
		if(($.trim(($('#' + reply_area_id).html()))).length < 1)
		{
			$('#' + reply_aid).click();
		}
	}
	else
	{
		hash_id = full_id;
		
		$.post
		(
			'ajax.php?mod=view&code=topic_content',
			{
				'id' : idval,
				'sid' : sidval,
				'TPT_' : TPT_id,
				'topic_view' : topic_view
			},
			function(d)
			{
				// $('#' + short_id).empty();
				$('#' + short_id).css('display', 'none');
				
				$('#' + full_id).html(d);
				$('#' + full_id).css('display', 'block');
				
				if(($.trim(($('#' + reply_area_id).html()))).length < 1)
				{
					$('#' + reply_aid).click();
				}
			}
		);
	}
	
	window.location.hash = '#' + hash_id;
}


// 选择签名档皮肤
function select_qmd(ci)
{	
	// 选择皮肤的路径
	var c = 'undefined' == typeof(ci) ? '' : ci;

	var myAjax = $.post
	(
		"ajax.php?mod=topic&code=qmd",
		{	
			// 签名档背景图片路径
			qmd_bg_path:c
		},
		function (d) 
		{
			if(d) 
			{
				// $("#skin_images").remove();
				// $('#'+"qmd_list").html('<img src="' + d + '" />');
				;
			}
			if(c != '')
			{
				location.reload();   
			}
		}
	);
	
	return true;
}

// 更新签名档
function insert_qmd()
{
	
	var myAjax = $.post
	(
		"ajax.php?mod=topic&code=insert_qmd",
		{	
			// 签名档背景图片路径
			qmd_bg_path:'images/qmd.jpg'
		},
		function (d) 
		{
			if(d) 
			{	
				document.write(d);
				 return false;
			}
			
		}
	);
	
	return true;
	
}


/*
 * 注册 后 - 关注用户选项
 * 
 */
function reg_follow_user(follow_type,list_limit)
{	
	// 选择关注的类别 如: 人气推荐 、一周影响榜 等。。
	var follow_type = 'undefined' == typeof(follow_type) ? '' : follow_type;

	// 查看更多 显示条数
	var list_limit = 'undefined' == typeof(list_limit) ? '' : list_limit;
	
	$("#left_nav li").each(function(){
		if ($(this).attr('id') == 'nav_'+follow_type) {
			$(this).addClass('on');
		} else {
			$(this).removeClass('on');
		}
	});
	// alert(follow_type); return false;

	var myAjax = $.post
	(
		"ajax.php?mod=topic&code=reg_follow_user",
		{
			followType:follow_type,
			list_limit:list_limit
		},
		function (d) 
		{ // alert(d); return false;
			if(d) 
			{
				$('#'+"reg_follow_user").html(d);     
			}
			// location.reload();
		}
	);
	
	return true;
}

/**
 * 修改 个人签名 by zx cid = input 输入框ID 获取输入的信息
 */

function modify_user_signature(uid,cid,return_type)
{	
	// 获取输入的 个人签名内容
	var signature = $("#"+cid).val();
	var signature = 'undefined' == typeof(signature) ? '' : signature;

	var myAjax = $.post
	(
		"ajax.php?mod=topic&code=modify_user_signature",
		{	
			uid:uid,
			signature:signature
			
		},
		function (d) 
		{
			if(d.done) {
				 $('*.[ectype=\"user_signature_ajax_' + uid + '\"]').html("(" + d.msg + ")"); 
				 $('*.[ectype=\"user_signature_ajax_left_' + uid + '\"]').html("(" + d.msg + ")"); 
				 show_message('个人签名修改成功',1);  
			}
			else{
				 show_message(d.msg,2);  
			}      
		},'json'
	);
	
	return true;
	
	alert(signature);return false;
}

/**
 * 修改 两栏 三栏选择状态
 * 
 * uid 修改三栏，两栏切换的用户UID web_style 我(当前登录的用户) 选择的分栏状态样式 get_code 当前页面 URL地址的 code
 * 在ajax显示页面 起判断显示模块作用 ajax_list 显示调用那个ajax模板来作为显示数据模板 list_uid 返回用户信息时 显示的UID
 * 
 */


function web_list_type(uid,web_style,get_code,ajax_list,list_uid)
{
	  var style_three_tol = 'right' == ajax_list ? 1 : 0;

	  $(".headerNav").toggleClass("t_col_header");
	  $(".logow").toggleClass("t_col_logo");
	  $(".tagBox").toggleClass("t_col_tagBox");
	  $('.main').toggleClass("t_col_main");
	  $('.main3').toggleClass("t_col_main");
	  $('.main_2').toggleClass("t_col_logo");
	  $('.t_col_main_si').toggleClass("t_col_main_side");
	  $('.bottomLinks').toggleClass("t_col_foot");
	  $('.backTop').toggleClass("t_col_backTop");
	  $('.t_col_main_rb').toggleClass("t_col_main_rn");
	  $('.t_col_main_ln').toggleClass("t_col_main_lb");
	  $('.bL_info').toggleClass("bL_info_three");
	  
	  // 执行修改
	  modify_user_stely_mod(uid,style_three_tol,get_code,ajax_list,list_uid);
	  
	  // 当前状态三栏
		 if(style_three_tol == 1)
		 {	
			 var type_html = '<a href="javascript:void(0);" onclick="web_list_type(\'' + uid + '\',\'0\',\'' + get_code + '\',\'left\',\'' + list_uid + '\'); return false;">三栏</a> ';            		
			 $("#web_list_type_"+uid).html(type_html);
			 
			 if(document.getElementById('web_style')){document.getElementById('web_style').value='0';}
		 }
		 
		 // 当前状态两栏
		 if(style_three_tol == 0)
		 {	 
			 var type_html = '<a href="javascript:void(0);" onclick="web_list_type(\'' + uid + '\',\'1\',\'' + get_code + '\',\'right\',\'' + list_uid + '\'); return false;">两栏</a> ';           		
			 $("#web_list_type_"+uid).html(type_html);
			 
			 if(document.getElementById('web_style')){document.getElementById('web_style').value='1';}
		 }
	
}


function modify_user_stely_mod(uid,style_three_tol,get_code,ajax_list,list_uid)
{
	
	
	if(uid == ''){	
		ShowLoginDialog();
	}
	
	// style_three_tol = 两栏 或者 三栏 状态
	var type = $('#hid_type').val();
	var myAjax = $.post
	(
		"ajax.php?mod=user&code=modify_user_three_tol",
		{	uid:uid,
			style_three_tol:style_three_tol,
			get_code:get_code,
			ajax_list:ajax_list,
			list_uid:list_uid,
			type:type
		},
		function (d) 
		{ 
			
			if(d) {
				
				// alert()
				// 单前状态是三栏 ， 切换为两栏
				if(style_three_tol == 1)
				{	
					
					// alert('三栏切两栏');
	
					// 隐藏左边
					$('#topic_index_left_ajax_list').css('display', 'none');
					//$('#topic_right_user_info').css('display', 'none');

					// 显示右边
					$('#topic_right_ajax_list').css('display', 'block');
					$('#topic_right_ajax_list').html(d);   
					
					
				}
				// 当前状态为两栏 ， 切换为三栏
				if(style_three_tol == 0)
				{
					
					// alert('两栏切三栏');

					// 隐藏右边
					$('#topic_right_ajax_list').css('display', 'none');
					//$('#topic_right_user_info').css('display', 'block');

					// 显示左边
					$('#topic_index_left_ajax_list').css('display', 'block');
					$('#topic_index_left_ajax_list').html(d);
				}

			}
			// location.reload();
		}
	);
	
	return true;
	
}


/**
 * 未登录，弹出登录对话框
 */
function ShowLoginDialog()
{
	var handle_key = 'del_show_login';
	
	showDialog('del_show_login', 'ajax', '快速登录', {"url":"ajax.php?mod=topic&code=showlogin"}, 650);	
}

/**
 * 右边栏 ajax 显示用户相关数据
 * 
 * @param uid
 * @param showCode
 *            ajax中 code显示的模块
 * @return
 */
function right_show_ajax(uid,showCode,ajax_list,ajax_mod)
{	
	var ajax_mod = 'undefined' == typeof(ajax_mod) ? '' : ajax_mod;
	
	var ajax_url = '';
	if (showCode == 'my_qun') {
		ajax_url = "ajax.php?mod=qun&code=widgets&op=my_qun&type=2";
	} 
	else if(ajax_mod == 'validate')
	{
		ajax_url = "ajax.php?mod=validate&code="+showCode;
	}
	else {
		ajax_url = "ajax.php?mod=user&code="+showCode;
	}
	var post = {
		uid:uid,
		type:ajax_mod
	};
	
	var myAjax=$.post(
	ajax_url,
	post,
	function(d){
		if(d){
			$('#'+uid+"_"+ajax_list).html(evalscript(d));
		}
	});
	return false;
}


/**
 * 显示验证码
 */
function seccode(options)
{
	if (typeof options == 'undefined' || options == null || options == '') {
		options = {};
	}
	var updateFunc = "updateSeccode";
	var id = null;
	if (options.updateFunc) {
		updateFunc = options.updateFunc;
	} else {
		if (options.id) {
			id = options.id;
		}
	}
	var img_id = "img_seccode";
	if (options.img_id) {
		img_id = options.img_id;
	}
	var img = 'index.php?mod=other&code=seccode&random='+Math.random();
	var html = '<img id="'+img_id+'" src="'+img+'" align="absmiddle" onclick="'+updateFunc+'(\''+id+'\',\''+img_id+'\');">';
	if (options.wp) {
		$('#'+options.wp).html(html);
	} else {
		document.writeln(html);
	}
}

/**
 * 更新验证码
 */
function updateSeccode(id, img_id)
{
	if (isUndefined(img_id)) {
		var img_id = "img_seccode";
	}
	var img = 'index.php?mod=other&code=seccode&random='+Math.random();
	$('#'+img_id).attr("src", img);
	if (typeof id != 'undefined' && id != null && id != '') {
		$('#'+id).val('');
	}
}

/**
 * 显示验证码校验对话框
 */
function showSeccodeDialog()
{
	var handle_key = 'handle_key_seccode_dialog';
	showDialog(handle_key, 'ajax', '请补充下面的登录信息', {url:"ajax.php?mod=misc&code=seccode&random="+Math.random()}, 300);
}

/**
 * 校验验证码 应该有一块独立的校验模块，暂时先这样了。
 */
function checkSeccode(seccode, options)
{
	if (isUndefined(options)) {
		options = {};
	}
	
	var tips_id = "check_seccode_tips";
	if (options.tips_id) {
		tips_id = options.tips_id;
	}
	
	$.post(
		'ajax.php?mod=member&code=check_seccode',
		{"check_value":seccode},
		function(r) {
			if ('' != r) {
				$("#"+tips_id).html('<img src="templates/default/images/member/regwrong.png" >');
				$("#"+tips_id).show();
			} else {
				$("#"+tips_id).html('<img src="templates/default/images/member/accept.png" >');
				$("#"+tips_id).show();
				if (options.success) {
					options.success.call();
				}
			}
		}
	);
}

/**
 * 显示创建关注分组对话框
 */
function showFollowGroupAddDialog()
{
	var handle_key = 'hk_follow_group_add_dialog';
	showDialog(handle_key, 'ajax', '创建分组', {url:"ajax.php?mod=topic&code=create_group"}, 300);
	
	// 设置关闭事件监听
	/*
	 * setDialogOnCloseListener(handle_key, function(){ if
	 * (__AddFansGroupSuccess__ == true) { location.reload();
	 * __AddFansGroupSuccess__ = false; } });
	 */
}

/**
 * 获取应用描述widgets
 */
function getAppDescriptionWidgets(type, options)
{
	if (isUndefined(type)) {
		return false;
	}
	
	if (isUndefined(options)) {
		options = {};
	}
	
	var get = {};
	var ajax_url = "";
	
	if (type == 'qun') {
		ajax_url = "ajax.php?mod=qun&code=widgets&op=simple_desc";
		if (options.item_id) {
			get = {"qid":options.item_id};
		} else {
			return false;
		}
	} else {
		return false;
	}
	var myAjax = $.get(
		ajax_url,
		get,
		function(d) {
			if (d) {
				if (options.success) {
					options.success.call(d);
				} else {
					// 默认填充app_description_wp
					$("#app_description_wp").html(d);
				}
			}
		}
	);
	return true;
}

// 获取投票详情
function getVoteDetailWidgets(tid, vid)
{
	var imgDetail = $("#vote_detail_"+tid);
	var area = $("#vote_area_"+tid);
	var content = $("#vote_content_"+tid);
	imgDetail.hide();
	content.html('<div style="text-align:center; margin-top:10px;"><img src="./images/loading.gif" /></div>');
	area.show();
	var myAjax = $.post(
		'ajax.php?mod=vote&code=detail',
		{vid:vid,tid:tid},
		function(r) {
			content.html(r);
			$('#close_vote_detail_'+tid).click(function(){
				closeVoteDetailWidgets(tid);
			});
		}
	);
	return false;
}

// 关闭投票详情
function closeVoteDetailWidgets(tid)
{
	var imgDetail = $("#vote_detail_"+tid);
	var area = $("#vote_area_"+tid);
	imgDetail.show();
	area.hide();
}

// 参于投票
function doVote(form, options)
{
	if (isUndefined(options)) {
		var options = {};
	}
	
	var form_name = form;
	var post_data = $('#'+form_name).serialize();
	var action = $('#'+form_name).attr("action");
	if (action) {
		$.post(
			action, 
			post_data, 
			function(r){
				if (!is_json(r)) {
					if (options.type == 'topic') {
						if (r != '') {
							$('#vote_main_'+options.tid).html("投票成功");
							var handle_key = "hk_toweibo";
							showDialog(handle_key, 'local', '评论一下', {'html':r}, 500);
							$('#topic_simple_close_btn'+options.tid).click(
								function() {
									closeDialog(handle_key);
								}
							);
							$('#topic_simple_share_btn'+options.tid).click(
								function () {
									var response = function() {
										// location.reload();
										closeDialog(handle_key);
										listAreaPrependTopic();
									};
									publishSimpleTopic($('#topic_simple_content'+options.tid).val(), 'vote', $("#topic_simple_item_id"+options.tid).val(), {response:response,topic_type:$('#topic_simple_type'+options.tid).val()});
									// publishSimpleTopic($('#topic_simple_content'+options.tid).val(),
									// '', 0, {response:response});
								}
							);
						}
					}
				} else {
					var json = eval('('+r.toString()+')');
					if (json.done == true) {
						if (options.type == 'topic') {
							$("#vote_publish_tips_"+options.tid).html(json.msg);
							$("#vote_publish_tips_"+options.tid).show();
							$('#vote_main_'+options.tid).hide();
						} else {
							MessageBox('notice', json.msg);
						}
					} else {
						if (options.type == 'topic') {
							$("#vote_publish_tips_"+options.tid).html(json.msg);
							$("#vote_publish_tips_"+options.tid).show();
						} else {
							MessageBox('warning', json.msg);
						}
					}
				}
			}
		);
	}
}

/**
 * 动态加载js
 */
function loadJs(file)
{
	var head = $('head');
	$("<scri"+"pt>"+"</scr"+"ipt>").attr({src:file,type:'text/javascript',id:'load'}).appendTo(head);
}
/*
 * 分类
 */
function post_class(){
	showDialog('pclass', 'ajax', '发布分类信息', {"url":"ajax.php?mod=class&code=classpost"}, 800);	
}

// 微博推荐对话框
function showTopicRecdDialog(options)
{
	if (isUndefined(options)) {
		var options = {};
	}
	
	// 错误检查
	var checkerror = function(data) {
		if (is_json(data)) {
			var json = eval('('+data.toString()+')');
			closeDialog(handle_key);
			MessageBox('warning', json.msg, '提示');
			return false;
		}
		return true;
	};
	var handle_key = options.tid ? "hk_tr_dialog_"+options.tid : "hk_tr_dialog";
	var ajax_url = "ajax.php?mod=topic&code=recd&tid="+options.tid;
	if (options.tag_id) {
		ajax_url += "&tag_id="+options.tag_id;
	}
	showDialog(handle_key, "ajax", "推荐", {'url':ajax_url, checkerror:checkerror}, 350);
}

// 推荐微博
function recdTopic(handle_key, form)
{
	var post_data = $('#'+form).serialize();
	$.post(
		"ajax.php?mod=topic&code=do_recd", 
		post_data, 
		function(r){
			if (r.done) {
				closeDialog(handle_key);
				MessageBox('notice', r.msg, '提示');
			} else {
				MessageBox('warning', r.msg, '提示');
			}
		},
		'json'
	);
}

// 输入自动完成
function Autocompleter(handle_key, url, options)
{
  if (typeof options == 'undefined') {
	options = {};
  }
  var autocompleter = new JSGST_Autocompleter();
  autocompleter.handle_key = handle_key;
  autocompleter.url = url;
  if (options.item_list_tips) {
	autocompleter.item_list_tips = options.item_list_tips;
  }
  if (options.formatItemCallback) {
	autocompleter.formatItemCallback = options.formatItemCallback;
  }
  if (options.resultCallback) {
	autocompleter.resultCallback = options.resultCallback;
  }
  return autocompleter;
}



// 删除分组
function deleteFollowGroup(gid)
{
	options = {
		'onClickYes':function(){
			location.href = "index.php?mod=other&code=groupdelete&gid="+gid;
		}
	};
	MessageBox('confirm', "你确定删除这个分组吗？", '提示', options);
}

/**
 * 显示修改分组名称对话框
 */
function showFollowGroupModifyDialog(options)
{
	if (isUndefined(options)) {
		options = {};
	}
	var handle_key = "hk_follow_group_modify_dialog";
	showDialog(handle_key, 'local', '修改分组名称', {"id":"modify_group"}, 300);
}

// ctrl+enter发布
function ctrlEnter(event, btnId, onlyEnter)
{
	if(isUndefined(onlyEnter)) onlyEnter = 0;
	if((event.ctrlKey || onlyEnter) && event.keyCode == 13) {
		$('#'+btnId).click();
		// $('#'+btnId).triggerHandler("click");
		return false;
	}
	return true;
}

// 只允许输入数字（不包括负数）
function allowNumeric(obj)
{
	obj.value = obj.value.replace(/[^0-9]/g,'');
}


// 设置分组提交
function setGroupSubmit(uid)
{
	var r = $('#qremark_name_'+uid);
	var remark = r.val();
	
	var myAjax = $.post(
		"ajax.php?mod=topic&code=add_remark",
		{
			remark:remark,
			buddyid:uid
		},
		function (d) {
			if(d) {
				$('#'+"remarklist_"+uid).html(d);
			} else {
				if(remark) {
					$('#'+"remarklist_"+uid).html('(' + remark + ')');
				} else {
					$('#'+"remarklist_"+uid).html('');
				}
			}                
		}
	);
	var handle_key = "global_select_"+uid;
	closeDialog(handle_key);
}

/**
 * 获取我的好友列表
 */
function getMyFollowUser(page, options)
{
	if (isUndefined(options)) {
		options = {};
	}	
	var myAjax = $.post(
			"ajax.php?mod=pm&code=pm_follow_user",
			{
				page:page
				
			},
		function(d) {
				alert(d);
		}
	);
}

function onFoucsReplyInput(tid)
{
	var input = $("#reply_content_"+tid);
	var h = $("#topic_lists_"+tid);
	if (h.length > 0) {
		$("html,body").animate({scrollTop: h.offset().top-40}, 1000, 'swing', function(){input.focus();});
	} else {
		input.focus();
	}
}





// 单选按钮 , 下拉框 ， 复选框 选中状态
/*
 * 使用方法：
 * 
 * <input id="input_id" value="1" checked="checked" type="radio" /> <input
 * id="input_id" value="0" checked="checked" type="radio" /> <script
 * language='JavaScript'
 * type="text/javascript">autoSelected(document.formInfo.input_id, '$val');</script>
 * 
 * 参数 @ formInfo = form表单ID @ input_id = 单选按钮 、 复选框 、 下拉框 id @ $val = 已保存的复选框的值
 * 如： 0 , 1 。 $vla = 1 选择值为 1 的状态 zx
 */
function autoSelected(obj, defVal)
{
	if(!obj) return;
	
	if((typeof defVal).toLowerCase() != 'object')
	{
		var tmp = defVal;
		
		defVal = new Array();
		defVal[0] = tmp;
	}
	
	if(obj.tagName)
	{
		switch(obj.tagName.toLowerCase())
		{
			case 'select':
					for(var i = 0; i < obj.length; i++)
					{
						if(in_array(obj.options[i].value, defVal))
						{
							obj.options[i].selected = true;
						}
					}
			case 'input':
					if(obj.type.toLowerCase() == 'checkbox' || obj.type.toLowerCase() == 'radio')
					{
						if(in_array(obj.value, defVal))
						{
							obj.checked = true;
						}
					}
					break;
		}
	}
	else
	{
		for(var i = 0; i < obj.length; i++)
		{
			if(obj[i].tagName.toLowerCase() == 'select')
			{
				for(var j = 0; j < obj[i].length; j++)
				{
					if(in_array(obj[i].options[j].value, defVal))
					{
						obj[i].options[j].selected = true;
					}
				}
			}
			else if(obj[i].tagName.toLowerCase() == 'input')
			{
				if(in_array(obj[i].value, defVal))
				{
					obj[i].checked = true;
				}
			}
		}
	}
}


/*
 * ajax 返回勋章详细信息
 */ 

function check_medal_list(uid,member_id,medal_type)
{
	
	var uid = 'undefined' == typeof(uid) ? '0' : uid;
	var member_id = 'undefined' == typeof(member_id) ? '0' : member_id;
	var medal_type = 'undefined' == typeof(medal_type) ? '' : medal_type;
	
	var myAjax=$.post(
		"ajax.php?mod=topic&code=check_medal_list",
		{
			uid:uid,
			member_id:member_id,
			medal_type:medal_type
		},
		function(d){
			if(''!=d){
				
				// alert(d);
				if(medal_type != '')
				{
					$('#'+"user_medal_list_"+uid+"_"+medal_type).html(d);
				}
				else
				{
					$('#'+"user_medal_list_"+uid+"_"+member_id).html(d);
				}
				
			
				// closeDialog('del_my_blacklist');
			}
		}
	 );
}



// ajax显示投票信息
function vote_ajax_menu(vid,uid)
{
	var uid = 'undefined' == typeof(uid) ? 0 : uid;
	var template = 'undefined' == typeof(template) ? '' : template;
	var handle_key = "alert_follower_menu_"+uid;
	var ajax_url = 'ajax.php?mod=validate&code=validate_vote';
	var post = {
					uid:uid,
					vid:vid

				};
	var title = "正在载入..."; 
	
	showDialog(handle_key, 'ajax', title, {"url":ajax_url, "post":post}, 620);
	
		
}



/**
 * 获取访谈提问嘉宾列表
 */
function askmenuajax(){
	var obj = '.askToDiffZone';var left = 0;var top = 0;
	if($(obj).length){
		$('.askToDiff').hover(function(){left = $(this).offset().left - 1 + 'px';top = $(this).offset().top + 22 +'px';$(obj).css('left',left);$(obj).css('top',top);$(obj).show();$(obj).hover(function(){$(obj).show();},function(){$(obj).hide();});},function(){$(obj).hide();});			
	}
}

/**
 * 虾米音乐搜索
 */
function music_serach(){
   var music_name=$('#music_name'); 
   var pages=$('#page').val(); 
   if(pages == null || pages == ''){
	   pages = 1;
   }
		if(music_name.val()==''){
		  show_message('请输入需要搜索的歌名',2);
		  music_name.focus();
		  return false;
		}
	   //$('#music_search_tip').html('<span style="color:red"><img src="./images/loading.gif">正在检索，请稍等...</span>');
		$.post('ajax.php?mod=class&code=xiami',
			{name:music_name.val(),
			page:pages},
			function(d){
				d= eval('('+d+')');
				var json=eval(d.results);
				if(json.length>0){
					var totle = d.total;
					var num = Math.ceil(totle/8);

					var phtml = page_html(pages,num);
					
					$('#music_list').html('<ul class="tagB" style="display:block"><div id="add_ajax_favorite_tags" class="music_end"></div><div>'+ phtml +'</div><span id="page"></span></ul>');
					
					for(var i=0; i<json.length; i++)  {
						json[i].song_name = decodeURIComponent(json[i].song_name).replace(/\+|'|"/g," ");
						json[i].artist_name = decodeURIComponent(json[i].artist_name).replace(/\+|'|"/g," ");
						var html='<span onclick="check_music('+json[i].song_id+',\''+decodeURI(json[i].album_logo)+'\',\''+decodeURI(json[i].artist_name)+'\',\''+decodeURI(json[i].song_name)+'\')" style="width:100%;cursor:pointer;" onmouseover="$(this).css(\'color\',\'red\');" onmouseout="$(this).css(\'color\',\'\');">'+decodeURI(json[i].song_name)+' ---  '+decodeURI(json[i].artist_name)+'</span>';
						$('.music_end').append(html);
					}
				}else{
					$('#music_list').html("未检索到符合条件的歌曲");
				}
		 });
}

/**
 * 制作虾米音乐的简单HTML分页代码 page：当前页 num：总页数
 */
function page_html(page,num){
	page = parseInt(page);
	num = parseInt(num);
	
	var html = '';
	if(num < 2){
		return '';
	}
	if(page > 1){
		var fpage = page-1;
		html += '<span onclick="changepage('+ fpage +');" style="cursor:pointer;">上一页</span>';
	}
	if(page < num){
		var npage = page+1;
		html += '<span onclick="changepage('+ npage +');" style="cursor:pointer;">下一页</span>';
	}
	return html;
}

function changepage(page){
	$('#page').val(page);
	music_serach();
}

/**
 * 选择音乐
 */
function check_music(music_id,p_url,name,music_name){
	$('#xiami_id').val(music_id);
	var content = music_name + '--' + name;
	$('#i_already').val(content);
	$(".menu_music").hide();
}

/**
 * 图片动态加载
 */
function loadphoto(uid)
{
	var loadmsg = '<div class="boutique_load"><span>正在加载，请稍后...</span></div>';
	var uid = 'undefined' == typeof(uid) ? 0 : uid; 
	if(isLoading){
		$('#boutique_load').html(loadmsg);onloading = true;
		var myAjax = $.post('ajax.php?mod=topic&code=photo',{page:photopagenum,uid:uid},function (d){if('' != d){var s=d.split('<jishigou>');var n=s.length;if(n>4){n=4;}for(var i=0;i<n;i++){$('#phototopic_'+i).append(s[i]);}photopagenum++;if(n<4){isLoading = false;$('#boutique_load').html('');}else{$('#boutique_load').html(photoloadmsg);onloading = false;}}else{$('#boutique_load').html('');isLoading = false;}});
	}else{
		$('#boutique_load').html('');
	}
}

/**
 * 微博动态加载
 */
function loadtopic(key,order)
{
	var loadmsg = '<div class="pageStyle"><li><span>正在加载，请稍后...</span></li></div>';
	var key = 'undefined' == typeof(key) ? 0 : key;
	var order = 'undefined' == typeof(order) ? '' : order;
	if(isLoading && key){
		$('#pageinfo').html(loadmsg);onloading = true;$("#pagehtml").hide();
		var myAjax = $.post('ajax.php?mod=topic&code=ajax',{key:key,order:order},function (d){if('' != d){$('#ajaxtopic').append(d);ajaxnum++;}else{isLoading = false;$("#pagehtml").show();}onloading = false;$('#pageinfo').html('');});
	}else{
		$('#pageinfo').html('');$("#pagehtml").show();
	}
}

/**
 * topicManage.js
 */
function force_out(uid){
	var handle_key = 'force_out';
	if(uid < 1){show_message('请选择要封杀的对象');return false;}
	showDialog(handle_key, 'ajax', '封杀用户', {url:'ajax.php?mod=topic_manage&code=force_out&uid='+uid}, 400);
}

function sendemailtoleader(uid,tid,type){
	var handle_key = 'sendemail';
	if(tid < 1 || uid < 1){show_message('请选择要报备的对象');return false;}
	showDialog(handle_key, 'ajax', '报备', {url:'ajax.php?mod=topic_manage&code=sendemail&uid='+uid+'&tid='+tid+'&type='+type}, 400);
}

function setFilterRed(){
	document.getElementById('setfiledmsg').style.display = 'block';
	$.post(
		'ajax.php?mod=class&code=getfilter&type=verify_list',
		{},
		function(d){
			if(d.done == true){
				if(d.retval.length > 0){
					var i = 0;
					var str = $('#topic_verify_list').html();
					for(i=0;i<d.retval.length;i++){
						str = str.replace(new RegExp(d.retval[i], 'g'),"<font color=red>"+d.retval[i]+"</font>");
					}
					$('#topic_verify_list').html(str);
					document.getElementById('setfiledmsg').style.display = 'none';
				}
			}
		},'json'
	)
}

function force_ip(ip){
	if('undefined' == typeof(ip)){
		show_message('无效IP',3);
		return false;
	}
	if(!confirm('确认封杀该IP？')){
		return false;
	}
	$.post(
		'ajax.php?mod=topic_manage&code=force_ip',	
		{ip:ip},
		function(d){
			show_message(d.msg,3);
		},'json'
	)
}