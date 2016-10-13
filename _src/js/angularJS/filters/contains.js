app.filter('contains', function(){
    return function(arr, value){
        if (!angular.isArray(arr)) return false;
        for(var i = 0; i < arr.length;++i){
            if (arr[i] == value) return true;
        }
        return false;
    }
});