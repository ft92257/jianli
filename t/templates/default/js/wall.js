/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Package JishiGou $
 *
 * @Filename wall.js $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:36 2019791678 1612676547 7061 $
 *******************************************************************/


/**
 * 上墙部分js库
 * 
 * @category   Wall
 * @author     狐狸<foxis@qq.com>
 * @version	   v1.0 $Date 2011-05-30
 */
 
var WallReloadTime = 5000;

WallUI = window.UI || 
{
	stopBubble: function(e)
	{
		e = e || window.event;
		if(e.stopPropagation)
		{
			e.stopPropagation();
		}
		else
		{
			e.cancelBubble = true;
		}
	},
    isObject: function(c) {
        return typeof c == "object";
    },
    isElement: function(c) {
        return c && c.nodeType == 1;
    },
    isUndefined: function(c) {
        return typeof c == "undefined";
    },
    isFunction: function(c) {
        return this.getType(c) == "Function";
    },
    isNumber: function(c) {
        return this.getType(c) == "Number";
    },
    isString: function(c) {
        return this.getType(c) == "String";
    },
    isArray: function(c) {
        return this.getType(c) == "Array";
    },
	hasClass: function(c, f) {
        if (!c || !c.className) return false;
        return c.className != c.className.replace(RegExp("\\b" + f + "\\b"), "");
    },
    addClass: function(c, f) {
        if (c) if (c.className) if (this.hasClass(c, f)) return false;
        else c.className += " " + f;
        else c.className = f;
    },
    removeClass: function(c, f) {
        if (c) c.className = c.className.replace(RegExp("\\b" + f + "\\b"), "");
    },
    toggleClass: function(c, f) {
        this.hasClass(c, f) ? this.removeClass(c, f) : this.addClass(c, f);
    },
    each: function(c, f) {
        if (WallUI.isUndefined(c[0])) for (var n in c) WallUI.isFunction(c[n]) || f(n, c[n]);
        else {
            n = 0;
            for (var a = c.length; n < a; n++) WallUI.isFunction(c[n]) || f(c[n], n);
        }
    }
};

function wall_add_key(keyval, typeval)
{
	$.post 
	(
		'ajax.php?mod=wall&code=add_key',
		{
			'key':keyval,
			'type':typeval
		},
		function (r) 
		{
			if(r.done)
			{
				options = {
					onclick:function() {
						location.reload();
					},
					close_first:true
				};
				MessageBox('notice', r.msg, '提示', options);
			}
			else
			{
				if(r.msg)
				{
					MessageBox('warning', r.msg);
				}
			}
		},
		'json'
	);
}

function wall_del_key(keyval, typeval)
{
	$.post 
	(
		'ajax.php?mod=wall&code=del_key',
		{
			'key':keyval,
			'type':typeval
		},
		function (r) 
		{
			if(r.done)
			{
				options = {
					onclick:function() {
						location.reload();
					},
					close_first:true
				};
				MessageBox('notice', r.msg, '提示', options);
			}
			else
			{
				if(r.msg)
				{
					MessageBox('warning', r.msg);
				}
			}
		},
		'json'
	);
}

function wall_add_draft(markval, tidval, c)
{
	$.post
	(
		'ajax.php?mod=wall&code=add_draft',
		{
			'mark':markval,
			'tid':tidval
		},
		function (r)
		{
			if(r.done)
			{
				WallUI.addClass(c.parentNode, 'current');
			}
			else
			{
				if(r.msg)
				{
					MessageBox('warning', r.msg);
				}
			}
		},
		'json'
	);
}

function wall_del_draft(markval, tidval, c)
{
	MessageBox("confirm", "确定删除吗？", {
		onClickYes:function() {
			$.post
			(
				'ajax.php?mod=wall&code=del_draft',
				{
					'mark':markval,
					'tid':tidval
				},
				function (r)
				{
					if(r.done)
					{
						WallUI.removeClass(c.parentNode, 'current');
						
						location.reload();
					}
					else
					{
						if(r.msg)
						{
							MessageBox('warning', r.msg);
						}
					}
				},
				'json'
			);
		}		
	});
}

function wall_add_playlist(tidval, unshiftval, c)
{	
	$.post
	(
		'ajax.php?mod=wall&code=add_playlist',
		{
			'tid':tidval,
			'unshift':unshiftval
		},
		function (r)
		{
			if(r.done)
			{
				if(unshiftval)
				{
					WallUI.addClass(c.parentNode, 'on');
				}
				else
				{
					WallUI.addClass(c, 'on');
				}
				
				wall_load_playlist();				
			}
			else
			{
				if(r.msg)
				{
					MessageBox('warning', r.msg);
				}
			}
		},
		'json'
	);
}

function wall_add_playlist_all()
{
	var tids = [];
	$('li.clearfix').each(function () {
		tids.push($(this).attr('id'));
		
		WallUI.addClass(this, 'on');
	});
	var tidsval = tids.join(',');
	
	$.post
	(
		'ajax.php?mod=wall&code=add_playlist_all',
		{
			'tids':tidsval
		},
		function (r)
		{
			if(r.done)
			{
				wall_load_playlist();
			}
			else
			{
				if(r.msg)
				{
					MessageBox('warning', r.msg);
				}
			}
		},
		'json'
	);
}

function wall_del_playlist(tidval)
{
	$.post
	(
		'ajax.php?mod=wall&code=del_playlist',
		{
			'tid':tidval
		},
		function (r)
		{
			if(r.done)
			{
				wall_load_playlist();
			}
			else
			{
				if(r.msg)
				{
					MessageBox('warning', r.msg);
				}
			}
		},
		'josn'
	);
}

function wall_del_playlist_all()
{
	$.post
	(
		'ajax.php?mod=wall&code=del_playlist_all',
		{
			
		},
		function (r)
		{
			if(r.done)
			{
				wall_load_playlist();
			}
			else
			{
				if(r.msg)
				{
					MessageBox('warning', r.msg);
				}
			}
		},
		'josn'
	);
}

function wall_load_playlist()
{
	$.post
	(
		'ajax.php?mod=wall&code=load_playlist',
		{
			
		},
		function (r)
		{
			$('#playList').html(r);
		}
	);

	setTimeout('wall_load_playlist();',WallReloadTime);
}

function wall_newly(wallidval)
{	
	var lasttidval = 0;	
	$('#talkList li:first-child').each(function () 
	{
		if(lasttidval < 1)
		{
			lasttidval = $(this).attr('id');
		}
	});
	
	$.post
	(
		'ajax.php?mod=wall&code=newly',
		{
			'wall_id':wallidval,
			'last_tid':lasttidval
		},
		function (r)
		{
			if( -1 != r.indexOf('<success></success>') )
			{
				$('#talkList').prepend(r);
			}
		}
	);
	
	setTimeout('wall_newly("' + wallidval + '");',WallReloadTime);
}

function wall_set_wall()
{
	$.post
	(
		$('#wall_set_form').attr('action'),
		$('#wall_set_form').serialize(),
		function(r)
		{
			if(r.done)
			{
				close_wall_set_dialog();
				options = {
					onclick:function() {
						location.reload();
					},
					close_first:true
				};
				MessageBox('notice', r.msg, '提示', options);
			}
			else
			{
				if(r.msg)
				{
					MessageBox('warning', r.msg);
				}
			}			
		},
		'json'
	);
}
function show_wall_set_dialog()
{	
	showDialog('wall_set_dialog', 'ajax', '设置微博上墙', {url:'ajax.php?mod=wall&code=set_wall'}, 420);
}
function close_wall_set_dialog()
{
	closeDialog('wall_set_dialog');
}

function wall_set_status()
{
	var btnval = $('#wall_status_btn').val();
	var statusval = ('暂停' == btnval ? '0' : '1');
	
	$.post
	(
		'ajax.php?mod=wall&code=set_status',
		{
			'status':statusval 
		},
		function (r)
		{
			if(r.done)
			{
				var newbtnval = ('暂停' == btnval ? '开始上墙' : '暂停');
				
				$('#wall_status_btn').val(newbtnval);
			}
			else
			{
				if(r.msg)
				{
					MessageBox('warning',r.msg);
				}
			}
		},
		'json'
	);
}


