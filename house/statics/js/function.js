function openimg(u, w, h){
	if($("#lightbox_bg").length==0){
		$("body").append('<div id="lightbox_img" style="width: '+w+'px;height: '+h+'px;"><img src="'+u+'" id="lightbox_i" width="'+w+'" height="'+h+'" alt="" title="点击关闭"/></div><div id="lightbox_bg"></div><div id="lightbox_tool" style="display: none;background: #fff;"><a href="#" onclick="roateimg(1);" title="向左转"><img src="images/blank.gif" width="19" height="16" style="background-position: 0 0;"/></a><a href="#" onclick="roateimg();" title="向右转"><img src="images/blank.gif" width="19" height="16" style="background-position: -18px 0;"/></a><a href="#" onclick="window.open($(\'#lightbox_i_u\').val());return false;" title="新窗口打开"><img src="images/blank.gif" width="16" height="16" style="background-position: -37px 0;"/></a><input type="hidden" id="lightbox_i_r" value="0"/><input type="hidden" id="lightbox_i_o" value="0"/><input type="hidden" id="lightbox_i_u" value="'+u+'"/><input type="hidden" id="lightbox_i_w" value="'+w+'"/><input type="hidden" id="lightbox_i_h" value="'+h+'"/></div>');
	}else{
		if($('#lightbox_i_u').val()!=u){
			$('#lightbox_img').html('<img src="'+u+'" id="lightbox_i" width="'+w+'" height="'+h+'" alt="" title="点击关闭"/>');
			$('#lightbox_i').css({'width':w+'px', 'height':h+'px'});
			$('#lightbox_img').css({'width':w+'px', 'height':h+'px'});
			$('#lightbox_i_r').val('0');
			$('#lightbox_i_u').val(u);
			$('#lightbox_i_w').val(w);
			$('#lightbox_i_h').val(h);
		}
	}
	$('#lightbox_i_o').val('1');
	var ri=parseInt($('#lightbox_i_r').val());
	var nw=parseInt($('#lightbox_i_w').val());
	var nh=parseInt($('#lightbox_i_h').val());
	if((ri%2)>0){
		nw=parseInt($('#lightbox_i_h').val());
		nh=parseInt($('#lightbox_i_w').val());
	}
	nw+=10;
	nh+=10;
	var dl=$(document).scrollLeft();
	var dt=$(document).scrollTop();
	var ww=$(window).width();
	var wh=$(window).height();
	var vw=$(document).width();
	var vh=$(document).height();
	var l=dl+(ww-nw)/2;
	if(l<0)l=0;
	var t=dt+(wh-nh)/2;
	if(t<0)t=0;
	if(nw>vw){
		vw=nw;
		l=0;
	}
	if(nh>vh){
		vh=nh;
		t=0;
	}
	if($.browser.msie){
		$("#lightbox_img").show();
		$("#lightbox_bg").show();
		$("#lightbox_bg").fadeTo(50, 0.5);
	}else{
		$("#lightbox_img").fadeIn(500);
		$("#lightbox_bg").fadeIn(500);
	}
	$("#lightbox_img").css({'top':t, 'left':l});
	$("#lightbox_bg").css({'width':vw+'px', 'height':vh+'px'});
	$("#lightbox_img").click(function(){
		$('#lightbox_tool').hide();
		$('#lightbox_i_o').val('0');
		$("#lightbox_bg").fadeOut(500);
		$("#lightbox_img").fadeOut(500);
	}).mouseover(function(){
		var p=$(this).offset();
		$('#lightbox_tool').css({'left':(p.left+5)+'px', 'top':(p.top+5)+'px'});
		$('#lightbox_tool').show();
	}).mouseout(function(){
		$('#lightbox_tool').hide();
	});
	$('#lightbox_tool').mouseover(function(){
		if($('#lightbox_i_o').val()=='1')$(this).show();
	}).mouseout(function(){
		$(this).hide();
	});
}

function roateimg(t){
	var w=parseInt($('#lightbox_i_w').val());
	var h=parseInt($('#lightbox_i_h').val());
	var ri=parseInt($('#lightbox_i_r').val());
	ri++;
	if(t){
		$("#lightbox_i").rotateLeft();
	}else{
		$("#lightbox_i").rotateRight();
	}
	var nw=w;
	var nh=h;
	if((ri%2)>0){
		nw=h;
		nh=w;
	}
	$('#lightbox_img').css({'width':nw+'px', 'height':nh+'px'});
	nw+=10;
	nh+=10;
	var dl=$(document).scrollLeft();
	var dt=$(document).scrollTop();
	var ww=$(window).width();
	var wh=$(window).height();
	var vw=$(document).width();
	var vh=$(document).height();
	var l=dl+(ww-nw)/2;
	if(l<0)l=0;
	var t=dt+(wh-nh)/2;
	if(t<0)t=0;
	if(nw>vw){
		vw=nw;
		l=0;
	}
	if(nh>vh){
		vh=nh;
		t=0;
	}
	$("#lightbox_img").css({'top':t, 'left':l});
	$("#lightbox_bg").css({'width':vw+'px', 'height':vh+'px'});
	$("#lightbox_img").mouseover();
	$('#lightbox_i_r').val(ri);
}

function postwb(v, u, fj){
	var fjid=fj?fj:'';
	var c=$.trim($('#content'+fjid).val());
	var vurl='';
	if($('#vurl'+fjid).length>0)vurl=$.trim($('#vurl'+fjid).val());
	if(c!='' || vurl!=''){
		if(c!='')c+=' ';
		c+=vurl;
		$('#content'+fjid).attr('disabled', 'disabled');
		if($('#vurl'+fjid).length>0)$('#vurl'+fjid).attr('disabled', 'disabled');
		if($('#submit_fb'+fjid).length>0){
			$('#submit_fb'+fjid).attr('jq_ov', $('#submit_fb'+fjid).val());
			$('#submit_fb'+fjid).val('正在提交');
			$('#submit_fb'+fjid).attr('disabled', 'disabled');
		}
		$('#'+v).load(u, {c:c}, function(){
			$('#content'+fjid).val('');
			$('#content'+fjid).removeAttr('disabled');
			if($('#vurl'+fjid).length>0){
				$('#vurl'+fjid).removeAttr('disabled');
				$('#vurl'+fjid).val('');
			}
			if($('#wb_v_div'+fjid).length>0)$('#wb_v_div'+fjid).hide();
			if($('#submit_fb'+fjid).length>0){
				$('#submit_fb'+fjid).val($('#submit_fb'+fjid).attr('jq_ov'));
				$('#submit_fb'+fjid).removeAttr('disabled');
			}
			if($('#imgu_div'+fjid).length>0){
				$('#imgu_upload'+fjid).html('');
				$('#imgu_upload'+fjid).hide();
				$('#img_c'+fjid).val('0');
				$('#imgu_div'+fjid).hide();
			}
		});
	}
}

function postxqwb(i){
	var u='j/xqtopic.php?xqid='+i;
	if($('#xqcid').val()>0)u+='&cid='+$('#xqcid').val();
	if($('#img_c').val()>0){
		u+='&imgid=0'
		for(var j=0;j<$('#img_c').val();j++){
			if($('#imgid_'+j).length>0 && $('#imgid_'+j).val()>0)u+='_'+$('#imgid_'+j).val();
		}
	}
	postwb('xqtopic_'+i, u);
}

function postqzhd(i){
	var u='j/qzhd.php?id='+i;
	if($('#img_c').val()>0){
		u+='&imgid=0'
		for(var j=0;j<$('#img_c').val();j++){
			if($('#imgid_'+j).length>0 && $('#imgid_'+j).val()>0)u+='_'+$('#imgid_'+j).val();
		}
	}
	postwb('faq_topic', u);
}

function postuwb(){
	var u='j/utopic.php?p=1';
	if($('#img_c').val()>0){
		u+='&imgid=0'
		for(var j=0;j<$('#img_c').val();j++){
			if($('#imgid_'+j).length>0 && $('#imgid_'+j).val()>0)u+='_'+$('#imgid_'+j).val();
		}
	}
	postwb('mblog_list', u);
}

function postjpwb(){
	if($('#jpid').length>0 && $('#jpid').val()>0){
		var u='j/jp.php?id='+$('#jlid').val()+'&jpid='+$('#jpid').val()+'&t=bottom';
		if($('#img_c').val()>0){
			u+='&imgid=0'
			for(var j=0;j<$('#img_c').val();j++){
				if($('#imgid_'+j).length>0 && $('#imgid_'+j).val()>0)u+='_'+$('#imgid_'+j).val();
			}
		}
		if($('#tpiszf').is(':checked'))u+='&iszf=1';
		if($('#tpisqz').length>0 && $('#tpisqz').is(':checked')){
			u+='&isqz=1';
			$('#tpisqz').removeAttr('checked');
		}
		postwb('jp_topic', u);
	}
}

function postclwb(){
	if($('#clid').length>0 && $('#clid').val()>0){
		var u='j/cl.php?id='+$('#jlid').val()+'&clid='+$('#clid').val()+'&t=bottom';
		if($('#img_c').val()>0){
			u+='&imgid=0'
			for(var j=0;j<$('#img_c').val();j++){
				if($('#imgid_'+j).length>0 && $('#imgid_'+j).val()>0)u+='_'+$('#imgid_'+j).val();
			}
		}
		if($('#tpiszf').is(':checked'))u+='&iszf=1';
		postwb('jp_topic', u);
	}
}

function posthdpwb(h){
	if($('#pid').length>0 && $('#pid').val()>0){
		var u='j/hd.php?id='+h+'&pid='+$('#pid').val()+'&t=bottom';
		if($('#img_c').val()>0){
			u+='&imgid=0'
			for(var j=0;j<$('#img_c').val();j++){
				if($('#imgid_'+j).length>0 && $('#imgid_'+j).val()>0)u+='_'+$('#imgid_'+j).val();
			}
		}
		if($('#tpiszf').is(':checked'))u+='&iszf=1';
		postwb('hd_topic', u);
	}
}

function posthdwb(i){
	var u='j/xzhdtopic.php?m=hd&id='+i;
	if($('#img_c').val()>0){
		u+='&imgid=0'
		for(var j=0;j<$('#img_c').val();j++){
			if($('#imgid_'+j).length>0 && $('#imgid_'+j).val()>0)u+='_'+$('#imgid_'+j).val();
		}
	}
	postwb('hdtopic_'+i, u);
}

function postqzwt(){
	var u='j/qz.php?cid='+$('#cid').val();;
	if($('#jluid').length>0 && $('#jluid').val()>0)u+='&jluid='+$('#jluid').val();
	if($('#img_c').val()>0){
		u+='&imgid=0'
		for(var j=0;j<$('#img_c').val();j++){
			if($('#imgid_'+j).length>0 && $('#imgid_'+j).val()>0)u+='_'+$('#imgid_'+j).val();
		}
	}
	var c=$.trim($('#content').val());
	var vurl='';
	if($('#vurl').length>0)vurl=$.trim($('#vurl').val());
	if(c!='' || vurl!=''){
		if(c!='')c+=' ';
		c+=vurl;
		$('#content').attr('disabled', 'disabled');
		if($('#vurl').length>0)$('#vurl').attr('disabled', 'disabled');
		if($('#submit_fb').length>0){
			$('#submit_fb').attr('jq_ov', $('#submit_fb').val());
			$('#submit_fb').val('正在提交');
			$('#submit_fb').attr('disabled', 'disabled');
		}
		$.get(u, {c:c}, function(){
			$('#content').val('');
			$('#content').removeAttr('disabled');
			if($('#vurl').length>0){
				$('#vurl').removeAttr('disabled');
				$('#vurl').val('');
			}
			if($('#wb_v_div').length>0)$('#wb_v_div').hide();
			if($('#submit_fb').length>0){
				$('#submit_fb').val($('#submit_fb').attr('jq_ov'));
				$('#submit_fb').removeAttr('disabled');
			}
			if($('#imgu_div').length>0){
				$('#imgu_upload').html('');
				$('#imgu_upload').hide();
				$('#img_c').val('0');
				$('#imgu_div').hide();
			}
			alert('问题已提交。');
		});
	}
}

function show_reply(i, t, m){
	if($('#wbrdiv_'+i).is(':visible')){
		if(!m){
			$('#wbrdiv_'+i).slideUp(500);
		}else{
			if($('#wbfdiv_'+i).is(':visible')){
				$('#wbfdiv_'+i).slideUp(500);
				if($('#reply_i_v_'+i).length>0 && $('#reply_i_v_'+i).is(':hidden'))$('#reply_i_v_'+i).slideDown(500);
			}
		}
	}else{
		if($('#wbfdiv_'+i).length>0 && $('#wbfdiv_'+i).is(':visible'))$('#wbfdiv_'+i).slideUp(500);
		$('#wbrdiv_'+i).html('正在载入…');
		if(!m){
			$('#wbrdiv_'+i).slideDown(500);
		}else{
			$('#wbrdiv_'+i).show();
		}
		$('#wbrdiv_'+i).load('j/wb.php?id='+i+'&iscy='+t+(!m?'':'&isplink=1'), function(){
			if($('#plc_'+i).val()>0)$('#plcm_'+i).html('评论('+$('#plc_'+i).val()+')');
		});
	}
}

function show_forward(i, t){
	if($('#wbfdiv_'+i).is(':visible')){
		$('#wbfdiv_'+i).slideUp(500);
		if(!t){
		}else{
			if($('#reply_i_v_'+i).length>0 && $('#reply_i_v_'+i).is(':hidden'))$('#reply_i_v_'+i).slideDown(500);
		}
	}else{
		if(!t){
			if($('#wbrdiv_'+i).length>0 && $('#wbrdiv_'+i).is(':visible'))$('#wbrdiv_'+i).slideUp(500);
		}else{
			if($('#reply_i_v_'+i).length>0 && $('#reply_i_v_'+i).is(':visible'))$('#reply_i_v_'+i).slideUp(500);
		}
		$('#wbfdiv_'+i).slideDown(500);
	}
}

function postzf(i){
	var c=$.trim($('#content_f_'+i).val());
	if(c!=''){
		$('#content_f_'+i).attr('disabled', 'disabled');
		$('#submit_fb_'+i).val('正在提交');
		$('#submit_fb_'+i).attr('disabled', 'disabled');
		$('#submit_cl_'+i).attr('disabled', 'disabled');
		var ispl=($('#ispl_'+i).length>0 && $('#ispl_'+i).is(':checked'))?1:0;
		$.get('j/zf.php?id='+i+'&ispl='+ispl, {c:c}, function(data){
			if(data!=''){
				$('#zfc_'+i).html('原文共'+data+' 次转发');
				$('#zfcm_'+i).html('转发('+data+')');
				$('#zfc_'+i).show();
			}
			$('#content_f_'+i).val('');
			$('#content_f_'+i).removeAttr('disabled');
			$('#submit_fb_'+i).val('发表');
			$('#submit_fb_'+i).removeAttr('disabled');
			$('#submit_cl_'+i).removeAttr('disabled');
			$('#wbfdiv_'+i).slideUp(500);
		})
	}
}

function qxzf(i, t){
	$('#content_f_'+i).val('');
	$('#wbfdiv_'+i).slideUp(500);
	if(!t){
	}else{
		if($('#reply_i_v_'+i).length>0 && $('#reply_i_v_'+i).is(':hidden'))$('#reply_i_v_'+i).slideDown(500);
	}
}

function postpl(i){
	var c=$.trim($('#content_pl_'+i).val());
	if(c!=''){
		$('#submit_pl_'+i).val('正在提交');
		$('#submit_pl_'+i).attr('disabled', 'disabled');
		var iszf=($('#iszf_'+i).length>0 && $('#iszf_'+i).is(':checked'))?1:0;
		$('#pllist_'+i).load('j/wb.php?t=1&iscy=1&id='+i+'&iszf='+iszf, {c:c}, function(data){
			$('#content_pl_'+i).val('');
			$('#content_pl_'+i).removeAttr('disabled');
			$('#submit_pl_'+i).val('发表');
			$('#submit_pl_'+i).removeAttr('disabled');
			if($('#plc_'+i).val()>0)$('#plcm_'+i).html('评论('+$('#plc_'+i).val()+')');
		})
	}
}

function delwb(i, u){
	$.get('j/delmblog.php?id='+i, function(data){
		if(!u){
			$('#wb_'+i).slideUp(500);
		}else{
			location.href='user-'+u+'.html';
		}
	});
}

function loadjlp(p, j){
	$('#jpid').val(p);
	$('#image_wrap').html('<img src="images/blank.gif" style="background: url(images/loading.gif) no-repeat center;" width="600" height="400" title="正在载入…"/>');
	$('#image_wrap').load('j/jp.php?id='+j+'&jpid='+p+'&t=top', function(){
		$('#jp_big').mouseover();
	});
	$('#div_report').load('j/jp.php?id='+j+'&jpid='+p+'&t=report');
	$('#jp_topic').html('<br/>正在载入…');
	$('#jp_topic').load('j/jp.php?id='+j+'&jpid='+p+'&t=bottom');
	$('#comment_count').load('j/jp.php?id='+j+'&jpid='+p+'&t=report&getcount=1');
}

function loadclp(p, j){
	$('#clid').val(p);
	$('#image_wrap').html('<img src="images/blank.gif" style="background: url(images/loading.gif) no-repeat center;" width="600" height="400" title="正在载入…"/>');
	$('#image_wrap').load('j/cl.php?id='+j+'&clid='+p+'&t=top', function(){
		$('#jp_big').mouseover();
	});
	$('#jp_topic').html('<br/>正在载入…');
	$('#jp_topic').load('j/cl.php?id='+j+'&clid='+p+'&t=bottom');
}

function loadhdp(p, j){
	$('#pid').val(p);
	$('#image_wrap').html('<img src="images/blank.gif" style="background: url(images/loading.gif) no-repeat center;" width="612" height="400" title="正在载入…"/>');
	$('#image_wrap').load('j/hd.php?id='+j+'&pid='+p+'&t=top', function(){
		$('#jp_big').mouseover();
	});
	$('#hd_topic').load('j/hd.php?id='+j+'&pid='+p+'&t=bottom');
	$('#hp_author').load('j/hd.php?id='+j+'&pid='+p+'&t=side');
}

function delimg(i){
	$.get('j/delimg.php?id='+i);
	$('#uimg_i_'+i).remove();
	var ic=$('#img_c').val();
	ic--;
	if(ic<0)ic=0;
	$('#img_c').val(ic);
	if(ic<3)$('#imgu_form').show();
	if(ic==0)$('#imgu_upload').hide();
}

function deltempimg(){
	$.get('j/deltempimg.php', {f:$('#temp_url').val()});
	$('#imgu_upload').slideUp(500, function(){
		$('#imgu_upload').html('');
	});
	$('#img_c').val('0');
	$('#imgu_form').slideDown(500);
}

function deljlimg(i){
	$.get('j/deltempimg.php', {f:$('#temp_url_'+i).val()});
	$('#imgu_t_'+i).remove();
	var ii=$('#img_i').val();
	ii--;
	if(ii<0)ii=0;
	$('#img_i').val(ii);
	if(ii<5)$('#jlpu_form_4').show();
	if(ii==0)$('#jlpu_upload').hide();
}

function showjpv(o){
	var p=o.offset();
	if($('#jpv_l').length>0){
		$('#jpv_l').css({'left':p.left+'px', 'top':p.top+'px'});
		$('#jpv_l').show();
	}
	if($('#jpv_r').length>0){
		$('#jpv_r').css({'left':(p.left+o.width()-$('#jpv_r').width())+'px', 'top':p.top+'px'});
		$('#jpv_r').show();
	}
}

function hidejpv(){
	if($('#jpv_l').length>0)$('#jpv_l').hide();
	if($('#jpv_r').length>0)$('#jpv_r').hide();
}

function shyh(m, x, i, t){
	var u='j/shyh.php?m='+m+'&id='+x+'&u='+i;
	if(t)u+='&t=1';
	$.get(u);
	$('#shu_'+i).hide();
	var c=parseInt($('#c_dsh').val());
	c--;
	if(c==0)$('#nomsg_v').show();
	$('#c_dsh').val(c);
}

function upimg_a_0(response){
	var r=response.split('|');
	if(r[0]>0 && r[1]!=''){
		var ic=$('#img_c').val();
		$('#imgu_upload').show();
		$('#imgu_upload').prepend('<span id="uimg_i_'+r[0]+'"><img src="'+r[1]+'"/><input type="hidden" id="imgid_'+ic+'" name="imgid_'+ic+'" value="'+r[0]+'"/><a onclick="delimg('+r[0]+');return false;" href="#" style="margin-left: -12px;"><img src="images/del.gif" alt=""/></a> </span>');
		ic++;
		$('#img_c').val(ic);
		if(ic>=3)$('#imgu_form').hide();
	}
}

function upimg_a_2(response){
	var r=response.split('|');
	if(r[0]!='' && r[1]>0 && r[2]>0){
		var ic=$('#jlimg_c').val();
		$('#jlpu_upload').append('<span id="imgu_t_'+ic+'"><img src="'+r[0]+'_t" width="80" height="80"/><input type="hidden" id="temp_url_'+ic+'" name="tt_url_'+ic+'" value="'+r[0]+'"/><input type="hidden" name="w_'+ic+'" value="'+r[1]+'"/><input type="hidden" name="h_'+ic+'" value="'+r[2]+'"/><a onclick="deljlimg('+ic+');return false;" href="#" style="margin-left: -12px;"><img src="images/del.gif" alt=""/></a>&nbsp;</span>');
		if($('#jlpu_upload').is(':hidden'))$('#jlpu_upload').slideDown(500);
		ic++;
		$('#jlimg_c').val(ic);
		var ii=$('#img_i').val();
		ii++;
		$('#img_i').val(ii);
	}
}

function upimg_a_5(response){
	var r=response.split('|');
	if(r[0]!='' && r[1]!='' && r[2]!=''){
		if($('#photo_v').is(':hidden')){
			$('#photo_v').show();
			$('#cphoto_v').hide();
		}
		$('#img_mdiv').show();
		var ow=$('#movev_ow').val();
		var oh=$('#movev_oh').val();
		var l=Math.round(($('#img_mbg').width()-ow)/2);
		var t=Math.round(($('#img_mbg').height()-oh)/2);
		$('#imgv_l').val(l);
		$('#imgv_t').val(t);
		$('#img_mdiv').css({'left':l+'px', 'top':t+'px', 'width':ow+'px', 'height':oh+'px'});
		$('#img_mdiv_v').css({'width':ow+'px', 'height':oh+'px'});

		var img_u=r[0];
		var img_w=r[1];
		var img_h=r[2];

		$('#img_u').val(img_u);
		$('#img_w').val(img_w);
		$('#img_h').val(img_h);
		$('#imgc_iw').val(img_w);
		$('#imgc_ih').val(img_h);
		$('#img_mbg_i').html('<img src="'+$('#img_u').val()+'" id="img_ib" style="width: '+img_w+'px;height: '+img_h+'px;"/>');
		$('#img_mspan').html('<img src="'+$('#img_u').val()+'" id="img_is" style="width: '+img_w+'px;height: '+img_h+'px;"/>');
		$('#img_sdiv_s').html('<img src="'+$('#img_u').val()+'" id="img_ss" style="width: '+img_w+'px;height: '+img_h+'px;"/>');
		$('#img_sdiv_b').html('<img src="'+$('#img_u').val()+'" id="img_sb" style="width: '+img_w+'px;height: '+img_h+'px;"/>');
		if(img_w<=$('#img_mbg').width() && img_h<=$('#img_mbg').height()){
			$('#move_td').hide();
		}else{
			$('#move_td').show();
		}
		$('#move_bar').css('margin-left', $('#move_tw').val()+'px');

		movebg();
	}
}

function upimg_a_7(response, v){
	if(response!='')$('#'+v).html('<img src="'+response+'" alt=""/>');
}

function upimg_a_11(response){
	if(response!='')$('#upload_v').append('<input type="hidden" class="upimg_id" value="'+response+'"/>');
}

function normalupload(i){
	var fileVal=$('#normalUploadf_'+i).val();
	if(($.trim(fileVal)).length<1){
		alert('请选择一个正确的图片文件');
		return false;
	}else{
		if(!(/\.(jpg|png|gif|jpeg)$/i.test(fileVal))){
			alert('请选择一个正确的图片文件');
			return false;
		}else{
			$('#normaluploadv_'+i).show();
			return true;
		}
	}
}

function hidenuv(i){
	$('#normalUploadf_'+i).val('');
	if($('#normaluploadv_'+i).length>0)$('#normaluploadv_'+i).hide();
}

function upload_switch(i){
	hidenuv(i);
	if($('#flashupload_'+i).is(':hidden')){
		$('#normalupload_'+i).hide();
		$('#flashupload_'+i).show();
	}else{
		$('#flashupload_'+i).hide();
		$('#normalupload_'+i).show();
	}
}

function movebg(){
	var x=$('#imgv_l').val();
	var y=$('#imgv_t').val();
	var iw=$('#img_mdiv').width();
	var ih=$('#img_mdiv').height();

	if($('#imgc_iw').val()>=$('#img_mbg').width()){
		var bl0=Math.round(x*($('#img_mbg').width()-$('#imgc_iw').val())/($('#img_mbg').width()-iw));
	}else{
		var bl0=Math.round(($('#img_mbg').width()-$('#imgc_iw').val())/2);
	}
	if($('#imgc_ih').val()>=$('#img_mbg').height()){
		var bt0=Math.round(y*($('#img_mbg').height()-$('#imgc_ih').val())/($('#img_mbg').height()-ih));
	}else{
		var bt0=Math.round(($('#img_mbg').height()-$('#imgc_ih').val())/2);
	}
	var ix=x-bl0;
	var iy=y-bt0;

	$('#imgc_w').val(iw);
	$('#imgc_h').val(ih);
	$('#imgc_x').val(ix);
	$('#imgc_y').val(iy);

	$('#img_ib').css({'width':$('#imgc_iw').val()+'px', 'height':$('#imgc_ih').val()+'px', 'margin-left':(bl0+1)+'px', 'margin-top':(bt0+1)+'px'});
	$('#img_is').css({'width':$('#imgc_iw').val()+'px', 'height':$('#imgc_ih').val()+'px', 'margin-left':(0-$('#imgc_x').val())+'px', 'margin-top':(0-$('#imgc_y').val())+'px'});
	$('#img_ss').css({'width':Math.round($('#imgc_iw').val()*$('#img_sdiv_s').width()/$('#imgc_w').val())+'px', 'height':Math.round($('#imgc_ih').val()*$('#img_sdiv_s').height()/$('#imgc_h').val())+'px', 'margin-left':(0-Math.round($('#imgc_x').val()*$('#img_sdiv_s').width()/$('#imgc_w').val()))+'px', 'margin-top':(0-Math.round($('#imgc_y').val()*$('#img_sdiv_s').height()/$('#imgc_h').val()))+'px'});
	$('#img_sb').css({'width':Math.round($('#imgc_iw').val()*$('#img_sdiv_b').width()/$('#imgc_w').val())+'px', 'height':Math.round($('#imgc_ih').val()*$('#img_sdiv_b').height()/$('#imgc_h').val())+'px', 'margin-left':(0-Math.round($('#imgc_x').val()*$('#img_sdiv_b').width()/$('#imgc_w').val()))+'px', 'margin-top':(0-Math.round($('#imgc_y').val()*$('#img_sdiv_b').height()/$('#imgc_h').val()))+'px'});
}

function movebar(mr){
	var tw=$('#move_tw').val();
	if(mr<0)mr=0;
	if(mr>tw)mr=tw;
	$('#move_bar').css('margin-left', mr+'px');
	$('#move_bar_mr').val(mr);

	var img_w=parseInt($('#img_w').val());
	var img_h=parseInt($('#img_h').val());
	if(img_w>=img_h){
		var nw=$('#img_mbg').width()+Math.round(mr*(img_w-$('#img_mbg').width())/tw);
		var nh=Math.round(nw*img_h/img_w);
	}else{
		var nh=$('#img_mbg').height()+Math.round(mr*(img_h-$('#img_mbg').height())/tw);
		var nw=Math.round(nh*img_w/img_h);
	}
	$('#imgc_iw').val(nw)
	$('#imgc_ih').val(nh)
	movebg();
}

function addplike(i, t){
	$('#c_plike_'+t+'_'+i).load('j/plike.php?id='+i+'&t='+t);
}

function upimg_ac_0(){
	var ic=$('#img_c').val();
	if(ic>0){
		$('#imgu_upload').show();
		if($.trim(($('#content').val())).length<1)$('#content').val('分享图片');	
		$('#content').focus();
	}
}

function upimg_ac_3(){
	if($('.upimg_id').length>0){
		var upid='';
		$('.upimg_id').each(function(){
			if($(this).val()>0)upid+='_'+$(this).val();
		});
		if(upid!='')location.href=$('#nu').val()+upid+'.html';
	}
}

function changel1(){
	var l1id=$('#l1id').val();
	if(l1id>0){
		$('#l2_s').load('j/l2se.php?t=1&l1id='+l1id);
		$('#xq_q').show();
	}else{
		$('#l2_s').html('');
		$('#xq_q').hide();
	}
}

function search_xq_h(t){
	var l1id=$('#l1id').val();
	var l2id=$('#l2id').val();
	var c=$.trim($('#xq_q').val());
	if(l1id>0){
		$('#xqs_div').html('<i>正在查找</i>');
		var u='j/xqsearch.php?l1id='+l1id+(l2id>0?'&l2id='+l2id:'');
		if(t && t>0)u+='&t='+t;
		$('#xqs_div').load(u, {q:c});
	}else{
		$('#xqs_div').html('');
	}
}

function addxq(){
	var c=$.trim($('#xjxq_n').html());
	if(c!=''){
		$('#xq_s').html(c+' (新增小区，需要等待审核)');
		$('#xqid').val('0');
		$('#xqname').val(c);
		if($('#nxqtd').is(':hidden'))$('#nxqtd').slideDown(500);
	}
	$('#xq_form').fadeOut(500);
	$('#lightbox_vbg').fadeOut(500);
	$('#lightbox_v').fadeOut(500);
}

function och1id(){
	var l1id=$('#o_l1id').val();
	if(l1id>0){
		$('#ndq2').load('j/l2se.php?l1id='+l1id);
	}else{
		$('#ndq2').html('');
	}
}

function och2id(){
	var l2id=$('#o_l2id').val();
	if(l2id>0){
		$('#ndq3').load('j/l2se.php?l2id='+l2id);
	}else{
		$('#ndq3').html('');
	}
}

function och3id(){
	var l3id=$('#o_l3id').val();
	if(l3id>0){
		$('#ndq4').load('j/l2se.php?l3id='+l3id);
	}else{
		$('#ndq4').html('');
	}
}

function showermsg(e, t){
	if(e!=''){
		$('#msg_'+t).html('<span class="error">'+e+'</span>');
		$('#is_er_'+t).val('0');
	}else{
		$('#msg_'+t).html('<span class="form_tip"><span class="ture">正确</span></span>');
		$('#is_er_'+t).val('1');
	}
}

function home_search_xq(l){
	setTimeout("$('#is_showsdiv').val('1');",100);
	var c=$.trim($('#chki_1').val());
	if(c!=''){
		if($('#xqid').val()>0 && c!=$('#xqname').val()){
			$('#xqid').val('0');
			$('#xqname').val('');
		}
		if($('#xqid').val()==0){
			$('#xqs_sdiv').html('<i>正在查找…</i>');
			var p=$('#chki_1').offset();
			$('#xqs_sdiv').css({'left':p.left+'px', 'top':(p.top+$('#chki_1').height()+10)+'px'});
			$('#xqs_sdiv').show();
			$('#xqs_sdiv').load('j/xqsearch.php?home=1&l1id='+l, {q:c}, function(){
				if($('#sr_c').val()>6){
					$('#xqs_sdiv').css('height', '156px');
				}else{
					$('#xqs_sdiv').css('height', '');
				}
			});
		}
	}else{
		$('#xqs_sdiv').css('height', '');
	}
}

function upload_p(k, id, fileName, loaded, total){
	$('#file_list_'+k).val('0');
	$('#file-uploader-demo'+k+' li').each(function(){
		var i=parseInt($('#file_list_'+k).val());
		if(i==id){
			var bgp=Math.round(100*loaded/total);
			$(this).css({'background-color':'#bfb', 'background-image':'url(images/loading-f.gif)', 'background-repeat':'no-repeat', 'background-position':'0% '+bgp+'%'});
		}
		i++;
		$('#file_list_'+k).val(i)
	});
}

function upload_c(k, id, fileName, responseJSON){
	$('#file_list_'+k).val('0');
	$('#file-uploader-demo'+k+' li').each(function(){
		var i=parseInt($('#file_list_'+k).val());
		if(i==id){
			$(this).css({'background-color':'', 'background-image':'', 'background-repeat':'', 'background-position':''});
		}
		i++;
		$('#file_list_'+k).val(i)
	});
	//$('.qq-upload-size').show();
}

function fx_link(u){
	var fx_id=$('#fx_id').val();
	var fx_u=$('#fx_url_'+fx_id).val();
	var fx_t=$('#fx_title_'+fx_id).val();
	var fx_t0=$('#fx_title0_'+fx_id).val();
	u=u.replace('[u]', fx_u);
	u=u.replace('[t]', fx_t);
	u=u.replace('[t0]', fx_t0);
	u=u.replace('[p]', '');
	window.open(u);
}

function showfxv(o, t){
	var p=o.offset();
	$('#fx_div').show();
	var l=p.left;
	if(t==1){
		var t=p.top-$('#fx_div').height();
		if($.browser.msie)t+=3;
	}else{
		var t=p.top+o.height();
	}
	$('#fx_div').css({'top':t+'px', 'left':l+'px'});
}

function jlmsg_p(){
	var st=$(document).scrollTop();
	if(st<25)st=25;
	st+=2;
	var sp=$('#yjl_top').offset();
	var sl=sp.left+$('#yjl_top').width()-$('#jlmsg_v').width()-22;
	$('#jlmsg_v').css({'left':sl+'px', 'top':st+'px'});
}

function jlmsg_v(){
	if($('#jlmsg_v').length>0){
		$.get('j/qzno.php', function(data){
			if(data!=''){
				if($('#jlmsg_isl').val()=='0' || data>$('#jlmsg_v').val()){
					$('#jlmsg_v').show();
					$('#jlmsg_isl').val('0');
					jlmsg_p();
				}
				$('#jlmsg_v').val(data);
				$('#msg_cs').html(data);
			}else{
				$('#jlmsg_v').hide();
			}
		});
		setTimeout("jlmsg_v();",2000);
	}
}

function yzm_sj(){
	var t=parseInt($('#yzm_bt_i').html());
	if(t>1){
		t--;
		$('#yzm_bt_i').html(t);
		setTimeout("yzm_sj();",1000);
	}else{
		$('#yzm_bt_0').show();
		$('#yzm_bt_1').hide();
	}
}

function showReport(bShow) {
	var arr = $(".photo_report_head a");
	if (bShow) {
		$("#form_report").show();
		$("#div_comment").hide();
		arr[1].className="reportTabSelected";
		arr[0].className="reportTab";
	} else {
		$("#form_report").hide();
		$("#div_comment").show();
		arr[0].className="reportTabSelected";
		arr[1].className="reportTab";
	}
}