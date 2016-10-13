app.filter('yesNo', function(){
   return  function(i){
       switch(typeof(i)){
           case 'boolean': return i?'yes':'no';
           case 'integer': return parseInt(i) == '1' ?'yes':'no';
           case 'string': return i == 'true';
           default: return typeof(i) != 'undefined' ?'yes':'no';
       }
   }
});