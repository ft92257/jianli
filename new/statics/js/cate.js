

	$.post('/ewm/index.php/ajax/returncatelist','',function(text){

	text = eval(text);

	 var plength=text.length
			for(i=0;i<plength;i++)
			{
			$("#c1").append(
	       "<option value="+text[i]["ca_id"]+">"+text[i]['cate_name']+"</option>")
			}
    })

   $('#c1').live('change', function(){

    
			$("#c2").empty()
            $("#c2").append("<option>请选择二级分类</option>")
			pid=(this.value)
			
			$.post('/ewm/index.php/ajax/getcatelist',{pid:pid},function(text){
			  text=eval(text);
			  
			  var length=text.length;
			for(i=0;i<length;i++)
			{
			$("#c2").append(
			"<option value="+text[i]["ca_id"]+">"+text[i]['cate_name']+"</option>")
			}
		})
    });
