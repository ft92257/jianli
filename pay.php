<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once($yjl_tpath.'setting/face.php');
$no_getxq=1;
require_once('function.php');
$f='estate.php';
$c='<br /><h2 class="h2">收费标准</h2>
<div class="vge_inf" style="padding: 20px;">
<table width="100%">
<tr><td align="center" colspan="2"><h3>公寓房</h3></td></tr>
<tr>
<td align="center" style="padding: 5px;">12步精监理<br/>2980元（130平米以内）</td>
<td style="padding: 5px;">1、协助业主洽谈装修合同，审核图纸，预算及装修合同的签订（一次）<br/>
2、开工三方现场交底及拆除（一次）<br/>
3、验收隐蔽材料，巡查工地，隐蔽工程验收（三次）<br/>
4、验收泥木材料，巡查工地，泥木工程验收（三次）<br/>
5、验收涂装材料，巡查工地，涂装工程验收（二次）<br/>
6、验收安装材料，巡查工地，安装洁具，竣工验收（二次）</td>
</tr>
<tr>
<td align="center" style="padding: 5px;">全程式监理<br/>50元/平米（150平米以下）</td>
<td style="padding: 5px;">1、协助业主洽谈装修合同，审核图纸，预算及装修合同的签订<br/>
2、开工三方现场交底及拆除<br/>
3、验收隐蔽材料，巡查工地，隐蔽工程验收<br/>
4、验收泥木材料，巡查工地，泥木工程验收<br/>
5、验收涂装材料，巡查工地，涂装工程验收<br/>
6、验收安装材料，巡查工地，安装洁具，竣工验收<br/>
7、监理每周到场三次<br/>
（提供验房服务）</td>
</tr>
<tr>
<td align="center" style="padding: 5px;">洽谈合同<br/>800元</td>
<td style="padding: 5px;">协助业主洽谈装修合同，审核图纸，预算及装修合同的签订</td>
</tr>
<tr>
<td align="center" style="padding: 5px;">备注</td>
<td style="padding: 5px;">车马费：外环以内免收车马费；外环以外易监理服务站2公里以内免收车马费，2公里外按照10%收取</td>
</tr>
<tr>
<tr><td align="center" colspan="2"><br/><br/><h3>别墅</h3></td></tr>
<td align="center" style="padding: 5px;">21步精监理<br/>8980元（150平米-250平米）</td>
<td style="padding: 5px;">1、协助业主洽谈装修合同，审核图纸，预算及装修合同的签订（二次）<br/>
2、开工三方现场交底及拆除（一次）<br/>
3、验收隐蔽材料，巡查工地，隐蔽工程验收（四次）<br/>
4、验收泥木材料，巡查工地，泥木工程验收（五次）<br/>
5、验收涂装材料，巡查工地，涂装工程验收（四次）<br/>
6、验收安装材料，巡查工地，安装洁具，竣工验收（五次）</td>
</tr>
<tr>
<td align="center" style="padding: 5px;">全程监理<br/>
80元/平米（150平米-300平米）<br/>
100元/平米（300平米-500平米）<br/>
120元/平米（500平米-800平米）</td>
<td style="padding: 5px;">1、协助业主洽谈装修合同，审核图纸，预算及装修合同的签订<br/>
2、开工三方现场交底及拆除<br/>
3、验收隐蔽材料，巡查工地，隐蔽工程验收<br/>
4、验收泥木材料，巡查工地，泥木工程验收<br/>
5、验收涂装材料，巡查工地，涂装工程验收<br/>
6、验收安装材料，巡查工地，安装洁具，竣工验收<br/>
7、监理每周到场三次<br/>
（提供验房服务）</td>
</tr>
<tr>
<td align="center" style="padding: 5px;">全托式监理<br/>
128元/平米（150平米-300平米）<br/>
160元/平米（300平米-500平米）<br/>
192元/平米（500平米-800平米）</td>
<td style="padding: 5px;">全程装潢管家服务（服务项目同全程监理服务项目相同）<br/>
每周监理到现场六次</td>
</tr>
<tr>
<td align="center" style="padding: 5px;">洽谈合同<br/>1000元</td>
<td style="padding: 5px;">协助业主洽谈装修合同，审核图纸，预算及装修合同的签订</td>
</tr>
<tr>
<td align="center" style="padding: 5px;">备注</td>
<td style="padding: 5px;">车马费：外环以内免收车马费；外环以外易监理服务站2公里以内免收车马费，2公里外按照10%收取</td>
</tr>
</table>
</div>';
echo yjl_html($c, 'supervisor');
?>