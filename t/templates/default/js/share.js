//将字符作为JS对象
String.prototype.evalJson=function (){return eval('('+this+')');};
var isIE=navigator.appVersion.match(/\bMSIE\b/);//是否为IE
//键盘提交
var submiting=false;
function keySubmit(event,formID,submitID) {
	if(submiting == false && ((event.ctrlKey && event.keyCode == 13) || (event.altKey && event.keyCode == 83)) && $(submitID)) 
	{
		if(typeof(Validator)!="undefined" && Validator.Validate($(formID),3)==false)
		{
			return false;
		}
		submiting = true;
		$(submitID).disabled = true;
		$(formID).submit();
	}
}
function showMsg(msg,type,showTime)
{
	var Obj=$('systemMSG');
	Element.update(Obj,msg);
	Element.show(Obj);
	if(type!=undefined)Obj.className=type;
	if(showTime!=undefined)
	{
		window.setTimeout(function (){Element.hide(Obj);},showTime*1000)
	}
}
/**********文本框获取焦点时样式事件***************/
function setOnFocusStyle(tagName,className) 
{
	var objList=document.getElementsByTagName(tagName);
	for (var i=0; i < objList.length; i++)
	{
		var obj=objList[i];
		Event.observe(obj,'focus',function(e) 
			{
				var element=Event.element(e);
		   		Element.addClassName(element,className);
		   		//tip
				var temptips=$(element.id+'_tips');
				if (temptips) {
					pos=absPos(element);
					temptips.style.display='block';
					temptips.style.top=pos.y-temptips.offsetHeight-3+'px';
					temptips.style.left=pos.x+3+'px';
				}
		  	}
		  );
		Event.observe(obj,'blur',function(e) 
			{
				var element=Event.element(e);
				//tip
		   		Element.removeClassName(element,className);
				var temptips=$(element.id+'_tips');
				if (temptips) {
					myTimeout = window.setTimeout(function() {temptips.style.display='none';}, 200); 
				}
		  	}
		  );
	 }
}
function setOnFocus()
{
	Event.observe(window,"load",
		function(){
			setOnFocusStyle("input","focus");
			setOnFocusStyle("textarea","focus");
		}
	)
}
setOnFocus();

//动态加载一个JS及CSS文件
function include(file)
{
	var head=document.getElementsByTagName('head').item(0);
	var id='_ref_'+file;
	if($(id)!=null)return true;
	if (file.indexOf(".css")!=-1)
	{
		var fileref=document.createElement("link")
		fileref.setAttribute("rel", "stylesheet");
		fileref.setAttribute("type", "text/css");
		fileref.setAttribute("href", file);
		fileref.setAttribute('id',	id);
	}
	else
	{
		var fileref=document.createElement('script');
		fileref.setAttribute('language',		'JavaScript');
		fileref.setAttribute('type',			'text/javascript');
		fileref.setAttribute('id',			id);
		fileref.setAttribute('src',			file);
	}
	head.appendChild(fileref);	
}
//加载JS，同时判断是否加载完成，完成里调用函数，一个一个加载。
function loadJs(file,varID,onloaded,timeout)
{
	var timeout=timeout || 30000;
	try{eval(varID);onloaded();return true;}catch(t){};
	include(file);
	var  onJsLoaded=function(onloaded)
	{
		try{eval(varID);onloaded();}
		catch(e){window.setTimeout(function(){onJsLoaded(onloaded),500});}
	}
	onJsLoaded(onloaded);
	setTimeout(function (){try{eval(varID)}catch(e){alert(file+"\n文件载入失败,或您设置的唯一标识符有误。")}},timeout);
}
function loadPrototype(file,onloaded){loadJs(file,'Prototype',onloaded);}
function loadEffect(file,onloaded){loadJs(file,'Effect',onloaded);}
function loadWindow(file,onloaded){loadJs(file,'Window',onloaded);}

//取得在页面中的绝对位置
function   absPos(eid){
  var e=$(eid);l=e.offsetLeft; t=e.offsetTop;   
  while(e=e.offsetParent){   
	  l+=e.offsetLeft;
	  t+=e.offsetTop;
  }
  return {x:l,y:t};  
} 

//显示TABLE的部分行数
//@string table_id TABLE元素ID
//@integer rows 行数
function showRowsTr(table_eid,rows)
{
	var Obj=$(table_eid);
	if(Obj==null)return false;
	for (var i=Obj.rows.length-1;i>=0;i--)
	{
		if(rows=='all')
		{
			Obj.rows[i].style.display='';
			if(Obj.rows[i].name=='showRowsTr')Obj.rows[i].parentNode.removeChild(Obj.rows[i]);
		}
		else
		if(i>=rows)
		{
			Obj.rows[i].style.display='none';
			if(i==rows)
			{
				var partStr=Obj.insertRow(-1);
				partStr.name='showRowsTr';
				for (var c=0;c<Obj.rows[i].cells.length-1;c++){partStr.insertCell(-1);}
				var more=partStr.insertCell(-1);
				more.innerHTML='<button type="button" onclick="showRowsTr(\''+table_eid+'\',\'all\');">更多>></button>';
			}
		}
	}
}

//上载图片时显示预览
function previewImage(src,containerId,width,height)
{
	var tip='如果图片不能正常预览,请使用IE浏览器。';
	if(containerId==undefined)
	{
		alert("预览的目标元素ID未指定。");
		return false;
	}
	width=width || 100;height=height || 100;
	$(containerId).innerHTML="<a href='"+ src +"' target='_blank'><img style='width:"+width+"px;height:"+height+"px;border:1px solid red' onerror='previewImageError(\""+containerId+"\")' src='" + src + "' alt='"+tip+"' title='"+tip+"'></a>";
}
function previewImageError(containerId)//图片预览出错提示
{
	$(containerId).innerHTML="<span class='warning'>图片格式不正确</span>";
}
//返回一个UNIX时间戳，和PHP一样的
function time()
{
	var _dateObj=new Date();
	var _time=_dateObj.getTime().toString();
	return parseInt(_time.substring(0,_time.length-3));
}
//JS缓存类，用于缓存AJAX返回结果非常有用
function CacheHandler()
{
	this.data={};
	this.lifeTime=3600;//默认缓存一小时
	this.setLifeTime=function(lifeTime)
	{
		this.lifeTime=lifeTime;
	}
	this.save=function (name,value,lifeTime)
	{
		this.data[name]={expire:time()+(parseInt(lifeTime) || this.lifeTime),value:value};
		return this.data[name]['value'];
	}
	this.get=function (name)
	{
		if(this.data[name]==undefined || this.data[name]['expire']<time())return undefined;
		return this.data[name]['value'];
	}
	this.getOrSave=function(name,value,lifeTime)
	{
		return this.get(name)==undefined && this.save(name,value,lifeTime);
	}
	this.clear=function (name)
	{
		name?delete this.data[name]:this.data={};
	}
}

var Cache=new CacheHandler();//实例化个全局缓存对象

//显示部分文本
//@string eid HTML元素ID
//@integer length 长度
//@integer start 开始位置
function showLengthText(eid,length,start,showMoreText,showPartText)
{
	if(showMoreText==undefined)showMoreText='>>显示更多';
	if(start==undefined)start=0;
	if(showPartText==undefined)showPartText='<<隐藏部分';
	var obj=$(eid);
	var _full='full_'+eid;
	if(obj.innerHTML.length<length)return false;
	if(Cache.get(_full)==undefined)
	{
		if(showPartText.length!=0)
		{
			showPartText='<span onclick="showLengthText(\''+eid+'\',\''+length+'\',\''+start+'\',\''+showMoreText+'\',\''+showPartText+'\');window.scrollTo(0,absPos(\''+eid+'\').y-20);" class=button>'+showPartText+'</span>';
		}
		Cache.save(_full,obj.innerHTML+showPartText,86400);
	}	
	var moreStr='&nbsp;&nbsp;<span onclick="$(\''+eid+'\').innerHTML=Cache.get(\''+_full+'\');" class="button">'+showMoreText+'</span>';
	obj.innerHTML=obj.innerHTML.replace(/<\/?[^>]+>/gi, '').substr(start,length)+moreStr;
}



/***********************狐狸负责 开始******************/
//显示，隐藏
function show_hide(elem_id)
{
	var elem = $(elem_id);
	elem.style.display=elem.style.display=='none'?'block':'none';
}
//放大字体
function do_zoom(size,elem_id)
{
	$(elem_id).style.fontSize=size+'px';
	return false;
}


//动态调整选中的TAG
/*
*@eid TAG列表存放的ul的id属性值
*@selected 偏移第几个LI从1开始,或者LI的name属性值
*/
function tabSelected(eid,selected)
{
	if(selected==undefined)return false;
	var tabList=$(eid).getElementsByTagName('li');
	for(var i=0;i<tabList.length;i++)
	{
		if(typeof selected=='string')tabList[i].className=tabList[i].getAttribute('name')!=selected?'':'selected';
		if(typeof selected=='number')tabList[i].className=i!=selected-1?'':'selected';
	}
}
//全选_parentId标签内所有的多选框
function selectAllCheckBox(_parentId)
{
	var _inputList = $(_parentId).getElementsByTagName('input');
	for (i = 0 ; i < _inputList.length ; i++)
	{
		if(_inputList[i]['type'].toLowerCase() == 'checkbox' && _inputList[i].checked == false)
		{
			_inputList[i].checked = true;
		}
	}
}
//反选_parentId标签内所有的多选框
function unselectAllCheckBox(_parentId)
{
	var _inputList = $(_parentId).getElementsByTagName('input');
	for (i = 0 ; i < _inputList.length ; i++ )
	{
		if(_inputList[i].type.toLowerCase() == 'checkbox')
		{
			_inputList[i].checked = (_inputList[i].checked) ? false : true;
		}
	}
}
/***********************狐狸负责 结束******************

/***********************其它 开始******************/
//拷贝剪贴板
function CopyText(id) {
	try{
		var targetText = $(id);
		targetText.focus();
		targetText.select();
		var clipeText = targetText.createTextRange();
		clipeText.execCommand("Copy");
		alert("内容已经成功复制!");
	}catch(e){
		alert("您的浏览器不支持自动复制,请按 Ctrl+C 手工复制内容,谢谢!");
	}
}
//头部搜索
function postSearch() {
	var nav_search = $("nav_search_form");
	if(nav_search.elements.keywords.value.trim().length<2){
		alert("必须在两个字以上");
		return false;
	}
	var typeSelected =  $("nav_search_target").value;
	if (typeSelected=='disease') {
		nav_search.action="?mod=disease_search&code=do"; 
	} else if (typeSelected=='hospital') {
		nav_search.action="?mod=hospital&code=list"; 
	}
	else if (typeSelected=='question') {
		nav_search.action="?mod=question&code=list"; 
	}
	else if (typeSelected=='test') {
		nav_search.action="?mod=test&code=list"; 
	}
	else if (typeSelected=='news') {
		nav_search.action="?mod=news_search&code=do"; 
	}
	else if (typeSelected=='drug') {
		nav_search.action="?mod=drug&code=list"; 
	}
	else
	{
		nav_search.action="?mod=news_search&code=do"; 
	}
}

//取得字符串长度
String.prototype.lenB = function() {
    var cArr = this.match(/[^\x00-\xff]/ig);
    return this.length + (cArr == null ? 0 : cArr.length);
}
//去除字符串两侧空格
String.prototype.trim = function() 
{ 
	return this.replace(/(^\s*)|(\s*$)/g, ""); 
}
/***********************其它 结束******************/

/*********页面框架*************/
var win_w, win_h, win_reload;
var objBody, objDivOverlay, objDivWindow, objDivLoad;

function win_open_in(thistitle, thislink, winwidth, winheight, reload) {
	var t = thistitle || null;
	win_show(t, thislink, winwidth, winheight, reload);
	return false;
}

function win_open(thislink, winwidth, winheight, reload) {
	var t = thislink.title || thislink.name || thislink.innerHTML || null;
	var url=thislink.href;
	win_show(t, url, winwidth, winheight, reload);
	return false;
}

function win_show(caption, url, winwidth, winheight, reload) {
	if(typeof(rewriteArgSeparator)!='undefined' && url.indexOf('?')===-1)
	{
		var url=url+rewriteArgSeparator+"in_frame"+rewriteVarSeparator+"1";
	}
	else
	{
		var url=url.indexOf('?')===-1?url+"?in_frame=1":url+"&in_frame=1";
	}
	try {
		win_w = winwidth + 30;
		win_h = winheight + 40;
		win_reload = reload;
		objBody = document.getElementsByTagName("body").item(0);
		objDivOverlay = document.createElement("div");
		objDivOverlay.setAttribute('id', 'Win_Overlay');
		objDivOverlay.onclick = win_remove;
		objBody.appendChild(objDivOverlay);
		objDivWindow = document.createElement("div");
		objDivWindow.setAttribute('id', 'Win_Window');
		objBody.appendChild(objDivWindow);
		win_overlay_size();
		var objImage = document.createElement("img");
		objImage.setAttribute('src', './templates/default/images/loading.gif');
		objDivLoad = document.createElement("div");
		objDivLoad.setAttribute('id', 'Win_Load');
		objDivLoad.appendChild(objImage);
		objBody.appendChild(objDivLoad);
		win_load_position();
		var objDivWindowTitle = document.createElement("div");
		objDivWindowTitle.setAttribute('id', 'Win_WindowTitle');
		if (caption == null) caption = "";
		objDivWindowTitle.appendChild(document.createTextNode(caption));
		var objACloseWindowButton = document.createElement("a");
		objACloseWindowButton.setAttribute('id', 'Win_CloseWindowButton');
		objACloseWindowButton.setAttribute('href', '#');
		objACloseWindowButton.setAttribute('title', '关闭');
		objACloseWindowButton.onclick = win_remove;
		objACloseWindowButton.appendChild(document.createTextNode('关闭'));
		var objDivCloseWindow = document.createElement("div");
		objDivCloseWindow.setAttribute('id', 'Win_CloseWindow');
		objDivCloseWindow.appendChild(objACloseWindowButton);
		var objDivTitle = document.createElement("div");
		objDivTitle.setAttribute('id', 'Win_Title');
		objDivTitle.appendChild(objDivWindowTitle);
		objDivTitle.appendChild(objDivCloseWindow);
		var objIframeContent = document.createElement("iframe");
		objIframeContent.setAttribute('id', 'Win_IframeContent');
		objIframeContent.setAttribute('name', 'Win_IframeContent');
		objIframeContent.setAttribute('src', url);
		objIframeContent.setAttribute('frameborder', 0);
		objIframeContent.setAttribute('hspace', 0);
		objIframeContent.style.width = winwidth + 29 + 'px';
		objIframeContent.style.height = winheight + 12 + 'px';
		objIframeContent.onload = win_show_iframe();
		objDivWindow.appendChild(objDivTitle);
		objDivWindow.appendChild(objIframeContent);
		win_position();
		if (frames['Win_IframeContent'] == undefined) {
			$("Win_Load").parentNode.removeChild($("Win_Load"));
			objDivWindow.style.display = "block";
		}
		window.onscroll = win_position;
		window.onresize = win_position;
		document.onkeyup = function(e){ 	
			if (e == null) {
				keycode = event.keyCode;
			} else {
				keycode = e.which;
			}
			if (keycode == 27) {
				win_remove();
			}
		}
	} catch(e) {
		alert(e);
	}
}

function win_show_iframe(){
	$("Win_Load").parentNode.removeChild($("Win_Load"));
	objDivWindow.style.display = "block";
}
function win_remove() {
	if (win_reload == 1) {
		window.location.reload();
	} else {
		$("Win_Window").parentNode.removeChild($("Win_Window"));
		$("Win_Overlay").parentNode.removeChild($("Win_Overlay"));
	}
	return false;
}

function win_position() {
	var pagesize = win_get_page_size();
	var arrayPageScroll = win_get_page_scrolltop();
	objDivWindow.style.width = win_w + "px";
	objDivWindow.style.left = (arrayPageScroll[0] + (pagesize[0] - win_w) / 2) + "px";
	objDivWindow.style.top = (arrayPageScroll[1] + (pagesize[1] - win_h) / 2) + "px";
}

function win_overlay_size(){
	var xScroll, yScroll;
	if (window.innerHeight && window.scrollMaxY) {	
		xScroll = document.body.scrollWidth;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight) {
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	} else {
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}
	var windowWidth, windowHeight;
	if (self.innerHeight) {
		windowWidth = self.innerWidth;
		windowHeight = self.innerHeight;
	} else if (document.documentElement && document.documentElement.clientHeight) {
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	} else if (document.body) {
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}
	if (yScroll < windowHeight){
		pageHeight = windowHeight;
	} else { 
		pageHeight = yScroll;
	}
	if (xScroll < windowWidth){	
		pageWidth = windowWidth;
	} else {
		pageWidth = xScroll;
	}
  	objDivOverlay.style.height = pageHeight + "px";
  	objDivOverlay.style.width = pageWidth + "px";
}

function win_load_position() {
	var pagesize = win_get_page_size();
	var arrayPageScroll = win_get_page_scrolltop();
	objDivLoad.style.left = (arrayPageScroll[0] + (pagesize[0] - 100) / 2) + "px";
	objDivLoad.style.top = (arrayPageScroll[1] + (pagesize[1] - 100) / 2) + "px";
	objDivLoad.style.display = "block";
}

function win_get_page_scrolltop(){
	var yScrolltop;
	var xScrollleft;
	if (self.pageYOffset || self.pageXOffset) {
		yScrolltop = self.pageYOffset;
		xScrollleft = self.pageXOffset;
	} else if (document.documentElement && document.documentElement.scrollTop || document.documentElement.scrollLeft ) {
		yScrolltop = document.documentElement.scrollTop;
		xScrollleft = document.documentElement.scrollLeft;
	} else if (document.body) {
		yScrolltop = document.body.scrollTop;
		xScrollleft = document.body.scrollLeft;
	}
	arrayPageScroll = new Array(xScrollleft, yScrolltop);
	return arrayPageScroll;
}

function win_get_page_size() {
	var de = document.documentElement;
	var w = window.innerWidth || self.innerWidth || (de&&de.clientWidth) || document.body.clientWidth;
	var h = window.innerHeight || self.innerHeight || (de&&de.clientHeight) || document.body.clientHeight;
	arrayPageSize = new Array(w, h);
	return arrayPageSize;
}

function copyRight(version)
{
	var url="http://www.jishigou.net";
	var name="JishiGou";
	var company="Cenwor Inc.";
	document.writeln("Powered by <strong>");
	document.writeln("<a href=\""+url+"\" target=\"_blank\">"+name+"</a></strong>");
  	document.writeln("<em>v"+version+"</em>&nbsp;&copy; 2005 - 2012 "+company+"&nbsp;");
}

////显示隐藏媒体
//function addMediaAction(div) {
//	var thediv = $(div);
//	if(thediv) {
//		var medias = thediv.getElementsByTagName('kbd');
//		if(medias) {
//			for (i=0;i<medias.length;i++) {
//				if(medias[i].className=='showvideo' || medias[i].className=='showflash'|| medias[i].className=='showreal') {
//					medias[i].onclick = function() {showmedia(this,400,400)};
//				}
//			}
//		}
//	}
//}
//function showmedia(Obj, mWidth, mHeight) {
//	var mediaStr, smFile;
//	if ( Obj.tagName.toLowerCase()=='a' ) { smFile = Obj.href; } else { smFile = Obj.title; }
//	var smFileType = Obj.className.toLowerCase();
//
//	switch(smFileType){
//		case "showflash":
//			mediaStr="<p style='text-align: right; margin: 0.3em 0; width: 520px;'>[<a href='"+smFile+"' target='_blank'>全屏观看</a>]</p><object codeBase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0' classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' width='520' height='390'><param name='movie' value='"+smFile+"'><param name='quality' value='high'><param name='AllowScriptAccess' value='never'><embed src='"+smFile+"' quality='high' pluginspage='http://www.macromedia.com/go/getflashplayer' type='application/x-shockwave-flash' width='520' height='390'></embed></OBJECT>";
//			break;
//		case "showvideo":
//			mediaStr="<object width='520' classid='CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6'><param name='url' value='"+smFile+"' /><embed width='520' type='application/x-mplayer2' src='"+smFile+"'></embed></object>";
//			break;
//		case "showreal":
//			mediaStr="<object classid='clsid:CFCDAA03-8BE4-11CF-B84B-0020AFBBCCFA' width='520' height='390' id='RealMoviePlayer' border='0'><param name='_ExtentX' value='13229'><param name='_ExtentY' value='1058'><param name='controls' value='ImageWindow,controlpanel'><param name='AUTOSTART' value='1'><param name='CONSOLE' value='_master'><param name='SRC' value='"+smFile+"'><EMBED SRC='"+smFile+"' WIDTH='520' type='audio/x-pn-realaudio-plugin'  HEIGHT='390' NOJAVA='true' CONTROLS='ImageWindow,controlpanel' AUTOSTART='true' REGION='newsregion' CONSOLE='one'></EMBED></object>";
//	}
//	
//	var mediaDiv = document.getElementById(escape(smFile.toLowerCase()));
//	
//	if (mediaDiv) {
//		Obj.parentNode.removeChild(mediaDiv);
//	} else {
//		mediaDiv = document.createElement("div");
//		mediaDiv.style.cssText = "text-align:center;text-indent:0"; 
//		mediaDiv.id = escape(smFile.toLowerCase());
//		mediaDiv.innerHTML = mediaStr;
//		Obj.parentNode.insertBefore(mediaDiv,Obj.nextSibling);
//	}
//	return false;
//}
