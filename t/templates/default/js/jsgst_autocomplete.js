/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Package JishiGou $
 *
 * @Filename jsgst_autocomplete.js $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:36 1535496469 1658952230 7226 $
 *******************************************************************/


/**
 * 微博输入框自动提示
 * 
 * @author 		~ZZ~
 * @package 	jishigou.net
 * @category	Publish
 * @version		v1.0 $Date: 2011-05-16
 */
var __JSGST_AUTO_CACHE__ = new Array();
function JSGST_Autocompleter() 
{
   var jsgst_auto = new Object();
   jsgst_auto.item_list_tips = '';
   jsgst_auto.handle_key = '_searchResult_';
   jsgst_auto.key =  0;
   jsgst_auto.selectCallback = function() {};
   jsgst_auto.filterSearchResultCallback = null;
   jsgst_auto.formatItemCallback = null;
   jsgst_auto.setItemIdCallback = null;
   jsgst_auto.resultCallback = null;
   jsgst_auto.url = '';

   jsgst_auto._selectCallback =  function(event) {
	   var option = jsgst_auto.itemList().find('li.active');
	   if (jsgst_auto.resultCallback != null) {
		   	jsgst_auto.itemList().hide();
		    var name = jsgst_auto.resultCallback(__JSGST_AUTO_CACHE__[option.attr('id')]);
			var item_name = {code:option.attr('id'),name:name};
			jsgst_auto.selectCallback[jsgst_auto.focusingElement](item_name);
	   }
  };
  
   jsgst_auto.itemList =  function() {
      if ($('#'+jsgst_auto.handle_key).length > 0) {
      	return $('#'+jsgst_auto.handle_key);
      }
	  //<div>想用@提到谁？</div>
      var list = $('<div>').addClass('quicksearchbar').attr('id',jsgst_auto.handle_key).hide().html(
        jsgst_auto.item_list_tips+'<ul class="stocks">' +
        '</ul>'
        ).appendTo(document.body);
      list.find('.min_btn').click(function(){
        list.hide();
      });
      return list;
  };
  
   	jsgst_auto.searchItems = function(code,pos, el_id) {
    jsgst_auto.focusingElement = el_id;
    jsgst_auto.itemList().hide().find('ul').empty();
    if (code === '') {
      return;
    }
	
	//获取查询字符串的第一个字符
    var firstChar = code;//.substr(0,1);
	
	//缓存中是否存在第一个字符
    if (jsgst_auto.itemList().data(firstChar)) {

      var filterResult = jsgst_auto.filterSearchResult(jsgst_auto.itemList().data(firstChar), code);
	  
	  //过滤结果集不存在就隐藏
      if (filterResult.length === 0) {
        jsgst_auto.itemList().hide();
        return;
      }
	  //设置当前选中项
      jsgst_auto.setSearchResult(filterResult);
      jsgst_auto.itemList().css('left', pos.left).css('top', pos.top).show();
      return;
    }
	
    jsgst_auto.searchingCode = code;
	
	//查询字符的首字符
    if (jsgst_auto.searchingChar && jsgst_auto.searchingChar === firstChar) {
      return;
    } else {
      jsgst_auto.searchingChar = firstChar;
    }
	
    jsgst_auto.searchingCode = code;
	var ret = null;
	$.get(jsgst_auto.url, {"q":jsgst_auto.searchingCode}, function(result){
	  result = $.trim(result);
      if (result === '') {
        return null;
      }
      
      var data = [];
      var rows = result.split("\n");
      var row;
      for (var i = 0; i < rows.length; i++) {
        if ($.trim(rows[i]) === '') {
          continue;
        }
		//用::分隔
        row = rows[i].split('|');
        if (!row || row.length === 0) {
          continue;
        }
        data[data.length] = row;
      }
	  ret = data;
      jsgst_auto.itemList().find('ul').empty();
      if (!ret || ret.length === 0) {
        jsgst_auto.itemList().hide();
        return;
      }
	  
      $(ret).map(function(){
        this[0] = this[0].replace('（','(').replace('）',')');
        return this;
      });
	  
	  //将查询结果缓存
      jsgst_auto.itemList().data(firstChar, ret);
	  
	  //查询结果过滤
	  var filterResult = jsgst_auto.filterSearchResult(ret, jsgst_auto.searchingCode);
      
	  jsgst_auto.setSearchResult(filterResult);
	  if (filterResult && filterResult.length > 0) {
      	jsgst_auto.itemList().css('left', pos.left).css('top', pos.top).show();
      }						   
	});
  };
  
  //对返回结果进行处理
   jsgst_auto.filterSearchResult =  function (result, code) {
	 if (jsgst_auto.filterSearchResultCallback == null) {
		var ret = [];
		var len = code.length;
		$(result).each(function(){
		  if (this[1].substr(0, len).toUpperCase() == code.toUpperCase() || $.trim(this[0]).substr(0,len) == code || $.trim(this[2]).substr(0,len).toUpperCase() == code.toUpperCase() || $.trim(this[3]).substr(0,len) == code) {
			ret.push(this);
		  } 
								
		});
	
		return ret;
	  } else {
	  	jsgst_auto.filterSearchResultCallback(result, code);
	  }
  };
  
  //移动选项
   jsgst_auto.moveSelectedItem = function(index) {
	   var list = jsgst_auto.itemList().find("li");
	   var activeLi = 0;
	   for (i=0;i<list.length;++i) {
		   if ($(list[i]).attr("class") == 'active') {
			   activeLi = i;
		   }
	   }
	   if (jsgst_auto.key) {
		   activeLi += index;
		   if (activeLi >= jsgst_auto.key || activeLi < 0) {
			   activeLi -= index;
		   }
		}
    	list.css({ 'background-color':'', 'color':'' }).removeClass('active');
    	$(list[activeLi]).addClass('active');
  };

  //设置选中项
  jsgst_auto.setSearchResult = function (result) {
    result = result.slice(0,10);
    var list = jsgst_auto.itemList().find('ul');
    jsgst_auto.key = 0;
    $(result).each(function(i){
	  __JSGST_AUTO_CACHE__[jsgst_auto.handle_key+i+'__'] = this;
      var o = $('<li>').attr('id',jsgst_auto.handle_key+i+'__');
	  
	  //数据显示格式
	  if (jsgst_auto.formatItemCallback != null) {
	  	o.html(jsgst_auto.formatItemCallback(this));
	  } else {
	  	o.html('<span>'+this[0]+'</span>');
	  }
	  
	  //选中样式
	  o.mouseover(
        function(event){
          jsgst_auto.itemList().find('li').removeClass('active');
          o.addClass('active');
        }
      ).click(function(event){
          jsgst_auto._selectCallback(event);
      });
	  
      if (jsgst_auto.key === 0){
          o.addClass('active');
      }
      list.append(o);
      jsgst_auto.key ++;
    });
  };

  //鼠标按下事件侦听
   jsgst_auto.keydownListener = function(event) {
    if (jsgst_auto.itemList() && jsgst_auto.key) {
      switch (event.keyCode) {
        case 27:
        case 32:
            jsgst_auto.hideItemList();
            break;
        case 38:			//按下up键
            if (jsgst_auto.itemList().is(":visible")) {
              event.preventDefault();
            }
            jsgst_auto.moveSelectedItem(-1);
            break;
        case 40:			//按下down键
            if (jsgst_auto.itemList().is(":visible")) {
              event.preventDefault();
            }
            jsgst_auto.moveSelectedItem(1);
            break;
        case 13:			//按下回车键
            if (jsgst_auto.itemList().is(":visible")) {
              event.preventDefault();
              jsgst_auto._selectCallback(event);
            }
            break;
      }
    }
  };

  jsgst_auto.setSelectCallback = function(el_name, func) {
    jsgst_auto.selectCallback[el_name] = func;
  };

   jsgst_auto.showItemList = function() {
    clearTimeout(jsgst_auto.hideItemListTimer);
    jsgst_auto.itemList().show();
  };
  
  //隐藏查询列表
 jsgst_auto.hideItemList = function() {
    jsgst_auto.hideItemListTimer = setTimeout(function(){jsgst_auto.itemList().hide();},300);
  };
  return jsgst_auto;
 }
