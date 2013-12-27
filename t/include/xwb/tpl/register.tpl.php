<?php if (!defined('IS_IN_XWB_PLUGIN')) {die('access deny!');}?><?php if('UTF-8' != strtoupper($GLOBALS['_J']['config']['charset'])){@header("Content-type: text/html; charset=utf-8");}?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>新浪微博帐号登录</title>
<link type="text/css" rel="stylesheet" href="<?php echo $GLOBALS['_J']['site_url'] . ('/images/xwb/xwb_'. XWB_S_VERSION .'.css');?>" />
<script language="javascript">
	document.onkeydown = function (e){
			var ev =  e || window.event ;
			if(ev.keyCode==13 && parent.XWBcontrol.isEndReg){
				parent.XWBcontrol.close('reg');
				window.top.location='index.php';
			}
	}

	function $id (id) {return document.getElementById(id);}
	
	function xwbSetTips(msg){
		/// 注册成功
		if (msg[0]>0){
			// 是否提示成功
			if (msg[2]==1){
                var regBox = $id('regBox');
                var action = 'none' == regBox.style.display ? 'bind' : 'reg';
                var head = 'none' == regBox.style.display ? 'forBindH3' : 'forRegH3';
                var forHead = $id(head);
                forHead.style.display = 'none';
				$id(action+'SuccessBox').style.display	= '';
				$id(action+'Box').style.display			= 'none';
				$id(action+'SuccessTips').innerHTML		= msg[1];
				parent.XWBcontrol.isEndReg = true;
				parent.XWBcontrol.ck.del('xwb_tips_type');
			}else{
				window.top.location='index.php';
			}
		}else{
			setError(msg[1]);
		}
	}
	
	function submitForm(id){
        clearError();
		$id(id).submit();
	}
	
	function setError(msg){
		clearError();
        var action = 'none' == $id('regBox').style.display ? 'bind' : 'reg';
		$id(action+'ErrorTips').style.display = '';
		$id(action+'ErrorTips').innerHTML = msg;
	}
	
	function clearError(){
        var action = 'none' == $id('regBox').style.display ? 'bind' : 'reg';
		$id(action+'ErrorTips').style.display = 'none';
	}

    function forTurn(action) {
        $id(action+'ErrorTips').style.display = 'none';
        var title = parent.document.getElementById('_xwb_dlg_tle');
        var forRegH3 = $id('forRegH3');
        var forBindH3 = $id('forBindH3');
        var regBox = $id('regBox');
        var bindBox = $id('bindBox');
        //var inputIds = new Array('siteRegName', 'siteRegEmail', 'regPwd', 'siteBindName', 'bindPwd', 'questionid', 'questionanswer');
        //var x;
        //for (x in inputIds) {
            //if ('questionid' == inputIds[x]) {
                //$id(inputIds[x]).value = 0;
            //} else {
                //$id(inputIds[x]).value = ''
            //}
        //};

        if ('reg' == action) {
            title.innerHTML = '第一次登录设置';
            forBindH3.style.display = 'none';
            bindBox.style.display = 'none';
            forRegH3.style.display = '';
            regBox.style.display = '';
        } else {
            title.innerHTML = '绑定已有帐号';
            forRegH3.style.display = 'none';
            regBox.style.display = 'none';
            forBindH3.style.display = '';
            bindBox.style.display = '';
        }
    }
</script>
</head>

<body id="xwb-plugin-register-layer" class="xwb-plugin" style="display:block;">
<h3 id="forRegH3" class="xwb-plugin-layer-title">您正在用<a class="xwb-plugin-td-right-a" href="http://weibo.com/u/<?php echo $sina_user_info['id'];?>" target="_blank">新浪微博帐号</a>登录本站，如您还无本站帐号请设置以下信息：</h3>
<h3 id="forBindH3" class="xwb-plugin-layer-title" style="display:none;">输入已有的<?php echo XWB_S_TITLE ;?>帐号信息，绑定当前<a class="xwb-plugin-td-right-a" href="http://weibo.com/u/<?php echo $sina_user_info['id'];?>" target="_blank">新浪微博帐号</a>。</h3>
<div id="regBox" class="xwb-plugin-form" style="display:block;">
    
    <?php $regstatus = jsg_member_register_check_status(); if($regstatus['error'] || (true!==JISHIGOU_FORCED_REGISTER && $regstatus['invite_enable'] && !$regstatus['normal_enable'])) { ?>
    
       非常抱歉，本站目前禁止绑定新注册用户；<a href="javascript:void(function(){})" class="xwb-plugin-td-right-a" onclick="forTurn('bind')" tabindex="-1"><span><strong>请点此绑定已有的帐号</strong></span></a>
                
    <?php } else { ?>
    
    <form action="<?php echo XWB_plugin::URL("xwbSiteInterface.doReg");?>" id="siteRegFrom"  method="post" target="xwbSiteRegister"  >
		<input type="hidden" name="FORMHASH" value="<?php echo FORMHASH; ?>" />
        <table class="xwb-plugin-table">
            <tr class="xwb-plugin-tr">
                <td class="xwb-plugin-td-msg"><label for="siteRegEmail">帐号昵称：</label></td>
                <td class="xwb-plugin-td-input">
                    <input type="text" name="siteRegNickname"	id="siteRegNickname" class="xwb-plugin-input-a" value="<?php echo $sina_user_info['screen_name'];?>" />
                    <br />用于登录、显示、@通知和私信等
                </td>
                <td rowspan="3" class="xwb-plugin-td-right-msg">
                    <span style="color:red;">已经有本站帐号？</span><a href="javascript:void(function(){})" class="xwb-plugin-td-right-a" onclick="forTurn('bind')" tabindex="-1"><strong >请点此进行绑定</strong></a>
                </td>
            </tr>
            <tr class="xwb-plugin-tr">
                <td class="xwb-plugin-td-msg"><label for="siteRegEmail">常用邮箱：</label></td>
                <td class="xwb-plugin-td-input">
                    <input type="text" name="siteRegEmail"	id="siteRegEmail" class="xwb-plugin-input-a" />
                </td>
            </tr>
            <tr class="xwb-plugin-tr">
                <td class="xwb-plugin-td-msg"><label for="regPwd">登录密码：</label></td>
                <td class="xwb-plugin-td-input">
                    <input name="regPwd" type="password"  class="xwb-plugin-input-a" id="regPwd" maxlength="256" />
					 <br />直接用上述昵称登录本站时使用
                </td>
            </tr>
            <tr class="xwb-plugin-tr-btn">
                <td colspan="3" class="xwb-plugin-td-btn">
                    <span class="xwb-plugin-btn">
                        <input name="registerBt1" type="button" onclick="submitForm('siteRegFrom')" id="registerBt1" value="确定提交" />
                    </span>
                </td>
            </tr>
            <tr class="xwb-plugin-tr-error">
                <td colspan="3">
                    <em id="regErrorTips" class="xwb-plugin-error" style="display:none;"></em>
                </td>
            </tr>
        </table>
    </form>
    
    <?php } ?>
    
</div>

<div id="bindBox" class="xwb-plugin-form" style="display:none;">
    <form action="<?php echo XWB_plugin::URL("xwbSiteInterface.doBindAtNotLog");?>" id="siteBindFrom"  method="post" target="xwbSiteRegister"  >
		<input type="hidden" name="FORMHASH" value="<?php echo FORMHASH; ?>" />
        <table class="xwb-plugin-table">
            <tr class="xwb-plugin-tr">
                <td class="xwb-plugin-td-msg"><label for="bindPwd">帐号昵称：</label></td>
                <td class="xwb-plugin-td-input">
                    <input type="text" name="siteBindName"	id="siteBindName" class="xwb-plugin-input-a" value="" />
                    <br />请输入您在 <?php echo XWB_S_TITLE; ?> 的昵称
                </td>
                <td rowspan="4" class="xwb-plugin-td-right-msg">
                    还没有本站帐号？<a href="javascript:void(function(){})" class="xwb-plugin-td-right-a" onclick="forTurn('reg')" tabindex="-1"><strong>点击这里进行设置</strong></a>
                </td>
            </tr>
            <tr class="xwb-plugin-tr">
                <td class="xwb-plugin-td-msg"><label for="bindPwd">登录密码：</label></td>
                <td class="xwb-plugin-td-input">
                    <input name="bindPwd" type="password" class="xwb-plugin-input-a" id="bindPwd" maxlength="256" />
                    <br />请输入本站登录密码
                </td>
            </tr>
            <tr class="xwb-plugin-tr-btn">
                <td colspan="3" class="xwb-plugin-td-btn">
                    <span class="xwb-plugin-btn">
                        <input name="bindBt" type="button" onclick="submitForm('siteBindFrom')" id="bindBt" value="确认绑定" />
                    </span>
                </td>
            </tr>
            <tr class="xwb-plugin-tr-error">
                <td colspan="3">
                    <em id="bindErrorTips" class="xwb-plugin-error" style="display:none;"></em>
                </td>
            </tr>
        </table>
    </form>
</div>

<div id="regSuccessBox" style="display:none;" class="xwb-plugin-reg-successBox">
	<div class="xwb-plugin-reg-successBox-forIE6">
        <p class="xwb-plugin-regSuccess xwb-plugin-reg-successBox-p">
            <strong>恭喜，创建成功!</strong>
            <span id="regSuccessTips" ></span>
        </p>
    </div>
    <div class="xwb-plugin-reg-successBox-btn">
        <span class="xwb-plugin-btn">
            <input name="registerBt2" type="button" onclick="window.top.location='index.php';" id="registerBt2" value="确 定" />
        </span>
    </div>
</div>

<div id="bindSuccessBox" style="display:none;" class="xwb-plugin-reg-successBox">
	<div class="xwb-plugin-reg-successBox-forIE6">
        <p class="xwb-plugin-regSuccess xwb-plugin-reg-successBox-p">
            <strong>恭喜，绑定成功!</strong>
            <span id="bindSuccessTips" ></span>
        </p>
    </div>
    <div class="xwb-plugin-reg-successBox-btn">
        <span class="xwb-plugin-btn">
            <input name="bindBt2" type="button" onclick="window.top.location='index.php';" id="bindBt2" value="确 定" />
        </span>
    </div>
</div>
<iframe src="" name="xwbSiteRegister" frameborder="0" height="0" width="0"></iframe>
</body>
</html>
