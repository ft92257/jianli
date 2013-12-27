/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Package JishiGou $
 *
 * @Filename combobox.js $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:36 1648213825 1844019060 4207 $
 *******************************************************************/


/**
 * 组合框控件
 * 使用或者二次开发请保留作者信息
 *
 * @author     ~ZZ~<505171269@qq.com>
 * @version	   v1.0 $Date 2011-07-20
 */
 
ComboBoxManager = {
	container:Array(),
	create:function(id) {
		var comboboxHandler = new ComboBox(id);
		this.set(id, comboboxHandler);
		return comboboxHandler;
	},
	get:function(id) {
		return this.container[id];
	},
	set:function(id, val) {
		this.container[id] = val;
	}
};

ComboBox = function(id) {
	this.id = id;
	this.init();
}

ComboBox.prototype = {
	id:'null',
	combobox:'null',
	selectIndex:0,		//默认第一个选中
	currentOption:null,
	currentVal:null,
	optionLimit:5,		//option列表显示限制,默认10条
	init:function() {
		//初始化组合框
		this.combobox = $('<dt><div style="overflow:auto;zoom:1"><span id="__selected_option_'+this.id+'" class="m"></span><span id="__allow_'+this.id+'" class="icon"></span></div></dt>');
		$('#'+this.id+' dd').before(this.combobox);
		
		//获取selectIndex选项
		var iCount = 0;
		var self = this;
		$('#'+this.id+' dd ul li').each(function(){
			//如果没有设定selected则默认是第一个选中，否则是最后一个选择
			if (iCount == self.selectIndex) {
				$('#__selected_option_'+self.id).html($(this).html());
				self.currentOption = this;
				self.currentVal = $(this).find('span[class="value"]').html();
				//$(this).hide();
			}
			
			var s = $(this).attr("selected");
			if (typeof s != 'undefined') {
				if (s.toLowerCase() == 'selected') {
					$('#__selected_option_'+self.id).html($(this).html());
					self.currentOption = this;
					self.currentVal = $(this).find('span[class="value"]').html();
				}
			}
			iCount++;
		});
		this.setOptionListWidth();
		
		if (iCount > this.optionLimit) {
			var optionHeight = this.getOptionListHeight() / iCount;
			this.setOptionListHeight(optionHeight * this.optionLimit);
		}

		//添加鼠标经过事件
		$(this.combobox.get(0)).mouseover(function(){
			$("#__allow_"+self.id).css({'background-position':'0 -16px'});
		});
		
		$(this.combobox.get(0)).mouseout(function(){
			$("#__allow_"+self.id).css({'background-position':'0 0'});
		});
		
		//鼠标点击事件
		$(this.combobox.get(0)).click(function(){
			if ($('#'+self.id+' dd ul').is(":hidden")) {
				$('body').one('click',function(){
					$('#'+self.id+' dd ul').hide();
				});
				$('#'+self.id+' dd ul').show();
				self.brightOption(self.currentOption);
			} else {
				$('#'+self.id+' dd ul').hide();
			}
			return false;
    	});
		
		//列表项点击事件
		$('#'+this.id+' dd ul li').click(function(){
			var itemContent = $(this).html();
			$('#__selected_option_'+self.id).html(itemContent);
			$(self.currentOption).show();
			//$(this).hide();
			self.currentOption = this;
			self.currentVal = $(this).find('span[class="value"]').html();
			self.change();
		});
		
		//设置列表鼠标经过高亮
		$('#'+this.id+' dd ul li').mouseover(function(){
			self.brightOption(this);
		});
	},
	
	//高亮选中项目
	brightOption:function(c, color){
		if (typeof color == 'undefined') {
			var color = "#609adb";
		}
		$('#'+this.id+' dd ul li').each(function(){
			$(this).css({"background-color":"#fff"});
		});
		$(c).css({"background-color":color});
	},
	
	//获取当前选中的值
	val:function() {
		return this.currentVal;
	},
	
	//设定options列表高
	setOptionListHeight:function(h) {
		h = h+'px';
		$('#'+this.id+' dd ul').css({'height':h});
	},
	
	//获取option的高
	getOptionListHeight:function() {
		var h = $('#'+this.id+' dd ul').height();
		return h;
	},
	
	//设定列表框宽
	setComboBoxWidth:function(w) {
		w = w+'px';
		$('#'+this.id).css({'width':w});
		this.setOptionListWidth();
	},
	
	//设定选项列表的宽
	setOptionListWidth:function() {
		var padding = parseInt($(this.combobox.get(0)).css('padding-left')) + parseInt($(this.combobox.get(0)).css('padding-right'));
		$('#'+this.id+' dd ul').width($(this.combobox.get(0)).width() + padding);
	},
	
	//设置option列表可显示条数
	setOptionLimit:function(limit) {
		this.optionLimit = limit;
	},
	
	//从列表移除一个元素
	removeOption:function(li){
		$(li).remove();
	},
	
	//选择项改变回调
	change:function() {
	}
};