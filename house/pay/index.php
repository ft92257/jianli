<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML><HEAD><TITLE>财付通付款通道</TITLE>
<META http-equiv=Content-Type content="text/html; charset=utf-8">
<META 
content=网上购物/网上支付/安全支付/安全购物/购物，安全/支付,安全/财付通/安全,支付/安全，购物/支付,在线/付款,收款/网上,贸易/网上贸易. 
name=description>
<META 
content=网上购物/网上支付/安全支付/安全购物/购物，安全/支付,安全/财付通/安全,支付/安全，购物/支付,在线/付款,收款/网上,贸易/网上贸易. 
name=keywords>
<META content="MSHTML 6.00.3790.2577" name=GENERATOR>

<script type='text/javascript' src='http://union.tenpay.com/bankList/jquery.js'></script>
<link rel="stylesheet" type="text/css" href="http://union.tenpay.com/bankList/css_col4.css"/>

<style type="text/css">
<!--
a:link {
	color: #003399;
}
.px12 {
	font-size: 12px;
}
a:visited {
	color: #003399;
}
a:hover {
	color: #FF6600;
}
.px14 {
	font-size: 14px;
}
.px12hui {
	font-size: 12px;
	color: #999999;
}
.STYLE2 {font-size: 14px; font-weight: bold; }
-->
</style>
</HEAD>
<BODY topMargin=0>

<div align="center">
  <table width="760" height="25" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td></td>
    </tr>
  </table>
  <table width="760" height="406" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td align="center" valign="top">
		<table width="760" border="0" cellspacing="0" cellpadding="3" align="center">
        <tr>
          <td height="30" width="5" bgcolor="#666666"></td>
          <td width="743" height="30" bgcolor="#FF6600"><font style="color:#FFFFFF;font-size:14px;"><B> 　易监理快速充值通道 </B></font></td>
        </tr>
		</table>
        <table width="760" height="42" border="0" align="center" cellpadding="0" cellspacing="0">
               <tr> <td height="30" ><span class="STYLE2"><img src="image/arrow_02_01.gif"> 填写信息</span></td>
            </tr>
        </table>
        <table width="760" border="0" cellspacing="0" cellpadding="0" align="center" height="1">
            <tr>
              <td width="740"></td>
              <td width="20"></td>
            </tr>
        </table>
        <table width="760" border="0" align="center" cellpadding="0" cellspacing="0">
        <script language="javascript">
	function payFrm()
	{
		if (directFrm.order_no.value=="")
		{
			alert("提醒：请填写订单编号；如果无特定的订单编号，请采用默认编号！（刷新一下页面就可以了）");
			directFrm.order_no.focus();
			return false;
		}
		if (directFrm.product_name.value=="")
		{
			alert("提醒：请填写商品名称(付款项目)！");
			directFrm.product_name.focus();
			return false;
		}
		if (directFrm.order_price.value=="")
		{
			alert("提醒：请填写订单的交易金额！");
			directFrm.order_price.focus();
			return false;
		}
		
		if (directFrm.remarkexplain.value=="")
		{
			alert("提醒：请填写您的简要说明！");
			directFrm.remarkexplain.focus();
			return false;
		}
		if (directFrm.remarkexplain.value.length>31)
		{
			alert("提醒：超出规定的字数,请重新输入");
  			event.returnValue=false;   
  			return   false;   
		}
		
		return true;
	}
  </script>

		<form action='tenpay.php' method='post' name='directFrm' onSubmit="return payFrm();">
            <tr>
              <td align="left">
				<table width="760" height="30" border="0" align="left" cellpadding="0" cellspacing="1" bgcolor="#FFCC00">
                  <tr>
                    <td align="center" valign="top" bgcolor="#FFFFEE"><table width="760" height="351" border="0" cellpadding="6" cellspacing="0" class="px14">
                      <tr>
                        <td valign="" style="padding-left:30px;padding-top:15px;">充值金额：
						<!--<select>
							<option value="100">100元</option>
							<option value="300">300元</option>
							<option value="1000">1000元</option>
						</select>-->
						<input type="text" name="order_price" value="1000" maxlength="50" size="18" onKeyUp="if(isNaN(value))execCommand('undo')" onafterpaste="if(isNaN(value))execCommand('undo')" font style="color:#000000;font-size:14px;"> 
                        元
						</td>
                      </tr>

					  <tr>
						<td>
							<div id="tenpayBankList"></div>
							<input type="hidden" name="bank_type_value" id="bank_type_value" value="0">
							<script>$.getScript("http://union.tenpay.com/bankList/bank.js");</script>
						</td>
					  </tr>
						
					  <tr>
                        <td valign="top" style="padding-left:30px;padding-bottom:15px;">
						<b>
                          <input name="submit" type="image" src="image/next.gif" alt="使用财付通安全支付" width="103" height="27">
                        </b>
						</td>
                      </tr>
                    </table></td>
                  </tr>
              </table>
			 </td>
            </tr> </form>
        </table>
	  </td>
    </tr>
  </table>
</div>

</body>
</html>
