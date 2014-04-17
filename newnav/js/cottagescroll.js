// JavaScript Document
$("p[class = nv1]").css('backgroundPosition', 'left -45px');
	$("p[class = nv1]").next("strong").css({'background':'#EB7C49','color':'#fff'});
	$("#snav a").click(function(){
		var sindex=$(this).index()+1;
		var tt =$("#s"+sindex).offset().top-$("#moBox").parent().height();
		$(window).scrollTop(tt);
		var left = (-45)*($(this).attr('eq')-1);
		$(this).find("p").css('backgroundPosition', left+'px -45').parent("a").siblings("a").find("p").css('backgroundPosition', left+'px 0');
		$(this).find("strong").addClass("ck_s").parent("a").siblings("a").find("strong").removeClass("ck_s");
	});
	var move2=$("#s2").offset().top-180;
	var move3=$("#s3").offset().top-180;
	var move4=$("#s4").offset().top-180;
	var move5=$("#s5").offset().top-180;
	$(window).bind('scroll',function(){
		var s = document.documentElement.scrollTop+document.body.scrollTop; 
		if(s < move2){
			a([2,3,4,5]);
			$("p[class = nv1]").css('backgroundPosition', 'left -45px');
			$("p[class = nv1]").next("strong").css({'background':'#EB7C49','color':'#fff'});
		}else if(s >= move2 && s < move3){
			a([1,3,4,5]);
			$("p[class = nv2]").css('backgroundPosition', '-45px -45px');
			$("p[class = nv2]").next("strong").css({'background':'#EB7C49','color':'#fff'});
		}else if(s >= move3 && s < move4){
			a([1,2,4,5]);
			$("p[class = nv3]").css('backgroundPosition', '-90px -45px');
			$("p[class = nv3]").next("strong").css({'background':'#EB7C49','color':'#fff'});
		}else if(s >= move4 && s < move5){
			a([1,2,3,5]);
			$("p[class = nv4]").css('backgroundPosition', '-135px -45px');
			$("p[class = nv4]").next("strong").css({'background':'#EB7C49','color':'#fff'});
		}else if(s >= move5){
			a([1,2,3,4]);
			$("p[class = nv5]").css('backgroundPosition', '-180px -45px');
			$("p[class = nv5]").next("strong").css({'background':'#EB7C49','color':'#fff'});
		}
		
	});
	function a(arr){
		for(i in arr){
				$("p[class = nv"+arr[i]+"]").css('backgroundPosition', ((-45)*(arr[i]-1))+'px top');
				$("p[class = nv"+arr[i]+"]").next("strong").css({'background':'none','color':'#000'});
			}
		}