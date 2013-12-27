最简单的记事狗微博模板创建步骤：
1、在templates目录下创建一个新的目录，如 my_template_1
2、在刚才创建的 my_template_1 目录下，创建一个 styles 目录
3、在刚才创建的 styles 目录下，创建一个 main.css 文件
4、编辑刚才创建的 templates/my_template_1/styles/main.css 文件，在里面写上您想要的CSS样式代码，如
/* 引入默认模板的样式 */
@import url(../../default/styles/main.css);

/* 附加的自定义样式 */
body{
	font-size:14px;
}
5、在后台“系统设置” 》 “界面与显示” 中选择 “默认模板风格” my_template_1。至此，您的新模板就已经创建成功并启用了。



建议先在本地开发和调试好，再将模板上传到服务器上

