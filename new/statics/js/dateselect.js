 var $$ = function(sId){
   return "string" == typeof(sId) ? document.getElementById(sId) : id;
 }

 var Extend = function(distination, source){
   for(var property in source){
       distination[property] = source[property];
   }
   return distination;
 }

 function addEventHandler(oTarget, sEventType, fnHandler){
   if(oTarget.addEventListener){
       oTarget.addEventListener(sEventType, fnHandler, false);
   }else if(oTarget.attachEvent){
       oTarget.attachEvent("on" + sEventType, fnHandler);
   }else{
       oTarget["on" + sEventType] = fnHandler;
   }
 }

 var Class = {
   create : function(){
       return function(){
           this.initialize.apply(this, arguments);
       }
   }
 }

 var DateSelector = Class.create();

 DateSelector.prototype = {
   initialize: function(oYear, oMonth, oDay, options){
       this.SelYear = $$(oYear);
       this.SelMonth = $$(oMonth);
       this.SelDate = $$(oDay);
       this.SetOptions(options);

       var dt = new Date(), iMonth = parseInt(this.options.Month), iDay = parseInt(this.options.Day), iMaxYear = parseInt(this.options.MaxYear), iMinYear = parseInt(this.options.MinYear);

       this.Year = parseInt(this.options.Year) || dt.getFullYear();
       this.Month = 1 <= iMonth && iMonth <= 12 ? iMonth : dt.getMonth() + 1;
       this.Day = 1 <= iDay ? iDay : dt.getDate();
       this.MinYear = iMinYear && iMinYear < this.Year ? iMinYear : this.Year;
       this.MaxYear = iMaxYear && iMaxYear > this.Year ? iMaxYear : this.Year;
       this.onChange = this.options.onChange;

       this.SetSelect(this.SelYear, this.MinYear, this.MaxYear - this.MinYear + 1, this.Year - this.MinYear);

       this.SetSelect(this.SelMonth, 1, 12, this.Month - 1);

       this.SetDay();
       var oThis = this;

       addEventHandler(this.SelYear, "change", function(){
           oThis.Year = oThis.SelYear.value; oThis.SetDay(); oThis.onChange();
       });

       addEventHandler(this.SelMonth, "change", function(){
           oThis.Month = oThis.SelMonth.value; oThis.SetDay(); oThis.onChange();
       });

       addEventHandler(this.SelDate, "change", function(){
           oThis.Day = oThis.SelDate.value; oThis.onChange();
       });    
   },
   SetOptions: function(options){
       this.options = {
           Year: 0,
           Month: 0,
           Day: 0,
           MaxYear: 0,
           MinYear: 0,
           onChange: function(){}
       };

       Extend(this.options, options || {});        
   },
   SetDay: function(){
       var daysInMonth = new Date(this.Year, this.Month, 0).getDate();
       if(this.Day > daysInMonth) { this.Day = daysInMonth};
       this.SetSelect(this.SelDate, 1, daysInMonth, this.Day - 1);
   },
   SetSelect: function(oSelect, iStart, iLength, iIndex){
       oSelect.options.length = iLength;
       for(var i = 0; i < iLength; i++){
           oSelect.options[i].value = oSelect.options[i].text = iStart + i;
       }
       oSelect.selectedIndex = iIndex;
   }    
 }