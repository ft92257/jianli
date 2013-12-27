/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Package JishiGou $
 *
 * @Filename city.js $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:36 1836487307 1233146125 1549 $
 *******************************************************************/


(function($){$.fn.doubleSelect=function(_2,_3,_4){_4=$.extend({preselectFirst:null,preselectSecond:null,emptyOption:false,emptyKey:-1,emptyValue:"Choose ..."},_4||{});var _5=this;var _6="#"+_2;var _7=$(_6);var _8=function(_9){_7.val(_9).change()};var _a=function(){$(_6+" option").remove()};$(this).change(function(){_a();$current=this.options[this.selectedIndex].value;if($current!=""){$.each(_3,function(k,v){if($current==v.key){$.each(v.values,function(k,v2){var o=$("<option>").html(k).attr("value",v2);if(v.defaultvalue!=null&&v2==v.defaultvalue){o.html(k).attr("selected","selected")}if(_4.preselectSecond!=null&&v2==_4.preselectSecond){o.html(k).attr("selected","selected")}o.appendTo(_7)})}})}else{_8(_4.emptyValue)}});return this.each(function(){_5.children().remove();_7.children().remove();if(_4.emptyOption){var oe=$("<option>").html(_4.emptyValue).attr("value",_4.emptyKey);oe.appendTo(_5)}$.each(_3,function(k,v){var of=$("<option>").html(k).attr("value",v.key);if(_4.preselectFirst!=null&&v.key==_4.preselectFirst){of.html(k).attr("selected","selected")}of.appendTo(_5)});if(_4.preselectFirst==null){$current=this.options[this.selectedIndex].value;if($current!=""){$.each(_3,function(k,v){if($current==v.key){$.each(v.values,function(k,v2){var o=$("<option>").html(k).attr("value",v2);if(v.defaultvalue!=null&&v2==v.defaultvalue){o.html(k).attr("selected","selected")}if(_4.preselectSecond!=null&&v2==_4.preselectSecond){o.html(k).attr("selected","selected")}o.appendTo(_7)})}})}else{_8(_4.emptyValue)}}else{_5.change()}})}})(jQuery);