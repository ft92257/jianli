/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Package JishiGou $
 *
 * @Filename recommend.js $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:36 231543696 405956144 2234 $
 *******************************************************************/


var $jishigou = jQuery.noConflict();
function jishigou_action()
{
	var strhtml;
	strhtml=$jishigou("#ajaxcontent .indexrow").eq(7).html();
	if(strhtml==null)
	{
		return false;
	}
	
	$jishigou("#ajaxcontent .indexrow").eq(0).appendTo("#ajaxcontent");
	$jishigou("#Pcontent").prepend("<div class='indexrow' style='display:none;' id='indexrowid'>"+strhtml+"</div>");
	$jishigou("#Pcontent .indexrow").eq(13).remove();
	$jishigou("#Pcontent .indexrow").eq(0).slideDown(500);
}
$jishigou(document).ready(
	function()
	{
		var Ds1,Ds2,Ds3,Ds4,Ds5,Ds6,Ds7;
		Ds1=$jishigou("#ajaxcontent .indexrow").eq(0).html();
		Ds2=$jishigou("#ajaxcontent .indexrow").eq(1).html();
		Ds3=$jishigou("#ajaxcontent .indexrow").eq(2).html();
		Ds4=$jishigou("#ajaxcontent .indexrow").eq(3).html();
		Ds5=$jishigou("#ajaxcontent .indexrow").eq(4).html();
		Ds6=$jishigou("#ajaxcontent .indexrow").eq(5).html();
		Ds7=$jishigou("#ajaxcontent .indexrow").eq(6).html();
		$jishigou("#Pcontent").prepend("<div class='indexrow' id='ds1'>"+Ds1+"</div>"+"<div class='indexrow' id='ds2'>"+Ds2+"</div>"+"<div class='indexrow' id='ds3'>"+Ds3+"</div>"+"<div class='indexrow' id='ds4'>"+Ds4+"</div>"+"<div class='indexrow' id='ds5'>"+Ds5+"</div>"+"<div class='indexrow' id='ds6'>"+Ds6+"</div>"+"<div class='indexrow' id='ds7'>"+Ds7+"</div>");
		if(Ds1==null)
		{
			document.getElementById("ds1").innerHTML="";
		}
		if(Ds2==null)
		{
			document.getElementById("ds2").innerHTML="";
		}
		if(Ds3==null)
		{
			document.getElementById("ds3").innerHTML="";
		}
		if(Ds4==null)
		{
			document.getElementById("ds4").innerHTML="";
		}
		if(Ds5==null)
		{
			document.getElementById("ds5").innerHTML="";
		}
		if(Ds6==null)
		{
			document.getElementById("ds6").innerHTML="";
		}
		if(Ds7==null)
		{
			document.getElementById("ds7").innerHTML="";
		}
		var Interval;
		Interval=setInterval("jishigou_action();",2000);
		$jishigou("#Pcontent").hover(function(){clearInterval(Interval);},
							 function(){Interval=setInterval("jishigou_action();",2000);});
	}
);

function jishigou_scroll(n){
	temp=n;
	News.scrollTop=News.scrollTop+temp;
	if (temp==0) return;
	setTimeout("jishigou_scroll(temp)",20); 
}
