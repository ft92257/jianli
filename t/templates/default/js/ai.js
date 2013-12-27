/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Package JishiGou $
 *
 * @Filename ai.js $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:36 2039485469 1477252304 11508 $
 *******************************************************************/


/**
 * 智能输入提示相关的函数库
 * 话题智能提示和At智能提示代码有点类似，但是要统一还是有点麻烦，所以等以后有时间再统一，暂时先这样了。
 * 使用或者二次开发请保留作者信息
 *
 * @author     ~ZZ~<505171269@qq.com>
 * @version	   v1.0 $Date 2011-08-10
 */

//初始化智能输入框
function initAiInput(handle_key, options)
{
	
	if (isUndefined(options)) {
		var options = {};
	}
	
	var at = new At(handle_key);
	var topic = new Topic(handle_key);
	
}

/**智能At提示**/
At = function(handle_key, options) {
	if (isUndefined(options)) {
		var options = {};
	}
	this.statusContentTextBox = $('#'+handle_key);
	if (options.itemListTips) {
		this.itemListTips = options.itemListTips;
	}
	this.init();
}

At.prototype = {
	statusContentTextBox:null,
	itemListTips:"<div class='list_tips'>想用@提到谁？</div>",
	atAutocompleter:null,
	atUrl:'ajax.php?mod=misc&code=atuser',
	
	atResult:function(data) {
		return data[0];
	},
	
	//初始化只能At Tips
	init:function(){
		this.atAutocompleter = Autocompleter('__at_search_result__', this.atUrl, {resultCallback:this.atResult,item_list_tips:this.itemListTips});
		
		//绑定输入框与提示控件
		var self = this;
		this.statusContentTextBox.keyup(function(event){
			self.searchAtInStatus(self.statusContentTextBox, event);
		}).keydown(self.atAutocompleter.keydownListener)
		  .click(function(event){self.searchAtInStatus(self.statusContentTextBox, event);})
		  .blur(function(event){
			var lastEditIndex = self.statusContentTextBox.data('atLastEditIndex');
			if (lastEditIndex != null) {
				self.selectFirstAtFromSearchResultList(self.statusContentTextBox,true);
			}
		  });
	},
	insertAt:function(at, atIndex, contentTextBox){
		var startIndex = contentTextBox.getSelectionStart();
		var atLastEditIndex = contentTextBox.data('atLastEditIndex');
		contentTextBox.setSelection(atLastEditIndex,startIndex - atLastEditIndex);
		contentTextBox.insertString(['',$.trim(at.name),' '].join(''));
	},
	searchAtInStatus:function(handle, event){
		var contentTextBox = handle;
		//获取当前光标的位置
		var startIndex = contentTextBox.getSelectionStart();
		if (event.type == 'keyup' && event.keyCode == 50 && contentTextBox.val().substr(startIndex-1, 1) === '@') {
			contentTextBox.data('atLastEditIndex',startIndex);
			return ;
		}
		
		if (contentTextBox.data('atLastEditIndex')) {
			var lastEditIndex = contentTextBox.data('atLastEditIndex');
			var content = contentTextBox.val().toString(); 
			
			if (this.atAutocompleter.itemList() && (event.keyCode == 38 || event.keyCode == 40 || event.keyCode == 13 || event.keyCode == 27)) {
				return;
			}
			
			var atuser = content.substr(lastEditIndex, startIndex - lastEditIndex);
			if (atuser == '') {
			  this.atAutocompleter.hideItemList();
			  return;
			}
			
			this.atAutocompleter.searchItems(atuser, this.getAtCaretPos(contentTextBox, contentTextBox.val()), contentTextBox.attr("id"));
			var self = this;
			this.atAutocompleter.setSelectCallback(contentTextBox.attr("id"),function(atuser){
				self.insertAt(atuser, startIndex, contentTextBox);
				contentTextBox.data('atLastEditIndex', null);
			});
		}
	},
	getAtCaretPos:function(textbox, content){
		var em = $("<em>&nbsp;</em>");
		var boxPos = textbox.offset();
		var cursorPos = {};
		if ($("#at_caret").length === 0) {
			caret = $("<pre></pre>").attr("id", 'at_caret').css({
			position: 'absolute',
			left: -9999,
			font: '12px/20px "Helvetica Neue", Helvetica, Arial',
			width: textbox.width() + 'px',
			border: '1px',
			"word-wrap": "break-word"
		  });
		  caret.appendTo("body");
		}
		caret.html(content.substr(0, content.length-1)).append(em);
		cursorPos = em.position();
		var res =  {
		  left: cursorPos.left + boxPos.left,
		  top: cursorPos.top + boxPos.top + 20
		};
		return res;
	},
	selectFirstAtFromSearchResultList:function(contentTextBox,delay){
		var self = this;
		var _insertAt = function(){
		if (!self.atAutocompleter.itemList().is(':hidden') && self.atAutocompleter.itemList().find('ul li').length) {
			var startIndex = contentTextBox.getSelectionStart();
			self.atAutocompleter.setSelectCallback(contentTextBox.attr("id"), function(at){
				self.insertAt(at, startIndex, contentTextBox);
			});
			self.atAutocompleter.itemList().find('li:first').click();
		  }
		}
		if (delay) {
		  setTimeout(_insertAt,100);
		} else {
		  _insertAt();
		}
	}
};

/**话题智能提示**/
Topic = function(handle_key, options) {
	if (isUndefined(options)) {
		var options = {};
	}
	this.statusContentTextBox = $('#'+handle_key);
	if (options.itemListTips) {
		this.itemListTips = options.itemListTips;
	}
	this.init();
}

Topic.prototype = {
	statusContentTextBox:null,
	defaultTopicText:'插入自定义话题',
	repostStatusContentTextBox:null,
	dmTextBox:null,
	topicAutocompleter:null,
	itemListTips:"<div class='list_tips'>想要插入什么话题？</div>",
	tUrl:"ajax.php?mod=misc&code=tag",
	tFormatItem:function(data) {
		return '<span id="'+data[0]+'_st_name">'+data[1]+'</span>';	
	},
	tResult:function(data) {
		return data[1];
  	},
	init:function(){
		this.repostStatusContentTextBox = $('#repostStatusTextBox');
		this.dmTextBox = $("#dmTextBox");
		this.topicAutocompleter = Autocompleter('__st_search_result__', this.tUrl, {formatItemCallback:this.tFormatItem,resultCallback:this.tResult,item_list_tips:this.itemListTips});
		
		var self = this;
		//添加监视事件
		this.statusContentTextBox.add(this.repostStatusContentTextBox)
		.add(this.dmTextBox).keyup(function(event){self.searchTopicInStatus(event);})		//按键弹起时#符号检查
		.keydown(self.topicAutocompleter.keydownListener)									//按键按下时的事件函数
		.click(function(event){self.searchTopicInStatus(event);}).blur(function(event){
    		var contentTextBox = self.statusContentTextBox.get(0) == event.target  ? self.statusContentTextBox : (self.repostStatusContentTextBox.get(0) == event.target ? self.repostStatusContentTextBox : self.dmTextBox);
    		var lastEditIndex = contentTextBox.data('lastEditIndex');
			if (lastEditIndex != null) {
				//self.selectFirstTopicFromSearchResultList(contentTextBox,true);
				self.topicAutocompleter.hideItemList();
			}
  		});
	},
	insertTopic:function(stock, stockIndex, contentTextBox) {
		var startIndex = contentTextBox.getSelectionStart();
		var range = JSGST.API.Statuses.getEditTopicRange(startIndex, contentTextBox.val());
		contentTextBox.setSelection(range.start,range.end-range.start);
		contentTextBox.insertString(['#',$.trim(stock.name),'#'].join(''));
	},
	autoInsertTopic:function(event) {
		var contentTextBox = this.statusContentTextBox.get(0) == event.target  ? this.statusContentTextBox : (this.repostStatusContentTextBox.get(0) == event.target ? this.repostStatusContentTextBox : this.dmTextBox);
		var lastEditIndex = contentTextBox.data('lastEditIndex');
		if (lastEditIndex !== null) {
			var startIndex = contentTextBox.getSelectionStart();
			var range = JSGST.API.Statuses.getEditTopicRange(startIndex, contentTextBox.val());
			var lastRange = JSGST.API.Statuses.getEditTopicRange(lastEditIndex, contentTextBox.val());
		  	if ((range.start != lastRange.start && range.end != lastRange.end) || startIndex == lastRange.start || startIndex == lastRange.end) {
				//contentTextBox.setSelection(lastRange.start+1,lastRange.end - lastRange.start);
				//this.selectFirstTopicFromSearchResultList(contentTextBox,false);
				this.topicAutocompleter.hideItemList();
				return false;
				/*
				range = JSGST.API.Statuses.getEditTopicRange(lastRange.start+1, contentTextBox.val());
				var indexOffset = 0;
				if (startIndex <= lastRange.start) {
			  		indexOffset = startIndex;
				} else {
			  		indexOffset = startIndex + range.end - lastRange.end;
				} 
				contentTextBox.setSelection(indexOffset,0);
				*/
		  }
		}
  	},
	searchTopicInStatus:function(event) {
    	var contentTextBox = this.statusContentTextBox.get(0) == event.target  ? this.statusContentTextBox : (this.repostStatusContentTextBox.get(0) == event.target ? this.repostStatusContentTextBox : this.dmTextBox);
    	var lastEditIndex = contentTextBox.data('lastEditIndex');
		
		//检查缓存中的变量
		if (contentTextBox.data('lastEditIndex')) {
			this.autoInsertTopic(event);
		}
	
		contentTextBox.data('lastEditIndex',null);
	
		//获取当前光标的位置,51是#号的KeyCode
		var startIndex = contentTextBox.getSelectionStart();
		if (event.type == 'keyup' && event.keyCode == 51 && contentTextBox.val().substr(startIndex-1, 1) === '#') {
			contentTextBox.insertString(this.defaultTopicText+'#');
			contentTextBox.setSelection(startIndex,this.defaultTopicText.length);
			return;
		}
		
		if (this.topicAutocompleter.itemList() && (event.keyCode == 38 || event.keyCode == 40 || event.keyCode == 13 || event.keyCode == 27)) {
			return;
		}
	
		//当前#**#内的字符串
		var topic = JSGST.API.Statuses.getEditTopic(startIndex, contentTextBox.val());
		
		//当#**#为空的时候就隐藏查询列表
		if (topic == '') {
			contentTextBox.data('lastEditIndex',null);
			this.topicAutocompleter.hideItemList();
			return;
		}
	
		//设置最后编辑的位置
		contentTextBox.data('lastEditIndex',startIndex);
	
		//查询下拉框
		this.topicAutocompleter.searchItems(topic, this.getCaretPos(contentTextBox, contentTextBox.val()), contentTextBox.attr("id"));
		
   		var self = this;
		this.topicAutocompleter.setSelectCallback(contentTextBox.attr("id"),function(topic){
			self.insertTopic(topic, startIndex, contentTextBox);
		});
	},
	getCaretPos:function(textbox, content) {
		var em = $("<em>&nbsp;</em>");
		var boxPos = textbox.offset();
		var cursorPos = {};
		if ($("#caret").length === 0) {
			caret = $("<pre></pre>").attr("id", 'caret').css({
			position: 'absolute',
			left: -9999,
			font: '12px/20px "Helvetica Neue", Helvetica, Arial',
			width: textbox.width() + 'px',
			border: '1px',
			"word-wrap": "break-word"
		  });
		  caret.appendTo("body");
		}
		caret.html(content.substr(0, content.length-1)).append(em);
		cursorPos = em.position();
		var res =  {
			left: cursorPos.left + boxPos.left,
			top: cursorPos.top + boxPos.top + 20
		};
		return res;
	},
	insertTopicToStatus:function(event){
		var startIndex = -1;
		var contentTextBox = event.target.id =='addTopicToStatusButton' ? this.statusContentTextBox : ('addTopicToRepostButton' == event.target.id ? this.repostStatusContentTextBox : this.dmTextBox);
		startIndex = contentTextBox.val().indexOf('#'+this.defaultTopicText+'#');
		if (startIndex < 0) {
			startIndex = contentTextBox.getSelectionStart();
			contentTextBox.insertString('#'+this.defaultTopicText+'#');
		}
		contentTextBox.setSelection(startIndex + 1, this.defaultTopicText.length);
	},
	selectFirstTopicFromSearchResultList:function(contentTextBox,delay) {
		var self = this;
		var _insertTopic = function(){
			if (!self.topicAutocompleter.itemList().is(':hidden') && self.topicAutocompleter.itemList().find('ul li').length) {
				var startIndex = contentTextBox.getSelectionStart();
				self.topicAutocompleter.setSelectCallback(contentTextBox.attr("id"), function(stock){
					self.insertTopic(stock, startIndex, contentTextBox);
				});
				self.topicAutocompleter.itemList().find('li:first').click();
			}
		};
		if (delay) {
			setTimeout(_insertTopic,100);
		} else {
			_insertTopic();
		}
	}
};