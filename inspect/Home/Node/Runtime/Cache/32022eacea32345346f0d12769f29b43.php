<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Insert title here</title>
</head>
<body>
<form action="__GROUP__/Index/index" method="post">
<select>
<option value="">请选择阶段</option>
<?php if(is_array($typeList)): foreach($typeList as $key=>$vo): ?><option value="<?php echo ($vo['id']); ?>"><?php echo ($vo['name']); ?></option><?php endforeach; endif; ?>
</select>
<br/>
<textarea rows="5" style='width:500px'></textarea>
<br/>
图片<input type='file'/>
<input name="act" value="submit"/>
<input type="submit" value="submit"/>
<br/>
</form>
</body>

</html>