/**
 * 关系中心js函数
 *
 * @author     由~ZZ~<505171269@qq.com>整理
 * @version	   v1.0 $Date 2011-07-27
 */

/**
 * 移除粉丝对话框
 */
function DelMyFansAddDialog(uid)
{	
	var handle_key = 'del_my_fans';
	showDialog('del_my_fans', 'ajax', '移除粉丝', {"url":"ajax.php?mod=topic&code=del_myfans&uid="+uid}, 300);	
}

/**
 * 移除粉丝
 */
function DoDelMyFans()
{	
	//是否拉入黑名单
	var is_black = 0;
	
	//移除粉丝 ID
	var touid = $("#touid").val();
	
	if($("#is_black").attr("checked"))
    {
		is_black = '1';
	}

	var myAjax=$.post(
    	"ajax.php?mod=topic&code=do_delmyfans",
    	{
    		touid:touid,
    		is_black:is_black
    	},
    	function(d)
    	{
    		if(''!=d)
            {
	        	$("#fans_user_"+touid).remove();
	        	//关闭 移除粉丝对话框   `
				closeDialog('alert_follower_menu_'+touid);  
    		}
    	}
     );
}

