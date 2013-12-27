/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Package JishiGou $
 *
 * @Filename topicManage.js $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:36 1073244982 1043181282 1403 $
 *******************************************************************/


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