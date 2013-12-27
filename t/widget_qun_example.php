<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
	<script type="text/javascript" src="./templates/default/js/min.js"></script>
		<style type="text/css">
			.jsg_mcenter{ width:600px; height:500px; background:#ffffff; border:1px solid #ebebeb; padding:1px; text-align:left}.iframe_share_title{ width:100%; height:30px; line-height:30px; overflow:hidden; background:#ffffff; color:#626262; font-size:14px; text-indent:7px;}.jsg_mcenter .comBox{ display:none;margin:10px 0; overflow:hidden; padding:10px; z-index:200}.jsg_mcenter .indexrow{ width:585px;}.jsg_mcenter .feedCell{ width:585px; overflow:hidden;}.jsg_mcenter .msg_left_img{ width:47px; height:47px; float:left;display:inline;margin-right:10px;}.jsg_mcenter .msg_left_con{ float:left; display:inline; white-space:normal; *height: *height:100%;width:520px; overflow:hidden;}.jsg_mcenter .feedCell {padding:6px; line-height:18px; *width:100%; *height:100%; overflow:hidden;}.jsg_mcenter .feedCell .avatar {display:inline;float:left;height:45px;width:45px; overflow:hidden; margin-bottom:-15px;margin-top:4px;text-align:center;border:1px solid #ccc; padding:1px;}.jsg_mcenter .feedCell .avatar img {height:45px;width:45px;}.jsg_mcenter .feedCell .feedUservideo {overflow:hidden;position:relative;}.jsg_mcenter .feedCell .feedUservideo img {border:1px solid #dcdcdc;padding:2px; cursor:pointer; width:120px; height:80px;}.jsg_mcenter .feedCell .feedUservideo .vP{position:absolute; margin:27px 46px;}.jsg_mcenter .feedCell .feedUservideo .vP img{border:none; filter:alpha(opacity=70);-moz-opacity:0.7; opacity:0.7; width:25px; height:25px;}.jsg_mcenter .bg_arrow{background:#e6e6e6 url(http:		</style>
	
	<script language="javascript">
		$(document).ready(function(){
			$.get(
				"widget.php?mod=qun&code=list",
				{
					qid:1,
					page:1,
					page_size:10
				},
				function (d) {
					if (d.error == true) {
						alert("no result");
					} else {
						var topicList = d.result.topic_list;
						var count = topicList.length;
						var html = '';
						for (var i in topicList) {
							html =html + '<div id="ds1" class="indexrow"><div class="feedCell"><div style="color:#626262;"><div class="msg_left_img"><span class="avatar"><img border="0" src="'+topicList[i].face_small+'" onerror="javascript:faceError(this);"></span>&nbsp;</div><div style="margin-top:3px;" class="msg_left_con">'+ topicList[i].nickname +':<a style="color:#7d7d7d;" target="_blank" href="http:							
						}$("#Pcontent").html(html);
					}
				},
				"json"
			);
		});
	</script>
	</head>
	<body>
	<div class="jsg_mcenter">
		<div style="z-index:100;height:480px; overflow:hidden; position:absolute; margin-left:5px;" id="Pcontent"></div>
	</div>
	</body>
</html>