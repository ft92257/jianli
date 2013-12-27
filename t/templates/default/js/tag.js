/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Package JishiGou $
 *
 * @Filename tag.js $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:36 1852101020 1704768590 4833 $
 *******************************************************************/


var Tag = {
  Version: '1.0.1'
}

function HtmlDecode(text){var re = {'&lt;':'<','&gt;':'>','&amp;':'&','&quot;':'"'};for (i in re) text = text.replace(new RegExp(i,'g'), re[i]);return text;}
function gid(id){return document.getElementById?document.getElementById(id):null;}
function trimTag(s){var m = s.toString().match(/^\s*(\S+(\s+\S+)*)\s*$/);return (m == null)?"":m[1];}
var get_e_src = function(e){if(e) return e.target;if(window.event) return window.event.srcElement;return null;}

	var _tagFid = "DP_Tags_";
	var _tagIName = "TagSpan";
	var _tagSName = "TagValue";
	var _spaceWord = " ";//用什么字符来分隔Tag，默认为空格键
	

	function TagInit(_input, _div, _spans, _ids, _tags)
	{
		var inputObj = gid(_input);
		

		if (inputObj)
		{
			inputObj.setAttribute(_tagIName, _ids);
			inputObj.onkeydown = inputObj.onkeyup = TagsHandler;
		}
		var divObj = gid(_div);
		
		while(divObj.childNodes.length>0)
		{
			divObj.removeChild(divObj.childNodes[0]);
		}
		
		for (var _s = 0 ; _s < _spans.length ; _s++)
		{
			var tObj = document.createElement("p");
			tObj.style.margin = "0px";
			tObj.style.padding = "0px";
			var pObj = CreatTagSpan(_spans[_s], null, null);
			var sObj = CreatTagSpan(null, _tagFid + _ids[_s], _tags[_s]);
			for (var t = 0 ; t < _tags[_s].length ; t++)
			{
				sObj.appendChild(document.createTextNode(_spaceWord));
				sObj.appendChild(CreatTag(_input, _tags[_s][t]));
			}
			tObj.appendChild(pObj);
			tObj.appendChild(sObj);
			if (divObj)
			{
				divObj.appendChild(tObj);
			}
		}
		
		var iTagSpanName = inputObj.getAttribute(_tagIName);
		var iTagText = _spaceWord + trimTag(inputObj.value) + _spaceWord;
		
		UpdateSelectTag(iTagSpanName, iTagText)

	}

	function CreatTagSpan(spanname, spanid, _tags)
	{
		var SPANobj = document.createElement("span");
		if (spanid) SPANobj.id = spanid;
		SPANobj.style.color = "#AAA";
		if (_tags) SPANobj.setAttribute(_tagSName, _tags);
		if (spanname) SPANobj.appendChild(document.createTextNode(spanname + ": "));
		return SPANobj;
	}

	function CreatTag(inputid, tag)
	{
		var Aobj = document.createElement("a");
		Aobj.className = "B";
		Aobj.setAttribute("href", "#");
		var stag = HtmlDecode(trimTag(tag));
		Aobj.onclick = function () {this.blur();swapTag(inputid, stag);return false;};
		Aobj.appendChild(document.createTextNode(stag));
		return Aobj;
	}

	function TagsHandler(event)
	{
		var eObj = get_e_src(event);
		var e = (event||window.event);
		var iTagSpanName = eObj.getAttribute(_tagIName);
		var iTagText = _spaceWord + trimTag(eObj.value) + _spaceWord;
		UpdateSelectTag(iTagSpanName, iTagText);
		if (e.keyCode == 13) return false;
	}

	function swapTag(inputid, tag)
	{
		var inputObj = gid(inputid);
		if (!inputObj) return;
		var iTagSpanName = inputObj.getAttribute(_tagIName);
		var TagIn = false;
		var iTagArray = trimTag(inputObj.value).split(_spaceWord);
		if (trimTag(iTagArray[0]) == "") iTagArray.splice(0,1);
		for (var t = 0; t < iTagArray.length;t++)
		{
			if (iTagArray[t].toLowerCase() == tag.toLowerCase())
			{
				iTagArray.splice(t,1);
				selectTag(iTagSpanName, tag, false);
				TagIn = true;
				t-=1;
			}
		}
		if (!TagIn)
		{
			iTagArray.push(tag);
			selectTag(iTagSpanName, tag, true);
		}
		var newTag = iTagArray.join(_spaceWord);
		inputObj.value = (newTag.length > 0)?newTag + _spaceWord:newTag;
	}

	function selectTag(spanids, tag, sel)
	{
		var spanid = spanids.toString().split(",");
		for(s in spanid)
		{
			var sObj = gid(_tagFid + spanid[s]);
			if (sObj)
			{
				var sTags = sObj.getAttribute(_tagSName).toString().split(",");
				var sTagObj = sObj.getElementsByTagName("a");
				for (t in sTags)
				{
					if (sTagObj[t])
					{
						if (sTagObj[t].className)
						{
							if (HtmlDecode(sTags[t].toLowerCase()) == tag.toLowerCase())
							{
								if (sel)
								{
									sTagObj[t].className = "BH";
								}
								else
								{
									sTagObj[t].className = "B";
								}
							}
						}
					}
				}
			}
		}
	}

	function UpdateSelectTag(spanids, _tags)
	{
		var spanid = spanids.toString().split(",");
		for(s in spanid)
		{
			var sObj = gid(_tagFid + spanid[s]);
			if (sObj)
			{
				var sTags = sObj.getAttribute(_tagSName).toString().split(",");
				var sTagObj = sObj.getElementsByTagName("a");
				_tags = _tags.toLowerCase();
				for (t in sTags)
				{
					if (sTagObj[t])
					{
						if (sTagObj[t].className)
						{
							if (_tags.indexOf(HtmlDecode(_spaceWord + sTags[t].toLowerCase() + _spaceWord)) >= 0)
							{
								sTagObj[t].className = "BH";
							}
							else
							{
								sTagObj[t].className = "B";
							}
						}
					}
				}
			}
		}
	}