$(function(){
	cityAjax(0,1);
	house_typeAjax(0,1);
	
	/*select下拉模拟*/
	if($(".sel-option").length>0){
		$(".sel-option").each(function(){
			$(this).find("p").unbind('click');
			$(this).find("p").bind('click', function(event){
				
				$(this).parent(".sel-option").css("zIndex","11111")
				$(".sel-option").not($(this).parent()).css("zIndex","1").find(".option-show").slideUp();

				$(this).next(".option-show").slideToggle(150);
				event.stopPropagation();
			});
			$(this).find("a").unbind('click');
			$(this).find("a").bind('click', function(){
				var optionIndex=$(this).parent().index();
				var tparents=$(this).parents(".sel-option");
				$(this).addClass("selected");
				$(this).parent("li").siblings().find("a").removeClass("selected");
				tparents.find("p").text($(this).text());
				tparents.next("select").find("option").eq(optionIndex).attr("selected","selected").siblings("option").removeAttr("selected");
				$(this).parents(".option-show").slideUp();
			});
		});
		$(document).click(function(){
			$(".sel-option .option-show").slideUp();
		});
	}
	
	
	
});
/*
	$("form").validationEngine('attach', {
		onValidationComplete: function(form, status){
			if (status){
				form.submit();
			}
		}  
	});
});
*/
/*房型户型列表联动*/
function house_typeAjax(id,level){
	$.ajax({
		type:'post',
		url:GROUP+'/House_type/house_typeSelect',
		async:false,
		data:'id='+id+'&level='+level,
		dataType:'json',
		success:function(r){
			createHouse_type(r, level);
		}
	});
}

function cityAjax(id,level){
	$.ajax({
		type:'post',
		url:GROUP+'/City/citySelect',
		async:false,
		data:'id='+id+'&level='+level,
		dataType:'json',
		success:function(r){
			createCity(r, level);
		}
	});
}

function createHouse_type(json,level){
	if(level == 1){
		tag = '房型';
		field ="room";
	} else if(level == 2){
		tag = '户型';
		field ="apartment";
	}
	var html = [];
	html.push('<div id="house_'+level+'"><div class="left sel-mn1 sel-house mr-1">');
	html.push('<p unselectable="on">'+tag+'</p>');
	html.push('<ul class="option-list option-show w-wid96">');
	html.push('<li><a data-value="0" data-level="'+level+'" href="javascript:;" class="selected">'+tag+'</a></li>');
	for(var i in json){
		html.push('<li><a href="javascript:;" data-value="'+json[i]['id']+'" data-level="'+json[i]['level']+'">'+json[i]['name']+'</a></li>');
	}
	html.push('</ul></div>');
	html.push('<select name="'+field+'" class="select1" style="display:none">');
	html.push('<option value="0">'+tag+'</option>');
	for(var i in json){
		html.push('<option value="'+json[i]['id']+'">'+json[i]['name']+'</option>');
	}
	html.push('</select></div>');
	
	$("#div_house_type").append(html.join(''));
	
	/*select下拉模拟*/
	if($(".sel-house").length>0){
		$(".sel-house").each(function(){
			$(this).find("p").unbind('click');
			$(this).find("p").bind('click', function(event){
				
				$(this).parent(".sel-house").css("zIndex","11111")
				$(".sel-house").not($(this).parent()).css("zIndex","1").find(".option-show").slideUp();

				$(this).next(".option-show").slideToggle(150);
				event.stopPropagation();
			});
			$(this).find("a").unbind('click');
			$(this).find("a").bind('click', function(){
				var tparents=$(this).parents(".sel-house");
				$(this).addClass("selected");
				$(this).parent("li").siblings().find("a").removeClass("selected");
				tparents.find("p").text($(this).text());
				tparents.next().val($(this).attr('data-value'));
				$(this).parents(".option-show").slideUp();
				if($(this).attr('data-level') < 2){
					level = $(this).attr('data-level')*1+1;
					houseRemove($(this).attr('data-level'));
					if($(this).attr('data-value')>0){
						house_typeAjax($(this).attr('data-value'), level);
					}
				}
			});
		});
		$(document).click(function(){
			$(".sel-house .option-show").slideUp();
		});
	}
}

function createCity(json,level){
	if(level == 1){
		tag = '省/直辖市';
		field ="province";
	} else if(level == 2){
		tag = '市/区';
		field ="city";
	} else if(level == 3){
		tag = '区/县';
		field ="county";
	}
	var html = [];
	html.push('<div id="city_'+level+'"><div class="left sel-mn1 sel-city mr-1">');
	html.push('<p unselectable="on">'+tag+'</p>');
	html.push('<ul class="option-list option-show w-wid96">');
	html.push('<li><a data-value="0" data-level="'+level+'" href="javascript:;" class="selected">'+tag+'</a></li>');
	for(var i in json){
		html.push('<li><a href="javascript:;" data-value="'+json[i]['id']+'" data-level="'+json[i]['level']+'">'+json[i]['name']+'</a></li>');
	}
	html.push('</ul></div>');
	html.push('<select class="select1" style="display:none" name="'+field+'">');
	html.push('<option value="0">'+tag+'</option>');
	for(var i in json){
		html.push('<option  value="'+json[i]['id']+'">'+json[i]['name']+'</option>');
	}
	html.push('</select></div>');
	
	$("#div_city").append(html.join(''));
	
	/*select下拉模拟*/
	if($(".sel-city").length>0){
		$(".sel-city").each(function(){
			$(this).find("p").unbind('click');
			$(this).find("p").bind('click', function(event){
				
				$(this).parent(".sel-city").css("zIndex","11111")
				$(".sel-city").not($(this).parent()).css("zIndex","1").find(".option-show").slideUp();

				$(this).next(".option-show").slideToggle(150);
				event.stopPropagation();
			});
			$(this).find("a").unbind('click');
			$(this).find("a").bind('click', function(){
				var tparents=$(this).parents(".sel-city");
				$(this).addClass("selected");
				$(this).parent("li").siblings().find("a").removeClass("selected");
				tparents.find("p").text($(this).text());
				tparents.next().val($(this).attr('data-value'));
				$(this).parents(".option-show").slideUp();
				if($(this).attr('data-level') < 3){
					level = $(this).attr('data-level')*1+1;
					cityRemove($(this).attr('data-level'));
					if($(this).attr('data-value')>0){
						cityAjax($(this).attr('data-value'), level);
					}
				}
			});
		});
		$(document).click(function(){
			$(".sel-city .option-show").slideUp();
		});
	}
}

function cityRemove(level){
		if(level == 1){
			$("#city_2").remove();
			$("#city_3").remove();
		} else if(level == 2){
			$("#city_3").remove();
		}
	}
	
function houseRemove(level){
	if(level == 1){
		$("#house_2").remove();
	}
}