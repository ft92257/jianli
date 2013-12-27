/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Package JishiGou $
 *
 * @Filename dialog.js $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:36 2144800777 1269941602 14573 $
 *******************************************************************/


/**
 * 对话框控件
 * 使用或者二次开发请保留作者信息
 *
 * @author     ~ZZ~<505171269@qq.com>
 * @version	   v1.0 $Date 2011-07-25
 */
 
__DIALOG_WRAPPER__ = {};
__ScreenLocker_HandleKey__ = null;

/* IE6有个Bug，如果不给定对话框的宽度的话，在IE6下，对话框将以100%宽度显示 (如果指定了对话框加载内容的width，可以正常显示)*/
DialogManager = {
    'create' : function(id){
        var d = {};
        if (!__DIALOG_WRAPPER__[id]) {
            d = new Dialog(id);
            __DIALOG_WRAPPER__[id] = d;
        } else {
            d = DialogManager.get(id);
        }
        return d;
    },
    'get' : function(id){
        return __DIALOG_WRAPPER__[id];
    },
    'close' : function(id){
		if (__DIALOG_WRAPPER__[id]) {
			if (__DIALOG_WRAPPER__[id].close()) {
				__DIALOG_WRAPPER__[id] = null;
			}
			return true;
		} else {
			return false;
		}
    },
    'onClose' : function (){
        return true;
    },
	
    /* 加载对话框样式 */
    'loadStyle' : function(){
        var _dialog_js_path = $('#dialog_js').attr('src');
        var _path    = _dialog_js_path.split('/');
        var _dialog_css = _path.slice(0, _path.length - 1).join('/') + '/dialog.css';
        $('#dialog_js').after('<link href="' + _dialog_css + '" rel="stylesheet" type="text/css" />');
    },
	
	'setTitle' : function(id, title) {
		if (__DIALOG_WRAPPER__[id]) {
			__DIALOG_WRAPPER__[id].setTitle(title);
		}
	}
};

//锁屏
ScreenLocker = {
    'style' : {
        'position' : 'absolute',
        'top' : '0px',
        'left' : '0px',
        'backgroundColor' : '#333',
        'opacity' : 0.5,
		'overflow':'hidden',
        'z-index' : 999
    },
    'masker' : null,
    'lock' : function(zIndex){
        if (this.masker !== null) {
            this.masker.width($(document).width()).height($(document).height());
            return true;
        }
        this.masker = $('<div></div>');

        /* IE6 Hack */
        if ($.browser.msie) {
            $('select').css('visibility', 'hidden');
        }
        //var _iframe = $('<iframe></iframe>').css({'opacity':0, 'width':'100%', 'height':'100%'});
        //this.masker.append(_iframe);

        /* 样式 */
        this.masker.css(this.style);

        if (zIndex) {
            this.masker.css('zIndex', zIndex);
        }

        /* 整个文档的宽高 */
        this.masker.width($(document).width()).height($(document).height());

        $(document.body).append(this.masker);
    },
    'unlock' : function(){
        if (this.masker === null) {
            return true;
        }
        this.masker.remove();
        this.masker = null;

        /* IE6 Hack */
        if ($.browser.msie) {
            $('select').css('visibility', 'visible');
        }
    }
};

//拖动
function draggable(m, c)
{
	var MOUSEDOWN_FLG = false;
	var _x, _y;
	$(c).mousedown(function(event){
		MOUSEDOWN_FLG = true;
		var offset = $(m).offset();
		_x = event.pageX - offset.left;
		_y = event.pageY - offset.top;
		$(m).css({left:event.pageX-_x, top:event.pageY-_y});
	});
	
	$(document).mousemove(function(event){
		if(MOUSEDOWN_FLG){
			$(m).css({left:event.pageX-_x, top:event.pageY-_y});
		}
	}).mouseup(function(event){
		MOUSEDOWN_FLG=false;
	});
}

//对话框类
Dialog = function (id){
    /*生成基础对话框*/
    this.id = id;
    this.init();
};
Dialog.prototype = {
    /* 唯一标识 */
    'id' : null,
	/*是否有标题栏*/
	'noTitleBar':false,
    /* 文档对象 */
    'dom' : null,
    'lastPos' : null,
    'status' : 'complete',
    'onClose' : function (){
		/*可执行扩展该函数,拦截标准的对话框关闭函数*/
        return true;
    },
    'tmp' : {},
    /* 初始化 */
    'init' : function(){
        this.dom = {'wrapper' : null, 'body':null, 'head':null, 'title':null, 'close_button':null, 'content':null};

        /* 创建外层容器 */
        this.dom.wrapper = $('<div id="dialog_object_' + this.id + '" class="dialog_wrapper"></div>').get(0);

        /* 创建对话框主体 */
        this.dom.body = $('<div class="dialog_body"></div>').get(0);
		
		//使用自定义标题栏
		if (!this.noTitleBar) {
			/* 创建标题栏 */
			this.dom.head = $('<div class="dialog_head"></div>').get(0);
	
			/* 创建标题文本 */
			this.dom.titletxt = $('<span class="dialog_title_icon"></span>').get(0);
			this.dom.title = $('<div class="dialog_title"></div>').append(this.dom.titletxt);
	
			/* 创建关闭按钮 */
			this.dom.close_button = $('<span class="dialog_close_button">close</span>').get(0);
			
			/* 组合 */
			$(this.dom.head).append(this.dom.title).append(this.dom.close_button);
			$(this.dom.body).append(this.dom.head);
		}
		
		/* 创建内容区域 */
		this.dom.content = $('<div class="dialog_content"></div>').get(0);
		$(this.dom.body).append(this.dom.content);
        $(this.dom.wrapper).append(this.dom.body).append('<div style="clear:both;display:block;"></div>');

        /* 初始化样式 */
        $(this.dom.wrapper).css({
            'z-index' : 9999,
            'display' : 'none',
			'position' : 'absolute'
        });
        $(this.dom.body).css({
            'position' : 'relative'
        });
		
		if (!this.noTitleBar) {
			$(this.dom.head).css({
				'cursor' : 'auto'//'move'
			});
			$(this.dom.close_button).css({
				'position' : 'absolute',
				'text-indent' : '-9999px',
				'cursor' : 'pointer',
				'overflow' : 'hidden'
			});
		}
		
        $(this.dom.content).css({
            'margin' : '0px',
            'padding' : '0px'
        });

        var self = this;

		if (!this.noTitleBar) {
			/* 初始化组件事件 */
			$(this.dom.close_button).click(function(){
				DialogManager.close(self.id);
			});
	
			/* 可拖动 */
			draggable(this.dom.wrapper, this.dom.title);
			/*
			$(this.dom.wrapper).draggable({
				'handle' : this.dom.title
			});*/
		}

        /* 放入文档流 */
        $(document.body).append(this.dom.wrapper);
    },

    /* 隐藏 */
    'hide' : function(){
        $(this.dom.wrapper).hide();
    },

    /* 显示 */
    'show' : function(pos){
        if (pos) {
            this.setPosition(pos);
        }

        /* 锁定屏幕 */
		if (__ScreenLocker_HandleKey__ == null || __ScreenLocker_HandleKey__ == '') {
        	ScreenLocker.lock(999);
			__ScreenLocker_HandleKey__ = this.id;
		}

        /* 显示对话框 */
        $(this.dom.wrapper).show();
    },

    /* 关闭 */
    'close' : function(){
        if (!this.onClose()) {
            return false;
        }
        /* 关闭对话框 */
        $(this.dom.wrapper).remove();

        /* 解锁屏幕 */
		if (__ScreenLocker_HandleKey__ == this.id) {
        	ScreenLocker.unlock();
			__ScreenLocker_HandleKey__ = null;
		}

        return true;
    },

    /* 对话框标题 */
    'setTitle' : function(title){
        $(this.dom.titletxt).html(title);
    },

    /* 改变对话框内容 */
    'setContents' : function(type, options){
        contents = this.createContents(type, options);
        if (typeof(contents) == 'string') {
			contents = evalscript(contents);
            $(this.dom.content).html(contents);
        } else {
            $(this.dom.content).empty();
            $(this.dom.content).append(contents);
        }
    },

    /* 设置对话框样式 */
    'setStyle' : function(style){
        if (typeof(style) == 'object') {
            /* 否则为CSS */
            $(this.dom.wrapper).css(style);
        } else {
            /* 如果是字符串，则认为是样式名 */
            $(this.dom.wrapper).addClass(style);
        }
    },
    'setWidth' : function(width){
        this.setStyle({'width' : width + 'px'});
    },
    'setHeight' : function(height){
        this.setStyle({'height' : height + 'px'});
    },

    /**
	 * 生成对话框内容
	 * options = {
	 * 		'type'				消息通知类型
	 *		'text'				消息通知文字
	 *		'button_name'		按钮名称
	 *		'yes_button_name'	Yes按钮
	 *		'no_button_name'	No按钮
	 * }
	 */
    'createContents'  : function(type, options){
        var _html = '', self  = this, status= 'complete';
        if (!options) {
            /* 如果只有一个参数，则认为其传递的是HTML字符串 */
            this.setStatus(status);
            return type;
        }
        switch(type){
            case 'ajax':
                /* 通过Ajax取得HTML，显示到页面上*/
				var _ajax_url = options.url+'&handle_key='+this.id;
				var _ajaxResponse = function(data) {
					//窗口加载时候错误情况处理
					if (options.checkerror) {
						if (options.checkerror(data) == false) {
							return false;
						}
					}
                   	self.setContents(data);
                    
					/* 使用上次定位重新定位窗口位置 */
                    self.setPosition(self.lastPos);
				};
				if (options.post) {
					$.post(_ajax_url, options.post, function(data) {
						_ajaxResponse(data);
					});
				} else {
					$.get(_ajax_url, function(data) {
						_ajaxResponse(data);
					});
				}
                /* 先提示正在加载 */
                _html = this.createContents('loading', {'text' : 'loading...'});
            break;
            /* 内置对话框*/
            case 'loading':
				var _css = '';
				if (options.width) {
					_css = "width:"+options.width+"px";
				}
                _html = '<div class="dialog_loading" style="'+_css+'"><div class="dialog_loading_text">' + options.text + '</div></div>';
                status = 'loading';
            break;
            case 'message':
                var type = 'notice';
                if (options.type) {
                    type = options.type;
                }
                _message_body = $('<div class="dialog_message_body"></div>');
                _message_contents = $('<div class="dialog_message_contents dialog_message_' + type + '">' + options.text + '</div>');
                _buttons_bar = $('<div class="dialog_buttons_bar"></div>');
                switch (type){
                    case 'notice':
                    case 'warning':
                        var button_name = "Sure";
                        if (options.button_name) {
                            button_name = options.button_name;
                        }
                        _ok_button = $('<input type="button" class="btn1" value="' + button_name + '" />');
                        $(_ok_button).click(function(){
							if (options.close_first) {
								DialogManager.close(self.id);
							}
                            if (options.onclick) {
                                if(!options.onclick.call()) {
                                    return;
                                }
                            }
							if (!options.close_first) {
                            	DialogManager.close(self.id);
							}
                        });
                        $(_buttons_bar).append(_ok_button);
                    	break;
                    case 'confirm':
                        var yes_button_name = 'Yes';
                        var no_button_name = 'No';
                        if (options.yes_button_name) {
                            yes_button_name = options.yes_button_name;
                        }
                        if (options.no_button_name) {
                            no_button_name = options.no_button_name;
                        }
                        _yes_button = $('<input type="button" class="btn1" value="' + yes_button_name + '" />');
                        _no_button = $('<input type="button" class="btn2" value="' + no_button_name + '" />');
                        $(_yes_button).click(function(){
							DialogManager.close(self.id);						  
                            if (options.onClickYes) {
                                if (options.onClickYes.call() === false) {
                                    return;
                                }
                            }
                        });
                        $(_no_button).click(function(){
                            DialogManager.close(self.id);
                            if (options.onClickNo) {
                                if (!options.onClickNo.call()) {
                                    return;
                                }
                            }
                        });
                        $(_buttons_bar).append(_yes_button).append(_no_button);
                    break;
                }
                _html = $(_message_body).append(_message_contents).append(_buttons_bar);

            break;
        }
        this.setStatus(status);

        return _html;
    },
    /* 定位 */
    'setPosition'   : function(pos){
        /* 上次定位 */
        this.lastPos = pos;
        if (typeof(pos) == 'string')
        {
            switch(pos){
                case 'center':
                    var left = 0;
                    var top  = 0;
                    var dialog_width    = $(this.dom.wrapper).width();
                    var dialog_height   = $(this.dom.wrapper).height();
					
                    /* left=滚动条的宽度  + (当前可视区的宽度 - 对话框的宽度 ) / 2 */
                    left = $(window).scrollLeft() + ($(window).width() - dialog_width) / 2;

                    /* top =滚动条的高度  + (当前可视区的高度 - 对话框的高度 ) / 2 */
                    top  = $(window).scrollTop()  + ($(window).height() - dialog_height) / 2;
                    $(this.dom.wrapper).css({left:left + 'px', top:top + 'px'});
                break;
            }
        }
        else
        {
            var _pos = {};
            if (typeof(pos.left) != 'undefined') {
                _pos.left = pos.left;
            }
            if (typeof(pos.top)  != 'undefined') {
                _pos.top  = pos.top;
            }
            $(this.dom.wrapper).css(_pos);
        }

    },
    /* 设置状态 */
    'setStatus' : function(code){
        this.status = code;
    },
    /* 获取状态 */
    'getStatus' : function(){
        return this.status;
    },
    'disableClose' : function(msg){
        this.tmp['oldOnClose'] = this.onClose;
        this.onClose = function(){
            if(msg)alert(msg);
            return false;
        };
    },
    'enableClose'  : function(){
        this.onClose = this.tmp['oldOnClose'];
        this.tmp['oldOnClose'] = null;
    }
};

//DialogManager.loadStyle();
