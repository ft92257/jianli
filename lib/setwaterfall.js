/** water fall layout**/
(function ($) {
	$.fn.setwaterfall = function (options) {
		function findLowestIndex(Arr){
			var index = 0;
			var i;
			for (i in Arr){
				if(Arr[i]<Arr[index]){
					index = i;
				}
			}
			return index;
		};

		function findBigestIndex(Arr){
			var index = 0;
			var i;
			for (i in Arr){
				if(Arr[i]>Arr[index]){
					index = i;
				}
			}
			return index;
		};

		var boxwidth = $(this).width()+parseInt($(this).css("paddingLeft"))*2+parseInt($(this).css("marginLeft"))*2;
		var wrapWidth = $(this).parent().width();
		var col = Math.floor(wrapWidth/boxwidth);
		var wrappadding = (wrapWidth % boxwidth) /2;
		var row = Math.floor($(this).length/col) == $(this).length/col?$(this).length/col:(Math.floor($(this).length/col)+1);
		var colhigharry = [];
		var colpos;
		var leftpos;
		var toppos;
		for(var len = 0;len < col;len++){
			colhigharry.push(0);
		};
		$(this).each(function(index){
			var pos = index;
			var col1height = 0;
			var col2height = 0;
			var col3height = 0;
			var col4height = 0;
			if(pos<col){
				leftpos = boxwidth*pos + wrappadding + "px";
				$(this).css({"top":"0","left":leftpos});
				colhigharry[index] = $(this).height()+parseInt($(this).css("marginTop"))*2+parseInt($(this).css("paddingTop"))+parseInt($(this).css("paddingBottom"));
			}else{
				colpos = findLowestIndex(colhigharry);
				leftpos = boxwidth*colpos + wrappadding +"px";
				toppos = colhigharry[colpos]+"px";
				$(this).css({"top":toppos,"left":leftpos});
				colhigharry[colpos] =	colhigharry[colpos] + $(this).height()+parseInt($(this).css("marginTop"))*2+parseInt($(this).css("paddingTop"))+parseInt($(this).css("paddingBottom"));
			}				
		});
		var wraphighindex = findBigestIndex(colhigharry);
		var wraphigh = colhigharry[wraphighindex]+"px";
		$(this).parent().height(wraphigh);				
		var selector = $(this).selector;
		window.onresize = function(){$(selector).setwaterfall();};
	}
})(jQuery);